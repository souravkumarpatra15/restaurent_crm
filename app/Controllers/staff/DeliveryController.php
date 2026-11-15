<?php
namespace App\Controllers\Staff;

use App\Controllers\BaseController;
use App\Libraries\RiderAssign;
use App\Libraries\FleetSettings;

/**
 * Delivery board + POS dispatch — rider assignment and delivery status
 * tracking for orders of type "delivery".
 *
 * The dispatch panel in POS, this board and the rider app all read the
 * same rider-card model: who is free, on duty, how far they are.
 */
class DeliveryController extends BaseController
{
    private const DELIVERY_STATES = ['unassigned', 'assigned', 'out_for_delivery', 'delivered', 'failed'];

    // ── Delivery board ────────────────────────────────────────
    public function board()
    {
        if ($g = $this->requirePermission('delivery.manage', 'Delivery Board')) return $g;

        $db = \Config\Database::connect();
        $rid = session('restaurant_id');
        $bid = session('branch_id');

        $orders = $db->table('orders o')
            ->select('o.id, o.order_number, o.status, o.customer_name, o.customer_phone, o.delivery_address,
                      o.delivery_status, o.rider_id, o.rider_name, o.total_amount, o.payment_status,
                      o.created_at, o.delivered_at')
            ->where('o.restaurant_id', $rid)
            ->where('o.branch_id', $bid)
            ->where('o.order_type', 'delivery')
            ->whereIn('o.status', ['pending', 'confirmed', 'preparing', 'ready', 'served', 'completed'])
            ->orderBy('o.created_at', 'DESC')
            ->limit(50)
            ->get()->getResultArray();

        $stats = [
            'unassigned' => count(array_filter($orders, fn($o) => ($o['delivery_status'] ?: 'unassigned') === 'unassigned')),
            'active'     => count(array_filter($orders, fn($o) => in_array($o['delivery_status'] ?: 'unassigned', ['assigned', 'out_for_delivery']))),
            'delivered'  => count(array_filter($orders, fn($o) => ($o['delivery_status'] ?: '') === 'delivered')),
            'failed'     => count(array_filter($orders, fn($o) => ($o['delivery_status'] ?: '') === 'failed')),
        ];

        return view('staff/pos/delivery', [
            'pageTitle'  => 'Delivery Board',
            'orders'     => $orders,
            'riderCards' => $this->riderCards($db, (int)$rid, (int)$bid),
            'mode'       => FleetSettings::get($db, (int)$rid)['delivery_mode'],
            'stats'      => $stats,
        ]);
    }

    /**
     * Assignable rider cards — used by the board dropdown and the POS
     * dispatch panel. Shows who is busy, on/off duty and how far they
     * are from the branch (straight-line from their last GPS ping).
     */
    private function riderCards($db, int $rid, int $bid): array
    {
        $riders = $db->table('riders r')
            ->select("r.user_id, r.vehicle_no, r.employer_type,
                      u.id, u.name, u.phone,
                      (SELECT COUNT(*) FROM orders o2
                        WHERE o2.rider_id = u.id
                          AND o2.delivery_status IN ('assigned','out_for_delivery')) AS busy,
                      rl.duty_on, rl.lat AS gps_lat, rl.lng AS gps_lng, rl.updated_at AS gps_at")
            ->join('users u', 'u.id = r.user_id')
            ->join('rider_locations rl', 'rl.user_id = u.id', 'left')
            ->where('r.restaurant_id', $rid)
            ->where('r.is_active', 1)
            ->where('u.restaurant_id', $rid)
            ->where('u.is_active', 1)
            ->orderBy('u.name', 'ASC')
            ->get()->getResultArray();

        if (!$riders) {
            // board stays usable before rider profiles exist
            $riders = $db->table('users u')
                ->select("u.id AS user_id, u.id, u.name, u.phone,
                          'restaurant' AS employer_type, 0 AS busy,
                          NULL AS vehicle_no, rl.duty_on, rl.lat AS gps_lat, rl.lng AS gps_lng, rl.updated_at AS gps_at")
                ->join('roles r', 'r.id = u.role_id')
                ->join('rider_locations rl', 'rl.user_id = u.id', 'left')
                ->where('u.restaurant_id', $rid)->where('u.is_active', 1)
                ->where('r.slug', 'delivery')
                ->get()->getResultArray();
        }

        $branch = $db->table('branches')->select('latitude, longitude')->where('id', $bid)->get()->getRowArray();

        return array_map(function ($r) use ($branch) {
            $km = null;
            if ($branch && $branch['latitude'] && ($r['gps_lat'] ?? null) !== null) {
                $km = RiderAssign::straightKm((float)$branch['latitude'], (float)$branch['longitude'], (float)$r['gps_lat'], (float)$r['gps_lng']);
            }
            return [
                'id'       => (int)$r['id'],
                'name'     => $r['name'],
                'phone'    => $r['phone'],
                'employer' => $r['employer_type'] ?? 'restaurant',
                'vehicle'  => $r['vehicle_no'] ?: '',
                'busy'     => (int)$r['busy'],
                'duty'     => (int)($r['duty_on'] ?? 0),
                'km'       => $km,
                'gps_at'   => $r['gps_at'] ?: null,
            ];
        }, $riders);
    }

    /** GET pos/delivery/riders — rider cards for the POS dispatch panel. */
    public function riders()
    {
        if ($g = $this->requirePermission('delivery.manage', 'Rider Dispatch')) return $g;
        $db = \Config\Database::connect();
        return $this->response->setJSON([
            'success' => true,
            'mode'    => FleetSettings::get($db, (int)session('restaurant_id'))['delivery_mode'],
            'riders'  => $this->riderCards($db, (int)session('restaurant_id'), (int)session('branch_id')),
        ]);
    }

    /** POST pos/delivery/auto/{id} — run auto-dispatch on demand. */
    public function auto($orderId)
    {
        if ($g = $this->requirePermission('delivery.manage', 'Rider Dispatch')) return $g;
        $db    = \Config\Database::connect();
        $order = $db->table('orders')
            ->where('id', (int)$orderId)->where('restaurant_id', session('restaurant_id'))
            ->where('branch_id', session('branch_id'))->where('order_type', 'delivery')
            ->get()->getRowArray();
        if (!$order) {
            return $this->response->setJSON(['success' => false, 'message' => 'Delivery order not found']);
        }
        if (in_array($order['delivery_status'], ['delivered', 'failed'], true)) {
            return $this->response->setJSON(['success' => false, 'message' => 'This delivery is already closed']);
        }

        [$ok, $msg] = RiderAssign::autoAssign($db, (int)$orderId);
        if (!$ok) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $msg === 'no free rider'
                    ? 'No free rider right now — all riders are busy or off duty. Assign manually below.'
                    : ($msg ?: 'Auto-assign failed'),
            ]);
        }

        $fresh = $db->table('orders')->where('id', (int)$orderId)->get()->getRowArray();
        return $this->response->setJSON([
            'success'    => true,
            'assigned'   => true,
            'rider_id'   => (int)$fresh['rider_id'],
            'rider_name' => $fresh['rider_name'],
            'km'         => $fresh['delivery_km'] !== null ? (float)$fresh['delivery_km'] : null,
            'message'    => 'Assigned to ' . $fresh['rider_name'] . ' — notified on their app with sound',
        ]);
    }

