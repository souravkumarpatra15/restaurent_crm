<?php

namespace App\Controllers\Staff;

use App\Controllers\BaseController;

// PHP QR Code (LGPL, https://sourceforge.net/projects/php-qrcode /
// t0k4rt port) — bundled single-file encoder, global class \QRcode
require_once APPPATH . 'Libraries' . DIRECTORY_SEPARATOR . 'phpqrcode' . DIRECTORY_SEPARATOR . 'phpqrcode.php';

/**
 * Restaurant-owned UPI QR payments.
 *
 * DinoviX NEVER collects or holds restaurant funds: the QR points the
 * customer's UPI app straight at the restaurant's own VPA. Payment is
 * confirmed ONLY by authorised staff after they check the real incoming
 * transaction in the restaurant's bank/merchant app — customer actions,
 * screenshots, browser redirects and polling can never mark money paid.
 */
class UpiController extends BaseController
{
    /** QR attempt lifetime (minutes). Money arriving later can still be verified. */
    private const QR_LIFETIME_MIN = 30;

    // ══════════════════════════════════════════════════════════════
    // Restaurant UPI settings (restaurant administrators only)
    // ══════════════════════════════════════════════════════════════

    public function settings()
    {
        if (session('role_slug') !== 'restaurant_admin') {
            return redirect()->to(base_url('admin/dashboard'))->with('error', 'Only restaurant administrators can change payment settings.');
        }

        $db  = \Config\Database::connect();
        $rid = (int)session('restaurant_id');

        $restaurant = $db->table('restaurants r')
            ->select('r.id, r.name, r.upi_enabled, r.upi_vpa, r.upi_payee_name,
                      r.upi_settings_updated_at, u.name as updated_by_name')
            ->join('users u', 'u.id = r.upi_settings_updated_by', 'left')
            ->where('r.id', $rid)
            ->get()->getRowArray();

        $lastChange = $db->table('payment_verifications')
            ->where('restaurant_id', $rid)
            ->where('event_type', 'settings_change')
            ->orderBy('id', 'DESC')->limit(5)->get()->getResultArray();

        return view('admin/upi/settings', [
            'pageTitle'  => 'UPI Payments',
            'restaurant' => $restaurant,
            'lastChange' => $lastChange,
        ]);
    }

    public function saveSettings()
    {
        if (session('role_slug') !== 'restaurant_admin') {
            return redirect()->to(base_url('admin/dashboard'))->with('error', 'Only restaurant administrators can change payment settings.');
        }

        $db  = \Config\Database::connect();
        $rid = (int)session('restaurant_id');

        $restaurant = $db->table('restaurants')->select('name, upi_enabled, upi_vpa, upi_payee_name')->where('id', $rid)->get()->getRowArray();
        if (!$restaurant) {
            return redirect()->back()->with('error', 'Restaurant not found');
        }

        $enabled  = $this->request->getPost('upi_enabled') ? 1 : 0;
        $vpa      = strtolower(trim((string)$this->request->getPost('upi_vpa')));
        $payeeRaw = trim((string)$this->request->getPost('upi_payee_name'));

        if ($enabled) {
            // UPI VPA: local@handle — safe, strict character set
            if (!preg_match('/^[a-z0-9][a-z0-9._\-]{1,120}@[a-z][a-z0-9.\-]{1,60}$/', $vpa)) {
                return redirect()->back()->withInput()->with('error', 'That does not look like a valid UPI ID (format: yourname@bank).');
            }
            // Payee name: what UPI apps display with the amount
            $payee = preg_replace('/[\r\n\t]+/', ' ', $payeeRaw);
            $payee = trim(preg_replace('/[^A-Za-z0-9 .&()\-]/', '', $payee));
            if (mb_strlen($payee) < 3 || mb_strlen($payee) > 40) {
                return redirect()->back()->withInput()->with('error', 'Payee name must be 3-40 characters (it is shown in the customer\'s UPI app).');
            }
        } else {
            $vpa   = null;
            $payee = null;
        }

        $db->transStart();

        $db->table('restaurants')->where('id', $rid)->update([
            'upi_enabled'            => $enabled,
            'upi_vpa'                => $vpa,
            'upi_payee_name'         => $payee,
            'upi_settings_updated_at'=> date('Y-m-d H:i:s'),
            'upi_settings_updated_by'=> (int)session('user_id'),
        ]);

        if ((int)$restaurant['upi_vpa'] !== $vpa || $restaurant['upi_payee_name'] !== $payee || (int)$restaurant['upi_enabled'] !== $enabled) {
            $db->table('payment_verifications')->insert([
                'restaurant_id' => $rid,
                'event_type'    => 'settings_change',
                'new_state'     => $enabled ? 'enabled' : 'disabled',
                'acted_by'      => (int)session('user_id'),
                'txn_ref'       => $vpa,
                'note'          => 'UPI settings changed. Previous: '
                    . ((int)$restaurant['upi_enabled'] ? 'enabled' : 'disabled')
                    . ', VPA ' . ($this->maskVpa($restaurant['upi_vpa']) ?: '(none)'),
                'created_at'    => date('Y-m-d H:i:s'),
            ]);
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->with('error', 'Could not save UPI settings — please try again.');
        }

        return redirect()->to(base_url('admin/upi-settings'))->with('success', $enabled
            ? 'UPI QR payments enabled. Every bill can now be paid into your own account.'
            : 'UPI QR payments disabled.');
    }

    private function maskVpa(?string $vpa): string
    {
        if (!$vpa) return '';
        [$local, $handle] = array_pad(explode('@', $vpa, 2), 2, '');
        $masked = strlen($local) <= 2 ? str_repeat('*', strlen($local)) : substr($local, 0, 2) . str_repeat('*', max(1, strlen($local) - 2));
        return $masked . '@' . $handle;
    }

    // ══════════════════════════════════════════════════════════
    // QR attempt generation (POS)
    // ══════════════════════════════════════════════════════════

    public function qr()
    {
        $orderId = (int)$this->request->getPost('order_id');
        $db      = \Config\Database::connect();
        $rid     = (int)session('restaurant_id');
        $bid     = (int)session('branch_id');

        $order = $db->table('orders')
            ->select('id, restaurant_id, branch_id, order_number, total_amount, paid_amount, balance_amount, payment_status, status')
            ->where('id', $orderId)
            ->where('restaurant_id', $rid)
            ->where('branch_id', $bid)
            ->get()->getRowArray();

        if (!$order) {
            return $this->response->setJSON(['success' => false, 'message' => 'Order not found']);
        }

        $res = \App\Libraries\UpiAttempts::create($db, $order, (int)session('user_id'), 'pos');
        if (!$res['ok']) {
            $msg = $res['message'];
            $json = ['success' => false, 'message' => $msg];
            if (str_contains($msg, 'not enabled')) {
                $json['message']    = 'UPI QR payments are not enabled for this restaurant. Configure your UPI ID first (Admin → UPI Payments).';
                $json['needs_setup'] = true;
            }
            if (str_contains($msg, 'already held')) {
                $json['show_pending'] = true;
            }
            return $this->response->setJSON($json);
        }

        $restaurant = $db->table('restaurants')
            ->select('name, upi_enabled, upi_vpa, upi_payee_name')
            ->where('id', $rid)->get()->getRowArray();
        $qrDataUri = $this->encodeQr($res['uri'], $res['ref']);

        return $this->response->setJSON([
            'success'      => true,
            'attempt_ref'  => $res['ref'],
            'payment_id'   => (int)$db->query('SELECT id FROM payments WHERE attempt_ref = ?', [$res['ref']])->getRowArray()['id'],
            'amount'       => $res['amount'],
            'vpa'          => $restaurant['upi_vpa'],
            'payee'        => $restaurant['upi_payee_name'],
            'upi_uri'      => $res['uri'],
            'qr'           => $qrDataUri,
            'expires_at'   => date('d M h:i A', strtotime($res['expires_at'])),
            'customer_url' => base_url('upi/pay/' . $res['ref']),
            'status_url'   => base_url('pos/upi/status/' . $res['ref']),
        ]);
    }

    /** Server-side outstanding balance (string, scale 2). QR attempts (pending) hold their amount. */
    private function outstanding($db, array $order): string
    {
        $collected = (string)round((float)$db->query(
            "SELECT COALESCE(SUM(CASE WHEN status = 'completed' THEN amount
                                     WHEN status = 'refunded' THEN -amount
                                     ELSE 0 END), 0) AS t
             FROM payments WHERE order_id = ?",
            [$order['id']]
        )->getRowArray()['t'], 2);

