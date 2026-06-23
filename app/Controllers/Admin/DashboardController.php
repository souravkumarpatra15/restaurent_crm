<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class DashboardController extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();
        $stats = [
            'total_restaurants'    => $db->table('restaurants')->countAllResults(),
            'active_subscriptions' => $db->table('restaurants')->where('subscription_status', 'active')->countAllResults(),
            'mrr'                  => 0,
            'trials'               => $db->table('restaurants')->where('subscription_status', 'trial')->countAllResults(),
            'new_this_month'       => $db->table('restaurants')->where('MONTH(created_at)', date('m'))->countAllResults(),
        ];
        $restaurants = $db->table('restaurants r')
            ->select('r.*, p.name as plan_name,
                      (SELECT COUNT(*) FROM branches WHERE restaurant_id=r.id) as branch_count')
            ->join('saas_plans p', 'p.id = r.plan_id', 'left')
            ->orderBy('r.created_at', 'DESC')
            ->limit(20)->get()->getResultArray();

        $planStats = $db->table('restaurants r')
            ->select('p.name as plan_name, COUNT(*) as count')
            ->join('saas_plans p', 'p.id = r.plan_id', 'left')
            ->groupBy('r.plan_id')
            ->get()->getResultArray();
        $total = array_sum(array_column($planStats, 'count')) ?: 1;
        foreach ($planStats as &$p) {
            $p['percent'] = round($p['count'] / $total * 100);
        }

        $expiringSoon = $db->table('restaurants')
            ->where('subscription_ends_at >=', date('Y-m-d'))
            ->where('subscription_ends_at <=', date('Y-m-d', strtotime('+7 days')))
            ->where('subscription_status', 'active')
            ->get()->getResultArray();

        return view('admin/dashboard/super_dashboard', [
            'pageTitle'    => 'Super Admin Dashboard',
            'stats'        => $stats,
            'restaurants'  => $restaurants,
            'planStats'    => $planStats,
            'expiringSoon' => $expiringSoon,
            'userName'     => session('user_name'),
            'userRole'     => session('role_slug'),
        ]);
    }
}
