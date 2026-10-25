<?php

namespace App\Controllers\Manager;

use App\Controllers\BaseController;
use App\Models\TableModel;
use App\Models\TableAreaModel;

class TableController extends BaseController
{
    protected $model;
    protected $areaModel;
    public function __construct()
    {
        $this->model = new TableModel();
        $this->areaModel = new TableAreaModel();
    }

    public function index()
    {
        $db  = \Config\Database::connect();
        $bid = session('branch_id');
        return view('admin/tables/index', [
            'pageTitle'      => 'Table Management',
            'tables'         => $this->model->where('branch_id', $bid)->orderBy('table_number', 'ASC')->findAll(),
            'areas'          => $db->table('table_areas')->where('branch_id', $bid)->get()->getResultArray(),
            'userName'       => session('user_name'),
            'userRole'       => session('role_slug'),
            'restaurantName' => session('restaurant_name'),
        ]);
    }

    public function store()
    {
        $bid   = session('branch_id');
        $count = $this->model->where('branch_id', $bid)->countAllResults();
        $check = $this->checkPlanLimit('tables', $count);
        if (!$check['allowed']) return $this->response->setJSON(['success' => false, 'message' => $check['message']]);

        $token = bin2hex(random_bytes(20)); // auto-generate QR token on table creation
        $this->model->insert([
            'branch_id'    => $bid,
            'area_id'      => $this->request->getPost('area_id') ?: null,
            'table_number' => $this->request->getPost('table_number'),
            'capacity'     => $this->request->getPost('capacity') ?? 4,
            'shape'        => $this->request->getPost('shape') ?? 'square',
            'status'       => 'available',
            'sort_order'   => $this->request->getPost('sort_order') ?? 0,
            'is_active'    => 1,
            'qr_token'     => $token,
        ]);
        return $this->response->setJSON(['success' => true]);
    }

    public function update($id)
    {
        if (!$this->scopedTable($id)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Table not found']);
        }
        $this->model->update($id, [
            'table_number' => $this->request->getPost('table_number'),
            'capacity'     => $this->request->getPost('capacity'),
            'area_id'      => $this->request->getPost('area_id') ?: null,
        ]);
        return $this->response->setJSON(['success' => true]);
    }

    public function toggle($id)
    {
        $t = $this->scopedTable($id);
        if (!$t) {
            return $this->response->setJSON(['success' => false, 'message' => 'Table not found']);
        }
        $this->model->update($id, ['is_active' => $t['is_active'] ? 0 : 1]);
        return $this->response->setJSON(['success' => true]);
    }

    /**
     * Ownership guard: only tables belonging to the session restaurant
     * (through its branches) may be modified.
     */
    private function scopedTable($id)
    {
        return \Config\Database::connect()->table('tables t')
            ->select('t.*')
            ->join('branches b', 'b.id = t.branch_id')
            ->where('t.id', $id)
            ->where('b.restaurant_id', session('restaurant_id'))
            ->get()->getRowArray();
    }

    private function scopedArea($id)
    {
        return \Config\Database::connect()->table('table_areas a')
            ->select('a.*')
            ->join('branches b', 'b.id = a.branch_id')
            ->where('a.id', $id)
            ->where('b.restaurant_id', session('restaurant_id'))
            ->get()->getRowArray();
    }

    // ── Generate / refresh QR token for a table ──────────────
    public function generateQr($id)
    {
        if (!$this->scopedTable($id)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Table not found']);
        }
        $token = bin2hex(random_bytes(20));
        $this->model->update($id, ['qr_token' => $token]);
        return $this->response->setJSON([
            'success' => true,
            'token'   => $token,
            'url'     => base_url('menu/table/' . $token),
        ]);
    }

    // ── Book a table ──────────────────────────────────────────
    public function book($id)
    {
        if (!$this->scopedTable($id)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Table not found']);
        }
        $this->model->update($id, [
            'status'       => 'booked',
            'booked_name'  => $this->request->getPost('name'),
            'booked_phone' => $this->request->getPost('phone') ?: null,
            'booked_for'   => $this->request->getPost('booked_for') ?: null,
            'booked_note'  => $this->request->getPost('note') ?: null,
        ]);
        return $this->response->setJSON(['success' => true]);
    }

