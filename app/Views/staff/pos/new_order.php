<?php $this->extend('layouts/pos_layout'); $this->section('content'); ?>
<?php
$sym       = $restaurant['currency_symbol'] ?? '₹';
$addToId   = $add_to_order_id ?? 0;
$addToOrder= $existing_order ?? null;
$taxType = $restaurant['tax_type'] ?? 'exclusive';
$svcPct  = (float)($restaurant['service_charge_percent'] ?? 0);
$typeIcons = ['dine_in'=>'fa-chair','takeaway'=>'fa-bag-shopping','delivery'=>'fa-motorcycle'];
?>

<!-- ═══ TOP BAR ═══ -->
<div class="posbar">
  <a href="<?= base_url('pos') ?>" class="posbar-back"><i class="fa fa-arrow-left"></i></a>
  <div class="posbar-info">
    <div class="posbar-title">
      <i class="fa <?= $typeIcons[$order_type] ?? 'fa-utensils' ?>"></i>
      <?= strtoupper(str_replace('_',' ',$order_type)) ?>
      <?php if ($table): ?><span style="opacity:.5;font-weight:500"> · T<?= esc($table['table_number']) ?></span><?php endif; ?>
    </div>
    <div class="posbar-branch"><?= esc($branch['name'] ?? '') ?></div>
  </div>
  <div class="posbar-actions">
    <div class="posbar-clock" id="pbclock"></div>
    <a href="<?= base_url('pos/kitchen') ?>" class="posbar-btn" title="Kitchen"><i class="fa fa-fire-burner"></i></a>
    <a href="<?= base_url('admin/dashboard') ?>" class="posbar-btn" title="Dashboard"><i class="fa fa-gauge-high"></i></a>
  </div>
</div>

<?php if ($addToId): ?>
<!-- ADD ROUND BANNER -->
<div style="background:linear-gradient(90deg,#6D28D9,#7C3AED);color:#fff;padding:.6rem 1rem;display:flex;align-items:center;gap:.625rem;font-size:.82rem;font-weight:700;flex-shrink:0">
  <i class="fa fa-circle-plus" style="font-size:1rem"></i>
  <span>Adding Round to Order <strong>#<?= esc($addToOrder['order_number'] ?? $addToId) ?></strong></span>
  <span style="margin-left:auto;opacity:.7;font-weight:500">Items will be added to existing order</span>
</div>
<?php endif; ?>

<!-- ═══ POS ROOT ═══ -->
<div class="pos-root">

  <!-- ─── LEFT: Menu ─── -->
  <div class="pos-menu">
    <!-- Search -->
    <div class="menu-search-bar">
      <div class="menu-search-box">
        <i class="fa fa-search"></i>
        <input class="menu-search-input" id="msearch" type="text" placeholder="Search items..." autocomplete="off" autocorrect="off">
      </div>
      <button class="menu-search-clear" id="msearchClear" onclick="clearSearch()"><i class="fa fa-times"></i></button>
      <div class="order-type-chip">
        <i class="fa <?= $typeIcons[$order_type] ?? 'fa-utensils' ?>"></i>
        <?= ucfirst(str_replace('_',' ',$order_type)) ?>
      </div>
    </div>
    <!-- Categories -->
    <div class="cat-scroll" id="catScroll">
      <button class="cat-pill on" data-cat="all">All <span class="cat-pill-n">(<?= array_sum(array_map(fn($c)=>count($c['items']),$categories)) ?>)</span></button>
      <?php foreach ($categories as $cat): if(empty($cat['items']))continue; ?>
      <button class="cat-pill" data-cat="<?= $cat['id'] ?>"><?= esc($cat['name']) ?> <span class="cat-pill-n">(<?= count($cat['items']) ?>)</span></button>
      <?php endforeach; ?>
    </div>
    <!-- Grid -->
    <div class="menu-grid" id="mgrid">
      <?php foreach ($categories as $cat): foreach ($cat['items'] as $item): ?>
      <div class="mcard <?= $item['is_active']?'':'off' ?>"
           data-id="<?= $item['id'] ?>"
           data-nm="<?= esc(strtolower($item['name'])) ?>"
           data-cat="<?= $cat['id'] ?>"
           data-type="<?= $item['item_type'] ?>"
           data-price="<?= $item['base_price'] ?>"
           data-tax="<?= $item['tax_percent'] ?>"
           data-vars='<?= htmlspecialchars(json_encode($item['variants']??[]),ENT_QUOTES) ?>'
           data-adds='<?= htmlspecialchars(json_encode($item['addon_groups']??[]),ENT_QUOTES) ?>'
           onclick="handleItem(this)">
        <div class="mcard-img">
          <?php if(!empty($item['image'])): ?>
          <img src="<?= base_url('images/uploads/'.$item['image']) ?>" loading="lazy" alt="<?= esc($item['name']) ?>">
          <?php else: ?><div class="mcard-emoji">🍽</div><?php endif; ?>
          <div class="food-dot <?= in_array($item['item_type'],['veg','vegan'])?'veg':'nonveg' ?>"></div>
          <?php if($item['is_bestseller']): ?><span class="mcard-tag best">🔥 BEST</span><?php endif; ?>
          <?php if($item['is_recommended']&&!$item['is_bestseller']): ?><span class="mcard-tag rec">⭐</span><?php endif; ?>
        </div>
        <div class="mcard-body">
          <div class="mcard-name"><?= esc($item['name']) ?></div>
          <div class="mcard-price"><?= $sym ?><?= number_format($item['base_price'],2) ?></div>
        </div>
      </div>
      <?php endforeach; endforeach; ?>
      <?php if(empty($categories)): ?>
      <div style="grid-column:1/-1;text-align:center;padding:3rem;color:var(--text-m)">
        <div style="font-size:3rem;margin-bottom:1rem">🍽</div>
        <p>No menu items yet.<br><a href="<?= base_url('admin/menu/items/create') ?>" style="color:var(--primary)">Add items</a></p>
      </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- ─── RIGHT: Cart (Desktop) ─── -->
  <div class="pos-cart">
    <div class="cart-hdr">
      <div class="cart-hdr-title">
        <i class="fa fa-cart-shopping" style="color:var(--primary)"></i>
        Order
        <span class="cart-badge" id="cartBadge">0</span>
      </div>
      <button class="cart-hdr-clear" onclick="clearCart()" title="Clear cart"><i class="fa fa-trash"></i></button>
    </div>
    <div class="cart-chips">
      <button class="chip" id="chipCustomer" onclick="openSheet('sCustomer')"><i class="fa fa-user-plus"></i> Customer</button>
      <?php if($order_type==='dine_in'): ?>
      <button class="chip" id="chipGuests" onclick="openSheet('sGuests')"><i class="fa fa-users"></i> <span id="guestsChipTxt">1 Guest</span></button>
      <?php endif; ?>
      <button class="chip" id="chipNote" onclick="openSheet('sNote')"><i class="fa fa-note-sticky"></i> Note</button>
    </div>
    <div id="cartBody">
      <div class="cart-empty" id="cartEmpty">
        <div class="cart-empty-icon"><i class="fa fa-cart-shopping"></i></div>
        <div class="cart-empty-hint">Tap any item<br>to add to order</div>
      </div>
    </div>
    <div class="disc-row">
      <select id="discType" onchange="calcDiscount()">
        <option value="">Discount</option>
        <option value="percent">% Off</option>
        <option value="flat">₹ Flat</option>
      </select>
      <input type="number" id="discVal" placeholder="0" min="0" oninput="calcDiscount()">
      <button class="disc-coupon-btn" onclick="openSheet('sCoupon')" title="Coupon"><i class="fa fa-ticket"></i></button>
    </div>
    <div class="cart-totals" id="cartTotals">
      <div class="trow"><span>Subtotal</span><span id="tSub"><?= $sym ?>0.00</span></div>
      <div class="trow disc" id="tDiscRow" style="display:none"><span>Discount</span><span id="tDisc">-<?= $sym ?>0.00</span></div>
      <div class="trow"><span>GST</span><span id="tTax"><?= $sym ?>0.00</span></div>
      <div class="trow" id="tSvcRow" style="display:none"><span>Service</span><span id="tSvc"><?= $sym ?>0.00</span></div>
      <div class="trow" id="tRndRow" style="display:none"><span>Round Off</span><span id="tRnd">0</span></div>
      <div class="trow grand"><span>TOTAL</span><span id="tTotal"><?= $sym ?>0.00</span></div>
    </div>
    <div class="cart-foot">
      <button class="btn-kot" id="btnKot" disabled onclick="doKot()"><i class="fa fa-fire-burner"></i> Send to Kitchen (KOT)</button>
      <button class="btn-pay" id="btnPay" disabled onclick="openSheet('sCheckout')">
        <i class="fa fa-cash-register"></i> Checkout &nbsp;<span id="payAmtChip"><?= $sym ?>0</span>
      </button>
    </div>
  </div>
