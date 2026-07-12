<?php $this->extend('layouts/pos_layout');
$this->section('content'); ?>
<?php
$totalTables = 0;
$availCount = 0;
$occuCount = 0;
$bookedCount = 0;
foreach ($tables as $area) {
  foreach ($area['tables'] as $t) {
    $totalTables++;
    if ($t['status'] === 'available') $availCount++;
    elseif ($t['status'] === 'occupied') $occuCount++;
    elseif ($t['status'] === 'booked') $bookedCount++;
  }
}
?>

<style>
  /* ══════════════════════════════════════════════════════════
   POS INDEX — NATIVE APP SHELL
   Status-bar aware, collapsing large-title nav, capsule
   segmented control, scrollable stat chips, bottom tab bar
   with home-indicator. Everything else (logic, IDs) untouched.
   ══════════════════════════════════════════════════════════ */

  :root {
    --sat: env(safe-area-inset-top, 0px);
    --sab: env(safe-area-inset-bottom, 0px);
    --navy-0: #0A0F1C;
    --navy-1: #111A2C;
    --navy-2: #1B2740;
    --glass: rgba(17, 26, 44, .72);
    --hair: rgba(255, 255, 255, .08);
  }

  * {
    -webkit-tap-highlight-color: transparent;
  }

  .pi-root {
    display: flex;
    flex-direction: column;
    height: 100vh;
    height: 100dvh;
    background: #F0F2F6;
    overflow: hidden;
  }

  /* ── Status bar spacer (mimics native safe area) ── */
  .pi-statusbar {
    height: var(--sat);
    background: var(--navy-0);
    flex-shrink: 0;
  }

  /* ── Sticky nav shell: compact bar + large title, iOS style ── */
  .pi-navshell {
    position: relative;
    flex-shrink: 0;
    background: linear-gradient(165deg, var(--navy-0) 0%, var(--navy-1) 60%, var(--navy-2) 100%);
    box-shadow: 0 1px 0 var(--hair), 0 10px 24px -14px rgba(0, 0, 0, .55);
    z-index: 20;
  }

  /* compact bar — always present, 44pt native height */
  .pi-navbar {
    height: 46px;
    display: flex;
    align-items: center;
    gap: .5rem;
    padding: 0 .625rem;
  }

  .pi-navbar-btn {
    width: 34px;
    height: 34px;
    border-radius: 50%;
    border: none;
    background: rgba(255, 255, 255, .08);
    color: rgba(255, 255, 255, .85);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: .85rem;
    cursor: pointer;
    flex-shrink: 0;
    transition: transform .12s ease, background .15s ease;
    text-decoration: none;
  }

  .pi-navbar-btn:active {
    transform: scale(.9);
    background: rgba(255, 255, 255, .18);
  }

  .pi-navbar-btn.hot {
    background: var(--primary);
    color: #fff;
  }

  .pi-navbar-compact {
    flex: 1;
    min-width: 0;
    display: flex;
    align-items: center;
    gap: .4rem;
    font-weight: 800;
    font-size: .92rem;
    color: #fff;
    letter-spacing: -.01em;
    opacity: 0;
    transform: translateY(4px);
    transition: opacity .18s ease, transform .18s ease;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  .pi-navshell.is-collapsed .pi-navbar-compact {
    opacity: 1;
    transform: translateY(0);
  }

  .pi-navbar-compact .live-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #34D399;
    flex-shrink: 0;
    box-shadow: 0 0 0 3px rgba(52, 211, 153, .18);
  }

  .pi-navbar-spacer {
    flex: 1;
  }

  .pi-navbar-acts {
    display: flex;
    align-items: center;
    gap: .35rem;
    flex-shrink: 0;
  }

  /* large title block, collapses away on scroll */
  .pi-largetitle {
    padding: .15rem 1rem 1rem;
    max-height: 88px;
    overflow: hidden;
    transition: max-height .22s ease, opacity .18s ease, padding .22s ease;
  }

  .pi-navshell.is-collapsed .pi-largetitle {
    max-height: 0;
    opacity: 0;
    padding-top: 0;
    padding-bottom: 0;
  }

  .pi-largetitle-eyebrow {
    display: flex;
    align-items: center;
    gap: .4rem;
    font-size: .68rem;
    font-weight: 700;
    letter-spacing: .04em;
    color: rgba(255, 255, 255, .45);
    margin-bottom: .15rem;
  }

  .pi-largetitle-eyebrow .live-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #34D399;
    box-shadow: 0 0 0 3px rgba(52, 211, 153, .18);
  }

  .pi-largetitle-clock {
    font-family: 'JetBrains Mono', monospace;
    color: rgba(255, 255, 255, .5);
    margin-left: auto;
    font-size: .68rem;
  }

  .pi-largetitle-text {
    margin: 0;
    font-size: 1.55rem;
    font-weight: 900;
    color: #fff;
    letter-spacing: -.02em;
    line-height: 1.15;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  .pi-largetitle-sub {
    font-size: .72rem;
    color: rgba(255, 255, 255, .4);
    margin-top: .15rem;
    font-weight: 600;
  }

  /* ── Segmented control (order type) — capsule, native-feel ── */
  .pi-segment-wrap {
    padding: 0 1rem .75rem;
  }

  .pi-segment {
    display: flex;
    background: rgba(255, 255, 255, .08);
    border-radius: 12px;
    padding: 3px;
    gap: 2px;
  }

  .pi-segment-item {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: .35rem;
    padding: .5rem .4rem;
    border-radius: 9px;
    border: none;
    background: transparent;
    color: rgba(255, 255, 255, .55);
    font-family: var(--font);
    font-size: .75rem;
    font-weight: 700;
    text-decoration: none;
    cursor: pointer;
    transition: background .18s ease, color .18s ease, box-shadow .18s ease;
  }

  .pi-segment-item i {
    font-size: .8rem;
  }

  .pi-segment-item.active {
    background: #fff;
    color: var(--navy-0);
    box-shadow: 0 2px 8px rgba(0, 0, 0, .25);
  }

  .pi-segment-item:active {
    transform: scale(.97);
  }

  /* ── Stat chips — horizontally scrollable native cards ── */
  .pi-chiprow {
    display: flex;
    gap: .5rem;
    overflow-x: auto;
    scrollbar-width: none;
    padding: 0 1rem .875rem;
  }

  .pi-chiprow::-webkit-scrollbar {
    display: none;
  }

  .pi-chip {
    flex: 0 0 auto;
    min-width: 84px;
    background: rgba(255, 255, 255, .06);
    border: 1px solid var(--hair);
    border-radius: 13px;
    padding: .5rem .7rem;
    display: flex;
    flex-direction: column;
    gap: .15rem;
  }

  .pi-chip-top {
    display: flex;
    align-items: center;
    gap: .35rem;
  }

  .pi-chip-dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
  }

  .pi-chip-lbl {
    font-size: .6rem;
    font-weight: 700;
    letter-spacing: .03em;
    text-transform: uppercase;
    color: rgba(255, 255, 255, .45);
  }

  .pi-chip-val {
    font-size: 1.15rem;
    font-weight: 900;
    color: #fff;
    line-height: 1;
  }

  /* ── Table map area ── */
  .pi-map {
    flex: 1;
    overflow-y: auto;
    -webkit-overflow-scrolling: touch;
    padding: 1rem 1rem calc(1rem + 68px + var(--sab));
    background: #F0F2F6;
  }

  .pi-area-lbl {
    font-size: .66rem;
    font-weight: 900;
    letter-spacing: .09em;
    text-transform: uppercase;
    color: #94A3B8;
    margin: 0 0 .55rem;
    padding-left: .15rem;
    display: flex;
    align-items: center;
    gap: .4rem;
  }

  .pi-area-lbl::after {
    content: '';
    flex: 1;
    height: 1px;
    background: #E2E8F0;
  }

  .pi-area-lbl:not(:first-child) {
    margin-top: 1.4rem;
  }

  .pi-tgrid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(90px, 1fr));
    gap: .55rem;
  }

  .pi-tile {
    border-radius: 16px;
    border: 1.5px solid transparent;
    padding: .7rem .5rem .6rem;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: .22rem;
    cursor: pointer;
    user-select: none;
    position: relative;
    min-height: 84px;
    justify-content: center;
    transition: transform .12s ease, box-shadow .15s ease;
    box-shadow: 0 1px 2px rgba(15, 23, 42, .04);
  }

  .pi-tile:active {
    transform: scale(.95);
  }

  .pi-tile-num {
    font-weight: 900;
    font-size: 1.08rem;
    letter-spacing: -.01em;
  }

  .pi-tile-cap {
    font-size: .58rem;
    opacity: .65;
    display: flex;
    align-items: center;
    gap: .18rem;
  }

  .pi-tile-status {
    font-size: .55rem;
    font-weight: 900;
    letter-spacing: .06em;
    text-transform: uppercase;
    padding: .18rem .5rem;
    border-radius: 20px;
    margin-top: .1rem;
  }

  .pi-tile.avail {
    background: #F0FDF4;
    border-color: #BBF7D0;
    color: #15803D;
  }

  .pi-tile.avail .pi-tile-status {
    background: #DCFCE7;
    color: #15803D;
  }

  .pi-tile.occu {
    background: #FEF2F2;
    border-color: #FECACA;
    color: #B91C1C;
  }

  .pi-tile.occu .pi-tile-status {
    background: #FEE2E2;
    color: #B91C1C;
  }

  .pi-tile.booked {
    background: #F5F3FF;
    border-color: #DDD6FE;
    color: #6D28D9;
  }

  .pi-tile.booked .pi-tile-status {
    background: #EDE9FE;
    color: #6D28D9;
  }

  .pi-tile.booked::before {
    content: '\f073';
    font-family: 'Font Awesome 6 Free';
    font-weight: 900;
    position: absolute;
    top: .4rem;
    right: .5rem;
    font-size: .6rem;
    opacity: .55;
  }

  .pi-tile.resv {
    background: #FFFBEB;
    border-color: #FDE68A;
    color: #92400E;
  }

  .pi-tile.resv .pi-tile-status {
    background: #FEF3C7;
    color: #92400E;
  }

  .pi-tile.clean {
    background: #F8FAFC;
    border-color: #E2E8F0;
    color: #64748B;
  }

  .pi-tile.clean .pi-tile-status {
    background: #F1F5F9;
    color: #64748B;
  }

  .pi-tile-orders {
    position: absolute;
    top: .35rem;
    left: .4rem;
    font-size: .58rem;
    font-weight: 800;
    background: #B91C1C;
    color: #fff;
    padding: .1rem .35rem;
    border-radius: 10px;
    line-height: 1.2;
  }

  .pi-empty {
    text-align: center;
    padding: 4rem 1rem;
    color: #94A3B8;
  }

  .pi-empty-emoji {
    font-size: 3rem;
    margin-bottom: 1rem;
  }

  /* ── Bottom tab bar — native, frosted, home indicator ── */
  .pi-tabbar {
    position: fixed;
    left: 0;
    right: 0;
    bottom: 0;
    display: flex;
    background: rgba(255, 255, 255, .92);
    backdrop-filter: blur(18px) saturate(180%);
    -webkit-backdrop-filter: blur(18px) saturate(180%);
    border-top: 1px solid #E5E9F0;
    padding: .4rem .5rem calc(.3rem + var(--sab));
    z-index: 30;
    box-shadow: 0 -6px 20px rgba(15, 23, 42, .06);
  }

  .pi-tab {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: .18rem;
    padding: .3rem .2rem;
    border-radius: 12px;
    color: #94A3B8;
    text-decoration: none;
    font-size: .6rem;
    font-weight: 700;
    transition: color .15s ease, background .15s ease;
  }

  .pi-tab i {
    font-size: 1.05rem;
  }

  .pi-tab.active {
    color: var(--primary);
  }

  .pi-tab:active {
    background: #F1F5F9;
  }

  .pi-tab-badge {
    position: relative;
  }

  .pi-tab-badge .dot {
    position: absolute;
    top: -2px;
    right: 6px;
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #EF4444;
    border: 1.5px solid #fff;
  }

  .pi-home-indicator {
    position: fixed;
    left: 50%;
    bottom: calc(.35rem + var(--sab));
    transform: translateX(-50%);
    width: 120px;
    height: 4px;
    border-radius: 3px;
    background: rgba(15, 23, 42, .18);
    z-index: 31;
    pointer-events: none;
  }

  .pi-desktop-act {
    display: none;
  }

  /* ══════════════════════════════════════════════════════════
   DESKTOP / WIDE VIEWPORT — reflow the same app shell into
   a proper toolbar instead of stretched mobile chrome.
   Bottom tab bar → top nav icons. Stacked header → one row.
   ══════════════════════════════════════════════════════════ */
  @media (min-width: 860px) {
    .pi-statusbar {
      display: none;
    }

    .pi-navshell {
      display: flex;
      flex-wrap: wrap;
      align-items: center;
      padding: .75rem 1.75rem .875rem;
      gap: .4rem 1.5rem;
    }

    .pi-navbar {
      flex: 1 1 100%;
      height: auto;
      padding: 0;
      margin-bottom: .15rem;
    }

    .pi-navbar-btn {
      width: 36px;
      height: 36px;
    }

    .pi-navbar-compact {
      opacity: 1 !important;
      transform: none !important;
      font-size: 1.15rem;
      gap: .5rem;
    }

    .pi-largetitle {
      display: none;
    }

    .pi-segment-wrap {
      flex: 0 0 auto;
      padding: 0;
    }

    .pi-segment {
      background: rgba(255, 255, 255, .08);
    }

    .pi-segment-item {
      padding: .55rem 1.25rem;
      font-size: .78rem;
    }

    .pi-chiprow {
      flex: 1 1 auto;
      padding: 0;
      justify-content: flex-end;
      overflow: visible;
    }

    .pi-chip {
      min-width: 96px;
      padding: .55rem .85rem;
    }

    .pi-chip-val {
      font-size: 1.3rem;
    }

    .pi-desktop-act {
      display: flex;
    }

    .pi-map {
      padding: 1.75rem 2.25rem 2.5rem;
    }

    .pi-tgrid {
      grid-template-columns: repeat(auto-fill, minmax(128px, 1fr));
      gap: .85rem;
    }

    .pi-tile {
      min-height: 104px;
      border-radius: 18px;
      padding: .9rem .6rem .75rem;
    }

    .pi-tile-num {
      font-size: 1.25rem;
    }

    .pi-tile-cap {
      font-size: .65rem;
    }

    .pi-tile-status {
      font-size: .6rem;
      padding: .22rem .6rem;
    }

    .pi-tile:hover {
      transform: translateY(-3px);
      box-shadow: 0 8px 20px rgba(15, 23, 42, .1);
    }

    .pi-tile:active {
      transform: translateY(-1px) scale(.98);
    }

    .pi-area-lbl {
      font-size: .7rem;
    }

    .pi-tabbar,
    .pi-home-indicator {
      display: none;
    }
  }

  @media (min-width: 1280px) {
    .pi-map {
      padding-left: 3rem;
      padding-right: 3rem;
    }

    .pi-tgrid {
      grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
    }
  }
