<?php $this->extend('layouts/main'); $this->section('content'); ?>
<div style="padding:0 1rem">

  <!-- Header -->
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem">
    <div style="font-size:.8rem;color:var(--text-muted)">
      <strong><?= count($tables) ?></strong> tables
    </div>
    <button class="btn btn-primary" onclick="openModal('addTableModal')">
      <i class="fa fa-plus"></i> Add Table
    </button>
  </div>

  <!-- Table grid -->
  <?php if (empty($tables)): ?>
  <div class="empty-state"><i class="fa fa-chair"></i><p>No tables yet. Add your first table.</p></div>
  <?php else: ?>
  <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:.75rem">
    <?php foreach ($tables as $t):
      $statusColor = match($t['status']) {
        'available' => '#15803D', 'occupied'  => '#B91C1C',
        'booked'    => '#6D28D9', 'reserved'  => '#92400E',
        default     => '#64748B'
      };
      $statusBg = match($t['status']) {
        'available' => '#F0FDF4', 'occupied'  => '#FFF1F2',
        'booked'    => '#F5F3FF', 'reserved'  => '#FFF7ED',
        default     => '#F8FAFC'
      };
      $statusBorder = match($t['status']) {
        'available' => '#86EFAC', 'occupied'  => '#FCA5A5',
        'booked'    => '#A78BFA', 'reserved'  => '#FCD34D',
        default     => '#E2E8F0'
      };
    ?>
    <div style="background:<?= $statusBg ?>;border:1.5px solid <?= $statusBorder ?>;border-radius:14px;padding:.875rem;position:relative;<?= !$t['is_active'] ? 'opacity:.5' : '' ?>">
      <!-- Status dot -->
      <div style="position:absolute;top:.7rem;right:.7rem;width:8px;height:8px;border-radius:50%;background:<?= $statusColor ?>"></div>

      <!-- Table number -->
      <div style="font-weight:900;font-size:1.4rem;color:#0F172A;margin-bottom:.1rem"><?= esc($t['table_number']) ?></div>
      <div style="font-size:.68rem;color:#94A3B8;display:flex;align-items:center;gap:.25rem;margin-bottom:.5rem">
        <i class="fa fa-users"></i> <?= $t['capacity'] ?> pax
        <?php if (!empty($t['booked_name'])): ?>
        · <span style="color:#6D28D9;font-weight:700"><?= esc($t['booked_name']) ?></span>
        <?php endif; ?>
      </div>
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.625rem">
        <span style="font-size:.6rem;font-weight:800;color:<?= $statusColor ?>;background:<?= $statusBg ?>;padding:.15rem .45rem;border-radius:20px;border:1px solid <?= $statusBorder ?>">
          <?= ucfirst($t['status']) ?>
        </span>
        <?php if (!empty($t['qr_token'])): ?>
        <span style="font-size:.6rem;color:#2563EB;background:#EFF6FF;padding:.15rem .4rem;border-radius:8px">
          <i class="fa fa-qrcode"></i> QR
        </span>
        <?php endif; ?>
      </div>
      <!-- Actions -->
      <div style="display:flex;gap:.35rem">
        <button onclick="showQr(<?= $t['id'] ?>,'<?= esc($t['table_number']) ?>','<?= $t['qr_token'] ?? '' ?>')"
                style="flex:1;padding:.35rem;border:1px solid #E2E8F0;border-radius:8px;background:#fff;cursor:pointer;font-size:.7rem;color:#2563EB" title="QR Code">
          <i class="fa fa-qrcode"></i>
        </button>
        <button onclick="editTable(<?= htmlspecialchars(json_encode($t),ENT_QUOTES) ?>)"
                style="flex:1;padding:.35rem;border:1px solid #E2E8F0;border-radius:8px;background:#fff;cursor:pointer;font-size:.7rem;color:#64748B" title="Edit">
          <i class="fa fa-pen"></i>
        </button>
        <button onclick="toggleTable(<?= $t['id'] ?>,<?= $t['is_active'] ?>)"
                style="flex:1;padding:.35rem;border:1px solid #E2E8F0;border-radius:8px;background:#fff;cursor:pointer;font-size:.7rem;color:<?= $t['is_active']?'#B91C1C':'#15803D' ?>" title="<?= $t['is_active']?'Deactivate':'Activate' ?>">
          <i class="fa <?= $t['is_active']?'fa-eye-slash':'fa-eye' ?>"></i>
        </button>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>

