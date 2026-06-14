<?php
namespace App\Controllers\Manager;
use App\Controllers\BaseController;
use App\Models\CustomerModel;

class CustomerController extends BaseController
{
    protected $model;
    public function __construct() { $this->model = new CustomerModel(); }

    public function index()
    {
        $rid   = session('restaurant_id');
        $phone = $this->request->getGet('phone');
        $ajax  = $this->request->getGet('ajax');

        if ($phone && $ajax) {
            $results = $this->model->search($rid, $phone);
            if ($results) {
                $c = $results[0];
                return $this->response->setJSON(['found'=>true,'id'=>$c['id'],'name'=>$c['name'],'phone'=>$c['phone'],'points'=>$c['loyalty_points']]);
            }
            return $this->response->setJSON(['found'=>false]);
        }

        $customers = $this->model->where('restaurant_id',$rid)->orderBy('name','ASC')->findAll();
        return view('admin/customers/index', [
            'pageTitle' => 'Customers', 'customers' => $customers,
            'userName'  => session('user_name'), 'userRole' => session('role_slug'),
            'restaurantName' => session('restaurant_name'),
        ]);
    }

    public function view($id)
    {
        $db  = \Config\Database::connect();
        $customer = $this->model->find($id);
        $orders   = $db->table('orders')->where('customer_id',$id)->orderBy('created_at','DESC')->limit(20)->get()->getResultArray();
        return view('admin/customers/view', [
            'pageTitle' => $customer['name'], 'customer' => $customer, 'orders' => $orders,
            'userName'  => session('user_name'), 'userRole' => session('role_slug'),
            'restaurantName' => session('restaurant_name'),
        ]);
    }

    public function store()
    {
        $this->model->insert([
            'restaurant_id' => session('restaurant_id'),
            'name'          => $this->request->getPost('name'),
            'phone'         => $this->request->getPost('phone'),
            'email'         => $this->request->getPost('email'),
            'dob'           => $this->request->getPost('dob') ?: null,
            'gender'        => $this->request->getPost('gender'),
            'address'       => $this->request->getPost('address'),
            'source'        => 'walk_in',
            'is_active'     => 1,
        ]);
        return redirect()->to(base_url('admin/customers'))->with('success','Customer added');
    }

    public function update($id)
    {
        $this->model->update($id, [
            'name'  => $this->request->getPost('name'),
            'phone' => $this->request->getPost('phone'),
            'email' => $this->request->getPost('email'),
            'dob'   => $this->request->getPost('dob') ?: null,
        ]);
        return redirect()->to(base_url('admin/customers'))->with('success','Customer updated');
    }
}
