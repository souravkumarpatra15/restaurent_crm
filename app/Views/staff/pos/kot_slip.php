<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>KOT #<?= esc($kotNumber) ?></title>
<style>
/* ── Web KOT Slip — 80mm thermal feel ── */
* { box-sizing:border-box; margin:0; padding:0; }
body {
  font-family: 'Courier New', Courier, monospace;
  background: #f0f0f0;
  display: flex; flex-direction: column;
  align-items: center; justify-content: flex-start;
  min-height: 100vh; padding: 1rem;
  gap: .875rem;
}

/* Print action bar (hidden on print) */
.print-bar {
  background: #1A202C; color: #fff;
  padding: .75rem 1.25rem; border-radius: 10px;
  display: flex; align-items: center; justify-content: space-between;
  width: 100%; max-width: 320px; gap: .5rem;
}
.print-bar-title { font-family: sans-serif; font-weight: 700; font-size: .9rem; }
.print-btn {
  padding: .45rem 1rem; background: #FF6B35; color: #fff; border: none;
  border-radius: 7px; font-weight: 700; cursor: pointer; font-size: .82rem;
  display: flex; align-items: center; gap: .35rem;
}
.print-btn:hover { background: #E85A24; }
.close-btn {
  padding: .45rem .875rem; background: rgba(255,255,255,.1); color: #fff;
  border: none; border-radius: 7px; font-weight: 600; cursor: pointer; font-size: .82rem;
}

/* Slip container */
.slip {
  background: #fff;
  width: 100%; max-width: 300px;
  padding: .875rem .75rem;
  border-radius: 4px;
  box-shadow: 0 2px 12px rgba(0,0,0,.12);
  font-size: 12px; line-height: 1.4;
  position: relative;
}

/* Tear marks */
.slip::before, .slip::after {
  content: '';
  display: block; height: 1px; width: 100%;
  border-top: 2px dashed #999;
  margin: .625rem 0;
}

/* Header */
.slip-head { text-align: center; margin-bottom: .5rem; }
.slip-title {
  font-size: 20px; font-weight: 900; letter-spacing: .08em;
  border: 3px solid #000; display: inline-block;
  padding: .25rem 1.5rem; margin-bottom: .5rem;
}
.slip-restaurant { font-size: 13px; font-weight: 700; }
.slip-branch     { font-size: 11px; color: #555; }

/* Info row */
.slip-info {
  display: flex; flex-direction: column; gap: .2rem;
  margin: .5rem 0; font-size: 11.5px;
}
.slip-info-row { display: flex; justify-content: space-between; }
.slip-info-row .lbl { color: #555; }
.slip-info-row .val { font-weight: 700; }

/* KOT number big */
.slip-kot-num {
  text-align: center; font-size: 28px; font-weight: 900;
  letter-spacing: .05em; color: #000; margin: .5rem 0;
  background: #f5f5f5; padding: .35rem; border-radius: 3px;
  border: 1.5px solid #ddd;
}

/* Type badge */
.slip-type {
  text-align: center; margin-bottom: .625rem;
}
.slip-type-badge {
  display: inline-block; font-size: 11px; font-weight: 900;
  letter-spacing: .12em; text-transform: uppercase;
  padding: .25rem .875rem; border: 2px solid #000; border-radius: 3px;
}

/* Divider */
.div-line { border-top: 1px dashed #aaa; margin: .5rem 0; }
.div-solid { border-top: 2px solid #000; margin: .5rem 0; }
.div-double { border-top: 3px double #000; margin: .5rem 0; }

/* Items */
.items-hdr {
  display: flex; font-size: 11px; font-weight: 900;
  text-transform: uppercase; letter-spacing: .06em;
  margin-bottom: .3rem; padding-bottom: .2rem;
  border-bottom: 1.5px solid #000;
}
.items-hdr .qh { width: 36px; text-align: center; }
.items-hdr .nh { flex: 1; }

.item { margin-bottom: .4rem; }
.item-main { display: flex; align-items: flex-start; gap: .2rem; }
.item-qty  {
  font-size: 16px; font-weight: 900; min-width: 36px;
  text-align: center; flex-shrink: 0; color: #000;
}
.item-name { flex: 1; font-size: 13px; font-weight: 700; line-height: 1.3; }
.item-variant { font-size: 10.5px; color: #444; margin-left: 36px; }
.item-addons  { font-size: 10.5px; color: #444; margin-left: 36px; }
.item-note    { font-size: 10.5px; font-weight: 700; color: #000; margin-left: 36px; background: #ffe; padding: 1px 4px; border-radius: 2px; }

/* Kitchen note */
.kitchen-note {
  background: #fff3cd; border: 1.5px solid #ffc107;
  border-radius: 3px; padding: .4rem .5rem; margin-top: .5rem;
  font-size: 11.5px; font-weight: 700;
}
.kitchen-note-label { font-size: 10px; text-transform: uppercase; letter-spacing: .06em; color: #888; margin-bottom: .15rem; }

/* Footer */
.slip-footer { text-align: center; margin-top: .5rem; font-size: 10.5px; color: #666; }
.slip-time   { font-size: 10.5px; text-align: center; color: #888; margin-top: .3rem; }

/* Checkbox section (for kitchen staff to tick off) */
.tick-section {
  margin-top: .625rem; border: 1.5px dashed #aaa; border-radius: 3px;
  padding: .4rem .5rem; font-size: 10.5px;
}
.tick-section-title { font-weight: 700; margin-bottom: .25rem; font-size: 11px; }
.tick-row { display: flex; align-items: center; gap: .375rem; margin-bottom: .2rem; }
.tick-box { width: 13px; height: 13px; border: 1.5px solid #000; border-radius: 2px; flex-shrink: 0; }

/* Print styles */
@media print {
  body { background: #fff; padding: 0; }
  .print-bar { display: none !important; }
  .slip { box-shadow: none; max-width: 100%; margin: 0; }
}
</style>
</head>
<body>

<!-- Action Bar -->
<div class="print-bar">
  <span class="print-bar-title">🍽 KOT Slip Preview</span>
  <div style="display:flex;gap:.4rem">
    <button class="print-btn" onclick="window.print()">🖨 Print</button>
    <button class="close-btn" onclick="window.close()">✕</button>
  </div>
</div>

<!-- KOT Slip -->
<div class="slip">

  <!-- Restaurant Header -->
  <div class="slip-head">
    <div class="slip-title">K O T</div>
    <div class="slip-restaurant"><?= esc($restaurant['name']) ?></div>
    <div class="slip-branch"><?= esc($branch['name']) ?></div>
  </div>

  <!-- KOT Number Big -->
  <div class="slip-kot-num"><?= esc($kotNumber) ?></div>

  <!-- Order Type -->
  <div class="slip-type">
    <span class="slip-type-badge">
      <?php $typeIcons=['dine_in'=>'🪑 DINE-IN','takeaway'=>'🛍 TAKEAWAY','delivery'=>'🛵 DELIVERY']; ?>
      <?= $typeIcons[$order['order_type']] ?? strtoupper(str_replace('_',' ',$order['order_type'])) ?>
    </span>
  </div>

  <!-- Info Row -->
  <div class="div-line"></div>
  <div class="slip-info">
    <div class="slip-info-row">
      <span class="lbl">Order#</span>
      <span class="val"><?= esc($order['order_number']) ?></span>
    </div>
    <?php if ($order['table']): ?>
    <div class="slip-info-row">
      <span class="lbl">Table</span>
      <span class="val" style="font-size:15px"><?= esc($order['table']['table_number']) ?></span>
    </div>
    <?php endif; ?>
    <?php if ($order['no_of_guests'] > 1): ?>
    <div class="slip-info-row">
      <span class="lbl">Guests</span>
      <span class="val"><?= $order['no_of_guests'] ?></span>
    </div>
    <?php endif; ?>
    <?php if ($order['customer_name']): ?>
    <div class="slip-info-row">
      <span class="lbl">Customer</span>
      <span class="val"><?= esc($order['customer_name']) ?></span>
    </div>
    <?php endif; ?>
    <div class="slip-info-row">
      <span class="lbl">Time</span>
      <span class="val"><?= date('h:i A', strtotime($order['created_at'])) ?></span>
    </div>
    <div class="slip-info-row">
      <span class="lbl">Date</span>
      <span class="val"><?= date('d M Y') ?></span>
    </div>
    <div class="slip-info-row">
      <span class="lbl">Cashier</span>
      <span class="val"><?= esc($order['cashier_name'] ?? session('user_name') ?? 'Staff') ?></span>
    </div>
  </div>
  <div class="div-double"></div>

  <!-- Items Header -->
  <div class="items-hdr">
    <span class="qh">Qty</span>
    <span class="nh">Item</span>
  </div>

  <!-- Items List -->
  <?php foreach ($order['items'] as $item): ?>
  <div class="item">
    <div class="item-main">
      <div class="item-qty"><?= $item['quantity'] ?>×</div>
      <div class="item-name"><?= esc($item['name']) ?></div>
    </div>
    <?php if (!empty($item['variant_name'])): ?>
    <div class="item-variant">└ <?= esc($item['variant_name']) ?></div>
    <?php endif; ?>
    <?php if (!empty($item['addons'])): foreach ($item['addons'] as $addon): ?>
    <div class="item-addons">+ <?= esc($addon['name']) ?></div>
    <?php endforeach; endif; ?>
    <?php if (!empty($item['notes'])): ?>
    <div class="item-note">⚡ <?= esc($item['notes']) ?></div>
    <?php endif; ?>
  </div>
  <?php endforeach; ?>

  <div class="div-solid"></div>
  <div style="font-size:11px;color:#666;text-align:right"><?= count($order['items']) ?> item type(s)</div>

  <!-- Kitchen Note -->
  <?php if (!empty($order['kitchen_notes'])): ?>
  <div class="kitchen-note">
    <div class="kitchen-note-label">⚡ Chef Note</div>
    <?= esc($order['kitchen_notes']) ?>
  </div>
  <?php endif; ?>

  <!-- Kitchen Tick-off Section -->
  <div class="tick-section" style="margin-top:.625rem">
    <div class="tick-section-title">✓ Kitchen Checklist</div>
    <?php foreach ($order['items'] as $item): ?>
    <div class="tick-row">
      <div class="tick-box"></div>
      <span style="font-size:11px"><?= $item['quantity'] ?>× <?= esc($item['name']) ?><?= $item['variant_name'] ? ' ('.$item['variant_name'].')' : '' ?></span>
    </div>
    <?php endforeach; ?>
  </div>

  <div class="div-line"></div>

  <!-- Footer -->
  <div class="slip-footer">
    <?php if ($order['payment_status'] === 'paid'): ?>
    <div style="background:#d4edda;border:1.5px solid #28a745;border-radius:3px;padding:.3rem;font-weight:900;font-size:12px;color:#155724;margin-bottom:.35rem">✅ PAID — Serve Immediately</div>
    <?php else: ?>
    <div style="background:#fff3cd;border:1.5px solid #ffc107;border-radius:3px;padding:.3rem;font-weight:900;font-size:12px;color:#856404;margin-bottom:.35rem">⏳ Pending Payment</div>
    <?php endif; ?>
    <div>Powered by RestOne</div>
  </div>
  <div class="slip-time">Printed: <?= date('d M Y h:i:s A') ?></div>

</div>

<!-- Second copy label -->
<div style="font-family:sans-serif;font-size:.75rem;color:#888;text-align:center">
  ↑ Tear along dashed line · Kitchen Copy
</div>

<script>
// Auto-print if opened with ?autoprint=1
<?php if (service('request')->getGet('autoprint')): ?>
window.onload = function() { setTimeout(window.print, 400); };
<?php endif; ?>
</script>
</body>
</html>
