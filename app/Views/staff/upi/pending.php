<?php $this->extend('layouts/main'); $this->section('content'); ?>
<div style="padding:0 1rem">
  <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:.5rem;margin-bottom:1rem">
    <div style="font-size:.85rem;color:var(--text-muted)">
      Confirm only what you can see in your bank / merchant UPI app. Customer taps, screenshots or SMS
      notices are <b>not</b> proof of payment.
    </div>
    <div style="display:flex;gap:.4rem">
      <a href="<?= base_url('pos/upi/history') ?>" class="btn btn-outline btn-sm"><i class="fa fa-history"></i> Verification History</a>
      <a href="<?= base_url('admin/upi-settings') ?>" class="btn btn-outline btn-sm"><i class="fa fa-qrcode"></i> UPI Settings</a>
    </div>
  </div>

  <?php if (session()->getFlashdata('success')): ?>
  <div class="card" style="border-left:4px solid var(--success);margin-bottom:1rem"><div class="card-body" style="color:var(--success);font-weight:600"><?= esc(session()->getFlashdata('success')) ?></div></div>
  <?php endif; ?>

  <?php if (empty($rows)): ?>
  <div class="card"><div class="empty-state" style="padding:3rem">
    <i class="fa fa-check-circle" style="font-size:2.5rem;color:var(--success)"></i>
    <p>No pending UPI payments — all clear</p>
  </div></div>
  <?php else: ?>
  <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:.75rem">
    <?php foreach ($rows as $r):
        $isExpired = $r['verify_status'] === 'expired';
        $age       = time() - strtotime($r['created_at']); ?>
    <div class="card" style="border-left:4px solid <?= $isExpired ? 'var(--warning)' : 'var(--primary)' ?>">
      <div class="card-body">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:.6rem">
          <div>
            <div style="font-weight:800;font-size:1.05rem"><?= esc($r['order_number']) ?></div>
            <div style="font-size:.72rem;color:var(--text-muted)">
              Ref <span style="font-family:'JetBrains Mono',monospace"><?= esc($r['attempt_ref']) ?></span>
              · <?= $r['customer_name'] ? esc($r['customer_name']) . ' · ' : '' ?><?= esc($r['table_id'] ?? '') ?>
            </div>
          </div>
          <span class="badge-pill badge-<?= $isExpired ? 'warning' : 'primary' ?>"><?= $isExpired ? 'EXPIRED' : 'AWAITING' ?></span>
        </div>

        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:.75rem">
          <div>
            <div style="font-size:.7rem;color:var(--text-muted)">QR amount to receive</div>
            <div style="font-size:1.6rem;font-weight:800;color:var(--primary)">₹<?= number_format((float)$r['amount'], 2) ?></div>
          </div>
          <div style="text-align:right;font-size:.72rem;color:var(--text-muted)">
            Order total ₹<?= number_format((float)$r['total_amount'], 2) ?><br>
            Outstanding ₹<?= number_format((float)$r['outstanding'], 2) ?><br>
            Opened <?= $age < 3600 ? floor($age/60) . ' min' : floor($age/3600) . ' hr' ?> ago
          </div>
        </div>

        <?php if ($r['claimed_by_customer']): ?>
        <div style="background:#FFF7ED;border:1px solid #FDBA74;border-radius:8px;padding:.5rem .6rem;font-size:.75rem;margin-bottom:.5rem;color:#9A3412">
          <i class="fa fa-hand-paper"></i> Customer tapped &ldquo;I&rsquo;ve paid&rdquo;
          <?= $r['claimed_at'] ? ' at ' . date('h:i A', strtotime($r['claimed_at'])) : '' ?> — verify in your bank app now.
        </div>
        <?php endif; ?>

        <div style="display:flex;gap:.4rem">
          <button class="btn btn-primary" style="flex:2;font-size:.82rem"
                  onclick="openVerify('<?= esc($r['attempt_ref']) ?>','<?= esc($r['order_number']) ?>',<?= (float)$r['amount'] ?>)">
            <i class="fa fa-check"></i> Verify
          </button>
          <button class="btn btn-outline" style="flex:1;font-size:.82rem"
                  onclick="openReject('<?= esc($r['attempt_ref']) ?>','<?= esc($r['order_number']) ?>')">
            <i class="fa fa-ban"></i> Reject
          </button>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>