</div>

<!-- ═══ MOBILE FAB ═══ -->
<button class="cart-fab" id="cartFab" style="display:none" onclick="openSheet('sCart')">
  <i class="fa fa-cart-shopping"></i>
  <span class="fab-badge" id="fabBadge">0</span>
</button>

<!-- ═══════════════════════════════════════
     BOTTOM SHEETS
═══════════════════════════════════════ -->

<!-- Variant / Addon Sheet -->
<div class="sheet-bg" id="bgVariant" onclick="closeBg('bgVariant','sVariant')"></div>
<div class="sheet" id="sVariant">
  <div class="sheet-pip"></div>
  <div class="sheet-hdr">
    <span class="sheet-title" id="sVariantTitle">Choose Options</span>
    <button class="sheet-x" onclick="closeSheet('sVariant','bgVariant')"><i class="fa fa-times"></i></button>
  </div>
  <div class="sheet-body" id="sVariantBody"></div>
  <div class="sheet-foot">
    <button onclick="closeSheet('sVariant','bgVariant')" style="flex:1;padding:.7rem;border:1.5px solid var(--border);border-radius:var(--radius-sm);background:#fff;font-family:var(--font);font-weight:700;cursor:pointer;font-size:.875rem">Cancel</button>
    <button onclick="confirmVariant()" style="flex:2;padding:.7rem;border:none;border-radius:var(--radius-sm);background:var(--primary);color:#fff;font-family:var(--font);font-weight:800;cursor:pointer;font-size:.95rem"><i class="fa fa-plus"></i> Add to Order</button>
  </div>
</div>

<!-- Checkout Sheet -->
<div class="sheet-bg" id="bgCheckout" onclick="closeBg('bgCheckout','sCheckout')"></div>
<div class="sheet" id="sCheckout">
  <div class="sheet-pip"></div>
  <div class="sheet-hdr">
    <span class="sheet-title">💳 Collect Payment</span>
    <button class="sheet-x" onclick="closeSheet('sCheckout','bgCheckout')"><i class="fa fa-times"></i></button>
  </div>
  <div class="sheet-body">
    <div class="checkout-amt-box">
      <div class="checkout-amt-label">Total Amount Due</div>
      <div class="checkout-amt-val" id="coTotal"><?= $sym ?>0</div>
    </div>
    <div style="font-size:.72rem;font-weight:800;color:var(--text-m);text-transform:uppercase;letter-spacing:.06em;margin-bottom:.625rem">Payment Method</div>
    <div class="pay-grid">
      <?php foreach(['cash'=>['💵','Cash'],'card'=>['💳','Card'],'upi'=>['📱','UPI'],'wallet'=>['👛','Wallet'],'online'=>['🌐','Online'],'credit'=>['📋','Credit']] as $k=>[$ic,$lb]): ?>
      <button class="pay-btn <?= $k==='cash'?'on':'' ?>" data-method="<?= $k ?>" onclick="selectPay(this)">
        <span class="pay-btn-icon"><?= $ic ?></span>
        <span class="pay-btn-label"><?= $lb ?></span>
      </button>
      <?php endforeach; ?>
    </div>
    <div class="amt-input-box">
      <div class="amt-input-label">Amount Received</div>
      <input type="number" class="amt-input" id="payAmt" inputmode="decimal" oninput="calcChange()">
    </div>
    <div class="quick-btns" id="quickBtns">
      <button class="quick-btn" onclick="setAmt('exact')">Exact</button>
      <button class="quick-btn" onclick="setAmt(500)">₹500</button>
      <button class="quick-btn" onclick="setAmt(1000)">₹1,000</button>
      <button class="quick-btn" onclick="setAmt(2000)">₹2,000</button>
      <button class="quick-btn" onclick="setAmt(5000)">₹5,000</button>
    </div>
    <div class="ref-box" id="refBox" style="display:none">
      <label>Reference / Transaction ID</label>
      <input type="text" id="payRef" placeholder="UPI ref · Card last 4 digits">
    </div>
    <div class="change-row" id="changeRow" style="display:none">
      <span class="change-row-label"><i class="fa fa-rotate-left"></i> Change to Return</span>
      <span class="change-row-val" id="changeVal"><?= $sym ?>0</span>
    </div>
  </div>
  <div class="sheet-foot">
    <button onclick="closeSheet('sCheckout','bgCheckout')" style="flex:1;padding:.75rem;border:1.5px solid var(--border);border-radius:var(--radius-sm);background:#fff;font-family:var(--font);font-weight:700;cursor:pointer">Cancel</button>
    <button id="btnConfirmPay" onclick="processPayment()" style="flex:2.5;padding:.75rem;border:none;border-radius:var(--radius-sm);background:var(--success);color:#fff;font-family:var(--font);font-weight:900;cursor:pointer;font-size:1rem">
      <i class="fa fa-check"></i> Confirm Payment
    </button>
  </div>
