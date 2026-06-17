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
  <div class="stats-grid" style="margin-bottom:1rem">
    <div class="stat-card green"><div class="stat-icon green"><i class="fa fa-indian-rupee-sign"></i></div>
      <div><div class="stat-value">₹<?= number_format($totalRevenue) ?></div><div class="stat-label">Total SaaS Revenue</div></div></div>
    <div class="stat-card blue"><div class="stat-icon blue"><i class="fa fa-store"></i></div>
      <div><div class="stat-value"><?= $restaurants ?></div><div class="stat-label">Total Restaurants</div></div></div>
    <div class="stat-card"><div class="stat-icon"><i class="fa fa-check-circle"></i></div>
      <div><div class="stat-value"><?= $active ?></div><div class="stat-label">Active Subscriptions</div></div></div>
  </div>
  <div class="card">
    <div class="card-header"><span class="card-title">Payment Breakdown</span></div>
    <div class="card-body">
      <?php if (empty($payments)): ?>
      <div class="empty-state"><i class="fa fa-chart-bar"></i><p>No payments in this period</p></div>
      <?php else: foreach ($payments as $p): ?>
      <div style="display:flex;justify-content:space-between;padding:.5rem 0;border-bottom:1px solid var(--border)">
        <span><?= ucfirst($p['billing_cycle'] ?? 'N/A') ?> subscriptions (<?= $p['count'] ?>)</span>
        <strong>₹<?= number_format($p['total'],2) ?></strong>
      </div>
      <?php endforeach; endif; ?>
    </div>
  </div>
</div>
<?php $this->endSection(); ?>
