<?php
namespace App\Controllers\Auth;

use App\Controllers\BaseController;

class AuthController extends BaseController
{
    public function login()
    {
        if (session()->get('user_id')) {
            return $this->redirectByRole();
        }
        return view('auth/login', ['title' => 'Login — RestoCRM']);
    }

    public function doLogin()
    {
        $rules = [
            'email'    => 'required|valid_email',
            'password' => 'required|min_length[6]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $email    = $this->request->getPost('email');
        $password = $this->request->getPost('password');

        $db   = \Config\Database::connect();
        $user = $db->table('users u')
            ->select('u.*, r.slug as role_slug, r.name as role_name, r.permissions_json,
                      res.name as restaurant_name, res.subscription_status, res.theme_color, res.currency_symbol,
                      b.name as branch_name')
            ->join('roles r', 'r.id = u.role_id')
            ->join('restaurants res', 'res.id = u.restaurant_id', 'left')
            ->join('branches b', 'b.id = u.branch_id', 'left')
            ->where('u.email', $email)
            ->where('u.is_active', 1)
            ->get()->getRowArray();

        if (!$user || !password_verify($password, $user['password'])) {
            return redirect()->back()->withInput()->with('error', 'Invalid email or password');
        }

        // Check subscription
        if ($user['restaurant_name'] && in_array($user['subscription_status'], ['suspended','cancelled','expired'])) {
            return redirect()->back()->with('error', 'Account suspended. Please contact support.');
        }

        // Update last login
        $db->table('users')->where('id', $user['id'])->update([
            'last_login_at' => date('Y-m-d H:i:s'),
            'last_login_ip' => $this->request->getIPAddress(),
        ]);

        // Set session
        session()->set([
            'user_id'         => $user['id'],
            'user_name'       => $user['name'],
            'user_email'      => $user['email'],
            'role_id'         => $user['role_id'],
            'role_slug'       => $user['role_slug'],
            'role_name'       => $user['role_name'],
            'permissions'     => json_decode($user['permissions_json'] ?? '{}', true),
            'restaurant_id'   => $user['restaurant_id'],
            'restaurant_name' => $user['restaurant_name'],
            'branch_id'       => $user['branch_id'],
            'branch_name'     => $user['branch_name'],
            'theme_color'     => $user['theme_color'] ?? '#FF6B35',
            'currency_symbol' => $user['currency_symbol'] ?? '₹',
        ]);

        // Log activity
        $db->table('activity_logs')->insert([
            'restaurant_id' => $user['restaurant_id'],
            'user_id'       => $user['id'],
            'action'        => 'user_login',
            'module'        => 'auth',
            'ip_address'    => $this->request->getIPAddress(),
            'user_agent'    => substr($this->request->getUserAgent(), 0, 255),
        ]);

        return $this->redirectByRole($user['role_slug']);
    }

    private function redirectByRole($role = null)
    {
        $role = $role ?: session()->get('role_slug');
        return match($role) {
            'super_admin'      => redirect()->to(base_url('super/dashboard')),
            'restaurant_admin' => redirect()->to(base_url('admin/dashboard')),
            'branch_manager'   => redirect()->to(base_url('admin/dashboard')),
            'cashier','waiter' => redirect()->to(base_url('pos')),
            'kitchen'          => redirect()->to(base_url('pos/kitchen')),
            default            => redirect()->to(base_url('pos')),
        };
    }

    public function logout()
    {
        $userId = session()->get('user_id');
        if ($userId) {
            \Config\Database::connect()->table('activity_logs')->insert([
                'user_id' => $userId,
                'action'  => 'user_logout',
                'module'  => 'auth',
                'ip_address' => service('request')->getIPAddress(),
            ]);
        }
        session()->destroy();
        return redirect()->to(base_url('login'))->with('success', 'Logged out successfully');
    }

    public function forgotPassword()
    {
        return view('auth/forgot_password');
    }

    public function doForgotPassword()
    {
        $email = $this->request->getPost('email');
        $db    = \Config\Database::connect();
        $user  = $db->table('users')->where('email', $email)->get()->getRowArray();

        if ($user) {
            $token   = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
            $db->table('users')->where('id', $user['id'])->update([
                'reset_token'      => $token,
                'reset_expires_at' => $expires,
            ]);
            // TODO: Send email with reset link
        }

        return redirect()->back()->with('success', 'If email exists, reset link has been sent.');
    }
}
