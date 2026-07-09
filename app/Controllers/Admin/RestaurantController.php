<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class RestaurantController extends BaseController
{
    private function plans()
    {
        return \Config\Database::connect()->table('saas_plans')
            ->where('is_active', 1)->orderBy('sort_order', 'ASC')->get()->getResultArray();
    }

    public function index()
    {
        $db = \Config\Database::connect();
        $restaurants = $db->table('restaurants r')
            ->select('r.*, p.name as plan_name,
                      (SELECT COUNT(*) FROM branches WHERE restaurant_id=r.id) as branch_count,
                      (SELECT COUNT(*) FROM users WHERE restaurant_id=r.id AND is_active=1) as user_count')
            ->join('saas_plans p', 'p.id=r.plan_id', 'left')
            ->orderBy('r.created_at', 'DESC')
            ->get()->getResultArray();

        return view('admin/restaurants/index', [
            'pageTitle'   => 'Restaurants',
            'restaurants' => $restaurants,
            'userName'    => session('user_name'),
            'userRole'    => session('role_slug'),
        ]);
    }

    public function create()
    {
        return view('admin/restaurants/form', [
            'pageTitle'  => 'Add Restaurant',
            'restaurant' => null,
            'plans'      => $this->plans(),
            'userName'   => session('user_name'),
            'userRole'   => session('role_slug'),
        ]);
    }

    public function store()
    {
        $db   = \Config\Database::connect();
        $name = $this->request->getPost('name');
        $slug = url_title(strtolower($name), '-', true);
        // Make slug unique
        $base = $slug;
        $i = 1;
        while ($db->table('restaurants')->where('slug', $slug)->countAllResults()) {
            $slug = $base . '-' . $i++;
        }

        $data = [
            'plan_id'             => $this->request->getPost('plan_id'),
            'name'                => $name,
            'slug'                => $slug,
            'restaurant_type'     => $this->request->getPost('restaurant_type') ?? 'qsr',
            'cuisine_type'        => $this->request->getPost('cuisine_type'),
            'email'               => $this->request->getPost('email'),
            'phone'               => $this->request->getPost('phone'),
            'address'             => $this->request->getPost('address'),
            'city'                => $this->request->getPost('city'),
            'state'               => $this->request->getPost('state'),
            'pincode'             => $this->request->getPost('pincode'),
            'gst_number'          => $this->request->getPost('gst_number'),
            'fssai_number'        => $this->request->getPost('fssai_number'),
            'currency'            => 'INR',
            'currency_symbol'     => '₹',
            'timezone'            => 'Asia/Kolkata',
            'tax_type'            => $this->request->getPost('tax_type') ?? 'exclusive',
            'default_tax_percent' => $this->request->getPost('default_tax_percent') ?? 5,
            'billing_prefix'      => $this->request->getPost('billing_prefix') ?? 'INV',
            'billing_counter'     => 1,
            'kot_prefix'          => 'KOT',
            'kot_counter'         => 1,
            'subscription_status' => $this->request->getPost('subscription_status') ?? 'trial',
            'trial_ends_at'       => date('Y-m-d H:i:s', strtotime('+30 days')),
            'subscription_ends_at' => $this->request->getPost('subscription_ends_at') ?: date('Y-m-d H:i:s', strtotime('+1 year')),
            'billing_cycle'       => $this->request->getPost('billing_cycle') ?? 'monthly',
            'is_active'           => 1,
            'created_at'          => date('Y-m-d H:i:s'),
            'updated_at'          => date('Y-m-d H:i:s'),
        ];

        $db->table('restaurants')->insert($data);
        $restaurantId = $db->insertID();

        // Create default branch
        $db->table('branches')->insert([
            'restaurant_id'  => $restaurantId,
            'name'           => 'Main Branch',
            'code'           => strtoupper(substr($slug, 0, 3)) . '-MAIN',
            'branch_type'    => 'main',
            'phone'          => $this->request->getPost('phone'),
            'email'          => $this->request->getPost('email'),
            'address'        => $this->request->getPost('address'),
            'city'           => $this->request->getPost('city'),
            'has_dine_in'    => 1,
            'has_takeaway'   => 1,
            'has_delivery'   => 0,
            'billing_prefix' => $this->request->getPost('billing_prefix') ?? 'INV',
            'billing_counter' => 1,
            'kot_counter'    => 1,
            'is_active'      => 1,
            'created_at'     => date('Y-m-d H:i:s'),
            'updated_at'     => date('Y-m-d H:i:s'),
        ]);
        $branchId = $db->insertID();

        // Create admin user
        $adminPass = $this->request->getPost('admin_password') ?: 'admin@123';
        $db->table('users')->insert([
            'restaurant_id'     => $restaurantId,
            'branch_id'         => null,
            'role_id'           => 2, // restaurant_admin
            'name'              => $this->request->getPost('admin_name') ?: $name . ' Admin',
            'email'             => $this->request->getPost('email'),
            'phone'             => $this->request->getPost('phone'),
            'password'          => password_hash($adminPass, PASSWORD_BCRYPT),
            'is_active'         => 1,
            'email_verified_at' => date('Y-m-d H:i:s'),
            'created_at'        => date('Y-m-d H:i:s'),
            'updated_at'        => date('Y-m-d H:i:s'),
        ]);

        // Record initial payment if status is 'active' at creation
        if ($this->request->getPost('subscription_status') === 'active') {
            $plan = $db->table('saas_plans')->where('id',$this->request->getPost('plan_id'))->get()->getRowArray();
            if ($plan) {
                $cycle  = $this->request->getPost('billing_cycle') ?? 'monthly';
                $amount = $cycle === 'yearly' ? $plan['price_yearly'] : $plan['price_monthly'];
                $db->table('subscription_payments')->insert([
                    'restaurant_id'  => $restaurantId,
                    'plan_id'        => $plan['id'],
                    'amount'         => $amount,
                    'billing_cycle'  => $cycle,
                    'period_start'   => date('Y-m-d'),
                    'period_end'     => $this->request->getPost('subscription_ends_at') ?: date('Y-m-d',strtotime('+1 month')),
                    'status'         => 'paid',
                    'payment_method' => 'cash',
                    'paid_at'        => date('Y-m-d H:i:s'),
                    'notes'          => 'Initial payment on restaurant creation',
                    'created_at'     => date('Y-m-d H:i:s'),
                ]);
            }
        }
        return redirect()->to(base_url('super/subscriptions?new_restaurant='.$restaurantId))
            ->with('success', "Restaurant '{$name}' created. Admin: {$this->request->getPost('email')} / {$adminPass}. Set up subscription payment below.");
    }

    public function edit($id)
    {
        $db = \Config\Database::connect();
        return view('admin/restaurants/form', [
            'pageTitle'  => 'Edit Restaurant',
            'restaurant' => $db->table('restaurants')->where('id', $id)->get()->getRowArray(),
            'plans'      => $this->plans(),
            'userName'   => session('user_name'),
            'userRole'   => session('role_slug'),
        ]);
    }

    public function update($id)
    {
        $db = \Config\Database::connect();
        $db->table('restaurants')->where('id', $id)->update([
            'plan_id'             => $this->request->getPost('plan_id'),
            'name'                => $this->request->getPost('name'),
            'restaurant_type'     => $this->request->getPost('restaurant_type'),
            'email'               => $this->request->getPost('email'),
            'phone'               => $this->request->getPost('phone'),
            'address'             => $this->request->getPost('address'),
            'city'                => $this->request->getPost('city'),
            'state'               => $this->request->getPost('state'),
            'pincode'             => $this->request->getPost('pincode'),
            'gst_number'          => $this->request->getPost('gst_number'),
            'subscription_status' => $this->request->getPost('subscription_status'),
            'subscription_ends_at' => $this->request->getPost('subscription_ends_at'),
            'billing_cycle'       => $this->request->getPost('billing_cycle'),
            'is_active'           => $this->request->getPost('is_active') ?? 1,
            'updated_at'          => date('Y-m-d H:i:s'),
        ]);
        return redirect()->to(base_url('super/restaurants'))->with('success', 'Restaurant updated');
    }

    public function view($id)
    {
        $db = \Config\Database::connect();
        $restaurant = $db->table('restaurants r')
            ->select('r.*, p.name as plan_name')
            ->join('saas_plans p', 'p.id=r.plan_id', 'left')
            ->where('r.id', $id)->get()->getRowArray();

        $branches = $db->table('branches')->where('restaurant_id', $id)->get()->getResultArray();
        $users    = $db->table('users u')->select('u.*, r.name as role_name')
            ->join('roles r', 'r.id=u.role_id', 'left')
            ->where('u.restaurant_id', $id)->get()->getResultArray();
        $stats = [
            'orders'   => $db->table('orders')->where('restaurant_id', $id)->countAllResults(),
            'revenue'  => $db->table('orders')->selectSum('total_amount')->where('restaurant_id', $id)->where('payment_status', 'paid')->get()->getRowArray()['total_amount'] ?? 0,
            'customers' => $db->table('customers')->where('restaurant_id', $id)->countAllResults(),
            'menu'     => $db->table('menu_items')->where('restaurant_id', $id)->countAllResults(),
        ];

        return view('admin/restaurants/view', [
            'pageTitle'  => $restaurant['name'] ?? 'Restaurant',
            'restaurant' => $restaurant,
            'branches'   => $branches,
            'users'      => $users,
            'stats'      => $stats,
            'userName'   => session('user_name'),
            'userRole'   => session('role_slug'),
        ]);
    }

    public function toggle($id)
    {
        $db = \Config\Database::connect();
        $r  = $db->table('restaurants')->where('id', $id)->get()->getRowArray();
        $db->table('restaurants')->where('id', $id)->update(['is_active' => $r['is_active'] ? 0 : 1]);
        return $this->response->setJSON(['success' => true, 'active' => !$r['is_active']]);
    }

    public function delete($id)
    {
        // Soft delete — suspend only
        \Config\Database::connect()->table('restaurants')->where('id', $id)
            ->update(['is_active' => 0, 'subscription_status' => 'cancelled']);
        return $this->response->setJSON(['success' => true]);
    }

    public function loginAs($id)
    {
        $db    = \Config\Database::connect();
        $admin = $db->table('users u')
            ->select('u.*, r.slug as role_slug, r.name as role_name, res.name as restaurant_name,
                      res.theme_color, res.currency_symbol, b.name as branch_name')
            ->join('roles r', 'r.id=u.role_id')
            ->join('restaurants res', 'res.id=u.restaurant_id', 'left')
            ->join('branches b', 'b.id=u.branch_id', 'left')
            ->where('u.restaurant_id', $id)
            ->where('u.role_id', 2)
            ->where('u.is_active', 1)
            ->get()->getRowArray();

        if (!$admin) {
            return redirect()->back()->with('error', 'No active admin found for this restaurant');
        }

        // Save super admin session to restore later
        session()->set('super_admin_backup', [
            'user_id'    => session('user_id'),
            'role_slug'  => session('role_slug'),
            'user_name'  => session('user_name'),
        ]);

        session()->set([
            'user_id'         => $admin['id'],
            'user_name'       => $admin['name'],
            'user_email'      => $admin['email'],
            'role_id'         => $admin['role_id'],
            'role_slug'       => 'restaurant_admin',
            'role_name'       => $admin['role_name'],
            'restaurant_id'   => $id,
            'restaurant_name' => $admin['restaurant_name'],
            'branch_id'       => $admin['branch_id'],
            'branch_name'     => $admin['branch_name'],
            'theme_color'     => $admin['theme_color'] ?? '#FF6B35',
            'currency_symbol' => $admin['currency_symbol'] ?? '₹',
            'impersonating'   => true,
        ]);

        return redirect()->to(base_url('admin/dashboard'))
            ->with('success', 'Logged in as ' . $admin['name']);
    }
}
