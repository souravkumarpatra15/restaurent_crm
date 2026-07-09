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

}