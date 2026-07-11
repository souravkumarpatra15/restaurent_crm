<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
<title><?= esc($table['restaurant_name']) ?> Menu</title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
:root {
  --p: <?= esc($table['theme_color'] ?? '#FF6B35') ?>;
  --p2: <?= esc($table['theme_color'] ?? '#FF6B35') ?>22;
  --font:'Plus Jakarta Sans',system-ui,sans-serif;
  --radius:14px; --border:#E8EDF2;
}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
html,body{height:100%;font-family:var(--font);background:#F8FAFC;color:#0F172A;-webkit-font-smoothing:antialiased;overscroll-behavior:none}

/* ── HERO ──────────────────────────────────────────── */
.hero{background:linear-gradient(135deg,#0F172A 0%,#1E293B 60%,#0F172A 100%);padding:1rem 1rem .75rem;position:sticky;top:0;z-index:100;box-shadow:0 2px 16px rgba(0,0,0,.35)}
.hero-top{display:flex;align-items:center;gap:.75rem}
.hero-logo{width:44px;height:44px;border-radius:12px;background:var(--p);display:flex;align-items:center;justify-content:center;font-size:1.3rem;flex-shrink:0}
.hero-info{flex:1;min-width:0}
.hero-name{color:#fff;font-weight:900;font-size:1.05rem;letter-spacing:-.01em;line-height:1.2}
.hero-sub{color:rgba(255,255,255,.45);font-size:.7rem;margin-top:.15rem}
.hero-table{background:var(--p);color:#fff;font-size:.65rem;font-weight:900;padding:.25rem .65rem;border-radius:20px;letter-spacing:.04em;white-space:nowrap;flex-shrink:0}

/* ── SEARCH ─────────────────────────────────────────── */
.srch-wrap{padding:.625rem 1rem .125rem;background:#F8FAFC;position:sticky;top:72px;z-index:90}
.srch-box{display:flex;align-items:center;gap:.5rem;background:#fff;border:1.5px solid var(--border);border-radius:24px;padding:.5rem .875rem}
.srch-box i{color:#94A3B8;font-size:.875rem;flex-shrink:0}
.srch-box input{flex:1;border:none;outline:none;font-family:var(--font);font-size:.875rem;background:transparent;color:#0F172A}
.srch-box input::placeholder{color:#CBD5E1}

/* ── CATEGORY PILLS ─────────────────────────────────── */
.cats{display:flex;gap:.4rem;padding:.625rem 1rem .25rem;overflow-x:auto;scrollbar-width:none;position:sticky;top:124px;z-index:80;background:#F8FAFC}
.cats::-webkit-scrollbar{display:none}
.cpill{flex-shrink:0;padding:.375rem .875rem;border-radius:20px;border:1.5px solid var(--border);background:#fff;font-size:.72rem;font-weight:700;color:#64748B;cursor:pointer;transition:all .15s;white-space:nowrap}
.cpill.on{background:var(--p);color:#fff;border-color:var(--p)}

/* ── MENU SECTIONS ──────────────────────────────────── */
.content{padding:0 1rem 8rem}
.sec-title{font-size:.875rem;font-weight:900;color:#1E293B;padding:.875rem 0 .5rem;display:flex;align-items:center;gap:.625rem}
.sec-title::after{content:'';flex:1;height:1px;background:#E8EDF2}
.items{display:flex;flex-direction:column;gap:.5rem}

/* ── ITEM CARD ──────────────────────────────────────── */
.icard{background:#fff;border-radius:var(--radius);border:1.5px solid var(--border);display:flex;gap:.75rem;padding:.75rem;align-items:flex-start;transition:border-color .15s;position:relative}
.icard:has(.qty-badge:not([style*="display:none"])){border-color:var(--p)}
.icard-img{width:76px;height:76px;border-radius:10px;flex-shrink:0;object-fit:cover;background:#F1F5F9;display:flex;align-items:center;justify-content:center;font-size:2rem;overflow:hidden}
.icard-img img{width:100%;height:100%;object-fit:cover;border-radius:10px}
.icard-body{flex:1;min-width:0}
.icard-top{display:flex;align-items:flex-start;gap:.4rem;margin-bottom:.2rem}
.dot{width:10px;height:10px;border-radius:50%;border:1.5px solid;flex-shrink:0;margin-top:3px}
.dot.veg{border-color:#15803D;background:#15803D}.dot.nveg{border-color:#B91C1C;background:#B91C1C}
.icard-name{font-weight:800;font-size:.875rem;line-height:1.3;color:#0F172A}
.icard-desc{font-size:.72rem;color:#94A3B8;margin:.15rem 0 .4rem;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
.icard-foot{display:flex;align-items:center;justify-content:space-between}
.icard-price{font-weight:900;font-size:.95rem;color:var(--p)}
.badges{display:flex;gap:.25rem}
.badge{font-size:.58rem;font-weight:800;padding:.12rem .4rem;border-radius:8px}
.badge.best{background:#FEF3C7;color:#92400E}
.badge.rec{background:#EFF6FF;color:#1D4ED8}
.badge.spicy{background:#FEF2F2;color:#B91C1C}

/* ── QTY CONTROLS ───────────────────────────────────── */
.qty-wrap{position:absolute;bottom:.75rem;right:.75rem;display:flex;align-items:center;gap:.375rem}
.qty-badge{position:absolute;top:.75rem;right:.75rem;background:var(--p);color:#fff;width:22px;height:22px;border-radius:50%;font-size:.65rem;font-weight:900;display:flex;align-items:center;justify-content:center}
.qty-btn{width:30px;height:30px;border-radius:50%;border:none;font-weight:900;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:.9rem;transition:all .15s}
.qty-btn.add{background:var(--p);color:#fff}
.qty-btn.add:hover{background:#E55A20}
.qty-btn.sub{background:#F1F5F9;color:#64748B}
.qty-num{font-weight:900;font-size:.9rem;min-width:18px;text-align:center;color:#0F172A}

/* ── CART FAB ───────────────────────────────────────── */
.cart-fab{position:fixed;bottom:1.25rem;left:50%;transform:translateX(-50%);background:var(--p);color:#fff;border:none;border-radius:24px;padding:.75rem 1.5rem;font-family:var(--font);font-weight:800;font-size:.9rem;cursor:pointer;box-shadow:0 4px 24px rgba(255,107,53,.4);z-index:200;display:flex;align-items:center;gap:.75rem;min-width:220px;justify-content:space-between;transition:all .2s;white-space:nowrap}
.cart-fab.hidden{transform:translateX(-50%) translateY(120px);opacity:0;pointer-events:none}
.fab-count{background:rgba(255,255,255,.25);padding:.2rem .55rem;border-radius:12px;font-size:.72rem}
.fab-total{font-size:.78rem;opacity:.9}

/* ── CART SHEET ─────────────────────────────────────── */
.sheet-bg{position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:300;opacity:0;pointer-events:none;transition:opacity .25s}
.sheet-bg.on{opacity:1;pointer-events:all}
.sheet{position:fixed;bottom:0;left:0;right:0;background:#fff;border-radius:22px 22px 0 0;z-index:301;transform:translateY(100%);transition:transform .3s cubic-bezier(.4,0,.2,1);max-height:92vh;display:flex;flex-direction:column}
.sheet.on{transform:translateY(0)}
.sheet-pip{width:40px;height:4px;background:#E2E8F0;border-radius:4px;margin:.75rem auto .5rem}
.sheet-hdr{padding:.25rem 1rem .75rem;display:flex;align-items:center;justify-content:space-between}
.sheet-title{font-weight:900;font-size:1rem;color:#0F172A}
.sheet-x{background:none;border:none;color:#94A3B8;font-size:1.1rem;cursor:pointer;padding:.25rem}
.sheet-body{flex:1;overflow-y:auto;padding:0 1rem}
.sheet-foot{padding:.875rem 1rem calc(.875rem + env(safe-area-inset-bottom,0px));border-top:1px solid var(--border);display:flex;gap:.625rem}

/* ── CART ITEMS ─────────────────────────────────────── */
.ci{display:flex;align-items:center;gap:.75rem;padding:.75rem 0;border-bottom:1px solid #F1F5F9}
.ci:last-child{border-bottom:none}
.ci-name{flex:1;font-weight:700;font-size:.875rem}
.ci-note{font-size:.68rem;color:#94A3B8;margin-top:.15rem}
.ci-qty{display:flex;align-items:center;gap:.375rem}
.ci-price{font-weight:900;font-size:.9rem;color:var(--p);min-width:60px;text-align:right}

/* ── PLACE ORDER FORM ───────────────────────────────── */
.form-field{margin-bottom:.75rem}
.form-label{font-size:.72rem;font-weight:800;color:#64748B;display:block;margin-bottom:.3rem;text-transform:uppercase;letter-spacing:.04em}
.form-input{width:100%;padding:.625rem .875rem;border:1.5px solid var(--border);border-radius:10px;font-family:var(--font);font-size:.875rem;outline:none;transition:border-color .15s;background:#fff}
.form-input:focus{border-color:var(--p)}
.order-summary{background:#F8FAFC;border-radius:12px;padding:.875rem;margin-bottom:1rem}
.ots-row{display:flex;justify-content:space-between;align-items:center;font-size:.8rem;margin-bottom:.3rem;color:#64748B}
.ots-row.total{font-weight:900;font-size:.95rem;color:#0F172A;margin-bottom:0;margin-top:.5rem;padding-top:.5rem;border-top:1px solid var(--border)}

.btn{display:flex;align-items:center;justify-content:center;gap:.5rem;padding:.8rem 1.5rem;border-radius:12px;font-family:var(--font);font-weight:800;font-size:.9rem;cursor:pointer;border:none;transition:all .15s}
.btn-primary{background:var(--p);color:#fff;flex:1}
.btn-primary:disabled{opacity:.6;cursor:not-allowed}
.btn-outline{background:#fff;color:#64748B;border:1.5px solid var(--border);padding:.8rem 1rem}

/* ── EMPTY ──────────────────────────────────────────── */
.empty{text-align:center;padding:3rem 1.5rem;color:#94A3B8}
.empty i{font-size:2.5rem;margin-bottom:.75rem;display:block}

/* ── LOADER ─────────────────────────────────────────── */
.loader{position:fixed;inset:0;background:rgba(255,255,255,.85);z-index:500;display:none;align-items:center;justify-content:center;flex-direction:column;gap:1rem}
.loader.on{display:flex}
.spinner{width:44px;height:44px;border:4px solid #E8EDF2;border-top-color:var(--p);border-radius:50%;animation:spin .8s linear infinite}
@keyframes spin{to{transform:rotate(360deg)}}
</style>
</head>
<body>

<!-- Hero -->
<div class="hero">
  <div class="hero-top">
    <div class="hero-logo">🍽</div>
    <div class="hero-info">
      <div class="hero-name"><?= esc($table['restaurant_name']) ?></div>
      <div class="hero-sub"><?= esc($table['branch_name']) ?></div>
    </div>
    <div class="hero-table"><i class="fa fa-chair"></i> Table <?= esc($table['table_number']) ?></div>
  </div>
</div>

<!-- Search -->
<div class="srch-wrap">
  <div class="srch-box">
    <i class="fa fa-search"></i>
    <input type="text" id="srch" placeholder="Search food & drinks…" oninput="search(this.value)">
    <i class="fa fa-xmark" id="srchX" style="cursor:pointer;display:none" onclick="clearSearch()"></i>
  </div>
</div>

<!-- Category pills -->
<div class="cats" id="catPills">
  <button class="cpill on" onclick="filterCat('all',this)">All Items</button>
  <?php foreach ($categories as $cat): ?>
  <button class="cpill" onclick="filterCat('<?= $cat['id'] ?>',this)"><?= esc($cat['name']) ?></button>
  <?php endforeach; ?>
</div>

<!-- Menu -->
<div class="content" id="menuContent">
  <?php if (empty($categories)): ?>
  <div class="empty"><i class="fa fa-utensils"></i><p>Menu coming soon!</p></div>
  <?php else: foreach ($categories as $cat): if (empty($cat['items'])) continue; ?>
  <div class="sec" data-cat="<?= $cat['id'] ?>">
    <div class="sec-title"><?= esc($cat['name']) ?></div>
    <div class="items">
      <?php foreach ($cat['items'] as $item): ?>
      <div class="icard" id="ic<?= $item['id'] ?>" data-id="<?= $item['id'] ?>" data-name="<?= esc(strtolower($item['name'])) ?>" data-desc="<?= esc(strtolower($item['description'] ?? '')) ?>" data-price="<?= $item['base_price'] ?>">
        <div class="icard-img">
          <?php if (!empty($item['image'])): ?>
          <img src="<?= base_url('images/uploads/'.$item['image']) ?>" loading="lazy" alt="">
          <?php else: ?>
          <?= $item['food_type']==='beverage'?'☕':($item['food_type']==='dessert'?'🍰':'🍽') ?>
          <?php endif; ?>
        </div>
        <div class="icard-body">
          <div class="icard-top">
            <span class="dot <?= in_array($item['item_type'],['veg','vegan'])?'veg':'nveg' ?>"></span>
            <span class="icard-name"><?= esc($item['name']) ?></span>
          </div>
          <?php if (!empty($item['description'])): ?><div class="icard-desc"><?= esc($item['description']) ?></div><?php endif; ?>
          <div class="icard-foot">
            <span class="icard-price"><?= $table['currency_symbol']??'₹' ?><?= number_format($item['base_price'],2) ?></span>
            <div class="badges">
              <?php if ($item['is_bestseller']): ?><span class="badge best">🔥 Best</span><?php endif; ?>
              <?php if ($item['is_recommended']&&!$item['is_bestseller']): ?><span class="badge rec">⭐ Chef's Pick</span><?php endif; ?>
              <?php if ($item['is_spicy']): ?><span class="badge spicy">🌶</span><?php endif; ?>
            </div>
          </div>
        </div>
        <!-- Qty badge (shows when >0 in cart) -->
        <div class="qty-badge" id="qb<?= $item['id'] ?>" style="display:none">0</div>
        <!-- Add button (shows when qty=0) -->
        <div class="qty-wrap" id="qw<?= $item['id'] ?>">
          <button class="qty-btn add" onclick="addItem(<?= $item['id'] ?>,<?= $item['base_price'] ?>,'<?= esc(addslashes($item['name'])) ?>')">
            <i class="fa fa-plus"></i>
          </button>
        </div>
        <!-- Qty controls (shows when qty>0) -->
        <div class="qty-wrap" id="qc<?= $item['id'] ?>" style="display:none">
          <button class="qty-btn sub" onclick="changeQty(<?= $item['id'] ?>,-1)">－</button>
          <span class="qty-num" id="qn<?= $item['id'] ?>">0</span>
          <button class="qty-btn add" onclick="changeQty(<?= $item['id'] ?>,1)">＋</button>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endforeach; endif; ?>
</div>

<!-- Cart FAB -->
<button class="cart-fab hidden" id="cartFab" onclick="openCart()">
  <span class="fab-count" id="fabCount">0 items</span>
  <span><i class="fa fa-shopping-cart"></i> View Order</span>
  <span class="fab-total" id="fabTotal">₹0</span>
</button>

<!-- ── Cart Sheet ──────────────────────────────────── -->
<div class="sheet-bg" id="bgCart" onclick="closeCart()"></div>
<div class="sheet" id="sCart" style="max-height:85vh">
  <div class="sheet-pip"></div>
  <div class="sheet-hdr">
    <span class="sheet-title">🛒 Your Order</span>
    <button class="sheet-x" onclick="closeCart()"><i class="fa fa-times"></i></button>
  </div>
  <div class="sheet-body" id="cartBody"></div>
  <div class="sheet-foot">
    <button class="btn btn-outline" onclick="closeCart()"><i class="fa fa-arrow-left"></i></button>
    <button class="btn btn-primary" id="checkoutBtn" onclick="openCheckout()">
      <i class="fa fa-arrow-right"></i> Confirm Order
    </button>
  </div>
</div>

<!-- ── Checkout / Place Order Sheet ──────────────── -->
<div class="sheet-bg" id="bgCo" onclick="closeCheckout()"></div>
<div class="sheet" id="sCo">
  <div class="sheet-pip"></div>
  <div class="sheet-hdr">
    <span class="sheet-title">📋 Place Order</span>
    <button class="sheet-x" onclick="closeCheckout()"><i class="fa fa-times"></i></button>
  </div>
  <div class="sheet-body">
    <!-- Order summary -->
    <div class="order-summary" id="coSummary"></div>
    <!-- Customer details (optional) -->
    <div class="form-field">
      <label class="form-label">Your Name (optional)</label>
      <input type="text" class="form-input" id="coName" placeholder="e.g. Rahul">
    </div>
    <div class="form-field">
      <label class="form-label">Phone (optional)</label>
      <input type="tel" class="form-input" id="coPhone" placeholder="+91 XXXXX XXXXX" inputmode="tel">
    </div>
    <div class="form-field">
      <label class="form-label">Special Instructions</label>
      <input type="text" class="form-input" id="coNote" placeholder="Allergies, less spice, extra napkins…">
    </div>
    <div style="background:#F0FDF4;border-radius:10px;padding:.75rem;font-size:.78rem;color:#15803D;display:flex;gap:.5rem;align-items:flex-start">
      <i class="fa fa-circle-info" style="margin-top:.1rem"></i>
      <span>Your order will be confirmed by our staff. You can track the status after placing the order.</span>
    </div>
  </div>
  <div class="sheet-foot">
    <button class="btn btn-outline" onclick="closeCheckout()"><i class="fa fa-arrow-left"></i></button>
    <button class="btn btn-primary" id="placeBtn" onclick="placeOrder()">
      <i class="fa fa-paper-plane"></i> Place Order
    </button>
  </div>
</div>

<!-- Loader -->
<div class="loader" id="loader">
  <div class="spinner"></div>
  <div style="font-weight:700;color:#64748B">Placing your order…</div>
</div>

<script>
const SYM   = '<?= $table['currency_symbol'] ?? '₹' ?>';
const TOKEN = '<?= $table['qr_token'] ?>';
const BASE  = '<?= base_url() ?>';

// Cart state: {id: {id, name, price, qty}}
const cart = {};

function getCartArr() { return Object.values(cart).filter(i => i.qty > 0); }
function cartTotal()  { return getCartArr().reduce((s,i) => s + i.price * i.qty, 0); }
function cartCount()  { return getCartArr().reduce((s,i) => s + i.qty, 0); }

// ── Add / Change Qty ─────────────────────────────────
function addItem(id, price, name) {
  if (!cart[id]) cart[id] = {id, name, price, qty: 0};
  cart[id].qty = 1;
  syncItem(id);
}

function changeQty(id, delta) {
  if (!cart[id]) return;
  cart[id].qty = Math.max(0, cart[id].qty + delta);
  syncItem(id);
}

function syncItem(id) {
  const qty = cart[id]?.qty || 0;
  const qb = document.getElementById('qb'+id);
  const qw = document.getElementById('qw'+id);
  const qc = document.getElementById('qc'+id);
  const qn = document.getElementById('qn'+id);
  if (qb) { qb.textContent = qty; qb.style.display = qty > 0 ? 'flex' : 'none'; }
  if (qw) qw.style.display = qty > 0 ? 'none' : 'flex';
  if (qc) qc.style.display = qty > 0 ? 'flex' : 'none';
  if (qn) qn.textContent = qty;
  updateFab();
}

function updateFab() {
  const cnt   = cartCount();
  const total = cartTotal();
  const fab   = document.getElementById('cartFab');
  document.getElementById('fabCount').textContent = cnt + (cnt===1?' item':' items');
  document.getElementById('fabTotal').textContent = SYM + total.toFixed(0);
  fab.classList.toggle('hidden', cnt === 0);
}

// ── Cart Sheet ────────────────────────────────────────
function openCart() {
  renderCartBody();
  document.getElementById('bgCart').classList.add('on');
  document.getElementById('sCart').classList.add('on');
  document.body.style.overflow = 'hidden';
}
function closeCart() {
  document.getElementById('bgCart').classList.remove('on');
  document.getElementById('sCart').classList.remove('on');
  document.body.style.overflow = '';
}

function renderCartBody() {
  const items = getCartArr();
  if (!items.length) {
    document.getElementById('cartBody').innerHTML = '<div class="empty"><i class="fa fa-cart-shopping"></i><p>Your cart is empty</p></div>';
    return;
  }
  let html = items.map(i => `
    <div class="ci">
      <div style="flex:1">
        <div class="ci-name">${i.name}</div>
        <div style="font-size:.72rem;color:#94A3B8;margin-top:.1rem">${SYM}${i.price.toFixed(2)} × ${i.qty}</div>
      </div>
      <div class="ci-qty">
        <button class="qty-btn sub" onclick="changeQty(${i.id},-1);renderCartBody();updateFab()">－</button>
        <span class="qty-num">${i.qty}</span>
        <button class="qty-btn add" onclick="changeQty(${i.id},1);renderCartBody();updateFab()">＋</button>
      </div>
      <div class="ci-price">${SYM}${(i.price*i.qty).toFixed(0)}</div>
    </div>`).join('');
  html += `<div style="display:flex;justify-content:space-between;font-weight:900;font-size:1rem;padding:.875rem 0;border-top:2px solid #F1F5F9;margin-top:.25rem">
    <span>Total</span><span style="color:var(--p)">${SYM}${cartTotal().toFixed(2)}</span>
  </div>`;
  document.getElementById('cartBody').innerHTML = html;
}

// ── Checkout Sheet ────────────────────────────────────
function openCheckout() {
  const items = getCartArr();
  let html = items.map(i => `
    <div class="ots-row"><span>${i.name} × ${i.qty}</span><span>${SYM}${(i.price*i.qty).toFixed(2)}</span></div>`).join('');
  html += `<div class="ots-row total"><span>Total</span><span>${SYM}${cartTotal().toFixed(2)}</span></div>`;
  document.getElementById('coSummary').innerHTML = html;
  document.getElementById('bgCo').classList.add('on');
  document.getElementById('sCo').classList.add('on');
}
function closeCheckout() {
  document.getElementById('bgCo').classList.remove('on');
  document.getElementById('sCo').classList.remove('on');
}

// ── Place Order ───────────────────────────────────────
async function placeOrder() {
  const items = getCartArr();
  if (!items.length) return;

  const btn = document.getElementById('placeBtn');
  btn.disabled = true;
  document.getElementById('loader').classList.add('on');

  const payload = new URLSearchParams({
    customer_name:  document.getElementById('coName').value.trim(),
    customer_phone: document.getElementById('coPhone').value.trim(),
    notes:          document.getElementById('coNote').value.trim(),
    items:          JSON.stringify(items.map(i => ({id:i.id, qty:i.qty}))),
  });

  try {
    const res  = await fetch(BASE+'menu/table/'+TOKEN+'/order', {method:'POST',body:payload,headers:{'Content-Type':'application/x-www-form-urlencoded'}});
    const data = await res.json();
    if (data.success) {
      window.location.href = data.redirect;
    } else {
      alert(data.message || 'Failed to place order. Please try again.');
      btn.disabled = false;
      document.getElementById('loader').classList.remove('on');
    }
  } catch(e) {
    alert('Network error. Please check your connection.');
    btn.disabled = false;
    document.getElementById('loader').classList.remove('on');
  }
}

// ── Search ────────────────────────────────────────────
function search(q) {
  q = q.toLowerCase().trim();
  document.getElementById('srchX').style.display = q ? 'block' : 'none';
  document.querySelectorAll('.icard').forEach(el => {
    const match = !q || el.dataset.name.includes(q) || (el.dataset.desc||'').includes(q);
    el.style.display = match ? '' : 'none';
  });
  document.querySelectorAll('.sec').forEach(sec => {
    const visible = [...sec.querySelectorAll('.icard')].some(el => el.style.display !== 'none');
    sec.style.display = visible ? '' : 'none';
  });
  // Reset pills to all
  document.querySelectorAll('.cpill').forEach((p,i) => p.classList.toggle('on', i===0));
}

function clearSearch() {
  document.getElementById('srch').value = '';
  search('');
}

// ── Category filter ───────────────────────────────────
function filterCat(cat, btn) {
  clearSearch();
  document.querySelectorAll('.cpill').forEach(b => b.classList.remove('on'));
  btn.classList.add('on');
  document.querySelectorAll('.sec').forEach(sec => {
    sec.style.display = (cat==='all' || sec.dataset.cat===cat) ? '' : 'none';
  });
  document.querySelectorAll('.icard').forEach(el => el.style.display = '');
  if (cat !== 'all') {
    // Scroll to section
    const sec = document.querySelector(`.sec[data-cat="${cat}"]`);
    if (sec) sec.scrollIntoView({behavior:'smooth', block:'start'});
  }
}
</script>
</body>
</html>
