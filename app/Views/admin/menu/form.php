<?php $this->extend('layouts/main'); $this->section('content'); ?>
<div style="padding:0 1rem;max-width:700px">
  <form action="<?= $item ? base_url('admin/menu/items/update/'.$item['id']) : base_url('admin/menu/items/store') ?>" method="POST" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <div class="card">
      <div class="card-header"><span class="card-title"><?= $pageTitle ?></span></div>
      <div class="card-body">
        <div class="form-group"><label class="form-label">Item Name <span class="req">*</span></label>
          <input type="text" class="form-control" name="name" value="<?= esc($item['name'] ?? '') ?>" required></div>
        <div class="form-row cols-2">
          <div class="form-group"><label class="form-label">Category <span class="req">*</span></label>
            <select class="form-control" name="category_id" required>
              <option value="">Select Category</option>
              <?php foreach ($categories as $cat): ?>
              <option value="<?= $cat['id'] ?>" <?= ($item['category_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>><?= esc($cat['name']) ?></option>
              <?php endforeach; ?>
            </select></div>
          <div class="form-group"><label class="form-label">Item Type</label>
            <select class="form-control" name="item_type">
              <?php foreach (['veg'=>'🟢 Veg','non_veg'=>'🔴 Non-Veg','egg'=>'🟡 Egg','vegan'=>'🌿 Vegan'] as $k=>$v): ?>
              <option value="<?= $k ?>" <?= ($item['item_type'] ?? 'veg') === $k ? 'selected' : '' ?>><?= $v ?></option>
              <?php endforeach; ?>
            </select></div>
        </div>
        <div class="form-row cols-2">
          <div class="form-group"><label class="form-label">Base Price (₹) <span class="req">*</span></label>
            <input type="number" class="form-control" name="base_price" step="0.01" value="<?= $item['base_price'] ?? '' ?>" required></div>
          <div class="form-group"><label class="form-label">GST %</label>
            <select class="form-control" name="tax_percent">
              <?php foreach ([0,5,12,18,28] as $t): ?>
              <option value="<?= $t ?>" <?= ($item['tax_percent'] ?? 0) == $t ? 'selected' : '' ?>><?= $t ?>%</option>
              <?php endforeach; ?>
            </select></div>
        </div>
        <div class="form-group"><label class="form-label">Description</label>
          <textarea class="form-control" name="description" rows="2"><?= esc($item['description'] ?? '') ?></textarea></div>
        <div class="form-group"><label class="form-label">Image</label>
          <input type="file" class="form-control" name="image" accept="image/*"></div>
        <div style="display:flex;gap:1.5rem;margin-bottom:1rem;flex-wrap:wrap">
          <label style="display:flex;align-items:center;gap:.4rem;cursor:pointer">
            <input type="checkbox" name="is_recommended" value="1" <?= ($item['is_recommended'] ?? 0) ? 'checked' : '' ?>> ⭐ Recommended</label>
          <label style="display:flex;align-items:center;gap:.4rem;cursor:pointer">
            <input type="checkbox" name="is_bestseller" value="1" <?= ($item['is_bestseller'] ?? 0) ? 'checked' : '' ?>> 🔥 Bestseller</label>
        </div>
        <div class="form-group"><label class="form-label">Sort Order</label>
          <input type="number" class="form-control" name="sort_order" value="<?= $item['sort_order'] ?? 0 ?>"></div>
      </div>
      <div class="modal-footer">
        <a href="<?= base_url('admin/menu/items') ?>" class="btn btn-outline">Cancel</a>
        <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save Item</button>
      </div>
    </div>
  </form>
</div>
<?php $this->endSection(); ?>
