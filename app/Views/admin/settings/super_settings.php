<?php $this->extend('layouts/main'); $this->section('content'); ?>
<div style="padding:0 1rem;max-width:600px">
  <div class="card"><div class="card-header"><span class="card-title">System Settings</span></div>
  <div class="card-body"><p style="color:var(--text-muted)">Global system settings — coming soon.</p>
  <a href="<?= base_url('super/activity-log') ?>" class="btn btn-outline"><i class="fa fa-list-check"></i> View Activity Log</a>
  </div></div>
</div>
<?php $this->endSection(); ?>
