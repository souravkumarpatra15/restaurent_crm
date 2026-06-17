<?php $this->extend('layouts/main'); $this->section('content'); ?>
<div style="padding:0 1rem">

  <!-- Date Nav + Add -->
  <div style="display:flex;gap:.5rem;align-items:center;margin-bottom:1rem;flex-wrap:wrap">
    <a href="?date=<?= date('Y-m-d', strtotime($date . ' -1 day')) ?>" class="btn btn-outline btn-sm"><i class="fa fa-chevron-left"></i></a>
    <input type="date" class="form-control" value="<?= $date ?>" onchange="window.location='?date='+this.value" style="width:160px">
    <a href="?date=<?= date('Y-m-d', strtotime($date . ' +1 day')) ?>" class="btn btn-outline btn-sm"><i class="fa fa-chevron-right"></i></a>
    <a href="?date=<?= date('Y-m-d') ?>" class="btn btn-outline btn-sm">Today</a>
    <button class="btn btn-primary" style="margin-left:auto" onclick="openModal('addResModal')"><i class="fa fa-plus"></i> New Reservation</button>
  </div>

  <!-- Reservation Cards -->
  <?php if (empty($reservations)): ?>
  <div class="card">
    <div class="empty-state" style="padding:3rem">
      <i class="fa fa-calendar-check"></i>
      <p>No reservations for <?= date('d M Y', strtotime($date)) ?></p>
    </div>
  </div>
  <?php else: ?>
  <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:.75rem">
    <?php foreach ($reservations as $r):
      $sc = ['pending'=>['warning','⏳'],'confirmed'=>['info','✅'],'seated'=>['success','🪑'],'completed'=>['success','✔'],'cancelled'=>['danger','❌'],'no_show'=>['danger','👻']];
      [$col,$icon] = $sc[$r['status']] ?? ['gray','?'];
    ?>
    <div class="card">
      <div style="display:flex;align-items:center;justify-content:space-between;padding:.75rem 1rem;background:var(--bg);border-bottom:1px solid var(--border)">
        <div style="font-weight:800;font-size:1rem"><?= date('h:i A', strtotime($r['reservation_time'])) ?></div>
        <span class="badge-pill badge-<?= $col ?>"><?= $icon ?> <?= ucfirst($r['status']) ?></span>
      </div>
      <div class="card-body">
        <div style="font-weight:700;font-size:.95rem;margin-bottom:.25rem"><?= esc($r['customer_name']) ?></div>
        <div style="font-size:.8rem;color:var(--text-muted);margin-bottom:.5rem">
          <i class="fa fa-phone"></i> <?= esc($r['customer_phone']) ?>
          <?php if ($r['customer_email']): ?> · <?= esc($r['customer_email']) ?><?php endif; ?>
        </div>
        <div style="display:flex;gap:.5rem;flex-wrap:wrap;margin-bottom:.5rem">
          <span class="badge-pill badge-gray"><i class="fa fa-users"></i> <?= $r['guests'] ?> guests</span>
          <?php if ($r['table_number']): ?>
          <span class="badge-pill badge-primary"><i class="fa fa-chair"></i> Table <?= esc($r['table_number']) ?></span>
          <?php endif; ?>
          <?php if ($r['occasion']): ?>
          <span class="badge-pill badge-info"><?= esc($r['occasion']) ?></span>
          <?php endif; ?>
        </div>
        <?php if ($r['special_requests']): ?>
        <div style="font-size:.78rem;color:var(--warning);background:#FFFBEB;padding:.4rem .6rem;border-radius:6px;margin-bottom:.5rem">
          <i class="fa fa-note-sticky"></i> <?= esc($r['special_requests']) ?>
        </div>
        <?php endif; ?>
        <!-- Status Actions -->
        <?php if (!in_array($r['status'],['completed','cancelled'])): ?>
        <div style="display:flex;gap:.35rem;flex-wrap:wrap">
          <?php if ($r['status'] === 'confirmed'): ?>
          <button onclick="updateStatus(<?= $r['id'] ?>,'seated',this)" class="btn btn-sm btn-success"><i class="fa fa-chair"></i> Seat</button>
          <?php endif; ?>
          <?php if ($r['status'] === 'seated'): ?>
          <button onclick="updateStatus(<?= $r['id'] ?>,'completed',this)" class="btn btn-sm btn-primary"><i class="fa fa-check"></i> Complete</button>
          <?php endif; ?>
          <?php if ($r['status'] === 'pending'): ?>
          <button onclick="updateStatus(<?= $r['id'] ?>,'confirmed',this)" class="btn btn-sm btn-info"><i class="fa fa-check"></i> Confirm</button>
          <?php endif; ?>
          <button onclick="updateStatus(<?= $r['id'] ?>,'cancelled',this)" class="btn btn-sm btn-outline" style="color:var(--danger)"><i class="fa fa-times"></i> Cancel</button>
          <button onclick="updateStatus(<?= $r['id'] ?>,'no_show',this)" class="btn btn-sm btn-outline" style="color:var(--text-muted)"><i class="fa fa-user-slash"></i> No Show</button>
        </div>
        <?php endif; ?>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>

