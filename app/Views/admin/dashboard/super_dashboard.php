<?php $this->extend('layouts/main'); $this->section('content'); ?>
<style>
/* ── SAAS DASHBOARD ──────────────────────────────────── */
.sd-root { padding:0 1rem 2rem; }

/* KPI grid */
.kpi-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(160px,1fr)); gap:.75rem; margin-bottom:1.25rem; }
.kpi { background:#fff; border-radius:16px; border:1.5px solid #F1F5F9; padding:1rem; position:relative; overflow:hidden; }
.kpi::before { content:''; position:absolute; top:0; left:0; right:0; height:3px; border-radius:16px 16px 0 0; }
.kpi.blue::before   { background:linear-gradient(90deg,#3B82F6,#60A5FA); }
.kpi.green::before  { background:linear-gradient(90deg,#22C55E,#4ADE80); }
.kpi.orange::before { background:linear-gradient(90deg,#F59E0B,#FCD34D); }
.kpi.red::before    { background:linear-gradient(90deg,#EF4444,#F87171); }
.kpi.purple::before { background:linear-gradient(90deg,#8B5CF6,#A78BFA); }
.kpi.teal::before   { background:linear-gradient(90deg,#14B8A6,#2DD4BF); }
.kpi-icon { width:36px; height:36px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:.95rem; margin-bottom:.625rem; }
.kpi.blue   .kpi-icon { background:#EFF6FF; color:#2563EB; }
.kpi.green  .kpi-icon { background:#F0FDF4; color:#15803D; }
.kpi.orange .kpi-icon { background:#FFF7ED; color:#C2410C; }
.kpi.red    .kpi-icon { background:#FFF1F2; color:#B91C1C; }
.kpi.purple .kpi-icon { background:#F5F3FF; color:#6D28D9; }
.kpi.teal   .kpi-icon { background:#F0FDFA; color:#0F766E; }
.kpi-val { font-size:1.6rem; font-weight:900; letter-spacing:-.02em; color:#0F172A; line-height:1; }
.kpi-lbl { font-size:.68rem; color:#94A3B8; margin-top:.2rem; font-weight:600; text-transform:uppercase; letter-spacing:.04em; }
.kpi-delta { font-size:.68rem; font-weight:800; margin-top:.35rem; }
.kpi-delta.up   { color:#15803D; }
.kpi-delta.warn { color:#92400E; }

/* Revenue + Growth charts row */
.charts-row { display:grid; grid-template-columns:1fr 1fr; gap:.75rem; margin-bottom:1.25rem; }
@media(max-width:600px){ .charts-row{grid-template-columns:1fr} }
.chart-card { background:#fff; border-radius:16px; border:1.5px solid #F1F5F9; padding:1rem; }
.chart-title { font-size:.78rem; font-weight:900; color:#64748B; text-transform:uppercase; letter-spacing:.06em; margin-bottom:.75rem; }
.bar-chart { display:flex; align-items:flex-end; gap:.35rem; height:80px; }
.bc-bar-wrap { flex:1; display:flex; flex-direction:column; align-items:center; gap:.25rem; }
.bc-bar { width:100%; border-radius:6px 6px 0 0; min-height:4px; transition:height .4s ease; }
.bc-lbl { font-size:.58rem; color:#94A3B8; font-weight:700; white-space:nowrap; }
.bc-val { font-size:.6rem; color:#334155; font-weight:800; }

/* Plan distribution */
.plan-dist { display:flex; flex-direction:column; gap:.5rem; }
.plan-row { display:flex; align-items:center; gap:.625rem; }
.plan-bar-track { flex:1; height:8px; background:#F1F5F9; border-radius:4px; overflow:hidden; }
.plan-bar-fill  { height:100%; border-radius:4px; transition:width .6s ease; }
.plan-name { font-size:.75rem; font-weight:700; color:#334155; min-width:70px; }
.plan-pct  { font-size:.72rem; font-weight:800; color:#64748B; min-width:30px; text-align:right; }

/* Bottom grid */
.bottom-grid { display:grid; grid-template-columns:1fr 1fr; gap:.75rem; }
@media(max-width:600px){ .bottom-grid{grid-template-columns:1fr} }
.bd-card { background:#fff; border-radius:16px; border:1.5px solid #F1F5F9; overflow:hidden; }
.bd-card-hdr { padding:.875rem 1rem .5rem; display:flex; align-items:center; justify-content:space-between; }
.bd-card-title { font-size:.82rem; font-weight:900; color:#0F172A; }
.bd-card-sub   { font-size:.68rem; color:#94A3B8; }
.bd-table { width:100%; border-collapse:collapse; }
.bd-table td { padding:.55rem 1rem; font-size:.78rem; border-bottom:1px solid #F8FAFC; }
.bd-table tr:last-child td { border-bottom:none; }
.bd-table tr:hover td { background:#FAFAFA; }
.s-badge { font-size:.62rem; font-weight:800; padding:.18rem .45rem; border-radius:8px; }
.s-active    { background:#F0FDF4; color:#15803D; }
.s-trial     { background:#FFF7ED; color:#92400E; }
.s-expired   { background:#FFF1F2; color:#B91C1C; }
.s-suspended { background:#FFF1F2; color:#B91C1C; }

/* Alert banner */
.alert-expire { background:linear-gradient(90deg,#FEF3C7,#FFF7ED); border:1.5px solid #FCD34D; border-radius:12px; padding:.75rem 1rem; margin-bottom:1rem; display:flex; align-items:center; gap:.75rem; flex-wrap:wrap; }

/* Saas pitch section */
.pitch { background:linear-gradient(135deg,#0F172A 0%,#1E293B 100%); border-radius:16px; padding:1.25rem; margin-bottom:1.25rem; color:#fff; }
.pitch-title { font-size:1rem; font-weight:900; margin-bottom:.625rem; }
.pitch-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(140px,1fr)); gap:.5rem; }
.pitch-item { background:rgba(255,255,255,.06); border:1px solid rgba(255,255,255,.1); border-radius:10px; padding:.625rem; }
.pitch-icon { font-size:1.1rem; margin-bottom:.3rem; }
.pitch-feat { font-size:.72rem; font-weight:800; color:#fff; }
.pitch-desc { font-size:.62rem; color:rgba(255,255,255,.45); margin-top:.15rem; }
</style>

<div class="sd-root">

  <!-- Expiring soon alert -->
  <?php if ($stats['expiring_count'] > 0): ?>
  <div class="alert-expire">
    <i class="fa fa-triangle-exclamation" style="color:#F59E0B;font-size:1.1rem"></i>
    <span style="font-weight:700;font-size:.85rem"><strong><?= $stats['expiring_count'] ?></strong> subscription<?= $stats['expiring_count']>1?'s':'' ?> expiring within 7 days</span>
    <?php foreach ($expiring as $e): ?>
    <button onclick="openPaymentModal(<?= $e['id'] ?>,'<?= esc(addslashes($e['name'])) ?>')"
            style="background:#F59E0B;color:#fff;border:none;border-radius:8px;padding:.3rem .75rem;font-weight:800;font-size:.72rem;cursor:pointer">
      <?= esc($e['name']) ?> — <?= date('d M',strtotime($e['subscription_ends_at'])) ?>
    </button>
    <?php endforeach; ?>
    <a href="<?= base_url('super/subscriptions') ?>" style="margin-left:auto;font-size:.75rem;font-weight:700;color:#92400E">Manage all →</a>
  </div>
  <?php endif; ?>

  <!-- KPI Cards -->
  <div class="kpi-grid">
    <div class="kpi blue">
      <div class="kpi-icon"><i class="fa fa-store"></i></div>
      <div class="kpi-val"><?= $stats['total'] ?></div>
      <div class="kpi-lbl">Total Restaurants</div>
      <?php if ($stats['new_month'] > 0): ?>
      <div class="kpi-delta up"><i class="fa fa-arrow-up"></i> <?= $stats['new_month'] ?> this month</div>
      <?php endif; ?>
    </div>
    <div class="kpi green">
      <div class="kpi-icon"><i class="fa fa-check-circle"></i></div>
      <div class="kpi-val"><?= $stats['active'] ?></div>
      <div class="kpi-lbl">Active Plans</div>
      <div class="kpi-delta up"><?= $stats['total'] > 0 ? round($stats['active']/$stats['total']*100) : 0 ?>% active rate</div>
    </div>
    <div class="kpi orange">
      <div class="kpi-icon"><i class="fa fa-clock"></i></div>
      <div class="kpi-val"><?= $stats['trial'] ?></div>
      <div class="kpi-lbl">On Trial</div>
    </div>
    <div class="kpi red">
      <div class="kpi-icon"><i class="fa fa-ban"></i></div>
      <div class="kpi-val"><?= $stats['expired'] ?></div>
      <div class="kpi-lbl">Inactive / Expired</div>
    </div>
    <div class="kpi green">
      <div class="kpi-icon"><i class="fa fa-indian-rupee-sign"></i></div>
      <div class="kpi-val">₹<?= number_format($stats['mrr'], 0) ?></div>
      <div class="kpi-lbl">Monthly Revenue</div>
    </div>
    <div class="kpi purple">
      <div class="kpi-icon"><i class="fa fa-calendar-check"></i></div>
      <div class="kpi-val">₹<?= number_format($stats['arr'] / 1000, 1) ?>K</div>
      <div class="kpi-lbl">Annual Run Rate</div>
    </div>
    <div class="kpi teal">
      <div class="kpi-icon"><i class="fa fa-receipt"></i></div>
      <div class="kpi-val"><?= number_format($stats['today_orders']) ?></div>
      <div class="kpi-lbl">Orders Today</div>
      <?php if ($stats['today_qr'] > 0): ?>
      <div class="kpi-delta up"><i class="fa fa-qrcode"></i> <?= $stats['today_qr'] ?> via QR</div>
      <?php endif; ?>
    </div>
    <div class="kpi blue">
      <div class="kpi-icon"><i class="fa fa-wallet"></i></div>
      <div class="kpi-val">₹<?= number_format($stats['today_revenue'], 0) ?></div>
      <div class="kpi-lbl">Revenue Today</div>
    </div>
  </div>

  <!-- Charts row -->
  <div class="charts-row">
    <!-- Revenue bar chart -->
    <div class="chart-card">
      <div class="chart-title"><i class="fa fa-chart-bar"></i> Revenue — Last 6 Months</div>
      <?php
        $maxRev = max(array_column($revenueData,'amount')) ?: 1;
        $colors = ['#3B82F6','#60A5FA','#818CF8','#8B5CF6','#A78BFA','#C4B5FD'];
      ?>
      <div class="bar-chart">
        <?php foreach ($revenueData as $i => $rd): ?>
        <div class="bc-bar-wrap">
          <div class="bc-val">₹<?= $rd['amount']>999 ? number_format($rd['amount']/1000,1).'K' : number_format($rd['amount'],0) ?></div>
          <div class="bc-bar" style="background:<?= $colors[$i%6] ?>;height:<?= max(4, round($rd['amount']/$maxRev*60)) ?>px"></div>
          <div class="bc-lbl"><?= $rd['month'] ?></div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Growth bar chart -->
    <div class="chart-card">
      <div class="chart-title"><i class="fa fa-chart-line"></i> New Restaurants — Last 6 Months</div>
      <?php $maxG = max(array_column($growthData,'count')) ?: 1; ?>
      <div class="bar-chart">
        <?php foreach ($growthData as $i => $gd): ?>
        <div class="bc-bar-wrap">
          <div class="bc-val"><?= $gd['count'] ?></div>
          <div class="bc-bar" style="background:#22C55E;opacity:<?= 0.4 + ($i * 0.1) ?>;height:<?= max(4, round($gd['count']/$maxG*60)) ?>px"></div>
          <div class="bc-lbl"><?= $gd['month'] ?></div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <!-- Plan Distribution + Expiring -->
  <div class="charts-row" style="margin-bottom:1.25rem">
    <div class="chart-card">
      <div class="chart-title"><i class="fa fa-pie-chart"></i> Plan Distribution (Active)</div>
      <?php
        $planColors = ['#3B82F6','#22C55E','#F59E0B','#EF4444','#8B5CF6','#14B8A6'];
        $pi = 0;
      ?>
      <div class="plan-dist">
        <?php if (empty($planDist)): ?>
        <div style="color:#94A3B8;font-size:.8rem;text-align:center;padding:1rem">No active subscriptions yet</div>
        <?php else: foreach ($planDist as $pd): ?>
        <div class="plan-row">
          <div class="plan-name" style="display:flex;align-items:center;gap:.35rem">
            <span style="width:8px;height:8px;border-radius:50%;background:<?= $planColors[$pi%6] ?>;flex-shrink:0"></span>
            <?= esc($pd['plan_name'] ?? 'N/A') ?>
          </div>
          <div class="plan-bar-track">
            <div class="plan-bar-fill" style="width:<?= $pd['pct'] ?>%;background:<?= $planColors[$pi++%6] ?>"></div>
          </div>
          <div class="plan-pct"><?= $pd['count'] ?></div>
        </div>
        <?php endforeach; endif; ?>
      </div>
    </div>

    <!-- Quick actions -->
    <div class="chart-card">
      <div class="chart-title"><i class="fa fa-bolt"></i> Quick Actions</div>
      <div style="display:flex;flex-direction:column;gap:.5rem">
        <a href="<?= base_url('super/restaurants/create') ?>" style="display:flex;align-items:center;gap:.625rem;padding:.625rem .75rem;background:#F8FAFC;border-radius:10px;text-decoration:none;color:#0F172A;font-size:.8rem;font-weight:700;border:1.5px solid #F1F5F9">
          <span style="width:28px;height:28px;background:#EFF6FF;color:#2563EB;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:.8rem"><i class="fa fa-plus"></i></span>
          Add New Restaurant
        </a>
        <a href="<?= base_url('super/subscriptions') ?>" style="display:flex;align-items:center;gap:.625rem;padding:.625rem .75rem;background:#F8FAFC;border-radius:10px;text-decoration:none;color:#0F172A;font-size:.8rem;font-weight:700;border:1.5px solid #F1F5F9">
          <span style="width:28px;height:28px;background:#F0FDF4;color:#15803D;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:.8rem"><i class="fa fa-credit-card"></i></span>
          Manage Subscriptions
        </a>
        <a href="<?= base_url('super/plans') ?>" style="display:flex;align-items:center;gap:.625rem;padding:.625rem .75rem;background:#F8FAFC;border-radius:10px;text-decoration:none;color:#0F172A;font-size:.8rem;font-weight:700;border:1.5px solid #F1F5F9">
          <span style="width:28px;height:28px;background:#F5F3FF;color:#6D28D9;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:.8rem"><i class="fa fa-tag"></i></span>
          Manage Plans & Pricing
        </a>
        <a href="<?= base_url('super/restaurants') ?>" style="display:flex;align-items:center;gap:.625rem;padding:.625rem .75rem;background:#F8FAFC;border-radius:10px;text-decoration:none;color:#0F172A;font-size:.8rem;font-weight:700;border:1.5px solid #F1F5F9">
          <span style="width:28px;height:28px;background:#FFF7ED;color:#C2410C;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:.8rem"><i class="fa fa-list"></i></span>
          All Restaurants
        </a>
      </div>
    </div>
  </div>

  <!-- SaaS feature pitch panel -->
  <div class="pitch" style="margin-bottom:1.25rem">
    <div class="pitch-title">🚀 Why Clients Choose RestOne CRM</div>
    <div class="pitch-grid">
      <div class="pitch-item">
        <div class="pitch-icon">📱</div>
        <div class="pitch-feat">QR Ordering</div>
        <div class="pitch-desc">Customers scan & order from their phone — zero contact</div>
      </div>
      <div class="pitch-item">
        <div class="pitch-icon">👨‍🍳</div>
        <div class="pitch-feat">Live Kitchen Display</div>
        <div class="pitch-desc">Real-time KOT flow from table to kitchen to customer</div>
      </div>
      <div class="pitch-item">
        <div class="pitch-icon">🏪</div>
        <div class="pitch-feat">Multi-Branch</div>
        <div class="pitch-desc">One dashboard for all your restaurant locations</div>
      </div>
      <div class="pitch-item">
        <div class="pitch-icon">🖨️</div>
        <div class="pitch-feat">Thermal Printing</div>
        <div class="pitch-desc">80mm/57mm ESC/POS bill & KOT printing over TCP/IP</div>
      </div>
      <div class="pitch-item">
        <div class="pitch-icon">📊</div>
        <div class="pitch-feat">Smart Reports</div>
        <div class="pitch-desc">Sales, tax, category and shift-wise analytics</div>
      </div>
      <div class="pitch-item">
        <div class="pitch-icon">💳</div>
        <div class="pitch-feat">Loyalty & Coupons</div>
        <div class="pitch-desc">Points, discount coupons, birthday offers built-in</div>
      </div>
      <div class="pitch-item">
        <div class="pitch-icon">🔒</div>
        <div class="pitch-feat">Role-Based Access</div>
        <div class="pitch-desc">5 roles: Super Admin → Manager → Cashier → Kitchen</div>
      </div>
      <div class="pitch-item">
        <div class="pitch-icon">☁️</div>
        <div class="pitch-feat">Cloud SaaS</div>
        <div class="pitch-desc">No installation, always updated, accessible anywhere</div>
      </div>
    </div>
  </div>

  <!-- Recent restaurants table -->
  <div class="bd-card">
    <div class="bd-card-hdr">
      <div>
        <div class="bd-card-title">Recent Restaurants</div>
        <div class="bd-card-sub">Latest onboarded clients</div>
      </div>
      <a href="<?= base_url('super/restaurants') ?>" style="font-size:.75rem;font-weight:700;color:var(--primary)">View all →</a>
    </div>
    <div style="overflow-x:auto">
      <table class="bd-table" style="width:100%">
        <thead>
          <tr style="background:#F8FAFC">
            <td style="font-size:.68rem;font-weight:900;color:#94A3B8;text-transform:uppercase;letter-spacing:.05em">Restaurant</td>
            <td style="font-size:.68rem;font-weight:900;color:#94A3B8;text-transform:uppercase;letter-spacing:.05em">Plan</td>
            <td style="font-size:.68rem;font-weight:900;color:#94A3B8;text-transform:uppercase;letter-spacing:.05em">Status</td>
            <td style="font-size:.68rem;font-weight:900;color:#94A3B8;text-transform:uppercase;letter-spacing:.05em">Orders Today</td>
            <td style="font-size:.68rem;font-weight:900;color:#94A3B8;text-transform:uppercase;letter-spacing:.05em">Joined</td>
            <td></td>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($recent)): ?>
          <tr><td colspan="6" style="text-align:center;color:#94A3B8;padding:2rem">No restaurants yet</td></tr>
          <?php else: foreach ($recent as $r):
            $sc = ['active'=>'s-active','trial'=>'s-trial','expired'=>'s-expired','suspended'=>'s-suspended'];
          ?>
          <tr>
            <td>
              <div style="font-weight:700;font-size:.82rem"><?= esc($r['name']) ?></div>
              <div style="font-size:.68rem;color:#94A3B8"><?= esc($r['email']) ?></div>
            </td>
            <td><span class="s-badge" style="background:#EFF6FF;color:#1D4ED8"><?= esc($r['plan_name']??'—') ?></span></td>
            <td><span class="s-badge <?= $sc[$r['subscription_status']]??'s-expired' ?>"><?= ucfirst($r['subscription_status']) ?></span></td>
            <td style="font-weight:800;color:<?= $r['orders_today']>0?'#15803D':'#94A3B8' ?>"><?= $r['orders_today'] ?></td>
            <td style="color:#94A3B8;font-size:.75rem"><?= date('d M Y',strtotime($r['created_at'])) ?></td>
            <td>
              <div style="display:flex;gap:.3rem">
                <a href="<?= base_url('super/restaurants/view/'.$r['id']) ?>" style="padding:.3rem .55rem;background:#F1F5F9;color:#334155;border-radius:8px;font-size:.68rem;font-weight:700;text-decoration:none">View</a>
                <form method="POST" action="<?= base_url('super/restaurants/login-as/'.$r['id']) ?>" style="margin:0">
                  <?= csrf_field() ?>
                  <button type="submit" style="padding:.3rem .55rem;background:var(--primary);color:#fff;border:none;border-radius:8px;font-size:.68rem;font-weight:700;cursor:pointer">Login</button>
                </form>
              </div>
            </td>
          </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>

</div>
<?php $this->endSection(); ?>
