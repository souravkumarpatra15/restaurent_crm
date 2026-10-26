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

    <?php $activeStatuses = ['pending', 'confirmed', 'preparing', 'ready', 'served']; ?>
    <?php if (in_array($order['status'], $activeStatuses, true) && in_array($order['order_type'], ['dine_in', 'takeaway'], true)): ?>
    <!-- Kitchen Timeline control — advances the customer's live tracking page -->
    <div class="card" id="timelineCard">
      <div class="card-body" style="padding:.7rem 1rem">
        <?php
          $tlSteps = [
            'pending'    => ['Received',  'clipboard-list'],
            'confirmed'  => ['Confirmed', 'check'],
            'preparing'  => ['Cooking',   'fire-burner'],
            'ready'      => ['Ready',     'utensils'],
            'served'     => ['Served',    'bell-concierge'],
          ];
          $curIdx = array_search($order['status'], array_keys($tlSteps), true);
        ?>
        <div style="display:flex;align-items:center;justify-content:center">
          <?php $n = 0; foreach ($tlSteps as $sk => [$sv, $sic]) : $n++; $isNow = $n === $curIdx; $isDone = $n < $curIdx; ?>
          <div style="display:flex;flex-direction:column;align-items:center;gap:.15rem;min-width:58px">
            <span style="width:26px;height:26px;border-radius:50%;background:<?= $isNow ? 'var(--primary)' : ($isDone ? '#68D391' : 'var(--border)') ?>;color:#fff;display:inline-flex;align-items:center;justify-content:center;font-size:.6rem<?= $isNow ? ';box-shadow:0 0 0 4px rgba(255,107,53,.15)' : '' ?>">
              <i class="fa fa-<?= $sic ?>"></i>
            </span>
            <span style="font-size:.62rem;font-weight:800;color:<?= $isNow ? 'var(--primary)' : ($isDone ? 'var(--success)' : 'var(--text-muted)') ?>"><?= $sv ?></span>
          </div>
          <?php if ($n < count($tlSteps)): ?><i class="fa fa-chevron-right" style="font-size:.55rem;color:var(--border)"></i><?php endif; ?>
          <?php endforeach; ?>
        </div>
        <?php if ($order['status'] === 'pending'): ?>
        <button class="btn btn-primary" style="width:100%;margin-top:.55rem" onclick="confirmPendingOrder()">
          <i class="fa fa-check"></i> Confirm order &amp; send to kitchen
        </button>
        <?php else: ?>
        <?php
          $nxtMap = [
            'confirmed' => ['preparing', '🍳 Start cooking',  '#ED8936'],
            'preparing' => ['ready',     '🍽 Mark Ready',     '#38A169'],
            'ready'     => ['served',    '🔔 Mark Served',   '#2B6CB0'],
          ];
          $nx = $nxtMap[$order['status']] ?? null;
        ?>
        <?php if ($nx): ?>
        <button class="btn" style="width:100%;margin-top:.55rem;background:<?= $nx[2] ?>;color:#fff" onclick="advanceStatus('<?= $nx[0] ?>', '<?= $order['status'] ?>')">
          <i class="fa fa-forward"></i> <?= $nx[1] ?>
        </button>
        <?php else: ?>
        <div style="text-align:center;font-size:.72rem;color:var(--text-muted);margin-top:.5rem">Served — complete the payment to finish the order</div>
        <?php endif; ?>
        <?php endif; ?>
      </div>
    </div>
    <?php endif; ?>

    <!-- Items List -->
    <div class="card">
      <div class="card-header">
        <span class="card-title"><i class="fa fa-list" style="color:var(--primary)"></i> Items (<?= count($order['items']) ?>)</span>
        <a href="<?= base_url('pos/new-order/' . $order['order_type']) ?>?table=<?= $order['table_id'] ?>&add_to=<?= $order['id'] ?>" class="btn btn-sm btn-outline">
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
      <button onclick="cancelOrderAction()" class="btn btn-outline" style="flex:1;border-color:#FCA5A5;color:#B91C1C" title="Cancel this order">
        <i class="fa fa-ban"></i> Cancel
      </button>
      <button onclick="printKotAction()" class="btn btn-outline" style="flex:1">
        <i class="fa fa-fire-burner"></i> KOT
      </button>
      <button onclick="printBillAction()" class="btn btn-outline" style="flex:1">
        <i class="fa fa-print"></i> Bill
      </button>
      <button onclick="startUpiQr()" class="btn" style="flex:2;background:#0B1E3D;color:#fff">
        <i class="fa fa-qrcode"></i> UPI QR Pay
      </button>
      <button onclick="openCheckoutModal()" class="btn btn-primary" style="flex:2">
        <i class="fa fa-cash-register"></i> Checkout — ₹<?= number_format($order['total_amount'], 2) ?>
      </button>
    </div>
  <?php elseif ($order['status'] === 'cancelled'): ?>
    <div style="padding:.875rem;background:#FFF5F5;border-top:1px solid var(--border);display:flex;align-items:center;gap:.5rem;flex-shrink:0">
      <i class="fa fa-ban" style="color:#B91C1C"></i>
      <div style="flex:1">
        <div style="font-weight:800;color:#B91C1C;font-size:.875rem">Order cancelled</div>
        <div style="font-size:.75rem;color:#7F1D1D"><?= esc($order['cancelled_reason'] ?? '') ?></div>
      </div>
      <a href="<?= base_url('pos') ?>" class="btn btn-outline">Back to POS</a>
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
            <button class="btn payment-btn" data-method="upi_qr" onclick="startUpiQr()" style="font-size:.78rem;padding:.4rem;background:var(--primary);color:#fff">
              📲 UPI QR
            </button>
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