</style>

<div class="pi-root">

  <!-- ── STATUS BAR SPACER ── -->
  <div class="pi-statusbar"></div>

  <!-- ── NAV SHELL: compact bar + collapsing large title ── -->
  <div class="pi-navshell" id="piNavshell">

    <div class="pi-navbar">
      <a href="<?= base_url('admin/dashboard') ?>" class="pi-navbar-btn"><i class="fa fa-chevron-left"></i></a>
      <div class="pi-navbar-compact" id="piNavCompact">
        <span class="live-dot"></span><?= esc(session('restaurant_name') ?? 'DinoviX POS') ?>
      </div>
      <div class="pi-navbar-spacer"></div>
      <div class="pi-navbar-acts">
        <button class="pi-navbar-btn" id="custOrdBtn" title="Customer Orders (QR)" onclick="openCustOrders()" style="position:relative">
          <i class="fa fa-bell"></i>
          <span id="custOrdBadge" style="display:none;position:absolute;top:2px;right:2px;width:15px;height:15px;border-radius:50%;background:#EF4444;color:#fff;font-size:.52rem;font-weight:900;display:flex;align-items:center;justify-content:center;line-height:1;border:1.5px solid var(--navy-0)">0</span>
        </button>
        <a href="<?= base_url('pos/kitchen') ?>" class="pi-navbar-btn pi-desktop-act" title="Kitchen Display"><i class="fa fa-fire-burner"></i></a>
        <a href="<?= base_url('pos/shift/summary') ?>" class="pi-navbar-btn pi-desktop-act" title="Shift Summary"><i class="fa fa-clock"></i></a>
        <a href="<?= base_url('logout') ?>" class="pi-navbar-btn pi-desktop-act" title="Logout"><i class="fa fa-right-from-bracket"></i></a>
      </div>
    </div>

    <div class="pi-largetitle">
      <div class="pi-largetitle-eyebrow">
        <span class="live-dot"></span><?= esc($branch['name'] ?? 'Main Branch') ?>
        <span class="pi-largetitle-clock" id="piClock"></span>
      </div>
      <h1 class="pi-largetitle-text"><?= esc(session('restaurant_name') ?? 'DinoviX POS') ?></h1>
      <div class="pi-largetitle-sub"><?= $totalTables ?> tables across <?= count($tables) ?> area<?= count($tables) === 1 ? '' : 's' ?></div>
    </div>

    <!-- Order type segmented control -->
    <div class="pi-segment-wrap">
      <div class="pi-segment">
        <a href="<?= base_url('pos/new-order/dine_in') ?>" class="pi-segment-item active">
          <i class="fa fa-chair"></i>Dine-in
        </a>
        <a href="<?= base_url('pos/new-order/takeaway') ?>" class="pi-segment-item">
          <i class="fa fa-bag-shopping"></i>Takeaway
        </a>
        <a href="<?= base_url('pos/new-order/delivery') ?>" class="pi-segment-item">
          <i class="fa fa-motorcycle"></i>Delivery
        </a>
      </div>
    </div>

    <!-- Stat chips -->
    <div class="pi-chiprow">
      <div class="pi-chip">
        <div class="pi-chip-top"><span class="pi-chip-dot" style="background:#F59E0B"></span><span class="pi-chip-lbl">Active</span></div>
        <div class="pi-chip-val" id="piStatActive"><?= $openOrder ?? 0 ?></div>
      </div>
      <div class="pi-chip">
        <div class="pi-chip-top"><span class="pi-chip-dot" style="background:#34D399"></span><span class="pi-chip-lbl">Free</span></div>
        <div class="pi-chip-val" style="color:#34D399"><?= $availCount ?></div>
      </div>
      <div class="pi-chip">
        <div class="pi-chip-top"><span class="pi-chip-dot" style="background:#F87171"></span><span class="pi-chip-lbl">Occupied</span></div>
        <div class="pi-chip-val" style="color:#F87171"><?= $occuCount ?></div>
      </div>
      <div class="pi-chip">
        <div class="pi-chip-top"><span class="pi-chip-dot" style="background:#A78BFA"></span><span class="pi-chip-lbl">Booked</span></div>
        <div class="pi-chip-val" style="color:#A78BFA"><?= $bookedCount ?></div>
      </div>
    </div>

  </div>

  <!-- ── TABLE MAP ── -->
  <div class="pi-map" id="piMap">
    <?php if (empty($tables)): ?>
      <div class="pi-empty">
        <div class="pi-empty-emoji">🪑</div>
        <div style="font-weight:700;margin-bottom:.5rem;color:#64748B">No Tables Set Up</div>
        <a href="<?= base_url('admin/tables') ?>" style="color:var(--primary);font-weight:700">Add Tables →</a>
      </div>
      <?php else: foreach ($tables as $area): if (empty($area['tables'])) continue; ?>
        <div class="pi-area-lbl"><?= esc($area['name']) ?></div>
        <div class="pi-tgrid">
          <?php foreach ($area['tables'] as $t):
            $cls = match ($t['status']) {
              'available' => 'avail',
              'occupied'  => 'occu',
              'booked'    => 'booked',
              'reserved'  => 'resv',
              default     => 'clean',
            };
            $lbl = match ($t['status']) {
              'available' => 'Free',
              'occupied'  => 'Occupied',
              'booked'    => 'Booked',
              'reserved'  => 'Reserved',
              'cleaning'  => 'Cleaning',
              default     => ucfirst($t['status']),
            };
            $tData = htmlspecialchars(json_encode([
              'id'     => $t['id'],
              'status' => $t['status'],
              'num'    => $t['table_number'],
              'cap'    => $t['capacity'],
              'booked_name'  => $t['booked_name']  ?? '',
              'booked_phone' => $t['booked_phone'] ?? '',
              'booked_for'   => $t['booked_for']   ?? '',
              'booked_note'  => $t['booked_note']  ?? '',
              'qr_token'     => $t['qr_token']     ?? '',
            ]), ENT_QUOTES);
          ?>
            <div class="pi-tile <?= $cls ?>" onclick="tableClick(<?= $tData ?>)">
              <div class="pi-tile-num"><?= esc($t['table_number']) ?></div>
              <div class="pi-tile-cap"><i class="fa fa-users"></i><?= $t['capacity'] ?> pax</div>
              <div class="pi-tile-status"><?= $lbl ?></div>
            </div>
          <?php endforeach; ?>
        </div>
    <?php endforeach;
    endif; ?>
  </div>

  <!-- ── BOTTOM TAB BAR (native) ── -->
  <div class="pi-tabbar">
    <a href="<?= base_url('pos') ?>" class="pi-tab active"><i class="fa fa-table-cells"></i><span>Tables</span></a>
    <a href="<?= base_url('pos/kitchen') ?>" class="pi-tab"><i class="fa fa-fire-burner"></i><span>Kitchen</span></a>
    <a href="<?= base_url('pos/shift/summary') ?>" class="pi-tab"><i class="fa fa-clock"></i><span>Shift</span></a>
    <div class="pi-tab pi-tab-badge" style="cursor:pointer" onclick="openCustOrders()">
      <i class="fa fa-bell"></i><span>Orders</span>
      <span class="dot" id="tabOrdDot" style="display:none"></span>
    </div>
    <a href="<?= base_url('logout') ?>" class="pi-tab"><i class="fa fa-right-from-bracket"></i><span>Logout</span></a>
  </div>
  <div class="pi-home-indicator"></div>