    // ── Cancel a booking ──────────────────────────────────────
    public function cancelBooking($id)
    {
        if (!$this->scopedTable($id)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Table not found']);
        }
        $this->model->update($id, [
            'status'       => 'available',
            'booked_name'  => null,
            'booked_phone' => null,
            'booked_for'   => null,
            'booked_note'  => null,
        ]);
        return $this->response->setJSON(['success' => true]);
    }

    // ── Bulk QR print page ────────────────────────────────
    public function bulkQr()
    {
        $db  = \Config\Database::connect();
        $bid = session('branch_id');
        $rid = session('restaurant_id');

        // Get restaurant + branch details for QR card header
        $restaurant = $db->table('restaurants')->where('id', $rid)->get()->getRowArray();
        $branch     = $db->table('branches')->where('id', $bid)->get()->getRowArray();

        // Get tables grouped by area
        $areas = $db->table('table_areas ta')
            ->select('ta.id, ta.name')
            ->where('ta.branch_id', $bid)
            ->where('ta.is_active', 1)
            ->orderBy('ta.sort_order', 'ASC')
            ->get()->getResultArray();

        foreach ($areas as &$area) {
            $area['tables'] = $this->model
                ->where('branch_id', $bid)
                ->where('area_id', $area['id'])
                ->where('is_active', 1)
                ->orderBy('sort_order', 'ASC')
                ->findAll();
        }

        // Tables with no area
        $noArea = $this->model
            ->where('branch_id', $bid)
            ->where('is_active', 1)
            ->where('area_id IS NULL', null, false)
            ->orderBy('sort_order', 'ASC')
            ->findAll();
        if ($noArea) {
            $areas[] = ['id' => 0, 'name' => 'All Tables', 'tables' => $noArea];
        }

        // If no areas at all, just get all tables
        if (empty($areas)) {
            $allTables = $this->model->where('branch_id', $bid)->where('is_active', 1)->orderBy('sort_order', 'ASC')->findAll();
            $areas = [['id' => 0, 'name' => 'Tables', 'tables' => $allTables]];
        }

        return view('admin/tables/bulk_qr', [
            'pageTitle'  => 'Print Table QR Codes',
            'restaurant' => $restaurant,
            'branch'     => $branch,
            'areas'      => $areas,
            'userName'   => session('user_name'),
            'userRole'   => session('role_slug'),
        ]);
    }

    public function storeArea()
    {
        $this->areaModel->insert([
            'branch_id'   => session('branch_id'),
            'name'        => trim($this->request->getPost('name')),
            'description' => $this->request->getPost('description'),
            'sort_order'  => $this->request->getPost('sort_order') ?? 0,
            'is_active'   => 1,
        ]);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Area added successfully.'
        ]);
    }

    public function updateArea($id)
    {
        if (!$this->scopedArea($id)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Area not found']);
        }
        $this->areaModel->update($id, [
            'name'        => trim($this->request->getPost('name')),
            'description' => $this->request->getPost('description'),
            'sort_order'  => $this->request->getPost('sort_order'),
        ]);

        return $this->response->setJSON([
            'success' => true
        ]);
    }

    public function toggleArea($id)
    {
        $area = $this->scopedArea($id);
        if (!$area) {
            return $this->response->setJSON(['success' => false, 'message' => 'Area not found']);
        }

        $this->areaModel->update($id, [
            'is_active' => $area['is_active'] ? 0 : 1
        ]);

        return $this->response->setJSON([
            'success' => true
        ]);
    }

    public function deleteArea($id)
    {
        if (!$this->scopedArea($id)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Area not found']);
        }

        $count = $this->model
            ->where('area_id', $id)
            ->countAllResults();

        if ($count > 0) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Area contains tables. Move or delete them first.'
            ]);
        }

        $this->areaModel->delete($id);

        return $this->response->setJSON([
            'success' => true
        ]);
    }
}
