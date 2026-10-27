<?php $this->extend('layouts/main'); $this->section('content'); ?>

<style>
.rider-card { background:#fff;border:1px solid var(--border);border-radius:14px;padding:1rem;margin-bottom:.7rem }
.r-badge { display:inline-flex;align-items:center;gap:.3rem;font-size:.66rem;font-weight:800;padding:.2rem .55rem;border-radius:999px }
.r-form { background:#fff;border:1px solid var(--border);border-radius:14px;padding:1rem }
.r-form .form-label { font-size:.72rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;letter-spacing:.04em;margin-bottom:.25rem }
</style>

<div style="display:flex;align-items:center;gap:.7rem;flex-wrap:wrap;margin-bottom:1rem">
  <div>
    <h1 style="font-size:1.35rem;font-weight:900;margin:0">🛵 Delivery Riders</h1>
    <div style="font-size:.8rem;color:var(--text-muted)">Onboard riders, set commission rules, settle wallets</div>
  </div>
</div>

<!-- Summary strip -->
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:.6rem;margin-bottom:1rem">
  <?php
    $totalHeld    = array_sum(array_map(fn($r) => (float)$r['cod_in_hand'], $riders));
    $totalPayable = array_sum(array_map(fn($r) => (float)$r['payable'], $riders));
    $activeCount  = count(array_filter($riders, fn($r) => (int)$r['user_active'] === 1));
  ?>
  <div class="rider-card" style="text-align:center"><b style="font-size:1.4rem"><?= count($riders) ?></b><div style="font-size:.68rem;color:var(--text-muted);font-weight:800">RIDERS</div><div style="font-size:.68rem;color:#16A34A;font-weight:800"><?= $activeCount ?> active</div></div>
  <div class="rider-card" style="text-align:center"><b style="font-size:1.4rem;color:#D97706">₹<?= number_format($totalHeld, 2) ?></b><div style="font-size:.68rem;color:var(--text-muted);font-weight:800">CASH WITH RIDERS</div><div style="font-size:.68rem;color:var(--text-muted)">COD to be deposited</div></div>
  <div class="rider-card" style="text-align:center"><b style="font-size:1.4rem;color:#16A34A">₹<?= number_format($totalPayable, 2) ?></b><div style="font-size:.68rem;color:var(--text-muted);font-weight:800">PAYABLE</div><div style="font-size:.68rem;color:var(--text-muted)">commissions owed</div></div>
</div>

<!-- Onboard form -->
<div class="r-form" style="margin-bottom:1.1rem">
  <div style="font-weight:900;margin-bottom:.6rem">➕ Onboard a rider</div>
  <form method="post" action="<?= base_url('admin/riders/store') ?>">
    <?= csrf_field() ?>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:.7rem">
      <div><div class="form-label">Name *</div><input type="text" name="name" class="form-control" placeholder="Rider name" required></div>
      <div><div class="form-label">Email (login) *</div><input type="email" name="email" class="form-control" placeholder="rider@restaurant.com" required></div>
      <div><div class="form-label">Password *</div><input type="text" name="password" class="form-control" placeholder="min 4 chars" required></div>
      <div><div class="form-label">Phone</div><input type="text" name="phone" class="form-control" placeholder="phone"></div>
      <div><div class="form-label">Branch</div>
        <select name="branch_id" class="form-control">
          <?php foreach ($branches as $b): ?><option value="<?= $b['id'] ?>"><?= esc($b['name']) ?></option><?php endforeach; ?>
        </select>
      </div>
      <div><div class="form-label">Vehicle no.</div><input type="text" name="vehicle_no" class="form-control" placeholder="WB-01-AB-1234"></div>
      <div><div class="form-label">Rider type</div>
        <select name="employer_type" class="form-control">
          <option value="restaurant">🧑‍🍳 My own rider</option>
          <option value="dinovix">🛵 DinoviX fleet rider</option>
        </select>
        <div style="font-size:.62rem;color:var(--text-muted);margin-top:.2rem">fleet riders get the DinoviX payout &amp; auto-assign</div>
      </div>
      <div><div class="form-label">Commission</div>
        <div style="display:flex;gap:.35rem">
          <select name="commission_type" class="form-control" style="max-width:130px">
            <option value="per_order">₹ per order</option>
            <option value="percent">% per order</option>
          </select>
          <input type="number" step="0.01" min="0" name="commission_value" class="form-control" placeholder="e.g. 20 / 2%" value="20">
        </div>
      </div>
    </div>
    <button type="submit" class="btn btn-primary" style="margin-top:.7rem"><i class="fa fa-user-plus"></i> Onboard rider</button>
  </form>
</div>

<?php if (!empty($pending)): ?>
<div class="rider-card" style="border-color:#FDBA74;background:#FFFBEB">
  <div style="display:flex;align-items:center;gap:.6rem;margin-bottom:.7rem">
    <span style="font-weight:900"><i class="fa fa-hourglass-half" style="color:#D97706"></i> Pending approval</span>
    <span class="r-badge" style="background:#FED7AA;color:#9A3412">applied from the signup link — they set their own password</span>
  </div>
  <?php foreach ($pending as $p): ?>
  <form method="post" action="<?= base_url('admin/riders/approve/' . $p['id']) ?>" style="display:flex;gap:.6rem;align-items:center;flex-wrap:wrap;background:#fff;border:1px solid #FDBA74;border-radius:12px;padding:.7rem;margin-bottom:.55rem">
    <?= csrf_field() ?>
    <div style="min-width:190px">
      <b><?= esc($p['name']) ?></b>
      <?php if (($p['employer_type'] ?? '') === 'dinovix'): ?><span class="r-badge" style="background:#FFEDD5;color:#C2410C">🛵 DinoviX fleet</span><?php endif; ?>
      <div style="font-size:.72rem;color:var(--text-muted)"><?= esc($p['email']) ?><?= $p['phone'] ? ' · ' . esc($p['phone']) : '' ?>
        <?= $p['vehicle_no'] ? ' · 🛵 ' . esc($p['vehicle_no']) : '' ?>
        <?= $p['branch_name'] ? ' · ' . esc($p['branch_name']) : '' ?></div>
    </div>
    <select name="commission_type" class="form-control" style="width:auto">
      <option value="per_order">₹ per order</option>
      <option value="percent">% per order</option>
    </select>
    <input type="number" step="0.01" min="0" name="commission_value" value="20" class="form-control" style="width:90px">
    <button type="submit" class="btn btn-sm btn-primary" style="margin-left:auto"><i class="fa fa-check"></i> Approve</button>
    <button type="submit" form="rejectForm<?= $p['id'] ?>" class="btn btn-sm btn-outline" style="border-color:#FCA5A5;color:#B91C1C"><i class="fa fa-xmark"></i> Reject</button>
  </form>
  <form method="post" id="rejectForm<?= $p['id'] ?>" action="<?= base_url('admin/riders/reject/' . $p['id']) ?>" style="display:none"><?= csrf_field() ?></form>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Invite link for direct driver signups -->
<div class="rider-card" style="display:flex;gap:.7rem;align-items:center;flex-wrap:wrap;background:#F8FAFC">
  <span style="font-weight:800;font-size:.8rem"><i class="fa fa-share-nodes" style="color:var(--primary)"></i> Let drivers apply themselves</span>
  <input id="applyLink" class="form-control" readonly style="flex:1;min-width:230px;font-size:.75rem" value="<?= base_url('rider-apply/' . ($restaurantId ?? session('restaurant_id'))) ?>">
  <button type="button" class="btn btn-sm btn-outline" onclick="copyApply(this)"><i class="fa fa-copy"></i> Copy link</button>
</div>

<!-- Rider list -->
<?php if (!$riders): ?>
  <div class="rider-card" style="text-align:center;color:var(--text-muted);padding:2rem"><?= $pending ? 'No active riders yet.' : 'No riders yet — onboard your first one above, or share the signup link.' ?></div>
<?php endif; ?>

<?php foreach ($riders as $r): ?>
<div class="rider-card" style="display:flex;align-items:center;gap:.8rem;flex-wrap:wrap">
  <div style="width:42px;height:42px;border-radius:50%;background:var(--primary-light);display:flex;align-items:center;justify-content:center;font-weight:900;color:var(--primary)">
    <?= strtoupper(substr($r['name'], 0, 1)) ?>
  </div>
  <div style="min-width:180px">
    <b><?= esc($r['name']) ?></b>
    <?php if (!(int)$r['user_active']): ?><span class="r-badge" style="background:#FEE2E2;color:#B91C1C">PAUSED</span><?php endif; ?>
    <div style="font-size:.72rem;color:var(--text-muted)"><?= esc($r['email']) ?><?= $r['phone'] ? ' · ' . esc($r['phone']) : '' ?>
      <?= $r['vehicle_no'] ? ' · 🛵 ' . esc($r['vehicle_no']) : '' ?></div>
  </div>
  <div class="r-badge" style="background:#F1F5F9;color:#475569">
    <?= ($r['employer_type'] ?? 'restaurant') === 'dinovix' ? '🛵 DinoviX fleet · ' : '' ?><?= $r['commission_type'] === 'percent' ? (float)$r['commission_value'] . '% per order' : '₹' . number_format((float)$r['commission_value'], 2) . ' per order' ?>
  </div>
  <div style="font-size:.7rem;color:var(--text-muted);font-weight:700"><?= (int)$r['delivered_count'] ?> deliveries</div>
  <div style="margin-left:auto;display:flex;gap:.5rem;align-items:center;flex-wrap:wrap">
    <div class="r-badge" style="background:#FFFBEB;color:#B45309">💵 holding ₹<?= number_format((float)$r['cod_in_hand'], 2) ?></div>
    <div class="r-badge" style="background:#ECFDF5;color:#047857">🏆 payable ₹<?= number_format((float)$r['payable'], 2) ?></div>
    <a class="btn btn-sm btn-outline" href="<?= base_url('admin/riders/wallet/' . $r['id']) ?>"><i class="fa fa-wallet"></i> Wallet</a>
    <form method="post" action="<?= base_url('admin/riders/toggle/' . $r['id']) ?>" style="display:inline">
      <?= csrf_field() ?>
      <button type="submit" class="btn btn-sm btn-outline" style="border-color:<?= (int)$r['user_active'] ? '#FCA5A5;color:#B91C1C' : '#86EFAC;color:#15803D' ?>">
        <i class="fa fa-<?= (int)$r['user_active'] ? 'pause' : 'play' ?>"></i> <?= (int)$r['user_active'] ? 'Pause' : 'Activate' ?>
      </button>
    </form>
  </div>
</div>
<?php endforeach; ?>

<script>
function copyApply(btn) {
  const inp = document.getElementById('applyLink');
  inp.select && inp.select();
  navigator.clipboard?.writeText(inp.value).then(() => {
    btn.innerHTML = '<i class="fa fa-check"></i> Copied!';
    btn.disabled = true;
    setTimeout(() => { btn.innerHTML = '<i class="fa fa-copy"></i> Copy link'; btn.disabled = false; }, 1800);
  });
}
</script>

<?php $this->endSection(); ?>
