<?php $this->extend('layouts/pos_layout');
$this->section('content'); ?>

<div style="height:calc(100vh - 48px);display:flex;flex-direction:column;overflow:hidden">

  <!-- Order Header -->
  <div style="background:var(--sidebar-bg);color:#fff;padding:.875rem 1rem;display:flex;align-items:center;justify-content:space-between;flex-shrink:0">
    <div>
      <div style="font-weight:800;font-size:1rem"><?= esc($order['order_number']) ?></div>
      <div style="font-size:.75rem;opacity:.7">
        <?= ucfirst(str_replace('_', ' ', $order['order_type'])) ?>
        <?= $order['table'] ? ' · Table ' . $order['table']['table_number'] : '' ?>
        <?= $order['customer_name'] ? ' · ' . $order['customer_name'] : '' ?>
      </div>
    </div>
    <?php $sc = ['pending' => '#F6AD55', 'confirmed' => '#63B3ED', 'preparing' => '#63B3ED', 'ready' => '#68D391', 'served' => '#68D391', 'completed' => '#68D391', 'cancelled' => '#FC8181']; ?>
    <span style="background:<?= $sc[$order['status']] ?? '#CBD5E0' ?>;color:#1A202C;font-size:.72rem;font-weight:700;padding:.25rem .75rem;border-radius:20px;text-transform:uppercase">
      <?= $order['status'] ?>
    </span>
  </div>

  <!-- Order Content -->
  <div style="flex:1;overflow-y:auto;padding:1rem;display:flex;flex-direction:column;gap:1rem">

    <!-- Items List -->
    <div class="card">
      <div class="card-header">
        <span class="card-title"><i class="fa fa-list" style="color:var(--primary)"></i> Items (<?= count($order['items']) ?>)</span>
        <a href="<?= base_url('pos/new-order/' . $order['order_type']) ?>?table=<?= $order['table_id'] ?>&edit=<?= $order['id'] ?>" class="btn btn-sm btn-outline">
          <i class="fa fa-plus"></i> Add More
        </a>
      </div>
      <?php foreach ($order['items'] as $item): ?>
        <div style="display:flex;align-items:center;justify-content:space-between;padding:.7rem 1.25rem;border-bottom:1px solid var(--border)">
          <div style="flex:1">
            <div style="font-weight:600;font-size:.875rem"><?= esc($item['name']) ?></div>
            <?php if ($item['variant_name']): ?>
              <div style="font-size:.72rem;color:var(--text-muted)"><?= esc($item['variant_name']) ?></div>
            <?php endif; ?>
            <?php if ($item['notes']): ?>
              <div style="font-size:.72rem;color:var(--warning)">* <?= esc($item['notes']) ?></div>
            <?php endif; ?>
            <?php $statusDot = ['pending' => '#CBD5E0', 'preparing' => '#63B3ED', 'ready' => '#68D391', 'served' => '#68D391', 'cancelled' => '#FC8181']; ?>
            <div style="display:inline-block;width:8px;height:8px;border-radius:50%;background:<?= $statusDot[$item['status']] ?? '#CBD5E0' ?>;margin-top:.2rem"></div>
            <span style="font-size:.68rem;color:var(--text-muted)"><?= $item['status'] ?></span>
          </div>
          <div style="text-align:right;margin-left:.75rem">
            <div style="font-size:.8rem;color:var(--text-muted)"><?= $item['quantity'] ?> × ₹<?= number_format($item['unit_price'], 2) ?></div>
            <div style="font-weight:800;font-size:.9rem;color:var(--primary)">₹<?= number_format($item['total_price'], 2) ?></div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <!-- Totals -->
    <div class="card">
      <div class="card-body" style="padding:.875rem 1.25rem">
        <div style="display:flex;justify-content:space-between;margin-bottom:.3rem;font-size:.85rem"><span>Subtotal</span><span>₹<?= number_format($order['subtotal'], 2) ?></span></div>
        <?php if ($order['discount_amount'] > 0): ?>
          <div style="display:flex;justify-content:space-between;margin-bottom:.3rem;font-size:.85rem;color:var(--success)"><span>Discount</span><span>-₹<?= number_format($order['discount_amount'], 2) ?></span></div>
        <?php endif; ?>
        <?php if ($order['cgst_amount'] > 0): ?>
          <div style="display:flex;justify-content:space-between;margin-bottom:.3rem;font-size:.85rem;color:var(--text-muted)"><span>CGST</span><span>₹<?= number_format($order['cgst_amount'], 2) ?></span></div>
          <div style="display:flex;justify-content:space-between;margin-bottom:.3rem;font-size:.85rem;color:var(--text-muted)"><span>SGST</span><span>₹<?= number_format($order['sgst_amount'], 2) ?></span></div>
        <?php elseif ($order['tax_amount'] > 0): ?>
          <div style="display:flex;justify-content:space-between;margin-bottom:.3rem;font-size:.85rem;color:var(--text-muted)"><span>Tax</span><span>₹<?= number_format($order['tax_amount'], 2) ?></span></div>
        <?php endif; ?>
        <?php if ($order['service_charge'] > 0): ?>
          <div style="display:flex;justify-content:space-between;margin-bottom:.3rem;font-size:.85rem;color:var(--text-muted)"><span>Service Charge</span><span>₹<?= number_format($order['service_charge'], 2) ?></span></div>
        <?php endif; ?>
        <?php if ($order['round_off'] != 0): ?>
          <div style="display:flex;justify-content:space-between;margin-bottom:.3rem;font-size:.85rem;color:var(--text-muted)"><span>Round Off</span><span><?= $order['round_off'] > 0 ? '+' : '' ?>₹<?= number_format($order['round_off'], 2) ?></span></div>
        <?php endif; ?>
        <div style="display:flex;justify-content:space-between;border-top:2px solid var(--border);padding-top:.5rem;margin-top:.4rem">
          <span style="font-weight:800;font-size:1rem">Total</span>
          <span style="font-weight:800;font-size:1.1rem;color:var(--primary)">₹<?= number_format($order['total_amount'], 2) ?></span>
        </div>
        <?php if ($order['payment_status'] === 'paid'): ?>
          <div style="background:#F0FFF4;border-radius:8px;padding:.5rem .75rem;margin-top:.75rem;text-align:center;color:var(--success);font-weight:700;font-size:.875rem">
            <i class="fa fa-check-circle"></i> PAID
          </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Payments if any -->
    <?php if (!empty($order['payments'])): ?>
      <div class="card">
        <div class="card-header"><span class="card-title">Payments</span></div>
        <div class="card-body" style="padding:.75rem 1.25rem">
          <?php foreach ($order['payments'] as $p): ?>
            <div style="display:flex;justify-content:space-between;padding:.3rem 0;font-size:.85rem;border-bottom:1px solid var(--border)">
              <span><?= ucfirst(str_replace('_', ' ', $p['payment_method'])) ?> <?= $p['payment_reference'] ? '<span style="color:var(--text-muted);font-size:.75rem">(' . $p['payment_reference'] . ')</span>' : '' ?></span>
              <strong>₹<?= number_format($p['amount'], 2) ?></strong>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>
  </div>

  <!-- Action Footer -->
  <?php if ($order['payment_status'] !== 'paid' && $order['status'] !== 'cancelled'): ?>
    <div style="padding:.875rem;background:#fff;border-top:1px solid var(--border);display:flex;gap:.5rem;flex-shrink:0">
      <button onclick="printKotAction()" class="btn btn-outline" style="flex:1">
        <i class="fa fa-fire-burner"></i> KOT
      </button>
      <button onclick="printBillAction()" class="btn btn-outline" style="flex:1">
        <i class="fa fa-print"></i> Print Bill
      </button>
      <button onclick="openCheckoutModal()" class="btn btn-primary" style="flex:2">
        <i class="fa fa-cash-register"></i> Checkout — ₹<?= number_format($order['total_amount'], 2) ?>
      </button>
    </div>
  <?php elseif ($order['payment_status'] === 'paid'): ?>
    <div style="padding:.875rem;background:#fff;border-top:1px solid var(--border);display:flex;gap:.5rem;flex-shrink:0">
      <button onclick="reprintBill()" class="btn btn-outline btn-block">
        <i class="fa fa-print"></i> Reprint Bill
      </button>
      <a href="<?= base_url('pos') ?>" class="btn btn-primary btn-block">
        <i class="fa fa-arrow-left"></i> Back to POS
      </a>
    </div>
  <?php endif; ?>
