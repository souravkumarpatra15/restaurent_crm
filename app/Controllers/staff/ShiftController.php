<?php
namespace App\Controllers\Staff;
use App\Controllers\BaseController;

class ShiftController extends BaseController
{
    // Indian denominations, high → low (notes + coins)
    private const DENOMS = [2000, 500, 200, 100, 50, 20, 10, 5, 2, 1];

    /**
     * Build a clean denominations array from posted counts.
     * Accepts counts[] parallel to denoms[] or a JSON string.
     */
    private function parseDenoms(): array
    {
        $json = $this->request->getPost('denominations');
        $out  = [];
        if ($json) {
            $decoded = json_decode($json, true);
            if (is_array($decoded)) {
                foreach ($decoded as $denom => $count) {
                    if (in_array((int)$denom, self::DENOMS, true) && (int)$count > 0) {
                        $out[(int)$denom] = (int)$count;
                    }
                }
            }
        }
        return array_replace(array_fill_keys(self::DENOMS, 0), $out);
    }

    private function denomsTotal(array $denoms): float
    {
        $t = 0;
        foreach ($denoms as $d => $c) { $t += ((int)$d) * ((int)$c); }
        return round($t, 2);
    }

    public function summary()
    {
        $db  = \Config\Database::connect();
        $uid = session('user_id');
        $bid = session('branch_id');
        $today = date('Y-m-d');

        $openShift = $db->table('shifts')
            ->where('user_id', $uid)->where('branch_id', $bid)
            ->where('status', 'open')->orderBy('id', 'DESC')
            ->get()->getRowArray();

        $summary = $db->table('orders')
            ->select('COUNT(*) as total_orders, SUM(total_amount) as total_revenue, SUM(discount_amount) as total_discount')
            ->where('branch_id', $bid)
            ->where('user_id', $uid)
            ->where('DATE(created_at)', $today)
            ->where('payment_status','paid')
            ->get()->getRowArray();

        $payments = $db->table('payments p')
            ->select('p.payment_method, COUNT(*) as count, SUM(p.amount) as total')
            ->join('orders o','o.id = p.order_id')
            ->where('o.branch_id', $bid)
            ->where('o.user_id', $uid)
            ->where('DATE(p.created_at)', $today)
            ->where('p.amount >=', 0)
            ->groupBy('p.payment_method')
            ->get()->getResultArray();

        $recent = $db->table('shifts')
            ->where('user_id', $uid)->where('branch_id', $bid)
            ->orderBy('id', 'DESC')->limit(10)
            ->get()->getResultArray();

        // Drawer movements from this open shift
        $handovers = [];
        $handoversOut = 0.0;
        if ($openShift) {
            $handovers = $db->table('cash_handovers')
                ->where('shift_id', $openShift['id'])
                ->orderBy('id', 'DESC')
                ->get()->getResultArray();
            foreach ($handovers as $h) {
                if ($h['status'] !== 'cancelled') $handoversOut += (float)$h['amount'];
            }
        }

        // Expected drawer right now: opening + cash sales − handed out
        $cashInShift = 0.0;
        if ($openShift) {
            $cashInShift = (float)($db->table('payments p')
                ->join('orders o','o.id = p.order_id')
                ->selectSum('p.amount')
                ->where('p.payment_method','cash')->where('p.amount >=', 0)
                ->where('o.user_id', $uid)->where('o.branch_id', $bid)
                ->where('p.created_at >=', $openShift['opened_at'])
                ->get()->getRowArray()['amount'] ?? 0);
        }
        $drawerExpected = ($openShift ? (float)$openShift['opening_cash'] : 0) + $cashInShift - $handoversOut;

        return view('staff/shift/summary', [
            'pageTitle'      => 'Cash Counter',
            'summary'        => $summary,
            'payments'       => $payments,
            'shifts'         => $recent,
            'openShift'      => $openShift,
            'handovers'      => $handovers,
            'handoversOut'   => $handoversOut,
            'cashInShift'    => $cashInShift,
            'drawerExpected' => $drawerExpected,
            'denoms'         => self::DENOMS,
            'date'           => $today,
            'userName'       => session('user_name'),
            'userRole'       => session('role_slug'),
        ]);
    }

