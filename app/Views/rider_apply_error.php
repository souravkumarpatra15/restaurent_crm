<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Link expired — DinoviX</title>
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>body{margin:0;min-height:100vh;background:#080D1A;color:#F8FAFC;font-family:Inter,sans-serif;display:flex;align-items:center;justify-content:center;padding:2rem 1rem;text-align:center}.card{background:#0E1525;border:1px solid rgba(255,255,255,.09);border-radius:24px;padding:2.6rem 2rem;max-width:400px;width:100%}h1{font:800 1.4rem Syne,sans-serif;margin:0 0 .5rem}p{color:rgba(248,250,252,.55);font-size:.86rem;line-height:1.7}i{color:#F59E0B}</style>
</head>
<body>
  <div class="card">
    <i class="fa fa-link-slash fa-2x"></i>
    <h1>Link not valid</h1>
    <p><?= esc($msg ?? 'This signup link is not valid any more — ask the restaurant for the current one.') ?></p>
  </div>
</body>
</html>
