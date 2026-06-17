<?php
namespace App\Controllers\Manager;
use App\Controllers\BaseController;

class SettingsController extends BaseController
{
    public function index()
    {
        $db  = \Config\Database::connect();
        $rid = session('restaurant_id');
        $restaurant = $db->table('restaurants')->where('id',$rid)->get()->getRowArray();
        $branches   = $db->table('branches')->where('restaurant_id',$rid)->get()->getResultArray();

        return view('admin/settings/index', [
            'pageTitle'  => 'Settings',
            'restaurant' => $restaurant,
            'branches'   => $branches,
            'userName'   => session('user_name'),
            'userRole'   => session('role_slug'),
            'restaurantName' => session('restaurant_name'),
        ]);
    }

    public function save()
    {
        $db  = \Config\Database::connect();
        $rid = session('restaurant_id');

        $db->table('restaurants')->where('id',$rid)->update([
            'name'                 => $this->request->getPost('name'),
            'phone'                => $this->request->getPost('phone'),
            'email'                => $this->request->getPost('email'),
            'address'              => $this->request->getPost('address'),
            'city'                 => $this->request->getPost('city'),
            'state'                => $this->request->getPost('state'),
            'pincode'              => $this->request->getPost('pincode'),
            'gst_number'           => $this->request->getPost('gst_number'),
            'fssai_number'         => $this->request->getPost('fssai_number'),
            'tax_type'             => $this->request->getPost('tax_type'),
            'default_tax_percent'  => $this->request->getPost('default_tax_percent'),
            'service_charge_percent'=> $this->request->getPost('service_charge_percent') ?? 0,
            'billing_prefix'       => $this->request->getPost('billing_prefix'),
            'receipt_header'       => $this->request->getPost('receipt_header'),
            'receipt_footer'       => $this->request->getPost('receipt_footer'),
            'theme_color'          => $this->request->getPost('theme_color') ?? '#FF6B35',
            'updated_at'           => date('Y-m-d H:i:s'),
        ]);
        return redirect()->to(base_url('admin/settings'))->with('success','Settings saved');
    }
}
