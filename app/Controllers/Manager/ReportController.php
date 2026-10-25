<?php
namespace App\Controllers\Manager;
use App\Controllers\BaseController;

class ReportController extends BaseController
{
    private function baseData(string $title): array
    {
        return [
            'pageTitle'      => $title,
            'userName'       => session('user_name'),
            'userRole'       => session('role_slug'),
            'restaurantName' => session('restaurant_name'),
        ];
    }

    // Plan gate: everything beyond the basic sales report is a paid feature
    private function planLocked(): string
    {
        return view('admin/reports/plan_locked', array_merge($this->baseData('Plan Upgrade Required'), [
            'planName' => session('plan_name') ?: 'Starter',
        ]));
    }

    public function index()   { return $this->sales(); }

    public function sales()
    {
        $db  = \Config\Database::connect();
        $rid = session('restaurant_id');
        $bid = session('branch_id');
        $from = $this->request->getGet('from') ?? date('Y-m-01');
        $to   = $this->request->getGet('to')   ?? date('Y-m-d');

        $q = $db->table('orders')
            ->where('restaurant_id', $rid)
            ->where('DATE(created_at) >=', $from)
            ->where('DATE(created_at) <=', $to)
            ->where('status !=', 'cancelled');
        if ($bid) $q->where('branch_id', $bid);

        $summary = (clone $q)
            ->select('COUNT(*) as total_orders, SUM(total_amount) as total_revenue, SUM(discount_amount) as total_discount, SUM(tax_amount) as total_tax, AVG(total_amount) as avg_order')
            ->get()->getRowArray();

        $daily = (clone $q)
            ->select('DATE(created_at) as date, COUNT(*) as orders, SUM(total_amount) as revenue')
            ->groupBy('DATE(created_at)')
            ->orderBy('date','ASC')
            ->get()->getResultArray();

        $byType = (clone $q)
            ->select('order_type, COUNT(*) as orders, SUM(total_amount) as revenue')
            ->groupBy('order_type')
            ->get()->getResultArray();

        $branches = $db->table('branches')->where('restaurant_id',$rid)->get()->getResultArray();

        return view('admin/reports/sales', array_merge($this->baseData('Sales Report'), [
            'summary' => $summary, 'daily' => $daily, 'byType' => $byType,
            'from' => $from, 'to' => $to, 'branches' => $branches,
        ]));
    }

