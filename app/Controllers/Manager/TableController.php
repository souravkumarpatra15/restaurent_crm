<?php
namespace App\Controllers\Manager;
use App\Controllers\BaseController;
use App\Models\TableModel;

class TableController extends BaseController
{
    protected $model;
    public function __construct() { $this->model = new TableModel(); }

    public function index()
    {
        $db  = \Config\Database::connect();
        $bid = session('branch_id');
        return view('admin/tables/index', [
            'pageTitle' => 'Table Management',
            'tables'    => $this->model->where('branch_id',$bid)->orderBy('table_number','ASC')->findAll(),
            'areas'     => $db->table('table_areas')->where('branch_id',$bid)->get()->getResultArray(),
            'userName'  => session('user_name'), 'userRole' => session('role_slug'),
            'restaurantName' => session('restaurant_name'),
        ]);
    }

    public function store()
    {
        $this->model->insert([
            'branch_id'    => session('branch_id'),
            'area_id'      => $this->request->getPost('area_id') ?: null,
            'table_number' => $this->request->getPost('table_number'),
            'capacity'     => $this->request->getPost('capacity') ?? 4,
            'shape'        => $this->request->getPost('shape') ?? 'square',
            'status'       => 'available',
            'sort_order'   => $this->request->getPost('sort_order') ?? 0,
            'is_active'    => 1,
        ]);
        return $this->response->setJSON(['success'=>true]);
    }

    public function update($id)
    {
        $this->model->update($id, [
            'table_number' => $this->request->getPost('table_number'),
            'capacity'     => $this->request->getPost('capacity'),
            'area_id'      => $this->request->getPost('area_id') ?: null,
        ]);
        return $this->response->setJSON(['success'=>true]);
    }

    public function toggle($id)
    {
        $t = $this->model->find($id);
        $this->model->update($id, ['is_active' => $t['is_active'] ? 0 : 1]);
        return $this->response->setJSON(['success'=>true]);
    }
}
