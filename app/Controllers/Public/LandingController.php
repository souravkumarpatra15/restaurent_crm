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
.hero-v2{min-height:92vh;padding:7.2rem 2rem 4.2rem;display:flex;align-items:center;position:relative;overflow:hidden;isolation:isolate;background:#07111f;background-image:radial-gradient(circle at 7% 15%,rgba(255,107,53,.24),transparent 30%),radial-gradient(circle at 88% 8%,rgba(255,153,102,.13),transparent 27%),radial-gradient(circle at 72% 90%,rgba(38,99,235,.13),transparent 30%),linear-gradient(135deg,#07111f 0%,#0b1830 48%,#10192b 100%);color:#fff}
.hero-v2:before{content:'';position:absolute;inset:0;z-index:-3;background:linear-gradient(115deg,rgba(255,255,255,.035) 1px,transparent 1px),linear-gradient(25deg,rgba(255,255,255,.025) 1px,transparent 1px);background-size:80px 80px;mask-image:linear-gradient(to bottom,#000 0%,transparent 90%)}
.hero-v2:after{content:'';position:absolute;z-index:-2;width:650px;height:650px;right:-280px;top:-250px;border-radius:50%;background:rgba(255,107,53,.16);filter:blur(90px)}
.hero-v2-grid{width:min(1220px,100%);margin:0 auto;display:grid;grid-template-columns:minmax(0,.9fr) minmax(480px,1.1fr);gap:3.8rem;align-items:center;position:relative;z-index:2}
.hero-v2-copy{max-width:610px}.hero-v2-badge{display:inline-flex;align-items:center;gap:.5rem;padding:.4rem .75rem;border-radius:999px;border:1px solid rgba(255,153,102,.28);background:rgba(255,107,53,.1);color:#ffb18e;font-size:.67rem;font-weight:800;letter-spacing:.08em;text-transform:uppercase;margin-bottom:1.15rem;box-shadow:0 8px 28px rgba(0,0,0,.14)}
.hero-v2-title{font-family:var(--display);font-size:clamp(2.55rem,4.15vw,4.05rem);line-height:1.04;letter-spacing:-.045em;font-weight:800;margin:0 0 1.1rem;color:#fff}.hero-v2-title .gradient{background:linear-gradient(90deg,#ff6b35 0%,#ff9b6b 55%,#ffd0b8 100%);-webkit-background-clip:text;background-clip:text;color:transparent}.hero-v2-title .gradient:after{content:'';display:block;width:62px;height:4px;margin-top:.45rem;border-radius:999px;background:linear-gradient(90deg,#ff6b35,#ffad80,transparent)}
.hero-v2-sub{max-width:565px;color:rgba(235,242,251,.74);font-size:.97rem;line-height:1.72;margin-bottom:1.6rem}.hero-v2-actions{display:flex;gap:.7rem;flex-wrap:wrap;margin-bottom:1.25rem}.hero-v2-primary,.hero-v2-secondary{min-height:48px;padding:.7rem 1.12rem;border-radius:12px;display:inline-flex;align-items:center;justify-content:center;gap:.5rem;font-size:.85rem;font-weight:800;transition:transform .2s,box-shadow .2s,background .2s}.hero-v2-primary{background:linear-gradient(135deg,#ff6b35,#ff8555);color:#fff;box-shadow:0 13px 30px rgba(255,107,53,.28)}.hero-v2-primary:hover{transform:translateY(-2px);box-shadow:0 17px 38px rgba(255,107,53,.38);background:linear-gradient(135deg,#ff7541,#ff9569)}.hero-v2-secondary{background:rgba(255,255,255,.055);border:1px solid rgba(255,255,255,.14);color:#fff;backdrop-filter:blur(12px)}.hero-v2-secondary:hover{transform:translateY(-2px);background:rgba(255,255,255,.1)}
.hero-v2-proof{display:flex;flex-wrap:wrap;gap:.55rem 1rem;color:rgba(222,232,245,.56);font-size:.69rem;font-weight:650}.hero-v2-proof span{display:inline-flex;align-items:center;gap:.3rem}.hero-v2-proof i{color:#ff9169}
.hero-v2-visual{position:relative;min-height:510px;display:flex;align-items:center;justify-content:center}.hero-v2-photo{position:absolute;inset:0 2% 4% 5%;border-radius:30px;overflow:hidden;background-image:linear-gradient(180deg,rgba(7,17,31,.02),rgba(7,17,31,.74)),url('https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?auto=format&fit=crop&w=1400&q=84');background-size:cover;background-position:center;box-shadow:0 35px 90px rgba(0,0,0,.52);border:1px solid rgba(255,255,255,.13)}
.hero-v2-photo:before{content:'';position:absolute;inset:0;background:linear-gradient(120deg,rgba(255,107,53,.16),transparent 42%,rgba(3,10,20,.4))}.hero-v2-photo:after{content:'';position:absolute;inset:1px;border-radius:29px;border:1px solid rgba(255,255,255,.06);pointer-events:none}
.hero-v2-photo-label{position:absolute;left:1.1rem;bottom:1rem;z-index:1;padding:.68rem .82rem;border-radius:13px;background:rgba(7,14,27,.72);border:1px solid rgba(255,255,255,.13);backdrop-filter:blur(14px)}.hero-v2-photo-label b{display:block;font-size:.7rem}.hero-v2-photo-label span{display:block;color:rgba(225,235,246,.58);font-size:.51rem;margin-top:.12rem}
.hero-v2-dashboard{position:absolute;width:76%;right:-1%;bottom:0;z-index:3;border-radius:18px;overflow:hidden;background:rgba(12,22,39,.95);border:1px solid rgba(255,255,255,.14);box-shadow:0 28px 70px rgba(0,0,0,.6),0 0 50px rgba(255,107,53,.09);backdrop-filter:blur(18px);transform:rotate(-1.2deg);transition:transform .35s}.hero-v2-dashboard:hover{transform:rotate(0) translateY(-3px)}
.hero-v2-topbar{height:42px;padding:0 .75rem;display:flex;align-items:center;gap:.42rem;border-bottom:1px solid rgba(255,255,255,.08);background:rgba(4,10,19,.72)}.hero-v2-dot{width:7px;height:7px;border-radius:50%}.hero-v2-top-title{margin-left:.25rem;font:700 .58rem var(--mono);color:rgba(229,237,247,.7)}.hero-v2-live{margin-left:auto;color:#62df9c;font-size:.52rem;font-weight:800}.hero-v2-dashboard-body{padding:.7rem}.hero-v2-metrics{display:grid;grid-template-columns:repeat(4,1fr);gap:.38rem;margin-bottom:.55rem}.hero-v2-metric{padding:.5rem .45rem;border-radius:9px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.07)}.hero-v2-metric-label{color:rgba(218,229,243,.48);font-size:.43rem;text-transform:uppercase;letter-spacing:.06em;font-weight:800}.hero-v2-metric-value{margin-top:.15rem;font:800 .86rem var(--display);color:#fff}.hero-v2-metric-up{color:#62df9c;font-size:.42rem;font-weight:800}
.hero-v2-main{display:grid;grid-template-columns:1.25fr .75fr;gap:.5rem}.hero-v2-panel{background:rgba(255,255,255,.035);border:1px solid rgba(255,255,255,.07);border-radius:10px;padding:.62rem;min-width:0}.hero-v2-panel-title{display:flex;justify-content:space-between;align-items:center;font-size:.5rem;font-weight:800;color:rgba(235,242,251,.78);margin-bottom:.5rem}.hero-v2-panel-title span{color:rgba(218,229,243,.44);font-size:.43rem}.hero-v2-tables{display:grid;grid-template-columns:repeat(4,1fr);gap:.28rem}.hero-v2-table{padding:.4rem .2rem;border-radius:7px;text-align:center;border:1px solid rgba(255,255,255,.08);background:rgba(255,255,255,.025)}.hero-v2-table strong{display:block;font-size:.58rem}.hero-v2-table small{display:block;margin-top:.1rem;font-size:.39rem;color:rgba(218,229,243,.44)}.hero-v2-table.available{color:#62df9c;border-color:rgba(98,223,156,.25);background:rgba(98,223,156,.07)}.hero-v2-table.busy{color:#ff956f;border-color:rgba(255,107,53,.28);background:rgba(255,107,53,.08)}.hero-v2-table.booked{color:#b9a5ff;border-color:rgba(167,139,250,.25);background:rgba(167,139,250,.07)}
.hero-v2-orders{display:flex;flex-direction:column;gap:.28rem}.hero-v2-order{display:flex;align-items:center;gap:.35rem;padding:.38rem;border-radius:7px;background:rgba(255,255,255,.035)}.hero-v2-order-icon{width:21px;height:21px;flex:0 0 21px;border-radius:6px;display:grid;place-items:center;background:rgba(255,107,53,.12);color:#ff9169;font-size:.48rem}.hero-v2-order-text{min-width:0}.hero-v2-order-text b{display:block;color:rgba(241,246,252,.86);font-size:.45rem}.hero-v2-order-text span{display:block;color:rgba(218,229,243,.43);font-size:.39rem;margin-top:.06rem}.hero-v2-order-status{margin-left:auto;font-size:.38rem;font-weight:800;color:#62df9c}
.hero-v2-food{position:absolute;z-index:4;left:-5%;top:5%;width:142px;height:142px;border-radius:22px;overflow:hidden;border:4px solid #0b1830;box-shadow:0 18px 45px rgba(0,0,0,.5);background-image:linear-gradient(135deg,rgba(255,107,53,.1),transparent),url('https://images.unsplash.com/photo-1504674900247-0877df9cc836?auto=format&fit=crop&w=500&q=84');background-size:cover;background-position:center;transform:rotate(-5deg);animation:heroFloat 5s ease-in-out infinite}
.hero-v2-revenue{position:absolute;z-index:5;right:-3%;top:15%;padding:.72rem .82rem;min-width:122px;border-radius:13px;background:rgba(8,16,29,.9);border:1px solid rgba(255,255,255,.13);box-shadow:0 16px 38px rgba(0,0,0,.42);backdrop-filter:blur(15px);animation:heroFloat 4s ease-in-out 1s infinite}.hero-v2-revenue .label{color:rgba(218,229,243,.48);font-size:.46rem;text-transform:uppercase;letter-spacing:.07em;font-weight:800}.hero-v2-revenue .value{margin-top:.12rem;font:800 .98rem var(--display);color:#fff}.hero-v2-revenue .up{color:#62df9c;font-size:.45rem;font-weight:800}
.hero-v2-qr{position:absolute;z-index:5;left:-1%;bottom:4%;padding:.55rem .65rem;display:flex;align-items:center;gap:.4rem;border-radius:11px;background:rgba(8,16,29,.92);border:1px solid rgba(255,255,255,.13);box-shadow:0 16px 35px rgba(0,0,0,.4);backdrop-filter:blur(14px);animation:heroFloat 4s ease-in-out 1.7s infinite}.hero-v2-qr-icon{width:27px;height:27px;border-radius:7px;display:grid;place-items:center;background:rgba(255,107,53,.13);color:#ff9169;font-size:.75rem}.hero-v2-qr b{display:block;font-size:.48rem;color:#fff}.hero-v2-qr span{display:block;color:rgba(218,229,243,.44);font-size:.4rem;margin-top:.06rem}
.hero-v2-notif{position:absolute;z-index:6;right:7%;bottom:20%;padding:.45rem .58rem;border-radius:9px;background:rgba(255,107,53,.94);color:#fff;font-size:.42rem;font-weight:800;box-shadow:0 12px 28px rgba(255,107,53,.25);opacity:0;transform:translateY(8px);transition:.35s}.hero-v2-notif.show{opacity:1;transform:translateY(0)}
@keyframes heroFloat{0%,100%{transform:translateY(0) rotate(-5deg)}50%{transform:translateY(-7px) rotate(-5deg)}}
@media(max-width:1024px){.hero-v2{padding-top:7.2rem}.hero-v2-grid{grid-template-columns:1fr;gap:2.8rem}.hero-v2-copy{max-width:700px;margin:0 auto;text-align:center}.hero-v2-sub{margin-left:auto;margin-right:auto}.hero-v2-actions,.hero-v2-proof{justify-content:center}.hero-v2-visual{width:min(720px,100%);margin:0 auto;min-height:480px}}
@media(max-width:640px){.hero-v2{min-height:auto;padding:6.5rem 1rem 3rem}.hero-v2-grid{gap:2rem}.hero-v2-title{font-size:clamp(2.2rem,10vw,3.15rem)}.hero-v2-sub{font-size:.9rem}.hero-v2-actions{flex-direction:column}.hero-v2-primary,.hero-v2-secondary{width:100%}.hero-v2-visual{min-height:365px}.hero-v2-photo{inset:0 0 8% 3%;border-radius:20px}.hero-v2-dashboard{width:89%;right:-1%;bottom:0}.hero-v2-food{width:88px;height:88px;left:-2%;top:4%;border-width:3px}.hero-v2-revenue{right:-1%;top:13%;min-width:104px}.hero-v2-qr{left:0;bottom:1%}.hero-v2-notif{right:4%;bottom:28%}}
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
        <div class="hero-v2-notif" id="heroNotif">New order · Table T06</div>

        <div class="hero-v2-dashboard reveal">
          <div class="hero-v2-topbar">
            <span class="hero-v2-dot" style="background:#EF4444"></span><span class="hero-v2-dot" style="background:#F59E0B"></span><span class="hero-v2-dot" style="background:#22C55E"></span>
            <span class="hero-v2-top-title">DinoviX Command Center</span><span class="hero-v2-live">● LIVE</span>
          </div>
          <div class="hero-v2-dashboard-body">
            <div class="hero-v2-metrics">
              <div class="hero-v2-metric"><div class="hero-v2-metric-label">Revenue</div><div class="hero-v2-metric-value">₹48.6K</div><div class="hero-v2-metric-up">+18.4%</div></div>
              <div class="hero-v2-metric"><div class="hero-v2-metric-label">Orders</div><div class="hero-v2-metric-value">126</div><div class="hero-v2-metric-up">+12.2%</div></div>
              <div class="hero-v2-metric"><div class="hero-v2-metric-label">Tables</div><div class="hero-v2-metric-value" id="heroTables">18/24</div><div class="hero-v2-metric-up">75% busy</div></div>
              <div class="hero-v2-metric"><div class="hero-v2-metric-label">Avg. Bill</div><div class="hero-v2-metric-value">₹386</div><div class="hero-v2-metric-up">+6.8%</div></div>
            </div>
            <div class="hero-v2-main">
              <div class="hero-v2-panel">
                <div class="hero-v2-panel-title">Live floor <span>24 tables</span></div>
                <div class="hero-v2-tables">
                  <div class="hero-v2-table available"><strong>T01</strong><small>Available</small></div><div class="hero-v2-table busy" id="ht2"><strong>T02</strong><small>Order #1842</small></div><div class="hero-v2-table busy"><strong>T03</strong><small>Preparing</small></div><div class="hero-v2-table booked"><strong>T04</strong><small>Reserved</small></div>
                </div>
              </div>
              <div class="hero-v2-panel">
                <div class="hero-v2-panel-title">Kitchen queue <span>Live KOT</span></div>
                <div class="hero-v2-orders">
                  <div class="hero-v2-order"><div class="hero-v2-order-icon"><i class="fa fa-cutlery"></i></div><div class="hero-v2-order-text"><b>KOT #1842</b><span>Table T02 · 4 items</span></div><span class="hero-v2-order-status">READY</span></div>
                  <div class="hero-v2-order"><div class="hero-v2-order-icon"><i class="fa fa-fire"></i></div><div class="hero-v2-order-text"><b>KOT #1843</b><span>Table T06 · 3 items</span></div><span class="hero-v2-order-status">COOKING</span></div>
                </div>
              </div>
            </div>
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
