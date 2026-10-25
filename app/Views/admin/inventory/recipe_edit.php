<?php $this->extend('layouts/main'); $this->section('content'); ?>
<div style="padding:0 1rem">
  <div style="display:flex;gap:.5rem;justify-content:space-between;align-items:center;margin-bottom:1rem">
    <div>
      <div style="font-weight:800;font-size:1rem">Recipe — <?= esc($menuItem['name']) ?></div>
      <div style="font-size:.75rem;color:#64748B">Ingredients consumed by one portion of this menu item.</div>
    </div>
    <a href="<?= base_url('admin/inventory/recipes') ?>" class="btn btn-outline"><i class="fa fa-arrow-left"></i> Back</a>
  </div>

  <div class="card">
    <div class="card-header"><span class="card-title">Ingredients</span></div>
    <div class="card-body">
      <form method="POST" action="<?= base_url('admin/inventory/recipes/save') ?>" id="recipeForm">
        <?= csrf_field() ?>
        <input type="hidden" name="menu_item_id" value="<?= (int)$menuItem['id'] ?>">
        <div id="recipeRows">
          <?php foreach ($recipeRows as $row): ?>
          <div class="recipe-row" style="display:flex;gap:.5rem;margin-bottom:.5rem;align-items:flex-end">
            <div style="flex:2">
              <label class="form-label">Inventory Item</label>
              <select class="form-control" name="inventory_item_id[]">
                <option value="">— select —</option>
                <?php foreach ($invItems as $iv): ?>
                  <option value="<?= (int)$iv['id'] ?>" <?= (int)$row['inventory_item_id'] === (int)$iv['id'] ? 'selected' : '' ?>>
                    <?= esc($iv['name']) ?> (<?= esc($iv['unit']) ?>)
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div style="flex:1">
              <label class="form-label">Qty per portion</label>
              <input type="number" step="0.001" min="0" class="form-control" name="quantity[]" value="<?= esc($row['quantity']) ?>">
            </div>
            <button type="button" class="btn btn-sm btn-outline" style="margin-bottom:.15rem" onclick="this.parentElement.remove()">✕</button>
          </div>
          <?php endforeach; ?>
          <div class="recipe-row" style="display:flex;gap:.5rem;margin-bottom:.5rem;align-items:flex-end">
            <div style="flex:2">
              <label class="form-label">Inventory Item</label>
              <select class="form-control" name="inventory_item_id[]">
                <option value="">— select —</option>
                <?php foreach ($invItems as $iv): ?>
                  <option value="<?= (int)$iv['id'] ?>"><?= esc($iv['name']) ?> (<?= esc($iv['unit']) ?>)</option>
                <?php endforeach; ?>
              </select>
            </div>
            <div style="flex:1">
              <label class="form-label">Qty per portion</label>
              <input type="number" step="0.001" min="0" class="form-control" name="quantity[]" value="">
            </div>
            <button type="button" class="btn btn-sm btn-outline" style="margin-bottom:.15rem" onclick="this.parentElement.remove()">✕</button>
          </div>
        </div>
        <div style="display:flex;gap:.5rem;margin-top:.75rem">
          <button type="button" class="btn btn-outline" onclick="addRow()"><i class="fa fa-plus"></i> Add ingredient</button>
          <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save Recipe</button>
        </div>
      </form>
    </div>
  </div>
</div>
<script>
function addRow() {
  const grid  = document.getElementById('recipeRows');
  const rows  = grid.querySelectorAll('.recipe-row');
  const copy  = rows[rows.length - 1].cloneNode(true);
  copy.querySelectorAll('.form-control').forEach(c => { if (c.tagName === 'SELECT') c.selectedIndex = 0; else c.value = ''; });
  grid.appendChild(copy);
}
</script>
<?php $this->endSection(); ?>