    public function items()
    {
        if (!$this->getPlanFeature('allow_reports_advanced')) return $this->planLocked();
        $db  = \Config\Database::connect();
        $rid = session('restaurant_id');
        $from = $this->request->getGet('from') ?? date('Y-m-01');
        $to   = $this->request->getGet('to')   ?? date('Y-m-d');

        $items = $db->table('order_items oi')
            ->select('oi.menu_item_id, ANY_VALUE(oi.name) as name, ANY_VALUE(mc.name) as category,
                      SUM(oi.quantity) as total_qty, SUM(oi.total_price) as total_revenue')
            ->join('orders o','o.id = oi.order_id')
            ->join('menu_items mi','mi.id = oi.menu_item_id','left')
            ->join('menu_categories mc','mc.id = mi.category_id','left')
            ->where('o.restaurant_id', $rid)
            ->where('DATE(o.created_at) >=', $from)
            ->where('DATE(o.created_at) <=', $to)
            ->where('o.status !=','cancelled')
            ->groupBy('oi.menu_item_id')
            ->orderBy('total_qty','DESC')
            ->get()->getResultArray();

        return view('admin/reports/items', array_merge($this->baseData('Item-wise Report'), [
            'items' => $items, 'from' => $from, 'to' => $to,
        ]));
    }

    public function payments()
    {
        if (!$this->getPlanFeature('allow_reports_advanced')) return $this->planLocked();
        $db  = \Config\Database::connect();
        $rid = session('restaurant_id');
        $from = $this->request->getGet('from') ?? date('Y-m-01');
        $to   = $this->request->getGet('to')   ?? date('Y-m-d');

        $payments = $db->table('payments p')
            ->select('p.payment_method, COUNT(*) as count, SUM(p.amount) as total')
            ->join('orders o','o.id = p.order_id')
            ->where('o.restaurant_id', $rid)
            // cancelled/refunded orders must not count as revenue
            ->whereNotIn('o.status', ['cancelled','refunded'])
            ->where('DATE(p.created_at) >=', $from)
            ->where('DATE(p.created_at) <=', $to)
            ->groupBy('p.payment_method')
            ->orderBy('total','DESC')
            ->get()->getResultArray();

        $daily = $db->table('payments p')
            ->select('DATE(p.created_at) as date, SUM(p.amount) as total')
            ->join('orders o','o.id = p.order_id')
            ->where('o.restaurant_id', $rid)
            ->whereNotIn('o.status', ['cancelled','refunded'])
            ->where('DATE(p.created_at) >=', $from)
            ->where('DATE(p.created_at) <=', $to)
            ->groupBy('DATE(p.created_at)')
            ->orderBy('date','ASC')
            ->get()->getResultArray();

        return view('admin/reports/payments', array_merge($this->baseData('Payment Report'), [
            'payments' => $payments, 'daily' => $daily, 'from' => $from, 'to' => $to,
        ]));
    }

    public function expenses()
    {
        $db  = \Config\Database::connect();
        $rid = session('restaurant_id');
        $from = $this->request->getGet('from') ?? date('Y-m-01');
        $to   = $this->request->getGet('to')   ?? date('Y-m-d');

        $expenses = $db->table('expenses e')
            ->select('e.*, ec.name as category_name, u.name as staff_name')
            ->join('expense_categories ec','ec.id = e.category_id','left')
            ->join('users u','u.id = e.user_id','left')
            ->where('e.restaurant_id', $rid)
            ->where('DATE(e.expense_date) >=', $from)
            ->where('DATE(e.expense_date) <=', $to)
            ->orderBy('e.expense_date','DESC')
            ->get()->getResultArray();

        $total = array_sum(array_column($expenses,'amount'));

        return view('admin/reports/expenses', array_merge($this->baseData('Expense Report'), [
            'expenses' => $expenses, 'total' => $total, 'from' => $from, 'to' => $to,
        ]));
    }

    /**
     * Incomplete Orders report — everything that never completed properly:
     * unpaid open orders, cancelled orders (with reasons) and refunds.
     */
    public function incomplete()
    {
        if (!$this->getPlanFeature('allow_reports_advanced')) return $this->planLocked();
        $db   = \Config\Database::connect();
        $rid  = session('restaurant_id');
        $bid  = session('branch_id');
        $from = $this->request->getGet('from') ?? date('Y-m-01');
        $to   = $this->request->getGet('to')   ?? date('Y-m-d');

        $orders = $db->table('orders o')
            ->select('o.id, o.order_number, o.status, o.order_type, o.payment_status,
                      o.total_amount, o.cancelled_reason, o.table_id, o.created_at,
                      t.table_number, u.name as staff_name')
            ->join('tables t', 't.id = o.table_id', 'left')
            ->join('users u', 'u.id = o.user_id', 'left')
            ->where('o.restaurant_id', $rid)
            ->where('o.branch_id', $bid)
            ->whereNotIn('o.status', ['completed'])
            ->groupStart()
                ->where('DATE(o.created_at) >=', $from)
                ->where('DATE(o.created_at) <=', $to)
            ->groupEnd()
            ->orderBy('o.created_at','DESC')
            ->limit(300)
            ->get()->getResultArray();

        $unpaid    = array_filter($orders, fn($o) => $o['payment_status'] !== 'paid' && !in_array($o['status'], ['cancelled','refunded'], true));
        $cancelled = array_filter($orders, fn($o) => $o['status'] === 'cancelled');
        $refunded  = array_filter($orders, fn($o) => $o['status'] === 'refunded');

        $stats = [
            'unpaid_count'    => count($unpaid),
            'unpaid_amount'   => array_sum(array_column($unpaid, 'total_amount')),
            'cancelled_count' => count($cancelled),
            'cancelled_amount'=> array_sum(array_column($cancelled, 'total_amount')),
            'refunded_count'  => count($refunded),
            'refunded_amount' => array_sum(array_column($refunded, 'total_amount')),
        ];

        return view('admin/reports/incomplete', array_merge($this->baseData('Incomplete Orders Report'), [
            'orders' => $orders, 'stats' => $stats, 'from' => $from, 'to' => $to,
        ]));
    }

    // ════════════════════════════════════════════════════════════════
    // Exports (CSV), print-HTML (PDF via the browser), GSTR-1 and the
    // consolidated multi-branch report
    // ════════════════════════════════════════════════════════════════

    private function reportRows(string $type, string $from, string $to, ?string &$title, ?array &$head): ?array
    {
        $db  = \Config\Database::connect();
        $rid = session('restaurant_id');

        switch ($type) {
            case 'sales':
                $q = $db->table('orders')->where('restaurant_id', $rid)
                    ->where('DATE(created_at) >=', $from)->where('DATE(created_at) <=', $to)
                    ->where('status !=', 'cancelled');
                if (session('branch_id')) $q->where('branch_id', session('branch_id'));
                $title = 'Sales Report';
                $head  = ['Date', 'Orders', 'Revenue', 'Tax', 'Discount'];
                return (clone $q)->select("DATE(created_at) as d, COUNT(*) as orders, SUM(total_amount) as revenue, SUM(tax_amount) as tax, SUM(discount_amount) as discount")
                    ->groupBy('DATE(created_at)')->orderBy('d','ASC')->get()->getResultArray();

            case 'items':
                $title = 'Item-wise Report';
                $head  = ['Item', 'Category', 'Qty Sold', 'Revenue'];
                return $db->table('order_items oi')
                    ->select("ANY_VALUE(oi.name) as name, ANY_VALUE(mc.name) as category, SUM(oi.quantity) as qty, SUM(oi.total_price) as revenue")
                    ->join('orders o','o.id = oi.order_id')
                    ->join('menu_items mi','mi.id = oi.menu_item_id','left')
                    ->join('menu_categories mc','mc.id = mi.category_id','left')
                    ->where('o.restaurant_id', $rid)
                    ->where('DATE(o.created_at) >=', $from)->where('DATE(o.created_at) <=', $to)
                    ->where('o.status !=','cancelled')
                    ->groupBy('oi.menu_item_id')->orderBy('qty','DESC')->get()->getResultArray();

            case 'payments':
                $title = 'Payment Report';
                $head  = ['Date', 'Method', 'Transactions', 'Amount'];
                return $db->table('payments p')
                    ->select("DATE(p.created_at) as d, p.payment_method as method, COUNT(*) as count, SUM(p.amount) as amount")
                    ->join('orders o','o.id = p.order_id')
                    ->where('o.restaurant_id', $rid)
                    ->whereNotIn('o.status', ['cancelled','refunded'])
                    ->where('DATE(p.created_at) >=', $from)->where('DATE(p.created_at) <=', $to)
                    ->groupBy("DATE(p.created_at), p.payment_method")
                    ->orderBy('d','ASC')->get()->getResultArray();

            case 'expenses':
                $title = 'Expense Report';
                $head  = ['Date', 'Category', 'Note', 'Staff', 'Amount'];
                return $db->table('expenses e')
                    ->select("e.expense_date as d, ec.name as category, e.notes, u.name as staff, e.amount")
                    ->join('expense_categories ec','ec.id = e.category_id','left')
                    ->join('users u','u.id = e.user_id','left')
                    ->where('e.restaurant_id', $rid)
                    ->where('DATE(e.expense_date) >=', $from)->where('DATE(e.expense_date) <=', $to)
                    ->orderBy('e.expense_date','ASC')->get()->getResultArray();

            case 'consolidated':
                $title = 'Consolidated Branch Report';
                $head  = ['Branch', 'Orders', 'Revenue', 'Discounts', 'Tax', 'Avg Order'];
                return $db->table('orders o')
                    ->select("b.name as branch, COUNT(*) as orders, SUM(o.total_amount) as revenue,
                              SUM(o.discount_amount) as discount, SUM(o.tax_amount) as tax,
                              ROUND(AVG(o.total_amount),2) as avg_order")
                    ->join('branches b','b.id = o.branch_id','left')
                    ->where('o.restaurant_id', $rid)
                    ->where('DATE(o.created_at) >=', $from)->where('DATE(o.created_at) <=', $to)
                    ->where('o.status !=','cancelled')
                    ->groupBy('o.branch_id, b.name')->orderBy('revenue','DESC')->get()->getResultArray();

            case 'gstr1':
                $title = 'GSTR-1 Outward Supply Summary';
                $head  = ['Rate', 'Taxable Value', 'CGST', 'SGST', 'IGST', 'Cess'];
                return $db->table('order_items oi')
                    ->select("oi.tax_percent as rate, SUM(oi.total_price - oi.tax_amount) as taxable,
                              ROUND(SUM(oi.tax_amount / 2),2) as cgst, ROUND(SUM(oi.tax_amount / 2),2) as sgst,
                              0 as igst, 0 as cess")
                    ->join('orders o','o.id = oi.order_id')
                    ->where('o.restaurant_id', $rid)
                    ->whereIn('o.status', ['completed','delivered'])
                    ->where("DATE_FORMAT(o.created_at,'%Y-%m')", $from)
                    ->groupBy('oi.tax_percent')->orderBy('rate','ASC')->get()->getResultArray();
        }
        return null;
    }

    /** CSV download for any report type. */
    public function export($type)
    {
        if (!$this->getPlanFeature('allow_reports_advanced') && in_array($type, ['items','payments','gstr1','consolidated'], true)) {
            return redirect()->to(base_url('admin/reports/sales'))->with('error', 'Upgrade your plan to export this report.');
        }

        $from  = $this->request->getGet('from') ?? ($type === 'gstr1' ? date('Y-m') : date('Y-m-01'));
        $to    = $this->request->getGet('to') ?? date('Y-m-d');
        $title = '';
        $head  = [];
        $rows  = $this->reportRows((string)$type, (string)$from, (string)$to, $title, $head);
        if ($rows === null) {
            return redirect()->back()->with('error', 'Unknown report type');
        }

        $fh = fopen('php://temp', 'r+');
        fputcsv($fh, [$title . ' — ' . ($type === 'gstr1' ? 'Month: ' . $from : $from . ' to ' . $to)]);
        if ($head) fputcsv($fh, $head);
        foreach ($rows as $r) {
            fputcsv($fh, array_map(fn($v) => (is_float($v) || is_int($v)) ? rtrim(rtrim(number_format((float)$v, 2, '.', ''), '0'), '.') : $v, array_values($r)));
        }
        rewind($fh);
        $csv = stream_get_contents($fh);
        fclose($fh);

        $name = $type . '_' . str_replace('-', '', ($type === 'gstr1' ? $from : $from . '-' . $to)) . '.csv';
        return $this->response
            ->setHeader('Content-Type', 'text/csv; charset=utf-8')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $name . '"')
            ->setBody("\xEF\xBB\xBF" . $csv);
    }

    /** Print-HTML view — the browser's Save-as-PDF produces the PDF. */
    public function printReport($type)
    {
        if (!$this->getPlanFeature('allow_reports_advanced') && in_array($type, ['items','payments','gstr1','consolidated'], true)) {
            return $this->planLocked();
        }

        $from  = $this->request->getGet('from') ?? ($type === 'gstr1' ? date('Y-m') : date('Y-m-01'));
        $to    = $this->request->getGet('to') ?? date('Y-m-d');
        $title = '';
        $head  = [];
        $rows  = $this->reportRows((string)$type, (string)$from, (string)$to, $title, $head);
        if ($rows === null) return redirect()->back()->with('error', 'Unknown report type');

        $summary = [
            'Restaurant' => session('restaurant_name'),
            'Period'     => $type === 'gstr1' ? 'Month: ' . $from : $from . ' → ' . $to,
            'Printed'    => date('d M Y, h:i A') . ' by ' . session('user_name'),
        ];

        return view('admin/reports/print_view', compact('rows', 'head', 'title', 'summary', 'type'));
    }

    /** GSTR-1 outward supply summary page (GST compliance). */
    public function gst()
    {
        if (!$this->getPlanFeature('allow_reports_advanced')) return $this->planLocked();

        $db    = \Config\Database::connect();
        $rid   = session('restaurant_id');
        $month = $this->request->getGet('month') ?? date('Y-m');

        $base = $db->table('orders')
            ->where('restaurant_id', $rid)
            ->whereIn('status', ['completed', 'delivered'])
            ->where("DATE_FORMAT(created_at,'%Y-%m')", $month);

        $totals = (clone $base)
            ->select('COUNT(*) as invoices, SUM(tax_amount) as tax, SUM(cgst_amount) as cgst,
                      SUM(sgst_amount) as sgst, SUM(igst_amount) as igst, SUM(cess_amount) as cess,
                      SUM(total_amount) as gross, SUM(discount_amount) as discount')
            ->get()->getRowArray();

        // Rate-wise taxable value from line items (taxable = line total − line tax)
        $slabs = $db->table('order_items oi')
            ->select("oi.tax_percent as rate, SUM(oi.total_price - oi.tax_amount) as taxable, SUM(oi.tax_amount) as tax")
            ->join('orders o', 'o.id = oi.order_id')
            ->where('o.restaurant_id', $rid)
            ->whereIn('o.status', ['completed', 'delivered'])
            ->where("DATE_FORMAT(o.created_at,'%Y-%m')", $month)
            ->groupBy('oi.tax_percent')->orderBy('rate','ASC')->get()->getResultArray();

        // Invoice-wise list (first 100 bills of the month, as in the portal's B2B cut)
        $invoices = (clone $base)
            ->select('order_number, customer_name, created_at, tax_amount, cgst_amount, sgst_amount, igst_amount, cess_amount, total_amount')
            ->orderBy('created_at','ASC')->limit(100)->get()->getResultArray();

        return view('admin/reports/gst', array_merge($this->baseData('GSTR-1 Summary'), [
            'month' => $month, 'totals' => $totals, 'slabs' => $slabs, 'invoices' => $invoices,
        ]));
    }

    /** Owner-level consolidated report across every branch of the tenant. */
    public function consolidated()
    {
        if (!$this->getPlanFeature('allow_reports_advanced')) return $this->planLocked();

        $db   = \Config\Database::connect();
        $rid  = session('restaurant_id');
        $from = $this->request->getGet('from') ?? date('Y-m-01');
        $to   = $this->request->getGet('to') ?? date('Y-m-d');

        $title = '';
        $head  = [];
        $rows  = $this->reportRows('consolidated', $from, $to, $title, $head);

        $grand = [
            'branches'  => count($rows),
            'orders'    => array_sum(array_column($rows, 'orders')),
            'revenue'   => array_sum(array_column($rows, 'revenue')),
            'discount'  => array_sum(array_column($rows, 'discount')),
            'tax'       => array_sum(array_column($rows, 'tax')),
        ];

        return view('admin/reports/consolidated', array_merge($this->baseData('Consolidated Branch Report'), [
            'rows' => $rows, 'grand' => $grand, 'from' => $from, 'to' => $to,
        ]));
    }
}
