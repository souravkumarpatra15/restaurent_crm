<?php $this->extend('layouts/main'); $this->section('content'); ?>
<div style="padding:0 1rem">

  <!-- Header -->
  <div style="background:linear-gradient(135deg,#667eea,#764ba2);border-radius:var(--radius);padding:1.25rem;margin-bottom:1rem;color:#fff">
    <div style="font-size:.78rem;opacity:.8;margin-bottom:.25rem">Powered by your data · Last 30 days</div>
    <div style="font-weight:900;font-size:1.2rem">🧠 Smart Insights</div>
    <div style="font-size:.82rem;opacity:.85;margin-top:.25rem">AI-powered recommendations to grow your restaurant</div>
  </div>

  <!-- 7-Day Revenue Chart -->
  <div class="card" style="margin-bottom:1rem">
    <div class="card-header"><span class="card-title">📈 7-Day Revenue Trend</span></div>
    <div class="card-body" style="padding:.75rem">
      <canvas id="revChart" height="100"></canvas>
    </div>
  </div>

  <!-- Insight Cards -->
  <div style="display:flex;flex-direction:column;gap:.75rem;margin-bottom:1rem">
    <?php foreach ($insights as $ins): ?>
    <?php $bgs=['high'=>'#FFF5F5','medium'=>'#FFFBEB','low'=>'#F0FFF4']; $bds=['high'=>'#FEB2B2','medium'=>'#FDE68A','low'=>'#9AE6B4']; ?>
    <div style="background:<?= $bgs[$ins['priority']]??'#fff' ?>;border:1.5px solid <?= $bds[$ins['priority']]??'var(--border)' ?>;border-radius:var(--radius);padding:1rem">
      <div style="display:flex;align-items:flex-start;gap:.75rem">
        <div style="font-size:1.75rem;flex-shrink:0"><?= $ins['icon'] ?></div>
        <div style="flex:1">
          <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:.3rem">
            <div style="font-weight:800;font-size:.9rem"><?= esc($ins['title']) ?></div>
            <?php $pc=['high'=>'danger','medium'=>'warning','low'=>'success']; ?>
            <span class="badge-pill badge-<?= $pc[$ins['priority']]??'gray' ?>" style="font-size:.62rem"><?= strtoupper($ins['priority']) ?></span>
          </div>
          <div style="font-size:.82rem;color:var(--text-muted);margin-bottom:.5rem;line-height:1.5"><?= esc($ins['detail']) ?></div>
          <div style="display:flex;align-items:center;gap:.35rem;font-size:.78rem;font-weight:700;color:var(--primary)">
            <i class="fa fa-lightbulb"></i> <?= esc($ins['action']) ?>
          </div>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
    <?php if (empty($insights)): ?>
    <div style="text-align:center;padding:3rem;color:var(--text-muted)">
      <div style="font-size:3rem;margin-bottom:1rem">✅</div>
      <div style="font-weight:700">Everything looks great!</div>
      <div style="font-size:.85rem;margin-top:.5rem">Check back after more orders for insights</div>
    </div>
    <?php endif; ?>
  </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const chart = <?= json_encode($chart) ?>;
new Chart(document.getElementById('revChart'), {
  type: 'bar',
  data: {
    labels: chart.map(d => d.date),
    datasets: [{
      label: 'Revenue',
      data:  chart.map(d => d.rev),
      backgroundColor: 'rgba(255,107,53,.2)',
      borderColor:     '#FF6B35',
      borderWidth:     2,
      borderRadius:    6,
    }]
  },
  options: {
    responsive:true,
    plugins:{ legend:{display:false} },
    scales:{
      y:{ ticks:{ callback: v => '₹'+v.toLocaleString('en-IN') }, grid:{color:'rgba(0,0,0,.04)'} },
      x:{ grid:{display:false} }
    }
  }
});
</script>
<?php $this->endSection(); ?>
