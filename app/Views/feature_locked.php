<?php
/**
 * Subscription upsell page — shown when a module is not part of the
 * tenant's plan. Self-contained (no layout) so it renders identically
 * inside the admin shell and the POS shell.
 *
 * Expects: featureName, featureIcon, featureBlurb, planName, backUrl
 */
$isAdmin = strtolower((string)session('role_slug')) === 'restaurant_admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= esc($featureName) ?> — Plan Upgrade</title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{min-height:100vh;font-family:'Plus Jakarta Sans',system-ui,sans-serif;display:flex;align-items:center;justify-content:center;padding:1.25rem;
     background:radial-gradient(1000px 500px at 80% -10%,#1E3A8A55,transparent),linear-gradient(160deg,#0B1220 0%,#101B33 55%,#0B1220 100%);color:#F1F5F9}
.card{width:100%;max-width:460px;background:linear-gradient(180deg,#131F38,#0E1730);border:1px solid #24335B;border-radius:22px;
      padding:2.25rem 1.75rem 1.75rem;text-align:center;box-shadow:0 24px 60px rgba(2,6,23,.55)}
.lock{width:74px;height:74px;margin:0 auto 1.1rem;border-radius:50%;display:flex;align-items:center;justify-content:center;
      background:linear-gradient(135deg,#F59E0B,#D97706);box-shadow:0 10px 26px rgba(245,158,11,.35);font-size:1.9rem;color:#fff}
.kicker{font-size:.66rem;font-weight:900;letter-spacing:.14em;text-transform:uppercase;color:#7D93C4;margin-bottom:.4rem}
h1{font-size:1.3rem;font-weight:900;letter-spacing:-.01em;margin-bottom:.5rem}
p{font-size:.84rem;line-height:1.65;color:#9FB1D6;margin-bottom:1.1rem}
.plan-chip{display:inline-flex;align-items:center;gap:.4rem;background:#1A2749;border:1px solid #2C3E6E;border-radius:20px;
           padding:.35rem .85rem;font-size:.7rem;font-weight:800;color:#C7D4F2;margin-bottom:1.4rem}
.btn{display:block;width:100%;padding:.85rem;border:none;border-radius:12px;font-family:inherit;font-weight:900;font-size:.88rem;cursor:pointer;
     text-decoration:none;transition:filter .15s}
.btn:hover{filter:brightness(1.1)}
.btn-gold{background:linear-gradient(135deg,#F59E0B,#D97706);color:#1A1200;margin-bottom:.6rem}
.btn-ghost{background:transparent;border:1.5px solid #2C3E6E;color:#C7D4F2}
.list{margin:0 0 1.5rem;padding:0;list-style:none;text-align:left;font-size:.78rem;color:#9FB1D6}
.list li{display:flex;gap:.55rem;align-items:flex-start;padding:.3rem 0}
.list i{color:#4ADE80;margin-top:.15rem}
</style>
</head>
<body>
  <div class="card">
    <div class="lock"><i class="fa <?= esc($featureIcon ?? 'fa-lock') ?>"></i></div>
    <div class="kicker">Not in your plan</div>
    <h1><?= esc($featureName) ?></h1>
    <p><?= esc($featureBlurb ?? 'This module is available on higher DinoviX plans. Upgrade to unlock it for your restaurant.') ?></p>
    <div class="plan-chip"><i class="fa fa-layer-group"></i> Current plan: <?= esc($planName ?: '—') ?></div>
    <ul class="list">
      <li><i class="fa fa-check"></i> Upgrade in one click from the subscription page</li>
      <li><i class="fa fa-check"></i> Your data is kept — everything resumes instantly</li>
      <li><i class="fa fa-check"></i> No re-install, no setup needed after upgrading</li>
    </ul>
    <?php if ($isAdmin): ?>
      <a class="btn btn-gold" href="<?= base_url('subscriptions') ?>"><i class="fa fa-bolt"></i> Upgrade Plan</a>
    <?php endif; ?>
    <a class="btn btn-ghost" href="<?= esc($backUrl ?? base_url('login')) ?>"><i class="fa fa-arrow-left"></i> Back</a>
  </div>
</body>
</html>
