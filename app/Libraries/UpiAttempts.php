<?php

namespace App\Libraries;

/**
 * One shared creator for UPI QR payment attempts so POS and the public
 * tracking page behave identically: bcmath money, per-order "one open
 * attempt" hold, explicit status, audit trail. Money NEVER leaves the
 * restaurant's own VPA — this only records an attempt to be verified.
 */
class UpiAttempts
{
    public const QR_LIFETIME_MIN = 20;

    /**
     * Create a pending attempt for an order.
     * $order must be a full orders row (or at least id, restaurant_id,
     * branch_id, order_number, total_amount, payment_status, status).
     * Returns ['ok' => true, 'ref', 'amount', 'uri'] or ['ok' => false, 'message'].
     */
    public static function create($db, array $order, int $userId, string $channel = 'pos'): array
    {
        $rid = (int)$order['restaurant_id'];

        $restaurant = $db->table('restaurants')
            ->select('name, upi_enabled, upi_vpa, upi_payee_name')
            ->where('id', $rid)->get()->getRowArray();

        if (!$restaurant || !(int)$restaurant['upi_enabled'] || empty($restaurant['upi_vpa'])) {
            return ['ok' => false, 'message' => 'UPI QR payments are not enabled for this restaurant'];
        }

        if ($order['payment_status'] === 'paid'
            || in_array($order['status'] ?? '', ['completed', 'cancelled'], true)) {
            return ['ok' => false, 'message' => 'This order is already billed/closed'];
        }

        // Outstanding in bcmath. Completed payments collect; refunded give
        // back; open (pending) attempts hold their spot so two QRs can't
        // over-collect the same order.
        $collected = (string)round((float)$db->query(
            "SELECT COALESCE(SUM(CASE WHEN status = 'completed' THEN amount
                                      WHEN status = 'refunded' THEN -amount
                                      ELSE 0 END), 0) AS t
             FROM payments WHERE order_id = ?",
            [$order['id']]
        )->getRowArray()['t'], 2);

        $outstanding = bcsub((string)round((float)$order['total_amount'], 2), $collected, 2);
        if (bccomp($outstanding, '0.00', 2) < 0) $outstanding = '0.00';

        $pendingTotal = (string)round((float)$db->query(
            "SELECT COALESCE(SUM(amount),0) AS t FROM payments
             WHERE order_id = ? AND verify_status = 'pending'",
            [$order['id']]
        )->getRowArray()['t'], 2);

        $attemptAmount = bcsub($outstanding, $pendingTotal, 2);
        if (bccomp($attemptAmount, '0.00', 2) <= 0) {
            return ['ok' => false, 'message' => 'The outstanding amount is already held by an open QR attempt'];
        }
        if (bccomp($attemptAmount, $outstanding, 2) > 0) {
            $attemptAmount = $outstanding;
        }

        do {
            $attemptRef = 'DX' . date('ymd') . strtoupper(bin2hex(random_bytes(4)));
        } while ((int)$db->table('payments')->where('attempt_ref', $attemptRef)->countAllResults() > 0);

        $upiUri = 'upi://pay'
            . '?pa=' . rawurlencode($restaurant['upi_vpa'])
            . '&pn=' . rawurlencode($restaurant['upi_payee_name'])
            . '&tr=' . rawurlencode($attemptRef)
            . '&tn=' . rawurlencode('Order ' . $order['order_number'])
            . '&am=' . rawurlencode($attemptAmount)
            . '&cu=INR';

        $db->transStart();
        $db->table('payments')->insert([
            'order_id'          => $order['id'],
            'branch_id'         => $order['branch_id'],
            // payments.user_id is NOT NULL + FK. Public attempts (tracking
            // page) are attributed to the staff user on the order itself,
            // with the audit trail recording the real source channel.
            'user_id'           => $userId > 0 ? $userId : ($order['user_id'] ?? 0),
            'amount'            => $attemptAmount,
            'payment_method'    => 'upi',
            'payment_gateway'   => 'upi_qr',
            'payment_reference' => $attemptRef,
            'attempt_ref'       => $attemptRef,
            'verify_status'     => 'pending',
            'status'            => 'pending',
            'gateway_response'  => json_encode([
                'uri'    => $upiUri,
                'vpa'    => $restaurant['upi_vpa'],
                'payee'  => $restaurant['upi_payee_name'],
                'source' => $channel,
            ]),
            'expires_at' => date('Y-m-d H:i:s', time() + self::QR_LIFETIME_MIN * 60),
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        $paymentId = (int)$db->insertID();

        $db->table('payment_verifications')->insert([
            'payment_id'    => $paymentId,
            'restaurant_id' => $rid,
            'branch_id'     => $order['branch_id'],
            'event_type'    => 'verification',
            'prev_state'    => null,
            'new_state'     => 'pending',
            'acted_by'     => $userId,
            'txn_ref'      => $attemptRef,
            'note'         => 'UPI QR generated for ₹' . $attemptAmount
                . ' (' . $order['order_number'] . ') via ' . $channel,
            'created_at'   => date('Y-m-d H:i:s'),
        ]);
        $db->transComplete();

        if ($db->transStatus() === false) {
            return ['ok' => false, 'message' => 'Could not start a payment attempt — please try again'];
        }

        return [
            'ok'         => true,
            'ref'        => $attemptRef,
            'amount'     => $attemptAmount,
            'uri'        => $upiUri,
            'expires_at' => date('Y-m-d H:i:s', time() + self::QR_LIFETIME_MIN * 60),
        ];
    }
}
