<?php

namespace App\Controllers\Manager;

use App\Controllers\BaseController;

/**
 * Rider onboarding & driver wallet (commission + COD tracking).
 *
 * Riders are delivery-role staff with a rider profile: vehicle and a
 * commission rule (per-order flat or % of order). Their money ledger
 * carries two independent streams:
 *   - COD the rider is holding (collect +, deposit −)
 *   - Commission payable (delivered +, payout −)
 * Everything is an append-only ledger row — the view just sums it.
 */
class RiderController extends BaseController
{
    private function canManage(): bool
    {
        return in_array(session('role_slug'), ['restaurant_admin', 'branch_manager'], true);
    }

    // ── Riders list ──────────────────────────────────────────
    public function index()
    {
        if (!$this->canManage()) return redirect()->to(base_url('admin/dashboard'))->with('error', 'Not permitted');

        $rid = session('restaurant_id');
        $db  = \Config\Database::connect();

        $riders = $db->table('riders r')
            ->select("u.id, u.name, u.email, u.phone, u.is_active AS user_active,
                      r.id AS profile_id, r.vehicle_no, r.commission_type, r.commission_value, r.employer_type,
                      COALESCE(w.cod_in_hand, 0) AS cod_in_hand,
                      COALESCE(w.payable, 0) AS payable,
                      (SELECT COUNT(*) FROM orders o2
                        WHERE o2.rider_id = u.id AND o2.delivery_status = 'delivered') AS delivered_count")
            ->join('users u', 'u.id = r.user_id')
            ->join("(
                SELECT rider_user_id,
                       SUM(cod_delta)  AS cod_in_hand,
                       SUM(earn_delta) AS payable
                FROM rider_wallet_txns GROUP BY rider_user_id
            ) w", 'w.rider_user_id = u.id', 'left')
            ->where('r.restaurant_id', $rid)
            ->where('u.restaurant_id', $rid)
            ->where('r.is_active', 1)
            ->where('u.is_active', 1)
            ->orderBy('u.name', 'ASC')
            ->get()->getResultArray();

        // self-signed-up drivers waiting for approval (rider-apply link).
        // A PAUSEd rider also has is_active=0, so only count someone pending
        // who never delivered a single order and never touched the wallet.
        $pending = $db->table('riders r')
            ->select('u.id, u.name, u.email, u.phone, u.created_at, r.vehicle_no, r.employer_type, r.branch_id, b.name AS branch_name')
            ->join('users u', 'u.id = r.user_id')
            ->join('branches b', 'b.id = r.branch_id', 'left')
            ->where('r.restaurant_id', $rid)
            ->where('u.restaurant_id', $rid)
            ->where('u.is_active', 0)
            ->where('r.is_active', 0)
            ->where('NOT EXISTS (SELECT 1 FROM orders o2 WHERE o2.rider_id = u.id AND o2.delivery_status = \'delivered\')', null, false)
            ->where('NOT EXISTS (SELECT 1 FROM rider_wallet_txns w2 WHERE w2.rider_user_id = u.id)', null, false)
            ->orderBy('u.created_at', 'ASC')
            ->get()->getResultArray();

        $branches = $db->table('branches')->where('restaurant_id', $rid)->get()->getResultArray();

        return view('admin/riders/index', [
            'pageTitle'   => 'Delivery Riders',
            'riders'      => $riders,
            'pending'     => $pending,
            'branches'    => $branches,
            'userName'    => session('user_name'), 'userRole' => session('role_slug'),
            'restaurantName' => session('restaurant_name'),
            'restaurantId'   => $rid,
        ]);
    }

    // ── Onboard a rider (delivery staff user + profile) ──────
    public function store()
    {
        if (!$this->canManage()) return redirect()->to(base_url('admin/dashboard'))->with('error', 'Not permitted');

        $db  = \Config\Database::connect();
        $rid = session('restaurant_id');

        $name    = trim((string)$this->request->getPost('name'));
        $email   = strtolower(trim((string)$this->request->getPost('email')));
        $phone   = trim((string)$this->request->getPost('phone'));
        $pass    = (string)$this->request->getPost('password');
        $bid     = (int)$this->request->getPost('branch_id') ?: null;
        $vehicle = strtoupper(trim((string)$this->request->getPost('vehicle_no')));
        $ctype   = $this->request->getPost('commission_type') === 'percent' ? 'percent' : 'per_order';
        $cvalue  = (float)$this->request->getPost('commission_value');
        $employer = $this->request->getPost('employer_type') === 'dinovix' ? 'dinovix' : 'restaurant';

        if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($pass) < 4) {
            return redirect()->back()->withInput()->with('error', 'Name, a valid email and a password (min 4 chars) are required.');
        }
        if ($ctype === 'percent' && ($cvalue < 0 || $cvalue > 100)) {
            return redirect()->back()->withInput()->with('error', 'Percent commission must be 0-100.');
        }

        $role   = $db->table('roles')->where('slug', 'delivery')->get()->getRowArray();
        $exists = $db->table('users u')
            ->join('roles r2', 'r2.id = u.role_id')
            ->where('u.email', $email)->where('u.restaurant_id', $rid)
            ->where('r2.slug', 'delivery')->countAllResults();
        if ($exists) {
            return redirect()->back()->withInput()->with('error', 'That email is already a rider.');
        }
        if (!$role) {
            return redirect()->back()->with('error', 'Delivery role missing — contact support.');
        }

        $db->transStart();
        $db->table('users')->insert([
            'restaurant_id' => $rid,
            'branch_id'     => $bid,
            'role_id'       => $role['id'],
            'name'          => $name,
            'email'         => $email,
            'phone'         => $phone ?: null,
            'password'      => password_hash($pass, PASSWORD_BCRYPT),
            'employee_code' => 'RDR-' . strtoupper(bin2hex(random_bytes(2))),
            'is_active'     => 1,
            'created_at'    => date('Y-m-d H:i:s'),
            'updated_at'    => date('Y-m-d H:i:s'),
        ]);
        $uid = (int)$db->insertID();
        $db->table('riders')->insert([
            'user_id'          => $uid,
            'restaurant_id'    => $rid,
            'branch_id'        => $bid,
            'employer_type'    => $employer,
            'vehicle_no'       => $vehicle ?: null,
            'commission_type'  => $ctype,
            'commission_value' => $cvalue,
            'is_active'        => 1,
            'created_at'       => date('Y-m-d H:i:s'),
            'updated_at'       => date('Y-m-d H:i:s'),
        ]);
        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->with('error', 'Could not create the rider — the email may already exist.');
        }

