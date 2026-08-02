<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=no,viewport-fit=cover">
<meta name="apple-mobile-web-app-capable" content="yes">
<title>Booking <?= ucfirst($booking['status']) ?> — DinoviX</title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
html{height:100%;-webkit-tap-highlight-color:transparent}
body{font-family:'Plus Jakarta Sans',system-ui,sans-serif;background:#F8FAFC;color:#0F172A;min-height:100%;padding:0}
:root{--primary:#FF6B35;--dark:#0F172A;--success:#22C55E;--warning:#F59E0B;--danger:#EF4444;--border:#E2E8F0;--bg:#F8FAFC;--radius:14px}

/* Header gradient by status */
.conf-header{
  padding:calc(3rem + env(safe-area-inset-top)) 1.5rem 2rem;
  text-align:center;position:relative;overflow:hidden;
}
<?php $s=$booking['status']; ?>
<?php if($s==='confirmed'): ?>
.conf-header{background:linear-gradient(160deg,#064E3B,#065F46,#059669)}
<?php elseif($s==='pending'): ?>
.conf-header{background:linear-gradient(160deg,#78350F,#92400E,#D97706)}
<?php else: ?>
.conf-header{background:linear-gradient(160deg,#1E293B,#334155)}
<?php endif; ?>
.conf-header::before{content:'';position:absolute;inset:0;background:radial-gradient(circle at 70% 50%,rgba(255,255,255,.06),transparent 65%);pointer-events:none}
.conf-icon{font-size:4.5rem;margin-bottom:.875rem;display:block;animation:popIn .5s cubic-bezier(.17,.67,.32,1.4) both}
@keyframes popIn{from{transform:scale(0);opacity:0}to{transform:scale(1);opacity:1}}
.conf-title{font-size:1.5rem;font-weight:900;color:#fff;margin-bottom:.35rem}
.conf-sub{font-size:.875rem;color:rgba(255,255,255,.7);margin-bottom:1rem}
.conf-num{
  display:inline-block;background:rgba(255,255,255,.15);
  border:1px solid rgba(255,255,255,.25);border-radius:10px;
  padding:.4rem 1.1rem;font-size:.85rem;font-weight:800;
  color:#fff;letter-spacing:.06em;font-family:monospace;
}

/* Body */
.conf-body{max-width:420px;margin:0 auto;padding:0 1rem calc(2rem + env(safe-area-inset-bottom))}

/* Cards */
.ccard{background:#fff;border-radius:var(--radius);margin-top:.875rem;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,.06)}
.ccard-hdr{padding:.75rem 1.1rem;border-bottom:1px solid var(--border);font-size:.72rem;font-weight:800;color:#64748B;text-transform:uppercase;letter-spacing:.06em;display:flex;align-items:center;gap:.4rem}
.ccard-body{padding:1rem 1.1rem}
.ccard-row{display:flex;justify-content:space-between;align-items:flex-start;padding:.35rem 0;border-bottom:1px solid #F8FAFC;font-size:.875rem}
.ccard-row:last-child{border-bottom:none}
.ccard-lbl{color:#64748B}
.ccard-val{font-weight:700;text-align:right;max-width:220px}

/* Restaurant info */
.rest-info{display:flex;align-items:flex-start;gap:.875rem;padding:1rem 1.1rem}
.rest-avatar{width:52px;height:52px;border-radius:12px;background:linear-gradient(135deg,#FF6B35,#FF8C5A);color:#fff;display:flex;align-items:center;justify-content:center;font-size:1.5rem;flex-shrink:0;overflow:hidden}
.rest-avatar img{width:100%;height:100%;object-fit:cover}
.rest-name{font-weight:800;font-size:.975rem;margin-bottom:.2rem}
.rest-addr{font-size:.75rem;color:#64748B;line-height:1.45;margin-bottom:.35rem}
.rest-call{display:inline-flex;align-items:center;gap:.3rem;color:var(--primary);font-size:.75rem;font-weight:700}

/* Deposit Notice */
.dep-notice{background:#FFFBEB;border:1px solid #FDE68A;border-radius:10px;padding:.875rem;margin:.75rem 1.1rem;font-size:.82rem;line-height:1.55;display:flex;align-items:flex-start;gap:.5rem}
.dep-notice i{color:var(--warning);margin-top:.15rem;flex-shrink:0}

/* Share Strip */
.share-strip{padding:1rem 1.1rem;display:flex;gap:.5rem}
.share-btn{flex:1;padding:.65rem;border-radius:10px;font-size:.78rem;font-weight:800;border:none;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:.35rem;transition:opacity .15s;font-family:inherit}
.share-btn:active{opacity:.8}
.btn-wa{background:#25D366;color:#fff}
.btn-copy{background:#E2E8F0;color:#334155}

/* Action Buttons */
.actions{padding:0 1rem .5rem;display:flex;flex-direction:column;gap:.5rem}
.act-btn{
  width:100%;padding:.8rem;border-radius:12px;font-size:.875rem;
  font-weight:800;border:none;cursor:pointer;font-family:inherit;
  display:flex;align-items:center;justify-content:center;gap:.4rem;
  transition:all .18s;
}
.act-primary{background:linear-gradient(135deg,#FF6B35,#FF8C5A);color:#fff;box-shadow:0 4px 16px rgba(255,107,53,.3)}
.act-primary:hover{transform:translateY(-1px);box-shadow:0 6px 20px rgba(255,107,53,.4)}
.act-outline{background:#fff;border:1.5px solid var(--border);color:#334155}
.act-outline:hover{background:#F8FAFC}
.act-danger{background:#FEF2F2;color:#DC2626;border:1.5px solid #FECACA}
.act-danger:hover{background:#FEE2E2}

/* Map Hint */
.map-hint{margin:.75rem 1.1rem;background:#EFF6FF;border-radius:10px;padding:.75rem;font-size:.78rem;color:#1D4ED8;display:flex;align-items:center;gap:.5rem}
</style>
</head>
<body>

<?php
$icons   = ['confirmed'=>'✅','pending'=>'⏳','cancelled'=>'❌','completed'=>'🎉','no_show'=>'🚫'];
$titles  = ['confirmed'=>'Booking Confirmed!','pending'=>'Request Sent!','cancelled'=>'Booking Cancelled','completed'=>'Visit Complete!'];
$subs    = ['confirmed'=>'Your table is reserved. Just show up!','pending'=>'Waiting for restaurant confirmation.','cancelled'=>'This booking has been cancelled.'];
$s       = $booking['status'];
?>

<!-- Header -->
<div class="conf-header">
  <span class="conf-icon"><?= $icons[$s] ?? '📋' ?></span>
  <div class="conf-title"><?= $titles[$s] ?? 'Booking Submitted' ?></div>
  <div class="conf-sub"><?= $subs[$s] ?? '' ?></div>
  <div class="conf-num"><?= esc($booking['booking_number']) ?></div>
</div>

<div class="conf-body">

  <!-- Restaurant Card -->
  <div class="ccard">
    <div class="ccard-hdr"><i class="fa fa-store"></i> Restaurant</div>
    <div class="rest-info">
      <div class="rest-avatar">
        <?php if (!empty($booking['cover_image'])): ?>
        <img src="<?= base_url('images/uploads/'.$booking['cover_image']) ?>" alt="">
        <?php else: ?>🍽<?php endif; ?>
      </div>
      <div>
        <div class="rest-name"><?= esc($booking['rname']) ?></div>
        <?php if (!empty($booking['bname'])): ?>
        <div style="font-size:.72rem;font-weight:700;color:var(--primary);margin-bottom:.2rem"><?= esc($booking['bname']) ?></div>
        <?php endif; ?>
        <div class="rest-addr">
          <?= esc($booking['baddress'] ?: ($booking['raddress'] ?? '')) ?>
          <?php if ($booking['rcity']): ?>, <?= esc($booking['rcity']) ?><?php endif; ?>
        </div>
        <?php $phone = $booking['bphone'] ?: ($booking['rphone'] ?? ''); if ($phone): ?>
        <a href="tel:<?= esc($phone) ?>" class="rest-call"><i class="fa fa-phone"></i> <?= esc($phone) ?></a>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Booking Details Card -->
  <div class="ccard">
    <div class="ccard-hdr"><i class="fa fa-calendar-check"></i> Booking Details</div>
    <div class="ccard-body">
      <div class="ccard-row"><span class="ccard-lbl">Date</span><span class="ccard-val"><?= date('l, d M Y', strtotime($booking['slot_date'])) ?></span></div>
      <div class="ccard-row"><span class="ccard-lbl">Time</span><span class="ccard-val" style="color:var(--primary);font-size:1rem"><?= date('g:i A', strtotime($booking['slot_time'])) ?></span></div>
      <div class="ccard-row"><span class="ccard-lbl">Guests</span><span class="ccard-val"><?= $booking['guests'] ?> person<?= $booking['guests']>1?'s':'' ?></span></div>
      <?php if ($booking['occasion'] && $booking['occasion']!=='none'): ?>
      <div class="ccard-row"><span class="ccard-lbl">Occasion</span><span class="ccard-val"><?= ucfirst($booking['occasion']) ?> 🎉</span></div>
      <?php endif; ?>
      <?php if (!empty($booking['special_requests'])): ?>
      <div class="ccard-row"><span class="ccard-lbl">Requests</span><span class="ccard-val" style="font-style:italic;font-weight:500;color:#64748B"><?= esc($booking['special_requests']) ?></span></div>
      <?php endif; ?>
      <div class="ccard-row"><span class="ccard-lbl">Status</span>
        <span class="ccard-val">
          <?php $sclr=['confirmed'=>'#059669','pending'=>'#D97706','cancelled'=>'#DC2626','completed'=>'#2563EB']; ?>
          <span style="color:<?= $sclr[$s]??'#64748B' ?>;font-weight:900"><?= ucfirst($s) ?></span>
        </span>
      </div>
      <?php if ($booking['confirmed_at']): ?>
      <div class="ccard-row"><span class="ccard-lbl">Confirmed at</span><span class="ccard-val" style="font-size:.78rem"><?= date('d M Y, g:i A', strtotime($booking['confirmed_at'])) ?></span></div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Deposit Card -->
  <?php if ($booking['payment_amount'] > 0): ?>
  <div class="dep-notice">
    <i class="fa fa-bolt"></i>
    <div>
      <strong>Deposit: ₹<?= number_format($booking['payment_amount'],2) ?></strong> ·
      <?php $pst=['not_required'=>'Not required','pending'=>'Pay on arrival','paid'=>'Paid ✓','refunded'=>'Refunded']; ?>
      <?= $pst[$booking['payment_status']] ?? ucfirst($booking['payment_status']) ?>
    </div>
  </div>
  <?php endif; ?>

  <!-- Map hint if address exists -->
  <?php $addr = trim(($booking['baddress'] ?: $booking['raddress'] ?? '') . ' ' . ($booking['rcity'] ?? '')); ?>
  <?php if ($addr): ?>
  <a href="https://maps.google.com/?q=<?= urlencode($addr) ?>" target="_blank" class="map-hint" style="text-decoration:none">
    <i class="fa fa-map-location-dot"></i>
    <span>Get Directions → <strong><?= esc(rtrim($addr,', ')) ?></strong></span>
  </a>
  <?php endif; ?>

  <!-- Share Strip -->
  <div class="ccard" style="margin-top:.875rem">
    <div class="ccard-hdr"><i class="fa fa-share-nodes"></i> Share Booking</div>
    <div class="share-strip">
      <button class="share-btn btn-wa" onclick="shareWA()"><i class="fa-brands fa-whatsapp"></i> WhatsApp</button>
      <button class="share-btn btn-copy" onclick="copyID()"><i class="fa fa-copy"></i> Copy ID</button>
    </div>
  </div>

  <!-- Actions -->
  <div style="margin-top:1rem">
    <div class="actions">
      <a href="<?= base_url('book') ?>" class="act-btn act-primary"><i class="fa fa-search"></i> Discover More Restaurants</a>
      <a href="<?= base_url('book/status/'.$booking['booking_number']) ?>" class="act-btn act-outline"><i class="fa fa-clock-rotate-left"></i> View Booking Status</a>
      <?php if (in_array($s,['pending','confirmed'])): ?>
      <button class="act-btn act-danger" onclick="cancelBooking()"><i class="fa fa-circle-xmark"></i> Cancel Booking</button>
      <?php endif; ?>
    </div>
  </div>

</div>

<script>
const NUM   = '<?= esc($booking['booking_number']) ?>';
const RNAME = '<?= esc($booking['rname']) ?>';
const DATE  = '<?= date('d M Y', strtotime($booking['slot_date'])) ?>';
const TIME  = '<?= date('g:i A', strtotime($booking['slot_time'])) ?>';
const BASE  = '<?= base_url() ?>';
const CN    = '<?= csrf_token() ?>';
const CT    = '<?= csrf_hash() ?>';

function shareWA() {
  const msg = `🍽 Table booked at *${RNAME}*\n📅 ${DATE} at ${TIME}\n👤 ${<?= (int)$booking['guests'] ?>} guest<?= $booking['guests']>1?'s':'' ?>\n🎟 Booking ID: *${NUM}*\nBooked via DinoviX`;
  window.open('https://wa.me/?text=' + encodeURIComponent(msg), '_blank');
}

function copyID() {
  navigator.clipboard?.writeText(NUM).then(() => {
    const btn = event.target.closest('.share-btn');
    btn.innerHTML = '<i class="fa fa-check"></i> Copied!';
    setTimeout(() => { btn.innerHTML = '<i class="fa fa-copy"></i> Copy ID'; }, 2000);
  });
}

async function cancelBooking() {
  if (!confirm('Cancel this booking? This cannot be undone.')) return;
  const d = await fetch(BASE + 'book/cancel/' + NUM, {
    method: 'POST',
    body: new URLSearchParams({ [CN]: CT, reason: 'Guest cancelled via app' })
  }).then(r => r.json());
  if (d.success) { location.reload(); }
  else alert(d.message || 'Could not cancel. Please try again.');
}
</script>
</body>
</html>
