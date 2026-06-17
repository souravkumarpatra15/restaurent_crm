<?php $this->extend('layouts/main'); $this->section('content'); ?>
<div style="padding:0 1rem;max-width:700px">

  <?php if (session()->getFlashdata('success')): ?>
  <div class="alert alert-success"><i class="fa fa-check-circle"></i> <?= session()->getFlashdata('success') ?></div>
  <?php endif; ?>

  <form action="<?= base_url('admin/settings/save') ?>" method="POST" enctype="multipart/form-data">
    <?= csrf_field() ?>

    <!-- Basic Info -->
    <div class="card" style="margin-bottom:1rem">
      <div class="card-header"><span class="card-title"><i class="fa fa-store" style="color:var(--primary)"></i> Restaurant Info</span></div>
      <div class="card-body">
        <div class="form-group">
          <label class="form-label">Restaurant Name <span class="req">*</span></label>
          <input type="text" class="form-control" name="name" value="<?= esc($restaurant['name']) ?>" required>
        </div>
        <div class="form-row cols-2">
          <div class="form-group">
            <label class="form-label">Phone</label>
            <input type="text" class="form-control" name="phone" value="<?= esc($restaurant['phone'] ?? '') ?>">
          </div>
          <div class="form-group">
            <label class="form-label">Email</label>
            <input type="email" class="form-control" name="email" value="<?= esc($restaurant['email']) ?>">
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Address</label>
          <textarea class="form-control" name="address" rows="2"><?= esc($restaurant['address'] ?? '') ?></textarea>
        </div>
        <div class="form-row cols-2">
          <div class="form-group">
            <label class="form-label">City</label>
            <input type="text" class="form-control" name="city" value="<?= esc($restaurant['city'] ?? '') ?>">
          </div>
          <div class="form-group">
            <label class="form-label">State</label>
            <input type="text" class="form-control" name="state" value="<?= esc($restaurant['state'] ?? '') ?>">
          </div>
        </div>
        <div class="form-row cols-2">
          <div class="form-group">
            <label class="form-label">GST Number</label>
            <input type="text" class="form-control" name="gst_number" value="<?= esc($restaurant['gst_number'] ?? '') ?>" placeholder="15-digit GSTIN">
          </div>
          <div class="form-group">
            <label class="form-label">FSSAI Number</label>
            <input type="text" class="form-control" name="fssai_number" value="<?= esc($restaurant['fssai_number'] ?? '') ?>">
          </div>
        </div>
      </div>
    </div>

    <!-- Billing Settings -->
    <div class="card" style="margin-bottom:1rem">
      <div class="card-header"><span class="card-title"><i class="fa fa-receipt" style="color:var(--primary)"></i> Billing & Tax</span></div>
      <div class="card-body">
        <div class="form-row cols-2">
          <div class="form-group">
            <label class="form-label">Tax Type</label>
            <select class="form-control" name="tax_type">
              <option value="exclusive" <?= ($restaurant['tax_type'] ?? '') === 'exclusive' ? 'selected' : '' ?>>Exclusive (added on top)</option>
              <option value="inclusive" <?= ($restaurant['tax_type'] ?? '') === 'inclusive' ? 'selected' : '' ?>>Inclusive (inside price)</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Default Tax %</label>
            <select class="form-control" name="default_tax_percent">
              <?php foreach ([0,5,12,18,28] as $t): ?>
              <option value="<?= $t ?>" <?= ($restaurant['default_tax_percent'] ?? 5) == $t ? 'selected' : '' ?>><?= $t ?>%</option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="form-row cols-2">
          <div class="form-group">
            <label class="form-label">Service Charge %</label>
            <input type="number" class="form-control" name="service_charge_percent" value="<?= $restaurant['service_charge_percent'] ?? 0 ?>" step="0.01" min="0" max="100">
          </div>
          <div class="form-group">
            <label class="form-label">Bill Prefix</label>
            <input type="text" class="form-control" name="billing_prefix" value="<?= esc($restaurant['billing_prefix'] ?? 'INV') ?>" placeholder="INV, BILL, etc.">
          </div>
        </div>
      </div>
    </div>

    <!-- Receipt Settings -->
    <div class="card" style="margin-bottom:1rem">
      <div class="card-header"><span class="card-title"><i class="fa fa-print" style="color:var(--primary)"></i> Receipt / Thermal Print</span></div>
      <div class="card-body">
        <div class="form-group">
          <label class="form-label">Receipt Header <span style="font-size:.72rem;color:var(--text-muted)">(shown at top of bill)</span></label>
          <textarea class="form-control" name="receipt_header" rows="3" placeholder="Welcome to our restaurant&#10;Best food in town"><?= esc($restaurant['receipt_header'] ?? '') ?></textarea>
        </div>
        <div class="form-group">
          <label class="form-label">Receipt Footer <span style="font-size:.72rem;color:var(--text-muted)">(shown at bottom of bill)</span></label>
          <textarea class="form-control" name="receipt_footer" rows="3" placeholder="Thank you! Visit again :)"><?= esc($restaurant['receipt_footer'] ?? '') ?></textarea>
        </div>
      </div>
    </div>

    <!-- Theme -->
    <div class="card" style="margin-bottom:1rem">
      <div class="card-header"><span class="card-title"><i class="fa fa-palette" style="color:var(--primary)"></i> Theme</span></div>
      <div class="card-body">
        <div class="form-group">
          <label class="form-label">Brand Color</label>
          <div style="display:flex;align-items:center;gap:.75rem">
            <input type="color" class="form-control" name="theme_color" value="<?= $restaurant['theme_color'] ?? '#FF6B35' ?>" style="width:60px;height:40px;padding:2px;cursor:pointer">
            <div style="display:flex;gap:.4rem;flex-wrap:wrap">
              <?php foreach (['#FF6B35','#E53E3E','#38A169','#3182CE','#805AD5','#D69E2E','#DD6B20','#2D3748'] as $c): ?>
              <button type="button" onclick="document.querySelector('[name=theme_color]').value='<?= $c ?>'"
                      style="width:28px;height:28px;border-radius:50%;background:<?= $c ?>;border:2px solid transparent;cursor:pointer"
                      onmouseover="this.style.borderColor='#000'" onmouseout="this.style.borderColor='transparent'"></button>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Branches List -->
    <?php if (!empty($branches)): ?>
    <div class="card" style="margin-bottom:1rem">
      <div class="card-header">
        <span class="card-title"><i class="fa fa-code-branch" style="color:var(--primary)"></i> Branches</span>
        <a href="<?= base_url('admin/branches/create') ?>" class="btn btn-sm btn-outline"><i class="fa fa-plus"></i> Add</a>
      </div>
      <div class="card-body" style="padding:.5rem">
        <?php foreach ($branches as $b): ?>
        <div style="display:flex;align-items:center;justify-content:space-between;padding:.5rem .75rem;border-bottom:1px solid var(--border)">
          <div>
            <div style="font-weight:600;font-size:.875rem"><?= esc($b['name']) ?></div>
            <div style="font-size:.72rem;color:var(--text-muted)"><?= esc($b['city'] ?? '') ?> <?= $b['printer_ip'] ? '· Printer: '.$b['printer_ip'] : '' ?></div>
          </div>
          <a href="<?= base_url('admin/branches/edit/'.$b['id']) ?>" class="btn btn-sm btn-outline"><i class="fa fa-edit"></i></a>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

    <div style="display:flex;gap:.5rem;padding-bottom:2rem">
      <button type="submit" class="btn btn-primary btn-lg" style="flex:1"><i class="fa fa-save"></i> Save Settings</button>
    </div>
  </form>
</div>
<?php $this->endSection(); ?>