</div>

<!-- Mobile Cart Sheet -->
<div class="sheet-bg" id="bgCart" onclick="closeBg('bgCart','sCart')"></div>
<div class="sheet" id="sCart" style="max-height:92vh">
  <div class="sheet-pip"></div>
  <div class="cart-hdr" style="border-radius:0">
    <div class="cart-hdr-title"><i class="fa fa-cart-shopping" style="color:var(--primary)"></i> Order <span class="cart-badge" id="cartBadgeMob">0</span></div>
    <button class="sheet-x" onclick="closeSheet('sCart','bgCart')"><i class="fa fa-times"></i></button>
  </div>
  <div class="cart-chips">
    <button class="chip" onclick="closeSheet('sCart','bgCart');setTimeout(()=>openSheet('sCustomer'),200)"><i class="fa fa-user-plus"></i> Customer</button>
    <?php if($order_type==='dine_in'): ?>
    <button class="chip" onclick="closeSheet('sCart','bgCart');setTimeout(()=>openSheet('sGuests'),200)"><i class="fa fa-users"></i> Guests</button>
    <?php endif; ?>
    <button class="chip" onclick="closeSheet('sCart','bgCart');setTimeout(()=>openSheet('sNote'),200)"><i class="fa fa-note-sticky"></i> Note</button>
  </div>
  <div id="mCartBody" style="flex:1;overflow-y:auto"></div>
  <div class="disc-row">
    <select id="discTypeMob" onchange="calcDiscountMob()">
      <option value="">Discount</option>
      <option value="percent">% Off</option>
      <option value="flat">₹ Flat</option>
    </select>
    <input type="number" id="discValMob" placeholder="0" min="0" oninput="calcDiscountMob()">
  </div>
  <div class="cart-totals" id="mCartTotals" style="display:none"></div>
  <div class="cart-foot" style="padding-bottom:max(.7rem,env(safe-area-inset-bottom))">
    <button class="btn-kot" id="btnKotMob" disabled onclick="closeSheet('sCart','bgCart');setTimeout(doKot,200)"><i class="fa fa-fire-burner"></i> Send KOT to Kitchen</button>
    <button class="btn-pay" id="btnPayMob" disabled onclick="closeSheet('sCart','bgCart');setTimeout(()=>openSheet('sCheckout'),200)">
      <i class="fa fa-cash-register"></i> Checkout &nbsp;<span id="payAmtMob"><?= $sym ?>0</span>
    </button>
  </div>
</div>

<!-- Customer Sheet -->
<div class="sheet-bg" id="bgCustomer" onclick="closeBg('bgCustomer','sCustomer')"></div>
<div class="sheet" id="sCustomer">
  <div class="sheet-pip"></div>
  <div class="sheet-hdr">
    <span class="sheet-title"><i class="fa fa-user" style="color:var(--primary)"></i> Add Customer</span>
    <button class="sheet-x" onclick="closeSheet('sCustomer','bgCustomer')"><i class="fa fa-times"></i></button>
  </div>
  <div class="sheet-body">
    <div style="display:flex;gap:.5rem;margin-bottom:.875rem">
      <input type="tel" id="custSearch" inputmode="numeric" placeholder="Search by phone number..."
             style="flex:1;padding:.7rem .875rem;border:1.5px solid var(--border);border-radius:var(--radius-sm);font-size:.9rem;outline:none;font-family:var(--font)">
      <button onclick="searchCust()" style="padding:.7rem 1rem;background:var(--primary);color:#fff;border:none;border-radius:var(--radius-sm);font-weight:800;cursor:pointer;font-size:.875rem"><i class="fa fa-search"></i></button>
    </div>
    <div id="custResult"></div>
    <div style="border-top:1px solid var(--border);padding-top:.875rem;margin-top:.25rem">
      <div style="font-size:.72rem;font-weight:800;color:var(--text-m);text-transform:uppercase;letter-spacing:.06em;margin-bottom:.6rem">Or Enter Manually</div>
      <div class="field-row">
        <div class="field-group"><label class="field-label">Name</label><input type="text" class="field-input" id="custName" placeholder="Customer name"></div>
        <div class="field-group"><label class="field-label">Phone</label><input type="tel" class="field-input" id="custPhone" inputmode="numeric" placeholder="Mobile number"></div>
      </div>
    </div>
  </div>
  <div class="sheet-foot">
    <button onclick="clearCust();closeSheet('sCustomer','bgCustomer')" style="flex:1;padding:.7rem;border:1.5px solid var(--border);border-radius:var(--radius-sm);background:#fff;font-family:var(--font);font-weight:700;cursor:pointer">Clear</button>
    <button onclick="setCust()" style="flex:2;padding:.7rem;border:none;border-radius:var(--radius-sm);background:var(--primary);color:#fff;font-family:var(--font);font-weight:800;cursor:pointer;font-size:.95rem">Set Customer</button>
  </div>
