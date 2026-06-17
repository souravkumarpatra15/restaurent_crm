<?php $this->extend('layouts/main');
$this->section('content'); ?>
<div style="padding:0 1rem">
  <div style="display:flex;justify-content:flex-end;margin-bottom:1rem">
    <a href="<?= base_url('super/restaurants/create') ?>" class="btn btn-primary"><i class="fa fa-plus"></i> Add Restaurant</a>
  </div>
  <div class="card">
    <div class="table-wrap">
      <table class="table">
        <thead>
          <tr>
            <th>Restaurant</th>
            <th>Type</th>
            <th>Plan</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($restaurants)): ?>
            <tr>
              <td colspan="5">
                <div class="empty-state"><i class="fa fa-store"></i>
                  <p>No restaurants yet</p>
                </div>
              </td>
            </tr>
            <?php else: foreach ($restaurants as $r): ?>
              <tr>
                <td>
                  <div style="font-weight:600"><?= esc($r['name']) ?></div>
                  <div style="font-size:.75rem;color:var(--text-muted)"><?= esc($r['email']) ?></div>
                </td>
                <td><?= esc(ucfirst(str_replace('_',' ', $r['restaurant_type'] ?? ''))) ?></td>
                <td><span class="badge-pill badge-primary"><?= $r['plan_name'] ?? 'N/A' ?></span></td>
                <td>
                  <?php $c = ['active' => 'success', 'trial' => 'warning', 'suspended' => 'danger', 'expired' => 'danger']; ?>
                  <span class="badge-pill badge-<?= $c[$r['subscription_status']] ?? 'gray' ?>"><?= ucfirst($r['subscription_status']) ?></span>
                </td>
                <td>
                  <a href="<?= base_url('super/restaurants/edit/' . $r['id']) ?>" class="btn btn-sm btn-outline"><i class="fa fa-edit"></i></a>
                  <button
                    type="button"
                    onclick="loginAsRestaurant(<?= $r['id'] ?>)"
                    class="btn btn-sm btn-outline">
                    <i class="fa fa-right-to-bracket"></i>
                  </button>
                </td>
              </tr>
          <?php endforeach;
          endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<script>
  function loginAsRestaurant(id) {
    if (!confirm('Login as this admin?')) return;

    let f = document.createElement('form');
    f.method = 'POST';
    f.action = '<?= base_url('super/restaurants/login-as') ?>/' + id;

    let csrf = document.createElement('input');
    csrf.type = 'hidden';
    csrf.name = '<?= csrf_token() ?>';
    csrf.value = '<?= csrf_hash() ?>';

    f.appendChild(csrf);
    document.body.appendChild(f);
    f.submit();
  }
</script>
<?php $this->endSection(); ?>