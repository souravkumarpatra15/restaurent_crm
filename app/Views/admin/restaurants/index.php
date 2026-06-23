<?php $this->extend('layouts/main'); $this->section('content'); ?>
<div style="padding:0 1rem">

  <!-- Filter Bar -->
  <div style="display:flex;gap:.5rem;align-items:center;margin-bottom:1rem;flex-wrap:wrap">
    <div style="position:relative;flex:1;min-width:180px">
      <i class="fa fa-search" style="position:absolute;left:.75rem;top:50%;transform:translateY(-50%);color:var(--text-muted)"></i>
      <input type="text" id="restSearch" class="form-control" placeholder="Search restaurants..." style="padding-left:2.25rem">
    </div>
    <select id="statusFilter" class="form-control" style="width:130px" onchange="filterTable()">
      <option value="">All Status</option>
      <option value="active">Active</option>
      <option value="trial">Trial</option>
      <option value="suspended">Suspended</option>
      <option value="expired">Expired</option>
    </select>
    <a href="<?= base_url('super/restaurants/create') ?>" class="btn btn-primary" style="flex-shrink:0">
      <i class="fa fa-plus"></i> Add Restaurant
    </a>
  </div>

  <!-- Stats Row -->
  <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:.5rem;margin-bottom:1rem">
    <?php
    $total    = count($restaurants);
    $active   = count(array_filter($restaurants, fn($r) => $r['subscription_status']==='active'));
    $trial    = count(array_filter($restaurants, fn($r) => $r['subscription_status']==='trial'));
    $inactive = $total - $active - $trial;
    ?>
    <div style="background:#fff;border-radius:10px;padding:.75rem;text-align:center;box-shadow:var(--shadow)">
      <div style="font-weight:800;font-size:1.25rem"><?= $total ?></div><div style="font-size:.7rem;color:var(--text-muted)">Total</div></div>
    <div style="background:#F0FFF4;border-radius:10px;padding:.75rem;text-align:center;box-shadow:var(--shadow)">
      <div style="font-weight:800;font-size:1.25rem;color:var(--success)"><?= $active ?></div><div style="font-size:.7rem;color:var(--text-muted)">Active</div></div>
    <div style="background:#FFFBEB;border-radius:10px;padding:.75rem;text-align:center;box-shadow:var(--shadow)">
      <div style="font-weight:800;font-size:1.25rem;color:var(--warning)"><?= $trial ?></div><div style="font-size:.7rem;color:var(--text-muted)">Trial</div></div>
    <div style="background:#FFF5F5;border-radius:10px;padding:.75rem;text-align:center;box-shadow:var(--shadow)">
      <div style="font-weight:800;font-size:1.25rem;color:var(--danger)"><?= $inactive ?></div><div style="font-size:.7rem;color:var(--text-muted)">Inactive</div></div>
  </div>

  <div class="card">
    <div class="table-wrap">
      <table class="table" id="restTable">
        <thead>
          <tr><th>Restaurant</th><th>Type</th><th>Plan</th><th>Status</th><th>Branches</th><th>Users</th><th>Actions</th></tr>
        </thead>
        <tbody>
          <?php if (empty($restaurants)): ?>
          <tr><td colspan="7">
            <div class="empty-state" style="padding:3rem">
              <i class="fa fa-store"></i>
              <p>No restaurants yet. <a href="<?= base_url('super/restaurants/create') ?>">Add the first one</a></p>
            </div>
          </td></tr>
          <?php else: foreach ($restaurants as $r): ?>
          <?php $sc=['active'=>'success','trial'=>'warning','suspended'=>'danger','expired'=>'danger','cancelled'=>'gray']; ?>
          <tr class="rest-row" data-name="<?= strtolower(esc($r['name'])) ?>" data-status="<?= $r['subscription_status'] ?>">
            <td>
              <div style="display:flex;align-items:center;gap:.6rem">
                <div style="width:36px;height:36px;border-radius:8px;background:var(--primary-light);color:var(--primary);display:flex;align-items:center;justify-content:center;font-weight:800;flex-shrink:0">
                  <?= strtoupper(substr($r['name'],0,1)) ?>
                </div>
                <div>
                  <div style="font-weight:700;font-size:.875rem"><?= esc($r['name']) ?></div>
                  <div style="font-size:.72rem;color:var(--text-muted)"><?= esc($r['email']) ?></div>
                </div>
              </div>
            </td>
            <td style="font-size:.82rem"><?= ucfirst(str_replace('_',' ',$r['restaurant_type'] ?? '')) ?></td>
            <td><span class="badge-pill badge-primary"><?= esc($r['plan_name'] ?? 'N/A') ?></span></td>
            <td><span class="badge-pill badge-<?= $sc[$r['subscription_status']] ?? 'gray' ?>"><?= ucfirst($r['subscription_status']) ?></span></td>
            <td style="text-align:center"><?= $r['branch_count'] ?? 0 ?></td>
            <td style="text-align:center"><?= $r['user_count'] ?? 0 ?></td>
            <td>
              <div style="display:flex;gap:.3rem">
                <a href="<?= base_url('super/restaurants/view/'.$r['id']) ?>" class="btn btn-sm btn-outline" title="View"><i class="fa fa-eye"></i></a>
                <a href="<?= base_url('super/restaurants/edit/'.$r['id']) ?>" class="btn btn-sm btn-outline" title="Edit"><i class="fa fa-edit"></i></a>
                <form method="POST" action="<?= base_url('super/restaurants/login-as/'.$r['id']) ?>" style="margin:0">
                  <?= csrf_field() ?>
                  <button type="submit" class="btn btn-sm btn-primary" title="Login As Admin"><i class="fa fa-right-to-bracket"></i></button>
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
<script>
document.getElementById('restSearch').addEventListener('input', filterTable);
document.getElementById('statusFilter').addEventListener('change', filterTable);
function filterTable() {
  const q      = document.getElementById('restSearch').value.toLowerCase();
  const status = document.getElementById('statusFilter').value;
  document.querySelectorAll('.rest-row').forEach(row => {
    const matchQ = !q || row.dataset.name.includes(q);
    const matchS = !status || row.dataset.status === status;
    row.style.display = matchQ && matchS ? '' : 'none';
  });
}
</script>
<?php $this->endSection(); ?>