</div>

<!-- Guests Sheet -->
<div class="sheet-bg" id="bgGuests" onclick="closeBg('bgGuests','sGuests')"></div>
<div class="sheet" id="sGuests" style="max-height:auto">
  <div class="sheet-pip"></div>
  <div class="sheet-hdr">
    <span class="sheet-title">No. of Guests</span>
    <button class="sheet-x" onclick="closeSheet('sGuests','bgGuests')"><i class="fa fa-times"></i></button>
  </div>
  <div class="sheet-body" style="text-align:center">
    <div class="guest-stepper">
      <button class="stepper-btn" onclick="changeGuests(-1)">−</button>
      <span class="stepper-val" id="guestNum">1</span>
      <button class="stepper-btn plus" onclick="changeGuests(1)">+</button>
    </div>
    <div style="color:var(--text-m);font-size:.85rem;margin-top:-.5rem">guests at this table</div>
  </div>
  <div class="sheet-foot">
    <button onclick="closeSheet('sGuests','bgGuests')" style="flex:1;padding:.7rem;border:none;border-radius:var(--radius-sm);background:var(--primary);color:#fff;font-family:var(--font);font-weight:800;cursor:pointer;font-size:.95rem">Done</button>
  </div>
</div>

<!-- Note Sheet -->
<div class="sheet-bg" id="bgNote" onclick="closeBg('bgNote','sNote')"></div>
<div class="sheet" id="sNote">
  <div class="sheet-pip"></div>
  <div class="sheet-hdr">
    <span class="sheet-title">Kitchen Note</span>
    <button class="sheet-x" onclick="closeSheet('sNote','bgNote')"><i class="fa fa-times"></i></button>
  </div>
  <div class="sheet-body">
    <textarea id="kitchenNote" rows="5" placeholder="e.g. No garlic for table 3, less spicy..."
              style="width:100%;padding:.875rem;border:1.5px solid var(--border);border-radius:var(--radius);font-size:.95rem;font-family:var(--font);resize:none;outline:none;line-height:1.55"></textarea>
  </div>
  <div class="sheet-foot">
    <button onclick="closeSheet('sNote','bgNote')" style="flex:1;padding:.7rem;border:none;border-radius:var(--radius-sm);background:var(--primary);color:#fff;font-family:var(--font);font-weight:800;cursor:pointer;font-size:.95rem"><i class="fa fa-check"></i> Save Note</button>
  </div>
</div>

<!-- Coupon Sheet -->
<div class="sheet-bg" id="bgCoupon" onclick="closeBg('bgCoupon','sCoupon')"></div>
<div class="sheet" id="sCoupon">
  <div class="sheet-pip"></div>
  <div class="sheet-hdr">
    <span class="sheet-title"><i class="fa fa-ticket" style="color:var(--primary)"></i> Apply Coupon</span>
    <button class="sheet-x" onclick="closeSheet('sCoupon','bgCoupon')"><i class="fa fa-times"></i></button>
  </div>
  <div class="sheet-body">
    <div style="display:flex;gap:.5rem;margin-bottom:.875rem">
      <input type="text" id="couponCode" placeholder="Enter coupon code" style="text-transform:uppercase;flex:1;padding:.75rem .875rem;border:1.5px solid var(--border);border-radius:var(--radius-sm);font-size:.95rem;font-family:var(--font);outline:none;letter-spacing:.08em">
      <button onclick="applyCoupon()" style="padding:.75rem 1.1rem;background:var(--primary);color:#fff;border:none;border-radius:var(--radius-sm);font-weight:800;cursor:pointer;font-size:.875rem">Apply</button>
    </div>
    <div id="couponResult"></div>
  </div>
</div>

<!-- ═══ Loading & Success ═══ -->
<div class="pos-loader" id="posLoader">
  <div class="loader-ring"></div>
  <div class="loader-txt" id="loaderTxt">Processing...</div>
</div>

<div class="pos-success" id="posSuccess">
  <div class="success-card">
    <span class="success-anim">✅</span>
    <div class="success-title">Payment Done!</div>
    <div class="success-inv" id="successInv"></div>
    <div class="success-change" id="successChange" style="display:none"></div>
    <div class="success-btns">
      <button class="success-btn-new" onclick="newOrder()"><i class="fa fa-plus"></i> New Order</button>
      <button class="success-btn-print" onclick="reprintBill()"><i class="fa fa-print"></i> Print Bill Again</button>
    </div>
  </div>
</div>

<!-- KOT alert -->
<div class="kot-alert" id="kotAlert"></div>

<!-- ═══ JS ENGINE ═══ -->
<script>
const SYM     = '<?= $sym ?>';
const TAXTYPE = '<?= $taxType ?>';
const SVCPCT  = <?= $svcPct ?>;
const OMODE   = '<?= $order_type ?>';
const TABLEID = '<?= $table_id ?? '' ?>';
const BASE    = '<?= base_url() ?>';
const CN      = '<?= csrf_token() ?>';
const CT      = '<?= csrf_hash() ?>';

let cart=[], disc={type:'',val:0,amt:0,coupon:''}, cust={id:null,name:'',phone:''}, guests=1, note='', payMethod='cash', lastOid=null;

// ── Clock ─────────────────────────────────────────────────
setInterval(()=>{ const e=document.getElementById('pbclock'); if(e)e.textContent=new Date().toLocaleTimeString('en-IN',{hour:'2-digit',minute:'2-digit',second:'2-digit'}); },1000);

// ── Sheet helpers ─────────────────────────────────────────
function openSheet(id){ document.getElementById('bg'+id.slice(1))?.classList.add('on'); document.getElementById(id).classList.add('on'); document.body.style.overflow='hidden'; }
function closeSheet(id,bgId){ document.getElementById(id)?.classList.remove('on'); if(bgId)document.getElementById(bgId)?.classList.remove('on'); document.body.style.overflow=''; }
function closeBg(bgId,shId){ closeSheet(shId,bgId); }

