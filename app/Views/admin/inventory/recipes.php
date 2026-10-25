<?php $this->extend('layouts/main'); $this->section('content'); ?>
<div style="padding:0 1rem">
  <div style="display:flex;gap:.5rem;justify-content:space-between;align-items:center;margin-bottom:1rem">
    <div>
      <div style="font-weight:800;font-size:1rem">Menu Recipes (BOM)</div>
      <div style="font-size:.75rem;color:#64748B">Define how much of each inventory item one portion consumes. Stock is deducted automatically on every sale (POS + QR ordering) and returned when an order is cancelled.</div>
    </div>
    <a href="<?= base_url('admin/inventory') ?>" class="btn btn-outline"><i class="fa fa-arrow-left"></i> Inventory</a>
  </div>

  <?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-warning" style="background:#ECFDF5;border-color:#A7F3D0;color:#065F46"><?= esc(session()->getFlashdata('success')) ?></div>
  <?php endif; ?>
  <?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-warning" style="background:#FEF2F2;border-color:#FECACA;color:#991B1B"><?= esc(session()->getFlashdata('error')) ?></div>
  <?php endif; ?>

  <div class="card">
    <div class="card-header">
      <span class="card-title">Menu items (<?= count($menuItems) ?>)</span>
    </div>
    <div class="card-body" style="padding:0">
      <table class="table">
        <thead>
          <tr><th>Menu Item</th><th>Recipe</th><th style="width:120px"></th></tr>
        </thead>
        <tbody>
          <?php foreach ($menuItems as $mi): ?>
          <tr>
            <td style="font-weight:700"><?= esc($mi['name']) ?></td>
            <td>
              <?php if (!empty($recipeMap[$mi['id']])): ?>
                <?php foreach ($recipeMap[$mi['id']] as $r): ?>
                  <span style="display:inline-block;background:#F1F5F9;border-radius:6px;padding:.15rem .5rem;margin:.1rem .25rem .1rem 0;font-size:.72rem;font-weight:700">
                    <?= esc($r['name'] ?: '?') ?> × <?= rtrim(rtrim(number_format((float)$r['quantity'], 3), '0'), '.') ?> <?= esc($r['unit'] ?? '') ?>
                  </span>
                <?php endforeach; ?>
              <?php else: ?>
                <span style="color:#94A3B8;font-size:.75rem">No recipe — stock not deducted</span>
              <?php endif; ?>
            </td>
            <td>
              <a href="<?= base_url('admin/inventory/recipes/' . $mi['id']) ?>" class="btn btn-sm btn-outline"><i class="fa fa-pen"></i> Edit</a>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php if (empty($menuItems)): ?>
          <tr><td colspan="3" style="text-align:center;padding:1.25rem;color:#94A3B8">No menu items yet</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php $this->endSection(); ?>
