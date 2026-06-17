<?php if (empty($liveOrders)): ?>
<div class="empty-state" style="padding:2rem">
  <i class="fa fa-receipt"></i>
  <p>No active orders right now</p>
</div>
<?php else: ?>
<?php foreach ($liveOrders as $o):
  $sc=['pending'=>'warning','confirmed'=>'info','preparing'=>'info','ready'=>'success','served'=>'success'];
?>
<div style="display:flex;align-items:center;justify-content:space-between;padding:.7rem 1.25rem;border-bottom:1px solid var(--border)">
  <div>
    <div style="font-weight:700;font-size:.875rem"><?= esc($o['order_number']) ?></div>
    <div style="font-size:.72rem;color:var(--text-muted)">
      <?= ucfirst(str_replace('_',' ',$o['order_type'])) ?>
      <?= $o['table_number'] ? ' · Table '.$o['table_number'] : '' ?>
    </div>
  </div>
  <div style="text-align:right">
    <span class="badge-pill badge-<?= $sc[$o['status']] ?? 'gray' ?>"><?= ucfirst($o['status']) ?></span>
    <div style="font-weight:800;font-size:.875rem;margin-top:.2rem">₹<?= number_format($o['total_amount'],2) ?></div>
  </div>
</div>
<?php endforeach; ?>
<?php endif; ?>
