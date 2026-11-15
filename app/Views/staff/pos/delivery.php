<?php $this->extend('layouts/main'); $this->section('content');
$CSRF = csrf_token() . '=' . urlencode(csrf_hash());
$sym  = function_exists('currency_symbol') ? currency_symbol() : '₹';
$stateMeta = [
  'unassigned'      => ['badge' => 'badge-danger',  'label' => 'Unassigned',       'color' => 'var(--danger)'],
  'assigned'        => ['badge' => 'badge-warning', 'label' => 'Assigned',         'color' => '#D69E2E'],
  'out_for_delivery'=>['badge' => 'badge-info',     'label' => 'Out for delivery', 'color' => '#3182CE'],
  'delivered'       => ['badge' => 'badge-success', 'label' => 'Delivered',        'color' => 'var(--success)'],
  'failed'          => ['badge' => 'badge-danger',  'label' => 'Failed',           'color' => '#7F1D1D'],
];
$modeLabel = ['auto' => 'Own + Fleet riders', 'self' => 'Own riders only', 'dinovix' => 'DinoviX fleet'];
?>
<div style="padding:0 1rem;max-width:1000px" id="boardWrap">
  <!-- Header + stats -->
  <div class="card" style="margin-bottom:1rem">
    <div class="card-header">
      <span class="card-title"><i class="fa fa-motorcycle" style="color:var(--primary)"></i> Delivery Board</span>
      <div style="display:flex;gap:.5rem;align-items:center">
        <span style="font-size:.68rem;font-weight:800;color:var(--text-m);background:#F1F5F9;padding:.3rem .6rem;border-radius:8px">
          Auto-dispatch: <b><?= $modeLabel[$mode] ?? $mode ?></b>
        </span>
        <a href="<?= base_url('pos') ?>" class="btn btn-sm btn-outline" style="white-space:nowrap"><i class="fa fa-cash-register"></i> Back to POS</a>
      </div>
    </div>
    <div class="card-body">
      <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:.7rem">
        <div style="text-align:center;padding:.8rem .4rem;background:#FEF2F2;border-radius:10px">
          <div style="font-size:1.35rem;font-weight:800;color:var(--danger)"><?= $stats['unassigned'] ?></div>
          <div style="font-size:.72rem;color:var(--text-muted)">Unassigned</div>
        </div>
        <div style="text-align:center;padding:.8rem .4rem;background:#FFFBEB;border-radius:10px">
          <div style="font-size:1.35rem;font-weight:800;color:#D69E2E"><?= $stats['active'] ?></div>
          <div style="font-size:.72rem;color:var(--text-muted)">On the road</div>
        </div>
        <div style="text-align:center;padding:.8rem .4rem;background:#F0FFF4;border-radius:10px">
          <div style="font-size:1.35rem;font-weight:800;color:var(--success)"><?= $stats['delivered'] ?></div>
          <div style="font-size:.72rem;color:var(--text-muted)">Delivered</div>
        </div>
        <div style="text-align:center;padding:.8rem .4rem;background:#F1F5F9;border-radius:10px">
          <div style="font-size:1.35rem;font-weight:800;color:#475569"><?= $stats['failed'] ?></div>
          <div style="font-size:.72rem;color:var(--text-muted)">Failed</div>
        </div>
      </div>
    </div>
  </div>

  <!-- Orders -->
  <?php if (empty($orders)): ?>
  <div class="card">
    <div class="card-body" style="text-align:center;padding:2.5rem 1rem">
      <i class="fa fa-motorcycle" style="font-size:2rem;color:var(--border)"></i>
      <p style="color:var(--text-muted);margin-top:.5rem">No delivery orders yet. Create one from POS → Delivery.</p>
    </div>
  </div>
  <?php else: foreach ($orders as $o):
    $ds = $o['delivery_status'] ?: 'unassigned';
    $m  = $stateMeta[$ds];
    $closed = in_array($ds, ['delivered', 'failed']);
    $waiting = $ds === 'unassigned';
  ?>
  <div class="card" style="margin-bottom:.8rem;<?= $waiting ? 'border:1.5px solid var(--danger);box-shadow:0 0 0 3px rgba(220,38,38,.07)' : '' ?>" id="ocard<?= $o['id'] ?>">
    <div class="card-body">
      <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:.6rem;flex-wrap:wrap">
        <div style="min-width:220px">
          <div style="font-weight:800;font-size:.95rem">
            <?= esc($o['order_number']) ?>
            <span class="badge-pill <?= $m['badge'] ?>" style="margin-left:.4rem"><?= $m['label'] ?></span>
            <?php if ($o['payment_status'] === 'paid'): ?><span class="badge-pill badge-success" style="margin-left:.25rem">Paid</span>
            <?php else: ?><span class="badge-pill badge-warning" style="margin-left:.25rem">COD / Unpaid</span><?php endif; ?>
          </div>
          <div style="font-size:.8rem;color:var(--text-muted);margin-top:.2rem">
            <?= esc($o['customer_name'] ?: 'Walk-in') ?> · <?= esc($o['customer_phone'] ?: '—') ?>
            · <?= date('d M h:i A', strtotime($o['created_at'])) ?>
          </div>
          <?php if ($o['delivery_address']): ?>
          <div style="font-size:.78rem;color:var(--text-muted);margin-top:.25rem"><i class="fa fa-location-dot"></i> <?= esc($o['delivery_address']) ?></div>
          <?php endif; ?>
          <?php if ($o['rider_name']): ?>
          <div style="font-size:.78rem;margin-top:.25rem"><i class="fa fa-user" style="color:var(--primary)"></i> Rider: <strong><?= esc($o['rider_name']) ?></strong></div>
          <?php endif; ?>
        </div>
        <div style="text-align:right">
          <div style="font-weight:900;font-size:1.15rem;color:var(--primary)"><?= $sym ?><?= number_format($o['total_amount'], 2) ?></div>
        </div>
      </div>

      <?php if (!$closed): ?>
      <div style="display:flex;gap:.5rem;margin-top:.75rem;flex-wrap:wrap;align-items:center">
        <?php if ($waiting): ?>
        <button class="btn btn-sm btn-primary" onclick="autoAssign(<?= $o['id'] ?>, this)"><i class="fa fa-bolt"></i> Auto-assign best rider</button>
        <?php endif; ?>
        <select id="rider<?= $o['id'] ?>" style="padding:.5rem .6rem;border:1.5px solid var(--border);border-radius:8px;font-size:.8rem;font-weight:600;background:#fff;outline:none;max-width:230px">
          <option value="0">— pick a rider manually —</option>
          <?php foreach ($riderCards as $r): ?>
          <option value="<?= $r['id'] ?>" <?= $o['rider_id'] == $r['id'] ? 'selected' : '' ?>>
            <?= esc($r['name']) ?> · <?= $r['employer'] === 'dinovix' ? 'Fleet' : 'Own' ?> · <?= $r['busy'] ? 'busy(' . $r['busy'] . ')' : 'free' ?><?= $r['km'] !== null ? ' · ≈' . $r['km'] . 'km' : '' ?><?= $r['duty'] ? '' : ' · off duty' ?>
          </option>
          <?php endforeach; ?>
        </select>
        <button class="btn btn-sm btn-outline" onclick="assignRider(<?= $o['id'] ?>)"><i class="fa fa-user-check"></i> Assign</button>
        <?php if ($o['rider_id'] && $ds !== 'out_for_delivery'): ?>
        <button class="btn btn-sm btn-outline" onclick="setStatus(<?= $o['id'] ?>,'out_for_delivery')"><i class="fa fa-route"></i> Out for delivery</button>
        <?php endif; ?>
        <?php if ($ds === 'out_for_delivery'): ?>
        <button class="btn btn-sm btn-success" onclick="setStatus(<?= $o['id'] ?>,'delivered')"><i class="fa fa-circle-check"></i> Delivered</button>
        <button class="btn btn-sm btn-danger" onclick="armFail(<?= $o['id'] ?>, this)"><i class="fa fa-circle-xmark"></i> Failed</button>
        <?php endif; ?>
        <?php if ($o['rider_id'] && !$closed): ?>
        <button class="btn btn-sm btn-outline" title="Unassign" onclick="unassign(<?= $o['id'] ?>)"><i class="fa fa-user-slash"></i></button>
        <?php endif; ?>
      </div>
      <?php elseif ($ds === 'delivered'): ?>
      <div style="font-size:.78rem;color:var(--success);margin-top:.5rem"><i class="fa fa-circle-check"></i> Delivered at <?= date('d M h:i A', strtotime($o['delivered_at'])) ?></div>
      <?php endif; ?>
    </div>
  </div>
  <?php endforeach; endif; ?>
