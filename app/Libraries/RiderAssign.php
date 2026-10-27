<?php

namespace App\Libraries;

/**
 * Auto-assignment of drivers to delivery orders.
 *
 * Decides WHO rides (restaurant's own staff vs the DinoviX fleet from the
 * restaurant's delivery_mode setting), picks the nearest FREE rider, works
 * out the distance the pricing is computed on, and notifies the driver.
 *
 * Distance stack (all free, NO API keys):
 *   1. Nominatim (OpenStreetMap) geocode of the delivery address
 *   2. OSRM route (project-osrm.org) for real road km
 *   3. Haversine straight-line fallback
 *   Anything that fails just degrades — km stays null, fees use base amounts.
 */
class RiderAssign
{
    /**
     * Assign a rider to a delivery order automatically (never throws).
     * Returns [bool assigned, string message].
     */
    public static function autoAssign($db, int $orderId): array
    {
        try {
            return self::assign($db, $orderId);
        } catch (\Throwable $e) {
            return [false, 'Auto-assign skipped: ' . $e->getMessage()];
        }
    }

    private static function assign($db, int $orderId): array
    {
        $order = $db->table('orders')->where('id', $orderId)->get()->getRowArray();
        if (!$order || $order['order_type'] !== 'delivery') {
            return [false, 'not a delivery order'];
        }
        if ($order['delivery_status'] && !in_array($order['delivery_status'], ['unassigned'], true)) {
            return [false, 'already assigned'];
        }

        $rid = (int)$order['restaurant_id'];
        $cfg = FleetSettings::get($db, $rid);

        // Distance first — you cannot price or pick nearest without it
        [$lat, $lng, $km] = self::distance($db, $order);
        if ($lat !== null) {
            $db->table('orders')->where('id', $orderId)->update([
                'delivery_lat' => $lat, 'delivery_lng' => $lng, 'delivery_km' => $km,
            ]);
        }

        // Which employer pools may we draw from, in preference order
        $pools = $cfg['delivery_mode'] === 'self'
            ? ['restaurant']
            : ($cfg['delivery_mode'] === 'dinovix' ? ['dinovix'] : ['restaurant', 'dinovix']);

        // All active riders of this restaurant with their "busy" run count
        $riders = $db->table('riders r')
            ->select("r.employer_type, u.id, u.name,
                (SELECT COUNT(*) FROM orders o2
                  WHERE o2.rider_id = u.id
                    AND o2.delivery_status IN ('assigned','out_for_delivery')) AS busy")
            ->join('users u', 'u.id = r.user_id')
            ->where('r.restaurant_id', $rid)
            ->where('r.is_active', 1)
            ->where('u.restaurant_id', $rid)
            ->where('u.is_active', 1)
            ->get()->getResultArray();
        if (!$riders) {
            // keep board usable even before profiles exist: fall back to
            // delivery-role staff without a rider profile
            $riders = $db->table('users u')
                ->select("'restaurant' AS employer_type, u.id, u.name, 0 AS busy")
                ->join('roles r', 'r.id = u.role_id')
                ->where('u.restaurant_id', $rid)->where('u.is_active', 1)
                ->where('r.slug', 'delivery')
                ->get()->getResultArray();
        }

        // Fresh GPS for nearest-first picking
        $gps = [];
        foreach ($db->table('rider_locations')->whereIn('user_id', array_column($riders, 'id'))->get()->getResultArray() as $g) {
            $gps[$g['user_id']] = $g;
        }

        foreach ($pools as $pool) {
            $candidates = array_values(array_filter($riders, fn($r) =>
                ($r['employer_type'] ?? 'restaurant') === $pool && (int)$r['busy'] === 0
            ));
            if (!$candidates) continue;

            // nearest first when we know where the food and the rider are
            usort($candidates, function ($a, $b) use ($gps, $lat, $lng) {
                $da = self::straightKm($lat, $lng, $gps[$a['id']]['lat'] ?? null, $gps[$a['id']]['lng'] ?? null);
                $dbb = self::straightKm($lat, $lng, $gps[$b['id']]['lat'] ?? null, $gps[$b['id']]['lng'] ?? null);
                return ($da ?? 1e9) <=> ($dbb ?? 1e9);
            });

            $picked = $candidates[0];
            $db->table('orders')->where('id', $orderId)->update([
                'rider_id'        => (int)$picked['id'],
                'rider_name'      => $picked['name'],
                'delivery_status' => 'assigned',
                'delivery_mode'   => $pool,
                'updated_at'      => date('Y-m-d H:i:s'),
            ]);
            self::notifyRider($db, $rid, (int)$picked['id'], $order, $km, $pool === 'dinovix');
            $bill = ' (≈' . number_format((float)$km, 1) . ' km)' . ($km !== null ? '' : '');
            return [true, $picked['name'] . $bill];
        }

        return [false, 'no free rider'];
    }

    /** Notify a driver (also lands in the staff notification bell). */
    public static function notifyRider($db, int $rid, int $riderId, array $order, ?float $km, bool $fleet = false): void
    {
        $cod   = $order['payment_status'] !== 'paid' && (float)($order['balance_amount'] ?? 0) > 0;
        $msgParts = [];
        $msgParts[] = '#' . $order['order_number'];
        $msgParts[] = '₹' . number_format((float)$order['total_amount'], 0) . ($cod ? ' COD ₹' . number_format((float)$order['balance_amount'], 0) . ' to collect' : ' prepaid');
        if ($km !== null) $msgParts[] = '≈' . number_format((float)$km, 1) . ' km';
        $msgParts[] = 'for ' . ($order['customer_name'] ?: 'customer');
        if ($fleet) $msgParts[] = 'DinoviX fleet run';

        $db->table('notifications')->insert([
            'restaurant_id' => $rid,
            'user_id'       => $riderId,
            'type'          => 'delivery_assigned',
            'title'         => '🛵 New delivery assigned',
            'message'       => implode(' · ', $msgParts),
            'data_json'     => json_encode(['order_id' => (int)$order['id'], 'km' => $km, 'fleet' => $fleet]),
            'is_read'       => 0,
            'created_at'    => date('Y-m-d H:i:s'),
        ]);
    }

    // ── Distance helpers ────────────────────────────────────────────
    private static function distance($db, array $order): array
    {
        $addr = trim((string)($order['delivery_address'] ?? ''));
        $lat = $order['delivery_lat'] ?: null;
        $lng = $order['delivery_lng'] ?: null;

        $branch = $db->table('branches')
            ->select('latitude, longitude')
            ->where('id', (int)$order['branch_id'])->get()->getRowArray();
        $bLat = $branch && $branch['latitude']  ? (float)$branch['latitude']  : null;
        $bLng = $branch && $branch['longitude'] ? (float)$branch['longitude'] : null;

        if (!$lat && $addr && $bLat !== null) {
            $g = self::geocode($addr);
            if ($g) { $lat = $g[0]; $lng = $g[1]; }
        }
        if ($bLat === null || $lat === null) {
            return [null, null, null];
        }

        $km = null;
        $road = self::roadKm($bLng, $bLat, $lng, $lat);
        if ($road !== null) {
            $km = $road;
        } else {
            $km = round(self::straightKm($bLat, $bLng, $lat, $lng), 2);
        }
        return [(float)$lat, (float)$lng, $km];
    }

    /** Nominatim geocode — free, no key. Returns [lat, lng] or null. */
    private static function geocode(string $addr): ?array
    {
        try {
            $ctx = stream_context_create(['http' => [
                'method' => 'GET',
                'header' => "User-Agent: DinoviX-CRM/1.0 (delivery dispatch)\r\n",
                'timeout' => 4,
            ]]);
            $raw = @file_get_contents(
                'https://nominatim.openstreetmap.org/search?format=json&limit=1&q=' . urlencode($addr),
                false, $ctx
            );
            $j = json_decode((string)$raw, true);
            if (!empty($j[0]['lat']) && !empty($j[0]['lon'])) {
                return [(float)$j[0]['lat'], (float)$j[0]['lon']];
            }
        } catch (\Throwable $e) {
        }
        return null;
    }

    /** OSRM road distance in km — free, no key. Falls back to null fast. */
    private static function roadKm($lon1, $lat1, $lon2, $lat2): ?float
    {
        try {
            $ctx = stream_context_create(['http' => ['timeout' => 4, 'header' => "User-Agent: DinoviX-CRM/1.0\r\n"]]);
            $raw = @file_get_contents(
                "https://router.project-osrm.org/route/v1/driving/{$lon1},{$lat1};{$lon2},{$lat2}?overview=false",
                false, $ctx
            );
            $j = json_decode((string)$raw, true);
            if ((($j['code'] ?? '') === 'Ok') && isset($j['routes'][0]['distance'])) {
                return round($j['routes'][0]['distance'] / 1000, 2);
            }
        } catch (\Throwable $e) {
        }
        return null;
    }

    public static function straightKm($lat1, $lng1, $lat2, $lng2): ?float
    {
        if ($lat1 === null || $lng1 === null || $lat2 === null || $lng2 === null) return null;
        $r = 6371.0;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;
        return round(2 * $r * asin(min(1.0, sqrt($a))), 2);
    }
}