// ── Search & Filter ───────────────────────────────────────
const ms=document.getElementById('msearch');
const msc=document.getElementById('msearchClear');
ms.addEventListener('input',function(){
  const q=this.value.toLowerCase().trim();
  msc.classList.toggle('show',q.length>0);
  const cat=document.querySelector('.cat-pill.on')?.dataset.cat||'all';
  filterMenu(q,cat);
});
function clearSearch(){ ms.value=''; msc.classList.remove('show'); filterMenu('',document.querySelector('.cat-pill.on')?.dataset.cat||'all'); ms.focus(); }
function filterMenu(q,cat){
  document.querySelectorAll('.mcard').forEach(c=>{
    const mq=!q||c.dataset.nm.includes(q);
    const mc=cat==='all'||c.dataset.cat===cat;
    c.style.display=mq&&mc?'':'none';
  });
}
document.querySelectorAll('.cat-pill').forEach(b=>b.addEventListener('click',function(){
  document.querySelectorAll('.cat-pill').forEach(x=>x.classList.remove('on'));
  this.classList.add('on');
  const q=ms.value.toLowerCase().trim();
  filterMenu(q,this.dataset.cat);
  this.scrollIntoView({behavior:'smooth',inline:'center',block:'nearest'});
}));

// ── Add to Cart ───────────────────────────────────────────
function handleItem(el){
  const vars=JSON.parse(el.dataset.vars||'[]');
  const adds=JSON.parse(el.dataset.adds||'[]');
  const item={ id:el.dataset.id, name:ucfirst(el.dataset.nm), price:parseFloat(el.dataset.price), tax:parseFloat(el.dataset.tax), type:el.dataset.type, vars, adds };
  el.classList.add('pop'); setTimeout(()=>el.classList.remove('pop'),220);
  if(vars.length||adds.length){ pendingItem=item; openVariantSheet(item); }
  else addToCart({...item,vid:null,vname:null,addons:[],qty:1,note:''});
}

let pendingItem=null;
function addToCart(item){
  const key=`${item.id}_${item.vid||''}_${item.addons.map(a=>a.id).sort().join(',')}`;
  const ex=cart.find(c=>c._k===key);
  if(ex){ ex.qty++; } else { cart.push({...item,_k:key}); }
  renderAll();
  toast(item.name+' added','success');
}

// ── Variant Sheet ─────────────────────────────────────────
function openVariantSheet(item){
  document.getElementById('sVariantTitle').textContent=item.name;
  let h='';
  if(item.vars.length){
    h+=`<div style="margin-bottom:1rem"><div style="font-size:.72rem;font-weight:800;color:var(--text-m);text-transform:uppercase;letter-spacing:.06em;margin-bottom:.5rem">Choose Variant <span style="color:var(--danger)">*</span></div><div class="variant-grid" id="varGrid">`;
    item.vars.forEach(v=>{ h+=`<div class="var-opt" data-vid="${v.id}" data-vprice="${v.price}" data-vname="${v.name}" onclick="selectVar(this)"><div class="var-name">${v.name}</div><div class="var-price">${SYM}${parseFloat(v.price).toFixed(2)}</div></div>`; });
    h+='</div></div>';
  }
  item.adds.forEach(g=>{
    const itype=g.selection_type==='single'?'radio':'checkbox';
    h+=`<div style="margin-bottom:1rem"><div style="font-size:.72rem;font-weight:800;color:var(--text-m);text-transform:uppercase;letter-spacing:.06em;margin-bottom:.5rem">${g.name} ${g.is_required?'<span style="color:var(--danger)">*</span>':'<span style="font-weight:400;opacity:.7">(optional)</span>'}</div>`;
    g.addons.forEach(a=>{ h+=`<div class="addon-opt"><input type="${itype}" name="ag${g.id}" value="${a.id}" data-nm="${a.name}" data-pr="${a.price}"><span class="addon-opt-name">${a.name}</span>${a.price>0?`<span class="addon-opt-price">+${SYM}${parseFloat(a.price).toFixed(2)}</span>`:''}</div>`; });
    h+='</div>';
  });
  h+=`<div style="margin-bottom:.25rem"><div style="font-size:.72rem;font-weight:800;color:var(--text-m);margin-bottom:.4rem">Special Instructions</div><input type="text" id="itemNote" placeholder="e.g. No onion, less spicy..." class="field-input"></div>`;
  document.getElementById('sVariantBody').innerHTML=h;
  openSheet('sVariant');
}
function selectVar(el){ document.querySelectorAll('.var-opt').forEach(o=>o.classList.remove('on')); el.classList.add('on'); }
function confirmVariant(){
  if(!pendingItem)return;
  const item=pendingItem;
  let vid=null,vname=null,price=item.price;
  if(item.vars.length){
    const s=document.querySelector('.var-opt.on');
    if(!s){toast('Please select a variant','error');return;}
    vid=s.dataset.vid; vname=s.dataset.vname; price=parseFloat(s.dataset.vprice);
  }
  const addons=[];
  document.querySelectorAll('[name^="ag"]:checked').forEach(i=>addons.push({id:i.value,name:i.dataset.nm,price:parseFloat(i.dataset.pr||0)}));
  const n=document.getElementById('itemNote')?.value||'';
  addToCart({...item,vid,vname,price,addons,qty:1,note:n});
  closeSheet('sVariant','bgVariant');
  pendingItem=null;
}

// ── Render ────────────────────────────────────────────────
function renderAll(){ renderCart(); renderMobileCart(); updateFab(); }

