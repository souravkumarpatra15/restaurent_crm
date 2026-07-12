<?php $this->extend('layouts/main');
$this->section('content'); ?>
<?php
$themeColor = $restaurant['theme_color'] ?? '#FF6B35';
$restName   = $restaurant['name']   ?? session('restaurant_name') ?? 'Restaurant';
$branchName = $branch['name']       ?? 'Main Branch';
$address    = trim(($restaurant['address'] ?? '') . ($restaurant['city'] ? ', ' . $restaurant['city'] : ''));
$phone      = $restaurant['phone']  ?? '';
$gst        = $restaurant['gst_number'] ?? '';
// Flatten all tables for JS
$allTables  = [];
foreach ($areas as $area) {
  foreach (($area['tables'] ?? []) as $t) {
    $t['area_name'] = $area['name'];
    $allTables[] = $t;
  }
}
$totalTables = count($allTables);
$withQr      = count(array_filter($allTables, fn($t) => !empty($t['qr_token'])));
?>

<style>
  /* ── SCREEN UI ────────────────────────────────────────── */
  .bqr-root {
    padding: 0 1rem 3rem;
  }

  /* Toolbar */
  .bqr-toolbar {
    position: sticky;
    top: 0;
    z-index: 50;
    background: #fff;
    border-bottom: 1px solid #E2E8F0;
    padding: .875rem 1rem;
    display: flex;
    align-items: center;
    gap: .75rem;
    flex-wrap: wrap;
    margin: 0 -1rem 1.5rem;
    box-shadow: 0 1px 8px rgba(0, 0, 0, .06);
  }

  .bqr-title {
    font-weight: 900;
    font-size: 1rem;
    color: #0F172A;
    flex: 1;
    min-width: 160px;
  }

  .bqr-meta {
    font-size: .75rem;
    color: #94A3B8;
  }

  .bqr-actions {
    display: flex;
    gap: .5rem;
    margin-left: auto;
  }

  /* Selection chips */
  .bqr-sel-row {
    display: flex;
    gap: .5rem;
    flex-wrap: wrap;
    margin-bottom: 1.25rem;
    align-items: center;
  }

  .bqr-sel-lbl {
    font-size: .75rem;
    font-weight: 700;
    color: #64748B;
  }

  .sel-chip {
    padding: .35rem .875rem;
    border-radius: 20px;
    font-size: .75rem;
    font-weight: 700;
    border: 1.5px solid #E2E8F0;
    background: #fff;
    color: #64748B;
    cursor: pointer;
    transition: all .15s;
  }

  .sel-chip.active {
    background: var(--primary);
    color: #fff;
    border-color: var(--primary);
  }

  .sel-chip:hover:not(.active) {
    border-color: #94A3B8;
  }

  /* Table selector grid */
  .bqr-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(96px, 1fr));
    gap: .5rem;
    margin-bottom: 1.5rem;
  }

  .tsel {
    border: 2px solid #E2E8F0;
    border-radius: 12px;
    padding: .625rem .5rem;
    text-align: center;
    cursor: pointer;
    transition: all .2s;
    position: relative;
    background: #fff;
    user-select: none;
  }

  .tsel.selected {
    border-color: var(--primary);
    background: #FFF8F5;
  }

  .tsel.selected::after {
    content: '✓';
    position: absolute;
    top: 4px;
    right: 6px;
    font-size: .6rem;
    font-weight: 900;
    color: var(--primary);
  }

  .tsel-num {
    font-weight: 900;
    font-size: 1rem;
    color: #0F172A;
  }

  .tsel-area {
    font-size: .58rem;
    color: #94A3B8;
    margin-top: .15rem;
  }

  .tsel-qr {
    font-size: .58rem;
    margin-top: .2rem;
  }

  .tsel.no-qr {
    opacity: .55;
  }

  .tsel.no-qr .tsel-qr {
    color: #EF4444;
  }

  .tsel:not(.no-qr) .tsel-qr {
    color: #22C55E;
  }

  /* Count bar */
  .count-bar {
    background: #F8FAFC;
    border: 1px solid #E2E8F0;
    border-radius: 10px;
    padding: .625rem 1rem;
    margin-bottom: 1.25rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
  }

  .count-bar span {
    font-size: .82rem;
    font-weight: 700;
    color: #334155;
  }

  .count-bar b {
    color: var(--primary);
  }

  /* No QR warning */
  .no-qr-warn {
    background: #FEF3C7;
    border: 1px solid #FCD34D;
    border-radius: 10px;
    padding: .625rem 1rem;
    font-size: .8rem;
    color: #92400E;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: .5rem;
  }

  /* ── PRINT PREVIEW GRID ───────────────────────────────── */
  .preview-section {
    margin-top: 2rem;
  }

  .preview-title {
    font-size: .75rem;
    font-weight: 900;
    color: #94A3B8;
    text-transform: uppercase;
    letter-spacing: .08em;
    margin-bottom: 1rem;
  }

  #previewGrid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 1rem;
  }

  /* ── QR CARD (screen + print) ─────────────────────────── */
  .qr-card {
    border: 2px solid #E2E8F0;
    border-radius: 16px;
    overflow: hidden;
    background: #fff;
    box-shadow: 0 2px 12px rgba(0, 0, 0, .06);
    break-inside: avoid;
    page-break-inside: avoid;
  }

  .qr-card-header {
    background: <?= esc($themeColor) ?>;
    padding: .75rem 1rem;
    text-align: center;
  }

  .qc-rest {
    color: #fff;
    font-weight: 900;
    font-size: .9rem;
    letter-spacing: -.01em;
    line-height: 1.2;
  }

  .qc-branch {
    color: rgba(255, 255, 255, .75);
    font-size: .65rem;
    margin-top: .15rem;
  }

  .qr-card-body {
    padding: 1rem;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: .75rem;
  }

  .qr-box {
    background: #fff;
    padding: 10px;
    border: 1.5px solid #F1F5F9;
    border-radius: 10px;
    display: inline-block;
  }

  .qr-table-num {
    font-size: 1.5rem;
    font-weight: 900;
    color: #0F172A;
    letter-spacing: -.02em;
  }

  .qr-table-label {
    font-size: .65rem;
    font-weight: 700;
    color: #94A3B8;
    text-transform: uppercase;
    letter-spacing: .08em;
    margin-top: -.25rem;
  }

  .qr-cta {
    font-size: .62rem;
    color: #64748B;
    text-align: center;
    line-height: 1.5;
  }

  .qr-card-footer {
    border-top: 1px solid #F1F5F9;
    padding: .5rem 1rem;
    background: #F8FAFC;
    text-align: center;
  }

  .qc-addr {
    font-size: .6rem;
    color: #94A3B8;
    line-height: 1.5;
  }

  .qc-phone {
    font-size: .6rem;
    color: #64748B;
    font-weight: 600;
    margin-top: .15rem;
  }

  /* ── PRINT STYLES ──────────────────────────────────────── */
  @media print {

    /* Hide everything except print cards */
    body * {
      visibility: hidden !important;
    }

    #printArea,
    #printArea * {
      visibility: visible !important;
    }

    #printArea {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      visibility: visible !important;
      padding: 8mm;
    }

    #printGrid {
      display: flex !important;
      flex-wrap: wrap !important;
      justify-content: flex-start !important;
      align-items: flex-start !important;
      gap: 0 !important;
    }

    #printGrid .qr-card {
      width: 31.8% !important;
      margin: 0.8% !important;
      box-sizing: border-box !important;
      page-break-inside: avoid !important;
      break-inside: avoid !important;
    }

    .qr-card {
      border: 1.5px solid #CBD5E1 !important;
      border-radius: 10px !important;
      box-shadow: none !important;
      break-inside: avoid !important;
      page-break-inside: avoid !important;
      background: #fff !important;
      overflow: hidden !important;
    }

    .qr-card-header {
      background: <?= esc($themeColor) ?> !important;
      -webkit-print-color-adjust: exact !important;
      print-color-adjust: exact !important;
      padding: .5rem .75rem !important;
    }

    .qr-box {
      padding: 8px !important;
    }

    .qr-table-num {
      font-size: 1.25rem !important;
    }

    .qr-card-body {
      padding: .75rem .875rem !important;
      gap: .5rem !important;
    }

    .qr-card-footer {
      padding: .375rem .75rem !important;
    }

    @page {
      size: A4;
      margin: 8mm;
    }

    @media print {

      @page {
        size: A4 portrait;
        margin: 8mm;
      }

      #printArea {
        position: static !important;
        display: block !important;
        padding: 0 !important;
      }

      #printGrid {
        display: flex !important;
        flex-wrap: wrap !important;
        align-items: flex-start !important;
      }

      #printGrid .qr-card {

        width: 60mm !important;
        height: 87mm !important;

        margin: 2mm;

        page-break-inside: avoid !important;
        break-inside: avoid !important;

        box-shadow: none !important;
        border: 1px solid #bbb !important;
      }

    }
  }
