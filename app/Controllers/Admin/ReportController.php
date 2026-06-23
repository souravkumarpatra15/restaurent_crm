<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class ReportController extends BaseController
{
    public function index()
    {
        return $this->revenue();
    }
    public function revenue()
    {
        $db = \Config\Database::connect();
        $from = $this->request->getGet('from') ?? date('Y-m-01');
        $to   = $this->request->getGet('to')   ?? date('Y-m-d');

        $payments = $db->table('subscription_payments')
            ->select('SUM(amount) as total, COUNT(*) as count, billing_cycle')
            ->where('status', 'paid')
            ->where('DATE(paid_at) >=', $from)
            ->where('DATE(paid_at) <=', $to)
            ->groupBy('billing_cycle')
            ->get()->getResultArray();

        $totalRevenue = array_sum(array_column($payments, 'total'));
        $restaurants  = $db->table('restaurants')->countAllResults();
        $active       = $db->table('restaurants')->where('subscription_status', 'active')->countAllResults();

        return view('admin/reports/revenue', [
            'pageTitle'    => 'Revenue Report',
            'payments'     => $payments,
            'totalRevenue' => $totalRevenue,
            'restaurants'  => $restaurants,
            'active'       => $active,
            'from'         => $from,
            'to'           => $to,
            'userName'     => session('user_name'),
            'userRole'     => session('role_slug'),
        ]);
    }
    public function subscriptions()
    {
        return $this->revenue();
    }
}
