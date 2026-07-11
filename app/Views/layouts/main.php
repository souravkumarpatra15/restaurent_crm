<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
<title><?= $pageTitle ?? 'DinoviX' ?> — <?= $restaurantName ?? session('restaurant_name') ?? 'DinoviX' ?></title>
<meta name="csrf-token" data-name="<?= csrf_token() ?>" content="<?= csrf_hash() ?>">
<meta name="theme-color" content="<?= session('theme_color') ?? '#FF6B35' ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="<?= base_url('css/main.css') ?>">
<link rel="icon" type="image/png" href="<?= base_url('images/favicon.png') ?>">
<?= $styles ?? '' ?>
</head>
<body>
<?php $userRole = session('role_slug') ?? 'guest'; ?>

<!-- Impersonation Bar -->
<?php if (session('impersonating')): ?>
<div style="background:#D69E2E;color:#fff;text-align:center;padding:.5rem 1rem;font-size:.82rem;font-weight:600;position:fixed;top:0;left:0;right:0;z-index:2000;display:flex;align-items:center;justify-content:center;gap:.75rem">
  <i class="fa fa-eye"></i>
  Viewing as: <strong><?= esc(session('restaurant_name')) ?></strong> (Restaurant Admin)
  <a href="<?= base_url('back-to-super') ?>" style="color:#fff;text-decoration:underline;font-weight:700">← Back to Super Admin</a>
</div>
<style>body{padding-top:36px !important}</style>
<?php endif; ?>

<!-- Mobile Header -->
<header class="mobile-header" id="mobileHeader">
  <button class="sidebar-toggle" id="sidebarToggle"><span></span><span></span><span></span></button>
  <div class="mobile-logo">
    <img src="<?= base_url('images/logo.png') ?>" alt="DinoviX" style="height:28px">
  </div>
  <div class="mobile-actions">
    <?php if (in_array($userRole, ['restaurant_admin','branch_manager','cashier','waiter'])): ?>
    <a href="<?= base_url('pos') ?>" style="width:38px;height:38px;border-radius:50%;background:rgba(255,255,255,.1);color:#fff;display:flex;align-items:center;justify-content:center;font-size:.9rem"><i class="fa fa-cash-register"></i></a>
    <?php endif; ?>
    <button class="btn-notif" id="notifBtn" onclick="toggleNotifPanel()">
      <i class="fa fa-bell"></i>
      <span class="badge" id="notifCount" style="display:none">0</span>
    </button>
  </div>
</header>

<!-- Notification Dropdown Panel -->
<div id="notifPanel" style="display:none;position:fixed;top:56px;right:0;width:320px;max-height:420px;background:#fff;border-radius:0 0 12px 12px;box-shadow:var(--shadow-lg);z-index:800;overflow:hidden;flex-direction:column">
  <div style="padding:.75rem 1rem;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center">
    <span style="font-weight:700;font-size:.875rem">Notifications</span>
    <button onclick="markAllRead()" style="background:none;border:none;color:var(--primary);font-size:.78rem;cursor:pointer;font-weight:600">Mark all read</button>
  </div>
  <div id="notifList" style="overflow-y:auto;max-height:340px">
    <div style="text-align:center;padding:2rem;color:var(--text-muted);font-size:.85rem"><i class="fa fa-bell-slash"></i><br>No notifications</div>
  </div>
</div>

