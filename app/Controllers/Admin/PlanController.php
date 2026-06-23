<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class PlanController extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();
        $plans = $db->table('saas_plans sp')
            ->select('sp.*, (SELECT COUNT(*) FROM restaurants WHERE plan_id=sp.id AND is_active=1) as subscriber_count')
            ->orderBy('sp.sort_order', 'ASC')->get()->getResultArray();
        return view('admin/plans/index', [
            'pageTitle' => 'Subscription Plans',
            'plans'     => $plans,
            'userName'  => session('user_name'),
            'userRole'  => session('role_slug'),
        ]);
    }

    public function create()
    {
        return view('admin/plans/form', [
            'pageTitle' => 'Create Plan',
            'plan'      => null,
            'userName'  => session('user_name'),
            'userRole'  => session('role_slug'),
        ]);
    }

    public function store()
    {
        \Config\Database::connect()->table('saas_plans')->insert([
            'name'                   => $this->request->getPost('name'),
            'slug'                   => url_title(strtolower($this->request->getPost('name')), '-', true),
            'price_monthly'          => $this->request->getPost('price_monthly'),
            'price_yearly'           => $this->request->getPost('price_yearly'),
            'max_branches'           => $this->request->getPost('max_branches') ?? 1,
            'max_users'              => $this->request->getPost('max_users') ?? 5,
            'max_menu_items'         => $this->request->getPost('max_menu_items') ?? 100,
            'max_tables'             => $this->request->getPost('max_tables') ?? 20,
            'allow_thermal_print'    => $this->request->getPost('allow_thermal_print') ? 1 : 0,
            'allow_kot_print'        => $this->request->getPost('allow_kot_print') ? 1 : 0,
            'allow_online_ordering'  => $this->request->getPost('allow_online_ordering') ? 1 : 0,
            'allow_loyalty'          => $this->request->getPost('allow_loyalty') ? 1 : 0,
            'allow_reports_advanced' => $this->request->getPost('allow_reports_advanced') ? 1 : 0,
            'allow_api_access'       => $this->request->getPost('allow_api_access') ? 1 : 0,
            'allow_whitelabel'       => $this->request->getPost('allow_whitelabel') ? 1 : 0,
            'is_active'              => 1,
            'sort_order'             => $this->request->getPost('sort_order') ?? 0,
        ]);
        return redirect()->to(base_url('super/plans'))->with('success', 'Plan created');
    }

    public function edit($id)
    {
        return view('admin/plans/form', [
            'pageTitle' => 'Edit Plan',
            'plan'      => \Config\Database::connect()->table('saas_plans')->where('id', $id)->get()->getRowArray(),
            'userName'  => session('user_name'),
            'userRole'  => session('role_slug'),
        ]);
    }

    public function update($id)
    {
        \Config\Database::connect()->table('saas_plans')->where('id', $id)->update([
            'name'                   => $this->request->getPost('name'),
            'price_monthly'          => $this->request->getPost('price_monthly'),
            'price_yearly'           => $this->request->getPost('price_yearly'),
            'max_branches'           => $this->request->getPost('max_branches'),
            'max_users'              => $this->request->getPost('max_users'),
            'max_menu_items'         => $this->request->getPost('max_menu_items'),
            'max_tables'             => $this->request->getPost('max_tables'),
            'allow_thermal_print'    => $this->request->getPost('allow_thermal_print') ? 1 : 0,
            'allow_kot_print'        => $this->request->getPost('allow_kot_print') ? 1 : 0,
            'allow_online_ordering'  => $this->request->getPost('allow_online_ordering') ? 1 : 0,
            'allow_loyalty'          => $this->request->getPost('allow_loyalty') ? 1 : 0,
            'allow_reports_advanced' => $this->request->getPost('allow_reports_advanced') ? 1 : 0,
            'is_active'              => $this->request->getPost('is_active') ?? 1,
            'sort_order'             => $this->request->getPost('sort_order') ?? 0,
        ]);
        return redirect()->to(base_url('super/plans'))->with('success', 'Plan updated');
    }
}