<!-- UPI QR Payment Modal — mock flow: Total → QR (fixed amount) → live status → verify -->
<div class="modal-overlay" id="upiQrModal">
  <div class="modal" style="max-width:400px">
    <div class="modal-header">
      <span class="modal-title">📲 Payment — <span id="qrOrder"></span></span>
      <button class="modal-close" onclick="closeUpiQr()"><i class="fa fa-times"></i></button>
    </div>
    <div class="modal-body" style="text-align:center;max-height:calc(100vh - 210px);overflow-y:auto">
      <a id="qrCustomerLink" href="#" style="display:none"></a>
      <div style="padding:.875rem;background:var(--primary-light,#FFF1E8);border-radius:10px;margin-bottom:.875rem">
        <div style="font-size:.78rem;color:var(--text-muted)">Total Amount</div>
        <div style="font-size:2rem;font-weight:800;color:var(--primary)">₹<span id="qrAmt">—</span></div>
      </div>

      <div id="qrHolder" style="margin:0 auto .75rem;width:238px;height:238px;display:flex;align-items:center;justify-content:center;background:#fff;border:1px solid var(--border);border-radius:12px;overflow:hidden">
        <i class="fa fa-spinner fa-spin" style="font-size:1.5rem;color:var(--text-muted)"></i>
      </div>

      <div style="margin-bottom:.55rem">
        <div style="font-weight:800;font-size:.95rem">Scan QR to Pay</div>
        <div style="font-size:.75rem;color:var(--text-muted)">UPI Payment · Scan using any UPI app</div>
        <div style="margin-top:.4rem"><span class="badge-pill badge-success" style="font-size:.72rem">✓ Amount is fixed — pay exactly ₹<span id="qrAmt2">—</span></span></div>
      </div>

      <div id="qrStatusBox" style="background:#F5F7FA;border-radius:10px;padding:.65rem;font-size:.8rem;margin-bottom:.35rem">
        ⏳ Waiting for payment — this updates automatically…
      </div>
      <div style="font-size:.68rem;color:var(--text-muted);margin-bottom:.5rem;display:flex;justify-content:center;gap:.5rem;flex-wrap:wrap">
        <span>Ref <span id="qrRef" style="font-family:'JetBrains Mono',monospace"></span></span>
        <span>· valid until <span id="qrExpiry"></span></span>
      </div>

      <!-- customer screen helper -->
      <div style="background:#F0F9FF;border:1px solid #BAE6FD;border-radius:10px;padding:.55rem .65rem;margin-bottom:.55rem;text-align:left">
        <div style="font-size:.72rem;color:#0369A1;line-height:1.5">
          <b>How the customer pays:</b> they scan the QR above with any UPI app (GPay/PhonePe/Paytm).
          It already contains the exact amount — no typing, no wrong amounts.
        </div>
        <div style="display:flex;gap:.4rem;margin-top:.45rem">
          <button class="btn btn-sm" style="background:#0284C7;color:#fff;flex:1" onclick="window.open(document.getElementById('qrCustomerLink').href,'_blank')">
            <i class="fa fa-mobile"></i> Open customer screen
          </button>
          <button class="btn btn-outline btn-sm" style="flex:1" onclick="copyCustomerLink()"><i class="fa fa-copy"></i> Copy link</button>
        </div>
        <div style="font-size:.66rem;color:rgba(0,0,0,.45);margin-top:.35rem;line-height:1.4">
          The customer screen is a mobile page with this same QR + live status — use it on the counter tablet, or send the link to the customer's phone (WhatsApp).
        </div>
      </div>

      <!-- verify mini-form (slides in under the QR, QR stays visible) -->
      <div id="qrVerifyPane" style="display:none;text-align:left;background:#F0FFF4;border:1px solid #86EFAC;border-radius:10px;padding:.7rem">
        <div style="font-size:.76rem;color:#166534;line-height:1.45">
          <b>✅ Money received? Confirm it here.</b> Check your bank/merchant app first.
          Only confirm what you actually see credited.
        </div>
        <div class="form-group" style="margin:.55rem 0 .45rem">
          <label class="form-label" style="font-size:.72rem">Bank / UPI reference (UTR) — <span style="color:var(--text-muted)">optional</span></label>
          <input type="text" id="qrTxnRef" class="form-control" maxlength="64" placeholder="Not mandatory — auto-recorded if blank" style="font-family:'JetBrains Mono',monospace">
          <div style="font-size:.66rem;color:var(--text-muted);margin-top:.2rem">Most banks/apps show a 12-digit reference. Leave it blank if you can't find it — we record one automatically.</div>
        </div>
        <div class="form-group" style="margin:0 0 .5rem">
          <label class="form-label" style="font-size:.72rem">Note for your records (optional)</label>
          <input type="text" id="qrVerifyNote" class="form-control" maxlength="200" placeholder="e.g. Salary-day rush, checked by Ramesh">
        </div>
        <div style="display:flex;gap:.4rem">
          <button class="btn btn-outline btn-sm" onclick="toggleVerifyPane(false)">Back</button>
          <button class="btn btn-success btn-sm" id="qrVerifyBtn" style="flex:2" onclick="verifyUpiNow()"><i class="fa fa-check"></i> Confirm receipt — ₹<span id="qrVerifyAmt">—</span></button>
        </div>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-outline" id="qrRejectBtn" onclick="rejectUpiClaim()"><i class="fa fa-ban"></i> Reject</button>
      <button class="btn btn-primary" id="qrVerifyOpenBtn" onclick="toggleVerifyPane(true)"><i class="fa fa-check"></i> Money received? Verify</button>
    </div>
  </div>
