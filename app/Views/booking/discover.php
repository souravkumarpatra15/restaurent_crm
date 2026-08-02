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
    /* ==========================================================
   DINOVIX RESTAURANT BOOKING
   Responsive UI v2.0
   Part 1
========================================================== */

    /* ===========================
   RESET
=========================== */

    *,
    *::before,
    *::after {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    html {
      scroll-behavior: smooth;
      -webkit-tap-highlight-color: transparent;
    }

    body {
      font-family: 'Plus Jakarta Sans', sans-serif;
      background: #F8FAFC;
      color: #0F172A;
      overflow-x: hidden;
      line-height: 1.5;
    }

    img {
      max-width: 100%;
      display: block;
    }

    a {
      color: inherit;
      text-decoration: none;
    }

    button,
    input,
    select,
    textarea {
      font-family: inherit;
    }

    button {
      cursor: pointer;
    }

    ul {
      list-style: none;
    }


    /* ===========================
   VARIABLES
=========================== */

    :root {

      --primary: #FF6B35;
      --primary-dark: #E85A24;
      --primary-light: #FFF0EB;

      --dark: #0F172A;
      --dark2: #1E293B;
      --dark3: #334155;

      --text: #0F172A;
      --text2: #334155;
      --text3: #64748B;
      --text4: #94A3B8;

      --success: #22C55E;
      --danger: #EF4444;

      --bg: #F8FAFC;
      --white: #ffffff;

      --border: #E2E8F0;

      --shadow-sm:
        0 2px 8px rgba(0, 0, 0, .05);

      --shadow:
        0 8px 24px rgba(15, 23, 42, .08);

      --shadow-lg:
        0 18px 50px rgba(15, 23, 42, .15);

      --radius: 16px;
      --radius-lg: 22px;
      --radius-xl: 30px;

      --container: 1200px;

    }


    /* ===========================
   APP
=========================== */

    .app-shell {
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }


    /* ===========================
   CONTAINER
=========================== */

    .container,
    .main,
    .hero-inner,
    .topnav-inner {
      width: min(100% - 30px, var(--container));
      margin-inline: auto;
    }


    /* ===========================
   HEADER
=========================== */

    .topnav {

      position: sticky;
      top: 0;
      z-index: 999;

      background: rgba(15, 23, 42, .96);
      backdrop-filter: blur(18px);

      border-bottom: 1px solid rgba(255, 255, 255, .05);

    }

    .topnav-inner {

      display: flex;
      align-items: center;
      justify-content: space-between;

      min-height: 72px;

    }


    .logo {

      display: flex;
      align-items: center;
      gap: 12px;

    }

    .logo-icon {

      width: 42px;
      height: 42px;

      border-radius: 12px;

      background: linear-gradient(135deg,
          var(--primary),
          #ff9d6c);

      display: flex;
      align-items: center;
      justify-content: center;

      color: #fff;
      font-size: 20px;
      font-weight: bold;

      box-shadow: 0 8px 20px rgba(255, 107, 53, .35);

    }

    .logo-text {

      font-size: 22px;
      font-weight: 900;
      color: #fff;

    }

    .logo-text span {
      color: var(--primary);
    }


    /* ===========================
   NAV BUTTONS
=========================== */

    .nav-right {

      display: flex;
      align-items: center;
      gap: 12px;

    }

    .nav-btn {

      padding: 11px 18px;

      border-radius: 10px;

      border: none;

      background: rgba(255, 255, 255, .08);

      color: #fff;

      font-size: 14px;
      font-weight: 700;

      transition: .25s;

    }

    .nav-btn:hover {

      background: rgba(255, 255, 255, .16);

    }

    .nav-btn.primary {

      background: linear-gradient(135deg,
          var(--primary),
          #ff8d5a);

    }

    .nav-btn.primary:hover {

      transform: translateY(-2px);

      box-shadow: 0 10px 22px rgba(255, 107, 53, .35);

    }


    /* ===========================
   HERO
=========================== */

    .hero {

      position: relative;

      overflow: hidden;

      background:
        linear-gradient(135deg,
          #0F172A,
          #1E293B,
          #0F172A);

      padding: 80px 20px 120px;

    }

    .hero::before {

      content: "";

      position: absolute;
      inset: 0;

      background:

        radial-gradient(circle at 20% 30%,
          rgba(255, 107, 53, .22),
          transparent 45%),

        radial-gradient(circle at 90% 70%,
          rgba(255, 107, 53, .10),
          transparent 40%);

    }

    .hero-inner {

      position: relative;
      z-index: 5;

      max-width: 760px;

    }

    .hero-badge {

      display: inline-flex;

      align-items: center;

      gap: 8px;

      padding: 8px 16px;

      border-radius: 50px;

      background: rgba(255, 107, 53, .14);

      border: 1px solid rgba(255, 107, 53, .35);

      color: var(--primary);

      font-size: 13px;

      font-weight: 800;

      text-transform: uppercase;

      margin-bottom: 22px;

    }

    .hero h1 {

      color: #fff;

      font-size: clamp(2rem, 5vw, 4rem);

      line-height: 1.1;

      font-weight: 900;

      margin-bottom: 18px;

    }

    .hero h1 span {

      color: var(--primary);

    }

    .hero-sub {

      color: rgba(255, 255, 255, .70);

      font-size: 18px;

      line-height: 1.8;

      max-width: 640px;

    }


    /* ===========================
   MOBILE
=========================== */

    @media(max-width:992px) {

      .hero {

        padding: 70px 20px 90px;

      }

      .hero h1 {

        font-size: 42px;

      }

    }

    @media(max-width:768px) {

      .topnav-inner {

        min-height: 64px;

      }

      .logo-text {

        font-size: 18px;

      }

      .nav-btn {

        padding: 10px 14px;
        font-size: 13px;

      }

      .hero {

        padding: 60px 15px 80px;

      }

      .hero h1 {

        font-size: 34px;

      }

      .hero-sub {

        font-size: 15px;

      }

    }

    @media(max-width:480px) {

      .nav-right {

        gap: 8px;

      }

      .nav-btn {

        padding: 8px 10px;
        font-size: 12px;

      }

      .hero {

        padding: 50px 15px 70px;

      }

      .hero h1 {

        font-size: 30px;

      }

      .hero-sub {

        font-size: 14px;

      }

    }

    /*==========================================================
 SEARCH CARD
==========================================================*/

    .search-card {
      background: #fff;
      border-radius: 26px;
      padding: 28px;
      margin-top: 35px;
      box-shadow: 0 25px 60px rgba(15, 23, 42, .18);
      position: relative;
      z-index: 20;
    }

    .search-fields {
      display: grid;
      grid-template-columns:
        repeat(2, minmax(0, 1fr));
      gap: 18px;
    }

    .sf {

      display: flex;
      align-items: center;
      gap: 14px;

      background: #fff;

      border: 2px solid var(--border);

      border-radius: 16px;

      padding: 16px 18px;

      transition: .25s;

    }

    .sf:hover {

      border-color: #FFD1BF;

    }

    .sf:focus-within {

      border-color: var(--primary);

      box-shadow:
        0 0 0 4px rgba(255, 107, 53, .10);

    }

    .sf i {

      width: 20px;
      text-align: center;

      color: var(--primary);

      font-size: 18px;

    }

    .sf input,
    .sf select {

      width: 100%;

      border: none;

      outline: none;

      background: none;

      font-size: 15px;

      color: var(--text);

    }

    .sf input::placeholder {

      color: var(--text4);

    }

    .sf-label {

      display: block;

      font-size: 11px;

      font-weight: 800;

      text-transform: uppercase;

      letter-spacing: 1px;

      color: var(--text3);

      margin-bottom: 3px;

    }

    .sf-btn-wrap {

      grid-column: 1/-1;

    }

    .search-btn {

      width: 100%;

      border: none;

      border-radius: 16px;

      padding: 18px;

      background:
        linear-gradient(135deg,
          var(--primary),
          #FF8E63);

      color: #fff;

      font-size: 16px;

      font-weight: 800;

      transition: .3s;

      box-shadow:
        0 18px 35px rgba(255, 107, 53, .35);

    }

    .search-btn:hover {

      transform: translateY(-3px);

      box-shadow:
        0 22px 40px rgba(255, 107, 53, .45);

    }


    /*==========================================================
 STATS
==========================================================*/

    .stats-strip {

      display: flex;

      justify-content: center;

      gap: 60px;

      padding: 22px;

      background: var(--dark2);

    }

    .stat-item {

      text-align: center;

    }

    .stat-num {

      color: var(--primary);

      font-size: 28px;

      font-weight: 900;

    }

    .stat-txt {

      color: rgba(255, 255, 255, .65);

      font-size: 13px;

      margin-top: 4px;

    }


    /*==========================================================
 MAIN
==========================================================*/

    .main {

      max-width: 1200px;

      margin: auto;

      padding: 40px 20px 100px;

    }


    /*==========================================================
 FILTERS
==========================================================*/

    .filter-row {

      display: flex;

      gap: 12px;

      overflow: auto;

      padding-bottom: 12px;

      scrollbar-width: none;

    }

    .filter-row::-webkit-scrollbar {

      display: none;

    }

    .fchip {

      flex-shrink: 0;

      border: 2px solid var(--border);

      border-radius: 50px;

      padding: 11px 18px;

      background: #fff;

      color: var(--text3);

      font-size: 14px;

      font-weight: 700;

      transition: .25s;

      cursor: pointer;

    }

    .fchip:hover {

      border-color: var(--primary);

      color: var(--primary);

    }

    .fchip.on {

      background: var(--primary);

      color: #fff;

      border-color: var(--primary);

    }


    /*==========================================================
 SECTION HEADER
==========================================================*/

    .sec-hdr {

      display: flex;

      justify-content: space-between;

      align-items: center;

      margin: 35px 0 25px;

    }

    .sec-title {

      display: flex;

      align-items: center;

      gap: 10px;

      font-size: 28px;

      font-weight: 900;

    }

    .sec-count {

      background: #fff;

      padding: 10px 18px;

      border-radius: 30px;

      font-weight: 700;

      color: var(--text3);

      box-shadow: var(--shadow-sm);

    }


    /*==========================================================
 RESTAURANT GRID
==========================================================*/

    .rest-grid {

      display: grid;

      grid-template-columns:
        repeat(auto-fill, minmax(330px, 1fr));

      gap: 28px;

      align-items: start;

    }


    /*==========================================================
 RESPONSIVE
==========================================================*/

    @media(max-width:992px) {

      .search-fields {

        grid-template-columns: 1fr;

      }

      .stats-strip {

        gap: 35px;

      }

      .rest-grid {

        grid-template-columns:
          repeat(auto-fill, minmax(300px, 1fr));

      }

      .sec-title {

        font-size: 24px;

      }

    }


    @media(max-width:768px) {

      .main {

        padding: 25px 15px 90px;

      }

      .search-card {

        padding: 20px;

        border-radius: 20px;

      }

      .sf {

        padding: 14px;

      }

      .stats-strip {

        gap: 25px;

        flex-wrap: wrap;

      }

      .stat-num {

        font-size: 22px;

      }

      .sec-hdr {

        flex-direction: column;

        align-items: flex-start;

        gap: 12px;

      }

      .rest-grid {

        grid-template-columns: 1fr;

        gap: 20px;

      }

      .fchip {

        font-size: 13px;

        padding: 9px 16px;

      }

    }


    @media(max-width:480px) {

      .search-card {

        padding: 16px;

      }

      .search-btn {

        padding: 15px;

        font-size: 15px;

      }

      .sec-title {

        font-size: 20px;

      }

      .sec-count {

        padding: 8px 14px;

        font-size: 13px;

      }

      .stat-num {

        font-size: 20px;

      }

      .stat-txt {

        font-size: 12px;

      }

    }

    /*==========================================================
    RESTAURANT CARD
==========================================================*/

    .rcard {

      display: flex;
      flex-direction: column;

      background: #fff;

      border-radius: 24px;

      overflow: hidden;

      box-shadow:
        0 8px 30px rgba(15, 23, 42, .08);

      transition: .35s;

      border: 1px solid rgba(226, 232, 240, .8);

      position: relative;

    }

    .rcard:hover {

      transform: translateY(-8px);

      box-shadow:
        0 25px 60px rgba(15, 23, 42, .18);

    }


    /*==========================================================
    IMAGE
==========================================================*/

    .rcard-img {

      position: relative;

      width: 100%;

      height: 240px;

      overflow: hidden;

      background: #EEF2F7;

    }

    .rcard-img img {

      width: 100%;
      height: 100%;

      object-fit: cover;

      transition: transform .5s;

    }

    .rcard:hover img {

      transform: scale(1.08);

    }


    /*==========================================================
IMAGE OVERLAY
==========================================================*/

    .rcard-img::after {

      content: "";

      position: absolute;

      inset: 0;

      background:
        linear-gradient(transparent,
          rgba(0, 0, 0, .18));

    }


    /*==========================================================
BADGES
==========================================================*/

    .img-badges {

      position: absolute;

      top: 15px;
      left: 15px;
      right: 15px;

      display: flex;

      justify-content: space-between;

      align-items: flex-start;

      z-index: 5;

    }

    .avail-badge {

      padding: 7px 14px;

      border-radius: 50px;

      font-size: 12px;

      font-weight: 800;

      color: #fff;

      backdrop-filter: blur(8px);

    }

    .avail-badge.yes {

      background: rgba(34, 197, 94, .92);

    }

    .avail-badge.no {

      background: rgba(239, 68, 68, .92);

    }

    .pay-badge {

      padding: 7px 14px;

      border-radius: 50px;

      background: rgba(79, 70, 229, .95);

      color: #fff;

      font-size: 12px;

      font-weight: 700;

    }


    /*==========================================================
BODY
==========================================================*/

    .rcard-body {

      display: flex;

      flex-direction: column;

      flex: 1;

      padding: 22px;

    }


    /*==========================================================
TITLE
==========================================================*/

    .rcard-name {

      font-size: 24px;

      font-weight: 900;

      color: var(--dark);

      margin-bottom: 10px;

      line-height: 1.2;

    }


    /*==========================================================
LOCATION
==========================================================*/

    .rcard-loc {

      display: flex;

      align-items: center;

      gap: 8px;

      color: var(--text3);

      font-size: 15px;

      margin-bottom: 16px;

    }


    /*==========================================================
DESCRIPTION
==========================================================*/

    .rcard-desc {

      color: var(--text3);

      font-size: 15px;

      line-height: 1.8;

      margin-bottom: 18px;

      display: -webkit-box;

      -webkit-line-clamp: 2;

      -webkit-box-orient: vertical;

      overflow: hidden;

    }


    /*==========================================================
TAGS
==========================================================*/

    .rcard-tags {

      display: flex;

      flex-wrap: wrap;

      gap: 10px;

      margin-bottom: 22px;

    }

    .rcard-tag {

      background: #FFF2EC;

      color: var(--primary);

      padding: 8px 14px;

      border-radius: 50px;

      font-size: 13px;

      font-weight: 700;

    }


    /*==========================================================
FOOTER
==========================================================*/

    .rcard-foot {

      margin-top: auto;

      display: flex;

      justify-content: space-between;

      align-items: center;

      gap: 18px;

      border-top: 1px solid #EDF2F7;

      padding-top: 20px;

    }


    /*==========================================================
PRICE
==========================================================*/

    .rcard-cost {

      color: var(--text3);

      font-size: 14px;

    }

    .rcard-cost b {

      display: block;

      font-size: 22px;

      color: var(--dark);

      margin-bottom: 4px;

    }

    .rcard-deposit {

      margin-top: 8px;

      color: #F59E0B;

      font-size: 13px;

      font-weight: 700;

    }


    /*==========================================================
BUTTON
==========================================================*/

    .rcard-book-btn {

      border: none;

      outline: none;

      cursor: pointer;

      padding: 14px 26px;

      border-radius: 14px;

      background:
        linear-gradient(135deg,
          var(--primary),
          #FF8E63);

      color: #fff;

      font-size: 15px;

      font-weight: 800;

      transition: .3s;

      white-space: nowrap;

      box-shadow:
        0 12px 30px rgba(255, 107, 53, .28);

    }

    .rcard-book-btn:hover {

      transform: translateY(-2px);

      box-shadow:
        0 20px 40px rgba(255, 107, 53, .38);

    }


    /*==========================================================
MOBILE
==========================================================*/

    @media(max-width:992px) {

      .rcard-img {

        height: 220px;

      }

      .rcard-name {

        font-size: 22px;

      }

    }


    @media(max-width:768px) {

      .rcard {

        border-radius: 20px;

      }

      .rcard-img {

        height: 200px;

      }

      .rcard-body {

        padding: 18px;

      }

      .rcard-name {

        font-size: 20px;

      }

      .rcard-foot {

        flex-direction: column;

        align-items: stretch;

      }

      .rcard-book-btn {

        width: 100%;

      }

      .rcard-cost {

        text-align: center;

      }

    }


    @media(max-width:480px) {

      .rcard-img {

        height: 180px;

      }

      .rcard-body {

        padding: 16px;

      }

      .rcard-name {

        font-size: 18px;

      }

      .rcard-loc {

        font-size: 13px;

      }

      .rcard-desc {

        font-size: 13px;

      }

      .rcard-tag {

        font-size: 12px;

        padding: 6px 10px;

      }

      .rcard-book-btn {

        padding: 13px;

        font-size: 14px;

      }

    }

    /*==========================================================
EMPTY STATE
==========================================================*/

    .empty {

      padding: 100px 20px;

      text-align: center;

    }

    .empty-icon {

      font-size: 80px;

      margin-bottom: 25px;

      opacity: .65;

    }

    .empty-title {

      font-size: 32px;

      font-weight: 900;

      color: var(--dark);

      margin-bottom: 12px;

    }

    .empty-sub {

      max-width: 550px;

      margin: auto;

      font-size: 16px;

      color: var(--text3);

      line-height: 1.8;

    }



    /*==========================================================
HOW IT WORKS
==========================================================*/

    .how-section {

      margin-top: 80px;

      background:

        linear-gradient(135deg,
          var(--dark),
          var(--dark2));

      border-radius: 30px;

      padding: 60px;

      position: relative;

      overflow: hidden;

    }

    .how-section::before {

      content: "";

      position: absolute;

      right: -120px;
      top: -120px;

      width: 320px;
      height: 320px;

      border-radius: 50%;

      background:

        radial-gradient(rgba(255, 107, 53, .12),
          transparent 70%);

    }

    .how-title {

      text-align: center;

      color: #fff;

      font-size: 34px;

      font-weight: 900;

      margin-bottom: 45px;

    }

    .how-steps {

      display: grid;

      grid-template-columns:

        repeat(auto-fit, minmax(220px, 1fr));

      gap: 30px;

    }

    .how-step {

      background:

        rgba(255, 255, 255, .04);

      border:

        1px solid rgba(255, 255, 255, .08);

      border-radius: 22px;

      padding: 30px;

      text-align: center;

      transition: .35s;

    }

    .how-step:hover {

      transform: translateY(-8px);

      background:

        rgba(255, 255, 255, .06);

    }

    .how-step-icon {

      width: 70px;
      height: 70px;

      margin: auto;

      margin-bottom: 20px;

      border-radius: 18px;

      display: flex;

      justify-content: center;

      align-items: center;

      background:

        rgba(255, 107, 53, .18);

      color: var(--primary);

      font-size: 30px;

    }

    .how-step-title {

      color: #fff;

      font-size: 20px;

      font-weight: 800;

      margin-bottom: 10px;

    }

    .how-step-sub {

      color: rgba(255, 255, 255, .65);

      line-height: 1.7;

      font-size: 14px;

    }



    /*==========================================================
FOOTER
==========================================================*/

    .foot {

      margin-top: 80px;

      background: var(--dark);

      color:

        rgba(255, 255, 255, .55);

      text-align: center;

      padding: 35px 20px;

      font-size: 15px;

    }

    .foot a {

      color: var(--primary);

      font-weight: 700;

    }



    /*==========================================================
SKELETON
==========================================================*/

    .skeleton {

      position: relative;

      overflow: hidden;

      background: #E8EDF4;

    }

    .skeleton::before {

      content: "";

      position: absolute;

      inset: 0;

      background:

        linear-gradient(90deg,

          transparent,

          rgba(255, 255, 255, .65),

          transparent);

      animation:

        shimmer 1.4s infinite;

    }

    @keyframes shimmer {

      from {

        transform: translateX(-100%);

      }

      to {

        transform: translateX(100%);

      }

    }



    /*==========================================================
MOBILE BOTTOM BAR
==========================================================*/

    .mob-bar {

      display: none;

      position: fixed;

      left: 0;
      right: 0;
      bottom: 0;

      z-index: 999;

      background: #fff;

      border-top: 1px solid var(--border);

      padding:

        12px 10px calc(12px + env(safe-area-inset-bottom));

      justify-content: space-around;

      box-shadow:

        0 -10px 30px rgba(0, 0, 0, .08);

    }

    .mob-bar-btn {

      display: flex;

      flex-direction: column;

      align-items: center;

      gap: 6px;

      border: none;

      background: none;

      color: var(--text3);

      font-size: 13px;

      font-weight: 700;

    }

    .mob-bar-btn i {

      font-size: 22px;

      transition: .25s;

    }

    .mob-bar-btn.on {

      color: var(--primary);

    }

    .mob-bar-btn.on i {

      transform: translateY(-2px);

    }



    /*==========================================================
RESPONSIVE
==========================================================*/

    @media(max-width:992px) {

      .how-section {

        padding: 45px;

      }

      .how-title {

        font-size: 28px;

      }

    }



    @media(max-width:768px) {

      .empty {

        padding: 70px 20px;

      }

      .empty-title {

        font-size: 26px;

      }

      .empty-sub {

        font-size: 15px;

      }

      .how-section {

        padding: 35px 25px;

        border-radius: 22px;

      }

      .how-title {

        font-size: 24px;

        margin-bottom: 30px;

      }

      .how-steps {

        grid-template-columns: 1fr;

        gap: 20px;

      }

      .mob-bar {

        display: flex;

      }

      .main {

        padding-bottom: 95px;

      }

    }



    @media(max-width:480px) {

      .empty-icon {

        font-size: 60px;

      }

      .empty-title {

        font-size: 22px;

      }

      .how-step {

        padding: 22px;

      }

      .how-step-icon {

        width: 60px;

        height: 60px;

        font-size: 24px;

      }

      .how-step-title {

        font-size: 18px;

      }

      .foot {

        font-size: 13px;

        padding: 28px 15px;

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