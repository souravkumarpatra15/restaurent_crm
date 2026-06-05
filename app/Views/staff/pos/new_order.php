<?php $this->extend('layouts/pos_layout'); $this->section('content'); ?>

<div class="pos-wrap" id="posWrap">

  <!-- LEFT: Menu Panel -->
  <div class="pos-menu" id="posMenu">
    <!-- Search -->
    <div class="menu-search">
      <div class="menu-search-wrap">
        <i class="fa fa-search"></i>
        <input type="text" id="menuSearch" placeholder="Search items..." autocomplete="off">
      </div>
    </div>

    <!-- Category Tabs -->
    <div class="menu-cats" id="menuCats">
      <button class="cat-tab active" data-cat="all">All</button>
      <?php foreach ($categories as $cat): ?>
        <?php if (!empty($cat['items'])): ?>
        <button class="cat-tab" data-cat="<?= $cat['id'] ?>">
          <?= esc($cat['name']) ?>
          <span style="font-size:.7rem;opacity:.6">(<?= count($cat['items']) ?>)</span>
        </button>
        <?php endif; ?>
      <?php endforeach; ?>
    </div>

    <!-- Order Type Banner -->
    <div style="padding:.5rem .75rem; display:flex; gap:.5rem; align-items:center; background:var(--primary-light); border-bottom:1px solid var(--border);">
      <?php
        $icons = ['dine_in'=>'fa-chair','takeaway'=>'fa-bag-shopping','delivery'=>'fa-motorcycle'];
        $colors = ['dine_in'=>'var(--primary)','takeaway'=>'var(--info)','delivery'=>'var(--success)'];
      ?>
      <i class="fa <?= $icons[$order_type] ?? 'fa-utensils' ?>" style="color:<?= $colors[$order_type] ?? 'var(--primary)' ?>"></i>
      <strong style="font-size:.82rem"><?= strtoupper(str_replace('_',' ',$order_type)) ?></strong>
      <?php if ($table): ?>
        <span style="font-size:.8rem;color:var(--text-muted)">— Table <?= esc($table['table_number']) ?></span>
      <?php endif; ?>
      <a href="<?= base_url('pos') ?>" style="margin-left:auto;font-size:.75rem;color:var(--text-muted)"><i class="fa fa-arrow-left"></i> Back</a>
    </div>

    <!-- Menu Items Grid -->
    <div class="menu-items-grid" id="menuItemsGrid">
      <?php foreach ($categories as $cat): ?>
        <?php foreach ($cat['items'] as $item): ?>
        <div class="menu-item-card <?= $item['is_active'] ? '' : 'unavailable' ?>"
             data-id="<?= $item['id'] ?>"
             data-name="<?= esc($item['name']) ?>"
             data-price="<?= $item['base_price'] ?>"
             data-tax="<?= $item['tax_percent'] ?>"
             data-type="<?= $item['item_type'] ?>"
             data-cat="<?= $cat['id'] ?>"
             data-variants='<?= json_encode($item['variants'] ?? []) ?>'
             data-addons='<?= json_encode($item['addon_groups'] ?? []) ?>'
             onclick="addToCart(this)">
          <div class="menu-item-img">
            <?php if ($item['image']): ?>
              <img src="<?= base_url('public/images/uploads/' . $item['image']) ?>" alt="<?= esc($item['name']) ?>" loading="lazy">
            <?php else: ?>
              <div class="no-img">🍽</div>
            <?php endif; ?>
            <div class="veg-dot <?= in_array($item['item_type'],['veg','vegan']) ? 'veg' : 'nonveg' ?>"></div>
            <?php if ($item['is_bestseller']): ?>
              <span style="position:absolute;top:5px;right:5px;background:var(--warning);color:#fff;font-size:.6rem;font-weight:700;padding:2px 5px;border-radius:4px;">BEST</span>
            <?php endif; ?>
          </div>
          <div class="menu-item-body">
            <div class="menu-item-name"><?= esc($item['name']) ?></div>
            <div class="menu-item-price"><?= $restaurant['currency_symbol'] ?><?= number_format($item['base_price'],2) ?></div>
          </div>
        </div>
        <?php endforeach; ?>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- RIGHT: Cart Panel -->
  <div class="pos-cart" id="posCart">
    <div class="cart-header">
      <span><i class="fa fa-cart-shopping" style="color:var(--primary)"></i> Order</span>
      <div style="display:flex;gap:.5rem;align-items:center">
        <span class="badge-pill badge-primary" id="cartCount">0 items</span>
        <button class="btn btn-sm btn-outline" onclick="clearCart()"><i class="fa fa-trash"></i></button>
      </div>
    </div>

    <!-- Customer Info -->
    <div class="cart-order-info" id="cartOrderInfo">
      <button class="btn btn-sm btn-outline" onclick="openCustomerModal()"><i class="fa fa-user-plus"></i> Customer</button>
      <?php if ($order_type === 'dine_in'): ?>
      <button class="btn btn-sm btn-outline" onclick="openGuestsModal()"><i class="fa fa-users"></i> Guests</button>
      <?php endif; ?>
      <button class="btn btn-sm btn-outline" onclick="openNotesModal()"><i class="fa fa-note-sticky"></i> Note</button>
    </div>

    <!-- Cart Items -->
    <div class="cart-items" id="cartItems">
      <div class="empty-state" id="cartEmpty" style="padding:2rem 1rem">
        <i class="fa fa-cart-shopping"></i>
        <p>No items added yet.<br>Tap a menu item to add.</p>
      </div>
    </div>

    <!-- Totals -->
    <div class="cart-totals" id="cartTotals" style="display:none">
      <div class="total-row"><span>Subtotal</span><span id="tSubtotal">₹0.00</span></div>
      <div class="total-row" id="rowDiscount" style="display:none;color:var(--success)"><span>Discount</span><span id="tDiscount">-₹0.00</span></div>
      <div class="total-row" id="rowTax"><span>Tax (GST)</span><span id="tTax">₹0.00</span></div>
      <div class="total-row" id="rowService" style="display:none"><span>Service Charge</span><span id="tService">₹0.00</span></div>
      <div class="total-row" id="rowRound" style="display:none"><span>Round Off</span><span id="tRoundOff">₹0.00</span></div>
      <div class="total-row grand"><span>TOTAL</span><strong id="tTotal" style="color:var(--primary);font-size:1.1rem">₹0.00</strong></div>
    </div>

    <!-- Discount Bar -->
    <div style="padding:.5rem 1rem;border-top:1px solid var(--border);display:flex;gap:.5rem;align-items:center">
      <select class="form-control" id="discountType" style="width:100px;padding:.4rem;font-size:.8rem">
        <option value="">Discount</option>
        <option value="percent">%</option>
        <option value="flat">₹ Flat</option>
      </select>
      <input type="number" class="form-control" id="discountValue" placeholder="0" style="flex:1;padding:.4rem;font-size:.85rem" min="0">
      <button class="btn btn-sm btn-outline" onclick="applyDiscount()"><i class="fa fa-check"></i></button>
      <button class="btn btn-sm btn-outline" onclick="applyCoupon()"><i class="fa fa-ticket"></i></button>
    </div>

    <!-- Cart Actions -->
    <div class="cart-actions">
      <button class="btn btn-outline btn-block" onclick="printKot()" id="btnKot" disabled>
        <i class="fa fa-fire-burner"></i> Send to Kitchen (KOT)
      </button>
      <button class="btn btn-primary btn-lg btn-block" onclick="openCheckout()" id="btnCheckout" disabled>
        <i class="fa fa-cash-register"></i> Checkout
      </button>
    </div>
  </div>
