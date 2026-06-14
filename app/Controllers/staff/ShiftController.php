<?php
namespace App\Controllers\Staff;
use App\Controllers\BaseController;

class ShiftController extends BaseController
{
    public function summary()
    {
        $db  = \Config\Database::connect();
        $uid = session('user_id');
        $bid = session('branch_id');
        $today = date('Y-m-d');

        $summary = $db->table('orders')
            ->select('COUNT(*) as total_orders, SUM(total_amount) as total_revenue, SUM(discount_amount) as total_discount')
            ->where('branch_id', $bid)
            ->where('user_id', $uid)
            ->where('DATE(created_at)', $today)
            ->where('payment_status','paid')
            ->get()->getRowArray();

        $payments = $db->table('payments p')
            ->select('p.payment_method, COUNT(*) as count, SUM(p.amount) as total')
            ->join('orders o','o.id = p.order_id')
            ->where('o.branch_id', $bid)
            ->where('o.user_id', $uid)
            ->where('DATE(p.created_at)', $today)
            ->groupBy('p.payment_method')
            ->get()->getResultArray();

        return view('staff/shift/summary', [
            'pageTitle' => 'My Shift Summary',
            'summary'   => $summary,
            'payments'  => $payments,
            'date'      => $today,
            'userName'  => session('user_name'),
            'userRole'  => session('role_slug'),
        ]);
    }
    public function open()  { return redirect()->to(base_url('pos')); }
    public function doOpen(){ return redirect()->to(base_url('pos')); }
    public function close() { return redirect()->to(base_url('pos')); }
    public function doClose(){ return redirect()->to(base_url('pos')); }
}
