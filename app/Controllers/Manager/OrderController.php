<?php
namespace App\Controllers\Manager;
use App\Controllers\BaseController;
use App\Models\OrderModel;

class OrderController extends BaseController
{
    protected $model;
    public function __construct() { $this->model = new OrderModel(); }

    public function index()
    {
        $db  = \Config\Database::connect();
        $rid = session('restaurant_id');
        $from = $this->request->getGet('from') ?? date('Y-m-d');
        $to   = $this->request->getGet('to')   ?? date('Y-m-d');

        $orders = $db->table('orders o')
            ->select('o.*, t.table_number, u.name as staff_name')
            ->join('tables t','t.id = o.table_id','left')
            ->join('users u','u.id = o.user_id','left')
            ->where('o.restaurant_id', $rid)
            ->where('DATE(o.created_at) >=', $from)
            ->where('DATE(o.created_at) <=', $to)
            ->orderBy('o.created_at','DESC')
            ->get()->getResultArray();

        return view('admin/orders/index', [
            'pageTitle' => 'Orders', 'orders' => $orders,
            'from' => $from, 'to' => $to,
            'userName'  => session('user_name'), 'userRole' => session('role_slug'),
            'restaurantName' => session('restaurant_name'),
        ]);
    }

    public function view($id)
    {
        $order = $this->model->getOrderWithDetails($id);
        return view('admin/orders/view', [
            'pageTitle' => 'Order #' . ($order['order_number'] ?? $id),
            'order'     => $order,
            'userName'  => session('user_name'), 'userRole' => session('role_slug'),
            'restaurantName' => session('restaurant_name'),
        ]);
    }

    public function cancel($id)
    {
        $order = $this->model->find($id);

        // Ownership scoping — a restaurant must only cancel its own orders
        if (!$order || $order['restaurant_id'] != session('restaurant_id')) {
            return $this->response->setJSON(['success' => false, 'message' => 'Order not found']);
        }
        if ($order['status'] === 'cancelled') {
            return $this->response->setJSON(['success' => false, 'message' => 'This order is already cancelled']);
        }
        if ($order['payment_status'] === 'paid') {
            return $this->response->setJSON(['success' => false, 'message' => 'Paid orders cannot be cancelled — process a refund instead.']);
        }
        if ($order['status'] === 'completed') {
            return $this->response->setJSON(['success' => false, 'message' => 'Completed/billed orders cannot be cancelled.']);
        }

        $reason  = $this->request->getPost('reason') ?? 'Cancelled by manager';
        $db  = \Config\Database::connect();
        $db->transStart();

        $this->model->update($id, [
            'status'           => 'cancelled',
            'cancelled_reason' => $reason,
            'cancelled_by'     => session('user_id'),
        ]);

        // Cancel any still-open KOTs so the kitchen stops cooking
        $db->table('kots')
            ->where('order_id', $id)
            ->whereIn('status', ['pending', 'in_progress'])
            ->update(['status' => 'cancelled']);

        // Free the table only when this was the last active order on it
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
            return $this->response->setJSON(['success' => false, 'message' => 'Failed to cancel order — please try again']);
        }

        return $this->response->setJSON(['success' => true, 'message' => 'Order cancelled']);
    }

    /**
     * Refund a paid/completed order. Marks the order refunded (payment_status
     * refunded) so the incomplete-orders and payments reports pick it up.
     */
    public function refund($id)
    {
        $order = $this->model->find($id);
        if (!$order || $order['restaurant_id'] != session('restaurant_id')) {
            return $this->response->setJSON(['success' => false, 'message' => 'Order not found']);
        }
        if ($order['status'] === 'refunded') {
            return $this->response->setJSON(['success' => false, 'message' => 'This order is already refunded']);
        }
        if ($order['payment_status'] !== 'paid') {
            return $this->response->setJSON(['success' => false, 'message' => 'Only paid orders can be refunded']);
        }

        $reason = $this->request->getPost('reason') ?? 'Refunded by manager';
        $db = \Config\Database::connect();
        $db->transStart();

        $this->model->update($id, [
            'status'           => 'refunded',
            'payment_status'   => 'refunded',
            'cancelled_reason' => $reason,
            'cancelled_by'     => session('user_id'),
            'updated_at'       => date('Y-m-d H:i:s'),
        ]);

        // Log a refund payment row so the drawers/reports balance out
        $totalPaid = (float)($db->table('payments')->selectSum('amount')->where('order_id', $id)
            ->get()->getRowArray()['amount'] ?? 0);
        if ($totalPaid > 0) {
            $db->table('payments')->insert([
                'order_id'       => $id,
                'payment_method' => 'other',
                'amount'         => -$totalPaid,
                'status'         => 'success',
                'transaction_id' => 'REFUND',
                'created_at'     => date('Y-m-d H:i:s'),
            ]);
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->response->setJSON(['success' => false, 'message' => 'Refund failed — please try again']);
        }

        return $this->response->setJSON(['success' => true, 'message' => 'Order refunded']);
    }
}
