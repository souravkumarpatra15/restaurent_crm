<?php $this->extend('layouts/pos_layout'); $this->section('content'); ?>
<?php
$pending    = count(array_filter($kots, fn($k) => $k['status'] === 'pending'));
$preparing  = count(array_filter($kots, fn($k) => $k['status'] === 'in_progress'));
$ready      = count(array_filter($kots, fn($k) => $k['status'] === 'ready'));
?>

<div class="kitch-root">

  <!-- Top Bar -->
  <div class="kitch-bar">
    <div class="kitch-live">
      <div class="kitch-dot"></div>
      <strong style="font-size:.95rem">Kitchen Display</strong>
      <span style="opacity:.4;font-size:.78rem">— <?= esc(session('branch_name') ?? 'Main Branch') ?></span>
    </div>
    <div style="display:flex;align-items:center;gap:.625rem">
      <div style="font-family:var(--mono);font-size:.72rem;color:rgba(255,255,255,.5);background:rgba(255,255,255,.06);padding:.25rem .6rem;border-radius:6px" id="kClock"></div>
      <a href="<?= base_url('pos') ?>" style="padding:.35rem .875rem;border:1px solid rgba(255,255,255,.18);border-radius:8px;color:rgba(255,255,255,.75);font-size:.78rem;font-weight:700;display:flex;align-items:center;gap:.35rem">
        <i class="fa fa-cash-register"></i> POS
      </a>
    </div>
  </div>

  <!-- Stats Bar -->
  <div class="kitch-stats-bar">
    <div class="kstat"><b id="sPending" style="color:var(--warning)"><?= $pending ?></b> Pending</div>
    <div class="kstat"><b id="sPreparing" style="color:var(--info)"><?= $preparing ?></b> Preparing</div>
    <div class="kstat"><b id="sReady" style="color:var(--success)"><?= $ready ?></b> Ready</div>
    <div style="margin-left:auto;font-size:.75rem;color:var(--text-m)">
      Refresh in <b id="kCountdown" style="color:var(--primary)"><?= empty($kots) ? 30 : 20 ?></b>s
    </div>
  </div>

  <!-- KOT Grid -->
  <div class="kitch-body">
    <div class="kitch-grid" id="kotGrid">

      <?php if (empty($kots)): ?>
      <div class="kitch-empty">
        <div class="kitch-empty-icon">✅</div>
        <div class="kitch-empty-title">Kitchen is Clear!</div>
        <div class="kitch-empty-sub">No pending orders — great work!</div>
      </div>

      <?php else: foreach ($kots as $kot):
        $mins     = (time() - strtotime($kot['created_at'])) / 60;
        $isOverdue = $mins > 20 && $kot['status'] !== 'ready';
        $cardCls   = $kot['status'] === 'ready' ? 'ready' : ($isOverdue ? 'overdue' : '');
        $timerCls  = $mins >= 20 ? 'r' : ($mins >= 10 ? 'y' : 'g');
        $statusLabels = ['pending'=>'⏳ Waiting','in_progress'=>'🔥 Preparing','ready'=>'✅ Ready to Serve'];
      ?>
      <div class="kotc <?= $cardCls ?>" id="kc<?= $kot['id'] ?>">

        <!-- Header -->
        <div class="kotc-hdr">
          <div>
            <div class="kotc-num"><?= esc($kot['kot_number']) ?></div>
            <div class="kotc-meta">
              <?= ucfirst(str_replace('_',' ', $kot['order_type'] ?? 'dine_in')) ?>
              <?php if ($kot['table_number']): ?>
               · Table <strong><?= esc($kot['table_number']) ?></strong>
              <?php endif; ?>
              · <?= date('h:i A', strtotime($kot['created_at'])) ?>
            </div>
          </div>
          <div class="kotc-timer <?= $timerCls ?>"
               id="kt<?= $kot['id'] ?>"
               data-start="<?= strtotime($kot['created_at']) ?>">
            <?= floor($mins) ?>m <?= str_pad((int)(($mins - floor($mins)) * 60), 2, '0', STR_PAD_LEFT) ?>s
          </div>
        </div>

        <!-- Status Badge -->
        <div style="padding:.4rem 1rem 0">
          <span class="kotc-badge <?= $kot['status'] ?>" id="kb<?= $kot['id'] ?>">
            <?= $statusLabels[$kot['status']] ?? ucfirst($kot['status']) ?>
          </span>
        </div>

        <!-- Items -->
        <div class="kotc-items">
          <?php foreach ($kot['items'] as $item): ?>
          <div class="kitem">
            <div class="kitem-qty"><?= $item['quantity'] ?>×</div>
            <div class="kitem-body">
              <div class="kitem-name"><?= esc($item['name']) ?></div>
              <?php if (!empty($item['notes'])): ?>
              <div class="kitem-note">⚡ <?= esc($item['notes']) ?></div>
              <?php endif; ?>
              <?php if (!empty($item['addons'])): ?>
              <div class="kitem-add">+ <?= is_array($item['addons']) ? implode(', ', array_map('esc', $item['addons'])) : esc($item['addons']) ?></div>
              <?php endif; ?>
            </div>
          </div>
          <?php endforeach; ?>
        </div>

        <!-- Actions -->
        <div class="kotc-foot" id="ka<?= $kot['id'] ?>">
          <?php if ($kot['status'] === 'pending'): ?>
          <button class="kbtn start" onclick="updateKot(<?= $kot['id'] ?>,'in_progress',this)">
            <i class="fa fa-fire"></i> Start Cooking
          </button>
          <button class="kbtn rdy" onclick="updateKot(<?= $kot['id'] ?>,'ready',this)">
            <i class="fa fa-check"></i> Ready
          </button>

          <?php elseif ($kot['status'] === 'in_progress'): ?>
          <button class="kbtn rdy" onclick="updateKot(<?= $kot['id'] ?>,'ready',this)" style="flex:1">
            <i class="fa fa-check-double"></i> Mark as Ready
          </button>

          <?php elseif ($kot['status'] === 'ready'): ?>
          <button class="kbtn served" onclick="updateKot(<?= $kot['id'] ?>,'served',this)" style="flex:1">
            <i class="fa fa-hand-sparkles"></i> Mark Served &amp; Done
          </button>
          <?php endif; ?>
        </div>

      </div>
      <?php endforeach; endif; ?>

    </div>
  </div>

  <!-- Footer -->
  <div class="kitch-foot">
    <span>Auto-refreshes every <?= empty($kots) ? 30 : 20 ?>s · Last: <?= date('h:i:s A') ?></span>
    <div class="kitch-prog-wrap">
      <div class="kitch-prog" id="kprog" style="width:100%"></div>
    </div>
  </div>

