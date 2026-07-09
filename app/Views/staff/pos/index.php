<?php $this->extend('layouts/pos_layout'); $this->section('content'); ?>
<?php
$totalTables = 0; $availCount = 0; $occuCount = 0;
foreach ($tables as $area) {
  foreach ($area['tables'] as $t) {
    $totalTables++;
    if ($t['status'] === 'available') $availCount++;
    elseif ($t['status'] === 'occupied') $occuCount++;
  }
}
?>

<div class="kitch-root" style="background:var(--bg)">

  <!-- Top Bar -->
  <div class="posbar">
    <a href="<?= base_url('admin/dashboard') ?>" class="posbar-back"><i class="fa fa-arrow-left"></i></a>
    <div class="posbar-info">
      <div class="posbar-title"><i class="fa fa-cash-register"></i> POS</div>
      <div class="posbar-branch"><?= esc(session('restaurant_name') ?? 'RestOne') ?> · <?= esc($branch['name'] ?? 'Main Branch') ?></div>
    </div>
    <div class="posbar-actions">
      <div class="posbar-clock" id="idxClock"></div>
      <a href="<?= base_url('pos/kitchen') ?>" class="posbar-btn" title="Kitchen Display"><i class="fa fa-fire-burner"></i></a>
      <a href="<?= base_url('pos/shift/summary') ?>" class="posbar-btn" title="Shift"><i class="fa fa-clock"></i></a>
      <a href="<?= base_url('logout') ?>" class="posbar-btn" title="Logout"><i class="fa fa-right-from-bracket"></i></a>
    </div>
  </div>

  <!-- Quick Actions -->
  <div class="pos-quick-bar">
    <a href="<?= base_url('pos/new-order/dine_in') ?>" class="pos-quick-btn primary">
      <i class="fa fa-chair"></i><span>Dine-in</span>
    </a>
    <a href="<?= base_url('pos/new-order/takeaway') ?>" class="pos-quick-btn">
      <i class="fa fa-bag-shopping"></i><span>Takeaway</span>
    </a>
    <a href="<?= base_url('pos/new-order/delivery') ?>" class="pos-quick-btn">
      <i class="fa fa-motorcycle"></i><span>Delivery</span>
    </a>
  </div>

  <!-- Stats Row -->
  <div class="pos-stats-row">
    <div class="pos-stat">
      <div class="pos-stat-val" id="openOrdersStat"><?= $openOrder ?? 0 ?></div>
      <div class="pos-stat-lbl">Active Orders</div>
    </div>
    <div class="pos-stat">
      <div class="pos-stat-val" style="color:var(--success)"><?= $availCount ?></div>
      <div class="pos-stat-lbl">Available</div>
    </div>
    <div class="pos-stat">
      <div class="pos-stat-val" style="color:var(--danger)"><?= $occuCount ?></div>
      <div class="pos-stat-lbl">Occupied</div>
    </div>
    <div class="pos-stat">
      <div class="pos-stat-val"><?= $totalTables ?></div>
      <div class="pos-stat-lbl">Tables</div>
    </div>
  </div>

  <!-- Table Map -->
  <div style="flex:1;overflow-y:auto;padding:.875rem">
    <?php if (empty($tables)): ?>
    <div style="text-align:center;padding:4rem 1rem;color:var(--text-m)">
      <div style="font-size:3rem;margin-bottom:1rem">🪑</div>
      <div style="font-weight:700;margin-bottom:.5rem">No Tables Set Up</div>
      <a href="<?= base_url('admin/tables') ?>" style="color:var(--primary);font-weight:700">Set up tables →</a>
    </div>
    <?php else: ?>
    <?php foreach ($tables as $area): if (empty($area['tables'])) continue; ?>
    <div class="table-area-label"><?= esc($area['name']) ?></div>
    <div class="table-grid">
      <?php foreach ($area['tables'] as $t):
        $cls = match($t['status']) { 'available'=>'avail','occupied'=>'occu','reserved'=>'resv','cleaning'=>'clean', default=>'' };
        $statusLabel = match($t['status']) { 'available'=>'Available','occupied'=>'Occupied','reserved'=>'Reserved','cleaning'=>'Cleaning', default=>ucfirst($t['status']) };
      ?>
      <div class="ttile <?= $cls ?>" onclick="tableClick('<?= $t['id'] ?>','<?= $t['status'] ?>','<?= esc($t['table_number']) ?>')">
        <div class="ttile-num"><?= esc($t['table_number']) ?></div>
        <div class="ttile-cap"><i class="fa fa-users" style="font-size:.55rem"></i> <?= $t['capacity'] ?></div>
        <div class="ttile-status"><?= $statusLabel ?></div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
  </div>
</div>

