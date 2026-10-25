<?php $this->extend('layouts/main'); $this->section('content'); ?>
<div style="padding:0 1rem;max-width:980px">

  <!-- Page header -->
  <div class="card" style="margin-bottom:1rem">
    <div class="card-header">
      <span class="card-title"><i class="fa fa-file-import" style="color:var(--primary)"></i> Import Data</span>
      <span style="font-size:.72rem;color:var(--text-muted)">CSV or Excel (.xlsx) · header row required · max 8 MB</span>
    </div>
    <div class="card-body">
      <!-- type tabs -->
      <div style="display:flex;gap:.5rem;margin-bottom:1rem" id="typeTabs">
        <button class="imp-tab on" data-type="menu" style="flex:1;padding:.7rem .5rem;border:1.5px solid var(--primary);border-radius:10px;background:var(--primary-light);cursor:pointer;font-weight:800;font-size:.85rem;display:flex;align-items:center;justify-content:center;gap:.4rem">
          <i class="fa fa-utensils"></i> Menu Items
        </button>
        <button class="imp-tab" data-type="customer" style="flex:1;padding:.7rem .5rem;border:1.5px solid var(--border);border-radius:10px;background:#fff;cursor:pointer;font-weight:800;font-size:.85rem;display:flex;align-items:center;justify-content:center;gap:.4rem">
          <i class="fa fa-person"></i> Customers
        </button>
      </div>

      <!-- upload box -->
      <div id="uploadBox" style="border:2px dashed var(--border);border-radius:12px;padding:1.75rem 1rem;text-align:center;background:var(--bg-soft,#F8FAFC);cursor:pointer" onclick="document.getElementById('fileInput').click();return false;">
        <i class="fa fa-cloud-arrow-up" style="font-size:1.8rem;color:var(--primary)"></i>
        <div style="font-weight:800;margin-top:.4rem">Choose your CSV or Excel file</div>
        <div style="font-size:.75rem;color:var(--text-muted);margin-top:.2rem">First row must be the column headers · we'll auto-detect the mapping for you</div>
        <input type="file" id="fileInput" accept=".csv,.xlsx,.xlsm,.xls,.txt" style="display:none">
      </div>
      <div id="fileStatus" style="display:none;margin-top:.6rem;font-size:.8rem;font-weight:700;color:var(--primary)"></div>
    </div>
  </div>

  <!-- mapping card -->
  <div class="card" id="mappingCard" style="margin-bottom:1rem;display:none">
    <div class="card-header">
      <span class="card-title"><i class="fa fa-diagram-project" style="color:#6366F1"></i> Map your columns</span>
      <span id="rowInfo" style="font-size:.72rem;color:var(--text-muted)"></span>
    </div>
    <div class="card-body">
      <div style="font-size:.78rem;color:var(--text-muted);margin-bottom:.75rem">
        For each database field pick the <strong>file column</strong> that holds it. If the file has no such column choose
        <strong>“Custom value”</strong> — the value you type (or pick from the dropdown) is applied to <strong>every row</strong>.
        Required fields open in custom mode automatically when the file doesn't contain them.
      </div>

      <!-- data preview -->
      <div class="table-wrap" style="max-height:190px;overflow:auto;border:1px solid var(--border);border-radius:10px;margin-bottom:1rem">
        <table class="table" id="previewTable" style="font-size:.72rem"></table>
      </div>

      <!-- mapping rows -->
      <div id="mapRows"></div>

      <div style="display:flex;gap:.5rem;align-items:center;margin-top:1rem">
        <button class="btn btn-primary" onclick="runImport()"><i class="fa fa-play"></i> Import <span id="rowCt" style="opacity:.75"></span></button>
        <button class="btn btn-outline" onclick="resetImport()"><i class="fa fa-rotate-right"></i> Start over</button>
        <span id="mapHint" style="font-size:.75rem;color:var(--text-muted)"></span>
      </div>
    </div>
  </div>

  <!-- results card -->
  <div class="card" id="resultsCard" style="margin-bottom:1rem;display:none">
    <div class="card-header"><span class="card-title"><i class="fa fa-circle-check" style="color:var(--success)"></i> Import finished</span></div>
    <div class="card-body" id="resultsBody"></div>
  </div>
