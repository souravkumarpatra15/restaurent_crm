<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Bill #<?= esc($invoice['invoice_number'] ?? $order['order_number']) ?></title>
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

.invnum { font-size: 14px; font-weight: 900; }

.info-t { display: flex; flex-direction: column; gap: .18rem; margin: .4rem 0; }
.info-r { display: flex; justify-content: space-between; font-size: 11px; }
.info-r .l { color: #555; }
.info-r .v { font-weight: 700; }

/* Items table */
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

/* Totals */
.tot-t  { display: flex; flex-direction: column; gap: .18rem; margin: .4rem 0; }
.tot-r  { display: flex; justify-content: space-between; font-size: 11.5px; }
.tot-r.grand {
  font-size: 14px; font-weight: 900;
  border-top: 2px solid #000; padding-top: .35rem; margin-top: .2rem;
}
.tot-r.disc  { color: #155724; }

/* Payment */
.pay-r  { display: flex; justify-content: space-between; font-size: 11px; margin: .15rem 0; }
.paid-stamp {
  text-align: center; font-size: 13px; font-weight: 900;
  border: 2.5px solid #28a745; border-radius: 4px;
  color: #155724; padding: .3rem; margin-top: .4rem; letter-spacing: .08em;
}

/* QR / UPI area */
.upi-box {
  border: 1.5px dashed #aaa; border-radius: 4px;
  padding: .4rem; text-align: center; margin: .4rem 0;
  font-size: 10.5px; color: #555;
}
.upi-box .uid { font-weight: 700; font-size: 11.5px; color: #000; margin-top: .15rem; }

.footer { text-align: center; font-size: 10.5px; color: #555; margin-top: .5rem; }
.footer .big { font-size: 13px; font-weight: 700; color: #000; }

@media print {
  body { background: #fff; padding: 0; }
  .print-bar { display: none !important; }
  .slip { box-shadow: none; max-width: 100%; }
}
</style>
</head>
<body>

<!-- Action Bar -->
<div class="print-bar">
  <span class="print-bar-title">🧾 Bill Preview</span>
  <div style="display:flex;gap:.4rem">
    <button class="pbtn pbtn-print" onclick="window.print()">🖨 Print Bill</button>
    <button class="pbtn pbtn-close" onclick="window.close()">✕</button>
  </div>
</div>

<!-- Bill Slip -->
<div class="slip">

  <!-- Header -->
  <div class="c" style="margin-bottom:.5rem">
    <?php if (!empty($restaurant['receipt_header'])): ?>
      <?php foreach (explode("\n", $restaurant['receipt_header']) as $line): ?>
      <div style="font-size:11px;color:#555"><?= esc(trim($line)) ?></div>
      <?php endforeach; ?>
    <?php endif; ?>
    <div class="rname"><?= esc($restaurant['name']) ?></div>
    <div class="bname"><?= esc($branch['name']) ?></div>
    <?php if ($branch['address']): ?>
    <div class="addr"><?= esc($branch['address']) ?><?= $branch['city'] ? ', '.$branch['city'] : '' ?></div>
    <?php endif; ?>
    <?php if ($branch['phone']): ?><div class="addr">Ph: <?= esc($branch['phone']) ?></div><?php endif; ?>
    <?php if ($restaurant['gst_number']): ?><div class="addr b">GSTIN: <?= esc($restaurant['gst_number']) ?></div><?php endif; ?>
    <?php if ($restaurant['fssai_number']): ?><div class="addr">FSSAI: <?= esc($restaurant['fssai_number']) ?></div><?php endif; ?>
  </div>

  <div class="div-d"></div>

  <!-- Invoice Info -->
  <div class="c invnum">
    <?php if ($invoice): ?>
    Invoice #<?= esc($invoice['invoice_number']) ?>
    <?php else: ?>
    Order #<?= esc($order['order_number']) ?>
    <?php endif; ?>
  </div>
  <div class="info-t" style="margin-top:.35rem">
    <div class="info-r"><span class="l">Date</span><span class="v"><?= date('d M Y') ?></span></div>
    <div class="info-r"><span class="l">Time</span><span class="v"><?= date('h:i A') ?></span></div>
    <div class="info-r">
      <span class="l">Type</span>
      <span class="v">
        <?php $tl=['dine_in'=>'Dine-in','takeaway'=>'Takeaway','delivery'=>'Delivery']; ?>
        <?= $tl[$order['order_type']] ?? ucfirst($order['order_type']) ?>
      </span>
    </div>
    <?php if ($order['table']): ?>
    <div class="info-r"><span class="l">Table</span><span class="v bb"><?= esc($order['table']['table_number']) ?></span></div>
    <?php endif; ?>
    <?php if ($order['customer_name']): ?>
    <div class="info-r"><span class="l">Customer</span><span class="v"><?= esc($order['customer_name']) ?></span></div>
    <?php endif; ?>
    <?php if ($order['customer_phone']): ?>
    <div class="info-r"><span class="l">Phone</span><span class="v"><?= esc($order['customer_phone']) ?></span></div>
    <?php endif; ?>
    <?php if (!empty($order['customer']) && !empty($order['customer']['gstin'])): ?>
    <div class="info-r"><span class="l">GSTIN</span><span class="v"><?= esc($order['customer']['gstin']) ?></span></div>
    <?php endif; ?>
  </div>

  <div class="div-s"></div>

  <!-- Items Header -->
  <div class="items-hdr">
    <span class="ih-nm">Item</span>
    <span class="ih-qt">Qty</span>
    <span class="ih-rt">Rate</span>
    <span class="ih-am">Amt</span>
  </div>

  <!-- Items -->
  <?php $sym = $restaurant['currency_symbol'] ?? '₹'; ?>
  <?php foreach ($order['items'] as $item): ?>
  <div class="item">
    <div class="item-row">
      <span class="item-nm"><?= esc($item['name']) ?></span>
      <span class="item-qt"><?= $item['quantity'] ?></span>
      <span class="item-rt"><?= $sym ?><?= number_format($item['unit_price'],2) ?></span>
      <span class="item-am"><?= $sym ?><?= number_format($item['total_price'],2) ?></span>
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

  <div class="div-s"></div>

  <!-- Totals -->
  <div class="tot-t">
    <div class="tot-r"><span>Subtotal</span><span><?= $sym ?><?= number_format($order['subtotal'],2) ?></span></div>
    <?php if ($order['discount_amount'] > 0): ?>
    <div class="tot-r disc"><span>Discount</span><span>-<?= $sym ?><?= number_format($order['discount_amount'],2) ?></span></div>
    <?php endif; ?>
    <?php if ($order['cgst_amount'] > 0): ?>
    <div class="tot-r"><span>CGST (<?= $order['cgst_percent']??2.5 ?>%)</span><span><?= $sym ?><?= number_format($order['cgst_amount'],2) ?></span></div>
    <div class="tot-r"><span>SGST (<?= $order['sgst_percent']??2.5 ?>%)</span><span><?= $sym ?><?= number_format($order['sgst_amount'],2) ?></span></div>
    <?php elseif ($order['tax_amount'] > 0): ?>
    <div class="tot-r"><span>GST</span><span><?= $sym ?><?= number_format($order['tax_amount'],2) ?></span></div>
    <?php endif; ?>
    <?php if ($order['service_charge'] > 0): ?>
    <div class="tot-r"><span>Service Charge</span><span><?= $sym ?><?= number_format($order['service_charge'],2) ?></span></div>
    <?php endif; ?>
    <?php if ($order['delivery_charge'] > 0): ?>
    <div class="tot-r"><span>Delivery</span><span><?= $sym ?><?= number_format($order['delivery_charge'],2) ?></span></div>
    <?php endif; ?>
    <?php if ($order['round_off'] != 0): ?>
    <div class="tot-r"><span>Round Off</span><span><?= $order['round_off']>0?'+':'' ?><?= $sym ?><?= number_format($order['round_off'],2) ?></span></div>
    <?php endif; ?>
    <div class="tot-r grand"><span>TOTAL</span><span><?= $sym ?><?= number_format($order['total_amount'],2) ?></span></div>
  </div>

  <div class="div-d"></div>

  <!-- Payments -->
  <div class="b" style="font-size:11px;margin-bottom:.2rem">Payment Details:</div>
  <?php foreach ($order['payments'] as $pmt): ?>
  <div class="pay-r">
    <span><?= ucfirst(str_replace('_',' ',$pmt['payment_method'])) ?><?= $pmt['payment_reference'] ? ' ('.$pmt['payment_reference'].')' : '' ?></span>
    <span class="b"><?= $sym ?><?= number_format($pmt['amount'],2) ?></span>
  </div>
  <?php endforeach; ?>
  <?php
  $totalPaid = array_sum(array_column($order['payments'],'amount'));
  $change = $totalPaid - $order['total_amount'];
  if ($change > 0.009): ?>
  <div class="pay-r"><span>Change Returned</span><span class="b"><?= $sym ?><?= number_format($change,2) ?></span></div>
  <?php endif; ?>

  <?php if ($order['payment_status'] === 'paid'): ?>
  <div class="paid-stamp">✓ PAID</div>
  <?php endif; ?>

  <!-- UPI QR hint -->
  <?php if (!empty($restaurant['upi_id'])): ?>
  <div class="upi-box">
    Scan to Pay / Review
    <div class="uid"><?= esc($restaurant['upi_id']) ?></div>
  </div>
  <?php endif; ?>

  <div class="div-d"></div>

  <!-- Footer -->
  <div class="footer">
    <?php if (!empty($restaurant['receipt_footer'])): ?>
      <?php foreach (explode("\n", $restaurant['receipt_footer']) as $fline): ?>
      <div><?= esc(trim($fline)) ?></div>
      <?php endforeach; ?>
    <?php else: ?>
    <div class="big">Thank You!</div>
    <div>Visit Us Again 🙏</div>
    <?php endif; ?>
    <div style="margin-top:.3rem">Powered by RestOne</div>
    <div style="color:#aaa;font-size:10px;margin-top:.2rem">Printed: <?= date('d M Y h:i:s A') ?></div>
  </div>

</div>

<script>
<?php if (service('request')->getGet('autoprint')): ?>
window.onload = function() { setTimeout(window.print, 400); };
<?php endif; ?>
</script>
</body>
</html>
