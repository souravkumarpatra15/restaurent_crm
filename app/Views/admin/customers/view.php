<?php $this->extend('layouts/main'); $this->section('content'); ?>
<div style="padding:0 1rem;max-width:700px">
  <div class="card" style="margin-bottom:1rem">
    <div class="card-body">
      <div style="display:flex;align-items:center;gap:1rem">
        <div style="width:60px;height:60px;border-radius:50%;background:var(--primary-light);color:var(--primary);display:flex;align-items:center;justify-content:center;font-size:1.5rem;font-weight:800"><?= substr($customer['name'],0,1) ?></div>
        <div>
          <div style="font-weight:800;font-size:1.1rem"><?= esc($customer['name']) ?></div>
          <div style="font-size:.82rem;color:var(--text-muted)"><?= esc($customer['phone'] ?? '') ?> <?= $customer['email'] ? '· '.esc($customer['email']) : '' ?></div>
        </div>
        <div style="margin-left:auto;text-align:right">
          <div style="font-size:.75rem;color:var(--text-muted)">Loyalty Points</div>
          <div style="font-weight:800;font-size:1.25rem;color:var(--primary)"><?= number_format($customer['loyalty_points'],0) ?></div>
        </div>
      </div>
      <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:.75rem;margin-top:1rem">
        <div style="text-align:center;padding:.75rem;background:var(--bg);border-radius:10px">
          <div style="font-weight:800;font-size:1.1rem"><?= $customer['total_orders'] ?></div>
          <div style="font-size:.72rem;color:var(--text-muted)">Total Orders</div>
        </div>
        <div style="text-align:center;padding:.75rem;background:var(--bg);border-radius:10px">
          <div style="font-weight:800;font-size:1.1rem">₹<?= number_format($customer['total_spent']) ?></div>
          <div style="font-size:.72rem;color:var(--text-muted)">Total Spent</div>
        </div>
        <div style="text-align:center;padding:.75rem;background:var(--bg);border-radius:10px">
          <div style="font-weight:800;font-size:1.1rem"><?= $customer['last_visit'] ? date('d M',strtotime($customer['last_visit'])) : '-' ?></div>
          <div style="font-size:.72rem;color:var(--text-muted)">Last Visit</div>
        </div>
      </div>
    </div>
  </div>
  <div class="card">
    <div class="card-header"><span class="card-title">Order History</span></div>
    <div class="table-wrap">
      <table class="table">
        <thead><tr><th>Order#</th><th>Type</th><th>Amount</th><th>Date</th><th></th></tr></thead>
        <tbody>
          <?php if (empty($orders)): ?>
          <tr><td colspan="5"><div class="empty-state" style="padding:1.5rem"><i class="fa fa-receipt"></i><p>No orders yet</p></div></td></tr>
          <?php else: foreach ($orders as $o): ?>
          <tr>
            <td><strong><?= esc($o['order_number']) ?></strong></td>
            <td><?= ucfirst(str_replace('_',' ',$o['order_type'])) ?></td>
            <td>₹<?= number_format($o['total_amount'],2) ?></td>
            <td style="font-size:.78rem;color:var(--text-muted)"><?= date('d M Y h:i A',strtotime($o['created_at'])) ?></td>
            <td><a href="<?= base_url('admin/orders/view/'.$o['id']) ?>" class="btn btn-sm btn-outline"><i class="fa fa-eye"></i></a></td>
          </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php $this->endSection(); ?>
