<?php $this->extend('layouts/main'); $this->section('content'); ?>

<div style="padding:0 1rem">

<!-- Category Tabs -->
<div style="display:flex;gap:.5rem;margin-bottom:1rem;overflow-x:auto;scrollbar-width:none;padding-bottom:.25rem">
  <button class="btn <?= $activeTab === 'items' ? 'btn-primary' : 'btn-outline' ?>" onclick="switchTab('items')">
    <i class="fa fa-utensils"></i> Menu Items
  </button>
  <button class="btn <?= $activeTab === 'categories' ? 'btn-primary' : 'btn-outline' ?>" onclick="switchTab('categories')">
    <i class="fa fa-layer-group"></i> Categories
  </button>
  <button class="btn <?= $activeTab === 'addons' ? 'btn-primary' : 'btn-outline' ?>" onclick="switchTab('addons')">
    <i class="fa fa-plus-circle"></i> Add-ons
  </button>
  <a href="<?= base_url('admin/menu/print') ?>" class="btn btn-outline" style="margin-left:auto;flex-shrink:0" target="_blank">
    <i class="fa fa-print"></i> Print Menu
  </a>
  <a href="<?= base_url('admin/menu/items/create') ?>" class="btn btn-primary" style="flex-shrink:0">
    <i class="fa fa-plus"></i> Add Item
  </a>
</div>

