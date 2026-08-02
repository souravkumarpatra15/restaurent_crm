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

        // Raw SQL — CI4 query builder breaks with table aliases + r.* selects
        $where  = ['r.is_active = 1', 'bs.is_enabled = 1', 'bs.listed_on_platform = 1'];
        $params = [];

        if ($city) { $where[] = 'r.city LIKE ?'; $params[] = '%' . $city . '%'; }
        if ($q)    {
            $where[]  = '(r.name LIKE ? OR bs.cuisines LIKE ? OR bs.tags LIKE ?)';
            $params[] = '%'.$q.'%'; $params[] = '%'.$q.'%'; $params[] = '%'.$q.'%';
        }
        if ($pax > 0) {
            $where[] = 'bs.min_guests <= ?'; $params[] = $pax;
            $where[] = 'bs.max_guests >= ?'; $params[] = $pax;
        }

        $sql = 'SELECT r.id, r.name, r.slug, r.booking_slug, r.city, r.state,
                       r.cuisine_type, r.restaurant_type, r.cover_image, r.short_desc,
                       r.theme_color,
                       bs.avg_cost_for_two, bs.tags, bs.cuisines,
                       bs.deposit_required, bs.deposit_amount,
                       bs.min_guests, bs.max_guests, bs.accepts_online_payment
                FROM restaurants r
                INNER JOIN booking_settings bs ON bs.restaurant_id = r.id
                WHERE ' . implode(' AND ', $where) . '
                ORDER BY r.name ASC';

        $restaurants = $db->query($sql, $params)->getResultArray();

        // Availability badge for selected date
        foreach ($restaurants as &$rest) {
            $booked = (int)$db->query(
                'SELECT COALESCE(SUM(guests),0) as total FROM public_bookings
                 WHERE restaurant_id = ? AND slot_date = ? AND status IN ("confirmed","pending")',
                [$rest['id'], $date]
            )->getRowArray()['total'];
            $rest['has_slots'] = $booked < ($rest['max_guests'] ?? 20);
        }
        unset($rest);

        // Cities dropdown
        $cities = $db->query(
            'SELECT DISTINCT r.city FROM restaurants r
             INNER JOIN booking_settings bs ON bs.restaurant_id = r.id
             WHERE bs.listed_on_platform = 1 AND r.city IS NOT NULL AND r.is_active = 1
             ORDER BY r.city ASC'
        )->getResultArray();

        return view('booking/discover', [
            'restaurants' => $restaurants,
            'cities'      => array_column($cities, 'city'),
            'city'        => $city,
            'q'           => $q,
            'date'        => $date,
            'pax'         => $pax,
        ]);
    }

    public function restaurant($slug)
    {
        $db   = \Config\Database::connect();

        // Raw SQL — avoid CI4 alias bug with r.* selects
        $rest = $db->query(
            'SELECT r.*, bs.*,
                    r.id AS id, r.name AS name, r.slug AS slug,
                    bs.id AS bs_id
             FROM restaurants r
             INNER JOIN booking_settings bs ON bs.restaurant_id = r.id
             WHERE r.is_active = 1 AND bs.is_enabled = 1
               AND (r.slug = ? OR r.booking_slug = ?)
             LIMIT 1',
            [$slug, $slug]
        )->getRowArray();

        if (!$rest) {
            return redirect()->to(base_url('book'))->with('error', 'Restaurant not found or bookings disabled');
        }

        // Available dates (skip closed days)
        $availDates = [];
        $closedDays = $rest['closed_days'] ? explode(',', $rest['closed_days']) : [];
        $advanceDays = (int)($rest['booking_advance_days'] ?? 30);
        for ($i = 0; $i < $advanceDays; $i++) {
            $d   = date('Y-m-d', strtotime("+$i days"));
            $dow = (int)date('w', strtotime($d));
            if (!in_array((string)$dow, $closedDays)) {
                $availDates[] = $d;
            }
        }

        $branches = $db->table('branches')
            ->where('restaurant_id', $rest['id'])
            ->where('is_active', 1)
            ->get()->getResultArray();

        // Menu photos for the strip — items with images
        $menuPhotos = $db->query(
            'SELECT name, image FROM menu_items
             WHERE restaurant_id = ? AND image IS NOT NULL AND image != "" AND is_active = 1
             ORDER BY is_bestseller DESC, is_recommended DESC
             LIMIT 15',
            [$rest['id']]
        )->getResultArray();

        return view('booking/restaurant', [
            'rest'       => $rest,
            'availDates' => $availDates,
            'branches'   => $branches,
            'menuPhotos' => $menuPhotos,
        ]);
    }

    public function slots()
    {
        $db     = \Config\Database::connect();
        $restId = (int)$this->request->getPost('restaurant_id');
        $date   = $this->request->getPost('date');
        $pax    = (int)($this->request->getPost('pax') ?? 2);

        $rest = $db->query(
            'SELECT bs.slot_duration_min, bs.max_guests, bs.min_guests,
                    bs.open_time, bs.close_time, bs.closed_days
             FROM booking_settings bs
             WHERE bs.restaurant_id = ?
             LIMIT 1',
            [$restId]
        )->getRowArray();

        if (!$rest || !$date) {
            return $this->response->setJSON(['slots' => []]);
        }

        $slots    = [];
        $open     = strtotime($date . ' ' . $rest['open_time']);
        $close    = strtotime($date . ' ' . $rest['close_time']);
        $duration = ((int)($rest['slot_duration_min'] ?? 60)) * 60;
        $now      = time();
        $maxPax   = (int)($rest['max_guests'] ?? 20);

        for ($t = $open; $t < $close; $t += $duration) {
            if ($t < $now + 1800) continue; // 30 min advance minimum

            $timeStr = date('H:i:s', $t);

            $booked = (int)$db->query(
                'SELECT COALESCE(SUM(guests),0) as total FROM public_bookings
                 WHERE restaurant_id = ? AND slot_date = ? AND slot_time = ?
                   AND status IN ("confirmed","pending")',
                [$restId, $date, $timeStr]
            )->getRowArray()['total'];

            $avail = max(0, $maxPax - $booked);
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

        $rest = $db->query(
            'SELECT r.id, r.name,
                    bs.auto_confirm, bs.max_guests, bs.deposit_required,
                    bs.deposit_type, bs.deposit_amount, bs.accepts_online_payment
             FROM restaurants r
             INNER JOIN booking_settings bs ON bs.restaurant_id = r.id
             WHERE r.id = ? AND r.is_active = 1 AND bs.is_enabled = 1
             LIMIT 1',
            [$restId]
        )->getRowArray();

        if (!$rest) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid restaurant']);
        }

        $date   = $this->request->getPost('date');
        $time   = $this->request->getPost('time');
        $guests = (int)$this->request->getPost('guests');

        // Check capacity
        $booked = (int)$db->query(
            'SELECT COALESCE(SUM(guests),0) as total FROM public_bookings
             WHERE restaurant_id = ? AND slot_date = ? AND slot_time = ?
               AND status IN ("confirmed","pending")',
            [$restId, $date, $time]
        )->getRowArray()['total'];

        if ($booked + $guests > (int)($rest['max_guests'] ?? 20)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Slot full for selected guests. Please pick another time.']);
        }

        // Booking number: DVX + yymmdd + 4 random chars
        $num = 'DVX' . date('ymd') . strtoupper(substr(uniqid(), -4));

        $status  = $rest['auto_confirm'] ? 'confirmed' : 'pending';
        $payReq  = $rest['deposit_required'] ? 'pending' : 'not_required';
        $deposit = 0;
        if ($rest['deposit_required']) {
            $deposit = $rest['deposit_type'] === 'per_person'
                ? (float)$rest['deposit_amount'] * $guests
                : (float)$rest['deposit_amount'];
        }

        $db->table('public_bookings')->insert([
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
            'deposit_req'    => (bool)$rest['deposit_required'],
            'confirm_url'    => base_url('book/confirmation/' . $num),
        ]);
    }

    public function confirmation($num)
    {
        $db      = \Config\Database::connect();
        $booking = $db->query(
            'SELECT b.*,
                    r.name AS rname, r.phone AS rphone, r.address AS raddress,
                    r.city AS rcity, r.theme_color, r.cover_image,
                    br.name AS bname, br.address AS baddress, br.phone AS bphone
             FROM public_bookings b
             INNER JOIN restaurants r ON r.id = b.restaurant_id
             LEFT  JOIN branches br   ON br.id = b.branch_id
             WHERE b.booking_number = ?
             LIMIT 1',
            [$num]
        )->getRowArray();

        if (!$booking) {
            return redirect()->to(base_url('book'))->with('error', 'Booking not found');
        }

        return view('booking/confirmation', ['booking' => $booking]);
    }

    public function cancel($num)
    {
        $db      = \Config\Database::connect();
        $booking = $db->table('public_bookings')
            ->where('booking_number', $num)
            ->get()->getRowArray();

        if (!$booking) {
            return $this->response->setJSON(['success' => false, 'message' => 'Booking not found']);
        }
        if (in_array($booking['status'], ['cancelled', 'completed'])) {
            return $this->response->setJSON(['success' => false, 'message' => 'This booking cannot be cancelled']);
        }

        $db->table('public_bookings')->where('booking_number', $num)->update([
            'status'        => 'cancelled',
            'cancelled_by'  => 'guest',
            'cancel_reason' => $this->request->getPost('reason') ?? 'Guest cancelled',
            'cancelled_at'  => date('Y-m-d H:i:s'),
        ]);

        return $this->response->setJSON(['success' => true]);
    }

    public function status($num = null)
    {
        $db      = \Config\Database::connect();
        $num     = $num ?? $this->request->getGet('num');
        $booking = null;

        if ($num) {
            $booking = $db->query(
                'SELECT b.*, r.name AS rname, r.theme_color
                 FROM public_bookings b
                 INNER JOIN restaurants r ON r.id = b.restaurant_id
                 WHERE b.booking_number = ?
                 LIMIT 1',
                [strtoupper(trim($num))]
            )->getRowArray();
        }

        return view('booking/status', [
            'booking'      => $booking,
            'numInput'     => $num ? strtoupper(trim($num)) : '',
            'showNotFound' => ($num && !$booking),
        ]);
    }
}
