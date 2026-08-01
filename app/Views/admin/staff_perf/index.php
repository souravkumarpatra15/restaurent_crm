<?php $this->extend('layouts/main'); $this->section('content'); ?>
<?php $sym = session('currency_symbol') ?? '₹'; ?>
<div style="padding:0 1rem">

  <!-- Date Filter -->
  <div style="display:flex;gap:.5rem;align-items:center;flex-wrap:wrap;margin-bottom:1rem">
    <form method="GET" style="display:flex;gap:.5rem;flex-wrap:wrap;flex:1">
      <input type="date" name="from" value="<?= $from ?>" class="form-control" style="width:140px">
      <input type="date" name="to"   value="<?= $to ?>"   class="form-control" style="width:140px">
      <button type="submit" class="btn btn-primary"><i class="fa fa-filter"></i> Filter</button>
    </form>
  </div>

  <!-- Staff Orders Table -->
  <div class="card" style="margin-bottom:1rem">
    <div class="card-header"><span class="card-title"><i class="fa fa-users" style="color:var(--primary)"></i> Staff Sales Performance</span></div>
    <div class="table-wrap">
      <table class="table">
        <thead>
          <tr><th>Staff</th><th>Orders</th><th>Revenue</th><th>Avg Order</th><th>Discounts</th><th>Cancelled</th><th>Avg Time</th><th>Score</th></tr>
        </thead>
        <tbody>
          <?php if (empty($staffStats)): ?>
          <tr><td colspan="8"><div class="empty-state" style="padding:2rem"><i class="fa fa-users"></i><p>No data for this period</p></div></td></tr>
          <?php else: ?>
          <?php
          $maxRev = max(array_column($staffStats,'total_revenue') ?: [1]);
          foreach ($staffStats as $idx => $s):
            // Score = (revenue/maxrev * 60) + ((1 - cancelled/orders) * 20) + (orders > 10 ? 20 : orders*2)
            $cancelRate = $s['total_orders'] > 0 ? $s['cancelled_orders'] / $s['total_orders'] : 0;
            $score = min(100, round(
              ($s['total_revenue'] / $maxRev) * 60 +
              (1 - $cancelRate) * 20 +
              min(20, $s['total_orders'] / 2)
            ));
            $scoreColor = $score >= 75 ? 'var(--success)' : ($score >= 50 ? 'var(--warning)' : 'var(--danger)');
          ?>
          <tr>
            <td>
              <div style="display:flex;align-items:center;gap:.5rem">
                <div style="width:32px;height:32px;border-radius:50%;background:var(--primary);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:.85rem;flex-shrink:0">
                  <?= $idx < 3 ? ['🥇','🥈','🥉'][$idx] : strtoupper(substr($s['staff_name'],0,1)) ?>
                </div>
                <div>
                  <div style="font-weight:700;font-size:.875rem"><?= esc($s['staff_name']) ?></div>
                  <div style="font-size:.7rem;color:var(--text-muted)"><?= esc($s['role_name'] ?? '') ?></div>
                </div>
              </div>
            </td>
            <td style="font-weight:700"><?= $s['total_orders'] ?></td>
            <td style="font-weight:700;color:var(--primary)"><?= $sym ?><?= number_format($s['total_revenue']) ?></td>
            <td><?= $sym ?><?= number_format($s['avg_order_value']) ?></td>
            <td style="color:var(--danger)">-<?= $sym ?><?= number_format($s['total_discount']) ?></td>
            <td>
              <span class="badge-pill badge-<?= $s['cancelled_orders'] > 0 ? 'danger' : 'success' ?>">
                <?= $s['cancelled_orders'] ?>
              </span>
            </td>
            <td style="font-size:.8rem"><?= $s['avg_completion_min'] ? round($s['avg_completion_min']).'m' : '—' ?></td>
            <td>
              <div style="display:flex;align-items:center;gap:.4rem">
                <div style="flex:1;height:6px;background:var(--border);border-radius:3px;min-width:48px">
                  <div style="height:100%;width:<?= $score ?>%;background:<?= $scoreColor ?>;border-radius:3px;transition:width .6s"></div>
                </div>
                <span style="font-weight:800;font-size:.8rem;color:<?= $scoreColor ?>"><?= $score ?></span>
              </div>
            </td>
          </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Kitchen Performance -->
  <?php if (!empty($kotPerf)): ?>
  <div class="card" style="margin-bottom:1rem">
    <div class="card-header"><span class="card-title"><i class="fa fa-fire-burner" style="color:var(--primary)"></i> Kitchen Speed Performance</span></div>
    <div class="table-wrap">
      <table class="table">
        <thead><tr><th>Staff</th><th>KOTs Handled</th><th>Avg Prep Time</th><th>Overdue (&gt;20m)</th><th>Speed Grade</th></tr></thead>
        <tbody>
          <?php foreach ($kotPerf as $k):
            $avgMin  = round($k['avg_prep_min'] ?? 0);
            $grade   = $avgMin <= 8 ? ['A+','success'] : ($avgMin <= 12 ? ['A','success'] : ($avgMin <= 18 ? ['B','warning'] : ['C','danger']));
          ?>
          <tr>
            <td style="font-weight:700"><?= esc($k['staff_name'] ?? 'Unknown') ?></td>
            <td><?= $k['total_kots'] ?></td>
            <td><?= $avgMin ?>m</td>
            <td><span class="badge-pill badge-<?= $k['overdue_kots'] > 0 ? 'danger':'success' ?>"><?= $k['overdue_kots'] ?></span></td>
            <td><span class="badge-pill badge-<?= $grade[1] ?>" style="font-size:.82rem;font-weight:900"><?= $grade[0] ?></span></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php endif; ?>

  <!-- Hourly Activity Chart -->
  <div class="card">
    <div class="card-header"><span class="card-title"><i class="fa fa-clock" style="color:var(--primary)"></i> Hourly Order Activity</span></div>
    <div class="card-body" style="padding:.75rem">
      <canvas id="hourlyChart" height="100"></canvas>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const hourly = <?= json_encode($hourly) ?>;
const labels = Array.from({length:24}, (_,h) => h===0?'12a':(h<12?h+'a':(h===12?'12p':(h-12)+'p')));
const data   = Array(24).fill(0);
hourly.forEach(h => { data[parseInt(h.hr)] = parseInt(h.cnt); });
new Chart(document.getElementById('hourlyChart'),{
  type:'bar',
  data:{ labels, datasets:[{ label:'Orders', data, backgroundColor:'rgba(255,107,53,.3)', borderColor:'#FF6B35', borderWidth:1.5, borderRadius:4 }] },
  options:{ responsive:true, plugins:{legend:{display:false}}, scales:{y:{beginAtZero:true,ticks:{precision:0}},x:{grid:{display:false}}} }
});
</script>
<?php $this->endSection(); ?>
