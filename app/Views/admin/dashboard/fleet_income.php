<?php $this->extend('layouts/main'); $this->section('content'); ?>
<style>
.fi-root { padding:0 1rem 2rem; }
.fi-tabs { display:flex; gap:.5rem; margin-bottom:1rem; flex-wrap:wrap; }
.fi-tabs a { padding:.45rem .9rem; border-radius:20px; font-size:.75rem; font-weight:800; text-decoration:none; color:#64748B; background:#F1F5F9; border:1.5px solid transparent; }
.fi-tabs a.on { background:#FF6B35; color:#fff; }
.kpi-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(170px,1fr)); gap:.75rem; margin-bottom:1.25rem; }
.kpi { background:#fff; border-radius:16px; border:1.5px solid #F1F5F9; padding:1rem; }
.kpi h4 { margin:0 0 .55rem; font-size:.68rem; font-weight:800; color:#94A3B8; letter-spacing:.05em; text-transform:uppercase; }
.kpi .v { font-size:1.6rem; font-weight:900; letter-spacing:-.02em; color:#0F172A; line-height:1; }
.kpi .s { font-size:.7rem; color:#94A3B8; margin-top:.35rem; font-weight:600; }
.chart { background:#fff; border:1.5px solid #F1F5F9; border-radius:16px; padding:1rem 1rem .4rem; margin-bottom:1.25rem; }
.bars { display:flex; align-items:flex-end; gap:.7rem; height:120px; padding:.25rem 0; }
.bars .b { flex:1; display:flex; flex-direction:column; justify-content:flex-end; align-items:center; gap:.3rem; height:100%; }
.bars .bar { width:70%; background:linear-gradient(180deg,#FF6B35,#FDBA74); border-radius:8px 8px 0 0; min-height:3px; }
.bars .lb { font-size:.62rem; color:#94A3B8; font-weight:700; }
.bars .amt { font-size:.6rem; color:#334155; font-weight:800; }
.card { background:#fff; border:1.5px solid #F1F5F9; border-radius:16px; padding:1.1rem; margin-bottom:1.25rem; overflow:auto; }
.card h3 { margin:0 0 .8rem; font-size:.95rem; font-weight:800; color:#0F172A; display:flex; align-items:center; gap:.5rem; }
table.fi { width:100%; border-collapse:collapse; font-size:.8rem; min-width:640px; }
table.fi th { text-align:left; font-size:.62rem; text-transform:uppercase; letter-spacing:.05em; color:#94A3B8; padding:.5rem .6rem; border-bottom:1.5px solid #F1F5F9; white-space:nowrap; }
table.fi td { padding:.6rem; border-bottom:1px solid #F8FAFC; white-space:nowrap; }
table.fi tr:last-child td { border-bottom:0; }
.pill { display:inline-block; padding:.18rem .55rem; border-radius:12px; font-size:.62rem; font-weight:800; }
.pill.fleet { background:#FFEDD5; color:#C2410C; }
.pill.dfee { background:#DBEAFE; color:#1D4ED8; }
.pill.own { background:#DCFCE7; color:#15803D; }
</style>

<div class="fi-root">
  <div style="display:flex;align-items:center;gap:.6rem;margin-bottom:1rem;flex-wrap:wrap">
    <h2 style="margin:0;font-weight:900;letter-spacing:-.02em">🛵 DinoviX Income</h2>
    <span style="font-size:.75rem;color:#94A3B8">every rupee we book on driver deliveries (subscription money is on the Revenue page)</span>
  </div>

  <div class="fi-tabs">
    <a href="<?= base_url('super/fleet-income?when=today') ?>" class="<?= $when === 'today' ? 'on' : '' ?>">Today</a>
    <a href="<?= base_url('super/fleet-income?when=month') ?>" class="<?= $when === 'month' ? 'on' : '' ?>">This month</a>
    <a href="<?= base_url('super/fleet-income?when=all') ?>" class="<?= $when === 'all' ? 'on' : '' ?>">All time</a>
    <?php if ($ridFilter): ?><span class="pill dfee">filtered to restaurant #<?= $ridFilter ?> — <a href="<?= base_url('super/fleet-income?when=' . $when) ?>" style="color:#1D4ED8">clear</a></span><?php endif; ?>
  </div>

  <div class="kpi-grid">
    <div class="kpi"><h4><?= $when === 'today' ? 'Earned today' : ($when === 'month' ? 'Earned this month' : 'Earned all time') ?></h4>
      <div class="v" style="color:#F97316">₹<?= number_format($grand, 2) ?></div>
      <div class="s"><?= (int)($counts['fleet_commission'] + $counts['delivery_fee'] + $counts['own_platform_fee']) ?> fee entries booked</div></div>
    <div class="kpi"><h4>Fleet commission</h4>
      <div class="v" style="color:#EA580C">₹<?= number_format($totals['fleet_commission'], 2) ?></div>
      <div class="s"><?= $counts['fleet_commission'] ?> fleet deliveries</div></div>
    <div class="kpi"><h4>Customer delivery fee</h4>
      <div class="v" style="color:#2563EB">₹<?= number_format($totals['delivery_fee'], 2) ?></div>
      <div class="s">booked on fleet runs</div></div>
    <div class="kpi"><h4>Own-rider platform fee</h4>
      <div class="v" style="color:#16A34A">₹<?= number_format($totals['own_platform_fee'], 2) ?></div>
      <div class="s"><?= $counts['own_platform_fee'] ?> own-rider deliveries · ₹5 each</div></div>
  </div>

  <div class="chart">
    <h3 style="margin:.1rem 0 .6rem;font-size:.9rem">📈 Last 6 months</h3>
    <?php $max = max(array_merge([1], array_column($trend, 'amount'))); ?>
    <div class="bars">
      <?php foreach ($trend as $t): ?>
        <div class="b">
          <span class="amt">₹<?= number_format($t['amount'], 0) ?></span>
          <div class="bar" style="height:<?= max(2, round($t['amount'] / $max * 100)) ?>%"></div>
          <span class="lb"><?= esc($t['label']) ?></span>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <div class="card">
    <h3>🏪 Earnings by restaurant</h3>
    <?php if (!$byRest): ?>
      <div style="padding:1.6rem;text-align:center;color:#94A3B8;font-size:.85rem">No delivery income in this period yet — it books the moment a driver marks a delivery complete.</div>
    <?php else: ?>
      <table class="fi">
        <tr><th>Restaurant</th><th>Runs</th><th>Fleet comm.</th><th>Delivery fees</th><th>Own-rider ₹5</th><th>Total</th><th>Their pricing</th><th></th></tr>
        <?php foreach ($byRest as $r): ?>
          <tr>
            <td><b><?= esc($r['name']) ?></b></td>
            <td><?= (int)$r['runs'] ?></td>
            <td>₹<?= number_format((float)($r['fleet'] ?? 0), 2) ?></td>
            <td>₹<?= number_format((float)($r['dfee'] ?? 0), 2) ?></td>
            <td>₹<?= number_format((float)($r['own'] ?? 0), 2) ?></td>
            <td><b style="color:#EA580C">₹<?= number_format((float)$r['total'], 2) ?></b></td>
            <td style="white-space:normal;font-size:.68rem;color:#64748B">
              <?php if ($r['cfg']): ?>
                fleet <?= (float)$r['cfg']['delivery_fleet_commission_pct'] ?>% min ₹<?= (float)$r['cfg']['delivery_fleet_commission_min'] ?>
                · fee ₹<?= (float)$r['cfg']['delivery_fee_base'] ?> + ₹<?= (float)$r['cfg']['delivery_fee_per_km'] ?>/km
                · payout ₹<?= (float)$r['cfg']['delivery_payout_base'] ?> + ₹<?= (float)$r['cfg']['delivery_payout_per_km'] ?>/km
              <?php endif; ?>
            </td>
            <td><a style="font-size:.68rem;font-weight:800;color:#2563EB;text-decoration:none" href="<?= base_url('super/fleet-income?when=' . $when . '&r=' . $r['restaurant_id']) ?>">view only</a></td>
          </tr>
        <?php endforeach; ?>
      </table>
    <?php endif; ?>
  </div>

  <div class="card">
    <h3>🧾 Fee ledger (latest 40)</h3>
    <?php if (!$recent): ?><div style="padding:1rem;color:#94A3B8;font-size:.85rem">Nothing yet.</div>
    <?php else: ?>
      <table class="fi">
        <tr><th>When</th><th>Restaurant</th><th>Order</th><th>Driver</th><th>km</th><th>Type</th><th style="text-align:right">Amount</th></tr>
        <?php foreach ($recent as $f): ?>
          <tr>
            <td style="color:#64748B"><?= esc($f['created_at']) ?></td>
            <td><?= esc($f['restaurant'] ?? '—') ?></td>
            <td><?= esc($f['order_number'] ?? '—') ?></td>
            <td><?= esc($f['driver'] ?? '—') ?></td>
            <td><?= $f['delivery_km'] !== null ? number_format((float)$f['delivery_km'], 2) : '—' ?></td>
            <td><span class="pill <?= $f['fee_type'] === 'fleet_commission' ? 'fleet' : ($f['fee_type'] === 'delivery_fee' ? 'dfee' : 'own') ?>"><?= esc(str_replace('_', ' ', $f['fee_type'])) ?></span></td>
            <td style="text-align:right"><b>₹<?= number_format((float)$f['amount'], 2) ?></b></td>
          </tr>
        <?php endforeach; ?>
      </table>
    <?php endif; ?>
  </div>
</div>
<?php $this->endSection(); ?>
