<?php $this->extend('layouts/main'); $this->section('content'); ?>
<?php $sym = session('currency_symbol') ?? '₹'; ?>
<div style="padding:0 1rem">

  <!-- Stats -->
  <div class="stats-grid" style="margin-bottom:1rem">
    <div class="stat-card red">
      <div class="stat-icon red"><i class="fa fa-trash"></i></div>
      <div><div class="stat-value"><?= $sym ?><?= number_format($totalWasteCost) ?></div><div class="stat-label">Waste Cost</div></div>
    </div>
    <div class="stat-card orange">
      <div class="stat-icon orange"><i class="fa fa-list"></i></div>
      <div><div class="stat-value"><?= count($logs) ?></div><div class="stat-label">Log Entries</div></div>
    </div>
    <div class="stat-card">
      <div class="stat-icon"><i class="fa fa-ban"></i></div>
      <div><div class="stat-value"><?= count($cancelledItems) ?></div><div class="stat-label">Cancelled Items</div></div>
    </div>
  </div>

  <!-- Add Waste Log + Filters in one row -->
  <div style="display:flex;gap:.5rem;align-items:center;flex-wrap:wrap;margin-bottom:1rem">
    <form method="GET" style="display:flex;gap:.5rem;flex:1;flex-wrap:wrap">
      <input type="date" name="from" value="<?= $from ?>" class="form-control" style="width:140px">
      <input type="date" name="to"   value="<?= $to ?>"   class="form-control" style="width:140px">
      <button type="submit" class="btn btn-outline btn-sm"><i class="fa fa-filter"></i> Filter</button>
    </form>
    <button class="btn btn-primary btn-sm" onclick="openModal('wasteModal')"><i class="fa fa-plus"></i> Log Waste</button>
  </div>

  <!-- Waste by Reason -->
  <?php if (!empty($byReason)): ?>
  <div class="card" style="margin-bottom:1rem">
    <div class="card-header"><span class="card-title"><i class="fa fa-chart-pie" style="color:var(--primary)"></i> Waste by Reason</span></div>
    <div class="card-body">
      <?php $total = array_sum($byReason) ?: 1; ?>
      <?php foreach ($byReason as $reason => $cost): ?>
      <div style="margin-bottom:.625rem">
        <div style="display:flex;justify-content:space-between;font-size:.82rem;margin-bottom:.2rem">
          <span style="font-weight:600;text-transform:capitalize"><?= str_replace('_',' ', $reason) ?></span>
          <span style="font-weight:700"><?= $sym ?><?= number_format($cost) ?> (<?= round($cost/$total*100) ?>%)</span>
        </div>
        <div style="height:7px;background:var(--bg);border-radius:4px;overflow:hidden">
          <div style="height:100%;width:<?= round($cost/$total*100) ?>%;background:var(--danger);border-radius:4px;transition:width .6s"></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>

  <!-- Top Wasted Items -->
  <?php if (!empty($byItem)): ?>
  <div class="card" style="margin-bottom:1rem">
    <div class="card-header"><span class="card-title"><i class="fa fa-ranking-star" style="color:var(--primary)"></i> Most Wasted Items</span></div>
    <div class="table-wrap">
      <table class="table">
        <thead><tr><th>Item</th><th>Qty Wasted</th><th>Cost</th></tr></thead>
        <tbody>
          <?php foreach ($byItem as $it): ?>
          <tr>
            <td style="font-weight:600"><?= esc($it['item_name']) ?></td>
            <td><?= $it['qty'] ?></td>
            <td style="font-weight:700;color:var(--danger)"><?= $sym ?><?= number_format($it['cost'],2) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php endif; ?>

  <!-- Cancelled Item Revenue Loss -->
  <?php if (!empty($cancelledItems)): ?>
  <div class="card" style="margin-bottom:1rem">
    <div class="card-header"><span class="card-title"><i class="fa fa-circle-xmark" style="color:var(--danger)"></i> Revenue Lost from Cancellations</span></div>
    <div class="table-wrap">
      <table class="table">
        <thead><tr><th>Item</th><th>Qty Cancelled</th><th>Revenue Lost</th></tr></thead>
        <tbody>
          <?php foreach ($cancelledItems as $ci): ?>
          <tr>
            <td style="font-weight:600"><?= esc($ci['name']) ?></td>
            <td><?= $ci['qty'] ?></td>
            <td style="font-weight:700;color:var(--danger)"><?= $sym ?><?= number_format($ci['potential_loss'],2) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php endif; ?>

  <!-- Waste Log History -->
  <div class="card">
    <div class="card-header"><span class="card-title"><i class="fa fa-clock-rotate-left" style="color:var(--primary)"></i> Waste Log</span></div>
    <div class="table-wrap">
      <table class="table">
        <thead><tr><th>Date</th><th>Item</th><th>Qty</th><th>Reason</th><th>Cost</th><th>By</th></tr></thead>
        <tbody>
          <?php if (empty($logs)): ?>
          <tr><td colspan="6"><div class="empty-state" style="padding:2rem"><i class="fa fa-trash"></i><p>No waste logged</p></div></td></tr>
          <?php else: foreach ($logs as $l): ?>
          <tr>
            <td style="font-size:.8rem"><?= date('d M, h:i A', strtotime($l['created_at'])) ?></td>
            <td style="font-weight:600"><?= esc($l['item_name']) ?></td>
            <td><?= $l['quantity'] ?> <?= esc($l['unit']) ?></td>
            <td><span class="badge-pill badge-gray" style="font-size:.7rem"><?= str_replace('_',' ',$l['reason']) ?></span></td>
            <td style="color:var(--danger);font-weight:700"><?= $sym ?><?= number_format($l['cost'],2) ?></td>
            <td style="font-size:.8rem"><?= esc($l['logged_by_name'] ?? '—') ?></td>
          </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Add Waste Modal -->