</div>

<!-- Floating Cart Toggle (Mobile) -->
<button class="pos-cart-mobile-toggle" id="cartToggle" onclick="toggleCart()">
  <i class="fa fa-cart-shopping"></i>
  <span id="cartFloatCount" style="position:absolute;top:-4px;right:-4px;background:#fff;color:var(--primary);border-radius:50%;width:20px;height:20px;font-size:.7rem;font-weight:800;display:flex;align-items:center;justify-content:center">0</span>
</button>

<!-- Variant / Addon Modal -->
<div class="modal-overlay" id="variantModal">
  <div class="modal">
    <div class="modal-header">
      <span class="modal-title" id="variantModalTitle">Select Options</span>
      <button class="modal-close" onclick="closeModal('variantModal')"><i class="fa fa-times"></i></button>
    </div>
    <div class="modal-body" id="variantModalBody"></div>
    <div class="modal-footer">
      <button class="btn btn-outline" onclick="closeModal('variantModal')">Cancel</button>
      <button class="btn btn-primary" onclick="confirmVariant()"><i class="fa fa-plus"></i> Add to Cart</button>
    </div>
  </div>
</div>

<!-- Checkout Modal -->
<div class="modal-overlay" id="checkoutModal">
  <div class="modal" style="max-width:420px">
    <div class="modal-header">
      <span class="modal-title">💳 Payment</span>
      <button class="modal-close" onclick="closeModal('checkoutModal')"><i class="fa fa-times"></i></button>
    </div>
    <div class="modal-body">
      <div style="text-align:center;margin-bottom:1.25rem">
        <div style="font-size:.85rem;color:var(--text-muted)">Total Amount</div>
        <div style="font-size:2rem;font-weight:800;color:var(--primary)" id="checkoutTotal">₹0.00</div>
      </div>

      <div class="form-group">
        <label class="form-label">Payment Method</label>
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:.5rem" id="paymentMethods">
          <?php foreach (['cash'=>'💵 Cash','card'=>'💳 Card','upi'=>'📱 UPI','wallet'=>'👛 Wallet','online'=>'🌐 Online','credit'=>'📋 Credit'] as $k => $v): ?>
          <button class="btn btn-outline payment-method-btn" data-method="<?= $k ?>" onclick="selectPayment(this)">
            <?= $v ?>
          </button>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="form-group" id="paymentAmountGroup">
        <label class="form-label">Amount Received</label>
        <input type="number" class="form-control" id="paymentAmount" placeholder="Enter amount" style="font-size:1.1rem;font-weight:700">
        <div class="form-hint" id="changeDisplay" style="display:none;font-size:.9rem;font-weight:600;color:var(--success)">
          <i class="fa fa-rotate-left"></i> Change: <span id="changeAmount">₹0</span>
        </div>
      </div>

      <div class="form-group" id="paymentRefGroup" style="display:none">
        <label class="form-label">Reference / Transaction ID</label>
        <input type="text" class="form-control" id="paymentRef" placeholder="UPI ID / Last 4 digits">
      </div>

      <!-- Quick cash buttons -->
      <div id="quickCash" style="display:flex;gap:.5rem;flex-wrap:wrap;margin-bottom:1rem">
        <button class="btn btn-sm btn-outline" onclick="setExactAmount()">Exact</button>
        <button class="btn btn-sm btn-outline" onclick="setAmount(500)">₹500</button>
        <button class="btn btn-sm btn-outline" onclick="setAmount(1000)">₹1000</button>
        <button class="btn btn-sm btn-outline" onclick="setAmount(2000)">₹2000</button>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-outline" onclick="closeModal('checkoutModal')">Cancel</button>
      <button class="btn btn-success btn-lg" onclick="processPayment()" id="btnPay">
        <i class="fa fa-check"></i> Confirm Payment
      </button>
    </div>
  </div>
