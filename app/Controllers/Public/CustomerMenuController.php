<?php

namespace App\Controllers\Public;

use App\Controllers\BaseController;
use App\Models\OrderModel;
use App\Models\MenuModel;

class CustomerMenuController extends BaseController
{
    // ── Load table + restaurant data from QR token ────────────
    private function getTableByToken(string $token): ?array
    {
        $db = \Config\Database::connect();
        return $db->table('tables t')
            ->select('t.id, t.table_number, t.capacity, t.status, t.branch_id, t.qr_token,
                      b.name as branch_name, b.restaurant_id, b.billing_prefix, b.billing_counter,
                      b.kot_prefix, b.kot_counter,
                      r.name as restaurant_name, r.logo, r.currency_symbol, r.theme_color,
                      r.tax_type, r.default_tax_percent, r.service_charge_percent,
                      r.receipt_header')
            ->join('branches b', 'b.id = t.branch_id', 'left')
            ->join('restaurants r', 'r.id = b.restaurant_id', 'left')
            ->where('t.qr_token', $token)
            ->get()->getRowArray();
    }

    // ── Menu page (customer scans QR) ────────────────────────
    public function index($token)
    {
        $table = $this->getTableByToken($token);
        if (!$table) {
            return view('public/menu_error', ['message' => 'Invalid or expired QR code. Please ask staff for help.']);
        }

        $db  = \Config\Database::connect();
        $rid = $table['restaurant_id'];

        $categories = $db->table('menu_categories')
            ->where('restaurant_id', $rid)
            ->where('is_active', 1)
            ->orderBy('sort_order', 'ASC')
            ->get()->getResultArray();

        foreach ($categories as &$cat) {
            $cat['items'] = $db->table('menu_items')
                ->where('restaurant_id', $rid)
                ->where('category_id', $cat['id'])
                ->where('is_active', 1)
                ->orderBy('sort_order', 'ASC')
                ->get()->getResultArray();
        }
        $categories = array_values(array_filter($categories, fn($c) => !empty($c['items'])));

        return view('public/menu', ['table' => $table, 'categories' => $categories]);
    }

    // ── Place order from customer ─────────────────────────────
    public function placeOrder($token)
    {
        $table = $this->getTableByToken($token);
        if (!$table) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid QR code']);
        }

        $items = json_decode($this->request->getPost('items'), true);
        if (empty($items)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Your cart is empty']);
        }

        $db  = \Config\Database::connect();
        $rid = $table['restaurant_id'];
        $bid = $table['branch_id'];

        // Get restaurant config
        $restaurant = $db->table('restaurants')->where('id', $rid)->get()->getRowArray();
        if (!$restaurant) {
            return $this->response->setJSON(['success' => false, 'message' => 'Restaurant not found']);
        }

        // Get a system user for this branch (required for orders.user_id NOT NULL)
        $sysUser = $db->table('users')
            ->where('branch_id', $bid)
            ->where('is_active', 1)
            ->orderBy('id', 'ASC')
            ->get()->getRowArray();
        if (!$sysUser) {
            $sysUser = $db->table('users')
                ->where('restaurant_id', $rid)
                ->where('is_active', 1)
                ->orderBy('id', 'ASC')
                ->get()->getRowArray();
        }
        $userId = $sysUser['id'] ?? 1;

        // Build item details
        $orderModel = new OrderModel();
        $menuModel  = new MenuModel();

        $itemDetails = [];
        foreach ($items as $item) {
            $menuItem = $menuModel->find((int)$item['id']);
            if (!$menuItem || !$menuItem['is_active']) continue;
            $itemDetails[] = [
                'menu_item_id' => $menuItem['id'],
                'variant_id'   => null,
                'name'         => $menuItem['name'],
                'variant_name' => null,
                'quantity'     => max(1, (int)($item['qty'] ?? 1)),
                'unit_price'   => $menuItem['base_price'],
                'tax_percent'  => $menuItem['tax_percent'],
                'notes'        => $item['note'] ?? '',
                'addons'       => [],
            ];
        }

        if (empty($itemDetails)) {
            return $this->response->setJSON(['success' => false, 'message' => 'No valid items found']);
        }

        // Calculate totals
        $totals = $orderModel->calculateTotals($itemDetails, $restaurant, null, 0, false);

        $db->transStart();

        // Generate order number
        $branch  = $db->table('branches')->where('id', $bid)->get()->getRowArray();
        $prefix  = $branch['billing_prefix'] ?: 'ORD';
        $counter = (int)($branch['billing_counter'] ?? 1);
        $db->table('branches')->where('id', $bid)->update(['billing_counter' => $counter + 1]);
        $orderNumber = $prefix . str_pad($counter, 5, '0', STR_PAD_LEFT);

        // Estimate prep time (5 min base + 3 min per item)
        $totalItems  = array_sum(array_column($itemDetails, 'quantity'));
        $estMins     = 5 + ($totalItems * 3);

        // Insert order
        $orderId = $db->table('orders')->insert([
            'restaurant_id'   => $rid,
            'branch_id'       => $bid,
            'table_id'        => $table['id'],
            'user_id'         => $userId,
            'order_number'    => $orderNumber,
            'order_type'      => 'dine_in',
            'source'          => 'qr_customer',
            'status'          => 'pending',
            'customer_name'   => $this->request->getPost('customer_name') ?: 'Table ' . $table['table_number'],
            'customer_phone'  => $this->request->getPost('customer_phone') ?: null,
            'no_of_guests'    => (int)($this->request->getPost('guests') ?? 1),
            'subtotal'        => $totals['subtotal'],
            'discount_amount' => 0,
            'tax_amount'      => $totals['tax_amount'],
            'cgst_amount'     => $totals['cgst_amount'],
            'sgst_amount'     => $totals['sgst_amount'],
            'total_amount'    => $totals['total_amount'],
            'payment_status'  => 'unpaid',
            'estimated_mins'  => $estMins,
            'notes'           => $this->request->getPost('notes') ?: null,
            'created_at'      => date('Y-m-d H:i:s'),
        ]);
        $orderId = $db->insertID();

        // Insert order items
        foreach ($totals['items'] as $item) {
            $db->table('order_items')->insert([
                'order_id'     => $orderId,
                'menu_item_id' => $item['menu_item_id'],
                'variant_id'   => null,
                'name'         => $item['name'],
                'quantity'     => $item['quantity'],
                'unit_price'   => $item['unit_price'],
                'tax_percent'  => $item['tax_percent'],
                'tax_amount'   => $item['tax_amount'],
                'total_price'  => $item['total_price'],
                'notes'        => $item['notes'],
                'status'       => 'pending',
            ]);
        }

        // Mark table occupied
        $db->table('tables')->where('id', $table['id'])->update([
            'status'       => 'occupied',
            'booked_name'  => null,
            'booked_phone' => null,
            'booked_for'   => null,
        ]);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->response->setJSON(['success' => false, 'message' => 'Failed to place order. Please try again.']);
        }