</div>

<!-- ══ SHEET: Active Table Orders (occupied) ═════════════ -->
<div class="sheet-bg" id="bgOrders" onclick="closeBg('bgOrders','sOrders')"></div>
<div class="sheet" id="sOrders">
  <div class="sheet-pip"></div>
  <div class="sheet-hdr">
    <span class="sheet-title" id="sOrdersTitle">Table Orders</span>
    <button class="sheet-x" onclick="closeSheet('sOrders','bgOrders')"><i class="fa fa-times"></i></button>
  </div>
  <div class="sheet-body" id="sOrdersBody"></div>
  <div class="sheet-foot" id="sOrdersFoot"></div>
</div>

<!-- ══ SHEET: Available Table Actions ════════════════════ -->
<div class="sheet-bg" id="bgAction" onclick="closeBg('bgAction','sAction')"></div>
<div class="sheet" id="sAction">
  <div class="sheet-pip"></div>
  <div class="sheet-hdr">
    <span class="sheet-title" id="sActionTitle">Table Options</span>
    <button class="sheet-x" onclick="closeSheet('sAction','bgAction')"><i class="fa fa-times"></i></button>
  </div>
  <div class="sheet-body" id="sActionBody"></div>
</div>

<!-- ══ SHEET: Book Table ═════════════════════════════════ -->
<div class="sheet-bg" id="bgBook" onclick="closeBg('bgBook','sBook')"></div>
<div class="sheet" id="sBook">
  <div class="sheet-pip"></div>
  <div class="sheet-hdr">
    <span class="sheet-title" id="sBookTitle">📅 Book Table</span>
    <button class="sheet-x" onclick="closeSheet('sBook','bgBook')"><i class="fa fa-times"></i></button>
  </div>
  <div class="sheet-body">
    <input type="hidden" id="bookTableId">
    <div style="display:flex;flex-direction:column;gap:.75rem">
      <div>
        <label style="font-size:.75rem;font-weight:700;color:#64748B;display:block;margin-bottom:.3rem">Guest Name *</label>
        <input type="text" id="bookName" class="form-control" placeholder="Customer name" style="width:100%;padding:.625rem;border:1.5px solid #E2E8F0;border-radius:10px;font-family:var(--font)">
      </div>
      <div>
        <label style="font-size:.75rem;font-weight:700;color:#64748B;display:block;margin-bottom:.3rem">Phone</label>
        <input type="tel" id="bookPhone" class="form-control" placeholder="+91 XXXXX XXXXX" style="width:100%;padding:.625rem;border:1.5px solid #E2E8F0;border-radius:10px;font-family:var(--font)">
      </div>
      <div>
        <label style="font-size:.75rem;font-weight:700;color:#64748B;display:block;margin-bottom:.3rem">Booking Date & Time *</label>
        <input type="datetime-local" id="bookFor" class="form-control" style="width:100%;padding:.625rem;border:1.5px solid #E2E8F0;border-radius:10px;font-family:var(--font)">
      </div>
      <div>
        <label style="font-size:.75rem;font-weight:700;color:#64748B;display:block;margin-bottom:.3rem">Note</label>
        <input type="text" id="bookNote" class="form-control" placeholder="Birthday, Anniversary..." style="width:100%;padding:.625rem;border:1.5px solid #E2E8F0;border-radius:10px;font-family:var(--font)">
      </div>
    </div>
  </div>
  <div class="sheet-foot">
    <button onclick="closeSheet('sBook','bgBook')" style="flex:1;padding:.75rem;border:1.5px solid #E2E8F0;border-radius:10px;background:#fff;font-family:var(--font);font-weight:700;cursor:pointer">Cancel</button>
    <button onclick="confirmBooking()" style="flex:2;padding:.75rem;border:none;border-radius:10px;background:#6D28D9;color:#fff;font-family:var(--font);font-weight:800;cursor:pointer;font-size:.95rem">
      <i class="fa fa-calendar-check"></i> Confirm Booking
    </button>
  </div>
