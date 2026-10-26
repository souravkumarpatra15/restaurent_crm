<?php

namespace App\Controllers\Public;

use App\Controllers\BaseController;

/**
 * public /track/{token} — live order tracking for ANY order, reached from
 * the bill slip link/QR (no login, no phone). Primarily for takeaway and
 * delivery orders which don't have the table-QR tracking flow.
 *
 * Dine-in table orders keep their dedicated QR-menu status page; this
 * tracker also works for them, but normal usage stays unchanged.
 */
class TrackController extends BaseController
{
    private function load(string $token): ?array
    {
        $token = preg_replace('/[^a-f0-9]/i', '', $token);
        if (strlen($token) < 8) {
            return null;
        }

        $db = \Config\Database::connect();
        $order = $db->table('orders o')
            ->select('o.id, o.order_number, o.order_type, o.status, o.payment_status,
                      o.delivery_status, o.rider_name, o.customer_phone, o.delivery_address,
                      o.estimated_mins, o.created_at, o.total_amount, o.table_id,
                      o.restaurant_id, o.branch_id, o.user_id,
                      o.cancelled_reason, o.completed_at, o.balance_amount,
                      r.name AS restaurant_name, r.theme_color, r.id as rid,
                      b.name AS branch_name, b.printer_type')
            ->join('restaurants r', 'r.id = o.restaurant_id', 'left')
            ->join('branches b', 'b.id = o.branch_id', 'left')
            ->where('o.track_token', $token)
            ->get()->getRowArray();

        if (!$order) {
            return null;
        }

        $items = $db->table('order_items oi')
            ->select('oi.name, oi.variant_name, oi.quantity, oi.status')
            ->where('oi.order_id', $order['id'])
            ->where('oi.status !=', 'cancelled')
            ->orderBy('oi.id', 'ASC')
            ->get()->getResultArray();

        $order['items'] = $items;
        return $order;
    }

    private function timeline(array $order): array
    {
        $orderType = $order['order_type'] === 'delivery' ? 'delivery' : 'takeaway';
        $steps = \App\Libraries\OrderTrack::steps($orderType);

        if ($orderType === 'delivery') {
            if (in_array($order['status'], ['cancelled'], true)) {
                return ['steps' => $steps, 'current' => 0, 'cancelled' => true];
            }
            if ($order['delivery_status'] === 'delivered' || $order['status'] === 'completed') {
                return ['steps' => $steps, 'current' => 5, 'cancelled' => false];
            }
            if ($order['delivery_status'] === 'out_for_delivery') {
                return ['steps' => $steps, 'current' => 4, 'cancelled' => false];
            }
            if ($order['delivery_status'] === 'failed') {
                return ['steps' => $steps, 'current' => 4, 'cancelled' => true,
                        'note' => 'Delivery could not complete — the restaurant is contacting you.'];
            }

            $map = ['pending' => 1, 'confirmed' => 2, 'preparing' => 3, 'ready' => 3, 'served' => 3];
            $current = $map[$order['status']] ?? 1;
            // "ready" in the kitchen means food is packed & waiting for the rider
            if ($order['status'] === 'ready') {
                $steps[4]['name'] = 'Waiting for rider';
                $steps[4]['desc'] = 'Packed, rider arriving soon';
            }
            return ['steps' => $steps, 'current' => $current, 'cancelled' => false];
        }

        if ($order['status'] === 'cancelled') {
            return ['steps' => $steps, 'current' => 0, 'cancelled' => true];
        }
        $map = ['pending' => 1, 'confirmed' => 2, 'preparing' => 3, 'ready' => 4, 'served' => 5, 'completed' => 5];
        return ['steps' => $steps, 'current' => $map[$order['status']] ?? 1, 'cancelled' => false];
    }

    private function itemProgress(array $items): array
    {
        return \App\Libraries\OrderTrack::itemProgress($items);
    }

    // ── Public: customer taps "Pay by UPI" on their tracking page ──
    public function pay($token)
    {
        $order = $this->load($token);
        if (!$order) {
            return view('public/track', ['order' => null]);
        }
        if ($order['payment_status'] === 'paid' || in_array($order['status'], ['completed', 'cancelled'], true)) {
            return redirect()->to(base_url('track/' . $token));
        }

        $db = \Config\Database::connect();
        $res = \App\Libraries\UpiAttempts::create($db, $order, 0, 'customer_tracking');
        if (!$res['ok']) {
            // A pending attempt (even expired) already holds the amount —
            // send the customer to that screen so they can pay it.
            $open = $db->query(
                "SELECT attempt_ref FROM payments
                  WHERE order_id = ? AND verify_status = 'pending'
                  ORDER BY id DESC LIMIT 1",
                [$order['id']]
            )->getRowArray();
            if ($open) {
                return redirect()->to(base_url('upi/pay/' . $open['attempt_ref']));
            }
            return view('public/track', ['order' => null]);
        }

        return redirect()->to(base_url('upi/pay/' . $res['ref']));
    }

    // ── Page ────────────────────────────────────────────────────
    public function index($token)
    {
        $order = $this->load($token);
        if (!$order) {
            return view('public/track', ['order' => null]);
        }

        $timeline = $this->timeline($order);
        $progress = $this->itemProgress($order['items']);

        $created     = strtotime($order['created_at']);
        $elapsedMins = (time() - $created) / 60;
        $etaMins     = (int)($order['estimated_mins'] ?? 15);

        // Order snapshot for the pay entry point (upi enabled / amount)
        $db = \Config\Database::connect();
        $upiEnabled = false;
        if ($order['payment_status'] !== 'paid' && !empty($order['rid'])) {
            $r2 = $db->table('restaurants')
                ->select('upi_enabled, upi_vpa')->where('id', (int)$order['rid'])->get()->getRowArray();
            $upiEnabled = $r2 && (int)$r2['upi_enabled'] === 1 && !empty($r2['upi_vpa']);
        }
        $payAmount = (float)$order['balance_amount'] ?: (float)$order['total_amount'];

        return view('public/track', [
            'order'     => $order,
            'token'     => $token,
            'steps'     => $timeline['steps'],
            'current'   => $timeline['current'],
            'cancelled' => $timeline['cancelled'],
            'note'      => $timeline['note'] ?? null,
            'progress'  => $progress,
            'etaMins'   => (int)max(1, round($etaMins - $elapsedMins)),
            'theme'     => $order['theme_color'] ?: '#FF6B35',
            'upiEnabled' => $upiEnabled,
            'payUrl'    => base_url('track/' . $token . '/pay'),
            'payAmount' => (float)$order['balance_amount'] ?: (float)$order['total_amount'],
        ]);
    }

    // ── Poll JSON ───────────────────────────────────────────────
    public function poll($token)
    {
        $order = $this->load($token);
        if (!$order) {
            return $this->response->setJSON(['error' => true]);
        }

        $timeline = $this->timeline($order);

        $created     = strtotime($order['created_at']);
        $elapsedMins = (time() - $created) / 60;
        $etaMins     = (int)($order['estimated_mins'] ?? 15);

        // headline text mirroring the table tracker
        $current = $timeline['current'];
        $steps   = $timeline['steps'];

        $labels = [
            1 => ['label' => 'Order Received', 'msg' => 'Staff will confirm your order shortly', 'icon' => '📋'],
            2 => ['label' => 'Confirmed!',     'msg' => 'Kitchen is preparing your food',       'icon' => '✅'],
            3 => ['label' => 'In Kitchen',     'msg' => 'Your food is being prepared',          'icon' => '👨‍🍳'],
            4 => ['label' => ($order['order_type'] === 'delivery'
                                 ? ($order['delivery_status'] === 'out_for_delivery' ? 'Out for Delivery' : 'Waiting for rider')
                                 : 'Ready for Pickup'),
                  'msg' => ($order['order_type'] === 'delivery'
                                 ? 'Your rider is on the way'
                                 : 'Packed, collect at the counter'),
                  'icon' => '🥡'],
            5 => ['label' => ($order['order_type'] === 'delivery' ? 'Delivered' : 'Picked Up'),
                  'msg' => ($order['order_type'] === 'delivery' ? 'Enjoy your food!' : 'Thank you for your order!'),
                  'icon' => '🎉'],
        ];

        if ($timeline['cancelled']) {
            $label = ['label' => $order['status'] === 'cancelled' ? 'Order Cancelled' : 'Not delivered',
                      'msg'   => $order['cancelled_reason'] ?: ($timeline['note'] ?? 'Contact the restaurant'),
                      'icon'  => '❌',
                      'step'  => 0];
        } else {
            $label = $labels[$current] ?? $labels[1];
            $label['step'] = $current;
        }

        return $this->response->setJSON([
            'success'      => true,
            'status'       => $order['status'],
            'delivery_status' => $order['delivery_status'],
            'step'         => $label['step'],
            'label'        => $label['label'],
            'message'      => $label['msg'],
            'icon'         => $label['icon'],
            'cancelled'    => $timeline['cancelled'],
            'payment_status' => $order['payment_status'],
            'rider_name'   => $order['rider_name'],
            'remain_mins'  => $timeline['cancelled'] ? 0 : (int)max(1, round($etaMins - $elapsedMins)),
            'elapsed_mins' => (int)$elapsedMins,
            'items'        => array_map(fn($i) => [
                'name'    => $i['name'],
                'qty'     => (int)$i['quantity'],
                'variant' => $i['variant_name'],
                'status'  => $i['status'],
            ], $order['items']),
            'progress'     => $this->itemProgress($order['items']),
        ]);
    }
}