</div>

<!-- Customer Modal -->
<div class="modal-overlay" id="customerModal">
  <div class="modal">
    <div class="modal-header">
      <span class="modal-title"><i class="fa fa-user"></i> Add Customer</span>
      <button class="modal-close" onclick="closeModal('customerModal')"><i class="fa fa-times"></i></button>
    </div>
    <div class="modal-body">
      <div class="form-group">
        <label class="form-label">Search by Phone</label>
        <div style="display:flex;gap:.5rem">
          <input type="tel" class="form-control" id="customerSearch" placeholder="Phone number">
          <button class="btn btn-primary" onclick="searchCustomer()"><i class="fa fa-search"></i></button>
        </div>
      </div>
      <div id="customerResult" style="display:none" class="card" style="padding:.75rem;margin-bottom:1rem"></div>
      <hr style="margin:1rem 0">
      <div class="form-row cols-2">
        <div class="form-group">
          <label class="form-label">Name</label>
          <input type="text" class="form-control" id="custName">
        </div>
        <div class="form-group">
          <label class="form-label">Phone</label>
          <input type="tel" class="form-control" id="custPhone">
        </div>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-outline" onclick="closeModal('customerModal')">Skip</button>
      <button class="btn btn-primary" onclick="setCustomer()">Add Customer</button>
    </div>
  </div>
</div>

