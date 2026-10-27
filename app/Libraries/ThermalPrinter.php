<?php
namespace App\Libraries;

/**
 * ThermalPrinter Library
 * Supports ESC/POS protocol over TCP/IP (network printers)
 * Compatible with: Epson, TVS, Posiflex, Star, Bixolon, Generic thermal
 * Paper sizes: 80mm, 57mm
 */
class ThermalPrinter
{
    private $branch;
    private $printerIp;
    private $printerPort;
    private $printerType;
    private $paperWidth;

    // ESC/POS Commands
    const ESC = "\x1B";
    const GS  = "\x1D";
    const LF  = "\x0A";
    const CR  = "\x0D";
    const FF  = "\x0C";
    const NUL = "\x00";

    // Initialize
    const INIT         = "\x1B\x40";         // ESC @ - Initialize printer
    const CUT_PAPER    = "\x1D\x56\x41\x03"; // GS V A - Partial cut
    const FEED_LINES   = "\x1B\x64";          // ESC d - Feed n lines
    const BOLD_ON      = "\x1B\x45\x01";
    const BOLD_OFF     = "\x1B\x45\x00";
    const UNDERLINE_ON = "\x1B\x2D\x01";
    const UNDERLINE_OFF= "\x1B\x2D\x00";
    const ALIGN_LEFT   = "\x1B\x61\x00";
    const ALIGN_CENTER = "\x1B\x61\x01";
    const ALIGN_RIGHT  = "\x1B\x61\x02";
    const FONT_NORMAL  = "\x1B\x21\x00";
    const FONT_DOUBLE  = "\x1B\x21\x30";     // Double width+height
    const FONT_WIDE    = "\x1B\x21\x20";     // Double width only
    const FONT_TALL    = "\x1B\x21\x10";     // Double height only

    public function __construct($branch)
    {
        $this->branch      = $branch;
        $this->printerIp   = $branch['printer_ip']   ?? '192.168.1.100';
        $this->printerPort = $branch['printer_port']  ?? 9100;
        $this->printerType = $branch['printer_type']  ?? 'thermal_80mm';
        $this->paperWidth  = ($this->printerType === 'thermal_57mm') ? 32 : 48;
    }

    // ----------------------------------------------------------------
    // Print Bill / Invoice
    // ----------------------------------------------------------------
    public function printBill($order, $restaurant, $branch)
    {
        return $this->send($this->buildBill($order, $restaurant, $branch));
    }

