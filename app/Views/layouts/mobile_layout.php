<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=no,viewport-fit=cover">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="theme-color" content="#0F172A">
<title><?= $pageTitle ?? 'DinoviX' ?> — DinoviX</title>
<meta name="csrf-token" data-name="<?= csrf_token() ?>" content="<?= csrf_hash() ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="<?= base_url('css/pos.css') ?>">
<link rel="icon" href="<?= base_url('images/favicon.png') ?>">
<style>
* { box-sizing: border-box; margin: 0; padding: 0 }
html, body { height: 100% }
body {
  font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
  background: #F1F5F9;
  color: #1A202C;
  -webkit-font-smoothing: antialiased;
  overscroll-behavior-y: contain;
}
/* Native-feel app bar */
.mbar {
  position: sticky; top: 0; z-index: 100;
  background: #0F172A; color: #fff;
  padding: calc(.65rem + env(safe-area-inset-top)) 1rem .65rem;
  display: flex; align-items: center; gap: .6rem;
}
.mbar-title { font-weight: 800; font-size: 1rem; letter-spacing: .01em }
.mbar-sub   { font-size: .68rem; color: rgba(255,255,255,.55); text-transform: uppercase; letter-spacing: .08em }
.mbar-right { margin-left: auto; display: flex; gap: .5rem; align-items: center }
.mbar-right a { color: rgba(255,255,255,.75); font-size: 1.05rem; text-decoration: none; padding: .35rem }
.mcontent { padding: .9rem .9rem 2rem; max-width: 560px; margin: 0 auto; width: 100% }
.mcardx {
  background: #fff; border-radius: 14px; padding: .9rem;
  margin-bottom: .75rem;
  box-shadow: 0 1px 3px rgba(15,23,42,.08);
  border: 1px solid #E2E8F0;
  width: 100%;
}
.mstat { display: grid; grid-template-columns: repeat(3, 1fr); gap: .5rem; margin-bottom: .9rem }
.mstat > div {
  background: #fff; border-radius: 12px; padding: .6rem .7rem;
  border: 1px solid #E2E8F0; text-align: center;
}
.mstat b { font-size: 1.15rem; font-weight: 900; display: block }
.mstat span { font-size: .62rem; color: #64748B; text-transform: uppercase; letter-spacing: .05em; font-weight: 700 }
.mbtn {
  border: none; border-radius: 10px; padding: .68rem .9rem;
  font-family: inherit; font-weight: 800; font-size: .82rem; cursor: pointer;
  display: inline-flex; align-items: center; justify-content: center; gap: .4rem;
}
.mbtn:disabled { opacity: .55; cursor: wait }
.mbtn-primary { background: #16A34A; color: #fff }
.mbtn-dark    { background: #1A202C; color: #fff }
.mbtn-danger  { background: #FEE2E2; color: #B91C1C }
.mchip {
  display: inline-flex; align-items: center; gap: .25rem;
  font-size: .64rem; font-weight: 800; padding: .2rem .5rem; border-radius: 999px;
}
.mtoast {
  position: fixed; left: 50%; bottom: calc(1rem + env(safe-area-inset-bottom));
  transform: translateX(-50%) translateY(20px);
  background: #0F172A; color: #fff; font-size: .78rem; font-weight: 700;
  padding: .6rem 1rem; border-radius: 12px; opacity: 0; transition: .25s;
  z-index: 999; max-width: 90vw; text-align: center;
}
.mtoast.show { opacity: 1; transform: translateX(-50%) translateY(0) }
</style>
<script>
// Shared pocket-app helpers
const MBASE = '<?= base_url() ?>';
const CSRF_NAME = '<?= csrf_token() ?>', CSRF_TOKEN = '<?= csrf_hash() ?>';
function mToast(msg, ok = true) {
  const t = document.getElementById('mToast');
  if (!t) return;
  t.textContent = msg;
  t.style.background = ok ? '#14532D' : '#7F1D1D';
  t.classList.add('show');
  clearTimeout(window.__mt);
  window.__mt = setTimeout(() => t.classList.remove('show'), 3200);
}
function mPost(url, fields) {
  return fetch(url, {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
    body: new URLSearchParams({ [CSRF_NAME]: CSRF_TOKEN, ...fields })
  }).then(r => {
    if (!r.ok) return r.text().then(t => { throw new Error('Session expired — reopening'); });
    return r.json();
  });
}
</script>
</head>
<body>
<div class="mbar">
  <div>
    <div class="mbar-title"><?= esc($pageTitle ?? 'DinoviX') ?></div>
    <div class="mbar-sub"><?= esc(session('restaurant_name') ?? 'DinoviX') ?> · <?= esc(session('branch_name') ?? '') ?></div>
  </div>
  <div class="mbar-right">
    <a href="<?= base_url('logout') ?>" title="Log out"><i class="fa fa-power-off"></i></a>
  </div>
</div>
<div class="mcontent" id="mContent">
  <?= $this->renderSection('content') ?>
</div>
<div class="mtoast" id="mToast"></div>
</body>
</html>
