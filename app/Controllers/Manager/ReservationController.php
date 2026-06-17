<?php
namespace App\Controllers\Manager;
use App\Controllers\BaseController;

class ReservationController extends BaseController
{
    public function index()
    {
        $db  = \Config\Database::connect();
        $bid = session('branch_id');
        $date = $this->request->getGet('date') ?? date('Y-m-d');

        $reservations = $db->table('reservations r')
            ->select('r.*, t.table_number')
            ->join('tables t','t.id = r.table_id','left')
            ->where('r.branch_id', $bid)
            ->where('r.reservation_date', $date)
            ->orderBy('r.reservation_time','ASC')
            ->get()->getResultArray();

        $tables = $db->table('tables')->where('branch_id',$bid)->where('is_active',1)->get()->getResultArray();

        return view('admin/reservations/index', [
            'pageTitle'    => 'Reservations',
            'reservations' => $reservations,
            'tables'       => $tables,
            'date'         => $date,
            'userName'     => session('user_name'),
            'userRole'     => session('role_slug'),
            'restaurantName' => session('restaurant_name'),
        ]);
    }

    public function store()
    {
        $db = \Config\Database::connect();
        $db->table('reservations')->insert([
            'branch_id'        => session('branch_id'),
            'table_id'         => $this->request->getPost('table_id') ?: null,
            'customer_name'    => $this->request->getPost('customer_name'),
            'customer_phone'   => $this->request->getPost('customer_phone'),
            'customer_email'   => $this->request->getPost('customer_email'),
            'reservation_date' => $this->request->getPost('reservation_date'),
            'reservation_time' => $this->request->getPost('reservation_time'),
            'guests'           => $this->request->getPost('guests') ?? 2,
            'occasion'         => $this->request->getPost('occasion'),
            'special_requests' => $this->request->getPost('special_requests'),
            'status'           => 'confirmed',
            'created_at'       => date('Y-m-d H:i:s'),
        ]);
        return redirect()->to(base_url('admin/reservations'))->with('success','Reservation added');
    }

    public function updateStatus($id)
    {
        $status = $this->request->getPost('status');
        \Config\Database::connect()->table('reservations')->where('id',$id)->update(['status'=>$status]);
        return $this->response->setJSON(['success'=>true]);
    }
}