    /**
     * Build the raw ESC/POS byte document for a bill WITHOUT sending it —
     * this is what the mobile app fetches for USB / Bluetooth (RawBT)
     * printing, and it guarantees the in-app receipt is byte-identical to
     * the network-printed one.
     */
    public function buildBill($order, $restaurant, $branch): string
    {
        if (!$order) {
            return '';
        }
        $data = self::INIT;
        $data .= self::ALIGN_CENTER;

        // Logo text (if no image printer)
        if ($restaurant['receipt_show_logo'] ?? true) {
            $data .= self::FONT_DOUBLE . self::BOLD_ON;
            $data .= $this->centerText(strtoupper($restaurant['name'])) . self::LF;
            $data .= self::FONT_NORMAL . self::BOLD_OFF;
        }

        // Branch name
        $data .= self::BOLD_ON . $this->centerText($branch['name']) . self::LF . self::BOLD_OFF;

        // Address
        if ($branch['address']) {
            $wrapped = $this->wordWrap($branch['address'], $this->paperWidth);
            foreach ($wrapped as $line) $data .= $this->centerText($line) . self::LF;
        }
        if ($branch['phone']) $data .= $this->centerText('Ph: ' . $branch['phone']) . self::LF;
        if ($restaurant['gst_number']) $data .= $this->centerText('GSTIN: ' . $restaurant['gst_number']) . self::LF;

        // Custom header
        if ($restaurant['receipt_header'] ?? null) {
            $data .= self::LF;
            foreach (explode("\n", $restaurant['receipt_header']) as $line) {
                $data .= $this->centerText(trim($line)) . self::LF;
            }
        }

        $data .= self::ALIGN_LEFT;
        $data .= $this->dashedLine() . self::LF;

        // Invoice details
        $invoice = $this->getInvoice($order['id']);
        $data .= $this->twoColumns('Invoice#', $invoice['invoice_number'] ?? $order['order_number']) . self::LF;
        $data .= $this->twoColumns('Date', date('d-m-Y H:i', strtotime($order['created_at']))) . self::LF;
        $data .= $this->twoColumns('Type', strtoupper(str_replace('_',' ',$order['order_type']))) . self::LF;

        if ($order['table']) $data .= $this->twoColumns('Table', $order['table']['table_number']) . self::LF;
        if ($order['customer_name']) $data .= $this->twoColumns('Customer', $order['customer_name']) . self::LF;
        if ($order['customer_phone']) $data .= $this->twoColumns('Phone', $order['customer_phone']) . self::LF;

        $data .= $this->dashedLine() . self::LF;

        // Header row
        $data .= self::BOLD_ON;
        $data .= $this->itemLine('Item', 'Qty', 'Rate', 'Amt') . self::LF;
        $data .= self::BOLD_OFF;
        $data .= $this->dashedLine() . self::LF;

        // Items
        foreach ($order['items'] as $item) {
            $data .= $this->itemLine(
                $item['name'],
                $item['quantity'],
                number_format($item['unit_price'], 2),
                number_format($item['total_price'], 2)
            ) . self::LF;

            // Addons
            foreach ($item['addons'] ?? [] as $addon) {
                $data .= '  + ' . $addon['name'];
                if ($addon['price'] > 0) $data .= ' (' . number_format($addon['price'],2) . ')';
                $data .= self::LF;
            }

            // Notes
            if ($item['notes']) $data .= '  * ' . $item['notes'] . self::LF;
        }

        $data .= $this->dashedLine() . self::LF;

        // Totals
        $sym = $restaurant['currency_symbol'] ?: '₹';
        $data .= $this->twoColumns('Subtotal', $sym . number_format($order['subtotal'], 2)) . self::LF;

        if ($order['discount_amount'] > 0) {
            $data .= $this->twoColumns('Discount', '-' . $sym . number_format($order['discount_amount'], 2)) . self::LF;
        }

        if (($order['igst_amount'] ?? 0) > 0) {
            $data .= $this->twoColumns('IGST', $sym . number_format($order['igst_amount'], 2)) . self::LF;
        } elseif ($order['cgst_amount'] > 0) {
            $data .= $this->twoColumns('CGST', $sym . number_format($order['cgst_amount'], 2)) . self::LF;
            $data .= $this->twoColumns('SGST', $sym . number_format($order['sgst_amount'], 2)) . self::LF;
        } elseif ($order['tax_amount'] > 0) {
            $data .= $this->twoColumns('Tax', $sym . number_format($order['tax_amount'], 2)) . self::LF;
        }

        if (($order['cess_amount'] ?? 0) > 0) {
            $data .= $this->twoColumns('Cess', $sym . number_format($order['cess_amount'], 2)) . self::LF;
        }
        if (($order['packing_charge'] ?? 0) > 0) {
            $data .= $this->twoColumns('Packing', $sym . number_format($order['packing_charge'], 2)) . self::LF;
        }

        if ($order['service_charge'] > 0) {
            $data .= $this->twoColumns('Service Charge', $sym . number_format($order['service_charge'], 2)) . self::LF;
        }
        if ($order['delivery_charge'] > 0) {
            $data .= $this->twoColumns('Delivery', $sym . number_format($order['delivery_charge'], 2)) . self::LF;
        }
        if ($order['round_off'] != 0) {
            $data .= $this->twoColumns('Round Off', $sym . number_format($order['round_off'], 2)) . self::LF;
        }

        $data .= $this->dashedLine() . self::LF;

        // Grand Total
        $data .= self::BOLD_ON . self::FONT_WIDE;
        $data .= $this->twoColumns('TOTAL', $sym . number_format($order['total_amount'], 2)) . self::LF;
        $data .= self::FONT_NORMAL . self::BOLD_OFF;
        $data .= $this->dashedLine() . self::LF;

        // Payment details
        $data .= self::BOLD_ON . 'Payment Details:' . self::LF . self::BOLD_OFF;
        foreach ($order['payments'] as $pmt) {
            $data .= $this->twoColumns(
                '  ' . ucfirst(str_replace('_',' ',$pmt['payment_method'])),
                $sym . number_format($pmt['amount'], 2)
            ) . self::LF;
            if ($pmt['payment_reference']) $data .= '  Ref: ' . $pmt['payment_reference'] . self::LF;
        }

        if ($order['paid_amount'] > $order['total_amount']) {
            $change = $order['paid_amount'] - $order['total_amount'];
            $data .= $this->twoColumns('Change', $sym . number_format($change, 2)) . self::LF;
        }

        // Footer
        $data .= $this->dashedLine() . self::LF;
        $data .= self::ALIGN_CENTER;

        if ($restaurant['receipt_footer'] ?? null) {
            foreach (explode("\n", $restaurant['receipt_footer']) as $line) {
                $data .= $this->centerText(trim($line)) . self::LF;
            }
        } else {
            $data .= $this->centerText('Thank you! Visit again!') . self::LF;
            $data .= $this->centerText('Powered by DinoviX') . self::LF;
        }

        // Feedback QR — customer scans to rate without any printed link
        if (!empty($order['feedback_token'])) {
            $feedbackUrl = base_url('feedback/' . $order['feedback_token']);
            $data .= $this->dashedLine() . self::LF;
            $data .= self::ALIGN_CENTER;
            $data .= self::BOLD_ON . 'STAR Rate Your Experience' . self::LF . self::BOLD_OFF;
            $data .= 'Scan the QR - takes 30 seconds' . self::LF;
            $data .= $this->escPosQr($feedbackUrl, 6);
            $data .= self::LF;
        }

        $data .= self::LF . self::LF . self::LF;
        $data .= self::CUT_PAPER;

        return $data;
    }

