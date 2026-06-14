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
        $reason = $this->request->getPost('reason') ?? 'Cancelled by manager';
        $this->model->update($id, [
            'status'           => 'cancelled',
            'cancelled_reason' => $reason,
            'cancelled_by'     => session('user_id'),
        ]);
        // Free table
        $order = $this->model->find($id);
        if ($order['table_id']) {
            \Config\Database::connect()->table('tables')->where('id',$order['table_id'])->update(['status'=>'available']);
        }
        return $this->response->setJSON(['success' => true]);
    }
}
