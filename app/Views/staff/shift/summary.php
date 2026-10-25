<?php
$this->extend('layouts/main'); $this->section('content');
$CSRF = csrf_token() . '=' . urlencode(csrf_hash());
$sym  = function_exists('currency_symbol') ? currency_symbol() : '₹';
?>
<div style="padding:0 1rem;max-width:860px">
  <!-- Drawer status header -->
  <div class="card" style="margin-bottom:1rem">
    <div class="card-header">
      <span class="card-title"><i class="fa fa-cash-register" style="color:var(--primary)"></i> Cash Counter — <?= date('d M Y, h:i A') ?></span>
      <?php if ($openShift): ?>
        <span style="font-weight:800;color:var(--success)">COUNTER OPEN since <?= date('h:i A', strtotime($openShift['opened_at'])) ?></span>
      <?php else: ?>
        <span style="font-weight:800;color:var(--text-muted)">COUNTER CLOSED</span>
      <?php endif; ?>
    </div>
    <div class="card-body">
      <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:.7rem;margin-bottom:.75rem">
        <div style="text-align:center;padding:.8rem .5rem;background:var(--primary-light);border-radius:10px">
          <div style="font-size:1.3rem;font-weight:800;color:var(--primary)"><?= $summary['total_orders'] ?? 0 ?></div>
          <div style="font-size:.72rem;color:var(--text-muted)">Orders Today</div>
        </div>
        <div style="text-align:center;padding:.8rem .5rem;background:#F0FFF4;border-radius:10px">
          <div style="font-size:1.3rem;font-weight:800;color:var(--success)"><?= $sym ?><?= number_format(($summary['total_revenue'] ?? 0), 0) ?></div>
          <div style="font-size:.72rem;color:var(--text-muted)">Sales (all methods)</div>
        </div>
        <div style="text-align:center;padding:.8rem .5rem;background:#EFF6FF;border-radius:10px">
          <div style="font-size:1.3rem;font-weight:800;color:#3182CE"><?= $sym ?><?= number_format($cashInShift, 0) ?></div>
          <div style="font-size:.72rem;color:var(--text-muted)">Cash Sales (this shift)</div>
        </div>
        <div style="text-align:center;padding:.8rem .5rem;background:#FFFBEB;border-radius:10px">
          <div style="font-size:1.3rem;font-weight:800;color:#D69E2E"><?= $sym ?><?= number_format(max(0, $drawerExpected), 2) ?></div>
          <div style="font-size:.72rem;color:var(--text-muted)">Expected in Drawer</div>
        </div>
      </div>
      <?php if ($handoversOut > 0): ?>
      <div style="font-size:.8rem;color:var(--text-muted)"><i class="fa fa-arrow-up-from-bracket"></i> <?= $sym ?><?= number_format($handoversOut, 2) ?> already handed over / deposited from this shift</div>
      <?php endif; ?>
      <div style="font-size:.78rem;color:var(--text-muted);margin-top:.3rem">Drawer math: Opening <?= $sym.number_format($openShift ? $openShift['opening_cash'] : 0, 2) ?> + Cash sales <?= $sym.number_format($cashInShift, 2) ?> − Handovers <?= $sym.number_format($handoversOut, 2) ?></div>
    </div>
  </div>

  <!-- OPEN COUNTER (denominations) -->
  <?php if (empty($openShift)): ?>
  <div class="card" style="margin-bottom:1rem">
    <div class="card-header"><span class="card-title"><i class="fa fa-unlock" style="color:var(--success)"></i> Open Counter — count your float</span></div>
    <div class="card-body">
      <div id="denomGridOpen"></div>
      <div style="display:flex;justify-content:space-between;align-items:center;margin-top:.75rem;padding:.7rem 1rem;background:#F0FDF4;border-radius:10px">
        <span style="font-weight:700">Opening float total</span>
        <strong id="openTotal" style="font-size:1.25rem;color:var(--success)"><?= $sym ?>0.00</strong>
      </div>
      <button type="button" class="btn btn-success btn-block" style="margin-top:.75rem" onclick="openCounter()"><i class="fa fa-unlock"></i> Open Counter</button>
      <div style="text-align:center;margin-top:.4rem"><a href="#" id="skipOpen" style="font-size:.75rem;color:var(--text-muted)">or declare total without denominations</a></div>
    </div>
  </div>
  <?php endif; ?>

  <!-- HANDOVER / BANK DEPOSIT -->
  <?php if ($openShift): ?>
  <div class="card" style="margin-bottom:1rem">
    <div class="card-header"><span class="card-title"><i class="fa fa-hand-holding-dollar" style="color:#6366F1"></i> Handover / Bank Deposit</span></div>
    <div class="card-body">
      <div style="display:flex;gap:.5rem;margin-bottom:.75rem" id="handoverTypeRow">
        <?php foreach ([['handover','Hand Over','fa-user-check'],['bank_deposit','Bank Deposit','fa-building-columns'],['safe_drop','Safe Drop','fa-vault']] as $ht): ?>
        <button type="button" class="hv-type<?= $ht[0]==='handover' ? ' on' : '' ?>" data-type="<?= $ht[0] ?>"
          style="flex:1;padding:.65rem .4rem;border:1.5px solid var(--border);border-radius:10px;background:#fff;cursor:pointer;font-weight:700;font-size:.8rem;display:flex;flex-direction:column;align-items:center;gap:.25rem">
          <i class="fa <?= $ht[2] ?>" style="font-size:1rem;color:#6366F1"></i><?= $ht[1] ?>
        </button>
        <?php endforeach; ?>
      </div>
      <div id="denomGridHandover"></div>
      <div style="display:flex;justify-content:space-between;align-items:center;margin-top:.75rem;padding:.7rem 1rem;background:#EEF2FF;border-radius:10px">
        <span style="font-weight:700">Handover amount</span>
        <strong id="handoverTotal" style="font-size:1.25rem;color:#6366F1"><?= $sym ?>0.00</strong>
      </div>
      <input type="hidden" id="handoverType" value="handover">
      <div style="display:flex;gap:.5rem;margin-top:.75rem">
        <input type="text" id="hvNotes" class="form-control" placeholder="Note (optional — e.g. heavy cash to cash office)">
        <button type="button" class="btn btn-primary" onclick="doHandover()" style="white-space:nowrap"><i class="fa fa-receipt"></i> Generate Slip</button>
      </div>
    </div>
  </div>

  <!-- CLOSE COUNTER -->
  <div class="card" style="margin-bottom:1rem">
    <div class="card-header"><span class="card-title"><i class="fa fa-lock" style="color:var(--danger)"></i> Close Counter — recount all denominations</span></div>
    <div class="card-body">
      <div id="denomGridClose"></div>
      <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:.6rem;margin-top:.75rem">
        <div style="padding:.7rem;background:#F8FAFC;border-radius:10px;text-align:center"><div style="font-size:.7rem;color:var(--text-muted)">Expected</div><strong id="cExpected"><?= $sym.number_format(max(0,$drawerExpected),2) ?></strong></div>
        <div style="padding:.7rem;background:#F8FAFC;border-radius:10px;text-align:center"><div style="font-size:.7rem;color:var(--text-muted)">Counted</div><strong id="cCounted"><?= $sym ?>0.00</strong></div>
        <div style="padding:.7rem;background:#F8FAFC;border-radius:10px;text-align:center"><div style="font-size:.7rem;color:var(--text-muted)">Difference</div><strong id="cDiff">—</strong></div>
      </div>
      <button type="button" class="btn btn-danger btn-block" style="margin-top:.75rem" onclick="closeCounter()"><i class="fa fa-lock"></i> Close Counter & Reconcile</button>
    </div>
  </div>

  <!-- Handover slips of this shift -->
  <?php if (!empty($handovers)): ?>
  <div class="card" style="margin-bottom:1rem">
    <div class="card-header"><span class="card-title">Slips generated this shift</span></div>
    <div class="table-wrap">
      <table class="table">
        <thead><tr><th>Slip #</th><th>Type</th><th>Amount</th><th>Status</th><th>Received by</th><th></th></tr></thead>
        <tbody>
          <?php foreach ($handovers as $h): ?>
          <tr style="font-size:.8rem">
            <td style="font-weight:700"><?= esc($h['slip_number']) ?></td>
            <td><?= ucfirst(str_replace('_',' ',$h['movement_type'])) ?></td>
            <td><strong><?= $sym.number_format($h['amount'],2) ?></strong></td>
            <td>
              <?php if ($h['status']==='pending'): ?><span class="badge-pill badge-warning">Pending</span>
              <?php elseif ($h['status']==='handed'): ?><span class="badge-pill badge-success">Handed</span>
              <?php elseif ($h['status']==='deposited'): ?><span class="badge-pill badge-info">Deposited</span>
              <?php else: ?><span class="badge-pill badge-danger">Cancelled</span><?php endif; ?>
            </td>
            <td><?= esc($h['received_by'] ?? '—') ?></td>
            <td style="white-space:nowrap">
              <a href="<?= base_url('pos/shift/handover-slip/'.$h['id']) ?>" target="_blank" class="btn btn-sm btn-outline" title="Print slip"><i class="fa fa-print"></i></a>
              <?php if ($h['status']==='pending'): ?>
              <button class="btn btn-sm btn-outline" title="Mark handed/deposited" onclick="receiveHandover(<?= $h['id'] ?>,'<?= $h['movement_type'] ?>')"><i class="fa fa-check"></i></button>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php endif; ?>
  <?php endif; ?>

  <!-- Payment breakdown -->
  <div class="card" style="margin-bottom:1rem">
    <div class="card-header"><span class="card-title">Today's payment breakdown</span></div>
    <div class="card-body">
      <?php if (empty($payments)): ?>
        <p style="color:var(--text-muted);font-size:.85rem;margin:0">No payments yet today.</p>
      <?php else: foreach ($payments as $p): ?>
        <div style="display:flex;justify-content:space-between;padding:.4rem 0;border-bottom:1px solid var(--border);font-size:.875rem">
          <span><?= ucfirst(str_replace('_',' ',$p['payment_method'])) ?> (<?= $p['count'] ?>)</span>
          <strong><?= $sym ?><?= number_format($p['total'],2) ?></strong>
        </div>
      <?php endforeach; endif; ?>
    </div>
  </div>

  <!-- Recent counters -->
  <?php if (!empty($shifts)): ?>
  <div class="card" style="margin-bottom:1rem">
    <div class="card-header"><span class="card-title">Previous counters</span></div>
    <div class="table-wrap">
      <table class="table">
        <thead><tr><th>Opened</th><th>Closed</th><th>Opening</th><th>Sales</th><th>Handed</th><th>Diff</th></tr></thead>
        <tbody>
          <?php foreach ($shifts as $s): $out = ($s['opening_cash'] + $s['cash_sales']) - ($s['expected_cash'] ?: ($s['opening_cash'] + $s['cash_sales'])); ?>
          <tr style="font-size:.8rem">
            <td><?= date('d M h:i A', strtotime($s['opened_at'])) ?></td>
            <td><?= $s['closed_at'] ? date('d M h:i A', strtotime($s['closed_at'])) : '<span style="color:var(--success);font-weight:700">Open</span>' ?></td>
            <td><?= $sym.number_format($s['opening_cash'],2) ?></td>
            <td><?= $sym.number_format($s['total_sales'] ?? 0,2) ?> <?= $s['orders_count'] ? '<span style="color:var(--text-muted);font-size:.7rem">(' . (int)$s['orders_count'] . ' orders)</span>' : '' ?></td>
            <td><?= $s['closed_at'] ? $sym.number_format((float)($s['opening_cash'] + $s['cash_sales']) - (float)($s['expected_cash'] ?: 0), 2) : '—' ?></td>
            <td style="color:<?= (float)($s['difference'] ?? 0) > 0 ? 'var(--success)' : ((float)($s['difference'] ?? 0) < 0 ? 'var(--danger)' : 'inherit') ?>">
              <?= $s['closed_at'] ? $sym.number_format($s['difference'] ?? 0,2) : '—' ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php endif; ?>

  <a href="<?= base_url('pos') ?>" class="btn btn-primary btn-block" style="margin-bottom:2rem"><i class="fa fa-cash-register"></i> Back to POS</a>
