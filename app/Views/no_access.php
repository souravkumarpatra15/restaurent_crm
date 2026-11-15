<?php
/**
 * "No access" page — the role-permission denial screen. Self-contained
 * so it renders the same inside admin and POS shells.
 *
 * Expects: moduleName, backUrl
 */
$isAdmin = in_array(strtolower((string)session('role_slug')), ['restaurant_admin', 'branch_manager'], true);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>No access — <?= esc($moduleName) ?></title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{min-height:100vh;font-family:'Plus Jakarta Sans',system-ui,sans-serif;display:flex;align-items:center;justify-content:center;padding:1.25rem;
     background:radial-gradient(900px 480px at 20% -10%,#7F1D1D33,transparent),linear-gradient(160deg,#0B1220 0%,#101B33 55%,#0B1220 100%);color:#F1F5F9}
.card{width:100%;max-width:420px;background:linear-gradient(180deg,#131F38,#0E1730);border:1px solid #24335B;border-radius:22px;
      padding:2.25rem 1.75rem 1.75rem;text-align:center;box-shadow:0 24px 60px rgba(2,6,23,.55)}
.icon{width:74px;height:74px;margin:0 auto 1.1rem;border-radius:50%;display:flex;align-items:center;justify-content:center;
      background:linear-gradient(135deg,#F87171,#DC2626);box-shadow:0 10px 26px rgba(220,38,38,.3);font-size:1.9rem;color:#fff}
.kicker{font-size:.66rem;font-weight:900;letter-spacing:.14em;text-transform:uppercase;color:#7D93C4;margin-bottom:.4rem}
h1{font-size:1.25rem;font-weight:900;letter-spacing:-.01em;margin-bottom:.5rem}
p{font-size:.84rem;line-height:1.65;color:#9FB1D6;margin-bottom:1.4rem}
.chip{display:inline-flex;align-items:center;gap:.4rem;background:#1A2749;border:1px solid #2C3E6E;border-radius:20px;
      padding:.35rem .85rem;font-size:.7rem;font-weight:800;color:#C7D4F2;margin-bottom:1.3rem}
.btn{display:block;width:100%;padding:.85rem;border:none;border-radius:12px;font-family:inherit;font-weight:900;font-size:.88rem;cursor:pointer;
     text-decoration:none;transition:filter .15s}
.btn:hover{filter:brightness(1.1)}
.btn-ghost{background:transparent;border:1.5px solid #2C3E6E;color:#C7D4F2}
.hint{font-size:.68rem;color:#7D93C4;margin-top:1rem}
</style>
</head>
<body>
  <div class="card">
    <div class="icon"><i class="fa fa-user-lock"></i></div>
    <div class="kicker">Restricted</div>
    <h1><?= esc($moduleName) ?></h1>
    <p>Your role doesn't include this option. Your admin can grant it in<br><strong>Admin → Roles &amp; Access</strong> — changes apply instantly.</p>
    <div class="chip"><i class="fa fa-id-badge"></i> Logged in as <?= esc(session('role_name') ?: session('role_slug')) ?></div>
    <a class="btn btn-ghost" href="<?= esc($backUrl) ?>"><i class="fa fa-arrow-left"></i> Back</a>
    <div class="hint">Need this for your work? Ask the restaurant owner to enable it for your role.</div>
  </div>
</body>
</html>