<div class="modal-overlay" id="wasteModal">
  <div class="modal" style="max-width:420px">
    <div class="modal-header"><span class="modal-title">Log Food Waste</span><button class="modal-close" onclick="closeModal('wasteModal')"><i class="fa fa-times"></i></button></div>
    <div class="modal-body">
      <div class="form-group"><label class="form-label">Item Name *</label><input type="text" class="form-control" id="wItem" placeholder="e.g. Paneer Tikka, Bread rolls"></div>
      <div class="form-row cols-2">
        <div class="form-group"><label class="form-label">Quantity *</label><input type="number" class="form-control" id="wQty" min="0" step="0.5"></div>
        <div class="form-group"><label class="form-label">Unit</label>
          <select class="form-control" id="wUnit"><option>portion</option><option>kg</option><option>g</option><option>litre</option><option>piece</option><option>plate</option></select>
        </div>
      </div>
      <div class="form-row cols-2">
        <div class="form-group"><label class="form-label">Cost (₹)</label><input type="number" class="form-control" id="wCost" min="0" step="0.5" placeholder="0"></div>
        <div class="form-group"><label class="form-label">Reason *</label>
          <select class="form-control" id="wReason">
            <option value="spoiled">Spoiled</option><option value="over_cooked">Over Cooked</option>
            <option value="wrong_order">Wrong Order</option><option value="prep_error">Prep Error</option>
            <option value="expired">Expired</option><option value="other">Other</option>
          </select>
        </div>
      </div>
      <div class="form-group"><label class="form-label">Notes</label><input type="text" class="form-control" id="wNotes" placeholder="Optional details"></div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-outline" onclick="closeModal('wasteModal')">Cancel</button>
      <button class="btn btn-primary" onclick="logWaste()"><i class="fa fa-save"></i> Save</button>
    </div>
  </div>
</div>

<script>
function logWaste(){
  const item = document.getElementById('wItem').value.trim();
  if(!item){ alert('Enter item name'); return; }
  const body = new URLSearchParams({
    '<?= csrf_token() ?>':'<?= csrf_hash() ?>',
    item_name: item,
    quantity:  document.getElementById('wQty').value,
    unit:      document.getElementById('wUnit').value,
    cost:      document.getElementById('wCost').value,
    reason:    document.getElementById('wReason').value,
    notes:     document.getElementById('wNotes').value,
  });
  fetch('<?= base_url('admin/waste/store') ?>',{method:'POST',body})
    .then(r=>r.json()).then(d=>{ if(d.success){showToast('Waste logged','success');closeModal('wasteModal');setTimeout(()=>location.reload(),800);} });
}
</script>
<?php $this->endSection(); ?>
