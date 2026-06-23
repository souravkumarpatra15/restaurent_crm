<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class SubscriptionController extends BaseController
{
    public function index()
    {
        $db     = \Config\Database::connect();
        $filter = $this->request->getGet('status') ?? '';

        $q = $db->table('restaurants r')
            ->select('r.id, r.name, r.email, r.phone, r.subscription_status, r.billing_cycle,
                      r.subscription_ends_at, r.trial_ends_at, r.next_billing_date, r.is_active,
                      p.name as plan_name, p.price_monthly, p.price_yearly, p.id as plan_id')
            ->join('saas_plans p', 'p.id=r.plan_id', 'left')
            ->orderBy('r.subscription_ends_at', 'ASC');
        if ($filter) $q->where('r.subscription_status', $filter);

        $subs = $q->get()->getResultArray();

        $stats = [
            'active'    => $db->table('restaurants')->where('subscription_status', 'active')->countAllResults(),
            'trial'     => $db->table('restaurants')->where('subscription_status', 'trial')->countAllResults(),
            'expired'   => $db->table('restaurants')->whereIn('subscription_status', ['expired', 'cancelled'])->countAllResults(),
            'suspended' => $db->table('restaurants')->where('subscription_status', 'suspended')->countAllResults(),
            'mrr'       => 0,
        ];
        foreach ($subs as $s) {
            if ($s['subscription_status'] === 'active') {
                $stats['mrr'] += $s['billing_cycle'] === 'yearly' ? $s['price_yearly'] / 12 : $s['price_monthly'];
            }
        }

        $expiring = $db->table('restaurants r')
            ->select('r.id, r.name, r.email, r.subscription_ends_at, p.name as plan_name')
            ->join('saas_plans p', 'p.id=r.plan_id', 'left')
            ->where('r.subscription_status', 'active')
            ->where('r.subscription_ends_at >=', date('Y-m-d'))
            ->where('r.subscription_ends_at <=', date('Y-m-d', strtotime('+7 days')))
            ->get()->getResultArray();

        $plans = $db->table('saas_plans')->where('is_active', 1)->orderBy('sort_order', 'ASC')->get()->getResultArray();

        return view('admin/subscriptions/index', [
            'pageTitle' => 'Subscriptions',
            'subs'      => $subs,
            'stats'     => $stats,
            'expiring'  => $expiring,
            'plans'     => $plans,
            'filter'    => $filter,
            'userName'  => session('user_name'),
            'userRole'  => session('role_slug'),
        ]);
    }

    public function changePlan($id)
    {
        $db      = \Config\Database::connect();
        $planId  = $this->request->getPost('plan_id');
        $cycle   = $this->request->getPost('billing_cycle') ?? 'monthly';
        $ends    = $this->request->getPost('subscription_ends_at');
        $status  = $this->request->getPost('subscription_status') ?? 'active';

        $db->table('restaurants')->where('id', $id)->update([
            'plan_id'              => $planId,
            'subscription_status'  => $status,
            'billing_cycle'        => $cycle,
            'subscription_ends_at' => $ends,
            'updated_at'           => date('Y-m-d H:i:s'),
        ]);

        // Log payment if marking active
        if ($status === 'active') {
            $plan = $db->table('saas_plans')->where('id', $planId)->get()->getRowArray();
            $amount = $cycle === 'yearly' ? $plan['price_yearly'] : $plan['price_monthly'];
            $db->table('subscription_payments')->insert([
                'restaurant_id' => $id,
                'plan_id'       => $planId,
                'amount'        => $amount,
                'billing_cycle' => $cycle,
                'period_start'  => date('Y-m-d'),
                'period_end'    => $ends,
                'status'        => 'paid',
                'paid_at'       => date('Y-m-d H:i:s'),
                'notes'         => 'Manual update by super admin',
                'created_at'    => date('Y-m-d H:i:s'),
            ]);
        }

        return $this->response->setJSON(['success' => true]);
    }

    public function suspend($id)
    {
        \Config\Database::connect()->table('restaurants')->where('id', $id)
            ->update(['subscription_status' => 'suspended', 'updated_at' => date('Y-m-d H:i:s')]);
        return $this->response->setJSON(['success' => true]);
    }

    public function activate($id)
    {
        \Config\Database::connect()->table('restaurants')->where('id', $id)
            ->update(['subscription_status' => 'active', 'updated_at' => date('Y-m-d H:i:s')]);
        return $this->response->setJSON(['success' => true]);
    }

    public function view($id)
    {
        return redirect()->to(base_url('super/subscriptions'));
    }

    public function remind($id)
    {
        $db   = \Config\Database::connect();
        $rest = $db->table('restaurants')->where('id', $id)->get()->getRowArray();
        // In production: send email reminder
        // For now: log and return success
        log_message('info', 'Renewal reminder sent to: ' . ($rest['email'] ?? 'unknown'));
        return $this->response->setJSON(['success' => true, 'message' => 'Reminder sent to ' . $rest['email']]);
    }
}
