<?php

namespace App\Controllers\Manager;

use App\Controllers\BaseController;

/**
 * Stock requisitions — staff request inventory, managers/admins approve,
 * approval is issued (stock deducted with a ledger entry).
 * Same page serves everyone; approve/reject/issue is gated by role.
 */
class RequisitionController extends BaseController
{
    private const CAN_MANAGE = ['restaurant_admin', 'branch_manager'];

    public function index()
    {
        $db   = \Config\Database::connect();
        $rid  = (int)session('restaurant_id');
        $bid  = (int)session('branch_id');
        $role = session('role_slug');
        $canManage = in_array($role, self::CAN_MANAGE, true);

        $items = $db->table('inventory_items ii')
            ->select('ii.id, ii.name, ii.unit, COALESCE(s.current_stock,0) AS stock, c.name AS category')
            ->join('inventory_categories c', 'c.id = ii.category_id', 'left')
            ->join('inventory_stock s', "s.inventory_item_id = ii.id AND s.branch_id = {$bid}", 'left')
            ->where('ii.restaurant_id', $rid)
            ->where('ii.is_active', 1)
            ->orderBy('ii.name', 'ASC')
            ->get()->getResultArray();

        $rows = $db->table('requisitions rq')
            ->select('rq.*, u.name AS requester, a.name AS approver, i.name AS issuer')
            ->join('users u', 'u.id = rq.requested_by', 'left')
            ->join('users a', 'a.id = rq.approved_by', 'left')
            ->join('users i', 'i.id = rq.issued_by', 'left')
            ->where('rq.restaurant_id', $rid)
            // non-managers only see their own branch and can't read others' history
            ->where($canManage ? 'rq.restaurant_id' : 'rq.branch_id', $canManage ? $rid : $bid)
            ->orderBy('rq.id', 'DESC')
            ->limit(100)
            ->get()->getResultArray();

        $itemRows = [];
        if ($rows) {
            $ids   = array_column($rows, 'id');
            $names = $db->table('requisition_items ri')
                ->select('ri.*, ii.name AS item_name')
                ->join('inventory_items ii', 'ii.id = ri.inventory_item_id', 'left')
                ->whereIn('ri.requisition_id', $ids)
                ->orderBy('ri.id', 'ASC')
                ->get()->getResultArray();
            foreach ($names as $n) {
                $itemRows[(int)$n['requisition_id']][] = $n;
            }
        }

        return view('admin/requisitions/index', [
            'pageTitle'  => 'Stock Requisitions',
            'items'      => $items,
            'rows'       => $rows,
            'itemRows'   => $itemRows,
            'canManage'  => $canManage,
            'role'       => $role,
        ]);
    }

    // ── Staff: create a requisition ─────────────────────────────
    public function store()
    {
        $db      = \Config\Database::connect();
        $rid     = (int)session('restaurant_id');
        $bid     = (int)session('branch_id');
        $uid     = (int)session('user_id');
        $note    = trim((string)$this->request->getPost('note'));
        $ids     = array_map('intval', (array)($this->request->getPost('item_id') ?? []));
        $qtys    = (array)($this->request->getPost('qty') ?? []);

        $valid = [];
        foreach ($ids as $k => $itemId) {
            $qty = round((float)($qtys[$k] ?? 0), 3);
            if ($itemId <= 0 || $qty <= 0) continue;
            $item = $db->table('inventory_items ii')
                ->select('ii.id, ii.name, ii.unit')
                ->where('ii.id', $itemId)->where('ii.restaurant_id', $rid)->where('ii.is_active', 1)
                ->get()->getRowArray();
            if ($item) $valid[$itemId] = ['item' => $item, 'qty' => $qty];
        }

        if (!$valid) {
            return redirect()->back()->with('error', 'Add at least one item with a quantity.');
        }

        // Unique requisition number (atomic counter + random suffix safety)
        do {
            $db->query('UPDATE restaurants SET requisition_counter = LAST_INSERT_ID(requisition_counter) + 1 WHERE id = ?', [$rid]);
            $n = (int)$db->query('SELECT LAST_INSERT_ID() AS n')->getRowArray()['n'];
            $reqNumber = 'REQ' . date('ymd') . str_pad((string)$n, 4, '0', STR_PAD_LEFT)
                . '-' . random_int(100, 999);
        } while ((int)$db->table('requisitions')->where('restaurant_id', $rid)->where('req_number', $reqNumber)->countAllResults() > 0);

        $db->transStart();
        $db->table('requisitions')->insert([
            'restaurant_id' => $rid,
            'branch_id'     => $bid,
            'req_number'    => $reqNumber,
            'status'        => 'pending',
            'requested_by'  => $uid,
            'note'          => $note ?: null,
            'created_at'    => date('Y-m-d H:i:s'),
        ]);
        $reqId = (int)$db->insertID();
        foreach ($valid as $v) {
            $db->table('requisition_items')->insert([
                'requisition_id'    => $reqId,
                'inventory_item_id' => $v['item']['id'],
                'qty_requested'     => $v['qty'],
                'unit'              => $v['item']['unit'],
                'created_at'        => date('Y-m-d H:i:s'),
            ]);
        }
        $db->transComplete();

        return redirect()->to(base_url('admin/requisitions'))->with('success',
            "Requisition {$reqNumber} submitted — pending manager approval.");
    }

