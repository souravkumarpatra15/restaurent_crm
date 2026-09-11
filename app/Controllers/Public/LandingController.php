<?php

namespace App\Controllers\Public;

use App\Controllers\BaseController;

class LandingController extends BaseController
{
    public function index()
    {
        if (session()->get('user_id')) {
            $role = session()->get('role_slug');

            return redirect()->to(base_url(match ($role) {
                'super_admin'      => 'super/dashboard',
                'restaurant_admin' => 'admin/dashboard',
                'branch_manager'   => 'admin/dashboard',
                'cashier',
                'waiter'           => 'pos',
                'kitchen_staff'    => 'pos/kitchen',
                default            => 'login',
            }));
        }

        $db = \Config\Database::connect();

        $data['plans'] = $db->table('saas_plans sp')
            ->select('sp.*')
            ->where('sp.is_active', 1)
            ->orderBy('sp.sort_order', 'ASC')
            ->get()
            ->getResultArray();

        $html = view('landing', $data);

        // Replace only the homepage hero. All other landing-page sections remain untouched.
        $heroCss = <<<'CSS'
<style id="dinovix-hero-v2">
  .hero-v2 {
    min-height: 100vh;
    padding: 8rem 2rem 5rem;
    display: flex;
    align-items: center;
    position: relative;
    overflow: hidden;
    isolation: isolate;
    background:
      radial-gradient(circle at 78% 38%, rgba(255,107,53,.13), transparent 28%),
      radial-gradient(circle at 12% 85%, rgba(34,197,94,.06), transparent 25%);
  }

  .hero-v2::before {
    content: '';
    position: absolute;
    inset: 0;
    z-index: -2;
    opacity: .34;
    background-image: linear-gradient(rgba(255,255,255,.035) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,.035) 1px, transparent 1px);
    background-size: 56px 56px;
    mask-image: linear-gradient(to bottom, black, transparent 92%);
  }

  .hero-v2::after {
    content: '';
    position: absolute;
    width: 680px;
    height: 680px;
    right: -230px;
    top: 80px;
    border: 1px solid rgba(255,107,53,.10);
    border-radius: 50%;
    box-shadow: 0 0 0 80px rgba(255,107,53,.025), 0 0 0 160px rgba(255,107,53,.015);
    z-index: -1;
  }

  .hero-v2-grid {
    width: min(1240px, 100%);
    margin: 0 auto;
    display: grid;
    grid-template-columns: minmax(0, .96fr) minmax(480px, 1.04fr);
    gap: 5rem;
    align-items: center;
    position: relative;
    z-index: 2;
  }

  .hero-v2-copy { max-width: 650px; }

  .hero-v2-badge {
    display: inline-flex;
    align-items: center;
    gap: .55rem;
    padding: .45rem .8rem;
    border-radius: 999px;
    border: 1px solid rgba(255,107,53,.24);
    background: rgba(255,107,53,.08);
    color: #FF8A5C;
    font-size: .72rem;
    font-weight: 800;
    letter-spacing: .09em;
    text-transform: uppercase;
    margin-bottom: 1.5rem;
  }

  .hero-v2-badge i { font-size: .65rem; }

  .hero-v2-title {
    font-family: var(--display);
    font-size: clamp(3rem, 5.5vw, 5.2rem);
    line-height: .98;
    letter-spacing: -.045em;
    font-weight: 800;
    margin-bottom: 1.45rem;
  }

  .hero-v2-title .gradient { color: var(--p); position: relative; }

  .hero-v2-title .gradient::after {
    content: '';
    display: block;
    width: 92px;
    height: 5px;
    margin-top: .55rem;
    border-radius: 999px;
    background: linear-gradient(90deg, var(--p), rgba(255,107,53,0));
  }

  .hero-v2-sub { max-width: 590px; color: var(--t2); font-size: 1.08rem; line-height: 1.75; margin-bottom: 2rem; }

  .hero-v2-actions { display:flex; gap:.8rem; flex-wrap:wrap; margin-bottom:1.5rem; }

  .hero-v2-primary, .hero-v2-secondary {
    min-height:52px; padding:.8rem 1.25rem; border-radius:13px; display:inline-flex; align-items:center; justify-content:center; gap:.55rem; font-size:.9rem; font-weight:800; transition:transform .2s, box-shadow .2s, background .2s;
  }

