<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Reset Password — RestOne</title>
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
.input-wrap i.icon{position:absolute;left:.85rem;top:50%;transform:translateY(-50%);color:#A0AEC0}
.toggle-pwd{position:absolute;right:.85rem;top:50%;transform:translateY(-50%);background:none;border:none;color:#A0AEC0;cursor:pointer}
input{width:100%;padding:.65rem .9rem .65rem 2.5rem;border:1.5px solid #E2E8F0;border-radius:10px;font-size:.875rem;font-family:inherit}
input:focus{outline:none;border-color:#FF6B35;box-shadow:0 0 0 3px rgba(255,107,53,.12)}
.btn{width:100%;padding:.75rem;background:linear-gradient(135deg,#FF6B35,#FF8C5A);color:#fff;border:none;border-radius:10px;font-size:.95rem;font-weight:700;cursor:pointer;margin-top:.5rem;font-family:inherit}
.back{text-align:center;margin-top:1rem;font-size:.82rem;color:#718096}
.back a{color:#FF6B35;font-weight:600;text-decoration:none}
.alert-error{background:#FFF5F5;color:#E53E3E;border:1px solid #FED7D7;padding:.75rem 1rem;border-radius:8px;font-size:.85rem;margin-bottom:1rem}
</style>
</head>
<body>
<div class="card">
  <div class="logo">
    <div class="logo-circle">🔒</div>
    <h2>Set New Password</h2>
    <p class="sub">Choose a strong password for your account</p>
  </div>
  <?php if (session()->getFlashdata('error')): ?>
  <div class="alert-error"><?= session()->getFlashdata('error') ?></div>
  <?php endif; ?>
  <form action="<?= base_url('reset-password') ?>" method="POST">
    <?= csrf_field() ?>
    <input type="hidden" name="token" value="<?= esc($token) ?>">
    <div class="form-group">
      <label>New Password</label>
      <div class="input-wrap">
        <i class="fa fa-lock icon"></i>
        <input type="password" name="password" id="pwd" placeholder="Min 6 characters" required>
        <button type="button" class="toggle-pwd" onclick="togglePwd('pwd','eye1')"><i class="fa fa-eye" id="eye1"></i></button>
      </div>
    </div>
    <div class="form-group">
      <label>Confirm Password</label>
      <div class="input-wrap">
        <i class="fa fa-lock icon"></i>
        <input type="password" name="confirm_password" id="pwd2" placeholder="Repeat password" required>
        <button type="button" class="toggle-pwd" onclick="togglePwd('pwd2','eye2')"><i class="fa fa-eye" id="eye2"></i></button>
      </div>
    </div>
    <button type="submit" class="btn"><i class="fa fa-check"></i> Update Password</button>
  </form>
  <div class="back"><a href="<?= base_url('login') ?>"><i class="fa fa-arrow-left"></i> Back to Login</a></div>
</div>
<script>
function togglePwd(id, eyeId) {
  const i = document.getElementById(id);
  const e = document.getElementById(eyeId);
  i.type = i.type === 'password' ? 'text' : 'password';
  e.className = i.type === 'password' ? 'fa fa-eye' : 'fa fa-eye-slash';
}
</script>
</body>
</html>
