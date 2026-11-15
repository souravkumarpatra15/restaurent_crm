<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description"
    content="DinoviX CRM — QR ordering, live kitchen display, POS and billing for Indian restaurants. Table to kitchen in seconds.">
  <title>DinoviX — Run Your Restaurant Smarter</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link
    href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500&display=swap"
    rel="stylesheet">
  <style>
    /* ── RESET & BASE ──────────────────────────────────────── */
    *,
    *::before,
    *::after {
      box-sizing: border-box;
      margin: 0;
      padding: 0
    }

    :root {
      --p: #FF6B35;
      --p-d: #E55A20;
      --p-l: rgba(255, 107, 53, .12);
      --g: #22C55E;
      --g-l: rgba(34, 197, 94, .12);
      --bg: #080D1A;
      --bg2: #0E1525;
      --bg3: #151F35;
      --card: rgba(255, 255, 255, .04);
      --card-b: rgba(255, 255, 255, .07);
      --t: #F8FAFC;
      --t2: rgba(248, 250, 252, .55);
      --t3: rgba(248, 250, 252, .28);
      --display: 'Syne', sans-serif;
      --body: 'Inter', sans-serif;
      --mono: 'JetBrains Mono', monospace;
      --r: 14px;
      --r2: 20px;
    }

    html {
      scroll-behavior: smooth
    }

    body {
      background: var(--bg);
      color: var(--t);
      font-family: var(--body);
      line-height: 1.6;
      -webkit-font-smoothing: antialiased;
      overflow-x: hidden
    }

    a {
      color: inherit;
      text-decoration: none
    }

    img {
      max-width: 100%;
      display: block
    }

    ::selection {
      background: var(--p);
      color: #fff
    }

    /* ── NAV ──────────────────────────────────────────────── */
    nav {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      z-index: 100;
      padding: .875rem 2rem;
      display: flex;
      align-items: center;
      gap: 2rem;
      transition: background .3s, backdrop-filter .3s
    }

    nav.scrolled {
      background: rgba(8, 13, 26, .85);
      backdrop-filter: blur(20px);
      border-bottom: 1px solid var(--card-b)
    }

    .nav-logo {
      font-family: var(--display);
      font-size: 1.3rem;
      font-weight: 800;
      color: var(--t);
      display: flex;
      align-items: center;
      gap: .5rem;
      flex-shrink: 0
    }

    .nav-logo span {
      color: var(--p)
    }

    .nav-links {
      display: flex;
      align-items: center;
      gap: 1.75rem;
      margin-left: auto
    }

    .nav-links a {
      font-size: .85rem;
      font-weight: 500;
      color: var(--t2);
      transition: color .15s
    }

    .nav-links a:hover {
      color: var(--t)
    }

    .nav-cta {
      background: var(--p);
      color: #fff;
      font-size: .82rem;
      font-weight: 700;
      padding: .55rem 1.25rem;
      border-radius: 24px;
      transition: background .15s, transform .15s
    }

    .nav-cta:hover {
      background: var(--p-d);
      transform: translateY(-1px)
    }

    .nav-mob {
      display: none;
      background: none;
      border: none;
      color: var(--t);
      font-size: 1.3rem;
      cursor: pointer;
      margin-left: auto
    }

    /* ── HERO ──────────────────────────────────────────────── */
    .hero {
      min-height: 100vh;
      display: flex;
      align-items: center;
      padding: 7rem 2rem 4rem;
      position: relative;
      overflow: hidden
    }

    .hero-bg {
      position: absolute;
      inset: 0;
      pointer-events: none
    }

    .hero-glow1 {
      position: absolute;
      top: -10%;
      left: -5%;
      width: 600px;
      height: 600px;
      background: radial-gradient(circle, rgba(255, 107, 53, .12) 0%, transparent 70%);
      animation: pulse 6s ease-in-out infinite
    }

    .hero-glow2 {
      position: absolute;
      bottom: -10%;
      right: -5%;
      width: 500px;
      height: 500px;
      background: radial-gradient(circle, rgba(34, 197, 94, .08) 0%, transparent 70%);
      animation: pulse 6s ease-in-out 3s infinite
    }

    @keyframes pulse {

      0%,
      100% {
        transform: scale(1);
        opacity: .8
      }

      50% {
        transform: scale(1.1);
        opacity: 1
      }
    }

    .hero-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 4rem;
      align-items: center;
      max-width: 1200px;
      margin: 0 auto;
      width: 100%;
      position: relative;
      z-index: 1
    }

    .hero-eyebrow {
      display: inline-flex;
      align-items: center;
      gap: .5rem;
      font-size: .75rem;
      font-weight: 700;
      color: var(--p);
      background: var(--p-l);
      border: 1px solid rgba(255, 107, 53, .2);
      padding: .35rem .875rem;
      border-radius: 20px;
      margin-bottom: 1.25rem;
      letter-spacing: .05em;
      text-transform: uppercase
    }

    .hero-h1 {
      font-family: var(--display);
      font-size: clamp(2.5rem, 5vw, 4rem);
      font-weight: 800;
      line-height: 1.05;
      letter-spacing: -.025em;
      margin-bottom: 1.25rem
    }

    .hero-h1 .accent {
      color: var(--p)
    }

    .hero-sub {
      font-size: 1.05rem;
      color: var(--t2);
      line-height: 1.7;
      margin-bottom: 2rem;
      max-width: 480px
    }

    .hero-btns {
      display: flex;
      gap: .875rem;
      flex-wrap: wrap;
      margin-bottom: 2.5rem
    }

    .btn-hero-p {
      background: var(--p);
      color: #fff;
      padding: .875rem 2rem;
      border-radius: 12px;
      font-weight: 700;
      font-size: .95rem;
      display: inline-flex;
      align-items: center;
      gap: .5rem;
      transition: all .2s
    }

    .btn-hero-p:hover {
      background: var(--p-d);
      transform: translateY(-2px);
      box-shadow: 0 8px 24px rgba(255, 107, 53, .35)
    }

    .btn-hero-s {
      background: var(--card);
      color: var(--t);
      padding: .875rem 2rem;
      border-radius: 12px;
      font-weight: 600;
      font-size: .95rem;
      display: inline-flex;
      align-items: center;
      gap: .5rem;
      border: 1px solid var(--card-b);
      transition: all .2s
    }

    .btn-hero-s:hover {
      background: rgba(255, 255, 255, .08);
      transform: translateY(-2px)
    }

    .hero-trust {
      display: flex;
      align-items: center;
      gap: 1rem;
      flex-wrap: wrap
    }

    .trust-pill {
      display: flex;
      align-items: center;
      gap: .4rem;
      font-size: .78rem;
      color: var(--t3);
      font-weight: 500
    }

    .trust-pill i {
      color: var(--g);
      font-size: .75rem
    }

    /* ── LIVE TABLE MAP (Hero visual) ─────────────────────── */
    .hero-visual {
      position: relative
    }

    .pos-mockup {
      background: var(--bg3);
      border: 1px solid var(--card-b);
      border-radius: 20px;
      overflow: hidden;
      box-shadow: 0 32px 80px rgba(0, 0, 0, .6), 0 0 0 1px rgba(255, 255, 255, .04);
      position: relative
    }

    .pos-bar {
      background: #0E1525;
      padding: .625rem 1rem;
      display: flex;
      align-items: center;
      gap: .625rem;
      border-bottom: 1px solid rgba(255, 255, 255, .06)
    }

    .pos-bar-dot {
      width: 10px;
      height: 10px;
      border-radius: 50%
    }

    .pos-bar-title {
      font-size: .72rem;
      font-weight: 700;
      color: var(--t2);
      margin-left: .25rem;
      font-family: var(--mono)
    }

    .pos-bar-time {
      margin-left: auto;
      font-family: var(--mono);
      font-size: .68rem;
      color: var(--t3)
    }

    .pos-body {
      padding: 1rem
    }

    .pos-stat-row {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: .5rem;
      margin-bottom: 1rem
    }

    .pos-stat {
      background: rgba(255, 255, 255, .04);
      border: 1px solid rgba(255, 255, 255, .06);
      border-radius: 10px;
      padding: .5rem .625rem;
      text-align: center
    }

    .ps-val {
      font-size: 1.1rem;
      font-weight: 900;
      font-family: var(--display)
    }

    .ps-lbl {
      font-size: .55rem;
      color: var(--t3);
      text-transform: uppercase;
      letter-spacing: .05em;
      margin-top: .1rem
    }

    .area-lbl {
      font-size: .58rem;
      font-weight: 800;
      color: var(--t3);
      letter-spacing: .1em;
      text-transform: uppercase;
      margin-bottom: .5rem
    }

    .table-grid {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: .4rem;
      margin-bottom: .75rem
    }

    .t-tile {
      border-radius: 10px;
      padding: .6rem .4rem;
      text-align: center;
      border: 1.5px solid;
      cursor: pointer;
      transition: all .3s;
      position: relative
    }

    .t-tile .tn {
      font-weight: 900;
      font-size: .85rem;
      line-height: 1
    }

    .t-tile .tc {
      font-size: .5rem;
      opacity: .7;
      margin-top: .15rem
    }

    .t-tile .ts {
      font-size: .5rem;
      font-weight: 900;
      letter-spacing: .05em;
      margin-top: .2rem;
      text-transform: uppercase
    }

    .t-avail {
      background: rgba(34, 197, 94, .08);
      border-color: rgba(34, 197, 94, .3);
      color: #22C55E
    }

    .t-occu {
      background: rgba(239, 68, 68, .08);
      border-color: rgba(239, 68, 68, .3);
      color: #EF4444
    }

    .t-book {
      background: rgba(139, 92, 246, .08);
      border-color: rgba(139, 92, 246, .3);
      color: #8B5CF6
    }

    /* notification pop */
    .notif {
      position: absolute;
      top: .75rem;
      right: .75rem;
      background: #EF4444;
      color: #fff;
      border-radius: 12px;
      padding: .5rem .75rem;
      font-size: .65rem;
      font-weight: 800;
      display: flex;
      align-items: center;
      gap: .35rem;
      box-shadow: 0 4px 16px rgba(239, 68, 68, .4);
      animation: notifpop .4s cubic-bezier(.34, 1.56, .64, 1) both;
      opacity: 0;
      pointer-events: none
    }

    @keyframes notifpop {
      0% {
        transform: scale(.6) translateY(-8px);
        opacity: 0
      }

      100% {
        transform: scale(1) translateY(0);
        opacity: 1
      }
    }

    /* qr scan anim */
    .qr-scan-wrap {
      position: absolute;
      bottom: .75rem;
      left: .75rem;
      background: rgba(8, 13, 26, .92);
      border: 1px solid var(--card-b);
      border-radius: 10px;
      padding: .5rem .75rem;
      display: flex;
      align-items: center;
      gap: .5rem;
      opacity: 0;
      transform: translateY(8px);
      transition: all .4s
    }

    .qr-scan-wrap.show {
      opacity: 1;
      transform: translateY(0)
    }

    .qr-scan-wrap img {
      width: 32px;
      height: 32px
    }

    .qr-text {
      font-size: .62rem;
      color: var(--t2);
      font-weight: 600
    }

    .qr-text b {
      color: var(--g);
      display: block;
      font-size: .7rem
    }

    /* ── LOGOS STRIP ──────────────────────────────────────── */
    .strip {
      padding: 2rem;
      border-top: 1px solid var(--card-b);
      border-bottom: 1px solid var(--card-b);
      background: var(--bg2)
    }

    .strip-inner {
      max-width: 1000px;
      margin: 0 auto;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 3rem;
      flex-wrap: wrap
    }

    .strip-label {
      font-size: .72rem;
      color: var(--t3);
      text-transform: uppercase;
      letter-spacing: .1em;
      font-weight: 600;
      text-align: center;
      width: 100%
    }

    .stat-big {
      text-align: center
    }

    .stat-big-val {
      font-family: var(--display);
      font-size: 2rem;
      font-weight: 800;
      color: var(--t)
    }

    .stat-big-lbl {
      font-size: .72rem;
      color: var(--t3);
      margin-top: .15rem
    }

    /* ── SECTIONS ─────────────────────────────────────────── */
    section {
      padding: 6rem 2rem
    }

    .sec-inner {
      max-width: 1200px;
      margin: 0 auto
    }

    .eyebrow {
      font-size: .72rem;
      font-weight: 700;
      color: var(--p);
      text-transform: uppercase;
      letter-spacing: .1em;
      margin-bottom: .75rem
    }

    .sec-h {
      font-family: var(--display);
      font-size: clamp(2rem, 4vw, 3rem);
      font-weight: 800;
      line-height: 1.1;
      letter-spacing: -.02em;
      margin-bottom: 1rem
    }

    .sec-sub {
      font-size: 1rem;
      color: var(--t2);
      max-width: 560px;
      line-height: 1.7
    }

    /* ── FEATURES GRID ────────────────────────────────────── */
    .feat-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 1.25rem;
      margin-top: 3.5rem
    }

    .feat-card {
      background: var(--card);
      border: 1px solid var(--card-b);
      border-radius: var(--r2);
      padding: 1.75rem;
      transition: all .25s;
      position: relative;
      overflow: hidden
    }

    .feat-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 2px;
      background: linear-gradient(90deg, transparent, var(--p), transparent);
      opacity: 0;
      transition: opacity .3s
    }

    .feat-card:hover {
      background: rgba(255, 255, 255, .06);
      border-color: rgba(255, 107, 53, .2);
      transform: translateY(-4px)
    }

    .feat-card:hover::before {
      opacity: 1
    }

    .feat-icon {
      width: 48px;
      height: 48px;
      border-radius: 14px;
      background: var(--p-l);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.3rem;
      margin-bottom: 1.25rem;
      border: 1px solid rgba(255, 107, 53, .15)
    }

    .feat-title {
      font-family: var(--display);
      font-size: 1.05rem;
      font-weight: 800;
      margin-bottom: .5rem;
      letter-spacing: -.01em
    }

    .feat-desc {
      font-size: .85rem;
      color: var(--t2);
      line-height: 1.65
    }

    .feat-tag {
      display: inline-block;
      font-size: .6rem;
      font-weight: 800;
      color: var(--p);
      background: var(--p-l);
      padding: .18rem .5rem;
      border-radius: 8px;
      margin-top: .875rem;
      letter-spacing: .05em;
      text-transform: uppercase
    }

    /* ── QR FEATURE (full-width section) ─────────────────── */
    .qr-section {
      background: var(--bg2)
    }

    .qr-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 5rem;
      align-items: center
    }

    .qr-steps {
      display: flex;
      flex-direction: column;
      gap: 1.5rem;
      margin-top: 2rem
    }

    .qr-step {
      display: flex;
      gap: 1.25rem;
      align-items: flex-start
    }

    .qr-step-num {
      width: 38px;
      height: 38px;
      border-radius: 50%;
      background: var(--p);
      color: #fff;
      font-family: var(--display);
      font-weight: 800;
      font-size: .9rem;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
      box-shadow: 0 4px 12px rgba(255, 107, 53, .3)
    }

    /* Phone mockup */
    .phone-wrap {
      position: relative;
      display: flex;
      justify-content: center
    }

    .phone {
      width: 260px;
      background: #0E1525;
      border-radius: 36px;
      border: 2px solid rgba(255, 255, 255, .1);
      overflow: hidden;
      box-shadow: 0 32px 64px rgba(0, 0, 0, .6), 0 0 0 8px rgba(255, 255, 255, .03)
    }

    .phone-bar {
      background: #080D1A;
      height: 32px;
      display: flex;
      align-items: center;
      justify-content: center
    }

    .phone-cam {
      width: 12px;
      height: 12px;
      border-radius: 50%;
      background: #1E293B;
      border: 2px solid rgba(255, 255, 255, .1)
    }

    .phone-screen {
      padding: .875rem
    }

    .ph-rest {
      font-family: var(--display);
      font-size: .85rem;
      font-weight: 800;
      color: #fff;
      margin-bottom: .15rem
    }

    .ph-table {
      background: var(--p);
      color: #fff;
      font-size: .58rem;
      font-weight: 800;
      padding: .15rem .4rem;
      border-radius: 10px;
      display: inline-block;
      margin-bottom: .875rem
    }

    .ph-cat-row {
      display: flex;
      gap: .35rem;
      margin-bottom: .75rem;
      overflow: hidden
    }

    .ph-cat {
      background: rgba(255, 255, 255, .06);
      border: 1px solid rgba(255, 255, 255, .1);
      border-radius: 16px;
      padding: .25rem .6rem;
      font-size: .58rem;
      font-weight: 700;
      color: var(--t2);
      white-space: nowrap;
      flex-shrink: 0
    }

    .ph-cat.on {
      background: var(--p);
      color: #fff;
      border-color: var(--p)
    }

    .ph-items {
      display: flex;
      flex-direction: column;
      gap: .5rem
    }

    .ph-item {
      background: rgba(255, 255, 255, .04);
      border: 1px solid rgba(255, 255, 255, .06);
      border-radius: 10px;
      padding: .625rem;
      display: flex;
      align-items: center;
      gap: .625rem
    }

    .ph-item-emoji {
      font-size: 1.1rem;
      flex-shrink: 0
    }

    .ph-item-body {
      flex: 1;
      min-width: 0
    }

    .ph-item-name {
      font-size: .68rem;
      font-weight: 700;
      color: #fff
    }

    .ph-item-price {
      font-size: .62rem;
      color: var(--p);
      font-weight: 800
    }

    .ph-add {
      width: 24px;
      height: 24px;
      border-radius: 50%;
      background: var(--p);
      color: #fff;
      font-size: .75rem;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
      cursor: pointer;
      transition: transform .15s
    }

    .ph-add.added {
      background: var(--g)
    }

    .ph-fab {
      background: var(--p);
      border-radius: 20px;
      padding: .6rem .875rem;
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-top: .75rem
    }

    .ph-fab-l {
      font-size: .62rem;
      font-weight: 800;
      color: rgba(255, 255, 255, .7)
    }

    .ph-fab-r {
      font-size: .65rem;
      font-weight: 800;
      color: #fff
    }

    .phone-glow {
      position: absolute;
      bottom: -20px;
      left: 50%;
      transform: translateX(-50%);
      width: 160px;
      height: 80px;
      background: var(--p);
      filter: blur(40px);
      opacity: .25;
      border-radius: 50%
    }

    /* ── HOW IT WORKS ─────────────────────────────────────── */
    .hiw-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 2rem;
      margin-top: 3.5rem;
      position: relative
    }

    .hiw-grid::before {
      content: '';
      position: absolute;
      top: 32px;
      left: 16.6%;
      right: 16.6%;
      height: 1px;
      background: linear-gradient(90deg, transparent, var(--p), var(--p), transparent);
      opacity: .3
    }

    .hiw-card {
      text-align: center;
      padding: 2rem 1.5rem;
      position: relative
    }

    .hiw-num {
      width: 56px;
      height: 56px;
      border-radius: 50%;
      background: var(--bg3);
      border: 2px solid var(--p);
      color: var(--p);
      font-family: var(--display);
      font-size: 1.1rem;
      font-weight: 800;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 1.25rem
    }

    .hiw-icon {
      font-size: 2.5rem;
      margin-bottom: .75rem
    }

    .hiw-title {
      font-family: var(--display);
      font-size: 1.05rem;
      font-weight: 800;
      margin-bottom: .5rem
    }

    .hiw-desc {
      font-size: .85rem;
      color: var(--t2);
      line-height: 1.65
    }

    /* ── PRICING ──────────────────────────────────────────── */
    .pricing-section {
      background: var(--bg2)
    }

    .price-grid {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 1.25rem;
      margin-top: 3.5rem
    }

    .price-card {
      background: var(--bg3);
      border: 1.5px solid var(--card-b);
      border-radius: var(--r2);
      padding: 2rem;
      position: relative;
      transition: all .25s
    }

    .price-card.popular {
      border-color: var(--p);
      background: linear-gradient(135deg, rgba(255, 107, 53, .06), rgba(255, 107, 53, .02))
    }

    .popular-badge {
      position: absolute;
      top: -14px;
      left: 50%;
      transform: translateX(-50%);
      background: var(--p);
      color: #fff;
      font-size: .65rem;
      font-weight: 800;
      padding: .3rem .875rem;
      border-radius: 20px;
      letter-spacing: .05em;
      text-transform: uppercase;
      white-space: nowrap
    }

    .price-plan {
      font-size: .72rem;
      font-weight: 800;
      color: var(--t3);
      text-transform: uppercase;
      letter-spacing: .1em;
      margin-bottom: .5rem
    }

    .price-val {
      font-family: var(--display);
      font-size: 2.5rem;
      font-weight: 800;
      line-height: 1;
      margin-bottom: .25rem
    }

    .price-val sup {
      font-size: 1.2rem;
      vertical-align: top;
      margin-top: .4rem
    }

    .price-cycle {
      font-size: .78rem;
      color: var(--t3);
      margin-bottom: .25rem
    }

    .price-yearly {
      font-size: .75rem;
      color: var(--g);
      font-weight: 700;
      margin-bottom: 1.5rem
    }

    .price-desc {
      font-size: .82rem;
      color: var(--t2);
      margin-bottom: 1.5rem;
      padding-bottom: 1.5rem;
      border-bottom: 1px solid var(--card-b)
    }

    .price-feats {
      display: flex;
      flex-direction: column;
      gap: .625rem;
      margin-bottom: 1.75rem
    }

    .pf {
      display: flex;
      align-items: center;
      gap: .625rem;
      font-size: .82rem;
      color: var(--t2)
    }

    .pf i {
      color: var(--g);
      font-size: .75rem;
      flex-shrink: 0
    }

    .pf.no {
      color: var(--t3)
    }

    .pf.no i {
      color: var(--t3)
    }

    .price-btn {
      width: 100%;
      padding: .875rem;
      border-radius: 12px;
      font-family: var(--body);
      font-weight: 700;
      font-size: .9rem;
      cursor: pointer;
      border: none;
      transition: all .2s;
      text-align: center;
      display: block
    }

    .price-btn.p {
      background: var(--p);
      color: #fff
    }

    .price-btn.p:hover {
      background: var(--p-d);
      transform: translateY(-2px);
      box-shadow: 0 8px 24px rgba(255, 107, 53, .3)
    }

    .price-btn.s {
      background: var(--card);
      color: var(--t);
      border: 1px solid var(--card-b)
    }

    .price-btn.s:hover {
      background: rgba(255, 255, 255, .08)
    }

    .price-note {
      text-align: center;
      margin-top: 1.25rem;
      font-size: .78rem;
      color: var(--t3)
    }

    .price-note a {
      color: var(--p);
      font-weight: 600
    }

    /* ── TESTIMONIALS ─────────────────────────────────────── */
    .testi-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 1.25rem;
      margin-top: 3rem
    }

    .testi {
      background: var(--card);
      border: 1px solid var(--card-b);
      border-radius: var(--r2);
      padding: 1.5rem
    }

    .testi-quote {
      font-size: .9rem;
      color: var(--t2);
      line-height: 1.7;
      margin-bottom: 1.25rem;
      font-style: italic
    }

    .testi-quote::before {
      content: '"';
      font-size: 2rem;
      color: var(--p);
      line-height: 0;
      vertical-align: -.4rem;
      margin-right: .15rem;
      font-family: Georgia, serif
    }

    .testi-author {
      display: flex;
      align-items: center;
      gap: .75rem
    }

    .testi-avatar {
      width: 38px;
      height: 38px;
      border-radius: 50%;
      background: var(--p);
      color: #fff;
      font-family: var(--display);
      font-weight: 800;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: .85rem;
      flex-shrink: 0
    }

    .testi-name {
      font-weight: 700;
      font-size: .85rem
    }

    .testi-role {
      font-size: .72rem;
      color: var(--t3)
    }

    .stars {
      color: #F59E0B;
      font-size: .75rem;
      margin-top: .15rem;
      letter-spacing: .1em
    }

    /* ── FAQ ──────────────────────────────────────────────── */
    .faq-list {
      max-width: 720px;
      margin: 3rem auto 0;
      display: flex;
      flex-direction: column;
      gap: .75rem
    }

    .faq-item {
      background: var(--card);
      border: 1px solid var(--card-b);
      border-radius: var(--r);
      overflow: hidden
    }

    .faq-q {
      padding: 1.25rem 1.5rem;
      font-weight: 700;
      font-size: .9rem;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 1rem;
      transition: color .15s
    }

    .faq-q:hover {
      color: var(--p)
    }

    .faq-q i {
      color: var(--t3);
      font-size: .8rem;
      transition: transform .25s;
      flex-shrink: 0
    }

    .faq-a {
      max-height: 0;
      overflow: hidden;
      transition: max-height .35s ease, padding .25s
    }

    .faq-a-inner {
      padding: 0 1.5rem 1.25rem;
      font-size: .87rem;
      color: var(--t2);
      line-height: 1.7
    }

    .faq-item.open .faq-q i {
      transform: rotate(180deg)
    }

    .faq-item.open .faq-a {
      max-height: 300px
    }

    /* ── CTA BOTTOM ───────────────────────────────────────── */
    .cta-section {
      background: linear-gradient(135deg, rgba(255, 107, 53, .08), rgba(34, 197, 94, .05));
      border-top: 1px solid var(--card-b);
      text-align: center;
      padding: 7rem 2rem
    }

    .cta-h {
      font-family: var(--display);
      font-size: clamp(2rem, 4vw, 3rem);
      font-weight: 800;
      letter-spacing: -.025em;
      margin-bottom: 1rem
    }

    .cta-sub {
      font-size: 1rem;
      color: var(--t2);
      margin-bottom: 2.5rem;
      max-width: 500px;
      margin-left: auto;
      margin-right: auto;
      line-height: 1.7
    }

    .cta-btns {
      display: flex;
      gap: 1rem;
      justify-content: center;
      flex-wrap: wrap
    }

    .cta-input-row {
      display: flex;
      gap: .75rem;
      max-width: 420px;
      margin: 0 auto 1.5rem;
      flex-wrap: wrap
    }

    .cta-input {
      flex: 1;
      min-width: 200px;
      padding: .875rem 1.25rem;
      background: rgba(255, 255, 255, .06);
      border: 1.5px solid var(--card-b);
      border-radius: 12px;
      color: #fff;
      font-family: var(--body);
      font-size: .9rem;
      outline: none;
      transition: border-color .15s
    }

    .cta-input:focus {
      border-color: var(--p)
    }

    .cta-input::placeholder {
      color: var(--t3)
    }

    .cta-btn {
      background: var(--p);
      color: #fff;
      padding: .875rem 2rem;
      border-radius: 12px;
      font-weight: 700;
      font-family: var(--body);
      font-size: .9rem;
      border: none;
      cursor: pointer;
      transition: all .2s;
      white-space: nowrap
    }

    .cta-btn:hover {
      background: var(--p-d);
      transform: translateY(-2px);
      box-shadow: 0 8px 24px rgba(255, 107, 53, .35)
    }

    .cta-guarantee {
      font-size: .75rem;
      color: var(--t3);
      margin-top: .75rem
    }

    /* ── FOOTER ───────────────────────────────────────────── */
    footer {
      background: var(--bg2);
      border-top: 1px solid var(--card-b);
      padding: 3rem 2rem
    }

    .footer-inner {
      max-width: 1200px;
      margin: 0 auto;
      display: grid;
      grid-template-columns: 1.5fr 1fr 1fr 1fr;
      gap: 3rem
    }

    .footer-brand .nav-logo {
      margin-bottom: .875rem;
      font-size: 1.2rem
    }

    .footer-brand p {
      font-size: .83rem;
      color: var(--t3);
      line-height: 1.7;
      max-width: 260px
    }

    .footer-col h4 {
      font-size: .78rem;
      font-weight: 800;
      color: var(--t);
      text-transform: uppercase;
      letter-spacing: .08em;
      margin-bottom: .875rem
    }

    .footer-col a {
      display: block;
      font-size: .83rem;
      color: var(--t3);
      margin-bottom: .5rem;
      transition: color .15s
    }

    .footer-col a:hover {
      color: var(--p)
    }

    .footer-bottom {
      max-width: 1200px;
      margin: 2rem auto 0;
      padding-top: 1.5rem;
      border-top: 1px solid var(--card-b);
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
      gap: 1rem
    }

    .footer-bottom p {
      font-size: .78rem;
      color: var(--t3)
    }

    /* ── SCROLL REVEAL ────────────────────────────────────── */
    .reveal {
      opacity: 0;
      transform: translateY(28px);
      transition: opacity .6s ease, transform .6s ease
    }

    .reveal.visible {
      opacity: 1;
      transform: translateY(0)
    }

    /* ── MOBILE NAV DRAWER ────────────────────────────────── */
    .mob-menu {
      position: fixed;
      top: 0;
      right: -100%;
      width: 280px;
      height: 100vh;
      background: #0E1525;
      border-left: 1px solid var(--card-b);
      z-index: 200;
      padding: 5rem 2rem 2rem;
      display: flex;
      flex-direction: column;
      gap: 1.5rem;
      transition: right .3s;
      box-shadow: -16px 0 40px rgba(0, 0, 0, .4)
    }

    .mob-menu.open {
      right: 0
    }

    .mob-menu a {
      font-size: 1rem;
      font-weight: 600;
      color: var(--t2);
      padding: .5rem 0;
      border-bottom: 1px solid var(--card-b)
    }

    .mob-overlay {
      position: fixed;
      inset: 0;
      background: rgba(0, 0, 0, .6);
      z-index: 199;
      display: none
    }

    .mob-overlay.open {
      display: block
    }

    .mob-close {
      position: absolute;
      top: 1.25rem;
      right: 1.25rem;
      background: none;
      border: none;
      color: var(--t2);
      font-size: 1.3rem;
      cursor: pointer
    }

    /* ── RESPONSIVE ───────────────────────────────────────── */
    @media(max-width:1024px) {
      .hero-grid {
        grid-template-columns: 1fr;
        text-align: center
      }

      .hero-sub {
        margin-left: auto;
        margin-right: auto
      }

      .hero-btns {
        justify-content: center
      }

      .hero-trust {
        justify-content: center
      }

      .hero-visual {
        display: none
      }

      .feat-grid {
        grid-template-columns: repeat(2, 1fr)
      }

      .qr-grid {
        grid-template-columns: 1fr;
        gap: 2.5rem
      }

      .phone-wrap {
        order: -1
      }

      .hiw-grid::before {
        display: none
      }

      .price-grid {
        grid-template-columns: 1fr
      }

      .price-card.popular {
        transform: none
      }

      .testi-grid {
        grid-template-columns: 1fr
      }

      .footer-inner {
        grid-template-columns: 1fr 1fr
      }

      .nav-links {
        display: none
      }

      .nav-mob {
        display: block
      }
    }

    @media(max-width:640px) {
      section {
        padding: 4rem 1.25rem
      }

      .hero {
        padding: 5.5rem 1.25rem 3rem
      }

      .feat-grid {
        grid-template-columns: 1fr
      }

      .hiw-grid {
        grid-template-columns: 1fr;
        gap: 1.5rem
      }

      .footer-inner {
        grid-template-columns: 1fr
      }

      .strip-inner {
        gap: 2rem
      }

      nav {
        padding: .75rem 1.25rem
      }
    }
  </style>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    /* ═══ GRAPHICAL v2 — feature vignettes, showcases, extra motion ═══ */
    /* ── shared keyframes ── */
    @keyframes floaty {0%,100%{transform:translateY(0) rotate(-3deg)}50%{transform:translateY(-16px) rotate(3deg)}}
    @keyframes floaty2 {0%,100%{transform:translateY(0) rotate(4deg)}50%{transform:translateY(-12px) rotate(-4deg)}}
    @keyframes marquee {from{transform:translateX(0)}to{transform:translateX(-50%)}}
    @keyframes pulseDot {0%,100%{box-shadow:0 0 0 0 rgba(255,107,53,.55)}70%{box-shadow:0 0 0 9px rgba(255,107,53,0)}}
    @keyframes greenPulse {0%,100%{box-shadow:0 0 0 0 rgba(34,197,94,.5)}70%{box-shadow:0 0 0 8px rgba(34,197,94,0)}}
    @keyframes growBar {from{transform:scaleY(.05)}to{transform:scaleY(1)}}
    @keyframes slideKot {0%{opacity:0;transform:translateY(16px) scale(.94)}12%,86%{opacity:1;transform:none}100%{opacity:0;transform:translateY(-12px) scale(.97)}}
    @keyframes hotTimer {0%,100%{color:#F59E0B}50%{color:#EF4444}}
    @keyframes scanline {0%{top:10%}50%{top:78%}100%{top:10%}}
    @keyframes dashFlow {to{stroke-dashoffset:-28}}
    @keyframes linkFlow {to{background-position:8px 0}}
    @keyframes shine {from{transform:translateX(-120%) rotate(18deg)}to{transform:translateX(240%) rotate(18deg)}}
    @keyframes wiggle {0%,100%{transform:rotate(0)}25%{transform:rotate(-9deg)}75%{transform:rotate(9deg)}}
    @keyframes stepFill {0%{width:0}100%{width:100%}}
    @keyframes coinFlip {0%,100%{transform:rotateY(0)}50%{transform:rotateY(180deg)}}
    @keyframes printOut {0%,100%{transform:translateY(6px);clip-path:inset(30% 0 0 0)}50%{transform:translateY(0);clip-path:inset(0 0 0 0)}}
    @keyframes toggleFlip {0%,100%{left:2px;background:#64748B}50%{left:calc(100% - 16px);background:#22C55E}}
    @keyframes starPop {0%,100%{transform:scale(1)}50%{transform:scale(1.25)}}

    /* ── floating decorations in hero ── */
    .float-deco{position:absolute;font-size:1.7rem;opacity:.16;pointer-events:none;filter:grayscale(.2);animation:floaty 7s ease-in-out infinite;z-index:0}
    .float-deco.d2{animation:floaty2 9s ease-in-out infinite;animation-delay:-3s}
    .float-deco.d3{animation:floaty 11s ease-in-out infinite;animation-delay:-5s}

    /* ── capability marquee ── */
    .marquee{overflow:hidden;border-top:1px solid var(--card-b);border-bottom:1px solid var(--card-b);background:var(--bg2);padding:.8rem 0;position:relative}
    .marquee-track{display:flex;gap:2.5rem;width:max-content;animation:marquee 28s linear infinite;will-change:transform}
    .marquee-item{font-family:var(--display);font-weight:800;font-size:.82rem;letter-spacing:.08em;text-transform:uppercase;color:var(--t3);white-space:nowrap;display:flex;align-items:center;gap:2.5rem}
    .marquee-item b{color:var(--p)}
    .marquee:hover .marquee-track{animation-play-state:paused}

    /* ── features v2 (point-wise bento) ── */
    .feat-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:1.25rem;margin-top:3.5rem}
    .feat-card{display:flex;flex-direction:column;cursor:default;transform-style:preserve-3d;will-change:transform}
    .feat-card.wide{grid-column:span 2}
    .feat-vig{position:relative;height:132px;border-radius:14px;background:linear-gradient(160deg,rgba(255,255,255,.05),rgba(255,255,255,.015));border:1px solid var(--card-b);margin-bottom:1.15rem;overflow:hidden;display:flex;align-items:center;justify-content:center}
    .feat-vig::after{content:'';position:absolute;top:-40%;left:-60%;width:45%;height:180%;background:linear-gradient(90deg,transparent,rgba(255,255,255,.07),transparent);animation:shine 4.5s ease-in-out infinite;animation-delay:1.2s;pointer-events:none}
    .feat-card:hover .feat-icon{animation:wiggle .5s ease}
    .feat-points{list-style:none;margin:.65rem 0 .2rem;flex:1}
    .feat-points li{font-size:.78rem;color:var(--t2);line-height:1.5;padding:.26rem 0 .26rem 1.35rem;position:relative}
    .feat-points li::before{content:'✓';position:absolute;left:0;top:.28rem;font-weight:900;color:var(--g);font-size:.72rem}
    /* vignette atoms */
    .vig-row{display:flex;gap:8px;align-items:center;justify-content:center;width:100%;padding:0 14px}
    .vig-phone{width:52px;height:96px;border-radius:10px;background:#0E1525;border:1.5px solid rgba(255,107,53,.4);position:relative;overflow:hidden;flex:none}
    .vig-phone .scan{position:absolute;left:8%;right:8%;height:2px;background:linear-gradient(90deg,transparent,#FF6B35,transparent);animation:scanline 2.6s ease-in-out infinite;border-radius:2px}
    .vig-phone .pl1,.vig-phone .pl2,.vig-phone .pl3{position:absolute;left:8px;right:8px;height:7px;border-radius:3px;background:rgba(255,255,255,.12)}
    .vig-phone .pl1{top:18px;width:70%}.vig-phone .pl2{top:30px;width:55%}.vig-phone .pl3{top:44px;width:62%}
    .vig-phone .pfb{position:absolute;bottom:8px;left:8px;right:8px;height:16px;border-radius:6px;background:var(--p);display:flex;align-items:center;justify-content:center;font-size:.5rem;font-weight:900;color:#fff}
    .vig-chip{padding:.42rem .7rem;border-radius:10px;font-size:.66rem;font-weight:800;white-space:nowrap;background:rgba(255,255,255,.06);border:1px solid var(--card-b);color:var(--t2)}
    .vig-chip.on{background:var(--p-l);border-color:rgba(255,107,53,.35);color:var(--p);animation:pulseDot 2.4s infinite}
    .vig-chip.ok{background:var(--g-l);border-color:rgba(34,197,94,.3);color:var(--g)}
    .vig-chip.wait{background:rgba(245,158,11,.1);border-color:rgba(245,158,11,.3);color:#F59E0B}
    /* KOT vignette */
    .vig-kot{display:flex;gap:9px}
    .vig-kot .k{width:58px;border-radius:9px;background:#0E1525;border:1px solid var(--card-b);padding:7px 7px 8px;animation:slideKot 6s ease-in-out infinite}
    .vig-kot .k:nth-child(2){animation-delay:1.1s;border-color:rgba(255,107,53,.35)}
    .vig-kot .k:nth-child(3){animation-delay:2.2s}
    .vig-kot .kt{font-size:.56rem;font-weight:900;color:#fff}
    .vig-kot .ktb{font-size:.5rem;color:var(--t3);margin-top:2px}
    .vig-kot .ktt{font-size:.56rem;font-weight:800;margin-top:5px;animation:hotTimer 2s infinite}
    .vig-kot .kbar{height:3px;border-radius:2px;background:rgba(255,255,255,.1);margin-top:4px;overflow:hidden}
    .vig-kot .kbar i{display:block;height:100%;background:var(--p);border-radius:2px;animation:stepFill 6s linear infinite}
    .vig-kot .k:nth-child(2) .kbar i{animation-delay:-1.4s}
    .vig-kot .k:nth-child(3) .kbar i{animation-delay:-2.8s}
    /* table tiles vignette */
    .vig-tiles{display:grid;grid-template-columns:repeat(4,1fr);gap:7px;width:80%}
    .vig-t{border-radius:8px;aspect-ratio:1.25;display:flex;align-items:center;justify-content:center;font-size:.55rem;font-weight:900;color:#fff;background:rgba(34,197,94,.16);border:1px solid rgba(34,197,94,.35)}
    .vig-t.o{background:rgba(255,107,53,.18);border-color:rgba(255,107,53,.4);animation:pulseDot 2.8s infinite}
    .vig-t.b{background:rgba(139,92,246,.16);border-color:rgba(139,92,246,.4)}
    /* map + rider vignette */
    .vig-map{position:relative;width:100%;height:100%}
    .vig-map svg{position:absolute;inset:0;width:100%;height:100%}
    .vig-eta{position:absolute;top:10px;left:12px;display:flex;gap:6px}
    /* UPI vignette */
    .vig-upi{display:flex;gap:14px;align-items:center}
    .vig-qr{width:64px;height:64px;border-radius:10px;background:#fff;padding:7px;display:grid;grid-template-columns:repeat(4,1fr);grid-template-rows:repeat(4,1fr);gap:2px;flex:none;position:relative}
    .vig-qr i{background:#0F172A;border-radius:1px}
    .vig-qr i:nth-child(3n){opacity:.45}
    .vig-qr::after{content:'';position:absolute;inset:-5px;border:2px solid rgba(34,197,94,.55);border-radius:13px;animation:greenPulse 2.2s infinite}
    .vig-split{display:flex;flex-direction:column;gap:5px}
    .vig-split .sr{display:flex;align-items:center;gap:7px;font-size:.62rem;font-weight:700;color:var(--t2)}
    .vig-split .sr b{color:#fff;font-weight:800}
    .vig-split .dot{width:9px;height:9px;border-radius:50%;background:#22C55E;flex:none}
    .vig-split .dot.o{background:#F59E0B;animation:pulseDot 2s infinite}
    /* booking vignette */
    .vig-cal{display:grid;grid-template-columns:repeat(5,1fr);gap:6px;width:78%}
    .vig-c{aspect-ratio:1.15;border-radius:8px;background:rgba(255,255,255,.05);border:1px solid var(--card-b);display:flex;flex-direction:column;align-items:center;justify-content:center;gap:2px}
    .vig-c b{font-size:.6rem;color:var(--t2)}
    .vig-c span{font-size:.48rem;color:var(--t3)}
    .vig-c.hot{background:var(--p-l);border-color:rgba(255,107,53,.45);animation:pulseDot 2.6s infinite}
    .vig-c.hot b{color:var(--p)}
    /* loyalty vignette */
    .vig-loy{display:flex;align-items:center;gap:14px}
    .vig-coin{width:52px;height:52px;border-radius:50%;background:radial-gradient(circle at 32% 30%,#FBBF24,#B45309);display:flex;align-items:center;justify-content:center;font-size:1.2rem;animation:coinFlip 4s ease infinite;box-shadow:0 6px 18px rgba(251,191,36,.25)}
    .vig-stamps{display:grid;grid-template-columns:repeat(5,1fr);gap:5px}
    .vig-stamps i{width:16px;height:16px;border-radius:50%;border:1.5px dashed rgba(255,255,255,.25);display:flex;align-items:center;justify-content:center;font-size:.5rem;color:var(--g)}
    .vig-stamps i.on{background:var(--g-l);border:1.5px solid rgba(34,197,94,.5);animation:starPop 2.4s infinite}
    /* branch vignette */
    .vig-branch{display:flex;align-items:center;gap:10px}
    .vig-b{width:44px;height:52px;border-radius:10px;background:#0E1525;border:1px solid var(--card-b);display:flex;flex-direction:column;align-items:center;justify-content:center;gap:3px}
    .vig-b b{font-size:.55rem;color:#fff}.vig-b span{font-size:.44rem;color:var(--t3)}
    .vig-b.hq{border-color:rgba(255,107,53,.45);animation:pulseDot 2.8s infinite}
    .vig-link{width:22px;height:2px;background:repeating-linear-gradient(90deg,rgba(255,255,255,.3) 0 4px,transparent 4px 8px);background-size:8px 2px;animation:linkFlow .7s linear infinite}
    /* printer vignette */
    .vig-print{display:flex;flex-direction:column;align-items:center;gap:0}
    .vig-slot{width:120px;height:26px;border-radius:8px 8px 4px 4px;background:#1E293B;border:1px solid var(--card-b);position:relative;z-index:2}
    .vig-slot::before{content:'';position:absolute;left:10px;right:10px;top:10px;height:3px;background:#0F172A;border-radius:2px}
    .vig-slip{width:86px;background:#fff;border-radius:0 0 5px 5px;padding:6px 8px;margin-top:-2px;animation:printOut 3.4s ease-in-out infinite;z-index:1}
    .vig-slip i{display:block;height:3px;border-radius:2px;background:#94A3B8;margin-bottom:4px}
    .vig-slip i:nth-child(2){width:72%}.vig-slip i:nth-child(3){width:55%;margin-bottom:0}
    .vig-slip b{display:block;font-size:.5rem;color:#0F172A;font-weight:900;text-align:center;margin-bottom:4px}
    /* chart vignette */
    .vig-chart{display:flex;align-items:flex-end;gap:8px;height:82px;width:64%}
    .vig-chart i{flex:1;border-radius:5px 5px 0 0;background:linear-gradient(180deg,var(--p),rgba(255,107,53,.35));transform-origin:bottom;animation:growBar 1.6s cubic-bezier(.34,1.4,.64,1) both}
    .vig-chart i:nth-child(2){animation-delay:.15s;height:55%;background:linear-gradient(180deg,#F59E0B,rgba(245,158,11,.3))}
    .vig-chart i:nth-child(3){animation-delay:.3s;height:78%}
    .vig-chart i:nth-child(4){animation-delay:.45s;height:46%;background:linear-gradient(180deg,#22C55E,rgba(34,197,94,.3))}
    .vig-chart i:nth-child(5){animation-delay:.6s;height:92%}
    .vig-chart i:nth-child(1){height:64%}
    /* permissions matrix vignette */
    .vig-matrix{display:flex;flex-direction:column;gap:6px;width:66%}
    .vig-mrow{display:flex;align-items:center;justify-content:space-between;background:rgba(255,255,255,.04);border:1px solid var(--card-b);border-radius:8px;padding:5px 9px}
    .vig-mrow span{font-size:.6rem;font-weight:700;color:var(--t2)}
    .vig-mrow .tgl{width:26px;height:13px;border-radius:8px;background:#334155;position:relative}
    .vig-mrow .tgl::after{content:'';position:absolute;top:2px;left:2px;width:9px;height:9px;border-radius:50%;background:#64748B}
    .vig-mrow .tgl.on::after{left:15px;background:#22C55E}
    .vig-mrow:nth-child(2) .tgl{animation:toggleFlip 3.6s ease infinite}
    /* feedback stars vignette */
    .vig-stars{display:flex;gap:8px;font-size:1.3rem}
    .vig-stars i{font-style:normal;color:#334155;animation:starPop 2.8s infinite}
    .vig-stars i.on{color:#FBBF24}
    .vig-stars i:nth-child(2){animation-delay:.2s}.vig-stars i:nth-child(3){animation-delay:.4s}
    .vig-stars i:nth-child(4){animation-delay:.6s}.vig-stars i:nth-child(5){animation-delay:.8s}
    .vig-bubble{position:absolute;bottom:10px;left:14px;font-size:.6rem;font-weight:700;color:var(--t2);background:rgba(255,255,255,.06);border:1px solid var(--card-b);padding:.3rem .6rem;border-radius:10px 10px 10px 3px}

    /* ── showcase: KITCHEN ── */
    .kds-section{background:var(--bg2)}
    .kds-grid{display:grid;grid-template-columns:1fr 1.15fr;gap:4rem;align-items:center}
    .kds-screen{border-radius:20px;background:#0A0F1E;border:1px solid rgba(255,255,255,.08);padding:1rem;box-shadow:0 30px 80px rgba(0,0,0,.5);position:relative}
    .kds-top{display:flex;justify-content:space-between;align-items:center;margin-bottom:.9rem}
    .kds-top b{color:#fff;font-size:.8rem;letter-spacing:.06em}
    .kds-top span{font-size:.68rem;color:#22C55E;font-weight:700}
    .kds-cards{display:grid;grid-template-columns:1fr 1fr;gap:.7rem}
    .kds-card{border-radius:12px;padding:.7rem .75rem;border:1px solid rgba(255,255,255,.09);background:#111A2E;animation:slideKot 8s ease-in-out infinite}
    .kds-card:nth-child(2){animation-delay:1.4s;border-color:rgba(255,107,53,.4)}
    .kds-card:nth-child(3){animation-delay:2.8s}
    .kds-card:nth-child(4){animation-delay:4.2s;animation-duration:9s}
    .kds-hd{display:flex;justify-content:space-between;align-items:center;margin-bottom:.45rem}
    .kds-hd b{color:#fff;font-size:.72rem}
    .kds-timer{font-size:.68rem;font-weight:900;animation:hotTimer 2.4s infinite}
    .kds-items{font-size:.66rem;color:#94A3B8;line-height:1.6}
    .kds-items b{color:#E2E8F0}
    .kds-foot{margin-top:.5rem;height:4px;border-radius:3px;background:rgba(255,255,255,.08);overflow:hidden}
    .kds-foot i{display:block;height:100%;border-radius:3px;background:var(--p);animation:stepFill 8s linear infinite}
    .kds-card:nth-child(2) .kds-foot i{animation-delay:-1.6s;background:#F59E0B}
    .kds-card:nth-child(3) .kds-foot i{animation-delay:-3.2s;background:#22C55E}
    .kds-card:nth-child(4) .kds-foot i{animation-delay:-4.8s}
    .kds-blink{position:absolute;top:-10px;right:24px;display:flex;align-items:center;gap:.45rem;background:var(--g-l);border:1px solid rgba(34,197,94,.35);color:var(--g);font-size:.66rem;font-weight:800;padding:.4rem .7rem;border-radius:20px;animation:pulseDot 2.2s infinite}

    /* ── showcase: DELIVERY ── */
    .del-grid{display:grid;grid-template-columns:1.15fr 1fr;gap:4rem;align-items:center}
    .del-map{border-radius:20px;background:#0A0F1E;border:1px solid rgba(255,255,255,.08);padding:1rem;box-shadow:0 30px 80px rgba(0,0,0,.5);position:relative;overflow:hidden;min-height:340px}
    .del-map svg{display:block;width:100%;height:300px}
    .del-chip{position:absolute;display:flex;align-items:center;gap:.45rem;font-size:.66rem;font-weight:800;padding:.42rem .7rem;border-radius:20px}
    .del-chip.auto{top:14px;left:14px;background:var(--p-l);border:1px solid rgba(255,107,53,.4);color:var(--p);animation:pulseDot 2.4s infinite}
    .del-chip.eta{bottom:14px;right:14px;background:rgba(34,197,94,.12);border:1px solid rgba(34,197,94,.35);color:#22C55E}
    .del-steps{display:flex;flex-direction:column;gap:.7rem;margin-top:1.6rem}
    .del-step{display:flex;gap:.85rem;align-items:flex-start;background:var(--card);border:1px solid var(--card-b);border-radius:14px;padding:.8rem .9rem;transition:border-color .3s}
    .del-step .n{width:28px;height:28px;border-radius:50%;background:var(--p-l);color:var(--p);font-weight:900;font-size:.75rem;display:flex;align-items:center;justify-content:center;flex:none;border:1px solid rgba(255,107,53,.25)}
    .del-step b{display:block;font-size:.85rem;margin-bottom:.15rem}
    .del-step span{font-size:.74rem;color:var(--t2);line-height:1.5}
    .del-step.hot{border-color:rgba(255,107,53,.45);animation:ridePulse 2.4s infinite}
    @keyframes ridePulse{0%,100%{box-shadow:0 0 0 0 rgba(255,107,53,.28)}70%{box-shadow:0 0 0 7px rgba(255,107,53,0)}}

    /* ── counters ── */
    .stat-big-val{font-variant-numeric:tabular-nums}
    /* stagger the feature card reveals */
    .feat-grid .reveal:nth-child(2){transition-delay:.06s}
    .feat-grid .reveal:nth-child(3){transition-delay:.12s}
    .feat-grid .reveal:nth-child(4){transition-delay:.18s}
    .feat-grid .reveal:nth-child(5){transition-delay:.24s}
    .feat-grid .reveal:nth-child(6){transition-delay:.3s}
    .feat-grid .reveal:nth-child(7){transition-delay:.05s}
    .feat-grid .reveal:nth-child(8){transition-delay:.1s}
    .feat-grid .reveal:nth-child(9){transition-delay:.15s}
    .feat-grid .reveal:nth-child(10){transition-delay:.2s}
    .feat-grid .reveal:nth-child(11){transition-delay:.25s}
    .feat-grid .reveal:nth-child(12){transition-delay:.3s}
    @media (prefers-reduced-motion:reduce){
      .marquee-track,.float-deco,.vig-kot .k,.kds-card,.feat-vig::after{animation:none!important}
    }

    @media (max-width:980px){
      .feat-grid{grid-template-columns:1fr 1fr}
      .feat-card.wide{grid-column:span 2}
      .kds-grid,.del-grid{grid-template-columns:1fr;gap:2.5rem}
      .del-map{min-height:280px}
    }
    @media (max-width:640px){
      .feat-grid{grid-template-columns:1fr}
      .feat-card.wide{grid-column:span 1}
      .vig-tiles{width:92%}
    }
  </style>
</head>

<body>

  <!-- ── NAV ──────────────────────────────────────────── -->
  <nav id="nav">
    <div class="nav-brand">
      <img src="https://www.DinoviX.ngwebd.com/images/logo2.png" alt="DinoviX Logo" class="nav-logo-img" style="width:120px;">
    </div>

    <div class="nav-links">
      <a href="#features">Features</a>
      <a href="#qr">QR Ordering</a>
      <a href="#kitchen">Kitchen</a>
      <a href="#delivery">Delivery</a>
      <a href="#pricing">Pricing</a>
      <a href="#faq">FAQ</a>
      <a href="/book">Table Booking</a>

      <a href="/login" class="nav-cta">
        <i class="fa-solid fa-right-to-bracket"></i>
        Login
      </a>
    </div>

    <button class="nav-mob" onclick="openMob()">
      <i class="fa fa-bars"></i>
    </button>
  </nav>

  <!-- Mobile nav -->
  <div class="mob-overlay" id="mobOverlay" onclick="closeMob()"></div>
  <div class="mob-menu" id="mobMenu">
    <button class="mob-close" onclick="closeMob()"><i class="fa fa-times"></i></button>
    <a href="#features" onclick="closeMob()">Features</a>
    <a href="#qr" onclick="closeMob()">QR Ordering</a>
    <a href="#kitchen" onclick="closeMob()">Kitchen</a>
    <a href="#delivery" onclick="closeMob()">Delivery</a>
    <a href="#pricing" onclick="closeMob()">Pricing</a>
    <a href="#faq" onclick="closeMob()">FAQ</a>
    <a href="/book">Table Booking</a>
    <a href="/login" style="color:var(--p);font-weight:800">Login →</a>
  </div>

  <!-- ── HERO ──────────────────────────────────────────── -->
  <section class="hero">
    <div class="hero-bg">
      <div class="hero-glow1"></div>
      <div class="hero-glow2"></div>
      <span class="float-deco" style="top:12%;left:6%">🍛</span>
      <span class="float-deco d2" style="top:68%;left:3%">🛵</span>
      <span class="float-deco d3" style="top:20%;left:48%">🥘</span>
      <span class="float-deco d2" style="top:80%;left:55%">☕</span>
      <span class="float-deco" style="top:8%;left:78%">🍚</span>
      <span class="float-deco d3" style="top:60%;left:92%">🌶️</span>
    </div>
    <div class="hero-grid">
      <!-- Left -->
      <div>
        <div class="hero-eyebrow"><i class="fa fa-bolt"></i> Built for Indian Restaurants</div>
        <h1 class="hero-h1">Your Customers Order.<br><span class="accent">Your Kitchen Cooks.</span><br>You Just Watch.
        </h1>
        <p class="hero-sub">DinoviX connects your tables, kitchen, and billing in real time — with QR ordering, live KOT
          display, and a POS your staff will actually use.</p>
        <div class="hero-btns">
          <a href="#pricing" class="btn-hero-p"><i class="fa fa-rocket"></i> Start Free Trial</a>
          <a href="#qr" class="btn-hero-s"><i class="fa fa-play"></i> See How It Works</a>
        </div>
        <div class="hero-trust">
          <div class="trust-pill"><i class="fa fa-check-circle"></i> No installation</div>
          <div class="trust-pill"><i class="fa fa-check-circle"></i> 30-day free trial</div>
          <div class="trust-pill"><i class="fa fa-check-circle"></i> Cancel anytime</div>
        </div>
      </div>
      <!-- Right: Live table map mockup -->
      <div class="hero-visual reveal">
        <div class="pos-mockup">
          <div class="pos-bar">
            <div class="pos-bar-dot" style="background:#EF4444"></div>
            <div class="pos-bar-dot" style="background:#F59E0B;margin:0 4px"></div>
            <div class="pos-bar-dot" style="background:#22C55E"></div>
            <span class="pos-bar-title">DinoviX POS — Spice Garden · Main Branch</span>
            <span class="pos-bar-time" id="heroTime"></span>
          </div>
          <div class="pos-body">
            <div class="pos-stat-row">
              <div class="pos-stat">
                <div class="ps-val" style="color:#F59E0B" id="hStatA">6</div>
                <div class="ps-lbl">Active</div>
              </div>
              <div class="pos-stat">
                <div class="ps-val" style="color:#22C55E">4</div>
                <div class="ps-lbl">Free</div>
              </div>
              <div class="pos-stat">
                <div class="ps-val" style="color:#EF4444">4</div>
                <div class="ps-lbl">Busy</div>
              </div>
              <div class="pos-stat">
                <div class="ps-val" style="color:#8B5CF6">1</div>
                <div class="ps-lbl">Booked</div>
              </div>
            </div>
            <div class="area-lbl">Ground Floor</div>
            <div class="table-grid" id="heroTables">
              <div class="t-tile t-avail">
                <div class="tn">T01</div>
                <div class="tc">2 pax</div>
                <div class="ts">Free</div>
              </div>
              <div class="t-tile t-occu" id="ht2">
                <div class="tn">T02</div>
                <div class="tc">4 pax</div>
                <div class="ts">Busy</div>
              </div>
              <div class="t-tile t-occu">
                <div class="tn">T03</div>
                <div class="tc">2 pax</div>
                <div class="ts">Busy</div>
              </div>
              <div class="t-tile t-avail">
                <div class="tn">T04</div>
                <div class="tc">4 pax</div>
                <div class="ts">Free</div>
              </div>
              <div class="t-tile t-occu">
                <div class="tn">T05</div>
                <div class="tc">6 pax</div>
                <div class="ts">Busy</div>
              </div>
              <div class="t-tile t-avail">
                <div class="tn">T06</div>
                <div class="tc">2 pax</div>
                <div class="ts">Free</div>
              </div>
              <div class="t-tile t-book">
                <div class="tn">T07</div>
                <div class="tc">4 pax</div>
                <div class="ts">Booked</div>
              </div>
              <div class="t-tile t-occu">
                <div class="tn">T08</div>
                <div class="tc">2 pax</div>
                <div class="ts">Busy</div>
              </div>
            </div>
            <div class="area-lbl">First Floor</div>
            <div class="table-grid">
              <div class="t-tile t-avail">
                <div class="tn">T09</div>
                <div class="tc">4 pax</div>
                <div class="ts">Free</div>
              </div>
              <div class="t-tile t-occu" id="ht10">
                <div class="tn">T10</div>
                <div class="tc">2 pax</div>
                <div class="ts">Busy</div>
              </div>
              <div class="t-tile t-avail">
                <div class="tn">T11</div>
                <div class="tc">6 pax</div>
                <div class="ts">Free</div>
              </div>
              <div class="t-tile t-occu">
                <div class="tn">T12</div>
                <div class="tc">4 pax</div>
                <div class="ts">Busy</div>
              </div>
            </div>
            <!-- notification pop -->
            <div class="notif" id="heroNotif"><i class="fa fa-bell"></i> New QR order — T06</div>
            <!-- qr scan indicator -->
            <div class="qr-scan-wrap" id="heroQr">
              <svg width="32" height="32" viewBox="0 0 32 32" fill="none">
                <rect x="2" y="2" width="12" height="12" rx="2" fill="none" stroke="#FF6B35" stroke-width="2" />
                <rect x="5" y="5" width="6" height="6" rx="1" fill="#FF6B35" />
                <rect x="18" y="2" width="12" height="12" rx="2" fill="none" stroke="#FF6B35" stroke-width="2" />
                <rect x="21" y="5" width="6" height="6" rx="1" fill="#FF6B35" />
                <rect x="2" y="18" width="12" height="12" rx="2" fill="none" stroke="#FF6B35" stroke-width="2" />
                <rect x="5" y="21" width="6" height="6" rx="1" fill="#FF6B35" />
                <rect x="18" y="18" width="3" height="3" fill="#FF6B35" />
                <rect x="23" y="18" width="3" height="3" fill="#FF6B35" />
                <rect x="28" y="18" width="3" height="3" fill="#FF6B35" />
                <rect x="18" y="23" width="3" height="3" fill="#FF6B35" />
                <rect x="23" y="23" width="3" height="3" fill="#FF6B35" />
                <rect x="28" y="28" width="3" height="3" fill="#FF6B35" />
              </svg>
              <div class="qr-text"><b>Customer scanning T06</b>Viewing menu now…</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ── STATS STRIP ────────────────────────────────────── -->
  <div class="strip">
    <div class="strip-inner">
      <div class="stat-big">
        <div class="stat-big-val"><span data-count="500">0</span>+</div>
        <div class="stat-big-lbl">Restaurants Onboarded</div>
      </div>
      <div style="width:1px;height:40px;background:var(--card-b)"></div>
      <div class="stat-big">
        <div class="stat-big-val"><span data-count="240">0</span>K+</div>
        <div class="stat-big-lbl">Orders Processed</div>
      </div>
      <div style="width:1px;height:40px;background:var(--card-b)"></div>
      <div class="stat-big">
        <div class="stat-big-val"><span data-count="99" data-dec="9">0</span>%</div>
        <div class="stat-big-lbl">Uptime</div>
      </div>
      <div style="width:1px;height:40px;background:var(--card-b)"></div>
      <div class="stat-big">
        <div class="stat-big-val"><span data-count="8">0</span> sec</div>
        <div class="stat-big-lbl">Avg. QR to Kitchen</div>
      </div>
    </div>
  </div>

  <!-- ── FEATURE MARQUEE ─────────────────────────────────── -->
  <div class="marquee">
    <div class="marquee-track">
      <?php for ($i = 0; $i < 2; $i++): ?>
      <span class="marquee-item">QR Ordering <b>⚡</b> Kitchen Display <b>⚡</b> Smart POS <b>⚡</b> Rider Dispatch <b>⚡</b> UPI QR Payments <b>⚡</b> Split Bills <b>⚡</b> Table Bookings <b>⚡</b> Loyalty & Coupons <b>⚡</b> GST Reports <b>⚡</b> Multi-Branch <b>⚡</b> Thermal Printing <b>⚡</b> Staff Permissions <b>⚡</b></span>
      <?php endfor; ?>
    </div>
  </div>

  <!-- ── FEATURES (point-wise, all features) ─────────────── -->
  <section id="features">
    <div class="sec-inner">
      <div class="eyebrow">Everything you need</div>
      <h2 class="sec-h reveal">One platform.<br>Every role covered.</h2>
      <p class="sec-sub reveal">From the customer's phone to the rider's app — here is every feature, point by point.
        Hover any card to see it move.</p>
      <div class="feat-grid">

        <div class="feat-card reveal">
          <div class="feat-vig">
            <div class="vig-row">
              <div class="vig-phone"><div class="scan"></div><div class="pl1"></div><div class="pl2"></div><div class="pl3"></div><div class="pfb">2 items</div></div>
              <div class="vig-row" style="flex-direction:column;gap:8px;align-items:flex-start">
                <span class="vig-chip on">Scanning T06…</span>
                <span class="vig-chip ok">Order sent</span>
                <span class="vig-chip">No app needed</span>
              </div>
            </div>
          </div>
          <div class="feat-icon">📱</div>
          <div class="feat-title">QR Self-Ordering</div>
          <ul class="feat-points">
            <li>Unique QR per table — menu opens in the browser, zero downloads</li>
            <li>Full menu with photos, categories, bestseller badges & add-ons</li>
            <li>Customer tracks live status: Received → Cooking → Ready</li>
          </ul>
          <div class="feat-tag">⭐ Most loved feature</div>
        </div>

        <div class="feat-card reveal">
          <div class="feat-vig">
            <div class="vig-kot">
              <div class="k"><div class="kt">T02 · #1041</div><div class="ktb">2 × Tikka, 1 × Naan</div><div class="ktt">06:12</div><div class="kbar"><i></i></div></div>
              <div class="k"><div class="kt">T06 · #1042</div><div class="ktb">1 × Biryani</div><div class="ktt">03:41</div><div class="kbar"><i></i></div></div>
              <div class="k"><div class="kt">T10 · #1043</div><div class="ktb">3 × Paneer</div><div class="ktt">01:05</div><div class="kbar"><i></i></div></div>
            </div>
          </div>
          <div class="feat-icon">👨‍🍳</div>
          <div class="feat-title">Live Kitchen Display</div>
          <ul class="feat-points">
            <li>Every confirmed order becomes a KOT card with a running timer</li>
            <li>Colour-coded aging — late orders flash before you do</li>
            <li>One tap to mark items cooking / ready — no paper slips</li>
          </ul>
        </div>

        <div class="feat-card reveal">
          <div class="feat-vig">
            <div class="vig-tiles">
              <div class="vig-t">T1</div><div class="vig-t o">T2</div><div class="vig-t">T3</div><div class="vig-t o">T4</div>
              <div class="vig-t o">T5</div><div class="vig-t b">T6</div><div class="vig-t">T7</div><div class="vig-t">T8</div>
            </div>
          </div>
          <div class="feat-icon">🖥️</div>
          <div class="feat-title">Smart POS & Billing</div>
          <ul class="feat-points">
            <li>Floor-map POS on any screen — dine-in, takeaway & delivery</li>
            <li>Split bills, discounts, coupons and multiple payment methods</li>
            <li>GST-compliant receipts with CGST/SGST breakdowns</li>
          </ul>
        </div>

        <div class="feat-card reveal wide">
          <div class="feat-vig" style="height:150px">
            <div class="vig-map">
              <svg viewBox="0 0 400 150" fill="none">
                <path d="M40 120 C 120 40, 260 140, 360 40" stroke="rgba(255,255,255,.14)" stroke-width="3" stroke-linecap="round"/>
                <path d="M40 120 C 120 40, 260 140, 360 40" stroke="#FF6B35" stroke-width="3" stroke-linecap="round" stroke-dasharray="10 18" style="animation:dashFlow 1.2s linear infinite"/>
                <circle cx="40" cy="120" r="7" fill="#22C55E"/>
                <circle cx="40" cy="120" r="13" fill="none" stroke="rgba(34,197,94,.4)" stroke-width="2" style="animation:greenPulse 2.2s infinite"/>
                <circle cx="360" cy="40" r="7" fill="#FF6B35"/>
                <g font-size="15"><text x="34" y="126">🏪</text><text x="352" y="46">🏠</text></g>
                <g style="animation:slideKot 6s ease-in-out infinite">
                  <circle cx="0" cy="0" r="0" fill="none"/>
                  <text font-size="17" x="-9" y="-9">🛵<animateMotion dur="4s" repeatCount="indefinite" path="M40 120 C 120 40, 260 140, 360 40"/></text>
                </g>
              </svg>
              <div class="vig-eta"><span class="vig-chip ok">Rahul · 2.1 km · 8 min</span></div>
            </div>
          </div>
          <div class="feat-icon">🛵</div>
          <div class="feat-title">Rider Dispatch & Delivery Fleet</div>
          <ul class="feat-points">
            <li>One-tap auto-assign picks the nearest free rider — or hand-pick from rider cards showing free/busy, on-duty & distance</li>
            <li>Own riders or the DinoviX fleet — the dispatch mode shows exactly which is on</li>
            <li>Assigned riders get a full-screen alert with a repeating chime on their app</li>
            <li>Rider app: pickup → delivered with COD cash tracking & live GPS on the board</li>
          </ul>
          <div class="feat-tag">🆕 Just upgraded</div>
        </div>

        <div class="feat-card reveal">
          <div class="feat-vig">
            <div class="vig-upi">
              <div class="vig-qr"><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i></div>
              <div class="vig-split">
                <div class="sr"><span class="dot"></span>Cash <b>₹300</b></div>
                <div class="sr"><span class="dot"></span>UPI <b>₹280</b></div>
                <div class="sr"><span class="dot o"></span>Awaiting UTR…</div>
              </div>
            </div>
          </div>
          <div class="feat-icon">💸</div>
          <div class="feat-title">UPI QR & Split Payments</div>
          <ul class="feat-points">
            <li>Scannable UPI QR on the checkout sheet — customer pays, cashier verifies the UTR</li>
            <li>Split any bill across cash, card, UPI and more</li>
            <li>Duplicate-UTR guard keeps every payment clean</li>
          </ul>
        </div>

        <div class="feat-card reveal">
          <div class="feat-vig">
            <div class="vig-cal">
              <div class="vig-c"><b>12</b><span>—</span></div>
              <div class="vig-c"><b>13</b><span>—</span></div>
              <div class="vig-c hot"><b>14</b><span>7 PM</span></div>
              <div class="vig-c"><b>15</b><span>—</span></div>
              <div class="vig-c"><b>16</b><span>—</span></div>
            </div>
          </div>
          <div class="feat-icon">🪑</div>
          <div class="feat-title">Table Bookings</div>
          <ul class="feat-points">
            <li>Public booking page per branch — customers reserve in seconds</li>
            <li>Sets the table to "Booked" on your POS floor map automatically</li>
            <li>Booking settings: slots, lead time and capacity per branch</li>
          </ul>
        </div>

        <div class="feat-card reveal">
          <div class="feat-vig">
            <div class="vig-loy">
              <div class="vig-coin">🪙</div>
              <div>
                <div class="vig-stamps"><i class="on">✓</i><i class="on">✓</i><i class="on">✓</i><i class="on">✓</i><i>·</i></div>
                <div style="font-size:.6rem;color:var(--t3);margin-top:6px;font-weight:700">1 more visit → free dessert</div>
              </div>
            </div>
          </div>
          <div class="feat-icon">🎁</div>
          <div class="feat-title">Loyalty & Coupons</div>
          <ul class="feat-points">
            <li>Earn & redeem points at checkout — flat, percentage or loyalty discounts</li>
            <li>Auto-apply coupons with usage limits and expiry</li>
            <li>Customer directory with visit history and spend</li>
          </ul>
        </div>

        <div class="feat-card reveal">
          <div class="feat-vig">
            <div class="vig-branch">
              <div class="vig-b hq"><b>🏢</b><span>Head</span></div>
              <div class="vig-link"></div>
              <div class="vig-b"><b>🍲</b><span>Br-1</span></div>
              <div class="vig-link"></div>
              <div class="vig-b"><b>🍜</b><span>Br-2</span></div>
            </div>
          </div>
          <div class="feat-icon">🏪</div>
          <div class="feat-title">Multi-Branch Support</div>
          <ul class="feat-points">
            <li>Every location from one dashboard — menus, staff, tables per branch</li>
            <li>Consolidated cross-branch reports for the owner</li>
            <li>Starter 1 branch · Growth 3 · Enterprise unlimited</li>
          </ul>
        </div>

        <div class="feat-card reveal">
          <div class="feat-vig">
            <div class="vig-print">
              <div class="vig-slot"></div>
              <div class="vig-slip"><b>SPICE GARDEN</b><i></i><i></i><i></i></div>
            </div>
          </div>
          <div class="feat-icon">🖨️</div>
          <div class="feat-title">Thermal Printer Ready</div>
          <ul class="feat-points">
            <li>80mm & 57mm ESC/POS printers over WiFi — no drivers</li>
            <li>Auto-print KOT on order, bill on checkout — or print again anytime</li>
            <li>Bluetooth printing via RawBT for Android phones</li>
          </ul>
        </div>

        <div class="feat-card reveal">
          <div class="feat-vig"><div class="vig-chart"><i></i><i></i><i></i><i></i><i></i></div></div>
          <div class="feat-icon">📊</div>
          <div class="feat-title">Sales, Tax & GST Reports</div>
          <ul class="feat-points">
            <li>Daily, weekly, monthly sales with CGST/SGST/IGST slabs</li>
            <li>Category-wise, item-wise and staff-wise performance</li>
            <li>GSTR-1 export ready, plus expenses & waste tracking</li>
          </ul>
        </div>

        <div class="feat-card reveal">
          <div class="feat-vig">
            <div class="vig-matrix">
              <div class="vig-mrow"><span>Cashier · Discounts</span><div class="tgl on"></div></div>
              <div class="vig-mrow"><span>Manager · Cancel order</span><div class="tgl"></div></div>
              <div class="vig-mrow"><span>Waiter · POS access</span><div class="tgl on"></div></div>
            </div>
          </div>
          <div class="feat-icon">🛡️</div>
          <div class="feat-title">Roles, Permissions & PIN</div>
          <ul class="feat-points">
            <li>22-option permission matrix per role — tick exactly what each role can do</li>
            <li>Manager PIN unlocks sensitive actions live — no re-login, every approval audited</li>
            <li>Dashboards, sidebar and POS actions filter themselves per role</li>
          </ul>
        </div>

        <div class="feat-card reveal">
          <div class="feat-vig">
            <div class="vig-stars"><i class="on">★</i><i class="on">★</i><i class="on">★</i><i class="on">★</i><i class="on">★</i></div>
            <div class="vig-bubble">"Best biryani in town!" — verified visit</div>
          </div>
          <div class="feat-icon">💬</div>
          <div class="feat-title">Customer Feedback Loop</div>
          <ul class="feat-points">
            <li>Post-bill feedback link — ratings land straight on your dashboard</li>
            <li>Happy customers become public testimonials on your site</li>
            <li>Low ratings flag issues before they hit Google reviews</li>
          </ul>
        </div>

      </div>
    </div>
  </section>

  <!-- ── QR ORDERING DEEP DIVE ──────────────────────────── -->
  <section id="qr" class="qr-section">
    <div class="sec-inner">
      <div class="qr-grid">
        <!-- Phone mockup -->
        <div class="phone-wrap reveal">
          <div class="phone">
            <div class="phone-bar">
              <div class="phone-cam"></div>
            </div>
            <div class="phone-screen">
              <div
                style="background:linear-gradient(135deg,#0F172A,#1E293B);margin:-.875rem -.875rem .875rem;padding:.875rem;border-radius:0 0 14px 14px">
                <div class="ph-rest">Spice Garden</div>
                <div class="ph-table">🪑 Table T06</div>
              </div>
              <div class="ph-cat-row">
                <div class="ph-cat on">All</div>
                <div class="ph-cat">Starters</div>
                <div class="ph-cat">Biryani</div>
                <div class="ph-cat">Drinks</div>
              </div>
              <div class="ph-items">
                <div class="ph-item">
                  <div class="ph-item-emoji">🍗</div>
                  <div class="ph-item-body">
                    <div class="ph-item-name">Chicken Tikka</div>
                    <div class="ph-item-price">₹280 · 🔥 Bestseller</div>
                  </div>
                  <div class="ph-add added" id="padd1">✓</div>
                </div>
                <div class="ph-item">
                  <div class="ph-item-emoji">🍛</div>
                  <div class="ph-item-body">
                    <div class="ph-item-name">Chicken Biryani</div>
                    <div class="ph-item-price">₹280 · ⭐ Chef's Pick</div>
                  </div>
                  <div class="ph-add" id="padd2"
                    onclick="this.textContent='✓';this.classList.add('added');this.style.pointerEvents='none';updateFab()">
                    +</div>
                </div>
                <div class="ph-item">
                  <div class="ph-item-emoji">🥭</div>
                  <div class="ph-item-body">
                    <div class="ph-item-name">Mango Lassi</div>
                    <div class="ph-item-price">₹80</div>
                  </div>
                  <div class="ph-add" id="padd3"
                    onclick="this.textContent='✓';this.classList.add('added');this.style.pointerEvents='none';updateFab()">
                    +</div>
                </div>
              </div>
              <div class="ph-fab" id="phFab">
                <span class="ph-fab-l" id="phFabL">1 item in order</span>
                <span class="ph-fab-r">View Order →</span>
              </div>
            </div>
          </div>
          <div class="phone-glow"></div>
        </div>
        <!-- Text -->
        <div class="reveal">
          <div class="eyebrow">QR Ordering</div>
          <h2 class="sec-h">From Scan to Kitchen<br>in Under 10 Seconds</h2>
          <p class="sec-sub">No dedicated device. No printed menus. Customers use their own phone — and your kitchen
            gets the order instantly.</p>
          <div class="qr-steps">
            <div class="qr-step">
              <div class="qr-step-num">1</div>
              <div class="qr-step-body">
                <div class="qr-step-title">Customer scans the table QR</div>
                <div class="qr-step-desc">Each table has a unique QR code. No login, no app — the menu opens instantly
                  in their browser.</div>
              </div>
            </div>
            <div class="qr-step">
              <div class="qr-step-num">2</div>
              <div class="qr-step-body">
                <div class="qr-step-title">Browses menu and places order</div>
                <div class="qr-step-desc">Full menu with categories, search, images, and bestseller badges. One tap to
                  add, one tap to order.</div>
              </div>
            </div>
            <div class="qr-step">
              <div class="qr-step-num">3</div>
              <div class="qr-step-body">
                <div class="qr-step-title">Cashier confirms → Kitchen cooks</div>
                <div class="qr-step-desc">The POS notifies your cashier instantly. One tap to confirm — the KOT appears
                  in the kitchen display.</div>
              </div>
            </div>
            <div class="qr-step">
              <div class="qr-step-num">4</div>
              <div class="qr-step-body">
                <div class="qr-step-title">Customer tracks live status</div>
                <div class="qr-step-desc">Their phone shows a live tracker: Received → Confirmed → Cooking → Ready. Zero
                  calls to the waiter.</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ── SHOWCASE: KITCHEN DISPLAY ───────────────────────── -->
  <section id="kitchen" class="kds-section">
    <div class="sec-inner">
      <div class="kds-grid">
        <!-- Kitchen screen mockup -->
        <div class="kds-screen reveal">
          <div class="kds-blink"><i class="fa fa-circle" style="font-size:.5rem"></i> 3 new KOTs today</div>
          <div class="kds-top"><b>KITCHEN — LIVE</b><span id="kdsClock">--:--:--</span></div>
          <div class="kds-cards">
            <div class="kds-card">
              <div class="kds-hd"><b>T02 · #1041</b><span class="kds-timer">06:12</span></div>
              <div class="kds-items"><b>2 ×</b> Chicken Tikka<br><b>1 ×</b> Butter Naan</div>
              <div class="kds-foot"><i></i></div>
            </div>
            <div class="kds-card">
              <div class="kds-hd"><b>T06 · #1042</b><span class="kds-timer">03:41</span></div>
              <div class="kds-items"><b>1 ×</b> Chicken Biryani<br><b>1 ×</b> Mango Lassi</div>
              <div class="kds-foot"><i></i></div>
            </div>
            <div class="kds-card">
              <div class="kds-hd"><b>T10 · #1043</b><span class="kds-timer">01:05</span></div>
              <div class="kds-items"><b>3 ×</b> Paneer Tikka<br><b>1 ×</b> Dal Makhani</div>
              <div class="kds-foot"><i></i></div>
            </div>
            <div class="kds-card">
              <div class="kds-hd"><b>T01 · #1044</b><span class="kds-timer">00:18</span></div>
              <div class="kds-items"><b>2 ×</b> Mutton Rogan<br><b>2 ×</b> Rumali Roti</div>
              <div class="kds-foot"><i></i></div>
            </div>
          </div>
        </div>
        <!-- Text -->
        <div class="reveal">
          <div class="eyebrow">Kitchen Display</div>
          <h2 class="sec-h">No paper slips.<br>No shouting.</h2>
          <p class="sec-sub">Orders slide onto the kitchen screen the moment they're confirmed — with timers, table
            numbers and the full item list. Chefs work the screen like a video game.</p>
          <div class="del-steps">
            <div class="del-step hot">
              <div class="n"><i class="fa fa-fire" style="font-size:.7rem"></i></div>
              <div><b>Colour-coded aging</b><span>Timers turn amber then red — the oldest order is always obvious, even across a hot kitchen.</span></div>
            </div>
            <div class="del-step">
              <div class="n"><i class="fa fa-hand-pointer" style="font-size:.7rem"></i></div>
              <div><b>Big touch targets</b><span>Tap to mark cooking, ready, or served — designed for greasy fingers and fast pace.</span></div>
            </div>
            <div class="del-step">
              <div class="n"><i class="fa fa-bell" style="font-size:.7rem"></i></div>
              <div><b>Instant sound alerts</b><span>A chime plays on every new KOT so the kitchen never misses a rush-hour order.</span></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ── SHOWCASE: DELIVERY DISPATCH ─────────────────────── -->
  <section id="delivery" class="qr-section">
    <div class="sec-inner">
      <div class="del-grid">
        <!-- Animated map mockup -->
        <div class="del-map reveal">
          <div class="del-chip auto"><i class="fa fa-bolt"></i> Auto-dispatch: ON</div>
          <div class="del-chip eta"><i class="fa fa-clock"></i> ETA 8 min · 2.1 km</div>
          <svg viewBox="0 0 400 300" fill="none">
            <!-- subtle city blocks -->
            <g opacity=".25" fill="rgba(255,255,255,.05)">
              <rect x="30" y="30" width="70" height="55" rx="6"/><rect x="130" y="60" width="60" height="45" rx="6"/>
              <rect x="230" y="25" width="80" height="60" rx="6"/><rect x="60" y="170" width="80" height="60" rx="6"/>
              <rect x="190" y="180" width="70" height="55" rx="6"/><rect x="300" y="130" width="70" height="65" rx="6"/>
            </g>
            <!-- roads -->
            <g stroke="rgba(255,255,255,.07)" stroke-width="10">
              <path d="M0 140 H400 M0 235 H400 M120 0 V300 M265 0 V300"/>
            </g>
            <!-- route -->
            <path d="M55 255 C 110 190, 150 245, 195 195 C 245 140, 300 200, 340 95" stroke="rgba(255,255,255,.14)" stroke-width="3.5" stroke-linecap="round"/>
            <path d="M55 255 C 110 190, 150 245, 195 195 C 245 140, 300 200, 340 95" stroke="#FF6B35" stroke-width="3.5" stroke-linecap="round" stroke-dasharray="12 20" style="animation:dashFlow 1.1s linear infinite"/>
            <!-- restaurant -->
            <circle cx="55" cy="255" r="8" fill="#22C55E"/>
            <circle cx="55" cy="255" r="15" fill="none" stroke="rgba(34,197,94,.4)" stroke-width="2" style="animation:greenPulse 2.2s infinite"/>
            <text font-size="16" x="47" y="262">🏪</text>
            <!-- customer -->
            <circle cx="340" cy="95" r="8" fill="#FF6B35"/>
            <text font-size="16" x="332" y="102">🏠</text>
            <!-- rider moving along the route -->
            <g>
              <text font-size="19" x="-10" y="-10">🛵<animateMotion dur="5.5s" repeatCount="indefinite" rotate="auto-reverse" path="M55 255 C 110 190, 150 245, 195 195 C 245 140, 300 200, 340 95"/></text>
            </g>
            <!-- geofence ping at destination -->
            <circle cx="340" cy="95" r="26" fill="none" stroke="rgba(34,197,94,.25)" stroke-width="2" style="animation:greenPulse 2.2s infinite"/>
          </svg>
        </div>
        <!-- Text -->
        <div class="reveal">
          <div class="eyebrow">Delivery Dispatch</div>
          <h2 class="sec-h">Billed → assigned<br>in one screen.</h2>
          <p class="sec-sub">Right after billing a delivery order, the till shows a dispatch panel — auto-assign the
            nearest free rider, or hand-pick one. The rider's phone rings instantly.</p>
          <div class="del-steps">
            <div class="del-step">
              <div class="n">1</div>
              <div><b>Order billed at POS</b><span>The dispatch panel opens right on the success screen — no jumping to other pages.</span></div>
            </div>
            <div class="del-step hot">
              <div class="n">2</div>
              <div><b>Auto or manual — you see which is on</b><span>One tap auto-dispatch picks the nearest free rider, or pick from cards showing free/busy, duty status and distance.</span></div>
            </div>
            <div class="del-step">
              <div class="n">3</div>
              <div><b>Rider alerted with sound</b><span>Full-screen takeover + repeating chime on the rider app. They tap pickup, deliver, or failed — COD tracked automatically.</span></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ── HOW IT WORKS ────────────────────────────────────── -->
  <section>
    <div class="sec-inner" style="text-align:center">
      <div class="eyebrow">Setup in minutes</div>
      <h2 class="sec-h reveal">Your restaurant live in 3 steps</h2>
      <div class="hiw-grid">
        <div class="hiw-card reveal">
          <div class="hiw-num">1</div>
          <div class="hiw-icon">🏪</div>
          <div class="hiw-title">Create Your Restaurant</div>
          <div class="hiw-desc">Enter your restaurant details, GST number, and branding. Add branches, floors, and
            tables — takes about 5 minutes.</div>
        </div>
        <div class="hiw-card reveal">
          <div class="hiw-num">2</div>
          <div class="hiw-icon">🍽️</div>
          <div class="hiw-title">Upload Your Menu</div>
          <div class="hiw-desc">Add categories, items, prices, and photos. Mark bestsellers, set tax rates, and
            configure variants. Your QR menu is live instantly.</div>
        </div>
        <div class="hiw-card reveal">
          <div class="hiw-num">3</div>
          <div class="hiw-icon">🚀</div>
          <div class="hiw-title">Print & Start Taking Orders</div>
          <div class="hiw-desc">Print the QR codes for your tables, connect your thermal printer, and open the POS.
            You're live.</div>
        </div>
      </div>
    </div>
  </section>

  <!-- ── PRICING ─────────────────────────────────────────── -->
  <section id="pricing" class="pricing-section">
    <div class="sec-inner" style="text-align:center">

      <div class="eyebrow">Simple Pricing</div>

      <h2 class="sec-h reveal">
        Choose the perfect plan for your restaurant
      </h2>

      <p class="sec-sub reveal">
        Start with a 7-day free trial. Upgrade anytime.
      </p>

      <div class="price-grid">

        <?php foreach ($plans as $plan): ?>

          <?php
          $popular = strtolower($plan['name']) === 'growth';
          ?>

          <div class="price-card reveal <?= $popular ? 'popular' : '' ?>">

            <?php if ($popular): ?>
              <div class="popular-badge">Most Popular</div>
            <?php endif; ?>

            <div class="price-plan">
              <?= esc($plan['name']) ?>
            </div>

            <div class="price-val">
              <sup>₹</sup><?= number_format($plan['price_monthly']) ?>
            </div>

            <div class="price-cycle">
              Per Month
            </div>

            <div class="price-yearly">
              ₹<?= number_format($plan['price_yearly']) ?> / Year
            </div>

            <div class="price-desc">
              <?php
              switch (strtolower($plan['name'])) {
                case 'starter':
                  echo "Perfect for small restaurants starting with digital operations.";
                  break;

                case 'growth':
                  echo "Best for growing restaurants with multiple staff and advanced features.";
                  break;

                case 'enterprise':
                  echo "Designed for restaurant chains and large businesses.";
                  break;

                default:
                  echo "Powerful restaurant management solution.";
              }
              ?>
            </div>

            <div class="price-feats">

              <div class="pf">
                <i class="fa fa-check"></i>
                <?= $plan['max_branches'] < 0 ? 'Unlimited' : $plan['max_branches'] ?>
                Branches
              </div>

              <div class="pf">
                <i class="fa fa-check"></i>
                <?= $plan['max_users'] < 0 ? 'Unlimited' : $plan['max_users'] ?>
                Staff Accounts
              </div>

              <div class="pf">
                <i class="fa fa-check"></i>
                <?= $plan['max_menu_items'] < 0 ? 'Unlimited' : $plan['max_menu_items'] ?>
                Menu Items
              </div>

              <div class="pf <?= $plan['allow_online_ordering'] ? '' : 'no' ?>">
                <i class="fa fa-<?= $plan['allow_online_ordering'] ? 'check' : 'times' ?>"></i>
                Online Ordering
              </div>

              <div class="pf <?= $plan['allow_loyalty'] ? '' : 'no' ?>">
                <i class="fa fa-<?= $plan['allow_loyalty'] ? 'check' : 'times' ?>"></i>
                Loyalty Program
              </div>

              <div class="pf <?= $plan['allow_reports_advanced'] ? '' : 'no' ?>">
                <i class="fa fa-<?= $plan['allow_reports_advanced'] ? 'check' : 'times' ?>"></i>
                Advanced Reports
              </div>

              <div class="pf <?= $plan['allow_api_access'] ? '' : 'no' ?>">
                <i class="fa fa-<?= $plan['allow_api_access'] ? 'check' : 'times' ?>"></i>
                API Access
              </div>

            </div>

            <a href="<?= base_url('register?plan=' . $plan['id']) ?>"
              class="price-btn <?= $popular ? 'p' : 's' ?>">
              Start Free Trial
            </a>

          </div>

        <?php endforeach; ?>

      </div>

      <p class="price-note">
        Need help choosing?
        <a href="tel:+919593026451">Talk to us</a>
        and we'll help you choose the best plan.
      </p>

    </div>
  </section>

  <!-- ── TESTIMONIALS ────────────────────────────────────── -->
  <section>
    <div class="sec-inner" style="text-align:center">
      <div class="eyebrow">What restaurant owners say</div>
      <h2 class="sec-h reveal">Loved by kitchens across India</h2>
      <div class="testi-grid">
        <?php $autoTestis = array_slice(($testimonials ?? []), 0, 3); if (!empty($autoTestis)): ?>
          <?php foreach ($autoTestis as $t): ?>
          <div class="testi reveal">
            <div class="testi-quote"><?= esc($t['comment'] ?: 'DinoviX has simplified how we run our restaurant every single day.') ?></div>
            <div class="testi-author">
              <div class="testi-avatar"><?= esc(mb_strtoupper(mb_substr($t['display_name'] ?? 'R', 0, 2))) ?></div>
              <div>
                <div class="testi-name"><?= esc($t['display_name'] ?? 'Restaurant Owner') ?></div>
                <div class="testi-role"><?= esc($t['restaurant_name'] ?? 'Restaurant Partner') ?></div>
                <div class="stars"><?= str_repeat('★', (int)$t['rating']) . str_repeat('☆', max(0, 5 - (int)$t['rating'])) ?></div>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        <?php else: ?>
        <div class="testi reveal">
          <div class="testi-quote">The QR ordering completely changed how our peak hours work. Tables turn faster, staff
            isn't running to take orders, and the kitchen actually knows what's happening.</div>
          <div class="testi-author">
            <div class="testi-avatar">RK</div>
            <div>
              <div class="testi-name">Rajesh Kumar</div>
              <div class="testi-role">Owner, The Biryani House · Kolkata</div>
              <div class="stars">★★★★★</div>
            </div>
          </div>
        </div>
        <div class="testi reveal">
          <div class="testi-quote">We have 3 branches and one dashboard for everything. The reports are clean, staff
            permissions work properly, and setup took less than a day per branch.</div>
          <div class="testi-author">
            <div class="testi-avatar">PM</div>
            <div>
              <div class="testi-name">Priya Mehta</div>
              <div class="testi-role">F&amp;B Manager, Spice Routes · Delhi</div>
              <div class="stars">★★★★★</div>
            </div>
          </div>
        </div>
        <div class="testi reveal">
          <div class="testi-quote">Our kitchen staff love the KOT display. No more paper slips getting lost, no more
            shouting across the kitchen. Every order comes in clear with a timer.</div>
          <div class="testi-author">
            <div class="testi-avatar">AS</div>
            <div>
              <div class="testi-name">Amit Sharma</div>
              <div class="testi-role">Head Chef, Coastal Kitchen · Mumbai</div>
              <div class="stars">★★★★★</div>
            </div>
          </div>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <!-- ── FAQ ─────────────────────────────────────────────── -->
  <section id="faq" style="background:var(--bg2)">
    <div class="sec-inner" style="text-align:center">
      <div class="eyebrow">FAQ</div>
      <h2 class="sec-h reveal">Questions we get asked a lot</h2>
      <div class="faq-list">
        <div class="faq-item">
          <div class="faq-q" onclick="toggleFaq(this)">Do my customers need to download an app? <i
              class="fa fa-chevron-down"></i></div>
          <div class="faq-a">
            <div class="faq-a-inner">No. The QR menu opens in their browser — any phone, any browser. No app, no login,
              no friction.</div>
          </div>
        </div>
        <div class="faq-item">
          <div class="faq-q" onclick="toggleFaq(this)">Can I use my existing thermal printer? <i
              class="fa fa-chevron-down"></i></div>
          <div class="faq-a">
            <div class="faq-a-inner">Yes — DinoviX works with any ESC/POS-compatible 80mm or 57mm thermal printer
              connected over your WiFi network (TCP/IP). No drivers needed.</div>
          </div>
        </div>
        <div class="faq-item">
          <div class="faq-q" onclick="toggleFaq(this)">What happens when my internet goes down? <i
              class="fa fa-chevron-down"></i></div>
          <div class="faq-a">
            <div class="faq-a-inner">The POS and kitchen display continue to work as long as your local WiFi is up.
              Orders already in the system remain accessible. Cloud sync resumes when connectivity returns.</div>
          </div>
        </div>
        <div class="faq-item">
          <div class="faq-q" onclick="toggleFaq(this)">Is GST billing supported? <i class="fa fa-chevron-down"></i>
          </div>
          <div class="faq-a">
            <div class="faq-a-inner">Yes. DinoviX supports CGST/SGST/IGST, multiple tax slabs per item, tax-inclusive
              and tax-exclusive pricing. Bills and reports are GST-compliant.</div>
          </div>
        </div>
        <div class="faq-item">
          <div class="faq-q" onclick="toggleFaq(this)">How many restaurants can I manage? <i
              class="fa fa-chevron-down"></i></div>
          <div class="faq-a">
            <div class="faq-a-inner">Starter supports 1 branch, Growth supports 3 branches, and Enterprise is unlimited.
              Each branch can have its own menu, staff, tables, and reports — all managed from one admin account.</div>
          </div>
        </div>
        <div class="faq-item">
          <div class="faq-q" onclick="toggleFaq(this)">Can I try it before paying? <i class="fa fa-chevron-down"></i>
          </div>
          <div class="faq-a">
            <div class="faq-a-inner">Yes — every plan includes a 30-day free trial. No credit card required to start. If
              you decide DinoviX isn't for you, just don't renew.</div>
          </div>
        </div>
        <div class="faq-item">
          <div class="faq-q" onclick="toggleFaq(this)">Do you offer support in Hindi? <i class="fa fa-chevron-down"></i>
          </div>
          <div class="faq-a">
            <div class="faq-a-inner">Yes — our support team is available in English and Hindi via WhatsApp, email, and
              phone during business hours (9 AM – 8 PM IST).</div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ── CTA ──────────────────────────────────────────────── -->
  <section class="cta-section">
    <div class="eyebrow" style="display:inline-flex;align-items:center;gap:.4rem;margin:0 auto .875rem"><i
        class="fa fa-rocket"></i> Free 30-Day Trial</div>
    <h2 class="cta-h reveal">Ready to modernise<br>your restaurant?</h2>
    <p class="cta-sub">Join hundreds of restaurants already using DinoviX. Setup takes under an hour.</p>
    <div class="cta-input-row">
      <input type="email" class="cta-input" placeholder="Your email address" id="ctaEmail">
      <button class="cta-btn" onclick="startTrial()">Get Started Free</button>
    </div>
    <div class="cta-guarantee"><i class="fa fa-shield-halved" style="color:var(--g)"></i> No credit card · No setup fee
      · Cancel anytime</div>
  </section>

  <!-- ── FOOTER ──────────────────────────────────────────── -->
  <footer>
    <div class="footer-inner">
      <div class="footer-brand">
        <img src="https://www.DinoviX.ngwebd.com/images/logo2.png" alt="DinoviX Logo" class="nav-logo-img" style="width:120px;">
        <p>Modern restaurant management for the Indian food industry. QR ordering, live kitchen display, POS, and
          billing in one platform.</p>
      </div>
      <div class="footer-col">
        <h4>Product</h4>
        <a href="#features">Features</a>
        <a href="#qr">QR Ordering</a>
        <a href="#kitchen">Kitchen Display</a>
        <a href="#delivery">Delivery Dispatch</a>
        <a href="#pricing">Pricing</a>
        <a href="/login">Login</a>
        <a href="/book">Book a Table</a>
      </div>
      <div class="footer-col">
        <h4>Support</h4>
        <a href="mailto:info.DinoviX@gmail.com">Email Support</a>
        <a href="https://wa.me/919593026451?text=Hello%20DinoviX%2C%20I%20want%20to%20know%20more%20about%20your%20Restaurant%20ERP.">WhatsApp</a>
        <a href="#">Documentation</a>
        <a href="#">Setup Guide</a>
      </div>
      <div class="footer-col">
        <h4>Legal</h4>
        <a href="#">Privacy Policy</a>
        <a href="#">Terms of Service</a>
        <a href="#">Refund Policy</a>
      </div>
    </div>
    <div class="footer-bottom">
      <p>© 2026 DinoviX · Made with ❤️ in India</p>
      <p style="color:var(--t3);font-size:.75rem">Contact: <a href="mailto:info.DinoviX@gmail.com"
          style="color:var(--p)">info.DinoviX@gmail.com</a></p>
    </div>
  </footer>

  <script>
    // ── Nav scroll ───────────────────────────────────────────
    window.addEventListener('scroll', () => {
      document.getElementById('nav').classList.toggle('scrolled', window.scrollY > 40);
    });

    // ── Clock ────────────────────────────────────────────────
    function tick() {
      const el = document.getElementById('heroTime');
      if (el) el.textContent = new Date().toLocaleTimeString('en-IN', {
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
      });
    }
    tick();
    setInterval(tick, 1000);

    // ── Hero table animation ─────────────────────────────────
    let animPhase = 0;

    function heroAnim() {
      const t2 = document.getElementById('ht2');
      const notif = document.getElementById('heroNotif');
      const qr = document.getElementById('heroQr');

      if (animPhase === 0) {
        // T06 gets a new QR order — show notification
        notif.style.opacity = '1';
        notif.style.animation = 'notifpop .4s cubic-bezier(.34,1.56,.64,1) both';
        qr.classList.add('show');
      } else if (animPhase === 1) {
        // hide notif after 2s
        notif.style.opacity = '0';
        notif.style.animation = '';
      } else if (animPhase === 2) {
        qr.classList.remove('show');
        // T10 becomes available
        const t10 = document.getElementById('ht10');
        if (t10) {
          t10.className = 't-tile t-avail';
          t10.querySelector('.ts').textContent = 'Free';
        }
      } else if (animPhase === 3) {
        // T10 occupied again
        const t10 = document.getElementById('ht10');
        if (t10) {
          t10.className = 't-tile t-occu';
          t10.querySelector('.ts').textContent = 'Busy';
        }
      }
      animPhase = (animPhase + 1) % 4;
    }
    setInterval(heroAnim, 3000);

    // ── Phone mockup add item ────────────────────────────────
    let fabCount = 1;

    function updateFab() {
      const added = document.querySelectorAll('.ph-add.added').length;
      const el = document.getElementById('phFabL');
      if (el) el.textContent = added + ' item' + (added > 1 ? 's' : '') + ' in order';
    }

    // ── Scroll reveal ────────────────────────────────────────
    const observer = new IntersectionObserver(entries => {
      entries.forEach(e => {
        if (e.isIntersecting) {
          e.target.classList.add('visible');
          observer.unobserve(e.target);
        }
      });
    }, {
      threshold: 0.12
    });
    document.querySelectorAll('.reveal').forEach(el => observer.observe(el));

    // ── FAQ toggle ───────────────────────────────────────────
    function toggleFaq(el) {
      const item = el.parentElement;
      const isOpen = item.classList.contains('open');
      document.querySelectorAll('.faq-item.open').forEach(i => i.classList.remove('open'));
      if (!isOpen) item.classList.add('open');
    }

    // ── Mobile nav ───────────────────────────────────────────
    function openMob() {
      document.getElementById('mobMenu').classList.add('open');
      document.getElementById('mobOverlay').classList.add('open');
    }

    function closeMob() {
      document.getElementById('mobMenu').classList.remove('open');
      document.getElementById('mobOverlay').classList.remove('open');
    }

    // ── CTA ──────────────────────────────────────────────────
    function startTrial() {
      const e = document.getElementById('ctaEmail').value.trim();
      if (!e || !e.includes('@')) {
        alert('Please enter a valid email address');
        return;
      }
      window.location.href = '/login?email=' + encodeURIComponent(e);
    }
  </script>

  <script>
    // ═══ v2 motion: counters, tilt, kitchen clock ═══
    // Count-up on scroll
    const cObs = new IntersectionObserver(entries => {
      entries.forEach(en => {
        if (!en.isIntersecting || en.target._done) return;
        en.target._done = true;
        const el = en.target, target = parseInt(el.dataset.count), dec = el.dataset.dec || '';
        const t0 = performance.now(), dur = 1400;
        (function step(t) {
          const p = Math.min(1, (t - t0) / dur), e = 1 - Math.pow(1 - p, 3);
          el.textContent = dec ? (Math.floor(e * target) + '.' + dec) : Math.floor(e * target);
          if (p < 1) requestAnimationFrame(step);
          else el.textContent = dec ? (target + '.' + dec) : target;
        })(t0);
      });
    }, { threshold: .6 });
    document.querySelectorAll('[data-count]').forEach(el => cObs.observe(el));

    // 3D tilt on feature cards (skip on touch)
    if (matchMedia('(pointer:fine)').matches) {
      document.querySelectorAll('.feat-card').forEach(card => {
        card.addEventListener('mousemove', e => {
          const r = card.getBoundingClientRect();
          const x = (e.clientX - r.left) / r.width - .5, y = (e.clientY - r.top) / r.height - .5;
          card.style.transform = `translateY(-4px) rotateX(${-y * 5}deg) rotateY(${x * 5}deg)`;
          card.style.transition = 'transform .08s';
        });
        card.addEventListener('mouseleave', () => {
          card.style.transition = 'transform .4s';
          card.style.transform = '';
        });
      });
    }

    // Kitchen showcase clock (skips ticks while the tab is hidden)
    const kdsClockEl = document.getElementById('kdsClock');
    if (kdsClockEl) {
      setInterval(() => {
        if (document.hidden) return;
        kdsClockEl.textContent = new Date().toLocaleTimeString('en-IN', { hour: '2-digit', minute: '2-digit', second: '2-digit' }) + ' IST';
      }, 1000);
    }
  </script>
</body>

</html>