<!-- Active Orders Sheet (bottom) -->
<div class="sheet-bg" id="bgOrders" onclick="closeBg('bgOrders','sOrders')"></div>
<div class="sheet" id="sOrders">
  <div class="sheet-pip"></div>
  <div class="sheet-hdr">
    <span class="sheet-title" id="sOrdersTitle">Table Orders</span>
    <button class="sheet-x" onclick="closeSheet('sOrders','bgOrders')"><i class="fa fa-times"></i></button>
  </div>
  <div class="sheet-body" id="sOrdersBody">
    <div style="text-align:center;padding:2rem;color:var(--text-m)"><i class="fa fa-spinner fa-spin"></i> Loading...</div>
  </div>
  <div class="sheet-foot" id="sOrdersFoot"></div>
</div>

<!-- Table Action Sheet -->
<div class="sheet-bg" id="bgTableAction" onclick="closeBg('bgTableAction','sTableAction')"></div>
<div class="sheet" id="sTableAction">
  <div class="sheet-pip"></div>
  <div class="sheet-hdr">
    <span class="sheet-title" id="sTableTitle">Table Action</span>
    <button class="sheet-x" onclick="closeSheet('sTableAction','bgTableAction')"><i class="fa fa-times"></i></button>
  </div>
  <div class="sheet-body" id="sTableBody"></div>
</div>

<script>
const BASE = '<?= base_url() ?>';
const CN   = '<?= csrf_token() ?>';
const CT   = '<?= csrf_hash() ?>';

// Clock
setInterval(() => {
  const el = document.getElementById('idxClock');
  if (el) el.textContent = new Date().toLocaleTimeString('en-IN', {hour:'2-digit',minute:'2-digit',second:'2-digit'});
}, 1000);

// Sheet helpers
function openSheet(id)  { document.getElementById('bg'+id.slice(1))?.classList.add('on'); document.getElementById(id).classList.add('on'); document.body.style.overflow='hidden'; }
function closeSheet(id,bgId) { document.getElementById(id)?.classList.remove('on'); document.getElementById(bgId||'')?.classList.remove('on'); document.body.style.overflow=''; }
function closeBg(bgId,shId) { closeSheet(shId, bgId); }

