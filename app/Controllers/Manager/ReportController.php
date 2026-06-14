<?php
namespace App\Controllers\Manager;
use App\Controllers\BaseController;
class ReportController extends BaseController
{
    public function index()         { return view('admin/coming_soon',['pageTitle'=>'ReportController','userName'=>session('user_name'),'userRole'=>session('role_slug')]); }
    public function create()        { return $this->index(); }
    public function store()         { return redirect()->back()->with('success','Saved'); }
    public function edit($id)      { return $this->index(); }
    public function update($id)    { return redirect()->back()->with('success','Updated'); }
    public function toggle($id)    { return $this->response->setJSON(['success'=>true]); }
    public function delete($id)    { return $this->response->setJSON(['success'=>true]); }
    public function view($id)      { return $this->index(); }
    public function cancel($id)    { return $this->response->setJSON(['success'=>true]); }
    public function approve($id)   { return $this->response->setJSON(['success'=>true]); }
    public function updateStatus($id){ return $this->response->setJSON(['success'=>true]); }
    public function sales()         { return $this->index(); }
    public function items()         { return $this->index(); }
    public function payments()      { return $this->index(); }
    public function expenses()      { return $this->index(); }
    public function save()          { return redirect()->back(); }
    public function transaction()   { return $this->response->setJSON(['success'=>true]); }
    public function createItem()    { return $this->index(); }
    public function storeItem()     { return redirect()->back()->with('success','Item saved'); }
    public function editItem($id)  { return $this->index(); }
    public function updateItem($id){ return redirect()->back(); }
    public function toggleItem($id){ return $this->response->setJSON(['success'=>true]); }
    public function deleteItem($id){ return $this->response->setJSON(['success'=>true]); }
    public function duplicateItem($id){ return $this->response->setJSON(['success'=>true]); }
    public function storeCategory() { return $this->response->setJSON(['success'=>true]); }
    public function updateCategory($id){ return $this->response->setJSON(['success'=>true]); }
    public function toggleCategory($id){ return $this->response->setJSON(['success'=>true]); }
    public function deleteCategory($id){ return $this->response->setJSON(['success'=>true]); }
    public function storeAddonGroup(){ return $this->response->setJSON(['success'=>true]); }
    public function deleteAddonGroup($id){ return $this->response->setJSON(['success'=>true]); }
    public function storeAddon()    { return $this->response->setJSON(['success'=>true]); }
    public function deleteAddon($id){ return $this->response->setJSON(['success'=>true]); }
}