</div>

<script>
const DENOMS = [2000, 500, 200, 100, 50, 20, 10, 5, 2, 1];
const SYM = '<?= $sym ?>';
const CSRF = '<?= $CSRF ?>';
const IS_OPEN = <?= $openShift ? 'true' : 'false' ?>;

/* Build a denomination counter grid inside a container */
function buildDenomGrid(containerId, totalId) {
  const el = document.getElementById(containerId);
  el.innerHTML = `
    <div style="display:grid;grid-template-columns:repeat(5,1fr);gap:.45rem">
      ${DENOMS.map(d => `
        <div style="border:1.5px solid var(--border);border-radius:10px;padding:.45rem;text-align:center;background:#fff">
          <div style="font-weight:800;font-size:.85rem">${SYM}${d}</div>
          <input type="number" min="0" value="" placeholder="× 0" data-denom="${d}" class="denom-in"
                 style="width:100%;border:none;outline:none;text-align:center;font-weight:700;font-size:.95rem;margin-top:.2rem;padding:.2rem 0">
          <div style="font-size:.62rem;color:var(--text-muted)" data-part="${d}">${SYM}0</div>
        </div>`).join('')}
    </div>
    <div style="display:flex;gap:.4rem;margin-top:.5rem;flex-wrap:wrap">
      <button type="button" class="btn btn-sm btn-outline" onclick="fillDenom('${containerId}','all')"><i class="fa fa-arrows-rotate"></i> Count all boxes here</button>
      <button type="button" class="btn btn-sm btn-outline" onclick="clearDenom('${containerId}')"><i class="fa fa-eraser"></i> Clear</button>
    </div>`;
  el.querySelectorAll('.denom-in').forEach(inp => inp.addEventListener('input', () => updateDenomTotal(containerId, totalId)));
}