    // ── Open a cash drawer shift (denomination counting) ─────
    public function open()
    {
        $db  = \Config\Database::connect();
        $uid = session('user_id');
        $bid = session('branch_id');

        $denoms = $this->parseDenoms();
        $opening = $this->denomsTotal($denoms);
        // Allow declaring an opening float without denomination detail
        if ($opening <= 0 && $this->request->getPost('opening_cash') !== null) {
            $opening = max(0, (float)$this->request->getPost('opening_cash'));
            $denoms  = [];
        }

        // One open shift per user per branch
        $existing = $db->table('shifts')->where('user_id', $uid)->where('branch_id', $bid)->where('status', 'open')->countAllResults();
        if ($existing) {
            return $this->response->setJSON(['success' => false, 'message' => 'You already have an open shift']);
        }

        $db->table('shifts')->insert([
            'restaurant_id'         => session('restaurant_id'),
            'branch_id'             => $bid,
            'user_id'               => $uid,
            'opening_cash'          => $opening,
            'opening_denominations' => $denoms ? json_encode($denoms) : null,
            'status'                => 'open',
            'opened_at'             => date('Y-m-d H:i:s'),
            'created_at'            => date('Y-m-d H:i:s'),
        ]);

        return $this->response->setJSON(['success' => true, 'message' => 'Counter opened', 'opening' => $opening]);
    }

    // ── Slip number: HV/<branchCode>/<yyMM>/<seq> ─────────────
    private function nextSlipNumber($db, $branchId): string
    {
        $branch = $db->table('branches')->where('id', $branchId)->get()->getRowArray();
        $code   = $branch['code'] ?: 'B' . $branchId;
        $db->table('cash_handovers'); // warm connection
        $count  = (int)$db->table('cash_handovers')->where('branch_id', $branchId)->countAllResults();
        return 'HV/' . $code . '/' . date('ym') . '/' . str_pad($count + 1, 4, '0', STR_PAD_LEFT);
    }

    // ── Handover / bank deposit / safe drop ──────────────────
    public function handover()
    {
        $db  = \Config\Database::connect();
        $uid = session('user_id');
        $bid = session('branch_id');

        $shift = $db->table('shifts')
            ->where('user_id', $uid)->where('branch_id', $bid)
            ->where('status', 'open')->orderBy('id', 'DESC')
            ->get()->getRowArray();
        if (!$shift) {
            return $this->response->setJSON(['success' => false, 'message' => 'Open a counter first']);
        }

        $type = $this->request->getPost('movement_type');
        if (!in_array($type, ['handover', 'bank_deposit', 'safe_drop'], true)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid movement type']);
        }

        $denoms = $this->parseDenoms();
        $amount = $this->denomsTotal($denoms);
        if ($amount <= 0) {
            // allow amount-only entry
            $amount = (float)$this->request->getPost('amount');
            if ($amount <= 0) {
                return $this->response->setJSON(['success' => false, 'message' => 'Enter the cash being handed over']);
            }
        }

        // Cannot hand over more cash than the drawer should contain
        $cashInShift = (float)($db->table('payments p')
            ->join('orders o','o.id = p.order_id')
            ->selectSum('p.amount')
            ->where('p.payment_method','cash')->where('p.amount >=', 0)
            ->where('o.user_id', $uid)->where('o.branch_id', $bid)
            ->where('p.created_at >=', $shift['opened_at'])
            ->get()->getRowArray()['amount'] ?? 0);
        $alreadyOut = (float)($db->table('cash_handovers')
            ->selectSum('amount')->where('shift_id', $shift['id'])->whereIn('status', ['pending','handed','deposited'])
            ->get()->getRowArray()['amount'] ?? 0);
        $available = (float)$shift['opening_cash'] + $cashInShift - $alreadyOut;

        if ($amount > $available + 0.01) {
            return $this->response->setJSON(['success' => false, 'message' =>
                'Drawer only has ' . number_format($available, 2) . ' available for handover']);
        }

        $slipNo = $this->nextSlipNumber($db, $bid);
        $db->table('cash_handovers')->insert([
            'restaurant_id' => session('restaurant_id'),
            'branch_id'     => $bid,
            'shift_id'      => $shift['id'],
            'user_id'       => $uid,
            'movement_type' => $type,
            'amount'        => $amount,
            'denominations' => $denoms ? json_encode($denoms) : null,
            'slip_number'   => $slipNo,
            'status'        => 'pending',
            'notes'         => $this->request->getPost('notes'),
            'created_at'    => date('Y-m-d H:i:s'),
        ]);
        $handoverId = (int)$db->insertID();

        return $this->response->setJSON([
            'success'     => true,
            'message'     => 'Slip generated — print it and hand over the cash',
            'handover_id' => $handoverId,
            'slip_number' => $slipNo,
            'slip_url'    => base_url('pos/shift/handover-slip/' . $handoverId),
        ]);
    }

