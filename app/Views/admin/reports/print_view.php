<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?= esc($title) ?></title>
<style>
  * { box-sizing:border-box; margin:0; padding:0; }
  body { font-family: Arial, sans-serif; font-size:12px; color:#111; padding:24px; }
  .bar { background:#1A202C; color:#fff; padding:.6rem 1rem; border-radius:8px; display:flex; justify-content:space-between; align-items:center; max-width:100%; margin-bottom:16px; }
  .bar b { font-size:.9rem; }
  .bar button { padding:.4rem 1rem; border:none; border-radius:6px; background:#FF6B35; color:#fff; font-weight:700; cursor:pointer; }
  h1 { font-size:18px; margin-bottom:2px; }
  .meta { color:#555; font-size:11px; margin-bottom:14px; line-height:1.6; }
  table { width:100%; border-collapse:collapse; }
  th, td { border:1px solid #ccc; padding:5px 8px; }
  th { background:#F1F5F9; text-align:left; font-size:11px; text-transform:uppercase; letter-spacing:.03em; }
  tfoot td { font-weight:900; background:#F8FAFC; }
  .num { text-align:right; }
  @media print { .bar { display:none; } body { padding:0; } }
</style>
</head>
<body>
<div class="bar">
  <b><?= esc($title) ?></b>
  <button onclick="window.print()">🖨 Print / Save as PDF</button>
</div>

<h1><?= esc(session('restaurant_name')) ?> — <?= esc($title) ?></h1>
<div class="meta">
  <?php foreach ($summary as $k => $v): ?><?= esc($k) ?>: <b><?= esc((string)$v) ?></b> &nbsp;·&nbsp;<?php endforeach; ?>
</div>

<table>
  <thead>
    <tr>
      <?php foreach ($head as $h): ?><th><?= esc($h) ?></th><?php endforeach; ?>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($rows as $r): ?>
    <tr>
      <?php foreach (array_values($r) as $v): ?>
      <td class="<?= is_numeric($v) ? 'num' : '' ?>"><?= esc((is_float($v) || is_int($v)) ? number_format((float)$v, 2) : (string)$v) ?></td>
      <?php endforeach; ?>
    </tr>
    <?php endforeach; ?>
    <?php if (empty($rows)): ?>
    <tr><td colspan="<?= count($head) ?>" style="text-align:center;padding:16px;color:#888">No data for this period</td></tr>
    <?php endif; ?>
  </tbody>
</table>
</body>
</html>
