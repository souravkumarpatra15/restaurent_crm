<?php $this->extend('layouts/main'); $this->section('content'); ?>

<!-- Stats Grid -->
<div class="stats-grid" style="margin-bottom:1rem">
  <div class="stat-card green">
    <div class="stat-icon green"><i class="fa fa-store"></i></div>
    <div>
      <div class="stat-value"><?= number_format($stats['total_restaurants']) ?></div>
      <div class="stat-label">Total Restaurants</div>
      <div class="stat-change up"><i class="fa fa-arrow-up"></i> <?= $stats['new_this_month'] ?> this month</div>
    </div>
  </div>
  <div class="stat-card blue">
    <div class="stat-icon blue"><i class="fa fa-credit-card"></i></div>
    <div>
      <div class="stat-value"><?= $stats['active_subscriptions'] ?></div>
      <div class="stat-label">Active Subscriptions</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon"><i class="fa fa-indian-rupee-sign"></i></div>
    <div>
      <div class="stat-value">₹<?= number_format($stats['mrr']/1000,1) ?>K</div>
      <div class="stat-label">Monthly Revenue (MRR)</div>
    </div>
  </div>
  <div class="stat-card orange">
    <div class="stat-icon orange"><i class="fa fa-clock"></i></div>
    <div>
      <div class="stat-value"><?= $stats['trials'] ?></div>
      <div class="stat-label">Trial Accounts</div>
    </div>
  </div>
</div>

<!-- Quick Actions -->
<div style="display:flex;gap:.75rem;flex-wrap:wrap;margin-bottom:1.25rem">
  <a href="<?= base_url('super/restaurants/create') ?>" class="btn btn-primary"><i class="fa fa-plus"></i> New Restaurant</a>
  <a href="<?= base_url('super/plans') ?>" class="btn btn-outline"><i class="fa fa-layer-group"></i> Manage Plans</a>
  <a href="<?= base_url('super/reports/revenue') ?>" class="btn btn-outline"><i class="fa fa-chart-line"></i> Revenue</a>
</div>

