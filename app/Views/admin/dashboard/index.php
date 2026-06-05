<?php $this->extend('layouts/main'); $this->section('content'); ?>

<!-- Greeting Bar -->
<div style="display:flex;align-items:center;justify-content:space-between;padding:.75rem 1rem;background:linear-gradient(135deg,var(--primary),#FF8C5A);color:#fff;margin-bottom:.75rem;border-radius:0 0 var(--radius) var(--radius)">
  <div>
    <div style="font-size:.78rem;opacity:.85">Good <?= date('H') < 12 ? 'Morning' : (date('H') < 17 ? 'Afternoon' : 'Evening') ?> 👋</div>
    <div style="font-weight:800;font-size:1.05rem"><?= esc($userName) ?></div>
  </div>
  <div style="text-align:right">
    <div style="font-size:.75rem;opacity:.85"><?= date('l, d M Y') ?></div>
    <div style="font-size:.82rem;font-weight:600" id="liveClock"></div>
  </div>
</div>

<!-- Quick Stats -->
<div style="padding:0 1rem">
  <div class="stats-grid">
    <div class="stat-card">
      <div class="stat-icon"><i class="fa fa-receipt"></i></div>
      <div>
        <div class="stat-value"><?= number_format($stats['orders_today']) ?></div>
        <div class="stat-label">Orders Today</div>
        <div class="stat-change <?= $stats['orders_change'] >= 0 ? 'up' : 'down' ?>">
          <i class="fa fa-arrow-<?= $stats['orders_change'] >= 0 ? 'up' : 'down' ?>"></i>
          <?= abs($stats['orders_change']) ?>% vs yesterday
        </div>
      </div>
    </div>
    <div class="stat-card green">
      <div class="stat-icon green"><i class="fa fa-indian-rupee-sign"></i></div>
      <div>
        <div class="stat-value">₹<?= number_format($stats['revenue_today']) ?></div>
        <div class="stat-label">Revenue Today</div>
        <div class="stat-change <?= $stats['revenue_change'] >= 0 ? 'up' : 'down' ?>">
          <i class="fa fa-arrow-<?= $stats['revenue_change'] >= 0 ? 'up' : 'down' ?>"></i>
          ₹<?= number_format(abs($stats['revenue_change_val'])) ?> vs yesterday
        </div>
      </div>
    </div>
    <div class="stat-card blue">
      <div class="stat-icon blue"><i class="fa fa-users"></i></div>
      <div>
        <div class="stat-value"><?= $stats['customers_today'] ?></div>
        <div class="stat-label">Customers Today</div>
      </div>
    </div>
    <div class="stat-card orange">
      <div class="stat-icon orange"><i class="fa fa-fire-burner"></i></div>
      <div>
        <div class="stat-value"><?= $stats['pending_kots'] ?></div>
        <div class="stat-label">Pending KOTs</div>
      </div>
    </div>
  </div>
</div>

<!-- Quick Actions -->
<div style="padding:.75rem 1rem;display:flex;gap:.5rem;overflow-x:auto;scrollbar-width:none">
  <a href="<?= base_url('pos') ?>" class="btn btn-primary" style="flex-shrink:0">
    <i class="fa fa-cash-register"></i> Open POS
  </a>
  <a href="<?= base_url('pos/new-order/takeaway') ?>" class="btn btn-outline" style="flex-shrink:0">
    <i class="fa fa-bag-shopping"></i> Takeaway
  </a>
  <a href="<?= base_url('admin/reservations') ?>" class="btn btn-outline" style="flex-shrink:0">
    <i class="fa fa-calendar-check"></i> Reserves
  </a>
  <a href="<?= base_url('admin/reports/sales') ?>" class="btn btn-outline" style="flex-shrink:0">
    <i class="fa fa-chart-bar"></i> Reports
  </a>
</div>

