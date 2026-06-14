<?php $this->extend('layouts/main'); $this->section('content'); ?>
<div style="padding:0 1rem;max-width:600px">
  <form action="<?= $user ? base_url('admin/users/update/'.$user['id']) : base_url('admin/users/store') ?>" method="POST">
    <?= csrf_field() ?>
    <div class="card">
      <div class="card-header"><span class="card-title"><?= $pageTitle ?></span></div>
      <div class="card-body">
        <div class="form-row cols-2">
          <div class="form-group"><label class="form-label">Full Name <span class="req">*</span></label>
            <input type="text" class="form-control" name="name" value="<?= esc($user['name'] ?? '') ?>" required></div>
          <div class="form-group"><label class="form-label">Email <span class="req">*</span></label>
            <input type="email" class="form-control" name="email" value="<?= esc($user['email'] ?? '') ?>" <?= $user ? 'readonly' : 'required' ?>></div>
        </div>
        <div class="form-row cols-2">
          <div class="form-group"><label class="form-label">Phone</label>
            <input type="text" class="form-control" name="phone" value="<?= esc($user['phone'] ?? '') ?>"></div>
          <div class="form-group"><label class="form-label">Employee Code</label>
            <input type="text" class="form-control" name="employee_code" value="<?= esc($user['employee_code'] ?? '') ?>"></div>
        </div>
        <div class="form-row cols-2">
          <div class="form-group"><label class="form-label">Role <span class="req">*</span></label>
            <select class="form-control" name="role_id" required>
              <?php foreach ($roles as $r): ?>
              <option value="<?= $r['id'] ?>" <?= ($user['role_id'] ?? '') == $r['id'] ? 'selected' : '' ?>><?= esc($r['name']) ?></option>
              <?php endforeach; ?>
            </select></div>
          <div class="form-group"><label class="form-label">Branch</label>
            <select class="form-control" name="branch_id">
              <option value="">All Branches</option>
              <?php foreach ($branches as $b): ?>
              <option value="<?= $b['id'] ?>" <?= ($user['branch_id'] ?? '') == $b['id'] ? 'selected' : '' ?>><?= esc($b['name']) ?></option>
              <?php endforeach; ?>
            </select></div>
        </div>
        <div class="form-row cols-2">
          <div class="form-group"><label class="form-label">Password <?= $user ? '(leave blank to keep)' : '<span class="req">*</span>' ?></label>
            <input type="password" class="form-control" name="password" <?= $user ? '' : 'required' ?> placeholder="Min 6 characters"></div>
          <div class="form-group"><label class="form-label">POS PIN (4 digits)</label>
            <input type="text" class="form-control" name="pin" maxlength="4" value="<?= esc($user['pin'] ?? '') ?>" placeholder="1234"></div>
        </div>
      </div>
      <div class="modal-footer">
        <a href="<?= base_url('admin/users') ?>" class="btn btn-outline">Cancel</a>
        <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save</button>
      </div>
    </div>
  </form>
</div>
<?php $this->endSection(); ?>
