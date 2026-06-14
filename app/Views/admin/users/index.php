<?php $this->extend('layouts/main'); $this->section('content'); ?>
<div style="padding:0 1rem">
  <div style="display:flex;justify-content:flex-end;margin-bottom:1rem">
    <a href="<?= base_url('admin/users/create') ?>" class="btn btn-primary"><i class="fa fa-plus"></i> Add Staff</a>
  </div>
  <div class="card">
    <div class="table-wrap">
      <table class="table">
        <thead><tr><th>Name</th><th>Role</th><th>Branch</th><th>Contact</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
          <?php if (empty($users)): ?>
          <tr><td colspan="6"><div class="empty-state"><i class="fa fa-users"></i><p>No staff yet</p></div></td></tr>
          <?php else: foreach ($users as $u): ?>
          <tr>
            <td>
              <div style="display:flex;align-items:center;gap:.5rem">
                <div style="width:34px;height:34px;border-radius:50%;background:var(--primary-light);color:var(--primary);display:flex;align-items:center;justify-content:center;font-weight:800"><?= substr($u['name'],0,1) ?></div>
                <div><div style="font-weight:600"><?= esc($u['name']) ?></div><div style="font-size:.72rem;color:var(--text-muted)"><?= esc($u['email']) ?></div></div>
              </div>
            </td>
            <td><span class="badge-pill badge-primary"><?= esc($u['role_name']) ?></span></td>
            <td><?= esc($u['branch_name'] ?? 'All Branches') ?></td>
            <td><?= esc($u['phone'] ?? '-') ?></td>
            <td><span class="badge-pill badge-<?= $u['is_active']?'success':'danger' ?>"><?= $u['is_active']?'Active':'Inactive' ?></span></td>
            <td>
              <a href="<?= base_url('admin/users/edit/'.$u['id']) ?>" class="btn btn-sm btn-outline"><i class="fa fa-edit"></i></a>
              <button onclick="fetch('<?= base_url('admin/users/toggle/'.$u['id']) ?>',{method:'POST',headers:{'X-CSRF-TOKEN':'<?= csrf_hash() ?>'}}).then(()=>location.reload())" class="btn btn-sm btn-outline"><?= $u['is_active']?'Disable':'Enable' ?></button>
            </td>
          </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php $this->endSection(); ?>
