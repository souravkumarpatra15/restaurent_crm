<?php
namespace App\Models;

class OrderModel extends BaseModel
{
    protected $table      = 'orders';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = true;

    protected $allowedFields = [
        'restaurant_id','branch_id','table_id','customer_id','user_id',
        'order_number','order_type','status','customer_name','customer_phone',
        'no_of_guests','waiter_id','rider_id','rider_name','delivery_address','delivery_status','delivered_at',
        'subtotal','discount_type','discount_amount',
        'discount_percent','coupon_code','tax_amount','cgst_amount','sgst_amount',
        'igst_amount','cess_amount','service_charge','packing_charge','delivery_charge',
        'round_off','total_amount','paid_amount','balance_amount','payment_status',
        'notes','kitchen_notes','cancelled_reason','cancelled_by','billed_at','completed_at'
    ];

    public function generateOrderNumber($restaurantId, $branchId)
    {
        $branch = $this->db->table('branches')->where('id', $branchId)->get()->getRowArray();
        $prefix = $branch['billing_prefix'] ?? 'ORD';

        // Atomic read-and-increment (LAST_INSERT_ID trick) — two simultaneous
        // orders can no longer receive the same number
        $this->db->query('UPDATE branches SET billing_counter = LAST_INSERT_ID(billing_counter) + 1 WHERE id = ?', [$branchId]);
        $counter = (int)$this->db->query('SELECT LAST_INSERT_ID() AS n')->getRowArray()['n'];

        return $prefix . str_pad($counter, 5, '0', STR_PAD_LEFT);
    }

    public function calculateTotals($items, $restaurant, $discountType = null, $discountValue = 0, $serviceChargeApply = false, $couponCode = null, $packingCharge = 0)
    {
        $subtotal  = 0;
        $taxTotal  = 0;
        $cessTotal = 0;

        foreach ($items as &$item) {
            $lineTotal = $item['unit_price'] * $item['quantity'];
            $lineTax   = 0;
            $lineCess  = $lineTotal * (float)($item['cess_percent'] ?? 0) / 100;

            if ($restaurant['tax_type'] === 'exclusive') {
                $lineTax = $lineTotal * ($item['tax_percent'] / 100);
            } else {
                // inclusive — extract tax
                $lineTax = $lineTotal - ($lineTotal / (1 + $item['tax_percent'] / 100));
            }

            $item['tax_amount']   = round($lineTax, 2);
            $item['total_price']  = round($lineTotal + ($restaurant['tax_type'] === 'exclusive' ? $lineTax : 0) + $lineCess, 2);
            $subtotal += $lineTotal;
            $taxTotal += $lineTax;
            $cessTotal += $lineCess;
        }

        // 'coupon' discounts arrive as a flat amount pre-computed by
        // applyCoupon() — it used to fall through BOTH branches and the
        // customer was silently charged full price
        if (!in_array($discountType, ['percent', 'flat', 'coupon', 'loyalty'], true)) {
            $discountType = 'flat';
        }

        $discountAmount = 0;
        if ($discountType === 'percent') {
            $discountAmount = round($subtotal * $discountValue / 100, 2);
        } elseif ($discountType === 'flat' || $discountType === 'coupon') {
            $discountAmount = min($discountValue, $subtotal);
        } elseif ($discountType === 'loyalty') {
            // $discountValue carries the POINTS the customer wants to redeem.
            // Real rules are looked up so the value can never be forged client-side.
            $rule = $this->db->table('loyalty_rules')
                ->where('restaurant_id', $restaurant['id'])
                ->where('is_active', 1)
                ->orderBy('id','ASC')
                ->get()->getRowArray();
            $points = (float)$discountValue;
            if ($rule && $points >= (float)$rule['min_redeem_points'] && $points > 0) {
                $loyaltyDiscount = $points * (float)$rule['redeem_value'];
                $cap             = round($subtotal * (float)$rule['max_redeem_percent'] / 100, 2);
                $discountAmount  = max(0, min($loyaltyDiscount, $cap, $subtotal));
            }
        }

        $serviceCharge = $serviceChargeApply
            ? round(($subtotal - $discountAmount) * ($restaurant['service_charge_percent'] / 100), 2)
            : 0;

        // Inclusive tax is ALREADY inside the subtotal — adding $taxTotal
        // again overcharged every inclusive-tax restaurant
        $netTotal    = $subtotal - $discountAmount + ($restaurant['tax_type'] === 'exclusive' ? $taxTotal : 0)
                     + $cessTotal + $serviceCharge + (float)$packingCharge;
        $roundOff    = round($netTotal) - $netTotal;
        $finalTotal  = round($netTotal);

        // GST split — intra-state: equal CGST+SGST halves; inter-state: single IGST
        $taxMode = ($restaurant['tax_mode'] ?? 'intra') === 'inter';
        $igst    = $taxMode ? round($taxTotal, 2) : 0;
        $cgst    = $taxMode ? 0 : round($taxTotal / 2, 2);
        $sgst    = $taxMode ? 0 : round($taxTotal, 2) - $cgst;

        return [
            'subtotal'        => round($subtotal, 2),
            'discount_amount' => $discountAmount,
            'coupon_code'     => ($discountType === 'coupon' && $discountAmount > 0) ? strtoupper(trim((string)$couponCode)) : null,
            'tax_amount'      => round($taxTotal, 2),
            'igst_amount'     => $igst,
            'cgst_amount'     => $cgst,
            'sgst_amount'     => $sgst,
            'cess_amount'     => round($cessTotal, 2),
            'service_charge'  => $serviceCharge,
            'packing_charge'  => round((float)$packingCharge, 2),
            'round_off'       => round($roundOff, 2),
            'total_amount'    => $finalTotal,
            'items'           => $items,
        ];
    }