<!-- ── ITEMS TAB ── -->
<div id="tab-items" class="tab-content <?= $activeTab !== 'items' ? 'hidden' : '' ?>">

  <!-- Search & Filter Bar -->
  <div class="card" style="margin-bottom:1rem">
    <div class="card-body" style="padding:.75rem;display:flex;gap:.5rem;flex-wrap:wrap">
      <div style="position:relative;flex:1;min-width:180px">
        <i class="fa fa-search" style="position:absolute;left:.75rem;top:50%;transform:translateY(-50%);color:var(--text-muted)"></i>
        <input type="text" id="itemSearch" class="form-control" placeholder="Search items..." style="padding-left:2.25rem" oninput="filterItems()">
      </div>
      <select class="form-control" id="catFilter" style="width:140px" onchange="filterItems()">
        <option value="">All Categories</option>
        <?php foreach ($categories as $cat): ?>
        <option value="<?= $cat['id'] ?>"><?= esc($cat['name']) ?></option>
        <?php endforeach; ?>
      </select>
      <select class="form-control" id="typeFilter" style="width:120px" onchange="filterItems()">
        <option value="">All Types</option>
        <option value="veg">🟢 Veg</option>
        <option value="non_veg">🔴 Non-Veg</option>
        <option value="egg">🟡 Egg</option>
        <option value="vegan">🌿 Vegan</option>
      </select>
      <select class="form-control" id="statusFilter" style="width:120px" onchange="filterItems()">
        <option value="">All Status</option>
        <option value="1">Active</option>
        <option value="0">Inactive</option>
      </select>
    </div>
  </div>

  <!-- Items Grid -->
  <div id="menuItemsContainer">
    <?php if (empty($menuItems)): ?>
    <div class="card">
      <div class="empty-state" style="padding:3rem">
        <i class="fa fa-utensils"></i>
        <p>No menu items yet.<br>Add your first item to get started.</p>
        <a href="<?= base_url('admin/menu/items/create') ?>" class="btn btn-primary" style="margin-top:1rem">
          <i class="fa fa-plus"></i> Add Menu Item
        </a>
      </div>
    </div>
    <?php else: ?>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:.75rem" id="menuItemsList">
      <?php foreach ($menuItems as $item): ?>
      <div class="menu-manage-card card"
           data-name="<?= strtolower(esc($item['name'])) ?>"
           data-cat="<?= $item['category_id'] ?>"
           data-type="<?= $item['item_type'] ?>"
           data-status="<?= $item['is_active'] ?>">
        <div style="display:flex;gap:.75rem;padding:.875rem">
          <!-- Image -->
          <div style="width:70px;height:70px;border-radius:10px;overflow:hidden;flex-shrink:0;background:var(--bg)">
            <?php if ($item['image']): ?>
              <img src="<?= base_url('public/images/uploads/'.$item['image']) ?>" style="width:100%;height:100%;object-fit:cover">
            <?php else: ?>
              <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;font-size:1.75rem">🍽</div>
            <?php endif; ?>
          </div>
          <!-- Details -->
          <div style="flex:1;min-width:0">
            <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:.5rem;margin-bottom:.25rem">
              <div style="font-weight:700;font-size:.875rem;line-height:1.3"><?= esc($item['name']) ?></div>
              <?php $dotColor = in_array($item['item_type'],['veg','vegan']) ? 'var(--success)' : 'var(--danger)'; ?>
              <div style="width:12px;height:12px;border:2px solid <?= $dotColor ?>;border-radius:2px;flex-shrink:0;margin-top:2px;display:flex;align-items:center;justify-content:center">
                <div style="width:5px;height:5px;background:<?= $dotColor ?>;border-radius:50%"></div>
              </div>
            </div>
            <div style="font-size:.72rem;color:var(--text-muted);margin-bottom:.35rem"><?= esc($item['category_name']) ?></div>
            <div style="display:flex;align-items:center;gap:.5rem;flex-wrap:wrap">
              <span style="font-weight:800;font-size:.925rem;color:var(--primary)">₹<?= number_format($item['base_price'],2) ?></span>
              <?php if ($item['tax_percent'] > 0): ?>
                <span class="badge-pill badge-gray" style="font-size:.65rem">+<?= $item['tax_percent'] ?>% GST</span>
              <?php endif; ?>
              <?php if ($item['is_recommended']): ?>
                <span class="badge-pill badge-primary" style="font-size:.65rem">⭐ Recommended</span>
              <?php endif; ?>
              <?php if ($item['is_bestseller']): ?>
                <span class="badge-pill badge-warning" style="font-size:.65rem">🔥 Bestseller</span>
              <?php endif; ?>
            </div>
            <?php if (!empty($item['variants'])): ?>
            <div style="font-size:.7rem;color:var(--text-muted);margin-top:.2rem">
              <?= count($item['variants']) ?> variants: <?= implode(', ', array_column($item['variants'],'name')) ?>
            </div>
            <?php endif; ?>
          </div>
        </div>
        <!-- Actions -->
        <div style="display:flex;align-items:center;justify-content:space-between;padding:.5rem .875rem;border-top:1px solid var(--border);background:var(--bg)">
          <label class="toggle-switch" title="<?= $item['is_active'] ? 'Active' : 'Inactive' ?>">
            <input type="checkbox" <?= $item['is_active'] ? 'checked' : '' ?>
                   onchange="toggleItem(<?= $item['id'] ?>, this.checked)">
            <span class="toggle-slider"></span>
          </label>
          <div style="display:flex;gap:.35rem">
            <a href="<?= base_url('admin/menu/items/edit/'.$item['id']) ?>" class="btn btn-sm btn-outline" title="Edit">
              <i class="fa fa-edit"></i>
            </a>
            <button onclick="duplicateItem(<?= $item['id'] ?>)" class="btn btn-sm btn-outline" title="Duplicate">
              <i class="fa fa-copy"></i>
            </button>
            <button onclick="deleteItem(<?= $item['id'] ?>, '<?= esc($item['name']) ?>')" class="btn btn-sm btn-outline" style="color:var(--danger);border-color:var(--danger)" title="Delete">
              <i class="fa fa-trash"></i>
            </button>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</div>

