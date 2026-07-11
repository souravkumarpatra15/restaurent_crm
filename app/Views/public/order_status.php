<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
<title>Order <?= esc($order['order_number']) ?> — Status</title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
:root{--p:<?= esc($table['theme_color']??'#FF6B35') ?>;--font:'Plus Jakarta Sans',system-ui,sans-serif}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
html,body{height:100%;font-family:var(--font);background:#0F172A;color:#fff;-webkit-font-smoothing:antialiased;overflow-x:hidden}

/* ── HERO ─────────────────────────────────────── */
.hero{padding:1.5rem 1.25rem 1rem;text-align:center;position:relative}
.hero-rest{font-size:.75rem;color:rgba(255,255,255,.4);letter-spacing:.08em;text-transform:uppercase;margin-bottom:.4rem}
.hero-num{font-size:2rem;font-weight:900;letter-spacing:-.02em;color:#fff}
.hero-sub{font-size:.82rem;color:rgba(255,255,255,.45);margin-top:.35rem}
.pulse-ring{width:120px;height:120px;margin:1.5rem auto;position:relative;display:flex;align-items:center;justify-content:center}
.pulse-ring::before,.pulse-ring::after{content:'';position:absolute;border-radius:50%;border:2px solid var(--p);animation:ripple 2s linear infinite;opacity:0}
.pulse-ring::after{animation-delay:1s}
@keyframes ripple{0%{width:70px;height:70px;opacity:.8}100%{width:120px;height:120px;opacity:0}}
.status-icon{width:72px;height:72px;border-radius:50%;background:var(--p);display:flex;align-items:center;justify-content:center;font-size:2rem;z-index:1;position:relative;box-shadow:0 0 0 8px rgba(255,107,53,.15)}
.status-icon.green{background:#22C55E;box-shadow:0 0 0 8px rgba(34,197,94,.15)}
.status-label{font-size:1.4rem;font-weight:900;text-align:center;margin-bottom:.35rem}
.status-msg{font-size:.875rem;color:rgba(255,255,255,.55);text-align:center;margin-bottom:1.5rem;line-height:1.5}

/* ── TIMER ────────────────────────────────────── */
.timer-box{background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.1);border-radius:16px;padding:1rem 1.5rem;margin:0 1.25rem 1.25rem;text-align:center}
.timer-val{font-size:2.5rem;font-weight:900;letter-spacing:-.02em;color:var(--p);font-variant-numeric:tabular-nums}
.timer-lbl{font-size:.72rem;color:rgba(255,255,255,.4);text-transform:uppercase;letter-spacing:.08em;margin-top:.15rem}

/* ── STEPS ────────────────────────────────────── */
.steps{margin:0 1.25rem 1.25rem;display:flex;flex-direction:column;gap:0}
.step{display:flex;align-items:flex-start;gap:.875rem;padding:.625rem 0;position:relative}
.step:not(:last-child)::after{content:'';position:absolute;left:15px;top:36px;width:2px;height:calc(100% - 10px);background:rgba(255,255,255,.1)}
.step.done:not(:last-child)::after{background:var(--p)}
.step-dot{width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.85rem;flex-shrink:0;background:rgba(255,255,255,.08);color:rgba(255,255,255,.3);border:2px solid rgba(255,255,255,.1);z-index:1;transition:all .4s}
.step.done .step-dot{background:var(--p);color:#fff;border-color:var(--p)}
.step.active .step-dot{background:rgba(255,107,53,.15);color:var(--p);border-color:var(--p);box-shadow:0 0 0 4px rgba(255,107,53,.1)}
.step-info{flex:1;padding-top:.3rem}
.step-name{font-size:.82rem;font-weight:800;color:rgba(255,255,255,.35)}
.step.done .step-name,.step.active .step-name{color:#fff}
.step-desc{font-size:.7rem;color:rgba(255,255,255,.25);margin-top:.1rem}
.step.active .step-desc{color:rgba(255,255,255,.5)}
.step-time{font-size:.65rem;color:var(--p);font-weight:700;margin-top:.15rem}

/* ── ORDER ITEMS ──────────────────────────────── */
.card{background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.08);border-radius:16px;margin:0 1.25rem 1.25rem;padding:1rem}
.card-title{font-size:.7rem;font-weight:900;letter-spacing:.08em;text-transform:uppercase;color:rgba(255,255,255,.3);margin-bottom:.75rem}
.oitem{display:flex;justify-content:space-between;align-items:center;padding:.4rem 0;border-bottom:1px solid rgba(255,255,255,.05)}
.oitem:last-child{border-bottom:none}
.oitem-name{font-size:.82rem;font-weight:700}
.oitem-qty{font-size:.72rem;color:rgba(255,255,255,.4);margin-top:.1rem}
.oitem-price{font-size:.82rem;font-weight:900;color:var(--p)}
.total-row{display:flex;justify-content:space-between;align-items:center;padding:.625rem 0 0;margin-top:.25rem;border-top:1px solid rgba(255,255,255,.1)}
.total-lbl{font-size:.82rem;font-weight:900}
.total-val{font-size:1.1rem;font-weight:900;color:var(--p)}

/* ── ACTIONS ──────────────────────────────────── */
.actions{padding:0 1.25rem 2rem;display:flex;flex-direction:column;gap:.625rem}
.action-btn{display:flex;align-items:center;justify-content:center;gap:.5rem;padding:.875rem;border-radius:14px;font-family:var(--font);font-size:.875rem;font-weight:800;cursor:pointer;text-decoration:none;border:none;width:100%;transition:all .15s}
.action-btn.primary{background:var(--p);color:#fff}
.action-btn.outline{background:rgba(255,255,255,.06);color:rgba(255,255,255,.8);border:1.5px solid rgba(255,255,255,.12)}
.action-btn.success{background:#22C55E;color:#fff}

/* ── READY ANIMATION ──────────────────────────── */
.confetti{position:fixed;top:0;left:0;width:100%;height:100%;pointer-events:none;z-index:1000}
.ready-glow{animation:glow 1.5s ease-in-out infinite alternate}
@keyframes glow{from{box-shadow:0 0 0 8px rgba(34,197,94,.15)}to{box-shadow:0 0 0 20px rgba(34,197,94,.3)}}

/* ── POLL INDICATOR ───────────────────────────── */
.poll-dot{width:7px;height:7px;border-radius:50%;background:#22C55E;display:inline-block;animation:blink 1.5s ease-in-out infinite;margin-right:.3rem}
@keyframes blink{0%,100%{opacity:1}50%{opacity:.2}}
.poll-bar{position:fixed;top:0;left:0;height:3px;background:var(--p);transition:width .5s ease;z-index:200}
</style>
</head>
<body>

<!-- Live poll progress bar -->
<div class="poll-bar" id="pollBar" style="width:0%"></div>

<!-- Hero -->
<div class="hero">
  <div class="hero-rest"><?= esc($table['restaurant_name']) ?> · Table <?= esc($table['table_number']) ?></div>
  <div class="hero-num">#<?= esc($order['order_number']) ?></div>
  <div class="hero-sub">
    <span class="poll-dot"></span>Live tracking your order
  </div>

  <!-- Animated icon -->
  <div class="pulse-ring">
    <div class="status-icon" id="sIcon">📋</div>
  </div>

  <div class="status-label" id="sLabel">Order Received</div>
  <div class="status-msg" id="sMsg">Staff will confirm your order shortly...</div>
</div>

<!-- Timer -->
<div class="timer-box" id="timerBox">
  <div class="timer-val" id="timerVal">~<?= (int)($order['estimated_mins'] ?? 15) ?></div>
  <div class="timer-lbl">Estimated minutes</div>
</div>

<!-- Steps -->
<div class="steps" id="steps">
  <div class="step active" id="step1">
    <div class="step-dot"><i class="fa fa-clipboard-list"></i></div>
    <div class="step-info">
      <div class="step-name">Order Received</div>
      <div class="step-desc">We've got your order</div>
      <div class="step-time"><?= date('h:i A', strtotime($order['created_at'])) ?></div>
    </div>
  </div>
  <div class="step" id="step2">
    <div class="step-dot"><i class="fa fa-check"></i></div>
    <div class="step-info">
      <div class="step-name">Confirmed by Staff</div>
      <div class="step-desc">Staff reviewed and sent to kitchen</div>
    </div>
  </div>
  <div class="step" id="step3">
    <div class="step-dot"><i class="fa fa-fire-burner"></i></div>
    <div class="step-info">
      <div class="step-name">Preparing in Kitchen</div>
      <div class="step-desc">Chef is cooking your food</div>
    </div>
  </div>
  <div class="step" id="step4">
    <div class="step-dot"><i class="fa fa-utensils"></i></div>
    <div class="step-info">
      <div class="step-name">Ready / Served</div>
      <div class="step-desc">Enjoy your meal!</div>
    </div>
  </div>
</div>

<!-- Order items -->
<div class="card">
  <div class="card-title">Your Order</div>
  <?php foreach ($items as $it): ?>
  <div class="oitem">
    <div>
      <div class="oitem-name"><?= esc($it['name']) ?></div>
      <div class="oitem-qty">× <?= $it['quantity'] ?><?= $it['notes']?' · '.$it['notes']:'' ?></div>
    </div>
    <div class="oitem-price"><?= $table['currency_symbol']??'₹' ?><?= number_format($it['total_price'],2) ?></div>
  </div>
  <?php endforeach; ?>
  <div class="total-row">
    <span class="total-lbl">Total</span>
    <span class="total-val"><?= $table['currency_symbol']??'₹' ?><?= number_format($order['total_amount'],2) ?></span>
  </div>
</div>

<!-- Actions -->
<div class="actions">
  <a href="<?= base_url('menu/table/'.$token) ?>" class="action-btn outline">
    <i class="fa fa-plus"></i> Add More Items
  </a>
  <div style="text-align:center;font-size:.72rem;color:rgba(255,255,255,.3);padding:.25rem 0">
    Payment will be collected by staff at the table
  </div>
</div>

<canvas class="confetti" id="confetti"></canvas>

<script>
const BASE    = '<?= base_url() ?>';
const TOKEN   = '<?= esc($token) ?>';
const ORDER_ID= <?= (int)$order['id'] ?>;
const EST_MINS= <?= (int)($order['estimated_mins']??15) ?>;
const CREATED = <?= strtotime($order['created_at']) * 1000 ?>;

let currentStep = 1;
let pollInterval;
let pollProgress = 0;
let confettiFired = false;

// ── Status update ──────────────────────────────────────
function applyStatus(data) {
  const icon  = document.getElementById('sIcon');
  const label = document.getElementById('sLabel');
  const msg   = document.getElementById('sMsg');
  const timer = document.getElementById('timerVal');
  const tbox  = document.getElementById('timerBox');

  const icons = {1:'📋', 2:'✅', 3:'👨‍🍳', 4:'🍽'};
  icon.textContent  = icons[data.step] || '📋';
  label.textContent = data.label;
  msg.textContent   = data.message;

  // Timer
  if (data.step >= 4) {
    tbox.style.display = 'none';
  } else if (data.remain_mins <= 0) {
    timer.textContent = 'Almost ready!';
  } else {
    timer.textContent = '~' + data.remain_mins + ' min' + (data.remain_mins!==1?'s':'');
  }

  // Step indicators
  for (let i = 1; i <= 4; i++) {
    const el = document.getElementById('step'+i);
    if (!el) continue;
    el.classList.remove('done','active');
    if (i < data.step) el.classList.add('done');
    else if (i === data.step) el.classList.add('active');
  }

  // Ready/served — celebration!
  if (data.step === 4 && !confettiFired) {
    confettiFired = true;
    icon.classList.add('green','ready-glow');
    fireConfetti();
    // Show special action button
    const actions = document.querySelector('.actions');
    const readyBtn = document.createElement('a');
    readyBtn.href = '#';
    readyBtn.className = 'action-btn success';
    readyBtn.innerHTML = '<i class="fa fa-party-horn"></i> Your Order is Ready! 🎉';
    actions.insertBefore(readyBtn, actions.firstChild);
    // Stop polling
    clearInterval(pollInterval);
  }
}

// ── Poll server ───────────────────────────────────────
async function poll() {
  // Animate progress bar
  pollProgress = 0;
  const bar = document.getElementById('pollBar');
  const tick = setInterval(() => {
    pollProgress = Math.min(pollProgress + 2, 90);
    bar.style.width = pollProgress + '%';
  }, 100);

  try {
    const res  = await fetch(BASE+'menu/table/'+TOKEN+'/poll/'+ORDER_ID);
    const data = await res.json();
    clearInterval(tick);
    bar.style.width = '100%';
    setTimeout(() => bar.style.width = '0%', 400);
    if (!data.error) applyStatus(data);
  } catch(e) {
    clearInterval(tick);
    bar.style.width = '0%';
  }
}

// ── Confetti ──────────────────────────────────────────
function fireConfetti() {
  const canvas = document.getElementById('confetti');
  const ctx    = canvas.getContext('2d');
  canvas.width = window.innerWidth; canvas.height = window.innerHeight;
  const pieces = [];
  const colors = ['#FF6B35','#FFD700','#22C55E','#3B82F6','#EC4899','#A78BFA'];
  for (let i = 0; i < 120; i++) {
    pieces.push({
      x: Math.random()*canvas.width, y: -10,
      w: 6 + Math.random()*8, h: 10 + Math.random()*10,
      color: colors[Math.floor(Math.random()*colors.length)],
      vx: (Math.random()-0.5)*4, vy: 2+Math.random()*4,
      rot: Math.random()*360, vr: (Math.random()-0.5)*8, life:1
    });
  }
  let frame = 0;
  function draw() {
    ctx.clearRect(0,0,canvas.width,canvas.height);
    pieces.forEach(p => {
      ctx.save(); ctx.translate(p.x,p.y); ctx.rotate(p.rot*Math.PI/180);
      ctx.globalAlpha = p.life;
      ctx.fillStyle = p.color;
      ctx.fillRect(-p.w/2,-p.h/2,p.w,p.h);
      ctx.restore();
      p.x += p.vx; p.y += p.vy; p.rot += p.vr; p.vy += 0.05;
      if (p.y > canvas.height) p.life -= 0.02;
    });
    if (frame++ < 180) requestAnimationFrame(draw);
    else ctx.clearRect(0,0,canvas.width,canvas.height);
  }
  draw();
}

// ── Start ─────────────────────────────────────────────
poll(); // immediate
pollInterval = setInterval(poll, 8000); // every 8 seconds

// Initial state from server
const initStatus = '<?= $order['status'] ?>';
const stepMap = {pending:1, confirmed:2, preparing:3, ready:4, served:4, completed:4};
const initStep = stepMap[initStatus] || 1;
if (initStep > 1) {
  applyStatus({
    step: initStep, label: document.getElementById('sLabel').textContent,
    message: document.getElementById('sMsg').textContent,
    remain_mins: EST_MINS, icon: '📋'
  });
}
</script>
</body>
</html>
