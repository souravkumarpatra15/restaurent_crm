<?php $this->extend('layouts/main'); $this->section('content'); ?>
<style>
.tl-wrap{padding:0 1rem 2rem}.tl-stats{display:grid;grid-template-columns:repeat(4,1fr);gap:.75rem;margin-bottom:1rem}.tl-stat{background:#fff;border:1.5px solid #F1F5F9;border-radius:14px;padding:1rem}.tl-stat strong{display:block;font-size:1.5rem;color:#0F172A}.tl-stat span{font-size:.7rem;color:#64748B;text-transform:uppercase;font-weight:700}.tl-toolbar{background:#fff;border:1.5px solid #F1F5F9;border-radius:14px;padding:.8rem;display:flex;gap:.6rem;align-items:center;margin-bottom:1rem;flex-wrap:wrap}.tl-toolbar input,.tl-toolbar select{border:1px solid #E2E8F0;border-radius:9px;padding:.6rem .75rem;font:500 .8rem inherit;outline:none;background:#fff}.tl-toolbar input{min-width:240px}.tl-table-wrap{background:#fff;border:1.5px solid #F1F5F9;border-radius:14px;overflow:auto}.tl-table{width:100%;border-collapse:collapse;min-width:950px}.tl-table th{text-align:left;font-size:.68rem;color:#64748B;text-transform:uppercase;letter-spacing:.04em;padding:.8rem;border-bottom:1px solid #E2E8F0;white-space:nowrap}.tl-table td{padding:.8rem;border-bottom:1px solid #F1F5F9;font-size:.78rem;color:#334155;vertical-align:top}.tl-table tr:last-child td{border-bottom:0}.tl-name{font-weight:800;color:#0F172A}.tl-muted{color:#94A3B8;font-size:.7rem;margin-top:.15rem}.tl-status{border:0;border-radius:999px;padding:.3rem .55rem;font-size:.65rem;font-weight:800}.tl-new{background:#FFF7ED;color:#C2410C}.tl-contacted{background:#EFF6FF;color:#1D4ED8}.tl-converted{background:#F0FDF4;color:#15803D}.tl-lost{background:#F1F5F9;color:#64748B}.tl-actions{display:flex;gap:.4rem;align-items:center}.tl-actions select{border:1px solid #E2E8F0;border-radius:8px;padding:.35rem;font-size:.68rem}.tl-btn{border:0;border-radius:8px;padding:.4rem .55rem;background:#FFF7ED;color:#C2410C;font-weight:700;cursor:pointer}.tl-empty{text-align:center;padding:3rem;color:#94A3B8}.tl-message{max-width:220px;line-height:1.5}.tl-count{margin-left:auto;font-size:.75rem;color:#64748B}@media(max-width:700px){.tl-stats{grid-template-columns:1fr 1fr}.tl-toolbar input{min-width:100%}}
</style>
<div class="tl-wrap">
  <?php if (session('success')): ?><div class="alert alert-success" style="margin-bottom:1rem"><?= esc(session('success')) ?></div><?php endif; ?>
  <?php if (session('error')): ?><div class="alert alert-danger" style="margin-bottom:1rem"><?= esc(session('error')) ?></div><?php endif; ?>

  <div class="tl-stats">
    <div class="tl-stat"><strong><?= (int)$counts['new'] ?></strong><span>New Leads</span></div>
    <div class="tl-stat"><strong><?= (int)$counts['contacted'] ?></strong><span>Contacted</span></div>
    <div class="tl-stat"><strong><?= (int)$counts['converted'] ?></strong><span>Converted</span></div>
    <div class="tl-stat"><strong><?= (int)$counts['lost'] ?></strong><span>Lost</span></div>
  </div>

  <form class="tl-toolbar" method="get" action="<?= base_url('super/trial-leads') ?>">
    <input type="search" name="q" value="<?= esc($search) ?>" placeholder="Search name, restaurant, phone or email...">
    <select name="status">
      <option value="">All statuses</option>
      <?php foreach (['new'=>'New','contacted'=>'Contacted','converted'=>'Converted','lost'=>'Lost'] as $value=>$label): ?>
        <option value="<?= $value ?>" <?= $status===$value?'selected':'' ?>><?= $label ?></option>
      <?php endforeach; ?>
    </select>
    <button class="btn btn-primary" type="submit"><i class="fa fa-search"></i> Filter</button>
    <?php if ($search || $status): ?><a class="btn btn-outline" href="<?= base_url('super/trial-leads') ?>">Reset</a><?php endif; ?>
    <span class="tl-count"><?= count($leads) ?> lead<?= count($leads)===1?'':'s' ?></span>
  </form>

  <div class="tl-table-wrap">
    <?php if (empty($leads)): ?>
      <div class="tl-empty"><i class="fa fa-inbox" style="font-size:2rem;margin-bottom:.75rem"></i><div>No trial leads found.</div></div>
    <?php else: ?>
      <table class="tl-table">
        <thead><tr><th>Lead</th><th>Restaurant</th><th>Contact</th><th>Plan / Branches</th><th>Message</th><th>Received</th><th>Status</th><th>Action</th></tr></thead>
        <tbody>
        <?php foreach ($leads as $lead): ?>
          <?php $leadStatus = $lead['status'] ?: 'new'; ?>
          <tr>
            <td><div class="tl-name"><?= esc($lead['name']) ?></div><div class="tl-muted"><?= esc($lead['city'] ?: 'City not provided') ?></div></td>
            <td><div class="tl-name"><?= esc($lead['restaurant_name']) ?></div><div class="tl-muted">Source: <?= esc($lead['source']) ?></div></td>
            <td><div><a href="tel:<?= esc($lead['phone']) ?>"><?= esc($lead['phone']) ?></a></div><div class="tl-muted"><a href="mailto:<?= esc($lead['email']) ?>"><?= esc($lead['email']) ?></a></div></td>
            <td><div class="tl-name"><?= esc($lead['plan_name'] ?: 'Not sure yet') ?></div><div class="tl-muted"><?= (int)$lead['branches'] ?> branch<?= (int)$lead['branches']===1?'':'es' ?></div></td>
            <td><div class="tl-message"><?= esc($lead['message'] ?: '—') ?></div><?php if ($lead['notes']): ?><div class="tl-muted"><strong>Notes:</strong> <?= esc($lead['notes']) ?></div><?php endif; ?></td>
            <td><div><?= esc(date('d M Y', strtotime($lead['created_at']))) ?></div><div class="tl-muted"><?= esc(date('h:i A', strtotime($lead['created_at']))) ?></div></td>
            <td><span class="tl-status tl-<?= esc($leadStatus) ?>"><?= esc(ucfirst($leadStatus)) ?></span></td>
            <td>
              <form class="tl-actions" method="post" action="<?= base_url('super/trial-leads/status/'.$lead['id']) ?>">
                <?= csrf_field() ?>
                <select name="status" onchange="this.form.submit()">
                  <?php foreach (['new'=>'New','contacted'=>'Contacted','converted'=>'Converted','lost'=>'Lost'] as $value=>$label): ?><option value="<?= $value ?>" <?= $leadStatus===$value?'selected':'' ?>><?= $label ?></option><?php endforeach; ?>
                </select>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>
</div>
<?php $this->endSection(); ?>
