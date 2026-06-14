<?php $this->extend('layouts/main'); $this->section('content'); ?>
<div style="display:flex;align-items:center;justify-content:center;min-height:60vh;flex-direction:column;gap:1rem;text-align:center;padding:2rem">
  <div style="font-size:4rem">🚧</div>
  <h2 style="font-size:1.5rem;font-weight:800;color:var(--text)"><?= $pageTitle ?></h2>
  <p style="color:var(--text-muted)">This section is coming soon.<br>Core POS and billing are fully functional.</p>
  <a href="javascript:history.back()" class="btn btn-outline"><i class="fa fa-arrow-left"></i> Go Back</a>
</div>
<?php $this->endSection(); ?>