<!-- Sidebar Overlay -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- Sidebar -->
<aside class="sidebar" id="sidebar">
  <div class="sidebar-header">
    <div class="brand">
      <img src="<?= base_url('images/favicon.png') ?>" alt="" style="width:32px;height:32px;border-radius:8px">
      <div class="brand-text">
        <strong>DinoviX</strong>
        <small><?= esc(session('branch_name') ?? session('restaurant_name') ?? 'Platform') ?></small>
      </div>
    </div>
    <button class="sidebar-close" id="sidebarClose"><i class="fa fa-times"></i></button>
  </div>

  <div class="sidebar-user">
    <div class="user-avatar"><?= strtoupper(substr(session('user_name') ?? 'U', 0, 1)) ?></div>
    <div>
      <div class="user-name"><?= esc(session('user_name') ?? 'User') ?></div>
      <div class="user-role"><?= ucfirst(str_replace('_',' ', $userRole)) ?></div>
    </div>
  </div>

  <nav class="sidebar-nav">

    <?php if ($userRole === 'super_admin'): ?>
    <!-- ── SUPER ADMIN ── -->
    <div class="nav-section">OVERVIEW</div>
    <a href="<?= base_url('super/dashboard') ?>" class="nav-item <?= active('super/dashboard') ?>">
      <i class="fa fa-gauge-high"></i><span>Dashboard</span>
    </a>
    <div class="nav-section">MANAGEMENT</div>
    <a href="<?= base_url('super/restaurants') ?>" class="nav-item <?= active('super/restaurants*') ?>">
      <i class="fa fa-store"></i><span>Restaurants</span>
    </a>
    <a href="<?= base_url('super/plans') ?>" class="nav-item <?= active('super/plans*') ?>">
      <i class="fa fa-layer-group"></i><span>Plans</span>
    </a>
    <a href="<?= base_url('super/subscriptions') ?>" class="nav-item <?= active('super/subscriptions*') ?>">
      <i class="fa fa-credit-card"></i><span>Subscriptions</span>
    </a>
    <div class="nav-section">ANALYTICS</div>
    <a href="<?= base_url('super/reports/revenue') ?>" class="nav-item <?= active('super/reports*') ?>">
      <i class="fa fa-chart-line"></i><span>Revenue</span>
    </a>
    <div class="nav-section">SYSTEM</div>
    <a href="<?= base_url('super/notifications') ?>" class="nav-item <?= active('super/notifications*') ?>">
      <i class="fa fa-bell"></i><span>Notifications</span>
    </a>
    <a href="<?= base_url('super/settings') ?>" class="nav-item <?= active('super/settings*') ?>">
      <i class="fa fa-gear"></i><span>Settings</span>
    </a>
    <a href="<?= base_url('super/activity-log') ?>" class="nav-item <?= active('super/activity-log') ?>">
      <i class="fa fa-list-check"></i><span>Activity Log</span>
    </a>

    <?php elseif (in_array($userRole, ['restaurant_admin','branch_manager'])): ?>
    <!-- ── RESTAURANT ADMIN ── -->
    <div class="nav-section">OVERVIEW</div>
    <a href="<?= base_url('admin/dashboard') ?>" class="nav-item <?= active('admin/dashboard') ?>">
      <i class="fa fa-gauge-high"></i><span>Dashboard</span>
    </a>
    <a href="<?= base_url('pos') ?>" class="nav-item nav-pos">
      <i class="fa fa-cash-register"></i><span>Open POS</span>
      <span class="nav-badge">LIVE</span>
    </a>
    <div class="nav-section">SETUP</div>
    <a href="<?= base_url('admin/branches') ?>" class="nav-item <?= active('admin/branches*') ?>">
      <i class="fa fa-code-branch"></i><span>Branches</span>
    </a>
    <a href="<?= base_url('admin/users') ?>" class="nav-item <?= active('admin/users*') ?>">
      <i class="fa fa-users"></i><span>Staff</span>
    </a>
    <a href="<?= base_url('admin/menu/items') ?>" class="nav-item <?= active('admin/menu*') ?>">
      <i class="fa fa-utensils"></i><span>Menu</span>
    </a>
    <a href="<?= base_url('admin/tables') ?>" class="nav-item <?= active('admin/tables*') ?>">
      <i class="fa fa-chair"></i><span>Tables</span>
    </a>
    <div class="nav-section">SALES</div>
    <a href="<?= base_url('admin/orders') ?>" class="nav-item <?= active('admin/orders*') ?>">
      <i class="fa fa-receipt"></i><span>Orders</span>
    </a>
    <a href="<?= base_url('admin/customers') ?>" class="nav-item <?= active('admin/customers*') ?>">
      <i class="fa fa-person"></i><span>Customers</span>
    </a>
    <a href="<?= base_url('admin/reservations') ?>" class="nav-item <?= active('admin/reservations*') ?>">
      <i class="fa fa-calendar-check"></i><span>Reservations</span>
    </a>
    <div class="nav-section">FINANCE</div>
    <a href="<?= base_url('admin/expenses') ?>" class="nav-item <?= active('admin/expenses*') ?>">
      <i class="fa fa-wallet"></i><span>Expenses</span>
    </a>
    <a href="<?= base_url('admin/inventory') ?>" class="nav-item <?= active('admin/inventory*') ?>">
      <i class="fa fa-boxes-stacked"></i><span>Inventory</span>
    </a>
    <a href="<?= base_url('admin/coupons') ?>" class="nav-item <?= active('admin/coupons*') ?>">
      <i class="fa fa-ticket"></i><span>Coupons</span>
    </a>
    <div class="nav-section">REPORTS</div>
    <a href="<?= base_url('admin/reports/sales') ?>" class="nav-item <?= active('admin/reports*') ?>">
      <i class="fa fa-chart-bar"></i><span>Reports</span>
    </a>
    <div class="nav-section">SETTINGS</div>
    <a href="<?= base_url('admin/settings') ?>" class="nav-item <?= active('admin/settings*') ?>">
      <i class="fa fa-gear"></i><span>Settings</span>
    </a>

    <?php else: ?>
    <!-- ── CASHIER / WAITER / KITCHEN ── -->
    <div class="nav-section">POS</div>
    <a href="<?= base_url('pos') ?>" class="nav-item nav-pos <?= active('pos') ?>">
      <i class="fa fa-cash-register"></i><span>Table Map</span>
    </a>
    <a href="<?= base_url('pos/new-order/takeaway') ?>" class="nav-item">
      <i class="fa fa-bag-shopping"></i><span>Takeaway</span>
    </a>
    <a href="<?= base_url('pos/new-order/delivery') ?>" class="nav-item">
      <i class="fa fa-motorcycle"></i><span>Delivery</span>
    </a>
    <div class="nav-section">KITCHEN</div>
    <a href="<?= base_url('pos/kitchen') ?>" class="nav-item <?= active('pos/kitchen') ?>">
      <i class="fa fa-fire-burner"></i><span>Kitchen Display</span>
    </a>
    <div class="nav-section">SHIFT</div>
    <a href="<?= base_url('pos/shift/summary') ?>" class="nav-item <?= active('pos/shift/summary') ?>">
      <i class="fa fa-clock"></i><span>Shift Summary</span>
    </a>
    <?php endif; ?>

  </nav>

  <div class="sidebar-footer">
    <a href="<?= base_url('logout') ?>" class="nav-item nav-logout">
      <i class="fa fa-right-from-bracket"></i><span>Logout</span>
    </a>
  </div>
