<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Combined Bill — <?= count($orders) ?> orders</title>
<style>
* { box-sizing:border-box; margin:0; padding:0; }
body {
  font-family: 'Courier New', Courier, monospace;
  background: #f0f0f0;
  display: flex; flex-direction: column;
  align-items: center; min-height: 100vh; padding: 1rem; gap: .875rem;
}
.print-bar {
  background: #1A202C; color: #fff;
  padding: .75rem 1.25rem; border-radius: 10px;
  display: flex; align-items: center; justify-content: space-between;
  width: 100%; max-width: 320px; gap: .5rem;
}
.print-bar-title { font-family: sans-serif; font-weight: 700; font-size: .9rem; }
.pbtn {
  padding: .45rem 1rem; border: none; border-radius: 7px;
  font-weight: 700; cursor: pointer; font-size: .82rem;
  display: flex; align-items: center; gap: .35rem;
}
.pbtn-print { background: #FF6B35; color: #fff; }
.pbtn-close { background: rgba(255,255,255,.1); color: #fff; }

.slip {
  background: #fff; width: 100%; max-width: 300px;
  padding: .875rem .75rem; border-radius: 4px;
  box-shadow: 0 2px 12px rgba(0,0,0,.12);
  font-size: 11.5px; line-height: 1.45;
}
.c  { text-align: center; }
.b  { font-weight: 700; }
.bb { font-weight: 900; }
.div-d { border-top: 2px dashed #aaa; margin: .5rem 0; }
.div-s { border-top: 1.5px solid #000; margin: .5rem 0; }
.div-2 { border-top: 3px double #000; margin: .5rem 0; }

.rname  { font-size: 15px; font-weight: 900; letter-spacing: .03em; }
.bname  { font-size: 12px; font-weight: 700; margin-top: .15rem; }
.addr   { font-size: 10.5px; color: #555; }

.info-t { display: flex; flex-direction: column; gap: .18rem; margin: .4rem 0; }
.info-r { display: flex; justify-content: space-between; font-size: 11px; }
.info-r .l { color: #555; padding-right: .5rem; }
.info-r .v { font-weight: 700; }

.items-hdr {
  display: flex; font-size: 10px; font-weight: 900;
  text-transform: uppercase; letter-spacing: .05em;
  border-bottom: 1.5px solid #000; padding-bottom: .25rem; margin-bottom: .3rem;
}
.ih-nm { flex: 1; }
.ih-qt { width: 28px; text-align: center; }
.ih-rt { width: 48px; text-align: right; }
.ih-am { width: 52px; text-align: right; }

.item { margin-bottom: .35rem; }
.item-row { display: flex; font-size: 11.5px; }
.item-nm  { flex: 1; font-weight: 600; line-height: 1.3; }
.item-qt  { width: 28px; text-align: center; }
.item-rt  { width: 48px; text-align: right; }
.item-am  { width: 52px; text-align: right; font-weight: 700; }
.item-sub { font-size: 10.5px; color: #444; padding-left: .2rem; }

.ordline { display:flex; justify-content:space-between; font-weight:900; font-size:11.5px; background:#F7F7F7; padding:.25rem .3rem; border-radius:3px; margin:.4rem 0 .25rem; }

/* Totals */
.tot-t  { display: flex; flex-direction: column; gap: .18rem; margin: .4rem 0; }
.tot-r  { display: flex; justify-content: space-between; font-size: 11.5px; }
.tot-r.grand {
  font-size: 14px; font-weight: 900;
  border-top: 2px solid #000; padding-top: .35rem; margin-top: .2rem;
}
.tot-r.disc  { color: #155724; }

/* Payment & QR */
.pay-r  { display: flex; justify-content: space-between; font-size: 11px; margin: .15rem 0; }
.paid-stamp {
  text-align: center; font-size: 13px; font-weight: 900;
  border: 2.5px solid #28a745; border-radius: 4px;
  color: #155724; padding: .3rem; margin-top: .4rem; letter-spacing: .08em;
}
.footer { text-align: center; font-size: 10.5px; color: #555; margin-top: .5rem; }
.footer .big { font-size: 13px; font-weight: 700; color: #000; }

@media print {
  body { background: #fff; padding: 0; }
  .print-bar { display: none !important; }
  .slip { box-shadow: none; max-width: 100%; }
}
</style>
<style>
<?php $wide = ($branch['printer_type'] ?? 'thermal_80mm') === 'thermal_80mm'; ?>
<?php if ($wide): ?>
.slip { max-width: 380px; font-size: 13.5px; }
.rname { font-size: 17px; }
.item-row { font-size: 13px; }
.tot-r { font-size: 13px; }
.tot-r.grand { font-size: 15.5px; }
<?php endif; ?>
</style>
</head>
<body>

<div class="print-bar">
  <span class="print-bar-title">🧾 Combined Bill</span>
  <div style="display:flex;gap:.4rem">
    <button class="pbtn pbtn-print" onclick="window.print()">🖨 Print</button>
    <button class="pbtn pbtn-close" onclick="window.close()">✕</button>
  </div>
</div>

<?php $sym = $restaurant['currency_symbol'] ?? '₹';
      $grand = 0; $firstFeedback = null;
      foreach ($orders as $o) { $grand += (float)$o['total_amount']; if (!$firstFeedback && !empty($o['feedback_token'])) $firstFeedback = $o['feedback_token']; } ?>
<?php $single = count($orders) === 1; $tl=['dine_in'=>'Dine-in','takeaway'=>'Takeaway','delivery'=>'Delivery']; ?>

<div class="slip">

  <!-- Header -->
  <div class="c" style="margin-bottom:.5rem">
    <?php if (!empty($restaurant['receipt_header'])): ?>
      <?php foreach (explode("\n", $restaurant['receipt_header']) as $line): ?>
      <div style="font-size:11px;color:#555"><?= esc(trim($line)) ?></div>
      <?php endforeach; ?>
    <?php endif; ?>
    <div class="rname"><?= esc($restaurant['name'] ?? '') ?></div>
    <div class="bname"><?= esc($branch['name'] ?? '') ?></div>
    <?php if (!empty($branch['address'])): ?>
    <div class="addr"><?= esc($branch['address']) ?><?= !empty($branch['city']) ? ', '.$branch['city'] : '' ?></div>
    <?php endif; ?>
    <?php if (!empty($branch['phone'])): ?><div class="addr">Ph: <?= esc($branch['phone']) ?></div><?php endif; ?>
    <?php if (!empty($restaurant['gst_number'])): ?><div class="addr b">GSTIN: <?= esc($restaurant['gst_number']) ?></div><?php endif; ?>
  </div>

  <div class="div-d"></div>

  <div class="c invnum">
    <?= $single ? 'Bill' : 'COMBINED BILL (' . count($orders) . ' orders)' ?>
  </div>
  <div class="info-t" style="margin-top:.35rem">
    <div class="info-r"><span class="l">Date</span><span class="v"><?= date('d M Y') ?></span></div>
    <div class="info-r"><span class="l">Time</span><span class="v"><?= date('h:i A') ?></span></div>
    <div class="info-r"><span class="l">Type</span><span class="v"><?= $tl[$orders[0]['order_type']] ?? ucfirst($orders[0]['order_type']) ?></span></div>
    <?php if (!empty($orders[0]['table'])): ?>
    <div class="info-r"><span class="l">Table</span><span class="v bb"><?= esc($orders[0]['table']['table_number']) ?></span></div>
    <?php endif; ?>
    <?php if (!empty($orders[0]['customer_name'])): ?>
    <div class="info-r"><span class="l">Guest</span><span class="v"><?= esc($orders[0]['customer_name']) ?></span></div>
    <?php endif; ?>
  </div>

  <?php foreach ($orders as $o): ?>
  <div class="div-s"></div>
  <div class="ordline">
    <span><?= esc($o['order_number']) ?></span>
    <span><?= $sym . number_format((float)$o['total_amount'], 2) ?></span>
  </div>
  <div class="items-hdr">
    <span class="ih-nm">Item</span>
    <span class="ih-qt">Qty</span>
    <span class="ih-rt">Rate</span>
    <span class="ih-am">Amt</span>
  </div>
  <?php foreach (($o['items'] ?? []) as $item): ?>
  <div class="item">
    <div class="item-row">
      <span class="item-nm"><?= esc($item['name']) ?></span>
      <span class="item-qt"><?= rtrim(rtrim(number_format((float)$item['quantity'], 2), '0'), '.') ?></span>
      <span class="item-rt"><?= number_format((float)$item['unit_price'], 2) ?></span>
      <span class="item-am"><?= number_format((float)$item['total_price'], 2) ?></span>
    </div>
    <?php if (!empty($item['variant_name'])): ?>
    <div class="item-sub">  <?= esc($item['variant_name']) ?></div>
    <?php endif; ?>
    <?php if (!empty($item['addons'])): foreach ($item['addons'] as $addon): ?>
    <div class="item-sub">  + <?= esc($addon['name']) ?><?= $addon['price']>0?' ('.$sym.number_format($addon['price'],2).')':'' ?></div>
    <?php endforeach; endif; ?>
    <?php if (!empty($item['notes'])): ?>
    <div class="item-sub" style="font-style:italic">  * <?= esc($item['notes']) ?></div>
    <?php endif; ?>
  </div>
  <?php endforeach; ?>
  <?php endforeach; ?>

  <div class="div-2"></div>

  <!-- Grand total -->
  <div class="tot-r grand"><span>GRAND TOTAL</span><span><?= $sym . number_format($grand, 2) ?></span></div>

  <div class="paid-stamp"><?= strtoupper($orders[0]['payment_status']) ?></div>

  <?php if ($single && !empty($orders[0]['subtotal'])): ?>
  <div class="tot-t" style="margin-top:.3rem">
    <div class="info-r"><span class="l">Subtotal</span><span class="v"><?= $sym . number_format((float)$orders[0]['subtotal'], 2) ?></span></div>
    <?php if ((float)$orders[0]['discount_amount'] > 0): ?>
    <div class="tot-r disc"><span>Discount</span><span>-<?= $sym . number_format((float)$orders[0]['discount_amount'], 2) ?></span></div>
    <?php endif; ?>
    <?php if ((float)$orders[0]['delivery_charge'] > 0): ?>
    <div class="info-r"><span class="l">Delivery</span><span class="v"><?= $sym . number_format((float)$orders[0]['delivery_charge'], 2) ?></span></div>
    <?php endif; ?>
    <div class="info-r"><span class="l">GST</span><span class="v"><?= $sym . number_format((float)$orders[0]['tax_amount'], 2) ?></span></div>
  </div>
  <?php endif; ?>

  <!-- Feedback QR (single receipt scan for the table) -->
  <?php if ($firstFeedback): ?>
  <?php $fbUrl = base_url('feedback/' . $firstFeedback); ?>
  <div class="div-d"></div>
  <div class="c" style="margin:.25rem 0">
    <div style="font-size:10.5px;color:#555">Enjoyed the food? Scan & tell us</div>
    <div style="display:flex;justify-content:center;margin-top:.35rem">
      <div id="qrbox"></div>
    </div>
  </div>
  <?php endif; ?>

  <div class="div-d"></div>
  <div class="footer">
    <div class="big">Thank You! Visit Again!</div>
    <div><?= empty($whitelabel) ? 'Powered by DinoviX POS' : 'Thank you!' ?></div>
  </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
<?php if ($firstFeedback): ?>
new QRCode(document.getElementById('qrbox'), { text: '<?= $fbUrl ?>', width: 96, height: 96, correctLevel: QRCode.CorrectLevel.M });
<?php endif; ?>
<?php if (service('request')->getGet('autoprint')): ?>
window.addEventListener('load', () => setTimeout(() => window.print(), 400));
<?php endif; ?>
</script>
</body>
</html>