</style>

<div class="bqr-root">

  <!-- Toolbar -->
  <div class="bqr-toolbar">
    <div>
      <div class="bqr-title"><i class="fa fa-qrcode" style="color:var(--primary)"></i> Bulk Print QR Codes</div>
      <div class="bqr-meta"><?= esc($restName) ?> · <?= esc($branchName) ?> · <?= $totalTables ?> tables (<?= $withQr ?> with QR)</div>
    </div>
    <div class="bqr-actions">
      <button onclick="selectAll()" class="btn btn-outline btn-sm"><i class="fa fa-check-double"></i> Select All</button>
      <button onclick="selectNone()" class="btn btn-outline btn-sm"><i class="fa fa-square"></i> None</button>
      <button onclick="generateMissing()" class="btn btn-outline btn-sm" id="genBtn"><i class="fa fa-wand-magic-sparkles"></i> Generate Missing QR</button>
      <button onclick="printSelected()" class="btn btn-primary" id="printBtn" disabled>
        <i class="fa fa-print"></i> Print Selected (<span id="selCount">0</span>)
      </button>
    </div>
  </div>

  <!-- Table selector -->
  <?php if ($withQr < $totalTables): ?>
    <div class="no-qr-warn">
      <i class="fa fa-triangle-exclamation"></i>
      <span><?= $totalTables - $withQr ?> table(s) don't have a QR code yet. Click <strong>Generate Missing QR</strong> to create them all at once.</span>
    </div>
  <?php endif; ?>

  <div class="bqr-sel-row">
    <span class="bqr-sel-lbl">Filter by area:</span>
    <button class="sel-chip active" onclick="filterArea('all',this)">All Areas</button>
    <?php foreach ($areas as $area): if (empty($area['tables'])) continue; ?>
      <button class="sel-chip" onclick="filterArea('<?= $area['id'] ?>',this)"><?= esc($area['name']) ?></button>
    <?php endforeach; ?>
  </div>

  <div class="count-bar">
    <span><b id="selCount2">0</b> selected</span>
    <span style="color:#E2E8F0">|</span>
    <span><b><?= $totalTables ?></b> total tables</span>
    <span style="color:#E2E8F0">|</span>
    <span><b style="color:#22C55E"><?= $withQr ?></b> with QR &nbsp; <b style="color:#EF4444"><?= $totalTables - $withQr ?></b> without</span>
  </div>

  <!-- Table tiles -->
  <div class="bqr-grid" id="tileGrid">
    <?php foreach ($allTables as $t): ?>
      <div class="tsel <?= empty($t['qr_token']) ? 'no-qr' : '' ?>"
        id="tile_<?= $t['id'] ?>"
        data-id="<?= $t['id'] ?>"
        data-area="<?= $t['area_id'] ?? 0 ?>"
        data-token="<?= esc($t['qr_token'] ?? '') ?>"
        data-num="<?= esc($t['table_number']) ?>"
        onclick="toggleTile(this)">
        <div class="tsel-num"><?= esc($t['table_number']) ?></div>
        <div class="tsel-area"><?= esc($t['area_name'] ?? '') ?></div>
        <div class="tsel-qr">
          <?= empty($t['qr_token'])
            ? '<i class="fa fa-xmark"></i> No QR'
            : '<i class="fa fa-qrcode"></i> Ready' ?>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <!-- Preview section -->
  <div class="preview-section">
    <div class="preview-title"><i class="fa fa-eye"></i> Print Preview — <span id="prevLabel">Select tables above to preview</span></div>
    <div id="previewGrid"></div>
  </div>
