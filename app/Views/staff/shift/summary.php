<?php $this->extend('layouts/main'); $this->section('content'); ?>
<div style="padding:0 1rem;max-width:500px">
  <div class="card" style="margin-bottom:1rem">
    <div class="card-header"><span class="card-title">📊 My Shift — <?= date('d M Y', strtotime($date)) ?></span></div>
    <div class="card-body">
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1rem">
        <div style="text-align:center;padding:1rem;background:var(--primary-light);border-radius:10px">
          <div style="font-size:1.5rem;font-weight:800;color:var(--primary)"><?= $summary['total_orders'] ?? 0 ?></div>
          <div style="font-size:.78rem;color:var(--text-muted)">Orders Taken</div>
        </div>
        <div style="text-align:center;padding:1rem;background:#F0FFF4;border-radius:10px">
          <div style="font-size:1.5rem;font-weight:800;color:var(--success)">₹<?= number_format($summary['total_revenue'] ?? 0) ?></div>
          <div style="font-size:.78rem;color:var(--text-muted)">Revenue</div>
        </div>
      </div>
      <div style="font-weight:700;margin-bottom:.5rem">Payment Breakdown</div>
      <?php if (empty($payments)): ?>
        <p style="color:var(--text-muted);font-size:.85rem">No payments yet today.</p>
      <?php else: foreach ($payments as $p): ?>
        <div style="display:flex;justify-content:space-between;padding:.4rem 0;border-bottom:1px solid var(--border);font-size:.875rem">
          <span><?= ucfirst(str_replace('_',' ',$p['payment_method'])) ?> (<?= $p['count'] ?>)</span>
          <strong>₹<?= number_format($p['total'],2) ?></strong>
        </div>
      <?php endforeach; endif; ?>
    </div>
  </div>
  <div class="card" style="margin-bottom:1rem">
    <div class="card-header"><span class="card-title" style="font-weight:700;<?= empty($openShift) ? 'color:var(--text-muted)' : 'color:var(--success)' ?>">Cash Drawer: <?= empty($openShift) ? 'CLOSED' : 'OPEN since ' . date('h:i A', strtotime($openShift['opened_at'])) ?></span></div>
    <div class="card-body">
      <?php if (empty($openShift)): ?>
        <form id="openShiftForm" style="display:flex;gap:.5rem;align-items:flex-end">
          <div class="form-group" style="margin:0;flex:1">
            <label class="form-label">Opening cash in drawer</label>
            <input type="number" step="0.01" min="0" class="form-control" name="opening_cash" id="openingCash" placeholder="0.00" required>
          </div>
          <button type="submit" class="btn btn-success"><i class="fa fa-unlock"></i> Open Shift</button>
        </form>
      <?php else: ?>
        <p style="font-size:.82rem;color:var(--text-muted);margin-bottom:.75rem">Opening cash: <strong>₹<?= number_format($openShift['opening_cash'],2) ?></strong></p>
        <form id="closeShiftForm" style="display:flex;gap:.5rem;align-items:flex-end">
          <div class="form-group" style="margin:0;flex:1">
            <label class="form-label">Counted cash now</label>
            <input type="number" step="0.01" min="0" class="form-control" name="counted_cash" id="countedCash" placeholder="0.00" required>
          </div>
          <button type="submit" class="btn btn-danger"><i class="fa fa-lock"></i> Close Shift</button>
        </form>
      <?php endif; ?>
    </div>
  </div>

  <?php if (!empty($shifts)): ?>
  <div class="card" style="margin-bottom:1rem">
    <div class="card-header"><span class="card-title">Recent Shifts</span></div>
    <div class="table-wrap">
      <table class="table">
        <thead><tr><th>Opened</th><th>Closed</th><th>Sales</th><th>Diff</th></tr></thead>
        <tbody>
          <?php foreach ($shifts as $s): ?>
          <tr style="font-size:.8rem">
            <td><?= date('d M h:i A', strtotime($s['opened_at'])) ?></td>
            <td><?= $s['closed_at'] ? date('d M h:i A', strtotime($s['closed_at'])) : '<span style="color:var(--success);font-weight:700">Open</span>' ?></td>
            <td>₹<?= number_format($s['total_sales'] ?? 0,2) ?> (<?= (int)$s['orders_count'] ?>)</td>
            <td style="color:<?= ($s['difference'] ?? 0) > 0 ? 'var(--success)' : ((float)($s['difference'] ?? 0) < 0 ? 'var(--danger)' : 'inherit') ?>">
              <?= $s['closed_at'] ? '₹' . number_format($s['difference'] ?? 0,2) : '—' ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php endif; ?>

  <a href="<?= base_url('pos') ?>" class="btn btn-primary btn-block"><i class="fa fa-cash-register"></i> Back to POS</a>
</div>

<script>
function csrf(){ return '<?= csrf_token() ?>=<?= csrf_hash() ?>'; }
document.getElementById('openShiftForm')?.addEventListener('submit', function(e){
  e.preventDefault();
  const c = document.getElementById('openingCash').value || 0;
  fetch('<?= base_url('pos/shift/open') ?>', { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body: csrf()+'&opening_cash='+c })
    .then(r=>r.json()).then(d=>{ if(d.success){ location.reload(); } else { alert(d.message||'Could not open the shift'); } })
    .catch(()=>alert('Network error'));
});
document.getElementById('closeShiftForm')?.addEventListener('submit', function(e){
  e.preventDefault();
  const c = document.getElementById('countedCash').value || 0;
  fetch('<?= base_url('pos/shift/close') ?>', { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body: csrf()+'&counted_cash='+c })
    .then(r=>r.json()).then(d=>{ if(d.success){ alert('Shift closed. Expected ₹'+(+d.expected).toFixed(2)+', counted ₹'+(+d.counted).toFixed(2)+' ('+d.status+')'); location.reload(); } else { alert(d.message||'Could not close the shift'); } })
    .catch(()=>alert('Network error'));
});
</script>
<?php $this->endSection(); ?>
