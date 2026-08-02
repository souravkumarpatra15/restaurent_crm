<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=no,viewport-fit=cover">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <title>Booking Status — DinoViX</title>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800;900&family=JetBrains+Mono:wght@600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    *,
    *::before,
    *::after {
      box-sizing: border-box;
      margin: 0;
      padding: 0
    }

    html,
    body {
      min-height: 100%;
      -webkit-tap-highlight-color: transparent
    }

    body {
      font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
      background: #0F172A;
      color: #0F172A
    }

    :root {
      --primary: #FF6B35;
      --dark: #0F172A;
      --border: #E2E8F0;
      --radius: 14px;
      --success: #22C55E;
      --warning: #F59E0B;
      --danger: #EF4444
    }

    .page {
      min-height: 100vh;
      display: flex;
      flex-direction: column
    }

    /* ── Top Bar ── */
    .topbar {
      padding: calc(.875rem + env(safe-area-inset-top)) 1.25rem .875rem;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    .logo {
      display: flex;
      align-items: center;
      gap: .4rem
    }

    .logo-icon {
      width: 32px;
      height: 32px;
      border-radius: 9px;
      background: linear-gradient(135deg, var(--primary), #FF8C5A);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1rem
    }

    .logo-txt {
      font-weight: 900;
      font-size: .95rem;
      color: #fff
    }

    .logo-txt span {
      color: var(--primary)
    }

    .home-btn {
      padding: .4rem .875rem;
      background: rgba(255, 255, 255, .08);
      border: 1px solid rgba(255, 255, 255, .12);
      border-radius: 8px;
      color: rgba(255, 255, 255, .7);
      font-size: .75rem;
      font-weight: 700;
      cursor: pointer;
      font-family: inherit
    }

    /* ── Lookup Section (when no booking) ── */
    .lookup-wrap {
      flex: 1;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 2rem 1.5rem;
    }

    .lookup-icon {
      font-size: 4rem;
      margin-bottom: 1rem
    }

    .lookup-title {
      font-size: 1.2rem;
      font-weight: 900;
      color: #fff;
      margin-bottom: .4rem;
      text-align: center
    }

    .lookup-sub {
      font-size: .85rem;
      color: rgba(255, 255, 255, .5);
      margin-bottom: 2rem;
      text-align: center;
      line-height: 1.55
    }

    .lookup-card {
      background: #fff;
      border-radius: var(--radius);
      padding: 1.5rem;
      width: 100%;
      max-width: 380px
    }

    .lookup-label {
      display: block;
      font-size: .72rem;
      font-weight: 800;
      color: #64748B;
      text-transform: uppercase;
      letter-spacing: .06em;
      margin-bottom: .45rem
    }

    .lookup-input {
      width: 100%;
      padding: .8rem 1rem;
      border: 1.5px solid var(--border);
      border-radius: 10px;
      font-size: .95rem;
      font-family: 'JetBrains Mono', monospace;
      text-transform: uppercase;
      letter-spacing: .06em;
      text-align: center;
      color: #0F172A;
      outline: none;
      transition: border-color .2s, box-shadow .2s;
      background: #F8FAFC;
      margin-bottom: .875rem;
    }

    .lookup-input:focus {
      border-color: var(--primary);
      background: #fff;
      box-shadow: 0 0 0 3px rgba(255, 107, 53, .12)
    }

    .lookup-btn {
      width: 100%;
      padding: .8rem;
      border: none;
      border-radius: 10px;
      background: linear-gradient(135deg, var(--primary), #FF8C5A);
      color: #fff;
      font-size: .95rem;
      font-weight: 800;
      cursor: pointer;
      font-family: inherit;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: .4rem;
      transition: all .2s;
    }

    .lookup-btn:hover {
      transform: translateY(-1px);
      box-shadow: 0 6px 20px rgba(255, 107, 53, .4)
    }

    .lookup-error {
      background: #FEF2F2;
      border: 1px solid #FECACA;
      border-radius: 10px;
      padding: .75rem 1rem;
      margin-top: .75rem;
      font-size: .82rem;
      color: #DC2626;
      font-weight: 600;
      text-align: center;
    }

    /* ── Booking Found ── */
    .booking-wrap {
      flex: 1;
      background: #F8FAFC;
      border-radius: 24px 24px 0 0;
      margin-top: .5rem;
      overflow: hidden;
    }

    /* Status Header */
    .status-hdr {
      padding: 2rem 1.25rem 1.5rem;
      text-align: center;
      position: relative;
      overflow: hidden;
    }

    <?php if (!empty($booking)):
      $s = $booking['status'];
      if ($s === 'confirmed')     echo '.status-hdr{background:linear-gradient(160deg,#064E3B,#065F46)}';
      elseif ($s === 'pending')   echo '.status-hdr{background:linear-gradient(160deg,#78350F,#92400E)}';
      elseif ($s === 'completed') echo '.status-hdr{background:linear-gradient(160deg,#1E3A5F,#1E40AF)}';
      elseif ($s === 'cancelled' || $s === 'no_show') echo '.status-hdr{background:linear-gradient(160deg,#3B1F1F,#7F1D1D)}';
      else echo '.status-hdr{background:linear-gradient(160deg,#1E293B,#334155)}';
    endif; ?>.status-hdr::before {
      content: '';
      position: absolute;
      inset: 0;
      background: radial-gradient(circle at 80% 20%, rgba(255, 255, 255, .06), transparent 60%);
      pointer-events: none
    }

    .status-icon {
      font-size: 3.5rem;
      margin-bottom: .625rem;
      display: block;
      animation: popIn .4s cubic-bezier(.17, .67, .32, 1.4) both
    }

    @keyframes popIn {
      from {
        transform: scale(.6);
        opacity: 0
      }

      to {
        transform: scale(1);
        opacity: 1
      }
    }

    .status-title {
      font-size: 1.25rem;
      font-weight: 900;
      color: #fff;
      margin-bottom: .25rem
    }

    .status-sub {
      font-size: .8rem;
      color: rgba(255, 255, 255, .65);
      margin-bottom: .875rem
    }

    .status-id {
      font-family: 'JetBrains Mono', monospace;
      font-size: .85rem;
      font-weight: 700;
      color: #fff;
      background: rgba(255, 255, 255, .12);
      padding: .35rem 1rem;
      border-radius: 8px;
      display: inline-block;
      letter-spacing: .06em;
      border: 1px solid rgba(255, 255, 255, .2);
    }

    /* Timeline */
    .timeline {
      padding: 1.25rem;
      background: #fff;
      border-bottom: 1px solid var(--border)
    }

    .tl-title {
      font-size: .72rem;
      font-weight: 800;
      color: #64748B;
      text-transform: uppercase;
      letter-spacing: .06em;
      margin-bottom: .875rem
    }

    .tl {
      display: flex;
      flex-direction: column;
      gap: 0
    }

    .tl-item {
      display: flex;
      gap: .75rem;
      position: relative
    }

    .tl-item:not(:last-child)::before {
      content: '';
      position: absolute;
      left: 11px;
      top: 22px;
      bottom: -4px;
      width: 2px;
      background: var(--border);
      z-index: 0;
    }

    .tl-dot {
      width: 24px;
      height: 24px;
      border-radius: 50%;
      flex-shrink: 0;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: .7rem;
      z-index: 1;
      margin-top: 2px;
    }

    .tl-dot.done {
      background: var(--success);
      color: #fff
    }

    .tl-dot.active {
      background: var(--primary);
      color: #fff
    }

    .tl-dot.pending {
      background: #E2E8F0;
      color: #94A3B8
    }

    .tl-content {
      flex: 1;
      padding-bottom: 1rem
    }

    .tl-label {
      font-size: .85rem;
      font-weight: 700;
      color: #0F172A
    }

    .tl-time {
      font-size: .72rem;
      color: #94A3B8;
      margin-top: .1rem
    }

    /* Info Card */
    .info-card {
      background: #fff;
      margin: .5rem 0;
      padding: 1.1rem 1.25rem
    }

    .info-card-title {
      font-size: .72rem;
      font-weight: 800;
      color: #64748B;
      text-transform: uppercase;
      letter-spacing: .06em;
      margin-bottom: .75rem;
      display: flex;
      align-items: center;
      gap: .4rem
    }

    .info-row {
      display: flex;
      justify-content: space-between;
      padding: .3rem 0;
      font-size: .875rem;
      border-bottom: 1px solid #F8FAFC
    }

    .info-row:last-child {
      border-bottom: none
    }

    .info-lbl {
      color: #64748B
    }

    .info-val {
      font-weight: 700;
      text-align: right
    }

    .rest-row {
      display: flex;
      align-items: center;
      gap: .75rem;
      padding: .5rem 0
    }

    .rest-av {
      width: 44px;
      height: 44px;
      border-radius: 10px;
      background: linear-gradient(135deg, #FF6B35, #FF8C5A);
      color: #fff;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.2rem;
      flex-shrink: 0
    }

    .rest-nm {
      font-weight: 800;
      font-size: .9rem
    }

    .rest-loc {
      font-size: .72rem;
      color: #64748B;
      margin-top: .15rem
    }

    /* Action Buttons */
    .actions-wrap {
      padding: 1rem 1.25rem;
      background: #F8FAFC;
      display: flex;
      flex-direction: column;
      gap: .5rem;
      padding-bottom: calc(1rem + env(safe-area-inset-bottom))
    }

    .act {
      width: 100%;
      padding: .8rem;
      border-radius: 12px;
      font-size: .875rem;
      font-weight: 800;
      border: none;
      cursor: pointer;
      font-family: inherit;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: .4rem;
      transition: all .18s
    }

    .act-primary {
      background: linear-gradient(135deg, var(--primary), #FF8C5A);
      color: #fff;
      box-shadow: 0 4px 14px rgba(255, 107, 53, .3)
    }

    .act-outline {
      background: #fff;
      border: 1.5px solid var(--border);
      color: #334155
    }

    .act-danger {
      background: #FEF2F2;
      color: var(--danger);
      border: 1.5px solid #FECACA
    }

    .act:hover {
      opacity: .9;
      transform: translateY(-1px)
    }

    .act:active {
      transform: scale(.98)
    }
  </style>
</head>

<body>
  <div class="page">

    <!-- Top Bar -->
    <div class="topbar">
      <div class="logo">
        <img src="https://www.DinoviX.ngwebd.com/images/logo2.png" alt="DinoviX Logo" class="nav-logo-img" style="width:140px;">
      </div>
      <button class="home-btn" onclick="location.href='<?= base_url('book') ?>'"><i class="fa fa-house"></i> Discover</button>
    </div>

    <?php if (!$booking): ?>
      <!-- Lookup Form -->
      <div class="lookup-wrap">
        <div class="lookup-icon">🎟</div>
        <div class="lookup-title">Check Your Booking</div>
        <div class="lookup-sub">Enter your booking ID to see<br>reservation status and details</div>
        <div class="lookup-card">
          <form method="GET" action="<?= base_url('book/status') ?>">
            <label class="lookup-label">Booking ID</label>
            <input type="text" name="num" class="lookup-input"
              placeholder="DVX240101ABCD"
              value="<?= esc($numInput ?? '') ?>"
              autocomplete="off" autocorrect="off" spellcheck="false" autofocus>
            <button type="submit" class="lookup-btn"><i class="fa fa-search"></i> Find Booking</button>
          </form>
          <?php if ($showNotFound ?? false): ?>
            <div class="lookup-error"><i class="fa fa-circle-exclamation"></i> Booking not found. Please check the ID.</div>
          <?php endif; ?>
        </div>
      </div>

    <?php else:
      $s = $booking['status'];
      $icons  = ['confirmed' => '✅', 'pending' => '⏳', 'cancelled' => '❌', 'completed' => '🎉', 'no_show' => '🚫'];
      $titles = ['confirmed' => 'Booking Confirmed', 'pending' => 'Awaiting Confirmation', 'cancelled' => 'Booking Cancelled', 'completed' => 'Visit Completed!', 'no_show' => 'Marked No-Show'];
      $subs   = ['confirmed' => 'Your table is reserved. See you soon!', 'pending' => 'The restaurant will confirm shortly.', 'cancelled' => 'This booking was cancelled.', 'completed' => 'Hope you had a great time!', 'no_show' => 'You were marked as no-show for this booking.'];
    ?>

      <!-- Booking Found -->
      <div class="booking-wrap">

        <!-- Status Header -->
        <div class="status-hdr">
          <span class="status-icon"><?= $icons[$s] ?? '📋' ?></span>
          <div class="status-title"><?= $titles[$s] ?? ucfirst($s) ?></div>
          <div class="status-sub"><?= $subs[$s] ?? '' ?></div>
          <div class="status-id"><?= esc($booking['booking_number']) ?></div>
        </div>

        <!-- Timeline -->
        <div class="timeline">
          <div class="tl-title"><i class="fa fa-timeline"></i> Booking Timeline</div>
          <div class="tl">
            <?php
            $steps = [
              'booked'    => ['Booking Created',    $booking['created_at'],   in_array($s, ['pending', 'confirmed', 'completed', 'cancelled', 'no_show'])],
              'confirmed' => ['Confirmed',           $booking['confirmed_at'], in_array($s, ['confirmed', 'completed'])],
              'visit'     => ['Visit',               $booking['slot_date'] . ' ' . $booking['slot_time'], $s === 'completed'],
              'done'      => ['Completed',           null,                     $s === 'completed'],
            ];
            if (in_array($s, ['cancelled', 'no_show'])) {
              $steps['cancelled'] = [ucfirst($s), $booking['cancelled_at'] ?? null, true];
            }
            $stepKeys = array_keys($steps);
            foreach ($steps as $key => [$label, $time, $done]):
              $isActive = ($key === 'confirmed' && $s === 'confirmed') || ($key === 'booked' && $s === 'pending');
            ?>
              <div class="tl-item">
                <div class="tl-dot <?= $done ? 'done' : ($isActive ? 'active' : 'pending') ?>">
                  <i class="fa <?= $done ? 'fa-check' : ($isActive ? 'fa-circle-dot' : 'fa-circle') ?>"></i>
                </div>
                <div class="tl-content">
                  <div class="tl-label"><?= $label ?></div>
                  <?php if ($time): ?>
                    <div class="tl-time"><?= date('d M Y, g:i A', strtotime($time)) ?></div>
                  <?php elseif (!$done): ?>
                    <div class="tl-time" style="color:#CBD5E1">Pending</div>
                  <?php endif; ?>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- Restaurant -->
        <div class="info-card">
          <div class="info-card-title"><i class="fa fa-store"></i> Restaurant</div>
          <div class="rest-row">
            <div class="rest-av">🍽</div>
            <div>
              <div class="rest-nm"><?= esc($booking['rname']) ?></div>
              <div class="rest-loc"><?= esc($booking['theme_color'] ? '' : '') ?></div>
            </div>
          </div>
        </div>

        <!-- Details -->
        <div class="info-card">
          <div class="info-card-title"><i class="fa fa-calendar-check"></i> Details</div>
          <div class="info-row"><span class="info-lbl">Date</span><span class="info-val"><?= date('D, d M Y', strtotime($booking['slot_date'])) ?></span></div>
          <div class="info-row"><span class="info-lbl">Time</span><span class="info-val" style="color:var(--primary)"><?= date('g:i A', strtotime($booking['slot_time'])) ?></span></div>
          <div class="info-row"><span class="info-lbl">Guests</span><span class="info-val"><?= $booking['guests'] ?></span></div>
          <div class="info-row"><span class="info-lbl">Guest</span><span class="info-val"><?= esc($booking['guest_name']) ?></span></div>
          <?php if ($booking['occasion'] && $booking['occasion'] !== 'none'): ?>
            <div class="info-row"><span class="info-lbl">Occasion</span><span class="info-val"><?= ucfirst($booking['occasion']) ?> 🎉</span></div>
          <?php endif; ?>
          <?php if ($booking['payment_amount'] > 0): ?>
            <div class="info-row">
              <span class="info-lbl">Deposit</span>
              <span class="info-val" style="color:<?= $booking['payment_status'] === 'paid' ? 'var(--success)' : 'var(--warning)' ?>">
                ₹<?= number_format($booking['payment_amount'], 2) ?> · <?= ucfirst($booking['payment_status']) ?>
              </span>
            </div>
          <?php endif; ?>
          <?php if ($booking['special_requests']): ?>
            <div class="info-row"><span class="info-lbl">Requests</span><span class="info-val" style="font-style:italic;color:#64748B;font-size:.8rem"><?= esc($booking['special_requests']) ?></span></div>
          <?php endif; ?>
        </div>

        <!-- Actions -->
        <div class="actions-wrap">
          <button class="act act-primary" onclick="shareWA()"><i class="fa-brands fa-whatsapp"></i> Share on WhatsApp</button>
          <a href="<?= base_url('book') ?>" class="act act-outline"><i class="fa fa-search"></i> Find Another Restaurant</a>
          <?php if (in_array($s, ['pending', 'confirmed'])): ?>
            <button class="act act-danger" onclick="cancelBooking()"><i class="fa fa-circle-xmark"></i> Cancel This Booking</button>
          <?php endif; ?>
          <button class="act act-outline" onclick="location.href='<?= base_url('book/status') ?>'" style="font-size:.78rem;padding:.6rem;opacity:.7"><i class="fa fa-search"></i> Look Up Another Booking</button>
        </div>

      </div>
    <?php endif; ?>
  </div>

  <script>
    <?php if (!empty($booking)): ?>
      const NUM = '<?= esc($booking['booking_number']) ?>';
      const RNAME = '<?= esc($booking['rname']) ?>';
      const DATE = '<?= date('d M Y', strtotime($booking['slot_date'])) ?>';
      const TIME = '<?= date('g:i A', strtotime($booking['slot_time'])) ?>';
      const BASE = '<?= base_url() ?>';
      const CN = '<?= csrf_token() ?>';
      const CT = '<?= csrf_hash() ?>';

      function shareWA() {
        const msg = `🍽 My booking at *${RNAME}*\n📅 ${DATE} at ${TIME}\n🎟 ID: *${NUM}*\nBooked via DinoViX`;
        window.open('https://wa.me/?text=' + encodeURIComponent(msg), '_blank');
      }

      async function cancelBooking() {
        if (!confirm('Cancel this booking?')) return;
        const d = await fetch(BASE + 'book/cancel/' + NUM, {
          method: 'POST',
          body: new URLSearchParams({
            [CN]: CT,
            reason: 'Guest cancelled'
          })
        }).then(r => r.json());
        if (d.success) {
          location.reload();
        } else alert(d.message || 'Error cancelling. Try again.');
      }
    <?php endif; ?>
  </script>
</body>

</html>