<!-- ── Add Table Modal ─────────────────────────────── -->
<div class="modal-overlay" id="addTableModal">
  <div class="modal" style="max-width:420px">
    <div class="modal-header">
      <span class="modal-title">Add Table</span>
      <button class="modal-close" onclick="closeModal('addTableModal')"><i class="fa fa-times"></i></button>
    </div>
    <div class="modal-body">
      <div class="form-row cols-2">
        <div class="form-group">
          <label class="form-label">Table Number *</label>
          <input type="text" class="form-control" id="tNum" placeholder="T01, A1, 01">
        </div>
        <div class="form-group">
          <label class="form-label">Capacity</label>
          <input type="number" class="form-control" id="tCap" value="4" min="1" max="50">
        </div>
      </div>
      <div class="form-row cols-2">
        <div class="form-group">
          <label class="form-label">Shape</label>
          <select class="form-control" id="tShape">
            <option value="square">Square</option>
            <option value="round">Round</option>
            <option value="rectangle">Rectangle</option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Sort Order</label>
          <input type="number" class="form-control" id="tSort" value="0" min="0">
        </div>
      </div>
      <?php if (!empty($areas)): ?>
      <div class="form-group">
        <label class="form-label">Area / Floor</label>
        <select class="form-control" id="tArea">
          <option value="">No Area</option>
          <?php foreach ($areas as $a): ?>
          <option value="<?= $a['id'] ?>"><?= esc($a['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <?php endif; ?>
      <div style="background:#F0FDF4;border:1px solid #86EFAC;border-radius:8px;padding:.625rem;font-size:.78rem;color:#15803D;margin-top:.25rem">
        <i class="fa fa-qrcode"></i> A unique QR code will be auto-generated for this table.
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-outline" onclick="closeModal('addTableModal')">Cancel</button>
      <button class="btn btn-primary" onclick="addTable()"><i class="fa fa-plus"></i> Add Table</button>
    </div>
  </div>
</div>

<!-- ── Edit Table Modal ────────────────────────────── -->
<div class="modal-overlay" id="editTableModal">
  <div class="modal" style="max-width:420px">
    <div class="modal-header">
      <span class="modal-title">Edit Table</span>
      <button class="modal-close" onclick="closeModal('editTableModal')"><i class="fa fa-times"></i></button>
    </div>
    <div class="modal-body">
      <input type="hidden" id="etId">
      <div class="form-row cols-2">
        <div class="form-group">
          <label class="form-label">Table Number *</label>
          <input type="text" class="form-control" id="etNum">
        </div>
        <div class="form-group">
          <label class="form-label">Capacity</label>
          <input type="number" class="form-control" id="etCap" min="1">
        </div>
      </div>
      <?php if (!empty($areas)): ?>
      <div class="form-group">
        <label class="form-label">Area / Floor</label>
        <select class="form-control" id="etArea">
          <option value="">No Area</option>
          <?php foreach ($areas as $a): ?>
          <option value="<?= $a['id'] ?>"><?= esc($a['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <?php endif; ?>
    </div>
    <div class="modal-footer">
      <button class="btn btn-outline" onclick="closeModal('editTableModal')">Cancel</button>
      <button class="btn btn-primary" onclick="saveEdit()"><i class="fa fa-save"></i> Save</button>
    </div>
  </div>
</div>

<!-- ── QR Code Modal ───────────────────────────────── -->
<div class="modal-overlay" id="qrModal">
  <div class="modal" style="max-width:380px">
    <div class="modal-header">
      <span class="modal-title" id="qrModalTitle"><i class="fa fa-qrcode" style="color:var(--primary)"></i> Table QR Code</span>
      <button class="modal-close" onclick="closeModal('qrModal')"><i class="fa fa-times"></i></button>
    </div>
    <div class="modal-body" style="display:flex;flex-direction:column;align-items:center;gap:1rem">
      <div id="qrBox" style="background:#fff;padding:16px;border-radius:16px;border:1.5px solid #E2E8F0;display:inline-block"></div>
      <div id="qrLink" style="font-size:.72rem;color:#64748B;text-align:center;word-break:break-all;max-width:300px;background:#F8FAFC;padding:.5rem .75rem;border-radius:8px"></div>
      <div style="font-size:.75rem;color:#94A3B8;text-align:center">
        <i class="fa fa-mobile-screen"></i> Customer scans this to view your menu
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-outline" onclick="copyQrLink()"><i class="fa fa-copy"></i> Copy Link</button>
      <button class="btn btn-outline" onclick="printQr()"><i class="fa fa-print"></i> Print</button>
      <button class="btn btn-primary" onclick="regenerateQr()"><i class="fa fa-refresh"></i> New QR</button>
    </div>
  </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
const CN = '<?= csrf_token() ?>', CT = '<?= csrf_hash() ?>';
const BASE = '<?= base_url() ?>';
let currentQrTableId = null;

function postApi(url, data={}) {
  return fetch(url, {
    method:'POST',
    headers:{'Content-Type':'application/x-www-form-urlencoded','X-Requested-With':'XMLHttpRequest'},
    body: new URLSearchParams({[CN]:CT,...data})
  }).then(r=>r.json());
}

function addTable() {
  postApi(BASE+'admin/tables/store', {
    table_number: document.getElementById('tNum').value,
    capacity:     document.getElementById('tCap').value,
    shape:        document.getElementById('tShape').value,
    sort_order:   document.getElementById('tSort').value,
    area_id:      document.getElementById('tArea')?.value || '',
  }).then(d => {
    if (d.success) { closeModal('addTableModal'); location.reload(); }
    else showToast(d.message||'Failed to add table','error');
  });
}

function editTable(t) {
  document.getElementById('etId').value  = t.id;
  document.getElementById('etNum').value = t.table_number;
  document.getElementById('etCap').value = t.capacity;
  if (document.getElementById('etArea')) document.getElementById('etArea').value = t.area_id||'';
  openModal('editTableModal');
}

function saveEdit() {
  const id = document.getElementById('etId').value;
  postApi(BASE+'admin/tables/update/'+id, {
    table_number: document.getElementById('etNum').value,
    capacity:     document.getElementById('etCap').value,
    area_id:      document.getElementById('etArea')?.value || '',
  }).then(d => {
    if (d.success) { closeModal('editTableModal'); location.reload(); }
    else showToast('Failed','error');
  });
}

function toggleTable(id, active) {
  if (!confirm(active ? 'Deactivate this table?' : 'Activate this table?')) return;
  postApi(BASE+'admin/tables/toggle/'+id).then(d => { if(d.success) location.reload(); });
}

// ── QR Code ─────────────────────────────────────────────
function showQr(tableId, tableNum, token) {
  currentQrTableId = tableId;
  document.getElementById('qrModalTitle').innerHTML = '<i class="fa fa-qrcode" style="color:var(--primary)"></i> Table ' + tableNum + ' — QR Code';
  document.getElementById('qrBox').innerHTML = '';
  document.getElementById('qrLink').textContent = '';
  openModal('qrModal');

  if (!token) {
    // Generate one automatically
    fetch(BASE+'admin/tables/generate-qr/'+tableId, {headers:{'X-Requested-With':'XMLHttpRequest'}})
      .then(r=>r.json()).then(d => { if(d.success) renderQr(d.url); });
  } else {
    renderQr(BASE+'menu/table/'+token);
  }
}

function renderQr(url) {
  document.getElementById('qrBox').innerHTML = '';
  document.getElementById('qrLink').textContent = url;
  new QRCode(document.getElementById('qrBox'), {
    text: url, width:220, height:220,
    colorDark:'#0F172A', colorLight:'#ffffff',
    correctLevel: QRCode.CorrectLevel.M,
  });
}

function regenerateQr() {
  if (!currentQrTableId) return;
  fetch(BASE+'admin/tables/generate-qr/'+currentQrTableId, {headers:{'X-Requested-With':'XMLHttpRequest'}})
    .then(r=>r.json()).then(d => {
      if (d.success) { renderQr(d.url); showToast('New QR generated','success'); }
    });
}

function copyQrLink() {
  const url = document.getElementById('qrLink').textContent;
  navigator.clipboard?.writeText(url).then(() => showToast('Link copied!','success')).catch(() => {
    const el = document.createElement('textarea');
    el.value = url; document.body.appendChild(el); el.select();
    document.execCommand('copy'); document.body.removeChild(el);
    showToast('Copied!','success');
  });
}

function printQr() {
  const qrBox = document.getElementById('qrBox');
  const img   = qrBox.querySelector('img');
  if (!img) return;
  const w = window.open('','_blank','width=400,height=500');
  w.document.write(`<html><head><title>Table QR</title><style>body{font-family:sans-serif;text-align:center;padding:1rem}img{width:240px;height:240px}h2{margin:.5rem 0 0;font-size:1.1rem}p{color:#64748B;font-size:.75rem}</style></head>
  <body><img src="${img.src}"><h2>${document.getElementById('qrModalTitle').textContent.replace('QR Code','').trim()}</h2><p>Scan to view menu</p><script>window.onload=()=>window.print()<\/script></body></html>`);
  w.document.close();
}

function showToast(msg, type='info') {
  const clr = {success:'#22C55E',error:'#EF4444',warning:'#F59E0B',info:'#3B82F6'};
  const t = document.createElement('div');
  t.style.cssText = `position:fixed;bottom:1.5rem;left:50%;transform:translateX(-50%);background:${clr[type]||clr.info};color:#fff;padding:.5rem 1.2rem;border-radius:24px;font-weight:700;font-size:.82rem;z-index:9999;box-shadow:0 4px 16px rgba(0,0,0,.2)`;
  t.textContent = msg;
  document.body.appendChild(t);
  setTimeout(()=>{t.style.transition='opacity .3s';t.style.opacity='0';setTimeout(()=>t.remove(),300);},2500);
}
</script>
<?php $this->endSection(); ?>
