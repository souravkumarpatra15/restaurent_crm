<?php $this->extend('layouts/main'); $this->section('content'); ?>
<div style="padding:0 1rem">
  <div style="display:flex;justify-content:flex-end;margin-bottom:1rem">
    <button class="btn btn-primary" onclick="document.getElementById('addTableModal').classList.add('open')"><i class="fa fa-plus"></i> Add Table</button>
  </div>
  <div class="table-map">
    <?php foreach ($tables as $t): ?>
    <div class="table-tile <?= $t['status'] ?>" style="position:relative">
      <?php if (!$t['is_active']): ?><div style="position:absolute;inset:0;background:rgba(255,255,255,.7);border-radius:6px;display:flex;align-items:center;justify-content:center;font-size:.65rem;color:var(--text-muted)">Inactive</div><?php endif; ?>
      <div class="table-num"><?= esc($t['table_number']) ?></div>
      <div class="table-cap"><i class="fa fa-users"></i> <?= $t['capacity'] ?></div>
      <div class="table-status"><?= ucfirst($t['status']) ?></div>
    </div>
    <?php endforeach; ?>
    <?php if (empty($tables)): ?>
    <div style="grid-column:1/-1"><div class="empty-state"><i class="fa fa-chair"></i><p>No tables yet</p></div></div>
    <?php endif; ?>
  </div>
</div>
<div class="modal-overlay" id="addTableModal">
  <div class="modal"><div class="modal-header"><span class="modal-title">Add Table</span><button class="modal-close" onclick="document.getElementById('addTableModal').classList.remove('open')"><i class="fa fa-times"></i></button></div>
  <div class="modal-body">
    <div class="form-row cols-2">
      <div class="form-group"><label class="form-label">Table Number *</label><input type="text" class="form-control" id="tNum" placeholder="T1, A1, 01"></div>
      <div class="form-group"><label class="form-label">Capacity</label><input type="number" class="form-control" id="tCap" value="4" min="1"></div>
    </div>
    <?php if (!empty($areas)): ?>
    <div class="form-group"><label class="form-label">Area</label>
      <select class="form-control" id="tArea"><option value="">No Area</option>
        <?php foreach ($areas as $a): ?><option value="<?= $a['id'] ?>"><?= esc($a['name']) ?></option><?php endforeach; ?>
      </select></div>
    <?php endif; ?>
  </div>
  <div class="modal-footer">
    <button class="btn btn-outline" onclick="document.getElementById('addTableModal').classList.remove('open')">Cancel</button>
    <button class="btn btn-primary" onclick="addTable()">Add Table</button>
  </div></div>
</div>
<script>
function addTable() {
  fetch('<?= base_url('admin/tables/store') ?>', {
    method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'},
    body:'<?= csrf_token() ?>=<?= csrf_hash() ?>&table_number='+document.getElementById('tNum').value+'&capacity='+document.getElementById('tCap').value+'&area_id='+(document.getElementById('tArea')?.value||'')
  }).then(r=>r.json()).then(d=>{if(d.success){location.reload();}});
}
</script>
<?php $this->endSection(); ?>
