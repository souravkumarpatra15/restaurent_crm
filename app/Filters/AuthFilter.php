<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();

        // Check remember me cookie if not logged in
        if (!$session->get('user_id')) {
            helper('cookie');
            $token = get_cookie('remember_token');
            if ($token) {
                $db   = \Config\Database::connect();
                $user = $db->table('users u')
                    ->select('u.*, r.slug as role_slug, r.name as role_name,
                              res.name as restaurant_name, res.theme_color, res.currency_symbol,
                              res.subscription_status, b.name as branch_name')
                    ->join('roles r', 'r.id = u.role_id')
                    ->join('restaurants res', 'res.id = u.restaurant_id', 'left')
                    ->join('branches b', 'b.id = u.branch_id', 'left')
                    ->where('u.remember_token', $token)
                    ->where('u.is_active', 1)
                    ->get()->getRowArray();

                if ($user) {
                    $session->set([
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
                    ]);
                }
            }
        }

        if (!$session->get('user_id')) {
            return redirect()->to(base_url('login'))->with('error', 'Please login to continue');
        }

        $userRole = $session->get('role_slug');

        // Super admin can access everything except POS
        if ($userRole === 'super_admin') {
            // Block super admin from POS routes
            $uri = $request->getUri()->getPath();
            if (strpos($uri, '/pos') !== false || strpos($uri, 'pos/') === 0) {
                return redirect()->to(base_url('super/dashboard'))
                    ->with('error', 'Super Admin does not have POS access');
            }
            return; // Allow all other routes
        }

        // Check specific role requirements
        if ($arguments) {
            if (!in_array($userRole, $arguments)) {
                // Redirect to appropriate dashboard
                $dashboard = match ($userRole) {
                    'restaurant_admin', 'branch_manager' => base_url('admin/dashboard'),
                    'cashier', 'waiter'                  => base_url('pos'),
                    'kitchen'                            => base_url('pos/kitchen'),
                    default                              => base_url('login'),
                };
                return redirect()->to($dashboard)->with('error', 'Access denied');
            }
        }

        // ── Subscription gate — every tenant, every request ─────
        if ($session->get('restaurant_id')) {
            try {
                $db = \Config\Database::connect();
                $restaurant = $db->table('restaurants r')
                    ->select('r.id, r.subscription_status, r.trial_ends_at, r.subscription_ends_at,
                              p.name as plan_name, p.max_branches, p.max_users, p.max_menu_items, p.max_tables')
                    ->join('saas_plans p', 'p.id = r.plan_id', 'left')
                    ->where('r.id', $session->get('restaurant_id'))
                    ->get()->getRowArray();

                $state = $restaurant['subscription_status'] ?? 'active';
                $now   = date('Y-m-d H:i:s');

                // Fold trial expiry and renewal-date expiry into the live status
                if ($state === 'trial' && !empty($restaurant['trial_ends_at']) && $restaurant['trial_ends_at'] < $now) {
                    $state = 'expired';
                } elseif ($state === 'active' && !empty($restaurant['subscription_ends_at']) && $restaurant['subscription_ends_at'] < $now) {
                    $state = 'expired';
                }

                // Self-correct the stored row so every zone reads the truth
                if ($state !== ($restaurant['subscription_status'] ?? '')) {
                    $db->table('restaurants')->where('id', $restaurant['id'])->update(['subscription_status' => $state]);
                }

                // Publish to the session for layout banners / dashboards
                $session->set([
                    'subscription_state' => $state,
                    'plan_name'          => $restaurant['plan_name'] ?? '',
                    'trial_ends_at'      => $restaurant['trial_ends_at'],
                    'plan_max_branches'   => $restaurant['max_branches'] ?? -1,
                    'plan_max_users'      => $restaurant['max_users'] ?? -1,
                    'plan_max_menu_items' => $restaurant['max_menu_items'] ?? -1,
                    'plan_max_tables'     => $restaurant['max_tables'] ?? -1,
                ]);

                // Super admin impersonating a tenant keeps access for support
                if (!empty($session->get('super_admin_backup'))) {
                    return;
                }

                if (in_array($state, ['suspended', 'cancelled', 'expired'])) {
                    $session->destroy();
                    return redirect()->to(base_url('login'))
                        ->with('error', 'Your subscription is ' . $state . '. Please renew to continue using DinoviX.');
                }
            } catch (\Exception $e) {
            }
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null) {}
}