    public function getOrderWithDetails($orderId)
    {
        $order = $this->find($orderId);
        if (!$order) return null;

        $order['items'] = $this->db->table('order_items oi')
            ->select('oi.*, mi.image, mi.item_type')
            ->join('menu_items mi', 'mi.id = oi.menu_item_id', 'left')
            ->where('oi.order_id', $orderId)
            ->get()->getResultArray();

        foreach ($order['items'] as &$item) {
            $item['addons'] = $this->db->table('order_item_addons')
                ->where('order_item_id', $item['id'])
                ->get()->getResultArray();
        }

        $order['payments'] = $this->db->table('payments')
            ->where('order_id', $orderId)
            ->get()->getResultArray();

        $order['table'] = $order['table_id']
            ? $this->db->table('tables')->where('id', $order['table_id'])->get()->getRowArray()
            : null;

        $order['customer'] = $order['customer_id']
            ? $this->db->table('customers')->where('id', $order['customer_id'])->get()->getRowArray()
            : null;

        return $order;
    }

    public function getDailySummary($branchId, $date = null)
    {
        $date = $date ?: date('Y-m-d');
        $builder = $this->db->table('orders')
            ->where('branch_id', $branchId)
            ->where('DATE(created_at)', $date)
            ->where('status !=', 'cancelled');

        $result = $builder->select('
            COUNT(*) as total_orders,
            SUM(total_amount) as total_revenue,
            SUM(discount_amount) as total_discount,
            SUM(tax_amount) as total_tax,
            AVG(total_amount) as avg_order_value
        ')->get()->getRowArray();

        $byType = $this->db->table('orders')
            ->select('order_type, COUNT(*) as count, SUM(total_amount) as revenue')
            ->where('branch_id', $branchId)
            ->where('DATE(created_at)', $date)
            ->where('status !=', 'cancelled')
            ->groupBy('order_type')
            ->get()->getResultArray();

        $byPayment = $this->db->table('payments p')
            ->select('p.payment_method, COUNT(*) as count, SUM(p.amount) as total')
            ->join('orders o', 'o.id = p.order_id')
            ->where('o.branch_id', $branchId)
            ->where('DATE(p.created_at)', $date)
            ->groupBy('p.payment_method')
            ->get()->getResultArray();

        return compact('result', 'byType', 'byPayment');
    }

    public function getTopItems($restaurantId, $branchId = null, $limit = 10, $from = null, $to = null)
    {
        $builder = $this->db->table('order_items oi')
            ->select('oi.menu_item_id, oi.name, SUM(oi.quantity) as total_qty, SUM(oi.total_price) as total_revenue, mi.image, mi.item_type')
            ->join('orders o', 'o.id = oi.order_id')
            ->join('menu_items mi', 'mi.id = oi.menu_item_id', 'left')
            ->where('o.restaurant_id', $restaurantId)
            ->where('o.status !=', 'cancelled')
            ->groupBy('oi.menu_item_id')
            ->orderBy('total_qty', 'DESC')
            ->limit($limit);
        if ($branchId) $builder->where('o.branch_id', $branchId);
        if ($from)     $builder->where('DATE(o.created_at) >=', $from);
        if ($to)       $builder->where('DATE(o.created_at) <=', $to);
        return $builder->get()->getResultArray();
    }
}
