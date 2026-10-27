<?php

namespace App\Controllers\Staff;

use App\Controllers\BaseController;

/**
 * Driver app — the rider's phone pocket.
 *
 * One screen: my assigned deliveries with big touch targets — pickup,
 * hand over, mark delivered (with COD cash collection through the
 * shared OrderFinalize finish-line), or failed with a reason. Riders
 * ping their GPS location while on duty so the delivery board can see
 * where each rider last was.
 *
 * Role "delivery" (Delivery Staff) IS the rider; managers/admins get
 * the same screen for testing and live support.
 */
class DriverAppController extends BaseController
{
    // ── My deliveries (driver home) ───────────────────────────
    public function home()
    {
        $db   = \Config\Database::connect();
        $rid  = session('restaurant_id');
        $bid  = session('branch_id');
        $uid  = session('user_id');

        $rows = $db->table('orders o')
            ->select("o.id, o.order_number, o.status, o.customer_name, o.customer_phone,
                      o.delivery_address, o.delivery_status, o.total_amount, o.balance_amount,
                      o.payment_status, o.notes, o.estimated_mins,
                      o.created_at, o.delivered_at,
                      TIMESTAMPDIFF(MINUTE, o.created_at, NOW()) AS age_mins,
                      (SELECT COALESCE(SUM(LOWER(oi.status) = 'ready'), 0)
                         FROM order_items oi WHERE oi.order_id = o.id AND oi.status != 'cancelled') AS ready_items,
                      (SELECT COUNT(*) FROM order_items oi2 WHERE oi2.order_id = o.id AND oi2.status != 'cancelled') AS total_items")
            ->where('o.restaurant_id', $rid)
            ->where('o.branch_id', $bid)
            ->where('o.rider_id', $uid)
            ->where('o.order_type', 'delivery')
            ->get()->getResultArray();

        $active  = array_values(array_filter($rows, fn($o) => in_array($o['delivery_status'] ?: 'unassigned', ['assigned', 'out_for_delivery'], true)));
        $closed  = array_values(array_filter($rows, fn($o) => in_array($o['delivery_status'] ?: '', ['delivered', 'failed'], true)));
        usort($active, fn($a, $b) => strcmp($a['created_at'], $b['created_at'])); // oldest first — deliver in order
        usort($closed, fn($a, $b) => strcmp(($b['delivered_at'] ?? $b['created_at']), ($a['delivered_at'] ?? $a['created_at'])));

        $stats = [
            'active'    => count($active),
            'out_now'   => count(array_filter($active, fn($o) => $o['delivery_status'] === 'out_for_delivery')),
            'delivered' => count(array_filter($closed, fn($o) => $o['delivery_status'] === 'delivered' && ($o['delivered_at'] ?? '') >= date('Y-m-d 00:00:00'))),
            'cod_due'   => array_sum(array_map(fn($o) => (float)$o['balance_amount'], array_filter($active, fn($o) => $o['payment_status'] !== 'paid'))),
        ];

        // My wallet: commissions payable to me + COD cash I am holding
        $wallet = $db->query(
            "SELECT COALESCE(SUM(cod_delta), 0) AS cod_in_hand,
                    COALESCE(SUM(earn_delta), 0) AS payable
             FROM rider_wallet_txns WHERE rider_user_id = ?",
            [$uid]
        )->getRowArray();
        $profile = $db->table('riders')->where('user_id', $uid)->get()->getRowArray();
        $duty = $db->table('rider_locations')->select('duty_on')->where('user_id', $uid)->get()->getRowArray();

        return view('staff/mobile/driver', [
            'pageTitle' => 'My Deliveries',
            'active'    => $active,
            'closed'    => array_slice($closed, 0, 8),
            'stats'     => $stats,
            'wallet'    => $wallet,
            'profile'   => $profile,
            'dutyOn'    => (int)($duty['duty_on'] ?? 0) === 1,
        ]);
    }

    // ── Driver actions on a delivery ─────────────────────────
    public function act()
    {
        $db = \Config\Database::connect();
        $rid = session('restaurant_id');
        $bid = session('branch_id');
        $uid = (int)session('user_id');

        $orderId = (int)$this->request->getPost('order_id');
        $action  = $this->request->getPost('action');
        $notes   = trim((string)$this->request->getPost('notes'));
        $collect = (float)$this->request->getPost('collect'); // COD cash handed to restaurant

        $order = $db->table('orders')
            ->where('id', $orderId)->where('restaurant_id', $rid)
            ->where('branch_id', $bid)
            ->where('order_type', 'delivery')
            ->get()->getRowArray();
        if (!$order) {
            return $this->response->setJSON(['success' => false, 'message' => 'Delivery order not found']);
        }

        // Riders act only on their own runs (managers can correct any)
        if (session('role_slug') === 'delivery' && (int)$order['rider_id'] !== $uid) {
            return $this->response->setJSON(['success' => false, 'message' => 'That delivery is not assigned to you']);
        }

        $cur = $order['delivery_status'] ?: 'unassigned';

        switch ($action) {
            case 'pickup':
                if (!in_array($cur, ['assigned', 'out_for_delivery'], true)) {
                    return $this->response->setJSON(['success' => false, 'message' => 'This delivery is ' . $cur]);
                }
                $update = ['delivery_status' => 'out_for_delivery', 'updated_at' => date('Y-m-d H:i:s')];
                break;

            case 'deliver':
                if (!in_array($cur, ['assigned', 'out_for_delivery'], true)) {
                    return $this->response->setJSON(['success' => false, 'message' => 'This delivery is already ' . $cur]);
                }
                $update = [
                    'delivery_status' => 'delivered',
                    'delivered_at'    => date('Y-m-d H:i:s'),
                    'updated_at'      => date('Y-m-d H:i:s'),
                ];

                // COD: cash collected at the door completes the order through
                // the same finish-line as counter checkout (invoice, table,
                // loyalty). Only the outstanding part may be collected.
                if ($collect > 0 && $order['payment_status'] !== 'paid') {
                    $db->transBegin();
                    $money = [
                        'paid_amount'     => $collect,
                        'balance_amount'  => max(0, (float)$order['balance_amount'] - $collect),
                        'payment_status'  => ((float)$order['balance_amount'] - $collect) <= 0.01 ? 'paid' : 'partial',
                    ];
                    \App\Libraries\OrderFinalize::complete($db, $orderId, $money);
                    $this->recordCashPayment($db, $order, $collect);
                    if ($db->transStatus() === false) {
                        $db->transRollback();
                        return $this->response->setJSON(['success' => false, 'message' => 'Could not record the cash payment — try again']);
                    }
                    $db->transCommit();
                    $update = array_merge($update, $money);
                } elseif ($collect <= 0 && $order['payment_status'] === 'paid') {
                    // prepaid delivery — nothing to collect
                }
                break;

            case 'fail':
                if (in_array($cur, ['delivered', 'failed'], true)) {
                    return $this->response->setJSON(['success' => false, 'message' => 'This delivery is already closed']);
                }
                $update = [
                    'delivery_status' => 'failed',
                    'updated_at'       => date('Y-m-d H:i:s'),
                    'notes'            => trim(($order['notes'] ? $order['notes'] . "\n" : '') . 'Delivery failed: ' . ($notes ?: 'no reason given')),
                ];
                break;

            default:
                return $this->response->setJSON(['success' => false, 'message' => 'Unknown action']);
        }

        $db->table('orders')->where('id', $orderId)->update($update);

        // ── Driver wallet events (money ledger) ───────────────
        $commission = 0.0;
        $isFleet    = false;
        if ($action === 'deliver') {
            // COD cash the rider is now holding
            if ($collect > 0 && $order['payment_status'] !== 'paid') {
                $this->walletTxn($db, [
                    'rider_user_id' => (int)$order['rider_id'],
                    'restaurant_id' => (int)$order['restaurant_id'],
                    'branch_id'     => (int)$order['branch_id'],
                    'order_id'      => (int)$order['id'],
                    'txn_type'      => 'cod_collect',
                    'cod_delta'     => $collect,
                    'earn_delta'    => 0,
                    'notes'         => 'COD cash collected on ' . $order['order_number'],
                    'created_by'    => $uid,
                ]);
            }

            // Commission for this run — split depends on WHO delivers.
            // Own rider: restaurant's rule + DinoviX ₹/order platform fee.
            // DinoviX fleet rider: DinoviX pays the payout per run, and the
            // platform books the restaurant's commission + delivery fee.
            $profile = $db->table('riders')->where('user_id', (int)$order['rider_id'])
                ->where('is_active', 1)->get()->getRowArray();
            $isFleet = ($order['delivery_mode'] ?? 'self') === 'dinovix'
                || ($profile && ($profile['employer_type'] ?? 'restaurant') === 'dinovix');
            $km = $order['delivery_km'] !== null ? (float)$order['delivery_km'] : null;

            if ($profile) {
                if ($isFleet) {
                    $cfg = \App\Libraries\FleetSettings::get($db, (int)$order['restaurant_id']);
                    $commission = \App\Libraries\FleetSettings::driverPayout($cfg, $km);
                    if ($commission > 0) {
                        $this->walletTxn($db, [
                            'rider_user_id' => (int)$order['rider_id'],
                            'restaurant_id' => (int)$order['restaurant_id'],
                            'branch_id'     => (int)$order['branch_id'],
                            'order_id'      => (int)$order['id'],
                            'txn_type'      => 'commission',
                            'cod_delta'     => 0,
                            'earn_delta'    => $commission,
                            'notes'         => 'Fleet payout — ' . $order['order_number'] . ' (base ₹' . number_format($cfg['delivery_payout_base'], 0)
                                             . ($km !== null ? ' + ₹' . number_format($cfg['delivery_payout_per_km'], 0) . '/km over ' . number_format($cfg['delivery_fee_free_km'], 1) . ' km' : ' (no distance data)') . ')',
                            'created_by'    => null,
                        ]);
                    }
                    // DinoviX platform revenue for this order
                    $this->platformFee($db, $order, 'fleet_commission',
                        \App\Libraries\FleetSettings::fleetCommission($cfg, (float)$order['total_amount']));
                    $this->platformFee($db, $order, 'delivery_fee', (float)$order['delivery_charge']);
                } else {
                    $commission = $profile['commission_type'] === 'percent'
                        ? round(((float)$order['total_amount'] * (float)$profile['commission_value']) / 100, 2)
                        : (float)$profile['commission_value'];
                    if ($commission > 0) {
                        $this->walletTxn($db, [
                            'rider_user_id' => (int)$order['rider_id'],
                            'restaurant_id' => (int)$order['restaurant_id'],
                            'branch_id'     => (int)$order['branch_id'],
                            'order_id'      => (int)$order['id'],
                            'txn_type'      => 'commission',
                            'cod_delta'     => 0,
                            'earn_delta'    => $commission,
                            'notes'         => 'Commission — ' . $order['order_number'] . ' (' . $profile['commission_type'] . ')',
                            'created_by'    => null,
                        ]);
                    }
                    $cfgOwn = \App\Libraries\FleetSettings::get($db, (int)$order['restaurant_id']);
                    if ((float)$cfgOwn['delivery_own_platform_fee'] > 0) {
                        $this->platformFee($db, $order, 'own_platform_fee', (float)$cfgOwn['delivery_own_platform_fee']);
                    }
                }
            }
        }

        $msg = match ($action) {
            'pickup'  => 'On the way — marked out for delivery',
            'deliver' => ($collect > 0 && $order['payment_status'] !== 'paid'
                          ? 'Delivered · collected ₹' . number_format($collect, 2)
                          : 'Delivered')
                         . ($commission > 0 ? ' · +' . ($isFleet ? '₹' . number_format($commission, 2) . ' payout' : '₹' . number_format($commission, 2) . ' commission') : ''),
            'fail'    => 'Marked as failed',
        };

        return $this->response->setJSON(['success' => true, 'message' => $msg]);
    }

    /** Append one wallet ledger row (single atomic insert). */
    private function walletTxn($db, array $row): void
    {
        $row['created_at'] = $row['created_at'] ?? date('Y-m-d H:i:s');
        $db->table('rider_wallet_txns')->insert($row);
    }

    /** DinoviX platform revenue row — once per order per fee type. */
    private function platformFee($db, array $order, string $type, float $amount): void
    {
        if ($amount <= 0) return;
        try {
            $db->table('dinovix_fees')->insert([
                'order_id'      => (int)$order['id'],
                'restaurant_id' => (int)$order['restaurant_id'],
                'fee_type'      => $type,
                'amount'        => round($amount, 2),
                'created_at'    => date('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable $e) { /* unique(order_id, fee_type) — already booked */ }
    }

    /** Cash row for money the rider collected at the door. */
    private function recordCashPayment($db, array $order, float $collect): void
    {
        $db->table('payments')->insert([
            'order_id'          => (int)$order['id'],
            'branch_id'         => (int)$order['branch_id'],
            'user_id'           => (int)session('user_id'),
            'amount'            => $collect,
            'payment_method'    => 'cash',
            'payment_gateway'   => 'collected_between',
            'payment_reference' => 'COD-' . $order['order_number'] . '-' . date('His'),
            'gateway_response'  => json_encode(['channel' => 'driver_app', 'rider' => session('user_name')]),
            'status'            => 'completed',
            'notes'             => 'Cash on delivery collected by ' . session('user_name'),
            'created_at'        => date('Y-m-d H:i:s'),
            'verified_by'       => (int)session('user_id'),
            'verified_at'       => date('Y-m-d H:i:s'),
        ]);
    }

    // ── GPS ping from the driver app ─────────────────────────
    public function ping()
    {
        $lat = (float)$this->request->getPost('lat');
        $lng = (float)$this->request->getPost('lng');
        $acc = (float)$this->request->getPost('accuracy');

        if ($lat < -90 || $lat > 90 || $lng < -180 || $lng > 180) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid coordinates']);
        }

        $db   = \Config\Database::connect();
        $uid  = (int)session('user_id');
        $rid  = (int)session('restaurant_id');
        $bid  = (int)session('branch_id');
        $now  = date('Y-m-d H:i:s');

        $exists = $db->table('rider_locations')->where('user_id', $uid)->countAllResults();
        if ($exists) {
            $db->table('rider_locations')->where('user_id', $uid)->update([
                'lat' => $lat, 'lng' => $lng, 'accuracy' => $acc,
                'updated_at' => $now,
            ]);
        } else {
            $db->table('rider_locations')->insert([
                'user_id' => $uid, 'restaurant_id' => $rid, 'branch_id' => $bid,
                'lat' => $lat, 'lng' => $lng, 'accuracy' => $acc,
                'created_at' => $now, 'updated_at' => $now,
            ]);
        }

        return $this->response->setJSON(['success' => true]);
    }

    // ── On-duty switch (drives the Android foreground service) ──
    // duty_on lives on the rider_locations row so the app reads the same
    // state after a restart and re-launches the service automatically.
    public function duty()
    {
        $db = \Config\Database::connect();
        $uid = (int)session('user_id');
        $rid = (int)session('restaurant_id');
        $bid = (int)session('branch_id');
        $on  = $this->request->getPost('on') === '1';
        $now = date('Y-m-d H:i:s');

        $exists = $db->table('rider_locations')->where('user_id', $uid)->countAllResults();
        if ($exists) {
            $db->table('rider_locations')->where('user_id', $uid)->update(['duty_on' => $on ? 1 : 0, 'updated_at' => $now]);
        } else {
            $db->table('rider_locations')->insert([
                'user_id' => $uid, 'restaurant_id' => $rid, 'branch_id' => $bid,
                'duty_on' => $on ? 1 : 0, 'created_at' => $now, 'updated_at' => $now,
            ]);
        }

        return $this->response->setJSON(['success' => true, 'duty_on' => $on]);
    }

    // ── Assignment feed for the Android foreground service ──────
    // Key-less "push": the duty service polls this every ~25 s with its
    // own cursor and raises a local notification for the new assignments.
    public function feed()
    {
        $db   = \Config\Database::connect();
        $uid  = (int)session('user_id');
        $since = (int)($this->request->getGet('since') ?? 0);

        $rows = $db->table('notifications')
            ->select('id, title, message, data_json, created_at')
            ->where('user_id', $uid)
            ->where('type', 'delivery_assigned')
            ->where('id >', $since > 0 ? $since : 0)
            ->orderBy('id', 'ASC')->limit(10)
            ->get()->getResultArray();

        // cursor: highest notification id the rider has seen overall
        $latest = $db->table('notifications')->selectMax('id')->where('user_id', $uid)->get()->getRowArray();
        $head   = (int)($latest['id'] ?? 0);

        return $this->response->setJSON([
            'success' => true,
            'items'   => array_map(fn($r) => [
                'id'          => (int)$r['id'],
                'title'       => $r['title'],
                'message'     => $r['message'],
                'order_id'    => (int)(json_decode($r['data_json'] ?? '{}', true)['order_id'] ?? 0),
                'fleet'       => (bool)(json_decode($r['data_json'] ?? '{}', true)['fleet'] ?? false),
                'created_at'  => $r['created_at'],
            ], $rows),
            'head' => $head,
        ]);
    }
}
