<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
<title><?= $title ?? 'RestoCRM' ?> — <?= $restaurantName ?? 'SaaS Restaurant Management' ?></title>
<meta name="csrf-token" data-name="<?= csrf_token() ?>" content="<?= csrf_hash() ?>">
<meta name="theme-color" content="<?= $themeColor ?? '#FF6B35' ?>">

<!-- Fonts -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
<!-- Icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<!-- Main CSS -->
<link rel="stylesheet" href="<?= base_url('css/main.css') ?>">
<?= $styles ?? '' ?>
</head>
<body class="<?= $bodyClass ?? '' ?>">
<?php $userRole = $userRole ?? 'guest'; ?>

<!-- Mobile Header -->
<header class="mobile-header" id="mobileHeader">
  <button class="sidebar-toggle" id="sidebarToggle">
    <span></span><span></span><span></span>
  </button>
  <div class="mobile-logo">
    <span class="logo-icon">🍽</span>
    <span><?= $restaurantName ?? 'RestoCRM' ?></span>
  </div>
  <div class="mobile-actions">
    <a href="<?= base_url('pos') ?>" class="btn-pos-mobile"><i class="fa fa-cash-register"></i></a>
    <button class="btn-notif" id="notifBtn"><i class="fa fa-bell"></i><span class="badge" id="notifCount">0</span></button>
  </div>
</header>

<!-- Sidebar Overlay -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- Sidebar -->
<aside class="sidebar" id="sidebar">
  <div class="sidebar-header">
    <div class="brand">
      <div class="brand-icon">🍽</div>
      <div class="brand-text">
        <strong><?= $restaurantName ?? 'RestoCRM' ?></strong>
        <small><?= $branchName ?? 'All Branches' ?></small>
      </div>
    </div>
    <button class="sidebar-close" id="sidebarClose"><i class="fa fa-times"></i></button>
  </div>

  <!-- User Info -->
  <div class="sidebar-user">
    <div class="user-avatar"><?= substr($userName ?? 'U', 0, 1) ?></div>
    <div>
      <div class="user-name"><?= $userName ?? 'User' ?></div>
      <div class="user-role"><?= $userRole ?? 'Staff' ?></div>
    </div>
  </div>

  <nav class="sidebar-nav">
    <?php if ($userRole === 'super_admin'): ?>
    <!-- SUPER ADMIN NAV -->
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
    <a href="<?= base_url('super/settings') ?>" class="nav-item <?= active('super/settings*') ?>">
      <i class="fa fa-gear"></i><span>Settings</span>
    </a>
    <a href="<?= base_url('super/activity-log') ?>" class="nav-item">
      <i class="fa fa-list-check"></i><span>Activity Log</span>
    </a>

    <?php elseif (in_array($userRole, ['restaurant_admin','branch_manager'])): ?>
    <!-- RESTAURANT ADMIN NAV -->
    <div class="nav-section">OVERVIEW</div>
    <a href="<?= base_url('admin/dashboard') ?>" class="nav-item <?= active('admin/dashboard') ?>">
      <i class="fa fa-gauge-high"></i><span>Dashboard</span>
    </a>
    <a href="<?= base_url('pos') ?>" class="nav-item nav-pos">
      <i class="fa fa-cash-register"></i><span>Open POS</span>
      <span class="nav-badge">LIVE</span>
    </a>
    <div class="nav-section">RESTAURANT</div>
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
    <!-- CASHIER / WAITER NAV -->
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
    <a href="<?= base_url('pos/kitchen') ?>" class="nav-item">
      <i class="fa fa-fire-burner"></i><span>Kitchen Display</span>
    </a>
    <div class="nav-section">SHIFT</div>
    <a href="<?= base_url('pos/shift/summary') ?>" class="nav-item">
      <i class="fa fa-clock"></i><span>Shift Summary</span>
    </a>
    <?php endif; ?>
  </nav>

  <div class="sidebar-footer">
    <a href="<?= base_url('admin/settings') ?>" class="nav-item"><i class="fa fa-gear"></i><span>Settings</span></a>
    <a href="<?= base_url('logout') ?>" class="nav-item nav-logout"><i class="fa fa-right-from-bracket"></i><span>Logout</span></a>
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
            <a href="<?= $crumb['url'] ?>"><?= $crumb['label'] ?></a>
            <span>/</span>
          <?php else: ?>
            <span class="active"><?= $crumb['label'] ?></span>
          <?php endif; ?>
        <?php endforeach; ?>
      </nav>
      <?php endif; ?>
      <h1 class="page-title"><?= $pageTitle ?></h1>
    </div>
    <div class="page-header-right">
      <?= $headerActions ?? '' ?>
    </div>
  </div>
  <?php endif; ?>

  <!-- Flash Messages -->
  <?php if (session()->getFlashdata('success')): ?>
  <div class="alert alert-success"><i class="fa fa-check-circle"></i> <?= session()->getFlashdata('success') ?></div>
  <?php endif; ?>
  <?php if (session()->getFlashdata('error')): ?>
  <div class="alert alert-error"><i class="fa fa-circle-exclamation"></i> <?= session()->getFlashdata('error') ?></div>
  <?php endif; ?>

  <!-- Page Body -->
  <div class="page-body">
    <?= $this->renderSection('content') ?>
  </div>
</main>

<!-- Scripts -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="<?= base_url('js/main.js') ?>"></script>
<?= $scripts ?? '' ?>
</body>
</html>
