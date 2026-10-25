<?php

namespace App\Controllers\Auth;

use App\Controllers\BaseController;

/**
 * Self-serve SaaS signup — creates tenant + main branch + owner user,
 * starts a 14-day free trial and logs the owner straight into the
 * onboarding dashboard. Template menu categories come pre-created.
 */
class SignupController extends BaseController
{
    public function form()
    {
        if (session()->get('user_id')) {
            return redirect()->to(base_url('admin/dashboard'));
        }

        $plans = \Config\Database::connect()->table('saas_plans')
            ->orderBy('price_monthly', 'ASC')->get()->getResultArray();

        return view('auth/signup', [
            'title' => 'Create your restaurant account',
            'plans' => $plans,
        ]);
    }

    public function submit()
    {
        $request = $this->request;
        $db      = \Config\Database::connect();

        // ---- Collect + validate ----
        $name       = trim((string)$request->getPost('restaurant_name'));
        $city       = trim((string)$request->getPost('city'));
        $ownerName  = trim((string)$request->getPost('owner_name'));
        $email      = strtolower(trim((string)$request->getPost('email')));
        $phone      = trim((string)$request->getPost('phone'));
        $password   = (string)$request->getPost('password');
        $planId     = (int)$request->getPost('plan_id');
        $type       = (string)$request->getPost('restaurant_type') ?: 'casual_dining';

        $fail = fn(string $m) => redirect()->back()->withInput()->with('error', $m);

        if (mb_strlen($name) < 3)                return $fail('Restaurant name is too short');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return $fail('Enter a valid owner email');
        if (strlen($password) < 6)               return $fail('Password must be at least 6 characters');
        if (!preg_match('/^[0-9+\-\s]{6,20}$/', $phone)) return $fail('Enter a valid phone number');

        // Uniqueness
        if ($db->table('users')->where('email', $email)->countAllResults() > 0) {
            return $fail('This email is already registered — login instead');
        }

        // Unique slug: my-restaurant-restaurant → tenant-2, tenant-3...
        $base = $slug = $this->makeSlug($name);
        $i = 1;
        while ($db->table('restaurants')->where('slug', $slug)->countAllResults() > 0) {
            $slug = $base . '-' . ++$i;
        }

        // Plan — optional (defaults to the cheapest plan)
        $plan = $planId
            ? $db->table('saas_plans')->where('id', $planId)->get()->getRowArray()
            : $db->table('saas_plans')->orderBy('price_monthly', 'ASC')->limit(1)->get()->getRowArray();
        if (!$plan) return $fail('No plans configured yet — contact support');

        // ---- Create tenant (14-day trial) ----
        $db->transStart();

        $db->table('restaurants')->insert([
            'plan_id'             => (int)$plan['id'],
            'name'                => $name,
            'slug'                => $slug,
            'booking_slug'        => substr($slug, 0, 90),
            'restaurant_type'     => $type,
            'email'               => $email,
            'phone'               => $phone,
            'city'                => $city ?: null,
            'currency'            => 'INR',
            'currency_symbol'     => '₹',
            'timezone'            => 'Asia/Kolkata',
            'tax_type'            => 'exclusive',
            'default_tax_percent' => 5.00,
            'billing_prefix'      => strtoupper(substr($this->makeSlug($name), 0, 2)) ?: 'DV',
            'billing_counter'     => 1,
            'kot_prefix'          => 'KOT',
            'kot_counter'         => 1,
            'theme_color'         => '#FF6B35',
            'receipt_header'      => "Welcome to {$name}",
            'receipt_footer'      => 'Thank you. Visit again!',
            'subscription_status' => 'trial',
            'trial_ends_at'       => date('Y-m-d H:i:s', strtotime('+14 days')),
            'billing_cycle'       => 'monthly',
            'is_active'           => 1,
            'created_at'          => date('Y-m-d H:i:s'),
            'updated_at'          => date('Y-m-d H:i:s'),
        ]);
        $restaurantId = (int)$db->insertID();

        // Main branch
        $branchCode = strtoupper(substr($this->makeSlug($name), 0, 2)) . '-MAIN';
        $db->table('branches')->insert([
            'restaurant_id'  => $restaurantId,
            'name'           => 'Main Branch',
            'code'           => $branchCode,
            'branch_type'    => 'main',
            'phone'          => $phone,
            'email'          => $email,
            'city'           => $city ?: null,
            'opening_time'   => '09:00:00',
            'closing_time'   => '23:00:00',
            'has_dine_in'    => 1,
            'has_takeaway'   => 1,
            'has_delivery'   => 0,
            'printer_type'   => 'thermal_80mm',
            'billing_prefix' => strtoupper(substr($this->makeSlug($name), 0, 2)) ?: 'DV',
            'billing_counter'=> 1,
            'kot_counter'    => 1,
            'is_active'      => 1,
            'created_at'     => date('Y-m-d H:i:s'),
            'updated_at'     => date('Y-m-d H:i:s'),
        ]);
        $branchId = (int)$db->insertID();

        // Starter table area + a couple of tables so the POS opens
        $db->table('table_areas')->insert(['branch_id' => $branchId, 'name' => 'Main Hall', 'sort_order' => 1, 'is_active' => 1]);
        $areaId = (int)$db->insertID();
        for ($i = 1; $i <= 4; $i++) {
            $token = bin2hex(random_bytes(20));
            $db->table('tables')->insert([
                'branch_id'    => $branchId,
                'area_id'      => $areaId,
                'table_number' => 'T' . str_pad((string)$i, 2, '0', STR_PAD_LEFT),
                'capacity'     => 4,
                'shape'        => 'square',
                'status'       => 'available',
                'qr_token'     => $token,
                'sort_order'   => $i,
                'is_active'    => 1,
            ]);
        }

        // Template menu categories
        foreach (['Starters' => 1, 'Main Course' => 2, 'Biryani' => 3, 'Breads' => 4, 'Beverages' => 5, 'Desserts' => 6] as $cat => $sort) {
            $db->table('menu_categories')->insert([
                'restaurant_id' => $restaurantId,
                'name'          => $cat,
                'sort_order'    => $sort,
                'is_active'     => 1,
            ]);
        }

        // Owner account
        $db->table('users')->insert([
            'restaurant_id'     => $restaurantId,
            'branch_id'         => $branchId,
            'role_id'           => 2, // restaurant_admin
            'name'              => $ownerName ?: $name . ' Owner',
            'email'             => $email,
            'phone'             => $phone,
            'password'          => password_hash($password, PASSWORD_BCRYPT),
            'pin'               => (string)random_int(1000, 9999),
            'department'        => 'Management',
            'is_active'         => 1,
            'email_verified_at' => date('Y-m-d H:i:s'),
            'created_at'        => date('Y-m-d H:i:s'),
            'updated_at'        => date('Y-m-d H:i:s'),
        ]);

        $db->transComplete();
        if ($db->transStatus() === false) {
            return redirect()->back()->withInput()->with('error', 'Signup failed — please try again');
        }

        // Auto-login the owner (same session shape as AuthController)
        session()->set([
            'user_id'         => $db->query('SELECT id FROM users WHERE email = ?', [$email])->getRowArray()['id'],
            'user_name'       => $ownerName ?: $name . ' Owner',
            'role_slug'       => 'restaurant_admin',
            'restaurant_id'   => $restaurantId,
            'restaurant_name' => $name,
            'branch_id'       => $branchId,
            'branch_name'     => 'Main Branch',
            'theme_color'     => '#FF6B35',
            'currency_symbol' => '₹',
        ]);

        return redirect()->to(base_url('admin/dashboard'))
            ->with('success', "Welcome to DinoviX! Your 14-day free trial of the {$plan['name']} plan has started.");
    }

    /** "My Restaurant & Bar!" → my-restaurant-bar */
    private function makeSlug(string $name): string
    {
        $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', trim($name)));
        return trim(preg_replace('/-+/', '-', $slug), '-') ?: 'restaurant';
    }
}