<!-- ── CATEGORIES TAB ── -->
<div id="tab-categories" class="tab-content <?= $activeTab !== 'categories' ? 'hidden' : '' ?>">
  <div style="display:flex;justify-content:flex-end;margin-bottom:.75rem">
    <button class="btn btn-primary" onclick="openCategoryModal()"><i class="fa fa-plus"></i> Add Category</button>
  </div>
  <div class="card">
    <div class="table-wrap">
      <table class="table">
        <thead>
          <tr>
            <th style="width:50px">#</th>
            <th>Category Name</th>
            <th>Items</th>
            <th>Status</th>
            <th>Sort</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody id="categoriesList" class="sortable-list">
          <?php if (empty($categories)): ?>
          <tr><td colspan="6"><div class="empty-state"><i class="fa fa-layer-group"></i><p>No categories yet</p></div></td></tr>
          <?php else: ?>
          <?php foreach ($categories as $cat): ?>
          <tr data-id="<?= $cat['id'] ?>">
            <td style="cursor:grab"><i class="fa fa-grip-vertical" style="color:var(--text-light)"></i></td>
            <td>
              <div style="display:flex;align-items:center;gap:.6rem">
                <?php if ($cat['image']): ?>
                  <img src="<?= base_url('public/images/uploads/'.$cat['image']) ?>" style="width:32px;height:32px;border-radius:6px;object-fit:cover">
                <?php else: ?>
                  <div style="width:32px;height:32px;border-radius:6px;background:var(--primary-light);color:var(--primary);display:flex;align-items:center;justify-content:center;font-size:.85rem;font-weight:700"><?= substr($cat['name'],0,1) ?></div>
                <?php endif; ?>
                <div>
                  <div style="font-weight:600;font-size:.875rem"><?= esc($cat['name']) ?></div>
                  <?php if ($cat['description']): ?>
                    <div style="font-size:.72rem;color:var(--text-muted)"><?= esc(substr($cat['description'],0,40)) ?>...</div>
                  <?php endif; ?>
                </div>
              </div>
            </td>
            <td><span class="badge-pill badge-primary"><?= $cat['item_count'] ?? 0 ?> items</span></td>
            <td>
              <label class="toggle-switch">
                <input type="checkbox" <?= $cat['is_active'] ? 'checked' : '' ?> onchange="toggleCategory(<?= $cat['id'] ?>, this.checked)">
                <span class="toggle-slider"></span>
              </label>
            </td>
            <td style="font-size:.82rem;font-family:var(--font-mono)"><?= $cat['sort_order'] ?></td>
            <td>
              <div style="display:flex;gap:.3rem">
                <button onclick="editCategory(<?= htmlspecialchars(json_encode($cat), ENT_QUOTES) ?>)" class="btn btn-sm btn-outline"><i class="fa fa-edit"></i></button>
                <button onclick="deleteCategory(<?= $cat['id'] ?>, '<?= esc($cat['name']) ?>')" class="btn btn-sm btn-outline" style="color:var(--danger);border-color:var(--danger)"><i class="fa fa-trash"></i></button>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- ── ADD-ONS TAB ── -->
<div id="tab-addons" class="tab-content <?= $activeTab !== 'addons' ? 'hidden' : '' ?>">
  <div style="display:flex;justify-content:flex-end;margin-bottom:.75rem">
    <button class="btn btn-primary" onclick="openAddonGroupModal()"><i class="fa fa-plus"></i> Add Addon Group</button>
  </div>
  <?php foreach ($addonGroups as $group): ?>
  <div class="card" style="margin-bottom:1rem">
    <div class="card-header">
      <div>
        <span class="card-title"><?= esc($group['name']) ?></span>
        <span class="badge-pill badge-<?= $group['is_required'] ? 'danger' : 'gray' ?>" style="margin-left:.5rem;font-size:.7rem">
          <?= $group['is_required'] ? 'Required' : 'Optional' ?>
        </span>
        <span class="badge-pill badge-info" style="margin-left:.25rem;font-size:.7rem">
          <?= ucfirst($group['selection_type']) ?>
        </span>
      </div>
      <div style="display:flex;gap:.35rem">
        <button onclick="addAddon(<?= $group['id'] ?>)" class="btn btn-sm btn-outline"><i class="fa fa-plus"></i> Add</button>
        <button onclick="deleteAddonGroup(<?= $group['id'] ?>)" class="btn btn-sm btn-outline" style="color:var(--danger)"><i class="fa fa-trash"></i></button>
      </div>
    </div>
    <div class="card-body" style="padding:.5rem">
      <?php foreach ($group['addons'] as $addon): ?>
      <div style="display:flex;align-items:center;justify-content:space-between;padding:.4rem .5rem;border-radius:8px;margin-bottom:.25rem;background:var(--bg)">
        <span style="font-size:.85rem"><?= esc($addon['name']) ?></span>
        <div style="display:flex;align-items:center;gap:.75rem">
          <?php if ($addon['price'] > 0): ?>
            <span style="font-weight:700;font-size:.82rem;color:var(--primary)">+₹<?= number_format($addon['price'],2) ?></span>
          <?php else: ?>
            <span style="font-size:.78rem;color:var(--text-muted)">Free</span>
          <?php endif; ?>
          <button onclick="deleteAddon(<?= $addon['id'] ?>)" style="background:none;border:none;color:var(--danger);cursor:pointer;padding:2px"><i class="fa fa-times"></i></button>
        </div>
      </div>
      <?php endforeach; ?>
      <?php if (empty($group['addons'])): ?>
        <div style="text-align:center;padding:.75rem;color:var(--text-muted);font-size:.82rem">No addons. Click "Add" to add options.</div>
      <?php endif; ?>
    </div>
  </div>
  <?php endforeach; ?>
  <?php if (empty($addonGroups)): ?>
  <div class="card"><div class="empty-state" style="padding:2.5rem"><i class="fa fa-plus-circle"></i><p>No addon groups yet</p></div></div>
  <?php endif; ?>
