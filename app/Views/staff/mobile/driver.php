<?php $this->extend('layouts/mobile_layout'); $this->section('content'); ?>

<div class="mstat">
  <div><b style="<?= $stats['active'] ? 'color:#1A202C' : 'color:#94A3B8' ?>"><?= $stats['active'] ?></b><span>On hand</span></div>
  <div><b style="color:#16A34A"><?= $stats['delivered'] ?></b><span>Today</span></div>
  <div><b style="color:#D97706">₹<?= number_format($stats['cod_due'], 0) ?></b><span>COD due</span></div>
</div>

<?php if ($profile): ?>
<div class="mcardx" style="display:flex;align-items:center;gap:.7rem;background:#0F172A;border-color:#0F172A;color:#fff;padding:.75rem .9rem">
  <div style="font-size:1.6rem">👛</div>
  <div style="line-height:1.3">
    <div style="font-size:.72rem;color:rgba(255,255,255,.55);font-weight:700;text-transform:uppercase;letter-spacing:.05em">My wallet</div>
    <div style="font-size:.82rem"><b style="color:#4ADE80">₹<?= number_format((float)($wallet['payable'] ?? 0), 2) ?></b> commission payable
      · <b style="color:#FBBF24">₹<?= number_format((float)($wallet['cod_in_hand'] ?? 0), 2) ?></b> cash holding</div>
    <div style="font-size:.62rem;color:rgba(255,255,255,.45)"><?= ($profile['employer_type'] ?? 'restaurant') === 'dinovix' ? '🛵 DinoviX fleet — ' : '' ?><?= $profile['commission_type'] === 'percent' ? (float)$profile['commission_value'] . '% per order' : '₹' . number_format((float)$profile['commission_value'], 2) . ' per order' ?></div>
  </div>
</div>
<?php endif; ?>

<!-- On-duty switch: keeps GPS + new-order alerts alive in the background -->
<div class="mcardx" style="display:flex;align-items:center;gap:.6rem;padding:.7rem .9rem">
  <div style="font-size:1.4rem"><?= $dutyOn ? '🟢' : '⚪' ?></div>
  <div style="line-height:1.25">
    <b style="font-size:.85rem"><?= $dutyOn ? 'On duty' : 'Off duty' ?></b>
    <div style="font-size:.68rem;color:#64748B"><?= $dutyOn ? 'GPS + new order alerts running' : 'Tap on to receive new orders' ?></div>
  </div>
  <button type="button" id="dutyBtn" class="mbtn <?= $dutyOn ? 'mbtn-danger' : 'mbtn-primary' ?>" style="margin-left:auto;padding:.5rem .9rem;font-size:.75rem" onclick="flipDuty(this)"><?= $dutyOn ? 'Go off duty' : 'Go ON duty' ?></button>
</div>

