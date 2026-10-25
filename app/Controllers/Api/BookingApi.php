<?php

namespace App\Controllers\Api;

use CodeIgniter\Controller;

/**
 * Public Booking API — lets restaurant websites / widgets create table
 * reservations without a DinoviX session. Two endpoints:
 *
 *   GET  /api/bookings/slots?restaurant=<id|slug>&date=YYYY-MM-DD&guests=N
 *   POST /api/bookings            (JSON body: same fields as the widget form)
 *
 * Access rules:
 *  - restaurant must have booking enabled (booking_settings.is_enabled)
 *  - restaurant's plan must include allow_api_access (higher-tier plans)
 *  - every booking_setting rule is enforced: advance days, closed days,
 *    open/close window, min/max guests and per-slot capacity
 */
class BookingApi extends Controller
{
    private function json($data, int $code = 200)
    {
        return $this->response->setStatusCode($code)
            ->setHeader('Content-Type', 'application/json')
            ->setJSON($data);
    }

    private function forbidden(string $message)
    {
        return $this->json(['success' => false, 'error' => $message], 403);
    }

    /** Shared tenant lookup + plan gate */
    private function loadTenant(string $slugOrId): ?array
    {
        $db = \Config\Database::connect();

        // Plan gate FIRST — API access is a plan feature
        $rest = $db->query(
            'SELECT r.id, r.name, r.slug, p.allow_api_access,
                    bs.auto_confirm, bs.max_guests, bs.min_guests, bs.is_enabled,
                    bs.open_time, bs.close_time, bs.closed_days,
                    bs.booking_advance_days, bs.slot_duration_min,
                    bs.deposit_required, bs.deposit_type, bs.deposit_amount
             FROM restaurants r
             LEFT JOIN saas_plans p ON p.id = r.plan_id
             LEFT JOIN booking_settings bs ON bs.restaurant_id = r.id
             WHERE (r.slug = ? OR r.id = ?) AND r.is_active = 1
             LIMIT 1',
            [$slugOrId, ctype_digit($slugOrId) ? (int)$slugOrId : 0]
        )->getRowArray();

        if (!$rest) {
            return null;
        }
        return $rest;
    }

    private function guard(array $rest)
    {
        if (empty($rest['is_enabled'])) {
            return 'This restaurant has not enabled online bookings';
        }
        if (!$rest['allow_api_access']) {
            return 'API access is not available on this plan'; // surfaced as HTTP 403
        }
        return null;
    }

