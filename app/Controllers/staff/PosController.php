<?php
namespace App\Controllers\Staff;

use App\Controllers\BaseController;
use App\Models\OrderModel;
use App\Models\MenuModel;
use App\Models\TableModel;
use App\Models\CustomerModel;
use App\Models\RestaurantModel;
use App\Libraries\ThermalPrinter;
use App\Libraries\RecipeEngine;
use Config\Database;

class PosController extends BaseController
{
    protected $orderModel;
    protected $menuModel;
    protected $tableModel;
    protected $restaurantModel;
    protected $db;
    protected $session;
    protected $restaurant;
    protected $branch;

    public function __construct()
    {
        $this->orderModel      = new OrderModel();
        $this->menuModel       = new MenuModel();
        $this->tableModel      = new TableModel();
        $this->restaurantModel = new RestaurantModel();
        $this->db             = Database::connect();
        $this->session         = session();

        $this->restaurant = $this->restaurantModel->find($this->session->get('restaurant_id'));
        $this->branch = $this->db->table('branches')
            ->where('id', $this->session->get('branch_id'))
            ->get()->getRowArray();
    }

    // ----------------------------------------------------------------
    // POS Home — Table Map
    // ----------------------------------------------------------------
    public function index()
    {
        $data = [
            'title'    => 'POS - Table Map',
            'tables'   => $this->tableModel->getTablesByArea($this->session->get('branch_id')),
            'openOrder'=> $this->getOpenOrdersCount(),
            'restaurant' => $this->restaurant,
            'branch'   => $this->branch,
        ];
        return view('staff/pos/index', $data);
    }

    public function tableMap()
    {
        return $this->index();
    }

    // ----------------------------------------------------------------
    // New Order
    // ----------------------------------------------------------------
    public function newOrder($type = 'dine_in')
    {
        $tableId    = $this->request->getGet('table');
        $addToOrder = (int)$this->request->getGet('add_to'); // for "add round" mode
        $existingOrder = null;
        if ($addToOrder) {
            $existingOrder = $this->orderModel->find($addToOrder);
        }
        $data = [
            'title'           => $addToOrder ? 'Add Round to Order' : 'New Order',
            'order_type'      => $type,
            'table_id'        => $tableId,
            'table'           => $tableId ? $this->tableModel->find($tableId) : null,
            'categories'      => $this->menuModel->getCategoriesWithItems($this->session->get('restaurant_id'), $this->session->get('branch_id')),
            'restaurant'      => $this->restaurant,
            'branch'          => $this->branch,
            'add_to_order_id' => $addToOrder ?: 0,
            'existing_order'  => $existingOrder,
        ];
        return view('staff/pos/new_order', $data);
    }

    // ----------------------------------------------------------------
    // Create Order (AJAX)
    // ----------------------------------------------------------------
    public function createOrder()
    {
        $rules = [
            'order_type' => 'required|in_list[dine_in,takeaway,delivery]',
            'items'      => 'required',
        ];
        if (!$this->validate($rules)) {
            return $this->response->setJSON(['success' => false, 'errors' => $this->validator->getErrors()]);
        }

        $restaurantId = $this->session->get('restaurant_id');
        $branchId     = $this->session->get('branch_id');
        $userId       = $this->session->get('user_id');

        $items = json_decode($this->request->getPost('items'), true);
        if (empty($items)) {
            return $this->response->setJSON(['success' => false, 'message' => 'No items in order']);
        }

        // Build item details from DB (tenant-guarded: only this restaurant's
        // menu items and variants may be added)
        $itemDetails = [];
        foreach ($items as $item) {
            $menuItem = $this->menuModel
                ->where('id', $item['menu_item_id'])
                ->where('restaurant_id', $restaurantId)
                ->first();
            if (!$menuItem) continue;

            $variantPrice = null;
            if ($item['variant_id']) {
                $variant = $this->menuModel->db->table('menu_variants')
                    ->where('id', $item['variant_id'])
                    ->where('menu_item_id', $menuItem['id'])
                    ->get()->getRowArray();
                if (!$variant) {
                    return $this->response->setJSON(['success' => false, 'message' => 'Invalid variant for ' . $menuItem['name']]);
                }
                $variantPrice = $variant['price'];
            }

            $price = $variantPrice ?? $menuItem['base_price'];

            // Addons must be priced into the bill — they were silently free before
            $addonTotal = 0;
            foreach ($item['addons'] ?? [] as $addon) {
                $addonTotal += (float)($addon['price'] ?? 0);
            }
            $price = round($price + $addonTotal, 2);

            $itemDetails[] = [
                'menu_item_id' => $menuItem['id'],
                'variant_id'   => $item['variant_id'] ?? null,
                'name'         => $menuItem['name'],
                'variant_name' => $item['variant_name'] ?? null,
                'quantity'     => max(1, (float)$item['quantity']),
                'unit_price'   => $price,
                'tax_percent'  => $menuItem['tax_percent'],
                'cess_percent' => (float)($menuItem['cess_percent'] ?? 0),
                'notes'        => $item['notes'] ?? '',
                'addons'       => $item['addons'] ?? [],
            ];
        }

        // Calculate totals — packing charge applies to takeaway/delivery
        $discountType  = $this->request->getPost('discount_type') ?: 'flat';
        $discountValue = (float)($this->request->getPost('discount_value') ?? 0);
        $couponCode    = strtoupper(trim($this->request->getPost('coupon_code') ?? ''));
        $orderType     = $this->request->getPost('order_type');
        $packingCharge = in_array($orderType, ['takeaway', 'delivery'], true)
            ? max(0, (float)($this->restaurant['packaging_charge'] ?? 0))
            : 0;
        $totals = $this->orderModel->calculateTotals($itemDetails, $this->restaurant, $discountType, $discountValue, true, $couponCode, $packingCharge);

        // Delivery orders can carry a delivery charge + address
        $deliveryCharge  = ($orderType === 'delivery') ? max(0, (float)($this->request->getPost('delivery_charge') ?? 0)) : 0;
        $deliveryCharge  = ($orderType === 'delivery') ? max(0, (float)($this->request->getPost('delivery_charge') ?? 0)) : 0;
        $deliveryAddress = ($orderType === 'delivery') ? trim($this->request->getPost('delivery_address') ?: '') : null;
        if ($deliveryCharge > 0) {
            $totals['total_amount'] = round($totals['total_amount'] + $deliveryCharge, 2);
        }

        $db = \Config\Database::connect();
        $db->transStart();

        // Insert Order
        $orderNumber = $this->orderModel->generateOrderNumber($restaurantId, $branchId);
        $orderId = $this->orderModel->insert([
            'restaurant_id'   => $restaurantId,
            'branch_id'       => $branchId,
            'table_id'        => $this->request->getPost('table_id') ?: null,
            'customer_id'     => $this->request->getPost('customer_id') ?: null,
            'user_id'         => $userId,
            'order_number'    => $orderNumber,
            'order_type'      => $orderType,
            'status'          => 'pending',
            'customer_name'   => $this->request->getPost('customer_name') ?: null,
            'customer_phone'  => $this->request->getPost('customer_phone') ?: null,
            'delivery_address'=> $deliveryAddress,
            'delivery_status' => $orderType === 'delivery' ? 'unassigned' : null,
            'delivery_charge' => $deliveryCharge,
            'no_of_guests'    => (int)($this->request->getPost('no_of_guests') ?? $this->request->getPost('guests') ?? 1),
            'subtotal'        => $totals['subtotal'],
            'discount_type'   => $discountType,
            'discount_amount' => $totals['discount_amount'],
            'coupon_code'     => $totals['coupon_code'] ?? null,
            'tax_amount'      => $totals['tax_amount'],
            'igst_amount'     => $totals['igst_amount'],
            'cgst_amount'     => $totals['cgst_amount'],
            'sgst_amount'     => $totals['sgst_amount'],
            'cess_amount'     => $totals['cess_amount'],
            'packing_charge'  => $totals['packing_charge'],
            'service_charge'  => $totals['service_charge'],
            'round_off'       => $totals['round_off'],
            'total_amount'    => $totals['total_amount'],
            'payment_status'  => 'unpaid',
            'notes'           => $this->request->getPost('notes') ?: null,
            'kitchen_notes'   => $this->request->getPost('kitchen_notes') ?: null,
        ]);

        // Insert Order Items
        foreach ($totals['items'] as $item) {
            $orderItemId = $db->table('order_items')->insert([
                'order_id'     => $orderId,
                'menu_item_id' => $item['menu_item_id'],
                'variant_id'   => $item['variant_id'],
                'name'         => $item['name'],
                'variant_name' => $item['variant_name'],
                'quantity'     => $item['quantity'],
                'unit_price'   => $item['unit_price'],
                'tax_percent'  => $item['tax_percent'],
                'tax_amount'   => $item['tax_amount'],
                'total_price'  => $item['total_price'],
                'notes'        => $item['notes'],
                'status'       => 'pending',
            ]);
            $oid = $db->insertID();

            // Addons
            foreach ($item['addons'] ?? [] as $addon) {
                $db->table('order_item_addons')->insert([
                    'order_item_id' => $oid,
                    'addon_id'      => $addon['id'],
                    'name'          => $addon['name'],
                    'price'         => $addon['price'],
                ]);
            }
        }

        // Update table status
        if ($this->request->getPost('table_id')) {
            $db->table('tables')->where('id', $this->request->getPost('table_id'))->update(['status' => 'occupied']);
        }

        // Inventory: deduct recipe ingredients for everything just sold
        RecipeEngine::deduct($db, $restaurantId, (int)$this->branch['id'], $itemDetails,
            (int)$this->session->get('user_id'), (int)$orderId);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->response->setJSON(['success' => false, 'message' => 'Order creation failed']);
        }