</div>

<!-- toast (no native dialogs) -->
<div id="boardToast" style="display:none;position:fixed;bottom:1.2rem;left:50%;transform:translateX(-50%);z-index:999;padding:.7rem 1.1rem;border-radius:12px;font-weight:800;font-size:.85rem;box-shadow:0 10px 30px rgba(2,6,23,.25)"></div>

<script>
const CSRF = '<?= $CSRF ?>';
function toast(msg, type) {
  const el = document.getElementById('boardToast');
  const c = { success: ['#F0FDF4', '#BBF7D0', '#15803D'], error: ['#FEF2F2', '#FECACA', '#B91C1C'], info: ['#EFF6FF', '#BFDBFE', '#1D4ED8'] }[type || 'info'];
  el.style.background = c[0]; el.style.border = '1.5px solid ' + c[1]; el.style.color = c[2];
  el.textContent = msg; el.style.display = 'block';
  clearTimeout(el._t); el._t = setTimeout(() => el.style.display = 'none', 3200);
}
function post(url, body) {
  return fetch(url, { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded','X-Requested-With':'XMLHttpRequest'}, body: CSRF + '&' + body })
    .then(r => r.json());
}
function ding() {
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
}
function autoAssign(id, btn) {
  const old = btn.innerHTML;
  btn.disabled = true; btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Dispatching…';
  post('<?= base_url('pos/delivery/auto/' ) ?>' + id, 'x=1')
    .then(d => { if (d.success) { toast(d.message, 'success'); ding(); setTimeout(() => location.reload(), 700); } else { toast(d.message, 'error'); btn.disabled = false; btn.innerHTML = old; } })
    .catch(() => { toast('Network error', 'error'); btn.disabled = false; btn.innerHTML = old; });
}
function assignRider(id) {
  const v = document.getElementById('rider' + id).value;
  if (!v || v === '0') { toast('Pick a rider first', 'error'); return; }
  post('<?= base_url('pos/delivery/assign') ?>', 'order_id=' + id + '&rider_id=' + v)
    .then(d => { if (d.success) { toast(d.message, 'success'); ding(); setTimeout(() => location.reload(), 700); } else toast(d.message, 'error'); });
}
function unassign(id) {
  post('<?= base_url('pos/delivery/assign') ?>', 'order_id=' + id + '&rider_id=0')
    .then(d => { if (d.success) location.reload(); else toast(d.message, 'error'); });
}
function setStatus(id, s) {
  const body = 'order_id=' + id + '&delivery_status=' + s;
  post('<?= base_url('pos/delivery/status') ?>', body)
    .then(d => { if (d.success) location.reload(); else toast(d.message, 'error'); });
}
// Two-tap failure with an inline reason field (no native prompt)
function armFail(id, btn) {
  const cell = btn.parentElement;
  if (!cell.dataset.armed) {
    cell.dataset.armed = '1';
    btn.outerHTML = `<span style="display:inline-flex;gap:.4rem;align-items:center">
      <input id="fail_${id}" placeholder="What went wrong?" style="padding:.45rem .55rem;border:1.5px solid #FECACA;border-radius:8px;font-family:inherit;font-size:.78rem;outline:none">
      <button class="btn btn-sm btn-danger" onclick="submitFail(${id})"><i class="fa fa-check"></i></button>
    </span>`;
    document.getElementById('fail_' + id).focus();
    return;
  }
  submitFail(id);
}
function submitFail(id) {
  const reason = (document.getElementById('fail_' + id)?.value || '').trim();
  post('<?= base_url('pos/delivery/status') ?>', 'order_id=' + id + '&delivery_status=failed&notes=' + encodeURIComponent(reason))
    .then(d => { if (d.success) location.reload(); else toast(d.message, 'error'); });
}

// Live board: silent refresh every 20s + ding when new unassigned orders appear
let LAST_UNASSIGNED = <?= $stats['unassigned'] ?>;
setInterval(() => {
  if (document.hidden) return;
  fetch(location.href, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
    .then(r => r.text()).then(html => {
      const fresh = new DOMParser().parseFromString(html, 'text/html').querySelector('#boardWrap');
      const cur = document.getElementById('boardWrap');
      if (fresh && cur && fresh.innerHTML !== cur.innerHTML) {
        const m = fresh.innerHTML.match(/font-weight:800;color:var\(--danger\)">(\d+)</);
        const now = m ? parseInt(m[1]) : LAST_UNASSIGNED;
        cur.innerHTML = fresh.innerHTML;
        if (now > LAST_UNASSIGNED) { ding(); toast('🛵 New delivery waiting for a rider!', 'info'); }
        LAST_UNASSIGNED = now;
      }
    }).catch(() => {});
}, 20000);
</script>
<?php $this->endSection(); ?>
