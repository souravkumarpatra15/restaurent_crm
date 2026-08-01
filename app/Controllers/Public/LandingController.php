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

        return view('landing', $data);
    }
}
