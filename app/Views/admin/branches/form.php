<?php $this->extend('layouts/main'); $this->section('content'); ?>
<div style="padding:0 1rem;max-width:700px">
  <form action="<?= $branch ? base_url('admin/branches/update/'.$branch['id']) : base_url('admin/branches/store') ?>" method="POST">
    <?= csrf_field() ?>
    <div class="card">
      <div class="card-header"><span class="card-title"><?= $pageTitle ?></span></div>
      <div class="card-body">
        <div class="form-row cols-2">
          <div class="form-group"><label class="form-label">Branch Name <span class="req">*</span></label>
            <input type="text" class="form-control" name="name" value="<?= esc($branch['name'] ?? '') ?>" required></div>
          <div class="form-group"><label class="form-label">Branch Code</label>
            <input type="text" class="form-control" name="code" value="<?= esc($branch['code'] ?? '') ?>"></div>
        </div>
        <div class="form-row cols-2">
          <div class="form-group"><label class="form-label">Phone</label>
            <input type="text" class="form-control" name="phone" value="<?= esc($branch['phone'] ?? '') ?>"></div>
          <div class="form-group"><label class="form-label">Email</label>
            <input type="email" class="form-control" name="email" value="<?= esc($branch['email'] ?? '') ?>"></div>
        </div>
        <div class="form-group"><label class="form-label">Address</label>
          <textarea class="form-control" name="address" rows="2"><?= esc($branch['address'] ?? '') ?></textarea></div>
        <div class="form-row cols-2">
          <div class="form-group"><label class="form-label">City</label>
            <input type="text" class="form-control" name="city" value="<?= esc($branch['city'] ?? '') ?>"></div>
          <div class="form-group"><label class="form-label">Pincode</label>
            <input type="text" class="form-control" name="pincode" value="<?= esc($branch['pincode'] ?? '') ?>"></div>
        </div>
        <div class="form-row cols-2">
          <div class="form-group"><label class="form-label">Opening Time</label>
            <input type="time" class="form-control" name="opening_time" value="<?= $branch['opening_time'] ?? '09:00' ?>"></div>
          <div class="form-group"><label class="form-label">Closing Time</label>
            <input type="time" class="form-control" name="closing_time" value="<?= $branch['closing_time'] ?? '23:00' ?>"></div>
        </div>
        <div style="display:flex;gap:1.5rem;margin-bottom:1rem">
          <label style="display:flex;align-items:center;gap:.4rem;cursor:pointer">
            <input type="checkbox" name="has_dine_in" value="1" <?= ($branch['has_dine_in'] ?? 1) ? 'checked' : '' ?>> Dine-in</label>
          <label style="display:flex;align-items:center;gap:.4rem;cursor:pointer">
            <input type="checkbox" name="has_takeaway" value="1" <?= ($branch['has_takeaway'] ?? 1) ? 'checked' : '' ?>> Takeaway</label>
          <label style="display:flex;align-items:center;gap:.4rem;cursor:pointer">
            <input type="checkbox" name="has_delivery" value="1" <?= ($branch['has_delivery'] ?? 0) ? 'checked' : '' ?>> Delivery</label>
        </div>
        <div class="form-row cols-2">
          <div class="form-group"><label class="form-label">Printer IP</label>
            <input type="text" class="form-control" name="printer_ip" placeholder="192.168.1.100" value="<?= esc($branch['printer_ip'] ?? '') ?>"></div>
          <div class="form-group"><label class="form-label">Printer Port</label>
            <input type="number" class="form-control" name="printer_port" value="<?= $branch['printer_port'] ?? 9100 ?>"></div>
        </div>
        <div class="form-group"><label class="form-label">Bill Prefix</label>
          <input type="text" class="form-control" name="billing_prefix" placeholder="INV" value="<?= esc($branch['billing_prefix'] ?? 'INV') ?>"></div>
      </div>
      <div class="modal-footer">
        <a href="<?= base_url('admin/branches') ?>" class="btn btn-outline">Cancel</a>
        <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save Branch</button>
      </div>
    </div>
  </form>
</div>
<?php $this->endSection(); ?>
