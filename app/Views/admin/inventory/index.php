<?php $this->extend('layouts/main'); $this->section('content'); ?>
<div style="padding:0 1rem">
  <div style="display:flex;gap:.5rem;justify-content:flex-end;margin-bottom:1rem">
    <button class="btn btn-outline" onclick="openModal('addItemModal')"><i class="fa fa-plus"></i> Add Item</button>
    <button class="btn btn-primary" onclick="openModal('transactionModal')"><i class="fa fa-right-left"></i> Stock Entry</button>
  </div>

  <!-- Low Stock Alert -->
  <?php $lowStock = array_filter($items, fn($i) => $i['min_stock'] > 0 && $i['current_stock'] <= $i['min_stock']); ?>
  <?php if (!empty($lowStock)): ?>
  <div class="alert alert-warning" style="margin-bottom:1rem">
    <i class="fa fa-triangle-exclamation"></i>
    <strong><?= count($lowStock) ?> items</strong> are at or below minimum stock level
  </div>
  <?php endif; ?>

  <div class="card">
    <div class="card-header">
      <span class="card-title">Inventory (<?= count($items) ?> items)</span>
      <div style="position:relative">
        <i class="fa fa-search" style="position:absolute;left:.75rem;top:50%;transform:translateY(-50%);color:var(--text-muted);font-size:.85rem"></i>
        <input type="text" id="invSearch" class="form-control" placeholder="Search..." style="padding-left:2.25rem;width:180px">
      </div>
    </div>
    <div class="table-wrap">
      <table class="table">
        <thead>
          <tr><th>Item</th><th>Category</th><th>Unit</th><th>Stock</th><th>Min Stock</th><th>Unit Cost</th><th>Value</th><th></th></tr>
        </thead>
        <tbody>
          <?php if (empty($items)): ?>
          <tr><td colspan="8">
            <div class="empty-state" style="padding:2.5rem"><i class="fa fa-boxes-stacked"></i><p>No inventory items yet</p></div>
          </td></tr>
          <?php else: foreach ($items as $item):
            $isLow = $item['min_stock'] > 0 && $item['current_stock'] <= $item['min_stock'];
          ?>
          <tr class="inv-row" data-name="<?= strtolower(esc($item['name'])) ?>">
            <td>
              <div style="font-weight:600"><?= esc($item['name']) ?></div>
              <?php if ($item['sku']): ?><div style="font-size:.72rem;color:var(--text-muted)">SKU: <?= esc($item['sku']) ?></div><?php endif; ?>
            </td>
            <td style="font-size:.82rem"><?= esc($item['category_name'] ?? '-') ?></td>
            <td style="font-size:.82rem"><?= esc($item['unit']) ?></td>
            <td>
              <span style="font-weight:800;color:<?= $isLow ? 'var(--danger)' : 'var(--success)' ?>">
                <?= number_format($item['current_stock'],2) ?>
              </span>
              <?php if ($isLow): ?><span class="badge-pill badge-danger" style="font-size:.62rem;margin-left:.3rem">LOW</span><?php endif; ?>
            </td>
            <td style="font-size:.82rem;color:var(--text-muted)"><?= $item['min_stock'] > 0 ? number_format($item['min_stock'],2) : '-' ?></td>
            <td style="font-size:.82rem">₹<?= number_format($item['unit_cost'],2) ?></td>
            <td style="font-weight:700">₹<?= number_format($item['current_stock'] * $item['unit_cost'],2) ?></td>
            <td>
              <button onclick="openStockEntry(<?= $item['id'] ?>, '<?= esc($item['name']) ?>')"
                      class="btn btn-sm btn-outline"><i class="fa fa-right-left"></i></button>
            </td>
          </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Add Item Modal -->
