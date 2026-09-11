<?php

namespace App\Controllers\Public;

use App\Controllers\BaseController;

class LandingController extends BaseController
{
    public function index()
    {
        if (session()->get('user_id')) {
            $role = session()->get('role_slug');

            return redirect()->to(base_url(match ($role) {
                'super_admin'      => 'super/dashboard',
                'restaurant_admin' => 'admin/dashboard',
                'branch_manager'   => 'admin/dashboard',
                'cashier',
                'waiter'           => 'pos',
                'kitchen_staff'    => 'pos/kitchen',
                default            => 'login',
            }));
        }

        $db = \Config\Database::connect();

        $data['plans'] = $db->table('saas_plans sp')
            ->select('sp.*')
            ->where('sp.is_active', 1)
            ->orderBy('sp.sort_order', 'ASC')
            ->get()
            ->getResultArray();

        // Keep the production landing page markup/styles intact; only route the
        // existing hero Start Free Trial CTA to the new lead form.
        $html = view('landing', $data);
        $html = str_replace(
            '<a href="#pricing" class="btn-hero-p">',
            '<a href="' . base_url('register') . '" class="btn-hero-p">',
            $html
        );

        return $html;
    }
}
