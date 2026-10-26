<?php
// $pay: id, amount, verify_status, order_number, restaurant_name, claimed_by_customer
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Pay ₹<?= number_format((float)$pay['amount'], 2) ?> — <?= esc($pay['restaurant_name']) ?></title>
<style>
  * { margin:0; padding:0; box-sizing:border-box; font-family:-apple-system,'Segoe UI',Roboto,Arial,sans-serif; }
  body { min-height:100vh; background:#0B1E3D; display:flex; align-items:center; justify-content:center; padding:1rem; }
  .sheet { background:#fff; border-radius:20px; max-width:380px; width:100%; padding:1.75rem 1.5rem; text-align:center; }
  .brand { color:#0B1E3D; }
  .brand h1 { font-size:1.05rem; font-weight:800; letter-spacing:.02em; }
  .brand .tag { font-size:.72rem; color:#FD5A01; font-weight:700; letter-spacing:.08em; margin-bottom:1rem; }
  .order-box { background:#FFF4ED; border-radius:12px; padding:.875rem; margin-bottom:1rem; }
  .order-box .o-n { font-weight:800; color:#0B1E3D; font-size:.95rem; }
  .order-box .o-a { font-size:2rem; font-weight:800; color:#FD5A01; }
  .order-box .o-l { font-size:.7rem; color:#8A94A6; }
  .qr { width:230px; height:230px; margin:0 auto .5rem; display:block; border-radius:12px; }
  .steps { text-align:left; font-size:.78rem; color:#5A6472; background:#F5F7FA; border-radius:12px; padding:.75rem .875rem; margin-bottom:.875rem; line-height:1.55; }
  .steps b { color:#0B1E3D; }
  .pay-link { display:inline-block; background:#0B1E3D; color:#fff; font-weight:700; font-size:.85rem;
              padding:.7rem 1.1rem; border-radius:10px; text-decoration:none; margin-bottom:.875rem; }
  .state { font-weight:800; font-size:.9rem; border-radius:10px; padding:.65rem; margin-bottom:.875rem; }
  .st-pending  { background:#FFF7ED; color:#9A3412; border:1px solid #FDBA74; }
  .st-paid     { background:#F0FFF4; color:#047857; border:1px solid #6EE7B7; }
  .st-expired  { background:#F9F5EF; color:#92400E; border:1px solid #E7D8BB; }
  .st-failed,.st-rejected { background:#FEF2F2; color:#B91C1C; border:1px solid #FCA5A5; }
  .claim-btn { width:100%; border:0; background:#FD5A01; color:#fff; font-weight:800; font-size:.95rem;
               padding:.85rem; border-radius:12px; cursor:pointer; }
  .claim-btn:disabled { opacity:.6; cursor:default; }
  .claim-btn[disabled] { background:#94A3B8; }
  .foot { font-size:.68rem; color:#94A3B8; margin-top:.875rem; line-height:1.5; }
</style>
</head>
<body>
<div class="sheet">
  <div class="brand">
    <div class="tag">DINOVIX · SECURE UPI</div>
    <h1><?= esc($pay['restaurant_name']) ?></h1>
  </div>

  <div class="order-box">
    <div class="o-n">Order <?= esc($pay['order_number']) ?></div>
    <div class="o-a">₹<?= number_format((float)$pay['amount'], 2) ?></div>
    <div class="o-l">Amount payable</div>
  </div>

  <?php if ($pay['verify_status'] === 'paid'): ?>
    <div class="state st-paid">✅ Payment confirmed</div>
    <p style="font-size:.78rem;color:#5A6472">Thank you for dining with us!</p>
  <?php else: ?>
    <img class="qr" src="<?= base_url('upi/pay/' . rawurlencode($pay['attempt_ref']) . '/qr') ?>" alt="Payment QR"
         onerror="this.style.display='none'">
    <div class="steps">
      <b>How to pay:</b><br>
      1. Open any UPI app (GPay, PhonePe, Paytm, BHIM).<br>
      2. Scan this QR and pay <b>₹<?= number_format((float)$pay['amount'], 2) ?></b>.<br>
      3. Show the success screen to our staff.
    </div>

    <?php if ($pay['verify_status'] === 'pending' || $pay['verify_status'] === 'expired'): ?>
      <div class="state st-pending" id="stateBox">⏳ Waiting for payment verification…</div>
    <?php else: ?>
      <div class="state st-<?= esc($pay['verify_status']) ?>">
        <?= $pay['verify_status'] === 'expired' ? '⏰ This payment link has expired — please ask our staff' : '❌ This payment was not verified — please ask our staff' ?>
      </div>
    <?php endif; ?>

    <?php if (!empty($pay['upi_uri']) && in_array($pay['verify_status'], ['pending','expired'], true)): ?>
      <a class="pay-link" href="<?= esc($pay['upi_uri']) ?>">📱 Open UPI app to pay</a>
    <?php endif; ?>

    <button class="claim-btn" id="claimBtn" onclick="claim()">
      <?= $pay['claimed_by_customer'] ? 'Claim recorded ✓' : "I've paid" ?>
    </button>

    <div class="foot">
      Money goes directly to <?= esc($pay['restaurant_name']) ?>'s own bank account. DinoviX does not hold your payment.
      Our staff confirm after checking the real transaction in their bank app — showing this screen does not confirm it automatically.
    </div>
  <?php endif; ?>
</div>

<script>
const REF = '<?= esc($pay['attempt_ref'] ?? '') ?>';

function claim() {
  const btn = document.getElementById('claimBtn');
  if (!btn || btn.disabled) return;
  btn.disabled = true;
  btn.textContent = 'Recording…';
  fetch('<?= base_url('upi/pay/') ?>/' + REF + '/claim', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
      body: ''
    }).then(r => r.json()).then(d => {
      btn.textContent = d.success ? 'Claim recorded ✓' : 'Could not record';
      if (d.success) setTimeout(() => location.reload(), 1200);
      else btn.textContent = "I've paid";
    }).catch(() => { btn.textContent = "I've paid"; })
    .finally(() => { setTimeout(() => { btn.disabled = false; }, 1500); });
}

<?php if ($pay['verify_status'] === 'pending'): ?>
// Poll status every 4s; stop when resolved
const timer = setInterval(() => {
  fetch('<?= base_url('upi/pay/') ?>/' + REF + '/status')
    .then(r => r.json()).then(d => {
      if (!d.success) { clearInterval(timer); return; }
      if (d.state === 'paid')    { clearInterval(timer); location.reload(); }
      if (d.state !== 'pending') { clearInterval(timer); location.reload(); }
    }).catch(() => {});
}, 4000);
setTimeout(() => { clearInterval(timer); }, 15 * 60 * 1000);
window.addEventListener('pagehide', () => clearInterval(timer));
<?php endif; ?>
</script>
</body>
</html>