    /**
     * Build an ESC/POS QR code command block (printer generates the QR itself).
     * Uses QR Model 2, module size 4-8, ECC level M — supported by practically
     * all Epson-compliant thermal printers.
     */
    private function escPosQr($data, $moduleSize = 6)
    {
        // 1: Select QR model 2  —  GS ( k pL pH cn fn n1 n2
        $cmd  = "\x1D\x28\x6B\x04\x00\x31\x41\x32\x00";
        // 2: Select module size  —  GS ( k pL pH cn fn n
        $cmd .= "\x1D\x28\x6B\x03\x00\x31\x43" . chr(max(1, min(8, $moduleSize)));
        // 3: Select error correction level M  —  GS ( k pL pH cn fn n
        $cmd .= "\x1D\x28\x6B\x03\x00\x31\x45\x31";
        // 4: Store data  —  GS ( k pL pH cn fm m d1..dk  (pL,pH = len(data)+3)
        $len  = strlen($data) + 3;
        $cmd .= "\x1D\x28\x6B" . chr($len % 256) . chr(intdiv($len, 256)) . "\x31\x50\x30" . $data;
        // 5: Print  —  GS ( k pL pH cn fn m
        $cmd .= "\x1D\x28\x6B\x03\x00\x31\x51\x30";
        $cmd .= self::LF;
        return $cmd;
    }

    // ----------------------------------------------------------------
    // Print KOT
    // ----------------------------------------------------------------
    public function printKOT($order, $kotNumber)
    {
        // KOT printer uses a separate IP if configured
        $kotIp   = $this->branch['kot_printer_ip']   ?: $this->printerIp;
        $kotPort = $this->branch['kot_printer_port']  ?: $this->printerPort;

        return $this->sendTo($kotIp, $kotPort, $this->buildKOT($order, $kotNumber));
    }

