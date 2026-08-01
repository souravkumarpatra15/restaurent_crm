<?php
namespace App\Controllers\Manager;
use App\Controllers\BaseController;

class StaffPerfController extends BaseController
{
    public function index()
    {
        $db   = \Config\Database::connect();
        $rid  = session('restaurant_id');
        $bid  = session('branch_id');
        $from = $this->request->getGet('from') ?? date('Y-m-d', strtotime('-30 days'));
        $to   = $this->request->getGet('to')   ?? date('Y-m-d');

        // Per-staff order and revenue stats
        $staffStats = $db->table('orders o')
            ->select('o.user_id, u.name as staff_name, r.name as role_name,
                      COUNT(o.id) as total_orders,
                      SUM(o.total_amount) as total_revenue,
                      AVG(o.total_amount) as avg_order_value,
                      SUM(o.discount_amount) as total_discount,
                      COUNT(CASE WHEN o.status="cancelled" THEN 1 END) as cancelled_orders,
                      AVG(TIMESTAMPDIFF(MINUTE, o.created_at, o.completed_at)) as avg_completion_min')
            ->join('users u','u.id=o.user_id')
            ->join('roles r','r.id=u.role_id')
            ->where('o.restaurant_id',$rid)
            ->where('o.branch_id',$bid)
            ->where('DATE(o.created_at) >=', $from)
            ->where('DATE(o.created_at) <=', $to)
            ->groupBy('o.user_id')
            ->orderBy('total_revenue','DESC')
            ->get()->getResultArray();

        // KOT processing time per kitchen staff
        $kotPerf = $db->table('kots k')
            ->select('k.prepared_by, u.name as staff_name,
                      COUNT(k.id) as total_kots,
                      AVG(TIMESTAMPDIFF(MINUTE, k.created_at, k.ready_at)) as avg_prep_min,
                      COUNT(CASE WHEN TIMESTAMPDIFF(MINUTE,k.created_at,k.ready_at) > 20 THEN 1 END) as overdue_kots')
            ->join('users u','u.id=k.prepared_by','left')
            ->where('k.branch_id',$bid)
            ->where('DATE(k.created_at) >=', $from)
            ->where('DATE(k.created_at) <=', $to)
            ->where('k.prepared_by IS NOT NULL','',false)
            ->groupBy('k.prepared_by')
            ->get()->getResultArray();

        // Hourly performance
        $hourly = $db->table('orders')
            ->select('HOUR(created_at) as hr, COUNT(*) as cnt, SUM(total_amount) as rev')
            ->where('restaurant_id',$rid)->where('branch_id',$bid)
            ->where('DATE(created_at) >=', $from)->where('DATE(created_at) <=', $to)
            ->where('status !=','cancelled')
            ->groupBy('HOUR(created_at)')->orderBy('hr','ASC')
            ->get()->getResultArray();

        return view('admin/staff_perf/index', [
            'pageTitle'  => 'Staff Performance',
            'staffStats' => $staffStats,
            'kotPerf'    => $kotPerf,
            'hourly'     => $hourly,
            'from'       => $from,
            'to'         => $to,
            'userName'   => session('user_name'),
            'userRole'   => session('role_slug'),
        ]);
    }
}
