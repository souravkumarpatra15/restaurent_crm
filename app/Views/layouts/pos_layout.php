<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
<title><?= $title ?? 'POS' ?> — RestOne</title>
<meta name="csrf-token" data-name="<?= csrf_token() ?>" content="<?= csrf_hash() ?>">
<meta name="theme-color" content="#FF6B35">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="<?= base_url('css/main.css') ?>">
</head>
<body style="overflow:hidden">

<!-- POS Top Bar -->
<div style="height:48px;background:var(--sidebar-bg);display:flex;align-items:center;justify-content:space-between;padding:0 1rem;position:fixed;top:0;left:0;right:0;z-index:100">
  <div style="display:flex;align-items:center;gap:.75rem">
    <a href="<?= base_url('admin/dashboard') ?>" style="color:rgba(255,255,255,.6);font-size:.85rem"><i class="fa fa-arrow-left"></i></a>
    <span style="color:#fff;font-weight:700;font-size:.95rem">🍽 POS</span>
    <?php if (!empty($branch)): ?>
    <span style="color:rgba(255,255,255,.5);font-size:.78rem">— <?= esc($branch['name'] ?? '') ?></span>
    <?php endif; ?>
  </div>
  <div style="display:flex;align-items:center;gap:.75rem">
    <span style="color:rgba(255,255,255,.7);font-size:.78rem;font-family:'JetBrains Mono',monospace" id="posTime"></span>
    <a href="<?= base_url('pos/kitchen') ?>" style="color:rgba(255,255,255,.7);font-size:.82rem"><i class="fa fa-fire-burner"></i></a>
    <a href="<?= base_url('pos/shift/summary') ?>" style="color:rgba(255,255,255,.7);font-size:.82rem"><i class="fa fa-clock"></i></a>
    <a href="<?= base_url('logout') ?>" style="color:rgba(255,255,255,.5);font-size:.82rem"><i class="fa fa-right-from-bracket"></i></a>
  </div>
</div>

<!-- Content starts below the bar -->
<div style="padding-top:48px;height:100vh;overflow:hidden">
  <?= $this->renderSection('content') ?>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="<?= base_url('js/main.js') ?>"></script>
<script>
setInterval(()=>{
  const n=new Date();
  const el=document.getElementById('posTime');
  if(el) el.textContent=n.toLocaleTimeString('en-IN',{hour:'2-digit',minute:'2-digit',second:'2-digit'});
},1000);
</script>
<?= $scripts ?? '' ?>
</body>
</html>