    /**
     * Raw ESC/POS bytes for a KOT — the app-print path reuses the LAST
     * kot_number recorded for the order so reprints never burn a new number.
     */
    public function buildKOT($order, $kotNumber): string
    {
        $data  = self::INIT;
        $data .= self::ALIGN_CENTER;
        $data .= self::FONT_DOUBLE . self::BOLD_ON;
        $data .= '-- KOT --' . self::LF;
        $data .= self::FONT_NORMAL . self::BOLD_OFF;

        $data .= self::BOLD_ON . 'KOT#: ' . $kotNumber . self::LF . self::BOLD_OFF;
        $data .= 'Time: ' . date('H:i:s') . self::LF;
        $data .= self::ALIGN_LEFT;
        $data .= $this->dashedLine() . self::LF;

        $data .= self::BOLD_ON;
        $data .= $this->twoColumns('Type', strtoupper(str_replace('_',' ',$order['order_type']))) . self::LF;
        if ($order['table']) $data .= $this->twoColumns('Table', $order['table']['table_number']) . self::LF;
        if ($order['no_of_guests']) $data .= $this->twoColumns('Guests', $order['no_of_guests']) . self::LF;
        $data .= self::BOLD_OFF;
        $data .= $this->dashedLine() . self::LF;

        foreach ($order['items'] as $item) {
            if (in_array($item['status'], ['cancelled'])) continue;

            $data .= self::FONT_WIDE;
            $data .= $item['quantity'] . 'x  ' . $item['name'] . self::LF;
            $data .= self::FONT_NORMAL;

            if ($item['variant_name']) $data .= '   [' . $item['variant_name'] . ']' . self::LF;

            foreach ($item['addons'] ?? [] as $addon) {
                $data .= '   + ' . $addon['name'] . self::LF;
            }

            if ($item['notes']) {
                $data .= self::BOLD_ON . '   * ' . $item['notes'] . self::LF . self::BOLD_OFF;
            }
        }

        $data .= $this->dashedLine() . self::LF;

        if ($order['kitchen_notes']) {
            $data .= self::BOLD_ON . 'Chef Note: ' . self::BOLD_OFF . $order['kitchen_notes'] . self::LF;
            $data .= $this->dashedLine() . self::LF;
        }

        $data .= self::LF . self::LF . self::LF;
        $data .= self::CUT_PAPER;

        return $data;
    }

    // ----------------------------------------------------------------
    // Send data to printer via TCP/IP socket
    // ----------------------------------------------------------------
    private function send($data)
    {
        return $this->sendTo($this->printerIp, $this->printerPort, $data);
    }

    /**
     * Print a small test ticket to a printer (used by Branch Settings
     * "Test Print" to validate IP/port wiring).
     */
    public function printTest(array $printer, string $branchName = 'DinoviX')
    {
        $data  = self::INIT;
        $data .= self::ALIGN_CENTER;
        $data .= self::BOLD_ON . 'Printer Test' . self::LF . self::BOLD_OFF;
        $data .= $branchName . self::LF;
        $data .= date('d M Y, h:i A') . self::LF;
        $data .= $this->dashedLine() . self::LF;
        $data .= self::ALIGN_LEFT;
        $data .= 'If you can read this, POS printing works.' . self::LF;
        $data .= self::LF . self::LF . self::CUT_PAPER;

        return $this->sendTo($printer['printer_ip'], $printer['printer_port'], $data);
    }

