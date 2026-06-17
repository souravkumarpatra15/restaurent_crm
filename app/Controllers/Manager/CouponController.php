<?php
namespace App\Controllers\Manager;
use App\Controllers\BaseController;

class CouponController extends BaseController
{
    public function index()
    {
        $db  = \Config\Database::connect();
        $rid = session('restaurant_id');
        $coupons = $db->table('coupons')->where('restaurant_id',$rid)->orderBy('created_at','DESC')->get()->getResultArray();
        return view('admin/coupons/index', [
            'pageTitle' => 'Coupons & Discounts',
            'coupons'   => $coupons,
            'userName'  => session('user_name'),
            'userRole'  => session('role_slug'),
            'restaurantName' => session('restaurant_name'),
        ]);
    }

    public function store()
    {
        $db  = \Config\Database::connect();
        $db->table('coupons')->insert([
            'restaurant_id'  => session('restaurant_id'),
            'code'           => strtoupper($this->request->getPost('code')),
            'name'           => $this->request->getPost('name'),
            'discount_type'  => $this->request->getPost('discount_type'),
            'discount_value' => $this->request->getPost('discount_value'),
            'min_order_amount'     => $this->request->getPost('min_order_amount') ?? 0,
            'max_discount_amount'  => $this->request->getPost('max_discount_amount') ?? 0,
            'usage_limit'    => $this->request->getPost('usage_limit') ?? 0,
            'per_user_limit' => $this->request->getPost('per_user_limit') ?? 1,
            'valid_from'     => $this->request->getPost('valid_from') ?: null,
            'valid_to'       => $this->request->getPost('valid_to') ?: null,
            'applicable_on'  => $this->request->getPost('applicable_on') ?? 'all',
            'is_active'      => 1,
            'created_at'     => date('Y-m-d H:i:s'),
        ]);
        return redirect()->to(base_url('admin/coupons'))->with('success','Coupon created');
    }

    public function toggle($id)
    {
        $db = \Config\Database::connect();
        $c  = $db->table('coupons')->where('id',$id)->get()->getRowArray();
        $db->table('coupons')->where('id',$id)->update(['is_active'=> $c['is_active'] ? 0 : 1]);
        return $this->response->setJSON(['success'=>true]);
    }
}