    public function slots()
    {
        $tenant = $this->loadTenant((string)($this->request->getGet('restaurant') ?? ''));
        if (!$tenant)            return $this->json(['success' => false, 'error' => 'Restaurant not found'], 404);
        if ($deny = $this->guard($tenant)) {
            if ($deny === 'API access is not available on this plan') {
                return $this->forbidden($deny);
            }
            return $this->json(['success' => false, 'error' => $deny], 200);
        }

        $date = $this->request->getGet('date') ?: date('Y-m-d');
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)$date)) {
            return $this->json(['success' => false, 'error' => 'Invalid date (use YYYY-MM-DD)'], 422);
        }
        $pax = max(1, (int)($this->request->getGet('guests') ?? 2));

        // Calendar rules
        $closedDays = $tenant['closed_days'] ? explode(',', $tenant['closed_days']) : [];
        if (in_array((string)(int)date('w', strtotime($date)), $closedDays, true)) {
            return $this->json(['success' => true, 'date' => $date, 'slots' => [], 'message' => 'Closed on this day']);
        }
        $maxAdvance = (int)($tenant['booking_advance_days'] ?? 30);
        if (strtotime($date) > strtotime("+" . $maxAdvance . " days")) {
            return $this->json(['success' => true, 'date' => $date, 'slots' => [], 'message' => 'Beyond ' . $maxAdvance . ' days ahead']);
        }

        $db  = \Config\Database::connect();
        $open  = strtotime($date . ' ' . ($tenant['open_time'] ?? '11:00:00'));
        $close = strtotime($date . ' ' . ($tenant['close_time'] ?? '23:00:00'));
        $step  = max(15, (int)($tenant['slot_duration_min'] ?? 60)) * 60;
        $maxPax = (int)($tenant['max_guests'] ?? 20);
        $now = time();

        $slots = [];
        for ($t = $open; $t < $close; $t += $step) {
            if ($t < $now + 1800) continue; // 30-min advance
            $timeStr = date('H:i:s', $t);
            $booked = (int)$db->query(
                'SELECT COALESCE(SUM(guests),0) as total FROM public_bookings
                 WHERE restaurant_id = ? AND slot_date = ? AND slot_time = ?
                   AND status IN ("confirmed","pending")',
                [$tenant['id'], $date, $timeStr]
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

        return $this->json([
            'success' => true,
            'restaurant' => ['id' => (int)$tenant['id'], 'name' => $tenant['name']],
            'date'   => $date,
            'guests' => $pax,
            'slots'  => $slots,
        ]);
    }

    public function book()
    {
        // Accept JSON body (widget) and form-encoding alike
        if (str_contains((string)($this->request->getHeaderLine('Content-Type')), 'application/json')) {
            $guestData = $this->request->getJSON(true) ?: [];
        } else {
            $guestData = $this->request->getPost() ?: [];
        }
        $tenant = $this->loadTenant((string)($guestData['restaurant'] ?? ''));
        if (!$tenant)            return $this->json(['success' => false, 'error' => 'Restaurant not found'], 404);
        if ($deny = $this->guard($tenant)) {
            if ($deny === 'API access is not available on this plan') {
                return $this->forbidden($deny);
            }
            return $this->json(['success' => false, 'error' => $deny], 200);
        }

        // Required payload
        $rules = [
            'date'   => '/^\d{4}-\d{2}-\d{2}$/',
        ];
        foreach ($rules as $k => $pattern) {
            $v = (string)($guestData[$k] ?? '');
            if (!preg_match($pattern, $v)) {
                return $this->json(['success' => false, 'error' => 'Invalid or missing ' . $k], 422);
            }
        }
        $guests = (int)($guestData['guests'] ?? 0);
        if ($guests < (int)($tenant['min_guests'] ?? 1) || $guests > (int)($tenant['max_guests'] ?? 20)) {
            return $this->json(['success' => false, 'error' => 'Guests must be between '
                . (int)($tenant['min_guests'] ?? 1) . ' and ' . (int)($tenant['max_guests'] ?? 20)], 422);
        }
        if (empty($guestData['time']) || !preg_match('/^\d{2}:\d{2}/', (string)$guestData['time'])) {
            return $this->json(['success' => false, 'error' => 'Invalid or missing time (HH:MM)'], 422);
        }
        if (empty($guestData['name']) || empty($guestData['phone'])) {
            return $this->json(['success' => false, 'error' => 'Name and phone are required'], 422);
        }
        $validPhone = preg_match("/^[0-9+\-\s]{6,20}$/", (string)$guestData['phone']);
        if (!$validPhone) {
             return $this->json(['success' => false, 'error' => 'Phone format is invalid'], 422);
        }

        $db = \Config\Database::connect();

        // Slot-time + calendar drop-in for the requested slot
        if (strtotime($guestData['date']) < strtotime(date('Y-m-d'))) {
            return $this->json(['success' => false, 'error' => 'Date already past'], 422);
        }
        $closedDays = $tenant['closed_days'] ? explode(',', $tenant['closed_days']) : [];
        if (in_array((string)(int)date('w', strtotime($guestData['date'])), $closedDays, true)) {
            return $this->json(['success' => false, 'error' => 'Closed on that day'], 409);
        }
        $startTime = strtotime($guestData['date'] . ' ' . $guestData['time']);
        $openT  = strtotime($guestData['date'] . ' ' . ($tenant['open_time'] ?? '11:00:00'));
        $closeT = strtotime($guestData['date'] . ' ' . ($tenant['close_time'] ?? '23:00:00'));
        if ($startTime < $openT || $startTime >= $closeT) {
            return $this->json(['success' => false, 'error' => 'Time outside the opening window ('
                . date('g:i A', $openT) . ' – ' . date('g:i A', $closeT) . ')'], 409);
        }

        // Capacity on the requested slot
        $timeStr = date('H:i:s', $startTime);
        $booked = (int)$db->query(
            'SELECT COALESCE(SUM(guests),0) as total FROM public_bookings
             WHERE restaurant_id = ? AND slot_date = ? AND slot_time = ?
               AND status IN ("confirmed","pending")',
            [$tenant['id'], $guestData['date'], $timeStr]
        )->getRowArray()['total'];
        if ($booked + $guests > (int)($tenant['max_guests'] ?? 20)) {
            return $this->json(['success' => false, 'error' => 'That slot cannot fit ' . $guests . ' guests'], 409);
        }

        // Booking number: DVX + yymmdd + 4 random chars
        $num = 'DVX' . date('ymd') . strtoupper(substr(uniqid(), -4));

        $status  = $tenant['auto_confirm'] ? 'confirmed' : 'pending';
        $payReq  = $tenant['deposit_required'] ? 'pending' : 'not_required';
        $deposit = 0;
        if ($tenant['deposit_required']) {
            $deposit = $tenant['deposit_type'] === 'per_person'
                ? (float)$tenant['deposit_amount'] * $guests
                : (float)$tenant['deposit_amount'];
        }

        $db->table('public_bookings')->insert([
            'booking_number'   => $num,
            'restaurant_id'    => $tenant['id'],
            'branch_id'        => null,
            'slot_date'        => $guestData['date'],
            'slot_time'        => $timeStr,
            'guests'           => $guests,
            'guest_name'       => (string)$guestData['name'],
            'guest_phone'      => (string)$guestData['phone'],
            'guest_email'      => $guestData['email'] ?? null,
            'special_requests' => $guestData['special_requests'] ?? null,
            'occasion'         => in_array($guestData['occasion'] ?? 'none',
                ['birthday','anniversary','business','date','family','other','none'], true)
                ? $guestData['occasion'] : 'none',
            'status'           => $status,
            'payment_status'   => $payReq,
            'payment_amount'   => $deposit,
            'created_at'       => date('Y-m-d H:i:s'),
        ]);
        $bookingId = $db->insertID();

        return $this->json([
            'success' => true,
            'booking' => [
                'id'      => $bookingId,
                'number'  => $num,
                'status'  => $status,
                'deposit' => $deposit,
                'when'    => $guestData['date'] . ' ' . date('g:i A', $startTime),
                'guests'  => $guests,
                'for'     => $guestData['name'],
            ],
        ], 201);
    }
}
