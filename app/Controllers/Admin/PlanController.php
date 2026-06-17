<?php
namespace App\Controllers\Admin;
use App\Controllers\BaseController;

class PlanController extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();
        $plans = $db->table('saas_plans sp')
            ->select('sp.*, (SELECT COUNT(*) FROM restaurants WHERE plan_id=sp.id AND subscription_status="active") as subscriber_count')
            ->orderBy('sp.sort_order','ASC')
            ->get()->getResultArray();

        return view('admin/plans/index', [
            'pageTitle' => 'Subscription Plans',
            'plans'     => $plans,
            'userName'  => session('user_name'),
            'userRole'  => session('role_slug'),
        ]);
    }
    public function create()  { return $this->index(); }
    public function store()   { return redirect()->to(base_url('super/plans')); }
    public function edit($id) { return $this->index(); }
    public function update($id){ return redirect()->to(base_url('super/plans')); }
}