function renderCart(){
  const tot=calcTotals();
  const cnt=cart.reduce((s,i)=>s+i.qty,0);
  ['cartBadge','cartBadgeMob'].forEach(id=>{ const e=document.getElementById(id); if(e)e.textContent=cnt; });
  const empty=document.getElementById('cartEmpty');
  const body=document.getElementById('cartBody');
  if(!body)return;
  if(cart.length===0){
    if(empty)empty.style.display=''; else body.innerHTML=`<div class="cart-empty"><div class="cart-empty-icon"><i class="fa fa-cart-shopping"></i></div><div class="cart-empty-hint">Tap any item<br>to add to order</div></div>`;
    document.getElementById('cartTotals').style.display='none';
    ['btnKot','btnPay','btnKotMob','btnPayMob'].forEach(id=>{ const e=document.getElementById(id); if(e)e.disabled=true; });
    return;
  }
  if(empty)empty.style.display='none';
  body.innerHTML=cartItemsHTML();
  document.getElementById('cartTotals').style.display='';
  updateTotalsDOM(tot,'tSub','tDisc','tDiscRow','tTax','tSvc','tSvcRow','tRnd','tRndRow','tTotal');
  ['btnKot','btnPay','btnKotMob','btnPayMob'].forEach(id=>{ const e=document.getElementById(id); if(e)e.disabled=false; });
  ['payAmtChip','payAmtMob'].forEach(id=>{ const e=document.getElementById(id); if(e)e.textContent=SYM+tot.total.toFixed(2); });
  document.getElementById('coTotal').textContent=SYM+tot.total.toFixed(2);
  const pa=document.getElementById('payAmt'); if(pa)pa.value=tot.total.toFixed(2);
}

function renderMobileCart(){
  const tot=calcTotals();
  const mb=document.getElementById('mCartBody'); if(!mb)return;
  if(cart.length===0){ mb.innerHTML=`<div class="cart-empty"><div class="cart-empty-icon"><i class="fa fa-cart-shopping"></i></div><div class="cart-empty-hint">Tap items to add</div></div>`; document.getElementById('mCartTotals').style.display='none'; return; }
  mb.innerHTML=cartItemsHTML(true);
  const mt=document.getElementById('mCartTotals'); mt.style.display='';
  mt.innerHTML=totalsHTML(tot);
}

function cartItemsHTML(mobile=false){
  return cart.map((item,i)=>{
    const addt=item.addons.reduce((s,a)=>s+a.price,0);
    const unit=item.price+addt;
    const line=unit*item.qty;
    const isVeg=['veg','vegan'].includes(item.type);
    const dotClr=isVeg?'var(--success)':'var(--danger)';
    return `<div class="citem">
      <div class="citem-left">
        <div class="citem-name">
          <span style="display:inline-block;width:10px;height:10px;border:1.5px solid ${dotClr};border-radius:2px;margin-right:4px;vertical-align:middle;position:relative">
            <span style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);width:4px;height:4px;border-radius:50%;background:${dotClr}"></span>
          </span>${item.name}</div>
        ${item.vname?`<div class="citem-sub">${item.vname}</div>`:''}
        ${item.addons.map(a=>`<div class="citem-sub">+ ${a.name}${a.price>0?' (+'+SYM+a.price.toFixed(2)+')':''}</div>`).join('')}
        ${item.note?`<div class="citem-note">⚡ ${item.note}</div>`:''}
      </div>
      <div class="citem-right">
        <div class="citem-price">${SYM}${line.toFixed(2)}</div>
        <div class="qrow">
          <button class="qbtn minus" onclick="chgQty(${i},-1)">−</button>
          <span class="qval">${item.qty}</span>
          <button class="qbtn" onclick="chgQty(${i},1)">+</button>
        </div>
      </div>
    </div>`;
  }).join('');
}

function totalsHTML(tot){
  return `<div class="trow"><span>Subtotal</span><span>${SYM}${tot.sub.toFixed(2)}</span></div>
    ${tot.disc>0?`<div class="trow disc"><span>Discount</span><span>-${SYM}${tot.disc.toFixed(2)}</span></div>`:''}
    <div class="trow"><span>GST</span><span>${SYM}${tot.tax.toFixed(2)}</span></div>
    ${tot.svc>0?`<div class="trow"><span>Service</span><span>${SYM}${tot.svc.toFixed(2)}</span></div>`:''}
    ${Math.abs(tot.rnd)>.001?`<div class="trow"><span>Round Off</span><span>${tot.rnd>0?'+':''}${SYM}${tot.rnd.toFixed(2)}</span></div>`:''}
    <div class="trow grand"><span>TOTAL</span><span>${SYM}${tot.total.toFixed(2)}</span></div>`;
}

function updateTotalsDOM(tot,sub,discid,discrow,tax,svc,svcrow,rnd,rndrow,total){
  const gs=(id)=>document.getElementById(id);
  if(gs(sub)) gs(sub).textContent=SYM+tot.sub.toFixed(2);
  if(gs(tax)) gs(tax).textContent=SYM+tot.tax.toFixed(2);
  if(gs(total)) gs(total).textContent=SYM+tot.total.toFixed(2);
  if(gs(discrow)) gs(discrow).style.display=tot.disc>0?'':'none';
  if(gs(discid))  gs(discid).textContent='-'+SYM+tot.disc.toFixed(2);
  if(gs(svcrow))  gs(svcrow).style.display=tot.svc>0?'':'none';
  if(gs(svc))     gs(svc).textContent=SYM+tot.svc.toFixed(2);
  if(gs(rndrow))  gs(rndrow).style.display=Math.abs(tot.rnd)>.001?'':'none';
  if(gs(rnd))     gs(rnd).textContent=(tot.rnd>0?'+':'')+SYM+tot.rnd.toFixed(2);
}

function updateFab(){
  const cnt=cart.reduce((s,i)=>s+i.qty,0);
  const fab=document.getElementById('cartFab');
  const fbb=document.getElementById('fabBadge');
  if(fab){ fab.style.display=cnt>0?'flex':'none'; }
  if(fbb) fbb.textContent=cnt;
}

function chgQty(i,d){ cart[i].qty=Math.max(0,cart[i].qty+d); if(cart[i].qty===0)cart.splice(i,1); renderAll(); }
function clearCart(){ if(!confirm('Clear all items?'))return; cart=[]; disc={type:'',val:0,amt:0,coupon:''}; renderAll(); }

// ── Totals ────────────────────────────────────────────────
function calcTotals(){
  let sub=0,tax=0;
  cart.forEach(item=>{
    const at=item.addons.reduce((s,a)=>s+a.price,0);
    const lt=(item.price+at)*item.qty;
    sub+=lt;
    tax+=TAXTYPE==='exclusive'?lt*(item.tax/100):lt-lt/(1+item.tax/100);
  });
  const d=disc.amt||0;
  const svc=SVCPCT>0?(sub-d)*SVCPCT/100:0;
  const pre=sub-d+tax+svc;
  const total=Math.round(pre);
  const rnd=total-pre;
  return {sub:parseFloat(sub.toFixed(2)),tax:parseFloat(tax.toFixed(2)),disc:d,svc:parseFloat(svc.toFixed(2)),rnd:parseFloat(rnd.toFixed(2)),total};
}

