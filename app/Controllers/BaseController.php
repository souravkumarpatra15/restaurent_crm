<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 *
 * Extend this class in any new controllers:
 * ```
 *     class Home extends BaseController
 * ```
 *
 * For security, be sure to declare any new methods as protected or private.
 */
abstract class BaseController extends Controller
{
    /**
     * Route-guard map: controller class => permission key. Enforced by
     * _remap() before any method runs, so a hidden sidebar item is never
     * the only protection — the server always re-checks.
     */
    protected const CONTROLLER_PERMS = [
        \App\Controllers\Manager\MenuController::class       => 'menu.manage',
        \App\Controllers\Manager\TableController::class      => 'tables.manage',
        \App\Controllers\Manager\CustomerController::class   => 'customers.manage',
        \App\Controllers\Manager\CouponController::class     => 'coupons.manage',
        \App\Controllers\Manager\InventoryController::class  => 'inventory.manage',
        \App\Controllers\Manager\ExpenseController::class    => 'expenses.manage',
        \App\Controllers\Manager\ReservationController::class => 'reservations.manage',
        \App\Controllers\Manager\OrderController::class      => 'orders.view',
        \App\Controllers\Manager\ReportController::class     => 'reports.view',
        \App\Controllers\Manager\SettingsController::class   => 'settings.manage',
        \App\Controllers\Manager\BranchController::class     => 'branches.manage',
        \App\Controllers\Manager\UserController::class       => 'staff.manage',
        \App\Controllers\Admin\RoleController::class         => 'roles.manage',
    ];
    /**
     * Be sure to declare properties for any property fetch you initialized.
     * The creation of dynamic property is deprecated in PHP 8.2.
     */

    // protected $session;

    /**
     * Enforce the CONTROLLER_PERMS map before every method runs.
     * Controllers not in the map are untouched.
     */
    public function _remap(string $method, ...$params)
    {
        if (!method_exists($this, $method)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }
        $perm = self::CONTROLLER_PERMS[static::class] ?? null;
        if ($perm) {
            $module = ucfirst(explode('.', $perm)[0]);
            if ($g = $this->requirePermission($perm, $module . ' Management')) return $g;
        }
        return $this->{$method}(...$params);
    }

    /**
     * @return void
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Load here all helpers you want to be available in your controllers that extend BaseController.
        // Caution: Do not put the this below the parent::initController() call below.
        // $this->helpers = ['form', 'url'];

        // Caution: Do not edit this line.
        parent::initController($request, $response, $logger);

        // Preload any models, libraries, etc, here.
        // $this->session = service('session');
    }

    protected function checkPlanLimit(string $feature, int $currentCount): array
    {
        $db  = \Config\Database::connect();
        $rid = session('restaurant_id');
        if (!$rid) return ['allowed' => true];

        // Trial / renewal expiry is folded into the live status first
        $this->subscriptionState();

        $restaurant = $db->table('restaurants r')
            ->select('r.subscription_status, r.plan_id, p.max_branches, p.max_users, p.max_menu_items, p.max_tables')
            ->join('saas_plans p', 'p.id = r.plan_id', 'left')
            ->where('r.id', $rid)
            ->get()->getRowArray();

        if (!$restaurant) return ['allowed' => true];

        if (in_array($restaurant['subscription_status'] ?? '', ['suspended','cancelled','expired'])) {
            return ['allowed'=>false,'message'=>'Your subscription is '.$restaurant['subscription_status'].'. Please renew to continue.'];
        }

        $limits = [
            'branches'   => (int)($restaurant['max_branches']   ?? 999),
            'users'      => (int)($restaurant['max_users']      ?? 999),
            'menu_items' => (int)($restaurant['max_menu_items'] ?? 999),
            'tables'     => (int)($restaurant['max_tables']     ?? 999),
        ];

        if (!isset($limits[$feature])) return ['allowed' => true];

        if ($currentCount >= $limits[$feature]) {
            $label = str_replace('_',' ',$feature);
            return ['allowed'=>false,'message'=>"Your plan allows max {$limits[$feature]} {$label}. Upgrade your plan to add more."];
        }
        return ['allowed' => true];
    }

    protected function getPlanFeature(string $feature): bool
    {
        $db  = \Config\Database::connect();
        $rid = session('restaurant_id');
        if (!$rid) return true;
        $plan = $db->table('restaurants r')
            ->select('p.'.$feature)
            ->join('saas_plans p','p.id=r.plan_id','left')
            ->where('r.id',$rid)->get()->getRowArray();
        return !$plan || (bool)($plan[$feature] ?? false);
    }

    /**
     * Role permission check — server-side twin of can_access().
     * super_admin / '*' roles always pass; unknown keys deny.
     */
    protected function can(string $permission): bool
    {
        if (session('role_slug') === 'super_admin') return true;
        $perms = session('user_perms');
        if (!is_array($perms)) {
            // Session predates the permission system — recompute from role
            $db   = \Config\Database::connect();
            $role = $db->table('roles')->where('id', session('role_id'))->get()->getRowArray();
            $perms = \App\Libraries\Permissions::effective(session('role_slug'), $role['permissions_json'] ?? null);
            session()->set(['user_perms' => $perms, 'user_perms_role' => session('role_id')]);
        }
        return \App\Libraries\Permissions::allows($perms, $permission);
    }

