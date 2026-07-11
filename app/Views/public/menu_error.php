<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Invalid QR Code</title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@700;900&display=swap" rel="stylesheet">
<style>*{box-sizing:border-box;margin:0;padding:0}body{font-family:'Plus Jakarta Sans',sans-serif;background:#0F172A;color:#fff;height:100vh;display:flex;align-items:center;justify-content:center;text-align:center;padding:2rem}.wrap{max-width:320px}.icon{font-size:4rem;margin-bottom:1rem}.title{font-size:1.4rem;font-weight:900;margin-bottom:.75rem}.msg{color:rgba(255,255,255,.5);font-size:.875rem;line-height:1.6}</style>
</head>
<body>
<div class="wrap">
  <div class="icon">🔍</div>
  <div class="title">QR Code Invalid</div>
  <div class="msg"><?= esc($message ?? 'This QR code is invalid or has expired. Please ask staff to generate a new one.') ?></div>
</div>
</body>
</html>
