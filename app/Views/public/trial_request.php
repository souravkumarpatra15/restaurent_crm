<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="Request your DinoviX free trial.">
  <title>Start Free Trial — DinoviX</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    *{box-sizing:border-box}body{margin:0;min-height:100vh;background:#080D1A;color:#F8FAFC;font-family:Inter,sans-serif;display:flex;align-items:center;justify-content:center;padding:2rem 1rem;overflow-x:hidden}
    .bg{position:fixed;inset:0;pointer-events:none;background:radial-gradient(circle at 15% 15%,rgba(255,107,53,.13),transparent 32%),radial-gradient(circle at 85% 85%,rgba(34,197,94,.08),transparent 30%)}
    .wrap{width:100%;max-width:920px;position:relative;z-index:1}.brand{display:flex;justify-content:center;margin-bottom:1.5rem}.brand img{width:140px;height:auto}
    .card{background:#0E1525;border:1px solid rgba(255,255,255,.09);border-radius:24px;box-shadow:0 30px 80px rgba(0,0,0,.45);overflow:hidden;display:grid;grid-template-columns:.85fr 1.15fr}
    .intro{padding:2.5rem;background:linear-gradient(145deg,rgba(255,107,53,.12),rgba(255,255,255,.02));border-right:1px solid rgba(255,255,255,.07)}
    .eyebrow{display:inline-flex;gap:.45rem;align-items:center;color:#FF6B35;background:rgba(255,107,53,.1);border:1px solid rgba(255,107,53,.2);border-radius:20px;padding:.35rem .75rem;font-size:.72rem;font-weight:800;text-transform:uppercase;letter-spacing:.06em}
    h1{font:800 clamp(2rem,4vw,3rem)/1.05 Syne,sans-serif;margin:1.25rem 0 .9rem;letter-spacing:-.03em}h1 span{color:#FF6B35}.intro p{color:rgba(248,250,252,.58);line-height:1.7;font-size:.9rem}
    .benefits{margin-top:2rem;display:grid;gap:.9rem}.benefit{display:flex;gap:.7rem;align-items:flex-start;font-size:.82rem;color:rgba(248,250,252,.75)}.benefit i{color:#22C55E;margin-top:.2rem}
    .form{padding:2.5rem}.form h2{font:800 1.3rem Syne,sans-serif;margin:0 0 .35rem}.form-sub{color:rgba(248,250,252,.48);font-size:.82rem;margin-bottom:1.5rem}.grid{display:grid;grid-template-columns:1fr 1fr;gap:1rem}.field{display:flex;flex-direction:column;gap:.4rem}.field.full{grid-column:1/-1}label{font-size:.72rem;font-weight:700;color:rgba(248,250,252,.65)}input,select,textarea{width:100%;border:1px solid rgba(255,255,255,.1);border-radius:11px;background:rgba(255,255,255,.045);color:#fff;padding:.78rem .85rem;font:500 .84rem Inter,sans-serif;outline:none}select option{background:#0E1525;color:#fff}textarea{min-height:90px;resize:vertical}input:focus,select:focus,textarea:focus{border-color:#FF6B35}.hint{font-size:.68rem;color:rgba(248,250,252,.35)}.error{background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.2);color:#FCA5A5;border-radius:11px;padding:.75rem;font-size:.78rem;margin-bottom:1rem}.success{background:rgba(34,197,94,.1);border:1px solid rgba(34,197,94,.22);color:#86EFAC;border-radius:12px;padding:1rem;font-size:.82rem;line-height:1.6;margin-bottom:1rem}.btn{width:100%;border:0;border-radius:12px;background:#FF6B35;color:#fff;padding:.9rem 1rem;font:700 .9rem Inter,sans-serif;cursor:pointer;margin-top:.25rem}.btn:hover{background:#E55A20}.back{display:block;text-align:center;margin-top:1rem;color:rgba(248,250,252,.45);font-size:.75rem;text-decoration:none}.back:hover{color:#FF6B35}
    @media(max-width:720px){.card{grid-template-columns:1fr}.intro{border-right:0;border-bottom:1px solid rgba(255,255,255,.07);padding:2rem}.form{padding:2rem}.benefits{grid-template-columns:1fr 1fr}.grid{grid-template-columns:1fr}.field.full{grid-column:auto}}
    @media(max-width:480px){body{padding:1rem}.benefits{grid-template-columns:1fr}.intro,.form{padding:1.4rem}.brand{margin-bottom:1rem}}
  </style>
</head>
<body>
<div class="bg"></div>
<div class="wrap">
  <div class="brand"><a href="<?= base_url('/') ?>"><img src="<?= base_url('images/logo2.png') ?>" alt="DinoviX"></a></div>
  <div class="card">
    <div class="intro">
      <div class="eyebrow"><i class="fa fa-rocket"></i> Free Trial Request</div>
      <h1>Let's get your restaurant <span>started.</span></h1>
      <p>Tell us a little about your restaurant. Our team will review your request and contact you to activate your DinoviX free trial.</p>
      <div class="benefits">
        <div class="benefit"><i class="fa fa-circle-check"></i><span>7-day free trial</span></div>
        <div class="benefit"><i class="fa fa-circle-check"></i><span>No credit card required</span></div>
        <div class="benefit"><i class="fa fa-circle-check"></i><span>Quick onboarding assistance</span></div>
        <div class="benefit"><i class="fa fa-circle-check"></i><span>POS + QR + Kitchen in one platform</span></div>
      </div>
    </div>
    <div class="form">
      <h2>Request your free trial</h2>
      <div class="form-sub">We'll use these details only to contact you about your DinoviX trial.</div>

      <?php if (session('success')): ?>
        <div class="success"><i class="fa fa-circle-check"></i> <?= esc(session('success')) ?></div>
      <?php endif; ?>
      <?php if (session('errors')): ?>
        <div class="error"><strong>Please check the form:</strong><br><?= esc(implode(' ', session('errors'))) ?></div>
      <?php endif; ?>

      <form method="post" action="<?= base_url('start-free-trial') ?>">
        <?= csrf_field() ?>
        <div class="grid">
          <div class="field"><label for="name">Your Name *</label><input id="name" name="name" required maxlength="120" value="<?= esc(old('name')) ?>" placeholder="Restaurant owner / manager"></div>
          <div class="field"><label for="restaurant_name">Restaurant Name *</label><input id="restaurant_name" name="restaurant_name" required maxlength="180" value="<?= esc(old('restaurant_name')) ?>" placeholder="Your restaurant name"></div>
          <div class="field"><label for="phone">Phone / WhatsApp *</label><input id="phone" name="phone" required maxlength="30" value="<?= esc(old('phone')) ?>" placeholder="+91 98765 43210"></div>
          <div class="field"><label for="email">Email *</label><input id="email" type="email" name="email" required maxlength="180" value="<?= esc(old('email')) ?>" placeholder="you@example.com"></div>
          <div class="field"><label for="city">City</label><input id="city" name="city" maxlength="100" value="<?= esc(old('city')) ?>" placeholder="Kolkata"></div>
          <div class="field"><label for="branches">Number of Branches *</label><input id="branches" type="number" name="branches" min="1" max="9999" required value="<?= esc(old('branches') ?: 1) ?>"></div>
          <div class="field full"><label for="plan_id">Interested Plan</label><select id="plan_id" name="plan_id"><option value="">Not sure yet</option><?php foreach ($plans as $plan): ?><option value="<?= (int)$plan['id'] ?>" <?= ((int)old('plan_id', $selectedPlan) === (int)$plan['id']) ? 'selected' : '' ?>><?= esc($plan['name']) ?> — ₹<?= number_format($plan['price_monthly']) ?>/month</option><?php endforeach; ?></select></div>
          <div class="field full"><label for="message">Anything you'd like us to know?</label><textarea id="message" name="message" maxlength="3000" placeholder="Tell us about your requirements, current POS, number of tables, etc."><?= esc(old('message')) ?></textarea><div class="hint">Optional</div></div>
          <div class="field full"><button class="btn" type="submit"><i class="fa fa-paper-plane"></i> Submit Free Trial Request</button></div>
        </div>
      </form>
      <a class="back" href="<?= base_url('/') ?>">← Back to DinoviX</a>
    </div>
  </div>
</div>
</body>
</html>