        $outstanding = bcsub((string)round((float)$order['total_amount'], 2), $collected, 2);
        return bccomp($outstanding, '0.00', 2) < 0 ? '0.00' : $outstanding;
    }

    /** Encode the QR PNG into writable/uploads/upi_qr and return its data-URI */
    private function encodeQr(string $uri, string $attemptRef)
    {
        $dir = rtrim(WRITEPATH, '/\\') . '/uploads/upi_qr';
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        $path = $dir . '/qr_' . $attemptRef . '.png';
        try {
            \QRcode::png($uri, $path, QR_ECLEVEL_M, 6, 2);
            return 'data:image/png;base64,' . base64_encode(file_get_contents($path));
        } catch (\Throwable $e) {
            // The bank app can also read the URI from the customer link;
            // never show a broken image as if it were a QR
            log_message('error', 'UPI QR encode failed for ' . $attemptRef . ': ' . $e->getMessage());
            return null;
        }
    }

    // ══════════════════════════════════════════════════════════
    // Status polling (staff, while POS screen open)
    // ══════════════════════════════════════════════════════════

    public function status(string $attemptRef)
    {
        $db  = \Config\Database::connect();
        $rid = (int)session('restaurant_id');

        $pay = $db->query(
            "SELECT p.id, p.verify_status, p.amount, p.claimed_by_customer, p.claimed_at,
                    p.expires_at, p.verified_at, p.verify_txn_ref, p.verify_note,
                    o.order_number, o.restaurant_id
             FROM payments p
             JOIN orders o ON o.id = p.order_id
             WHERE p.attempt_ref = ? AND o.restaurant_id = ?
             LIMIT 1",
            [$attemptRef, $rid]
        )->getRowArray();

        if (!$pay) {
            return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Payment attempt not found']);
        }

        // Lazy expiry: actual money may still arrive — verified history is kept
        if ($pay['verify_status'] === 'pending' && $pay['expires_at'] && $pay['expires_at'] < date('Y-m-d H:i:s')) {
            $db->transStart();
            $db->table('payments')->where('id', $pay['id'])->where('verify_status', 'pending')->update(['verify_status' => 'expired']);
            $this->audit($db, $pay['id'], $rid, null, 'note', 'pending', 'expired', (int)session('user_id'), null, 'QR expired after ' . self::QR_LIFETIME_MIN . ' min (lazy)');
            $db->transComplete();
            $pay['verify_status'] = 'expired';
        }

        return $this->response->setJSON([
            'success'  => true,
            'state'    => $pay['verify_status'],
            'amount'   => $pay['amount'],
            'order'    => $pay['order_number'],
            'claimed'  => (bool)$pay['claimed_by_customer'],
            'claimed_at' => $pay['claimed_at'],
            'expires_at' => $pay['expires_at'],
            'verified_at'=> $pay['verified_at'],
            'txn_ref'    => $pay['verify_txn_ref'],
            'note'       => $pay['verify_note'],
        ]);
    }

    // ══════════════════════════════════════════════════════════
    // Manual verification — the ONLY way money becomes "paid"
    // ══════════════════════════════════════════════════════════

    private function canVerify(): bool
    {
        return in_array(session('role_slug'), ['restaurant_admin', 'branch_manager', 'cashier'], true);
    }

    public function verify()
    {
        if (!$this->canVerify()) {
            return $this->response->setStatusCode(403)->setJSON(['success' => false, 'message' => 'You are not allowed to verify payments']);
        }

        $attemptRef = trim((string)$this->request->getPost('attempt_ref'));
        $txnRef     = strtoupper(trim((string)$this->request->getPost('txn_ref')));
        $note       = trim((string)$this->request->getPost('note'));
        $receivedIn = trim((string)$this->request->getPost('amount'));

        $db  = \Config\Database::connect();
        $rid = (int)session('restaurant_id');

        if ($attemptRef === '') {
            return $this->response->setJSON(['success' => false, 'message' => 'Payment attempt reference missing']);
        }
        if ($txnRef !== '' && !preg_match('/^[A-Za-z0-9\-\._\/]{4,64}$/', $txnRef)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Transaction reference format looks invalid — leave it empty if your app doesn\'t show one']);
        }

        // UTR is optional: staff may just confirm after checking the bank app.
        // Blank ref records a unique automatic reference so the audit trail
        // and duplicate guard still work (real UTRs dedupe; AUTO refs never collide).
        if ($txnRef === '') {
            $txnRef = 'AUTO-' . $attemptRef;
            if ($note === '') {
                $note = 'Verified in the bank/merchant app without entering a reference';
            }
        }

        // Row-locked read: payment, order, tenant/branch access
        $db->transBegin();

        $pay = $db->query(
            "SELECT p.*, o.restaurant_id, o.branch_id AS o_branch, o.order_number,
                    o.total_amount, o.payment_status, o.status AS o_status
             FROM payments p
             JOIN orders o ON o.id = p.order_id
             WHERE p.attempt_ref = ?
             FOR UPDATE",
            [$attemptRef]
        )->getRowArray();

        if (!$pay || (int)$pay['restaurant_id'] !== $rid) {
            $this->rollbackQuiet($db);
            return $this->response->setJSON(['success' => false, 'message' => 'Payment attempt not found']);
        }
        // Cashiers verify only their own branch; managers/admins any branch of the tenant
        if (session('role_slug') === 'cashier' && (int)$pay['o_branch'] !== (int)session('branch_id')) {
            $this->rollbackQuiet($db);
            return $this->response->setStatusCode(403)->setJSON(['success' => false, 'message' => 'This payment belongs to another branch']);
        }
        if (!in_array($pay['verify_status'], ['pending', 'expired'], true)) {
            $this->rollbackQuiet($db);
            return $this->response->setJSON(['success' => false, 'message' => 'Only pending/expired attempts can be verified (state: ' . $pay['verify_status'] . ')']);
        }
        if ($pay['o_status'] === 'cancelled') {
            $this->rollbackQuiet($db);
            return $this->response->setJSON(['success' => false, 'message' => 'This order was cancelled — verify through a refund instead']);
        }

        // Received amount: staff confirms against the bank statement
        $expected = (string)round((float)$pay['amount'], 2);
        $received = $receivedIn === '' ? $expected : (string)round((float)$receivedIn, 2);
        if (bccomp($received, '0.00', 2) <= 0 || bccomp($received, '999999.99', 2) > 0) {
            $this->rollbackQuiet($db);
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid received amount']);
        }

        // Duplicate-transaction guard: same UTR can never settle two payments
        $dupe = (int)$db->query(
            "SELECT COUNT(*) AS c FROM payments p
             JOIN orders o ON o.id = p.order_id
             WHERE p.verify_txn_ref = ? AND o.restaurant_id = ? AND p.id != ?",
            [$txnRef, $rid, $pay['id']]
        )->getRowArray()['c'];
        if ($dupe > 0) {
            $this->rollbackQuiet($db);
            return $this->response->setJSON(['success' => false, 'message' => 'That transaction reference is already recorded on another payment']);
        }

        // Amount must match the money actually collected in this QR (±₹1 tolerance for razorpay-style rounding? No — UPI QR collects exact)
        if (bccomp($received, $expected, 2) !== 0) {
            $this->rollbackQuiet($db);
            return $this->response->setJSON([
                'success' => false,
                'message' => "Received ₹{$received} does not match the QR amount ₹{$expected}. Compare with the bank statement; reject the claim if it is a different payment.",
            ]);
        }

        // 1) secure the payment row
        $db->table('payments')->where('id', $pay['id'])->update([
            'amount'          => $received,
            'verify_status'   => 'paid',
            'status'          => 'completed',
            'verify_txn_ref'  => $txnRef,
            'verify_note'     => $note ?: null,
            'verified_by'     => (int)session('user_id'),
            'verified_at'     => date('Y-m-d H:i:s'),
        ]);

        $this->audit($db, $pay['id'], $rid, (int)$pay['o_branch'],
            'verification', $pay['verify_status'], 'paid', (int)session('user_id'), $txnRef,
            $note ?: 'Verified manually against the bank/UPI app');

        // 2) recompute the order's collection state under the same lock
        $collected = (string)round((float)$db->query(
            "SELECT COALESCE(SUM(CASE WHEN status = 'completed' THEN amount
                                      WHEN status = 'refunded' THEN -amount
                                      ELSE 0 END), 0) AS t
             FROM payments WHERE order_id = ?",
            [$pay['order_id']]
        )->getRowArray()['t'], 2);
        $total  = (string)round((float)$pay['total_amount'], 2);
        $remain = bcsub($total, $collected, 2);
        if (bccomp($remain, '0.00', 2) < 0) $remain = '0.00';

        $orderUpdate = ['paid_amount' => $collected, 'balance_amount' => $remain];
        $fullyPaid = bccomp($remain, '0.00', 2) === 0;
        if ($fullyPaid) {
            $orderUpdate['payment_status'] = 'paid';
        } else {
            $orderUpdate['payment_status'] = 'partial';
        }
        $db->table('orders')->where('id', $pay['order_id'])->update($orderUpdate);

        // Fully paid through the QR → give the order the EXACT same
        // finish as counter checkout: completed, invoice, feedback token,
        // table freed, loyalty earned. Otherwise dine-in tables stayed
        // occupied and sales reports missed QR income.
        $invoiceNumber = null;
        if ($fullyPaid) {
            $fin = \App\Libraries\OrderFinalize::complete($db, (int)$pay['order_id'], [
                'payment_status' => 'paid',
                'paid_amount'    => $collected,
                'balance_amount' => '0.00',
            ]);
            $invoiceNumber = $fin['invoice_number'];
        }

        $this->audit($db, $pay['id'], $rid, (int)$pay['o_branch'],
            'note', null, null, (int)session('user_id'), $txnRef,
            'Order ledger updated — paid ' . $collected . ', balance ' . $remain);

        $db->query('COMMIT');

        return $this->response->setJSON([
            'success'      => true,
            'message'      => $fullyPaid
                ? 'Payment verified — order fully paid & completed (₹' . $collected . ' collected)'
                : 'Payment verified (₹' . $collected . ' collected; ₹' . $remain . ' still outstanding)',
            'fully_paid'   => $fullyPaid,
            'paid'         => $collected,
            'balance'      => $remain,
            'invoice_number' => $invoiceNumber,
        ]);
    }

    public function reject()
    {
        if (!$this->canVerify()) {
            return $this->response->setStatusCode(403)->setJSON(['success' => false, 'message' => 'You are not allowed to verify payments']);
        }

        $attemptRef = trim((string)$this->request->getPost('attempt_ref'));
        $reason     = trim((string)$this->request->getPost('reason'));

        if ($attemptRef === '' || mb_strlen($reason) < 3) {
            return $this->response->setJSON(['success' => false, 'message' => 'A reason is required to reject a claim']);
        }

        $db  = \Config\Database::connect();
        $rid = (int)session('restaurant_id');

        $db->transBegin();

        $pay = $db->query(
            "SELECT p.*, o.restaurant_id, o.branch_id AS o_branch
             FROM payments p JOIN orders o ON o.id = p.order_id
             WHERE p.attempt_ref = ?
             FOR UPDATE",
            [$attemptRef]
        )->getRowArray();

        if (!$pay || (int)$pay['restaurant_id'] !== $rid) {
            $this->rollbackQuiet($db);
            return $this->response->setJSON(['success' => false, 'message' => 'Payment attempt not found']);
        }
        if (!in_array($pay['verify_status'], ['pending', 'expired'], true)) {
            $this->rollbackQuiet($db);
            return $this->response->setJSON(['success' => false, 'message' => 'Attempt already resolved (' . $pay['verify_status'] . ')']);
        }

        $db->table('payments')->where('id', $pay['id'])->update([
            'verify_status' => 'rejected',
            'verify_note'   => mb_substr($reason, 0, 255),
            'verified_by'   => (int)session('user_id'),
            'verified_at'   => date('Y-m-d H:i:s'),
        ]);

        $this->audit($db, $pay['id'], $rid, (int)$pay['o_branch'],
            'verification', $pay['verify_status'], 'rejected', (int)session('user_id'), null,
            'Rejected: ' . mb_substr($reason, 0, 200));

        $db->query('COMMIT');

        return $this->response->setJSON(['success' => true, 'message' => 'Claim rejected — the attempt stays on record for reconciliation']);
    }

    private function rollbackQuiet($db): void
    {
        try { $db->transRollback(); } catch (\Throwable $e) { /* nothing open */ }
    }

    private function audit($db, ?int $paymentId, int $restaurantId, ?int $branchId,
                           string $eventType, ?string $prev, ?string $next,
                           ?int $actedBy, ?string $txnRef, string $note): void
    {
        $db->table('payment_verifications')->insert([
            'payment_id'    => $paymentId,
            'restaurant_id' => $restaurantId,
            'branch_id'     => $branchId,
            'event_type'    => in_array($eventType, ['verification', 'claim', 'settings_change', 'note'], true) ? $eventType : 'note',
            'prev_state'    => $prev,
            'new_state'     => $next,
            'acted_by'      => $actedBy,
            'txn_ref'       => $txnRef,
            'note'          => mb_substr($note, 0, 255),
            'created_at'    => date('Y-m-d H:i:s'),
        ]);
    }

    // ══════════════════════════════════════════════════════════
    // Pending-payment list + verification history (staff pages)
    // ══════════════════════════════════════════════════════════

    public function pending()
    {
        $db  = \Config\Database::connect();
        $rid = (int)session('restaurant_id');

        $rows = $db->query(
            "SELECT p.id, p.attempt_ref, p.amount, p.verify_status, p.created_at,
                    p.expires_at, p.claimed_by_customer, p.claimed_at,
                    p.payment_reference, o.id AS order_id, o.order_number,
                    o.total_amount, o.table_id, o.customer_name,
                    (SELECT COALESCE(SUM(CASE WHEN p2.status='completed' THEN p2.amount WHEN p2.status='refunded' THEN -p2.amount ELSE 0 END),0)
                       FROM payments p2 WHERE p2.order_id = o.id) AS collected
             FROM payments p
             JOIN orders o ON o.id = p.order_id
             WHERE o.restaurant_id = ? AND p.payment_gateway = 'upi_qr'
               AND p.verify_status IN ('pending','expired')
             ORDER BY p.created_at ASC",
            [$rid]
        )->getResultArray();

        foreach ($rows as &$r) {
            $r['outstanding'] = (string)max(0, (float)$r['total_amount'] - (float)$r['collected']);
        }

        return view('staff/upi/pending', [
            'pageTitle'    => 'Pending UPI Payments',
            'rows'         => $rows,
            'canVerify'    => $this->canVerify(),
            'userName'     => session('user_name'),
            'userRole'     => session('role_slug'),
            'restaurantName' => session('restaurant_name'),
        ]);
    }

    public function history()
    {
        $db  = \Config\Database::connect();
        $rid = (int)session('restaurant_id');

        $filter = $this->request->getGet('state') ?: '';

        $sql = "SELECT p.id, p.attempt_ref, p.amount, p.verify_status, p.status,
                       p.verify_txn_ref, p.verify_note, p.created_at, p.verified_at,
                       o.order_number, u.name AS verified_by_name
                FROM payments p
                JOIN orders o ON o.id = p.order_id
                LEFT JOIN users u ON u.id = p.verified_by
                WHERE o.restaurant_id = ? AND p.payment_gateway = 'upi_qr'";
        $params = [$rid];
        if (in_array($filter, ['pending', 'paid', 'failed', 'expired', 'rejected'], true)) {
            $sql .= ' AND p.verify_status = ?';
            $params[] = $filter;
        }
        $sql .= ' ORDER BY p.id DESC LIMIT 200';
        $rows = $db->query($sql, $params)->getResultArray();

        $audit = $db->query(
            "SELECT pv.*, u.name AS user_name, p.attempt_ref
             FROM payment_verifications pv
             LEFT JOIN users u ON u.id = pv.acted_by
             LEFT JOIN payments p ON p.id = pv.payment_id
             WHERE pv.restaurant_id = ?
             ORDER BY pv.id DESC LIMIT 100",
            [$rid]
        )->getResultArray();

        return view('staff/upi/history', [
            'pageTitle'  => 'UPI Payment History',
            'rows'       => $rows,
            'audit'      => $audit,
            'filter'     => $filter,
            'userName'   => session('user_name'),
            'userRole'   => session('role_slug'),
            'restaurantName' => session('restaurant_name'),
        ]);
    }
}
