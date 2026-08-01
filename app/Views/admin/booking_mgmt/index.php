<?php $this->extend('layouts/main'); $this->section('content'); ?>
<div style="padding:0 1rem">

  <!-- Stats -->
  <div class="stats-grid" style="margin-bottom:1rem">
    <div class="stat-card green">
      <div class="stat-icon green"><i class="fa fa-calendar-check"></i></div>
      <div><div class="stat-value"><?= $stats['today'] ?></div><div class="stat-label">Today's Bookings</div></div>
    </div>
    <div class="stat-card orange">
      <div class="stat-icon orange"><i class="fa fa-clock"></i></div>
      <div><div class="stat-value"><?= $stats['pending'] ?></div><div class="stat-label">Pending Approval</div></div>
    </div>
    <div class="stat-card">
      <div class="stat-icon"><i class="fa fa-users"></i></div>
      <div><div class="stat-value"><?= $stats['total_pax'] ?></div><div class="stat-label">Guests Today</div></div>
    </div>
    <div class="stat-card blue">
      <div class="stat-icon blue"><i class="fa fa-calendar"></i></div>
      <div><div class="stat-value"><?= $stats['this_week'] ?></div><div class="stat-label">This Week</div></div>
    </div>
  </div>

  <!-- Filter Bar -->
  <div style="display:flex;gap:.5rem;align-items:center;flex-wrap:wrap;margin-bottom:1rem">
    <form method="GET" style="display:flex;gap:.5rem;flex-wrap:wrap;flex:1">
      <input type="date" name="date" value="<?= $date ?>" class="form-control" style="width:160px">
      <select name="status" class="form-control" style="width:140px">
        <option value="">All Status</option>
        <?php foreach(['pending'=>'Pending','confirmed'=>'Confirmed','cancelled'=>'Cancelled','completed'=>'Completed','no_show'=>'No Show'] as $k=>$v): ?>
        <option value="<?= $k ?>" <?= $status===$k?'selected':'' ?>><?= $v ?></option>
        <?php endforeach; ?>
      </select>
      <button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-filter"></i> Filter</button>
    </form>
    <a href="<?= base_url('admin/booking/settings') ?>" class="btn btn-outline btn-sm"><i class="fa fa-gear"></i> Settings</a>
  </div>

  <!-- Bookings Table -->
  <div class="card">
    <div class="table-wrap">
      <table class="table">
        <thead>
          <tr><th>Booking#</th><th>Guest</th><th>Date & Time</th><th>Guests</th><th>Occasion</th><th>Status</th><th>Actions</th></tr>
        </thead>
        <tbody>
          <?php if (empty($bookings)): ?>
          <tr><td colspan="7">
            <div class="empty-state" style="padding:3rem">
              <i class="fa fa-calendar-xmark"></i>
              <p>No bookings for <?= date('d M Y',strtotime($date)) ?></p>
              <small>Share your booking link: <a href="<?= base_url('book') ?>" target="_blank" style="color:var(--primary)"><?= base_url('book') ?></a></small>
            </div>
          </td></tr>
          <?php else: foreach ($bookings as $b):
            $sc = ['pending'=>'warning','confirmed'=>'success','cancelled'=>'danger','completed'=>'gray','no_show'=>'danger'];
          ?>
          <tr>
            <td>
              <div style="font-weight:800;font-size:.875rem;font-family:monospace"><?= esc($b['booking_number']) ?></div>
              <div style="font-size:.7rem;color:var(--text-muted)"><?= date('h:i A',strtotime($b['created_at'])) ?> booked</div>
            </td>
            <td>
              <div style="font-weight:700;font-size:.875rem"><?= esc($b['guest_name']) ?></div>
              <div style="font-size:.75rem;color:var(--text-muted)">
                <a href="tel:<?= esc($b['guest_phone']) ?>" style="color:var(--primary)"><i class="fa fa-phone"></i> <?= esc($b['guest_phone']) ?></a>
              </div>
              <?php if ($b['guest_email']): ?>
              <div style="font-size:.7rem;color:var(--text-muted)"><?= esc($b['guest_email']) ?></div>
              <?php endif; ?>
            </td>
            <td>
              <div style="font-weight:700"><?= date('d M Y',strtotime($b['slot_date'])) ?></div>
              <div style="font-size:.82rem;color:var(--primary);font-weight:700"><?= date('g:i A',strtotime($b['slot_time'])) ?></div>
            </td>
            <td style="font-weight:800;font-size:1rem;text-align:center"><?= $b['guests'] ?></td>
            <td>
              <?php $ocIcons=['birthday'=>'🎂','anniversary'=>'💍','date'=>'❤️','business'=>'💼','family'=>'👨‍👩‍👧','none'=>'']; ?>
              <span style="font-size:.85rem"><?= ($ocIcons[$b['occasion']]??'').' '.ucfirst($b['occasion']!='none'?$b['occasion']:'—') ?></span>
              <?php if ($b['special_requests']): ?>
              <div style="font-size:.7rem;color:var(--warning);margin-top:.15rem" title="<?= esc($b['special_requests']) ?>">⚡ Special req.</div>
              <?php endif; ?>
            </td>
            <td><span class="badge-pill badge-<?= $sc[$b['status']]??'gray' ?>"><?= ucfirst(str_replace('_',' ',$b['status'])) ?></span></td>
            <td>
              <div style="display:flex;gap:.3rem;flex-wrap:wrap">
                <?php if ($b['status']==='pending'): ?>
                <button onclick="doAction('confirm',<?= $b['id'] ?>)" class="btn btn-sm" style="background:var(--success);color:#fff" title="Confirm">
                  <i class="fa fa-check"></i>
                </button>
                <?php endif; ?>
                <?php if (in_array($b['status'],['pending','confirmed'])): ?>
                <button onclick="doAction('no-show',<?= $b['id'] ?>)" class="btn btn-sm btn-outline" title="No Show" style="color:var(--warning)">
                  <i class="fa fa-user-slash"></i>
                </button>
                <button onclick="doAction('complete',<?= $b['id'] ?>)" class="btn btn-sm btn-outline" title="Completed" style="color:var(--success)">
                  <i class="fa fa-circle-check"></i>
                </button>
                <button onclick="doAction('cancel',<?= $b['id'] ?>)" class="btn btn-sm btn-outline" title="Cancel" style="color:var(--danger)">
                  <i class="fa fa-times"></i>
                </button>
                <?php endif; ?>
              </div>
            </td>
          </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Special requests panel -->
  <?php $withReq = array_filter($bookings, fn($b) => !empty($b['special_requests']) && in_array($b['status'],['confirmed','pending'])); ?>
  <?php if (!empty($withReq)): ?>
  <div class="card" style="margin-top:1rem">
    <div class="card-header"><span class="card-title"><i class="fa fa-note-sticky" style="color:var(--warning)"></i> Special Requests for Today</span></div>
    <?php foreach ($withReq as $b): ?>
    <div style="padding:.75rem 1.25rem;border-bottom:1px solid var(--border)">
      <div style="display:flex;align-items:flex-start;justify-content:space-between">
        <div>
          <div style="font-weight:700;font-size:.875rem"><?= esc($b['guest_name']) ?> · <?= date('g:i A',strtotime($b['slot_time'])) ?></div>
          <div style="font-size:.82rem;color:var(--text-muted);margin-top:.2rem"><?= esc($b['special_requests']) ?></div>
        </div>
        <span class="badge-pill badge-<?= $b['status']==='confirmed'?'success':'warning' ?>"><?= ucfirst($b['status']) ?></span>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

</div>
<script>
function doAction(action, id) {
  const labels = {confirm:'Confirm this booking?', cancel:'Cancel this booking?', 'no-show':'Mark as no-show?', complete:'Mark as completed?'};
  if(!confirm(labels[action]||'Continue?')) return;
  fetch('<?= base_url('admin/booking/') ?>'+action+'/'+id,{
    method:'POST',
    headers:{'Content-Type':'application/x-www-form-urlencoded'},
    body:'<?= csrf_token() ?>=<?= csrf_hash() ?>'
  }).then(r=>r.json()).then(d=>{
    if(d.success){showToast('Updated','success');setTimeout(()=>location.reload(),700);}
    else showToast(d.message||'Failed','error');
  });
}
</script>
<?php $this->endSection(); ?>
