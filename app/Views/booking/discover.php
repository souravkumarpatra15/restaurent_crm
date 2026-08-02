<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=no,viewport-fit=cover">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="mobile-web-app-capable" content="yes">
  <meta name="theme-color" content="#0F172A">
  <title>DinoviX — Book a Table</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    /* ── Reset & Base ── */
    *,
    *::before,
    *::after {
      box-sizing: border-box;
      margin: 0;
      padding: 0
    }

    html {
      height: 100%;
      -webkit-tap-highlight-color: transparent
    }

    body {
      font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
      background: #F8FAFC;
      color: #0F172A;
      min-height: 100%;
      overflow-x: hidden
    }

    a {
      text-decoration: none;
      color: inherit
    }

    img {
      display: block;
      max-width: 100%
    }

    button,
    input,
    select,
    textarea {
      font-family: inherit
    }

    /* ── CSS Variables ── */
    :root {
      --primary: #FF6B35;
      --primary-d: #E85A24;
      --primary-l: #FFF0EB;
      --dark: #0F172A;
      --dark-2: #1E293B;
      --dark-3: #334155;
      --text: #0F172A;
      --text-2: #334155;
      --text-m: #64748B;
      --text-l: #94A3B8;
      --bg: #F8FAFC;
      --card: #FFFFFF;
      --border: #E2E8F0;
      --success: #22C55E;
      --success-l: #F0FDF4;
      --radius: 14px;
      --radius-lg: 20px;
      --radius-xl: 28px;
      --shadow: 0 2px 12px rgba(0, 0, 0, .07);
      --shadow-lg: 0 8px 32px rgba(0, 0, 0, .12);
    }

    /* ── Mobile App Shell ── */
    .app-shell {
      display: flex;
      flex-direction: column;
      min-height: 100vh
    }

    /* ── Sticky Top Nav ── */
    .topnav {
      background: var(--dark);
      padding: calc(.875rem + env(safe-area-inset-top, .5rem)) 1rem .875rem;
      position: sticky;
      top: 0;
      z-index: 200;
    }

    .topnav-inner {
      display: flex;
      align-items: center;
      justify-content: space-between;
      max-width: 1100px;
      margin: 0 auto
    }

    .logo {
      display: flex;
      align-items: center;
      gap: .5rem
    }

    .logo-icon {
      width: 34px;
      height: 34px;
      border-radius: 10px;
      background: linear-gradient(135deg, var(--primary), #FF8C5A);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.1rem
    }

    .logo-text {
      font-weight: 900;
      font-size: 1rem;
      color: #fff
    }

    .logo-text span {
      color: var(--primary)
    }

    .nav-right {
      display: flex;
      align-items: center;
      gap: .625rem
    }

    .nav-btn {
      padding: .4rem .875rem;
      border-radius: 8px;
      background: rgba(255, 255, 255, .08);
      color: rgba(255, 255, 255, .8);
      font-size: .78rem;
      font-weight: 700;
      border: none;
      cursor: pointer;
      transition: background .15s
    }

    .nav-btn:hover {
      background: rgba(255, 255, 255, .15);
      color: #fff
    }

    .nav-btn.primary {
      background: var(--primary);
      color: #fff
    }

    .nav-btn.primary:hover {
      background: var(--primary-d)
    }

    /* ── Hero ── */
    .hero {
      background: linear-gradient(160deg, #0F172A 0%, #1E293B 50%, #0F172A 100%);
      padding: 2.5rem 1rem 4rem;
      position: relative;
      overflow: hidden;
    }

    .hero::before {
      content: '';
      position: absolute;
      inset: 0;
      background: radial-gradient(ellipse at 70% 50%, rgba(255, 107, 53, .18) 0%, transparent 65%);
      pointer-events: none;
    }

    .hero-inner {
      max-width: 680px;
      margin: 0 auto;
      position: relative;
      z-index: 1
    }

    .hero-badge {
      display: inline-flex;
      align-items: center;
      gap: .4rem;
      background: rgba(255, 107, 53, .15);
      border: 1px solid rgba(255, 107, 53, .3);
      color: var(--primary);
      padding: .3rem .875rem;
      border-radius: 20px;
      font-size: .72rem;
      font-weight: 800;
      text-transform: uppercase;
      letter-spacing: .06em;
      margin-bottom: 1rem;
    }

    .hero h1 {
      font-size: clamp(1.6rem, 6vw, 2.6rem);
      font-weight: 900;
      color: #fff;
      line-height: 1.15;
      margin-bottom: .75rem;
    }

    .hero h1 span {
      background: linear-gradient(135deg, var(--primary), #FF8C5A);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
    }

    .hero-sub {
      color: rgba(255, 255, 255, .65);
      font-size: .95rem;
      line-height: 1.55;
      margin-bottom: 2rem
    }

    /* ── Search Card ── */
    .search-card {
      background: #fff;
      border-radius: var(--radius-lg);
      padding: 1rem;
      box-shadow: 0 12px 48px rgba(0, 0, 0, .25);
    }

    .search-fields {
      display: flex;
      flex-direction: column;
      gap: .625rem
    }

    .sf {
      display: flex;
      align-items: center;
      gap: .625rem;
      background: #F8FAFC;
      border: 1.5px solid var(--border);
      border-radius: 10px;
      padding: .6rem .875rem;
      transition: border-color .2s;
    }

    .sf:focus-within {
      border-color: var(--primary);
      background: #fff
    }

    .sf i {
      color: var(--primary);
      font-size: .875rem;
      flex-shrink: 0;
      width: 16px;
      text-align: center
    }

    .sf input,
    .sf select {
      flex: 1;
      border: none;
      background: transparent;
      font-size: .9rem;
      color: var(--text);
      outline: none;
      min-width: 0;
    }

    .sf input::placeholder {
      color: var(--text-l)
    }

    .sf-label {
      font-size: .65rem;
      font-weight: 700;
      color: var(--text-m);
      text-transform: uppercase;
      letter-spacing: .05em;
      display: block;
      margin-bottom: .15rem
    }

    .sf-value {
      font-size: .875rem;
      color: var(--text);
      font-weight: 600
    }

    @media(min-width:600px) {
      .search-fields {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: .625rem
      }

      .sf-btn-wrap {
        grid-column: 1/-1
      }
    }

    .search-btn {
      width: 100%;
      padding: .8rem;
      border: none;
      border-radius: 10px;
      background: linear-gradient(135deg, var(--primary), #FF8C5A);
      color: #fff;
      font-size: .95rem;
      font-weight: 800;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: .5rem;
      transition: all .2s;
      box-shadow: 0 4px 16px rgba(255, 107, 53, .35);
    }

    .search-btn:hover {
      transform: translateY(-1px);
      box-shadow: 0 6px 24px rgba(255, 107, 53, .45)
    }

    /* ── Stats Strip ── */
    .stats-strip {
      background: var(--dark-2);
      padding: .875rem 1rem;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 2rem;
      flex-wrap: wrap;
    }

    .stat-item {
      text-align: center
    }

    .stat-num {
      font-size: 1.1rem;
      font-weight: 900;
      color: var(--primary)
    }

    .stat-txt {
      font-size: .68rem;
      color: rgba(255, 255, 255, .5);
      margin-top: .1rem
    }

    /* ── Main Content ── */
    .main {
      max-width: 1100px;
      margin: 0 auto;
      padding: 1.5rem 1rem 5rem
    }

    /* ── Filter Chips ── */
    .filter-row {
      display: flex;
      gap: .4rem;
      overflow-x: auto;
      scrollbar-width: none;
      padding-bottom: .25rem;
      margin-bottom: 1.25rem;
    }

    .filter-row::-webkit-scrollbar {
      display: none
    }

    .fchip {
      flex-shrink: 0;
      padding: .4rem .875rem;
      border-radius: 20px;
      border: 1.5px solid var(--border);
      background: #fff;
      font-size: .75rem;
      font-weight: 700;
      color: var(--text-m);
      cursor: pointer;
      transition: all .18s;
      white-space: nowrap;
    }

    .fchip.on,
    .fchip:hover {
      background: var(--primary);
      border-color: var(--primary);
      color: #fff
    }

    /* ── Section Header ── */
    .sec-hdr {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: .875rem
    }

    .sec-title {
      font-weight: 800;
      font-size: 1.05rem;
      display: flex;
      align-items: center;
      gap: .4rem
    }

    .sec-count {
      font-size: .75rem;
      font-weight: 500;
      color: var(--text-m);
      background: var(--border);
      padding: .15rem .6rem;
      border-radius: 20px
    }

    /* ── Restaurant Grid ── */
    .rest-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
      gap: .875rem
    }

    /* ── Restaurant Card ── */
    .rcard {
      background: #fff;
      border-radius: var(--radius-lg);
      overflow: hidden;
      box-shadow: var(--shadow);
      transition: all .22s;
      cursor: pointer;
      border: 1.5px solid transparent;
    }

    .rcard:hover {
      transform: translateY(-4px);
      box-shadow: var(--shadow-lg);
      border-color: rgba(255, 107, 53, .2)
    }

    .rcard:active {
      transform: scale(.98)
    }

    .rcard-img {
      height: 180px;
      position: relative;
      overflow: hidden;
      background: #E2E8F0
    }

    .rcard-img img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      transition: transform .3s
    }

    .rcard:hover .rcard-img img {
      transform: scale(1.04)
    }

    .rcard-img-ph {
      width: 100%;
      height: 100%;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      gap: .5rem;
      background: linear-gradient(135deg, #E2E8F0, #CBD5E1);
      font-size: 3rem;
    }

    .rcard-img-ph span {
      font-size: .72rem;
      color: var(--text-l);
      font-weight: 600
    }

    /* Badges on image */
    .img-badges {
      position: absolute;
      top: .625rem;
      left: .625rem;
      right: .625rem;
      display: flex;
      justify-content: space-between;
      align-items: flex-start
    }

    .avail-badge {
      padding: .25rem .7rem;
      border-radius: 20px;
      font-size: .65rem;
      font-weight: 800;
      backdrop-filter: blur(8px)
    }

    .avail-badge.yes {
      background: rgba(34, 197, 94, .9);
      color: #fff
    }

    .avail-badge.no {
      background: rgba(239, 68, 68, .85);
      color: #fff
    }

    .pay-badge {
      background: rgba(79, 70, 229, .9);
      color: #fff;
      padding: .2rem .6rem;
      border-radius: 20px;
      font-size: .62rem;
      font-weight: 800
    }

    /* Card body */
    .rcard-body {
      padding: .875rem
    }

    .rcard-name {
      font-size: 1rem;
      font-weight: 800;
      margin-bottom: .2rem;
      line-height: 1.25
    }

    .rcard-loc {
      display: flex;
      align-items: center;
      gap: .3rem;
      font-size: .75rem;
      color: var(--text-m);
      margin-bottom: .5rem
    }

    .rcard-desc {
      font-size: .78rem;
      color: var(--text-m);
      line-height: 1.5;
      margin-bottom: .625rem;
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;
      overflow: hidden
    }

    .rcard-tags {
      display: flex;
      gap: .3rem;
      flex-wrap: wrap;
      margin-bottom: .7rem
    }

    .rcard-tag {
      background: #F1F5F9;
      color: #475569;
      padding: .18rem .55rem;
      border-radius: 10px;
      font-size: .65rem;
      font-weight: 700
    }

    .rcard-foot {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding-top: .625rem;
      border-top: 1px solid #F1F5F9
    }

    .rcard-cost {
      font-size: .78rem;
      color: var(--text-m);
      line-height: 1.4
    }

    .rcard-cost b {
      font-size: .9rem;
      color: var(--text);
      font-weight: 900
    }

    .rcard-deposit {
      font-size: .65rem;
      color: #F59E0B;
      font-weight: 700;
      display: flex;
      align-items: center;
      gap: .2rem;
      margin-top: .1rem
    }

    .rcard-book-btn {
      padding: .5rem 1.1rem;
      background: linear-gradient(135deg, var(--primary), #FF8C5A);
      color: #fff;
      border: none;
      border-radius: 10px;
      font-size: .78rem;
      font-weight: 800;
      cursor: pointer;
      transition: all .15s;
      white-space: nowrap;
    }

    .rcard-book-btn:hover {
      transform: scale(1.04)
    }

    /* ── Empty State ── */
    .empty {
      text-align: center;
      padding: 5rem 1rem;
    }

    .empty-icon {
      font-size: 4.5rem;
      margin-bottom: 1rem;
      opacity: .6
    }

    .empty-title {
      font-weight: 900;
      font-size: 1.15rem;
      color: var(--text);
      margin-bottom: .5rem
    }

    .empty-sub {
      font-size: .875rem;
      color: var(--text-m);
      line-height: 1.55
    }

    /* ── How It Works ── */
    .how-section {
      background: var(--dark);
      margin-top: 2rem;
      border-radius: var(--radius-xl);
      padding: 2rem 1.5rem;
      overflow: hidden;
      position: relative
    }

    .how-section::before {
      content: '';
      position: absolute;
      top: -40px;
      right: -40px;
      width: 200px;
      height: 200px;
      border-radius: 50%;
      background: rgba(255, 107, 53, .06);
      pointer-events: none
    }

    .how-title {
      font-weight: 900;
      font-size: 1.1rem;
      color: #fff;
      margin-bottom: 1.25rem;
      text-align: center
    }

    .how-steps {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
      gap: 1rem
    }

    .how-step {
      text-align: center
    }

    .how-step-icon {
      width: 52px;
      height: 52px;
      border-radius: 14px;
      background: rgba(255, 107, 53, .15);
      border: 1px solid rgba(255, 107, 53, .25);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.4rem;
      margin: 0 auto .75rem
    }

    .how-step-title {
      font-weight: 800;
      font-size: .875rem;
      color: #fff;
      margin-bottom: .3rem
    }

    .how-step-sub {
      font-size: .75rem;
      color: rgba(255, 255, 255, .5);
      line-height: 1.5
    }

    /* ── Footer ── */
    .foot {
      background: var(--dark);
      color: rgba(255, 255, 255, .45);
      text-align: center;
      padding: 1.5rem 1rem;
      font-size: .78rem;
      margin-top: 2rem;
    }

    .foot a {
      color: var(--primary);
      font-weight: 600
    }

    /* ── Skeleton ── */
    .skeleton {
      background: linear-gradient(90deg, #E2E8F0 25%, #F1F5F9 50%, #E2E8F0 75%);
      background-size: 200% 100%;
      animation: shimmer 1.4s infinite;
      border-radius: 8px
    }

    @keyframes shimmer {
      to {
        background-position: -200% 0
      }
    }

    /* ── Mobile Bottom Bar ── */
    .mob-bar {
      display: none;
      position: fixed;
      bottom: 0;
      left: 0;
      right: 0;
      z-index: 100;
      background: #fff;
      border-top: 1px solid var(--border);
      padding: .625rem 1rem calc(.625rem + env(safe-area-inset-bottom));
      display: flex;
      justify-content: space-around;
    }

    .mob-bar-btn {
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: .2rem;
      border: none;
      background: none;
      cursor: pointer;
      padding: .25rem .5rem;
      min-width: 56px
    }

    .mob-bar-btn i {
      font-size: 1.1rem;
      color: var(--text-l)
    }

    .mob-bar-btn span {
      font-size: .62rem;
      font-weight: 600;
      color: var(--text-m)
    }

    .mob-bar-btn.on i {
      color: var(--primary)
    }

    .mob-bar-btn.on span {
      color: var(--primary)
    }

    @media(max-width:640px) {
      .mob-bar {
        display: flex
      }

      .main {
        padding-bottom: 80px
      }

      .rest-grid {
        grid-template-columns: 1fr
      }

      .hero {
        padding: 1.75rem 1rem 3rem
      }

      .hero h1 {
        font-size: 1.6rem
      }

      .stats-strip {
        gap: 1.5rem;
        padding: .75rem
      }

      .how-steps {
        grid-template-columns: 1fr 1fr
      }
    }
  </style>
</head>

<body class="app-shell">

  <!-- Top Nav -->
  <nav class="topnav">
    <div class="topnav-inner">
      <div class="nav-brand">
        <img src="https://www.DinoviX.ngwebd.com/images/logo2.png" alt="DinoviX Logo" class="nav-logo-img" style="width:140px;">
      </div>
      <div class="nav-right">
        <a href="<?= base_url('book/status') ?>" class="nav-btn"><i class="fa fa-search" style="margin-right:.3rem"></i> My Booking</a>
        <?php if (session()->get('user_id')): ?>
          <a href="<?= base_url('admin/dashboard') ?>" class="nav-btn primary"><i class="fa fa-gauge-high" style="margin-right:.3rem"></i> Dashboard</a>
        <?php endif; ?>
      </div>
    </div>
  </nav>

  <!-- Hero -->
  <div class="hero">
    <div class="hero-inner">
      <div class="hero-badge"><i class="fa fa-star"></i> Instant Confirmation · Zero Commission</div>
      <h1>Book Your <span>Perfect Table</span><br>in Seconds</h1>
      <div class="hero-sub">Reserve at the best restaurants near you — no calls, no waiting. Just pick, click, and show up.</div>

      <!-- Search Card -->
      <div class="search-card">
        <form method="GET" action="<?= base_url('book') ?>">
          <div class="search-fields">
            <div class="sf">
              <i class="fa fa-search"></i>
              <div style="flex:1;min-width:0">
                <div class="sf-label">Restaurant / Cuisine / Vibe</div>
                <input type="text" name="q" value="<?= esc($q) ?>" placeholder="e.g. Italian, Rooftop, Spice Garden...">
              </div>
            </div>
            <div class="sf">
              <i class="fa fa-location-dot"></i>
              <div style="flex:1;min-width:0">
                <div class="sf-label">City</div>
                <select name="city" style="width:100%">
                  <option value="">Any City</option>
                  <?php foreach ($cities as $c): ?>
                    <option value="<?= esc($c) ?>" <?= $city === $c ? 'selected' : '' ?>><?= esc($c) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
            <div class="sf">
              <i class="fa fa-calendar"></i>
              <div style="flex:1;min-width:0">
                <div class="sf-label">Date</div>
                <input type="date" name="date" value="<?= esc($date) ?>" min="<?= date('Y-m-d') ?>">
              </div>
            </div>
            <div class="sf">
              <i class="fa fa-users"></i>
              <div style="flex:1;min-width:0">
                <div class="sf-label">Guests</div>
                <select name="pax">
                  <?php for ($i = 1; $i <= 12; $i++): ?>
                    <option value="<?= $i ?>" <?= $pax == $i ? 'selected' : '' ?>><?= $i ?> Guest<?= $i > 1 ? 's' : '' ?></option>
                  <?php endfor; ?>
                  <option value="15" <?= $pax == 15 ? 'selected' : '' ?>>15+ Guests</option>
                </select>
              </div>
            </div>
            <div class="sf-btn-wrap">
              <button type="submit" class="search-btn"><i class="fa fa-magnifying-glass"></i> Search Tables</button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Stats Strip -->
  <?php if (!empty($restaurants)): ?>
    <div class="stats-strip">
      <div class="stat-item">
        <div class="stat-num"><?= count($restaurants) ?>+</div>
        <div class="stat-txt">Restaurants</div>
      </div>
      <div class="stat-item">
        <div class="stat-num">⚡</div>
        <div class="stat-txt">Instant Confirm</div>
      </div>
      <div class="stat-item">
        <div class="stat-num">₹0</div>
        <div class="stat-txt">Booking Fee</div>
      </div>
      <div class="stat-item">
        <div class="stat-num">24/7</div>
        <div class="stat-txt">Book Anytime</div>
      </div>
    </div>
  <?php endif; ?>

  <!-- Main Content -->
  <div class="main">

    <!-- Filter Chips -->
    <div class="filter-row">
      <div class="fchip on" onclick="filterTag(this,'')">All</div>
      <div class="fchip" onclick="filterTag(this,'rooftop')">🌆 Rooftop</div>
      <div class="fchip" onclick="filterTag(this,'romantic')">❤️ Romantic</div>
      <div class="fchip" onclick="filterTag(this,'family')">👨‍👩‍👧 Family</div>
      <div class="fchip" onclick="filterTag(this,'live-music')">🎵 Live Music</div>
      <div class="fchip" onclick="filterTag(this,'pet-friendly')">🐾 Pet Friendly</div>
      <div class="fchip" onclick="filterTag(this,'outdoor')">🌿 Outdoor</div>
      <div class="fchip" onclick="filterTag(this,'vegan')">🌱 Vegan</div>
      <div class="fchip" onclick="filterTag(this,'business')">💼 Business</div>
    </div>

    <!-- Results Header -->
    <div class="sec-hdr">
      <div class="sec-title">
        <i class="fa fa-store" style="color:var(--primary)"></i>
        <?= ($q || $city) ? 'Search Results' : 'Restaurants Taking Bookings' ?>
      </div>
      <span class="sec-count"><?= count($restaurants) ?> found</span>
    </div>

    <!-- Restaurant Grid -->
    <?php if (empty($restaurants)): ?>
      <div class="empty">
        <div class="empty-icon">🍽</div>
        <div class="empty-title">No restaurants found</div>
        <div class="empty-sub">Try a different city, cuisine, or search term.<br>More restaurants are joining DinoviX every day!</div>
      </div>
    <?php else: ?>
      <div class="rest-grid" id="restGrid">
        <?php foreach ($restaurants as $r): ?>
          <?php $tags = array_filter(array_map('trim', explode(',', $r['tags'] ?? ''))); ?>
          <a href="<?= base_url('book/' . ($r['booking_slug'] ?: $r['slug'])) ?>?date=<?= esc($date) ?>&pax=<?= $pax ?>"
            class="rcard"
            data-tags="<?= esc(strtolower($r['tags'] ?? '')) ?>"
            data-name="<?= esc(strtolower($r['name'])) ?>">
            <!-- Image -->
            <div class="rcard-img">
              <?php if (!empty($r['cover_image'])): ?>
                <img src="<?= base_url('images/uploads/' . $r['cover_image']) ?>" alt="<?= esc($r['name']) ?>" loading="lazy">
              <?php else: ?>
                <img src="<?= base_url('images/placeholder.png') ?>"
                  alt="Restaurant"
                  class="placeholder-img"
                  loading="lazy">
              <?php endif; ?>
              <div class="img-badges">
                <span class="avail-badge <?= $r['has_slots'] ? 'yes' : 'no' ?>">
                  <?= $r['has_slots'] ? '● Available' : '● Full Today' ?>
                </span>
                <?php if ($r['accepts_online_payment']): ?>
                  <span class="pay-badge">💳 Pay Online</span>
                <?php endif; ?>
              </div>
            </div>
            <!-- Body -->
            <div class="rcard-body">
              <div class="rcard-name"><?= esc($r['name']) ?></div>
              <div class="rcard-loc">
                <i class="fa fa-location-dot" style="color:var(--primary)"></i>
                <?= esc($r['city'] ?? '') ?>
                <?php if ($r['cuisine_type']): ?> · <?= esc($r['cuisine_type']) ?><?php endif; ?>
                  <?php if ($r['restaurant_type']): ?> · <?= ucfirst(str_replace('_', ' ', $r['restaurant_type'])) ?><?php endif; ?>
              </div>
              <?php if (!empty($r['short_desc'])): ?>
                <div class="rcard-desc"><?= esc($r['short_desc']) ?></div>
              <?php endif; ?>
              <?php if (!empty($tags)): ?>
                <div class="rcard-tags">
                  <?php foreach (array_slice($tags, 0, 4) as $tag): ?>
                    <span class="rcard-tag"><?= esc($tag) ?></span>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>
              <div class="rcard-foot">
                <div class="rcard-cost">
                  <?php if ($r['avg_cost_for_two'] > 0): ?>
                    <b>₹<?= number_format($r['avg_cost_for_two']) ?></b> for 2
                  <?php else: ?>
                    <span style="color:var(--text-l)">Price varies</span>
                  <?php endif; ?>
                  <?php if ($r['deposit_required']): ?>
                    <div class="rcard-deposit"><i class="fa fa-bolt"></i> ₹<?= number_format($r['deposit_amount']) ?> deposit req.</div>
                  <?php endif; ?>
                </div>
                <button class="rcard-book-btn">Book Table →</button>
              </div>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <!-- How It Works -->
    <div class="how-section">
      <div class="how-title">How DinoviX Works</div>
      <div class="how-steps">
        <div class="how-step">
          <div class="how-step-icon">🔍</div>
          <div class="how-step-title">Search</div>
          <div class="how-step-sub">Find restaurants by city, cuisine, or vibe</div>
        </div>
        <div class="how-step">
          <div class="how-step-icon">📅</div>
          <div class="how-step-title">Pick a Slot</div>
          <div class="how-step-sub">Choose date, time and number of guests</div>
        </div>
        <div class="how-step">
          <div class="how-step-icon">✅</div>
          <div class="how-step-title">Confirm</div>
          <div class="how-step-sub">Instant confirmation — no calls needed</div>
        </div>
        <div class="how-step">
          <div class="how-step-icon">🍽</div>
          <div class="how-step-title">Show Up & Enjoy</div>
          <div class="how-step-sub">Your table is waiting — just walk in</div>
        </div>
      </div>
    </div>
  </div>

  <!-- Footer -->
  <div class="foot">
    <div>© <?= date('Y') ?> DinoviX · Restaurant Management Platform</div>
    <div style="margin-top:.4rem">
      <a href="<?= base_url('book/status') ?>">Check Booking</a>
      &nbsp;·&nbsp;
      <a href="<?= base_url() ?>">For Restaurants</a>
    </div>
  </div>

  <!-- Mobile Bottom Bar -->
  <div class="mob-bar">
    <button class="mob-bar-btn on" onclick="location.href='<?= base_url('book') ?>'">
      <i class="fa fa-house"></i><span>Discover</span>
    </button>
    <button class="mob-bar-btn" onclick="showSearch()">
      <i class="fa fa-search"></i><span>Search</span>
    </button>
    <button class="mob-bar-btn" onclick="location.href='<?= base_url('book/status') ?>'">
      <i class="fa fa-calendar-check"></i><span>My Booking</span>
    </button>
  </div>

  <script>
    // Filter by tag chip
    function filterTag(el, tag) {
      document.querySelectorAll('.fchip').forEach(c => c.classList.remove('on'));
      el.classList.add('on');
      document.querySelectorAll('.rcard').forEach(card => {
        if (!tag) {
          card.style.display = '';
          return;
        }
        card.style.display = card.dataset.tags.includes(tag) ? '' : 'none';
      });
    }

    // Mobile search — scroll to hero
    function showSearch() {
      document.querySelector('.hero').scrollIntoView({
        behavior: 'smooth'
      });
      setTimeout(() => document.querySelector('.sf input')?.focus(), 500);
    }

    // Lazy-load image fallback
    document.querySelectorAll('.rcard-img img').forEach(img => {
      img.onerror = () => {
        img.parentElement.innerHTML = '<div class="rcard-img-ph">🍽<span>No photo</span></div>';
      };
    });
  </script>
</body>

</html>