    /** GET pos/delivery/state/{id} — current dispatch state of one order. */
    public function state($orderId)
    {
        $db    = \Config\Database::connect();
        $order = $db->table('orders')
            ->select('delivery_status, rider_id, rider_name, delivery_km')
            ->where('id', (int)$orderId)->where('restaurant_id', session('restaurant_id'))
            ->where('branch_id', session('branch_id'))->where('order_type', 'delivery')
            ->get()->getRowArray();
        if (!$order) {
            return $this->response->setJSON(['success' => false, 'message' => 'Order not found']);
        }
        return $this->response->setJSON([
            'success'         => true,
            'delivery_status' => $order['delivery_status'] ?: 'unassigned',
            'rider_id'        => $order['rider_id'] !== null ? (int)$order['rider_id'] : null,
            'rider_name'      => $order['rider_name'],
            'km'              => $order['delivery_km'] !== null ? (float)$order['delivery_km'] : null,
        ]);
    }

    // ── Assign / change rider ─────────────────────────────────
    public function assign()
    {
        if ($g = $this->requirePermission('delivery.manage', 'Rider Dispatch')) return $g;
        $db  = \Config\Database::connect();
        $rid = session('restaurant_id');
        $bid = session('branch_id');

        $orderId = (int)$this->request->getPost('order_id');
        $riderId = (int)$this->request->getPost('rider_id');

        $order = $db->table('orders')
            ->where('id', $orderId)->where('restaurant_id', $rid)
            ->where('branch_id', $bid)->where('order_type', 'delivery')
            ->get()->getRowArray();
        if (!$order) {
            return $this->response->setJSON(['success' => false, 'message' => 'Delivery order not found']);
        }
        if (in_array($order['delivery_status'], ['delivered', 'failed'])) {
            return $this->response->setJSON(['success' => false, 'message' => 'This delivery is already ' . $order['delivery_status']]);
        }

        // "0" = unassign
        if ($riderId === 0) {
            $db->table('orders')->where('id', $orderId)->update([
                'rider_id'        => null,
                'rider_name'      => null,
                'delivery_status' => 'unassigned',
                'updated_at'      => date('Y-m-d H:i:s'),
            ]);
            return $this->response->setJSON(['success' => true, 'message' => 'Rider unassigned']);
        }

        $rider = $db->table('users u')
            ->select('u.id, u.name')
            ->where('u.id', $riderId)->where('u.restaurant_id', $rid)->where('u.is_active', 1)
            ->get()->getRowArray();
        if (!$rider) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid rider']);
        }

