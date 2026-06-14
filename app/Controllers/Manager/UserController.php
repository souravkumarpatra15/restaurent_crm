<?php
namespace App\Controllers\Manager;
use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Models\BranchModel;

class UserController extends BaseController
{
    protected $model;
    public function __construct() { $this->model = new UserModel(); }

    public function index()
    {
        return view('admin/users/index', [
            'pageTitle' => 'Staff Management',
            'users'     => $this->model->getWithRole(session('restaurant_id')),
            'userName'  => session('user_name'), 'userRole' => session('role_slug'),
            'restaurantName' => session('restaurant_name'),
        ]);
    }

    public function create()
    {
        $db = \Config\Database::connect();
        return view('admin/users/form', [
            'pageTitle' => 'Add Staff', 'user' => null,
            'roles'     => $db->table('roles')->where('is_system',1)->whereNotIn('slug',['super_admin'])->get()->getResultArray(),
            'branches'  => $db->table('branches')->where('restaurant_id',session('restaurant_id'))->get()->getResultArray(),
            'userName'  => session('user_name'), 'userRole' => session('role_slug'),
            'restaurantName' => session('restaurant_name'),
        ]);
    }

    public function store()
    {
        $this->model->insert([
            'restaurant_id' => session('restaurant_id'),
            'branch_id'     => $this->request->getPost('branch_id') ?: null,
            'role_id'       => $this->request->getPost('role_id'),
            'name'          => $this->request->getPost('name'),
            'email'         => $this->request->getPost('email'),
            'phone'         => $this->request->getPost('phone'),
            'password'      => password_hash($this->request->getPost('password'), PASSWORD_BCRYPT),
            'employee_code' => $this->request->getPost('employee_code'),
            'pin'           => $this->request->getPost('pin'),
            'is_active'     => 1,
        ]);
        return redirect()->to(base_url('admin/users'))->with('success', 'Staff member added');
    }

    public function edit($id)
    {
        $db = \Config\Database::connect();
        return view('admin/users/form', [
            'pageTitle' => 'Edit Staff', 'user' => $this->model->find($id),
            'roles'     => $db->table('roles')->where('is_system',1)->whereNotIn('slug',['super_admin'])->get()->getResultArray(),
            'branches'  => $db->table('branches')->where('restaurant_id',session('restaurant_id'))->get()->getResultArray(),
            'userName'  => session('user_name'), 'userRole' => session('role_slug'),
            'restaurantName' => session('restaurant_name'),
        ]);
    }

    public function update($id)
    {
        $data = [
            'branch_id'     => $this->request->getPost('branch_id') ?: null,
            'role_id'       => $this->request->getPost('role_id'),
            'name'          => $this->request->getPost('name'),
            'phone'         => $this->request->getPost('phone'),
            'employee_code' => $this->request->getPost('employee_code'),
            'pin'           => $this->request->getPost('pin'),
        ];
        if ($this->request->getPost('password')) {
            $data['password'] = password_hash($this->request->getPost('password'), PASSWORD_BCRYPT);
        }
        $this->model->update($id, $data);
        return redirect()->to(base_url('admin/users'))->with('success', 'Staff updated');
    }

    public function toggle($id)
    {
        $u = $this->model->find($id);
        $this->model->update($id, ['is_active' => $u['is_active'] ? 0 : 1]);
        return $this->response->setJSON(['success' => true]);
    }
}