<script>
// ============================================================
// POS JavaScript Engine
// ============================================================
const CURRENCY = '<?= $restaurant['currency_symbol'] ?>';
const TAX_TYPE = '<?= $restaurant['tax_type'] ?>';
const SERVICE_PCT = <?= (float)($restaurant['service_charge_percent'] ?? 0) ?>;
const ORDER_TYPE = '<?= $order_type ?>';
const TABLE_ID   = '<?= $table_id ?? '' ?>';
const CSRF_TOKEN = '<?= csrf_hash() ?>';

let cart = [];
let cartCustomer = { id: null, name: '', phone: '' };
let pendingItem = null;
let discountData = { type: '', value: 0, amount: 0 };
let selectedPaymentMethod = 'cash';
let orderId = null;

// ── Add to Cart ──────────────────────────────────────────────
function addToCart(el) {
  const item = {
    menu_item_id: el.dataset.id,
    name:  el.dataset.name,
    price: parseFloat(el.dataset.price),
    tax:   parseFloat(el.dataset.tax),
    type:  el.dataset.type,
    variants: JSON.parse(el.dataset.variants || '[]'),
    addons:   JSON.parse(el.dataset.addons || '[]'),
  };

  if (item.variants.length > 0 || item.addons.length > 0) {
    pendingItem = item;
    openVariantModal(item);
    return;
  }

  pushToCart({ ...item, variant_id: null, variant_name: null, selectedAddons: [], qty: 1 });
}

function pushToCart(item) {
  const key = `${item.menu_item_id}_${item.variant_id || ''}_${JSON.stringify(item.selectedAddons)}`;
  const existing = cart.find(c => c._key === key);
  if (existing) {
    existing.qty++;
  } else {
    cart.push({ ...item, _key: key, qty: 1, notes: '' });
  }
  renderCart();
  // Visual feedback
  document.querySelector(`[data-id="${item.menu_item_id}"]`)?.classList.add('added');
  setTimeout(() => document.querySelector(`[data-id="${item.menu_item_id}"]`)?.classList.remove('added'), 300);
}

// ── Variant Modal ──────────────────────────────────────────
function openVariantModal(item) {
  document.getElementById('variantModalTitle').textContent = item.name;
  let html = '';

  if (item.variants.length > 0) {
    html += `<div class="form-group">
      <label class="form-label">Size / Variant <span class="req">*</span></label>
      <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(100px,1fr));gap:.5rem">`;
    item.variants.forEach(v => {
      html += `<label style="border:1.5px solid var(--border);border-radius:8px;padding:.5rem;cursor:pointer;text-align:center;transition:all .2s" 
                class="variant-opt" data-vid="${v.id}" data-vprice="${v.price}" data-vname="${v.name}">
        <input type="radio" name="variant" value="${v.id}" style="display:none">
        <div style="font-weight:700;font-size:.82rem">${v.name}</div>
        <div style="font-size:.8rem;color:var(--primary);font-weight:800">${CURRENCY}${parseFloat(v.price).toFixed(2)}</div>
      </label>`;
    });
    html += `</div></div>`;
  }

  if (item.addons.length > 0) {
    item.addons.forEach(group => {
      html += `<div class="form-group">
        <label class="form-label">${group.name} ${group.is_required ? '<span class="req">*</span>' : ''}</label>
        <div style="display:grid;gap:.4rem">`;
      group.addons.forEach(addon => {
        const inputType = group.selection_type === 'single' ? 'radio' : 'checkbox';
        html += `<label style="display:flex;align-items:center;gap:.5rem;padding:.4rem;border:1px solid var(--border);border-radius:6px;cursor:pointer">
          <input type="${inputType}" name="addon_${group.id}" value="${addon.id}" data-aname="${addon.name}" data-aprice="${addon.price}">
          <span style="flex:1;font-size:.85rem">${addon.name}</span>
          ${addon.price > 0 ? `<span style="font-size:.82rem;color:var(--primary);font-weight:700">+${CURRENCY}${parseFloat(addon.price).toFixed(2)}</span>` : ''}
        </label>`;
      });
      html += `</div></div>`;
    });
  }

  html += `<div class="form-group">
    <label class="form-label">Special Instructions</label>
    <input type="text" class="form-control" id="itemNotes" placeholder="e.g. No onion, extra spicy">
  </div>`;

  document.getElementById('variantModalBody').innerHTML = html;
  
  // Handle variant selection styling
  document.querySelectorAll('.variant-opt').forEach(opt => {
    opt.addEventListener('click', () => {
      document.querySelectorAll('.variant-opt').forEach(o => o.style.borderColor = 'var(--border)');
      opt.style.borderColor = 'var(--primary)';
      opt.style.background = 'var(--primary-light)';
      opt.querySelector('input').checked = true;
    });
  });

  openModal('variantModal');
}