</div>

<script>
  const ORDER_ID = <?= $order['id'] ?>;
  const ORDER_NUMBER = '<?= esc($order['order_number']) ?>';
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
    // UPI opens the restaurant QR flow instead of instant recording
    const payBtn = document.getElementById('payBtn');
    if (selectedMethod === 'upi') {
      payBtn.innerHTML = '<i class="fa fa-qrcode"></i> Open UPI QR';
      document.getElementById('payAmt').closest('.form-group').style.opacity = '.45';
    } else {
      payBtn.innerHTML = '<i class="fa fa-check"></i> Confirm Payment';
      document.getElementById('payAmt').closest('.form-group').style.opacity = '';
    }
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

  // ── Kitchen timeline: advance the order toward served ──
  function advanceStatus(next, from) {
    const btn = event.target.closest('button');
    if (btn) { btn.disabled = true; btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Updating…'; }
    fetch('<?= base_url('pos/order/set-status') ?>', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
      body: new URLSearchParams({ [CSRF_NAME]: CSRF_TOKEN, order_id: ORDER_ID, status: next, order_status: from })
    }).then(r => r.json()).then(d => {
      if (d.success) {
        showToast(d.message + ' — customer screen updates automatically', 'success');
        setTimeout(() => location.reload(), 700);
      } else {
        showToast(d.message || 'Could not update', 'error');
        if (btn) { btn.disabled = false; location.reload(); }
      }
    }).catch(() => { showToast('Network error', 'error'); if (btn) { btn.disabled = false; btn.innerHTML = 'Retry'; } });
  }

  function confirmPendingOrder() {
    // Uses the QR-order confirm endpoint (creates the KOT + prints), same as the Ready screen
    fetch('<?= base_url('pos/order/confirm-customer/' . $order['id']) ?>', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
      body: new URLSearchParams({ [CSRF_NAME]: CSRF_TOKEN })
    }).then(r => r.json()).then(d => {
      if (d.success) {
        showToast('Order confirmed & sent to kitchen', 'success');
        setTimeout(() => location.reload(), 700);
      } else {
        showToast(d.message || 'Could not confirm', 'error');
      }
    }).catch(() => showToast('Network error', 'error'));
  }

  // ── Live status sync: kitchen/delivery updates appear here on their own ──
  let POS_STATUS = '<?= $order['status'] ?>';
  setInterval(async () => {
    if (!document.body || document.body.dataset.posPoll === 'off') return;
    try {
      const d = await (await fetch('<?= base_url('pos/order/state/' . $order['id']) ?>')).json();
      if (d.success && d.order && d.order.status && d.order.status !== POS_STATUS) {
        POS_STATUS = d.order.status;
        if (document.querySelectorAll('.modal-overlay.open').length === 0) {
          location.reload();
        } else {
          showToast('Order status changed — the page will refresh', 'warning');
          setTimeout(() => location.reload(), 2500);
        }
      }
    } catch (e) { /* ignore transient errors */ }
  }, 10000);

  function processCheckout() {
    // UPI is NEVER instantly recorded — the money must land on the
    // restaurant's own QR and be verified against the bank app.
    if (selectedMethod === 'upi') {
      closeModal('checkoutModal');
      startUpiQr();
      showToast('UPI is collected through your restaurant QR — show it to the customer', 'info');
      return;
    }

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
  function handlePrintResult(d, okMsg) {
    if (d.success) { showToast(okMsg, 'success'); return; }
    const fullError = d.error || 'Printer error';
    // Network/IP print failed — offer the exact same receipt as a browser slip
    if (d.slip_url && confirm(fullError + '\n\nPrint via browser slip instead?')) {
      window.open(d.slip_url, '_blank', 'width=420,height=760,scrollbars=yes');
      return;
    }
    showToast(fullError, 'error');
  }

  function cancelOrderAction() {
    const reason = prompt('Reason for cancelling this order?', 'Walk-in cancelled');
    if (reason === null) return;
    fetch('<?= base_url('pos/order/cancel/' . $order['id']) ?>', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: new URLSearchParams({
          [CSRF_NAME]: CSRF_TOKEN,
          reason: reason
        })
      }).then(r => r.json()).then(d => {
        if (d.success) {
          showToast(d.message || 'Order cancelled', 'warning');
          setTimeout(() => location.reload(), 700);
        } else {
          showToast(d.message || 'Could not cancel order', 'error');
        }
      }).catch(e => showToast(e.message || 'Network error', 'error'));
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
      }).then(d => handlePrintResult(d, 'KOT sent to kitchen!'))
      .catch(e => showToast(e.message || 'Network error', 'error'));
  }

  function printBillAction() {
    // Bill print = the same printable web slip everywhere (PDF-like),
    // so reprint is identical to the original and works without a thermal printer
    window.open('<?= base_url('pos/slip/bill/') ?>' + ORDER_ID + '?print=1', '_blank');
    showToast('Opening the printable bill…', 'info');
  }

  function reprintBill() {
    printBillAction();
  }

  // ── UPI QR payment flow (mock-style: Total → fixed QR → live status → verify) ──
  let UPI_REF = null, UPI_TIMER = null;

  function startUpiQr() {
    closeModal('checkoutModal');
    UPI_REF = null;
    document.getElementById('qrVerifyPane').style.display = 'none';
    document.getElementById('qrRejectBtn').disabled = false;
    document.getElementById('qrVerifyOpenBtn').disabled = false;
    document.getElementById('qrHolder').innerHTML = '<i class="fa fa-spinner fa-spin" style="font-size:1.5rem;color:var(--text-muted)"></i>';
    document.getElementById('qrStatusBox').innerHTML = '⏳ Preparing your QR…';
    document.getElementById('qrAmt').textContent = '—';
    document.getElementById('qrAmt2').textContent = '—';
    openModal('upiQrModal');

    fetch('<?= base_url('pos/upi/qr') ?>', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
        body: new URLSearchParams({ [CSRF_NAME]: CSRF_TOKEN, order_id: ORDER_ID })
      }).then(r => r.json()).then(d => {
        if (!d.success) {
          closeUpiQr();
          showToast(d.message || 'UPI QR not available', d.needs_setup ? 'warning' : 'error');
          if (d.needs_setup) setTimeout(() => { location.href = '<?= base_url('admin/upi-settings') ?>'; }, 800);
          return;
        }
        UPI_REF = d.attempt_ref;
        const amt = parseFloat(d.amount).toFixed(2).replace(/\.00$/, '');
        document.getElementById('qrAmt').textContent   = amt;
        document.getElementById('qrAmt2').textContent  = amt;
        document.getElementById('qrOrder').textContent = ORDER_NUMBER;
        document.getElementById('qrRef').textContent   = d.attempt_ref;
        document.getElementById('qrExpiry').textContent = d.expires_at;
        document.getElementById('qrHolder').innerHTML = d.qr
          ? '<img src="' + d.qr + '" style="width:100%;height:100%;object-fit:contain" alt="UPI QR">'
          : '<div style="font-size:.78rem;color:var(--danger);padding:.6rem">QR image unavailable — open the customer link below</div>';
        document.getElementById('qrCustomerLink').href = d.customer_url;
        document.getElementById('qrStatusBox').innerHTML = d.resumed
          ? '⏳ Same QR reopened — still waiting for this payment…'
          : '⏳ Waiting for payment — this updates automatically…';
        startUpiPoll(d.status_url);
      }).catch(e => {
        closeUpiQr();
        showToast(e.message || 'Network error', 'error');
      });
  }

  function startUpiPoll(statusUrl) {
    stopUpiPoll();
    if (!statusUrl) return;
    UPI_TIMER = setInterval(() => {
      fetch(statusUrl).then(r => r.json()).then(d => {
        if (!d.success) { stopUpiPoll(); return; }
        const box = document.getElementById('qrStatusBox');
        if (d.claimed && d.state === 'pending') {
          box.style.background = '#FFF7ED';
          box.innerHTML = '🙋 Customer says they have paid — check your bank/merchant app, then verify below.';
        }
        if (d.state === 'paid') {
          stopUpiPoll();
          box.style.background = '#F0FFF4';
          box.innerHTML = '✅ Payment verified — ₹' + d.amount.replace(/\.00$/, '') + ' received. Thank you!';
          showModalFooter(false);
          setTimeout(() => { closeModal('upiQrModal'); location.reload(); }, 1400);
        } else if (d.state === 'expired') {
          stopUpiPoll();
          box.style.background = '#F9F5EF';
          box.innerHTML = '⏰ QR expired — close and tap UPI QR Pay again for a fresh one.';
          showModalFooter(false);
        } else if (d.state === 'rejected') {
          stopUpiPoll();
          box.style.background = '#FEF2F2';
          box.innerHTML = '🚫 Claim rejected — close and tap UPI QR Pay to retry.';
          showModalFooter(false);
        }
      }).catch(() => {});
    }, 5000);
  }

  function showModalFooter(show) {
    document.getElementById('qrRejectBtn').style.display = show ? '' : 'none';
    document.getElementById('qrVerifyOpenBtn').style.display = show ? '' : 'none';
  }

  function stopUpiPoll() {
    if (UPI_TIMER) { clearInterval(UPI_TIMER); UPI_TIMER = null; }
  }

  function closeUpiQr() {
    stopUpiPoll();
    closeModal('upiQrModal');
  }

  function copyCustomerLink() {
    const url = document.getElementById('qrCustomerLink').href;
    if (!url || url === '#') return;
    (navigator.clipboard ? navigator.clipboard.writeText(url) : Promise.reject())
      .then(() => showToast('Customer link copied — send it on WhatsApp or open on any phone', 'success'))
      .catch(() => {
        const i = document.createElement('input');
        i.value = url; document.body.appendChild(i); i.select();
        document.execCommand('copy'); i.remove();
        showToast('Customer link copied', 'success');
      });
  }

  function toggleVerifyPane(show) {
    const pane = document.getElementById('qrVerifyPane');
    if (show) {
      if (!UPI_REF) { showToast('Generate the QR first', 'warning'); return; }
      document.getElementById('qrVerifyAmt').textContent = document.getElementById('qrAmt').textContent;
      pane.style.display = '';
      setTimeout(() => document.getElementById('qrTxnRef').focus(), 120);
    } else {
      pane.style.display = 'none';
    }
  }

  function verifyUpiNow() {
    const ref = document.getElementById('qrTxnRef').value.trim(); // optional — server auto-records when blank
    const note = document.getElementById('qrVerifyNote').value.trim();
    const btn = document.getElementById('qrVerifyBtn');
    btn.disabled = true; btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Verifying…';
    fetch('<?= base_url('pos/upi/verify') ?>', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
        body: new URLSearchParams({
          [CSRF_NAME]: CSRF_TOKEN,
          attempt_ref: UPI_REF,
          txn_ref: ref,
          note: note
        })
      }).then(r => r.json()).then(d => {
        if (d.success) {
          stopUpiPoll();
          const box = document.getElementById('qrStatusBox');
          box.style.background = '#F0FFF4';
          box.innerHTML = '✅ ' + d.message;
          showModalFooter(false);
          setTimeout(() => { closeModal('upiQrModal'); location.reload(); }, 1400);
        } else {
          showToast(d.message || 'Could not verify', 'error');
        }
      }).catch(e => showToast(e.message || 'Network error', 'error'))
      .finally(() => { btn.disabled = false; btn.innerHTML = 'Confirm receipt — ₹<span id="qrVerifyAmt">—</span>'; });
  }

  function rejectUpiClaim() {
    if (!UPI_REF) { showToast('Generate the QR first', 'warning'); return; }
    const reason = prompt('Reason for rejecting this payment claim?', 'No matching credit in our bank app');
    if (reason === null) return;
    if (!reason || reason.trim().length < 3) { showToast('A real reason is required', 'error'); return; }
    fetch('<?= base_url('pos/upi/reject') ?>', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
        body: new URLSearchParams({ [CSRF_NAME]: CSRF_TOKEN, attempt_ref: UPI_REF, reason: reason.trim() })
      }).then(r => r.json()).then(d => {
        if (d.success) {
          stopUpiPoll();
          const box = document.getElementById('qrStatusBox');
          box.style.background = '#FEF2F2';
          box.innerHTML = '🚫 Claim rejected — the customer screen will show "not verified". Close and generate a new QR to retry.';
          showModalFooter(false);
          // order page can stay; staff may generate a fresh QR immediately
          setTimeout(() => { closeModal('upiQrModal'); }, 2500);
        } else showToast(d.message || 'Could not reject', 'error');
      }).catch(e => showToast(e.message || 'Network error', 'error'));
  }
</script>

<?php $this->endSection(); ?>