// ── Discount ──────────────────────────────────────────────
function calcDiscount(){
  const t=document.getElementById('discType').value;
  const v=parseFloat(document.getElementById('discVal').value)||0;
  _applyDiscount(t,v);
}
function calcDiscountMob(){
  const t=document.getElementById('discTypeMob').value;
  const v=parseFloat(document.getElementById('discValMob').value)||0;
  _applyDiscount(t,v);
}
function _applyDiscount(t,v){
  const sub=cart.reduce((s,i)=>s+(i.price+i.addons.reduce((a,b)=>a+b.price,0))*i.qty,0);
  let amt=t==='percent'?Math.min(sub,sub*v/100):t==='flat'?Math.min(v,sub):0;
  disc={type:t,val:v,amt:parseFloat(amt.toFixed(2)),coupon:disc.coupon};
  renderAll();
}
function applyCoupon(){
  const code=document.getElementById('couponCode').value.trim();
  if(!code)return;
  const sub=cart.reduce((s,i)=>s+(i.price+i.addons.reduce((a,b)=>a+b.price,0))*i.qty,0);
  postF(BASE+'pos/order/apply-coupon',{coupon_code:code,subtotal:sub}).then(d=>{
    const el=document.getElementById('couponResult');
    if(d.success){
      disc={type:'coupon',val:d.discount,amt:d.discount,coupon:code};
      renderAll();
      el.innerHTML=`<div style="background:var(--success-l);border:1px solid #BBF7D0;padding:.75rem;border-radius:var(--radius);color:var(--success);font-weight:700"><i class="fa fa-check-circle"></i> Applied! Saving ${SYM}${d.discount.toFixed(2)}</div>`;
      setTimeout(()=>closeSheet('sCoupon','bgCoupon'),1600);
    } else {
      el.innerHTML=`<div style="background:var(--danger-l);border:1px solid #FECACA;padding:.75rem;border-radius:var(--radius);color:var(--danger);font-weight:700"><i class="fa fa-circle-exclamation"></i> ${d.message||'Invalid coupon'}</div>`;
    }
  });
}

// ── Customer ──────────────────────────────────────────────
function searchCust(){
  const ph=document.getElementById('custSearch').value.trim(); if(!ph)return;
  fetch(BASE+'admin/customers?phone='+encodeURIComponent(ph)+'&ajax=1')
    .then(r=>r.json()).then(d=>{
      const el=document.getElementById('custResult');
      if(d.found){
        el.innerHTML=`<div class="cust-result"><div class="cust-result-info"><div class="cust-result-name">${d.name}</div><div class="cust-result-phone">${d.phone}</div>${d.points>0?`<div class="cust-result-pts">⭐ ${d.points} loyalty pts</div>`:''}</div><button class="cust-pick-btn" onclick="pickCust('${d.id}','${d.name}','${d.phone}')">Select</button></div>`;
        document.getElementById('custName').value=d.name;
        document.getElementById('custPhone').value=d.phone;
      } else {
        el.innerHTML=`<div style="color:var(--text-m);font-size:.85rem;padding:.5rem 0">No customer found — fill details below.</div>`;
        document.getElementById('custPhone').value=ph;
      }
    });
}
function pickCust(id,name,phone){ cust={id,name,phone}; updateCustChip(); closeSheet('sCustomer','bgCustomer'); toast('Customer: '+name,'success'); }
function setCust(){ const n=document.getElementById('custName').value.trim(); const p=document.getElementById('custPhone').value.trim(); cust={id:cust.id||null,name:n,phone:p}; updateCustChip(); closeSheet('sCustomer','bgCustomer'); if(n)toast('Customer set','success'); }
function clearCust(){ cust={id:null,name:'',phone:''}; updateCustChip(); }
function updateCustChip(){ const c=document.getElementById('chipCustomer'); if(!c)return; if(cust.name){c.innerHTML=`<i class="fa fa-user"></i> ${cust.name}`;c.classList.add('on');}else{c.innerHTML=`<i class="fa fa-user-plus"></i> Customer`;c.classList.remove('on');} }

// ── Guests ────────────────────────────────────────────────
function changeGuests(d){ guests=Math.max(1,Math.min(50,guests+d)); document.getElementById('guestNum').textContent=guests; const c=document.getElementById('guestsChipTxt'); if(c)c.textContent=guests+' Guest'+(guests>1?'s':''); }

// ── Checkout ──────────────────────────────────────────────
document.querySelectorAll('.pay-btn').forEach(b=>b.addEventListener('click',()=>selectPay(b)));
function selectPay(btn){
  document.querySelectorAll('.pay-btn').forEach(b=>b.classList.remove('on'));
  btn.classList.add('on'); payMethod=btn.dataset.method;
  document.getElementById('refBox').style.display=['card','upi','online'].includes(payMethod)?'':'none';
  document.getElementById('quickBtns').style.display=payMethod==='cash'?'':'none';
  calcChange();
}
function setAmt(v){ const t=calcTotals().total; document.getElementById('payAmt').value=v==='exact'?t.toFixed(2):v; calcChange(); }
function calcChange(){
  const paid=parseFloat(document.getElementById('payAmt').value)||0;
  const total=calcTotals().total;
  const ch=paid-total;
  const cr=document.getElementById('changeRow'); const cv=document.getElementById('changeVal');
  if(ch>.009&&payMethod==='cash'){cr.style.display='';cv.textContent=SYM+ch.toFixed(2);}else{cr.style.display='none';}
}

