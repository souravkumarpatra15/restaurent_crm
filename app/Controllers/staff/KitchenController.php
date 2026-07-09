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
        $db     = \Config\Database::connect();
        $db->table('kots')->where('id',$kotId)->update(['status'=>$status,'updated_at'=>date('Y-m-d H:i:s')]);
        if ($status === 'ready') {
            $db->table('kot_items')->where('kot_id',$kotId)->update(['status'=>'ready']);
        }
        return $this->response->setJSON(['success'=>true]);
    }

    public function summary()
    {
        return view('admin/coming_soon', ['pageTitle'=>'Shift Summary','userName'=>session('user_name'),'userRole'=>session('role_slug')]);
    }
}