<!-- Verify modal -->
<div class="modal-overlay" id="verifyModal">
  <div class="modal" style="max-width:430px">
    <div class="modal-header">
      <span class="modal-title">✅ Verify payment</span>
      <button class="modal-close" onclick="closeModal('verifyModal')"><i class="fa fa-times"></i></button>
    </div>
    <div class="modal-body">
      <div style="text-align:center;margin-bottom:1rem;padding:.875rem;background:var(--primary-light,#FFF1E8);border-radius:10px">
        <div id="vOrder" style="font-weight:800"></div>
        <div style="font-size:1.6rem;font-weight:800;color:var(--primary)">₹<span id="vAmount"></span></div>
        <div style="font-size:.72rem;color:var(--text-muted)">Match this with the amount that actually arrived in your bank/UPI app</div>
      </div>
      <div class="form-group">
        <label class="form-label">Bank / UPI transaction reference (UTR)</label>
        <input type="text" id="vTxnRef" class="form-control" maxlength="64" placeholder="e.g. 405123456789 or your bank's ref"
               style="font-family:'JetBrains Mono',monospace">
      </div>
      <div class="form-group">
        <label class="form-label">Received amount (₹)</label>
        <input type="number" id="vReceived" class="form-control" step="0.01" min="0">
      </div>
      <div class="form-group">
        <label class="form-label">Note (optional)</label>
        <input type="text" id="vNote" class="form-control" maxlength="200" placeholder="e.g. verified in GPay merchant app">
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-outline" onclick="closeModal('verifyModal')">Cancel</button>
      <button class="btn btn-primary" id="vBtn" onclick="doVerify()"><i class="fa fa-check"></i> Confirm Payment</button>
    </div>
  </div>
</div>

<!-- Reject modal -->
<div class="modal-overlay" id="rejectModal">
  <div class="modal" style="max-width:380px">
    <div class="modal-header">
      <span class="modal-title">🚫 Reject claim</span>
      <button class="modal-close" onclick="closeModal('rejectModal')"><i class="fa fa-times"></i></button>
    </div>
    <div class="modal-body">
      <div id="rOrder" style="font-weight:700;margin-bottom:.5rem"></div>
      <div class="form-group">
        <label class="form-label">Reason (required)</label>
        <input type="text" id="rReason" class="form-control" maxlength="200" placeholder="e.g. No matching credit in bank app">
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-outline" onclick="closeModal('rejectModal')">Cancel</button>
      <button class="btn btn-primary" id="rBtn" onclick="doReject()" style="background:var(--danger)"><i class="fa fa-ban"></i> Reject</button>
    </div>
  </div>
</div>

<script>
const CSRF_NAME  = document.querySelector('meta[name="csrf-token"]').dataset.name;
const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').content;
let VERIFY_REF = '', REJECT_REF = '';

function openVerify(ref, order, amount) {
  VERIFY_REF = ref;
  document.getElementById('vOrder').textContent  = order;
  document.getElementById('vAmount').textContent = amount.toFixed(2);
  document.getElementById('vReceived').value     = amount.toFixed(2);
  document.getElementById('vTxnRef').value       = '';
  document.getElementById('vNote').value         = '';
  openModal('verifyModal');
  setTimeout(() => document.getElementById('vTxnRef').focus(), 150);
}

function doVerify() {
  const btn = document.getElementById('vBtn');
  btn.disabled = true;
  btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Verifying...';
  fetch('<?= base_url('pos/upi/verify') ?>', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
      body: new URLSearchParams({
        [CSRF_NAME]: CSRF_TOKEN,
        attempt_ref: VERIFY_REF,
        txn_ref: document.getElementById('vTxnRef').value.trim(),
        amount:  document.getElementById('vReceived').value,
        note:    document.getElementById('vNote').value.trim()
      })
    }).then(r => r.json()).then(d => {
      if (d.success) { closeModal('verifyModal'); showToast(d.message, 'success'); setTimeout(() => location.reload(), 900); }
      else showToast(d.message || 'Could not verify', 'error');
    }).catch(e => showToast(e.message || 'Network error', 'error'))
    .finally(() => { btn.disabled = false; btn.innerHTML = '<i class="fa fa-check"></i> Confirm Payment'; });
}

function openReject(ref, order) {
  REJECT_REF = ref;
  document.getElementById('rOrder').textContent = 'Reject claim for ' + order + '?';
  document.getElementById('rReason').value      = '';
  openModal('rejectModal');
  setTimeout(() => document.getElementById('rReason').focus(), 150);
}

function doReject() {
  const btn = document.getElementById('rBtn');
  btn.disabled = true;
  btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Rejecting...';
  fetch('<?= base_url('pos/upi/reject') ?>', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
      body: new URLSearchParams({
        [CSRF_NAME]: CSRF_TOKEN,
        attempt_ref: REJECT_REF,
        reason: document.getElementById('rReason').value.trim()
      })
    }).then(r => r.json()).then(d => {
      if (d.success) { closeModal('rejectModal'); showToast(d.message, 'warning'); setTimeout(() => location.reload(), 900); }
      else showToast(d.message || 'Could not reject', 'error');
    }).catch(e => showToast(e.message || 'Network error', 'error'))
    .finally(() => { btn.disabled = false; btn.innerHTML = '<i class="fa fa-ban"></i> Reject'; });
}
</script>
<?php $this->endSection(); ?>