<div style="padding:0 1rem;display:grid;gap:1rem">

  <!-- Branch Switcher (if multi-branch) -->
  <?php if (count($branches) > 1): ?>
  <div class="card">
    <div class="card-header">
      <span class="card-title"><i class="fa fa-code-branch" style="color:var(--primary)"></i> Branches</span>
    </div>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:.5rem;padding:1rem">
      <?php foreach ($branches as $b): ?>
      <div style="border:1.5px solid <?= $b['is_active'] ? 'var(--border)' : '#FED7D7' ?>;border-radius:10px;padding:.625rem;cursor:pointer;transition:all .2s"
           onclick="switchBranch(<?= $b['id'] ?>)"
           class="branch-tile <?= (session('branch_id') == $b['id']) ? 'active-branch' : '' ?>">
        <div style="font-weight:700;font-size:.82rem"><?= esc($b['name']) ?></div>
        <div style="font-size:.7rem;color:var(--text-muted)"><?= esc($b['city']) ?></div>
        <div style="margin-top:.35rem;font-size:.72rem">
          <span class="badge-pill badge-<?= $b['is_active'] ? 'success' : 'danger' ?>"><?= $b['is_active'] ? 'Active' : 'Inactive' ?></span>
        </div>
        <div style="margin-top:.3rem;font-size:.78rem;font-weight:700;color:var(--primary)">₹<?= number_format($b['today_revenue'] ?? 0) ?></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>

  <!-- Live Orders -->
  <div class="card">
    <div class="card-header">
      <span class="card-title">
        <span style="display:inline-flex;align-items:center;gap:.4rem">
          <span style="width:8px;height:8px;background:var(--success);border-radius:50%;animation:pulse 1.5s infinite"></span>
          Live Orders
        </span>
      </span>
      <a href="<?= base_url('admin/orders') ?>" class="btn btn-sm btn-outline">View All</a>
    </div>
    <div id="liveOrders">
      <?php if (empty($liveOrders)): ?>
      <div class="empty-state" style="padding:2rem">
        <i class="fa fa-receipt"></i>
        <p>No active orders right now</p>
      </div>
      <?php else: ?>
      <?php foreach ($liveOrders as $o): ?>
      <div class="live-order-row" style="display:flex;align-items:center;justify-content:space-between;padding:.7rem 1.25rem;border-bottom:1px solid var(--border)">
        <div>
          <div style="font-weight:700;font-size:.875rem"><?= esc($o['order_number']) ?></div>
          <div style="font-size:.72rem;color:var(--text-muted)">
            <?= ucfirst(str_replace('_',' ',$o['order_type'])) ?>
            <?= $o['table_number'] ? ' · Table '.$o['table_number'] : '' ?>
            · <?= time_elapsed_string($o['created_at']) ?>
          </div>
        </div>
        <div style="text-align:right">
          <?php $statusColors = ['pending'=>'warning','confirmed'=>'info','preparing'=>'info','ready'=>'success','served'=>'success','completed'=>'gray']; ?>
          <span class="badge-pill badge-<?= $statusColors[$o['status']] ?? 'gray' ?>"><?= ucfirst($o['status']) ?></span>
          <div style="font-weight:800;font-size:.875rem;margin-top:.2rem">₹<?= number_format($o['total_amount'],2) ?></div>
        </div>
      </div>
      <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>

  <!-- Today's Revenue by Hour -->
  <div class="card">
    <div class="card-header">
      <span class="card-title"><i class="fa fa-chart-area" style="color:var(--primary)"></i> Revenue Today</span>
      <select class="form-control" id="chartBranch" style="width:auto;padding:.3rem .6rem;font-size:.8rem" onchange="loadChart(this.value)">
        <option value="">All Branches</option>
        <?php foreach ($branches as $b): ?>
        <option value="<?= $b['id'] ?>"><?= esc($b['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div style="padding:1rem">
      <canvas id="revenueChart" height="180"></canvas>
    </div>
  </div>

  <!-- Payment Methods Split -->
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
    <div class="card">
      <div class="card-header"><span class="card-title" style="font-size:.875rem"><i class="fa fa-credit-card" style="color:var(--primary)"></i> Payments</span></div>
      <div class="card-body" style="padding:.75rem">
        <?php foreach ($paymentSplit as $pmt): ?>
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.5rem;font-size:.82rem">
          <span><?= ucfirst(str_replace('_',' ',$pmt['payment_method'])) ?></span>
          <strong>₹<?= number_format($pmt['total']) ?></strong>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <div class="card">
      <div class="card-header"><span class="card-title" style="font-size:.875rem"><i class="fa fa-chart-pie" style="color:var(--primary)"></i> Order Types</span></div>
      <div class="card-body" style="padding:.75rem">
        <?php foreach ($orderTypeSplit as $ot): ?>
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.5rem;font-size:.82rem">
          <span><?= ucfirst(str_replace('_',' ',$ot['order_type'])) ?></span>
          <strong><?= $ot['count'] ?></strong>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <!-- Top Selling Items -->
  <div class="card">
    <div class="card-header">
      <span class="card-title"><i class="fa fa-fire" style="color:var(--danger)"></i> Top Selling Today</span>
    </div>
    <div class="card-body" style="padding:.5rem">
      <?php if (empty($topItems)): ?>
        <div class="empty-state" style="padding:1.5rem"><i class="fa fa-utensils"></i><p>No sales data yet</p></div>
      <?php else: ?>
        <?php foreach (array_slice($topItems,0,6) as $idx => $item): ?>
        <div style="display:flex;align-items:center;gap:.75rem;padding:.5rem .75rem;border-bottom:1px solid var(--border)">
          <div style="width:22px;height:22px;border-radius:50%;background:<?= $idx === 0 ? 'var(--warning)' : ($idx === 1 ? '#CBD5E0' : ($idx === 2 ? '#D69E2E' : 'var(--bg)')) ?>;color:<?= $idx < 3 ? '#fff' : 'var(--text-muted)' ?>;display:flex;align-items:center;justify-content:center;font-size:.7rem;font-weight:800;flex-shrink:0">
            <?= $idx+1 ?>
          </div>
          <div style="flex:1;min-width:0">
            <div style="font-size:.83rem;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= esc($item['name']) ?></div>
            <div style="font-size:.7rem;color:var(--text-muted)"><?= $item['total_qty'] ?> sold</div>
          </div>
          <div style="font-weight:700;font-size:.83rem;color:var(--primary)">₹<?= number_format($item['total_revenue']) ?></div>
        </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>

  <!-- Upcoming Reservations -->
  <?php if (!empty($reservations)): ?>
  <div class="card">
    <div class="card-header">
      <span class="card-title"><i class="fa fa-calendar-check" style="color:var(--info)"></i> Upcoming Reservations</span>
      <a href="<?= base_url('admin/reservations') ?>" class="btn btn-sm btn-outline">All</a>
    </div>
    <div class="card-body" style="padding:.5rem">
      <?php foreach ($reservations as $r): ?>
      <div style="display:flex;align-items:center;justify-content:space-between;padding:.5rem .75rem;border-bottom:1px solid var(--border)">
        <div>
          <div style="font-weight:600;font-size:.85rem"><?= esc($r['customer_name']) ?></div>
          <div style="font-size:.72rem;color:var(--text-muted)"><?= date('h:i A', strtotime($r['reservation_time'])) ?> · <?= $r['guests'] ?> guests<?= $r['table_number'] ? ' · Table '.$r['table_number'] : '' ?></div>
        </div>
        <span class="badge-pill badge-info"><?= ucfirst($r['status']) ?></span>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>

</div>

<style>
.active-branch { border-color: var(--primary) !important; background: var(--primary-light); }
.branch-tile:hover { border-color: var(--primary); transform: translateY(-2px); box-shadow: var(--shadow); }
@keyframes pulse { 0%,100%{opacity:1;transform:scale(1)} 50%{opacity:.5;transform:scale(1.3)} }
</style>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>
<script>
// Live clock
function updateClock() {
  const now = new Date();
  document.getElementById('liveClock').textContent = now.toLocaleTimeString('en-IN',{hour:'2-digit',minute:'2-digit',second:'2-digit'});
}
updateClock(); setInterval(updateClock, 1000);

// Auto-refresh live orders every 30 seconds
setInterval(() => {
  fetch('<?= base_url('admin/dashboard/live-orders') ?>')
    .then(r => r.text()).then(html => {
      document.getElementById('liveOrders').innerHTML = html;
    }).catch(() => {});
}, 30000);

// Revenue Chart
const hourlyData = <?= json_encode($hourlyRevenue ?? []) ?>;
const ctx = document.getElementById('revenueChart').getContext('2d');
const chart = new Chart(ctx, {
  type: 'bar',
  data: {
    labels: hourlyData.map(d => d.hour + ':00'),
    datasets: [{
      label: 'Revenue (₹)',
      data: hourlyData.map(d => d.revenue),
      backgroundColor: 'rgba(255,107,53,.2)',
      borderColor: 'rgba(255,107,53,1)',
      borderWidth: 2,
      borderRadius: 6,
      borderSkipped: false,
    }]
  },
  options: {
    responsive: true,
    plugins: { legend: { display: false } },
    scales: {
      x: { grid: { display: false }, ticks: { font: { size: 10 } } },
      y: {
        grid: { color: 'rgba(0,0,0,.05)' },
        ticks: {
          font: { size: 10 },
          callback: v => '₹' + (v >= 1000 ? (v/1000).toFixed(1)+'K' : v)
        }
      }
    }
  }
});

function loadChart(branchId) {
  fetch('<?= base_url('admin/dashboard/hourly-chart') ?>?branch=' + branchId)
    .then(r => r.json()).then(data => {
      chart.data.labels   = data.map(d => d.hour + ':00');
      chart.data.datasets[0].data = data.map(d => d.revenue);
      chart.update();
    });
}

function switchBranch(id) {
  fetch('<?= base_url('admin/dashboard/switch-branch') ?>?branch_id=' + id)
    .then(r => r.json()).then(d => {
      if (d.success) location.reload();
    });
}
</script>

<?php $this->endSection(); ?>
