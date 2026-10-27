<?php

namespace App\Controllers\Public;

use App\Controllers\BaseController;

/**
 * PUBLIC driver self-signup (no login needed).
 *
 * The restaurant (or DinoviX) shares the link  /rider-apply/{restaurant_id} .
 * The driver fills the form themself — name, phone, email, THEIR OWN
 * password, vehicle, rider type (own rider / DinoviX fleet) — no SMS or
 * email required anywhere. The account is created PENDING:
 *   users.is_active = 0, riders.is_active = 0
 * so they cannot log in yet. The owner sees it on Delivery Riders →
 * "Pending approval", sets the commission and taps Approve — only then
 * can the driver sign in to the pocket app.
 */
class RiderApplyController extends BaseController
{
    private function spot(int $rid): array
    {
        $db = \Config\Database::connect();
        $r  = $db->table('restaurants')->where('id', $rid)->where('is_active', 1)->get()->getRowArray();
        if (!$r) {
            echo view('rider_apply_error', ['msg' => 'This signup link is not valid any more — ask the restaurant for the current one.']);
            exit;
        }
        return [$r, $db];
    }

    public function form($rid)
    {
        [$r, $db] = $this->spot((int)$rid);
        return view('rider_apply', [
            'restaurant' => $r,
            'branches'   => $db->table('branches')->where('restaurant_id', $r['id'])->where('is_active', 1)->get()->getResultArray(),
        ]);
    }

    public function submit($rid)
    {
        [$r, $db] = $this->spot((int)$rid);
        $rid = (int)$r['id'];

        $name     = trim((string)$this->request->getPost('name'));
        $phone    = trim((string)$this->request->getPost('phone'));
        $email    = strtolower(trim((string)$this->request->getPost('email')));
        $pass     = (string)$this->request->getPost('password');
        $bid      = (int)$this->request->getPost('branch_id') ?: null;
        $vehicle  = strtoupper(trim((string)$this->request->getPost('vehicle_no')));
        $employer = $this->request->getPost('employer_type') === 'dinovix' ? 'dinovix' : 'restaurant';

        $bad = null;
        if ($name === '' || mb_strlen($name) > 60)                        $bad = 'Your name is required.';
        elseif (!preg_match('/^[0-9+\-\s]{6,15}$/', $phone))               $bad = 'Enter a valid phone number.';
        elseif (!filter_var($email, FILTER_VALIDATE_EMAIL))                $bad = 'Enter a valid email — it is your login.';
        elseif (strlen($pass) < 6)                                         $bad = 'Password must be at least 6 characters.';
        if ($bad) {
            return view('rider_apply', [
                'restaurant' => $r,
                'branches'   => $db->table('branches')->where('restaurant_id', $rid)->where('is_active', 1)->get()->getResultArray(),
                'error'      => $bad,
                'old'        => compact('name', 'phone', 'email', 'vehicle', 'bid', 'employer'),
            ]);
        }

        // unique e-mail per restaurant (any role) — else duplicate accounts
        $exists = $db->table('users')->where('email', $email)->where('restaurant_id', $rid)->countAllResults();
        if ($exists) {
            return view('rider_apply', [
                'restaurant' => $r,
                'branches'   => $db->table('branches')->where('restaurant_id', $rid)->where('is_active', 1)->get()->getResultArray(),
                'error'      => 'This email is already registered here. Try logging in, or use another email.',
                'old'        => compact('name', 'phone', 'email', 'vehicle', 'bid', 'employer'),
            ]);
        }

        $role = $db->table('roles')->where('slug', 'delivery')->get()->getRowArray();
        if (!$role) {
            return view('rider_apply', ['restaurant' => $r, 'branches' => [], 'error' => 'Signup temporarily unavailable — contact support.']);
        }

        $now = date('Y-m-d H:i:s');
        $db->transStart();
        $db->table('users')->insert([
            'restaurant_id' => $rid,
            'branch_id'     => $bid,
            'role_id'       => $role['id'],
            'name'          => $name,
            'email'         => $email,
            'phone'         => $phone,
            'password'      => password_hash($pass, PASSWORD_BCRYPT),
            'employee_code' => 'RDR-' . strtoupper(bin2hex(random_bytes(2))),
            'is_active'     => 0,           // PENDING approval
            'created_at'    => $now,
            'updated_at'    => $now,
        ]);
        $uid = (int)$db->insertID();
        $db->table('riders')->insert([
            'user_id'          => $uid,
            'restaurant_id'    => $rid,
            'branch_id'        => $bid,
            'employer_type'    => $employer,
            'vehicle_no'       => $vehicle ?: null,
            'commission_type'  => 'per_order',
            'commission_value' => 0,
            'is_active'        => 0,        // PENDING approval
            'created_at'       => $now,
            'updated_at'       => $now,
        ]);
        $db->transComplete();

        return view('rider_apply_done', [
            'restaurant' => $r,
            'name'       => $name,
            'fleet'      => $employer === 'dinovix',
        ]);
    }
}
