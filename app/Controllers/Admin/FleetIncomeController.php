<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\FleetSettings;

/**
 * DinoviX-PLATFORM INCOME (super admin).
 *
 * Subscription money lands in subscription_payments (Revenue page).
 * The OTHER income stream is delivery: every driver delivery books a
 * row in `dinovix_fees`:
 *   fleet_commission  — 8% (min ₹15) we keep on DinoviX-fleet runs
 *   delivery_fee      — the ₹29 + ₹8/km customer fee we collected on fleet runs
 *   own_platform_fee  — flat ₹5 on restaurants' OWN-rider deliveries
 * This page is the fintech view over that ledger.
 */
class FleetIncomeController extends BaseController
{
    public function index()
    {
        $db   = \Config\Database::connect();
        $when = $this->request->getGet('when') ?? 'all';
        $ridF = (int)($this->request->getGet('r') ?? 0);

        // ── period window (DB clock, never PHP time) ──
        $windows = [
            'today'  => 'f.created_at >= CURDATE()',
            'month'  => "f.created_at >= DATE_FORMAT(CURDATE(), '%Y-%m-01')",
            'all'    => '1=1',
        ];
        $where = $windows[$when] ?? $windows['all'];
        if ($ridF) $where .= ' AND f.restaurant_id = ' . $ridF;

        // ── headline totals split by fee stream ──
        $byType = $db->query("SELECT f.fee_type, SUM(f.amount) total, COUNT(*) cnt
                              FROM dinovix_fees f WHERE $where
                              GROUP BY f.fee_type")->getResultArray();
        $totals = ['fleet_commission' => 0, 'delivery_fee' => 0, 'own_platform_fee' => 0];
        $counts = $totals;
        foreach ($byType as $t) {
            $totals[$t['fee_type']] = (float)$t['total'];
            $counts[$t['fee_type']] = (int)$t['cnt'];
        }

        // ── per-restaurant earnings ──
        $byRest = [];
        foreach ($db->query("SELECT f.restaurant_id, r.name,
                                SUM(f.amount) total,
                                SUM(CASE WHEN f.fee_type='fleet_commission' THEN f.amount END) fleet,
                                SUM(CASE WHEN f.fee_type='delivery_fee' THEN f.amount END) dfee,
                                SUM(CASE WHEN f.fee_type='own_platform_fee' THEN f.amount END) own,
                                COUNT(DISTINCT f.order_id) runs
                              FROM dinovix_fees f
                              JOIN restaurants r ON r.id = f.restaurant_id
                              WHERE $where
                              GROUP BY f.restaurant_id, r.name
                              ORDER BY total DESC")->getResultArray() as $row) {
            $row['cfg'] = null;
            try { $row['cfg'] = FleetSettings::get($db, (int)$row['restaurant_id']); } catch (\Throwable $e) {}
            $byRest[] = $row;
        }

        // ── last 6 months trend (all restaurants, by month) ──
        $series = [];
        foreach ($db->query("SELECT DATE_FORMAT(f.created_at,'%Y-%m') ym, SUM(f.amount) amt
                             FROM dinovix_fees f
                             WHERE f.created_at >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
                             GROUP BY ym ORDER BY ym")->getResultArray() as $s) {
            $series[ $s['ym'] ] = (float)$s['amt'];
        }
        $trend = [];
        for ($i = 5; $i >= 0; $i--) {
            $ym = date('Y-m', strtotime("-$i months"));
            $trend[] = ['label' => date('M', strtotime($ym)), 'amount' => $series[$ym] ?? 0.0];
        }

        // ── latest ledger rows ──
        $recent = $db->table('dinovix_fees f')
            ->select("f.id, f.fee_type, f.amount, f.created_at,
                      o.order_number, o.delivery_km, r.name AS restaurant,
                      u.name AS driver")
            ->join('orders o', 'o.id = f.order_id', 'left')
            ->join('restaurants r', 'r.id = f.restaurant_id', 'left')
            ->join('users u', 'u.id = o.rider_id', 'left')
            ->where($where, null, false)
            ->orderBy('f.id', 'DESC')->limit(40)->get()->getResultArray();

        $restaurants = $db->table('restaurants')->select('id, name')->orderBy('name')->get()->getResultArray();

        return view('admin/dashboard/fleet_income', [
            'pageTitle'   => 'DinoviX Income',
            'totals'      => $totals,
            'counts'      => $counts,
            'grand'       => array_sum($totals),
            'byRest'      => $byRest,
            'trend'       => $trend,
            'recent'      => $recent,
            'restaurants' => $restaurants,
            'when'        => $when,
            'ridFilter'   => $ridF,
            'userName'    => session('user_name'),
            'userRole'    => session('role_slug'),
        ]);
    }
}