function confirmVariant() {
  if (!pendingItem) return;
  const item = pendingItem;

  let variantId = null, variantName = null, price = item.price;
  if (item.variants.length > 0) {
    const sel = document.querySelector('input[name="variant"]:checked');
    if (!sel) { alert('Please select a variant'); return; }
    variantId   = sel.value;
    const opt   = sel.closest('.variant-opt');
    variantName = opt.dataset.vname;
    price       = parseFloat(opt.dataset.vprice);
  }

  const selectedAddons = [];
  document.querySelectorAll('[name^="addon_"]:checked').forEach(inp => {
    selectedAddons.push({ id: inp.value, name: inp.dataset.aname, price: parseFloat(inp.dataset.aprice) });
  });

  const notes = document.getElementById('itemNotes')?.value || '';
  pushToCart({ ...item, variant_id: variantId, variant_name: variantName, price, selectedAddons, qty: 1, notes });
  closeModal('variantModal');
  pendingItem = null;
}

// ── Render Cart ──────────────────────────────────────────────
function renderCart() {
  const list = document.getElementById('cartItems');
  const empty = document.getElementById('cartEmpty');
  const totals = document.getElementById('cartTotals');

  if (cart.length === 0) {
    empty.style.display = '';
    totals.style.display = 'none';
    document.getElementById('btnCheckout').disabled = true;
    document.getElementById('btnKot').disabled = true;
    document.getElementById('cartCount').textContent = '0 items';
    document.getElementById('cartFloatCount').textContent = '0';
    return;
  }

  empty.style.display = 'none';
  totals.style.display = '';
  document.getElementById('btnCheckout').disabled = false;
  document.getElementById('btnKot').disabled = false;

  let html = '';
  cart.forEach((item, idx) => {
    const addonTotal = item.selectedAddons.reduce((s,a) => s + a.price, 0);
    const unitPrice = item.price + addonTotal;
    const lineTotal = unitPrice * item.qty;
    const itemType = item.type;
    const dot = ['veg','vegan'].includes(itemType) ? 'veg' : 'nonveg';

    html += `<div class="cart-item">
      <div>
        <div class="cart-item-name">
          <span class="veg-dot ${dot}" style="display:inline-flex;width:10px;height:10px;border-width:1.5px;margin-right:4px;vertical-align:middle"></span>
          ${item.name}
        </div>
        ${item.variant_name ? `<div class="cart-item-variant">${item.variant_name}</div>` : ''}
        ${item.selectedAddons.map(a => `<div class="cart-item-variant">+ ${a.name}</div>`).join('')}
        ${item.notes ? `<div class="cart-item-variant" style="color:var(--warning)">* ${item.notes}</div>` : ''}
      </div>
      <div style="display:flex;flex-direction:column;align-items:flex-end;gap:.3rem">
        <div class="cart-item-price">${CURRENCY}${lineTotal.toFixed(2)}</div>
        <div class="cart-qty-ctrl">
          <button class="qty-btn" onclick="changeQty(${idx},-1)">−</button>
          <span class="qty-val">${item.qty}</span>
          <button class="qty-btn" onclick="changeQty(${idx},1)">+</button>
          <button class="cart-item-del" onclick="removeItem(${idx})"><i class="fa fa-trash"></i></button>
        </div>
      </div>
    </div>`;
  });

  list.innerHTML = html;

  const count = cart.reduce((s,i) => s + i.qty, 0);
  document.getElementById('cartCount').textContent = count + ' item' + (count !== 1 ? 's' : '');
  document.getElementById('cartFloatCount').textContent = count;
  calculateTotals();
}

