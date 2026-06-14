<?php
namespace App\Controllers\Staff;
use App\Controllers\BaseController;
class ShiftController extends BaseController
{
    public function index()        { return view('admin/coming_soon',['pageTitle'=>'ShiftController','userName'=>session('user_name'),'userRole'=>session('role_slug')]); }
    public function updateStatus() { return $this->response->setJSON(['success'=>true]); }
    public function summary()      { return $this->index(); }
    public function open()         { return $this->index(); }
    public function doOpen()       { return redirect()->to(base_url('pos')); }
    public function close()        { return $this->index(); }
    public function doClose()      { return redirect()->to(base_url('pos')); }
}
