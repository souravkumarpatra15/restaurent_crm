<?php $this->extend('layouts/main'); $this->section('content'); ?>
<div style="padding:0 1rem">
  <div class="card">
    <div class="card-header">
      <span class="card-title">Customers (<?= count($customers) ?>)</span>
      <button class="btn btn-primary btn-sm" onclick="document.getElementById('addCustModal').classList.add('open')"><i class="fa fa-plus"></i> Add</button>
    </div>
    <div class="table-wrap">
      <table class="table">
        <thead><tr><th>Name</th><th>Phone</th><th>Orders</th><th>Spent</th><th>Points</th><th></th></tr></thead>
        <tbody>
          <?php if (empty($customers)): ?>
          <tr><td colspan="6"><div class="empty-state"><i class="fa fa-person"></i><p>No customers yet</p></div></td></tr>
          <?php else: foreach ($customers as $c): ?>
          <tr>
            <td><div style="font-weight:600"><?= esc($c['name']) ?></div><div style="font-size:.72rem;color:var(--text-muted)"><?= esc($c['email'] ?? '') ?></div></td>
            <td><?= esc($c['phone'] ?? '-') ?></td>
            <td><?= $c['total_orders'] ?></td>
            <td>₹<?= number_format($c['total_spent'],2) ?></td>
            <td><span class="badge-pill badge-primary"><?= number_format($c['loyalty_points'],0) ?> pts</span></td>
            <td><a href="<?= base_url('admin/customers/view/'.$c['id']) ?>" class="btn btn-sm btn-outline"><i class="fa fa-eye"></i></a></td>
          </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<div class="modal-overlay" id="addCustModal">
  <div class="modal"><div class="modal-header"><span class="modal-title">Add Customer</span><button class="modal-close" onclick="document.getElementById('addCustModal').classList.remove('open')"><i class="fa fa-times"></i></button></div>
  <form action="<?= base_url('admin/customers/store') ?>" method="POST">
    <?= csrf_field() ?>
    <div class="modal-body">
      <div class="form-row cols-2">
        <div class="form-group"><label class="form-label">Name *</label><input type="text" class="form-control" name="name" required></div>
        <div class="form-group"><label class="form-label">Phone</label><input type="text" class="form-control" name="phone"></div>
      </div>
      <div class="form-group"><label class="form-label">Email</label><input type="email" class="form-control" name="email"></div>
    </div>
    <div class="modal-footer"><button type="button" class="btn btn-outline" onclick="document.getElementById('addCustModal').classList.remove('open')">Cancel</button><button type="submit" class="btn btn-primary">Save</button></div>
  </form></div>
</div>
<?php $this->endSection(); ?>