function changeQty(idx, delta) {
  cart[idx].qty = Math.max(0, cart[idx].qty + delta);
  if (cart[idx].qty === 0) cart.splice(idx, 1);
  renderCart();
}
function removeItem(idx) { cart.splice(idx, 1); renderCart(); }
function clearCart() { if (confirm('Clear all items?')) { cart = []; discountData = {type:'',value:0,amount:0}; renderCart(); } }

// ── Calculate Totals ─────────────────────────────────────────
function calculateTotals() {
  let subtotal = 0, taxTotal = 0;

  cart.forEach(item => {
    const addonTotal = item.selectedAddons.reduce((s,a) => s + a.price, 0);
    const unitPrice  = item.price + addonTotal;
    const lineTotal  = unitPrice * item.qty;

    if (TAX_TYPE === 'exclusive') {
      taxTotal += lineTotal * (item.tax / 100);
    } else {
      taxTotal += lineTotal - (lineTotal / (1 + item.tax / 100));
    }
    subtotal += lineTotal;
  });

  const serviceCharge = SERVICE_PCT > 0 ? (subtotal - discountData.amount) * SERVICE_PCT / 100 : 0;
  const netBeforeRound = subtotal - discountData.amount + taxTotal + serviceCharge;
  const roundOff = Math.round(netBeforeRound) - netBeforeRound;
  const total = Math.round(netBeforeRound);

  const sym = CURRENCY;
  document.getElementById('tSubtotal').textContent = sym + subtotal.toFixed(2);
  document.getElementById('tTax').textContent      = sym + taxTotal.toFixed(2);

  const rowDiscount = document.getElementById('rowDiscount');
  if (discountData.amount > 0) {
    rowDiscount.style.display = '';
    document.getElementById('tDiscount').textContent = '-' + sym + discountData.amount.toFixed(2);
  } else { rowDiscount.style.display = 'none'; }

  const rowService = document.getElementById('rowService');
  if (serviceCharge > 0) {
    rowService.style.display = '';
    document.getElementById('tService').textContent = sym + serviceCharge.toFixed(2);
  } else { rowService.style.display = 'none'; }

  const rowRound = document.getElementById('rowRound');
  if (Math.abs(roundOff) > 0.001) {
    rowRound.style.display = '';
    document.getElementById('tRoundOff').textContent = (roundOff > 0 ? '+' : '') + sym + roundOff.toFixed(2);
  } else { rowRound.style.display = 'none'; }

  document.getElementById('tTotal').textContent = sym + total.toFixed(2);
  return total;
}

// ── Discount ─────────────────────────────────────────────────
function applyDiscount() {
  const type = document.getElementById('discountType').value;
  const val  = parseFloat(document.getElementById('discountValue').value) || 0;
  if (!type || val <= 0) { discountData = {type:'',value:0,amount:0}; renderCart(); return; }

  const subtotal = cart.reduce((s,i) => s + (i.price + i.selectedAddons.reduce((sa,a)=>sa+a.price,0)) * i.qty, 0);
  let amount = type === 'percent' ? subtotal * val / 100 : Math.min(val, subtotal);
  discountData = { type, value: val, amount: parseFloat(amount.toFixed(2)) };
  renderCart();
}

function applyCoupon() {
  const code = prompt('Enter coupon code:');
  if (!code) return;
  fetch('<?= base_url('pos/order/apply-coupon') ?>', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-CSRF-TOKEN': CSRF_TOKEN },
    body: `coupon_code=${code}&subtotal=${getSubtotal()}`
  }).then(r => r.json()).then(data => {
    if (data.success) {
      discountData = { type: 'coupon', value: data.discount, amount: data.discount };
      renderCart();
      showToast('Coupon applied: -' + CURRENCY + data.discount.toFixed(2), 'success');
    } else {
      showToast(data.message || 'Invalid coupon', 'error');
    }
  });
}

function getSubtotal() {
  return cart.reduce((s,i) => s + (i.price + i.selectedAddons.reduce((sa,a)=>sa+a.price,0)) * i.qty, 0);
}

// ── Checkout ─────────────────────────────────────────────────
function openCheckout() {
  const total = calculateTotals();
  document.getElementById('checkoutTotal').textContent = CURRENCY + total.toFixed(2);
  document.getElementById('paymentAmount').value = total.toFixed(2);
  openModal('checkoutModal');
}

