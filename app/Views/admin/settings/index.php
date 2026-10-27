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
            <label class="form-label">GST Split</label>
            <select class="form-control" name="tax_mode">
              <option value="intra" <?= ($restaurant['tax_mode'] ?? 'intra') === 'intra' ? 'selected' : '' ?>>Intra-state — CGST + SGST</option>
              <option value="inter" <?= ($restaurant['tax_mode'] ?? 'intra') === 'inter' ? 'selected' : '' ?>>Inter-state — IGST only</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Packing Charge (₹, takeaway/delivery)</label>
            <input type="number" step="0.01" min="0" class="form-control" name="packaging_charge" value="<?= esc($restaurant['packaging_charge'] ?? 0) ?>">
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
          <div style="display:flex;gap:.35rem">
            <button type="button" class="btn btn-sm btn-outline" onclick="testPrintRow(<?= $b['id'] ?>)" title="Test print"><i class="fa fa-print"></i></button>
            <a href="<?= base_url('admin/branches/edit/'.$b['id']) ?>" class="btn btn-sm btn-outline"><i class="fa fa-edit"></i></a>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

    <div class="card" style="margin-bottom:1rem">
      <div class="card-header"><span class="card-title"><i class="fa fa-fire-burner" style="color:var(--primary)"></i> Operational Preferences</span></div>
      <div class="card-body">
        <label style="display:flex;align-items:center;gap:.6rem;cursor:pointer;font-size:.9rem">
          <input type="checkbox" name="auto_print_kot" value="1" <?= $autoKot ? 'checked' : '' ?>>
          <span>
            <strong>Auto-print KOT</strong>
            <span style="display:block;font-size:.75rem;color:var(--text-muted)">Send a KOT to the kitchen automatically the moment an order is created (no printer = saved to file, order never blocked)</span>
          </span>
        </label>
      </div>
    </div>

    <div class="card" style="margin-bottom:1rem">
      <div class="card-header"><span class="card-title"><i class="fa fa-motorcycle" style="color:var(--primary)"></i> Delivery &amp; DinoviX Fleet</span></div>
      <div class="card-body">
        <?php $f = $fleet; ?>
        <div style="margin-bottom:.9rem">
          <label style="font-size:.75rem;font-weight:800;color:var(--text-muted);text-transform:uppercase;letter-spacing:.04em">Who delivers your orders</label>
          <select name="delivery_mode" class="form-control" style="max-width:420px">
            <option value="self" <?= $f['delivery_mode'] === 'self' ? 'selected' : '' ?>>🧑‍🍳 Own riders only — fastest for them, you set their commission</option>
            <option value="dinovix" <?= $f['delivery_mode'] === 'dinovix' ? 'selected' : '' ?>>🛵 DinoviX fleet only — we dispatch, you pay our low fee</option>
            <option value="auto" <?= $f['delivery_mode'] === 'auto' ? 'selected' : '' ?>>⚡ Auto — own riders first, DinoviX fleet when everyone is busy (recommended)</option>
          </select>
        </div>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:.7rem">
          <div><div style="font-size:.75rem;font-weight:800;color:var(--text-muted)">FLEET COMMISSION %</div>
            <div style="display:flex;gap:.4rem"><input type="number" step="0.5" min="0" name="delivery_fleet_commission_pct" value="<?= $f['delivery_fleet_commission_pct'] ?>" class="form-control"><input type="number" step="1" min="0" name="delivery_fleet_commission_min" title="Minimum ₹" placeholder="min ₹" value="<?= $f['delivery_fleet_commission_min'] ?>" class="form-control" style="max-width:110px"></div>
            <div style="font-size:.68rem;color:var(--text-muted)">we keep on fleet runs (min ₹ applied)</div></div>
          <div><div style="font-size:.75rem;font-weight:800;color:var(--text-muted)">CUSTOMER DELIVERY FEE</div>
            <div style="display:flex;gap:.4rem"><input type="number" step="1" min="0" name="delivery_fee_base" value="<?= $f['delivery_fee_base'] ?>" class="form-control"><input type="number" step="0.5" min="0" name="delivery_fee_free_km" title="Free distance km" placeholder="free km" value="<?= $f['delivery_fee_free_km'] ?>" class="form-control" style="max-width:110px"></div>
            <div style="font-size:.68rem;color:var(--text-muted)">₹ base + free distance km</div></div>
          <div><div style="font-size:.75rem;font-weight:800;color:var(--text-muted)">FEE PER EXTRA KM</div>
            <input type="number" step="1" min="0" name="delivery_fee_per_km" value="<?= $f['delivery_fee_per_km'] ?>" class="form-control"></div>
          <div><div style="font-size:.75rem;font-weight:800;color:var(--text-muted)">FLEET DRIVER PAYOUT</div>
            <input type="number" step="1" min="0" name="delivery_payout_base" value="<?= $f['delivery_payout_base'] ?>" class="form-control">
            <div style="font-size:.68rem;color:var(--text-muted)">₹ base + per km</div></div>
          <div><div style="font-size:.75rem;font-weight:800;color:var(--text-muted)">PAYOUT PER EXTRA KM</div>
            <input type="number" step="1" min="0" name="delivery_payout_per_km" value="<?= $f['delivery_payout_per_km'] ?>" class="form-control"></div>
          <div><div style="font-size:.75rem;font-weight:800;color:var(--text-muted)">FEE ON OWN-RIDER DELIVERIES</div>
            <input type="number" step="1" min="0" name="delivery_own_platform_fee" value="<?= $f['delivery_own_platform_fee'] ?>" class="form-control">
            <div style="font-size:.68rem;color:var(--text-muted)">₹ per order we take</div></div>
        </div>
        <div style="margin-top:.6rem">
          <label style="display:flex;align-items:center;gap:.6rem;cursor:pointer;font-size:.9rem">
            <input type="checkbox" name="delivery_free_km_enabled" value="1" <?= $freeKm ? 'checked' : '' ?>>
            <span>
              <strong>Auto distance &amp; fees</strong>
              <span style="display:block;font-size:.75rem;color:var(--text-muted)">We geocode the delivery address (free OpenStreetMap) and price by road km automatically. Untick to keep manual flat delivery charges.</span>
            </span>
          </label>
        </div>
      </div>
    </div>

    <div style="display:flex;gap:.5rem;padding-bottom:2rem">
      <button type="submit" class="btn btn-primary btn-lg" style="flex:1"><i class="fa fa-save"></i> Save Settings</button>
    </div>
  </form>
</div>
<script>
function testPrintRow(id){
  fetch('<?= base_url('admin/branches/test-print') ?>/'+id,{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:'<?= csrf_token() ?>=<?= csrf_hash() ?>'})
    .then(r=>r.json()).then(d=>alert(d.success?'Test ticket sent to the printer ✔':(d.message||'Test print failed')))
    .catch(()=>alert('Network error'));
}
</script>
<?php $this->endSection(); ?>
