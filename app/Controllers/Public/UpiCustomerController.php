<?php

namespace App\Controllers\Public;

use App\Controllers\BaseController;

/**
 * Customer-facing UPI QR screen — keyed by the payment attempt_ref
 * (unguessable random hex). It exposes ONLY safe display data:
 * restaurant name, order number, payable amount, QR image, status.
 *
 * A customer interaction here can NEVER mark a payment paid: the
 * "I've paid" action only records a claim that staff must verify
 * against the restaurant's real bank/merchant UPI account.
 */
class UpiCustomerController extends BaseController
{
    public function screen(string $attemptRef)
    {
        $pay = $this->loadAttempt($attemptRef);
        if (!$pay) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Payment link not found');
        }

        return view('public/upi/pay_screen', ['pay' => $pay]);
    }

    /** QR image file (PNG) for this attempt */
    public function qrImage(string $attemptRef)
    {
        $pay = $this->loadAttempt($attemptRef);
        if (!$pay) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Payment link not found');
        }

        $path = rtrim(WRITEPATH, '/\\') . '/uploads/upi_qr/qr_' . $attemptRef . '.png';
        if (!is_file($path)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('QR not available');
        }

        return $this->response
            ->setContentType('image/png')
            ->setBody(file_get_contents($path));
    }

    /** Sanitised status for polling from the customer screen */
    public function status(string $attemptRef)
    {
        $pay = $this->loadAttempt($attemptRef);
        if (!$pay) {
            return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Payment link not found']);
        }

        $this->lazyExpire($pay);

        return $this->response->setJSON([
            'success'  => true,
            'state'    => $pay['verify_status'],
            'claimed'  => (bool)$pay['claimed_by_customer'],
            'order'    => $pay['order_number'],
            'restaurant' => $pay['restaurant_name'],
            'amount'   => $pay['amount'],
        ]);
    }

    /** Customer claims to have paid — recorded as a claim ONLY. */
    public function claim(string $attemptRef)
    {
        $pay = $this->loadAttempt($attemptRef);
        if (!$pay) {
            return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Payment link not found']);
        }

        $db = \Config\Database::connect();

        if (!in_array($pay['verify_status'], ['pending', 'expired'], true)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $pay['verify_status'] === 'paid'
                    ? 'This payment is already confirmed. Thank you!'
                    : 'This payment link is no longer active — please ask the restaurant staff.',
            ]);
        }

        $firstClaim = (int)$pay['claimed_by_customer'] === 0;

        $db->table('payments')
            ->where('id', $pay['id'])
            ->where('claimed_by_customer', 0)
            ->update([
                'claimed_by_customer' => 1,
                'claimed_at'          => date('Y-m-d H:i:s'),
            ]);

        // Audit the claim exactly once (repeat taps must not spam history)
        if ($firstClaim) {
            $db->table('payment_verifications')->insert([
                'payment_id'    => $pay['id'],
                'restaurant_id' => (int)$pay['restaurant_id'],
                'branch_id'     => (int)$pay['branch_id'],
                'event_type'    => 'claim',
                'prev_state'    => $pay['verify_status'],
                'new_state'     => $pay['verify_status'],
                'acted_by'      => null,
                'note'          => 'Customer tapped "I have paid" on the QR screen — NOT yet verified',
                'created_at'    => date('Y-m-d H:i:s'),
            ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Recorded. The restaurant staff will verify your payment in their bank app shortly.',
            'claimed' => true,
        ]);
    }

    // ── helpers ───────────────────────────────────────────────

    private function loadAttempt(string $attemptRef): ?array
    {
        if (!preg_match('/^DX[0-9A-Za-z]{6,26}$/', $attemptRef)) {
            return null;
        }

        $db = \Config\Database::connect();
        $row = $db->query(
            "SELECT p.id, p.attempt_ref, p.amount, p.verify_status, p.expires_at,
                    p.claimed_by_customer, p.verified_at, p.verify_txn_ref, p.gateway_response,
                    p.branch_id, o.order_number, o.restaurant_id,
                    r.name AS restaurant_name
             FROM payments p
             JOIN orders o     ON o.id = p.order_id
             JOIN restaurants r ON r.id = o.restaurant_id
             WHERE p.attempt_ref = ? AND p.payment_gateway = 'upi_qr'
             LIMIT 1",
            [$attemptRef]
        )->getRowArray();

        if ($row) {
            $gw = json_decode((string)($row['gateway_response'] ?? ''), true);
            $row['upi_uri'] = is_array($gw) ? ($gw['uri'] ?? '') : '';
        }

        return $row ?: null;
    }

    /** Mirror the staff-side lazy expiry so the customer never sees a stale "pending" */
    private function lazyExpire(array $pay): void
    {
        if ($pay['verify_status'] === 'pending'
            && $pay['expires_at'] && $pay['expires_at'] < date('Y-m-d H:i:s')) {
            $db = \Config\Database::connect();
            $db->table('payments')
                ->where('id', $pay['id'])
                ->where('verify_status', 'pending')
                ->update(['verify_status' => 'expired']);
            $pay['verify_status'] = 'expired';
        }
    }
}