</div>

</div><!-- end padding wrapper -->

<!-- Category Modal -->
<div class="modal-overlay" id="categoryModal">
  <div class="modal">
    <div class="modal-header">
      <span class="modal-title" id="catModalTitle">Add Category</span>
      <button class="modal-close" onclick="closeModal('categoryModal')"><i class="fa fa-times"></i></button>
    </div>
    <div class="modal-body">
      <form id="categoryForm">
        <?= csrf_field() ?>
        <input type="hidden" id="catId" name="id">
        <div class="form-group">
          <label class="form-label">Category Name <span class="req">*</span></label>
          <input type="text" class="form-control" id="catName" name="name" placeholder="e.g. Starters, Main Course" required>
        </div>
        <div class="form-row cols-2">
          <div class="form-group">
            <label class="form-label">Sort Order</label>
            <input type="number" class="form-control" id="catSort" name="sort_order" value="0" min="0">
          </div>
          <div class="form-group">
            <label class="form-label">Status</label>
            <select class="form-control" id="catStatus" name="is_active">
              <option value="1">Active</option>
              <option value="0">Inactive</option>
            </select>
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Description</label>
          <textarea class="form-control" id="catDesc" name="description" rows="2" placeholder="Optional description"></textarea>
        </div>
      </form>
    </div>
    <div class="modal-footer">
      <button class="btn btn-outline" onclick="closeModal('categoryModal')">Cancel</button>
      <button class="btn btn-primary" onclick="saveCategory()" id="saveCatBtn"><i class="fa fa-save"></i> Save Category</button>
    </div>
  </div>
</div>

<!-- Addon Group Modal -->
<div class="modal-overlay" id="addonGroupModal">
  <div class="modal">
    <div class="modal-header">
      <span class="modal-title">Add Addon Group</span>
      <button class="modal-close" onclick="closeModal('addonGroupModal')"><i class="fa fa-times"></i></button>
    </div>
    <div class="modal-body">
      <form id="addonGroupForm">
        <?= csrf_field() ?>
        <div class="form-group">
          <label class="form-label">Group Name <span class="req">*</span></label>
          <input type="text" class="form-control" id="agName" name="name" placeholder="e.g. Toppings, Sauces, Sizes" required>
        </div>
        <div class="form-row cols-2">
          <div class="form-group">
            <label class="form-label">Selection Type</label>
            <select class="form-control" id="agType" name="selection_type">
              <option value="multiple">Multiple</option>
              <option value="single">Single</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Required?</label>
            <select class="form-control" id="agRequired" name="is_required">
              <option value="0">Optional</option>
              <option value="1">Required</option>
            </select>
          </div>
        </div>
      </form>
    </div>
    <div class="modal-footer">
      <button class="btn btn-outline" onclick="closeModal('addonGroupModal')">Cancel</button>
      <button class="btn btn-primary" onclick="saveAddonGroup()"><i class="fa fa-save"></i> Save</button>
    </div>
  </div>
</div>

<style>
.hidden { display: none !important; }
.toggle-switch { position:relative; display:inline-block; width:38px; height:22px; }
.toggle-switch input { opacity:0; width:0; height:0; }
.toggle-slider {
  position:absolute; cursor:pointer; top:0; left:0; right:0; bottom:0;
  background:#CBD5E0; transition:.3s; border-radius:22px;
}
.toggle-slider:before {
  position:absolute; content:''; height:16px; width:16px; left:3px; bottom:3px;
  background:#fff; transition:.3s; border-radius:50%;
}
input:checked + .toggle-slider { background:var(--success); }
input:checked + .toggle-slider:before { transform:translateX(16px); }
</style>

