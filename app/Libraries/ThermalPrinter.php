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
        $data = '';
        $data .= self::INIT;
        $data .= self::ALIGN_CENTER;

        // Logo text (if no image printer)
        if ($restaurant['receipt_show_logo']) {
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
        if ($restaurant['receipt_header']) {
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

        if ($order['cgst_amount'] > 0) {
            $data .= $this->twoColumns('CGST', $sym . number_format($order['cgst_amount'], 2)) . self::LF;
            $data .= $this->twoColumns('SGST', $sym . number_format($order['sgst_amount'], 2)) . self::LF;
        } elseif ($order['tax_amount'] > 0) {
            $data .= $this->twoColumns('Tax', $sym . number_format($order['tax_amount'], 2)) . self::LF;
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

        if ($restaurant['receipt_footer']) {
            foreach (explode("\n", $restaurant['receipt_footer']) as $line) {
                $data .= $this->centerText(trim($line)) . self::LF;
            }
        } else {
            $data .= $this->centerText('Thank you! Visit again!') . self::LF;
            $data .= $this->centerText('Powered by RestoCRM') . self::LF;
        }

        // QR code placeholder for UPI payment
        $data .= self::LF . self::LF . self::LF;
        $data .= self::CUT_PAPER;

        return $this->send($data);
    }

    // ----------------------------------------------------------------
    // Print KOT
    // ----------------------------------------------------------------
    public function printKOT($order, $kotNumber)
    {
        // KOT printer uses a separate IP if configured
        $kotIp   = $this->branch['kot_printer_ip']   ?: $this->printerIp;
        $kotPort = $this->branch['kot_printer_port']  ?: $this->printerPort;

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

        return $this->sendTo($kotIp, $kotPort, $data);
    }

    // ----------------------------------------------------------------
    // Send data to printer via TCP/IP socket
    // ----------------------------------------------------------------
    private function send($data)
    {
        return $this->sendTo($this->printerIp, $this->printerPort, $data);
    }

    private function sendTo($ip, $port, $data)
    {
        if (empty($ip)) {
            // Save to file for web-based printing (fallback)
            $path = WRITEPATH . 'uploads/receipts/receipt_' . time() . '.bin';
            file_put_contents($path, $data);
            return ['success' => true, 'method' => 'file', 'path' => $path];
        }

        $socket = @fsockopen($ip, $port, $errno, $errstr, 3);
        if (!$socket) {
            log_message('error', "Printer connection failed: $errstr ($errno) to $ip:$port");
            return ['success' => false, 'error' => "Cannot connect to printer at $ip:$port - $errstr"];
        }

        fwrite($socket, $data);
        fclose($socket);

        return ['success' => true, 'method' => 'network'];
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
