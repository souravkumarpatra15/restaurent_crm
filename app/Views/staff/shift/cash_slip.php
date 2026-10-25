<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Cash Slip — <?= esc($h['slip_number']) ?></title>
<style>
  * { margin:0; padding:0; box-sizing:border-box; }
  body { font-family:'Courier New', monospace; display:flex; justify-content:center; background:#E5E7EB; padding:1rem; }
  .slip { width:302px; background:#fff; padding:14px 16px; box-shadow:0 8px 24px rgba(0,0,0,.15); }
  .center { text-align:center; }
  .b { font-weight:bold; }
  .title { font-size:15px; letter-spacing:1px; }
  .dv { border-top:1px dashed #999; margin:8px 0; }
  .row { display:flex; justify-content:space-between; font-size:12px; padding:2px 0; }
  .denom { font-size:12px; padding:1px 0; display:flex; justify-content:space-between; }
  .box { border:1.5px solid #999; border-radius:6px; padding:8px; text-align:center; margin:8px 0; font-size:13px; }
  .sign { margin-top:22px; display:flex; justify-content:space-between; font-size:11px; }
  .sign .line { border-top:1px solid #333; padding-top:3px; width:40%; text-align:center; }
  .small { font-size:10px; color:#555; }
  .stamp { position:absolute; }
  @media print { body { background:#fff; padding:0; } .slip { box-shadow:none; } .noprint { display:none; } }
</style>
</head>
<body>
  <div class="slip">
    <div class="center b title"><?= esc(strtoupper($h['restaurant_name'] ?? '')) ?></div>
    <div class="center small"><?= esc($h['branch_name'] ?? '') ?></div>
    <div class="dv"></div>
    <div class="center b" style="font-size:14px">
      <?php if ($h['movement_type'] === 'bank_deposit'): ?>BANK DEPOSIT SLIP
      <?php elseif ($h['movement_type'] === 'safe_drop'): ?>SAFE DROP SLIP
      <?php else: ?>CASH HANDOVER SLIP<?php endif; ?>
    </div>
    <div class="center small">Slip # <?= esc($h['slip_number']) ?> <?= in_array($h['status'], ['handed','deposited']) ? '· ' . strtoupper($h['status']) : ($h['status'] === 'pending' ? '· PENDING' : '') ?></div>
    <div class="dv"></div>
    <div class="row"><span>Date</span><span><?= date('d M Y', strtotime($h['created_at'])) ?></span></div>
    <div class="row"><span>Time</span><span><?= date('h:i A', strtotime($h['created_at'])) ?></span></div>
    <div class="row"><span>Counter opened by</span><span><?= esc($h['staff_name'] ?? '-') ?></span></div>
    <div class="row"><span>Shift opened</span><span><?= $h['shift_id'] ? '#' . $h['shift_id'] : '-' ?></span></div>
    <div class="dv"></div>
    <?php if (!empty($denoms)): ?>
      <div class="b" style="font-size:11px;margin-bottom:3px">DENOMINATION COUNT</div>
      <?php foreach ($denoms as $d => $c): if ($c <= 0) continue; ?>
        <div class="denom"><span><?= (int)$d ?> × <?= (int)$c ?></span><span class="b"><?= esc($h['currency_symbol'] ?? '₹') ?><?= number_format($d * $c, 2) ?></span></div>
      <?php endforeach; ?>
      <div class="dv"></div>
    <?php endif; ?>
    <div class="box">
      TOTAL CASH<?= $h['movement_type'] === 'bank_deposit' ? ' DEPOSITED' : ' HANDED OVER' ?><br>
      <span class="b" style="font-size:20px"><?= esc($h['currency_symbol'] ?? '₹') ?><?= number_format($h['amount'], 2) ?></span>
    </div>
    <?php if ($h['received_by']): ?>
      <div class="row"><span><?= $h['movement_type'] === 'bank_deposit' ? 'Deposit ref / ack' : 'Received by' ?></span><span class="b"><?= esc($h['received_by']) ?></span></div>
    <?php endif; ?>
    <?php if ($h['reference_no']): ?>
      <div class="row"><span>Reference</span><span><?= esc($h['reference_no']) ?></span></div>
    <?php endif; ?>
    <?php if ($h['notes']): ?>
      <div class="row"><span>Note</span><span style="text-align:right;font-size:11px"><?= esc($h['notes']) ?></span></div>
    <?php endif; ?>
    <div class="dv"></div>
    <div class="sign">
      <div><div class="line">Handed by</div></div>
      <div><div class="line">Received by</div></div>
    </div>
    <div class="center small" style="margin-top:10px">Powered by DinoviX · POS Cash Management</div>
  </div>

  <div class="noprint" style="position:fixed;top:1rem;right:1rem;display:flex;gap:.5rem">
    <button onclick="window.print()" style="padding:.7rem 1.2rem;border:0;border-radius:10px;background:#FF6B35;color:#fff;font-weight:800;cursor:pointer">🖨️ Print Slip</button>
    <button onclick="window.close()" style="padding:.7rem 1.2rem;border:0;border-radius:10px;background:#374151;color:#fff;font-weight:800;cursor:pointer">Close</button>
  </div>
  <script>window.addEventListener('load', () => setTimeout(() => window.print(), 350));</script>
</body>
</html>
