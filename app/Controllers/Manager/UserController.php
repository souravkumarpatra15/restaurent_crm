<?php
namespace App\Controllers\Manager;
use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Models\BranchModel;

class UserController extends BaseController
{
    protected $model;
    public function __construct() { $this->model = new UserModel(); }

    /** Staff-management gate: the upsell page when the plan lacks the module. */
    private function gate()
    {
        if ($this->getPlanFeature('allow_staff_mgmt')) return null;
        return $this->featureLocked(
            'Staff Management',
            'fa-users-gear',
            'Add and manage unlimited staff — cashiers, waiters and kitchen staff with roles, PINs, branches and attendance.',
            base_url()
        );
    }

    public function index()
    {
        if ($g = $this->gate()) return $g;
        return view('admin/users/index', [
            'pageTitle' => 'Staff Management',
            'users'     => $this->model->getWithRole(session('restaurant_id')),
            'userName'  => session('user_name'), 'userRole' => session('role_slug'),
            'restaurantName' => session('restaurant_name'),
        ]);
    }

    public function create()
    {
        if ($g = $this->gate()) return $g;
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
        if ($g = $this->gate()) return $g;
        $rid   = session('restaurant_id');
        $count = \Config\Database::connect()->table('users')->where('restaurant_id',$rid)->where('is_active',1)->countAllResults();
        $check = $this->checkPlanLimit('users',$count);
        if (!$check['allowed']) return redirect()->back()->with('error',$check['message']);
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
        if ($g = $this->gate()) return $g;
        $db = \Config\Database::connect();
        $user = $db->table('users')
            ->where('id', $id)
            ->where('restaurant_id', session('restaurant_id'))
            ->get()->getRowArray();
        if (!$user) {
            return redirect()->to(base_url('admin/users'))->with('error', 'Staff member not found');
        }
        return view('admin/users/form', [
            'pageTitle' => 'Edit Staff', 'user' => $user,
            'roles'     => $db->table('roles')->where('is_system',1)->whereNotIn('slug',['super_admin'])->get()->getResultArray(),
            'branches'  => $db->table('branches')->where('restaurant_id',session('restaurant_id'))->get()->getResultArray(),
            'userName'  => session('user_name'), 'userRole' => session('role_slug'),
            'restaurantName' => session('restaurant_name'),
        ]);
    }

    public function update($id)
    {
        if ($g = $this->gate()) return $g;
        // Tenant guard — do not touch another restaurant's staff
        $target = $this->model->where('id', $id)->where('restaurant_id', session('restaurant_id'))->first();
        if (!$target) {
            return redirect()->to(base_url('admin/users'))->with('error', 'Staff member not found');
        }
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
        if (!$this->getPlanFeature('allow_staff_mgmt')) {
            return $this->response->setJSON(['success' => false, 'message' => 'Staff Management is not included in your plan — upgrade to use it']);
        }
        // Tenant guard
        $u = $this->model->where('id', $id)->where('restaurant_id', session('restaurant_id'))->first();
        if (!$u) return $this->response->setJSON(['success' => false, 'message' => 'Staff member not found']);
        $this->model->update($id, ['is_active' => $u['is_active'] ? 0 : 1]);
        return $this->response->setJSON(['success' => true]);
    }
}
