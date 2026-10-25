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
        $rid   = session('restaurant_id');
        $count = \Config\Database::connect()->table('branches')->where('restaurant_id',$rid)->where('is_active',1)->countAllResults();
        $check = $this->checkPlanLimit('branches', $count);
        if (!$check['allowed']) return redirect()->back()->with('error',$check['message']);
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
            'kot_printer_ip'   => $this->request->getPost('kot_printer_ip'),
            'kot_printer_port' => $this->request->getPost('kot_printer_port') ?? 9100,
            'kot_prefix'       => $this->request->getPost('kot_prefix') ?: 'KOT',
            'billing_prefix'=> $this->request->getPost('billing_prefix') ?? 'INV',
            'is_active'     => 1,
        ]);
        return redirect()->to(base_url('admin/branches'))->with('success', 'Branch added successfully');
    }

    public function edit($id)
    {
        $branch = $this->model->where('id', $id)->where('restaurant_id', session('restaurant_id'))->first();
        if (!$branch) {
            return redirect()->to(base_url('admin/branches'))->with('error', 'Branch not found');
        }
        return view('admin/branches/form', [
            'pageTitle' => 'Edit Branch', 'branch' => $branch,
            'userName'  => session('user_name'), 'userRole' => session('role_slug'),
            'restaurantName' => session('restaurant_name'),
        ]);
    }

    public function update($id)
    {
        // Tenant guard
        if (!$this->model->where('id', $id)->where('restaurant_id', session('restaurant_id'))->countAllResults()) {
            return redirect()->to(base_url('admin/branches'))->with('error', 'Branch not found');
        }
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
            'kot_printer_ip'   => $this->request->getPost('kot_printer_ip'),
            'kot_printer_port' => $this->request->getPost('kot_printer_port') ?? 9100,
            'kot_prefix'       => $this->request->getPost('kot_prefix') ?: 'KOT',
            'billing_prefix'=> $this->request->getPost('billing_prefix'),
        ]);
        return redirect()->to(base_url('admin/branches'))->with('success', 'Branch updated');
    }

    public function toggle($id)
    {
        $b = $this->model->where('id', $id)->where('restaurant_id', session('restaurant_id'))->first();
        if (!$b) return $this->response->setJSON(['success' => false, 'message' => 'Branch not found']);
        $this->model->update($id, ['is_active' => $b['is_active'] ? 0 : 1]);
        return $this->response->setJSON(['success' => true]);
    }

    /**
     * Print a small test ticket to the branch's thermal printer — validates
     * IP/port wiring from the branch settings screen.
     */
    public function testPrint($id)
    {
        $branch = $this->model->where('id', $id)->where('restaurant_id', session('restaurant_id'))->first();
        if (!$branch) {
            return $this->response->setJSON(['success' => false, 'message' => 'Branch not found']);
        }
        if (empty($branch['printer_ip'])) {
            return $this->response->setJSON(['success' => false, 'message' => 'No printer IP is configured for this branch']);
        }

        $printer = new \App\Libraries\ThermalPrinter();
        $result  = $printer->printTest([
            'printer_ip'   => $branch['printer_ip'],
            'printer_port' => $branch['printer_port'] ?: 9100,
        ], $branch['name'] ?? 'DinoviX');

        return $this->response->setJSON(['success' => $result['success'], 'message' => $result['message']]);
    }
}