</aside>

<!-- Main Content -->
<main class="main-content" id="mainContent">

  <!-- Page Header -->
  <?php if (isset($pageTitle)): ?>
  <div class="page-header">
    <div class="page-header-left">
      <?php if (isset($breadcrumbs)): ?>
      <nav class="breadcrumb">
        <?php foreach ($breadcrumbs as $crumb): ?>
          <?php if (isset($crumb['url'])): ?>
            <a href="<?= $crumb['url'] ?>"><?= esc($crumb['label']) ?></a><span>/</span>
          <?php else: ?>
            <span class="active"><?= esc($crumb['label']) ?></span>
          <?php endif; ?>
        <?php endforeach; ?>
      </nav>
      <?php endif; ?>
      <h1 class="page-title"><?= $pageTitle ?></h1>
    </div>
    <div class="page-header-right"><?= $headerActions ?? '' ?></div>
  </div>
  <?php endif; ?>

  <!-- Flash Messages -->
  <?php if (session()->getFlashdata('success')): ?>
  <div class="alert alert-success" style="margin:.5rem 1rem 0"><i class="fa fa-check-circle"></i> <?= session()->getFlashdata('success') ?></div>
  <?php endif; ?>
  <?php if (session()->getFlashdata('error')): ?>
  <div class="alert alert-error" style="margin:.5rem 1rem 0"><i class="fa fa-circle-exclamation"></i> <?= session()->getFlashdata('error') ?></div>
  <?php endif; ?>

  <div class="page-body"><?= $this->renderSection('content') ?></div>
</main>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="<?= base_url('js/main.js') ?>"></script>
<script>
// Notification system
function toggleNotifPanel() {
  const p = document.getElementById('notifPanel');
  const isOpen = p.style.display === 'flex';
  p.style.display = isOpen ? 'none' : 'flex';
  p.style.flexDirection = 'column';
  if (!isOpen) loadNotifications();
}

function loadNotifications() {
  const role = '<?= $userRole ?>';
  const base = role === 'super_admin' ? 'super' : 'admin';
  fetch(`<?= base_url() ?>${base}/notifications/count`)
    .then(r=>r.json()).then(d=>{
      const badge = document.getElementById('notifCount');
      if (d.count > 0) { badge.textContent = d.count > 99 ? '99+' : d.count; badge.style.display=''; }
      else { badge.style.display = 'none'; }
    }).catch(()=>{});
}

function markAllRead() {
  const role = '<?= $userRole ?>';
  const base = role === 'super_admin' ? 'super' : 'admin';
  fetch(`<?= base_url() ?>${base}/notifications/mark-read`,{
    method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'},
    body:'<?= csrf_token() ?>=<?= csrf_hash() ?>'
  }).then(()=>{ document.getElementById('notifCount').style.display='none'; });
}

// Close notification panel on outside click
document.addEventListener('click', e => {
  const panel = document.getElementById('notifPanel');
  const btn   = document.getElementById('notifBtn');
  if (panel && !panel.contains(e.target) && !btn.contains(e.target)) {
    panel.style.display = 'none';
  }
});

// Poll notification count every 60s
loadNotifications();
setInterval(loadNotifications, 60000);
</script>
<?= $scripts ?? '' ?>
</body>
</html>
