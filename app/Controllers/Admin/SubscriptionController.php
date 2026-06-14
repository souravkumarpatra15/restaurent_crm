<?php
namespace App\Controllers\Admin;
use App\Controllers\BaseController;
class SubscriptionController extends BaseController
{
    public function index()   { return view('admin/coming_soon', ['pageTitle'=>'SubscriptionController','userName'=>session('user_name'),'userRole'=>session('role_slug')]); }
    public function create()  { return $this->index(); }
    public function store()   { return redirect()->back(); }
    public function edit($id) { return $this->index(); }
    public function update($id){ return redirect()->back(); }
    public function revenue()  { return $this->index(); }
    public function subscriptions(){ return $this->index(); }
    public function save()    { return redirect()->back(); }
    public function activityLog(){ return $this->index(); }
}
