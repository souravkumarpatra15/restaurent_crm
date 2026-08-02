<?php $this->extend('layouts/main'); $this->section('content'); ?>
<div style="padding:0 1rem;max-width:680px">

  <?php if (session()->getFlashdata('success')): ?>
  <div class="alert alert-success" style="margin-bottom:1rem"><i class="fa fa-check-circle"></i> <?= session()->getFlashdata('success') ?></div>
  <?php endif; ?>

  <?php $s = $settings ?? []; $r = $restaurant ?? []; ?>

  <!-- Preview Link -->
  <?php if ($s && $s['is_enabled'] && $s['listed_on_platform']): ?>
  <div style="background:linear-gradient(135deg,#1A202C,#2D3748);border-radius:var(--radius);padding:1rem 1.25rem;margin-bottom:1rem;display:flex;align-items:center;justify-content:space-between;gap:.75rem;flex-wrap:wrap">
    <div>
      <div style="font-weight:800;color:#fff;font-size:.875rem">🌍 Your Public Booking Page</div>
      <div style="font-size:.75rem;color:rgba(255,255,255,.6);margin-top:.2rem">
        <?= base_url('book/'.($r['booking_slug']?:$r['slug'])) ?>
      </div>
    </div>
    <a href="<?= base_url('book/'.($r['booking_slug']?:$r['slug'])) ?>" target="_blank" class="btn btn-sm" style="background:#FF6B35;color:#fff;border:none">
      <i class="fa fa-external-link"></i> Preview
    </a>
  </div>
  <?php endif; ?>

  <form action="<?= base_url('admin/booking/settings/save') ?>" method="POST">
    <?= csrf_field() ?>

    <!-- Enable/Disable -->
    <div class="card" style="margin-bottom:1rem">
      <div class="card-header"><span class="card-title"><i class="fa fa-toggle-on" style="color:var(--primary)"></i> Booking Status</span></div>
      <div class="card-body">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem">
          <label style="display:flex;align-items:center;gap:.625rem;padding:.875rem;border:1.5px solid var(--border);border-radius:var(--radius);cursor:pointer">
            <input type="checkbox" name="is_enabled" value="1" <?= ($s['is_enabled']??0)?'checked':'' ?> style="accent-color:var(--primary);width:18px;height:18px">
            <div>
              <div style="font-weight:700;font-size:.875rem">Enable Bookings</div>
              <div style="font-size:.72rem;color:var(--text-muted)">Accept reservations</div>
            </div>
          </label>
          <label style="display:flex;align-items:center;gap:.625rem;padding:.875rem;border:1.5px solid var(--border);border-radius:var(--radius);cursor:pointer">
            <input type="checkbox" name="listed_on_platform" value="1" <?= ($s['listed_on_platform']??0)?'checked':'' ?> style="accent-color:var(--primary);width:18px;height:18px">
            <div>
              <div style="font-weight:700;font-size:.875rem">List on DinoviX</div>
              <div style="font-size:.72rem;color:var(--text-muted)">Show on discovery page</div>
            </div>
          </label>
          <label style="display:flex;align-items:center;gap:.625rem;padding:.875rem;border:1.5px solid var(--border);border-radius:var(--radius);cursor:pointer">
            <input type="checkbox" name="auto_confirm" value="1" <?= ($s['auto_confirm']??1)?'checked':'' ?> style="accent-color:var(--primary);width:18px;height:18px">
            <div>
              <div style="font-weight:700;font-size:.875rem">Auto-Confirm</div>
              <div style="font-size:.72rem;color:var(--text-muted)">No manual approval needed</div>
            </div>
          </label>
          <label style="display:flex;align-items:center;gap:.625rem;padding:.875rem;border:1.5px solid var(--border);border-radius:var(--radius);cursor:pointer">
            <input type="checkbox" name="accepts_online_payment" value="1" <?= ($s['accepts_online_payment']??0)?'checked':'' ?> style="accent-color:var(--primary);width:18px;height:18px">
            <div>
              <div style="font-weight:700;font-size:.875rem">Online Payment</div>
              <div style="font-size:.72rem;color:var(--text-muted)">Accept deposit online</div>
            </div>
          </label>
        </div>
      </div>
    </div>

    <!-- Public Listing Info -->
    <div class="card" style="margin-bottom:1rem">
      <div class="card-header"><span class="card-title"><i class="fa fa-store" style="color:var(--primary)"></i> Public Profile</span></div>
      <div class="card-body">
        <div class="form-group">
          <label class="form-label">Booking URL Slug</label>
          <div style="display:flex;align-items:center;gap:.5rem">
            <span style="font-size:.8rem;color:var(--text-muted);white-space:nowrap"><?= base_url('book/') ?></span>
            <input type="text" name="booking_slug" class="form-control" value="<?= esc($r['booking_slug'] ?? '') ?>" placeholder="your-restaurant-name">
          </div>
          <div style="font-size:.72rem;color:var(--text-muted);margin-top:.25rem">Custom URL for your booking page. Leave blank to use auto-generated slug.</div>
        </div>
        <div class="form-group">
          <label class="form-label">Short Description</label>
          <textarea name="short_desc" class="form-control" rows="2" placeholder="e.g. Authentic North Indian cuisine with rooftop seating and live music on weekends..."><?= esc($r['short_desc'] ?? '') ?></textarea>
        </div>
        <div class="form-row cols-2">
          <div class="form-group">
            <label class="form-label">Cuisines</label>
            <input type="text" name="cuisines" class="form-control" value="<?= esc($s['cuisines']??'') ?>" placeholder="e.g. North Indian, Chinese, Italian">
          </div>
          <div class="form-group">
            <label class="form-label">Avg Cost for 2 (₹)</label>
            <input type="number" name="avg_cost_for_two" class="form-control" value="<?= $s['avg_cost_for_two']??'' ?>" placeholder="e.g. 800">
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Tags <span style="font-weight:400;color:var(--text-muted)">(comma separated)</span></label>
          <input type="text" name="tags" class="form-control" value="<?= esc($s['tags']??'') ?>" placeholder="rooftop, family-friendly, romantic, live-music, pet-friendly">
        </div>
      </div>
    </div>

    <!-- Slot Settings -->
    <div class="card" style="margin-bottom:1rem">
      <div class="card-header"><span class="card-title"><i class="fa fa-clock" style="color:var(--primary)"></i> Slot Settings</span></div>
      <div class="card-body">
        <div class="form-row cols-2">
          <div class="form-group">
            <label class="form-label">Opening Time</label>
            <input type="time" name="open_time" class="form-control" value="<?= $s['open_time']??'10:00' ?>">
          </div>
          <div class="form-group">
            <label class="form-label">Closing Time</label>
            <input type="time" name="close_time" class="form-control" value="<?= $s['close_time']??'23:00' ?>">
          </div>
          <div class="form-group">
            <label class="form-label">Slot Duration</label>
            <select name="slot_duration_min" class="form-control">
              <?php foreach ([30=>'30 min',45=>'45 min',60=>'1 hour',90=>'1.5 hours',120=>'2 hours'] as $v=>$l): ?>
              <option value="<?= $v ?>" <?= ($s['slot_duration_min']??60)==$v?'selected':'' ?>><?= $l ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Booking Window (days)</label>
            <input type="number" name="booking_advance_days" class="form-control" value="<?= $s['booking_advance_days']??30 ?>" min="1" max="90">
          </div>
          <div class="form-group">
            <label class="form-label">Min Guests</label>
            <input type="number" name="min_guests" class="form-control" value="<?= $s['min_guests']??1 ?>" min="1">
          </div>
          <div class="form-group">
            <label class="form-label">Max Guests</label>
            <input type="number" name="max_guests" class="form-control" value="<?= $s['max_guests']??20 ?>" min="1">
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Closed Days</label>
          <div style="display:flex;gap:.5rem;flex-wrap:wrap">
            <?php
            $closedArr = !empty($s['closed_days']) ? explode(',', $s['closed_days']) : [];
            $days = [0=>'Sun',1=>'Mon',2=>'Tue',3=>'Wed',4=>'Thu',5=>'Fri',6=>'Sat'];
            foreach ($days as $num => $name): ?>
            <label style="display:flex;align-items:center;gap:.35rem;padding:.35rem .75rem;border:1.5px solid var(--border);border-radius:8px;cursor:pointer;font-size:.8rem">
              <input type="checkbox" name="closed_days[]" value="<?= $num ?>" <?= in_array($num,$closedArr)?'checked':'' ?> style="accent-color:var(--danger)">
              <?= $name ?>
            </label>
            <?php endforeach; ?>
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Free Cancellation Window (hours)</label>
          <input type="number" name="cancellation_hours" class="form-control" value="<?= $s['cancellation_hours']??2 ?>" min="0" max="72" style="width:120px">
          <div style="font-size:.72rem;color:var(--text-muted);margin-top:.25rem">Guests can cancel for free up to this many hours before the booking</div>
        </div>
      </div>
    </div>

    <!-- Deposit Settings -->
    <div class="card" style="margin-bottom:1rem">
      <div class="card-header"><span class="card-title"><i class="fa fa-indian-rupee-sign" style="color:var(--primary)"></i> Deposit / Payment</span></div>
      <div class="card-body">
        <label style="display:flex;align-items:center;gap:.625rem;margin-bottom:.875rem;cursor:pointer">
          <input type="checkbox" name="deposit_required" value="1" id="depReq" <?= ($s['deposit_required']??0)?'checked':'' ?> style="accent-color:var(--primary);width:18px;height:18px" onchange="toggleDeposit()">
          <div>
            <div style="font-weight:700;font-size:.875rem">Require Deposit to Confirm</div>
            <div style="font-size:.72rem;color:var(--text-muted)">Reduces no-shows significantly</div>
          </div>
        </label>
        <div id="depositFields" style="<?= ($s['deposit_required']??0)?'':'display:none' ?>">
          <div class="form-row cols-2">
            <div class="form-group">
              <label class="form-label">Deposit Type</label>
              <select name="deposit_type" class="form-control">
                <option value="flat" <?= ($s['deposit_type']??'flat')==='flat'?'selected':'' ?>>Flat Amount</option>
                <option value="per_person" <?= ($s['deposit_type']??'')==='per_person'?'selected':'' ?>>Per Person</option>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label">Amount (₹)</label>
              <input type="number" name="deposit_amount" class="form-control" value="<?= $s['deposit_amount']??0 ?>" min="0" step="0.5">
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Instructions -->
    <div class="card" style="margin-bottom:1rem">
      <div class="card-header"><span class="card-title"><i class="fa fa-circle-info" style="color:var(--primary)"></i> Guest Instructions</span></div>
      <div class="card-body">
        <textarea name="booking_instructions" class="form-control" rows="3" placeholder="e.g. Please arrive 10 minutes early. Parking available on 2nd floor. Dress code: Smart casual."><?= esc($s['booking_instructions']??'') ?></textarea>
      </div>
    </div>

    <button type="submit" class="btn btn-primary" style="width:100%;margin-bottom:2rem">
      <i class="fa fa-save"></i> Save Booking Settings
    </button>
  </form>
</div>
<script>
function toggleDeposit(){
  document.getElementById('depositFields').style.display = document.getElementById('depReq').checked ? '' : 'none';
}
</script>
<?php $this->endSection(); ?>