    // ── Receiver confirms: handed / deposited / cancel ────────
    public function handoverReceive($id)
    {
        $db  = \Config\Database::connect();
        $bid = session('branch_id');

        $h = $db->table('cash_handovers')->where('id', $id)->where('branch_id', $bid)->get()->getRowArray();
        if (!$h)           return $this->response->setJSON(['success' => false, 'message' => 'Slip not found']);
        if ($h['status'] !== 'pending') return $this->response->setJSON(['success' => false, 'message' => 'This slip is already ' . $h['status']]);

        $action = $this->request->getPost('action');
        $valid  = ['handover' => 'handed', 'bank_deposit' => 'deposited', 'safe_drop' => 'handed'];
        if (!isset($valid[$action])) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid action']);
        }

        $db->table('cash_handovers')->where('id', $id)->update([
            'status'      => $valid[$action],
            'received_by' => $this->request->getPost('received_by') ?: (
                $action === 'bank_deposit' ? 'Bank' : 'Manager'),
            'reference_no' => $this->request->getPost('reference_no') ?: null,
            'handed_at'   => date('Y-m-d H:i:s'),
        ]);
        return $this->response->setJSON(['success' => true, 'message' => 'Marked as ' . $valid[$action]]);
    }

    // ── Printable slip (80mm style, browser print) ────────────
    public function handoverSlip($id)
    {
        $db  = \Config\Database::connect();
        $h = $db->table('cash_handovers h')
            ->select('h.*, u.name as staff_name, b.name as branch_name, r.name as restaurant_name, r.currency_symbol')
            ->join('users u', 'u.id = h.user_id', 'left')
            ->join('branches b', 'b.id = h.branch_id', 'left')
            ->join('restaurants r', 'r.id = h.restaurant_id', 'left')
            ->where('h.id', $id)
            ->where('h.branch_id', session('branch_id'))
            ->get()->getRowArray();
        if (!$h) return redirect()->to(base_url('pos'));

        $denoms = $h['denominations'] ? json_decode($h['denominations'], true) : [];
        arsort($denoms);

        return view('staff/shift/cash_slip', [
            'h'      => $h,
            'denoms' => $denoms,
        ]);
    }

    // ── Close shift: closure vs denomination recount ──────────
    public function close()
    {
        $db  = \Config\Database::connect();
        $uid = session('user_id');
        $bid = session('branch_id');

        $shift = $db->table('shifts')
            ->where('user_id', $uid)->where('branch_id', $bid)
            ->where('status', 'open')->orderBy('id', 'DESC')
            ->get()->getRowArray();
        if (!$shift) {
            return $this->response->setJSON(['success' => false, 'message' => 'You have no open counter']);
        }

        $denoms  = $this->parseDenoms();
        $counted = $this->denomsTotal($denoms);
        if ($counted <= 0) {
            $counted = (float)$this->request->getPost('counted_cash');
        }

        // Cash taken between shift open and now
        $cashIn = (float)($db->table('payments p')
            ->join('orders o','o.id = p.order_id')
            ->selectSum('p.amount')
            ->where('p.payment_method','cash')->where('p.amount >=', 0)
            ->where('o.user_id', $uid)->where('o.branch_id', $bid)
            ->where('p.created_at >=', $shift['opened_at'])
            ->get()->getRowArray()['amount'] ?? 0);

        $sales = (float)($db->table('orders')
            ->selectSum('total_amount')
            ->where('user_id', $uid)->where('branch_id', $bid)
            ->where('created_at >=', $shift['opened_at'])
            ->where('payment_status', 'paid')
            ->where('status !=', 'cancelled')
            ->get()->getRowArray()['amount'] ?? 0);
        $ordersCount = $db->table('orders')
            ->where('user_id', $uid)->where('branch_id', $bid)
            ->where('created_at >=', $shift['opened_at'])
            ->where('payment_status', 'paid')
            ->where('status !=', 'cancelled')
            ->countAllResults();

        // Handovers already removed from the drawer
        $alreadyOut = (float)($db->table('cash_handovers')
            ->selectSum('amount')->where('shift_id', $shift['id'])->whereIn('status', ['pending','handed','deposited'])
            ->get()->getRowArray()['amount'] ?? 0);

        // Expected drawer = opening + cash sales − handed out
        $expected   = (float)$shift['opening_cash'] + $cashIn - $alreadyOut;
        $difference = $counted - $expected;

        $db->table('shifts')->where('id', $shift['id'])->update([
            'cash_sales'           => $cashIn,
            'total_sales'          => $sales,
            'expected_cash'        => $expected,
            'counted_cash'         => $counted,
            'difference'           => $difference,
            'closing_denominations'=> $denoms ? json_encode($denoms) : null,
            'orders_count'         => $ordersCount,
            'notes'                => $this->request->getPost('notes'),
            'status'               => 'closed',
            'closed_at'            => date('Y-m-d H:i:s'),
            'updated_at'           => date('Y-m-d H:i:s'),
        ]);

        return $this->response->setJSON([
            'success'    => true,
            'message'    => 'Counter closed',
            'opening'    => (float)$shift['opening_cash'],
            'cash_sales' => $cashIn,
            'handed_out' => $alreadyOut,
            'expected'   => $expected,
            'counted'    => $counted,
            'difference' => $difference,
            'status'     => abs($difference) < 10 ? 'balanced' : ($difference > 0 ? 'surplus' : 'shortage'),
            'z_slip_url' => base_url('pos/shift/z-slip/' . $shift['id']),
        ]);
    }

    // ── Z-Report (end-of-shift summary slip, printable) ───────
    public function zSlip($shiftId)
    {
        $db   = \Config\Database::connect();
        $bid  = session('branch_id');
        $rid  = session('restaurant_id');

        $shift = $db->table('shifts s')
            ->select('s.*, u.name as staff_name, b.name as branch_name')
            ->join('users u', 'u.id = s.user_id', 'left')
            ->join('branches b', 'b.id = s.branch_id', 'left')
            ->where('s.id', $shiftId)
            ->where('s.branch_id', $bid)
            ->get()->getRowArray();
        if (!$shift) return redirect()->to(base_url('pos/shift/summary'));

        $to   = $shift['closed_at'] ?? date('Y-m-d H:i:s');

        // Payment split for this cashier's period
        $methods = $db->table('payments p')
            ->select("p.payment_method, COUNT(*) as count, SUM(p.amount) as total")
            ->join('orders o', 'o.id = p.order_id')
            ->where('o.branch_id', $bid)
            ->where('o.user_id', $shift['user_id'])
            ->where('p.created_at >=', $shift['opened_at'])
            ->where('p.created_at <=', $to)
            ->whereNotIn('o.status', ['cancelled', 'refunded'])
            ->groupBy('p.payment_method')->orderBy('total', 'DESC')
            ->get()->getResultArray();
        $methodTotal = array_sum(array_column($methods, 'total'));

        // Sales stats for the period
        $stats = $db->table('orders')
            ->select('COUNT(*) as bills, SUM(total_amount) as sales, SUM(discount_amount) as discounts,
                      SUM(service_charge) as service, SUM(tax_amount) as tax')
            ->where('user_id', $shift['user_id'])->where('branch_id', $bid)
            ->where('created_at >=', $shift['opened_at'])->where('created_at <=', $to)
            ->where('status !=', 'cancelled')
            ->get()->getRowArray();
        $cancels = $db->table('orders')
            ->where('user_id', $shift['user_id'])->where('branch_id', $bid)
            ->where('created_at >=', $shift['opened_at'])->where('created_at <=', $to)
            ->where('status', 'cancelled')->countAllResults();

        $restaurant = $db->table('restaurants')->where('id', $rid)->get()->getRowArray();

        return view('staff/shift/z_slip', [
            'shift'      => $shift,
            'methods'    => $methods,
            'methodTotal'=> $methodTotal,
            'stats'      => $stats,
            'cancels'    => $cancels,
            'restaurant' => $restaurant ?? [],
        ]);
    }
}
