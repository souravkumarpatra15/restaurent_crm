<?php $this->extend('layouts/main'); $this->section('content'); ?>
<?php $sym = session('currency_symbol') ?? '₹'; ?>
<div style="padding:0 1rem;max-width:680px">

  <?php if ($alreadyClosed): ?>
  <div class="alert alert-success" style="margin-bottom:1rem"><i class="fa fa-check-circle"></i> Day already closed for <?= $today ?>. Showing today's summary.</div>
  <?php endif; ?>

  <!-- Today's Summary Cards -->
  <div class="stats-grid" style="margin-bottom:1rem">
    <div class="stat-card green">
      <div class="stat-icon green"><i class="fa fa-indian-rupee-sign"></i></div>
      <div><div class="stat-value"><?= $sym ?><?= number_format($totalRevenue) ?></div><div class="stat-label">Total Revenue</div></div>
    </div>
    <div class="stat-card">
      <div class="stat-icon"><i class="fa fa-receipt"></i></div>
      <div><div class="stat-value"><?= $totalOrders ?></div><div class="stat-label">Orders</div></div>
    </div>
    <div class="stat-card orange">
      <div class="stat-icon orange"><i class="fa fa-wallet"></i></div>
      <div><div class="stat-value"><?= $sym ?><?= number_format($cashBalance) ?></div><div class="stat-label">Cash in Hand</div></div>
    </div>
    <div class="stat-card <?= $netProfit >= 0 ? 'green' : 'red' ?>">
      <div class="stat-icon <?= $netProfit >= 0 ? 'green' : 'red' ?>"><i class="fa fa-chart-line"></i></div>
      <div><div class="stat-value"><?= $sym ?><?= number_format($netProfit) ?></div><div class="stat-label">Net (Revenue−Exp)</div></div>
    </div>
  </div>

  <!-- Payment Method Breakdown -->
  <div class="card" style="margin-bottom:1rem">
    <div class="card-header"><span class="card-title"><i class="fa fa-credit-card" style="color:var(--primary)"></i> Payment Breakdown</span></div>
    <div class="card-body">
      <?php foreach ($payments as $p): ?>
      <div style="display:flex;align-items:center;gap:.75rem;padding:.4rem 0;border-bottom:1px solid var(--border)">
        <div style="width:36px;height:36px;border-radius:8px;background:var(--primary-light);color:var(--primary);display:flex;align-items:center;justify-content:center;font-size:.9rem">
          <?php $picons=['cash'=>'fa-money-bill-wave','card'=>'fa-credit-card','upi'=>'fa-mobile-screen','wallet'=>'fa-wallet','online'=>'fa-globe']; ?>
          <i class="fa <?= $picons[$p['payment_method']] ?? 'fa-circle-dollar-to-slot' ?>"></i>
        </div>
        <div style="flex:1">
          <div style="font-weight:700;font-size:.875rem"><?= ucfirst(str_replace('_',' ',$p['payment_method'])) ?></div>
          <div style="font-size:.72rem;color:var(--text-muted)"><?= $p['count'] ?> transactions</div>
        </div>
        <div style="font-weight:900;font-size:.95rem;color:var(--primary)"><?= $sym ?><?= number_format($p['total'],2) ?></div>
      </div>
      <?php endforeach; ?>
      <?php if (empty($payments)): ?>
      <div style="text-align:center;padding:1rem;color:var(--text-muted)">No payments today</div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Top Items -->
  <div class="card" style="margin-bottom:1rem">
    <div class="card-header"><span class="card-title"><i class="fa fa-fire" style="color:var(--primary)"></i> Top Items Today</span></div>
    <div class="card-body">
      <?php foreach ($topItems as $i => $item): ?>
      <div style="display:flex;align-items:center;gap:.625rem;padding:.35rem 0;border-bottom:1px solid var(--border)">
        <div style="width:24px;height:24px;border-radius:50%;background:var(--primary);color:#fff;display:flex;align-items:center;justify-content:center;font-size:.7rem;font-weight:800;flex-shrink:0"><?= $i+1 ?></div>
        <div style="flex:1;font-size:.85rem;font-weight:600"><?= esc($item['name']) ?></div>
        <span class="badge-pill badge-primary"><?= $item['qty'] ?> sold</span>
        <div style="font-size:.82rem;font-weight:700"><?= $sym ?><?= number_format($item['revenue']) ?></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Cash Reconciliation Form -->
  <?php if (!$alreadyClosed): ?>
  <div class="card" style="margin-bottom:1rem">
    <div class="card-header">
      <span class="card-title"><i class="fa fa-scale-balanced" style="color:var(--primary)"></i> Cash Reconciliation</span>
    </div>
    <div class="card-body">
      <div style="background:var(--bg);border-radius:var(--radius);padding:.875rem;margin-bottom:1rem">
        <div style="display:flex;justify-content:space-between;font-size:.85rem;margin-bottom:.3rem">
          <span style="color:var(--text-muted)">Cash Sales Today</span>
          <strong><?= $sym ?><?= number_format($cashIn,2) ?></strong>
        </div>
        <div style="display:flex;justify-content:space-between;font-size:.85rem;margin-bottom:.3rem">
          <span style="color:var(--text-muted)">Cash Expenses</span>
          <strong style="color:var(--danger)">-<?= $sym ?><?= number_format($cashExp,2) ?></strong>
        </div>
        <div style="display:flex;justify-content:space-between;font-size:.95rem;font-weight:900;border-top:1px solid var(--border);padding-top:.4rem;margin-top:.3rem">
          <span>Expected Cash in Drawer</span>
          <span style="color:var(--primary)"><?= $sym ?><?= number_format($cashBalance,2) ?></span>
        </div>
      </div>
      <div class="form-group" style="margin-bottom:.75rem">
        <label class="form-label">Actual Cash Counted (₹)</label>
        <input type="number" class="form-control" id="cashActual" step="0.01" placeholder="Enter actual cash in drawer" oninput="calcDiff()">
      </div>
      <div id="diffResult" style="display:none;padding:.75rem;border-radius:var(--radius);margin-bottom:.75rem;font-weight:700;text-align:center"></div>
      <div class="form-group" style="margin-bottom:.875rem">
        <label class="form-label">Notes / Remarks</label>
        <textarea class="form-control" id="closeNotes" rows="2" placeholder="Any remarks about today's operations..."></textarea>
      </div>
      <button onclick="closeDay()" class="btn btn-primary" style="width:100%"><i class="fa fa-lock"></i> Close Day & Save</button>
    </div>
  </div>
  <?php endif; ?>

