<?php $this->extend('layouts/main'); $this->section('content'); ?>
<div style="padding:0 1rem;max-width:640px">
  <form action="<?= $plan ? base_url('super/plans/update/'.$plan['id']) : base_url('super/plans/store') ?>" method="POST">
    <?= csrf_field() ?>
    <div class="card" style="margin-bottom:1rem">
      <div class="card-header">
        <span class="card-title"><i class="fa fa-layer-group" style="color:var(--primary)"></i> <?= $pageTitle ?></span>
        <a href="<?= base_url('super/plans') ?>" class="btn btn-sm btn-outline"><i class="fa fa-arrow-left"></i> Back</a>
      </div>
      <div class="card-body">
        <div class="form-group">
          <label class="form-label">Plan Name <span class="req">*</span></label>
          <input type="text" class="form-control" name="name" value="<?= esc($plan['name'] ?? '') ?>" required placeholder="e.g. Starter, Growth, Pro">
        </div>
        <div class="form-row cols-2">
          <div class="form-group">
            <label class="form-label">Monthly Price (₹) <span class="req">*</span></label>
            <input type="number" class="form-control" name="price_monthly" value="<?= $plan['price_monthly'] ?? '' ?>" required step="0.01">
          </div>
          <div class="form-group">
            <label class="form-label">Yearly Price (₹)</label>
            <input type="number" class="form-control" name="price_yearly" value="<?= $plan['price_yearly'] ?? '' ?>" step="0.01">
          </div>
        </div>
        <div style="font-size:.8rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;margin:1rem 0 .5rem">Limits (-1 = Unlimited)</div>
        <div class="form-row cols-2">
          <div class="form-group">
            <label class="form-label">Max Branches</label>
            <input type="number" class="form-control" name="max_branches" value="<?= $plan['max_branches'] ?? 1 ?>" min="-1">
          </div>
          <div class="form-group">
            <label class="form-label">Max Users</label>
            <input type="number" class="form-control" name="max_users" value="<?= $plan['max_users'] ?? 5 ?>" min="-1">
          </div>
          <div class="form-group">
            <label class="form-label">Max Menu Items</label>
            <input type="number" class="form-control" name="max_menu_items" value="<?= $plan['max_menu_items'] ?? 100 ?>" min="-1">
          </div>
          <div class="form-group">
            <label class="form-label">Max Tables</label>
            <input type="number" class="form-control" name="max_tables" value="<?= $plan['max_tables'] ?? 20 ?>" min="-1">
          </div>
        </div>
        <div style="font-size:.8rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;margin:1rem 0 .5rem">Features</div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:.5rem">
          <?php
          $features = [
            'allow_thermal_print'    => 'Thermal Printing',
            'allow_kot_print'        => 'KOT Printing',
            'allow_online_ordering'  => 'Online Ordering',
            'allow_loyalty'          => 'Loyalty Program',
            'allow_reports_advanced' => 'Advanced Reports',
            'allow_api_access'       => 'API Access',
            'allow_whitelabel'       => 'White Label',
          ];
          foreach ($features as $key => $label): ?>
          <label style="display:flex;align-items:center;gap:.5rem;padding:.5rem;border:1px solid var(--border);border-radius:8px;cursor:pointer;font-size:.85rem">
            <input type="checkbox" name="<?= $key ?>" value="1" <?= ($plan[$key] ?? 0) ? 'checked' : '' ?> style="accent-color:var(--primary)">
            <?= $label ?>
          </label>
          <?php endforeach; ?>
        </div>
        <div class="form-row cols-2" style="margin-top:1rem">
          <div class="form-group">
            <label class="form-label">Sort Order</label>
            <input type="number" class="form-control" name="sort_order" value="<?= $plan['sort_order'] ?? 0 ?>" min="0">
          </div>
          <?php if ($plan): ?>
          <div class="form-group">
            <label class="form-label">Status</label>
            <select class="form-control" name="is_active">
              <option value="1" <?= ($plan['is_active'] ?? 1) ? 'selected' : '' ?>>Active</option>
              <option value="0" <?= !($plan['is_active'] ?? 1) ? 'selected' : '' ?>>Inactive</option>
            </select>
          </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
    <div style="display:flex;gap:.5rem;padding-bottom:2rem">
      <a href="<?= base_url('super/plans') ?>" class="btn btn-outline"><i class="fa fa-times"></i> Cancel</a>
      <button type="submit" class="btn btn-primary" style="flex:1"><i class="fa fa-save"></i> <?= $plan ? 'Update Plan' : 'Create Plan' ?></button>
    </div>
  </form>
</div>
<?php $this->endSection(); ?>