<div class="modal-overlay" id="addItemModal">
  <div class="modal">
    <div class="modal-header">
      <span class="modal-title">Add Inventory Item</span>
      <button class="modal-close" onclick="closeModal('addItemModal')"><i class="fa fa-times"></i></button>
    </div>
    <form action="<?= base_url('admin/inventory/store') ?>" method="POST">
      <?= csrf_field() ?>
      <div class="modal-body">
        <div class="form-group">
          <label class="form-label">Item Name <span class="req">*</span></label>
          <input type="text" class="form-control" name="name" required>
        </div>
        <div class="form-row cols-2">
          <div class="form-group">
            <label class="form-label">Category</label>
            <select class="form-control" name="category_id">
              <option value="">Select</option>
              <?php foreach ($categories as $cat): ?>
              <option value="<?= $cat['id'] ?>"><?= esc($cat['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Unit</label>
            <select class="form-control" name="unit">
              <?php foreach (['pcs','kg','g','l','ml','dozen','box','bag','bottle','packet'] as $u): ?>
              <option value="<?= $u ?>"><?= $u ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="form-row cols-2">
          <div class="form-group">
            <label class="form-label">SKU</label>
            <input type="text" class="form-control" name="sku" placeholder="Optional">
          </div>
          <div class="form-group">
            <label class="form-label">Min Stock Alert</label>
            <input type="number" class="form-control" name="min_stock" value="0" min="0" step="0.01">
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="closeModal('addItemModal')">Cancel</button>
        <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Add Item</button>
      </div>
    </form>
  </div>
</div>

<!-- Stock Transaction Modal -->
<div class="modal-overlay" id="transactionModal">
  <div class="modal">
    <div class="modal-header">
      <span class="modal-title" id="transModalTitle">Stock Entry</span>
      <button class="modal-close" onclick="closeModal('transactionModal')"><i class="fa fa-times"></i></button>
    </div>
    <div class="modal-body">
      <input type="hidden" id="transItemId">
      <div class="form-group">
        <label class="form-label">Item</label>
        <select class="form-control" id="transItemSelect" onchange="document.getElementById('transItemId').value=this.value">
          <option value="">Select Item</option>
          <?php foreach ($items as $item): ?>
          <option value="<?= $item['id'] ?>"><?= esc($item['name']) ?> (<?= number_format($item['current_stock'],2) ?> <?= $item['unit'] ?>)</option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-row cols-2">
        <div class="form-group">
          <label class="form-label">Transaction Type</label>
          <select class="form-control" id="transType">
            <option value="purchase">Purchase (Stock In)</option>
            <option value="adjustment">Adjustment</option>
            <option value="waste">Waste / Spoilage</option>
            <option value="transfer_in">Transfer In</option>
            <option value="transfer_out">Transfer Out</option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Quantity <span class="req">*</span></label>
          <input type="number" class="form-control" id="transQty" min="0" step="0.01" placeholder="0">
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Unit Cost (₹)</label>
        <input type="number" class="form-control" id="transCost" min="0" step="0.01" placeholder="0">
      </div>
      <div class="form-group">
        <label class="form-label">Notes</label>
        <input type="text" class="form-control" id="transNotes" placeholder="Supplier name, bill no., etc.">
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-outline" onclick="closeModal('transactionModal')">Cancel</button>
      <button class="btn btn-primary" onclick="saveTransaction()"><i class="fa fa-save"></i> Save Entry</button>
    </div>
  </div>
</div>

<script>
document.getElementById('invSearch').addEventListener('input', function() {
  const q = this.value.toLowerCase();
  document.querySelectorAll('.inv-row').forEach(row => {
    row.style.display = row.dataset.name.includes(q) ? '' : 'none';
  });
});

function openStockEntry(id, name) {
  document.getElementById('transItemId').value = id;
  document.getElementById('transItemSelect').value = id;
  document.getElementById('transModalTitle').textContent = 'Stock Entry — ' + name;
  openModal('transactionModal');
}

function saveTransaction() {
  const itemId = document.getElementById('transItemId').value || document.getElementById('transItemSelect').value;
  if (!itemId) { showToast('Select an item', 'error'); return; }
  const qty = document.getElementById('transQty').value;
  if (!qty || qty <= 0) { showToast('Enter valid quantity', 'error'); return; }

  fetch('<?= base_url('admin/inventory/transaction') ?>', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: '<?= csrf_token() ?>=<?= csrf_hash() ?>'
      + '&inventory_item_id=' + itemId
      + '&transaction_type=' + document.getElementById('transType').value
      + '&quantity=' + qty
      + '&unit_cost=' + (document.getElementById('transCost').value || 0)
      + '&notes=' + encodeURIComponent(document.getElementById('transNotes').value)
  }).then(r => r.json()).then(d => {
    if (d.success) {
      showToast('Stock updated! New qty: ' + d.new_stock, 'success');
      closeModal('transactionModal');
      setTimeout(() => location.reload(), 1000);
    } else { showToast('Failed', 'error'); }
  });
}
</script>
<?php $this->endSection(); ?>