<script>
const CSRF = '<?= csrf_hash() ?>';
const CSRF_NAME = '<?= csrf_token() ?>';

// Tab switching
function switchTab(tab) {
  document.querySelectorAll('.tab-content').forEach(t => t.classList.add('hidden'));
  document.getElementById('tab-' + tab).classList.remove('hidden');
  window.history.replaceState(null, '', '?tab=' + tab);
}

// Filter items
function filterItems() {
  const q    = document.getElementById('itemSearch').value.toLowerCase();
  const cat  = document.getElementById('catFilter').value;
  const type = document.getElementById('typeFilter').value;
  const stat = document.getElementById('statusFilter').value;
  document.querySelectorAll('.menu-manage-card').forEach(card => {
    const matchQ    = !q    || card.dataset.name.includes(q);
    const matchCat  = !cat  || card.dataset.cat  === cat;
    const matchType = !type || card.dataset.type === type;
    const matchStat = stat === '' || card.dataset.status === stat;
    card.style.display = (matchQ && matchCat && matchType && matchStat) ? '' : 'none';
  });
}

// Toggle item
function toggleItem(id, active) {
  fetch('<?= base_url('admin/menu/items/toggle/') ?>' + id, {
    method:'POST',
    headers:{'Content-Type':'application/x-www-form-urlencoded'},
    body: CSRF_NAME + '=' + CSRF + '&active=' + (active ? 1 : 0)
  }).then(r => r.json()).then(d => {
    if (!d.success) showToast('Failed to update', 'error');
  });
}

// Delete item
function deleteItem(id, name) {
  if (!confirm('Delete "' + name + '"? This cannot be undone.')) return;
  fetch('<?= base_url('admin/menu/items/delete/') ?>' + id, {
    method:'POST',
    headers:{'Content-Type':'application/x-www-form-urlencoded'},
    body: CSRF_NAME + '=' + CSRF
  }).then(r => r.json()).then(d => {
    if (d.success) {
      document.querySelector(`[data-name="${name.toLowerCase()}"]`)?.remove();
      showToast('Item deleted', 'success');
    } else showToast('Failed to delete', 'error');
  });
}

function duplicateItem(id) {
  fetch('<?= base_url('admin/menu/items/duplicate/') ?>' + id, {
    method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body: CSRF_NAME+'='+CSRF
  }).then(r=>r.json()).then(d=>{
    if(d.success){showToast('Item duplicated','success');setTimeout(()=>location.reload(),800);}
    else showToast('Failed','error');
  });
}

// Category Modal
function openCategoryModal(data = null) {
  document.getElementById('catId').value   = data?.id || '';
  document.getElementById('catName').value = data?.name || '';
  document.getElementById('catSort').value = data?.sort_order || 0;
  document.getElementById('catStatus').value = data?.is_active ?? 1;
  document.getElementById('catDesc').value = data?.description || '';
  document.getElementById('catModalTitle').textContent = data ? 'Edit Category' : 'Add Category';
  openModal('categoryModal');
}
function editCategory(data) { openCategoryModal(data); }

function saveCategory() {
  const id  = document.getElementById('catId').value;
  const url = id
    ? '<?= base_url('admin/menu/categories/update/') ?>' + id
    : '<?= base_url('admin/menu/categories/store') ?>';
  const btn = document.getElementById('saveCatBtn');
  btn.disabled = true; btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i>';
  // URLSearchParams + explicit CSRF token prevents CI4 303 CSRF redirect on AJAX
  const params = new URLSearchParams({
    [CSRF_NAME]: CSRF,
    name:        document.getElementById('catName').value,
    description: document.getElementById('catDesc').value || '',
    sort_order:  document.getElementById('catSort').value,
    is_active:   document.getElementById('catStatus').value,
  });
  if (id) params.append('id', id);
  fetch(url, {
    method: 'POST',
    headers: {'Content-Type':'application/x-www-form-urlencoded','X-Requested-With':'XMLHttpRequest'},
    body: params
  }).then(r => { if (!r.ok) throw new Error('HTTP '+r.status); return r.json(); })
  .then(d => {
    if (d.success) { closeModal('categoryModal'); showToast(id?'Category updated':'Category created','success'); setTimeout(()=>location.reload(),600); }
    else showToast(d.message||'Failed to save','error');
  }).catch(e => showToast('Error: '+e.message,'error'))
  .finally(() => { btn.disabled=false; btn.innerHTML='<i class="fa fa-save"></i> Save Category'; });
}