// Table click
function tableClick(tableId, status, tableNum) {
  if (status === 'available') {
    // Go directly to new order sheet
    const body  = document.getElementById('sTableBody');
    const title = document.getElementById('sTableTitle');
    title.textContent = 'Table ' + tableNum + ' — New Order';
    body.innerHTML = `
      <div style="display:flex;flex-direction:column;gap:.75rem">
        <a href="${BASE}pos/new-order/dine_in?table=${tableId}" style="display:flex;align-items:center;gap:1rem;padding:1rem;border:2px solid var(--border);border-radius:var(--radius);text-decoration:none;color:var(--text);transition:border-color .15s" onmouseover="this.style.borderColor='var(--primary)'" onmouseout="this.style.borderColor='var(--border)'">
          <div style="width:48px;height:48px;border-radius:var(--radius);background:var(--primary-l);color:var(--primary);display:flex;align-items:center;justify-content:center;font-size:1.25rem"><i class="fa fa-chair"></i></div>
          <div><div style="font-weight:800;font-size:.95rem">New Dine-in Order</div><div style="font-size:.78rem;color:var(--text-m)">Table ${tableNum} · Start taking order</div></div>
          <i class="fa fa-chevron-right" style="margin-left:auto;color:var(--text-l)"></i>
        </a>
        <a href="${BASE}pos/new-order/takeaway" style="display:flex;align-items:center;gap:1rem;padding:1rem;border:2px solid var(--border);border-radius:var(--radius);text-decoration:none;color:var(--text);transition:border-color .15s" onmouseover="this.style.borderColor='var(--info)'" onmouseout="this.style.borderColor='var(--border)'">
          <div style="width:48px;height:48px;border-radius:var(--radius);background:var(--info-l);color:var(--info);display:flex;align-items:center;justify-content:center;font-size:1.25rem"><i class="fa fa-bag-shopping"></i></div>
          <div><div style="font-weight:800;font-size:.95rem">Takeaway Order</div><div style="font-size:.78rem;color:var(--text-m)">Customer carries out</div></div>
          <i class="fa fa-chevron-right" style="margin-left:auto;color:var(--text-l)"></i>
        </a>
        <a href="${BASE}pos/new-order/delivery" style="display:flex;align-items:center;gap:1rem;padding:1rem;border:2px solid var(--border);border-radius:var(--radius);text-decoration:none;color:var(--text);transition:border-color .15s" onmouseover="this.style.borderColor='var(--success)'" onmouseout="this.style.borderColor='var(--border)'">
          <div style="width:48px;height:48px;border-radius:var(--radius);background:var(--success-l);color:var(--success);display:flex;align-items:center;justify-content:center;font-size:1.25rem"><i class="fa fa-motorcycle"></i></div>
          <div><div style="font-weight:800;font-size:.95rem">Delivery Order</div><div style="font-size:.78rem;color:var(--text-m)">Deliver to customer address</div></div>
          <i class="fa fa-chevron-right" style="margin-left:auto;color:var(--text-l)"></i>
        </a>
      </div>`;
    openSheet('sTableAction');
  } else if (status === 'occupied') {
    // Load orders for this table
    const title = document.getElementById('sOrdersTitle');
    const body  = document.getElementById('sOrdersBody');
    const foot  = document.getElementById('sOrdersFoot');
    title.textContent = 'Table ' + tableNum + ' — Active Orders';
    body.innerHTML = `<div style="text-align:center;padding:2rem;color:var(--text-m)"><i class="fa fa-spinner fa-spin"></i> Loading orders...</div>`;
    foot.innerHTML  = '';
    openSheet('sOrders');

    fetch(BASE + 'pos/table-orders/' + tableId)
      .then(r => r.json()).then(orders => {
        if (!orders.length) {
          body.innerHTML = `<div style="text-align:center;padding:2rem;color:var(--text-m)">No active orders found.</div>`;
          foot.innerHTML = `<a href="${BASE}pos/new-order/dine_in?table=${tableId}" style="flex:1;padding:.75rem;background:var(--primary);color:#fff;border:none;border-radius:var(--radius-sm);font-weight:800;text-align:center;text-decoration:none;font-size:.9rem"><i class="fa fa-plus"></i> New Order</a>`;
          return;
        }
        const statusClr = {pending:'var(--warning)',confirmed:'var(--info)',preparing:'var(--info)',ready:'var(--success)',served:'var(--success)'};
        body.innerHTML = orders.map(o => {
          const isPaid = o.payment_status === 'paid';
          const payBadge = isPaid
            ? `<span style="background:#dcfce7;color:#15803d;font-size:.6rem;font-weight:800;padding:.12rem .4rem;border-radius:20px;vertical-align:middle;margin-left:.3rem">✓ PAID</span>`
            : (parseInt(o.kot_count||0) > 0
              ? `<span style="background:#fff7ed;color:#c2410c;font-size:.6rem;font-weight:800;padding:.12rem .4rem;border-radius:20px;vertical-align:middle;margin-left:.3rem">KOT#${o.kot_count} · UNPAID</span>`
              : `<span style="background:#fef9c3;color:#854d0e;font-size:.6rem;font-weight:800;padding:.12rem .4rem;border-radius:20px;vertical-align:middle;margin-left:.3rem">UNPAID</span>`);
          return `<div class="active-order-item" style="border-left:3px solid ${isPaid?'var(--success)':'var(--warning)'}">
            <div style="flex:1;min-width:0">
              <div class="aoi-num">${o.order_number}${payBadge}</div>
              <div class="aoi-sub">${o.items_count} items · ${o.time_ago}</div>
            </div>
            <span class="aoi-status" style="background:${statusClr[o.status]||'var(--text-l)'}22;color:${statusClr[o.status]||'var(--text-m)'}">
              ${o.status.charAt(0).toUpperCase()+o.status.slice(1)}
            </span>
            <div style="text-align:right;margin:0 .5rem;flex-shrink:0">
              <div class="aoi-amt">₹${parseFloat(o.total_amount).toFixed(2)}</div>
            </div>
            <a href="${BASE}pos/order/${o.id}" class="aoi-open">Open</a>
          </div>`;
        }).join('');
        foot.innerHTML = `
          <a href="${BASE}pos/new-order/dine_in?table=${tableId}" style="flex:1;padding:.75rem;border:1.5px solid var(--border);border-radius:var(--radius-sm);font-weight:700;text-align:center;text-decoration:none;color:var(--text-2);font-size:.875rem;background:#fff">
            <i class="fa fa-plus"></i> Add Order
          </a>`;
      }).catch(() => {
        body.innerHTML = `<div style="color:var(--danger);text-align:center;padding:1.5rem">Could not load orders</div>`;
      });
  } else {
    // Reserved / Cleaning
    const body  = document.getElementById('sTableBody');
    const title = document.getElementById('sTableTitle');
    title.textContent = 'Table ' + tableNum + ' · ' + status.charAt(0).toUpperCase() + status.slice(1);
    body.innerHTML = `
      <div style="text-align:center;padding:1.5rem 0">
        <div style="font-size:3rem;margin-bottom:.75rem">${status==='reserved'?'📅':'🧹'}</div>
        <div style="font-weight:700;font-size:1rem;margin-bottom:.5rem">Table is ${status}</div>
        <div style="font-size:.85rem;color:var(--text-m);margin-bottom:1.5rem">You can still create an order if needed.</div>
        <a href="${BASE}pos/new-order/dine_in?table=${tableId}" style="display:inline-flex;align-items:center;gap:.5rem;padding:.75rem 1.5rem;background:var(--primary);color:#fff;border-radius:var(--radius-sm);font-weight:800;text-decoration:none">
          <i class="fa fa-utensils"></i> Start Order Anyway
        </a>
      </div>`;
    openSheet('sTableAction');
  }
}

// Auto refresh active orders count every 30s
setInterval(() => {
  fetch(BASE + 'pos/active-orders')
    .then(r => r.json()).then(orders => {
      const el = document.getElementById('openOrdersStat');
      if (el) el.textContent = orders.length;
    }).catch(() => {});
}, 30000);
</script>

<?php $this->endSection(); ?>