</div>

<script>
const BASE      = '<?= base_url() ?>';
const CSRF      = '<?= csrf_token() . '=' . urlencode(csrf_hash()) ?>';
const CSRF_NAME = '<?= csrf_token() ?>';

let TYPE   = 'menu';
let FILE   = null;      // chosen File object
let PARSED = null;      // {headers, sample, fields, total}

/* ── type tabs ─────────────────────────────────────────── */
document.querySelectorAll('.imp-tab').forEach(btn => btn.addEventListener('click', () => {
  document.querySelectorAll('.imp-tab').forEach(b => { b.classList.remove('on'); b.style.borderColor = 'var(--border)'; b.style.background = '#fff'; });
  btn.classList.add('on'); btn.style.borderColor = 'var(--primary)'; btn.style.background = 'var(--primary-light)';
  TYPE = btn.dataset.type;
  PARSED = null; FILE = null;
  document.getElementById('fileInput').value = '';
  document.getElementById('mappingCard').style.display = 'none';
  document.getElementById('resultsCard').style.display = 'none';
  document.getElementById('fileStatus').style.display = 'none';
}));

/* ── pick file → preview ───────────────────────────────── */
document.getElementById('fileInput').addEventListener('change', e => {
  const f = e.target.files[0];
  if (!f) return;
  FILE = f;
  document.getElementById('fileStatus').style.display = '';
  document.getElementById('fileStatus').innerHTML = '<i class="fa fa-spinner fa-spin"></i> Reading ' + escHtml(f.name) + '…';
  const fd = new FormData();
  fd.append(CSRF_NAME, CSRF.split('=')[1]);
  fd.append('type', TYPE);
  fd.append('file', f);
  fetch(BASE + 'admin/import/preview', { method: 'POST', body: fd })
    .then(r => r.json())
    .then(d => {
      if (!d.success) {
        document.getElementById('fileStatus').innerHTML = '<span style="color:var(--danger)"><i class="fa fa-circle-exclamation"></i> ' + escHtml(d.message) + '</span>';
        return;
      }
      PARSED = d;
      document.getElementById('fileStatus').innerHTML = '<i class="fa fa-circle-check" style="color:var(--success)"></i> ' + escHtml(d.file) + ' — ' + d.total + ' rows found';
      buildMapping();
    })
    .catch(() => {
      document.getElementById('fileStatus').innerHTML = '<span style="color:var(--danger)">Upload failed — check the file and try again</span>';
    });
});

/* ── mapping UI ────────────────────────────────────────── */
function headerMatch(aliases, headers) {
  for (let i = 0; i < headers.length; i++) {
    const h = headers[i].toLowerCase().replace(/[^a-z0-9%]/g, '');
    for (const a of (aliases || [])) {
      const al = a.toLowerCase().replace(/[^a-z0-9%]/g, '');
      if (h === al || h === al.replace(/_/g, '')) return i;
    }
  }
  // weaker: contains
  for (let i = 0; i < headers.length; i++) {
    const h = headers[i].toLowerCase().replace(/[^a-z0-9%]/g, '');
    for (const a of (aliases || [])) {
      const al = a.toLowerCase().replace(/[^a-z0-9%]/g, '');
      if (h.includes(al) && al.length > 2) return i;
    }
  }
  return -1;
}

