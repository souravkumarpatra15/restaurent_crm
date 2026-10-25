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
}
