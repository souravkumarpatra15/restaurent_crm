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
        $trialUrl = base_url('register');

        // Replace only the homepage hero. All other landing-page sections remain untouched.
        $heroCss = <<<'CSS'
<style id="dinovix-hero-v2">
.hero-v2{min-height:92vh;padding:7.4rem 2rem 4.5rem;display:flex;align-items:center;position:relative;overflow:hidden;isolation:isolate;background:radial-gradient(circle at 76% 35%,rgba(255,107,53,.12),transparent 30%),radial-gradient(circle at 10% 85%,rgba(34,197,94,.05),transparent 25%)}
.hero-v2:before{content:'';position:absolute;inset:0;z-index:-2;opacity:.22;background-image:linear-gradient(rgba(255,255,255,.035) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.035) 1px,transparent 1px);background-size:58px 58px;mask-image:linear-gradient(to bottom,#000,transparent 88%)}
.hero-v2-grid{width:min(1200px,100%);margin:0 auto;display:grid;grid-template-columns:minmax(0,.92fr) minmax(450px,1.08fr);gap:4rem;align-items:center;position:relative;z-index:2}
.hero-v2-copy{max-width:600px}.hero-v2-badge{display:inline-flex;align-items:center;gap:.5rem;padding:.38rem .72rem;border-radius:999px;border:1px solid rgba(255,107,53,.22);background:rgba(255,107,53,.07);color:#ff9a75;font-size:.68rem;font-weight:800;letter-spacing:.07em;text-transform:uppercase;margin-bottom:1.15rem}
.hero-v2-title{font-family:var(--display);font-size:clamp(2.65rem,4.4vw,4.25rem);line-height:1.02;letter-spacing:-.04em;font-weight:800;margin-bottom:1.15rem}.hero-v2-title .gradient{color:var(--p)}.hero-v2-title .gradient:after{content:'';display:block;width:70px;height:4px;margin-top:.45rem;border-radius:999px;background:linear-gradient(90deg,var(--p),rgba(255,107,53,0))}
.hero-v2-sub{max-width:560px;color:var(--t2);font-size:.98rem;line-height:1.7;margin-bottom:1.65rem}.hero-v2-actions{display:flex;gap:.7rem;flex-wrap:wrap;margin-bottom:1.25rem}.hero-v2-primary,.hero-v2-secondary{min-height:48px;padding:.7rem 1.1rem;border-radius:11px;display:inline-flex;align-items:center;justify-content:center;gap:.5rem;font-size:.86rem;font-weight:800;transition:transform .2s,box-shadow .2s,background .2s}.hero-v2-primary{background:var(--p);color:#fff;box-shadow:0 10px 28px rgba(255,107,53,.18)}.hero-v2-primary:hover{transform:translateY(-2px);box-shadow:0 14px 34px rgba(255,107,53,.3);background:var(--p-d)}.hero-v2-secondary{background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.1);color:var(--t)}.hero-v2-secondary:hover{transform:translateY(-2px);background:rgba(255,255,255,.075)}
.hero-v2-proof{display:flex;flex-wrap:wrap;gap:.55rem 1rem;color:var(--t3);font-size:.7rem;font-weight:600}.hero-v2-proof span{display:inline-flex;align-items:center;gap:.3rem}.hero-v2-proof i{color:var(--g)}
.hero-v2-visual{position:relative;min-height:500px;display:flex;align-items:center;justify-content:center}.hero-v2-photo{position:absolute;inset:0 4% 5% 7%;border-radius:28px;overflow:hidden;background-image:linear-gradient(180deg,rgba(8,13,26,.08),rgba(8,13,26,.82)),url('https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?auto=format&fit=crop&w=1200&q=82');background-size:cover;background-position:center;box-shadow:0 30px 80px rgba(0,0,0,.5);border:1px solid rgba(255,255,255,.1)}
.hero-v2-photo:after{content:'';position:absolute;inset:0;background:linear-gradient(135deg,rgba(255,107,53,.12),transparent 45%,rgba(8,13,26,.35))}
.hero-v2-photo-label{position:absolute;left:1.1rem;bottom:1rem;z-index:1;padding:.65rem .8rem;border-radius:12px;background:rgba(8,13,26,.78);border:1px solid rgba(255,255,255,.1);backdrop-filter:blur(12px)}.hero-v2-photo-label b{display:block;font-size:.7rem}.hero-v2-photo-label span{display:block;color:var(--t3);font-size:.52rem;margin-top:.12rem}
.hero-v2-dashboard{position:absolute;width:77%;right:-1%;bottom:0;z-index:3;border-radius:18px;overflow:hidden;background:rgba(14,21,37,.96);border:1px solid rgba(255,255,255,.12);box-shadow:0 28px 65px rgba(0,0,0,.6),0 0 45px rgba(255,107,53,.08);backdrop-filter:blur(16px);transform:rotate(-1.5deg);transition:transform .35s}.hero-v2-dashboard:hover{transform:rotate(0) translateY(-3px)}
.hero-v2-topbar{height:42px;padding:0 .75rem;display:flex;align-items:center;gap:.42rem;border-bottom:1px solid rgba(255,255,255,.07);background:rgba(6,11,21,.72)}.hero-v2-dot{width:7px;height:7px;border-radius:50%}.hero-v2-top-title{margin-left:.25rem;font:700 .58rem var(--mono);color:var(--t2)}.hero-v2-live{margin-left:auto;color:#22c55e;font-size:.52rem;font-weight:800}.hero-v2-dashboard-body{padding:.7rem}.hero-v2-metrics{display:grid;grid-template-columns:repeat(4,1fr);gap:.38rem;margin-bottom:.55rem}.hero-v2-metric{padding:.5rem .45rem;border-radius:9px;background:rgba(255,255,255,.035);border:1px solid rgba(255,255,255,.06)}.hero-v2-metric-label{color:var(--t3);font-size:.43rem;text-transform:uppercase;letter-spacing:.06em;font-weight:800}.hero-v2-metric-value{margin-top:.15rem;font:800 .86rem var(--display)}.hero-v2-metric-up{color:#22c55e;font-size:.42rem;font-weight:800}
.hero-v2-main{display:grid;grid-template-columns:1.25fr .75fr;gap:.5rem}.hero-v2-panel{background:rgba(255,255,255,.032);border:1px solid rgba(255,255,255,.06);border-radius:10px;padding:.62rem;min-width:0}.hero-v2-panel-title{display:flex;justify-content:space-between;align-items:center;font-size:.5rem;font-weight:800;color:var(--t2);margin-bottom:.5rem}.hero-v2-panel-title span{color:var(--t3);font-size:.43rem}.hero-v2-tables{display:grid;grid-template-columns:repeat(4,1fr);gap:.28rem}.hero-v2-table{padding:.4rem .2rem;border-radius:7px;text-align:center;border:1px solid rgba(255,255,255,.07);background:rgba(255,255,255,.025)}.hero-v2-table strong{display:block;font-size:.58rem}.hero-v2-table small{display:block;margin-top:.1rem;font-size:.39rem;color:var(--t3)}.hero-v2-table.available{color:#22c55e;border-color:rgba(34,197,94,.25);background:rgba(34,197,94,.06)}.hero-v2-table.busy{color:#ff6b35;border-color:rgba(255,107,53,.25);background:rgba(255,107,53,.06)}.hero-v2-table.booked{color:#a78bfa;border-color:rgba(167,139,250,.25);background:rgba(167,139,250,.06)}
.hero-v2-orders{display:flex;flex-direction:column;gap:.28rem}.hero-v2-order{display:flex;align-items:center;gap:.35rem;padding:.38rem;border-radius:7px;background:rgba(255,255,255,.035)}.hero-v2-order-icon{width:21px;height:21px;flex:0 0 21px;border-radius:6px;display:grid;place-items:center;background:var(--p-l);color:var(--p);font-size:.48rem}.hero-v2-order-text{min-width:0}.hero-v2-order-text b{display:block;color:var(--t);font-size:.45rem}.hero-v2-order-text span{display:block;color:var(--t3);font-size:.39rem;margin-top:.06rem}.hero-v2-order-status{margin-left:auto;font-size:.38rem;font-weight:800;color:#22c55e}
.hero-v2-food{position:absolute;z-index:4;left:-4%;top:6%;width:150px;height:150px;border-radius:20px;overflow:hidden;border:4px solid rgba(8,13,26,.9);box-shadow:0 18px 45px rgba(0,0,0,.5);background-image:url('https://images.unsplash.com/photo-1504674900247-0877df9cc836?auto=format&fit=crop&w=500&q=82');background-size:cover;background-position:center;transform:rotate(-5deg);animation:heroFloat 5s ease-in-out infinite}
.hero-v2-revenue{position:absolute;z-index:5;right:-3%;top:17%;padding:.7rem .8rem;min-width:120px;border-radius:12px;background:rgba(10,16,29,.92);border:1px solid rgba(255,255,255,.1);box-shadow:0 16px 35px rgba(0,0,0,.4);backdrop-filter:blur(14px);animation:heroFloat 4s ease-in-out 1s infinite}.hero-v2-revenue .label{color:var(--t3);font-size:.46rem;text-transform:uppercase;letter-spacing:.07em;font-weight:800}.hero-v2-revenue .value{margin-top:.12rem;font:800 .98rem var(--display)}.hero-v2-revenue .up{color:#22c55e;font-size:.45rem;font-weight:800}
.hero-v2-qr{position:absolute;z-index:5;left:-1%;bottom:5%;padding:.55rem .65rem;display:flex;align-items:center;gap:.4rem;border-radius:11px;background:rgba(10,16,29,.93);border:1px solid rgba(255,255,255,.1);box-shadow:0 16px 35px rgba(0,0,0,.4);backdrop-filter:blur(14px);animation:heroFloat 4s ease-in-out 1.7s infinite}.hero-v2-qr-icon{width:27px;height:27px;border-radius:7px;display:grid;place-items:center;background:var(--p-l);color:var(--p);font-size:.75rem}.hero-v2-qr b{display:block;font-size:.48rem}.hero-v2-qr span{display:block;color:var(--t3);font-size:.4rem;margin-top:.06rem}
@keyframes heroFloat{0%,100%{transform:translateY(0) rotate(-5deg)}50%{transform:translateY(-7px) rotate(-5deg)}}
@media(max-width:1024px){.hero-v2{padding-top:7.2rem}.hero-v2-grid{grid-template-columns:1fr;gap:2.8rem}.hero-v2-copy{max-width:700px;margin:0 auto;text-align:center}.hero-v2-sub{margin-left:auto;margin-right:auto}.hero-v2-actions,.hero-v2-proof{justify-content:center}.hero-v2-visual{width:min(720px,100%);margin:0 auto;min-height:480px}}
@media(max-width:640px){.hero-v2{min-height:auto;padding:6.6rem 1rem 3rem}.hero-v2-grid{gap:2rem}.hero-v2-title{font-size:clamp(2.25rem,10vw,3.2rem)}.hero-v2-sub{font-size:.9rem}.hero-v2-actions{flex-direction:column}.hero-v2-primary,.hero-v2-secondary{width:100%}.hero-v2-visual{min-height:360px}.hero-v2-photo{inset:0 0 8% 4%;border-radius:20px}.hero-v2-dashboard{width:88%;right:-1%;bottom:0}.hero-v2-food{width:92px;height:92px;left:-2%;top:5%;border-width:3px}.hero-v2-revenue{right:-1%;top:14%;min-width:105px}.hero-v2-qr{left:0;bottom:1%}}
</style>
CSS;

        $heroHtml = <<<HTML
  <section class="hero-v2">
    <div class="hero-v2-grid">
      <div class="hero-v2-copy">
        <div class="hero-v2-badge"><i class="fa fa-bolt"></i> Restaurant Operations, Reimagined</div>
        <h1 class="hero-v2-title">Run every table.<br><span class="gradient">Grow every order.</span></h1>
        <p class="hero-v2-sub">DinoviX brings QR ordering, live kitchen KOT, smart POS, billing and restaurant analytics into one simple command center — so your team moves faster and you stay in control.</p>
        <div class="hero-v2-actions">
          <a href="{$trialUrl}" class="hero-v2-primary"><i class="fa fa-rocket"></i> Start Free Trial</a>
          <a href="#qr" class="hero-v2-secondary"><i class="fa fa-play"></i> See How It Works</a>
        </div>
        <div class="hero-v2-proof">
          <span><i class="fa fa-check-circle"></i> 30-day free trial</span>
          <span><i class="fa fa-check-circle"></i> No credit card</span>
          <span><i class="fa fa-check-circle"></i> Multi-branch ready</span>
        </div>
      </div>

      <div class="hero-v2-visual">
        <div class="hero-v2-photo"><div class="hero-v2-photo-label"><b>Built for modern restaurants</b><span>Tables · Kitchen · POS · Orders</span></div></div>
        <div class="hero-v2-food" aria-hidden="true"></div>
        <div class="hero-v2-revenue"><div class="label">Revenue today</div><div class="value">₹48,620</div><div class="up">↑ 18.4% vs yesterday</div></div>
        <div class="hero-v2-qr" id="heroQr"><div class="hero-v2-qr-icon"><i class="fa fa-qrcode"></i></div><div><b>QR order received</b><span>Table T06 · Just now</span></div></div>

        <div class="hero-v2-dashboard reveal">
          <div class="hero-v2-topbar">
            <span class="hero-v2-dot" style="background:#EF4444"></span><span class="hero-v2-dot" style="background:#F59E0B"></span><span class="hero-v2-dot" style="background:#22C55E"></span>
            <span class="hero-v2-top-title">DinoviX Command Center</span><span class="hero-v2-live">● LIVE</span><span class="pos-bar-time" id="heroTime"></span>
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
                  <div class="hero-v2-table available"><strong>T01</strong><small>Free</small></div><div class="hero-v2-table busy" id="ht2"><strong>T02</strong><small>Busy</small></div><div class="hero-v2-table busy"><strong>T03</strong><small>Busy</small></div><div class="hero-v2-table available"><strong>T04</strong><small>Free</small></div>
                  <div class="hero-v2-table busy"><strong>T05</strong><small>Busy</small></div><div class="hero-v2-table available"><strong>T06</strong><small>Free</small></div><div class="hero-v2-table booked"><strong>T07</strong><small>Booked</small></div><div class="hero-v2-table busy"><strong>T08</strong><small>Busy</small></div>
                  <div class="hero-v2-table available"><strong>T09</strong><small>Free</small></div><div class="hero-v2-table busy"><strong>T10</strong><small>Busy</small></div><div class="hero-v2-table available"><strong>T11</strong><small>Free</small></div><div class="hero-v2-table busy"><strong>T12</strong><small>Busy</small></div>
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
