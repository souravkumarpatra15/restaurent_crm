<?php $this->extend('layouts/main'); $this->section('content'); ?>
<div style="padding:0 1rem">
  <div class="stats-grid" style="margin-bottom:1rem">
    <div class="stat-card green"><div class="stat-icon green"><i class="fa fa-check-circle"></i></div>
      <div><div class="stat-value"><?= $stats['active'] ?></div><div class="stat-label">Active</div></div></div>
    <div class="stat-card orange"><div class="stat-icon orange"><i class="fa fa-clock"></i></div>
      <div><div class="stat-value"><?= $stats['trial'] ?></div><div class="stat-label">Trial</div></div></div>
    <div class="stat-card red"><div class="stat-icon red"><i class="fa fa-times-circle"></i></div>
      <div><div class="stat-value"><?= $stats['expired'] ?></div><div class="stat-label">Expired</div></div></div>
    <div class="stat-card"><div class="stat-icon"><i class="fa fa-indian-rupee-sign"></i></div>
      <div><div class="stat-value">₹<?= number_format($stats['mrr']) ?></div><div class="stat-label">MRR</div></div></div>
  </div>
  <div class="card">
    <div class="card-header"><span class="card-title">All Subscriptions</span></div>
    <div class="table-wrap">
      <table class="table">
        <thead><tr><th>Restaurant</th><th>Plan</th><th>Cycle</th><th>Status</th><th>Expires</th></tr></thead>
        <tbody>
          <?php foreach ($subs as $s):
            $sc=['active'=>'success','trial'=>'warning','expired'=>'danger','suspended'=>'danger','cancelled'=>'gray'];
          ?>
          <tr>
            <td><div style="font-weight:600"><?= esc($s['name']) ?></div><div style="font-size:.72rem;color:var(--text-muted)"><?= esc($s['email']) ?></div></td>
            <td><span class="badge-pill badge-primary"><?= esc($s['plan_name'] ?? 'N/A') ?></span></td>
            <td style="font-size:.82rem"><?= ucfirst($s['billing_cycle'] ?? '-') ?></td>
            <td><span class="badge-pill badge-<?= $sc[$s['subscription_status']] ?? 'gray' ?>"><?= ucfirst($s['subscription_status']) ?></span></td>
            <td style="font-size:.8rem"><?= $s['subscription_ends_at'] ? date('d M Y',strtotime($s['subscription_ends_at'])) : '-' ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php $this->endSection(); ?>
