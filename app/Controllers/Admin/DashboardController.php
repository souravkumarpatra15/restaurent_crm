<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class DashboardController extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();

        // ── Core SaaS metrics ─────────────────────────────────
        $allRests = $db->table('restaurants r')
            ->select('r.id, r.name, r.email, r.subscription_status, r.billing_cycle,
                      r.subscription_ends_at, r.created_at, r.is_active,
                      p.name as plan_name, p.price_monthly, p.price_yearly, p.id as plan_id')
            ->join('saas_plans p', 'p.id = r.plan_id', 'left')
            ->get()->getResultArray();

        $mrr = 0; $arr = 0;
        foreach ($allRests as $r) {
            if ($r['subscription_status'] === 'active') {
                $m    = $r['billing_cycle']==='yearly' ? ($r['price_yearly']/12) : $r['price_monthly'];
                $mrr += $m;
                $arr += $r['billing_cycle']==='yearly' ? $r['price_yearly'] : ($r['price_monthly']*12);
            }
        }

        // Monthly new restaurants (last 6 months)
        $growthData = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = date('Y-m', strtotime("-$i months"));
            $label = date('M', strtotime("-$i months"));
            $cnt   = $db->table('restaurants')->where("DATE_FORMAT(created_at,'%Y-%m')", $month)->countAllResults();
            $growthData[] = ['month' => $label, 'count' => $cnt];
        }

        // Revenue last 6 months
        $revenueData = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = date('Y-m', strtotime("-$i months"));
            $label = date('M', strtotime("-$i months"));
            $rev   = $db->table('subscription_payments')
                ->selectSum('amount')
                ->where("DATE_FORMAT(created_at,'%Y-%m')", $month)
                ->where('status', 'paid')
                ->get()->getRowArray();
            $revenueData[] = ['month' => $label, 'amount' => (float)($rev['amount'] ?? 0)];
        }

        // Plan distribution
        $planDist = $db->table('restaurants r')
            ->select('p.name as plan_name, COUNT(*) as count, p.price_monthly')
            ->join('saas_plans p', 'p.id = r.plan_id', 'left')
            ->where('r.subscription_status', 'active')
            ->groupBy('r.plan_id')->get()->getResultArray();
        $totalActive = array_sum(array_column($planDist, 'count')) ?: 1;
        foreach ($planDist as &$p) {
            $p['pct'] = round($p['count'] / $totalActive * 100);
        }

        // Expiring subscriptions
        $expiring = $db->table('restaurants r')
            ->select('r.id, r.name, r.email, r.subscription_ends_at, p.name as plan_name')
            ->join('saas_plans p', 'p.id = r.plan_id', 'left')
            ->where('r.subscription_status', 'active')
            ->where('r.subscription_ends_at >=', date('Y-m-d'))
            ->where('r.subscription_ends_at <=', date('Y-m-d', strtotime('+7 days')))
            ->orderBy('r.subscription_ends_at', 'ASC')
            ->get()->getResultArray();

        // Recent restaurants
        $recent = $db->table('restaurants r')
            ->select('r.id, r.name, r.email, r.subscription_status, r.created_at,
                      p.name as plan_name,
                      (SELECT COUNT(*) FROM branches WHERE restaurant_id=r.id) as branches,
                      (SELECT COUNT(*) FROM orders WHERE restaurant_id=r.id AND DATE(created_at)=CURDATE()) as orders_today')
            ->join('saas_plans p', 'p.id = r.plan_id', 'left')
            ->orderBy('r.created_at', 'DESC')->limit(8)->get()->getResultArray();

        // Today's platform activity
        $todayOrders = $db->table('orders')
            ->where('DATE(created_at)', date('Y-m-d'))->countAllResults();
        $todayQrOrders = $db->table('orders')
            ->where('DATE(created_at)', date('Y-m-d'))
            ->where('source', 'qr_customer')->countAllResults();
        $todayRevenue = $db->table('subscription_payments')
            ->selectSum('amount')
            ->where('DATE(created_at)', date('Y-m-d'))
            ->where('status', 'paid')
            ->get()->getRowArray();

        $stats = [
            'total'          => count($allRests),
            'active'         => count(array_filter($allRests, fn($r) => $r['subscription_status']==='active')),
            'trial'          => count(array_filter($allRests, fn($r) => $r['subscription_status']==='trial')),
            'expired'        => count(array_filter($allRests, fn($r) => in_array($r['subscription_status'],['expired','suspended','cancelled']))),
            'new_month'      => count(array_filter($allRests, fn($r) => date('Y-m',strtotime($r['created_at']))=== date('Y-m'))),
            'mrr'            => $mrr,
            'arr'            => $arr,
            'today_orders'   => $todayOrders,
            'today_qr'       => $todayQrOrders,
            'today_revenue'  => (float)($todayRevenue['amount'] ?? 0),
            'expiring_count' => count($expiring),
        ];

        return view('admin/dashboard/super_dashboard', [
            'pageTitle'   => 'SaaS Dashboard',
            'stats'       => $stats,
            'growthData'  => $growthData,
            'revenueData' => $revenueData,
            'planDist'    => $planDist,
            'expiring'    => $expiring,
            'recent'      => $recent,
            'userName'    => session('user_name'),
            'userRole'    => session('role_slug'),
        ]);
    }
}