        // Coupon redemption: record usage so limits keep working
        if (!empty($totals['coupon_code'])) {
            $db->table('coupons')
                ->where('code', $totals['coupon_code'])
                ->where('restaurant_id', $restaurantId)
                ->set('usage_count', 'usage_count + 1', false)
                ->update();
        }

        // Loyalty redemption: deduct points from the customer and log the
        // transaction (points purchase never used to go anywhere)
        $redeemedPoints = 0;
        if ($discountType === 'loyalty' && $this->getPlanFeature('allow_loyalty')
            && (float)$totals['discount_amount'] > 0
            && ($customerId = (int)($this->request->getPost('customer_id') ?: 0))) {
            $rule = $db->table('loyalty_rules')->where('restaurant_id', $restaurantId)->where('is_active', 1)->orderBy('id','ASC')->get()->getRowArray();
            $customerRow = $db->table('customers')
                ->where('id', $customerId)->where('restaurant_id', $restaurantId)->get()->getRowArray();
            $rule = $rule ?: ['min_redeem_points' => 0, 'redeem_value' => 0, 'max_redeem_percent' => 0];

            $points = (float)$discountValue;
            $cap    = round((float)$totals['subtotal'] * (float)$rule['max_redeem_percent'] / 100, 2);
            $value  = min($points * (float)$rule['redeem_value'], $cap, (float)$totals['subtotal']);

            if ($customerRow && $points >= (float)$rule['min_redeem_points'] && $points > 0
                && (float)$customerRow['loyalty_points'] >= $points && $value > 0) {
                $newBalance = (float)$customerRow['loyalty_points'] - $points;
                // Guarded decrement — do not race another terminal's deduction
                $db->query('UPDATE customers SET loyalty_points = loyalty_points - ? WHERE id = ? AND loyalty_points >= ?',
                    [$points, $customerId, $points]);
                $db->table('loyalty_transactions')->insert([
                    'restaurant_id' => $restaurantId,
                    'customer_id'   => $customerId,
                    'order_id'      => $orderId,
                    'transaction_type' => 'redeem',
                    'points'        => $points,
                    'balance_after' => $newBalance,
                    'notes'         => 'Redeemed on order ' . $orderNumber,
                    'created_at'    => date('Y-m-d H:i:s'),
                ]);
                $redeemedPoints = $points;
            } else {
                // Redemption was invalid — recalculate without the discount
                $totals = $this->orderModel->calculateTotals($itemDetails, $this->restaurant, 'flat', 0, true, null, $packingCharge);
                $this->orderModel->update($orderId, [
                    'discount_type'   => 'flat',
                    'discount_amount' => 0,
                    'tax_amount'      => $totals['tax_amount'],
                    'cgst_amount'     => $totals['cgst_amount'],
                    'sgst_amount'     => $totals['sgst_amount'],
                    'service_charge'  => $totals['service_charge'],
                    'round_off'       => $totals['round_off'],
                    'total_amount'    => $totals['total_amount'],
                ]);
            }
        }

        // Auto-print KOT if enabled (never blocks order creation)
        $kotPrintResult = ($this->settingOn('auto_print_kot') && $this->getPlanFeature('allow_kot_print')) ? $this->_printKot($orderId) : null;

