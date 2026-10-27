<?php

namespace App\Libraries;

/**
 * Fleet / delivery pricing settings — every number is a `settings` row
 * (keys prefixed `delivery_`) so owners can retune them any time without
 * code changes. Defaults reflect our standard pricing:
 *
 *   delivery_mode               self | dinovix | auto  (who delivers)
 *   delivery_fleet_commission_pct   8  → restaurant pays DinoviX % on fleet orders
 *   delivery_fleet_commission_min  15   minimum ₹ when the % is small
 *   delivery_fee_base             29   customer delivery fee (kept by DinoviX)
 *   delivery_fee_free_km          2.5  base fee covers this distance
 *   delivery_fee_per_km            8   ₹ per km beyond the free distance
 *   delivery_payout_base          20   fleet driver's guaranteed payout/run
 *   delivery_payout_per_km         5   ₹ per km beyond the free distance
 *   delivery_own_platform_fee      5   DinoviX fee when OWN riders deliver
 *
 * NOTE: fee_free_km drives BOTH the customer fee and the driver payout.
 */
class FleetSettings
{
    public const DEFAULTS = [
        'delivery_mode'               => 'auto',
        'delivery_fleet_commission_pct' => 8,
        'delivery_fleet_commission_min' => 15,
        'delivery_fee_base'           => 29,
        'delivery_fee_free_km'        => 2.5,
        'delivery_fee_per_km'         => 8,
        'delivery_payout_base'        => 20,
        'delivery_payout_per_km'      => 5,
        'delivery_own_platform_fee'   => 5,
    ];

    public static function get(\CodeIgniter\Database\BaseConnection $db, int $rid): array
    {
        $out = self::DEFAULTS;
        $rows = $db->table('settings')
            ->select('`key`, `value`')
            ->where('restaurant_id', $rid)
            ->like('`key`', 'delivery_', 'after')
            ->get()->getResultArray();
        foreach ($rows as $row) {
            $key = $row['key'];
            if (!array_key_exists($key, $out)) continue;
            if ($key === 'delivery_mode') {
                $out[$key] = in_array($row['value'], ['self', 'dinovix', 'auto'], true) ? $row['value'] : 'auto';
            } else {
                $out[$key] = max(0, (float)$row['value']);
            }
        }
        return $out;
    }

    /** Customer delivery fee for a fleet run of $km road-km (null km = base fee). */
    public static function customerFee(array $cfg, ?float $km): float
    {
        $fee = (float)$cfg['delivery_fee_base'];
        if ($km !== null && $km > (float)$cfg['delivery_fee_free_km']) {
            $fee += round(($km - (float)$cfg['delivery_fee_free_km']) * $cfg['delivery_fee_per_km'], 2);
        }
        return round($fee, 2);
    }

    /** Fleet driver payout for a run of $km (null km = base payout). */
    public static function driverPayout(array $cfg, ?float $km): float
    {
        $pay = (float)$cfg['delivery_payout_base'];
        if ($km !== null && $km > (float)$cfg['delivery_fee_free_km']) {
            $pay += round(($km - (float)$cfg['delivery_fee_free_km']) * $cfg['delivery_payout_per_km'], 2);
        }
        return round($pay, 2);
    }

    /** DinoviX commission the restaurant pays on a fleet order (min applies). */
    public static function fleetCommission(array $cfg, float $orderTotal): float
    {
        return round(max(
            (float)$cfg['delivery_fleet_commission_min'],
            $orderTotal * (float)$cfg['delivery_fleet_commission_pct'] / 100
        ), 2);
    }
}
