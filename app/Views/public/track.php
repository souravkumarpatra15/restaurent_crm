<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Order <?= esc($order['order_number'] ?? '') ?> — Track</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<style>
:root {
  --p: <?= esc($theme) ?>;
  --pg: linear-gradient(135deg, #0B132B 0%, #17244A 100%);
  --card: #1C2A4A;
  --card2: rgba(255,255,255,.06);
  --ink: #EDF1FB;
  --mut: rgba(237,241,251,.55);
  --ok: #34D399;
  --bad: #F87171;
}
* { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', system-ui, sans-serif; }
body { background: var(--pg); color: var(--ink); min-height: 100vh; padding-bottom: 2rem; }

/* poll bar */
.poll-bar { position: fixed; top: 0; left: 0; height: 3px; background: var(--p); transition: width 1.2s linear; z-index: 9; }

.hero { padding: 1.75rem 1.25rem 1.25rem; text-align: center; }
.hero-rest { font-size: .78rem; letter-spacing: .08em; text-transform: uppercase; color: var(--mut); font-weight: 700; }
.hero-num { font-size: 2rem; font-weight: 900; margin-top: .3rem; letter-spacing: .02em; }
.hero-sub { font-size: .75rem; color: var(--mut); margin-top: .25rem; display: flex; gap: .4rem; justify-content: center; align-items: center; }

.chips { display: flex; gap: .4rem; justify-content: center; margin-top: .6rem; flex-wrap: wrap; }
.chip { font-size: .68rem; font-weight: 800; letter-spacing: .05em; text-transform: uppercase; padding: .3rem .7rem; border-radius: 99px; }
.chip-pay-unpaid { background: rgba(248,113,113,.15); color: var(--bad); border: 1px solid rgba(248,113,113,.4); }
.chip-pay-paid   { background: rgba(52,211,153,.15); color: var(--ok); border: 1px solid rgba(52,211,153,.4); }
.chip-type { background: var(--card2); color: var(--mut); border: 1px solid rgba(255,255,255,.12); }

/* live icon */
.pulse { width: 78px; height: 78px; margin: 1.1rem auto .8rem; border-radius: 50%; position: relative;
  display: flex; align-items: center; justify-content: center; font-size: 2.1rem; background: var(--card); }
.pulse::before, .pulse::after { content: ''; position: absolute; inset: -10px; border-radius: 50%; border: 2px solid var(--p); opacity: .35; animation: ring 2.2s ease-out infinite; }
.pulse.done::before, .pulse.done::after { animation: none; opacity: .15; }
.pulse.cancel::before, .pulse.cancel::after { border-color: var(--bad); animation: none; opacity: .3; }
@keyframes ring { 0% { transform: scale(.85); opacity: .5; } 100% { transform: scale(1.25); opacity: 0; } }

.status-label { font-size: 1.25rem; font-weight: 900; text-align: center; }
.status-msg { font-size: .8rem; color: var(--mut); text-align: center; margin-top: .3rem; padding: 0 1rem; }

/* timer */
.timer-box { margin: 1.1rem 1.25rem; background: var(--card2); border: 1px solid rgba(255,255,255,.09); border-radius: 14px; padding: .8rem; display: flex; align-items: center; justify-content: center; gap: .8rem; }
.timer-val { font-size: 1.6rem; font-weight: 900; color: var(--p); }
.timer-lbl { font-size: .68rem; color: var(--mut); text-transform: uppercase; letter-spacing: .08em; line-height: 1.5; }

/* steps */
.steps { margin: .9rem 1.25rem; background: var(--card); border: 1px solid rgba(255,255,255,.08); border-radius: 16px; padding: .4rem .9rem; }
.step { display: flex; align-items: flex-start; gap: .8rem; padding: .7rem 0; position: relative; }
.step:not(:last-child)::after {
  content: ''; position: absolute; left: 15px; top: 40px; width: 2px; height: calc(100% - 14px);
  background: rgba(255,255,255,.1);
}
.step.done:not(:last-child)::after { background: var(--p); }
.step-dot { width: 32px; height: 32px; border-radius: 50%; flex-shrink: 0; display: flex; align-items: center; justify-content: center;
  font-size: .9rem; background: var(--card2); color: rgba(237,241,251,.35); border: 2px solid rgba(255,255,255,.1); z-index: 1; transition: all .3s; }
.step.done .step-dot { background: var(--p); color: #fff; border-color: transparent; }
.step.active .step-dot { border-color: var(--p); color: var(--p); box-shadow: 0 0 0 4px rgba(255,255,255,.06); animation: dotPulse 1.6s infinite; }
@keyframes dotPulse { 0%,100% { box-shadow: 0 0 0 3px rgba(255,255,255,.05); } 50% { box-shadow: 0 0 0 7px rgba(255,255,255,.08); } }
.step-info { min-width: 0; }
.step-name { font-size: .84rem; font-weight: 800; color: rgba(237,241,251,.4); padding-top: .45rem; }
.step.done .step-name, .step.active .step-name { color: var(--ink); }
.step-desc { font-size: .7rem; color: var(--mut); }
.step.active .step-desc { color: var(--p); }

/* items card */
.items-box { margin: .9rem 1.25rem; background: var(--card); border: 1px solid rgba(255,255,255,.08); border-radius: 16px; padding: .85rem .95rem; }
.items-head { display: flex; justify-content: space-between; align-items: center; margin-bottom: .55rem; }
.items-title { font-size: .68rem; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; color: var(--mut); }
.items-count { font-size: .72rem; font-weight: 800; color: var(--p); }
.item-row { display: flex; justify-content: space-between; gap: .6rem; padding: .32rem 0; border-top: 1px solid rgba(255,255,255,.06); font-size: .78rem; }
.item-row:first-of-type { border-top: 0; }
.item-name { flex: 1; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.item-name span { color: var(--mut); font-size: .7rem; }
.item-st { font-size: .66rem; font-weight: 800; text-transform: uppercase; letter-spacing: .04em; padding: .14rem .5rem; border-radius: 99px; flex-shrink: 0; }
.st-pending   { background: rgba(255,255,255,.08); color: var(--mut); }
.st-ready     { background: rgba(52,211,153,.14); color: var(--ok); }
.st-served    { background: rgba(52,211,153,.14); color: var(--ok); }

/* rider box */
.rider-box { margin: .9rem 1.25rem; background: var(--card2); border: 1px dashed rgba(255,255,255,.2); border-radius: 14px; padding: .8rem .95rem; display: none; }
.rider-box.show { display: block; }
.rider-t { font-size: .68rem; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; color: var(--mut); margin-bottom: .35rem; }
.rider-name { font-size: .95rem; font-weight: 800; }
.rider-phone { display: inline-block; margin-top: .45rem; font-size: .8rem; font-weight: 800; color: #fff; background: var(--p); padding: .5rem .95rem; border-radius: 10px; text-decoration: none; }

/* footer info */
.foot { margin: 1rem 1.25rem; text-align: center; font-size: .72rem; color: var(--mut); line-height: 1.7; }
.foot b { color: var(--ink); }
.empty { display: flex; align-items: center; justify-content: center; min-height: 70vh; text-align: center; flex-direction: column; gap: .8rem; padding: 1.5rem; }
.empty i { font-size: 2.6rem; opacity: .5; font-style: normal; }
</style>
</head>
<body>
<?php if (empty($order)): ?>
  <div class="empty">
    <i>🔍</i>
    <div class="status-label">Track link not found</div>
    <div class="status-msg">This tracking link is invalid. Ask the restaurant for a fresh bill.</div>
  </div>
<?php else: ?>
<div class="poll-bar" id="pollBar" style="width:0%"></div>

  <!-- Hero -->
  <div class="hero">
    <div class="hero-rest"><?= esc($order['restaurant_name']) ?><?= $order['branch_name'] ? ' · ' . esc($order['branch_name']) : '' ?></div>
    <div class="hero-num">#<?= esc($order['order_number']) ?></div>
    <div class="hero-sub"><span class="poll-dot"></span>Live order tracking</div>

    <div class="chips">
      <span class="chip chip-type"><?= $order['order_type'] === 'delivery' ? '🛵 Delivery' : '🥡 Takeaway' ?></span>
      <span class="chip <?= $order['payment_status'] === 'paid' ? 'chip-pay-paid' : 'chip-pay-unpaid' ?>" id="payChip">
        <?= $order['payment_status'] === 'paid' ? '✔ Paid' : '● Payment pending' ?>
      </span>
    </div>

    <!-- Animated icon -->
    <div class="pulse" id="pulse">📋</div>

    <div class="status-label" id="sLabel">Order Received</div>
    <div class="status-msg" id="sMsg">Staff will confirm your order shortly...</div>
  </div>

  <!-- Timer -->
  <div class="timer-box" id="timerBox">
    <div class="timer-val" id="timerVal">~<?= (int)$etaMins ?></div>
    <div class="timer-lbl">Minutes<br>remaining</div>
  </div>

  <!-- Steps -->
  <div class="steps" id="steps">
    <?php for ($i = 1; $i <= 5; $i++):
        $cls = '';
        if ($cancelled === false && $i < $current)  $cls = 'done';
        if ($cancelled === false && $i === $current) $cls = 'active'; ?>
    <div class="step <?= $cls ?>" id="step<?= $i ?>">
      <div class="step-dot"><i class="fa <?= esc($steps[$i]['fa']) ?>"></i></div>
      <div class="step-info">
        <div class="step-name" data-name="<?= esc($steps[$i]['name']) ?>"><?= esc($steps[$i]['name']) ?></div>
        <div class="step-desc"><?= esc($steps[$i]['desc']) ?></div>
      </div>
    </div>
    <?php endfor; ?>
  </div>

  <!-- Items live progress -->
  <div class="items-box">
    <div class="items-head">
      <div class="items-title">Your items — live kitchen status</div>
      <div class="items-count" id="progText"><?= (int)$progress['prepared'] ?>/<?= (int)$progress['total'] ?> prepared</div>
    </div>
    <?php if (empty($order['items'])): ?>
      <div class="item-row" style="color:var(--mut)">No item details available</div>
    <?php else: foreach ($order['items'] as $it): ?>
      <div class="item-row">
        <div class="item-name"><?= esc($it['name']) ?>
          <?php if ($it['variant_name']): ?><span>· <?= esc($it['variant_name']) ?></span><?php endif; ?>
          <span>× <?= (int)$it['quantity'] ?></span>
        </div>
        <div class="item-st st-<?= esc($it['status'] ?: 'pending') ?>"><?= esc($it['status'] ?: 'pending') ?></div>
      </div>
    <?php endforeach; endif; ?>
  </div>

  <!-- Delivery rider -->
  <div class="rider-box <?= $order['order_type'] === 'delivery' && $order['delivery_status'] === 'out_for_delivery' ? 'show' : '' ?>" id="riderBox">
    <div class="rider-t">Your delivery partner</div>
    <div class="rider-name" id="riderName"><?= $order['rider_name'] ? esc($order['rider_name']) : 'Being assigned…' ?></div>
    <?php if ($order['customer_phone']): ?><a class="rider-phone" href="tel:+<?= esc(preg_replace('/\D+/', '', $order['customer_phone'])) ?>">📞 <?= esc($order['customer_phone']) ?></a><?php endif; ?>
  </div>

  <div class="foot">
    Address: <b><?= esc(mb_substr((string)$order['delivery_address'], 0, 90)) ?: '—' ?></b><br>
    Order total <b>₹<?= number_format((float)$order['total_amount'], 2) ?></b>
    · Keep this page open — status updates automatically
  </div>

<script>
const POLL_URL = '<?= base_url('track/' . $token . '/poll') ?>';
const STEP_NAMES = <?= json_encode(array_map(fn($s) => $s['name'], $steps)) ?>;
const STEP_FA = <?= json_encode(array_map(fn($s) => $s['fa'], $steps)) ?>;
const THEME = '<?= esc($theme) ?>';
let pollTimer, pollProgress = 0, confettiFired = false;

// poll bar animation (visual "keep-alive", resets each cycle like the table tracker)
setInterval(() => {
  pollProgress += 100 / 6;
  if (pollProgress >= 100) pollProgress = 0;
  const bar = document.getElementById('pollBar');
  if (bar) bar.style.width = pollProgress + '%';
}, 800);

function paint(step, done, active) {
  for (let i = 1; i <= 5; i++) {
    const el = document.getElementById('step' + i);
    if (!el) continue;
    el.classList.toggle('done', i < step && step !== 0);
    el.classList.toggle('active', i === step);
    const dot = el.querySelector('.step-dot');
    if (i < step && step !== 0) dot.innerHTML = '<i class="fa fa-check"></i>';
    else dot.innerHTML = '<i class="fa ' + (STEP_FA[i] || 'fa-circle') + '"></i>';
  }
}

function apply(data) {
  const pulse = document.getElementById('pulse');
  document.getElementById('sLabel').textContent = data.label;
  document.getElementById('sMsg').textContent = data.message;

  if (data.cancelled) {
    pulse.className = 'pulse cancel';
    pulse.textContent = data.icon || '❌';
    document.getElementById('timerBox').style.display = 'none';
    document.getElementById('steps').style.opacity = '.45';
    paint(0, false, false);
    clearInterval(pollTimer);
    return;
  }

  const icons = {1:'📋',2:'✅',3:'👨‍🍳',4:data.statusIcon4||'🥡',5:'🎉'};
  pulse.className = 'pulse' + (data.step >= 5 ? ' done' : '');
  pulse.textContent = data.icon || '📋';

  paint(data.step);

  // timer
  const tv = document.getElementById('timerVal');
  if (tv) {
    if (data.step >= 4) { document.getElementById('timerBox').style.display = 'none'; }
    else tv.textContent = '~' + data.remain_mins;
  }

  // items progress
  const pt = document.getElementById('progText');
  if (pt) pt.textContent = data.progress.prepared + '/' + data.progress.total + ' prepared';
  (data.items || []).forEach((it, idx) => {
    const rows = document.querySelectorAll('.item-row .item-st');
    if (rows[idx]) { rows[idx].className = 'item-st st-' + it.status; rows[idx].textContent = it.status; }
  });

  // rider
  if (data.delivery_status === 'out_for_delivery' || (data.step === 4 && data.delivery_status === 'out_for_delivery')) {
    const rb = document.getElementById('riderBox');
    rb.classList.add('show');
    document.getElementById('riderName').textContent = data.rider_name || 'Being assigned…';
  }
  // step 4 icon switch for delivery
  if (data.step === 4) { pulse.textContent = '🛵'; }

  if (data.step >= 5 && !confettiFired) {
    confettiFired = true;
    burst();
  }
}

function burst() {
  const colors = [THEME, '#ffffff', '#ffd166'];
  for (let i = 0; i < 42; i++) {
    const c = document.createElement('div');
    c.style.cssText = 'position:fixed;z-index:99;pointer-events:none;width:9px;height:9px;border-radius:2px;opacity:1;left:50%;top:34%;background:' + colors[i % 3] + ';transition:all 1.4s cubic-bezier(.2,.6,.3,1)';
    document.body.appendChild(c);
    const dx = (Math.random() - .5) * 340, dy = 180 + Math.random() * 260, rot = Math.random() * 360;
    requestAnimationFrame(() => {
      c.style.transform = `translate(${dx}px, ${dy}px) rotate(${rot}deg)`;
      c.style.opacity = '0';
    });
    setTimeout(() => c.remove(), 1600);
  }
}

function tick() {
  fetch(POLL_URL)
    .then(r => r.json())
    .then(d => { if (!d.error && d.success) apply(d); })
    .catch(() => {});
}

apply({
  step: <?= (int)$current ?>,
  label: <?= json_encode($cancelled ? 'Order Cancelled' : $steps[$current]['name']) ?>,
  message: <?= json_encode($cancelled ? ($order['cancelled_reason'] ?: 'Contact the restaurant') : $steps[$current]['desc']) ?>,
  icon: <?= json_encode($cancelled ? '❌' : $steps[$current]['icon']) ?>,
  cancelled: <?= $cancelled ? 'true' : 'false' ?>,
  delivery_status: '<?= esc($order['delivery_status']) ?>',
  rider_name: <?= json_encode($order['rider_name']) ?>,
  remain_mins: '<?= (int)$etaMins ?>',
  items: [],
  progress: { prepared: <?= (int)$progress['prepared'] ?>, total: <?= (int)$progress['total'] ?> },
});
// first poll refreshes text immediately
setTimeout(tick, 600);
pollTimer = setInterval(tick, 5000);
</script>
<?php endif; ?>
</body>
</html>
