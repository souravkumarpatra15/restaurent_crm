<?php $this->extend('layouts/main'); $this->section('content'); ?>
<div style="padding:0 1rem">
  <div style="display:flex;justify-content:flex-end;margin-bottom:1rem">
    <a href="<?= base_url('admin/branches/create') ?>" class="btn btn-primary"><i class="fa fa-plus"></i> Add Branch</a>
  </div>
  <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:.75rem">
    <?php if (empty($branches)): ?>
    <div class="card"><div class="empty-state" style="padding:3rem"><i class="fa fa-code-branch"></i><p>No branches yet</p></div></div>
    <?php else: foreach ($branches as $b): ?>
    <div class="card">
      <div class="card-body">
        <div style="display:flex;justify-content:space-between;align-items:flex-start">
          <div>
            <div style="font-weight:700;font-size:1rem"><?= esc($b['name']) ?></div>
            <div style="font-size:.78rem;color:var(--text-muted)"><?= esc($b['city']) ?> <?= esc($b['state']) ?></div>
            <?php if ($b['phone']): ?><div style="font-size:.78rem"><i class="fa fa-phone"></i> <?= esc($b['phone']) ?></div><?php endif; ?>
          </div>
          <span class="badge-pill badge-<?= $b['is_active']?'success':'danger' ?>"><?= $b['is_active']?'Active':'Inactive' ?></span>
        </div>
        <div style="display:flex;gap:.35rem;margin-top:.75rem;flex-wrap:wrap">
          <?php if ($b['has_dine_in']): ?><span class="badge-pill badge-info" style="font-size:.7rem">Dine-in</span><?php endif; ?>
          <?php if ($b['has_takeaway']): ?><span class="badge-pill badge-primary" style="font-size:.7rem">Takeaway</span><?php endif; ?>
          <?php if ($b['has_delivery']): ?><span class="badge-pill badge-success" style="font-size:.7rem">Delivery</span><?php endif; ?>
        </div>
        <div style="display:flex;gap:.35rem;margin-top:.75rem">
          <a href="<?= base_url('admin/branches/edit/'.$b['id']) ?>" class="btn btn-sm btn-outline"><i class="fa fa-edit"></i> Edit</a>
          <button onclick="fetch('<?= base_url('admin/branches/toggle/'.$b['id']) ?>',{method:'POST',headers:{'X-CSRF-TOKEN':'<?= csrf_hash() ?>'}}).then(()=>location.reload())" class="btn btn-sm btn-outline"><?= $b['is_active']?'Disable':'Enable' ?></button>
        </div>
      </div>
    </div>
    <?php endforeach; endif; ?>
  </div>
</div>
<?php $this->endSection(); ?>
