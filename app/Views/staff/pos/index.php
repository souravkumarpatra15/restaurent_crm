<?php $this->extend('layouts/pos_layout'); $this->section('content'); ?>

<div style="display:flex;height:calc(100vh - 48px);overflow:hidden;background:var(--bg)">

  <!-- LEFT: Table Map + Quick Actions -->
  <div style="flex:1;overflow-y:auto;padding:1rem">

    <!-- Quick Action Buttons -->
    <div style="display:flex;gap:.5rem;margin-bottom:1rem;flex-wrap:wrap">
      <a href="<?= base_url('pos/new-order/dine_in') ?>" class="btn btn-primary" style="flex:1;min-width:120px;justify-content:center">
        <i class="fa fa-chair"></i> Dine-in
      </a>
      <a href="<?= base_url('pos/new-order/takeaway') ?>" class="btn btn-outline" style="flex:1;min-width:120px;justify-content:center;background:#fff">
        <i class="fa fa-bag-shopping"></i> Takeaway
      </a>
      <a href="<?= base_url('pos/new-order/delivery') ?>" class="btn btn-outline" style="flex:1;min-width:120px;justify-content:center;background:#fff">
        <i class="fa fa-motorcycle"></i> Delivery
      </a>
    </div>

    <!-- Stats Bar -->
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:.5rem;margin-bottom:1rem">
      <div style="background:#fff;border-radius:10px;padding:.75rem;text-align:center;box-shadow:var(--shadow)">
        <div style="font-weight:800;font-size:1.25rem;color:var(--primary)"><?= $openOrder ?? 0 ?></div>
        <div style="font-size:.7rem;color:var(--text-muted)">Active Orders</div>
      </div>
      <div style="background:#fff;border-radius:10px;padding:.75rem;text-align:center;box-shadow:var(--shadow)">
        <?php
          $availCount = 0;
          $occupiedCount = 0;
          foreach ($tables as $area) {
            foreach ($area['tables'] as $t) {
              if ($t['status'] === 'available') $availCount++;
              else $occupiedCount++;
            }
          }
        ?>
        <div style="font-weight:800;font-size:1.25rem;color:var(--success)"><?= $availCount ?></div>
        <div style="font-size:.7rem;color:var(--text-muted)">Available</div>
      </div>
      <div style="background:#fff;border-radius:10px;padding:.75rem;text-align:center;box-shadow:var(--shadow)">
        <div style="font-weight:800;font-size:1.25rem;color:var(--danger)"><?= $occupiedCount ?></div>
        <div style="font-size:.7rem;color:var(--text-muted)">Occupied</div>
      </div>
    </div>

    <!-- Table Map -->
    <?php if (empty($tables)): ?>
      <div style="text-align:center;padding:3rem;color:var(--text-muted)">
        <i class="fa fa-chair" style="font-size:3rem;opacity:.2;display:block;margin-bottom:1rem"></i>
        <p>No tables set up yet.</p>
        <a href="<?= base_url('admin/tables') ?>" class="btn btn-primary" style="margin-top:.75rem">Set Up Tables</a>
      </div>
    <?php else: ?>
      <?php foreach ($tables as $area): ?>
        <?php if (!empty($area['tables'])): ?>
        <div style="margin-bottom:1.25rem">
          <div style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--text-muted);margin-bottom:.5rem">
            <i class="fa fa-layer-group"></i> <?= esc($area['name']) ?>
          </div>
          <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(90px,1fr));gap:.5rem">
            <?php foreach ($area['tables'] as $t): ?>
              <?php
                $statusColor = match($t['status']) {
                  'available' => ['border'=>'var(--success)','bg'=>'#F0FFF4','text'=>'var(--success)'],
                  'occupied'  => ['border'=>'var(--danger)', 'bg'=>'#FFF5F5','text'=>'var(--danger)'],
                  'reserved'  => ['border'=>'var(--warning)','bg'=>'#FFFBEB','text'=>'var(--warning)'],
                  'cleaning'  => ['border'=>'var(--info)',   'bg'=>'#EBF8FF','text'=>'var(--info)'],
                  default     => ['border'=>'var(--border)', 'bg'=>'#fff',   'text'=>'var(--text-muted)'],
                };
              ?>
              <div onclick="tableClick('<?= $t['id'] ?>','<?= $t['status'] ?>','<?= esc($t['table_number']) ?>')"
                   style="border:2px solid <?= $statusColor['border'] ?>;background:<?= $statusColor['bg'] ?>;border-radius:10px;padding:.6rem .4rem;text-align:center;cursor:pointer;transition:all .2s"
                   onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                <div style="font-weight:800;font-size:1rem;color:var(--text)"><?= esc($t['table_number']) ?></div>
                <div style="font-size:.65rem;color:var(--text-muted)"><i class="fa fa-users"></i> <?= $t['capacity'] ?></div>
                <div style="font-size:.62rem;font-weight:700;color:<?= $statusColor['text'] ?>;text-transform:uppercase;margin-top:.15rem"><?= $t['status'] ?></div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endif; ?>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

  <!-- RIGHT: Active Orders List -->
  <div style="width:280px;flex-shrink:0;background:#fff;border-left:1px solid var(--border);display:flex;flex-direction:column;overflow:hidden" id="activeOrdersPanel">
    <div style="padding:.875rem 1rem;border-bottom:1px solid var(--border);font-weight:700;font-size:.9rem;display:flex;align-items:center;justify-content:space-between">
      <span><i class="fa fa-receipt" style="color:var(--primary)"></i> Active Orders</span>
      <button onclick="loadActiveOrders()" style="background:none;border:none;color:var(--text-muted);cursor:pointer;font-size:.8rem"><i class="fa fa-rotate"></i></button>
    </div>
    <div style="flex:1;overflow-y:auto" id="activeOrdersList">
      <div style="text-align:center;padding:2rem;color:var(--text-muted);font-size:.85rem">
        <i class="fa fa-spinner fa-spin"></i> Loading...
      </div>
    </div>
  </div>