function buildMapping() {
  const card = document.getElementById('mappingCard');
  card.style.display = '';
  document.getElementById('resultsCard').style.display = 'none';
  document.getElementById('rowInfo').textContent = '“' + escHtml(PARSED.file) + '” · ' + PARSED.total + ' data rows';

  // preview table (headers + first rows)
  const pt = document.getElementById('previewTable');
  let ph = '<thead><tr>' + PARSED.headers.map((h, i) => `<th style="white-space:nowrap">C${i + 1}: ${escHtml(h || '—')}</th>`).join('') + '</tr></thead>';
  let pb = PARSED.sample.map(r => '<tr>' + PARSED.headers.map((h, i) => `<td style="white-space:nowrap">${escHtml(r[i] ?? '')}</td>`).join('') + '</tr>').join('');
  pt.innerHTML = ph + '<tbody>' + pb + '</tbody>';

  // map rows
  const wrap = document.getElementById('mapRows');
  wrap.innerHTML = '';
  let assigned = {}; // column idx -> field key
  PARSED.fields.forEach((f, fi) => {
    const hit = headerMatch(f.aliases, PARSED.headers);
    let mode = hit >= 0 ? 'column' : 'custom';
    // a column can only serve one field
    if (hit >= 0 && assigned[hit]) mode = 'custom';
    if (hit >= 0) assigned[hit] = f.key;

    const row = document.createElement('div');
    row.className = 'map-row';
    row.dataset.field = f.key;
    row.style.cssText = 'display:flex;align-items:center;gap:.6rem;padding:.55rem .75rem;border:1.5px solid ' + (f.required ? 'var(--primary)' : 'var(--border)') + ';border-radius:10px;margin-bottom:.45rem;flex-wrap:wrap;background:' + (f.required ? 'var(--primary-light,#FFF7F2)' : '#fff');
    row.innerHTML = `
      <div style="flex:0 0 210px">
        <div style="font-weight:800;font-size:.82rem">${escHtml(f.label)}${f.required ? ' <span style="color:var(--danger)">*</span>' : ''}</div>
        <div style="font-size:.66rem;color:var(--text-muted)">db: ${escHtml(f.key)}</div>
      </div>
      <select class="form-control map-col" style="flex:0 0 240px;font-size:.8rem" onchange="onMapCol(this,'${f.key}')"></select>
      <div class="map-custom" style="flex:1;display:none;min-width:200px"></div>`;
    wrap.appendChild(row);

    const sel = row.querySelector('.map-col');
    sel.innerHTML = '<option value="none">— not in file —</option>' +
      PARSED.headers.map((h, i) => `<option value="${i}" ${mode === 'column' && hit === i ? 'selected' : ''}>C${i + 1}: ${escHtml(h || '—')}</option>`).join('') +
      '<option value="custom">✏️ Custom value (every row)</option>';

    renderCustom(f, row, hit >= 0 ? '' : (f.widget === 'number' ? (f.default ?? '') : (f.options?.[0]?.id ?? '')));
  });

  document.getElementById('rowCt').textContent = '(' + PARSED.total + ' rows)';
  document.getElementById('mapHint').textContent = PARSED.total + ' rows will be imported with this mapping';
}

/* custom-value control: number/text input, dropdown, or date-ish input */
function renderCustom(field, row, prefill) {
  const box = row.querySelector('.map-custom');
  box.innerHTML = '';
  let ctl;
  if (field.widget === 'entity' || field.widget === 'enum') {
    // dropdown of database values (auto-drop)
    ctl = document.createElement('select');
    ctl.className = 'form-control';
    ctl.style.cssText = 'max-width:230px;font-size:.8rem';
    (field.options || []).forEach(o => {
      const op = document.createElement('option');
      op.value = o.id; op.textContent = o.label;
      if (String(prefill) === String(o.id)) op.selected = true;
      ctl.appendChild(op);
    });
  } else {
    ctl = document.createElement('input');
    ctl.type = field.widget === 'number' ? 'number' : (field.widget === 'date' ? 'date' : 'text');
    ctl.className = 'form-control';
    ctl.style.cssText = 'max-width:230px;font-size:.8rem';
    ctl.step = field.widget === 'number' ? '0.01' : undefined;
    ctl.placeholder = prefillDesc(field);
    ctl.value = prefill || '';
  }
  ctl.dataset.custom = field.key;
  box.appendChild(ctl);
  const hint = document.createElement('span');
  hint.style.cssText = 'font-size:.66rem;color:var(--text-muted);margin-left:.4rem';
  hint.textContent = field.widget === 'entity' || field.widget === 'enum' ? 'pick from database — applied to every row' : 'applied to every row';
  box.appendChild(hint);
}
function prefillDesc(f) {
  if (f.required) return 'required — e.g. ' + (f.widget === 'number' ? '99' : 'value');
  return f.widget === 'number' ? 'e.g. ' + (f.default || '0') : 'value for every row';
}

