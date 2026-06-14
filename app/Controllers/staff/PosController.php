<?php
namespace App\Controllers\Staff;

use App\Controllers\BaseController;
use App\Models\OrderModel;
use App\Models\MenuModel;
use App\Models\TableModel;
use App\Models\CustomerModel;
use App\Models\RestaurantModel;
use App\Libraries\ThermalPrinter;
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
        $tableId = $this->request->getGet('table');
        $data = [
            'title'      => 'New Order',
            'order_type' => $type,
            'table_id'   => $tableId,
            'table'      => $tableId ? $this->tableModel->find($tableId) : null,
            'categories' => $this->menuModel->getCategoriesWithItems($this->session->get('restaurant_id'), $this->session->get('branch_id')),
            'restaurant' => $this->restaurant,
            'branch'     => $this->branch,
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

        // Build item details from DB
        $itemDetails = [];
        foreach ($items as $item) {
            $menuItem = $this->menuModel->find($item['menu_item_id']);
            if (!$menuItem) continue;

            $price = $item['variant_id']
                ? $this->menuModel->getVariantPrice($item['variant_id'])
                : $menuItem['base_price'];

            $itemDetails[] = [
                'menu_item_id' => $menuItem['id'],
                'variant_id'   => $item['variant_id'] ?? null,
                'name'         => $menuItem['name'],
                'variant_name' => $item['variant_name'] ?? null,
                'quantity'     => max(1, (float)$item['quantity']),
                'unit_price'   => $price,
                'tax_percent'  => $menuItem['tax_percent'],
                'notes'        => $item['notes'] ?? '',
                'addons'       => $item['addons'] ?? [],
            ];
        }

        // Calculate totals
        $discountType  = $this->request->getPost('discount_type') ?: 'flat';
        $discountValue = (float)($this->request->getPost('discount_value') ?? 0);
        $totals = $this->orderModel->calculateTotals($itemDetails, $this->restaurant, $discountType, $discountValue, true);

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
            'order_type'      => $this->request->getPost('order_type'),
            'status'          => 'pending',
            'customer_name'   => $this->request->getPost('customer_name') ?: null,
            'customer_phone'  => $this->request->getPost('customer_phone') ?: null,
            'no_of_guests'    => (int)($this->request->getPost('guests') ?? 1),
            'subtotal'        => $totals['subtotal'],
            'discount_type'   => $discountType,
            'discount_amount' => $totals['discount_amount'],
            'tax_amount'      => $totals['tax_amount'],
            'cgst_amount'     => $totals['cgst_amount'],
            'sgst_amount'     => $totals['sgst_amount'],
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

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->response->setJSON(['success' => false, 'message' => 'Order creation failed']);
        }

        // Auto-print KOT if enabled
        if ($this->session->get('auto_kot')) {
            $this->_printKot($orderId);
        }

        return $this->response->setJSON([
            'success'      => true,
            'order_id'     => $orderId,
            'order_number' => $orderNumber,
            'total'        => $totals['total_amount'],
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
        if (!$order) return $this->response->setJSON(['success' => false, 'message' => 'Order not found']);

        $payments   = json_decode($this->request->getPost('payments'), true);
        $totalPaid  = array_sum(array_column($payments, 'amount'));

        if ($totalPaid < $order['total_amount']) {
            return $this->response->setJSON(['success' => false, 'message' => 'Insufficient payment amount']);
        }

        $db = \Config\Database::connect();
        $db->transStart();

        foreach ($payments as $payment) {
            $db->table('payments')->insert([
                'order_id'         => $orderId,
                'branch_id'        => $order['branch_id'],
                'user_id'          => $this->session->get('user_id'),
                'amount'           => $payment['amount'],
                'payment_method'   => $payment['method'],
                'payment_reference'=> $payment['reference'] ?? null,
                'change_returned'  => max(0, $totalPaid - $order['total_amount']),
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

        // Free table
        if ($order['table_id']) {
            $db->table('tables')->where('id', $order['table_id'])->update(['status' => 'available']);
        }

        // Loyalty points
        if ($order['customer_id']) {
            $this->_awardLoyaltyPoints($order);
        }

        $db->transComplete();

        // Auto print bill
        $this->_printBill($orderId);

        return $this->response->setJSON([
            'success'        => true,
            'invoice_number' => $invoiceNumber,
            'change'         => max(0, $totalPaid - $order['total_amount']),
            'print_url'      => base_url('pos/order/' . $orderId . '/print'),
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
        if (!$order) return ['success' => false];

        // Generate KOT number
        $kotCounter = (int)$this->branch['kot_counter'];
        $kotNumber  = ($this->branch['billing_prefix'] ?: 'KOT') . str_pad($kotCounter, 4, '0', STR_PAD_LEFT);
        $this->db->table('branches')->where('id', $this->branch['id'])->update(['kot_counter' => $kotCounter + 1]);

        $kotId = $this->db->table('kots')->insert([
            'order_id'    => $orderId,
            'branch_id'   => $this->branch['id'],
            'kot_number'  => $kotNumber,
            'table_number'=> $order['table']['table_number'] ?? 'Takeaway',
            'order_type'  => $order['order_type'],
            'status'      => 'pending',
            'printed_by'  => $this->session->get('user_id'),
            'printed_at'  => date('Y-m-d H:i:s'),
        ]);

        // Print via Thermal Printer
        $printer = new ThermalPrinter($this->branch);
        return $printer->printKOT($order, $kotNumber);
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
        $order      = $this->orderModel->getOrderWithDetails($orderId);
        $restaurant = $this->restaurant;
        $branch     = $this->branch;

        $printer = new ThermalPrinter($branch);
        return $printer->printBill($order, $restaurant, $branch);
    }

    private function _generateInvoiceNumber($restaurantId, $branchId)
    {
        $restaurant = $this->restaurantModel->find($restaurantId);
        $prefix     = $restaurant['billing_prefix'] ?: 'INV';
        $counter    = (int)$restaurant['billing_counter'];
        $this->restaurantModel->update($restaurantId, ['billing_counter' => $counter + 1]);
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
            ->select('o.id, o.order_number, o.order_type, o.status, o.total_amount, o.created_at, t.table_number,
                      (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as items_count')
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
            ->select('id, order_number, order_type, status, total_amount,
                      (SELECT COUNT(*) FROM order_items WHERE order_id = orders.id) as items_count')
            ->where('table_id', $tableId)
            ->whereIn('status', ['pending','confirmed','preparing','ready','served'])
            ->get()->getResultArray();
        return $this->response->setJSON($orders);
    }

    public function cancelOrder($id)
    {
        $order = $this->orderModel->find($id);
        if (!$order) return $this->response->setJSON(['success'=>false]);
        $this->orderModel->update($id, [
            'status'           => 'cancelled',
            'cancelled_reason' => $this->request->getPost('reason') ?? 'Cancelled at POS',
            'cancelled_by'     => $this->session->get('user_id'),
        ]);
        if ($order['table_id']) {
            $this->db->table('tables')->where('id', $order['table_id'])->update(['status'=>'available']);
        }
        return $this->response->setJSON(['success' => true]);
    }

    public function applyCoupon()
    {
        $code     = $this->request->getPost('coupon_code');
        $subtotal = (float)$this->request->getPost('subtotal');
        $coupon   = $this->db->table('coupons')
            ->where('code', $code)
            ->where('restaurant_id', $this->session->get('restaurant_id'))
            ->where('is_active', 1)
            ->get()->getRowArray();

        if (!$coupon) return $this->response->setJSON(['success'=>false,'message'=>'Invalid coupon']);
        $discount = $coupon['discount_type'] === 'percent'
            ? min($subtotal * $coupon['discount_value'] / 100, $coupon['max_discount_amount'] ?: PHP_INT_MAX)
            : $coupon['discount_value'];
        return $this->response->setJSON(['success'=>true,'discount'=>round($discount,2)]);
    }
}