        return $this->response->setJSON([
            'success'      => true,
            'order_id'     => $orderId,
            'order_number' => $orderNumber,
            'total'        => $totals['total_amount'],
            'est_mins'     => $estMins,
            'redirect'     => base_url('menu/table/' . $token . '/status/' . $orderId),
        ]);
    }

    // ── Customer order status page ────────────────────────────
    public function orderStatus($token, $orderId)
    {
        $table = $this->getTableByToken($token);
        if (!$table) return view('public/menu_error', ['message' => 'Invalid QR code.']);

        $db    = \Config\Database::connect();
        $order = $db->table('orders o')
            ->select('o.id, o.order_number, o.status, o.payment_status, o.total_amount,
                      o.customer_name, o.estimated_mins, o.created_at, o.source')
            ->where('o.id', $orderId)
            ->where('o.table_id', $table['id'])
            ->get()->getRowArray();

        if (!$order) return view('public/menu_error', ['message' => 'Order not found.']);

        $items = $db->table('order_items')
            ->select('name, quantity, unit_price, total_price, notes')
            ->where('order_id', $orderId)
            ->get()->getResultArray();

        return view('public/order_status', [
            'table'  => $table,
            'order'  => $order,
            'items'  => $items,
            'token'  => $token,
        ]);
    }

    // ── AJAX: poll order status (called by customer status page) ─
    public function pollStatus($token, $orderId)
    {
        $table = $this->getTableByToken($token);
        if (!$table) return $this->response->setJSON(['error' => true]);

        $db    = \Config\Database::connect();
        $order = $db->table('orders')
            ->select('id, status, payment_status, estimated_mins, created_at')
            ->where('id', $orderId)
            ->where('table_id', $table['id'])
            ->get()->getRowArray();

        if (!$order) return $this->response->setJSON(['error' => true]);

        $created     = strtotime($order['created_at']);
        $elapsedMins = (time() - $created) / 60;
        $estMins     = (int)($order['estimated_mins'] ?? 15);
        $remainMins  = max(0, $estMins - $elapsedMins);

        $labels = [
            'pending'    => ['label' => 'Order Received', 'msg' => 'Staff will confirm your order shortly', 'step' => 1, 'icon' => '📋'],
            'confirmed'  => ['label' => 'Confirmed!',     'msg' => 'Kitchen is preparing your food',       'step' => 2, 'icon' => '✅'],
            'preparing'  => ['label' => 'In Kitchen',     'msg' => 'Your food is being prepared',          'step' => 3, 'icon' => '👨‍🍳'],
            'ready'      => ['label' => 'Ready!',          'msg' => 'Your order is ready. Enjoy! 🎉',       'step' => 4, 'icon' => '🍽'],
            'served'     => ['label' => 'Served',          'msg' => 'Enjoy your meal! 😋',                  'step' => 4, 'icon' => '😋'],
            'completed'  => ['label' => 'Completed',       'msg' => 'Thank you for dining with us!',        'step' => 4, 'icon' => '⭐'],
        ];

        $info = $labels[$order['status']] ?? $labels['pending'];

        return $this->response->setJSON([
            'status'      => $order['status'],
            'step'        => $info['step'],
            'label'       => $info['label'],
            'message'     => $info['msg'],
            'icon'        => $info['icon'],
            'remain_mins' => (int)$remainMins,
            'elapsed_mins'=> (int)$elapsedMins,
        ]);
    }
}