<!-- Add Reservation Modal -->
<div class="modal-overlay" id="addResModal">
  <div class="modal">
    <div class="modal-header">
      <span class="modal-title"><i class="fa fa-calendar-plus" style="color:var(--primary)"></i> New Reservation</span>
      <button class="modal-close" onclick="closeModal('addResModal')"><i class="fa fa-times"></i></button>
    </div>
    <form action="<?= base_url('admin/reservations/store') ?>" method="POST">
      <?= csrf_field() ?>
      <div class="modal-body">
        <div class="form-row cols-2">
          <div class="form-group">
            <label class="form-label">Customer Name <span class="req">*</span></label>
            <input type="text" class="form-control" name="customer_name" required>
          </div>
          <div class="form-group">
            <label class="form-label">Phone <span class="req">*</span></label>
            <input type="tel" class="form-control" name="customer_phone" required>
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Email</label>
          <input type="email" class="form-control" name="customer_email">
        </div>
        <div class="form-row cols-2">
          <div class="form-group">
            <label class="form-label">Date <span class="req">*</span></label>
            <input type="date" class="form-control" name="reservation_date" value="<?= $date ?>" required>
          </div>
          <div class="form-group">
            <label class="form-label">Time <span class="req">*</span></label>
            <input type="time" class="form-control" name="reservation_time" required>
          </div>
        </div>
        <div class="form-row cols-2">
          <div class="form-group">
            <label class="form-label">No. of Guests</label>
            <input type="number" class="form-control" name="guests" value="2" min="1" max="50">
          </div>
          <div class="form-group">
            <label class="form-label">Table</label>
            <select class="form-control" name="table_id">
              <option value="">Auto Assign</option>
              <?php foreach ($tables as $t): ?>
              <option value="<?= $t['id'] ?>"><?= esc($t['table_number']) ?> (<?= $t['capacity'] ?> seats)</option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Occasion</label>
          <select class="form-control" name="occasion">
            <option value="">None</option>
            <option value="Birthday">Birthday 🎂</option>
            <option value="Anniversary">Anniversary 💍</option>
            <option value="Business Lunch">Business Lunch 💼</option>
            <option value="Date Night">Date Night 🕯</option>
            <option value="Family Gathering">Family Gathering 👨‍👩‍👧</option>
            <option value="Other">Other</option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Special Requests</label>
          <textarea class="form-control" name="special_requests" rows="2" placeholder="Dietary requirements, seating preference..."></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="closeModal('addResModal')">Cancel</button>
        <button type="submit" class="btn btn-primary"><i class="fa fa-calendar-check"></i> Confirm Reservation</button>
      </div>
    </form>
  </div>
</div>

<script>
function updateStatus(id, status, btn) {
  fetch('<?= base_url('admin/reservations/update-status/') ?>' + id, {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: '<?= csrf_token() ?>=<?= csrf_hash() ?>&status=' + status
  }).then(r => r.json()).then(d => {
    if (d.success) { showToast('Status updated', 'success'); setTimeout(() => location.reload(), 800); }
  });
}
</script>
<?php $this->endSection(); ?>
