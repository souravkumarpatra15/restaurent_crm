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
        // Accept either an email address OR a mobile number in the same field
        if (!$this->validate(['email' => 'required', 'password' => 'required|min_length[6]'])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $identity = trim($this->request->getPost('email'));
        $password = $this->request->getPost('password');
        $remember = $this->request->getPost('remember');

        $isValidEmail  = filter_var($identity, FILTER_VALIDATE_EMAIL);
        // Accept 10-digit numbers, +country code, spaces/dashes
        $isValidMobile = preg_match('/^[0-9+\-\s.()]{6,20}$/', $identity) && preg_match('/\d{6,}/', str_replace([' ', '-'], ['', ''], $identity));

        if (!$isValidEmail && !$isValidMobile) {
            return redirect()->back()->withInput()->with('error', 'Please enter a valid email address or mobile number');
        }

        // Normalise mobile: keep digits, strip "0"/"91" prefixes for +91 numbers
        $mobile = null;
        if ($isValidMobile) {
            $mobile = preg_replace('/\D/', '', $identity);
            if (strlen($mobile) > 10) {           // 91XXXXXXXX10 digits → drop country code
                $mobile = substr($mobile, -10);
            }
        }

        try {
            $db   = \Config\Database::connect();
            $user = $db->table('users u')
                ->select('u.*, r.slug as role_slug, r.name as role_name,
                          res.name as restaurant_name, res.theme_color, res.currency_symbol,
                          res.subscription_status, b.name as branch_name')
                ->join('roles r', 'r.id = u.role_id')
                ->join('restaurants res', 'res.id = u.restaurant_id', 'left')
                ->join('branches b', 'b.id = u.branch_id', 'left')
                ->where('u.is_active', 1)
                ->where('u.email !=', '');
            if ($isValidEmail) {
                $user->where('u.email', $identity);
            } else {
                // Match the typed number, and also its 10-digit normalised form
                $user->groupStart()
                        ->where('u.phone', $identity)
                        ->orGroupStart()
                            ->where('RIGHT(REPLACE(REPLACE(u.phone, " ", ""), "-", ""), 10)', $mobile)
                        ->groupEnd()
                     ->groupEnd();
            }
            $user = $user->get()->getRowArray();
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Database error: ' . $e->getMessage());
        }

        if (!$user || !password_verify($password, $user['password'])) {
            return redirect()->back()->withInput()->with('error', 'Invalid email/mobile or password');
        }

        // ── Subscription gate at LOGIN TIME ────────────────────────
        // The stored status can lie (a trial that already ended still says
        // 'trial' until someone loads a page). Fold the real dates in using
        // the DB clock, self-correct the row, and refuse expired accounts.
        if ($user['restaurant_name']) {
            $db2 = \Config\Database::connect();
            $sub = $db2->table('restaurants')
                ->select("subscription_status,
                          (trial_ends_at IS NOT NULL AND trial_ends_at < NOW())        AS trial_over,
                          (subscription_ends_at IS NOT NULL AND subscription_ends_at < NOW()) AS sub_over,
                          trial_ends_at, subscription_ends_at")
                ->where('id', $user['restaurant_id'])
                ->get()->getRowArray();
            if ($sub) {
                $state = $sub['subscription_status'] ?? 'active';
                if ($state === 'trial' && $sub['trial_over'])          $state = 'expired';
                elseif ($state === 'active' && $sub['sub_over'])       $state = 'expired';
                if ($state !== $sub['subscription_status']) {
                    $db2->table('restaurants')->where('id', $user['restaurant_id'])
                        ->update(['subscription_status' => $state, 'updated_at' => date('Y-m-d H:i:s')]);
                }
                if ($state === 'expired') {
                    $when = $sub['subscription_status'] === 'trial'
                        ? ($sub['trial_ends_at'] ?? null) : ($sub['subscription_ends_at'] ?? null);
                    $msg = 'Your ' . ($sub['subscription_status'] === 'trial' ? 'free trial' : 'subscription')
                         . ((int)$sub['trial_over'] === 0 && (int)$sub['sub_over'] === 0
                              ? ' is not active.'
                              : ' ended on ' . date('d M Y', strtotime((string)$when)))
                         . '. Please renew from your payment link or contact support.';
                    return redirect()->back()->with('error', $msg);
                }
                if (in_array($state, ['suspended', 'cancelled'])) {
                    return redirect()->back()->with('error', 'Account suspended. Please contact support.');
                }
            }
        }

        // If branch_id is empty, get the restaurant's main (first) branch
        if (empty($user['branch_id']) && !empty($user['restaurant_id'])) {
            $mainBranch = \Config\Database::connect()
                ->table('branches')
                ->where('restaurant_id', $user['restaurant_id'])
                ->orderBy('id', 'ASC')
                ->get()
                ->getRowArray();

            if ($mainBranch) {
                $user['branch_id'] = $mainBranch['id'];
                $user['branch_name'] = $mainBranch['name'];
            }
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
            'delivery'                             => redirect()->to(base_url('driver')),
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
