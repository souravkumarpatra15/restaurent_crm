<?php $this->extend('layouts/main'); $this->section('content'); ?>
<div style="padding:0 1rem">
  <?php if (session()->getFlashdata('success')): ?>
  <div class="card" style="border-left:4px solid var(--success);margin-bottom:1rem"><div class="card-body" style="color:var(--success);font-weight:600"><?= esc(session()->getFlashdata('success')) ?></div></div>
  <?php endif; ?>
  <?php if (session()->getFlashdata('error')): ?>
  <div class="card" style="border-left:4px solid var(--danger);margin-bottom:1rem"><div class="card-body" style="color:var(--danger);font-weight:600"><?= esc(session()->getFlashdata('error')) ?></div></div>
  <?php endif; ?>

  <div class="card" style="margin-bottom:1rem">
    <div class="card-header"><span class="card-title"><i class="fa fa-qrcode" style="color:var(--primary)"></i> UPI QR Payments — Your Own Bank Account</span></div>
    <div class="card-body">
      <p style="font-size:.85rem;color:var(--text-muted);margin-bottom:.75rem">
        Customers scan a QR on the POS screen and pay <b>directly into your restaurant's bank/UPI account</b>.
        DinoviX never collects or holds your money — staff confirm each payment only after checking the real
        transaction in your bank or merchant UPI app.
      </p>
      <form action="<?= base_url('admin/upi-settings/save') ?>" method="POST">
        <?= csrf_field() ?>

        <div class="form-group" style="display:flex;align-items:center;gap:.75rem">
          <label class="toggle-switch" style="margin:0">
            <input type="checkbox" name="upi_enabled" id="upiEnabled" <?= !empty($restaurant['upi_enabled']) ? 'checked' : '' ?> onchange="toggleUpiFields()">
            <span class="toggle-slider"></span>
          </label>
          <div>
            <div style="font-weight:700">Enable UPI QR payments</div>
            <div style="font-size:.75rem;color:var(--text-muted)">Adds a &ldquo;UPI QR&rdquo; payment option at the POS checkout</div>
          </div>
        </div>

        <div id="upiFields" style="<?= empty($restaurant['upi_enabled']) ? 'opacity:.5;pointer-events:none' : '' ?>">
          <div class="form-group">
            <label class="form-label">Your receiving UPI ID (VPA)</label>
            <input type="text" name="upi_vpa" id="upiVpa" class="form-input" placeholder="yourrestaurant@okhdfcbank" maxlength="120"
                   value="<?= esc($restaurant['upi_vpa'] ?? '') ?>" style="max-width:420px;font-family:'JetBrains Mono',monospace">
            <div style="font-size:.72rem;color:var(--text-muted);margin-top:.25rem">Format: name@bankhandle &mdash; exactly the ID you use to receive money in GPay / PhonePe / Paytm / your bank app.</div>
          </div>
          <div class="form-group">
            <label class="form-label">Payee display name</label>
            <input type="text" name="upi_payee_name" id="upiPayee" class="form-input" maxlength="40"
                   value="<?= esc($restaurant['upi_payee_name'] ?? '') ?>" placeholder="<?= esc($restaurant['name'] ?? 'Restaurant name') ?>" style="max-width:420px">
            <div style="font-size:.72rem;color:var(--text-muted);margin-top:.25rem">Shown in the customer&rsquo;s UPI app next to the amount (3&ndash;40 characters).</div>
          </div>
        </div>

        <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save UPI Settings</button>
      </form>
    </div>
  </div>

  <?php if (!empty($restaurant['upi_settings_updated_at'])): ?>
  <div class="card" style="margin-bottom:1rem">
    <div class="card-header"><span class="card-title">Last change</span></div>
    <div class="card-body" style="font-size:.8rem;color:var(--text-muted)">
      <i class="fa fa-clock"></i> <?= date('d M Y, h:i A', strtotime($restaurant['upi_settings_updated_at'])) ?>
      <?= !empty($restaurant['updated_by_name']) ? ' &mdash; by ' . esc($restaurant['updated_by_name']) : '' ?>
    </div>
  </div>
  <?php endif; ?>

  <div class="card">
    <div class="card-header"><span class="card-title"><i class="fa fa-history"></i> Payment settings history</span></div>
    <div class="card-body" style="padding:0">
      <?php if (empty($lastChange)): ?>
        <div class="empty-state" style="padding:2rem"><p>No changes recorded yet</p></div>
      <?php else: ?>
        <table class="table" style="width:100%;font-size:.8rem">
          <thead><tr><th>When</th><th>Action</th><th>UPI ID</th><th>Note</th></tr></thead>
          <tbody>
          <?php foreach ($lastChange as $lc): ?>
            <tr>
              <td style="white-space:nowrap"><?= date('d M h:i A', strtotime($lc['created_at'])) ?></td>
              <td><span class="badge-pill badge-<?= $lc['new_state'] === 'enabled' ? 'success' : 'gray' ?>"><?= esc($lc['new_state']) ?></span></td>
              <td style="font-family:'JetBrains Mono',monospace"><?= esc($lc['txn_ref'] ?? '') ?></td>
              <td style="color:var(--text-muted)"><?= esc($lc['note'] ?? '') ?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>
  </div>
</div>

<script>
function toggleUpiFields() {
  const on = document.getElementById('upiEnabled').checked;
  const f  = document.getElementById('upiFields');
  f.style.opacity      = on ? '1' : '.5';
  f.style.pointerEvents = on ? 'auto' : 'none';
}
</script>
<?php $this->endSection(); ?>