  .hero-v2-primary { background:var(--p); color:#fff; box-shadow:0 12px 32px rgba(255,107,53,.2); }
  .hero-v2-primary:hover { transform:translateY(-3px); box-shadow:0 16px 38px rgba(255,107,53,.32); background:var(--p-d); }
  .hero-v2-secondary { background:rgba(255,255,255,.045); border:1px solid rgba(255,255,255,.1); color:var(--t); }
  .hero-v2-secondary:hover { transform:translateY(-3px); background:rgba(255,255,255,.08); }

  .hero-v2-proof { display:flex; flex-wrap:wrap; gap:.7rem 1.2rem; color:var(--t3); font-size:.75rem; font-weight:600; }
  .hero-v2-proof span { display:inline-flex; align-items:center; gap:.35rem; }
  .hero-v2-proof i { color:var(--g); }

  .hero-v2-dashboard-wrap { position:relative; min-width:0; }
  .hero-v2-orbit { position:absolute; inset:-34px -42px -34px -42px; border:1px solid rgba(255,107,53,.11); border-radius:34px; transform:rotate(-2deg); pointer-events:none; }

  .hero-v2-dashboard {
    position:relative; border-radius:24px; overflow:visible; background:linear-gradient(145deg,rgba(21,31,53,.98),rgba(10,16,29,.98)); border:1px solid rgba(255,255,255,.1); box-shadow:0 35px 100px rgba(0,0,0,.58),0 0 70px rgba(255,107,53,.08); transform:perspective(1200px) rotateY(-3deg) rotateX(1deg); transition:transform .45s ease;
  }
  .hero-v2-dashboard:hover { transform:perspective(1200px) rotateY(0) rotateX(0) translateY(-4px); }

  .hero-v2-topbar { height:58px; padding:0 1rem; display:flex; align-items:center; gap:.55rem; border-bottom:1px solid rgba(255,255,255,.07); background:rgba(6,11,21,.55); border-radius:24px 24px 0 0; }
  .hero-v2-dot { width:9px; height:9px; border-radius:50%; }
  .hero-v2-top-title { margin-left:.35rem; font:700 .68rem var(--mono); color:var(--t2); }
  .hero-v2-live { margin-left:auto; display:inline-flex; align-items:center; gap:.35rem; color:#22C55E; font-size:.63rem; font-weight:800; }
  .hero-v2-live::before { content:''; width:6px; height:6px; border-radius:50%; background:#22C55E; box-shadow:0 0 10px #22C55E; }
  .hero-v2-dashboard-body { padding:1rem; }
  .hero-v2-metrics { display:grid; grid-template-columns:repeat(4,1fr); gap:.55rem; margin-bottom:.85rem; }
  .hero-v2-metric { padding:.72rem .6rem; border-radius:12px; background:rgba(255,255,255,.035); border:1px solid rgba(255,255,255,.06); }
  .hero-v2-metric-label { color:var(--t3); font-size:.54rem; text-transform:uppercase; letter-spacing:.08em; font-weight:800; }
  .hero-v2-metric-value { margin-top:.22rem; font:800 1.18rem var(--display); }
  .hero-v2-metric-up { color:#22C55E; font-size:.52rem; font-weight:800; }

  .hero-v2-main { display:grid; grid-template-columns:1.25fr .75fr; gap:.75rem; }
  .hero-v2-panel { background:rgba(255,255,255,.032); border:1px solid rgba(255,255,255,.06); border-radius:15px; padding:.9rem; min-width:0; }
  .hero-v2-panel-title { display:flex; justify-content:space-between; align-items:center; font-size:.65rem; font-weight:800; color:var(--t2); margin-bottom:.75rem; }
  .hero-v2-panel-title span { color:var(--t3); font-size:.54rem; font-weight:600; }
  .hero-v2-tables { display:grid; grid-template-columns:repeat(4,1fr); gap:.45rem; }
  .hero-v2-table { padding:.6rem .35rem; border-radius:10px; text-align:center; border:1px solid rgba(255,255,255,.07); background:rgba(255,255,255,.025); }
  .hero-v2-table strong { display:block; font-size:.72rem; }
  .hero-v2-table small { display:block; margin-top:.18rem; font-size:.47rem; color:var(--t3); }
  .hero-v2-table.available { color:#22C55E; border-color:rgba(34,197,94,.25); background:rgba(34,197,94,.06); }
  .hero-v2-table.busy { color:#FF6B35; border-color:rgba(255,107,53,.25); background:rgba(255,107,53,.06); }
  .hero-v2-table.booked { color:#A78BFA; border-color:rgba(167,139,250,.25); background:rgba(167,139,250,.06); }

  .hero-v2-orders { display:flex; flex-direction:column; gap:.48rem; }
  .hero-v2-order { display:flex; align-items:center; gap:.55rem; padding:.55rem; border-radius:10px; background:rgba(255,255,255,.035); }
  .hero-v2-order-icon { width:28px; height:28px; flex:0 0 28px; border-radius:8px; display:grid; place-items:center; background:var(--p-l); color:var(--p); font-size:.65rem; }
  .hero-v2-order-text { min-width:0; }
  .hero-v2-order-text b { display:block; color:var(--t); font-size:.58rem; }
  .hero-v2-order-text span { display:block; color:var(--t3); font-size:.49rem; margin-top:.1rem; }
  .hero-v2-order-status { margin-left:auto; font-size:.47rem; font-weight:800; color:#22C55E; }

  .hero-v2-float { position:absolute; z-index:5; background:rgba(10,16,29,.94); border:1px solid rgba(255,255,255,.1); box-shadow:0 18px 40px rgba(0,0,0,.4); backdrop-filter:blur(18px); border-radius:15px; }
  .hero-v2-float.revenue { right:-38px; top:22%; padding:.75rem .85rem; min-width:125px; animation:heroFloat 4s ease-in-out infinite; }
  .hero-v2-float.qr { left:-42px; bottom:8%; padding:.7rem .8rem; display:flex; align-items:center; gap:.55rem; animation:heroFloat 4s ease-in-out 1.2s infinite; opacity:1; }
  .hero-v2-float .float-label { color:var(--t3); text-transform:uppercase; font-size:.48rem; letter-spacing:.08em; font-weight:800; }
  .hero-v2-float .float-value { margin-top:.15rem; font:800 1.1rem var(--display); }
  .hero-v2-float .float-up { color:#22C55E; font-size:.5rem; font-weight:800; }
  .hero-v2-qr-icon { width:32px; height:32px; border-radius:9px; display:grid; place-items:center; background:var(--p-l); color:var(--p); font-size:1rem; }
  .hero-v2-qr-copy b { display:block; font-size:.58rem; }
  .hero-v2-qr-copy span { color:var(--t3); font-size:.48rem; }
  .hero-v2-float.qr.show { opacity:1; transform:translateY(0); }
  .hero-v2-float.qr:not(.show) { opacity:1; }

  @keyframes heroFloat { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-7px)} }

  @media(max-width:1024px) {
    .hero-v2 { padding-top:7.5rem; }
    .hero-v2-grid { grid-template-columns:1fr; gap:3.5rem; }
    .hero-v2-copy { max-width:760px; margin:0 auto; text-align:center; }
    .hero-v2-sub { margin-left:auto; margin-right:auto; }
    .hero-v2-actions,.hero-v2-proof { justify-content:center; }
    .hero-v2-dashboard-wrap { width:min(720px,100%); margin:0 auto; }
  }

  @media(max-width:640px) {
    .hero-v2 { min-height:auto; padding:7rem 1rem 3.5rem; }
    .hero-v2-grid { gap:2.5rem; }
    .hero-v2-title { font-size:clamp(2.55rem,12vw,4rem); }
    .hero-v2-sub { font-size:.95rem; }
    .hero-v2-actions { flex-direction:column; }
    .hero-v2-primary,.hero-v2-secondary { width:100%; }
    .hero-v2-metrics { grid-template-columns:repeat(2,1fr); }
    .hero-v2-main { grid-template-columns:1fr; }
    .hero-v2-float.revenue { right:-6px; top:19%; }
    .hero-v2-float.qr { left:-6px; bottom:-15px; }
    .hero-v2-orbit { inset:-15px; }
  }
</style>
CSS;

        $heroHtml = <<<'HTML'
  <section class="hero-v2">
    <div class="hero-v2-grid">
      <div class="hero-v2-copy">
        <div class="hero-v2-badge"><i class="fa fa-bolt"></i> Restaurant Operations, Reimagined</div>
        <h1 class="hero-v2-title">Run every table.<br><span class="gradient">Grow every order.</span></h1>
        <p class="hero-v2-sub">DinoviX brings QR ordering, live kitchen KOT, smart POS, billing and restaurant analytics into one simple command center — so your team moves faster and you stay in control.</p>
        <div class="hero-v2-actions">
          <a href="<?= base_url('register') ?>" class="hero-v2-primary"><i class="fa fa-rocket"></i> Start Free Trial</a>
          <a href="#qr" class="hero-v2-secondary"><i class="fa fa-play"></i> See How It Works</a>
        </div>
        <div class="hero-v2-proof">
          <span><i class="fa fa-check-circle"></i> 30-day free trial</span>
          <span><i class="fa fa-check-circle"></i> No credit card</span>
          <span><i class="fa fa-check-circle"></i> Multi-branch ready</span>
        </div>
      </div>

      <div class="hero-v2-dashboard-wrap">
        <div class="hero-v2-orbit"></div>
        <div class="hero-v2-dashboard reveal">
          <div class="hero-v2-topbar">
            <span class="hero-v2-dot" style="background:#EF4444"></span>
            <span class="hero-v2-dot" style="background:#F59E0B"></span>
            <span class="hero-v2-dot" style="background:#22C55E"></span>
            <span class="hero-v2-top-title">DinoviX Command Center</span>
            <span class="hero-v2-live">LIVE</span>
            <span class="pos-bar-time" id="heroTime"></span>
          </div>
          <div class="hero-v2-dashboard-body">
            <div class="hero-v2-metrics">
              <div class="hero-v2-metric"><div class="hero-v2-metric-label">Today's Sales</div><div class="hero-v2-metric-value">₹48.6K</div><div class="hero-v2-metric-up">↑ 18.4%</div></div>
              <div class="hero-v2-metric"><div class="hero-v2-metric-label">Orders</div><div class="hero-v2-metric-value">126</div><div class="hero-v2-metric-up">↑ 12 today</div></div>
              <div class="hero-v2-metric"><div class="hero-v2-metric-label">Tables</div><div class="hero-v2-metric-value">6/14</div><div class="hero-v2-metric-up">Active now</div></div>
              <div class="hero-v2-metric"><div class="hero-v2-metric-label">Avg. KOT</div><div class="hero-v2-metric-value">08m</div><div class="hero-v2-metric-up">On target</div></div>
            </div>

            <div class="hero-v2-main">
              <div class="hero-v2-panel">
                <div class="hero-v2-panel-title">Live floor <span>Ground + First</span></div>
                <div class="hero-v2-tables" id="heroTables">
                  <div class="hero-v2-table available"><strong>T01</strong><small>Free</small></div>
                  <div class="hero-v2-table busy" id="ht2"><strong>T02</strong><small>Busy</small></div>
                  <div class="hero-v2-table busy"><strong>T03</strong><small>Busy</small></div>
                  <div class="hero-v2-table available"><strong>T04</strong><small>Free</small></div>
                  <div class="hero-v2-table busy"><strong>T05</strong><small>Busy</small></div>
                  <div class="hero-v2-table available"><strong>T06</strong><small>Free</small></div>
                  <div class="hero-v2-table booked"><strong>T07</strong><small>Booked</small></div>
                  <div class="hero-v2-table busy"><strong>T08</strong><small>Busy</small></div>
                  <div class="hero-v2-table available"><strong>T09</strong><small>Free</small></div>
                  <div class="hero-v2-table busy"><strong>T10</strong><small>Busy</small></div>
                  <div class="hero-v2-table available"><strong>T11</strong><small>Free</small></div>
                  <div class="hero-v2-table busy"><strong>T12</strong><small>Busy</small></div>
                </div>
              </div>

              <div class="hero-v2-panel">
                <div class="hero-v2-panel-title">Kitchen queue <span>Live KOT</span></div>
                <div class="hero-v2-orders">
                  <div class="hero-v2-order"><div class="hero-v2-order-icon"><i class="fa fa-fire"></i></div><div class="hero-v2-order-text"><b>#1042 · T06</b><span>2 items · QR order</span></div><span class="hero-v2-order-status">COOKING</span></div>
                  <div class="hero-v2-order"><div class="hero-v2-order-icon"><i class="fa fa-bell"></i></div><div class="hero-v2-order-text"><b>#1041 · T03</b><span>4 items · Dine-in</span></div><span class="hero-v2-order-status">READY</span></div>
                  <div class="hero-v2-order"><div class="hero-v2-order-icon"><i class="fa fa-receipt"></i></div><div class="hero-v2-order-text"><b>#1040 · T11</b><span>3 items · QR order</span></div><span class="hero-v2-order-status">NEW</span></div>
                </div>
              </div>
            </div>
          </div>

          <div class="notif" id="heroNotif"><i class="fa fa-bell"></i> New QR order — T06</div>
          <div class="hero-v2-float revenue">
            <div class="float-label">Revenue today</div>
            <div class="float-value">₹48,620</div>
            <div class="float-up">↑ 18.4% vs yesterday</div>
          </div>
          <div class="hero-v2-float qr" id="heroQr">
            <div class="hero-v2-qr-icon"><i class="fa fa-qrcode"></i></div>
            <div class="hero-v2-qr-copy"><b>QR order received</b><span>Table T06 · Just now</span></div>
          </div>
        </div>
      </div>
    </div>
  </section>
HTML;

        $html = preg_replace('/<section class="hero">.*?<\/section>/s', $heroHtml, $html, 1);
        $html = preg_replace('/<\/style>/', $heroCss . "\n</style>", $html, 1);

        return $html;
    }
}