</div>

<!-- Table Action Modal -->
<div class="modal-overlay" id="tableModal">
  <div class="modal" style="max-width:320px">
    <div class="modal-header">
      <span class="modal-title" id="tableModalTitle">Table Action</span>
      <button class="modal-close" onclick="closeModal('tableModal')"><i class="fa fa-times"></i></button>
    </div>
    <div class="modal-body" id="tableModalBody"></div>
  </div>
</div>

<script>
function tableClick(tableId, status, tableNum) {
  const title = document.getElementById('tableModalTitle');
  const body  = document.getElementById('tableModalBody');
  title.textContent = 'Table ' + tableNum;

  if (status === 'available') {
    body.innerHTML = `
      <p style="color:var(--text-muted);font-size:.85rem;margin-bottom:1rem">Table is available. What would you like to do?</p>
      <div style="display:flex;flex-direction:column;gap:.5rem">
        <a href="<?= base_url('pos/new-order/dine_in') ?>?table=" + tableId" class="btn btn-primary btn-block">
          <i class="fa fa-utensils"></i> New Dine-in Order
        </a>
        <a href="<?= base_url('pos/new-order/dine_in') ?>?table="+tableId class="btn btn-primary btn-block">
          <i class="fa fa-utensils"></i> New Dine-in Order
        </a>
      </div>`;
    // Fix the link properly
    body.innerHTML = `
      <p style="color:var(--text-muted);font-size:.85rem;margin-bottom:1rem">Table ${tableNum} is available.</p>
      <div style="display:flex;flex-direction:column;gap:.5rem">
        <a href="<?= base_url('pos/new-order/dine_in') ?>?table=${tableId}" class="btn btn-primary btn-block">
          <i class="fa fa-utensils"></i> New Dine-in Order
        </a>
        <button onclick="closeModal('tableModal')" class="btn btn-outline btn-block">Cancel</button>
      </div>`;
  } else if (status === 'occupied') {
    body.innerHTML = `<div style="text-align:center;padding:1rem"><i class="fa fa-spinner fa-spin"></i><p style="margin-top:.5rem;font-size:.85rem">Loading orders...</p></div>`;
    fetch('<?= base_url('pos/table-orders/') ?>' + tableId)
      .then(r => r.json()).then(orders => {
        if (orders.length === 0) {
          body.innerHTML = `<p style="color:var(--text-muted);font-size:.85rem">No active orders found.</p>`;
          return;
        }
        let html = `<p style="font-size:.8rem;color:var(--text-muted);margin-bottom:.75rem">Active orders on this table:</p>`;
        orders.forEach(o => {
          html += `<div style="border:1px solid var(--border);border-radius:8px;padding:.75rem;margin-bottom:.5rem">
            <div style="display:flex;justify-content:space-between;align-items:center">
              <div>
                <div style="font-weight:700;font-size:.875rem">${o.order_number}</div>
                <div style="font-size:.72rem;color:var(--text-muted)">${o.items_count} items · ₹${parseFloat(o.total_amount).toFixed(2)}</div>
              </div>
              <a href="<?= base_url('pos/order/') ?>${o.id}" class="btn btn-sm btn-primary">Open</a>
            </div>
          </div>`;
        });
        html += `<a href="<?= base_url('pos/new-order/dine_in') ?>?table=${tableId}" class="btn btn-outline btn-block" style="margin-top:.5rem"><i class="fa fa-plus"></i> Add New Order</a>`;
        body.innerHTML = html;
      }).catch(() => {
        body.innerHTML = `
          <p style="color:var(--text-muted);font-size:.85rem;margin-bottom:1rem">Table ${tableNum} is occupied.</p>
          <a href="<?= base_url('pos/new-order/dine_in') ?>?table=${tableId}" class="btn btn-primary btn-block">
            <i class="fa fa-plus"></i> New Order on This Table
          </a>`;
      });
  } else {
    body.innerHTML = `
      <p style="color:var(--text-muted);font-size:.85rem;margin-bottom:1rem">Table ${tableNum} is ${status}.</p>
      <a href="<?= base_url('pos/new-order/dine_in') ?>?table=${tableId}" class="btn btn-primary btn-block">
        <i class="fa fa-utensils"></i> Start Order Anyway
      </a>`;
  }

  openModal('tableModal');
}