        return $this->response->setJSON([
            'success'      => true,
            'order_id'     => $orderId,
            'order_number' => $orderNumber,
            'redeemed_points' => $redeemedPoints,
            'total'        => $totals['total_amount'],
            'kot_print'    => $kotPrintResult,
            'redirect'     => base_url('pos/order/' . $orderId),
        ]);
    }

    // ----------------------------------------------------------------
    // Checkout / Payment
    // ----------------------------------------------------------------
    public function checkout()
    {
        $orderId    = (int)$this->request->getPost('order_id');
        $order      = $this->orderModel->find($orderId);
        if (!$order || $order['branch_id'] != $this->session->get('branch_id')) {
            return $this->response->setJSON(['success' => false, 'message' => 'Order not found']);
        }

        // Prevent double billing — an already-paid order crashes on the
        // invoice UNIQUE(order_id) constraint and returns 500 at the till
        if ($order['payment_status'] === 'paid' || $order['status'] === 'completed') {
            return $this->response->setJSON(['success' => false, 'message' => 'This order has already been billed']);
        }

        $payments = json_decode($this->request->getPost('payments'), true);
        if (!is_array($payments) || empty($payments)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid payment data']);
        }

        // Validate each payment against real methods — an arbitrary string
        // used to hit a MySQL enum error and kill the whole transaction
        $allowedMethods = ['cash', 'card', 'upi', 'wallet', 'online', 'credit'];
        foreach ($payments as $payment) {
            if (empty($payment['method']) || !in_array($payment['method'], $allowedMethods, true)
                || !isset($payment['amount']) || (float)$payment['amount'] <= 0) {
                return $this->response->setJSON(['success' => false, 'message' => 'Invalid payment method or amount']);
            }
        }

        $totalPaid = array_sum(array_column($payments, 'amount'));

        if ($totalPaid < $order['total_amount']) {
            return $this->response->setJSON(['success' => false, 'message' => 'Insufficient payment amount']);
        }

        $change = max(0, $totalPaid - $order['total_amount']);

        $db = \Config\Database::connect();
        $db->transStart();

        foreach ($payments as $payment) {
            // Change is returned against the first payment (usually cash),
            // not duplicated onto every split-payment row
            $db->table('payments')->insert([
                'order_id'         => $orderId,
                'branch_id'        => $order['branch_id'],
                'user_id'          => $this->session->get('user_id'),
                'amount'           => $payment['amount'],
                'payment_method'   => $payment['method'],
                'payment_reference'=> $payment['reference'] ?? null,
                'change_returned'  => $change,
                'status'           => 'completed',
            ]);
        }

        // Update order
        $this->orderModel->update($orderId, [
            'payment_status' => 'paid',
            'paid_amount'    => $totalPaid,
            'balance_amount' => 0,
            'status'         => 'completed',
            'billed_at'      => date('Y-m-d H:i:s'),
            'completed_at'   => date('Y-m-d H:i:s'),
        ]);

        // Generate feedback token for post-meal QR review
        $feedbackToken = bin2hex(random_bytes(16));
        $db->table('orders')->where('id', $orderId)->update(['feedback_token' => $feedbackToken]);

        // Create invoice
        $invoiceNumber = $this->_generateInvoiceNumber($order['restaurant_id'], $order['branch_id']);
        $db->table('invoices')->insert([
            'order_id'       => $orderId,
            'restaurant_id'  => $order['restaurant_id'],
            'branch_id'      => $order['branch_id'],
            'invoice_number' => $invoiceNumber,
            'invoice_date'   => date('Y-m-d'),
            'customer_name'  => $order['customer_name'],
            'customer_phone' => $order['customer_phone'],
            'subtotal'       => $order['subtotal'],
            'discount'       => $order['discount_amount'],
            'tax_amount'     => $order['tax_amount'],
            'total_amount'   => $order['total_amount'],
        ]);

        // Free table — only when no OTHER active order is still open on it
        // (multi-order-per-table is supported via "add round")
        if ($order['table_id']) {
            $remaining = $db->table('orders')
                ->where('table_id', $order['table_id'])
                ->where('id !=', $orderId)
                ->whereIn('status', ['pending', 'confirmed', 'preparing', 'ready', 'served'])
                ->countAllResults();
            if ($remaining === 0) {
                $db->table('tables')->where('id', $order['table_id'])->update(['status' => 'available']);
            }
        }

        // Loyalty points
        if ($order['customer_id']) {
            $this->_awardLoyaltyPoints($order);
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            log_message('error', "Checkout transaction failed for order #$orderId");
            return $this->response->setJSON(['success' => false, 'message' => 'Payment could not be saved — please try again']);
        }

        // Auto print bill (result is reported back to the UI; a printer
        // failure must never silently swallow the bill print)
        $printResult = $this->_printBill($orderId);

        return $this->response->setJSON([
            'success'        => true,
            'invoice_number' => $invoiceNumber,
            'change'         => $change,
            'bill_slip_url'  => base_url('pos/slip/bill/' . $orderId),
            'kot_slip_url'   => base_url('pos/slip/kot/'  . $orderId),
            'print_url'      => base_url('pos/slip/bill/' . $orderId),
            'feedback_url'   => base_url('feedback/' . $feedbackToken),
            'print'          => $printResult,
        ]);
    }

    // ----------------------------------------------------------------
    // Print KOT
    // ----------------------------------------------------------------
    public function printKot()
    {
        $orderId = (int)$this->request->getPost('order_id');
        $result  = $this->_printKot($orderId);
        return $this->response->setJSON($result);
    }

    private function _printKot($orderId)
    {
        $order = $this->orderModel->getOrderWithDetails($orderId);
        if (!$order || $order['branch_id'] != $this->session->get('branch_id')) {
            return ['success' => false, 'error' => 'Order not found'];
        }

        // Generate KOT number — atomic read-and-increment, and the dedicated
        // KOT prefix (not the invoice prefix)
        $this->db->transStart();

        $this->db->query('UPDATE branches SET kot_counter = LAST_INSERT_ID(kot_counter) + 1 WHERE id = ?', [$this->branch['id']]);
        $kotCounter = (int)$this->db->query('SELECT LAST_INSERT_ID() AS n')->getRowArray()['n'];
        $kotNumber  = (($this->branch['kot_prefix'] ?? null) ?: 'KOT') . str_pad($kotCounter, 4, '0', STR_PAD_LEFT);

        $this->db->table('kots')->insert([
            'order_id'    => $orderId,
            'branch_id'   => $this->branch['id'],
            'kot_number'  => $kotNumber,
            'table_number'=> $order['table']['table_number'] ?? 'Takeaway',
            'order_type'  => $order['order_type'],
            'status'      => 'pending',
            'printed_by'  => $this->session->get('user_id'),
            'printed_at'  => date('Y-m-d H:i:s'),
        ]);
        $kotId = $this->db->insertID();

        // Without these rows the Kitchen Display shows every KOT empty —
        // the kitchen never sees what to cook
        foreach ($order['items'] as $item) {
            if (in_array($item['status'], ['cancelled'])) continue;

            $this->db->table('kot_items')->insert([
                'kot_id'        => $kotId,
                'order_item_id' => $item['id'],
                'name'          => $item['name'],
                'quantity'      => $item['quantity'],
                'status'        => 'pending',
            ]);
        }

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            return ['success' => false, 'error' => 'Could not save KOT record'];
        }

        // Print via Thermal Printer; on failure return the web slip so the
        // cashier can print the same KOT from the browser
        // Plan gate: KOT printing is a plan feature — fall back to the
        // web slip so the kitchen ticket is never silently lost
        if (!$this->getPlanFeature('allow_kot_print')) {
            return ['success' => false, 'error' => 'KOT printing is not included in your plan', 'slip_url' => base_url('pos/slip/kot/' . $orderId)];
        }

        $printer = new ThermalPrinter($this->branch);
        $result  = $printer->printKOT($order, $kotNumber);
        if (empty($result['success'])) {
            $result['slip_url'] = base_url('pos/slip/kot/' . $orderId);
        }
        return $result;
    }

    // ----------------------------------------------------------------
    // Print Bill
    // ----------------------------------------------------------------
    public function printBill()
    {
        $orderId = (int)$this->request->getPost('order_id');
        $result  = $this->_printBill($orderId);
        return $this->response->setJSON($result);
    }

    private function _printBill($orderId)
    {
        // Plan gate: thermal bill printing is a plan feature — fall back
        // to the web slip so billing is never blocked
        if (!$this->getPlanFeature('allow_thermal_print')) {
            return ['success' => false, 'error' => 'Thermal printing is not included in your plan — use the web slip', 'slip_url' => base_url('pos/slip/bill/' . $orderId)];
        }

        $order = $this->orderModel->getOrderWithDetails($orderId);
        if (!$order || $order['branch_id'] != $this->session->get('branch_id')) {
            return ['success' => false, 'error' => 'Order not found'];
        }

        $restaurant = $this->restaurant;
        $branch     = $this->branch;

        $printer = new ThermalPrinter($branch);
        $result  = $printer->printBill($order, $restaurant, $branch);
        if (empty($result['success'])) {
            $result['slip_url'] = base_url('pos/slip/bill/' . $orderId);
        }
        return $result;
    }

    private function _generateInvoiceNumber($restaurantId, $branchId)
    {
        $restaurant = $this->restaurantModel->find($restaurantId);
        $prefix     = $restaurant['billing_prefix'] ?: 'INV';

        // Atomic read-and-increment on the restaurant billing counter
        $this->db->query('UPDATE restaurants SET billing_counter = LAST_INSERT_ID(billing_counter) + 1 WHERE id = ?', [$restaurantId]);
        $counter = (int)$this->db->query('SELECT LAST_INSERT_ID() AS n')->getRowArray()['n'];

        return $prefix . '/' . date('Y') . '/' . str_pad($counter, 5, '0', STR_PAD_LEFT);
    }

    private function _awardLoyaltyPoints($order)
    {
        $rule = $this->db->table('loyalty_rules')
            ->where('restaurant_id', $order['restaurant_id'])
            ->where('is_active', 1)
            ->get()->getRowArray();
        if (!$rule) return;

        $points = floor($order['total_amount'] / $rule['earn_amount']) * $rule['earn_points_per_amount'];
        if ($points <= 0) return;

        $customer = $this->db->table('customers')->where('id', $order['customer_id'])->get()->getRowArray();
        $newBalance = $customer['loyalty_points'] + $points;

        $this->db->table('customers')->where('id', $order['customer_id'])->update(['loyalty_points' => $newBalance]);
        $this->db->table('loyalty_transactions')->insert([
            'restaurant_id'   => $order['restaurant_id'],
            'customer_id'     => $order['customer_id'],
            'order_id'        => $order['id'],
            'transaction_type'=> 'earn',
            'points'          => $points,
            'balance_after'   => $newBalance,
        ]);
    }

    private function getOpenOrdersCount()
    {
        return $this->db->table('orders')
            ->where('branch_id', $this->session->get('branch_id'))
            ->whereIn('status', ['pending','confirmed','preparing','ready','served'])
            ->countAllResults();
    }

    public function orderDetail($id)
    {
        $order = $this->orderModel->getOrderWithDetails($id);
        if (!$order) {
            return redirect()->to(base_url('pos'))->with('error', 'Order not found');
        }
        return view('staff/pos/order_detail', [
            'title'      => 'Order ' . $order['order_number'],
            'order'      => $order,
            'restaurant' => $this->restaurant,
            'branch'     => $this->branch,
        ]);
    }

    public function activeOrders()
    {
        $orders = $this->db->table('orders o')
            ->select('o.id, o.order_number, o.order_type, o.status, o.payment_status, o.total_amount, o.created_at, t.table_number,
                      (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as items_count,
                      (SELECT COUNT(*) FROM kots WHERE order_id = o.id) as kot_count')
            ->join('tables t', 't.id = o.table_id', 'left')
            ->where('o.branch_id', $this->session->get('branch_id'))
            ->whereIn('o.status', ['pending','confirmed','preparing','ready','served'])
            ->orderBy('o.created_at', 'ASC')
            ->get()->getResultArray();

        foreach ($orders as &$o) {
            $mins = (time() - strtotime($o['created_at'])) / 60;
            $o['time_ago'] = $mins < 1 ? 'Just now' : (floor($mins) . 'm ago');
        }
        return $this->response->setJSON($orders);
    }

    public function tableOrders($tableId)
    {
        $orders = $this->db->table('orders')
            ->select('id, order_number, order_type, status, payment_status, total_amount, created_at,
                      (SELECT COUNT(*) FROM order_items WHERE order_id = orders.id) as items_count,
                      (SELECT COUNT(*) FROM kots WHERE order_id = orders.id) as kot_count')
            ->where('table_id', $tableId)
            ->whereIn('status', ['pending','confirmed','preparing','ready','served'])
            ->orderBy('created_at', 'ASC')
            ->get()->getResultArray();
        foreach ($orders as &$o) {
            $mins = (time() - strtotime($o['created_at'])) / 60;
            $o['time_ago'] = $mins < 1 ? 'Just now' : (floor($mins) . 'm ago');
        }
        return $this->response->setJSON($orders);
    }

    // ── Add round: append items to existing order ─────────────
    public function addRound()
    {
        $orderId = (int)$this->request->getPost('order_id');
        $order   = $this->orderModel->find($orderId);
        if (!$order || $order['branch_id'] != $this->session->get('branch_id')) {
            return $this->response->setJSON(['success'=>false,'message'=>'Order not found']);
        }
        if (in_array($order['status'], ['cancelled', 'completed']) || $order['payment_status'] === 'paid') {
            return $this->response->setJSON(['success'=>false,'message'=>'Items cannot be added to a billed or cancelled order']);
        }

        $items = json_decode($this->request->getPost('items'), true);
        if (empty($items)) return $this->response->setJSON(['success'=>false,'message'=>'No items in cart']);

        $db = \Config\Database::connect();
        $db->transStart();

        $exclusive = ($this->restaurant['tax_type'] ?? 'inclusive') === 'exclusive';

        foreach ($items as $item) {
            $menuItem = $this->menuModel
                ->where('id', $item['menu_item_id'])
                ->where('restaurant_id', $order['restaurant_id'])
                ->first();
            if (!$menuItem) continue;

            $variantPrice = null;
            if ($item['variant_id']) {
                $variant = $this->menuModel->db->table('menu_variants')
                    ->where('id', $item['variant_id'])
                    ->where('menu_item_id', $menuItem['id'])
                    ->get()->getRowArray();
                if (!$variant) continue;
                $variantPrice = $variant['price'];
            }
            $price = $variantPrice ?? $menuItem['base_price'];

            // Addon pricing must match createOrder and the cart total
            $addonTotal = 0;
            foreach ($item['addons'] ?? [] as $addon) {
                $addonTotal += (float)($addon['price'] ?? 0);
            }
            $price    = round($price + $addonTotal, 2);
            $qty      = max(1, (float)$item['quantity']);
            $taxPct   = (float)$menuItem['tax_percent'];
            $taxAmt   = $exclusive ? round($price * $qty * $taxPct / 100, 2)
                                   : round($price * $qty - ($price * $qty / (1 + $taxPct / 100)), 2);

            $db->table('order_items')->insert([
                'order_id'     => $orderId,
                'menu_item_id' => $menuItem['id'],
                'variant_id'   => $item['variant_id'] ?? null,
                'name'         => $menuItem['name'],
                'variant_name' => $item['variant_name'] ?? null,
                'quantity'     => $qty,
                'unit_price'   => $price,
                'tax_percent'  => $menuItem['tax_percent'],
                'tax_amount'   => $taxAmt,
                'total_price'  => round($price * $qty + ($exclusive ? $taxAmt : 0), 2),
                'notes'        => $item['notes'] ?? '',
                'status'       => 'pending',
            ]);
        }

        // Recalculate totals from all items — respecting the restaurant's
        // tax type (inclusive tax must NOT be added on top again)
        $allItems  = $db->table('order_items')->where('order_id', $orderId)->get()->getResultArray();
        $subtotal  = 0;
        $taxAmount = 0;
        foreach ($allItems as $it) {
            $line = (float)$it['unit_price'] * (float)$it['quantity'];
            $subtotal  += $line;
            $pct        = (float)($it['tax_percent'] ?? 0);
            $taxAmount += $exclusive
                ? round($line * $pct / 100, 2)
                : round($line - ($line / (1 + $pct / 100)), 2);
        }
        $cgst      = round($taxAmount / 2, 2);
        $sgst      = round($taxAmount - $cgst, 2);
        $svcCharge = round(($subtotal - (float)$order['discount_amount']) * ((float)($this->restaurant['service_charge_percent'] ?? 0) / 100), 2);
        $grandTotal = round($subtotal + ($exclusive ? $taxAmount : 0) + $svcCharge - (float)$order['discount_amount']);

        $this->orderModel->update($orderId, [
            'subtotal'       => round($subtotal, 2),
            'tax_amount'     => round($taxAmount, 2),
            'cgst_amount'    => $cgst,
            'sgst_amount'    => $sgst,
            'service_charge' => $svcCharge,
            'total_amount'   => $grandTotal,
            // Status deliberately NOT reset to pending — adding a round
            // must not downgrade a confirmed/preparing/ready order
        ]);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->response->setJSON(['success'=>false,'message'=>'Failed to add items']);
        }

        // Print new KOT for this round
        $printResult = $this->_printKot($orderId);

        return $this->response->setJSON([
            'success'  => true,
            'order_id' => $orderId,
            'print'    => $printResult,
            'redirect' => base_url('pos/order/' . $orderId),
        ]);
    }

    // ── Book a table from POS ─────────────────────────────────
    public function bookTable($tableId)
    {
        if (!$this->ownedTable($tableId)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Table not found']);
        }
        $this->db->table('tables')->where('id', $tableId)->update([
            'status'       => 'booked',
            'booked_name'  => $this->request->getPost('name'),
            'booked_phone' => $this->request->getPost('phone') ?: null,
            'booked_for'   => $this->request->getPost('booked_for') ?: null,
            'booked_note'  => $this->request->getPost('note') ?: null,
        ]);
        return $this->response->setJSON(['success' => true]);
    }

    // ── Cancel booking from POS ───────────────────────────────
    public function cancelTableBooking($tableId)
    {
        if (!$this->ownedTable($tableId)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Table not found']);
        }
        $this->db->table('tables')->where('id', $tableId)->update([
            'status'       => 'available',
            'booked_name'  => null,
            'booked_phone' => null,
            'booked_for'   => null,
            'booked_note'  => null,
        ]);
        return $this->response->setJSON(['success' => true]);
    }

    /**
     * Read a boolean operational preference from the settings table.
     */
    private function settingOn(string $key): bool
    {
        $row = $this->db->table('settings')
            ->where('restaurant_id', $this->session->get('restaurant_id'))
            ->where('`key`', $key)
            ->get()->getRowArray();
        return $row && $row['value'] === '1';
    }

    /**
     * Tenant guard — a table is "ours" when its branch belongs to the
     * session restaurant.
     */    private function ownedTable($tableId)
    {
        return $this->db->table('tables t')
            ->join('branches b', 'b.id = t.branch_id')
            ->where('t.id', $tableId)
            ->where('b.restaurant_id', $this->session->get('restaurant_id'))
            ->countAllResults() > 0;
    }

    // ── Regenerate a table QR token from the POS (redesigned so
    //    cashiers no longer hit the admin-only route) ──────────
    public function generateTableQr($tableId)
    {
        $table = $this->db->table('tables t')
            ->select('t.id')
            ->join('branches b', 'b.id = t.branch_id')
            ->where('t.id', $tableId)
            ->where('b.restaurant_id', $this->session->get('restaurant_id'))
            ->get()->getRowArray();
        if (!$table) {
            return $this->response->setJSON(['success' => false, 'message' => 'Table not found']);
        }

        $token = bin2hex(random_bytes(20));
        $this->db->table('tables')->where('id', $tableId)->update(['qr_token' => $token]);

        return $this->response->setJSON([
            'success' => true,
            'token'   => $token,
            'url'     => base_url('menu/table/' . $token),
        ]);
    }

    // ── Pending customer (QR) orders — for POS notification ───
    public function pendingCustomerOrders()
    {
        $bid    = $this->session->get('branch_id');
        $orders = $this->db->table('orders o')
            ->select('o.id, o.order_number, o.status, o.total_amount, o.customer_name,
                      o.customer_phone, o.estimated_mins, o.created_at,
                      t.table_number,
                      (SELECT COUNT(*) FROM order_items WHERE order_id=o.id) as items_count')
            ->join('tables t', 't.id = o.table_id', 'left')
            ->where('o.branch_id', $bid)
            ->where('o.source', 'qr_customer')
            ->where('o.status', 'pending')
            ->orderBy('o.created_at', 'ASC')
            ->get()->getResultArray();

        foreach ($orders as &$o) {
            $mins = (time() - strtotime($o['created_at'])) / 60;
            $o['waiting'] = $mins < 1 ? 'Just now' : (floor($mins) . 'm ago');
            // Load items
            $o['items'] = $this->db->table('order_items')
                ->select('name, quantity, unit_price, notes')
                ->where('order_id', $o['id'])
                ->get()->getResultArray();
        }
        return $this->response->setJSON(['success' => true, 'orders' => $orders, 'count' => count($orders)]);
    }

    // ── Confirm customer QR order → creates KOT → goes to kitchen ─
    public function confirmCustomerOrder($orderId)
    {
        $order = $this->orderModel->find($orderId);
        if (!$order || $order['branch_id'] != $this->session->get('branch_id')) {
            return $this->response->setJSON(['success' => false, 'message' => 'Order not found']);
        }
        if ($order['status'] !== 'pending') {
            return $this->response->setJSON(['success' => false, 'message' => 'This order is already ' . $order['status']]);
        }

        $db = \Config\Database::connect();
        $db->transStart();

        $this->orderModel->update($orderId, [
            'status'     => 'confirmed',
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        // Occupation: a confirmed dine-in QR order marks its table occupied
        if ($order['table_id'] && in_array($order['order_type'], ['dine_in'])) {
            $db->table('tables')->where('id', $order['table_id'])->update(['status' => 'occupied']);
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->response->setJSON(['success' => false, 'message' => 'Could not confirm order']);
        }

        // Print / create KOT
        $this->_printKot($orderId);

        return $this->response->setJSON([
            'success'  => true,
            'order_id' => $orderId,
            'redirect' => base_url('pos/order/' . $orderId),
        ]);
    }

    // ── Reject a customer QR order ────────────────────────────
    public function rejectCustomerOrder($orderId)
    {
        $order = $this->orderModel->find($orderId);
        if (!$order || $order['branch_id'] != $this->session->get('branch_id')) {
            return $this->response->setJSON(['success' => false, 'message' => 'Order not found']);
        }
        if ($order['status'] !== 'pending') {
            return $this->response->setJSON(['success' => false, 'message' => 'Only pending orders can be rejected']);
        }

        $db = \Config\Database::connect();
        $db->transStart();

        $this->orderModel->update($orderId, [
            'status'           => 'cancelled',
            'cancelled_reason' => 'Rejected by cashier',
            'cancelled_by'     => $this->session->get('user_id'),
            'updated_at'       => date('Y-m-d H:i:s'),
        ]);

        // Release the table unless another active order still sits on it
        if ($order['table_id']) {
            $remaining = $db->table('orders')
                ->where('table_id', $order['table_id'])
                ->where('id !=', $orderId)
                ->whereIn('status', ['pending', 'confirmed', 'preparing', 'ready', 'served'])
                ->countAllResults();
            if ($remaining === 0) {
                $db->table('tables')->where('id', $order['table_id'])->update(['status' => 'available']);
            }
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->response->setJSON(['success' => false, 'message' => 'Could not reject order']);
        }

        return $this->response->setJSON(['success' => true]);
    }

    public function cancelOrder($id)
    {
        $order = $this->orderModel->find($id);

        // Ownership: only orders of the cashier's own branch can be cancelled
        if (!$order || $order['branch_id'] != $this->session->get('branch_id')) {
            return $this->response->setJSON(['success' => false, 'message' => 'Order not found']);
        }
        return $this->response->setJSON($this->doCancel($order, $this->request->getPost('reason') ?: 'Cancelled at POS'));
    }

    // ── Cancel one order — shared by single + bulk flows ─────
    private function doCancel(array $order, string $reason): array
    {
        $id = (int)$order['id'];
        if ($order['status'] === 'cancelled') {
            return ['success' => false, 'message' => 'This order is already cancelled'];
        }
        if ($order['payment_status'] === 'paid') {
            return ['success' => false, 'message' => 'Paid orders cannot be cancelled — use a refund instead.'];
        }
        if ($order['status'] === 'completed') {
            return ['success' => false, 'message' => 'Completed/billed orders cannot be cancelled.'];
        }

        $db = \Config\Database::connect();
        $db->transStart();

        $this->orderModel->update($id, [
            'status'           => 'cancelled',
            'cancelled_reason' => $reason,
            'cancelled_by'     => $this->session->get('user_id'),
        ]);

        // Inventory: recipe ingredients of this order go back to stock
        RecipeEngine::restockOrder($db, $order);

        // Stop the kitchen: cancel any still-open KOTs for this order
        $db->table('kots')
            ->where('order_id', $id)
            ->whereIn('status', ['pending', 'in_progress'])
            ->update(['status' => 'cancelled']);

        // Return redeemed loyalty points — customers lose points only while
        // the order is valid
        if ($order['customer_id'] && $order['discount_type'] === 'loyalty') {
            $redeem = $db->table('loyalty_transactions')
                ->where('order_id', $id)->where('transaction_type', 'redeem')
                ->orderBy('id','DESC')->get()->getRowArray();
            if ($redeem) {
                $points = (float)$redeem['points'];
                $db->query('UPDATE customers SET loyalty_points = loyalty_points + ? WHERE id = ?', [$points, $order['customer_id']]);
                $bal = (float)($db->table('customers')->select('loyalty_points')->where('id', $order['customer_id'])->get()->getRowArray()['loyalty_points'] ?? 0);
                $db->table('loyalty_transactions')->insert([
                    'restaurant_id'    => $order['restaurant_id'],
                    'customer_id'      => $order['customer_id'],
                    'order_id'         => $id,
                    'transaction_type' => 'adjust',
                    'points'           => $points,
                    'balance_after'    => $bal,
                    'notes'            => 'Points returned — order ' . $order['order_number'] . ' cancelled',
                    'created_at'       => date('Y-m-d H:i:s'),
                ]);
            }
        }

        // Free the table ONLY if this was the last active order on it —
        // cancelling one order must not release a table other guests use
        if ($order['table_id']) {
            $remaining = $db->table('orders')
                ->where('table_id', $order['table_id'])
                ->where('id !=', $id)
                ->whereIn('status', ['pending', 'confirmed', 'preparing', 'ready', 'served'])
                ->countAllResults();
            if ($remaining === 0) {
                $db->table('tables')->where('id', $order['table_id'])->update(['status' => 'available']);
            }
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            log_message('error', "Order cancellation transaction failed for order #$id");
            return ['success' => false, 'message' => 'Failed to cancel order — please try again'];
        }

        return [
            'success'  => true,
            'message'  => 'Order #' . $order['order_number'] . ' cancelled',
            'order_id' => (int)$id,
            'table_id' => $order['table_id'] ? (int)$order['table_id'] : null,
        ];
    }

    // ── Bulk cancel from the table sheet ──────────────────────
    public function cancelBulk()
    {
        $ids = json_decode($this->request->getPost('order_ids'), true);
        if (empty($ids) || !is_array($ids)) {
            return $this->response->setJSON(['success' => false, 'message' => 'No orders selected']);
        }
        $reason = $this->request->getPost('reason') ?: 'Bulk cancelled at POS';

        $ok      = 0;
        $skipped = [];
        foreach (array_map('intval', $ids) as $oid) {
            $order = $this->orderModel->find($oid);
            if (!$order || $order['branch_id'] != $this->session->get('branch_id')) {
                $skipped[] = 'order not found';
                continue;
            }
            $res = $this->doCancel($order, $reason . ' (bulk)');
            if (!empty($res['success'])) $ok++;
            else $skipped[] = $res['message'] ?? 'failed';
        }

        return $this->response->setJSON([
            'success'   => $ok > 0,
            'message'   => $ok
                ? "$ok order(s) cancelled" . ($skipped ? ' · ' . count($skipped) . ' skipped' : '')
                : ($skipped[0] ?? 'Nothing cancelled'),
            'cancelled' => $ok,
        ]);
    }

    // ── Bill several orders together in one payment ───────────
    public function billBulk()
    {
        $ids    = json_decode($this->request->getPost('order_ids'), true);
        $method = $this->request->getPost('payment_method') ?: 'cash';
        if (!in_array($method, ['cash', 'card', 'upi', 'wallet', 'online', 'credit'], true)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid payment method']);
        }
        if (empty($ids) || !is_array($ids)) {
            return $this->response->setJSON(['success' => false, 'message' => 'No orders selected']);
        }

        $db = \Config\Database::connect();
        $orders = $db->table('orders')
            ->whereIn('id', array_map('intval', $ids))
            ->where('branch_id', $this->session->get('branch_id'))
            ->whereIn('payment_status', ['unpaid', 'partial'])
            ->whereIn('status', ['pending', 'confirmed', 'preparing', 'ready', 'served'])
            ->orderBy('id', 'ASC')
            ->get()->getResultArray();
        if (empty($orders)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Selected orders are already billed or cancelled']);
        }
        $total = array_sum(array_column($orders, 'total_amount'));

        $db->transStart();
        foreach ($orders as $o) {
            $db->table('payments')->insert([
                'order_id'       => $o['id'],
                'branch_id'      => $o['branch_id'],
                'user_id'        => $this->session->get('user_id'),
                'amount'         => $o['total_amount'],
                'payment_method' => $method,
                'change_returned'=> 0,
                'status'         => 'completed',
            ]);
            $db->table('orders')->where('id', $o['id'])->update([
                'payment_status' => 'paid',
                'paid_amount'    => $o['total_amount'],
                'balance_amount' => 0,
                'status'         => 'completed',
                'billed_at'      => date('Y-m-d H:i:s'),
                'completed_at'   => date('Y-m-d H:i:s'),
                'feedback_token' => bin2hex(random_bytes(16)),
            ]);
        }
        $db->transComplete();
        if ($db->transStatus() === false) {
            return $this->response->setJSON(['success' => false, 'message' => 'Billing failed — please try again']);
        }

        // Free tables that now have no remaining active orders
        foreach (array_unique(array_filter(array_column($orders, 'table_id'))) as $tid) {
            $remaining = $db->table('orders')->where('table_id', $tid)
                ->whereIn('status', ['pending', 'confirmed', 'preparing', 'ready'])
                ->countAllResults();
            if ($remaining === 0) {
                $db->table('tables')->where('id', $tid)->update(['status' => 'available']);
            }
        }

        return $this->response->setJSON([
            'success'  => true,
            'message'  => count($orders) . ' order(s) billed together — ' . number_format($total, 2),
            'total'    => (float)$total,
            'slip_url' => base_url('pos/slip/bill-multi') . '?ids=' . implode(',', array_column($orders, 'id')),
        ]);
    }

    public function applyCoupon()
    {        $code     = $this->request->getPost('coupon_code');
        $subtotal = (float)$this->request->getPost('subtotal');
        $coupon   = $this->db->table('coupons')
            ->where('code', $code)
            ->where('restaurant_id', $this->session->get('restaurant_id'))
            ->where('is_active', 1)
            ->get()->getRowArray();

        if (!$coupon) return $this->response->setJSON(['success'=>false,'message'=>'Invalid coupon']);

        // Enforce validity rules that were previously ignored
        $today = date('Y-m-d');
        if (!empty($coupon['valid_from']) && $today < $coupon['valid_from']) {
            return $this->response->setJSON(['success'=>false,'message'=>'Coupon is not active yet']);
        }
        if (!empty($coupon['valid_to']) && $today > $coupon['valid_to']) {
            return $this->response->setJSON(['success'=>false,'message'=>'Coupon has expired']);
        }
        if (!empty($coupon['min_order_amount']) && $subtotal < (float)$coupon['min_order_amount']) {
            return $this->response->setJSON(['success'=>false,'message'=>'Minimum order amount is ₹'.number_format((float)$coupon['min_order_amount'], 0)]);
        }
        if (!empty($coupon['usage_limit']) && (int)$coupon['usage_count'] >= (int)$coupon['usage_limit']) {
            return $this->response->setJSON(['success'=>false,'message'=>'Coupon usage limit reached']);
        }
        // Per-customer limit — how many times THIS customer may use it
        $perPage = (int)($coupon['per_user_limit'] ?? 0);
        if ($perPage > 0) {
            $custId = (int)($this->request->getPost('customer_id') ?: 0);
            if (!$custId) {
                return $this->response->setJSON(['success'=>false,'message'=>'Attach a customer to use this coupon']);
            }
            $used = $this->db->table('orders')
                ->where('coupon_code', $coupon['code'])
                ->where('customer_id', $custId)
                ->where('status !=', 'cancelled')
                ->where('restaurant_id', $this->session->get('restaurant_id'))
                ->countAllResults();
            if ($used >= $perPage) {
                return $this->response->setJSON(['success'=>false,'message'=>'This customer has already used this coupon ' . $used . ' time(s)']);
            }
        }

        $discount = $coupon['discount_type'] === 'percent'
            ? min($subtotal * $coupon['discount_value'] / 100, (float)$coupon['max_discount_amount'] > 0 ? (float)$coupon['max_discount_amount'] : PHP_INT_MAX)
            : $coupon['discount_value'];
        return $this->response->setJSON(['success'=>true,'discount'=>round($discount,2)]);
    }

    /**
     * Preview a loyalty redemption — same rule engine the order creation
     * uses, so the POS shows the exact rupee value the customer gets.
     */
    public function loyaltyPreview()
    {
        // Plan gate: loyalty is a plan feature
        if (!$this->getPlanFeature('allow_loyalty')) {
            return $this->response->setJSON(['success' => false, 'message' => 'Loyalty program is not included in your current plan']);
        }

        $points = (float)$this->request->getPost('points');
        $sub    = (float)$this->request->getPost('subtotal');
        $custId = (int)($this->request->getPost('customer_id') ?: 0);

        if ($points <= 0) {
            return $this->response->setJSON(['success' => false, 'message' => 'Enter a valid number of points']);
        }

        $rule = $this->db->table('loyalty_rules')
            ->where('restaurant_id', $this->session->get('restaurant_id'))
            ->where('is_active', 1)
            ->orderBy('id','ASC')->get()->getRowArray();
        if (!$rule) {
            return $this->response->setJSON(['success' => false, 'message' => 'Loyalty program is not active. Contact admin.']);
        }
        if ($points < (float)$rule['min_redeem_points']) {
            return $this->response->setJSON(['success' => false, 'message' => 'Minimum redeemable points is ' . (int)$rule['min_redeem_points']]);
        }
        if ($custId) {
            $row = $this->db->table('customers')->select('loyalty_points')->where('id', $custId)->where('restaurant_id', $this->session->get('restaurant_id'))->get()->getRowArray();
            if (!$row || (float)$row['loyalty_points'] < $points) {
                return $this->response->setJSON(['success' => false, 'message' => 'Customer does not have enough points']);
            }
        }

        $cap = round($sub * (float)$rule['max_redeem_percent'] / 100, 2);
        $amt = max(0, min($points * (float)$rule['redeem_value'], $cap, $sub));
        if ($amt <= 0) {
            return $this->response->setJSON(['success' => false, 'message' => 'Points value is not applicable to this order']);
        }

        return $this->response->setJSON(['success' => true, 'discount' => $amt, 'points' => $points]);
    }

    // ── Web-based KOT Slip (fallback when no thermal printer) ──
    public function kotSlip($orderId)
    {
        $order      = $this->orderModel->getOrderWithDetails($orderId);
        if (!$order) return redirect()->to(base_url('pos'));

        // Get latest KOT number for this order
        $db  = \Config\Database::connect();
        $kot = $db->table('kots')
            ->where('order_id', $orderId)
            ->orderBy('created_at','DESC')
            ->get()->getRowArray();

        $kotNumber  = $kot['kot_number'] ?? ('KOT-' . str_pad($orderId, 4, '0', STR_PAD_LEFT));
        $restaurant = $db->table('restaurants')
            ->where('id', $this->session->get('restaurant_id'))
            ->get()->getRowArray();
        $branch = $db->table('branches')
            ->where('id', $this->session->get('branch_id'))
            ->get()->getRowArray();

        return view('staff/pos/kot_slip', [
            'order'      => $order,
            'kotNumber'  => $kotNumber,
            'restaurant' => $restaurant ?? [],
            'branch'     => $branch ?? [],
        ]);
    }

    // ── Web-based Bill Slip (fallback when no thermal printer) ──
    public function billSlip($orderId)
    {
        $db         = \Config\Database::connect();
        $order      = $this->orderModel->getOrderWithDetails($orderId);
        if (!$order) return redirect()->to(base_url('pos'));

        $invoice    = $db->table('invoices')
            ->where('order_id', $orderId)
            ->get()->getRowArray();
        $restaurant = $db->table('restaurants')
            ->where('id', $this->session->get('restaurant_id'))
            ->get()->getRowArray();
        $branch     = $db->table('branches')
            ->where('id', $this->session->get('branch_id'))
            ->get()->getRowArray();

        return view('staff/pos/bill_slip', [
            'order'      => $order,
            'invoice'    => $invoice,
            'restaurant' => $restaurant ?? [],
            'branch'     => $branch ?? [],
            'whitelabel' => $this->getPlanFeature('allow_whitelabel'),
        ]);
    }

    // ── Combined receipt for several orders billed together ───
    public function billSlipMulti()
    {
        $db  = \Config\Database::connect();
        $ids = array_filter(array_map('intval', explode(',', (string)($this->request->getGet('ids') ?? ''))));
        $orders = [];
        foreach ($ids as $oid) {
            $o = $this->orderModel->getOrderWithDetails($oid);
            if ($o && $o['branch_id'] == $this->session->get('branch_id')) {
                $orders[] = $o;
            }
        }
        if (empty($orders)) return redirect()->to(base_url('pos'));

        $restaurant = $db->table('restaurants')
            ->where('id', $this->session->get('restaurant_id'))
            ->get()->getRowArray();
        $branch     = $db->table('branches')
            ->where('id', $this->session->get('branch_id'))
            ->get()->getRowArray();

        return view('staff/pos/bill_slip_multi', [
            'orders'     => $orders,
            'restaurant' => $restaurant ?? [],
            'branch'     => $branch ?? [],
            'whitelabel' => $this->getPlanFeature('allow_whitelabel'),
        ]);
    }
}
