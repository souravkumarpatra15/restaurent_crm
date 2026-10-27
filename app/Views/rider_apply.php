<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Driver Signup — <?= esc($restaurant['name']) ?> · DinoviX</title>
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    *{box-sizing:border-box}body{margin:0;min-height:100vh;background:#080D1A;color:#F8FAFC;font-family:Inter,sans-serif;display:flex;align-items:center;justify-content:center;padding:2rem 1rem}
    .bg{position:fixed;inset:0;pointer-events:none;background:radial-gradient(circle at 15% 15%,rgba(255,107,53,.13),transparent 32%),radial-gradient(circle at 85% 85%,rgba(34,197,94,.08),transparent 30%)}
    .wrap{width:100%;max-width:440px;position:relative;z-index:1}
    .card{background:#0E1525;border:1px solid rgba(255,255,255,.09);border-radius:24px;padding:2rem}
    .eyebrow{display:inline-flex;gap:.45rem;align-items:center;color:#FF6B35;background:rgba(255,107,53,.1);border:1px solid rgba(255,107,53,.2);border-radius:20px;padding:.35rem .75rem;font-size:.72rem;font-weight:800;text-transform:uppercase;letter-spacing:.06em}
    h1{font:800 1.7rem/1.15 Syne,sans-serif;margin:1rem 0 .4rem;letter-spacing:-.02em}
    .sub{color:rgba(248,250,252,.5);font-size:.82rem;line-height:1.6;margin-bottom:1.4rem}
    label{font-size:.7rem;font-weight:700;color:rgba(248,250,252,.65);display:block;margin:.8rem 0 .3rem}
    input,select{width:100%;border:1px solid rgba(255,255,255,.1);border-radius:11px;background:rgba(255,255,255,.045);color:#fff;padding:.78rem .85rem;font:500 .85rem Inter,sans-serif;outline:none}
    select option{background:#0E1525;color:#fff}input:focus,select:focus{border-color:#FF6B35}
    .radio{display:flex;gap:.5rem;margin-top:.35rem}
    .radio label{flex:1;margin:0;border:1.5px solid rgba(255,255,255,.1);border-radius:12px;padding:.7rem .6rem;font-size:.72rem;font-weight:700;color:rgba(248,250,252,.75);cursor:pointer;display:flex;gap:.45rem;align-items:center}
    .radio input{width:auto}
    .radio label:has(input:checked){border-color:#FF6B35;background:rgba(255,107,53,.08);color:#fff}
    .btn{width:100%;border:0;border-radius:12px;background:#FF6B35;color:#fff;padding:.95rem 1rem;font:700 .9rem Inter,sans-serif;cursor:pointer;margin-top:1.3rem}
    .btn:hover{background:#E55A20}
    .error{background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.2);color:#FCA5A5;border-radius:11px;padding:.75rem;font-size:.78rem;margin-top:1rem}
    .note{color:rgba(248,250,252,.38);font-size:.7rem;line-height:1.6;margin-top:1.1rem}
  </style>
</head>
<body>
<div class="bg"></div>
<div class="wrap">
  <div class="card">
    <span class="eyebrow"><i class="fa fa-motorcycle"></i> Driver signup</span>
    <h1>Join <?= esc($restaurant['name']) ?></h1>
    <p class="sub">Fill your own details and pick a password. The restaurant team approves you — then you get delivery jobs right here in the DinoviX app.</p>

    <?php if (!empty($error)): ?><div class="error"><i class="fa fa-triangle-exclamation"></i> <?= esc($error) ?></div><?php endif; ?>

    <form method="post" action="<?= base_url('rider-apply/' . $restaurant['id']) ?>">
      <?= csrf_field() ?>
      <label>Your name</label>
      <input type="text" name="name" required maxlength="60" value="<?= esc($old['name'] ?? '') ?>" placeholder="Rahul Sharma">

      <label>Phone</label>
      <input type="tel" name="phone" required pattern="[0-9+\-\s]{6,15}" value="<?= esc($old['phone'] ?? '') ?>" placeholder="9876543210">

      <label>Email (this is your login)</label>
      <input type="email" name="email" required value="<?= esc($old['email'] ?? '') ?>" placeholder="you@email.com">

      <label>Choose a password (min 6)</label>
      <input type="password" name="password" required minlength="6" placeholder="Make it something you remember">

      <label>Vehicle number</label>
      <input type="text" name="vehicle_no" maxlength="20" value="<?= esc($old['vehicle_no'] ?? '') ?>" placeholder="WB-01-AB-1234">

      <label>Branch you ride from</label>
      <select name="branch_id">
        <?php foreach ($branches as $b): ?><option value="<?= $b['id'] ?>"><?= esc($b['name']) ?></option><?php endforeach; ?>
        <?php if (!$branches): ?><option value="">Main branch</option><?php endif; ?>
      </select>

      <label>Rider type</label>
      <div class="radio">
        <label><input type="radio" name="employer_type" value="restaurant" <?= ($old['employer'] ?? '') === 'dinovix' ? '' : 'checked' ?>> 🧑‍🍳 Restaurant's own rider</label>
        <label><input type="radio" name="employer_type" value="dinovix" <?= ($old['employer'] ?? '') === 'dinovix' ? 'checked' : '' ?>> 🛵 DinoviX fleet</label>
      </div>

      <button class="btn" type="submit"><i class="fa fa-arrow-right"></i> Apply to become a driver</button>
    </form>

    <p class="note"><i class="fa fa-circle-check" style="color:#22C55E"></i> You'll set your password now — after the restaurant approves you, open the DinoviX app, sign in with this email, and switch <b>On duty</b>.</p>
  </div>
</div>
</body>
</html>
