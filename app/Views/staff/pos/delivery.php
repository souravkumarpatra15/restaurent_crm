<?php $this->extend('layouts/main'); $this->section('content');
$CSRF = csrf_token() . '=' . urlencode(csrf_hash());
$sym  = function_exists('currency_symbol') ? currency_symbol() : '₹';
$stateMeta = [
  'unassigned'      => ['badge' => 'badge-danger',  'label' => 'Unassigned',       'color' => 'var(--danger)'],
  'assigned'        => ['badge' => 'badge-warning', 'label' => 'Assigned',         'color' => '#D69E2E'],
  'out_for_delivery'=>['badge' => 'badge-info',     'label' => 'Out for delivery', 'color' => '#3182CE'],
  'delivered'       => ['badge' => 'badge-success', 'label' => 'Delivered',        'color' => 'var(--success)'],
  'failed'          => ['badge' => 'badge-danger',  'label' => 'Failed',           'color' => '#7F1D1D'],
];
?>
<div style="padding:0 1rem;max-width:1000px">
  <!-- Header + stats -->
  <div class="card" style="margin-bottom:1rem">
    <div class="card-header">
      <span class="card-title"><i class="fa fa-motorcycle" style="color:var(--primary)"></i> Delivery Board</span>
      <a href="<?= base_url('pos') ?>" class="btn btn-sm btn-outline" style="white-space:nowrap"><i class="fa fa-cash-register"></i> Back to POS</a>
    </div>
    <div class="card-body">
      <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:.7rem">
        <div style="text-align:center;padding:.8rem .4rem;background:#FEF2F2;border-radius:10px">
          <div style="font-size:1.35rem;font-weight:800;color:var(--danger)"><?= $stats['unassigned'] ?></div>
          <div style="font-size:.72rem;color:var(--text-muted)">Unassigned</div>
        </div>
        <div style="text-align:center;padding:.8rem .4rem;background:#FFFBEB;border-radius:10px">
          <div style="font-size:1.35rem;font-weight:800;color:#D69E2E"><?= $stats['active'] ?></div>
          <div style="font-size:.72rem;color:var(--text-muted)">On the road</div>
        </div>
        <div style="text-align:center;padding:.8rem .4rem;background:#F0FFF4;border-radius:10px">
          <div style="font-size:1.35rem;font-weight:800;color:var(--success)"><?= $stats['delivered'] ?></div>
          <div style="font-size:.72rem;color:var(--text-muted)">Delivered</div>
        </div>
        <div style="text-align:center;padding:.8rem .4rem;background:#F1F5F9;border-radius:10px">
          <div style="font-size:1.35rem;font-weight:800;color:#475569"><?= $stats['failed'] ?></div>
          <div style="font-size:.72rem;color:var(--text-muted)">Failed</div>
        </div>
      </div>
    </div>
  </div>

  <!-- Orders -->
  <?php if (empty($orders)): ?>
  <div class="card">
    <div class="card-body" style="text-align:center;padding:2.5rem 1rem">
      <i class="fa fa-motorcycle" style="font-size:2rem;color:var(--border)"></i>
      <p style="color:var(--text-muted);margin-top:.5rem">No delivery orders yet. Create one from POS → Delivery.</p>
    </div>
  </div>
  <?php else: foreach ($orders as $o):
    $ds = $o['delivery_status'] ?: 'unassigned';
    $m  = $stateMeta[$ds];
    $closed = in_array($ds, ['delivered', 'failed']);
  ?>
  <div class="card" style="margin-bottom:.8rem" id="ocard<?= $o['id'] ?>">
    <div class="card-body">
      <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:.6rem;flex-wrap:wrap">
        <div style="min-width:220px">
          <div style="font-weight:800;font-size:.95rem">
            <?= esc($o['order_number']) ?>
            <span class="badge-pill <?= $m['badge'] ?>" style="margin-left:.4rem"><?= $m['label'] ?></span>
            <?php if ($o['payment_status'] === 'paid'): ?><span class="badge-pill badge-success" style="margin-left:.25rem">Paid</span>
            <?php else: ?><span class="badge-pill badge-warning" style="margin-left:.25rem">Unpaid</span><?php endif; ?>
          </div>
          <div style="font-size:.8rem;color:var(--text-muted);margin-top:.2rem">
            <?= esc($o['customer_name'] ?: 'Walk-in') ?> · <?= esc($o['customer_phone'] ?: '—') ?>
            · <?= date('d M h:i A', strtotime($o['created_at'])) ?>
          </div>
          <?php if ($o['delivery_address']): ?>
          <div style="font-size:.78rem;color:var(--text-muted);margin-top:.25rem"><i class="fa fa-location-dot"></i> <?= esc($o['delivery_address']) ?></div>
          <?php endif; ?>
          <?php if ($o['rider_name']): ?>
          <div style="font-size:.78rem;margin-top:.25rem"><i class="fa fa-user" style="color:var(--primary)"></i> Rider: <strong><?= esc($o['rider_name']) ?></strong></div>
          <?php endif; ?>
        </div>
        <div style="text-align:right">
          <div style="font-weight:900;font-size:1.15rem;color:var(--primary)"><?= $sym ?><?= number_format($o['total_amount'], 2) ?></div>
        </div>
      </div>

      <?php if (!$closed): ?>
      <div style="display:flex;gap:.5rem;margin-top:.75rem;flex-wrap:wrap;align-items:center">
        <select id="rider<?= $o['id'] ?>" style="padding:.5rem .6rem;border:1.5px solid var(--border);border-radius:8px;font-size:.8rem;font-weight:600;background:#fff;outline:none;max-width:190px">
          <option value="0">— assign rider —</option>
          <?php foreach ($riders as $r): ?>
          <option value="<?= $r['id'] ?>" <?= $o['rider_id'] == $r['id'] ? 'selected' : '' ?>><?= esc($r['name']) ?></option>
          <?php endforeach; ?>
        </select>
        <button class="btn btn-sm btn-primary" onclick="assignRider(<?= $o['id'] ?>)"><i class="fa fa-user-check"></i> Assign</button>
        <?php if ($o['rider_id'] && $ds !== 'out_for_delivery'): ?>
        <button class="btn btn-sm btn-outline" onclick="setStatus(<?= $o['id'] ?>,'out_for_delivery')"><i class="fa fa-route"></i> Out for delivery</button>
        <?php endif; ?>
        <?php if ($ds === 'out_for_delivery'): ?>
        <button class="btn btn-sm btn-success" onclick="setStatus(<?= $o['id'] ?>,'delivered')"><i class="fa fa-circle-check"></i> Delivered</button>
        <button class="btn btn-sm btn-danger" onclick="setStatus(<?= $o['id'] ?>,'failed')"><i class="fa fa-circle-xmark"></i> Failed</button>
        <?php endif; ?>
        <?php if ($o['rider_id'] && !$closed): ?>
        <button class="btn btn-sm btn-outline" title="Unassign" onclick="unassign(<?= $o['id'] ?>)"><i class="fa fa-user-slash"></i></button>
        <?php endif; ?>
      </div>
      <?php elseif ($ds === 'delivered'): ?>
      <div style="font-size:.78rem;color:var(--success);margin-top:.5rem"><i class="fa fa-circle-check"></i> Delivered at <?= date('d M h:i A', strtotime($o['delivered_at'])) ?></div>
      <?php endif; ?>
    </div>
  </div>
  <?php endforeach; endif; ?>
</div>

<script>
const CSRF = '<?= $CSRF ?>';
function post(url, body) {
  return fetch(url, { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded','X-Requested-With':'XMLHttpRequest'}, body: CSRF + '&' + body })
    .then(r => r.json());
}
function assignRider(id) {
  const v = document.getElementById('rider' + id).value;
  if (!v || v === '0') { alert('Pick a rider first'); return; }
  post('<?= base_url('pos/delivery/assign') ?>', 'order_id=' + id + '&rider_id=' + v)
    .then(d => { if (d.success) location.reload(); else alert(d.message); });
}
function unassign(id) {
  post('<?= base_url('pos/delivery/assign') ?>', 'order_id=' + id + '&rider_id=0')
    .then(d => { if (d.success) location.reload(); else alert(d.message); });
}
function setStatus(id, s) {
  const msg = s === 'failed' ? (prompt('Reason (recorded in order notes):') || '') : '';
  const body = 'order_id=' + id + '&delivery_status=' + s + (msg ? '&notes=' + encodeURIComponent(msg) : '');
  post('<?= base_url('pos/delivery/status') ?>', body)
    .then(d => { if (d.success) location.reload(); else alert(d.message); });
}
</script>
<?php $this->endSection(); ?>