function loadActiveOrders() {
  const list = document.getElementById('activeOrdersList');
  fetch('<?= base_url('pos/active-orders') ?>')
    .then(r => r.json())
    .then(orders => {
      if (!orders.length) {
        list.innerHTML = '<div style="text-align:center;padding:2rem;color:var(--text-muted);font-size:.85rem"><i class="fa fa-check-circle" style="font-size:2rem;opacity:.3;display:block;margin-bottom:.75rem"></i>No active orders</div>';
        return;
      }
      const statusColors = {pending:'var(--warning)',confirmed:'var(--info)',preparing:'var(--info)',ready:'var(--success)',served:'var(--success)'};
      list.innerHTML = orders.map(o => `
        <a href="<?= base_url('pos/order/') ?>${o.id}" style="display:block;padding:.75rem 1rem;border-bottom:1px solid var(--border);text-decoration:none;color:inherit;transition:background .15s" onmouseover="this.style.background='var(--bg)'" onmouseout="this.style.background=''">
          <div style="display:flex;justify-content:space-between;align-items:flex-start">
            <div>
              <div style="font-weight:700;font-size:.82rem">${o.order_number}</div>
              <div style="font-size:.7rem;color:var(--text-muted)">${o.order_type.replace('_',' ')} ${o.table_number ? '· T'+o.table_number : ''}</div>
              <div style="font-size:.7rem;color:var(--text-muted)">${o.time_ago}</div>
            </div>
            <div style="text-align:right">
              <div style="font-size:.7rem;font-weight:700;color:${statusColors[o.status]||'var(--text-muted)'};text-transform:uppercase">${o.status}</div>
              <div style="font-weight:700;font-size:.85rem;color:var(--primary)">₹${parseFloat(o.total_amount).toFixed(2)}</div>
            </div>
          </div>
        </a>`).join('');
    })
    .catch(() => {
      list.innerHTML = '<div style="text-align:center;padding:1rem;color:var(--text-muted);font-size:.8rem">Could not load orders</div>';
    });
}

// Load on page open + refresh every 30s
loadActiveOrders();
setInterval(loadActiveOrders, 30000);
</script>
<?php $this->endSection(); ?>
