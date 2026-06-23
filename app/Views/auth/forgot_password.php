<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Forgot Password — RestOne</title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Plus Jakarta Sans',sans-serif;min-height:100vh;background:#1A202C;display:flex;align-items:center;justify-content:center;padding:1rem}
.card{background:#fff;border-radius:16px;padding:2rem;width:100%;max-width:400px;box-shadow:0 20px 60px rgba(0,0,0,.4)}
.logo{text-align:center;margin-bottom:1.75rem}
.logo-circle{width:64px;height:64px;background:linear-gradient(135deg,#FF6B35,#FF8C5A);border-radius:16px;display:flex;align-items:center;justify-content:center;margin:0 auto .75rem;font-size:1.75rem}
h2{font-size:1.25rem;font-weight:800;color:#1A202C;text-align:center}
p.sub{color:#718096;font-size:.85rem;text-align:center;margin:.5rem 0 1.5rem}
.form-group{margin-bottom:1rem}
label{display:block;font-size:.8rem;font-weight:600;color:#4A5568;margin-bottom:.35rem}
.input-wrap{position:relative}
.input-wrap i{position:absolute;left:.85rem;top:50%;transform:translateY(-50%);color:#A0AEC0}
input{width:100%;padding:.65rem .9rem .65rem 2.5rem;border:1.5px solid #E2E8F0;border-radius:10px;font-size:.875rem;font-family:inherit;transition:border-color .2s}
input:focus{outline:none;border-color:#FF6B35;box-shadow:0 0 0 3px rgba(255,107,53,.12)}
.btn{width:100%;padding:.75rem;background:linear-gradient(135deg,#FF6B35,#FF8C5A);color:#fff;border:none;border-radius:10px;font-size:.95rem;font-weight:700;cursor:pointer;margin-top:.5rem;font-family:inherit}
.btn:hover{opacity:.9}
.back{text-align:center;margin-top:1rem;font-size:.82rem;color:#718096}
.back a{color:#FF6B35;font-weight:600;text-decoration:none}
.alert{padding:.75rem 1rem;border-radius:8px;font-size:.85rem;font-weight:500;margin-bottom:1rem;display:flex;align-items:center;gap:.5rem}
.alert-success{background:#F0FFF4;color:#38A169;border:1px solid #C6F6D5}
.alert-error{background:#FFF5F5;color:#E53E3E;border:1px solid #FED7D7}
</style>
</head>
<body>
<div class="card">
  <div class="logo">
    <div class="logo-circle">🍽</div>
    <h2>Forgot Password?</h2>
    <p class="sub">Enter your email and we'll send you a reset link</p>
  </div>

  <?php if (session()->getFlashdata('success')): ?>
  <div class="alert alert-success"><i class="fa fa-check-circle"></i><?= session()->getFlashdata('success') ?></div>
  <?php endif; ?>
  <?php if (session()->getFlashdata('error')): ?>
  <div class="alert alert-error"><i class="fa fa-circle-exclamation"></i><?= session()->getFlashdata('error') ?></div>
  <?php endif; ?>

  <form action="<?= base_url('forgot-password') ?>" method="POST">
    <?= csrf_field() ?>
    <div class="form-group">
      <label>Email Address</label>
      <div class="input-wrap">
        <i class="fa fa-envelope"></i>
        <input type="email" name="email" placeholder="you@restaurant.com" value="<?= old('email') ?>" required autocomplete="email">
      </div>
    </div>
    <button type="submit" class="btn"><i class="fa fa-paper-plane"></i> Send Reset Link</button>
  </form>

  <div class="back"><a href="<?= base_url('login') ?>"><i class="fa fa-arrow-left"></i> Back to Login</a></div>
</div>
</body>
</html>
