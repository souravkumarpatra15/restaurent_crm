<?php $this->extend('layouts/main'); $this->section('content'); ?>
<div style="padding:0 1rem">
  <div class="card" style="margin-bottom:1rem">
    <div class="card-body" style="padding:.75rem">
      <form method="GET" style="display:flex;gap:.5rem;align-items:flex-end;flex-wrap:wrap">
        <div class="form-group" style="margin:0"><label class="form-label">From</label><input type="date" class="form-control" name="from" value="<?= $from ?>"></div>
        <div class="form-group" style="margin:0"><label class="form-label">To</label><input type="date" class="form-control" name="to" value="<?= $to ?>"></div>
        <button class="btn btn-primary"><i class="fa fa-search"></i> Filter</button>
      </form>
    </div>
  </div>

  <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:.75rem;margin-bottom:1rem">
    <div class="card"><div class="card-body" style="padding:.9rem">
      <div style="font-size:.75rem;color:var(--text-muted);font-weight:700">PENDING / UNPAID ORDERS</div>
      <div style="font-weight:800;font-size:1.4rem;color:var(--warning)"><?= $stats['unpaid_count'] ?></div>
      <div style="font-size:.78rem;color:var(--text-muted)">₹<?= number_format($stats['unpaid_amount'],2) ?> still open</div>
    </div></div>
    <div class="card"><div class="card-body" style="padding:.9rem">
      <div style="font-size:.75rem;color:var(--text-muted);font-weight:700">CANCELLED ORDERS</div>
      <div style="font-weight:800;font-size:1.4rem;color:var(--danger)"><?= $stats['cancelled_count'] ?></div>
      <div style="font-size:.78rem;color:var(--text-muted)">₹<?= number_format($stats['cancelled_amount'],2) ?> lost value</div>
    </div></div>
    <div class="card"><div class="card-body" style="padding:.9rem">
      <div style="font-size:.75rem;color:var(--text-muted);font-weight:700">REFUNDED ORDERS</div>
      <div style="font-weight:800;font-size:1.4rem;color:#6366F1"><?= $stats['refunded_count'] ?></div>
      <div style="font-size:.78rem;color:var(--text-muted)">₹<?= number_format($stats['refunded_amount'],2) ?> refunded</div>
    </div></div>
  </div>

  <div class="card" style="margin-bottom:1rem">
    <div class="card-header"><span class="card-title">Incomplete Orders (<?= count($orders) ?>)</span></div>
    <div class="table-wrap">
      <table class="table">
        <thead><tr><th>Order</th><th>Type</th><th>Table</th><th>Staff</th><th>Amount</th><th>Status</th><th>Payment</th><th>Reason</th></tr></thead>
        <tbody>
          <?php if (empty($orders)): ?>
          <tr><td colspan="8"><div class="empty-state"><i class="fa fa-clipboard-check"></i><p>No incomplete orders in this period 🎉</p></div></td></tr>
          <?php else: foreach ($orders as $o): ?>
          <tr>
            <td style="font-size:.8rem">
              <a href="<?= base_url('admin/orders/'.$o['id']) ?>" style="font-weight:700"><?= esc($o['order_number']) ?></a>
              <div style="font-size:.7rem;color:var(--text-muted)"><?= date('d M, h:i A', strtotime($o['created_at'])) ?></div>
            </td>
            <td style="font-size:.78rem;text-transform:capitalize"><?= esc(str_replace('_',' ',$o['order_type'])) ?></td>
            <td><?= esc($o['table_number'] ?? '-') ?></td>
            <td style="font-size:.8rem"><?= esc($o['staff_name'] ?? '-') ?></td>
            <td><strong>₹<?= number_format($o['total_amount'],2) ?></strong></td>
            <td><span class="badge-pill badge-<?= $o['status']==='cancelled'?'danger':($o['status']==='refunded'?'info':'warning') ?>"><?= ucfirst($o['status']) ?></span></td>
            <td><span class="badge-pill badge-<?= $o['payment_status']==='paid'?'success':'warning' ?>"><?= ucfirst($o['payment_status']) ?></span></td>
            <td style="font-size:.75rem;color:var(--text-muted)"><?= esc($o['cancelled_reason'] ?? '-') ?></td>
          </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php $this->endSection(); ?>
