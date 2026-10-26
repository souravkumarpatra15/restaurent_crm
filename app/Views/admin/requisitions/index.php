<?php $this->extend('layouts/main'); $this->section('content'); ?>
<div style="padding:0 1rem">

  <?php if (session()->getFlashdata('success')): ?>
  <div class="card" style="border-left:4px solid var(--success);margin-bottom:1rem"><div class="card-body" style="color:var(--success);font-weight:600"><?= esc(session()->getFlashdata('success')) ?></div></div>
  <?php endif; ?>
  <?php if (session()->getFlashdata('error')): ?>
  <div class="card" style="border-left:4px solid var(--danger);margin-bottom:1rem"><div class="card-body" style="color:var(--danger);font-weight:600"><?= esc(session()->getFlashdata('error')) ?></div></div>
  <?php endif; ?>

  <div style="display:grid;grid-template-columns:340px 1fr;gap:1rem;align-items:start">

    <!-- Left: create requisition -->
    <div class="card">
      <div class="card-header"><span class="card-title"><i class="fa fa-boxes-packing" style="color:var(--primary)"></i> Request stock</span></div>
      <div class="card-body">
        <?php if (empty($items)): ?>
          <div class="empty-state" style="padding:1.2rem"><p>No inventory items yet — add items under Inventory first</p></div>
        <?php else: ?>
        <form action="<?= base_url('admin/requisitions/store') ?>" method="POST" id="reqForm">
          <?= csrf_field() ?>
          <div id="reqLines" style="display:flex;flex-direction:column;gap:.5rem">
            <div style="display:flex;gap:.4rem;align-items:center">
              <select class="form-input" name="item_id[]" style="flex:1" required>
                <option value="">Select item…</option>
                <?php foreach ($items as $it): ?>
                <option value="<?= (int)$it['id'] ?>"><?= esc($it['name']) ?> (<?= esc($it['unit'] ?: 'unit') ?>)<?= $it['stock'] !== null ? ' — stock ' . rtrim(rtrim(number_format((float)$it['stock'], 3), '0'), '.') : '' ?></option>
                <?php endforeach; ?>
              </select>
              <input type="number" name="qty[]" class="form-input" style="width:86px" step="0.001" min="0.001" placeholder="Qty" required>
              <button type="button" class="btn btn-outline btn-sm" onclick="this.parentElement.remove()">✕</button>
            </div>
          </div>
          <div style="margin:.55rem 0">
            <button type="button" class="btn btn-outline btn-sm" onclick="addReqLine()"><i class="fa fa-plus"></i> Add item</button>
          </div>
          <div class="form-group">
            <label class="form-label">Note to the manager (optional)</label>
            <input type="text" name="note" class="form-input" maxlength="255" placeholder="e.g. Fri biryani prep — need extra paneer">
          </div>
          <button type="submit" class="btn btn-primary" style="width:100%"><i class="fa fa-paper-plane"></i> Submit requisition</button>
        </form>
        <div style="font-size:.7rem;color:var(--text-muted);margin-top:.5rem;line-height:1.5">
          A manager or admin will approve/reject this. Approved items are issued from branch stock and recorded in the inventory ledger.
        </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Right: requisition list -->
    <div class="card">
      <div class="card-header">
        <span class="card-title"><i class="fa fa-clipboard-check" style="color:var(--primary)"></i> Requisitions</span>
        <span style="font-size:.72rem;color:var(--text-muted)"><?= count($rows) ?> recent</span>
      </div>
      <div class="card-body" style="padding:0">
        <?php if (empty($rows)): ?>
          <div class="empty-state" style="padding:2.5rem"><i class="fa fa-inbox" style="font-size:2rem;color:var(--text-muted)"></i><p>No requisitions yet</p></div>
        <?php else: foreach ($rows as $r): ?>
        <?php
          $badge = ['pending' => 'badge-warning', 'approved' => 'badge-success', 'issued' => 'badge-success', 'rejected' => 'badge-danger', 'cancelled' => 'badge-gray'];
          $badge = $badge[$r['status']] ?? 'badge-gray';
        ?>
        <div style="border-bottom:1px solid var(--border);padding:.75rem 1rem">
          <div style="display:flex;justify-content:space-between;gap:.6rem;align-items:flex-start;flex-wrap:wrap">
            <div style="min-width:0">
              <div style="display:flex;gap:.5rem;align-items:center;flex-wrap:wrap">
                <b style="font-family:'JetBrains Mono',monospace;font-size:.8rem"><?= esc($r['req_number']) ?></b>
                <span class="badge-pill <?= $badge ?>"><?= esc($r['status']) ?></span>
              </div>
              <div style="font-size:.72rem;color:var(--text-muted);margin-top:.25rem">
                <?= esc($r['requester'] ?: '—') ?> · <?= $r['created_at'] ? date('d M, h:i A', strtotime($r['created_at'])) : '' ?>
                <?php if ($r['note']): ?> · note: <?= esc(mb_substr($r['note'], 0, 60)) ?><?php endif; ?>
                <?php if ($r['status'] === 'rejected' && $r['reject_reason']): ?> · reason: <i><?= esc($r['reject_reason']) ?></i><?php endif; ?>
                <?php if ($r['status'] === 'issued'): ?> · issued by <?= esc($r['issuer'] ?: '—') ?> at <?= $r['issued_at'] ? date('d M h:i A', strtotime($r['issued_at'])) : '' ?><?php endif; ?>
              </div>
              <table style="font-size:.75rem;margin-top:.4rem;border-collapse:collapse">
                <tbody>
                <?php foreach (($itemRows[$r['id']] ?? []) as $it): ?>
                  <tr>
                    <td style="padding:.1rem .8rem .1rem 0"><?= esc($it['item_name'] ?: 'Item ' . $it['inventory_item_id']) ?></td>
                    <td style="padding:.1rem;color:var(--text-muted)">requested <b><?= rtrim(rtrim(number_format((float)$it['qty_requested'], 3), '0'), '.') ?></b> <?= esc($it['unit'] ?: '') ?></td>
                    <?php if ($r['status'] === 'pending' && $canManage): ?>
                    <td style="padding:.1rem">
                      approve <input type="number" class="form-input" style="width:86px;display:inline-block;padding:.15rem .4rem" step="0.001" min="0"
                             name="qty_approved[<?= (int)$it['id'] ?>]" form="appr<?= (int)$r['id'] ?>" value="<?= rtrim(rtrim(number_format((float)$it['qty_requested'], 3), '0'), '.') ?>">
                      <?= esc($it['unit'] ?: '') ?>
                    </td>
                    <?php else: ?>
                    <td style="padding:.1rem;color:var(--text-muted)">
                      <?= $r['status'] !== 'pending' ? 'approved <b>' . rtrim(rtrim(number_format((float)$it['qty_approved'] ?? 0, 3), '0'), '.') . '</b>' : '' ?>
                      <?= $r['status'] === 'issued' ? ' · issued <b>' . rtrim(rtrim(number_format((float)$it['qty_issued'] ?? 0, 3), '0'), '.') . '</b>' : '' ?>
                    </td>
                    <?php endif; ?>
                  </tr>
                <?php endforeach; ?>
                </tbody>
              </table>
            </div>

            <div style="display:flex;gap:.35rem;flex-shrink:0;flex-wrap:wrap">
              <?php if ($r['status'] === 'pending' && $canManage): ?>
              <form action="<?= base_url('admin/requisitions/approve/' . $r['id']) ?>" method="POST" id="appr<?= (int)$r['id'] ?>" onsubmit="return confirm('Approve <?= esc($r['req_number']) ?> with the quantities above?')">
                <?= csrf_field() ?>
                <button class="btn btn-success btn-sm"><i class="fa fa-check"></i> Approve</button>
              </form>
              <form action="<?= base_url('admin/requisitions/reject/' . $r['id']) ?>" method="POST" onsubmit="const v=prompt('Reason for rejecting <?= esc($r['req_number']) ?>? (optional)'); if(v===null) return false; this.querySelector('input[name=reason]').value=v;">
                <?= csrf_field() ?>
                <input type="hidden" name="reason" value="">
                <button class="btn btn-outline btn-sm" style="color:var(--danger)"><i class="fa fa-times"></i> Reject</button>
              </form>
              <?php elseif ($r['status'] === 'approved' && $canManage): ?>
              <form action="<?= base_url('admin/requisitions/issue/' . $r['id']) ?>" method="POST" onsubmit="return confirm('Issue <?= esc($r['req_number']) ?>? This DEDUCTS the approved quantities from branch stock.')">
                <?= csrf_field() ?>
                <button class="btn btn-primary btn-sm"><i class="fa fa-truck-ramp-box"></i> Issue stock</button>
              </form>
              <?php elseif ($r['status'] === 'pending'): ?>
              <form action="<?= base_url('admin/requisitions/cancel/' . $r['id']) ?>" method="POST" onsubmit="return confirm('Cancel your requisition <?= esc($r['req_number']) ?>?')">
                <?= csrf_field() ?>
                <button class="btn btn-outline btn-sm">Cancel</button>
              </form>
              <?php endif; ?>
            </div>
          </div>
        </div>
        <?php endforeach; endif; ?>
      </div>
    </div>
  </div>
</div>

<script>
const firstLine = document.getElementById('reqLines')?.firstElementChild;
function addReqLine() {
  if (!firstLine) return;
  const clone = firstLine.cloneNode(true);
  clone.querySelector('input[name="qty[]"]').value = '';
  document.getElementById('reqLines').appendChild(clone);
}
</script>
<?php $this->endSection(); ?>