function onMapCol(sel, key) {
  const row = sel.closest('.map-row');
  const f = PARSED.fields.find(x => x.key === key);
  if (sel.value === 'custom') {
    // open the custom value control (auto-drop for db-backed fields)
    renderCustom(f, row, f.widget === 'number' ? (f.default ?? '') : (f.options?.[0]?.id ?? ''));
    row.querySelector('.map-col').blur();
  } else {
    row.querySelector('.map-custom').innerHTML = '';
  }
}

function currentMapping() {
  const map = {};
  document.querySelectorAll('.map-row').forEach(row => {
    const key = row.dataset.field;
    const sel = row.querySelector('.map-col');
    if (sel.value === 'custom') {
      map[key] = { mode: 'custom', value: row.querySelector('[data-custom]')?.value ?? '' };
    } else {
      map[key] = { mode: 'column', col: parseInt(sel.value, 10) };
    }
  });
  return map;
}

/* ── run ───────────────────────────────────────────────── */
function runImport() {
  const map = currentMapping();
  // quick required check client-side too
  for (const f of PARSED.fields) {
    const m = map[f.key];
    if (f.required && m.mode === 'custom' && String(m.value ?? '').trim() === '') {
      alert('"' + f.label + '" is required — enter a value (it will be used for every row)');
      return;
    }
  }
  const btn = document.querySelector('#resultsCard') ? null : null;
  document.getElementById('mapHint').innerHTML = '<i class="fa fa-spinner fa-spin"></i> importing…';
  postJson(BASE + 'admin/import/run', { type: TYPE, mapping: JSON.stringify(map) })
    .then(d => {
      document.getElementById('mapHint').textContent = '';
      if (!d.success) { alert(d.message); document.getElementById('mapHint').textContent = d.message; return; }
      showResults(d.results);
    })
    .catch(() => { document.getElementById('mapHint').textContent = 'Import request failed'; });
}

function showResults(r) {
  const card = document.getElementById('resultsCard');
  card.style.display = '';
  document.getElementById('resultsBody').innerHTML = `
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:.7rem;margin-bottom:.9rem">
      <div style="text-align:center;padding:.8rem;background:#F0FFF4;border-radius:10px"><div style="font-size:1.4rem;font-weight:800;color:var(--success)">${r.imported}</div><div style="font-size:.72rem;color:var(--text-muted)">Imported</div></div>
      <div style="text-align:center;padding:.8rem;background:#FFFBEB;border-radius:10px"><div style="font-size:1.4rem;font-weight:800;color:#D69E2E">${r.skipped}</div><div style="font-size:.72rem;color:var(--text-muted)">Skipped</div></div>
      <div style="text-align:center;padding:.8rem;background:#F1F5F9;border-radius:10px"><div style="font-size:1.4rem;font-weight:800;color:#475569">${r.errors.length}</div><div style="font-size:.72rem;color:var(--text-muted)">Notes</div></div>
    </div>
    ${r.errors.length ? `<div class="table-wrap" style="max-height:220px;overflow:auto;border:1px solid var(--border);border-radius:10px">
      <table class="table" style="font-size:.75rem"><thead><tr><th>File row</th><th>Note</th></tr></thead><tbody>
      ${r.errors.slice(0, 100).map(e => `<tr><td style="font-weight:700">${e.row}</td><td>${escHtml(e.message)}</td></tr>`).join('')}
      </tbody></table></div>` : '<div style="color:var(--success);font-weight:700;font-size:.85rem"><i class="fa fa-circle-check"></i> Every row imported cleanly.</div>'}`;
  card.scrollIntoView({ behavior: 'smooth' });
}

function resetImport() {
  PARSED = null; FILE = null;
  document.getElementById('fileInput').value = '';
  document.getElementById('mappingCard').style.display = 'none';
  document.getElementById('resultsCard').style.display = 'none';
  document.getElementById('fileStatus').style.display = 'none';
  window.scrollTo({ top: 0, behavior: 'smooth' });
}

function postJson(url, body) {
  const fd = new FormData();
  Object.entries(body).forEach(([k, v]) => fd.append(k, v));
  fd.append(CSRF_NAME, CSRF.split('=')[1]);
  return fetch(url, { method: 'POST', body: fd }).then(r => r.json());
}
function escHtml(s) { const d = document.createElement('span'); d.textContent = s ?? ''; return d.innerHTML; }
</script>
<?php $this->endSection(); ?>
