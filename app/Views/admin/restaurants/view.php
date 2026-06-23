<?php $this->extend('layouts/main'); $this->section('content'); ?>
<div style="padding:0 1rem">

  <!-- Header Actions -->
  <div style="display:flex;gap:.5rem;margin-bottom:1rem;flex-wrap:wrap">
    <a href="<?= base_url('super/restaurants') ?>" class="btn btn-outline btn-sm"><i class="fa fa-arrow-left"></i> Back</a>
    <a href="<?= base_url('super/restaurants/edit/'.$restaurant['id']) ?>" class="btn btn-outline btn-sm"><i class="fa fa-edit"></i> Edit</a>
    <form method="POST" action="<?= base_url('super/restaurants/login-as/'.$restaurant['id']) ?>" style="margin:0">
      <?= csrf_field() ?>
      <button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-right-to-bracket"></i> Login as Admin</button>
    </form>
    <button onclick="toggleRestaurant(<?= $restaurant['id'] ?>, <?= $restaurant['is_active'] ?>)"
            class="btn btn-sm btn-<?= $restaurant['is_active'] ? 'outline' : 'success' ?>">
      <i class="fa fa-<?= $restaurant['is_active'] ? 'ban' : 'check' ?>"></i>
      <?= $restaurant['is_active'] ? 'Disable' : 'Enable' ?>
    </button>
  </div>

  <!-- Info Card -->
  <div class="card" style="margin-bottom:1rem">
    <div class="card-body">
      <div style="display:flex;align-items:center;gap:1rem;margin-bottom:1rem">
        <div style="width:60px;height:60px;border-radius:14px;background:var(--primary-light);color:var(--primary);display:flex;align-items:center;justify-content:center;font-size:1.75rem;font-weight:800;flex-shrink:0">
          <?= substr($restaurant['name'],0,1) ?>
        </div>
        <div>
          <h2 style="font-size:1.15rem;font-weight:800"><?= esc($restaurant['name']) ?></h2>
          <div style="font-size:.8rem;color:var(--text-muted)"><?= ucfirst(str_replace('_',' ',$restaurant['restaurant_type'])) ?> · <?= esc($restaurant['city'] ?? '') ?></div>
          <div style="display:flex;gap:.4rem;margin-top:.35rem;flex-wrap:wrap">
            <span class="badge-pill badge-primary"><?= esc($restaurant['plan_name'] ?? 'N/A') ?></span>
            <?php $sc=['active'=>'success','trial'=>'warning','suspended'=>'danger','expired'=>'danger','cancelled'=>'gray']; ?>
            <span class="badge-pill badge-<?= $sc[$restaurant['subscription_status']] ?? 'gray' ?>"><?= ucfirst($restaurant['subscription_status']) ?></span>
          </div>
        </div>
      </div>
      <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:.75rem;margin-bottom:1rem">
        <?php foreach (['orders'=>['receipt','Orders'],'revenue'=>['indian-rupee-sign','Revenue'],'customers'=>['users','Customers'],'menu'=>['utensils','Menu Items']] as $key=>[$icon,$label]): ?>
        <div style="text-align:center;padding:.75rem;background:var(--bg);border-radius:10px">
          <div style="font-weight:800;font-size:1.1rem;color:var(--primary)">
            <?= $key === 'revenue' ? '₹'.number_format($stats[$key]) : number_format($stats[$key]) ?>
          </div>
          <div style="font-size:.72rem;color:var(--text-muted)"><?= $label ?></div>
        </div>
        <?php endforeach; ?>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:.5rem;font-size:.82rem">
        <div><span style="color:var(--text-muted)">Email:</span> <?= esc($restaurant['email']) ?></div>
        <div><span style="color:var(--text-muted)">Phone:</span> <?= esc($restaurant['phone'] ?? '-') ?></div>
        <div><span style="color:var(--text-muted)">GST:</span> <?= esc($restaurant['gst_number'] ?? '-') ?></div>
        <div><span style="color:var(--text-muted)">Billing:</span> <?= ucfirst($restaurant['billing_cycle'] ?? '-') ?></div>
        <div><span style="color:var(--text-muted)">Expires:</span> <?= $restaurant['subscription_ends_at'] ? date('d M Y',strtotime($restaurant['subscription_ends_at'])) : '-' ?></div>
        <div><span style="color:var(--text-muted)">Created:</span> <?= date('d M Y',strtotime($restaurant['created_at'])) ?></div>
      </div>
    </div>
  </div>

  <!-- Branches -->
  <div class="card" style="margin-bottom:1rem">
    <div class="card-header"><span class="card-title"><i class="fa fa-code-branch" style="color:var(--primary)"></i> Branches (<?= count($branches) ?>)</span></div>
    <div class="table-wrap">
      <table class="table">
        <thead><tr><th>Name</th><th>City</th><th>Type</th><th>Status</th></tr></thead>
        <tbody>
          <?php foreach ($branches as $b): ?>
          <tr>
            <td><div style="font-weight:600"><?= esc($b['name']) ?></div><div style="font-size:.72rem;color:var(--text-muted)"><?= esc($b['phone'] ?? '') ?></div></td>
            <td><?= esc($b['city'] ?? '-') ?></td>
            <td><?= ucfirst($b['branch_type'] ?? '-') ?></td>
            <td><span class="badge-pill badge-<?= $b['is_active']?'success':'danger' ?>"><?= $b['is_active']?'Active':'Inactive' ?></span></td>
          </tr>
          <?php endforeach; ?>
          <?php if (empty($branches)): ?><tr><td colspan="4"><div class="empty-state" style="padding:1rem"><i class="fa fa-code-branch"></i><p>No branches</p></div></td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Staff -->
  <div class="card">
    <div class="card-header"><span class="card-title"><i class="fa fa-users" style="color:var(--primary)"></i> Staff (<?= count($users) ?>)</span></div>
    <div class="table-wrap">
      <table class="table">
        <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th></tr></thead>
        <tbody>
          <?php foreach ($users as $u): ?>
          <tr>
            <td style="font-weight:600"><?= esc($u['name']) ?></td>
            <td style="font-size:.82rem"><?= esc($u['email']) ?></td>
            <td><span class="badge-pill badge-gray"><?= esc($u['role_name'] ?? '-') ?></span></td>
            <td><span class="badge-pill badge-<?= $u['is_active']?'success':'danger' ?>"><?= $u['is_active']?'Active':'Inactive' ?></span></td>
          </tr>
          <?php endforeach; ?>
          <?php if (empty($users)): ?><tr><td colspan="4"><div class="empty-state" style="padding:1rem"><i class="fa fa-users"></i><p>No staff yet</p></div></td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script>
function toggleRestaurant(id, current) {
  if (!confirm(current ? 'Disable this restaurant?' : 'Enable this restaurant?')) return;
  fetch('<?= base_url('super/restaurants/toggle/') ?>' + id, {
    method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'},
    body:'<?= csrf_token() ?>=<?= csrf_hash() ?>'
  }).then(r=>r.json()).then(()=>location.reload());
}
</script>
<?php $this->endSection(); ?>
