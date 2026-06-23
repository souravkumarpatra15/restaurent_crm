<?php $this->extend('layouts/main');
$this->section('content'); ?>
<div style="padding:0 1rem;max-width:760px">
  <?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success" style="margin-bottom:1rem"><i class="fa fa-check-circle"></i> <?= session()->getFlashdata('success') ?></div>
  <?php endif; ?>

  <form action="<?= $restaurant ? base_url('super/restaurants/update/' . $restaurant['id']) : base_url('super/restaurants/store') ?>" method="POST">
    <?= csrf_field() ?>

    <!-- Basic Info -->
    <div class="card" style="margin-bottom:1rem">
      <div class="card-header">
        <span class="card-title"><i class="fa fa-store" style="color:var(--primary)"></i> <?= $pageTitle ?></span>
        <a href="<?= base_url('super/restaurants') ?>" class="btn btn-sm btn-outline"><i class="fa fa-arrow-left"></i> Back</a>
      </div>
      <div class="card-body">
        <div class="form-row cols-2">
          <div class="form-group">
            <label class="form-label">Restaurant Name <span class="req">*</span></label>
            <input type="text" class="form-control" name="name" value="<?= esc($restaurant['name'] ?? '') ?>" required placeholder="e.g. Spice Garden">
          </div>
          <div class="form-group">
            <label class="form-label">Type <span class="req">*</span></label>
            <select class="form-control" name="restaurant_type">
              <?php foreach (['qsr' => 'QSR (Quick Service)', 'casual_dining' => 'Casual Dining', 'fine_dining' => 'Fine Dining', 'cafe' => 'Café', 'bakery' => 'Bakery', 'bar' => 'Bar & Lounge', 'food_truck' => 'Food Truck', 'cloud_kitchen' => 'Cloud Kitchen', 'buffet' => 'Buffet', 'dhaba' => 'Dhaba', 'hotel' => 'Hotel Restaurant', 'pizza' => 'Pizza Parlour', 'ice_cream' => 'Ice Cream Parlour', 'juice_bar' => 'Juice Bar', 'other' => 'Other'] as $k => $v): ?>
                <option value="<?= $k ?>" <?= ($restaurant['restaurant_type'] ?? 'qsr') === $k ? 'selected' : '' ?>><?= $v ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="form-row cols-2">
          <div class="form-group">
            <label class="form-label">Email <span class="req">*</span></label>
            <input type="email" class="form-control" name="email" value="<?= esc($restaurant['email'] ?? '') ?>" required placeholder="owner@restaurant.com">
          </div>
          <div class="form-group">
            <label class="form-label">Phone</label>
            <input type="text" class="form-control" name="phone" value="<?= esc($restaurant['phone'] ?? '') ?>" placeholder="9876543210">
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Address</label>
          <textarea class="form-control" name="address" rows="2" placeholder="Full address"><?= esc($restaurant['address'] ?? '') ?></textarea>
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
        <div class="form-row cols-2">
          <div class="form-group">
            <label class="form-label">Cuisine Type</label>
            <input type="text" class="form-control" name="cuisine_type" value="<?= esc($restaurant['cuisine_type'] ?? '') ?>" placeholder="e.g. Indian, Chinese">
          </div>
          <div class="form-group">
            <label class="form-label">Bill Prefix</label>
            <input type="text" class="form-control" name="billing_prefix" value="<?= esc($restaurant['billing_prefix'] ?? 'INV') ?>" placeholder="INV">
          </div>
        </div>
      </div>
    </div>

    <!-- Subscription -->
    <div class="card" style="margin-bottom:1rem">
      <div class="card-header"><span class="card-title"><i class="fa fa-credit-card" style="color:var(--primary)"></i> Subscription</span></div>
      <div class="card-body">
        <div class="form-row cols-2">
          <div class="form-group">
            <label class="form-label">Plan <span class="req">*</span></label>
            <select class="form-control" name="plan_id" required>
              <option value="">Select Plan</option>
              <?php foreach ($plans as $p): ?>
                <option value="<?= $p['id'] ?>" <?= ($restaurant['plan_id'] ?? '') == $p['id'] ? 'selected' : '' ?>>
                  <?= esc($p['name']) ?> — ₹<?= number_format($p['price_monthly']) ?>/mo
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Status</label>
            <select class="form-control" name="subscription_status">
              <?php foreach (['trial' => 'Trial', 'active' => 'Active', 'suspended' => 'Suspended', 'expired' => 'Expired', 'cancelled' => 'Cancelled'] as $k => $v): ?>
                <option value="<?= $k ?>" <?= ($restaurant['subscription_status'] ?? 'trial') === $k ? 'selected' : '' ?>><?= $v ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="form-row cols-2">
          <div class="form-group">
            <label class="form-label">Billing Cycle</label>
            <select class="form-control" name="billing_cycle">
              <option value="monthly" <?= ($restaurant['billing_cycle'] ?? '') === 'monthly' ? 'selected' : '' ?>>Monthly</option>
              <option value="yearly" <?= ($restaurant['billing_cycle'] ?? '') === 'yearly' ? 'selected' : '' ?>>Yearly</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Subscription Ends</label>
            <input type="date" class="form-control" name="subscription_ends_at"
              value="<?= $restaurant ? date('Y-m-d', strtotime($restaurant['subscription_ends_at'] ?? '+1 year')) : date('Y-m-d', strtotime('+1 year')) ?>">
          </div>
        </div>
        <div class="form-row cols-2">
          <div class="form-group">
            <label class="form-label">Tax Type</label>
            <select class="form-control" name="tax_type">
              <option value="exclusive" <?= ($restaurant['tax_type'] ?? 'exclusive') === 'exclusive' ? 'selected' : '' ?>>Exclusive</option>
              <option value="inclusive" <?= ($restaurant['tax_type'] ?? '') === 'inclusive' ? 'selected' : '' ?>>Inclusive</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Default Tax %</label>
            <select class="form-control" name="default_tax_percent">
              <?php foreach ([0, 5, 12, 18, 28] as $t): ?>
                <option value="<?= $t ?>" <?= ($restaurant['default_tax_percent'] ?? 5) == $t ? 'selected' : '' ?>><?= $t ?>%</option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <?php if (!$restaurant): ?>
          <hr style="margin:1rem 0;border-color:var(--border)">
          <div style="font-size:.82rem;font-weight:700;color:var(--text-muted);margin-bottom:.75rem;text-transform:uppercase;letter-spacing:.05em">Admin Account (auto-created)</div>
          <div class="form-row cols-2">
            <div class="form-group">
              <label class="form-label">Admin Name</label>
              <input type="text" class="form-control" name="admin_name" placeholder="Restaurant Owner Name">
            </div>
            <div class="form-group">
              <label class="form-label">Admin Password</label>
              <input type="text" class="form-control" name="admin_password" placeholder="Default: admin@123" value="admin@123">
            </div>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <div style="display:flex;justify-content:space-between;align-items:center;padding-bottom:2rem;">
      <a href="<?= base_url('super/restaurants') ?>" class="btn btn-outline">
        <i class="fa fa-times"></i> Cancel
      </a>
      <button type="submit" class="btn btn-primary">
        <i class="fa fa-save"></i>
        <?= $restaurant ? 'Update Restaurant' : 'Create Restaurant' ?>
      </button>
    </div>
  </form>
</div>
<?php $this->endSection(); ?>