/* Quick-fill helper: "1 x 10, 2 x ?, 5, 10, 20, 50" style — tap a box and type the note count */
function fillDenom(containerId, mode) {
  // convenient preset: assumed 0 everywhere; user types counts per note
  updateDenomTotal(containerId, null);
}
function clearDenom(containerId) {
  document.querySelectorAll(`#${containerId} .denom-in`).forEach(i => { i.value = ''; });
  const gid = containerId.replace('denomGrid', '');
  updateDenomTotal(containerId, containerId.includes('Open') ? 'openTotal' : containerId.includes('Close') ? 'cCounted' : 'handoverTotal');
}
function denomTotalOf(containerId) {
  let total = 0;
  document.querySelectorAll(`#${containerId} .denom-in`).forEach(inp => {
    total += (parseInt(inp.dataset.denom, 10) || 0) * (parseInt(inp.value, 10) || 0);
  });
  return total;
}
function updateDenomTotal(containerId, totalId) {
  const total = denomTotalOf(containerId);
  document.querySelectorAll(`#${containerId} .denom-in`).forEach(inp => {
    const d = parseInt(inp.dataset.denom, 10), c = parseInt(inp.value, 10) || 0;
    const part = inp.closest('div').parentElement.querySelector(`[data-part="${d}"]`);
    if (part) part.textContent = SYM + (d * c).toLocaleString('en-IN');
  });
  if (!totalId) return;
  const tEl = document.getElementById(totalId);
  if (tEl) tEl.textContent = SYM + total.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  // close counter side-updates
  if (containerId === 'denomGridClose') {
    const expected = parseFloat(('<?= max(0, $drawerExpected) ?>'));
    const diff = total - expected;
    document.getElementById('cCounted').textContent = SYM + total.toLocaleString('en-IN');
    const dEl = document.getElementById('cDiff');
    dEl.textContent = SYM + diff.toLocaleString('en-IN');
    dEl.style.color = Math.abs(diff) < 10 ? 'var(--success)' : (diff > 0 ? '#D69E2E' : 'var(--danger)');
  }
}
function denomsJson(containerId) {
  const obj = {};
  document.querySelectorAll(`#${containerId} .denom-in`).forEach(inp => {
    const c = parseInt(inp.value, 10) || 0;
    if (c > 0) obj[inp.dataset.denom] = c;
  });
  return JSON.stringify(obj);
}

