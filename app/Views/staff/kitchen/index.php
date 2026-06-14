<?php $this->extend('layouts/main'); $this->section('content'); ?>
<style>
.kitchen-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:1rem;padding:1rem}
.kot-card{background:#fff;border-radius:12px;box-shadow:0 1px 3px rgba(0,0,0,.08);border-top:4px solid var(--warning);overflow:hidden}
.kot-card.ready{border-top-color:var(--success)}
.kot-card.overdue{border-top-color:var(--danger);animation:pulse-border 1.5s infinite}
@keyframes pulse-border{0%,100%{box-shadow:0 0 0 0 rgba(229,62,62,.4)}50%{box-shadow:0 0 0 6px rgba(229,62,62,0)}}
.kot-timer{font-family:'JetBrains Mono',monospace;font-size:.78rem;font-weight:700}
</style>

<div style="display:flex;align-items:center;justify-content:space-between;padding:.75rem 1rem;background:var(--sidebar-bg);color:#fff">
  <div style="display:flex;align-items:center;gap:.5rem">
    <span style="width:10px;height:10px;background:#48BB78;border-radius:50%;display:inline-block;animation:pulse 1.5s infinite"></span>
    <strong>Kitchen Display</strong>
  </div>
  <div style="font-size:.82rem;opacity:.7" id="kitchenClock"></div>
</div>

<div class="kitchen-grid" id="kitchenGrid">
  <?php if (empty($kots)): ?>
  <div style="grid-column:1/-1;text-align:center;padding:4rem;color:var(--text-muted)">
    <div style="font-size:3rem;margin-bottom:1rem">✅</div>
    <div style="font-weight:700;font-size:1.1rem">All Clear!</div>
    <div style="font-size:.85rem">No pending kitchen orders</div>
  </div>
  <?php else: foreach ($kots as $kot):
    $mins = (time() - strtotime($kot['created_at'])) / 60;
    $cls  = $mins > 20 ? 'overdue' : ($kot['status'] === 'ready' ? 'ready' : '');
  ?>
  <div class="kot-card <?= $cls ?>" id="kot-<?= $kot['id'] ?>">
    <div style="display:flex;justify-content:space-between;align-items:center;padding:.75rem 1rem;background:var(--bg)">
      <div>
        <div style="font-weight:800;font-size:1rem"><?= esc($kot['kot_number']) ?></div>
        <div style="font-size:.72rem;color:var(--text-muted)"><?= ucfirst(str_replace('_',' ',$kot['order_type'] ?? 'dine_in')) ?> <?= $kot['table_number'] ? '· Table '.$kot['table_number'] : '' ?></div>
      </div>
      <div class="kot-timer <?= $mins > 20 ? 'text-danger' : ($mins > 10 ? 'text-warning' : 'text-success') ?>"
           data-start="<?= strtotime($kot['created_at']) ?>" id="timer-<?= $kot['id'] ?>">
        <?= floor($mins) ?>m
      </div>
    </div>
    <div style="padding:.5rem 1rem">
      <?php foreach ($kot['items'] as $item): ?>
      <div style="display:flex;align-items:center;gap:.5rem;padding:.4rem 0;border-bottom:1px solid var(--border)">
        <span style="font-weight:800;font-size:1rem;color:var(--primary);min-width:28px"><?= $item['quantity'] ?>×</span>
        <div>
          <div style="font-size:.875rem;font-weight:600"><?= esc($item['name']) ?></div>
          <?php if ($item['notes']): ?>
            <div style="font-size:.72rem;color:var(--warning);font-weight:600">⚠ <?= esc($item['notes']) ?></div>
          <?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <div style="padding:.75rem 1rem;display:flex;gap:.5rem">
      <?php if ($kot['status'] !== 'ready'): ?>
      <button onclick="updateKot(<?= $kot['id'] ?>,'in_progress')" class="btn btn-sm btn-outline" style="flex:1">
        <i class="fa fa-fire"></i> Preparing
      </button>
      <button onclick="updateKot(<?= $kot['id'] ?>,'ready')" class="btn btn-sm btn-success" style="flex:1">
        <i class="fa fa-check"></i> Ready
      </button>
      <?php else: ?>
      <button onclick="updateKot(<?= $kot['id'] ?>,'served')" class="btn btn-sm btn-primary btn-block">
        <i class="fa fa-hand-sparkles"></i> Mark Served
      </button>
      <?php endif; ?>
    </div>
  </div>
  <?php endforeach; endif; ?>
</div>

<script>
// Live clock
setInterval(() => {
  const now = new Date();
  document.getElementById('kitchenClock').textContent = now.toLocaleTimeString('en-IN',{hour:'2-digit',minute:'2-digit',second:'2-digit'});
}, 1000);

// Live timers
setInterval(() => {
  document.querySelectorAll('[id^="timer-"]').forEach(el => {
    const start = parseInt(el.dataset.start);
    const mins  = Math.floor((Date.now()/1000 - start) / 60);
    const secs  = Math.floor((Date.now()/1000 - start) % 60);
    el.textContent = mins + 'm ' + secs + 's';
    if (mins >= 20) { el.style.color='var(--danger)'; el.closest('.kot-card').classList.add('overdue'); }
    else if (mins >= 10) el.style.color='var(--warning)';
  });
}, 1000);

// Auto-refresh every 20 seconds
setInterval(() => location.reload(), 20000);

function updateKot(id, status) {
  fetch('<?= base_url('pos/kitchen/update-status') ?>', {
    method: 'POST',
    headers: {'Content-Type':'application/x-www-form-urlencoded'},
    body: '<?= csrf_token() ?>=<?= csrf_hash() ?>&kot_id=' + id + '&status=' + status
  }).then(r => r.json()).then(d => {
    if (d.success) {
      if (status === 'served') {
        document.getElementById('kot-' + id)?.remove();
      } else if (status === 'ready') {
        document.getElementById('kot-' + id)?.classList.add('ready');
        document.getElementById('kot-' + id)?.classList.remove('overdue');
      }
    }
  });
}
</script>
<?php $this->endSection(); ?>
