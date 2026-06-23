<?php $this->extend('layouts/main'); $this->section('content'); ?>
<div style="padding:0 1rem">
  <div style="display:flex;justify-content:flex-end;margin-bottom:1rem">
    <a href="<?= base_url('super/plans/create') ?>" class="btn btn-primary"><i class="fa fa-plus"></i> New Plan</a>
  </div>
  <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:.75rem">
    <?php foreach ($plans as $plan): ?>
    <div class="card" style="border-top:4px solid <?= $plan['is_active'] ? 'var(--primary)' : 'var(--border)' ?>">
      <div class="card-body">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:.5rem">
          <div>
            <div style="font-weight:800;font-size:1.05rem"><?= esc($plan['name']) ?></div>
            <div style="font-size:1.4rem;font-weight:800;color:var(--primary);line-height:1.2">
              ₹<?= number_format($plan['price_monthly']) ?><span style="font-size:.8rem;color:var(--text-muted);font-weight:400">/mo</span>
            </div>
            <div style="font-size:.75rem;color:var(--text-muted)">₹<?= number_format($plan['price_yearly']) ?>/year</div>
          </div>
          <span class="badge-pill badge-<?= $plan['is_active']?'success':'gray' ?>"><?= $plan['is_active']?'Active':'Off' ?></span>
        </div>
        <div style="display:flex;flex-direction:column;gap:.25rem;font-size:.82rem;margin-bottom:.75rem">
          <div><i class="fa fa-code-branch" style="color:var(--primary);width:16px"></i>
            <?= $plan['max_branches'] < 0 ? 'Unlimited' : $plan['max_branches'] ?> branches</div>
          <div><i class="fa fa-users" style="color:var(--primary);width:16px"></i>
            <?= $plan['max_users'] < 0 ? 'Unlimited' : $plan['max_users'] ?> users</div>
          <div><i class="fa fa-utensils" style="color:var(--primary);width:16px"></i>
            <?= $plan['max_menu_items'] < 0 ? 'Unlimited' : $plan['max_menu_items'] ?> items</div>
          <?php foreach (['allow_online_ordering'=>'Online ordering','allow_loyalty'=>'Loyalty','allow_reports_advanced'=>'Advanced reports','allow_api_access'=>'API access'] as $k=>$v): ?>
          <div><i class="fa fa-<?= $plan[$k]?'check" style="color:var(--success)':'times" style="color:var(--border)' ?>;width:16px"></i> <?= $v ?></div>
          <?php endforeach; ?>
        </div>
        <div style="display:flex;justify-content:space-between;align-items:center;border-top:1px solid var(--border);padding-top:.625rem">
          <span style="font-size:.8rem;color:var(--text-muted)"><?= $plan['subscriber_count'] ?? 0 ?> subscribers</span>
          <a href="<?= base_url('super/plans/edit/'.$plan['id']) ?>" class="btn btn-sm btn-outline">
            <i class="fa fa-edit"></i> Edit
          </a>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
    <?php if (empty($plans)): ?>
    <div class="card"><div class="empty-state" style="padding:3rem"><i class="fa fa-layer-group"></i><p>No plans yet</p></div></div>
    <?php endif; ?>
  </div>
</div>
<?php $this->endSection(); ?>