<div id="rideList">
  <?php if (!$active && !$closed): ?>
    <div class="mcardx" style="text-align:center;color:#64748B;padding:2rem .9rem">
      <div style="font-size:2.2rem;margin-bottom:.4rem">🛵</div>
      <b style="font-size:.9rem">No deliveries assigned</b>
      <div style="font-size:.75rem;margin-top:.25rem">The restaurant assigns runs on the delivery board.<br>Pull down to refresh.</div>
    </div>
  <?php endif; ?>

  <?php foreach ($active as $o):
      $cod = $o['payment_status'] !== 'paid';
      $out = $o['delivery_status'] === 'out_for_delivery';
      $itemsDone = (int)($o['ready_items'] ?? 0) >= (int)($o['total_items'] ?? 1);
    ?>
  <div class="mcardx" data-oid="<?= $o['id'] ?>">
    <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:.5rem">
      <b style="font-size:.95rem">#<?= esc($o['order_number']) ?></b>
      <span class="mchip" style="<?= $out ? 'background:#FEF3C7;color:#B45309' : 'background:#DBEAFE;color:#1D4ED8' ?>">
        <?= $out ? '🛵 On the way' : '📦 Ready to load' ?>
      </span>
      <?php if ($o['payment_status'] === 'unpaid'): ?><span class="mchip" style="background:#FEE2E2;color:#B91C1C">💵 COD ₹<?= number_format((float)$o['balance_amount'], 0) ?></span><?php endif; ?>
      <span style="margin-left:auto;font-size:.66rem;color:#94A3B8;font-weight:700"><?= $o['age_mins'] < 1 ? 'now' : (int)$o['age_mins'] . 'm' ?></span>
    </div>

    <div style="font-size:.8rem;font-weight:800;color:#334155;margin-bottom:.15rem"><?= esc($o['customer_name'] ?: 'Customer') ?></div>
    <div style="font-size:.75rem;color:#64748B;line-height:1.35;margin-bottom:.55rem">
      <i class="fa fa-location-dot" style="color:#EF4444"></i> <?= esc($o['delivery_address'] ?: 'No address on order') ?>
    </div>

    <div style="display:flex;gap:.4rem;margin-bottom:.6rem;flex-wrap:wrap">
      <?php if ($o['customer_phone']): ?>
        <a class="mbtn mbtn-dark" style="text-decoration:none;font-size:.74rem;padding:.45rem .7rem" href="tel:<?= esc($o['customer_phone']) ?>"><i class="fa fa-phone"></i> Call</a>
      <?php endif; ?>
      <a class="mbtn mbtn-dark" style="text-decoration:none;font-size:.74rem;padding:.45rem .7rem"
         target="_blank" href="https://www.google.com/maps/dir/?api=1&destination=<?= rawurlencode($o['delivery_address'] ?: '') ?>">
        <i class="fa fa-diamondturn-right"></i> Navigate
      </a>
      <div class="mchip" style="margin-left:auto;background:#F1F5F9;color:#475569">
        <?php if ($itemsDone): ?>🍳 Kitchen: all packed<?php else: ?>🍳 Kitchen: <?= (int)$o['ready_items'] ?>/<?= (int)$o['total_items'] ?> ready<?php endif; ?>
      </div>
    </div>

    <?php if ($o['payment_status'] === 'unpaid'): ?>
    <label style="display:flex;align-items:center;gap:.45rem;font-size:.74rem;font-weight:700;color:#B45309;background:#FFFBEB;border:1px dashed #F59E0B;border-radius:8px;padding:.5rem .6rem;margin-bottom:.55rem">
      <input type="checkbox" class="codCheck" checked style="width:16px;height:16px;accent-color:#D97706">
      Collect cash ₹<?= number_format((float)$o['balance_amount'], 2) ?> at the door
    </label>
    <?php endif; ?>

    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:.4rem">
      <?php if (!$out): ?>
      <button type="button" class="mbtn mbtn-dark" style="grid-column: span 3;border-radius:12px" onclick="dAction(<?= $o['id'] ?>, 'pickup', this)">
        <i class="fa fa-box" style="color:#93C5FD"></i> Picked up the food — start ride
      </button>
      <?php else: ?>
      <button type="button" class="mbtn mbtn-primary" style="grid-column: span 2;border-radius:12px" onclick="dAction(<?= $o['id'] ?>, 'deliver', this)">
        <i class="fa fa-circle-check"></i> Delivered
      </button>
      <button type="button" class="mbtn mbtn-danger" style="border-radius:12px" onclick="dFail(<?= $o['id'] ?>, this)">
        <i class="fa fa-xmark"></i> Failed
      </button>
      <?php endif; ?>
    </div>
  </div>
  <?php endforeach; ?>

  <?php if ($closed): ?>
    <div style="font-size:.66rem;font-weight:800;letter-spacing:.06em;color:#94A3B8;text-transform:uppercase;margin:.4rem 0 .5rem">Completed runs</div>
    <?php foreach ($closed as $o): ?>
      <div class="mcardx" style="padding:.7rem .8rem;display:flex;align-items:center;gap:.5rem;opacity:.8">
        <div style="font-size:1.2rem"><?= $o['delivery_status'] === 'delivered' ? '✅' : '⚠️' ?></div>
        <div>
          <b style="font-size:.8rem">#<?= esc($o['order_number']) ?></b>
          <div style="font-size:.65rem;color:#94A3B8"><?= $o['delivery_status'] ?> <?= $o['delivered_at'] ? '· ' . date('d M h:i A', strtotime($o['delivered_at'])) : '' ?></div>
        </div>
        <div style="margin-left:auto;font-weight:900;font-size:.8rem">₹<?= number_format((float)$o['total_amount'], 0) ?></div>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>

<script>
const bal = <?= json_encode(array_combine(array_column($active, 'id'), array_map(fn($o) => number_format((float)$o['balance_amount'], 2, '.', ''), $active))) ?: '{}' ?>;

function dAction(oid, action, btn) {
  const card = document.querySelector('.mcardx[data-oid="' + oid + '"]');
  const collect = (action === 'deliver' && card && card.querySelector('.codCheck:checked')) ? (bal[oid] || '0') : '0';
  const old = btn.innerHTML;
  btn.disabled = true; btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i>';
  mPost('<?= base_url('driver/act') ?>', { order_id: oid, action: action, collect: collect })
    .then(d => {
      if (d.success) { mToast(d.message, true); setTimeout(() => location.reload(), 900); }
      else { mToast(d.message || 'Action failed', false); btn.disabled = false; btn.innerHTML = old; }
    })
    .catch(e => { mToast(e.message || 'Network error', false); btn.disabled = false; btn.innerHTML = old; });
}

