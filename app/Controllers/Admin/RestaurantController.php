<?php
namespace App\Controllers\Admin;
use App\Controllers\BaseController;
class RestaurantController extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();
        $data = [
            'pageTitle'   => 'Restaurants',
            'restaurants' => $db->table('restaurants r')->select('r.*, p.name as plan_name')->join('saas_plans p','p.id=r.plan_id','left')->orderBy('r.created_at','DESC')->get()->getResultArray(),
            'userName'    => session('user_name'),
            'userRole'    => session('role_slug'),
        ];
        return view('admin/restaurants/index', $data);
    }
    public function create()  { return view('admin/restaurants/form', ['pageTitle'=>'Add Restaurant','userName'=>session('user_name'),'userRole'=>session('role_slug')]); }
    public function store()   { return redirect()->to(base_url('super/restaurants')); }
    public function edit($id) { return view('admin/restaurants/form', ['pageTitle'=>'Edit Restaurant','userName'=>session('user_name'),'userRole'=>session('role_slug')]); }
    public function update($id){ return redirect()->to(base_url('super/restaurants')); }
    public function view($id) { return redirect()->to(base_url('super/restaurants')); }
    public function toggle($id){ return $this->response->setJSON(['success'=>true]); }
    public function delete($id){ return $this->response->setJSON(['success'=>true]); }
    public function loginAs($id)
    {
        $db = \Config\Database::connect();
        $admin = $db->table('users')->where('restaurant_id',$id)->where('role_id',2)->get()->getRowArray();
        if ($admin) {
            session()->set(['user_id'=>$admin['id'],'role_slug'=>'restaurant_admin','restaurant_id'=>$id]);
            return redirect()->to(base_url('admin/dashboard'));
        }
        return redirect()->back()->with('error','No admin found for this restaurant');
    }
}
