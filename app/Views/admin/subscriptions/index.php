<?php $this->extend('layouts/main'); $this->section('content'); ?>
<div style="padding:0 1rem">

  <!-- Stats -->
  <div class="stats-grid" style="margin-bottom:1rem">
    <div class="stat-card green"><div class="stat-icon green"><i class="fa fa-check-circle"></i></div>
      <div><div class="stat-value"><?= $stats['active'] ?></div><div class="stat-label">Active</div></div></div>
    <div class="stat-card orange"><div class="stat-icon orange"><i class="fa fa-clock"></i></div>
      <div><div class="stat-value"><?= $stats['trial'] ?></div><div class="stat-label">Trial</div></div></div>
    <div class="stat-card red"><div class="stat-icon red"><i class="fa fa-ban"></i></div>
      <div><div class="stat-value"><?= $stats['expired'] + $stats['suspended'] ?></div><div class="stat-label">Inactive</div></div></div>
    <div class="stat-card"><div class="stat-icon"><i class="fa fa-indian-rupee-sign"></i></div>
      <div><div class="stat-value">₹<?= number_format($stats['mrr']) ?></div><div class="stat-label">MRR</div></div></div>
  </div>

  <!-- Expiring Soon Alert -->
  <?php if (!empty($expiring)): ?>
  <div class="alert alert-warning" style="margin-bottom:1rem">
    <i class="fa fa-triangle-exclamation"></i>
    <strong><?= count($expiring) ?> subscription<?= count($expiring)>1?'s':'' ?></strong> expiring within 7 days
  </div>
  <?php endif; ?>

  <!-- Filter Tabs -->
  <div style="display:flex;gap:.4rem;margin-bottom:1rem;overflow-x:auto;scrollbar-width:none">
    <?php foreach (['' =>'All', 'active'=>'Active','trial'=>'Trial','expired'=>'Expired','suspended'=>'Suspended','cancelled'=>'Cancelled'] as $k=>$v): ?>
    <a href="?status=<?= $k ?>" class="btn btn-sm <?= $filter===$k ? 'btn-primary' : 'btn-outline' ?>" style="flex-shrink:0"><?= $v ?></a>
    <?php endforeach; ?>
  </div>

  <!-- Subscription Table -->
  <div class="card">
    <div class="card-header">
      <span class="card-title">Subscriptions (<?= count($subs) ?>)</span>
    </div>
    <div class="table-wrap">
      <table class="table">
        <thead>
          <tr><th>Restaurant</th><th>Plan</th><th>Cycle</th><th>Status</th><th>Expires</th><th>Actions</th></tr>
        </thead>
        <tbody>
          <?php if (empty($subs)): ?>
          <tr><td colspan="6"><div class="empty-state" style="padding:2rem"><i class="fa fa-credit-card"></i><p>No subscriptions found</p></div></td></tr>
          <?php else: foreach ($subs as $s):
            $sc = ['active'=>'success','trial'=>'warning','expired'=>'danger','suspended'=>'danger','cancelled'=>'gray'];
            $daysLeft = $s['subscription_ends_at'] ? ceil((strtotime($s['subscription_ends_at']) - time()) / 86400) : null;
          ?>
          <tr>
            <td>
              <div style="font-weight:600;font-size:.875rem"><?= esc($s['name']) ?></div>
              <div style="font-size:.72rem;color:var(--text-muted)"><?= esc($s['email']) ?></div>
            </td>
            <td><span class="badge-pill badge-primary"><?= esc($s['plan_name'] ?? 'N/A') ?></span></td>
            <td style="font-size:.82rem"><?= ucfirst($s['billing_cycle'] ?? '-') ?></td>
            <td>
              <span class="badge-pill badge-<?= $sc[$s['subscription_status']] ?? 'gray' ?>">
                <?= ucfirst($s['subscription_status']) ?>
              </span>
              <?php if ($daysLeft !== null && $daysLeft <= 7 && $daysLeft >= 0 && $s['subscription_status']==='active'): ?>
              <span class="badge-pill badge-danger" style="font-size:.65rem;margin-left:.25rem"><?= $daysLeft ?>d left</span>
              <?php endif; ?>
            </td>
            <td style="font-size:.8rem;white-space:nowrap">
              <?= $s['subscription_ends_at'] ? date('d M Y', strtotime($s['subscription_ends_at'])) : '-' ?>
            </td>
            <td>
              <div style="display:flex;gap:.3rem">
                <button onclick="openManageModal(<?= htmlspecialchars(json_encode($s), ENT_QUOTES) ?>)"
                        class="btn btn-sm btn-primary" title="Manage">
                  <i class="fa fa-gear"></i>
                </button>
                <?php if ($s['subscription_status'] === 'active'): ?>
                <button onclick="suspend(<?= $s['id'] ?>)" class="btn btn-sm btn-outline" style="color:var(--danger)" title="Suspend">
                  <i class="fa fa-ban"></i>
                </button>
                <?php elseif (in_array($s['subscription_status'],['suspended','expired','cancelled'])): ?>
                <button onclick="activate(<?= $s['id'] ?>)" class="btn btn-sm btn-outline" style="color:var(--success)" title="Activate">
                  <i class="fa fa-check"></i>
                </button>
                <?php endif; ?>
                <form method="POST" action="<?= base_url('super/restaurants/login-as/'.$s['id']) ?>" style="margin:0">
                  <?= csrf_field() ?>
                  <button type="submit" class="btn btn-sm btn-outline" title="Login As Admin"><i class="fa fa-right-to-bracket"></i></button>
                </form>
              </div>
            </td>
          </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Manage Subscription Modal -->