// ── On-duty toggle: server flag + Android foreground service ──
function flipDuty(btn) {
  const goingOn = btn.textContent.trim().indexOf('ON') >= 0;
  btn.disabled = true; btn.textContent = '…';
  mPost('<?= base_url('driver/duty') ?>', { on: goingOn ? '1' : '0' })
    .then(d => {
      if (d.success) {
        if (goingOn && (typeof window.AndroidDinoVIX !== 'undefined') && window.AndroidDinoVIX.setDuty) {
          window.AndroidDinoVIX.setDuty(true);   // starts the GPS/job service
          mToast('You are ON duty — keep the app on you', true);
        } else if (!goingOn && (typeof window.AndroidDinoVIX !== 'undefined') && window.AndroidDinoVIX.setDuty) {
          window.AndroidDinoVIX.setDuty(false);
          mToast('Off duty', true);
        } else {
          mToast(goingOn ? 'On duty! (alerts run while this page is open)' : 'Off duty', true);
        }
        setTimeout(() => location.reload(), 700);
      } else { mToast(d.message || 'Could not switch', false); btn.disabled = false; }
    })
    .catch(e => { mToast(e.message || 'Network error', false); btn.disabled = false; });
}

// ding on a freshly assigned run (also plays even when the list just scrolled)
function dingNewOrder() {
  try {
    const ctx = new (window.AudioContext || window.webkitAudioContext)();
    [880, 1175].forEach((f, i) => {
      const o = ctx.createOscillator(), g = ctx.createGain();
      o.frequency.value = f; o.type = 'sine'; o.connect(g); g.connect(ctx.destination);
      g.gain.setValueAtTime(.25, ctx.currentTime + i * .18);
      g.gain.exponentialRampToValueAtTime(.001, ctx.currentTime + i * .18 + .16);
      o.start(ctx.currentTime + i * .18); o.stop(ctx.currentTime + i * .18 + .17);
    });
  } catch (e) {}
  if (navigator.vibrate) navigator.vibrate([120, 80, 120]);
}

// Two-tap failure: first tap arms an inline reason field (no native dialogs)
function dFail(oid, btn) {
  const cell = btn.parentElement;
  if (!cell.dataset.armed) {
    cell.dataset.armed = '1';
    btn.outerHTML = `<div style="display:flex;gap:.35rem">
      <input type="text" id="fail_${oid}" style="flex:1;min-width:0;padding:.4rem .5rem;border:1px solid #FECACA;border-radius:9px;font-family:inherit;font-size:.72rem" placeholder="What went wrong?">
      <button type="button" class="mbtn mbtn-danger" style="padding:.4rem .55rem" onclick="submitFail(${oid})"><i class="fa fa-check"></i></button>
    </div>`;
    document.getElementById('fail_' + oid).focus();
    document.getElementById('fail_' + oid).addEventListener('keydown', e => { if (e.key === 'Enter') submitFail(oid); });
    return;
  }
  submitFail(oid);
}
function submitFail(oid) {
  const reason = (document.getElementById('fail_' + oid)?.value || '').trim();
  const old = document.getElementById('rideList').innerHTML;
  mPost('<?= base_url('driver/act') ?>', { order_id: oid, action: 'fail', notes: reason })
    .then(d => {
      if (d.success) { mToast(d.message, false); setTimeout(() => location.reload(), 900); }
      else { mToast(d.message || 'Action failed', false); document.getElementById('rideList').innerHTML = old; }
    })
    .catch(e => { mToast(e.message || 'Network error', false); document.getElementById('rideList').innerHTML = old; });
}

// Poll the list so new assignments drop in on their own + ding loudly
let KNOWN_OIDS = new Set(<?= json_encode(array_map('strval', array_column($active, 'id'))) ?>);
setInterval(() => {
  fetch('<?= base_url('driver') ?>', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
    .then(r => r.text()).then(html => {
      const doc = new DOMParser().parseFromString(html, 'text/html');
      const fresh = doc.querySelector('#rideList');
      const cur = document.getElementById('rideList');
      if (fresh && cur && fresh.innerHTML !== cur.innerHTML) {
        cur.innerHTML = fresh.innerHTML;
        // beep for every order we just received that we had not seen
        fresh.querySelectorAll('.mcardx[data-oid]').forEach(c => {
          if (!KNOWN_OIDS.has(c.dataset.oid)) { KNOWN_OIDS.add(c.dataset.oid); dingNewOrder(); mToast('🛵 New delivery assigned!', true); }
        });
      }
    }).catch(() => {});
}, 15000);

// GPS breadcrumb — ping while the app is open (fine-grained, silent)
const geoOpt = { enableHighAccuracy: true, timeout: 10000, maximumAge: 20000 };
function pingGps() {
  if (!navigator.geolocation) return;
  navigator.geolocation.getCurrentPosition(
    pos => mPost('<?= base_url('driver/ping') ?>', {
      lat: pos.coords.latitude, lng: pos.coords.longitude, accuracy: pos.coords.accuracy
    }).catch(() => {}),
    () => {},
    geoOpt
  );
}
pingGps();
setInterval(pingGps, 30000);

// Sync the on-duty state into the native service on every page load —
// lets the app re-launch the service after a phone restart / app kill.
if (typeof window.AndroidDinoVIX !== 'undefined' && window.AndroidDinoVIX.setDuty) {
  try { window.AndroidDinoVIX.setDuty(<?= $dutyOn ? 'true' : 'false' ?>); } catch (e) {}
}
</script>
<?php $this->endSection(); ?>
