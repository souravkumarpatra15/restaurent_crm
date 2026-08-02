<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=no,viewport-fit=cover">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="theme-color" content="<?= esc($rest['theme_color'] ?? '#0F172A') ?>">
  <title>Book at <?= esc($rest['name']) ?> — DinoviX</title>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
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
      min-height: 100%
    }

    a {
      text-decoration: none;
      color: inherit
    }

    :root {
      --c: <?= esc($rest['theme_color'] ?? '#FF6B35') ?>;
      --c-l: <?= esc($rest['theme_color'] ?? '#FF6B35') ?>22;
      --dark: #0F172A;
      --text-m: #64748B;
      --text-l: #94A3B8;
      --border: #E2E8F0;
      --bg: #F8FAFC;
      --card: #fff;
      --radius: 14px;
      --radius-lg: 20px;
    }

    /* ── Top Bar ── */
    .topbar {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      z-index: 300;
      padding: calc(.75rem + env(safe-area-inset-top, 0px)) 1rem .75rem;
      display: flex;
      align-items: center;
      justify-content: space-between;
      transition: background .3s, backdrop-filter .3s;
    }

    .topbar.scrolled {
      background: rgba(15, 23, 42, .92);
      backdrop-filter: blur(12px)
    }

    .tb-back {
      width: 38px;
      height: 38px;
      border-radius: 50%;
      background: rgba(255, 255, 255, .15);
      backdrop-filter: blur(8px);
      border: 1px solid rgba(255, 255, 255, .2);
      color: #fff;
      font-size: .9rem;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
    }

    .tb-back:hover {
      background: rgba(255, 255, 255, .25)
    }

    .tb-share {
      width: 38px;
      height: 38px;
      border-radius: 50%;
      background: rgba(255, 255, 255, .15);
      backdrop-filter: blur(8px);
      border: 1px solid rgba(255, 255, 255, .2);
      color: #fff;
      font-size: .85rem;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      border: none;
    }

    /* ── Hero Image ── */
    .hero-img-wrap {
      position: relative;
      height: 280px;
      overflow: hidden;
      background: #E2E8F0
    }

    .hero-img-wrap img {
      width: 100%;
      height: 100%;
      object-fit: cover
    }

    .hero-img-ph {
      width: 100%;
      height: 100%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 5rem;
      background: linear-gradient(135deg, #1E293B, #334155)
    }

    .hero-overlay {
      position: absolute;
      inset: 0;
      background: linear-gradient(to bottom, rgba(0, 0, 0, .1) 0%, rgba(0, 0, 0, .0) 40%, rgba(0, 0, 0, .7) 100%);
    }

    .hero-bottom {
      position: absolute;
      bottom: 0;
      left: 0;
      right: 0;
      padding: 1rem 1.25rem
    }

    .hero-rname {
      font-size: 1.4rem;
      font-weight: 900;
      color: #fff;
      line-height: 1.2;
      margin-bottom: .3rem
    }

    .hero-meta {
      display: flex;
      align-items: center;
      gap: .5rem;
      flex-wrap: wrap;
      font-size: .75rem;
      color: rgba(255, 255, 255, .8)
    }

    .hero-meta span {
      display: flex;
      align-items: center;
      gap: .2rem
    }

    /* ── Menu Photo Strip ── */
    .menu-strip {
      display: flex;
      gap: .5rem;
      padding: .875rem 1rem;
      overflow-x: auto;
      scrollbar-width: none;
      background: #fff;
      border-bottom: 1px solid var(--border);
    }

    .menu-strip::-webkit-scrollbar {
      display: none
    }

    .menu-strip-item {
      flex-shrink: 0;
      width: 72px;
      height: 72px;
      border-radius: 10px;
      overflow: hidden;
      position: relative;
      cursor: pointer;
      border: 2px solid transparent;
      transition: border-color .15s;
    }

    .menu-strip-item:hover {
      border-color: var(--c)
    }

    .menu-strip-item img {
      width: 100%;
      height: 100%;
      object-fit: cover
    }

    .menu-strip-label {
      position: absolute;
      bottom: 0;
      left: 0;
      right: 0;
      background: rgba(0, 0, 0, .55);
      color: #fff;
      font-size: .5rem;
      font-weight: 700;
      text-align: center;
      padding: 2px;
      text-overflow: ellipsis;
      overflow: hidden;
      white-space: nowrap;
    }

    /* ── Body ── */
    .body {
      max-width: 520px;
      margin: 0 auto;
      padding: 0 0 120px
    }

    /* ── Info Card ── */
    .info-card {
      background: #fff;
      padding: 1.25rem;
      border-bottom: 1px solid var(--border)
    }

    .info-tags {
      display: flex;
      gap: .35rem;
      flex-wrap: wrap;
      margin-bottom: .875rem
    }

    .info-tag {
      background: var(--c-l);
      color: var(--c);
      padding: .25rem .65rem;
      border-radius: 12px;
      font-size: .7rem;
      font-weight: 700
    }

    .info-desc {
      font-size: .875rem;
      color: var(--text-m);
      line-height: 1.6;
      margin-bottom: .875rem
    }

    .info-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: .5rem
    }

    .info-box {
      background: var(--bg);
      border-radius: 10px;
      padding: .7rem;
      text-align: center
    }

    .info-box-val {
      font-size: .95rem;
      font-weight: 800;
      color: var(--dark)
    }

    .info-box-lbl {
      font-size: .65rem;
      color: var(--text-m);
      margin-top: .1rem
    }

    /* ── Step Card ── */
    .step-card {
      background: #fff;
      border-bottom: 1px solid var(--border);
      padding: 1.25rem
    }

    .step-hdr {
      display: flex;
      align-items: center;
      gap: .625rem;
      margin-bottom: .875rem
    }

    .step-num {
      width: 26px;
      height: 26px;
      border-radius: 50%;
      background: var(--c);
      color: #fff;
      font-size: .72rem;
      font-weight: 900;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
    }

    .step-title {
      font-weight: 800;
      font-size: .95rem
    }

    /* ── Date Pills ── */
    .date-scroll {
      display: flex;
      gap: .4rem;
      overflow-x: auto;
      scrollbar-width: none;
      padding-bottom: .25rem
    }

    .date-scroll::-webkit-scrollbar {
      display: none
    }

    .dpill {
      flex-shrink: 0;
      min-width: 58px;
      padding: .5rem .625rem;
      border-radius: 12px;
      border: 1.5px solid var(--border);
      background: #fff;
      text-align: center;
      cursor: pointer;
      transition: all .15s;
    }

    .dpill.on {
      background: var(--c);
      border-color: var(--c)
    }

    .dpill-day {
      font-size: .6rem;
      font-weight: 700;
      color: var(--text-l);
      text-transform: uppercase;
      letter-spacing: .04em
    }

    .dpill-date {
      font-size: 1rem;
      font-weight: 900;
      color: var(--dark);
      line-height: 1.1
    }

    .dpill-mon {
      font-size: .6rem;
      color: var(--text-l)
    }

    .dpill.on .dpill-day,
    .dpill.on .dpill-date,
    .dpill.on .dpill-mon {
      color: #fff
    }

    /* ── Guest Stepper ── */
    .guest-row {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: .5rem 0
    }

    .guest-hint {
      font-size: .75rem;
      color: var(--text-m);
      margin-top: .35rem;
      text-align: center
    }

    .g-ctrl {
      display: flex;
      align-items: center;
      gap: .875rem
    }

    .g-btn {
      width: 44px;
      height: 44px;
      border-radius: 50%;
      border: 2px solid var(--border);
      background: #fff;
      font-size: 1.4rem;
      font-weight: 700;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      transition: all .15s;
      color: var(--dark);
      -webkit-tap-highlight-color: transparent;
    }

    .g-btn.plus {
      border-color: var(--c);
      background: var(--c);
      color: #fff
    }

    .g-btn:active {
      transform: scale(.93)
    }

    .g-val {
      font-size: 2.25rem;
      font-weight: 900;
      min-width: 52px;
      text-align: center;
      color: var(--dark)
    }

    .g-label {
      font-size: .75rem;
      color: var(--text-m)
    }

    /* ── Time Slots ── */
    .slot-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(88px, 1fr));
      gap: .375rem
    }

    .slot-btn {
      padding: .55rem .3rem;
      border: 1.5px solid var(--border);
      border-radius: 10px;
      background: #fff;
      font-size: .8rem;
      font-weight: 700;
      color: var(--dark);
      cursor: pointer;
      text-align: center;
      transition: all .15s;
    }

    .slot-btn:hover {
      border-color: var(--c);
      color: var(--c);
      background: var(--c-l)
    }

    .slot-btn.on {
      background: var(--c);
      border-color: var(--c);
      color: #fff
    }

    .slot-loading {
      text-align: center;
      padding: 1.5rem;
      color: var(--text-m);
      font-size: .85rem
    }

    .slot-empty {
      text-align: center;
      padding: 1.25rem;
      color: var(--text-m);
      font-size: .85rem;
      background: var(--bg);
      border-radius: 10px
    }

    /* ── Summary Bar ── */
    .summary-bar {
      background: var(--dark);
      border-radius: 12px;
      padding: .875rem 1.1rem;
      margin-bottom: .875rem;
      display: none;
    }

    .summary-bar.show {
      display: flex;
      align-items: center;
      justify-content: space-between
    }

    .sb-left {
      color: #fff
    }

    .sb-date {
      font-weight: 800;
      font-size: .9rem
    }

    .sb-detail {
      font-size: .72rem;
      color: rgba(255, 255, 255, .55);
      margin-top: .15rem
    }

    .sb-time {
      font-weight: 900;
      font-size: 1rem;
      color: var(--c)
    }

    /* ── Detail Form ── */
    .field-grp {
      margin-bottom: .875rem
    }

    .field-grp label {
      display: block;
      font-size: .7rem;
      font-weight: 800;
      color: var(--text-m);
      text-transform: uppercase;
      letter-spacing: .05em;
      margin-bottom: .35rem
    }

    .field-input {
      width: 100%;
      padding: .7rem .9rem;
      border: 1.5px solid var(--border);
      border-radius: 10px;
      font-size: .9rem;
      color: var(--dark);
      outline: none;
      transition: border-color .2s, box-shadow .2s;
      background: #fff;
    }

    .field-input:focus {
      border-color: var(--c);
      box-shadow: 0 0 0 3px var(--c-l)
    }

    .field-row {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: .625rem
    }

    textarea.field-input {
      resize: none;
      line-height: 1.5
    }

    /* Occasion chips */
    .occ-grid {
      display: flex;
      gap: .375rem;
      flex-wrap: wrap
    }

    .occ-btn {
      padding: .375rem .75rem;
      border: 1.5px solid var(--border);
      border-radius: 20px;
      background: #fff;
      font-size: .75rem;
      font-weight: 600;
      color: var(--text-m);
      cursor: pointer;
      transition: all .15s;
    }

    .occ-btn.on {
      background: var(--c);
      border-color: var(--c);
      color: #fff
    }

    /* Deposit Notice */
    .deposit-notice {
      background: #FFFBEB;
      border: 1px solid #FDE68A;
      border-radius: 10px;
      padding: .875rem;
      font-size: .82rem;
      line-height: 1.55;
      margin-bottom: .875rem;
    }

    .deposit-notice strong {
      color: #92400E
    }

    /* Instructions */
    .instructions {
      background: var(--c-l);
      border-radius: 10px;
      padding: .875rem;
      font-size: .82rem;
      color: var(--dark);
      line-height: 1.5;
      margin-bottom: .875rem;
      border: 1px solid var(--c)33;
    }

    /* ── Sticky Bottom Bar ── */
    .book-bar {
      position: fixed;
      bottom: 0;
      left: 0;
      right: 0;
      z-index: 200;
      background: #fff;
      border-top: 1px solid var(--border);
      padding: .875rem 1.25rem calc(.875rem + env(safe-area-inset-bottom));
    }

    .book-btn {
      width: 100%;
      padding: .875rem;
      border: none;
      border-radius: 14px;
      background: linear-gradient(135deg, var(--c), <?= esc($rest['theme_color'] ?? '#FF6B35') ?>cc);
      color: #fff;
      font-size: 1rem;
      font-weight: 900;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: .5rem;
      transition: all .2s;
      box-shadow: 0 4px 20px rgba(0, 0, 0, .15);
    }

    .book-btn:hover:not(:disabled) {
      transform: translateY(-1px);
      box-shadow: 0 6px 28px rgba(0, 0, 0, .2)
    }

    .book-btn:active:not(:disabled) {
      transform: scale(.98)
    }

    .book-btn:disabled {
      opacity: .4;
      cursor: not-allowed;
      transform: none;
      box-shadow: none
    }

    .book-bar-hint {
      text-align: center;
      font-size: .7rem;
      color: var(--text-m);
      margin-top: .4rem
    }

    /* ── Loading & Overlay ── */
    .loader-overlay {
      position: fixed;
      inset: 0;
      z-index: 900;
      background: rgba(15, 23, 42, .7);
      backdrop-filter: blur(6px);
      display: none;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      gap: 1rem;
    }

    .loader-overlay.on {
      display: flex
    }

    .loader-ring {
      width: 52px;
      height: 52px;
      border-radius: 50%;
      border: 4px solid rgba(255, 255, 255, .15);
      border-top-color: var(--c);
      animation: spin 1s linear infinite;
    }

    @keyframes spin {
      to {
        transform: rotate(360deg)
      }
    }

    .loader-txt {
      color: #fff;
      font-weight: 700;
      font-size: .9rem
    }

    /* Photo Lightbox */
    .lightbox {
      position: fixed;
      inset: 0;
      z-index: 800;
      background: rgba(0, 0, 0, .92);
      display: none;
      align-items: center;
      justify-content: center;
    }

    .lightbox.on {
      display: flex
    }

    .lightbox img {
      max-width: 92vw;
      max-height: 88vh;
      border-radius: 10px;
      object-fit: contain
    }

    .lightbox-close {
      position: absolute;
      top: 1rem;
      right: 1rem;
      width: 40px;
      height: 40px;
      border-radius: 50%;
      background: rgba(255, 255, 255, .15);
      border: none;
      color: #fff;
      font-size: 1.1rem;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center
    }

    @media(min-width:600px) {
      .body {
        border-left: 1px solid var(--border);
        border-right: 1px solid var(--border);
        box-shadow: 0 0 48px rgba(0, 0, 0, .06)
      }

      .hero-img-wrap {
        height: 340px
      }

      .field-grp {
        margin-bottom: .75rem
      }
    }
  </style>
