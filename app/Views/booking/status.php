<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Booking Status — DinoViX</title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Plus Jakarta Sans',sans-serif;background:#F8FAFC;min-height:100vh;padding:1.5rem 1rem}
.card{background:#fff;border-radius:20px;padding:1.5rem;max-width:400px;margin:0 auto;box-shadow:0 4px 24px rgba(0,0,0,.08)}
.badge-lg{width:72px;height:72px;border-radius:50%;margin:0 auto 1rem;display:flex;align-items:center;justify-content:center;font-size:2.2rem}
.badge-confirmed{background:#D1FAE5} .badge-pending{background:#FEF3C7}
.badge-cancelled{background:#FEE2E2} .badge-completed{background:#E0E7FF} .badge-no_show{background:#FEE2E2}
h2{font-weight:900;font-size:1.2rem;text-align:center;margin-bottom:.35rem}
.status-chip{display:inline-flex;align-items:center;gap:.35rem;padding:.35rem 1rem;border-radius:20px;font-size:.78rem;font-weight:800;margin:0 auto .875rem;display:flex;justify-content:center}
.chip-confirmed{background:#D1FAE5;color:#065F46} .chip-pending{background:#FEF3C7;color:#92400E}
.chip-cancelled{background:#FEE2E2;color:#991B1B} .chip-completed{background:#EDE9FE;color:#5B21B6}
.divider{border-top:1px dashed #E2E8F0;margin:.875rem 0}
.row{display:flex;justify-content:space-between;padding:.35rem 0;font-size:.85rem}
.row .lbl{color:#64748B} .row .val{font-weight:700}
.btn{width:100%;padding:.75rem;border-radius:10px;font-weight:700;font-size:.875rem;border:none;cursor:pointer;font-family:inherit;margin-top:.5rem;display:flex;align-items:center;justify-content:center;gap:.4rem}
.btn-primary{background:linear-gradient(135deg,#FF6B35,#FF8C5A);color:#fff}
.btn-outline{background:#fff;border:1.5px solid #E2E8F0;color:#334155}
.btn-danger{background:#FEF2F2;color:#DC2626;border:1.5px solid #FECACA}
/* Lookup form */
.lookup{max-width:400px;margin:0 auto;text-align:center}
.lookup h2{margin-bottom:1rem;font-size:1.1rem}
.lookup input{width:100%;padding:.75rem;border:1.5px solid #E2E8F0;border-radius:10px;font-size:.9rem;font-family:inherit;outline:none;text-align:center;text-transform:uppercase;letter-spacing:.08em;margin-bottom:.625rem}
.lookup input:focus{border-color:#FF6B35}
</style>
</head>
<body>
<?php if (!$booking): ?>
<div class="lookup">
  <h2>Check Booking Status</h2>
  <form method="GET">
    <input type="text" name="num" placeholder="Enter Booking ID (e.g. DVX240101ABCD)" autofocus>
    <button type="submit" style="width:100%;padding:.75rem;background:linear-gradient(135deg,#FF6B35,#FF8C5A);color:#fff;border:none;border-radius:10px;font-weight:800;font-size:.9rem;cursor:pointer;font-family:inherit">Check Status</button>
  </form>
  <?php if (service('request')->getGet('num')): ?>
  <div style="margin-top:1rem;color:#EF4444;font-weight:600">Booking not found. Check the ID and try again.</div>
  <?php endif; ?>
</div>
<?php else:
$s = $booking['status'];
$icons = ['confirmed'=>'✅','pending'=>'⏳','cancelled'=>'❌','completed'=>'🎉','no_show'=>'🚫'];
?>
<div class="card">
  <div style="text-align:center">
    <div class="badge-lg badge-<?= $s ?>"><?= $icons[$s]??'📋' ?></div>
    <h2><?= esc($booking['rname']) ?></h2>
    <div class="status-chip chip-<?= $s ?>"><i class="fa fa-circle-dot"></i> <?= ucfirst(str_replace('_',' ',$s)) ?></div>
    <div style="font-size:.78rem;color:#64748B;font-family:monospace;background:#F8FAFC;padding:.35rem .875rem;border-radius:8px;display:inline-block"><?= esc($booking['booking_number']) ?></div>
  </div>
  <div class="divider"></div>
  <div class="row"><span class="lbl">Guest</span><span class="val"><?= esc($booking['guest_name']) ?></span></div>
  <div class="row"><span class="lbl">Date</span><span class="val"><?= date('D, d M Y',strtotime($booking['slot_date'])) ?></span></div>
  <div class="row"><span class="lbl">Time</span><span class="val"><?= date('g:i A',strtotime($booking['slot_time'])) ?></span></div>
  <div class="row"><span class="lbl">Guests</span><span class="val"><?= $booking['guests'] ?></span></div>
  <?php if ($booking['occasion']!='none'): ?>
  <div class="row"><span class="lbl">Occasion</span><span class="val"><?= ucfirst($booking['occasion']) ?></span></div>
  <?php endif; ?>
  <?php if ($booking['payment_amount'] > 0): ?>
  <div class="row"><span class="lbl">Deposit</span><span class="val" style="color:<?= $booking['payment_status']==='paid'?'var(--success,#22C55E)':'#F59E0B' ?>">₹<?= number_format($booking['payment_amount'],2) ?> · <?= ucfirst($booking['payment_status']) ?></span></div>
  <?php endif; ?>
  <div class="divider"></div>
  <a href="<?= base_url('book') ?>" class="btn btn-primary"><i class="fa fa-home"></i> Back to DinoViX</a>
  <?php if (in_array($s,['pending','confirmed'])): ?>
  <button class="btn btn-danger" onclick="cancel()"><i class="fa fa-circle-xmark"></i> Cancel Booking</button>
  <?php endif; ?>
</div>
<script>
async function cancel(){
  if(!confirm('Cancel this booking?')) return;
  const d = await fetch('<?= base_url('book/cancel/'.$booking['booking_number']) ?>',{
    method:'POST',
    body:new URLSearchParams({'<?= csrf_token() ?>':'<?= csrf_hash() ?>',reason:'Guest cancelled'})
  }).then(r=>r.json());
  if(d.success){location.reload();}
  else alert(d.message||'Error');
}
</script>
<?php endif; ?>
</body>
</html>
