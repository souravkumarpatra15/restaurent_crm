<?php $this->extend('layouts/main'); $this->section('content'); ?>
<div style="padding:0 1rem;max-width:700px">
<?php if (!$order): ?>
  <div class="card"><div class="empty-state"><i class="fa fa-receipt"></i><p>Order not found</p></div></div>
<?php else: ?>
  <div class="card" style="margin-bottom:1rem">
    <div class="card-header">
      <div>
        <div style="font-weight:800;font-size:1.1rem"><?= esc($order['order_number']) ?></div>
        <div style="font-size:.78rem;color:var(--text-muted)"><?= date('d M Y h:i A',strtotime($order['created_at'])) ?></div>
      </div>
      <?php $sc=['pending'=>'warning','completed'=>'success','cancelled'=>'danger','preparing'=>'info']; ?>
      <span class="badge-pill badge-<?= $sc[$order['status']] ?? 'gray' ?>" style="font-size:.85rem"><?= ucfirst($order['status']) ?></span>
    </div>
    <div class="card-body">
      <div class="form-row cols-2">
        <div><div style="font-size:.75rem;color:var(--text-muted)">Order Type</div><strong><?= ucfirst(str_replace('_',' ',$order['order_type'])) ?></strong></div>
        <?php if ($order['table']): ?>
        <div><div style="font-size:.75rem;color:var(--text-muted)">Table</div><strong><?= esc($order['table']['table_number']) ?></strong></div>
        <?php endif; ?>
        <?php if ($order['customer_name']): ?>
        <div><div style="font-size:.75rem;color:var(--text-muted)">Customer</div><strong><?= esc($order['customer_name']) ?></strong></div>
        <?php endif; ?>
        <?php if ($order['customer_phone']): ?>
        <div><div style="font-size:.75rem;color:var(--text-muted)">Phone</div><strong><?= esc($order['customer_phone']) ?></strong></div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div class="card" style="margin-bottom:1rem">
    <div class="card-header"><span class="card-title">Items</span></div>
    <div class="table-wrap">
      <table class="table">
        <thead><tr><th>Item</th><th>Qty</th><th>Rate</th><th>Amount</th></tr></thead>
        <tbody>
          <?php foreach ($order['items'] as $item): ?>
          <tr>
            <td>
              <div style="font-weight:600"><?= esc($item['name']) ?></div>
              <?php if ($item['variant_name']): ?><div style="font-size:.72rem;color:var(--text-muted)"><?= esc($item['variant_name']) ?></div><?php endif; ?>
              <?php if ($item['notes']): ?><div style="font-size:.72rem;color:var(--warning)">* <?= esc($item['notes']) ?></div><?php endif; ?>
            </td>
            <td><?= $item['quantity'] ?></td>
            <td>₹<?= number_format($item['unit_price'],2) ?></td>
            <td><strong>₹<?= number_format($item['total_price'],2) ?></strong></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <div style="padding:1rem;border-top:1px solid var(--border)">
      <div style="display:flex;justify-content:space-between;margin-bottom:.3rem"><span>Subtotal</span><span>₹<?= number_format($order['subtotal'],2) ?></span></div>
      <?php if ($order['discount_amount'] > 0): ?>
      <div style="display:flex;justify-content:space-between;margin-bottom:.3rem;color:var(--success)"><span>Discount</span><span>-₹<?= number_format($order['discount_amount'],2) ?></span></div>
      <?php endif; ?>
      <div style="display:flex;justify-content:space-between;margin-bottom:.3rem"><span>Tax</span><span>₹<?= number_format($order['tax_amount'],2) ?></span></div>
      <div style="display:flex;justify-content:space-between;font-weight:800;font-size:1.05rem;border-top:1px solid var(--border);padding-top:.5rem;margin-top:.3rem">
        <span>Total</span><span style="color:var(--primary)">₹<?= number_format($order['total_amount'],2) ?></span>
      </div>
    </div>
  </div>

  <?php if (!empty($order['payments'])): ?>
  <div class="card" style="margin-bottom:1rem">
    <div class="card-header"><span class="card-title">Payments</span></div>
    <div class="card-body">
      <?php foreach ($order['payments'] as $p): ?>
      <div style="display:flex;justify-content:space-between;padding:.3rem 0">
        <span><?= ucfirst(str_replace('_',' ',$p['payment_method'])) ?><?= $p['payment_reference']?' — '.$p['payment_reference']:'' ?></span>
        <strong>₹<?= number_format($p['amount'],2) ?></strong>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>

  <?php if ($order['status'] !== 'cancelled' && $order['status'] !== 'completed'): ?>
  <button onclick="if(confirm('Cancel this order?'))fetch('<?= base_url('admin/orders/cancel/'.$order['id']) ?>',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:'<?= csrf_token() ?>=<?= csrf_hash() ?>'}).then(()=>location.reload())" class="btn btn-danger btn-block">
    <i class="fa fa-times"></i> Cancel Order
  </button>
  <?php endif; ?>
<?php endif; ?>
</div>
<?php $this->endSection(); ?>