    // ── Manager/admin: approve (adjustable quantities) ──────────
    public function approve($id)
    {
        if (!in_array(session('role_slug'), self::CAN_MANAGE, true)) {
            return redirect()->back()->with('error', 'Only admins and managers can approve requisitions.');
        }

        $db    = \Config\Database::connect();
        $rid   = (int)session('restaurant_id');
        $approved = (array)($this->request->getPost('qty_approved') ?? []);

        $req = $db->table('requisitions')
            ->where('id', $id)->where('restaurant_id', $rid)
            ->get()->getRowArray();
        if (!$req) return redirect()->back()->with('error', 'Requisition not found.');
        if ($req['status'] !== 'pending') {
            return redirect()->back()->with('error', 'Only pending requisitions can be approved.');
        }

        $db->transBegin();
        $items = $db->table('requisition_items ri')
            ->select('ri.*, ii.name')
            ->join('inventory_items ii', 'ii.id = ri.inventory_item_id', 'left')
            ->where('ri.requisition_id', $id)
            ->get()->getResultArray();

        $any = false;
        foreach ($items as $it) {
            $qty = $approved[$it['id']] ?? $it['qty_requested'];
            $qty = round(min((float)$it['qty_requested'], max(0.0, (float)$qty)), 3);
            $db->table('requisition_items')->where('id', $it['id'])->update(['qty_approved' => $qty]);
            if ($qty > 0) $any = true;
        }
        if (!$any) {
            $db->transRollback();
            return redirect()->back()->with('error', 'Approve at least one quantity (all were 0).');
        }

        $db->table('requisitions')->where('id', $id)->update([
            'status'      => 'approved',
            'approved_by' => (int)session('user_id'),
            'approved_at' => date('Y-m-d H:i:s'),
        ]);
        $db->transCommit();

        return redirect()->to(base_url('admin/requisitions'))->with('success',
            "Requisition {$req['req_number']} approved — issue stock from the list when ready.");
    }