        $db->table('orders')->where('id', $orderId)->update([
            'rider_id'        => $rider['id'],
            'rider_name'      => $rider['name'],
            'delivery_status' => 'assigned',
            'delivery_mode'   => $db->table('riders')->where('user_id', $rider['id'])->get()->getRowArray()['employer_type'] ?? 'restaurant',
            'updated_at'      => date('Y-m-d H:i:s'),
        ]);

        $fresh = $db->table('orders')->where('id', $orderId)->get()->getRowArray();
        RiderAssign::notifyRider($db, $rid, (int)$rider['id'], $fresh, $fresh['delivery_km'] ?: null);

        return $this->response->setJSON([
            'success'    => true,
            'assigned'   => true,
            'rider_id'   => (int)$rider['id'],
            'rider_name' => $rider['name'],
            'km'         => $fresh['delivery_km'] !== null ? (float)$fresh['delivery_km'] : null,
            'message'    => 'Assigned to ' . $rider['name'] . ' — notified on their app with sound',
        ]);
    }

    // ── Move delivery status along ────────────────────────────
    public function status()
    {
        if ($g = $this->requirePermission('delivery.manage', 'Delivery Board')) return $g;
        $db  = \Config\Database::connect();
        $rid = session('restaurant_id');
        $bid = session('branch_id');

        $orderId   = (int)$this->request->getPost('order_id');
        $newStatus = $this->request->getPost('delivery_status');
        if (!in_array($newStatus, self::DELIVERY_STATES, true)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid status']);
        }

        $order = $db->table('orders')
            ->where('id', $orderId)->where('restaurant_id', $rid)
            ->where('branch_id', $bid)->where('order_type', 'delivery')
            ->get()->getRowArray();
        if (!$order) {
            return $this->response->setJSON(['success' => false, 'message' => 'Delivery order not found']);
        }

        $cur = $order['delivery_status'] ?: 'unassigned';
        if (in_array($cur, ['delivered', 'failed']) && $newStatus !== 'out_for_delivery') {
            return $this->response->setJSON(['success' => false, 'message' => 'This delivery is already closed']);
        }
        if ($newStatus === 'assigned' && $order['rider_id'] === null) {
            return $this->response->setJSON(['success' => false, 'message' => 'Assign a rider first']);
        }
        if (in_array($newStatus, ['out_for_delivery', 'delivered']) && $cur === 'unassigned') {
            return $this->response->setJSON(['success' => false, 'message' => 'Assign a rider first']);
        }

        $update = [
            'delivery_status' => $newStatus,
            'updated_at'      => date('Y-m-d H:i:s'),
        ];
        $reason = trim($this->request->getPost('notes') ?: '');
        if ($newStatus === 'failed') {
            $update['notes'] = trim(($order['notes'] ? $order['notes'] . "\n" : '') . 'Delivery failed: ' . ($reason ?: 'no reason given'));
        }
        if ($newStatus === 'delivered') {
            $update['delivered_at'] = date('Y-m-d H:i:s');
        }
        if ($newStatus === 'out_for_delivery') {
            $update['delivered_at'] = null;
        }

        $db->table('orders')->where('id', $orderId)->update($update);

        return $this->response->setJSON(['success' => true, 'message' => 'Marked as ' . str_replace('_', ' ', $newStatus)]);
    }
}
