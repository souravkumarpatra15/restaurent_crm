<?php $this->extend('layouts/main'); $this->section('content'); ?>
<div style="padding:0 1rem">
  <div style="display:flex;justify-content:flex-end;margin-bottom:1rem">
    <button class="btn btn-primary" onclick="openModal('addPlanModal')"><i class="fa fa-plus"></i> New Plan</button>
  </div>
  <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:.75rem">
    <?php foreach ($plans as $plan): ?>
    <div class="card" style="border-top:4px solid var(--primary)">
      <div class="card-body">
        <div style="font-weight:800;font-size:1.1rem;margin-bottom:.25rem"><?= esc($plan['name']) ?></div>
        <div style="font-size:1.5rem;font-weight:800;color:var(--primary);margin-bottom:.5rem">
          ₹<?= number_format($plan['price_monthly']) ?><span style="font-size:.8rem;font-weight:400;color:var(--text-muted)">/mo</span>
        </div>
        <div style="font-size:.78rem;color:var(--text-muted);margin-bottom:.75rem">
          ₹<?= number_format($plan['price_yearly']) ?>/year
        </div>
        <div style="display:flex;flex-direction:column;gap:.3rem;font-size:.82rem">
          <div><i class="fa fa-code-branch" style="color:var(--primary);width:16px"></i> <?= $plan['max_branches'] < 0 ? 'Unlimited' : $plan['max_branches'] ?> branches</div>
          <div><i class="fa fa-users" style="color:var(--primary);width:16px"></i> <?= $plan['max_users'] < 0 ? 'Unlimited' : $plan['max_users'] ?> users</div>
          <div><i class="fa fa-utensils" style="color:var(--primary);width:16px"></i> <?= $plan['max_menu_items'] < 0 ? 'Unlimited' : $plan['max_menu_items'] ?> menu items</div>
          <div><i class="fa fa-<?= $plan['allow_online_ordering']?'check text-success':'times text-muted' ?>" style="width:16px"></i> Online ordering</div>
          <div><i class="fa fa-<?= $plan['allow_loyalty']?'check text-success':'times text-muted' ?>" style="width:16px"></i> Loyalty program</div>
          <div><i class="fa fa-<?= $plan['allow_reports_advanced']?'check text-success':'times text-muted' ?>" style="width:16px"></i> Advanced reports</div>
        </div>
        <div style="margin-top:.75rem;padding-top:.75rem;border-top:1px solid var(--border);display:flex;justify-content:space-between;align-items:center">
          <span class="badge-pill badge-<?= $plan['is_active']?'success':'gray' ?>"><?= $plan['is_active']?'Active':'Inactive' ?></span>
          <span style="font-size:.78rem;color:var(--text-muted)"><?= $plan['subscriber_count'] ?? 0 ?> subscribers</span>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</div>
<?php $this->endSection(); ?>
