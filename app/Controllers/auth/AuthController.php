<?php

namespace App\Controllers\Auth;

use App\Controllers\BaseController;
use App\Libraries\Mail;

class AuthController extends BaseController
{
    public function login()
    {
        if (session()->get('user_id')) return $this->redirectByRole();
        return view('auth/login', ['title' => 'Login']);
    }

    public function doLogin()
    {
        if (!$this->validate(['email' => 'required|valid_email', 'password' => 'required|min_length[6]'])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $email    = $this->request->getPost('email');
        $password = $this->request->getPost('password');
        $remember = $this->request->getPost('remember');

        try {
            $db   = \Config\Database::connect();
            $user = $db->table('users u')
                ->select('u.*, r.slug as role_slug, r.name as role_name,
                          res.name as restaurant_name, res.theme_color, res.currency_symbol,
                          res.subscription_status, b.name as branch_name')
                ->join('roles r', 'r.id = u.role_id')
                ->join('restaurants res', 'res.id = u.restaurant_id', 'left')
                ->join('branches b', 'b.id = u.branch_id', 'left')
                ->where('u.email', $email)
                ->where('u.is_active', 1)
                ->get()->getRowArray();
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Database error: ' . $e->getMessage());
        }

        if (!$user || !password_verify($password, $user['password'])) {
            return redirect()->back()->withInput()->with('error', 'Invalid email or password');
        }

        if ($user['restaurant_name'] && in_array($user['subscription_status'] ?? '', ['suspended', 'cancelled', 'expired'])) {
            return redirect()->back()->with('error', 'Account suspended. Please contact support.');
        }

        // Set session
        $sessionData = [
            'user_id'         => $user['id'],
            'user_name'       => $user['name'],
            'user_email'      => $user['email'],
            'role_id'         => $user['role_id'],
            'role_slug'       => $user['role_slug'],
            'role_name'       => $user['role_name'],
            'restaurant_id'   => $user['restaurant_id'],
            'restaurant_name' => $user['restaurant_name'],
            'branch_id'       => $user['branch_id'],
            'branch_name'     => $user['branch_name'],
            'theme_color'     => $user['theme_color'] ?? '#FF6B35',
            'currency_symbol' => $user['currency_symbol'] ?? '₹',
        ];
        session()->set($sessionData);

        // Remember Me — set cookie for 30 days
        if ($remember) {
            $token = bin2hex(random_bytes(32));
            \Config\Database::connect()->table('users')->where('id', $user['id'])
                ->update(['remember_token' => $token]);
            $this->response->setCookie('remember_token', $token, 30 * 24 * 3600);
        }

        // Update last login
        \Config\Database::connect()->table('users')->where('id', $user['id'])->update([
            'last_login_at' => date('Y-m-d H:i:s'),
            'last_login_ip' => $this->request->getIPAddress(),
        ]);

        return $this->redirectByRole($user['role_slug']);
    }

    private function redirectByRole($role = null)
    {
        $role = $role ?: session()->get('role_slug');
        return match ($role) {
            'super_admin'                          => redirect()->to(base_url('super/dashboard')),
            'restaurant_admin', 'branch_manager'   => redirect()->to(base_url('admin/dashboard')),
            'cashier', 'waiter'                    => redirect()->to(base_url('pos')),
            'kitchen'                              => redirect()->to(base_url('pos/kitchen')),
            default                                => redirect()->to(base_url('pos')),
        };
    }

    public function logout()
    {
        // Clear remember cookie
        $this->response->deleteCookie('remember_token');
        session()->destroy();
        return redirect()->to(base_url('login'))->with('success', 'Logged out successfully');
    }

    public function forgotPassword()
    {
        return view('auth/forgot_password');
    }

    public function doForgotPassword()
    {
        if (!$this->validate(['email' => 'required|valid_email'])) {
            return redirect()->back()->withInput()->with('error', 'Please enter a valid email');
        }

        $email = $this->request->getPost('email');
        $db    = \Config\Database::connect();
        $user  = $db->table('users')->where('email', $email)->where('is_active', 1)->get()->getRowArray();

        if ($user) {
            $token   = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
            $db->table('users')->where('id', $user['id'])->update([
                'reset_token'      => $token,
                'reset_expires_at' => $expires,
            ]);
            $resetLink = base_url('reset-password/' . $token);
            $sent      = Mail::sendPasswordReset($email, $user['name'], $resetLink);
            log_message('info', 'Password reset token for ' . $email . ': ' . $token);
        }

        return redirect()->to(base_url('login'))
            ->with('success', 'If that email exists, a reset link has been sent.');
    }

    public function resetPassword($token)
    {
        $db   = \Config\Database::connect();
        $user = $db->table('users')
            ->where('reset_token', $token)
            ->where('reset_expires_at >', date('Y-m-d H:i:s'))
            ->get()->getRowArray();

        if (!$user) {
            return redirect()->to(base_url('login'))->with('error', 'Reset link is invalid or expired.');
        }
        return view('auth/reset_password', ['token' => $token]);
    }

    public function doResetPassword()
    {
        $token    = $this->request->getPost('token');
        $password = $this->request->getPost('password');
        $confirm  = $this->request->getPost('confirm_password');

        if ($password !== $confirm || strlen($password) < 6) {
            return redirect()->back()->with('error', 'Passwords do not match or are too short.');
        }

        $db   = \Config\Database::connect();
        $user = $db->table('users')
            ->where('reset_token', $token)
            ->where('reset_expires_at >', date('Y-m-d H:i:s'))
            ->get()->getRowArray();

        if (!$user) {
            return redirect()->to(base_url('login'))->with('error', 'Reset link is invalid or expired.');
        }

        $db->table('users')->where('id', $user['id'])->update([
            'password'         => password_hash($password, PASSWORD_BCRYPT),
            'reset_token'      => null,
            'reset_expires_at' => null,
        ]);

        return redirect()->to(base_url('login'))->with('success', 'Password changed successfully. Please login.');
    }


    public function backToSuper()
    {
        $backup = session()->get('super_admin_backup');
        if (!$backup) return redirect()->to(base_url('login'));

        // Restore super admin session
        session()->set([
            'user_id'       => $backup['user_id'],
            'role_slug'     => $backup['role_slug'],
            'user_name'     => $backup['user_name'],
            'restaurant_id' => null,
            'branch_id'     => null,
            'impersonating' => false,
        ]);
        session()->remove('super_admin_backup');
        return redirect()->to(base_url('super/restaurants'))->with('success', 'Returned to Super Admin');
    }
}
