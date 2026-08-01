<?php
namespace App\Controllers\Manager;
use App\Controllers\BaseController;

class WasteController extends BaseController
{
    public function index()
    {
        $db  = \Config\Database::connect();
        $bid = session('branch_id');
        $rid = session('restaurant_id');

        $from = $this->request->getGet('from') ?? date('Y-m-d', strtotime('-30 days'));
        $to   = $this->request->getGet('to')   ?? date('Y-m-d');

        $logs = $db->table('waste_logs wl')
            ->select('wl.*, u.name as logged_by_name')
            ->join('users u','u.id=wl.logged_by','left')
            ->where('wl.branch_id',$bid)
            ->where('DATE(wl.created_at) >=', $from)
            ->where('DATE(wl.created_at) <=', $to)
            ->orderBy('wl.created_at','DESC')
            ->get()->getResultArray();

        $totalWasteCost  = array_sum(array_column($logs,'cost'));
        $byReason = [];
        foreach ($logs as $l) {
            $byReason[$l['reason']] = ($byReason[$l['reason']] ?? 0) + $l['cost'];
        }
        arsort($byReason);

        $byItem = $db->table('waste_logs')
            ->select('item_name, SUM(quantity) as qty, SUM(cost) as cost')
            ->where('branch_id',$bid)
            ->where('DATE(created_at) >=', $from)->where('DATE(created_at) <=', $to)
            ->groupBy('item_name')->orderBy('cost','DESC')->limit(10)
            ->get()->getResultArray();

        // Cancelled order items (auto waste tracking)
        $cancelledItems = $db->table('order_items oi')
            ->join('orders o','o.id=oi.order_id')
            ->select('oi.name, SUM(oi.quantity) as qty, SUM(oi.total_price) as potential_loss')
            ->where('o.branch_id',$bid)->where('o.status','cancelled')
            ->where('DATE(o.created_at) >=', $from)->where('DATE(o.created_at) <=', $to)
            ->groupBy('oi.name')->orderBy('potential_loss','DESC')->limit(10)
            ->get()->getResultArray();

        return view('admin/waste/index', [
            'pageTitle'      => 'Waste Tracker',
            'logs'           => $logs,
            'totalWasteCost' => $totalWasteCost,
            'byReason'       => $byReason,
            'byItem'         => $byItem,
            'cancelledItems' => $cancelledItems,
            'from'           => $from,
            'to'             => $to,
            'userName'       => session('user_name'),
            'userRole'       => session('role_slug'),
        ]);
    }

    public function store()
    {
        $db = \Config\Database::connect();
        $db->table('waste_logs')->insert([
            'restaurant_id' => session('restaurant_id'),
            'branch_id'     => session('branch_id'),
            'logged_by'     => session('user_id'),
            'item_name'     => $this->request->getPost('item_name'),
            'quantity'      => $this->request->getPost('quantity'),
            'unit'          => $this->request->getPost('unit') ?? 'portion',
            'cost'          => $this->request->getPost('cost') ?? 0,
            'reason'        => $this->request->getPost('reason'),
            'notes'         => $this->request->getPost('notes'),
            'created_at'    => date('Y-m-d H:i:s'),
        ]);
        return $this->response->setJSON(['success'=>true]);
    }
}
