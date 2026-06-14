<?php
namespace App\Controllers\Manager;
use App\Controllers\BaseController;
use App\Models\BranchModel;

class BranchController extends BaseController
{
    protected $model;
    public function __construct() { $this->model = new BranchModel(); }

    public function index()
    {
        return view('admin/branches/index', [
            'pageTitle' => 'Branches',
            'branches'  => $this->model->getByRestaurant(session('restaurant_id')),
            'userName'  => session('user_name'), 'userRole' => session('role_slug'),
            'restaurantName' => session('restaurant_name'),
        ]);
    }

    public function create()
    {
        return view('admin/branches/form', [
            'pageTitle' => 'Add Branch', 'branch' => null,
            'userName'  => session('user_name'), 'userRole' => session('role_slug'),
            'restaurantName' => session('restaurant_name'),
        ]);
    }

    public function store()
    {
        $this->model->insert([
            'restaurant_id' => session('restaurant_id'),
            'name'          => $this->request->getPost('name'),
            'code'          => $this->request->getPost('code'),
            'branch_type'   => $this->request->getPost('branch_type') ?? 'sub',
            'phone'         => $this->request->getPost('phone'),
            'email'         => $this->request->getPost('email'),
            'address'       => $this->request->getPost('address'),
            'city'          => $this->request->getPost('city'),
            'state'         => $this->request->getPost('state'),
            'pincode'       => $this->request->getPost('pincode'),
            'opening_time'  => $this->request->getPost('opening_time'),
            'closing_time'  => $this->request->getPost('closing_time'),
            'has_dine_in'   => $this->request->getPost('has_dine_in') ? 1 : 0,
            'has_takeaway'  => $this->request->getPost('has_takeaway') ? 1 : 0,
            'has_delivery'  => $this->request->getPost('has_delivery') ? 1 : 0,
            'printer_ip'    => $this->request->getPost('printer_ip'),
            'printer_port'  => $this->request->getPost('printer_port') ?? 9100,
            'billing_prefix'=> $this->request->getPost('billing_prefix') ?? 'INV',
            'is_active'     => 1,
        ]);
        return redirect()->to(base_url('admin/branches'))->with('success', 'Branch added successfully');
    }

    public function edit($id)
    {
        return view('admin/branches/form', [
            'pageTitle' => 'Edit Branch', 'branch' => $this->model->find($id),
            'userName'  => session('user_name'), 'userRole' => session('role_slug'),
            'restaurantName' => session('restaurant_name'),
        ]);
    }

    public function update($id)
    {
        $this->model->update($id, [
            'name'         => $this->request->getPost('name'),
            'code'         => $this->request->getPost('code'),
            'branch_type'  => $this->request->getPost('branch_type'),
            'phone'        => $this->request->getPost('phone'),
            'email'        => $this->request->getPost('email'),
            'address'      => $this->request->getPost('address'),
            'city'         => $this->request->getPost('city'),
            'state'        => $this->request->getPost('state'),
            'pincode'      => $this->request->getPost('pincode'),
            'opening_time' => $this->request->getPost('opening_time'),
            'closing_time' => $this->request->getPost('closing_time'),
            'has_dine_in'  => $this->request->getPost('has_dine_in') ? 1 : 0,
            'has_takeaway' => $this->request->getPost('has_takeaway') ? 1 : 0,
            'has_delivery' => $this->request->getPost('has_delivery') ? 1 : 0,
            'printer_ip'   => $this->request->getPost('printer_ip'),
            'printer_port' => $this->request->getPost('printer_port') ?? 9100,
            'billing_prefix'=> $this->request->getPost('billing_prefix'),
        ]);
        return redirect()->to(base_url('admin/branches'))->with('success', 'Branch updated');
    }

    public function toggle($id)
    {
        $b = $this->model->find($id);
        $this->model->update($id, ['is_active' => $b['is_active'] ? 0 : 1]);
        return $this->response->setJSON(['success' => true]);
    }
}
