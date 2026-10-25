<?php
namespace App\Libraries;

use ZipArchive;
use SimpleXMLElement;

/**
 * Lightweight CSV / XLSX table reader.
 * Returns ['headers' => [...], 'rows' => [[colA, colB, ...], ...]]
 * XLSX is parsed natively (sharedStrings.xml + sheet1.xml) so no
 * external spreadsheet package is required.
 */
class FileTableReader
{
    public const MAX_ROWS = 2000;

    private array $headers = [];
    private array $rows    = [];
    public  string $error  = '';

    public function read(string $path, string $originalName): bool
    {
        $this->headers = [];
        $this->rows    = [];
        $this->error   = '';
        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        try {
            if ($ext === 'csv' || $ext === 'txt') {
                return $this->readCsv($path);
            }
            if (in_array($ext, ['xlsx', 'xlsm'], true)) {
                return $this->readXlsx($path);
            }
            // sniff real content when the extension lies (common with xlsx saved as xls)
            $head = (string)file_get_contents($path, false, null, 0, 4);
            if (str_starts_with($head, "PK\x03\x04") || str_starts_with($head, "PK\x05\x06")) {
                return $this->readXlsx($path);
            }
            return $this->readCsv($path);
        } catch (\Throwable $e) {
            $this->error = 'Could not read the file. Please save it as .csv or .xlsx and try again. (' . $e->getMessage() . ')';
            return false;
        }
    }

    // ── CSV — delimiter sniffing, quoted fields, BOM ──────────
    private function readCsv(string $path): bool
    {
        $fh = fopen($path, 'r');
        if (!$fh) { $this->error = 'Cannot open file.'; return false; }

        $raw = (string)fgets($fh);
        rewind($fh);
        if (str_starts_with($raw, "\xEF\xBB\xBF")) {
            // strip UTF-8 BOM by re-reading via encoding conversion
            $whole = stream_get_contents($fh);
            $whole = substr($whole, 3);
            $tmp = tmpfile();
            fwrite($tmp, $whole);
            rewind($tmp);
            fclose($fh);
            $fh = $tmp;
        }

        $first = (string)fgets($fh);
        rewind($fh);
        $delim = ',';
        foreach ([",", ";", "\t"] as $d) {
            if (substr_count(trim($first), $d) > substr_count(trim($first), $delim)) $delim = $d;
        }
        if (trim($first) === '') { $this->error = 'The file appears to be empty.'; return false; }

        $lineNo = 0;
        while (($data = fgetcsv($fh, 0, $delim, '"', '\\')) !== false) {
            if ($lineNo > self::MAX_ROWS) break;
            if ($data === [null] || $data === [false] || $data === null) continue;
            // skip totally empty lines
            if (count($data) === 1 && trim((string)$data[0]) === '') continue;
            if ($lineNo === 0) {
                $this->headers = array_map(fn($h) => trim((string)$h), $data);
            } else {
                $this->rows[] = array_map(fn($v) => is_string($v) ? trim($v) : $v, $data);
            }
            $lineNo++;
        }
        fclose($fh);
        return $this->finish();
    }

    // ── XLSX — native zip + xml parse of the first sheet ───────
    private function readXlsx(string $path): bool
    {
        if (!class_exists(ZipArchive::class)) {
            $this->error = 'ZIP support is missing in PHP. Please use CSV instead.';
            return false;
        }
        $zip = new ZipArchive();
        if ($zip->open($path) !== TRUE) { $this->error = 'Cannot open the Excel file.'; return false; }

        // shared strings
        $shared = [];
        $ss = $zip->getFromName('xl/sharedStrings.xml');
        if ($ss !== false) {
            $xml = new SimpleXMLElement($ss);
            foreach ($xml->si as $si) {
                $text = '';
                if (isset($si->t)) {
                    $text = (string)$si->t;
                } else {
                    foreach ($si->r as $r) $text .= (string)$r->t;
                }
                $shared[] = $text;
            }
        }

        // first worksheet (workbook order) — sheet1.xml covers the common case
        $sheetName = 'xl/worksheets/sheet1.xml';
        if (!$zip->locateName($sheetName)) {
            foreach (['xl/worksheets/sheet2.xml', 'xl/worksheets/sheet3.xml'] as $fallback) {
                if ($zip->locateName($fallback)) { $sheetName = $fallback; break; }
            }
        }
        $sheetXml = $zip->getFromName($sheetName);
        $zip->close();
        if ($sheetXml === false) { $this->error = 'The Excel file has no readable worksheet.'; return false; }

        $xml = new SimpleXMLElement($sheetXml);
        $cols = 0;
        $lineNo = 0;
        foreach ($xml->sheetData->row as $row) {
            if ($lineNo > self::MAX_ROWS) break;
            $cells = [];
            $maxIdx = -1;
            foreach ($row->c as $c) {
                $ref = (string)$c['r'];
                $idx = $this->colIndexFromRef($ref);
                $type = (string)$c['t'];
                $val = '';
                if ($type === 'inlineStr' && isset($c->is->t)) {
                    $val = (string)$c->is->t;
                } elseif (isset($c->v)) {
                    $val = (string)$c->v;
                    if ($type === 's') {
                        $val = $shared[(int)$val] ?? '';
                    }
                }
                if ($val !== '') { $cells[$idx] = $val; $maxIdx = max($maxIdx, $idx); }
            }
            if ($maxIdx >= 0) $cols = max($cols, $maxIdx + 1);

            // build a dense row
            $r = array_fill(0, $cols, '');
            foreach ($cells as $idx => $v) { $r[$idx] = $v; }

            if ($lineNo === 0) {
                $this->headers = array_map(fn($h) => trim((string)$h), $r);
            } else {
                if (count(array_filter($r, fn($v) => trim((string)$v) !== '')) === 0) continue;
                $this->rows[] = array_map(fn($v) => trim((string)$v), $r);
            }
            $lineNo++;
        }

        return $this->finish();
    }

    private function colIndexFromRef(string $ref): int
    {
        $idx = 0;
        for ($i = 0, $n = strlen($ref); $i < $n; $i++) {
            $ch = $ref[$i];
            if ($ch >= 'A' && $ch <= 'Z') {
                $idx = $idx * 26 + (ord($ch) - 64) - 1;
            } else break;
        }
        return $idx;
    }

    private function finish(): bool
    {
        // drop empty header columns from the END
        while (!empty($this->headers) && trim(end($this->headers)) === '') array_pop($this->headers);
        $n = count($this->headers);
        foreach ($this->rows as &$r) $r = array_slice($r, 0, $n);
        unset($r);

        if ($n === 0) { $this->error = 'No header row found in the first row of the file.'; return false; }
        if (empty($this->rows)) { $this->error = 'No data rows found below the header row.'; return false; }
        return true;
    }

    public function headers(): array { return $this->headers; }
    public function rows(): array    { return $this->rows; }
}
