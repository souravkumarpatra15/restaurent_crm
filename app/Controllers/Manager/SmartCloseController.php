<?php
namespace App\Controllers\Manager;
use App\Controllers\BaseController;

class SmartCloseController extends BaseController
{
    public function index()
    {
        $db  = \Config\Database::connect();
        $rid = session('restaurant_id');
        $bid = session('branch_id');
        $today = date('Y-m-d');

        // Today's order summary
        $orders = $db->table('orders')
            ->where('restaurant_id', $rid)->where('branch_id', $bid)
            ->where('DATE(created_at)', $today)->where('status !=','cancelled')
            ->get()->getResultArray();

        $totalRevenue  = array_sum(array_column($orders,'total_amount'));
        $totalOrders   = count($orders);
        $totalDiscount = array_sum(array_column($orders,'discount_amount'));
        $totalTax      = array_sum(array_column($orders,'tax_amount'));

        // Payment method breakdown
        $payments = $db->table('payments p')
            ->join('orders o','o.id=p.order_id')
            ->select('p.payment_method, SUM(p.amount) as total, COUNT(*) as count')
            ->where('o.restaurant_id',$rid)->where('o.branch_id',$bid)
            ->where('DATE(p.created_at)',$today)
            ->groupBy('p.payment_method')
            ->get()->getResultArray();

        // Cash in hand (cash payments - cash expenses)
        $cashIn  = array_sum(array_map(fn($p) => $p['payment_method']==='cash'?(float)$p['total']:0, $payments));
        $cashExp = (float)($db->table('expenses')
            ->selectSum('amount')->where('branch_id',$bid)
            ->where('DATE(created_at)',$today)->where('paid_by','cash')
            ->get()->getRowArray()['amount'] ?? 0);
        $cashBalance = $cashIn - $cashExp;

        // Top 5 items today
        $topItems = $db->table('order_items oi')
            ->join('orders o','o.id=oi.order_id')
            ->select('oi.name, SUM(oi.quantity) as qty, SUM(oi.total_price) as revenue')
            ->where('o.restaurant_id',$rid)->where('o.branch_id',$bid)
            ->where('DATE(o.created_at)',$today)->where('o.status !=','cancelled')
            ->groupBy('oi.name')->orderBy('qty','DESC')->limit(5)
            ->get()->getResultArray();

        // Order type breakdown
        $byType = $db->table('orders')
            ->select('order_type, COUNT(*) as cnt, SUM(total_amount) as rev')
            ->where('restaurant_id',$rid)->where('branch_id',$bid)
            ->where('DATE(created_at)',$today)->where('status !=','cancelled')
            ->groupBy('order_type')->get()->getResultArray();

        // Expenses today
        $expenses = (float)($db->table('expenses')
            ->selectSum('amount')->where('branch_id',$bid)
            ->where('DATE(created_at)',$today)
            ->get()->getRowArray()['amount'] ?? 0);

        // KOT stats
        $kotStats = [
            'total'   => $db->table('kots')->where('branch_id',$bid)->where('DATE(created_at)',$today)->countAllResults(),
            'pending' => $db->table('kots')->where('branch_id',$bid)->where('DATE(created_at)',$today)->where('status','pending')->countAllResults(),
        ];

        // Check if already closed today
        $alreadyClosed = $db->table('day_close_logs')
            ->where('branch_id',$bid)->where('close_date',$today)
            ->countAllResults() > 0;

        return view('admin/smart_close/index', [
            'pageTitle'    => 'Smart Day Close',
            'totalRevenue' => $totalRevenue,
            'totalOrders'  => $totalOrders,
            'totalDiscount'=> $totalDiscount,
            'totalTax'     => $totalTax,
            'cashBalance'  => $cashBalance,
            'cashIn'       => $cashIn,
            'cashExp'      => $cashExp,
            'payments'     => $payments,
            'topItems'     => $topItems,
            'byType'       => $byType,
            'expenses'     => $expenses,
            'netProfit'    => $totalRevenue - $expenses,
            'kotStats'     => $kotStats,
            'alreadyClosed'=> $alreadyClosed,
            'today'        => $today,
            'userName'     => session('user_name'),
            'userRole'     => session('role_slug'),
        ]);
    }

    public function close()
    {
        $db       = \Config\Database::connect();
        $bid      = session('branch_id');
        $rid      = session('restaurant_id');
        $today    = date('Y-m-d');
        $cashHand = (float)$this->request->getPost('cash_in_hand');
        $notes    = $this->request->getPost('notes');

        // Calculate expected cash
        $cashIn = (float)($db->table('payments p')
            ->join('orders o','o.id=p.order_id')
            ->selectSum('p.amount')->where('o.branch_id',$bid)
            ->where('p.payment_method','cash')->where('DATE(p.created_at)',$today)
            ->get()->getRowArray()['amount'] ?? 0);
        $cashExp = (float)($db->table('expenses')
            ->selectSum('amount')->where('branch_id',$bid)
            ->where('DATE(created_at)',$today)->where('paid_by','cash')
            ->get()->getRowArray()['amount'] ?? 0);
        $expected   = $cashIn - $cashExp;
        $difference = $cashHand - $expected;

        $db->table('day_close_logs')->insert([
            'restaurant_id'   => $rid,
            'branch_id'       => $bid,
            'closed_by'       => session('user_id'),
            'close_date'      => $today,
            'cash_in'         => $cashIn,
            'cash_expenses'   => $cashExp,
            'cash_expected'   => $expected,
            'cash_actual'     => $cashHand,
            'cash_difference' => $difference,
            'notes'           => $notes,
            'created_at'      => date('Y-m-d H:i:s'),
        ]);

        return $this->response->setJSON([
            'success'    => true,
            'expected'   => $expected,
            'actual'     => $cashHand,
            'difference' => $difference,
            'status'     => abs($difference) < 10 ? 'balanced' : ($difference > 0 ? 'surplus' : 'shortage'),
        ]);
    }

    public function history()
    {
        $db   = \Config\Database::connect();
        $logs = $db->table('day_close_logs dcl')
            ->select('dcl.*, u.name as closed_by_name')
            ->join('users u','u.id=dcl.closed_by','left')
            ->where('dcl.branch_id', session('branch_id'))
            ->orderBy('dcl.close_date','DESC')->limit(30)
            ->get()->getResultArray();

        return $this->response->setJSON($logs);
    }
}