function toggleCategory(id, active) {
  fetch('<?= base_url('admin/menu/categories/toggle/') ?>' + id, {
    method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'},
    body: CSRF_NAME+'='+CSRF+'&active='+(active?1:0)
  });
}

function deleteCategory(id, name) {
  if(!confirm('Delete category "'+name+'"?')) return;
  fetch('<?= base_url('admin/menu/categories/delete/') ?>'+id,{
    method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:CSRF_NAME+'='+CSRF
  }).then(r=>r.json()).then(d=>{
    if(d.success){showToast('Deleted','success');setTimeout(()=>location.reload(),600);}
    else showToast(d.message||'Cannot delete — remove items first','error');
  });
}

// Addon Group
function openAddonGroupModal() { openModal('addonGroupModal'); }
function saveAddonGroup() {
  const params = new URLSearchParams({
    [CSRF_NAME]: CSRF,
    name:           document.getElementById('agName').value,
    selection_type: document.getElementById('agType').value,
    is_required:    document.getElementById('agRequired').value,
  });
  fetch('<?= base_url('admin/menu/addon-groups/store') ?>', {
    method:'POST',
    headers:{'Content-Type':'application/x-www-form-urlencoded','X-Requested-With':'XMLHttpRequest'},
    body: params
  }).then(r=>r.json()).then(d=>{
    if(d.success){closeModal('addonGroupModal');showToast('Addon group added','success');setTimeout(()=>location.reload(),600);}
    else showToast('Failed','error');
  }).catch(e=>showToast('Error: '+e.message,'error'));
}

function deleteAddonGroup(id) {
  if(!confirm('Delete this addon group?')) return;
  fetch('<?= base_url('admin/menu/addon-groups/delete/') ?>'+id,{
    method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:CSRF_NAME+'='+CSRF
  }).then(r=>r.json()).then(d=>{
    if(d.success){showToast('Deleted','success');setTimeout(()=>location.reload(),600);}
  });
}

function addAddon(groupId) {
  const name  = prompt('Addon name:'); if(!name) return;
  const price = parseFloat(prompt('Price (0 for free):') || 0);
  fetch('<?= base_url('admin/menu/addons/store') ?>', {
    method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},
    body: CSRF_NAME+'='+CSRF+'&addon_group_id='+groupId+'&name='+encodeURIComponent(name)+'&price='+price
  }).then(r=>r.json()).then(d=>{
    if(d.success){showToast('Addon added','success');setTimeout(()=>location.reload(),600);}
  });
}

function deleteAddon(id) {
  fetch('<?= base_url('admin/menu/addons/delete/') ?>'+id,{
    method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:CSRF_NAME+'='+CSRF
  }).then(r=>r.json()).then(d=>{
    if(d.success) document.querySelector(`[onclick="deleteAddon(${id})"]`).closest('div[style]').remove();
  });
}

function openModal(id)  { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }
document.querySelectorAll('.modal-overlay').forEach(o=>o.addEventListener('click',e=>{if(e.target===o)o.classList.remove('open');}));

function showToast(msg, type='info') {
  const colors = {success:'var(--success)',error:'var(--danger)',warning:'var(--warning)',info:'var(--info)'};
  const t = document.createElement('div');
  t.style.cssText=`position:fixed;bottom:1.5rem;right:1rem;background:${colors[type]};color:#fff;padding:.6rem 1.25rem;border-radius:20px;font-size:.85rem;font-weight:600;z-index:9999;box-shadow:0 4px 12px rgba(0,0,0,.2);animation:slideUp .2s ease`;
  t.textContent = msg;
  document.body.appendChild(t);
  setTimeout(()=>t.remove(), 2800);
}
</script>

<?php $this->endSection(); ?>
