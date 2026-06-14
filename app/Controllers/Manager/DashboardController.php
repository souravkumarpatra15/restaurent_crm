<?php
namespace App\Controllers\Manager;

use App\Controllers\BaseController;

class DashboardController extends BaseController
{
    public function index()
    {
        $db           = \Config\Database::connect();
        $restaurantId = session('restaurant_id');
        $branchId     = session('branch_id');

        $todayOrders = $db->table('orders')
            ->where('restaurant_id', $restaurantId)
            ->where('DATE(created_at)', date('Y-m-d'))
            ->where('status !=', 'cancelled');

        $yesterdayOrders = $db->table('orders')
            ->where('restaurant_id', $restaurantId)
            ->where('DATE(created_at)', date('Y-m-d', strtotime('-1 day')))
            ->where('status !=', 'cancelled');

        $todayCount   = (clone $todayOrders)->countAllResults();
        $todayRev     = (clone $todayOrders)->selectSum('total_amount')->get()->getRowArray()['total_amount'] ?? 0;
        $yestCount    = (clone $yesterdayOrders)->countAllResults();
        $yestRev      = (clone $yesterdayOrders)->selectSum('total_amount')->get()->getRowArray()['total_amount'] ?? 0;

        $stats = [
            'orders_today'       => $todayCount,
            'revenue_today'      => $todayRev,
            'orders_change'      => $yestCount ? round(($todayCount - $yestCount) / $yestCount * 100) : 0,
            'revenue_change'     => $yestRev ? round(($todayRev - $yestRev) / $yestRev * 100) : 0,
            'revenue_change_val' => $todayRev - $yestRev,
            'customers_today'    => $db->table('orders')->where('restaurant_id',$restaurantId)->where('DATE(created_at)',date('Y-m-d'))->where('customer_id IS NOT NULL','',false)->countAllResults(),
            'pending_kots'       => $db->table('kots')->where('branch_id',$branchId)->whereIn('status',['pending','in_progress'])->countAllResults(),
        ];

        $liveOrders = $db->table('orders o')
            ->select('o.*, t.table_number')
            ->join('tables t', 't.id = o.table_id', 'left')
            ->where('o.restaurant_id', $restaurantId)
            ->whereIn('o.status', ['pending','confirmed','preparing','ready','served'])
            ->orderBy('o.created_at','DESC')
            ->limit(10)->get()->getResultArray();

        $branches = $db->table('branches')
            ->where('restaurant_id', $restaurantId)
            ->get()->getResultArray();

        $paymentSplit = $db->table('payments p')
            ->select('p.payment_method, SUM(p.amount) as total')
            ->join('orders o', 'o.id = p.order_id')
            ->where('o.restaurant_id', $restaurantId)
            ->where('DATE(p.created_at)', date('Y-m-d'))
            ->groupBy('p.payment_method')
            ->get()->getResultArray();

        $orderTypeSplit = $db->table('orders')
            ->select('order_type, COUNT(*) as count')
            ->where('restaurant_id', $restaurantId)
            ->where('DATE(created_at)', date('Y-m-d'))
            ->groupBy('order_type')
            ->get()->getResultArray();

        $topItems = $db->table('order_items oi')
            ->select('oi.menu_item_id, ANY_VALUE(oi.name) as name, SUM(oi.quantity) as total_qty, SUM(oi.total_price) as total_revenue')
            ->join('orders o', 'o.id = oi.order_id')
            ->where('o.restaurant_id', $restaurantId)
            ->where('DATE(o.created_at)', date('Y-m-d'))
            ->groupBy('oi.menu_item_id')
            ->orderBy('total_qty','DESC')
            ->limit(6)->get()->getResultArray();

        $hourlyRevenue = $db->table('orders')
            ->select('HOUR(created_at) as hour, SUM(total_amount) as revenue')
            ->where('restaurant_id', $restaurantId)
            ->where('DATE(created_at)', date('Y-m-d'))
            ->where('payment_status', 'paid')
            ->groupBy('HOUR(created_at)')
            ->orderBy('hour','ASC')
            ->get()->getResultArray();

        $reservations = $db->table('reservations r')
            ->select('r.*, t.table_number')
            ->join('tables t', 't.id = r.table_id', 'left')
            ->where('r.reservation_date', date('Y-m-d'))
            ->whereIn('r.status', ['pending','confirmed'])
            ->orderBy('r.reservation_time','ASC')
            ->limit(5)->get()->getResultArray();

        return view('admin/dashboard/index', [
            'pageTitle'      => 'Dashboard',
            'stats'          => $stats,
            'liveOrders'     => $liveOrders,
            'branches'       => $branches,
            'paymentSplit'   => $paymentSplit,
            'orderTypeSplit' => $orderTypeSplit,
            'topItems'       => $topItems,
            'hourlyRevenue'  => $hourlyRevenue,
            'reservations'   => $reservations,
            'userName'       => session('user_name'),
            'userRole'       => session('role_slug'),
            'branchName'     => session('branch_name'),
            'restaurantName' => session('restaurant_name'),
        ]);
    }

    public function liveOrders()
    {
        $db = \Config\Database::connect();
        $orders = $db->table('orders o')
            ->select('o.*, t.table_number')
            ->join('tables t','t.id = o.table_id','left')
            ->where('o.restaurant_id', session('restaurant_id'))
            ->whereIn('o.status',['pending','confirmed','preparing','ready','served'])
            ->orderBy('o.created_at','DESC')->limit(10)
            ->get()->getResultArray();
        return view('admin/dashboard/partials/live_orders', ['liveOrders' => $orders]);
    }

    public function hourlyChart()
    {
        $db       = \Config\Database::connect();
        $branchId = $this->request->getGet('branch');
        $q = $db->table('orders')
            ->select('HOUR(created_at) as hour, SUM(total_amount) as revenue')
            ->where('restaurant_id', session('restaurant_id'))
            ->where('DATE(created_at)', date('Y-m-d'))
            ->where('payment_status','paid')
            ->groupBy('HOUR(created_at)')
            ->orderBy('hour','ASC');
        if ($branchId) $q->where('branch_id', $branchId);
        return $this->response->setJSON($q->get()->getResultArray());
    }

    public function switchBranch()
    {
        $branchId = $this->request->getGet('branch_id');
        if ($branchId) session()->set('branch_id', $branchId);
        return $this->response->setJSON(['success' => true]);
    }
}
