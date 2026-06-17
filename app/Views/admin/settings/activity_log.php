<?php $this->extend('layouts/main'); $this->section('content'); ?>
<div style="padding:0 1rem">
  <div class="card">
    <div class="card-header"><span class="card-title">Activity Log (last 100)</span></div>
    <div class="table-wrap">
      <table class="table">
        <thead><tr><th>Time</th><th>User</th><th>Action</th><th>Module</th><th>IP</th></tr></thead>
        <tbody>
          <?php if (empty($logs)): ?>
          <tr><td colspan="5"><div class="empty-state"><i class="fa fa-list"></i><p>No activity yet</p></div></td></tr>
          <?php else: foreach ($logs as $log): ?>
          <tr>
            <td style="font-size:.75rem;white-space:nowrap"><?= date('d M H:i',strtotime($log['created_at'])) ?></td>
            <td style="font-size:.82rem"><?= esc($log['user_name'] ?? 'System') ?></td>
            <td><span class="badge-pill badge-gray" style="font-size:.72rem"><?= esc($log['action']) ?></span></td>
            <td style="font-size:.78rem;color:var(--text-muted)"><?= esc($log['module'] ?? '-') ?></td>
            <td style="font-size:.75rem;color:var(--text-muted)"><?= esc($log['ip_address'] ?? '-') ?></td>
          </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php $this->endSection(); ?>
