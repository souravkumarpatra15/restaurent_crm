<?php $this->extend('layouts/main'); $this->section('content'); ?>
<div style="padding:0 1rem">
  <div class="card" style="margin-bottom:1rem">
    <div class="card-body" style="padding:.75rem">
      <form method="GET" style="display:flex;gap:.5rem;flex-wrap:wrap;align-items:flex-end">
        <div class="form-group" style="margin:0;flex:1;min-width:140px">
          <label class="form-label">From</label>
          <input type="date" class="form-control" name="from" value="<?= $from ?>">
        </div>
        <div class="form-group" style="margin:0;flex:1;min-width:140px">
          <label class="form-label">To</label>
          <input type="date" class="form-control" name="to" value="<?= $to ?>">
        </div>
        <button class="btn btn-primary"><i class="fa fa-search"></i> Filter</button>
      </form>
    </div>
  </div>
  <div class="card">
    <div class="card-header">
      <span class="card-title">Orders (<?= count($orders) ?>)</span>
      <div style="font-size:.82rem;color:var(--text-muted)"><?= $from ?> to <?= $to ?></div>
    </div>
    <div class="table-wrap">
      <table class="table">
        <thead><tr><th>Order#</th><th>Type</th><th>Table</th><th>Staff</th><th>Amount</th><th>Payment</th><th>Status</th><th>Time</th><th></th></tr></thead>
        <tbody>
          <?php if (empty($orders)): ?>
          <tr><td colspan="9"><div class="empty-state"><i class="fa fa-receipt"></i><p>No orders found</p></div></td></tr>
          <?php else: foreach ($orders as $o): ?>
          <?php $sc=['pending'=>'warning','confirmed'=>'info','preparing'=>'info','ready'=>'success','served'=>'success','completed'=>'success','cancelled'=>'danger']; ?>
          <tr>
            <td><strong><?= esc($o['order_number']) ?></strong></td>
            <td><?= ucfirst(str_replace('_',' ',$o['order_type'])) ?></td>
            <td><?= $o['table_number'] ?? '-' ?></td>
            <td style="font-size:.8rem"><?= esc($o['staff_name'] ?? '-') ?></td>
            <td><strong>₹<?= number_format($o['total_amount'],2) ?></strong></td>
            <td><span class="badge-pill badge-<?= $o['payment_status']==='paid'?'success':'warning' ?>"><?= ucfirst($o['payment_status']) ?></span></td>
            <td><span class="badge-pill badge-<?= $sc[$o['status']] ?? 'gray' ?>"><?= ucfirst($o['status']) ?></span></td>
            <td style="font-size:.75rem;color:var(--text-muted)"><?= date('h:i A',strtotime($o['created_at'])) ?></td>
            <td><a href="<?= base_url('admin/orders/view/'.$o['id']) ?>" class="btn btn-sm btn-outline"><i class="fa fa-eye"></i></a></td>
          </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php $this->endSection(); ?>
