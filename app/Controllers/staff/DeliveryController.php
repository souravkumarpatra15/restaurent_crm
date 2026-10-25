<?php
namespace App\Controllers\Staff;

use App\Controllers\BaseController;

/**
 * Delivery board — rider assignment and delivery status tracking
 * for orders of type "delivery".
 */
class DeliveryController extends BaseController
{
    private const DELIVERY_STATES = ['unassigned', 'assigned', 'out_for_delivery', 'delivered', 'failed'];

    // ── Delivery board ────────────────────────────────────────
    public function board()
    {
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

        // Riders: any active staff member of this restaurant
        $riders = $db->table('users u')
            ->select('u.id, u.name, u.phone, r.slug as role')
            ->join('roles r', 'r.id = u.role_id', 'left')
            ->where('u.restaurant_id', $rid)
            ->where('u.is_active', 1)
            ->orderBy('u.name', 'ASC')
            ->get()->getResultArray();

        $stats = [
            'unassigned' => count(array_filter($orders, fn($o) => ($o['delivery_status'] ?: 'unassigned') === 'unassigned')),
            'active'     => count(array_filter($orders, fn($o) => in_array($o['delivery_status'] ?: 'unassigned', ['assigned', 'out_for_delivery']))),
            'delivered'  => count(array_filter($orders, fn($o) => ($o['delivery_status'] ?: '') === 'delivered')),
            'failed'     => count(array_filter($orders, fn($o) => ($o['delivery_status'] ?: '') === 'failed')),
        ];

        return view('staff/pos/delivery', [
            'pageTitle' => 'Delivery Board',
            'orders'    => $orders,
            'riders'    => $riders,
            'stats'     => $stats,
        ]);
    }

    // ── Assign / change rider ─────────────────────────────────
    public function assign()
    {
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
            'updated_at'      => date('Y-m-d H:i:s'),
        ]);

        return $this->response->setJSON(['success' => true, 'message' => 'Assigned to ' . $rider['name']]);
    }

    // ── Move delivery status along ────────────────────────────
    public function status()
    {
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
