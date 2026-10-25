<?php
namespace App\Controllers\Staff;
use App\Controllers\BaseController;

class ShiftController extends BaseController
{
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
            ->groupBy('p.payment_method')
            ->get()->getResultArray();

        // Recent shift history for this user
        $shifts = $db->table('shifts')
            ->where('user_id', $uid)->where('branch_id', $bid)
            ->orderBy('id', 'DESC')->limit(10)
            ->get()->getResultArray();

        return view('staff/shift/summary', [
            'pageTitle' => 'My Shift',
            'summary'   => $summary,
            'payments'  => $payments,
            'shifts'    => $shifts,
            'openShift' => $openShift,
            'date'      => $today,
            'userName'  => session('user_name'),
            'userRole'  => session('role_slug'),
        ]);
    }

    // ── Open a cash drawer shift ─────────────────────────────
    public function open()
    {
        $db  = \Config\Database::connect();
        $uid = session('user_id');
        $bid = session('branch_id');

        $opening = (float)$this->request->getPost('opening_cash');
        if ($opening < 0 || !$this->request->getPost('opening_cash')) {
            $opening = 0;
        }

        // One open shift per user per branch
        $existing = $db->table('shifts')->where('user_id', $uid)->where('branch_id', $bid)->where('status', 'open')->countAllResults();
        if ($existing) {
            return $this->response->setJSON(['success' => false, 'message' => 'You already have an open shift']);
        }

        $db->table('shifts')->insert([
            'restaurant_id' => session('restaurant_id'),
            'branch_id'     => $bid,
            'user_id'       => $uid,
            'opening_cash'  => $opening,
            'status'        => 'open',
            'opened_at'     => date('Y-m-d H:i:s'),
            'created_at'    => date('Y-m-d H:i:s'),
        ]);

        return $this->response->setJSON(['success' => true, 'message' => 'Shift opened']);
    }

    // ── Close shift: cash reconciliation ─────────────────────
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
            return $this->response->setJSON(['success' => false, 'message' => 'You have no open shift']);
        }

        $counted = (float)$this->request->getPost('counted_cash');

        // Cash taken between shift open and now
        $cashIn  = (float)($db->table('payments p')
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

        $expected   = (float)$shift['opening_cash'] + $cashIn;
        $difference = $counted - $expected;

        $db->table('shifts')->where('id', $shift['id'])->update([
            'cash_sales'   => $cashIn,
            'total_sales'  => $sales,
            'expected_cash'=> $expected,
            'counted_cash' => $counted,
            'difference'   => $difference,
            'orders_count' => $ordersCount,
            'notes'        => $this->request->getPost('notes'),
            'status'       => 'closed',
            'closed_at'    => date('Y-m-d H:i:s'),
            'updated_at'   => date('Y-m-d H:i:s'),
        ]);

        return $this->response->setJSON([
            'success'  => true,
            'message'  => 'Shift closed',
            'expected' => $expected,
            'counted'  => $counted,
            'difference' => $difference,
            'status'   => abs($difference) < 10 ? 'balanced' : ($difference > 0 ? 'surplus' : 'shortage'),
        ]);
    }
}
