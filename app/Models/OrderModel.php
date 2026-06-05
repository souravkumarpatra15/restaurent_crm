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
        'no_of_guests','waiter_id','subtotal','discount_type','discount_amount',
        'discount_percent','coupon_code','tax_amount','cgst_amount','sgst_amount',
        'igst_amount','cess_amount','service_charge','packing_charge','delivery_charge',
        'round_off','total_amount','paid_amount','balance_amount','payment_status',
        'notes','kitchen_notes','cancelled_reason','cancelled_by','billed_at','completed_at'
    ];

    public function generateOrderNumber($restaurantId, $branchId)
    {
        $branch = $this->db->table('branches')->where('id', $branchId)->get()->getRowArray();
        $prefix = $branch['billing_prefix'] ?: 'ORD';
        $counter = $branch['billing_counter'] ?? 1;
        $this->db->table('branches')->where('id', $branchId)->update(['billing_counter' => $counter + 1]);
        return $prefix . str_pad($counter, 5, '0', STR_PAD_LEFT);
    }

    public function calculateTotals($items, $restaurant, $discountType = null, $discountValue = 0, $serviceChargeApply = false)
    {
        $subtotal = 0;
        $taxTotal = 0;

        foreach ($items as &$item) {
            $lineTotal = $item['unit_price'] * $item['quantity'];
            $lineTax   = 0;

            if ($restaurant['tax_type'] === 'exclusive') {
                $lineTax = $lineTotal * ($item['tax_percent'] / 100);
            } else {
                // inclusive — extract tax
                $lineTax = $lineTotal - ($lineTotal / (1 + $item['tax_percent'] / 100));
            }

            $item['tax_amount']   = round($lineTax, 2);
            $item['total_price']  = round($lineTotal + ($restaurant['tax_type'] === 'exclusive' ? $lineTax : 0), 2);
            $subtotal += $lineTotal;
            $taxTotal += $lineTax;
        }

        $discountAmount = 0;
        if ($discountType === 'percent') {
            $discountAmount = round($subtotal * $discountValue / 100, 2);
        } elseif ($discountType === 'flat') {
            $discountAmount = min($discountValue, $subtotal);
        }

        $serviceCharge = $serviceChargeApply
            ? round(($subtotal - $discountAmount) * ($restaurant['service_charge_percent'] / 100), 2)
            : 0;

        $netTotal    = $subtotal - $discountAmount + $taxTotal + $serviceCharge;
        $roundOff    = round($netTotal) - $netTotal;
        $finalTotal  = round($netTotal);

        // Split CGST/SGST (equal halves for intra-state)
        $cgst = round($taxTotal / 2, 2);
        $sgst = $taxTotal - $cgst;

        return [
            'subtotal'        => round($subtotal, 2),
            'discount_amount' => $discountAmount,
            'tax_amount'      => round($taxTotal, 2),
            'cgst_amount'     => $cgst,
            'sgst_amount'     => $sgst,
            'service_charge'  => $serviceCharge,
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
