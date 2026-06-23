<?php $this->extend('layouts/main'); $this->section('content'); ?>
<div style="padding:0 1rem;max-width:700px">

  <?php if (session()->getFlashdata('success')): ?>
  <div class="alert alert-success"><i class="fa fa-check-circle"></i> <?= session()->getFlashdata('success') ?></div>
  <?php endif; ?>

  <div class="card" style="margin-bottom:1rem">
    <div class="card-header"><span class="card-title"><i class="fa fa-gear" style="color:var(--primary)"></i> Platform Settings</span></div>
    <div class="card-body">
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem">
        <a href="<?= base_url('super/plans') ?>" class="card" style="padding:1rem;text-decoration:none;color:inherit;text-align:center;transition:all .2s" onmouseover="this.style.borderColor='var(--primary)'" onmouseout="this.style.borderColor=''">
          <i class="fa fa-layer-group" style="font-size:1.75rem;color:var(--primary);margin-bottom:.5rem"></i>
          <div style="font-weight:700">Manage Plans</div>
          <div style="font-size:.78rem;color:var(--text-muted)">Add / edit subscription plans</div>
        </a>
        <a href="<?= base_url('super/subscriptions') ?>" class="card" style="padding:1rem;text-decoration:none;color:inherit;text-align:center;transition:all .2s" onmouseover="this.style.borderColor='var(--primary)'" onmouseout="this.style.borderColor=''">
          <i class="fa fa-credit-card" style="font-size:1.75rem;color:var(--primary);margin-bottom:.5rem"></i>
          <div style="font-weight:700">Subscriptions</div>
          <div style="font-size:.78rem;color:var(--text-muted)">Manage restaurant accounts</div>
        </a>
        <a href="<?= base_url('super/activity-log') ?>" class="card" style="padding:1rem;text-decoration:none;color:inherit;text-align:center;transition:all .2s" onmouseover="this.style.borderColor='var(--primary)'" onmouseout="this.style.borderColor=''">
          <i class="fa fa-list-check" style="font-size:1.75rem;color:var(--info);margin-bottom:.5rem"></i>
          <div style="font-weight:700">Activity Log</div>
          <div style="font-size:.78rem;color:var(--text-muted)">View all system activity</div>
        </a>
        <a href="<?= base_url('super/notifications') ?>" class="card" style="padding:1rem;text-decoration:none;color:inherit;text-align:center;transition:all .2s" onmouseover="this.style.borderColor='var(--primary)'" onmouseout="this.style.borderColor=''">
          <i class="fa fa-bell" style="font-size:1.75rem;color:var(--warning);margin-bottom:.5rem"></i>
          <div style="font-weight:700">Notifications</div>
          <div style="font-size:.78rem;color:var(--text-muted)">View system notifications</div>
        </a>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-header"><span class="card-title"><i class="fa fa-circle-info" style="color:var(--info)"></i> Platform Info</span></div>
    <div class="card-body">
      <div style="display:flex;flex-direction:column;gap:.5rem;font-size:.875rem">
        <div style="display:flex;justify-content:space-between;padding:.4rem 0;border-bottom:1px solid var(--border)">
          <span style="color:var(--text-muted)">Version</span><strong>RestOne v1.0</strong>
        </div>
        <div style="display:flex;justify-content:space-between;padding:.4rem 0;border-bottom:1px solid var(--border)">
          <span style="color:var(--text-muted)">Framework</span><strong>CodeIgniter 4.7</strong>
        </div>
        <div style="display:flex;justify-content:space-between;padding:.4rem 0;border-bottom:1px solid var(--border)">
          <span style="color:var(--text-muted)">PHP Version</span><strong><?= PHP_VERSION ?></strong>
        </div>
        <div style="display:flex;justify-content:space-between;padding:.4rem 0;border-bottom:1px solid var(--border)">
          <span style="color:var(--text-muted)">DB Driver</span><strong><?= config('Database')->default['DBDriver'] ?></strong>
        </div>
        <div style="display:flex;justify-content:space-between;padding:.4rem 0">
          <span style="color:var(--text-muted)">Server Time</span><strong><?= date('d M Y, H:i:s') ?></strong>
        </div>
      </div>
    </div>
  </div>
</div>
<?php $this->endSection(); ?>
