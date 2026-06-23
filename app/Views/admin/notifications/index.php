<?php $this->extend('layouts/main'); $this->section('content'); ?>
<div style="padding:0 1rem;max-width:600px">
  <div class="card">
    <div class="card-header">
      <span class="card-title"><i class="fa fa-bell" style="color:var(--primary)"></i> Notifications</span>
      <button onclick="markAllRead()" class="btn btn-sm btn-outline">Mark All Read</button>
    </div>
    <?php if (empty($notifications)): ?>
    <div class="empty-state" style="padding:3rem">
      <i class="fa fa-bell-slash"></i>
      <p>No notifications yet</p>
    </div>
    <?php else: foreach ($notifications as $n): ?>
    <div style="display:flex;gap:.75rem;padding:.875rem 1.25rem;border-bottom:1px solid var(--border);background:<?= $n['is_read'] ? '#fff' : 'var(--primary-light)' ?>">
      <div style="width:36px;height:36px;border-radius:50%;background:var(--primary);color:#fff;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:.9rem">
        <i class="fa fa-bell"></i>
      </div>
      <div style="flex:1">
        <div style="font-weight:<?= $n['is_read'] ? '500' : '700' ?>;font-size:.875rem"><?= esc($n['title'] ?? 'Notification') ?></div>
        <div style="font-size:.8rem;color:var(--text-muted);margin-top:.15rem"><?= esc($n['message'] ?? '') ?></div>
        <div style="font-size:.72rem;color:var(--text-light);margin-top:.25rem"><?= time_elapsed_string($n['created_at']) ?></div>
      </div>
      <?php if (!$n['is_read']): ?>
      <div style="width:8px;height:8px;border-radius:50%;background:var(--primary);flex-shrink:0;margin-top:4px"></div>
      <?php endif; ?>
    </div>
    <?php endforeach; endif; ?>
  </div>
</div>
<script>
function markAllRead() {
  fetch('<?= base_url('super/notifications/mark-read') ?>', {
    method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'},
    body:'<?= csrf_token() ?>=<?= csrf_hash() ?>'
  }).then(()=>location.reload());
}
</script>
<?php $this->endSection(); ?>