</div>

<!-- Checkout Modal -->
<div class="modal-overlay" id="checkoutModal">
  <div class="modal" style="max-width:400px">
    <div class="modal-header">
      <span class="modal-title">💳 Checkout</span>
      <button class="modal-close" onclick="closeModal('checkoutModal')"><i class="fa fa-times"></i></button>
    </div>
    <div class="modal-body">
      <div style="text-align:center;margin-bottom:1.25rem;padding:1rem;background:var(--primary-light);border-radius:10px">
        <div style="font-size:.82rem;color:var(--text-muted)">Amount Due</div>
        <div style="font-size:2rem;font-weight:800;color:var(--primary)">₹<?= number_format($order['total_amount'], 2) ?></div>
      </div>
      <div class="form-group">
        <label class="form-label">Payment Method</label>
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:.4rem">
          <?php foreach (['cash' => '💵 Cash', 'card' => '💳 Card', 'upi' => '📱 UPI', 'wallet' => '👛 Wallet', 'online' => '🌐 Online', 'credit' => '📋 Credit'] as $k => $v): ?>
            <button class="btn btn-outline payment-btn" data-method="<?= $k ?>" onclick="selectPayment(this)" style="font-size:.78rem;padding:.4rem">
              <?= $v ?>
            </button>
          <?php endforeach; ?>
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Amount Received</label>
        <input type="number" class="form-control" id="payAmt" value="<?= number_format($order['total_amount'], 2, '.', '') ?>" style="font-size:1.1rem;font-weight:700;text-align:center" oninput="calcChange()">
      </div>
      <div id="changeRow" style="display:none;background:#F0FFF4;padding:.625rem;border-radius:8px;text-align:center;font-weight:700;color:var(--success);margin-bottom:.5rem">
        Change: <span id="changeAmt">₹0</span>
      </div>
      <div style="display:flex;gap:.4rem;flex-wrap:wrap">
        <button class="btn btn-sm btn-outline" onclick="setAmt(<?= ceil($order['total_amount']) ?>)">Exact</button>
        <button class="btn btn-sm btn-outline" onclick="setAmt(500)">₹500</button>
        <button class="btn btn-sm btn-outline" onclick="setAmt(1000)">₹1000</button>
        <button class="btn btn-sm btn-outline" onclick="setAmt(2000)">₹2000</button>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-outline" onclick="closeModal('checkoutModal')">Cancel</button>
      <button class="btn btn-success btn-lg" id="payBtn" onclick="processCheckout()">
        <i class="fa fa-check"></i> Confirm Payment
      </button>
    </div>
  </div>