function selectPayment(btn) {
  document.querySelectorAll('.payment-method-btn').forEach(b => {
    b.classList.remove('btn-primary');
    b.classList.add('btn-outline');
  });
  btn.classList.remove('btn-outline');
  btn.classList.add('btn-primary');
  selectedPaymentMethod = btn.dataset.method;

  const isCard = ['card','upi','online'].includes(selectedPaymentMethod);
  document.getElementById('paymentRefGroup').style.display = isCard ? '' : 'none';
  document.getElementById('quickCash').style.display = selectedPaymentMethod === 'cash' ? '' : 'none';
}

function setAmount(amt) { document.getElementById('paymentAmount').value = amt; updateChange(); }
function setExactAmount() {
  const total = calculateTotals();
  document.getElementById('paymentAmount').value = total.toFixed(2);
  updateChange();
}

function updateChange() {
  const paid  = parseFloat(document.getElementById('paymentAmount').value) || 0;
  const total = calculateTotals();
  const change = paid - total;
  const display = document.getElementById('changeDisplay');
  if (change > 0 && selectedPaymentMethod === 'cash') {
    display.style.display = '';
    document.getElementById('changeAmount').textContent = CURRENCY + change.toFixed(2);
  } else { display.style.display = 'none'; }
}
document.getElementById('paymentAmount')?.addEventListener('input', updateChange);

function processPayment() {
  const paid  = parseFloat(document.getElementById('paymentAmount').value) || 0;
  const total = calculateTotals();
  if (paid < total) { showToast('Amount is less than total!', 'error'); return; }

  const btn = document.getElementById('btnPay');
  btn.disabled = true;
  btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Processing...';

  // First create order, then checkout
  const payload = {
    order_type:      ORDER_TYPE,
    table_id:        TABLE_ID,
    customer_id:     cartCustomer.id || '',
    customer_name:   cartCustomer.name || '',
    customer_phone:  cartCustomer.phone || '',
    discount_type:   discountData.type || 'flat',
    discount_value:  discountData.amount || 0,
    items:           JSON.stringify(cart.map(i => ({
      menu_item_id: i.menu_item_id,
      variant_id:   i.variant_id,
      variant_name: i.variant_name,
      quantity:     i.qty,
      notes:        i.notes,
      addons:       i.selectedAddons
    }))),
    payments: JSON.stringify([{
      method:    selectedPaymentMethod,
      amount:    paid,
      reference: document.getElementById('paymentRef')?.value || ''
    }])
  };

  fetch('<?= base_url('pos/order/create') ?>', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-CSRF-TOKEN': CSRF_TOKEN },
    body: new URLSearchParams(payload)
  }).then(r => r.json()).then(data => {
    if (data.success) {
      orderId = data.order_id;
      // Now checkout
      return fetch('<?= base_url('pos/order/checkout') ?>', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-CSRF-TOKEN': CSRF_TOKEN },
        body: `order_id=${orderId}&payments=${JSON.stringify([{method:selectedPaymentMethod,amount:paid,reference:document.getElementById('paymentRef')?.value||''}])}`
      });
    } else { throw new Error(data.message || 'Order creation failed'); }
  }).then(r => r.json()).then(data => {
    if (data.success) {
      closeModal('checkoutModal');
      showBillSuccess(data);
      cart = [];
      discountData = {type:'',value:0,amount:0};
      renderCart();
    } else { throw new Error(data.message || 'Payment failed'); }
  }).catch(err => {
    showToast(err.message, 'error');
  }).finally(() => {
    btn.disabled = false;
    btn.innerHTML = '<i class="fa fa-check"></i> Confirm Payment';
  });
}

function showBillSuccess(data) {
  const msg = document.createElement('div');
  msg.style.cssText = 'position:fixed;inset:0;background:rgba(0,0,0,.7);z-index:9999;display:flex;align-items:center;justify-content:center;';
  msg.innerHTML = `<div style="background:#fff;border-radius:16px;padding:2rem;text-align:center;max-width:320px;width:90%">
    <div style="font-size:3rem;margin-bottom:1rem">✅</div>
    <div style="font-size:1.25rem;font-weight:800;margin-bottom:.5rem">Payment Successful!</div>
    <div style="font-size:.9rem;color:var(--text-muted);margin-bottom:1rem">Bill #${data.invoice_number}</div>
    ${data.change > 0 ? `<div style="background:#F0FFF4;padding:.75rem;border-radius:8px;margin-bottom:1rem;font-weight:700;color:var(--success)">Change: ${CURRENCY}${data.change.toFixed(2)}</div>` : ''}
    <button onclick="this.closest('div[style]').remove()" style="background:var(--primary);color:#fff;border:none;padding:.75rem 2rem;border-radius:8px;font-weight:700;cursor:pointer;width:100%">New Order</button>
  </div>`;
  document.body.appendChild(msg);
}

