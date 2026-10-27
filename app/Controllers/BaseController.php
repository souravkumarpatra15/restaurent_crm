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
     * Be sure to declare properties for any property fetch you initialized.
     * The creation of dynamic property is deprecated in PHP 8.2.
     */

    // protected $session;

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