    /**
     * Guard an action behind a role permission. Renders a friendly
     * "no access" page for GETs; JSON denial for AJAX/POSTs.
     */
    protected function requirePermission(string $permission, string $moduleName = '')
    {
        if ($this->can($permission)) return null;
        $wantsJson = $this->request->isAJAX()
            || (strcasecmp($this->request->getMethod(), 'post') === 0 && str_starts_with($this->request->getUri()->getPath(), 'pos/'));
        if ($wantsJson) {
            return $this->response->setStatusCode(403)->setJSON([
                'success' => false,
                'no_access' => true,
                'message' => "You don't have permission to use {$moduleName} — ask your admin for access",
            ]);
        }
        return $this->response->setBody(view('no_access', [
            'moduleName' => $moduleName ?: ucwords(str_replace(['.', '_'], ' ', $permission)),
            'backUrl'    => base_url(),
        ]));
    }

    /**
     * Render the subscription upsell page for a plan-gated module.
     * Modules stay shipped for everyone; the plan decides who sees them.
     */
    protected function featureLocked(string $featureName, string $icon = 'fa-lock', string $blurb = '', string $backUrl = '')
    {
        $st = $this->subscriptionState();
        return $this->response->setBody(view('feature_locked', [
            'featureName'  => $featureName,
            'featureIcon'  => $icon,
            'featureBlurb' => $blurb,
            'planName'     => $st['plan'] ?? '',
            'backUrl'      => $backUrl ?: base_url(),
        ]));
    }

    // ── Effective subscription status for the current tenant ──
    // Folds trial expiry and renewal-date expiry into the stored
    // status, self-correcting the row so every zone sees the truth.
    // Returns ['state', 'plan', 'trial_ends_at', 'subscription_ends_at', 'days_left'].
    protected function subscriptionState(): array
    {
        $db  = \Config\Database::connect();
        $rid = session('restaurant_id');
        if (!$rid) return ['state' => 'active', 'plan' => '', 'trial_ends_at' => null, 'subscription_ends_at' => null, 'days_left' => null];

        $r = $db->table('restaurants r')
            ->select("r.subscription_status, r.trial_ends_at, r.subscription_ends_at, p.name as plan_name,
                      COALESCE(CASE WHEN r.subscription_status = 'trial'
                                    THEN DATEDIFF(r.trial_ends_at, CURDATE())
                                    ELSE DATEDIFF(r.subscription_ends_at, CURDATE()) END, 9999) AS days_left")
            ->join('saas_plans p', 'p.id = r.plan_id', 'left')
            ->where('r.id', $rid)->get()->getRowArray();

        $state = $r['subscription_status'] ?? 'active';
        $now   = date('Y-m-d H:i:s');

        if ($state === 'trial' && !empty($r['trial_ends_at']) && $r['trial_ends_at'] < $now) {
            $state = 'expired';
        } elseif ($state === 'active' && !empty($r['subscription_ends_at']) && $r['subscription_ends_at'] < $now) {
            $state = 'expired';
        }

        // Self-correct the stored row so every later zone reads the truth
        if ($state !== ($r['subscription_status'] ?? '')) {
            $db->table('restaurants')->where('id', $rid)->update(['subscription_status' => $state]);
        }

        // Anchor must match the LIVE state: trial watches the trial date,
        // paid tenants watch subscription_ends_at (trial_ends_at is history
        // and never moves on renewal). Days come from the DB clock: the
        // DATEDIFF in the select is positive for a future anchor.
        $anchor = $state === 'trial' ? ($r['trial_ends_at'] ?? null) : ($r['subscription_ends_at'] ?? null);
        $daysLeft = ($r['days_left'] !== null && $r['days_left'] !== '' && (int)$r['days_left'] !== 9999)
            ? max(0, (int)$r['days_left']) : null;

        return [
            'state'                => $state,
            'plan'                 => $r['plan_name'] ?? '',
            'trial_ends_at'        => $r['trial_ends_at'] ?? null,
            'subscription_ends_at' => $r['subscription_ends_at'] ?? null,
            'sub_anchor_at'        => $anchor,
            'days_left'            => $daysLeft,
        ];
    }

}