// ── KOT ──────────────────────────────────────────────────────
function printKot() {
  if (!orderId) {
    showToast('Please create the order first', 'warning');
    return;
  }
  fetch('<?= base_url('pos/order/print-kot') ?>', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-CSRF-TOKEN': CSRF_TOKEN },
    body: `order_id=${orderId}`
  }).then(r=>r.json()).then(data => {
    showToast(data.success ? 'KOT sent to kitchen!' : (data.error || 'Print failed'), data.success ? 'success' : 'error');
  });
}

// ── Customer ─────────────────────────────────────────────────
function openCustomerModal() { openModal('customerModal'); }
function searchCustomer() {
  const phone = document.getElementById('customerSearch').value;
  if (!phone) return;
  fetch('<?= base_url('admin/customers') ?>?phone=' + phone + '&ajax=1')
    .then(r=>r.json()).then(data => {
      const result = document.getElementById('customerResult');
      if (data.found) {
        result.style.display = '';
        result.innerHTML = `<div style="padding:.75rem"><strong>${data.name}</strong><br><small>${data.phone}</small>
          <button class="btn btn-sm btn-primary" style="float:right;margin-top:-.5rem" onclick="selectCustomer('${data.id}','${data.name}','${data.phone}')">Select</button></div>`;
        document.getElementById('custName').value  = data.name;
        document.getElementById('custPhone').value = data.phone;
      } else { result.style.display = 'none'; showToast('Customer not found', 'warning'); }
    });
}
function selectCustomer(id, name, phone) { cartCustomer = {id,name,phone}; closeModal('customerModal'); showToast('Customer: ' + name, 'success'); }
function setCustomer() {
  cartCustomer.name  = document.getElementById('custName').value;
  cartCustomer.phone = document.getElementById('custPhone').value;
  closeModal('customerModal');
}

// ── Menu Filter ──────────────────────────────────────────────
document.getElementById('menuSearch').addEventListener('input', function() {
  const q = this.value.toLowerCase();
  document.querySelectorAll('.menu-item-card').forEach(card => {
    const match = card.dataset.name.toLowerCase().includes(q);
    card.style.display = match ? '' : 'none';
  });
});

document.querySelectorAll('.cat-tab').forEach(btn => {
  btn.addEventListener('click', function() {
    document.querySelectorAll('.cat-tab').forEach(b => b.classList.remove('active'));
    this.classList.add('active');
    const cat = this.dataset.cat;
    document.querySelectorAll('.menu-item-card').forEach(card => {
      card.style.display = (cat === 'all' || card.dataset.cat === cat) ? '' : 'none';
    });
  });
});

// ── Mobile Cart Toggle ───────────────────────────────────────
function toggleCart() { document.getElementById('posCart').classList.toggle('show'); }

// ── Modal Helpers ────────────────────────────────────────────
function openModal(id)  { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }
document.querySelectorAll('.modal-overlay').forEach(overlay => {
  overlay.addEventListener('click', e => { if (e.target === overlay) overlay.classList.remove('open'); });
});

// ── Toast ────────────────────────────────────────────────────
function showToast(msg, type = 'info') {
  const colors = { success:'var(--success)', error:'var(--danger)', warning:'var(--warning)', info:'var(--info)' };
  const t = document.createElement('div');
  t.style.cssText = `position:fixed;bottom:6rem;left:50%;transform:translateX(-50%);background:${colors[type]};color:#fff;
    padding:.6rem 1.25rem;border-radius:20px;font-size:.85rem;font-weight:600;z-index:9999;
    box-shadow:0 4px 12px rgba(0,0,0,.2);white-space:nowrap;animation:slideUp .2s ease;`;
  t.textContent = msg;
  document.body.appendChild(t);
  setTimeout(() => t.remove(), 3000);
}
</script>

<?php $this->endSection(); ?>
