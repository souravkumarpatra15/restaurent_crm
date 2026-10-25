<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Z-Report — <?= date('d M Y h:i A', strtotime($shift['closed_at'] ?? 'now')) ?></title>
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: 'Courier New', Courier, monospace; background: #f0f0f0; display: flex; flex-direction: column; align-items: center; min-height: 100vh; padding: 1rem; gap: .875rem; font-size: 12px; }
  .print-bar { background: #1A202C; color: #fff; padding: .6rem 1rem; border-radius: 8px; display: flex; align-items: center; gap: .5rem; width: 100%; max-width: 320px; justify-content: space-between; }
  .print-bar button { padding: .4rem .9rem; border: none; border-radius: 6px; font-weight: 700; cursor: pointer; background: #FF6B35; color: #fff; }
  .slip { background: #fff; width: 100%; max-width: 320px; padding: .9rem .75rem; box-shadow: 0 2px 12px rgba(0,0,0,.12); line-height: 1.5; }
  .c { text-align: center; } b, strong { font-weight: 900; }
  .dash { border-top: 2px dashed #999; margin: .5rem 0; }
  .r { display: flex; justify-content: space-between; font-size: 12px; }
  .r .l { color: #444; }
  .grand { font-size: 14px; font-weight: 900; border-top: 2px solid #000; padding-top: .3rem; margin-top: .3rem; display: flex; justify-content: space-between; }
  @media print { body { background:#fff; padding:0; } .print-bar { display:none !important; } .slip { box-shadow:none; max-width:100%; } }
</style>
</head>
<body>
<div class="print-bar">
  <strong>Z-REPORT</strong>
  <button onclick="window.print()">🖨 Print</button>
</div>

<div class="slip">
  <div class="c">
    <div style="font-size:15px;font-weight:900"><?= esc($restaurant['name'] ?? '') ?></div>
    <div><?= esc($shift['branch_name'] ?? '') ?></div>
    <div style="font-size:11px;color:#555">SHIFT Z-READ REPORT</div>
  </div>
  <div class="dash"></div>
  <div class="r"><span class="l">Shift</span><span><b>#<?= $shift['id'] ?></b></span></div>
  <div class="r"><span class="l">Cashier</span><span><?= esc($shift['staff_name'] ?? '—') ?></span></div>
  <div class="r"><span class="l">Open</span><span><?= date('d M h:i A', strtotime($shift['opened_at'])) ?></span></div>
  <div class="r"><span class="l">Close</span><span><?= !empty($shift['closed_at']) ? date('d M h:i A', strtotime($shift['closed_at'])) : 'OPEN' ?></span></div>
  <div class="dash"></div>

  <div class="r"><span class="l">Bills</span><span><b><?= (int)($stats['bills'] ?? 0) ?></b></span></div>
  <div class="r"><span class="l">Gross Sales</span><span><?= '₹' . number_format((float)($stats['sales'] ?? 0), 2) ?></span></div>
  <div class="r"><span class="l">Discounts</span><span>−₹<?= number_format((float)($stats['discounts'] ?? 0), 2) ?></span></div>
  <div class="r"><span class="l">Service Charge</span><span>₹<?= number_format((float)($stats['service'] ?? 0), 2) ?></span></div>
  <div class="r"><span class="l">Tax Collected</span><span>₹<?= number_format((float)($stats['tax'] ?? 0), 2) ?></span></div>
  <div class="r"><span class="l">Cancelled Bills</span><span><?= (int)$cancels ?></span></div>

  <div class="dash"></div>
  <div style="font-weight:900">PAYMENT SPLIT</div>
  <?php foreach ($methods as $m): ?>
  <div class="r"><span class="l"><?= ucfirst($m['payment_method']) ?> (<?= (int)$m['count'] ?>)</span><span>₹<?= number_format((float)$m['total'], 2) ?></span></div>
  <?php endforeach; ?>
  <?php if (empty($methods)): ?><div class="r"><span class="l">—</span><span>₹0.00</span></div><?php endif; ?>
  <div class="grand"><span>TOTAL</span><span>₹<?= number_format((float)$methodTotal, 2) ?></span></div>

  <div class="dash"></div>
  <div style="font-weight:900">CASH DRAWER</div>
  <div class="r"><span class="l">Opening</span><span>₹<?= number_format((float)$shift['opening_cash'], 2) ?></span></div>
  <div class="r"><span class="l">Cash Sales</span><span>₹<?= number_format((float)$shift['cash_sales'], 2) ?></span></div>
  <div class="r"><span class="l">Expected</span><span>₹<?= number_format((float)$shift['expected_cash'], 2) ?></span></div>
  <div class="r"><span class="l">Counted</span><span>₹<?= number_format((float)$shift['counted_cash'], 2) ?></span></div>
  <div class="grand"><span>DIFFERENCE</span><span>₹<?= number_format((float)$shift['difference'], 2) ?></span></div>
  <div class="r" style="margin-top:.3rem"><span class="l">Variance</span><span><b><?= abs((float)$shift['difference']) < 10 ? 'BALANCED' : ((float)$shift['difference'] > 0 ? 'SURPLUS' : 'SHORTAGE') ?></b></span></div>

  <div class="dash"></div>
  <div class="c" style="font-size:10.5px;color:#555">
    Z-Report · Powered by DinoviX POS<br>
    Printed <?= date('d M Y, h:i A') ?>
  </div>
</div>
<script>
<?php if (service('request')->getGet('autoprint')): ?>
window.addEventListener('load', () => setTimeout(() => window.print(), 400));
<?php endif; ?>
</script>
</body>
</html>
