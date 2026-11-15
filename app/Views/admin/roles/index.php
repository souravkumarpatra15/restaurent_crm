<?php $this->extend('layouts/main'); $this->section('content'); ?>
<div style="max-width:900px">
  <div class="card" style="margin-bottom:1rem">
    <div class="card-header">
      <span class="card-title"><i class="fa fa-user-shield" style="color:var(--primary)"></i> Roles &amp; Access</span>
    </div>
    <div class="card-body" style="padding:0">
      <div style="padding:1rem 1rem .5rem;font-size:.85rem;color:var(--text-m);line-height:1.6">
        Decide exactly what each role can see and do. The sidebar hides options a role lacks, and the server enforces the same
        rules — so removing access is safe, not cosmetic. Sensitive actions can still be unlocked live with a
        <strong>manager PIN approval</strong>.
      </div>
      <div style="overflow-x:auto">
        <table class="table" style="width:100%">
          <thead>
            <tr>
              <th>Role</th>
              <th>Access</th>
              <th>Staff</th>
              <th style="width:150px"></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($roles as $role): ?>
            <tr>
              <td>
                <div style="font-weight:800;font-size:.9rem"><?= esc($role['name']) ?></div>
                <div style="font-size:.7rem;color:var(--text-m)"><?= esc($role['slug']) ?><?= $role['is_custom'] ? ' · <span style="color:var(--primary)">customised</span>' : ' · DinoviX default' ?></div>
              </td>
              <td>
                <?php if ($role['granted'] === 'all'): ?>
                  <span style="font-size:.75rem;font-weight:800;color:var(--success)">Full access</span>
                <?php else: ?>
                  <span style="font-size:.75rem;font-weight:800;color:var(--text)"><?= $role['granted'] ?></span>
                  <span style="font-size:.7rem;color:var(--text-m)">of <?= count(\App\Libraries\Permissions::all()) ?> permissions</span>
                <?php endif; ?>
              </td>
              <td style="font-size:.8rem;color:var(--text-m)"><?= (int)$role['staff_count'] ?> active</td>
              <td style="text-align:right">
                <a href="<?= base_url('admin/roles/'.$role['id'].'/permissions') ?>" class="btn btn-sm btn-primary"><i class="fa fa-sliders"></i> Edit</a>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-header">
      <span class="card-title"><i class="fa fa-lightbulb" style="color:#F59E0B"></i> How advanced access works</span>
    </div>
    <div class="card-body" style="font-size:.83rem;color:var(--text-m);line-height:1.7">
      <div style="display:flex;gap:.75rem;align-items:flex-start;margin-bottom:.5rem">
        <i class="fa fa-key" style="color:var(--primary);margin-top:.15rem"></i>
        <div><strong style="color:var(--text)">Manager PIN override</strong> — a cashier without "cancel orders" can still complete the task by asking a manager to tap their PIN on the counter. Every approval is written to the override audit trail, so nothing happens off the record.</div>
      </div>
      <div style="display:flex;gap:.75rem;align-items:flex-start">
        <i class="fa fa-rotate" style="color:var(--primary);margin-top:.15rem"></i>
        <div><strong style="color:var(--text)">Instant apply</strong> — permission changes reach staff on their very next click, no app reinstall, no re-login.</div>
      </div>
    </div>
  </div>
</div>
<?php $this->endSection(); ?>