</div>

<!-- ══ CUSTOMER ORDERS MODAL ════════════════════════════ -->
<div id="custOrderModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:500;align-items:flex-end;justify-content:center">
  <div style="background:#F8FAFC;border-radius:22px 22px 0 0;width:100%;max-width:560px;max-height:90vh;display:flex;flex-direction:column">
    <!-- Header -->
    <div style="padding:1rem 1.25rem .75rem;border-bottom:1px solid #E2E8F0;display:flex;align-items:center;justify-content:space-between;flex-shrink:0">
      <div>
        <div style="font-weight:900;font-size:1rem;display:flex;align-items:center;gap:.5rem">
          <span style="font-size:1.1rem">📱</span> Customer QR Orders
        </div>
        <div style="font-size:.72rem;color:#94A3B8;margin-top:.15rem">Orders placed by customers via QR code at their table</div>
      </div>
      <button onclick="closeCustOrders()" style="width:34px;height:34px;border-radius:10px;border:none;background:#F1F5F9;color:#64748B;font-size:.9rem;cursor:pointer"><i class="fa fa-times"></i></button>
    </div>
    <!-- Body -->
    <div id="custOrderBody" style="flex:1;overflow-y:auto;padding:1rem 1.25rem"></div>
    <!-- Footer -->
    <div style="padding:.75rem 1.25rem;border-top:1px solid #E2E8F0;flex-shrink:0;display:flex;gap:.5rem">
      <button onclick="pollCustomerOrders();renderCustOrders()" style="flex:1;padding:.625rem;background:#F1F5F9;color:#334155;border:none;border-radius:10px;font-weight:700;font-size:.82rem;cursor:pointer;font-family:var(--font)"><i class="fa fa-refresh"></i> Refresh</button>
      <button onclick="closeCustOrders()" style="flex:1;padding:.625rem;background:#0F172A;color:#fff;border:none;border-radius:10px;font-weight:700;font-size:.82rem;cursor:pointer;font-family:var(--font)">Close</button>
    </div>
  </div>
</div>

