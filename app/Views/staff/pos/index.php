<?php $this->extend('layouts/pos_layout'); $this->section('content'); ?>
<?php
$totalTables = 0; $availCount = 0; $occuCount = 0; $bookedCount = 0;
foreach ($tables as $area) {
    foreach ($area['tables'] as $t) {
        $totalTables++;
        if ($t['status'] === 'available') $availCount++;
        elseif ($t['status'] === 'occupied') $occuCount++;
        elseif ($t['status'] === 'booked') $bookedCount++;
    }
}
?>

<style>
/* ── POS INDEX REDESIGN ──────────────────────────────────── */
.pi-root { display:flex; flex-direction:column; height:100vh; background:#F1F5F9; }

/* Top bar */
.pi-bar {
  background:linear-gradient(135deg,#0F172A 0%,#1E293B 100%);
  padding:0 .875rem;
  height:56px;
  display:flex; align-items:center; gap:.625rem;
  flex-shrink:0;
  box-shadow:0 2px 12px rgba(0,0,0,.25);
}
.pi-bar-back {
  width:38px; height:38px; border-radius:12px; border:none;
  background:rgba(255,255,255,.08); color:rgba(255,255,255,.75);
  display:flex; align-items:center; justify-content:center;
  font-size:.95rem; cursor:pointer; flex-shrink:0;
  transition:all .15s; text-decoration:none;
}
.pi-bar-back:hover { background:var(--primary); color:#fff; }
.pi-bar-brand { flex:1; min-width:0; }
.pi-bar-rest  { color:#fff; font-weight:900; font-size:.95rem; letter-spacing:-.01em; line-height:1.2; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.pi-bar-sub   { color:rgba(255,255,255,.4); font-size:.65rem; margin-top:.1rem; }
.pi-bar-acts  { display:flex; align-items:center; gap:.35rem; }
.pi-bar-clock { font-family:'JetBrains Mono',monospace; font-size:.7rem; color:rgba(255,255,255,.45); background:rgba(255,255,255,.06); padding:.25rem .55rem; border-radius:6px; }
.pi-bar-btn   { width:38px; height:38px; border-radius:10px; border:none; background:rgba(255,255,255,.08); color:rgba(255,255,255,.6); display:flex; align-items:center; justify-content:center; font-size:.85rem; cursor:pointer; transition:all .15s; text-decoration:none; }
.pi-bar-btn:hover { background:rgba(255,255,255,.18); color:#fff; }

/* Combined header (order types + stats) */
.pi-header {
  background:#fff;
  border-bottom:1px solid #E2E8F0;
  flex-shrink:0;
  box-shadow:0 1px 4px rgba(0,0,0,.05);
}
.pi-types {
  display:flex; gap:0;
  border-bottom:1px solid #F1F5F9;
  overflow-x:auto; scrollbar-width:none;
}
.pi-types::-webkit-scrollbar { display:none; }
.pi-type-btn {
  flex:1; min-width:90px; padding:.625rem .5rem .5rem;
  display:flex; flex-direction:column; align-items:center; gap:.2rem;
  border:none; background:transparent; cursor:pointer;
  font-family:var(--font); font-size:.72rem; font-weight:700;
  color:#64748B; border-bottom:2px solid transparent;
  transition:all .15s; text-decoration:none;
}
.pi-type-btn i { font-size:1.1rem; }
.pi-type-btn.active { color:var(--primary); border-bottom-color:var(--primary); background:#FFF8F5; }
.pi-type-btn:hover:not(.active) { color:#334155; background:#F8FAFC; }

.pi-stats {
  display:grid; grid-template-columns:repeat(4,1fr);
  padding:.45rem .625rem;
}
.pi-stat {
  display:flex; flex-direction:column; align-items:center;
  padding:.35rem .25rem;
}
.pi-stat-dot { width:8px; height:8px; border-radius:50%; margin-bottom:.2rem; }
.pi-stat-val { font-weight:900; font-size:1.05rem; line-height:1; }
.pi-stat-lbl { font-size:.58rem; color:#94A3B8; margin-top:.15rem; letter-spacing:.03em; text-transform:uppercase; }

/* Table map area */
.pi-map { flex:1; overflow-y:auto; padding:.875rem; }

/* Area label */
.pi-area-lbl {
  font-size:.65rem; font-weight:900; letter-spacing:.1em;
  text-transform:uppercase; color:#94A3B8;
  margin:0 0 .5rem; padding-left:.1rem;
}
.pi-area-lbl:not(:first-child) { margin-top:1.25rem; }

/* Table grid */
.pi-tgrid {
  display:grid;
  grid-template-columns:repeat(auto-fill, minmax(88px,1fr));
  gap:.5rem;
  margin-bottom:.25rem;
}

/* Table tile */
.pi-tile {
  border-radius:14px;
  border:1.5px solid transparent;
  padding:.625rem .5rem .55rem;
  display:flex; flex-direction:column; align-items:center; gap:.2rem;
  cursor:pointer; transition:all .18s; user-select:none;
  position:relative; min-height:80px; justify-content:center;
}
.pi-tile-num  { font-weight:900; font-size:1.05rem; letter-spacing:-.01em; }
.pi-tile-cap  { font-size:.58rem; color:inherit; opacity:.6; display:flex; align-items:center; gap:.18rem; }
.pi-tile-status {
  font-size:.55rem; font-weight:900; letter-spacing:.06em;
  text-transform:uppercase; padding:.18rem .5rem;
  border-radius:20px; margin-top:.1rem;
}

/* Available */
.pi-tile.avail { background:#F0FDF4; border-color:#86EFAC; color:#15803D; }
.pi-tile.avail:hover { background:#DCFCE7; border-color:#4ADE80; transform:translateY(-2px); box-shadow:0 4px 12px rgba(34,197,94,.2); }
.pi-tile.avail .pi-tile-status { background:#DCFCE7; color:#15803D; }

/* Occupied */
.pi-tile.occu  { background:#FFF1F2; border-color:#FCA5A5; color:#B91C1C; }
.pi-tile.occu:hover  { background:#FFE4E6; border-color:#F87171; transform:translateY(-2px); box-shadow:0 4px 12px rgba(239,68,68,.2); }
.pi-tile.occu .pi-tile-status  { background:#FFE4E6; color:#B91C1C; }

/* Booked */
.pi-tile.booked { background:#F5F3FF; border-color:#A78BFA; color:#6D28D9; }
.pi-tile.booked:hover { background:#EDE9FE; border-color:#7C3AED; transform:translateY(-2px); box-shadow:0 4px 12px rgba(109,40,217,.2); }
.pi-tile.booked .pi-tile-status { background:#EDE9FE; color:#6D28D9; }
.pi-tile.booked::before {
  content:'📅'; position:absolute; top:.3rem; right:.4rem;
  font-size:.65rem;
}

/* Reserved */
.pi-tile.resv  { background:#FFF7ED; border-color:#FCD34D; color:#92400E; }
.pi-tile.resv .pi-tile-status  { background:#FEF3C7; color:#92400E; }

/* Cleaning */
.pi-tile.clean { background:#F8FAFC; border-color:#CBD5E1; color:#64748B; }
.pi-tile.clean .pi-tile-status { background:#F1F5F9; color:#64748B; }

/* Active order indicator dot on occupied tables */
.pi-tile-orders {
  position:absolute; top:.3rem; left:.35rem;
  font-size:.58rem; font-weight:800;
  background:#B91C1C; color:#fff;
  padding:.1rem .35rem; border-radius:10px;
  line-height:1.2;
}
</style>

<div class="pi-root">

  <!-- ── TOP BAR ── -->
  <div class="pi-bar">
    <a href="<?= base_url('admin/dashboard') ?>" class="pi-bar-back"><i class="fa fa-arrow-left"></i></a>
    <div class="pi-bar-brand">
      <div class="pi-bar-rest"><i class="fa fa-cash-register" style="opacity:.6;margin-right:.3rem"></i><?= esc(session('restaurant_name') ?? 'RestOne POS') ?></div>
      <div class="pi-bar-sub"><?= esc($branch['name'] ?? 'Main Branch') ?></div>
    </div>
    <div class="pi-bar-acts">
      <div class="pi-bar-clock" id="piClock"></div>
      <button class="pi-bar-btn" id="custOrdBtn" title="Customer Orders (QR)" onclick="openCustOrders()" style="position:relative">
        <i class="fa fa-bell"></i>
        <span id="custOrdBadge" style="display:none;position:absolute;top:4px;right:4px;width:16px;height:16px;border-radius:50%;background:#EF4444;color:#fff;font-size:.55rem;font-weight:900;display:flex;align-items:center;justify-content:center;line-height:1">0</span>
      </button>
      <a href="<?= base_url('pos/kitchen') ?>" class="pi-bar-btn" title="Kitchen Display"><i class="fa fa-fire-burner"></i></a>
      <a href="<?= base_url('pos/shift/summary') ?>" class="pi-bar-btn" title="Shift Summary"><i class="fa fa-clock"></i></a>
      <a href="<?= base_url('logout') ?>" class="pi-bar-btn" title="Logout"><i class="fa fa-right-from-bracket"></i></a>
    </div>
  </div>

  <!-- ── COMBINED HEADER: Order types + Stats ── -->
  <div class="pi-header">
    <!-- Order type tabs -->
    <div class="pi-types">
      <a href="<?= base_url('pos/new-order/dine_in') ?>" class="pi-type-btn active">
        <i class="fa fa-chair"></i><span>Dine-in</span>
      </a>
      <a href="<?= base_url('pos/new-order/takeaway') ?>" class="pi-type-btn">
        <i class="fa fa-bag-shopping"></i><span>Takeaway</span>
      </a>
      <a href="<?= base_url('pos/new-order/delivery') ?>" class="pi-type-btn">
        <i class="fa fa-motorcycle"></i><span>Delivery</span>
      </a>
    </div>
    <!-- Stats row -->
    <div class="pi-stats">
      <div class="pi-stat">
        <div class="pi-stat-dot" style="background:#F59E0B"></div>
        <div class="pi-stat-val" id="piStatActive"><?= $openOrder ?? 0 ?></div>
        <div class="pi-stat-lbl">Active</div>
      </div>
      <div class="pi-stat">
        <div class="pi-stat-dot" style="background:#22C55E"></div>
        <div class="pi-stat-val" style="color:#15803D"><?= $availCount ?></div>
        <div class="pi-stat-lbl">Free</div>
      </div>
      <div class="pi-stat">
        <div class="pi-stat-dot" style="background:#EF4444"></div>
        <div class="pi-stat-val" style="color:#B91C1C"><?= $occuCount ?></div>
        <div class="pi-stat-lbl">Occupied</div>
      </div>
      <div class="pi-stat">
        <div class="pi-stat-dot" style="background:#8B5CF6"></div>
        <div class="pi-stat-val" style="color:#6D28D9"><?= $bookedCount ?></div>
        <div class="pi-stat-lbl">Booked</div>
      </div>
    </div>
  </div>

  <!-- ── TABLE MAP ── -->
  <div class="pi-map">
    <?php if (empty($tables)): ?>
    <div style="text-align:center;padding:4rem 1rem;color:#94A3B8">
      <div style="font-size:3rem;margin-bottom:1rem">🪑</div>
      <div style="font-weight:700;margin-bottom:.5rem;color:#64748B">No Tables Set Up</div>
      <a href="<?= base_url('admin/tables') ?>" style="color:var(--primary);font-weight:700">Add Tables →</a>
    </div>
    <?php else: foreach ($tables as $area): if (empty($area['tables'])) continue; ?>
    <div class="pi-area-lbl"><?= esc($area['name']) ?></div>
    <div class="pi-tgrid">
      <?php foreach ($area['tables'] as $t):
        $cls = match($t['status']) {
          'available' => 'avail',
          'occupied'  => 'occu',
          'booked'    => 'booked',
          'reserved'  => 'resv',
          default     => 'clean',
        };
        $lbl = match($t['status']) {
          'available' => 'Free',
          'occupied'  => 'Occupied',
          'booked'    => 'Booked',
          'reserved'  => 'Reserved',
          'cleaning'  => 'Cleaning',
          default     => ucfirst($t['status']),
        };
        $tData = htmlspecialchars(json_encode([
          'id'     => $t['id'],
          'status' => $t['status'],
          'num'    => $t['table_number'],
          'cap'    => $t['capacity'],
          'booked_name'  => $t['booked_name']  ?? '',
          'booked_phone' => $t['booked_phone'] ?? '',
          'booked_for'   => $t['booked_for']   ?? '',
          'booked_note'  => $t['booked_note']  ?? '',
          'qr_token'     => $t['qr_token']     ?? '',
        ]), ENT_QUOTES);
      ?>
      <div class="pi-tile <?= $cls ?>" onclick="tableClick(<?= $tData ?>)">
        <div class="pi-tile-num"><?= esc($t['table_number']) ?></div>
        <div class="pi-tile-cap"><i class="fa fa-users"></i><?= $t['capacity'] ?> pax</div>
        <div class="pi-tile-status"><?= $lbl ?></div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endforeach; endif; ?>
  </div>
</div>

<!-- ══ SHEET: Active Table Orders (occupied) ═════════════ -->
<div class="sheet-bg" id="bgOrders" onclick="closeBg('bgOrders','sOrders')"></div>
<div class="sheet" id="sOrders">
  <div class="sheet-pip"></div>
  <div class="sheet-hdr">
    <span class="sheet-title" id="sOrdersTitle">Table Orders</span>
    <button class="sheet-x" onclick="closeSheet('sOrders','bgOrders')"><i class="fa fa-times"></i></button>
  </div>
  <div class="sheet-body" id="sOrdersBody"></div>
  <div class="sheet-foot" id="sOrdersFoot"></div>
</div>

<!-- ══ SHEET: Available Table Actions ════════════════════ -->
<div class="sheet-bg" id="bgAction" onclick="closeBg('bgAction','sAction')"></div>
<div class="sheet" id="sAction">
  <div class="sheet-pip"></div>
  <div class="sheet-hdr">
    <span class="sheet-title" id="sActionTitle">Table Options</span>
    <button class="sheet-x" onclick="closeSheet('sAction','bgAction')"><i class="fa fa-times"></i></button>
  </div>
  <div class="sheet-body" id="sActionBody"></div>
</div>

<!-- ══ SHEET: Book Table ═════════════════════════════════ -->
<div class="sheet-bg" id="bgBook" onclick="closeBg('bgBook','sBook')"></div>
<div class="sheet" id="sBook">
  <div class="sheet-pip"></div>
  <div class="sheet-hdr">
    <span class="sheet-title" id="sBookTitle">📅 Book Table</span>
    <button class="sheet-x" onclick="closeSheet('sBook','bgBook')"><i class="fa fa-times"></i></button>
  </div>
  <div class="sheet-body">
    <input type="hidden" id="bookTableId">
    <div style="display:flex;flex-direction:column;gap:.75rem">
      <div>
        <label style="font-size:.75rem;font-weight:700;color:#64748B;display:block;margin-bottom:.3rem">Guest Name *</label>
        <input type="text" id="bookName" class="form-control" placeholder="Customer name" style="width:100%;padding:.625rem;border:1.5px solid #E2E8F0;border-radius:10px;font-family:var(--font)">
      </div>
      <div>
        <label style="font-size:.75rem;font-weight:700;color:#64748B;display:block;margin-bottom:.3rem">Phone</label>
        <input type="tel" id="bookPhone" class="form-control" placeholder="+91 XXXXX XXXXX" style="width:100%;padding:.625rem;border:1.5px solid #E2E8F0;border-radius:10px;font-family:var(--font)">
      </div>
      <div>
        <label style="font-size:.75rem;font-weight:700;color:#64748B;display:block;margin-bottom:.3rem">Booking Date & Time *</label>
        <input type="datetime-local" id="bookFor" class="form-control" style="width:100%;padding:.625rem;border:1.5px solid #E2E8F0;border-radius:10px;font-family:var(--font)">
      </div>
      <div>
        <label style="font-size:.75rem;font-weight:700;color:#64748B;display:block;margin-bottom:.3rem">Note</label>
        <input type="text" id="bookNote" class="form-control" placeholder="Birthday, Anniversary..." style="width:100%;padding:.625rem;border:1.5px solid #E2E8F0;border-radius:10px;font-family:var(--font)">
      </div>
    </div>
  </div>
  <div class="sheet-foot">
    <button onclick="closeSheet('sBook','bgBook')" style="flex:1;padding:.75rem;border:1.5px solid #E2E8F0;border-radius:10px;background:#fff;font-family:var(--font);font-weight:700;cursor:pointer">Cancel</button>
    <button onclick="confirmBooking()" style="flex:2;padding:.75rem;border:none;border-radius:10px;background:#6D28D9;color:#fff;font-family:var(--font);font-weight:800;cursor:pointer;font-size:.95rem">
      <i class="fa fa-calendar-check"></i> Confirm Booking
    </button>
  </div>
</div>

<!-- ══ CUSTOMER ORDERS MODAL ════════════════════════════ -->
<div id="custOrderModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:500;align-items:flex-end;justify-content:center">
  <div style="background:#F8FAFC;border-radius:22px 22px 0 0;width:100%;max-width:560px;max-height:90vh;display:flex;flex-direction:column">
    <!-- Header -->
    <div style="padding:1rem 1.25rem .75rem;border-bottom:1px solid #E2E8F0;display:flex;align-items:center;justify-content:space-between;flex-shrink:0">
      <div>
        <div style="font-weight:900;font-size:1rem;display:flex;align-items:center;gap:.5rem">
          <span style="font-size:1.1rem">📱</span> Customer QR Orders
        </div>
        <div style="font-size:.72rem;color:#94A3B8;margin-top:.15rem">Orders placed by customers via QR code at their table</div>
      </div>
      <button onclick="closeCustOrders()" style="width:34px;height:34px;border-radius:10px;border:none;background:#F1F5F9;color:#64748B;font-size:.9rem;cursor:pointer"><i class="fa fa-times"></i></button>
    </div>
    <!-- Body -->
    <div id="custOrderBody" style="flex:1;overflow-y:auto;padding:1rem 1.25rem"></div>
    <!-- Footer -->
    <div style="padding:.75rem 1.25rem;border-top:1px solid #E2E8F0;flex-shrink:0;display:flex;gap:.5rem">
      <button onclick="pollCustomerOrders();renderCustOrders()" style="flex:1;padding:.625rem;background:#F1F5F9;color:#334155;border:none;border-radius:10px;font-weight:700;font-size:.82rem;cursor:pointer;font-family:var(--font)"><i class="fa fa-refresh"></i> Refresh</button>
      <button onclick="closeCustOrders()" style="flex:1;padding:.625rem;background:#0F172A;color:#fff;border:none;border-radius:10px;font-weight:700;font-size:.82rem;cursor:pointer;font-family:var(--font)">Close</button>
    </div>
  </div>
</div>

<!-- ══ SHEET: QR Code Display ════════════════════════════ -->
<div class="sheet-bg" id="bgQr" onclick="closeBg('bgQr','sQr')"></div>
<div class="sheet" id="sQr">
  <div class="sheet-pip"></div>
  <div class="sheet-hdr">
    <span class="sheet-title" id="sQrTitle">QR Code</span>
    <button class="sheet-x" onclick="closeSheet('sQr','bgQr')"><i class="fa fa-times"></i></button>
  </div>
  <div class="sheet-body" style="display:flex;flex-direction:column;align-items:center;gap:1rem;padding:1.25rem">
    <div id="qrCanvas" style="background:#fff;padding:16px;border-radius:16px;box-shadow:0 4px 20px rgba(0,0,0,.1)"></div>
    <div id="qrUrl" style="font-size:.72rem;color:#64748B;text-align:center;word-break:break-all;max-width:280px"></div>
    <div style="display:flex;gap:.625rem;width:100%">
      <button onclick="copyQrUrl()" style="flex:1;padding:.7rem;border:1.5px solid #E2E8F0;border-radius:10px;background:#fff;font-family:var(--font);font-weight:700;cursor:pointer;font-size:.85rem">
        <i class="fa fa-copy"></i> Copy Link
      </button>
      <button onclick="refreshQr()" style="flex:1;padding:.7rem;border:none;border-radius:10px;background:var(--primary);color:#fff;font-family:var(--font);font-weight:700;cursor:pointer;font-size:.85rem">
        <i class="fa fa-refresh"></i> New QR
      </button>
    </div>
    <div style="font-size:.75rem;color:#94A3B8;text-align:center">
      Customers scan to view menu on their phone
    </div>
  </div>
</div>

<!-- QR library -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

<script>
const BASE = '<?= base_url() ?>';
const CN   = '<?= csrf_token() ?>';
const CT   = '<?= csrf_hash() ?>';
let currentQrTableId = null;
let currentQrToken   = null;

// Clock
setInterval(() => {
  const el = document.getElementById('piClock');
  if (el) el.textContent = new Date().toLocaleTimeString('en-IN', {hour:'2-digit',minute:'2-digit',second:'2-digit'});
}, 1000);

// Sheet helpers
function openSheet(id)    { document.getElementById('bg'+id.slice(1))?.classList.add('on'); document.getElementById(id).classList.add('on'); document.body.style.overflow='hidden'; }
function closeSheet(id,bg){ document.getElementById(id)?.classList.remove('on'); document.getElementById(bg||'')?.classList.remove('on'); document.body.style.overflow=''; }
function closeBg(bg,id)   { closeSheet(id, bg); }

function postApi(url, data) {
  return fetch(url, {
    method: 'POST',
    headers: { 'Content-Type':'application/x-www-form-urlencoded', 'X-Requested-With':'XMLHttpRequest' },
    body: new URLSearchParams({ [CN]: CT, ...data })
  }).then(r => r.json());
}

// ── Table click handler ──────────────────────────────────
function tableClick(t) {
  if (t.status === 'available') {
    showAvailableSheet(t);
  } else if (t.status === 'occupied') {
    showOccupiedSheet(t);
  } else if (t.status === 'booked') {
    showBookedSheet(t);
  } else {
    showOtherSheet(t);
  }
}

// ── Available table — show actions ───────────────────────
function showAvailableSheet(t) {
  document.getElementById('sActionTitle').textContent = '🪑 Table ' + t.num;
  document.getElementById('sActionBody').innerHTML = `
    <div style="display:flex;flex-direction:column;gap:.6rem">
      <a href="${BASE}pos/new-order/dine_in?table=${t.id}" class="pi-action-card primary">
        <div class="pi-action-icon" style="background:#FFF0EB;color:var(--primary)"><i class="fa fa-utensils"></i></div>
        <div class="pi-action-info"><div class="pi-action-title">New Dine-in Order</div><div class="pi-action-sub">Table ${t.num} · Start taking order now</div></div>
        <i class="fa fa-chevron-right" style="color:#CBD5E1;font-size:.8rem"></i>
      </a>
      <button onclick="startBooking(${t.id},'${t.num}')" class="pi-action-card">
        <div class="pi-action-icon" style="background:#F5F3FF;color:#7C3AED"><i class="fa fa-calendar-plus"></i></div>
        <div class="pi-action-info"><div class="pi-action-title">Book Table</div><div class="pi-action-sub">Reserve for a future customer</div></div>
        <i class="fa fa-chevron-right" style="color:#CBD5E1;font-size:.8rem"></i>
      </button>
      <button onclick="showQrSheet(${t.id},'${t.num}','${t.qr_token}')" class="pi-action-card">
        <div class="pi-action-icon" style="background:#EFF6FF;color:#2563EB"><i class="fa fa-qrcode"></i></div>
        <div class="pi-action-info"><div class="pi-action-title">Show QR Code</div><div class="pi-action-sub">Customer scans to see menu</div></div>
        <i class="fa fa-chevron-right" style="color:#CBD5E1;font-size:.8rem"></i>
      </button>
    </div>
    <style>
    .pi-action-card { display:flex;align-items:center;gap:.875rem;padding:.875rem;border:1.5px solid #E2E8F0;border-radius:14px;text-decoration:none;color:#0F172A;background:#fff;cursor:pointer;width:100%;font-family:var(--font);text-align:left;transition:border-color .15s; }
    .pi-action-card:hover { border-color:var(--primary); }
    .pi-action-card.primary { border-color:var(--primary);background:#FFF8F5; }
    .pi-action-icon { width:44px;height:44px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0; }
    .pi-action-info { flex:1;min-width:0; }
    .pi-action-title { font-weight:800;font-size:.875rem; }
    .pi-action-sub { font-size:.72rem;color:#64748B;margin-top:.15rem; }
    </style>`;
  openSheet('sAction');
}

// ── Occupied table — show orders ─────────────────────────
function showOccupiedSheet(t) {
  document.getElementById('sOrdersTitle').textContent = '🍽 Table ' + t.num + ' — Active Orders';
  document.getElementById('sOrdersBody').innerHTML = '<div style="text-align:center;padding:2rem;color:#94A3B8"><i class="fa fa-spinner fa-spin fa-lg"></i><br><br>Loading orders...</div>';
  document.getElementById('sOrdersFoot').innerHTML = '';
  openSheet('sOrders');

  fetch(BASE + 'pos/table-orders/' + t.id)
    .then(r => r.json()).then(orders => {
      if (!orders.length) {
        document.getElementById('sOrdersBody').innerHTML = '<div style="text-align:center;padding:2.5rem;color:#94A3B8"><i class="fa fa-receipt fa-2x" style="margin-bottom:.75rem;display:block"></i>No active orders at this table.</div>';
        document.getElementById('sOrdersFoot').innerHTML = `<a href="${BASE}pos/new-order/dine_in?table=${t.id}" style="flex:1;padding:.75rem;background:var(--primary);color:#fff;border:none;border-radius:10px;font-weight:800;text-align:center;text-decoration:none;font-size:.9rem;display:flex;align-items:center;justify-content:center;gap:.4rem"><i class="fa fa-plus"></i> New Order</a>`;
        return;
      }

      const statusClr = { pending:'#F59E0B',confirmed:'#3B82F6',preparing:'#8B5CF6',ready:'#22C55E',served:'#10B981' };
      document.getElementById('sOrdersBody').innerHTML = orders.map(o => {
        const isPaid = o.payment_status === 'paid';
        const kotBadge = parseInt(o.kot_count||0) > 0 ? `<span style="font-size:.58rem;background:${isPaid?'#DCFCE7':'#FEF3C7'};color:${isPaid?'#15803D':'#92400E'};padding:.1rem .35rem;border-radius:8px;font-weight:800">${isPaid?'✓ PAID':'KOT×'+o.kot_count+' UNPAID'}</span>` : `<span style="font-size:.58rem;background:#FEF3C7;color:#92400E;padding:.1rem .35rem;border-radius:8px;font-weight:800">UNPAID</span>`;
        return `
        <div style="display:flex;align-items:center;gap:.625rem;padding:.75rem 1rem;border-bottom:1px solid #F1F5F9;border-left:3px solid ${isPaid?'#22C55E':'#F59E0B'}">
          <div style="flex:1;min-width:0">
            <div style="font-weight:800;font-size:.875rem;display:flex;align-items:center;gap:.4rem">
              ${o.order_number} ${kotBadge}
            </div>
            <div style="font-size:.7rem;color:#94A3B8;margin-top:.2rem">${o.items_count} items · ${o.time_ago}</div>
          </div>
          <span style="font-size:.65rem;font-weight:800;padding:.2rem .45rem;border-radius:8px;background:${statusClr[o.status]||'#E2E8F0'}22;color:${statusClr[o.status]||'#64748B'}">${o.status.charAt(0).toUpperCase()+o.status.slice(1)}</span>
          <div style="text-align:right;flex-shrink:0">
            <div style="font-weight:900;font-size:.9rem;color:var(--primary)">₹${parseFloat(o.total_amount).toFixed(0)}</div>
          </div>
          <div style="display:flex;gap:.3rem">
            <a href="${BASE}pos/order/${o.id}" style="padding:.35rem .6rem;background:var(--primary);color:#fff;border-radius:8px;font-size:.72rem;font-weight:800;text-decoration:none">Open</a>
            <a href="${BASE}pos/new-order/dine_in?table=${t.id}&add_to=${o.id}" style="padding:.35rem .6rem;background:#F5F3FF;color:#6D28D9;border-radius:8px;font-size:.72rem;font-weight:800;text-decoration:none;border:1px solid #DDD6FE" title="Add more items to this order">+Round</a>
          </div>
        </div>`;
      }).join('');

      // Footer: total amount + new order button
      const total = orders.reduce((s,o) => s + parseFloat(o.total_amount), 0);
      document.getElementById('sOrdersFoot').innerHTML = `
        <div style="flex:1;display:flex;gap:.5rem">
          <a href="${BASE}pos/new-order/dine_in?table=${t.id}" style="flex:1;padding:.7rem;border:1.5px solid #E2E8F0;border-radius:10px;font-weight:700;text-align:center;text-decoration:none;color:#334155;background:#fff;font-size:.82rem;display:flex;align-items:center;justify-content:center;gap:.35rem"><i class="fa fa-plus"></i> New Order</a>
          <div style="flex:1;padding:.7rem;border-radius:10px;background:#0F172A;color:#fff;font-weight:900;text-align:center;font-size:.875rem">₹${total.toFixed(2)}</div>
        </div>`;
    }).catch(() => {
      document.getElementById('sOrdersBody').innerHTML = '<div style="color:#EF4444;text-align:center;padding:1.5rem">Failed to load orders</div>';
    });
}

// ── Booked table — show booking info ─────────────────────
function showBookedSheet(t) {
  document.getElementById('sActionTitle').textContent = '📅 Table ' + t.num + ' — Booked';
  const forTime = t.booked_for ? new Date(t.booked_for).toLocaleString('en-IN', {day:'2-digit',month:'short',hour:'2-digit',minute:'2-digit'}) : '—';
  document.getElementById('sActionBody').innerHTML = `
    <div style="background:#F5F3FF;border:1.5px solid #A78BFA;border-radius:12px;padding:1rem;margin-bottom:1rem">
      <div style="display:flex;align-items:center;gap:.75rem">
        <div style="width:44px;height:44px;border-radius:12px;background:#EDE9FE;color:#6D28D9;display:flex;align-items:center;justify-content:center;font-size:1.2rem;flex-shrink:0">📅</div>
        <div>
          <div style="font-weight:900;font-size:1rem;color:#4C1D95">${t.booked_name || 'Guest'}</div>
          ${t.booked_phone ? `<div style="font-size:.78rem;color:#6D28D9;margin-top:.15rem"><i class="fa fa-phone"></i> ${t.booked_phone}</div>` : ''}
          <div style="font-size:.78rem;color:#7C3AED;margin-top:.1rem"><i class="fa fa-clock"></i> ${forTime}</div>
          ${t.booked_note ? `<div style="font-size:.75rem;color:#7C3AED;margin-top:.15rem;font-style:italic">"${t.booked_note}"</div>` : ''}
        </div>
      </div>
    </div>
    <div style="display:flex;flex-direction:column;gap:.5rem">
      <a href="${BASE}pos/new-order/dine_in?table=${t.id}" style="display:flex;align-items:center;gap:.75rem;padding:.875rem;background:#FFF0EB;border:1.5px solid var(--primary);border-radius:12px;text-decoration:none;color:var(--primary);font-weight:800">
        <i class="fa fa-utensils"></i> Start Order for This Booking
      </a>
      <button onclick="cancelBookingNow(${t.id})" style="display:flex;align-items:center;gap:.75rem;padding:.875rem;background:#fff;border:1.5px solid #FCA5A5;border-radius:12px;cursor:pointer;color:#B91C1C;font-weight:800;font-family:var(--font);width:100%">
        <i class="fa fa-calendar-xmark"></i> Cancel Booking
      </button>
      <button onclick="showQrSheet(${t.id},'${t.num}','${t.qr_token}')" style="display:flex;align-items:center;gap:.75rem;padding:.875rem;background:#fff;border:1.5px solid #E2E8F0;border-radius:12px;cursor:pointer;color:#334155;font-weight:700;font-family:var(--font);width:100%">
        <i class="fa fa-qrcode"></i> Show QR Code
      </button>
    </div>`;
  openSheet('sAction');
}

// ── Other status (reserved/cleaning) ────────────────────
function showOtherSheet(t) {
  const icons = { reserved:'📋', cleaning:'🧹', inactive:'🚫' };
  document.getElementById('sActionTitle').textContent = (icons[t.status]||'🪑') + ' Table ' + t.num;
  document.getElementById('sActionBody').innerHTML = `
    <div style="text-align:center;padding:1.5rem 0">
      <div style="font-size:3rem;margin-bottom:.75rem">${icons[t.status]||'🪑'}</div>
      <div style="font-weight:700;font-size:1rem;margin-bottom:.4rem">Table is ${t.status}</div>
      <div style="font-size:.82rem;color:#94A3B8;margin-bottom:1.5rem">You can still start an order if needed.</div>
      <a href="${BASE}pos/new-order/dine_in?table=${t.id}" style="display:inline-flex;align-items:center;gap:.5rem;padding:.75rem 1.5rem;background:var(--primary);color:#fff;border-radius:10px;font-weight:800;text-decoration:none">
        <i class="fa fa-utensils"></i> Start Order Anyway
      </a>
    </div>`;
  openSheet('sAction');
}

// ── Booking ──────────────────────────────────────────────
function startBooking(tableId, tableNum) {
  document.getElementById('bookTableId').value = tableId;
  document.getElementById('sBookTitle').textContent = '📅 Book Table ' + tableNum;
  document.getElementById('bookName').value  = '';
  document.getElementById('bookPhone').value = '';
  document.getElementById('bookNote').value  = '';
  // Set default booking time to 1 hour from now
  const d = new Date(); d.setHours(d.getHours()+1); d.setSeconds(0);
  document.getElementById('bookFor').value = d.toISOString().slice(0,16);
  closeSheet('sAction','bgAction');
  setTimeout(() => openSheet('sBook'), 200);
}

function confirmBooking() {
  const id   = document.getElementById('bookTableId').value;
  const name = document.getElementById('bookName').value.trim();
  if (!name) { alert('Please enter guest name'); return; }
  postApi(BASE+'pos/table/book/'+id, {
    name:      name,
    phone:     document.getElementById('bookPhone').value,
    booked_for:document.getElementById('bookFor').value,
    note:      document.getElementById('bookNote').value,
  }).then(d => {
    if (d.success) { closeSheet('sBook','bgBook'); showToast('Table booked for '+name,'success'); setTimeout(()=>location.reload(),700); }
    else showToast('Failed to book','error');
  });
}

function cancelBookingNow(tableId) {
  if (!confirm('Cancel this booking? Table will be marked as available.')) return;
  postApi(BASE+'pos/table/cancel-booking/'+tableId, {})
    .then(d => { if(d.success){ closeSheet('sAction','bgAction'); showToast('Booking cancelled','success'); setTimeout(()=>location.reload(),700); } });
}

// ── QR Code ──────────────────────────────────────────────
function showQrSheet(tableId, tableNum, token) {
  currentQrTableId = tableId;
  currentQrToken   = token;
  document.getElementById('sQrTitle').textContent = '🔲 Table ' + tableNum + ' — QR Code';
  document.getElementById('qrCanvas').innerHTML   = '';

  closeSheet('sAction','bgAction');
  setTimeout(() => {
    openSheet('sQr');
    renderQr(token);
  }, 200);
}

function renderQr(token) {
  document.getElementById('qrCanvas').innerHTML = '';
  if (!token) {
    document.getElementById('qrCanvas').innerHTML = '<div style="padding:2rem;color:#94A3B8;font-size:.85rem">No QR generated yet. Click "New QR" to generate.</div>';
    document.getElementById('qrUrl').textContent = '';
    return;
  }
  const url = BASE + 'menu/table/' + token;
  document.getElementById('qrUrl').textContent = url;
  new QRCode(document.getElementById('qrCanvas'), {
    text: url, width:220, height:220,
    colorDark:'#0F172A', colorLight:'#ffffff',
    correctLevel: QRCode.CorrectLevel.M,
  });
}

function copyQrUrl() {
  const url = document.getElementById('qrUrl').textContent;
  navigator.clipboard?.writeText(url).then(() => showToast('Link copied!','success')).catch(() => {
    const el = document.createElement('textarea');
    el.value = url; document.body.appendChild(el); el.select();
    document.execCommand('copy'); document.body.removeChild(el);
    showToast('Link copied!','success');
  });
}

function refreshQr() {
  if (!currentQrTableId) return;
  fetch(BASE+'admin/tables/generate-qr/'+currentQrTableId, { headers:{'X-Requested-With':'XMLHttpRequest'} })
    .then(r=>r.json()).then(d => {
      if (d.success) {
        currentQrToken = d.token;
        renderQr(d.token);
        showToast('New QR generated','success');
      }
    });
}

// ── Toast ────────────────────────────────────────────────
function showToast(msg, type='info') {
  const clr = {success:'#22C55E',error:'#EF4444',warning:'#F59E0B',info:'#3B82F6'};
  const ic  = {success:'fa-check-circle',error:'fa-circle-exclamation',warning:'fa-triangle-exclamation',info:'fa-circle-info'};
  const t = document.createElement('div');
  t.style.cssText = `position:fixed;bottom:5rem;left:50%;transform:translateX(-50%);background:${clr[type]||clr.info};color:#fff;padding:.6rem 1.2rem;border-radius:24px;font-weight:700;font-size:.82rem;z-index:9999;display:flex;align-items:center;gap:.4rem;box-shadow:0 4px 16px rgba(0,0,0,.2);white-space:nowrap`;
  t.innerHTML = `<i class="fa ${ic[type]||ic.info}"></i>${msg}`;
  document.body.appendChild(t);
  setTimeout(()=>{t.style.opacity='0';t.style.transition='opacity .3s';setTimeout(()=>t.remove(),300);},2500);
}

// ── Auto-refresh active orders count ────────────────────
setInterval(() => {
  fetch(BASE+'pos/active-orders').then(r=>r.json()).then(orders => {
    const el = document.getElementById('piStatActive');
    if (el) el.textContent = orders.length;
  }).catch(()=>{});
}, 30000);

// ── Customer QR order notification polling ───────────────
let custOrders = [];
function pollCustomerOrders() {
  fetch(BASE+'pos/order/pending-customer', {headers:{'X-Requested-With':'XMLHttpRequest'}})
    .then(r=>r.json()).then(d=>{
      if (!d.success) return;
      custOrders = d.orders || [];
      const cnt  = d.count || 0;
      const badge= document.getElementById('custOrdBadge');
      const btn  = document.getElementById('custOrdBtn');
      if (badge) {
        badge.textContent = cnt;
        badge.style.display = cnt > 0 ? 'flex' : 'none';
      }
      if (btn) {
        btn.style.background = cnt > 0 ? '#EF4444' : 'rgba(255,255,255,.08)';
        btn.style.color      = cnt > 0 ? '#fff' : 'rgba(255,255,255,.6)';
      }
      // Auto-notify with toast
      if (cnt > 0 && document.hidden === false) {
        const existing = document.getElementById('custToast');
        if (!existing) {
          const t = document.createElement('div');
          t.id = 'custToast';
          t.style.cssText = 'position:fixed;top:4.5rem;right:1rem;background:#EF4444;color:#fff;padding:.6rem 1rem;border-radius:12px;font-weight:800;font-size:.8rem;z-index:999;cursor:pointer;box-shadow:0 4px 16px rgba(239,68,68,.4);display:flex;align-items:center;gap:.5rem;animation:slidein .3s ease';
          t.innerHTML = '<i class="fa fa-bell fa-shake"></i> ' + cnt + ' new customer order' + (cnt>1?'s':'') + ' waiting!';
          t.onclick = () => { openCustOrders(); t.remove(); };
          document.body.appendChild(t);
          setTimeout(() => t.remove(), 8000);
        }
      }
    }).catch(()=>{});
}
pollCustomerOrders();
setInterval(pollCustomerOrders, 12000);

function openCustOrders() {
  const modal = document.getElementById('custOrderModal');
  renderCustOrders();
  modal.style.display = 'flex';
  document.body.style.overflow = 'hidden';
}
function closeCustOrders() {
  document.getElementById('custOrderModal').style.display = 'none';
  document.body.style.overflow = '';
}

function renderCustOrders() {
  const body = document.getElementById('custOrderBody');
  if (!custOrders.length) {
    body.innerHTML = '<div style="text-align:center;padding:3rem 1rem;color:#94A3B8"><i class="fa fa-bell-slash fa-2x" style="margin-bottom:1rem;display:block"></i><div style="font-weight:700">No pending customer orders</div><div style="font-size:.8rem;margin-top:.4rem">Orders placed via QR code will appear here</div></div>';
    return;
  }
  body.innerHTML = custOrders.map(o => `
    <div style="border:1.5px solid #E2E8F0;border-radius:14px;padding:1rem;margin-bottom:.75rem;background:#fff">
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.625rem">
        <div>
          <span style="font-weight:900;font-size:.9rem">#${o.order_number}</span>
          <span style="background:#F5F3FF;color:#6D28D9;font-size:.62rem;font-weight:800;padding:.15rem .4rem;border-radius:8px;margin-left:.4rem">📱 QR Order</span>
        </div>
        <span style="font-size:.72rem;color:#94A3B8">${o.waiting}</span>
      </div>
      <div style="font-size:.78rem;color:#64748B;margin-bottom:.5rem">
        <i class="fa fa-chair" style="color:var(--primary)"></i> Table ${o.table_number}
        &nbsp;·&nbsp; <i class="fa fa-user"></i> ${o.customer_name || 'Guest'}
        ${o.customer_phone ? '&nbsp;·&nbsp; <i class="fa fa-phone"></i> ' + o.customer_phone : ''}
      </div>
      <div style="background:#F8FAFC;border-radius:8px;padding:.625rem;margin-bottom:.625rem">
        ${o.items.map(it=>`<div style="display:flex;justify-content:space-between;font-size:.78rem;padding:.2rem 0"><span>${it.name} ×${it.quantity}${it.notes?' <em style=\"color:#94A3B8\">('+it.notes+')</em>':''}</span><span style="font-weight:700">₹${parseFloat(it.unit_price*it.quantity).toFixed(0)}</span></div>`).join('')}
        <div style="display:flex;justify-content:space-between;font-weight:900;font-size:.85rem;padding-top:.5rem;border-top:1px solid #E2E8F0;margin-top:.35rem"><span>Total</span><span style="color:var(--primary)">₹${parseFloat(o.total_amount).toFixed(2)}</span></div>
      </div>
      <div style="display:flex;gap:.5rem">
        <button onclick="confirmOrder(${o.id})" style="flex:2;padding:.625rem;background:#22C55E;color:#fff;border:none;border-radius:10px;font-weight:800;font-size:.82rem;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:.4rem;font-family:var(--font)">
          <i class="fa fa-check"></i> Confirm & Send to Kitchen
        </button>
        <button onclick="rejectOrder(${o.id})" style="flex:1;padding:.625rem;background:#FFF1F2;color:#B91C1C;border:1.5px solid #FCA5A5;border-radius:10px;font-weight:700;font-size:.78rem;cursor:pointer;font-family:var(--font)">
          <i class="fa fa-times"></i> Reject
        </button>
      </div>
    </div>`).join('');
}

function confirmOrder(id) {
  postApi(BASE+'pos/order/confirm-customer/'+id, {})
    .then(d => {
      if (d.success) {
        showToast('Order confirmed! Sent to kitchen 🍳','success');
        pollCustomerOrders();
        setTimeout(renderCustOrders, 800);
        if (d.redirect) setTimeout(()=> window.open(d.redirect,'_blank'), 1200);
      }
    });
}

function rejectOrder(id) {
  if (!confirm('Reject this customer order? They will see it as cancelled.')) return;
  postApi(BASE+'pos/order/reject-customer/'+id, {})
    .then(d => {
      if (d.success) { showToast('Order rejected','warning'); pollCustomerOrders(); setTimeout(renderCustOrders, 800); }
    });
}
</script>

<?php $this->endSection(); ?>