</div>

<!-- Hidden print area -->
<div id="printArea" style="display:none;position:absolute;left:-9999px">
  <div id="printGrid"></div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
  const BASE = '<?= base_url() ?>';
  const CN = '<?= csrf_token() ?>';
  const CT = '<?= csrf_hash() ?>';
  const REST_NAME = '<?= esc(addslashes($restName)) ?>';
  const BRANCH = '<?= esc(addslashes($branchName)) ?>';
  const ADDRESS = '<?= esc(addslashes($address)) ?>';
  const PHONE = '<?= esc(addslashes($phone)) ?>';
  const THEME = '<?= esc($themeColor) ?>';

  let selected = new Set();
  let qrCache = {}; // id → {token, imgDataUrl}

  // ── Tile toggle ──────────────────────────────────────────
  function toggleTile(el) {
    const id = el.dataset.id;
    if (selected.has(id)) {
      selected.delete(id);
      el.classList.remove('selected');
    } else {
      selected.add(id);
      el.classList.add('selected');
    }
    updateUI();
  }

  function selectAll() {
    document.querySelectorAll('.tsel:not(.no-qr)').forEach(el => {
      selected.add(el.dataset.id);
      el.classList.add('selected');
    });
    updateUI();
  }

  function selectNone() {
    selected.clear();
    document.querySelectorAll('.tsel').forEach(el => el.classList.remove('selected'));
    updateUI();
  }

  function filterArea(areaId, btn) {
    document.querySelectorAll('.sel-chip').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    document.querySelectorAll('.tsel').forEach(el => {
      el.style.display = (areaId === 'all' || el.dataset.area === areaId) ? '' : 'none';
    });
  }

  // ── Update counts + preview ──────────────────────────────
  function updateUI() {
    const cnt = selected.size;
    document.getElementById('selCount').textContent = cnt;
    document.getElementById('selCount2').textContent = cnt;
    document.getElementById('printBtn').disabled = cnt === 0;
    document.getElementById('prevLabel').textContent =
      cnt ? cnt + ' table' + (cnt > 1 ? 's' : '') + ' selected' : 'Select tables above to preview';
    renderPreview();
  }

  // ── Build one QR card HTML ───────────────────────────────
  function buildCard(tableNum, qrImgSrc, containerId) {
    return `
    <div class="qr-card">
      <div class="qr-card-header">
        <div class="qc-rest">${REST_NAME}</div>
        <div class="qc-branch">${BRANCH}</div>
      </div>
      <div class="qr-card-body">
        <div class="qr-box" id="${containerId}"></div>
        <div class="qr-table-num">${tableNum}</div>
        <div class="qr-table-label">Table</div>
        <div class="qr-cta">📱 Scan to view menu<br>& place your order</div>
      </div>
      ${ADDRESS || PHONE ? `<div class="qr-card-footer">
        ${ADDRESS ? `<div class="qc-addr">${ADDRESS}</div>` : ''}
        ${PHONE   ? `<div class="qc-phone">📞 ${PHONE}</div>` : ''}
      </div>` : ''}
    </div>`;
  }

  // ── Generate QR code into a container element ────────────
  function genQr(containerId, url) {
    const el = document.getElementById(containerId);
    if (!el) return;
    el.innerHTML = '';
    new QRCode(el, {
      text: url,
      width: 160,
      height: 160,
      colorDark: '#0F172A',
      colorLight: '#ffffff',
      correctLevel: QRCode.CorrectLevel.M,
    });
  }

  // ── Preview ──────────────────────────────────────────────
  function renderPreview() {
    const grid = document.getElementById('previewGrid');
    if (selected.size === 0) {
      grid.innerHTML = '';
      return;
    }

    grid.innerHTML = '';
    let i = 0;
    selected.forEach(id => {
      const tile = document.getElementById('tile_' + id);
      if (!tile || tile.dataset.token === '') return;
      const cid = 'prev_qr_' + id;
      grid.innerHTML += buildCard(tile.dataset.num, null, cid);
      i++;
    });

    // Generate QR codes after DOM update
    setTimeout(() => {
      selected.forEach(id => {
        const tile = document.getElementById('tile_' + id);
        if (!tile || !tile.dataset.token) return;
        genQr('prev_qr_' + id, BASE + 'menu/table/' + tile.dataset.token);
      });
    }, 50);
  }

  // ── Print ────────────────────────────────────────────────
  function printSelected() {
    if (selected.size === 0) return;

    const printArea = document.getElementById('printArea');
    const printGrid = document.getElementById('printGrid');

    printGrid.innerHTML = '';

    let total = 0;

    // Build cards
    selected.forEach(id => {
      const tile = document.getElementById('tile_' + id);

      if (!tile || !tile.dataset.token) return;

      const cid = 'print_qr_' + id;

      printGrid.innerHTML += buildCard(tile.dataset.num, null, cid);

      total++;
    });

    // Show print area
    printArea.style.display = 'block';

    // Generate QR Codes
    selected.forEach(id => {

      const tile = document.getElementById('tile_' + id);

      if (!tile || !tile.dataset.token) return;

      genQr('print_qr_' + id, BASE + 'menu/table/' + tile.dataset.token);

    });

    // Wait until all QR images are rendered
    const wait = setInterval(() => {

      const images = printGrid.querySelectorAll('img');

      if (images.length === total) {

        clearInterval(wait);

        window.print();

        setTimeout(() => {
          printArea.style.display = 'none';
          printArea.style.position = 'absolute';
          printArea.style.left = '-9999px';
        }, 500);

      }

    }, 100);
  }
  // ── Generate missing QR tokens ───────────────────────────
  async function generateMissing() {
    const noQr = document.querySelectorAll('.tsel.no-qr');
    if (!noQr.length) {
      showToast('All tables already have QR codes!', 'success');
      return;
    }

    const btn = document.getElementById('genBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Generating...';

    let done = 0;
    for (const tile of noQr) {
      const id = tile.dataset.id;
      try {
        const res = await fetch(BASE + 'admin/tables/generate-qr/' + id, {
          headers: {
            'X-Requested-With': 'XMLHttpRequest'
          }
        });
        const data = await res.json();
        if (data.success) {
          tile.dataset.token = data.token;
          tile.classList.remove('no-qr');
          tile.querySelector('.tsel-qr').innerHTML = '<i class="fa fa-qrcode"></i> Ready';
          done++;
        }
      } catch (e) {}
    }

    btn.disabled = false;
    btn.innerHTML = '<i class="fa fa-wand-magic-sparkles"></i> Generate Missing QR';
    showToast(done + ' QR code' + (done > 1 ? 's' : '') + ' generated!', 'success');
  }

  // ── Toast ────────────────────────────────────────────────
  function showToast(msg, type = 'info') {
    const clr = {
      success: '#22C55E',
      error: '#EF4444',
      info: '#3B82F6'
    };
    const t = document.createElement('div');
    t.style.cssText = `position:fixed;bottom:2rem;right:1.5rem;background:${clr[type]||clr.info};color:#fff;padding:.6rem 1.25rem;border-radius:12px;font-weight:700;font-size:.82rem;z-index:9999;box-shadow:0 4px 20px rgba(0,0,0,.2)`;
    t.textContent = msg;
    document.body.appendChild(t);
    setTimeout(() => {
      t.style.transition = 'opacity .3s';
      t.style.opacity = '0';
      setTimeout(() => t.remove(), 300);
    }, 2500);
  }
</script>

<?php $this->endSection(); ?>