<div class="modal-overlay" id="manageModal">
  <div class="modal" style="max-width:480px">
    <div class="modal-header">
      <span class="modal-title" id="manageModalTitle">Manage Subscription</span>
      <button class="modal-close" onclick="closeModal('manageModal')"><i class="fa fa-times"></i></button>
    </div>
    <div class="modal-body">
      <input type="hidden" id="mgRestId">
      <div class="form-group">
        <label class="form-label">Plan</label>
        <select class="form-control" id="mgPlan">
          <?php foreach ($plans as $p): ?>
          <option value="<?= $p['id'] ?>">
            <?= esc($p['name']) ?> — ₹<?= number_format($p['price_monthly']) ?>/mo · ₹<?= number_format($p['price_yearly']) ?>/yr
          </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-row cols-2">
        <div class="form-group">
          <label class="form-label">Status</label>
          <select class="form-control" id="mgStatus">
            <option value="active">Active</option>
            <option value="trial">Trial</option>
            <option value="suspended">Suspended</option>
            <option value="expired">Expired</option>
            <option value="cancelled">Cancelled</option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Billing Cycle</label>
          <select class="form-control" id="mgCycle">
            <option value="monthly">Monthly</option>
            <option value="yearly">Yearly</option>
          </select>
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Subscription End Date</label>
        <input type="date" class="form-control" id="mgEnds">
      </div>
      <div style="background:var(--bg);border-radius:8px;padding:.75rem;font-size:.82rem;color:var(--text-muted)">
        <i class="fa fa-info-circle"></i> Setting status to <strong>Active</strong> will automatically log a payment record.
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-outline" onclick="closeModal('manageModal')">Cancel</button>
      <button class="btn btn-primary" onclick="saveManage()"><i class="fa fa-save"></i> Save Changes</button>
    </div>
  </div>
</div>

<script>
function openManageModal(s) {
  document.getElementById('mgRestId').value = s.id;
  document.getElementById('manageModalTitle').textContent = 'Manage — ' + s.name;
  document.getElementById('mgPlan').value   = s.plan_id;
  document.getElementById('mgStatus').value = s.subscription_status;
  document.getElementById('mgCycle').value  = s.billing_cycle || 'monthly';
  document.getElementById('mgEnds').value   = s.subscription_ends_at ? s.subscription_ends_at.substring(0,10) : '';
  openModal('manageModal');
}

function saveManage() {
  const id = document.getElementById('mgRestId').value;
  fetch('<?= base_url('super/subscriptions/change-plan/') ?>' + id, {
    method:'POST',
    headers:{'Content-Type':'application/x-www-form-urlencoded'},
    body: '<?= csrf_token() ?>=<?= csrf_hash() ?>'
      + '&plan_id='           + document.getElementById('mgPlan').value
      + '&subscription_status='+ document.getElementById('mgStatus').value
      + '&billing_cycle='     + document.getElementById('mgCycle').value
      + '&subscription_ends_at='+ document.getElementById('mgEnds').value
  }).then(r=>r.json()).then(d=>{
    if (d.success) { showToast('Subscription updated','success'); closeModal('manageModal'); setTimeout(()=>location.reload(),800); }
    else showToast('Failed','error');
  });
}

function suspend(id) {
  if (!confirm('Suspend this subscription? They will lose access.')) return;
  fetch('<?= base_url('super/subscriptions/suspend/') ?>' + id, {
    method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'},
    body:'<?= csrf_token() ?>=<?= csrf_hash() ?>'
  }).then(r=>r.json()).then(d=>{ if(d.success){showToast('Suspended','success');setTimeout(()=>location.reload(),800);} });
}

function activate(id) {
  fetch('<?= base_url('super/subscriptions/activate/') ?>' + id, {
    method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'},
    body:'<?= csrf_token() ?>=<?= csrf_hash() ?>'
  }).then(r=>r.json()).then(d=>{ if(d.success){showToast('Activated','success');setTimeout(()=>location.reload(),800);} });
}
</script>
<?php $this->endSection(); ?>