async function processPayment(){
  const paid=parseFloat(document.getElementById('payAmt').value)||0;
  const tot=calcTotals().total;
  if(paid<tot-.01){toast('Amount less than total!','error');return;}
  const btn=document.getElementById('btnConfirmPay');
  btn.disabled=true; btn.innerHTML='<div style="display:flex;align-items:center;justify-content:center;gap:.5rem"><div style="width:18px;height:18px;border:2px solid rgba(255,255,255,.3);border-top-color:#fff;border-radius:50%;animation:spin 1s linear infinite"></div> Processing...</div>';
  try {
    showLoader('Creating order...');
    if(!lastOid){ const r=await createOrder(); if(!r.success)throw new Error(r.message||'Order failed'); lastOid=r.order_id; }
    showLoader('Processing payment...');
    const payments=JSON.stringify([{method:payMethod,amount:paid,reference:document.getElementById('payRef')?.value||''}]);
    const d=await postF(BASE+'pos/order/checkout',{order_id:lastOid,payments});
    hideLoader();
    if(d.success){
      closeSheet('sCheckout','bgCheckout');
      autoKot(lastOid);
      // Open KOT slip for kitchen
      if (d.kot_slip_url) {
        setTimeout(() => {
          const kotWin = window.open(d.kot_slip_url + '?autoprint=1', 'kot_slip', 'width=380,height=650,scrollbars=yes');
        }, 300);
      }
      // Open bill slip for customer
      if (d.bill_slip_url) {
        setTimeout(() => {
          window.open(d.bill_slip_url + '?autoprint=1', 'bill_slip', 'width=380,height=750,scrollbars=yes');
        }, 800);
      }
      showSuccess(d,paid,tot);
      cart=[]; disc={type:'',val:0,amt:0,coupon:''}; lastOid=null; renderAll();
    } else throw new Error(d.message||'Payment failed');
  } catch(e){ hideLoader(); toast(e.message,'error'); }
  finally{ btn.disabled=false; btn.innerHTML='<i class="fa fa-check"></i> Confirm Payment'; }
}

async function autoKot(oid){
  try{ await postF(BASE+'pos/order/print-kot',{order_id:oid}); }catch(e){}
}

const ADD_TO_ORDER = <?= (int)($add_to_order_id ?? 0) ?>;

async function createOrder(){
  const items=cart.map(i=>({menu_item_id:i.id,variant_id:i.vid,variant_name:i.vname,quantity:i.qty,notes:i.note,addons:i.addons}));
  if (ADD_TO_ORDER) {
    // Add Round mode — append to existing order
    return postF(BASE+'pos/order/add-round',{order_id:ADD_TO_ORDER,items:JSON.stringify(items)});
  }
  return postF(BASE+'pos/order/create',{order_type:OMODE,table_id:TABLEID,customer_id:cust.id||'',customer_name:cust.name||'',customer_phone:cust.phone||'',no_of_guests:guests,discount_type:disc.type||'flat',discount_value:disc.amt||0,kitchen_notes:note,items:JSON.stringify(items)});
}

function showSuccess(data,paid,total){
  const change=paid-total;
  document.getElementById('successInv').textContent='Invoice: '+(data.invoice_number||'–');
  const sc=document.getElementById('successChange');
  if(change>.009&&payMethod==='cash'){sc.style.display='';sc.textContent='💵 Change: '+SYM+change.toFixed(2);}else{sc.style.display='none';}
  document.getElementById('posSuccess').classList.add('on');
}
function newOrder(){ document.getElementById('posSuccess').classList.remove('on'); cust={id:null,name:'',phone:''}; guests=1; note=''; updateCustChip(); }
async function reprintBill(){
  if(!lastOid) return;
  // Try thermal printer first
  try {
    const d = await postF(BASE+'pos/order/print-bill',{order_id:lastOid});
    if(d.success){ toast('Printing...','success'); return; }
  } catch(e){}
  // Fallback: open web slip
  window.open(BASE+'pos/slip/bill/'+lastOid+'?autoprint=1','bill_slip','width=380,height=750,scrollbars=yes');
}

// ── KOT ──────────────────────────────────────────────────
async function doKot(){
  if(cart.length===0)return;
  showLoader('Sending to Kitchen...');
  try{
    if(!lastOid){const r=await createOrder();if(!r.success)throw new Error(r.message);lastOid=r.order_id;}
    const d=await postF(BASE+'pos/order/print-kot',{order_id:lastOid});
    hideLoader();
    showKotAlert(d.success?'✅ KOT sent to kitchen!':'⚠️ Saved (printer offline)',d.success?'success':'warning');
  } catch(e){hideLoader();showKotAlert('⚠️ '+e.message,'error');}
}
function showKotAlert(msg,type){
  const clrs={success:'var(--success-l)',warning:'var(--warning-l)',error:'var(--danger-l)'};
  const bclrs={success:'#BBF7D0',warning:'#FDE68A',error:'#FECACA'};
  const tclrs={success:'var(--success)',warning:'var(--warning)',error:'var(--danger)'};
  const el=document.getElementById('kotAlert');
  el.innerHTML=`<div class="kot-alert-inner" style="background:${clrs[type]};border:1px solid ${bclrs[type]};color:${tclrs[type]}">${msg}</div>`;
  el.style.display='block';
  setTimeout(()=>el.style.display='none',3500);
}

// ── Loader ────────────────────────────────────────────────
function showLoader(txt='Processing...'){ document.getElementById('loaderTxt').textContent=txt; document.getElementById('posLoader').classList.add('on'); }
function hideLoader(){ document.getElementById('posLoader').classList.remove('on'); }

// ── Toast ─────────────────────────────────────────────────
function toast(msg,type='info'){
  const ic={success:'fa-check-circle',error:'fa-circle-exclamation',warning:'fa-triangle-exclamation',info:'fa-circle-info'};
  const t=document.createElement('div');
  t.className='pos-toast '+type;
  t.innerHTML=`<i class="fa ${ic[type]||ic.info}"></i>${msg}`;
  document.body.appendChild(t);
  setTimeout(()=>{t.style.transition='opacity .3s,transform .3s';t.style.opacity='0';t.style.transform='translateX(-50%) translateY(-8px)';setTimeout(()=>t.remove(),300);},2600);
}

// ── Utils ─────────────────────────────────────────────────
function postF(url,data={}){
  const body=new URLSearchParams({[CN]:CT,...data});
  return fetch(url,{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded','X-CSRF-TOKEN':CT},body}).then(r=>r.json());
}
function ucfirst(s){ return s.charAt(0).toUpperCase()+s.slice(1); }

// Init
renderAll();
</script>
<?php $this->endSection(); ?>