</div>
<script>
const expected = <?= $cashBalance ?>;
const SYM = '<?= $sym ?>';

function calcDiff() {
  const actual = parseFloat(document.getElementById('cashActual').value) || 0;
  const diff   = actual - expected;
  const el     = document.getElementById('diffResult');
  el.style.display = '';
  if (Math.abs(diff) < 1) {
    el.style.background = '#F0FFF4'; el.style.color = 'var(--success)';
    el.textContent = '✅ Balanced! Great job.';
  } else if (diff > 0) {
    el.style.background = '#FFFBEB'; el.style.color = 'var(--warning)';
    el.textContent = `⚠️ Surplus of ${SYM}${diff.toFixed(2)} — check for unrecorded expenses`;
  } else {
    el.style.background = '#FFF5F5'; el.style.color = 'var(--danger)';
    el.textContent = `❌ Shortage of ${SYM}${Math.abs(diff).toFixed(2)} — investigate before closing`;
  }
}

function closeDay() {
  const actual = parseFloat(document.getElementById('cashActual').value);
  if (isNaN(actual)) { alert('Enter actual cash amount'); return; }
  if (!confirm('Close today and save cash reconciliation?')) return;
  fetch('<?= base_url('admin/smart-close/close') ?>', {
    method:'POST',
    headers:{'Content-Type':'application/x-www-form-urlencoded'},
    body:`<?= csrf_token() ?>=<?= csrf_hash() ?>&cash_in_hand=${actual}&notes=${encodeURIComponent(document.getElementById('closeNotes').value)}`
  }).then(r=>r.json()).then(d=>{
    if (d.success) {
      const msg = d.status==='balanced' ? '✅ Day closed — cash balanced!' :
                  d.status==='surplus'  ? `⚠️ Day closed — surplus ₹${Math.abs(d.difference).toFixed(2)}` :
                                          `❌ Day closed — shortage ₹${Math.abs(d.difference).toFixed(2)}`;
      alert(msg);
      location.reload();
    }
  });
}
</script>
<?php $this->endSection(); ?>