function post(url, body) {
  return fetch(url, { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded','X-Requested-With':'XMLHttpRequest'}, body: CSRF + '&' + body })
    .then(r => r.json());
}

<?php if (empty($openShift)): ?>
function openCounter() {
  post('<?= base_url('pos/shift/open') ?>', 'denominations=' + encodeURIComponent(denomsJson('denomGridOpen')))
    .then(d => {
      if (d.success) { toastify('Counter opened with ' + SYM + d.opening.toFixed(2)); location.reload(); }
      else alert(d.message || 'Could not open counter');
    }).catch(() => alert('Network error'));
}
document.getElementById('skipOpen')?.addEventListener('click', e => {
  e.preventDefault();
  const total = prompt('Declare the total float amount in drawer:', '500');
  if (total === null) return;
  post('<?= base_url('pos/shift/open') ?>', 'opening_cash=' + encodeURIComponent(total))
    .then(d => { if (d.success) location.reload(); else alert(d.message || 'Could not open counter'); })
    .catch(() => alert('Network error'));
});
<?php endif; ?>

<?php if ($openShift): ?>
document.querySelectorAll('.hv-type').forEach(b => b.addEventListener('click', () => {
  document.querySelectorAll('.hv-type').forEach(x => { x.classList.remove('on'); x.style.background = '#fff'; x.style.borderColor = 'var(--border)'; });
  b.classList.add('on'); b.style.borderColor = '#6366F1'; b.style.background = '#EEF2FF';
  document.getElementById('handoverType').value = b.dataset.type;
}));

function doHandover() {
  const body = 'movement_type=' + document.getElementById('handoverType').value +
               '&denominations=' + encodeURIComponent(denomsJson('denomGridHandover')) +
               '&notes=' + encodeURIComponent(document.getElementById('hvNotes').value);
  post('<?= base_url('pos/shift/handover') ?>', body).then(d => {
    if (d.success) {
      window.open(d.slip_url, '_blank', 'width=420,height=640');
      toastify('Slip ' + d.slip_number + ' generated');
      setTimeout(() => location.reload(), 1200);
    } else alert(d.message || 'Could not generate slip');
  }).catch(() => alert('Network error'));
}

function receiveHandover(id, type) {
  const who = prompt(type === 'bank_deposit' ? 'Deposit reference / ack number (optional):' : 'Name of the person receiving the cash:', '');
  if (who === null) return;
  post('<?= base_url('pos/shift/handover-receive') ?>/' + id, 'action=' + type + '&received_by=' + encodeURIComponent(who))
    .then(d => { if (d.success) location.reload(); else alert(d.message || 'Failed'); })
    .catch(() => alert('Network error'));
}

function closeCounter() {
  if (!confirm('Close the counter now and reconcile cash?')) return;
  const body = 'denominations=' + encodeURIComponent(denomsJson('denomGridClose'));
  post('<?= base_url('pos/shift/close') ?>', body).then(d => {
    if (d.success) {
      alert('Counter closed.\nOpening: ' + SYM + d.opening.toFixed(2) +
            '\nCash sales: ' + SYM + d.cash_sales.toFixed(2) +
            '\nHanded out: ' + SYM + d.handed_out.toFixed(2) +
            '\nExpected: ' + SYM + d.expected.toFixed(2) +
            '\nCounted: ' + SYM + d.counted.toFixed(2) +
            '\nDifference: ' + SYM + d.difference.toFixed(2) + ' (' + d.status + ')');
      if (d.z_slip_url) window.open(d.z_slip_url + '?autoprint=1', 'z_slip', 'width=360,height=780,scrollbars=yes');
      location.reload();
    } else alert(d.message || 'Could not close counter');
  }).catch(() => alert('Network error'));
}
<?php endif; ?>

function toastify(msg) {
  const el = document.createElement('div');
  el.textContent = msg;
  el.style.cssText = 'position:fixed;bottom:1.4rem;left:50%;transform:translateX(-50%);background:#111827;color:#fff;padding:.7rem 1.2rem;border-radius:10px;font-weight:700;font-size:.85rem;z-index:99999;box-shadow:0 8px 24px rgba(0,0,0,.25)';
  document.body.appendChild(el);
  setTimeout(() => el.remove(), 2600);
}

initTab();
function initTab() {
  buildDenomGrid('denomGrid<?= $openShift ? 'Close' : 'Open' ?>', '<?= $openShift ? 'cCounted' : 'openTotal' ?>');
  if (IS_OPEN) {
    buildDenomGrid('denomGridHandover', 'handoverTotal');
    const first = document.querySelector('.hv-type.on');
    if (first) { first.style.borderColor = '#6366F1'; first.style.background = '#EEF2FF'; }
  }
}
</script>
<?php $this->endSection(); ?>
