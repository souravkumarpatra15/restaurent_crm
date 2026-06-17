<?php $this->extend('layouts/main'); $this->section('content'); ?>
<div style="padding:0 1rem">
  <!-- Filter -->
  <div class="card" style="margin-bottom:1rem">
    <div class="card-body" style="padding:.75rem">
      <form method="GET" style="display:flex;gap:.5rem;flex-wrap:wrap;align-items:flex-end">
        <div class="form-group" style="margin:0;flex:1;min-width:130px">
          <label class="form-label">From</label>
          <input type="date" class="form-control" name="from" value="<?= $from ?>">
        </div>
        <div class="form-group" style="margin:0;flex:1;min-width:130px">
          <label class="form-label">To</label>
          <input type="date" class="form-control" name="to" value="<?= $to ?>">
        </div>
        <button class="btn btn-primary"><i class="fa fa-search"></i> Filter</button>
        <a href="<?= base_url('admin/reports/items') ?>" class="btn btn-outline">Items</a>
        <a href="<?= base_url('admin/reports/payments') ?>" class="btn btn-outline">Payments</a>
        <a href="<?= base_url('admin/reports/expenses') ?>" class="btn btn-outline">Expenses</a>
      </form>
    </div>
  </div>
  <!-- Summary -->
  <div class="stats-grid" style="margin-bottom:1rem">
    <div class="stat-card"><div class="stat-icon"><i class="fa fa-receipt"></i></div>
      <div><div class="stat-value"><?= number_format($summary['total_orders'] ?? 0) ?></div><div class="stat-label">Total Orders</div></div></div>
    <div class="stat-card green"><div class="stat-icon green"><i class="fa fa-indian-rupee-sign"></i></div>
      <div><div class="stat-value">₹<?= number_format($summary['total_revenue'] ?? 0) ?></div><div class="stat-label">Revenue</div></div></div>
    <div class="stat-card blue"><div class="stat-icon blue"><i class="fa fa-percent"></i></div>
      <div><div class="stat-value">₹<?= number_format($summary['total_tax'] ?? 0) ?></div><div class="stat-label">Tax Collected</div></div></div>
    <div class="stat-card orange"><div class="stat-icon orange"><i class="fa fa-chart-line"></i></div>
      <div><div class="stat-value">₹<?= number_format($summary['avg_order'] ?? 0) ?></div><div class="stat-label">Avg Order</div></div></div>
  </div>
  <!-- Daily Breakdown -->
  <div class="card" style="margin-bottom:1rem">
    <div class="card-header"><span class="card-title">Daily Sales</span></div>
    <div class="table-wrap">
      <table class="table">
        <thead><tr><th>Date</th><th>Orders</th><th>Revenue</th></tr></thead>
        <tbody>
          <?php if (empty($daily)): ?>
          <tr><td colspan="3"><div class="empty-state"><i class="fa fa-chart-bar"></i><p>No data for this period</p></div></td></tr>
          <?php else: foreach ($daily as $d): ?>
          <tr>
            <td><?= date('D, d M Y', strtotime($d['date'])) ?></td>
            <td><?= $d['orders'] ?></td>
            <td><strong>₹<?= number_format($d['revenue'],2) ?></strong></td>
          </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
  <!-- By Type -->
  <div class="card">
    <div class="card-header"><span class="card-title">By Order Type</span></div>
    <div class="card-body">
      <?php foreach ($byType as $t): ?>
      <div style="display:flex;justify-content:space-between;padding:.4rem 0;border-bottom:1px solid var(--border);font-size:.875rem">
        <span><?= ucfirst(str_replace('_',' ',$t['order_type'])) ?></span>
        <span><?= $t['orders'] ?> orders · <strong>₹<?= number_format($t['revenue'],2) ?></strong></span>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>
<?php $this->endSection(); ?>