</div>

<script>
  const ORDER_ID = <?= $order['id'] ?>;
  const ORDER_TOTAL = <?= $order['total_amount'] ?>;
  const CSRF_NAME = '<?= csrf_token() ?>';
  const CSRF_TOKEN = '<?= csrf_hash() ?>';
  let selectedMethod = 'cash';

  function selectPayment(btn) {
    document.querySelectorAll('.payment-btn').forEach(b => {
      b.classList.remove('btn-primary');
      b.classList.add('btn-outline');
    });
    btn.classList.remove('btn-outline');
    btn.classList.add('btn-primary');
    selectedMethod = btn.dataset.method;
    document.getElementById('changeRow').style.display = selectedMethod === 'cash' ? '' : 'none';
  }

  document.querySelector('[data-method="cash"]')?.click();

  function setAmt(v) {
    document.getElementById('payAmt').value = v;
    calcChange();
  }

  function calcChange() {
    const paid = parseFloat(document.getElementById('payAmt').value) || 0;
    const change = paid - ORDER_TOTAL;
    if (change > 0 && selectedMethod === 'cash') {
      document.getElementById('changeRow').style.display = '';
      document.getElementById('changeAmt').textContent = '₹' + change.toFixed(2);
    } else {
      document.getElementById('changeRow').style.display = 'none';
    }
  }

  function openCheckoutModal() {
    openModal('checkoutModal');
  }

  function processCheckout() {
    const paid = parseFloat(document.getElementById('payAmt').value) || 0;
    if (paid < ORDER_TOTAL) {
      showToast('Amount is less than total!', 'error');
      return;
    }

    const btn = document.getElementById('payBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Processing...';

    fetch('<?= base_url('pos/order/checkout') ?>', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: new URLSearchParams({
          [CSRF_NAME]: CSRF_TOKEN,
          order_id: ORDER_ID,
          payments: JSON.stringify([{
            method: selectedMethod,
            amount: paid,
            reference: ''
          }])
        })
      }).then(r => {
        if (r.redirected) {
          throw new Error('Redirected to: ' + r.url);
        }
        return r.json();
      }).then(d => {
        if (d.success) {
          closeModal('checkoutModal');
          const change = paid - ORDER_TOTAL;
          const msg = document.createElement('div');
          msg.style.cssText = 'position:fixed;inset:0;background:rgba(0,0,0,.7);z-index:9999;display:flex;align-items:center;justify-content:center';
          msg.innerHTML = `<div style="background:#fff;border-radius:16px;padding:2rem;text-align:center;max-width:300px;width:90%">
        <div style="font-size:3rem;margin-bottom:.75rem">✅</div>
        <div style="font-weight:800;font-size:1.25rem;margin-bottom:.4rem">Payment Successful!</div>
        <div style="font-size:.85rem;color:var(--text-muted);margin-bottom:1rem">Bill: ${d.invoice_number}</div>
        ${change > 0 ? `<div style="background:#F0FFF4;padding:.625rem;border-radius:8px;margin-bottom:1rem;font-weight:700;color:var(--success)">Change: ₹${change.toFixed(2)}</div>` : ''}
        <a href="<?= base_url('pos') ?>" style="display:block;background:var(--primary);color:#fff;padding:.75rem;border-radius:8px;font-weight:700;text-decoration:none">New Order</a>
      </div>`;
          document.body.appendChild(msg);
        } else {
          showToast(d.message || 'Payment failed', 'error');
        }
      }).catch(e => showToast(e.message || 'Network error', 'error'))
      .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa fa-check"></i> Confirm Payment';
      });
  }

  function openKotSlip() {
  window.open('<?= base_url('pos/slip/kot/'.$order['id']) ?>','kot_slip','width=380,height=650,scrollbars=yes');
}
function openBillSlip() {
  window.open('<?= base_url('pos/slip/bill/'.$order['id']) ?>','bill_slip','width=380,height=750,scrollbars=yes');
}
function printKotAction() {
    fetch('<?= base_url('pos/order/print-kot') ?>', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: new URLSearchParams({
          [CSRF_NAME]: CSRF_TOKEN,
          order_id: ORDER_ID
        })
      }).then(r => {
        if (r.redirected) {
          throw new Error('Redirected to: ' + r.url);
        }
        return r.json();
      }).then(d => showToast(d.success ? 'KOT sent to kitchen!' : (d.error || 'Printer error'), d.success ? 'success' : 'error'))
      .catch(e => showToast(e.message || 'Network error', 'error'));
  }

  function printBillAction() {
    fetch('<?= base_url('pos/order/print-bill') ?>', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: new URLSearchParams({
          [CSRF_NAME]: CSRF_TOKEN,
          order_id: ORDER_ID
        })
      }).then(r => {
        if (r.redirected) {
          throw new Error('Redirected to: ' + r.url);
        }
        return r.json();
      }).then(d => showToast(d.success ? 'Bill printed!' : (d.error || 'Printer error'), d.success ? 'success' : 'error'))
      .catch(e => showToast(e.message || 'Network error', 'error'));
  }

  function reprintBill() {
    fetch('<?= base_url('pos/order/print-bill') ?>', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: new URLSearchParams({
          [CSRF_NAME]: CSRF_TOKEN,
          order_id: ORDER_ID
        })
      }).then(r => {
        if (r.redirected) {
          throw new Error('Redirected to: ' + r.url);
        }
        return r.json();
      }).then(d => showToast(d.success ? 'Reprinted!' : (d.error || 'Error'), d.success ? 'success' : 'error'))
      .catch(e => showToast(e.message || 'Network error', 'error'));
  }
</script>

<?php $this->endSection(); ?>