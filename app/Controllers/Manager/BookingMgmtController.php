<?php
namespace App\Controllers\Manager;
use App\Controllers\BaseController;

class BookingMgmtController extends BaseController
{
    public function index()
    {
        $db    = \Config\Database::connect();
        $rid   = session('restaurant_id');
        $date  = $this->request->getGet('date') ?? date('Y-m-d');
        $status= $this->request->getGet('status') ?? '';

        $q = $db->table('public_bookings')->where('restaurant_id',$rid)->where('slot_date',$date);
        if ($status) $q->where('status',$status);
        $bookings = $q->orderBy('slot_time','ASC')->get()->getResultArray();

        $stats = [
            'today'     => $db->table('public_bookings')->where('restaurant_id',$rid)->where('slot_date',date('Y-m-d'))->whereIn('status',['confirmed','pending'])->countAllResults(),
            'pending'   => $db->table('public_bookings')->where('restaurant_id',$rid)->where('status','pending')->countAllResults(),
            'this_week' => $db->table('public_bookings')->where('restaurant_id',$rid)->where('slot_date >=',date('Y-m-d'))->where('slot_date <=',date('Y-m-d',strtotime('+7 days')))->countAllResults(),
            'total_pax' => $db->table('public_bookings')->selectSum('guests')->where('restaurant_id',$rid)->where('slot_date',$date)->whereIn('status',['confirmed','pending'])->get()->getRowArray()['guests'] ?? 0,
        ];

        return view('admin/booking_mgmt/index', [
            'pageTitle' => 'Table Bookings',
            'bookings'  => $bookings,
            'stats'     => $stats,
            'date'      => $date,
            'status'    => $status,
            'userName'  => session('user_name'),
            'userRole'  => session('role_slug'),
        ]);
    }

    public function confirm($id)
    {
        \Config\Database::connect()->table('public_bookings')->where('id',$id)
            ->where('restaurant_id',session('restaurant_id'))
            ->update(['status'=>'confirmed','confirmed_at'=>date('Y-m-d H:i:s')]);
        return $this->response->setJSON(['success'=>true]);
    }

    public function cancel($id)
    {
        \Config\Database::connect()->table('public_bookings')->where('id',$id)
            ->where('restaurant_id',session('restaurant_id'))
            ->update(['status'=>'cancelled','cancelled_by'=>'restaurant','cancelled_at'=>date('Y-m-d H:i:s')]);
        return $this->response->setJSON(['success'=>true]);
    }

    public function noShow($id)
    {
        \Config\Database::connect()->table('public_bookings')->where('id',$id)
            ->where('restaurant_id',session('restaurant_id'))
            ->update(['status'=>'no_show']);
        return $this->response->setJSON(['success'=>true]);
    }

    public function complete($id)
    {
        \Config\Database::connect()->table('public_bookings')->where('id',$id)
            ->where('restaurant_id',session('restaurant_id'))
            ->update(['status'=>'completed']);
        return $this->response->setJSON(['success'=>true]);
    }

    public function settings()
    {
        $db  = \Config\Database::connect();
        $rid = session('restaurant_id');
        $s   = $db->table('booking_settings')->where('restaurant_id',$rid)->get()->getRowArray();
        $r   = $db->table('restaurants')->where('id',$rid)->get()->getRowArray();

        return view('admin/booking_settings/index', [
            'pageTitle' => 'Booking Settings',
            'settings'  => $s,
            'restaurant'=> $r,
            'userName'  => session('user_name'),
            'userRole'  => session('role_slug'),
        ]);
    }

    public function saveSettings()
    {
        $db  = \Config\Database::connect();
        $rid = session('restaurant_id');
        $data = [
            'restaurant_id'          => $rid,
            'is_enabled'             => $this->request->getPost('is_enabled') ? 1 : 0,
            'listed_on_platform'     => $this->request->getPost('listed_on_platform') ? 1 : 0,
            'accepts_online_payment' => $this->request->getPost('accepts_online_payment') ? 1 : 0,
            'booking_advance_days'   => $this->request->getPost('booking_advance_days') ?? 30,
            'slot_duration_min'      => $this->request->getPost('slot_duration_min') ?? 60,
            'min_guests'             => $this->request->getPost('min_guests') ?? 1,
            'max_guests'             => $this->request->getPost('max_guests') ?? 20,
            'deposit_required'       => $this->request->getPost('deposit_required') ? 1 : 0,
            'deposit_type'           => $this->request->getPost('deposit_type') ?? 'flat',
            'deposit_amount'         => $this->request->getPost('deposit_amount') ?? 0,
            'cancellation_hours'     => $this->request->getPost('cancellation_hours') ?? 2,
            'auto_confirm'           => $this->request->getPost('auto_confirm') ? 1 : 0,
            'avg_cost_for_two'       => $this->request->getPost('avg_cost_for_two') ?? 0,
            'cuisines'               => $this->request->getPost('cuisines'),
            'tags'                   => $this->request->getPost('tags'),
            'open_time'              => $this->request->getPost('open_time') ?? '10:00',
            'close_time'             => $this->request->getPost('close_time') ?? '23:00',
            'closed_days'            => implode(',', $this->request->getPost('closed_days') ?? []),
            'booking_instructions'   => $this->request->getPost('booking_instructions'),
            'updated_at'             => date('Y-m-d H:i:s'),
        ];

        $exists = $db->table('booking_settings')->where('restaurant_id',$rid)->countAllResults();
        if ($exists) $db->table('booking_settings')->where('restaurant_id',$rid)->update($data);
        else         $db->table('booking_settings')->insert($data);

        // Update restaurant booking_slug and short_desc
        $db->table('restaurants')->where('id',$rid)->update([
            'booking_slug' => url_title(strtolower($this->request->getPost('booking_slug') ?: ''), '-', true) ?: null,
            'short_desc'   => $this->request->getPost('short_desc'),
        ]);

        return redirect()->to(base_url('admin/booking/settings'))->with('success','Booking settings saved');
    }
}
