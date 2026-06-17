<?php $this->extend('layouts/main'); $this->section('content'); ?>
<div style="padding:0 1rem">
  <div class="card" style="margin-bottom:1rem">
    <div class="card-body" style="padding:.75rem">
      <form method="GET" style="display:flex;gap:.5rem;flex-wrap:wrap;align-items:flex-end">
        <div class="form-group" style="margin:0"><label class="form-label">From</label><input type="date" class="form-control" name="from" value="<?= $from ?>"></div>
        <div class="form-group" style="margin:0"><label class="form-label">To</label><input type="date" class="form-control" name="to" value="<?= $to ?>"></div>
        <button class="btn btn-primary"><i class="fa fa-search"></i> Filter</button>
        <a href="<?= base_url('admin/reports/sales') ?>" class="btn btn-outline">← Sales</a>
      </form>
    </div>
  </div>
  <div class="card">
    <div class="card-header"><span class="card-title">Item-wise Sales (<?= count($items) ?> items)</span></div>
    <div class="table-wrap">
      <table class="table">
        <thead><tr><th>Rank</th><th>Item</th><th>Category</th><th>Qty Sold</th><th>Revenue</th></tr></thead>
        <tbody>
          <?php if (empty($items)): ?>
          <tr><td colspan="5"><div class="empty-state"><i class="fa fa-utensils"></i><p>No sales data</p></div></td></tr>
          <?php else: foreach ($items as $i => $item): ?>
          <tr>
            <td><span style="width:26px;height:26px;border-radius:50%;background:<?= $i===0?'var(--warning)':($i===1?'#CBD5E0':($i===2?'#D69E2E':'var(--bg)')) ?>;color:<?= $i<3?'#fff':'var(--text-muted)' ?>;display:inline-flex;align-items:center;justify-content:center;font-size:.75rem;font-weight:800"><?= $i+1 ?></span></td>
            <td style="font-weight:600"><?= esc($item['name']) ?></td>
            <td style="color:var(--text-muted);font-size:.82rem"><?= esc($item['category'] ?? '-') ?></td>
            <td><?= number_format($item['total_qty']) ?></td>
            <td><strong>₹<?= number_format($item['total_revenue'],2) ?></strong></td>
          </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php $this->endSection(); ?>
