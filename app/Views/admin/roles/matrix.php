<?php $this->extend('layouts/main'); $this->section('content'); ?>
<div style="max-width:760px">
  <div class="card">
    <div class="card-header">
      <span class="card-title"><i class="fa fa-user-shield" style="color:var(--primary)"></i> <?= esc($role['name']) ?> — Permissions</span>
      <a href="<?= base_url('admin/roles') ?>" class="btn btn-sm btn-outline"><i class="fa fa-arrow-left"></i> Back</a>
    </div>
    <div class="card-body">
      <div style="display:flex;align-items:center;gap:.75rem;padding:.75rem .9rem;background:var(--primary)0D;border:1px solid var(--primary)33;border-radius:10px;margin-bottom:1rem">
        <i class="fa fa-circle-info" style="color:var(--primary)"></i>
        <div style="font-size:.8rem;color:var(--text-m);line-height:1.5">Ticked options appear in the sidebar for this role and are enforced server-side. Untick to remove — staff keep working on everything else instantly.</div>
      </div>

      <?php foreach ($catalog as $group => $keys): ?>
      <div style="margin-bottom:1.1rem">
        <div style="display:flex;align-items:center;justify-content:space-between;padding:.5rem .25rem">
          <span style="font-size:.72rem;font-weight:900;color:var(--text-m);text-transform:uppercase;letter-spacing:.07em"><?= esc($group) ?></span>
          <button type="button" class="grp-toggle" data-group="<?= md5($group) ?>" style="padding:.2rem .6rem;border:1px solid var(--border);border-radius:8px;background:#fff;font-size:.68rem;font-weight:800;color:var(--text-m);cursor:pointer">Toggle all</button>
        </div>
        <div class="perm-group" data-group="<?= md5($group) ?>" style="display:grid;grid-template-columns:1fr 1fr;gap:.5rem">
          <?php foreach ($keys as $key => $label): $on = $allStar || in_array($key, $perms, true); ?>
          <label class="perm-row" style="display:flex;align-items:center;gap:.6rem;padding:.6rem .75rem;border:1.5px solid <?= $on ? 'var(--primary)' : 'var(--border)' ?>;border-radius:10px;cursor:pointer;background:#fff;font-size:.8rem;transition:border-color .15s">
            <input type="checkbox" class="perm-cb" name="perms[]" value="<?= $key ?>" <?= $on ? 'checked' : '' ?> style="accent-color:var(--primary);width:17px;height:17px">
            <span style="font-weight:700;color:var(--text)"><?= esc($label) ?></span>
            <span style="margin-left:auto;font-size:.62rem;color:var(--text-m);font-family:monospace"><?= esc($key) ?></span>
          </label>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endforeach; ?>

      <div id="saveMsg" style="display:none;padding:.65rem .9rem;border-radius:10px;font-size:.8rem;font-weight:700;margin:.5rem 0"></div>
      <div style="display:flex;gap:.6rem;padding:1rem 0 2rem">
        <button onclick="saveMatrix()" class="btn btn-primary" style="flex:2"><i class="fa fa-save"></i> Save Permissions</button>
        <button onclick="resetMatrix()" class="btn btn-outline" style="flex:1"><i class="fa fa-rotate-left"></i> Reset to defaults</button>
      </div>
    </div>
  </div>
</div>

<script>
document.querySelectorAll('.perm-cb').forEach(cb => cb.addEventListener('change', () => {
  const row = cb.closest('.perm-row');
  row.style.borderColor = cb.checked ? 'var(--primary)' : 'var(--border)';
}));
document.querySelectorAll('.grp-toggle').forEach(btn => btn.addEventListener('click', () => {
  const boxes = document.querySelectorAll('.perm-group[data-group="' + btn.dataset.group + '"] .perm-cb');
  const target = ![...boxes].every(b => b.checked);
  boxes.forEach(b => { b.checked = target; b.closest('.perm-row').style.borderColor = target ? 'var(--primary)' : 'var(--border)'; });
}));
function msg(text, ok){
  const el = document.getElementById('saveMsg');
  el.textContent = text; el.style.display = 'block';
  el.style.background = ok ? 'rgba(21,128,61,.1)' : 'rgba(185,28,28,.08)';
  el.style.color = ok ? '#15803D' : '#B91C1C';
  setTimeout(() => el.style.display = 'none', 4200);
}
const CSRF_KEY = document.querySelector('meta[data-name]')?.dataset.name || '';
const CSRF_VAL = document.querySelector('meta[data-name]')?.content || '';
function withCsrf(fd){ if (CSRF_KEY) fd.append(CSRF_KEY, CSRF_VAL); return fd; }
async function saveMatrix(){
  const perms = [...document.querySelectorAll('.perm-cb:checked')].map(b => b.value);
  const fd = new FormData(); perms.forEach(p => fd.append('perms[]', p));
  const r = await fetch('<?= base_url('admin/roles/'.$role['id'].'/permissions') ?>', {method:'POST', body: withCsrf(fd)});
  const d = await r.json();
  msg(d.message || 'Saved', !!d.success);
}
async function resetMatrix(){
  const r = await fetch('<?= base_url('admin/roles/'.$role['id'].'/reset') ?>', {method:'POST', body: withCsrf(new FormData())});
  const d = await r.json();
  if (d.success) location.reload(); else msg(d.message || 'Failed', false);
}
</script>
<?php $this->endSection(); ?>