</div>

<!-- Loading overlay -->
<div class="kloader" id="kloader">
  <div class="kloader-card">
    <div class="kloader-ring"></div>
    <div class="kloader-txt" id="kloaderTxt">Updating...</div>
  </div>
</div>

<!-- Alert -->
<div class="kalert" id="kalert"></div>

<style>
@keyframes spin { to { transform: rotate(360deg); } }
@keyframes fadeSlide { from{opacity:0;transform:translateY(10px)} to{opacity:1;transform:none} }
.kotc-enter { animation: fadeSlide .3s ease; }
</style>

<script>
const BASE   = '<?= base_url() ?>';
const CN     = '<?= csrf_token() ?>';
const CT     = '<?= csrf_hash() ?>';
const TOTAL  = <?= empty($kots) ? 30 : 20 ?>;

// ── Clock ────────────────────────────────────────────────
setInterval(() => {
  const el = document.getElementById('kClock');
  if (el) el.textContent = new Date().toLocaleTimeString('en-IN', { hour:'2-digit', minute:'2-digit', second:'2-digit' });
}, 1000);

// ── Live Timers ──────────────────────────────────────────
setInterval(() => {
  document.querySelectorAll('[id^="kt"]').forEach(el => {
    const start = parseInt(el.dataset.start);
    if (!start) return;
    const secs  = Math.floor(Date.now() / 1000 - start);
    const mins  = Math.floor(secs / 60);
    const s     = String(secs % 60).padStart(2, '0');
    el.textContent = mins + 'm ' + s + 's';
    // Color class
    el.className = 'kotc-timer ' + (mins >= 20 ? 'r' : mins >= 10 ? 'y' : 'g');
    // Card overdue class
    const card = el.closest('.kotc');
    if (card && mins >= 20 && !card.classList.contains('ready')) {
      card.classList.add('overdue');
    }
  });
}, 1000);

// ── Update KOT Status ────────────────────────────────────
async function updateKot(id, status, btn) {
  // Spinner on button
  const orig = btn.innerHTML;
  btn.classList.add('loading');
  btn.innerHTML = `<div class="kbtn-spin"></div>`;

  // Loader for "served" since it removes the card
  if (status === 'served') {
    document.getElementById('kloader').classList.add('on');
    document.getElementById('kloaderTxt').textContent = 'Marking served...';
  }

  try {
    const d = await fetch(BASE + 'pos/kitchen/update-status', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-CSRF-TOKEN': CT },
      body: `${CN}=${CT}&kot_id=${id}&status=${status}`
    }).then(r => r.json());

    document.getElementById('kloader').classList.remove('on');

    if (d.success) {
      applyStatusUpdate(id, status);
    } else {
      btn.innerHTML = orig;
      btn.classList.remove('loading');
      showKAlert('❌ Update failed. Please try again.', 'error');
    }
  } catch (e) {
    document.getElementById('kloader').classList.remove('on');
    btn.innerHTML = orig;
    btn.classList.remove('loading');
    showKAlert('📡 Network error. Check connection.', 'error');
  }
}