        return redirect()->to(base_url('admin/riders'))->with('success', $name . ' onboarded — login: ' . $email);
    }

    // ── Activate / pause a rider ─────────────────────────────
    public function toggle($userId)
    {
        if (!$this->canManage()) return redirect()->to(base_url('admin/dashboard'))->with('error', 'Not permitted');

        $db  = \Config\Database::connect();
        $rid = session('restaurant_id');

        $rider = $db->table('riders')->where('user_id', (int)$userId)->where('restaurant_id', $rid)->get()->getRowArray();
        if (!$rider) return redirect()->to(base_url('admin/riders'))->with('error', 'Rider not found');

        $new = (int)!$rider['is_active'];
        $db->table('riders')->where('id', $rider['id'])->update(['is_active' => $new, 'updated_at' => date('Y-m-d H:i:s')]);
        $db->table('users')->where('id', (int)$userId)->update(['is_active' => $new]);

        return redirect()->to(base_url('admin/riders'))->with('success', $new ? 'Rider activated' : 'Rider paused');
    }

    // ── Approve a SELF-SIGNED-UP driver (public rider-apply) ──
    // The driver chose their own password on the signup form; approving
    // switches both rows active so they can finally log in.
    public function approve($userId)
    {
        if (!$this->canManage()) return redirect()->to(base_url('admin/dashboard'))->with('error', 'Not permitted');

        $db  = \Config\Database::connect();
        $rid = session('restaurant_id');

        $rider = $db->table('riders')->where('user_id', (int)$userId)->where('restaurant_id', $rid)->get()->getRowArray();
        if (!$rider) return redirect()->to(base_url('admin/riders'))->with('error', 'Rider not found');

        $ctype  = $this->request->getPost('commission_type') === 'percent' ? 'percent' : 'per_order';
        $cvalue = max(0, (float)$this->request->getPost('commission_value'));
        if ($ctype === 'percent' && $cvalue > 100) $cvalue = 100;

        $now = date('Y-m-d H:i:s');
        $db->table('users')->where('id', (int)$userId)->update(['is_active' => 1, 'updated_at' => $now]);
        $db->table('riders')->where('id', $rider['id'])->update([
            'is_active'        => 1,
            'commission_type'  => $ctype,
            'commission_value' => $cvalue,
            'updated_at'       => $now,
        ]);

        return redirect()->to(base_url('admin/riders'))->with('success', 'Approved — the driver can log in with the password they set.');
    }

    // ── Reject a self-signed-up driver (removes the pending rows) ──
    public function reject($userId)
    {
        if (!$this->canManage()) return redirect()->to(base_url('admin/dashboard'))->with('error', 'Not permitted');

        $db  = \Config\Database::connect();
        $rid = session('restaurant_id');

        $rider = $db->table('riders')->where('user_id', (int)$userId)->where('restaurant_id', $rid)->get()->getRowArray();
        if (!$rider) return redirect()->to(base_url('admin/riders'))->with('error', 'Rider not found');

        $uid = (int)$rider['user_id'];
        // only if they never earned anything (pending users cannot, but be safe)
        $earned = $db->table('rider_wallet_txns')->where('rider_user_id', $uid)->countAllResults();
        if (!$earned) {
            $db->table('riders')->where('id', $rider['id'])->delete();
            $db->table('users')->where('id', $uid)->delete();
        }
        return redirect()->to(base_url('admin/riders'))->with('success', $earned ? 'Rider kept — wallet has transactions, pause it instead' : 'Application rejected');
    }

    // ── Wallet page (ledger + payout / deposit / adjust) ─────
    public function wallet($userId)
    {
        if (!$this->canManage()) return redirect()->to(base_url('admin/dashboard'))->with('error', 'Not permitted');

        $db  = \Config\Database::connect();
        $rid = session('restaurant_id');

        $rider = $db->table('riders r')
            ->select('r.*, u.name, u.email, u.phone')
            ->join('users u', 'u.id = r.user_id')
            ->where('r.user_id', (int)$userId)->where('r.restaurant_id', $rid)
            ->get()->getRowArray();
        if (!$rider) return redirect()->to(base_url('admin/riders'))->with('error', 'Rider not found');

        $wallet = $db->query(
            "SELECT COALESCE(SUM(cod_delta), 0) AS cod_in_hand,
                    COALESCE(SUM(earn_delta), 0) AS payable
             FROM rider_wallet_txns WHERE rider_user_id = ?",
            [(int)$userId]
        )->getRowArray();

        $txns = $db->table('rider_wallet_txns t')
            ->select("t.*, o.order_number,
                      cu.name AS creator_name,
                      CASE WHEN t.order_id IS NOT NULL AND o.id IS NULL THEN CONCAT('#', t.order_id) ELSE '' END AS ghost")
            ->join('orders o', 'o.id = t.order_id', 'left')
            ->join('users cu', 'cu.id = t.created_by', 'left')
            ->where('t.rider_user_id', (int)$userId)
            ->orderBy('t.id', 'DESC')->limit(100)->get()->getResultArray();

        return view('admin/riders/wallet', [
            'pageTitle' => 'Rider Wallet — ' . $rider['name'],
            'rider'     => $rider,
            'wallet'    => $wallet,
            'txns'      => $txns,
            'userName'  => session('user_name'), 'userRole' => session('role_slug'),
            'restaurantName' => session('restaurant_name'),
        ]);
    }

    // ── Wallet money actions ─────────────────────────────────
    public function walletAction($userId)
    {
        if (!$this->canManage()) return redirect()->to(base_url('admin/dashboard'))->with('error', 'Not permitted');

        $db   = \Config\Database::connect();
        $rid  = session('restaurant_id');
        $uid  = (int)$userId;
        $type  = $this->request->getPost('type');
        $amount = round((float)$this->request->getPost('amount'), 2);
        $note   = trim((string)$this->request->getPost('notes'));

        $rider = $db->table('riders r')
            ->select('r.*, u.name')
            ->join('users u', 'u.id = r.user_id')
            ->where('r.user_id', $uid)->where('r.restaurant_id', $rid)
            ->get()->getRowArray();
        if (!$rider) return redirect()->to(base_url('admin/riders'))->with('error', 'Rider not found');

        $back = base_url('admin/riders/wallet/' . $uid);

        if ($amount <= 0) {
            return redirect()->to($back)->with('error', 'Enter an amount.');
        }

        $sums = $db->query(
            "SELECT COALESCE(SUM(cod_delta), 0) AS cod_in_hand,
                    COALESCE(SUM(earn_delta), 0) AS payable
             FROM rider_wallet_txns WHERE rider_user_id = ?", [$uid])->getRowArray();
        $codInHand = (float)$sums['cod_in_hand'];
        $payable   = (float)$sums['payable'];

        $row = [
            'rider_user_id' => $uid,
            'restaurant_id' => (int)$rider['restaurant_id'],
            'branch_id'     => (int)$rider['branch_id'],
            'created_by'    => (int)session('user_id'),
            'created_at'    => date('Y-m-d H:i:s'),
            'notes'         => $note ?: null,
        ];

        switch ($type) {
            case 'payout':
                // Pay the rider their earned commission
                if ($amount > $payable + 0.001) {
                    return redirect()->to($back)->with('error', 'Payout ₹' . number_format($amount, 2) . ' exceeds payable ₹' . number_format($payable, 2));
                }
                $row['txn_type']  = 'payout';
                $row['earn_delta'] = -$amount;
                $okMsg = 'Paid out ₹' . number_format($amount, 2) . ' to ' . $rider['name'];
                break;

            case 'deposit':
                // Rider hands the collected COD cash to the restaurant
                if ($amount > $codInHand + 0.001) {
                    return redirect()->to($back)->with('error', 'Deposit ₹' . number_format($amount, 2) . ' exceeds cash in hand ₹' . number_format($codInHand, 2));
                }
                $row['txn_type']  = 'cod_deposit';
                $row['cod_delta'] = -$amount;
                $okMsg = 'Cash deposit ₹' . number_format($amount, 2) . ' recorded';
                break;

            case 'adjust_cod':
                $row['txn_type']  = 'adjust';
                $row['cod_delta']  = $amount;
                $row['notes']      = trim('Adjustment: ' . ($note ?: 'manual COD correction'));
                $okMsg = 'COD adjusted by ₹' . number_format($amount, 2);
                break;

            case 'adjust_earn':
                $row['txn_type']   = 'adjust';
                $row['earn_delta'] = $amount;
                $row['notes']      = trim('Adjustment: ' . ($note ?: 'manual earning correction'));
                $okMsg = 'Earnings adjusted by ₹' . number_format($amount, 2);
                break;

            default:
                return redirect()->to($back)->with('error', 'Unknown wallet action');
        }

        $db->table('rider_wallet_txns')->insert($row);
        return redirect()->to($back)->with('success', $okMsg);
    }
}