<!-- ══ SHEET: QR Code Display ════════════════════════════ -->
<div class="sheet-bg" id="bgQr" onclick="closeBg('bgQr','sQr')"></div>
<div class="sheet" id="sQr">
  <div class="sheet-pip"></div>
  <div class="sheet-hdr">
    <span class="sheet-title" id="sQrTitle">QR Code</span>
    <button class="sheet-x" onclick="closeSheet('sQr','bgQr')"><i class="fa fa-times"></i></button>
  </div>
  <div class="sheet-body" style="display:flex;flex-direction:column;align-items:center;gap:1rem;padding:1.25rem">
    <div id="qrCanvas" style="background:#fff;padding:16px;border-radius:16px;box-shadow:0 4px 20px rgba(0,0,0,.1)"></div>
    <div id="qrUrl" style="font-size:.72rem;color:#64748B;text-align:center;word-break:break-all;max-width:280px"></div>
    <div style="display:flex;gap:.625rem;width:100%">
      <button onclick="copyQrUrl()" style="flex:1;padding:.7rem;border:1.5px solid #E2E8F0;border-radius:10px;background:#fff;font-family:var(--font);font-weight:700;cursor:pointer;font-size:.85rem">
        <i class="fa fa-copy"></i> Copy Link
      </button>
      <button onclick="refreshQr()" style="flex:1;padding:.7rem;border:none;border-radius:10px;background:var(--primary);color:#fff;font-family:var(--font);font-weight:700;cursor:pointer;font-size:.85rem">
        <i class="fa fa-refresh"></i> New QR
      </button>
    </div>
    <div style="font-size:.75rem;color:#94A3B8;text-align:center">
      Customers scan to view menu on their phone
    </div>
  </div>
</div>

<!-- QR library -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

