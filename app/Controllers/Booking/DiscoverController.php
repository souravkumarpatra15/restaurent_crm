<?php
namespace App\Controllers\Booking;
use App\Controllers\BaseController;

/**
 * DinoViX Public Booking — Discovery & Search
 * Routes under /book/* — no auth required
 */
class DiscoverController extends BaseController
{
    public function index()
    {
        $db   = \Config\Database::connect();
        $city = $this->request->getGet('city') ?? '';
        $q    = $this->request->getGet('q')    ?? '';
        $date = $this->request->getGet('date') ?? date('Y-m-d');
        $pax  = (int)($this->request->getGet('pax') ?? 2);

        $query = $db->table('restaurants r')
            ->join('booking_settings bs','bs.restaurant_id = r.id')
            ->select('r.id, r.name, r.slug, r.booking_slug, r.city, r.state,
                      r.cuisine_type, r.restaurant_type, r.cover_image, r.short_desc,
                      r.theme_color, bs.avg_cost_for_two, bs.tags, bs.cuisines,
                      bs.deposit_required, bs.deposit_amount, bs.min_guests, bs.max_guests,
                      bs.accepts_online_payment')
            ->where('r.is_active', 1)
            ->where('bs.is_enabled', 1)
            ->where('bs.listed_on_platform', 1);

        if ($city) $query->like('r.city', $city);
        if ($q)    $query->groupStart()->like('r.name',$q)->orLike('bs.cuisines',$q)->orLike('bs.tags',$q)->groupEnd();
        if ($pax)  { $query->where('bs.min_guests <=', $pax)->where('bs.max_guests >=', $pax); }

        $restaurants = $query->orderBy('r.name','ASC')->get()->getResultArray();

        // Add availability badge for today/selected date
        foreach ($restaurants as &$rest) {
            $booked = (int)$db->table('public_bookings')
                ->where('restaurant_id',$rest['id'])->where('slot_date',$date)
                ->whereIn('status',['confirmed','pending'])->countAllResults();
            $rest['has_slots'] = $booked < 30; // simplified check
        }

        // Cities for filter
        $cities = $db->table('restaurants r')
            ->join('booking_settings bs','bs.restaurant_id=r.id')
            ->select('DISTINCT r.city')->where('bs.listed_on_platform',1)
            ->where('r.city IS NOT NULL','',false)->orderBy('r.city','ASC')
            ->get()->getResultArray();

        return view('booking/discover', [
            'restaurants' => $restaurants,
            'cities'      => array_column($cities,'city'),
            'city'        => $city,
            'q'           => $q,
            'date'        => $date,
            'pax'         => $pax,
        ]);
    }

    public function restaurant($slug)
    {
        $db   = \Config\Database::connect();
        $rest = $db->table('restaurants r')
            ->join('booking_settings bs','bs.restaurant_id=r.id')
            ->select('r.*, bs.*,
                      r.id as id, r.name as name, r.slug as slug,
                      bs.id as bs_id')
            ->where('r.is_active',1)
            ->where('bs.is_enabled',1)
            ->groupStart()->where('r.slug',$slug)->orWhere('r.booking_slug',$slug)->groupEnd()
            ->get()->getRowArray();

        if (!$rest) return redirect()->to(base_url('book'))->with('error','Restaurant not found');

        // Load available dates (next 30 days)
        $availDates = [];
        $closedDays = $rest['closed_days'] ? explode(',', $rest['closed_days']) : [];
        for ($i = 0; $i < ($rest['booking_advance_days'] ?? 30); $i++) {
            $d   = date('Y-m-d', strtotime("+$i days"));
            $dow = date('w', strtotime($d)); // 0=Sun
            if (!in_array($dow, $closedDays)) $availDates[] = $d;
        }

        // Branches
        $branches = $db->table('branches')->where('restaurant_id',$rest['id'])->where('is_active',1)->get()->getResultArray();

        return view('booking/restaurant', [
            'rest'       => $rest,
            'availDates' => $availDates,
            'branches'   => $branches,
        ]);
    }

    public function slots()
    {
        $db       = \Config\Database::connect();
        $restId   = (int)$this->request->getPost('restaurant_id');
        $date     = $this->request->getPost('date');
        $pax      = (int)($this->request->getPost('pax') ?? 2);

        $rest = $db->table('restaurants r')
            ->join('booking_settings bs','bs.restaurant_id=r.id')
            ->select('bs.slot_duration_min, bs.max_guests, bs.min_guests,
                      bs.open_time, bs.close_time, bs.closed_days')
            ->where('r.id',$restId)->get()->getRowArray();

        if (!$rest) return $this->response->setJSON(['slots'=>[]]);

        // Generate slots from open to close
        $slots    = [];
        $open     = strtotime($date.' '.$rest['open_time']);
        $close    = strtotime($date.' '.$rest['close_time']);
        $duration = ($rest['slot_duration_min'] ?? 60) * 60;
        $now      = time();

        for ($t = $open; $t < $close; $t += $duration) {
            if ($t < $now + 1800) continue; // must book 30 min ahead

            $timeStr = date('H:i:s', $t);
            $booked  = (int)$db->table('public_bookings')
                ->selectSum('guests')
                ->where('restaurant_id',$restId)->where('slot_date',$date)
                ->where('slot_time',$timeStr)->whereIn('status',['confirmed','pending'])
                ->get()->getRowArray()['guests'] ?? 0;

            $maxCovers = $rest['max_guests'] ?? 20;
            $avail     = max(0, $maxCovers - $booked);

            if ($avail >= $pax) {
                $slots[] = [
                    'time'      => $timeStr,
                    'time_fmt'  => date('g:i A', $t),
                    'available' => $avail,
                ];
            }
        }

        return $this->response->setJSON(['slots' => $slots]);
    }

    public function book()
    {
        $db      = \Config\Database::connect();
        $restId  = (int)$this->request->getPost('restaurant_id');
        $branchId= (int)$this->request->getPost('branch_id');

        $rest    = $db->table('restaurants r')
            ->join('booking_settings bs','bs.restaurant_id=r.id')
            ->select('r.*, bs.*')->where('r.id',$restId)->get()->getRowArray();
        if (!$rest) return $this->response->setJSON(['success'=>false,'message'=>'Invalid restaurant']);

        $date    = $this->request->getPost('date');
        $time    = $this->request->getPost('time');
        $guests  = (int)$this->request->getPost('guests');

        // Validate capacity
        $booked = (int)$db->table('public_bookings')
            ->selectSum('guests')->where('restaurant_id',$restId)
            ->where('slot_date',$date)->where('slot_time',$time)
            ->whereIn('status',['confirmed','pending'])
            ->get()->getRowArray()['guests'] ?? 0;
        if ($booked + $guests > ($rest['max_guests'] ?? 20)) {
            return $this->response->setJSON(['success'=>false,'message'=>'Slot full for selected guests']);
        }

        // Generate booking number
        $num = 'DVX' . date('ymd') . strtoupper(substr(uniqid(),-4));

        $status = $rest['auto_confirm'] ? 'confirmed' : 'pending';
        $payReq = $rest['deposit_required'] ? 'pending' : 'not_required';
        $deposit= 0;
        if ($rest['deposit_required']) {
            $deposit = $rest['deposit_type'] === 'per_person'
                ? $rest['deposit_amount'] * $guests
                : $rest['deposit_amount'];
        }

        $bookingId = $db->table('public_bookings')->insert([
            'booking_number'   => $num,
            'restaurant_id'    => $restId,
            'branch_id'        => $branchId ?: null,
            'slot_date'        => $date,
            'slot_time'        => $time,
            'guests'           => $guests,
            'guest_name'       => $this->request->getPost('name'),
            'guest_phone'      => $this->request->getPost('phone'),
            'guest_email'      => $this->request->getPost('email'),
            'special_requests' => $this->request->getPost('special_requests'),
            'occasion'         => $this->request->getPost('occasion') ?? 'none',
            'status'           => $status,
            'payment_status'   => $payReq,
            'payment_amount'   => $deposit,
            'confirmed_at'     => $status === 'confirmed' ? date('Y-m-d H:i:s') : null,
            'created_at'       => date('Y-m-d H:i:s'),
        ]);

        return $this->response->setJSON([
            'success'        => true,
            'booking_number' => $num,
            'status'         => $status,
            'deposit'        => $deposit,
            'deposit_req'    => $rest['deposit_required'],
            'confirm_url'    => base_url('book/confirmation/'.$num),
        ]);
    }

    public function confirmation($num)
    {
        $db      = \Config\Database::connect();
        $booking = $db->table('public_bookings b')
            ->join('restaurants r','r.id=b.restaurant_id')
            ->join('branches br','br.id=b.branch_id','left')
            ->select('b.*, r.name as rname, r.phone as rphone, r.address as raddress,
                      r.city as rcity, r.theme_color, r.cover_image,
                      br.name as bname, br.address as baddress, br.phone as bphone')
            ->where('b.booking_number',$num)->get()->getRowArray();

        if (!$booking) return redirect()->to(base_url('book'))->with('error','Booking not found');
        return view('booking/confirmation', ['booking'=>$booking]);
    }

    public function cancel($num)
    {
        $db      = \Config\Database::connect();
        $booking = $db->table('public_bookings')->where('booking_number',$num)->get()->getRowArray();
        if (!$booking) return $this->response->setJSON(['success'=>false,'message'=>'Not found']);
        if (in_array($booking['status'],['cancelled','completed'])) {
            return $this->response->setJSON(['success'=>false,'message'=>'Cannot cancel this booking']);
        }

        $db->table('public_bookings')->where('booking_number',$num)->update([
            'status'       => 'cancelled',
            'cancelled_by' => 'guest',
            'cancel_reason'=> $this->request->getPost('reason') ?? 'Guest cancelled',
            'cancelled_at' => date('Y-m-d H:i:s'),
        ]);
        return $this->response->setJSON(['success'=>true]);
    }

    public function status($num = null)
    {
        $db  = \Config\Database::connect();
        $num = $num ?? $this->request->getGet('num');
        $booking = null;
        if ($num) {
            $booking = $db->table('public_bookings b')
                ->join('restaurants r','r.id=b.restaurant_id')
                ->select('b.*, r.name as rname, r.theme_color')
                ->where('b.booking_number', strtoupper(trim($num)))
                ->get()->getRowArray();
        }
        return view('booking/status', ['booking'=>$booking]);
    }
}
