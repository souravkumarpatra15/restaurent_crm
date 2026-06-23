<?php $this->extend('layouts/main');
$this->section('content'); ?>
<div style="padding:0 1rem">

  <!-- Stats -->
  <div class="stats-grid" style="margin-bottom:1rem">
    <div class="stat-card green">
      <div class="stat-icon green"><i class="fa fa-store"></i></div>
      <div>
        <div class="stat-value"><?= number_format($stats['total_restaurants']) ?></div>
        <div class="stat-label">Total Restaurants</div>
        <div class="stat-change up"><i class="fa fa-arrow-up"></i> <?= $stats['new_this_month'] ?> this month</div>
      </div>
    </div>
    <div class="stat-card blue">
      <div class="stat-icon blue"><i class="fa fa-check-circle"></i></div>
      <div>
        <div class="stat-value"><?= $stats['active_subscriptions'] ?></div>
        <div class="stat-label">Active Subscriptions</div>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon"><i class="fa fa-indian-rupee-sign"></i></div>
      <div>
        <div class="stat-value">₹<?= number_format($stats['mrr'] / 1000, 1) ?>K</div>
        <div class="stat-label">MRR</div>
      </div>
    </div>
    <div class="stat-card orange">
      <div class="stat-icon orange"><i class="fa fa-clock"></i></div>
      <div>
        <div class="stat-value"><?= $stats['trials'] ?></div>
        <div class="stat-label">On Trial</div>
      </div>
    </div>
  </div>

  <!-- Quick Actions -->
  <div style="display:flex;gap:.5rem;flex-wrap:wrap;margin-bottom:1rem">
    <a href="<?= base_url('super/restaurants/create') ?>" class="btn btn-primary"><i class="fa fa-plus"></i> Add Restaurant</a>
    <a href="<?= base_url('super/plans') ?>" class="btn btn-outline"><i class="fa fa-layer-group"></i> Plans</a>
    <a href="<?= base_url('super/subscriptions') ?>" class="btn btn-outline"><i class="fa fa-credit-card"></i> Subscriptions</a>
    <a href="<?= base_url('super/reports/revenue') ?>" class="btn btn-outline"><i class="fa fa-chart-line"></i> Revenue</a>
  </div>

  <!-- Expiring Soon Alert -->
  <?php if (!empty($expiringSoon)): ?>
    <div style="background:#FFFBEB;border:1px solid #F6AD55;border-radius:10px;padding:.875rem 1rem;margin-bottom:1rem;display:flex;align-items:flex-start;gap:.75rem">
      <i class="fa fa-triangle-exclamation" style="color:var(--warning);margin-top:.1rem"></i>
      <div>
        <div style="font-weight:700;font-size:.875rem;color:var(--warning)">Expiring Soon (<?= count($expiringSoon) ?>)</div>
        <div style="font-size:.8rem;color:var(--text-muted);margin-top:.25rem">
          <?php foreach ($expiringSoon as $r): ?>
            <span style="display:inline-block;background:#fff;border-radius:6px;padding:.2rem .6rem;margin:.15rem;font-size:.78rem">
              <?= esc($r['name']) ?> · <?= date('d M', strtotime($r['subscription_ends_at'])) ?>
            </span>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  <?php endif; ?>

  <!-- Recent Restaurants -->
  <div class="card" style="margin-bottom:1rem">
    <div class="card-header">
      <span class="card-title"><i class="fa fa-store" style="color:var(--primary)"></i> Restaurants</span>
      <a href="<?= base_url('super/restaurants') ?>" class="btn btn-sm btn-outline">View All</a>
    </div>
    <div class="table-wrap">
      <table class="table">
        <thead>
          <tr>
            <th>Restaurant</th>
            <th>Plan</th>
            <th>Status</th>
            <th>Branches</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($restaurants)): ?>
            <tr>
              <td colspan="5">
                <div class="empty-state" style="padding:2rem"><i class="fa fa-store"></i>
                  <p>No restaurants yet. <a href="<?= base_url('super/restaurants/create') ?>">Add one</a></p>
                </div>
              </td>
            </tr>
            <?php else: foreach ($restaurants as $r):
              $sc = ['active' => 'success', 'trial' => 'warning', 'suspended' => 'danger', 'expired' => 'danger', 'cancelled' => 'gray'];
            ?>
              <tr>
                <td>
                  <div style="display:flex;align-items:center;gap:.6rem">
                    <div style="width:34px;height:34px;border-radius:8px;background:var(--primary-light);color:var(--primary);display:flex;align-items:center;justify-content:center;font-weight:800;flex-shrink:0"><?= strtoupper(substr($r['name'], 0, 1)) ?></div>
                    <div>
                      <div style="font-weight:600;font-size:.875rem"><?= esc($r['name']) ?></div>
                      <div style="font-size:.72rem;color:var(--text-muted)"><?= esc($r['email']) ?></div>
                    </div>
                  </div>
                </td>
                <td><span class="badge-pill badge-primary"><?= esc($r['plan_name'] ?? 'N/A') ?></span></td>
                <td><span class="badge-pill badge-<?= $sc[$r['subscription_status']] ?? 'gray' ?>"><?= ucfirst($r['subscription_status']) ?></span></td>
                <td style="text-align:center"><?= $r['branch_count'] ?? 0 ?></td>
                <td>
                  <div style="display:flex;gap:.3rem">
                    <a href="<?= base_url('super/restaurants/view/' . $r['id']) ?>" class="btn btn-sm btn-outline"><i class="fa fa-eye"></i></a>
                    <a href="<?= base_url('super/restaurants/edit/' . $r['id']) ?>" class="btn btn-sm btn-outline"><i class="fa fa-edit"></i></a>
                    <form method="POST" action="<?= base_url('super/restaurants/login-as/' . $r['id']) ?>" style="margin:0">
                      <?= csrf_field() ?>
                      <button type="submit" class="btn btn-sm btn-primary" title="Login As"><i class="fa fa-right-to-bracket"></i></button>
                    </form>
                  </div>
                </td>
              </tr>
          <?php endforeach;
          endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Plan Distribution -->
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
    <div class="card">
      <div class="card-header"><span class="card-title"><i class="fa fa-chart-pie" style="color:var(--primary)"></i> By Plan</span></div>
      <div class="card-body" style="padding:.75rem">
        <?php foreach ($planStats as $plan): ?>
          <div style="margin-bottom:.625rem">
            <div style="display:flex;justify-content:space-between;margin-bottom:.25rem;font-size:.82rem">
              <span><?= esc($plan['plan_name']) ?></span>
              <span style="font-weight:700"><?= $plan['count'] ?> <span style="color:var(--text-muted);font-weight:400">(<?= $plan['percent'] ?>%)</span></span>
            </div>
            <div style="height:6px;background:var(--bg);border-radius:3px">
              <div style="height:100%;width:<?= $plan['percent'] ?>%;background:var(--primary);border-radius:3px;transition:width .6s"></div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
    <div class="card">
      <div class="card-header"><span class="card-title"><i class="fa fa-chart-pie" style="color:var(--primary)"></i> By Status</span></div>
      <div class="card-body" style="padding:.75rem">
        <?php
        $statusStats = ['active' => 0, 'trial' => 0, 'suspended' => 0, 'expired' => 0, 'cancelled' => 0];
        foreach ($restaurants as $r) {
          $statusStats[$r['subscription_status']] = ($statusStats[$r['subscription_status']] ?? 0) + 1;
        }
        $sTotal = array_sum($statusStats) ?: 1;
        $sColors = ['active' => 'var(--success)', 'trial' => 'var(--warning)', 'suspended' => 'var(--danger)', 'expired' => '#FC8181', 'cancelled' => 'var(--text-muted)'];
        foreach ($statusStats as $k => $v): if (!$v) continue; ?>
          <div style="margin-bottom:.5rem;display:flex;align-items:center;gap:.5rem;font-size:.82rem">
            <div style="width:10px;height:10px;border-radius:50%;background:<?= $sColors[$k] ?? 'var(--border)' ?>;flex-shrink:0"></div>
            <span style="flex:1"><?= ucfirst($k) ?></span>
            <strong><?= $v ?></strong>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

</div>
<?php $this->endSection(); ?>