<script>
  const BASE = '<?= base_url() ?>';
  const CN = '<?= csrf_token() ?>';
  const CT = '<?= csrf_hash() ?>';
  let currentQrTableId = null;
  let currentQrToken = null;

  // Clock
  setInterval(() => {
    const el = document.getElementById('piClock');
    if (el) el.textContent = new Date().toLocaleTimeString('en-IN', {
      hour: '2-digit',
      minute: '2-digit',
      second: '2-digit'
    });
  }, 1000);

  // Collapsing large-title on scroll (native app feel)
  (function() {
    const map = document.getElementById('piMap');
    const shell = document.getElementById('piNavshell');
    if (!map || !shell) return;
    map.addEventListener('scroll', () => {
      if (map.scrollTop > 28) shell.classList.add('is-collapsed');
      else shell.classList.remove('is-collapsed');
    }, {
      passive: true
    });
  })();

  // Sheet helpers
  function openSheet(id) {
    document.getElementById('bg' + id.slice(1))?.classList.add('on');
    document.getElementById(id).classList.add('on');
    document.body.style.overflow = 'hidden';
  }

  function closeSheet(id, bg) {
    document.getElementById(id)?.classList.remove('on');
    document.getElementById(bg || '')?.classList.remove('on');
    document.body.style.overflow = '';
  }

  function closeBg(bg, id) {
    closeSheet(id, bg);
  }

  function postApi(url, data) {
    return fetch(url, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: new URLSearchParams({
        [CN]: CT,
        ...data
      })
    }).then(r => r.json());
  }

  // ── Table click handler ──────────────────────────────────
  function tableClick(t) {
    if (t.status === 'available') {
      showAvailableSheet(t);
    } else if (t.status === 'occupied') {
      showOccupiedSheet(t);
    } else if (t.status === 'booked') {
      showBookedSheet(t);
    } else {
      showOtherSheet(t);
    }
  }

  // ── Available table — show actions ───────────────────────
  function showAvailableSheet(t) {
    document.getElementById('sActionTitle').textContent = '🪑 Table ' + t.num;
    document.getElementById('sActionBody').innerHTML = `
    <div style="display:flex;flex-direction:column;gap:.6rem">
      <a href="${BASE}pos/new-order/dine_in?table=${t.id}" class="pi-action-card primary">
        <div class="pi-action-icon" style="background:#FFF0EB;color:var(--primary)"><i class="fa fa-utensils"></i></div>
        <div class="pi-action-info"><div class="pi-action-title">New Dine-in Order</div><div class="pi-action-sub">Table ${t.num} · Start taking order now</div></div>
        <i class="fa fa-chevron-right" style="color:#CBD5E1;font-size:.8rem"></i>
      </a>
      <button onclick="startBooking(${t.id},'${t.num}')" class="pi-action-card">
        <div class="pi-action-icon" style="background:#F5F3FF;color:#7C3AED"><i class="fa fa-calendar-plus"></i></div>
        <div class="pi-action-info"><div class="pi-action-title">Book Table</div><div class="pi-action-sub">Reserve for a future customer</div></div>
        <i class="fa fa-chevron-right" style="color:#CBD5E1;font-size:.8rem"></i>
      </button>
      <button onclick="showQrSheet(${t.id},'${t.num}','${t.qr_token}')" class="pi-action-card">
        <div class="pi-action-icon" style="background:#EFF6FF;color:#2563EB"><i class="fa fa-qrcode"></i></div>
        <div class="pi-action-info"><div class="pi-action-title">Show QR Code</div><div class="pi-action-sub">Customer scans to see menu</div></div>
        <i class="fa fa-chevron-right" style="color:#CBD5E1;font-size:.8rem"></i>
      </button>
    </div>
    <style>
    .pi-action-card { display:flex;align-items:center;gap:.875rem;padding:.875rem;border:1.5px solid #E2E8F0;border-radius:14px;text-decoration:none;color:#0F172A;background:#fff;cursor:pointer;width:100%;font-family:var(--font);text-align:left;transition:border-color .15s; }
    .pi-action-card:hover { border-color:var(--primary); }
    .pi-action-card.primary { border-color:var(--primary);background:#FFF8F5; }
    .pi-action-icon { width:44px;height:44px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0; }
    .pi-action-info { flex:1;min-width:0; }
    .pi-action-title { font-weight:800;font-size:.875rem; }
    .pi-action-sub { font-size:.72rem;color:#64748B;margin-top:.15rem; }
    </style>`;
    openSheet('sAction');
  }

  // ── Occupied table — show orders ─────────────────────────
  function showOccupiedSheet(t) {
    document.getElementById('sOrdersTitle').textContent = '🍽 Table ' + t.num + ' — Active Orders';
    document.getElementById('sOrdersBody').innerHTML = '<div style="text-align:center;padding:2rem;color:#94A3B8"><i class="fa fa-spinner fa-spin fa-lg"></i><br><br>Loading orders...</div>';
    document.getElementById('sOrdersFoot').innerHTML = '';
    openSheet('sOrders');

    fetch(BASE + 'pos/table-orders/' + t.id)
      .then(r => r.json()).then(orders => {
        if (!orders.length) {
          document.getElementById('sOrdersBody').innerHTML = '<div style="text-align:center;padding:2.5rem;color:#94A3B8"><i class="fa fa-receipt fa-2x" style="margin-bottom:.75rem;display:block"></i>No active orders at this table.</div>';
          document.getElementById('sOrdersFoot').innerHTML = `
          <div style="display:flex;gap:.5rem;width:100%;">
              <a href="${BASE}pos/new-order/dine_in?table=${t.id}"
                style="flex:1;padding:.75rem;background:var(--primary);color:#fff;border:none;border-radius:10px;font-weight:800;text-align:center;text-decoration:none;font-size:.9rem;display:flex;align-items:center;justify-content:center;gap:.4rem">
                  <i class="fa fa-plus"></i> New Order
              </a>
              <button onclick="cancelTableBooking(${t.id})"
                  style="flex:1;padding:.75rem;background:#EF4444;color:#fff;border:none;border-radius:10px;font-weight:800;font-size:.9rem;display:flex;align-items:center;justify-content:center;gap:.4rem;cursor:pointer">
                  <i class="fa fa-ban"></i> Cancel Table
              </button>
          </div>`;
          return;
        }

        const statusClr = {
          pending: '#F59E0B',
          confirmed: '#3B82F6',
          preparing: '#8B5CF6',
          ready: '#22C55E',
          served: '#10B981'
        };
        document.getElementById('sOrdersBody').innerHTML = orders.map(o => {
          const isPaid = o.payment_status === 'paid';
          const kotBadge = parseInt(o.kot_count || 0) > 0 ? `<span style="font-size:.58rem;background:${isPaid?'#DCFCE7':'#FEF3C7'};color:${isPaid?'#15803D':'#92400E'};padding:.1rem .35rem;border-radius:8px;font-weight:800">${isPaid?'✓ PAID':'KOT×'+o.kot_count+' UNPAID'}</span>` : `<span style="font-size:.58rem;background:#FEF3C7;color:#92400E;padding:.1rem .35rem;border-radius:8px;font-weight:800">UNPAID</span>`;
          return `
        <div style="display:flex;align-items:center;gap:.625rem;padding:.75rem 1rem;border-bottom:1px solid #F1F5F9;border-left:3px solid ${isPaid?'#22C55E':'#F59E0B'}">
          <div style="flex:1;min-width:0">
            <div style="font-weight:800;font-size:.875rem;display:flex;align-items:center;gap:.4rem">
              ${o.order_number} ${kotBadge}
            </div>
            <div style="font-size:.7rem;color:#94A3B8;margin-top:.2rem">${o.items_count} items · ${o.time_ago}</div>
          </div>
          <span style="font-size:.65rem;font-weight:800;padding:.2rem .45rem;border-radius:8px;background:${statusClr[o.status]||'#E2E8F0'}22;color:${statusClr[o.status]||'#64748B'}">${o.status.charAt(0).toUpperCase()+o.status.slice(1)}</span>
          <div style="text-align:right;flex-shrink:0">
            <div style="font-weight:900;font-size:.9rem;color:var(--primary)">₹${parseFloat(o.total_amount).toFixed(0)}</div>
          </div>
          <div style="display:flex;gap:.3rem">
            <a href="${BASE}pos/order/${o.id}" style="padding:.35rem .6rem;background:var(--primary);color:#fff;border-radius:8px;font-size:.72rem;font-weight:800;text-decoration:none">Open</a>
            <a href="${BASE}pos/new-order/dine_in?table=${t.id}&add_to=${o.id}" style="padding:.35rem .6rem;background:#F5F3FF;color:#6D28D9;border-radius:8px;font-size:.72rem;font-weight:800;text-decoration:none;border:1px solid #DDD6FE" title="Add more items to this order">+Round</a>
          </div>
        </div>`;
        }).join('');

        // Footer: total amount + new order button
        const total = orders.reduce((s, o) => s + parseFloat(o.total_amount), 0);
        document.getElementById('sOrdersFoot').innerHTML = `
        <div style="flex:1;display:flex;gap:.5rem">
          <a href="${BASE}pos/new-order/dine_in?table=${t.id}" style="flex:1;padding:.7rem;border:1.5px solid #E2E8F0;border-radius:10px;font-weight:700;text-align:center;text-decoration:none;color:#334155;background:#fff;font-size:.82rem;display:flex;align-items:center;justify-content:center;gap:.35rem"><i class="fa fa-plus"></i> New Order</a>
          <div style="flex:1;padding:.7rem;border-radius:10px;background:#0F172A;color:#fff;font-weight:900;text-align:center;font-size:.875rem">₹${total.toFixed(2)}</div>
        </div>`;
      }).catch(() => {
        document.getElementById('sOrdersBody').innerHTML = '<div style="color:#EF4444;text-align:center;padding:1.5rem">Failed to load orders</div>';
      });
  }

  function cancelTableBooking(tableId) {
    if (!confirm('Are you sure you want to make this table available?')) {
      return;
    }
    fetch(BASE + 'pos/table/cancel-booking/' + tableId, {
        method: 'POST',
        headers: {
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: new URLSearchParams({
          '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
        })
      })
      .then(r => r.json())
      .then(res => {
        if (res.success) {
          showToast('Table booking cancelled successfully.', 'success');
          location.reload(setInterval(() => {}, 1000));
        } else {
          showToast(res.message || 'Unable to cancel table.', 'info');
        }
      })
      .catch(() => {
        showToast('Something went wrong.', 'error');
      });
  }

  // ── Booked table — show booking info ─────────────────────
  function showBookedSheet(t) {
    document.getElementById('sActionTitle').textContent = '📅 Table ' + t.num + ' — Booked';
    const forTime = t.booked_for ? new Date(t.booked_for).toLocaleString('en-IN', {
      day: '2-digit',
      month: 'short',
      hour: '2-digit',
      minute: '2-digit'
    }) : '—';
    document.getElementById('sActionBody').innerHTML = `
    <div style="background:#F5F3FF;border:1.5px solid #A78BFA;border-radius:12px;padding:1rem;margin-bottom:1rem">
      <div style="display:flex;align-items:center;gap:.75rem">
        <div style="width:44px;height:44px;border-radius:12px;background:#EDE9FE;color:#6D28D9;display:flex;align-items:center;justify-content:center;font-size:1.2rem;flex-shrink:0">📅</div>
        <div>
          <div style="font-weight:900;font-size:1rem;color:#4C1D95">${t.booked_name || 'Guest'}</div>
          ${t.booked_phone ? `<div style="font-size:.78rem;color:#6D28D9;margin-top:.15rem"><i class="fa fa-phone"></i> ${t.booked_phone}</div>` : ''}
          <div style="font-size:.78rem;color:#7C3AED;margin-top:.1rem"><i class="fa fa-clock"></i> ${forTime}</div>
          ${t.booked_note ? `<div style="font-size:.75rem;color:#7C3AED;margin-top:.15rem;font-style:italic">"${t.booked_note}"</div>` : ''}
        </div>
      </div>
    </div>
    <div style="display:flex;flex-direction:column;gap:.5rem">
      <a href="${BASE}pos/new-order/dine_in?table=${t.id}" style="display:flex;align-items:center;gap:.75rem;padding:.875rem;background:#FFF0EB;border:1.5px solid var(--primary);border-radius:12px;text-decoration:none;color:var(--primary);font-weight:800">
        <i class="fa fa-utensils"></i> Start Order for This Booking
      </a>
      <button onclick="cancelBookingNow(${t.id})" style="display:flex;align-items:center;gap:.75rem;padding:.875rem;background:#fff;border:1.5px solid #FCA5A5;border-radius:12px;cursor:pointer;color:#B91C1C;font-weight:800;font-family:var(--font);width:100%">
        <i class="fa fa-calendar-xmark"></i> Cancel Booking
      </button>
      <button onclick="showQrSheet(${t.id},'${t.num}','${t.qr_token}')" style="display:flex;align-items:center;gap:.75rem;padding:.875rem;background:#fff;border:1.5px solid #E2E8F0;border-radius:12px;cursor:pointer;color:#334155;font-weight:700;font-family:var(--font);width:100%">
        <i class="fa fa-qrcode"></i> Show QR Code
      </button>
    </div>`;
    openSheet('sAction');
  }

  // ── Other status (reserved/cleaning) ────────────────────
  function showOtherSheet(t) {
    const icons = {
      reserved: '📋',
      cleaning: '🧹',
      inactive: '🚫'
    };
    document.getElementById('sActionTitle').textContent = (icons[t.status] || '🪑') + ' Table ' + t.num;
    document.getElementById('sActionBody').innerHTML = `
    <div style="text-align:center;padding:1.5rem 0">
      <div style="font-size:3rem;margin-bottom:.75rem">${icons[t.status]||'🪑'}</div>
      <div style="font-weight:700;font-size:1rem;margin-bottom:.4rem">Table is ${t.status}</div>
      <div style="font-size:.82rem;color:#94A3B8;margin-bottom:1.5rem">You can still start an order if needed.</div>
      <a href="${BASE}pos/new-order/dine_in?table=${t.id}" style="display:inline-flex;align-items:center;gap:.5rem;padding:.75rem 1.5rem;background:var(--primary);color:#fff;border-radius:10px;font-weight:800;text-decoration:none">
        <i class="fa fa-utensils"></i> Start Order Anyway
      </a>
    </div>`;
    openSheet('sAction');
  }

  // ── Booking ──────────────────────────────────────────────
  function startBooking(tableId, tableNum) {
    document.getElementById('bookTableId').value = tableId;
    document.getElementById('sBookTitle').textContent = '📅 Book Table ' + tableNum;
    document.getElementById('bookName').value = '';
    document.getElementById('bookPhone').value = '';
    document.getElementById('bookNote').value = '';
    // Set default booking time to 1 hour from now
    const d = new Date();
    d.setHours(d.getHours() + 1);
    d.setSeconds(0);
    document.getElementById('bookFor').value = d.toISOString().slice(0, 16);
    closeSheet('sAction', 'bgAction');
    setTimeout(() => openSheet('sBook'), 200);
  }

  function confirmBooking() {
    const id = document.getElementById('bookTableId').value;
    const name = document.getElementById('bookName').value.trim();
    if (!name) {
      alert('Please enter guest name');
      return;
    }
    postApi(BASE + 'pos/table/book/' + id, {
      name: name,
      phone: document.getElementById('bookPhone').value,
      booked_for: document.getElementById('bookFor').value,
      note: document.getElementById('bookNote').value,
    }).then(d => {
      if (d.success) {
        closeSheet('sBook', 'bgBook');
        showToast('Table booked for ' + name, 'success');
        setTimeout(() => location.reload(), 700);
      } else showToast('Failed to book', 'error');
    });
  }

  function cancelBookingNow(tableId) {
    if (!confirm('Cancel this booking? Table will be marked as available.')) return;
    postApi(BASE + 'pos/table/cancel-booking/' + tableId, {})
      .then(d => {
        if (d.success) {
          closeSheet('sAction', 'bgAction');
          showToast('Booking cancelled', 'success');
          setTimeout(() => location.reload(), 700);
        }
      });
  }

  // ── QR Code ──────────────────────────────────────────────
  function showQrSheet(tableId, tableNum, token) {
    currentQrTableId = tableId;
    currentQrToken = token;
    document.getElementById('sQrTitle').textContent = '🔲 Table ' + tableNum + ' — QR Code';
    document.getElementById('qrCanvas').innerHTML = '';

    closeSheet('sAction', 'bgAction');
    setTimeout(() => {
      openSheet('sQr');
      renderQr(token);
    }, 200);
  }

  function renderQr(token) {
    document.getElementById('qrCanvas').innerHTML = '';
    if (!token) {
      document.getElementById('qrCanvas').innerHTML = '<div style="padding:2rem;color:#94A3B8;font-size:.85rem">No QR generated yet. Click "New QR" to generate.</div>';
      document.getElementById('qrUrl').textContent = '';
      return;
    }
    const url = BASE + 'menu/table/' + token;
    document.getElementById('qrUrl').textContent = url;
    new QRCode(document.getElementById('qrCanvas'), {
      text: url,
      width: 220,
      height: 220,
      colorDark: '#0F172A',
      colorLight: '#ffffff',
      correctLevel: QRCode.CorrectLevel.M,
    });
  }

  function copyQrUrl() {
    const url = document.getElementById('qrUrl').textContent;
    navigator.clipboard?.writeText(url).then(() => showToast('Link copied!', 'success')).catch(() => {
      const el = document.createElement('textarea');
      el.value = url;
      document.body.appendChild(el);
      el.select();
      document.execCommand('copy');
      document.body.removeChild(el);
      showToast('Link copied!', 'success');
    });
  }

  function refreshQr() {
    if (!currentQrTableId) return;
    fetch(BASE + 'admin/tables/generate-qr/' + currentQrTableId, {
        headers: {
          'X-Requested-With': 'XMLHttpRequest'
        }
      })
      .then(r => r.json()).then(d => {
        if (d.success) {
          currentQrToken = d.token;
          renderQr(d.token);
          showToast('New QR generated', 'success');
        }
      });
  }

  // ── Toast ────────────────────────────────────────────────
  function showToast(msg, type = 'info') {
    const clr = {
      success: '#22C55E',
      error: '#EF4444',
      warning: '#F59E0B',
      info: '#3B82F6'
    };
    const ic = {
      success: 'fa-check-circle',
      error: 'fa-circle-exclamation',
      warning: 'fa-triangle-exclamation',
      info: 'fa-circle-info'
    };
    const t = document.createElement('div');
    t.style.cssText = `position:fixed;bottom:calc(5rem + var(--sab));left:50%;transform:translateX(-50%);background:${clr[type]||clr.info};color:#fff;padding:.6rem 1.2rem;border-radius:24px;font-weight:700;font-size:.82rem;z-index:9999;display:flex;align-items:center;gap:.4rem;box-shadow:0 4px 16px rgba(0,0,0,.2);white-space:nowrap`;
    t.innerHTML = `<i class="fa ${ic[type]||ic.info}"></i>${msg}`;
    document.body.appendChild(t);
    setTimeout(() => {
      t.style.opacity = '0';
      t.style.transition = 'opacity .3s';
      setTimeout(() => t.remove(), 300);
    }, 2500);
  }

  // ── Auto-refresh active orders count ────────────────────
  setInterval(() => {
    fetch(BASE + 'pos/active-orders').then(r => r.json()).then(orders => {
      const el = document.getElementById('piStatActive');
      if (el) el.textContent = orders.length;
    }).catch(() => {});
  }, 30000);

  // ── Customer QR order notification polling ───────────────
  let custOrders = [];
  let prevCustCount = 0;

  function pollCustomerOrders() {
    fetch(BASE + 'pos/order/pending-customer', {
        headers: {
          'X-Requested-With': 'XMLHttpRequest'
        }
      })
      .then(r => r.json()).then(d => {
        if (!d.success) return;
        custOrders = d.orders || [];
        const cnt = d.count || 0;
        const badge = document.getElementById('custOrdBadge');
        const btn = document.getElementById('custOrdBtn');
        const tabDot = document.getElementById('tabOrdDot');
        if (badge) {
          badge.textContent = cnt;
          badge.style.display = cnt > 0 ? 'flex' : 'none';
        }
        if (btn) {
          btn.style.background = cnt > 0 ? '#EF4444' : 'rgba(255,255,255,.08)';
          btn.style.color = cnt > 0 ? '#fff' : 'rgba(255,255,255,.85)';
        }
        if (tabDot) tabDot.style.display = cnt > 0 ? 'block' : 'none';
        // Auto-notify with toast
        if (cnt > 0 && document.hidden === false) {
          playOrderSound();
          const existing = document.getElementById('custToast');
          if (!existing) {
            const t = document.createElement('div');
            t.id = 'custToast';
            t.style.cssText = 'position:fixed;top:calc(.75rem + var(--sat));right:1rem;background:#EF4444;color:#fff;padding:.6rem 1rem;border-radius:12px;font-weight:800;font-size:.8rem;z-index:999;cursor:pointer;box-shadow:0 4px 16px rgba(239,68,68,.4);display:flex;align-items:center;gap:.5rem;animation:slidein .3s ease';
            t.innerHTML = '<i class="fa fa-bell fa-shake"></i> ' + cnt + ' new customer order' + (cnt > 1 ? 's' : '') + ' waiting!';
            t.onclick = () => {
              openCustOrders();
              t.remove();
            };
            document.body.appendChild(t);
            setTimeout(() => t.remove(), 8000);
          }
        }
        prevCustCount = cnt;
      }).catch(() => {});
  }
  pollCustomerOrders();
  setInterval(pollCustomerOrders, 12000);

  // ── Notification sound (Web Audio API — no file needed) ──
  function playOrderSound() {
    try {
      const ctx = new(window.AudioContext || window.webkitAudioContext)();
      const master = ctx.createGain();
      master.gain.value = 1;
      master.connect(ctx.destination);

      // Four-note ascending chime: G4 → B4 → D5 → G5 (resolves up an octave, feels celebratory)
      const notes = [
        [392, 0],
        [494, 0.14],
        [587, 0.28],
        [784, 0.46]
      ];

      notes.forEach(([freq, delay], i) => {
        const t0 = ctx.currentTime + delay;
        const isLast = i === notes.length - 1;
        const peak = isLast ? 0.85 : 0.7; // louder overall, accent on final note
        const tail = isLast ? 1.4 : 0.85; // long shimmering tail on the last note

        // Fundamental tone
        const osc = ctx.createOscillator();
        const gain = ctx.createGain();
        osc.type = 'triangle'; // warmer/rounder than plain sine
        osc.frequency.setValueAtTime(freq, t0);
        gain.gain.setValueAtTime(0, t0);
        gain.gain.linearRampToValueAtTime(peak, t0 + 0.015);
        gain.gain.exponentialRampToValueAtTime(0.001, t0 + tail);
        osc.connect(gain);
        gain.connect(master);
        osc.start(t0);
        osc.stop(t0 + tail + 0.05);

        // Soft octave overtone for a "bell" shimmer
        const osc2 = ctx.createOscillator();
        const gain2 = ctx.createGain();
        osc2.type = 'sine';
        osc2.frequency.setValueAtTime(freq * 2, t0);
        gain2.gain.setValueAtTime(0, t0);
        gain2.gain.linearRampToValueAtTime(peak * 0.35, t0 + 0.015);
        gain2.gain.exponentialRampToValueAtTime(0.001, t0 + tail * 0.7);
        osc2.connect(gain2);
        gain2.connect(master);
        osc2.start(t0);
        osc2.stop(t0 + tail * 0.7 + 0.05);
      });
    } catch (e) {}
  }

  function openCustOrders() {
    const modal = document.getElementById('custOrderModal');
    renderCustOrders();
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
  }

  function closeCustOrders() {
    document.getElementById('custOrderModal').style.display = 'none';
    document.body.style.overflow = '';
  }

  function renderCustOrders() {
    const body = document.getElementById('custOrderBody');
    if (!custOrders.length) {
      body.innerHTML = '<div style="text-align:center;padding:3rem 1rem;color:#94A3B8"><i class="fa fa-bell-slash fa-2x" style="margin-bottom:1rem;display:block"></i><div style="font-weight:700">No pending customer orders</div><div style="font-size:.8rem;margin-top:.4rem">Orders placed via QR code will appear here</div></div>';
      return;
    }
    body.innerHTML = custOrders.map(o => `
    <div style="border:1.5px solid #E2E8F0;border-radius:14px;padding:1rem;margin-bottom:.75rem;background:#fff">
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.625rem">
        <div>
          <span style="font-weight:900;font-size:.9rem">#${o.order_number}</span>
          <span style="background:#F5F3FF;color:#6D28D9;font-size:.62rem;font-weight:800;padding:.15rem .4rem;border-radius:8px;margin-left:.4rem">📱 QR Order</span>
        </div>
        <span style="font-size:.72rem;color:#94A3B8">${o.waiting}</span>
      </div>
      <div style="font-size:.78rem;color:#64748B;margin-bottom:.5rem">
        <i class="fa fa-chair" style="color:var(--primary)"></i> Table ${o.table_number}
        &nbsp;·&nbsp; <i class="fa fa-user"></i> ${o.customer_name || 'Guest'}
        ${o.customer_phone ? '&nbsp;·&nbsp; <i class="fa fa-phone"></i> ' + o.customer_phone : ''}
      </div>
      <div style="background:#F8FAFC;border-radius:8px;padding:.625rem;margin-bottom:.625rem">
        ${o.items.map(it=>`<div style="display:flex;justify-content:space-between;font-size:.78rem;padding:.2rem 0"><span>${it.name} ×${it.quantity}${it.notes?' <em style=\"color:#94A3B8\">('+it.notes+')</em>':''}</span><span style="font-weight:700">₹${parseFloat(it.unit_price*it.quantity).toFixed(0)}</span></div>`).join('')}
        <div style="display:flex;justify-content:space-between;font-weight:900;font-size:.85rem;padding-top:.5rem;border-top:1px solid #E2E8F0;margin-top:.35rem"><span>Total</span><span style="color:var(--primary)">₹${parseFloat(o.total_amount).toFixed(2)}</span></div>
      </div>
      <div style="display:flex;gap:.5rem">
        <button onclick="confirmOrder(${o.id})" style="flex:2;padding:.625rem;background:#22C55E;color:#fff;border:none;border-radius:10px;font-weight:800;font-size:.82rem;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:.4rem;font-family:var(--font)">
          <i class="fa fa-check"></i> Confirm & Send to Kitchen
        </button>
        <button onclick="rejectOrder(${o.id})" style="flex:1;padding:.625rem;background:#FFF1F2;color:#B91C1C;border:1.5px solid #FCA5A5;border-radius:10px;font-weight:700;font-size:.78rem;cursor:pointer;font-family:var(--font)">
          <i class="fa fa-times"></i> Reject
        </button>
      </div>
    </div>`).join('');
  }

  function confirmOrder(id) {
    postApi(BASE + 'pos/order/confirm-customer/' + id, {})
      .then(d => {
        if (d.success) {
          showToast('Order confirmed! Sent to kitchen 🍳', 'success');
          pollCustomerOrders();
          setTimeout(renderCustOrders, 800);
          location.reload(setInterval(() => {}, 1000));
          if (d.redirect) setTimeout(() => window.open(d.redirect, '_blank'), 1200);
        }
      });
  }

  function rejectOrder(id) {
    if (!confirm('Reject this customer order? They will see it as cancelled.')) return;
    postApi(BASE + 'pos/order/reject-customer/' + id, {})
      .then(d => {
        if (d.success) {
          showToast('Order rejected', 'warning');
          pollCustomerOrders();
          setTimeout(renderCustOrders, 800);
          location.reload();
        }
      });
  }
</script>

<?php $this->endSection(); ?>