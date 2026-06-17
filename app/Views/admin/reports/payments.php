<?php $this->extend('layouts/main'); $this->section('content'); ?>
<div style="padding:0 1rem">
  <div class="card" style="margin-bottom:1rem">
    <div class="card-body" style="padding:.75rem">
      <form method="GET" style="display:flex;gap:.5rem;align-items:flex-end;flex-wrap:wrap">
        <div class="form-group" style="margin:0"><label class="form-label">From</label><input type="date" class="form-control" name="from" value="<?= $from ?>"></div>
        <div class="form-group" style="margin:0"><label class="form-label">To</label><input type="date" class="form-control" name="to" value="<?= $to ?>"></div>
        <button class="btn btn-primary"><i class="fa fa-search"></i> Filter</button>
        <a href="<?= base_url('admin/reports/sales') ?>" class="btn btn-outline">← Sales</a>
      </form>
    </div>
  </div>
  <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:.75rem;margin-bottom:1rem">
    <?php $colors=['cash'=>'success','card'=>'info','upi'=>'primary','wallet'=>'warning','online'=>'blue','credit'=>'danger'];
    foreach ($payments as $p): ?>
    <div class="card">
      <div class="card-body" style="padding:.875rem;text-align:center">
        <div style="font-weight:800;font-size:1.1rem">₹<?= number_format($p['total'],2) ?></div>
        <div style="font-size:.78rem;color:var(--text-muted)"><?= ucfirst(str_replace('_',' ',$p['payment_method'])) ?></div>
        <div style="font-size:.72rem;color:var(--text-muted)"><?= $p['count'] ?> transactions</div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <div class="card">
    <div class="card-header"><span class="card-title">Daily Collection</span></div>
    <div class="table-wrap">
      <table class="table">
        <thead><tr><th>Date</th><th>Amount Collected</th></tr></thead>
        <tbody>
          <?php foreach ($daily as $d): ?>
          <tr><td><?= date('D, d M Y',strtotime($d['date'])) ?></td><td><strong>₹<?= number_format($d['total'],2) ?></strong></td></tr>
          <?php endforeach; ?>
          <?php if (empty($daily)): ?><tr><td colspan="2"><div class="empty-state" style="padding:1.5rem"><i class="fa fa-credit-card"></i><p>No payment data</p></div></td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php $this->endSection(); ?>
