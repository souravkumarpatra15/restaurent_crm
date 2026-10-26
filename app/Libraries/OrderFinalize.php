<?php

namespace App\Libraries;

/**
 * One finish-line for fully-paid orders, used by BOTH counter checkout
 * (cash/card/wallet) and UPI QR verification, so the outcome is always
 * identical: order completed, invoice issued, feedback QR token, table
 * freed, loyalty earned. Call INSIDE the caller's open DB transaction
 * (checkout uses transStart, UPI verify uses transBegin — both work).
 */
class OrderFinalize
{
    /**
     * Complete a fully-paid order.
     *
     * @param mixed  $db    database connection
     * @param int    $orderId
     * @param array  $money optional payment columns to set in the same
     *                      update, e.g. ['paid_amount'=>250, 'balance_amount'=>0,
     *                      'payment_status'=>'paid']
     * @return array ['invoice_number' => ?string, 'feedback_token' => ?string]
     */
    public static function complete($db, int $orderId, array $money = []): array
    {
        $o = $db->table('orders')->where('id', $orderId)->get()->getRowArray();
        if (!$o) {
            return ['invoice_number' => null, 'feedback_token' => null];
        }

        // 1) Order row: complete + money + feedback token, one update
        $feedbackToken = bin2hex(random_bytes(16));
        $db->table('orders')->where('id', $orderId)->update(array_merge([
            'status'         => 'completed',
            'billed_at'      => date('Y-m-d H:i:s'),
            'completed_at'   => date('Y-m-d H:i:s'),
            'feedback_token' => $feedbackToken,
        ], $money));

        // 2) Invoice — UNIQUE(order_id) protects against double issue
        $invoiceNumber = null;
        $invoiceRow = $db->table('invoices')->select('invoice_number')->where('order_id', $orderId)->get()->getRowArray();
        if ($invoiceRow) {
            $invoiceNumber = $invoiceRow['invoice_number'];
        } else {
            $restaurant = $db->table('restaurants')
                ->select('billing_prefix')
                ->where('id', (int)$o['restaurant_id'])
                ->get()->getRowArray();
            $prefix = ($restaurant['billing_prefix'] ?? '') ?: 'INV';

            // Atomic read-and-increment on the restaurant billing counter
            $db->query('UPDATE restaurants SET billing_counter = LAST_INSERT_ID(billing_counter) + 1 WHERE id = ?', [$o['restaurant_id']]);
            $counter = (int)$db->query('SELECT LAST_INSERT_ID() AS n')->getRowArray()['n'];
            $invoiceNumber = $prefix . '/' . date('Y') . '/' . str_pad((string)$counter, 5, '0', STR_PAD_LEFT);

            $db->table('invoices')->insert([
                'order_id'       => $orderId,
                'restaurant_id'  => $o['restaurant_id'],
                'branch_id'      => $o['branch_id'],
                'invoice_number' => $invoiceNumber,
                'invoice_date'   => date('Y-m-d'),
                'customer_name'  => $o['customer_name'],
                'customer_phone' => $o['customer_phone'],
                'subtotal'       => $o['subtotal'],
                'discount'       => $o['discount_amount'],
                'tax_amount'     => $o['tax_amount'],
                'total_amount'   => $o['total_amount'],
            ]);
        }

        // 3) Free the table — only when no OTHER active order is still
        //    open on it (multi-order-per-table via "add round")
        if ($o['table_id']) {
            $remaining = $db->table('orders')
                ->where('table_id', $o['table_id'])
                ->where('id !=', $orderId)
                ->whereIn('status', ['pending', 'confirmed', 'preparing', 'ready', 'served'])
                ->countAllResults();
            if ($remaining === 0) {
                $db->table('tables')->where('id', $o['table_id'])->update(['status' => 'available']);
            }
        }

        // 4) Loyalty points
        if (!empty($o['customer_id'])) {
            $rule = $db->table('loyalty_rules')
                ->where('restaurant_id', $o['restaurant_id'])
                ->where('is_active', 1)
                ->get()->getRowArray();
            if ($rule) {
                $points = floor((float)$o['total_amount'] / $rule['earn_amount']) * $rule['earn_points_per_amount'];
                if ($points > 0) {
                    $customer = $db->table('customers')->where('id', $o['customer_id'])->get()->getRowArray();
                    $newBalance = $customer['loyalty_points'] + $points;
                    $db->table('customers')->where('id', $o['customer_id'])->update(['loyalty_points' => $newBalance]);
                    $db->table('loyalty_transactions')->insert([
                        'restaurant_id'    => $o['restaurant_id'],
                        'customer_id'      => $o['customer_id'],
                        'order_id'         => $orderId,
                        'transaction_type' => 'earn',
                        'points'           => $points,
                        'balance_after'    => $newBalance,
                    ]);
                }
            }
        }

        return ['invoice_number' => $invoiceNumber, 'feedback_token' => $feedbackToken];
    }
}
