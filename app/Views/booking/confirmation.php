<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1">
<title>Booking Confirmed — DinoViX</title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Plus Jakarta Sans',sans-serif;background:#F8FAFC;min-height:100vh;padding:1.5rem 1rem}
.card{background:#fff;border-radius:20px;padding:1.5rem;max-width:420px;margin:0 auto;box-shadow:0 4px 24px rgba(0,0,0,.08)}
.top{text-align:center;margin-bottom:1.5rem}
.badge{width:80px;height:80px;border-radius:50%;margin:0 auto .875rem;display:flex;align-items:center;justify-content:center;font-size:2.5rem}
.badge.confirmed{background:#D1FAE5}
.badge.pending{background:#FEF3C7}
h2{font-weight:900;font-size:1.3rem;margin-bottom:.35rem}
.num{font-size:.82rem;color:#64748B}
.num b{color:#FF6B35;font-family:'JetBrains Mono',monospace}
.divider{border-top:1px dashed #E2E8F0;margin:1rem 0}
.row{display:flex;justify-content:space-between;padding:.35rem 0;font-size:.875rem}
.row .lbl{color:#64748B}
.row .val{font-weight:700;text-align:right}
.rest-name{font-size:1rem;font-weight:800;margin-bottom:.25rem}
.rest-addr{font-size:.78rem;color:#64748B}
.actions{display:flex;flex-direction:column;gap:.5rem;margin-top:1.25rem}
.btn{padding:.75rem;border-radius:10px;font-weight:700;font-size:.875rem;border:none;cursor:pointer;font-family:inherit;display:flex;align-items:center;justify-content:center;gap:.4rem}
.btn-primary{background:linear-gradient(135deg,#FF6B35,#FF8C5A);color:#fff}
.btn-outline{background:#fff;border:1.5px solid #E2E8F0;color:#334155}
.btn-danger{background:#FEF2F2;color:#DC2626;border:1.5px solid #FECACA}
.share-strip{background:#F0F9FF;border-radius:10px;padding:.75rem;margin-top:.875rem;text-align:center}
.share-strip p{font-size:.78rem;color:#0369A1;font-weight:600;margin-bottom:.5rem}
.share-btns{display:flex;gap:.4rem;justify-content:center}
.share-btn{padding:.4rem .875rem;border-radius:8px;font-size:.75rem;font-weight:700;border:none;cursor:pointer;font-family:inherit}
.share-wa{background:#25D366;color:#fff}
.share-copy{background:#E2E8F0;color:#334155}
</style>
</head>
<body>
<?php
$sc = ['confirmed'=>'✅','pending'=>'⏳','cancelled'=>'❌'];
$sb = ['confirmed'=>'confirmed','pending'=>'pending'];
$msg= ['confirmed'=>'Booking Confirmed!','pending'=>'Booking Requested!'];
$sub= ['confirmed'=>'Your table is reserved. See you soon!','pending'=>'Awaiting restaurant confirmation.'];
$s  = $booking['status'];
?>

<div class="card">
  <div class="top">
    <div class="badge <?= $sb[$s]??'pending' ?>"><?= $sc[$s]??'⏳' ?></div>
    <h2><?= $msg[$s]??'Booking Submitted' ?></h2>
    <p style="font-size:.85rem;color:#64748B;margin-bottom:.625rem"><?= $sub[$s]??'' ?></p>
    <div class="num">Booking ID: <b><?= esc($booking['booking_number']) ?></b></div>
  </div>

  <!-- Restaurant Info -->
  <div style="background:#F8FAFC;border-radius:12px;padding:.875rem;margin-bottom:.875rem">
    <div class="rest-name"><?= esc($booking['rname']) ?></div>
    <?php if (!empty($booking['bname'])): ?>
    <div style="font-size:.78rem;font-weight:600;color:#FF6B35;margin-bottom:.2rem"><?= esc($booking['bname']) ?></div>
    <?php endif; ?>
    <div class="rest-addr">
      <?= esc($booking['baddress'] ?: $booking['raddress'] ?? '') ?>
      <?php if ($booking['rcity']): ?>, <?= esc($booking['rcity']) ?><?php endif; ?>
    </div>
    <?php if ($booking['rphone'] || $booking['bphone']): ?>
    <a href="tel:<?= esc($booking['bphone'] ?: $booking['rphone']) ?>" style="color:#FF6B35;font-size:.78rem;font-weight:700;display:flex;align-items:center;gap:.3rem;margin-top:.35rem">
      <i class="fa fa-phone"></i> <?= esc($booking['bphone'] ?: $booking['rphone']) ?>
    </a>
    <?php endif; ?>
  </div>

  <!-- Booking Details -->
  <div class="divider"></div>
  <div class="row"><span class="lbl">Date</span><span class="val"><?= date('l, d M Y',strtotime($booking['slot_date'])) ?></span></div>
  <div class="row"><span class="lbl">Time</span><span class="val"><?= date('g:i A',strtotime($booking['slot_time'])) ?></span></div>
  <div class="row"><span class="lbl">Guests</span><span class="val"><?= $booking['guests'] ?> person<?= $booking['guests']>1?'s':'' ?></span></div>
  <?php if ($booking['occasion'] && $booking['occasion']!=='none'): ?>
  <div class="row"><span class="lbl">Occasion</span><span class="val"><?= ucfirst($booking['occasion']) ?> 🎉</span></div>
  <?php endif; ?>
  <?php if ($booking['special_requests']): ?>
  <div class="row"><span class="lbl">Requests</span><span class="val" style="max-width:200px;text-align:right"><?= esc($booking['special_requests']) ?></span></div>
  <?php endif; ?>

  <?php if ($booking['payment_status']==='pending' && $booking['payment_amount']>0): ?>
  <div class="divider"></div>
  <div style="background:#FFFBEB;border-radius:10px;padding:.75rem;font-size:.82rem">
    <div style="font-weight:700;color:#D97706;margin-bottom:.2rem">⚡ Deposit Required</div>
    <div style="color:#92400E">Please pay ₹<?= number_format($booking['payment_amount'],2) ?> to the restaurant on arrival to confirm your seat.</div>
  </div>
  <?php endif; ?>

  <!-- Share strip -->
  <div class="share-strip">
    <p><i class="fa fa-share-nodes"></i> Share this booking</p>
    <div class="share-btns">
      <button class="share-btn share-wa" onclick="shareWA()"><i class="fa-brands fa-whatsapp"></i> WhatsApp</button>
      <button class="share-btn share-copy" onclick="copyNum()"><i class="fa fa-copy"></i> Copy ID</button>
    </div>
  </div>

  <!-- Actions -->
  <div class="actions">
    <a href="<?= base_url('book') ?>" class="btn btn-primary"><i class="fa fa-search"></i> Discover More</a>
    <a href="<?= base_url('book/status/'.$booking['booking_number']) ?>" class="btn btn-outline"><i class="fa fa-clock-rotate-left"></i> Check Booking Status</a>
    <button class="btn btn-danger" onclick="cancelBooking()"><i class="fa fa-circle-xmark"></i> Cancel Booking</button>
  </div>
</div>

<script>
const NUM  = '<?= esc($booking['booking_number']) ?>';
const RNAME= '<?= esc($booking['rname']) ?>';
const DATE = '<?= date('d M Y',strtotime($booking['slot_date'])) ?>';
const TIME = '<?= date('g:i A',strtotime($booking['slot_time'])) ?>';
const BASE = '<?= base_url() ?>';
const CN   = '<?= csrf_token() ?>';
const CT   = '<?= csrf_hash() ?>';

function shareWA(){
  const msg = `My table is booked at ${RNAME}! 🍽\n📅 ${DATE} at ${TIME}\n🎟 Booking ID: ${NUM}\nBooked via DinoViX`;
  window.open('https://wa.me/?text='+encodeURIComponent(msg),'_blank');
}
function copyNum(){
  navigator.clipboard.writeText(NUM).then(()=>alert('Booking ID copied!'));
}
async function cancelBooking(){
  if(!confirm('Cancel this booking?')) return;
  const d = await fetch(BASE+'book/cancel/'+NUM,{
    method:'POST',
    body:new URLSearchParams({[CN]:CT,reason:'Guest cancelled'})
  }).then(r=>r.json());
  if(d.success){ alert('Booking cancelled'); location.reload(); }
  else alert(d.message||'Could not cancel');
}
</script>
</body>
</html>
