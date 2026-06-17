<?php $this->extend('layouts/main'); $this->section('content'); ?>
<div style="padding:0 1rem;max-width:700px">
  <div class="card"><div class="card-header"><span class="card-title"><?= $pageTitle ?></span></div>
  <div class="card-body">
    <p style="color:var(--text-muted)">Restaurant form — full implementation coming soon.</p>
    <a href="<?= base_url('super/restaurants') ?>" class="btn btn-outline"><i class="fa fa-arrow-left"></i> Back</a>
  </div></div>
</div>
<?php $this->endSection(); ?>
