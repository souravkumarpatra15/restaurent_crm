<?php $this->extend('layouts/main'); $this->section('content'); ?>
<div style="padding:0 1rem">
  <div style="display:flex;justify-content:flex-end;margin-bottom:1rem">
    <button class="btn btn-primary" onclick="openModal('addCouponModal')"><i class="fa fa-plus"></i> New Coupon</button>
  </div>

  <?php if (empty($coupons)): ?>
  <div class="card"><div class="empty-state" style="padding:3rem"><i class="fa fa-ticket"></i><p>No coupons yet</p></div></div>
  <?php else: ?>
  <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:.75rem">
    <?php foreach ($coupons as $c): ?>
    <div class="card" style="border-left:4px solid <?= $c['is_active'] ? 'var(--success)' : 'var(--border)' ?>">
      <div class="card-body">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:.5rem">
          <div>
            <div style="font-family:'JetBrains Mono',monospace;font-weight:800;font-size:1.1rem;letter-spacing:.05em;color:var(--primary)"><?= esc($c['code']) ?></div>
            <div style="font-size:.78rem;color:var(--text-muted)"><?= esc($c['name'] ?? '') ?></div>
          </div>
          <label class="toggle-switch">
            <input type="checkbox" <?= $c['is_active'] ? 'checked' : '' ?> onchange="toggleCoupon(<?= $c['id'] ?>, this)">
            <span class="toggle-slider"></span>
          </label>
        </div>
        <div style="display:flex;gap:.5rem;flex-wrap:wrap;margin-bottom:.5rem">
          <span class="badge-pill badge-primary" style="font-size:.8rem">
            <?= $c['discount_type'] === 'percent' ? $c['discount_value'].'% OFF' : '₹'.$c['discount_value'].' OFF' ?>
          </span>
          <?php if ($c['min_order_amount'] > 0): ?>
          <span class="badge-pill badge-gray" style="font-size:.72rem">Min ₹<?= $c['min_order_amount'] ?></span>
          <?php endif; ?>
          <?php if ($c['max_discount_amount'] > 0): ?>
          <span class="badge-pill badge-gray" style="font-size:.72rem">Max ₹<?= $c['max_discount_amount'] ?></span>
          <?php endif; ?>
        </div>
        <div style="font-size:.75rem;color:var(--text-muted)">
          <?php if ($c['valid_from']): ?>
          <i class="fa fa-calendar"></i> <?= date('d M Y', strtotime($c['valid_from'])) ?>
          <?php if ($c['valid_to']): ?> – <?= date('d M Y', strtotime($c['valid_to'])) ?><?php endif; ?>
          <?php endif; ?>
        </div>
        <div style="font-size:.75rem;color:var(--text-muted);margin-top:.2rem">
          Used: <?= $c['usage_count'] ?><?= $c['usage_limit'] > 0 ? '/'.$c['usage_limit'] : '' ?> times
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>

<!-- Add Coupon Modal -->
<div class="modal-overlay" id="addCouponModal">
  <div class="modal">
    <div class="modal-header">
      <span class="modal-title"><i class="fa fa-ticket" style="color:var(--primary)"></i> Create Coupon</span>
      <button class="modal-close" onclick="closeModal('addCouponModal')"><i class="fa fa-times"></i></button>
    </div>
    <form action="<?= base_url('admin/coupons/store') ?>" method="POST">
      <?= csrf_field() ?>
      <div class="modal-body">
        <div class="form-row cols-2">
          <div class="form-group">
            <label class="form-label">Coupon Code <span class="req">*</span></label>
            <input type="text" class="form-control" name="code" placeholder="SAVE20" required style="font-family:monospace;text-transform:uppercase">
          </div>
          <div class="form-group">
            <label class="form-label">Name / Description</label>
            <input type="text" class="form-control" name="name" placeholder="e.g. Weekend Special">
          </div>
        </div>
        <div class="form-row cols-2">
          <div class="form-group">
            <label class="form-label">Discount Type <span class="req">*</span></label>
            <select class="form-control" name="discount_type" id="discType" onchange="toggleMaxDiscount()">
              <option value="percent">Percentage (%)</option>
              <option value="flat">Flat Amount (₹)</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Discount Value <span class="req">*</span></label>
            <input type="number" class="form-control" name="discount_value" placeholder="e.g. 20" step="0.01" min="0" required>
          </div>
        </div>
        <div class="form-row cols-2">
          <div class="form-group">
            <label class="form-label">Min Order Amount (₹)</label>
            <input type="number" class="form-control" name="min_order_amount" value="0" min="0">
          </div>
          <div class="form-group" id="maxDiscountGroup">
            <label class="form-label">Max Discount (₹)</label>
            <input type="number" class="form-control" name="max_discount_amount" value="0" min="0" placeholder="0 = unlimited">
          </div>
        </div>
        <div class="form-row cols-2">
          <div class="form-group">
            <label class="form-label">Valid From</label>
            <input type="date" class="form-control" name="valid_from" value="<?= date('Y-m-d') ?>">
          </div>
          <div class="form-group">
            <label class="form-label">Valid To</label>
            <input type="date" class="form-control" name="valid_to">
          </div>
        </div>
        <div class="form-row cols-2">
          <div class="form-group">
            <label class="form-label">Total Usage Limit</label>
            <input type="number" class="form-control" name="usage_limit" value="0" placeholder="0 = unlimited">
          </div>
          <div class="form-group">
            <label class="form-label">Per User Limit</label>
            <input type="number" class="form-control" name="per_user_limit" value="1">
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Applicable On</label>
          <select class="form-control" name="applicable_on">
            <option value="all">All Order Types</option>
            <option value="dine_in">Dine-in Only</option>
            <option value="takeaway">Takeaway Only</option>
            <option value="delivery">Delivery Only</option>
          </select>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="closeModal('addCouponModal')">Cancel</button>
        <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Create Coupon</button>
      </div>
    </form>
  </div>
</div>

<style>
.toggle-switch{position:relative;display:inline-block;width:38px;height:22px}
.toggle-switch input{opacity:0;width:0;height:0}
.toggle-slider{position:absolute;cursor:pointer;top:0;left:0;right:0;bottom:0;background:#CBD5E0;transition:.3s;border-radius:22px}
.toggle-slider:before{position:absolute;content:'';height:16px;width:16px;left:3px;bottom:3px;background:#fff;transition:.3s;border-radius:50%}
input:checked+.toggle-slider{background:var(--success)}
input:checked+.toggle-slider:before{transform:translateX(16px)}
</style>

<script>
function toggleCoupon(id, cb) {
  fetch('<?= base_url('admin/coupons/toggle/') ?>' + id, {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: '<?= csrf_token() ?>=<?= csrf_hash() ?>'
  }).then(r => r.json()).then(d => {
    if (!d.success) { cb.checked = !cb.checked; showToast('Failed','error'); }
    else showToast(cb.checked ? 'Coupon enabled' : 'Coupon disabled', 'success');
  });
}
function toggleMaxDiscount() {
  document.getElementById('maxDiscountGroup').style.display =
    document.getElementById('discType').value === 'percent' ? '' : 'none';
}
</script>
<?php $this->endSection(); ?>
