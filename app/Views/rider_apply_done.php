<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Application received — DinoviX</title>
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    *{box-sizing:border-box}body{margin:0;min-height:100vh;background:#080D1A;color:#F8FAFC;font-family:Inter,sans-serif;display:flex;align-items:center;justify-content:center;padding:2rem 1rem;text-align:center}
    .card{background:#0E1525;border:1px solid rgba(255,255,255,.09);border-radius:24px;padding:2.6rem 2rem;max-width:420px;width:100%}
    .check{width:64px;height:64px;border-radius:50%;background:rgba(34,197,94,.12);color:#22C55E;display:flex;align-items:center;justify-content:center;font-size:1.6rem;margin:0 auto 1.2rem}
    h1{font:800 1.5rem Syne,sans-serif;margin:0 0 .5rem}
    p{color:rgba(248,250,252,.55);font-size:.86rem;line-height:1.7}
    b{color:#FF6B35}
  </style>
</head>
<body>
  <div class="card">
    <div class="check"><i class="fa fa-check"></i></div>
    <h1>Application received!</h1>
    <p>Thanks <b><?= esc($name) ?></b> — <?= $fleet ? 'you applied to ride the <b>DinoviX fleet</b> for' : 'you applied to ride for' ?> <b><?= esc($restaurant['name']) ?></b>.</p>
    <p><b>What now?</b> The restaurant team reviews and approves you (usually same day). Once approved:<br>1. Open the DinoviX driver app<br>2. Sign in with the email &amp; password you just chose<br>3. Tap <b>On duty</b> — jobs will start arriving.</p>
  </div>
</body>
</html>
