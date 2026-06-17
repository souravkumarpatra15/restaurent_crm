<?php
namespace App\Controllers\Admin;
use App\Controllers\BaseController;

class SubscriptionController extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();
        $subs = $db->table('restaurants r')
            ->select('r.id, r.name, r.email, r.subscription_status, r.billing_cycle, r.subscription_ends_at, r.next_billing_date, p.name as plan_name, p.price_monthly, p.price_yearly')
            ->join('saas_plans p','p.id = r.plan_id','left')
            ->orderBy('r.subscription_ends_at','ASC')
            ->get()->getResultArray();

        $stats = [
            'active'    => array_sum(array_map(fn($s) => $s['subscription_status']==='active'?1:0, $subs)),
            'trial'     => array_sum(array_map(fn($s) => $s['subscription_status']==='trial'?1:0, $subs)),
            'expired'   => array_sum(array_map(fn($s) => in_array($s['subscription_status'],['expired','cancelled'])?1:0, $subs)),
            'mrr'       => array_sum(array_map(fn($s) => $s['subscription_status']==='active'?($s['billing_cycle']==='yearly'?$s['price_yearly']/12:$s['price_monthly']):0, $subs)),
        ];

        return view('admin/subscriptions/index', [
            'pageTitle' => 'Subscriptions',
            'subs'      => $subs,
            'stats'     => $stats,
            'userName'  => session('user_name'),
            'userRole'  => session('role_slug'),
        ]);
    }

    public function view($id) { return redirect()->to(base_url('super/subscriptions')); }
    public function remind($id) { return $this->response->setJSON(['success'=>true]); }
}