    // ── Manager/admin: reject ───────────────────────────────────
    public function reject($id)
    {
        if (!in_array(session('role_slug'), self::CAN_MANAGE, true)) {
            return redirect()->back()->with('error', 'Only admins and managers can reject requisitions.');
        }

        $db   = \Config\Database::connect();
        $rid  = (int)session('restaurant_id');
        $reason = trim((string)$this->request->getPost('reason'));

        $req = $db->table('requisitions')
            ->where('id', $id)->where('restaurant_id', $rid)->where('status', 'pending')
            ->get()->getRowArray();
        if (!$req) return redirect()->back()->with('error', 'Requisition not found or already processed.');

        $db->table('requisitions')->where('id', $id)->update([
            'status'        => 'rejected',
            'reject_reason' => $reason ?: null,
            'approved_by'   => (int)session('user_id'),
            'approved_at'   => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to(base_url('admin/requisitions'))->with('success',
            "Requisition {$req['req_number']} rejected.");
    }

    // ── Manager/admin: issue approved quantities (stock deduction) ──
    public function issue($id)
    {
        if (!in_array(session('role_slug'), self::CAN_MANAGE, true)) {
            return redirect()->back()->with('error', 'Only admins and managers can issue requisitions.');
        }

        $db  = \Config\Database::connect();
        $rid = (int)session('restaurant_id');
        $uid = (int)session('user_id');

        $req = $db->query(
            "SELECT * FROM requisitions
              WHERE id = ? AND restaurant_id = ? AND status = 'approved' FOR UPDATE",
            [$id, $rid]
        )->getRowArray();
        if (!$req) {
            return redirect()->back()->with('error', 'Requisition not found, or not approved yet.');
        }

        $items = $db->table('requisition_items')
            ->where('requisition_id', $id)
            ->where('qty_approved >', 0)
            ->get()->getResultArray();
        if (!$items) {
            return redirect()->back()->with('error', 'Nothing approved to issue.');
        }

        $db->transBegin();
        $weekAgoWarning = [];
        foreach ($items as $it) {
            $qty = (float)$it['qty_approved'];

            $stock = $db->table('inventory_stock')
                ->where('inventory_item_id', $it['inventory_item_id'])
                ->where('branch_id', $req['branch_id'])
                ->get()->getRowArray();

            if (!$stock) {
                $db->table('inventory_stock')->insert([
                    'inventory_item_id' => $it['inventory_item_id'],
                    'branch_id'         => $req['branch_id'],
                    'current_stock'     => 0,
                    'unit_cost'         => 0,
                    'updated_at'        => date('Y-m-d H:i:s'),
                ]);
                $weekAgoWarning[] = $it['inventory_item_id'];
            } elseif ((float)$stock['current_stock'] < $qty) {
                $weekAgoWarning[] = $it['inventory_item_id']; // issued to negative — flagged
            }

            // Stock deduction identical to RecipeEngine's ledger pattern
            $db->table('inventory_stock')
                ->where('inventory_item_id', $it['inventory_item_id'])
                ->where('branch_id', $req['branch_id'])
                ->set('current_stock', "current_stock - {$qty}", false)
                ->set('updated_at', date('Y-m-d H:i:s'))
                ->update();

            $db->table('inventory_transactions')->insert([
                'inventory_item_id' => $it['inventory_item_id'],
                'branch_id'         => $req['branch_id'],
                'user_id'           => $uid,
                'transaction_type'  => 'requisition_issue',
                'quantity'          => $qty,
                'reference_id'      => $id,
                'notes'             => 'Requisition ' . $req['req_number'] . ' issued',
                'created_at'        => date('Y-m-d H:i:s'),
            ]);

            $db->table('requisition_items')->where('id', $it['id'])->update(['qty_issued' => $qty]);
        }

        $db->table('requisitions')->where('id', $id)->update([
            'status'    => 'issued',
            'issued_by' => $uid,
            'issued_at' => date('Y-m-d H:i:s'),
        ]);
        $db->transCommit();

        $msg = "Requisition {$req['req_number']} issued — stock deducted from the branch.";
        if ($weekAgoWarning) {
            $msg .= ' Note: some items went below zero in stock — purchase before next use.';
        }
        return redirect()->to(base_url('admin/requisitions'))->with('success', $msg);
    }

    // ── Requester: cancel their own pending requisition ─────────
    public function cancel($id)
    {
        $db  = \Config\Database::connect();
        $rid = (int)session('restaurant_id');
        $uid = (int)session('user_id');

        $req = $db->table('requisitions')
            ->where('id', $id)->where('restaurant_id', $rid)
            ->where('requested_by', $uid)->where('status', 'pending')
            ->get()->getRowArray();
        if (!$req) {
            return redirect()->back()->with('error', 'Only your own pending requisitions can be cancelled.');
        }

        $db->table('requisitions')->where('id', $id)->update([
            'status' => 'cancelled',
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to(base_url('admin/requisitions'))->with('success', 'Requisition cancelled.');
    }
}
