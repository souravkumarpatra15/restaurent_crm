<?php
namespace App\Controllers\Staff;
use App\Controllers\BaseController;

class KitchenController extends BaseController
{
    public function index()
    {
        $db  = \Config\Database::connect();
        $bid = session('branch_id');

        $kots = $db->table('kots k')
            ->select('k.*, o.payment_status, o.order_number, o.order_type, o.total_amount, o.customer_name')
            ->join('orders o','o.id=k.order_id','left')
            ->where('k.branch_id', $bid)
            ->whereIn('k.status', ['pending','in_progress'])
            ->orderBy('k.created_at','ASC')
            ->get()->getResultArray();

        foreach ($kots as &$kot) {
            $kot['items'] = $db->table('kot_items ki')
                ->select('ki.*, oi.notes')
                ->join('order_items oi','oi.id = ki.order_item_id','left')
                ->where('ki.kot_id', $kot['id'])
                ->get()->getResultArray();
        }

        return view('staff/kitchen/index', [
            'pageTitle' => 'Kitchen Display',
            'kots'      => $kots,
            'userName'  => session('user_name'),
            'userRole'  => session('role_slug'),
            'restaurantName' => session('restaurant_name'),
        ]);
    }

    public function updateStatus()
    {
        $kotId  = $this->request->getPost('kot_id');
        $status = $this->request->getPost('status');

        // Validate against the kots status enum (an arbitrary value used to
        // throw a MySQL error)
        if (!in_array($status, ['pending','in_progress','ready','served','cancelled'], true)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid status']);
        }

        $db = \Config\Database::connect();
        $bid = session('branch_id');

        // Branch scoping only update our own KOTs
        $kot = $db->table('kots k')
            ->select('k.id, k.order_id')
            ->join('orders o', 'o.id = k.order_id')
            ->where('k.id', $kotId)
            ->where('k.branch_id', $bid)
            ->where('o.branch_id', $bid)
            ->get()->getRowArray();
        if (!$kot) {
            return $this->response->setJSON(['success' => false, 'message' => 'KOT not found']);
        }

        $update = ['status' => $status];
        if ($status === 'ready') {
            $update['ready_at'] = date('Y-m-d H:i:s');
        }
        if ($status === 'ready' || $status === 'served') {
            $update['prepared_by'] = $kot['prepared_by'] ?? session('user_id');
        }
        if ($status === 'served') {
            $update['served_at'] = date('Y-m-d H:i:s');
        }

        $db->transStart();

        $db->table('kots')->where('id', $kotId)->update($update);

        // Mirror the status down to the KOT items and the original order items
        // (they used to stay "pending" forever in the POS order view)
        $itemStatus = match ($status) {
            'ready', 'served' => 'ready',
            'in_progress'     => 'preparing',
            default           => null,
        };
        if ($itemStatus !== null) {
            $db->table('kot_items')->where('kot_id', $kotId)->update(['status' => $itemStatus]);
        }

        // order_items has the wider status set
        $orderItemStatus = match ($status) {
            'ready'       => 'ready',
            'served'      => 'served',
            'in_progress' => 'preparing',
            'cancelled'   => 'cancelled',
            default       => null,
        };
        if ($orderItemStatus !== null) {
            $orderItems = $db->table('kot_items')->select('order_item_id')->where('kot_id', $kotId)->get()->getResultArray();
            if ($orderItems) {
                $db->table('order_items')
                    ->whereIn('id', array_column($orderItems, 'order_item_id'))
                    ->update(['status' => $orderItemStatus]);
            }
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->response->setJSON(['success' => false, 'message' => 'Status update failed']);
        }

        return $this->response->setJSON(['success' => true]);
    }

    public function summary()
    {
        return view('admin/coming_soon', ['pageTitle'=>'Shift Summary','userName'=>session('user_name'),'userRole'=>session('role_slug')]);
    }
}
