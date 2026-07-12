<?php

namespace App\Controllers\Public;

use App\Controllers\BaseController;

class LandingController extends BaseController
{
    public function index()
    {
        // If already logged in, redirect to dashboard
        if (session()->get('user_id')) {
            $role = session()->get('role_slug');
            return redirect()->to(base_url(
                match($role) {
                    'super_admin'       => 'super/dashboard',
                    'restaurant_admin'  => 'admin/dashboard',
                    'branch_manager'    => 'admin/dashboard',
                    'cashier','waiter'  => 'pos',
                    'kitchen_staff'     => 'pos/kitchen',
                    default             => 'login',
                }
            ));
        }

        // Serve the static landing page
        return $this->response->setBody(
            file_get_contents(FCPATH . 'landing.html')
        );
    }
}