<div style="display:grid;gap:1rem;grid-template-columns:1fr">

  <!-- Recent Restaurants -->
  <div class="card">
    <div class="card-header">
      <span class="card-title"><i class="fa fa-store" style="color:var(--primary)"></i> Restaurants</span>
      <a href="<?= base_url('super/restaurants') ?>" class="btn btn-sm btn-outline">View All</a>
    </div>
    <div class="table-wrap">
      <table class="table">
        <thead>
          <tr>
            <th>Restaurant</th>
            <th>Type</th>
            <th>Plan</th>
            <th>Status</th>
            <th>Branches</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($restaurants)): ?>
          <tr><td colspan="6"><div class="empty-state"><i class="fa fa-store"></i><p>No restaurants yet</p></div></td></tr>
          <?php else: ?>
          <?php foreach ($restaurants as $r): ?>
          <tr>
            <td>
              <div style="display:flex;align-items:center;gap:.625rem">
                <?php if ($r['logo']): ?>
                  <img src="<?= base_url('public/images/uploads/'.$r['logo']) ?>" style="width:32px;height:32px;border-radius:50%;object-fit:cover">
                <?php else: ?>
                  <div style="width:32px;height:32px;border-radius:50%;background:var(--primary-light);color:var(--primary);display:flex;align-items:center;justify-content:center;font-weight:800;font-size:.85rem"><?= substr($r['name'],0,1) ?></div>
                <?php endif; ?>
                <div>
                  <div style="font-weight:600;font-size:.875rem"><?= esc($r['name']) ?></div>
                  <div style="font-size:.72rem;color:var(--text-muted)"><?= esc($r['email']) ?></div>
                </div>
              </div>
            </td>
            <td><span class="badge-pill badge-gray"><?= ucfirst(str_replace('_',' ',$r['restaurant_type'])) ?></span></td>
            <td><span class="badge-pill badge-primary"><?= $r['plan_name'] ?></span></td>
            <td>
              <?php $statusColors = ['active'=>'success','trial'=>'warning','suspended'=>'danger','expired'=>'danger','cancelled'=>'gray']; ?>
              <span class="badge-pill badge-<?= $statusColors[$r['subscription_status']] ?? 'gray' ?>">
                <?= ucfirst($r['subscription_status']) ?>
              </span>
            </td>
            <td style="text-align:center"><?= $r['branch_count'] ?? 0 ?></td>
            <td>
              <div style="display:flex;gap:.3rem">
                <a href="<?= base_url('super/restaurants/view/'.$r['id']) ?>" class="btn btn-sm btn-outline" title="View"><i class="fa fa-eye"></i></a>
                <a href="<?= base_url('super/restaurants/edit/'.$r['id']) ?>" class="btn btn-sm btn-outline" title="Edit"><i class="fa fa-edit"></i></a>
                <button onclick="loginAs(<?= $r['id'] ?>)" class="btn btn-sm btn-outline" title="Login As"><i class="fa fa-right-to-bracket"></i></button>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Plan Distribution -->
  <div class="card">
    <div class="card-header">
      <span class="card-title"><i class="fa fa-chart-pie" style="color:var(--primary)"></i> Plan Distribution</span>
    </div>
    <div class="card-body">
      <?php foreach ($planStats as $plan): ?>
      <div style="display:flex;align-items:center;gap:.75rem;margin-bottom:.75rem">
        <div style="flex:1">
          <div style="display:flex;justify-content:space-between;margin-bottom:.25rem">
            <span style="font-size:.82rem;font-weight:600"><?= $plan['plan_name'] ?></span>
            <span style="font-size:.82rem;color:var(--text-muted)"><?= $plan['count'] ?> (<?= $plan['percent'] ?>%)</span>
          </div>
          <div style="height:6px;background:var(--bg);border-radius:3px;overflow:hidden">
            <div style="height:100%;width:<?= $plan['percent'] ?>%;background:var(--primary);border-radius:3px;transition:width .6s ease"></div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Expiring Soon -->
  <?php if (!empty($expiringSoon)): ?>
  <div class="card">
    <div class="card-header">
      <span class="card-title"><i class="fa fa-triangle-exclamation" style="color:var(--warning)"></i> Expiring Soon (7 days)</span>
    </div>
    <div class="card-body" style="padding:.5rem">
      <?php foreach ($expiringSoon as $r): ?>
      <div style="display:flex;justify-content:space-between;align-items:center;padding:.5rem .75rem;border-bottom:1px solid var(--border)">
        <div>
          <div style="font-weight:600;font-size:.85rem"><?= esc($r['name']) ?></div>
          <div style="font-size:.72rem;color:var(--text-muted)">Expires: <?= date('d M Y', strtotime($r['subscription_ends_at'])) ?></div>
        </div>
        <button onclick="sendRenewalEmail(<?= $r['id'] ?>)" class="btn btn-sm btn-warning"><i class="fa fa-envelope"></i> Remind</button>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>

</div>

<script>
function loginAs(id) {
  if (!confirm('Login as this restaurant admin?')) return;
  const form = document.createElement('form');
  form.method = 'POST';
  form.action = '<?= base_url('super/restaurants/login-as/') ?>' + id;
  form.innerHTML = '<input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>">';
  document.body.appendChild(form);
  form.submit();
}

function sendRenewalEmail(id) {
  fetch('<?= base_url('super/subscriptions/remind/') ?>' + id, {
    method: 'POST',
    headers: { 'X-CSRF-TOKEN': '<?= csrf_hash() ?>' }
  }).then(r => r.json()).then(d => {
    alert(d.success ? 'Reminder sent!' : 'Failed to send');
  });
}
</script>

<?php $this->endSection(); ?>