    private function sendTo($ip, $port, $data, $attempts = 2)
    {
        if (empty($ip)) {
            $dir = WRITEPATH . 'uploads/receipts/';
            if (!is_dir($dir)) {
                mkdir($dir, 0777, true);
            }
            $path = $dir . 'receipt_' . time() . '.bin';
            if (file_put_contents($path, $data) === false) {
                log_message('error', "Printer file fallback failed: could not write $path");
                return ['success' => false, 'error' => 'No printer IP configured and fallback receipt file could not be saved'];
            }
            return [
                'success' => true,
                'method'  => 'file',
                'path'    => $path
            ];
        }

        if (!filter_var($ip, FILTER_VALIDATE_IP) && !filter_var($ip, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME)) {
            return ['success' => false, 'error' => "Printer IP '$ip' is not a valid IP address or hostname — check Branch Settings"];
        }

        // Retry: transient network blips are the #1 cause of "print IP fail"
        $errno  = 0;
        $errstr = '';
        $socket = null;
        for ($attempt = 1; $attempt <= $attempts; $attempt++) {
            $socket = @fsockopen($ip, $port, $errno, $errstr, 5);
            if ($socket) break;
            if ($attempt < $attempts) usleep(300000); // brief pause before retry
        }

        if (!$socket) {
            log_message('error', "Printer connection failed: $errstr ($errno) to $ip:$port");
            return [
                'success' => false,
                'error'   => "Cannot connect to printer at $ip:$port — {$this->friendlySocketError($errno, $errstr)}"
            ];
        }

        // Stream must not hang forever if the printer stalls mid-job
        stream_set_timeout($socket, 10);

        // Write ALL bytes — fwrite() may partial-write, which used to be
        // silently treated as "printed!"
        $written = 0;
        $total   = strlen($data);
        while ($written < $total) {
            $n = @fwrite($socket, substr($data, $written));
            if ($n === false || $n <= 0) {
                $meta = stream_get_meta_data($socket);
                fclose($socket);
                $why = !empty($meta['timed_out']) ? 'printer stopped accepting data (timed out)' : 'printer closed the connection';
                log_message('error', "Printer write failed to $ip:$port after $written/$total bytes ($why)");
                return [
                    'success' => false,
                    'error'   => "Print job failed at $ip:$port — only $written of $total bytes sent ($why). Check paper and cable/connection."
                ];
            }
            $written += $n;
        }

        fflush($socket);
        fclose($socket);

        log_message('info', "Printer job sent: $written bytes to $ip:$port with " . strlen($data) . " bytes payload");

        return ['success' => true, 'method' => 'network', 'bytes' => $written];
    }

    /**
     * Human-readable explanation for common socket failures so the POS
     * cashier can actually act on the message instead of "print failed".
     */
    private function friendlySocketError($errno, $errstr)
    {
        switch ((int)$errno) {
            case 111:
                return 'connection refused. The printer is reachable but nothing is listening on that port — check the printer port (usually 9100) and that the printer is powered on.';
            case 110:
            case 10060:
                return 'connection timed out. The printer is off, unplugged, on a different network/subnet, or the IP is wrong.';
            case 104:
                return 'connection reset by the printer. The printer rejected the job — power-cycle it and retry.';
            case 113:
                return 'host not found. The IP address in Branch Settings is invalid for this network.';
            default:
                return trim($errstr) . " (code $errno). Check that the printer is powered on, on the same network, and its IP is set in Branch Settings.";
        }
    }

    // ----------------------------------------------------------------
    // Helpers
    // ----------------------------------------------------------------
    private function dashedLine()
    {
        return str_repeat('-', $this->paperWidth);
    }

    private function centerText($text)
    {
        $len = strlen($text);
        if ($len >= $this->paperWidth) return $text;
        $pad = (int)(($this->paperWidth - $len) / 2);
        return str_repeat(' ', $pad) . $text;
    }

    private function twoColumns($left, $right)
    {
        $right = (string)$right;
        $left  = (string)$left;
        $rightLen = strlen($right);
        $leftMax  = $this->paperWidth - $rightLen - 1;
        if (strlen($left) > $leftMax) $left = substr($left, 0, $leftMax - 2) . '..';
        $spaces = $this->paperWidth - strlen($left) - $rightLen;
        return $left . str_repeat(' ', max(1, $spaces)) . $right;
    }

    private function itemLine($name, $qty, $rate, $amt)
    {
        // Format: Name[...] Qty  Rate   Amt
        $amt  = (string)$amt;
        $rate = (string)$rate;
        $qty  = (string)$qty;
        $nameWidth = $this->paperWidth - 15;
        $name = strlen($name) > $nameWidth ? substr($name, 0, $nameWidth - 2) . '..' : str_pad($name, $nameWidth);
        return $name . str_pad($qty, 4, ' ', STR_PAD_LEFT) . str_pad($rate, 6, ' ', STR_PAD_LEFT) . str_pad($amt, 7, ' ', STR_PAD_LEFT);
    }

    private function wordWrap($text, $width)
    {
        return explode("\n", wordwrap($text, $width, "\n", true));
    }

    private function getInvoice($orderId)
    {
        $db = \Config\Database::connect();
        return $db->table('invoices')->where('order_id', $orderId)->get()->getRowArray();
    }
}
