<?php $this->extend('layouts/main'); $this->section('content'); ?>
<div style="padding:0 1rem;max-width:500px">
  <div class="card" style="margin-bottom:1rem">
    <div class="card-header"><span class="card-title">📊 My Shift — <?= date('d M Y', strtotime($date)) ?></span></div>
    <div class="card-body">
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1rem">
        <div style="text-align:center;padding:1rem;background:var(--primary-light);border-radius:10px">
          <div style="font-size:1.5rem;font-weight:800;color:var(--primary)"><?= $summary['total_orders'] ?? 0 ?></div>
          <div style="font-size:.78rem;color:var(--text-muted)">Orders Taken</div>
        </div>
        <div style="text-align:center;padding:1rem;background:#F0FFF4;border-radius:10px">
          <div style="font-size:1.5rem;font-weight:800;color:var(--success)">₹<?= number_format($summary['total_revenue'] ?? 0) ?></div>
          <div style="font-size:.78rem;color:var(--text-muted)">Revenue</div>
        </div>
      </div>
      <div style="font-weight:700;margin-bottom:.5rem">Payment Breakdown</div>
      <?php if (empty($payments)): ?>
        <p style="color:var(--text-muted);font-size:.85rem">No payments yet today.</p>
      <?php else: foreach ($payments as $p): ?>
        <div style="display:flex;justify-content:space-between;padding:.4rem 0;border-bottom:1px solid var(--border);font-size:.875rem">
          <span><?= ucfirst(str_replace('_',' ',$p['payment_method'])) ?> (<?= $p['count'] ?>)</span>
          <strong>₹<?= number_format($p['total'],2) ?></strong>
        </div>
      <?php endforeach; endif; ?>
    </div>
  </div>
  <a href="<?= base_url('pos') ?>" class="btn btn-primary btn-block"><i class="fa fa-cash-register"></i> Back to POS</a>
</div>
<?php $this->endSection(); ?>