</head>

<body>

  <!-- Top Bar (transparent → solid on scroll) -->
  <div class="topbar" id="topbar">
    <button class="tb-back" onclick="history.back()"><i class="fa fa-arrow-left"></i></button>
    <button class="tb-share" onclick="shareRest()"><i class="fa fa-share-nodes"></i></button>
  </div>

  <!-- Hero Image -->
  <div class="hero-img-wrap">
    <?php if (!empty($rest['cover_image'])): ?>
      <img src="<?= base_url('images/uploads/' . $rest['cover_image']) ?>" alt="<?= esc($rest['name']) ?>">
    <?php else: ?>
      <img src="<?= base_url('images/reserved.png') ?>"
                  alt="Restaurant"
                  class="placeholder-img"
                  loading="lazy">
    <?php endif; ?>
    <div class="hero-overlay"></div>
    <div class="hero-bottom">
      <div class="hero-rname"><?= esc($rest['name']) ?></div>
      <div class="hero-meta">
        <?php if ($rest['city']): ?><span><i class="fa fa-location-dot"></i><?= esc($rest['city']) ?></span><?php endif; ?>
        <?php if ($rest['cuisine_type']): ?><span>·</span><span><?= esc($rest['cuisine_type']) ?></span><?php endif; ?>
        <?php if ($rest['avg_cost_for_two'] > 0): ?><span>·</span><span>₹<?= number_format($rest['avg_cost_for_two']) ?> for 2</span><?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Menu Photo Strip (from menu items with images) -->
  <?php if (!empty($menuPhotos)): ?>
    <div class="menu-strip" id="menuStrip">
      <?php foreach (array_slice($menuPhotos, 0, 12) as $mp): ?>
        <div class="menu-strip-item" onclick="openLightbox('<?= base_url('images/uploads/' . $mp['image']) ?>')">
          <img src="<?= base_url('images/uploads/' . $mp['image']) ?>" alt="<?= esc($mp['name']) ?>" loading="lazy">
          <div class="menu-strip-label"><?= esc($mp['name']) ?></div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <div class="body">

    <!-- Info -->
    <div class="info-card">
      <?php if ($rest['tags']): ?>
        <div class="info-tags">
          <?php foreach (array_filter(array_map('trim', explode(',', $rest['tags']))) as $t): ?>
            <span class="info-tag"><?= esc($t) ?></span>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
      <?php if ($rest['short_desc']): ?>
        <div class="info-desc"><?= esc($rest['short_desc']) ?></div>
      <?php endif; ?>
      <div class="info-grid">
        <div class="info-box">
          <div class="info-box-val"><i class="fa fa-clock" style="color:var(--c)"></i> <?= date('g A', strtotime($rest['open_time'] ?? '10:00')) ?>–<?= date('g A', strtotime($rest['close_time'] ?? '23:00')) ?></div>
          <div class="info-box-lbl">Hours</div>
        </div>
        <div class="info-box">
          <div class="info-box-val"><?= $rest['min_guests'] ?>–<?= $rest['max_guests'] ?></div>
          <div class="info-box-lbl">Guest Range</div>
        </div>
        <div class="info-box">
          <div class="info-box-val"><?= ($rest['slot_duration_min'] ?? 60) ?> min</div>
          <div class="info-box-lbl">Table Duration</div>
        </div>
        <div class="info-box">
          <div class="info-box-val" style="color:var(--c)"><?= $rest['auto_confirm'] ? 'Instant ⚡' : 'Manual ✋' ?></div>
          <div class="info-box-lbl">Confirmation</div>
        </div>
      </div>
    </div>

    <!-- Step 1: Date -->
    <div class="step-card">
      <div class="step-hdr">
        <div class="step-num">1</div>
        <div class="step-title">Choose a Date</div>
      </div>
      <div class="date-scroll" id="dateScroll">
        <?php foreach (array_slice($availDates, 0, 21) as $idx => $d):
          $ts  = strtotime($d);
          $dow = date('D', $ts);
          $dd  = date('d', $ts);
          $mon = date('M', $ts);
          $isToday = $d === date('Y-m-d');
        ?>
          <div class="dpill <?= $idx === 0 ? 'on' : '' ?>" data-date="<?= $d ?>" onclick="selectDate(this)">
            <div class="dpill-day"><?= $isToday ? 'Today' : $dow ?></div>
            <div class="dpill-date"><?= $dd ?></div>
            <div class="dpill-mon"><?= $mon ?></div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Step 2: Guests -->
    <div class="step-card">
      <div class="step-hdr">
        <div class="step-num">2</div>
        <div class="step-title">Number of Guests</div>
      </div>
      <div class="guest-row">
        <div class="g-label" id="gLabel">2 Guests</div>
        <div class="g-ctrl">
          <button class="g-btn" onclick="changeGuests(-1)">−</button>
          <span class="g-val" id="gVal">2</span>
          <button class="g-btn plus" onclick="changeGuests(1)">+</button>
        </div>
      </div>
      <div class="guest-hint">Min <?= $rest['min_guests'] ?> · Max <?= $rest['max_guests'] ?> guests per booking</div>
    </div>

    <!-- Step 3: Time -->
    <div class="step-card">
      <div class="step-hdr">
        <div class="step-num">3</div>
        <div class="step-title">Select Time Slot</div>
      </div>
      <div id="slotWrap">
        <div class="slot-loading"><i class="fa fa-spinner fa-spin"></i> Loading available slots...</div>
      </div>
    </div>

    <!-- Step 4: Your Details -->
    <div class="step-card" id="detailStep" style="display:none">
      <div class="step-hdr">
        <div class="step-num">4</div>
        <div class="step-title">Your Details</div>
      </div>

      <!-- Summary Bar -->
      <div class="summary-bar" id="sumBar">
        <div class="sb-left">
          <div class="sb-date" id="sbDate">—</div>
          <div class="sb-detail" id="sbDetail">—</div>
        </div>
        <div class="sb-time" id="sbTime">—</div>
      </div>

      <div class="field-row">
        <div class="field-grp">
          <label>Your Name *</label>
          <input type="text" class="field-input" id="gName" placeholder="Full name" autocomplete="name">
        </div>
        <div class="field-grp">
          <label>Phone *</label>
          <input type="tel" class="field-input" id="gPhone" placeholder="Mobile" inputmode="numeric" autocomplete="tel">
        </div>
      </div>

      <div class="field-grp">
        <label>Email <span style="font-weight:400;opacity:.6">(optional — for confirmation)</span></label>
        <input type="email" class="field-input" id="gEmail" placeholder="you@email.com" autocomplete="email">
      </div>

      <div class="field-grp">
        <label>Occasion</label>
        <div class="occ-grid">
          <?php foreach (['none' => 'No occasion', 'birthday' => '🎂 Birthday', 'anniversary' => '💍 Anniversary', 'date' => '❤️ Date Night', 'business' => '💼 Business', 'family' => '👨‍👩‍👧 Family', 'other' => '🎉 Other'] as $k => $v): ?>
            <button type="button" class="occ-btn <?= $k === 'none' ? 'on' : '' ?>" data-occ="<?= $k ?>" onclick="selectOcc(this)"><?= $v ?></button>
          <?php endforeach; ?>
        </div>
      </div>

      <?php if (count($branches) > 1): ?>
        <div class="field-grp">
          <label>Branch / Location</label>
          <select class="field-input" id="gBranch">
            <?php foreach ($branches as $b): ?>
              <option value="<?= $b['id'] ?>"><?= esc($b['name']) ?><?= $b['address'] ? ' — ' . esc($b['city'] ?? '') : '' ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      <?php endif; ?>

      <div class="field-grp">
        <label>Special Requests <span style="font-weight:400;opacity:.6">(optional)</span></label>
        <textarea class="field-input" id="gRequests" rows="3" placeholder="Window seat, high chair, cake arrangement, allergy info..."></textarea>
      </div>

      <?php if ($rest['deposit_required']): ?>
        <div class="deposit-notice">
          <i class="fa fa-bolt" style="color:#F59E0B"></i>
          <strong>Deposit Required:</strong>
          ₹<?= number_format($rest['deposit_amount']) ?><?= $rest['deposit_type'] === 'per_person' ? ' per person' : '' ?> to confirm your booking.
          <?= $rest['cancellation_hours'] > 0 ? 'Free cancellation up to ' . $rest['cancellation_hours'] . ' hour' . ($rest['cancellation_hours'] > 1 ? 's' : '') . ' before.' : '' ?>
        </div>
      <?php endif; ?>

      <?php if (!empty($rest['booking_instructions'])): ?>
        <div class="instructions">
          <i class="fa fa-circle-info" style="color:var(--c);margin-right:.3rem"></i>
          <?= esc($rest['booking_instructions']) ?>
        </div>
      <?php endif; ?>
    </div>

  </div><!-- /body -->

  <!-- Sticky Book Button -->
  <div class="book-bar" id="bookBar">
    <button class="book-btn" id="bookBtn" disabled onclick="submitBooking()">
      <i class="fa fa-calendar-check"></i>
      <span id="bookBtnTxt">Select a date & time to continue</span>
    </button>
    <div class="book-bar-hint">Free to book · No DinoviX charges</div>
  </div>

  <!-- Loader -->
  <div class="loader-overlay" id="loader">
    <div class="loader-ring"></div>
    <div class="loader-txt" id="loaderTxt">Confirming your booking...</div>
  </div>

  <!-- Lightbox -->
  <div class="lightbox" id="lightbox" onclick="closeLightbox()">
    <button class="lightbox-close" onclick="closeLightbox()"><i class="fa fa-times"></i></button>
    <img id="lightboxImg" src="" alt="">
  </div>

  <script>
    const REST_ID = <?= (int)$rest['id'] ?>;
    const BASE = '<?= base_url() ?>';
    const CN = '<?= csrf_token() ?>';
    let CT = '<?= csrf_hash() ?>';
    const MIN_PAX = <?= (int)($rest['min_guests'] ?? 1) ?>;
    const MAX_PAX = <?= (int)($rest['max_guests'] ?? 20) ?>;

    let selDate = '<?= date('Y-m-d') ?>';
    let selTime = '';
    let selGuests = 2;
    let selOcc = 'none';

    // Topbar scroll effect
    window.addEventListener('scroll', () => {
      document.getElementById('topbar').classList.toggle('scrolled', window.scrollY > 200);
    }, {
      passive: true
    });

    // Date
    function selectDate(el) {
      document.querySelectorAll('.dpill').forEach(d => d.classList.remove('on'));
      el.classList.add('on');
      selDate = el.dataset.date;
      selTime = '';
      updateBookBtn();
      loadSlots();
    }

    // Guests
    function changeGuests(d) {
      selGuests = Math.max(MIN_PAX, Math.min(MAX_PAX, selGuests + d));
      document.getElementById('gVal').textContent = selGuests;
      document.getElementById('gLabel').textContent = selGuests + ' Guest' + (selGuests > 1 ? 's' : '');
      if (selDate) loadSlots();
      updateBookBtn();
    }

    // Occasion
    function selectOcc(el) {
      document.querySelectorAll('.occ-btn').forEach(b => b.classList.remove('on'));
      el.classList.add('on');
      selOcc = el.dataset.occ;
    }

    // Load slots
    async function loadSlots() {
      const wrap = document.getElementById('slotWrap');
      wrap.innerHTML = '<div class="slot-loading"><i class="fa fa-spinner fa-spin"></i> Loading slots...</div>';

      const body = new URLSearchParams({
        [CN]: CT,
        restaurant_id: REST_ID,
        date: selDate,
        pax: selGuests
      });
      CT = CT; // refresh
      const d = await fetch(BASE + 'book/slots', {
        method: 'POST',
        body
      }).then(r => r.json()).catch(() => ({
        slots: []
      }));

      if (!d.slots || !d.slots.length) {
        wrap.innerHTML = '<div class="slot-empty">😔 No slots available for this date or party size.<br><span style="font-size:.75rem;opacity:.7">Try a different date or fewer guests.</span></div>';
        return;
      }

      wrap.innerHTML = '<div class="slot-grid">' +
        d.slots.map(s => `<button class="slot-btn" data-time="${s.time}" data-fmt="${s.time_fmt}" onclick="selectSlot(this)">${s.time_fmt}</button>`).join('') +
        '</div>';
    }

    // Select slot
    function selectSlot(el) {
      document.querySelectorAll('.slot-btn').forEach(s => s.classList.remove('on'));
      el.classList.add('on');
      selTime = el.dataset.time;
      updateSummary(el.dataset.fmt);
      updateBookBtn();
      // Show details step
      const ds = document.getElementById('detailStep');
      if (ds.style.display === 'none') {
        ds.style.display = '';
        setTimeout(() => ds.scrollIntoView({
          behavior: 'smooth',
          block: 'start'
        }), 100);
      }
    }

    // Summary bar
    function updateSummary(timeFmt) {
      const bar = document.getElementById('sumBar');
      if (!selDate || !timeFmt) {
        bar.classList.remove('show');
        return;
      }
      bar.classList.add('show');
      const d = new Date(selDate + 'T00:00:00');
      document.getElementById('sbDate').textContent = d.toLocaleDateString('en-IN', {
        weekday: 'short',
        day: 'numeric',
        month: 'short',
        year: 'numeric'
      });
      document.getElementById('sbDetail').textContent = selGuests + ' guest' + (selGuests > 1 ? 's' : '');
      document.getElementById('sbTime').textContent = timeFmt;
    }

    // Book button state
    function updateBookBtn() {
      const btn = document.getElementById('bookBtn');
      const txt = document.getElementById('bookBtnTxt');
      if (!selDate) {
        btn.disabled = true;
        txt.textContent = 'Select a date first';
        return;
      }
      if (!selTime) {
        btn.disabled = true;
        txt.textContent = 'Pick a time slot';
        return;
      }
      btn.disabled = false;
      txt.textContent = 'Confirm Booking';
    }

    // Submit
    async function submitBooking() {
      const name = document.getElementById('gName').value.trim();
      const phone = document.getElementById('gPhone').value.trim();
      if (!name) {
        alert('Please enter your name');
        document.getElementById('gName').focus();
        return;
      }
      if (!phone) {
        alert('Please enter your phone');
        document.getElementById('gPhone').value.focus?.();
        return;
      }
      if (!selDate || !selTime) {
        alert('Please select a date and time');
        return;
      }

      const btn = document.getElementById('bookBtn');
      btn.disabled = true;
      document.getElementById('loaderTxt').textContent = 'Confirming your booking...';
      document.getElementById('loader').classList.add('on');

      const body = new URLSearchParams({
        [CN]: CT,
        restaurant_id: REST_ID,
        branch_id: document.getElementById('gBranch')?.value || '',
        date: selDate,
        time: selTime,
        guests: selGuests,
        name,
        phone,
        email: document.getElementById('gEmail').value,
        special_requests: document.getElementById('gRequests').value,
        occasion: selOcc,
      });

      try {
        const d = await fetch(BASE + 'book/reserve', {
          method: 'POST',
          body
        }).then(r => r.json());
        document.getElementById('loader').classList.remove('on');
        if (d.success) {
          window.location.href = d.confirm_url;
        } else {
          alert(d.message || 'Booking failed. Please try again.');
          btn.disabled = false;
        }
      } catch (e) {
        document.getElementById('loader').classList.remove('on');
        alert('Network error. Please try again.');
        btn.disabled = false;
      }
    }

    // Share
    function shareRest() {
      if (navigator.share) {
        navigator.share({
          title: '<?= esc($rest['name']) ?>',
          text: 'Book a table at <?= esc($rest['name']) ?> on DinoviX!',
          url: location.href
        });
      } else {
        navigator.clipboard?.writeText(location.href).then(() => alert('Link copied!'));
      }
    }

    // Lightbox
    function openLightbox(src) {
      document.getElementById('lightboxImg').src = src;
      document.getElementById('lightbox').classList.add('on');
    }

    function closeLightbox() {
      document.getElementById('lightbox').classList.remove('on');
    }

    // Init
    loadSlots();
  </script>
</body>

</html>