function applyStatusUpdate(id, status) {
  const card    = document.getElementById('kc' + id);
  const badge   = document.getElementById('kb' + id);
  const actions = document.getElementById('ka' + id);

  if (status === 'served') {
    // Animate out
    card.style.transition = 'all .4s ease';
    card.style.opacity    = '0';
    card.style.transform  = 'scale(.92) translateY(8px)';
    setTimeout(() => {
      card.remove();
      updateStats();
      showKAlert('✅ Order marked served!', 'success');
      // Empty state check
      const grid = document.getElementById('kotGrid');
      if (!grid.querySelector('.kotc')) {
        grid.innerHTML = `<div class="kitch-empty"><div class="kitch-empty-icon">✅</div><div class="kitch-empty-title">Kitchen is Clear!</div><div class="kitch-empty-sub">Great work! No more pending orders.</div></div>`;
      }
    }, 400);
    return;
  }

  if (status === 'ready') {
    card.classList.remove('overdue');
    card.classList.add('ready');
    badge.className = 'kotc-badge ready';
    badge.textContent = '✅ Ready to Serve';
    actions.innerHTML = `<button class="kbtn served" onclick="updateKot(${id},'served',this)" style="flex:1"><i class="fa fa-hand-sparkles"></i> Mark Served &amp; Done</button>`;
    showKAlert('✅ KOT Ready to serve!', 'success');
  }

  if (status === 'in_progress') {
    badge.className = 'kotc-badge in_progress';
    badge.textContent = '🔥 Preparing';
    actions.innerHTML = `<button class="kbtn rdy" onclick="updateKot(${id},'ready',this)" style="flex:1"><i class="fa fa-check-double"></i> Mark as Ready</button>`;
    showKAlert('🔥 Started cooking!', 'info');
  }

  updateStats();
}

function updateStats() {
  const pending   = document.querySelectorAll('.kotc:not(.ready) .kotc-badge.pending').length;
  const preparing = document.querySelectorAll('.kotc-badge.in_progress').length;
  const ready     = document.querySelectorAll('.kotc.ready').length;
  const sp  = document.getElementById('sPending');
  const spr = document.getElementById('sPreparing');
  const sr  = document.getElementById('sReady');
  if (sp)  sp.textContent  = document.querySelectorAll('.kotc-badge.pending').length;
  if (spr) spr.textContent = preparing;
  if (sr)  sr.textContent  = ready;
}

// ── Kitchen Alert ─────────────────────────────────────────
function showKAlert(msg, type = 'success') {
  const cfg = {
    success: { bg: 'var(--success-l)', bd: '#BBF7D0', cl: 'var(--success)' },
    error:   { bg: 'var(--danger-l)',  bd: '#FECACA', cl: 'var(--danger)'  },
    info:    { bg: 'var(--info-l)',    bd: '#BFDBFE', cl: 'var(--info)'    },
    warning: { bg: 'var(--warning-l)', bd: '#FDE68A', cl: 'var(--warning)' },
  };
  const c = cfg[type] || cfg.success;
  const el = document.getElementById('kalert');
  el.innerHTML = `<div class="kalert-inner" style="background:${c.bg};border:1.5px solid ${c.bd};color:${c.cl}">${msg}</div>`;
  el.style.display = 'block';
  setTimeout(() => { el.style.display = 'none'; }, 3200);
}

// ── Auto Refresh Countdown ───────────────────────────────
let countdown = TOTAL;
let countdownInterval;

function startCountdown() {
  countdown = TOTAL;
  clearInterval(countdownInterval);
  countdownInterval = setInterval(() => {
    countdown--;
    const el  = document.getElementById('kCountdown');
    const prg = document.getElementById('kprog');
    if (el)  el.textContent = countdown;
    if (prg) prg.style.width = (countdown / TOTAL * 100) + '%';
    if (countdown <= 0) {
      clearInterval(countdownInterval);
      location.reload();
    }
  }, 1000);
}

// Reset countdown on any user interaction (still working)
document.addEventListener('click', startCountdown);
startCountdown();
</script>

<?php $this->endSection(); ?>
