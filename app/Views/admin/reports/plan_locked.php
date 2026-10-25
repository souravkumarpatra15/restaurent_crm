<?php $this->extend('layouts/main'); $this->section('content'); ?>
<div style="padding:2rem 1rem;display:flex;justify-content:center">
  <div class="card" style="max-width:480px;text-align:center;padding:2rem">
    <div style="font-size:2.5rem;margin-bottom:.75rem">🔒</div>
    <h3 style="font-weight:800;margin-bottom:.5rem">Advanced Reports need a plan upgrade</h3>
    <p style="color:#64748B;font-size:.875rem;line-height:1.6">
      Your current plan <strong><?= esc($planName ?? 'Starter') ?></strong> covers the basic Sales report.
      Item, Payment and Consolidated analytics are available on the
      <strong>Growth</strong> plan and above. The Sales report stays fully available to you.
    </p>
    <div style="display:flex;gap:.5rem;justify-content:center;margin-top:1.25rem">
      <a href="<?= base_url('admin/reports/sales') ?>" class="btn btn-outline"><i class="fa fa-chart-line"></i> Sales Report</a>
      <a href="<?= base_url('admin/dashboard') ?>" class="btn btn-primary"><i class="fa fa-gauge"></i> Dashboard</a>
    </div>
  </div>
</div>
<?php $this->endSection(); ?>
