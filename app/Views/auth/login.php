<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
<title>Login — RestoCRM</title>
<meta name="theme-color" content="#1A202C">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{--primary:#FF6B35;--sidebar:#1A202C;--border:#E2E8F0;--radius:14px;--font:'Plus Jakarta Sans',sans-serif}
body{font-family:var(--font);min-height:100vh;background:var(--sidebar);display:flex;align-items:center;justify-content:center;padding:1rem}
.login-bg{position:fixed;inset:0;background:linear-gradient(135deg,#1A202C 0%,#2D3748 50%,#1A202C 100%);z-index:0}
.login-bg::before{content:'';position:fixed;inset:0;background:url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.02'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E")}
.login-wrap{position:relative;z-index:1;width:100%;max-width:400px}
.login-card{background:#fff;border-radius:var(--radius);padding:2rem;box-shadow:0 20px 60px rgba(0,0,0,.4)}
.login-logo{text-align:center;margin-bottom:2rem}
.logo-circle{width:72px;height:72px;background:linear-gradient(135deg,var(--primary),#FF8C5A);border-radius:20px;display:flex;align-items:center;justify-content:center;margin:0 auto .75rem;font-size:2rem;box-shadow:0 8px 24px rgba(255,107,53,.35)}
.login-logo h1{font-size:1.5rem;font-weight:800;color:#1A202C}
.login-logo p{font-size:.82rem;color:#718096;margin-top:.25rem}
.form-group{margin-bottom:1rem}
.form-label{display:block;font-size:.8rem;font-weight:600;color:#4A5568;margin-bottom:.4rem}
.form-control{width:100%;padding:.65rem .9rem;border:1.5px solid var(--border);border-radius:10px;font-size:.875rem;font-family:var(--font);color:#1A202C;background:#fff;transition:border-color .2s,box-shadow .2s;-webkit-appearance:none}
.form-control:focus{outline:none;border-color:var(--primary);box-shadow:0 0 0 3px rgba(255,107,53,.12)}
.input-wrap{position:relative}
.input-icon{position:absolute;left:.85rem;top:50%;transform:translateY(-50%);color:#A0AEC0;font-size:.9rem}
.input-wrap .form-control{padding-left:2.5rem}
.input-toggle{position:absolute;right:.85rem;top:50%;transform:translateY(-50%);background:none;border:none;color:#A0AEC0;cursor:pointer;padding:2px}
.btn-login{width:100%;padding:.75rem;background:linear-gradient(135deg,var(--primary),#FF8C5A);color:#fff;border:none;border-radius:10px;font-size:.95rem;font-weight:700;font-family:var(--font);cursor:pointer;transition:all .2s;margin-top:.5rem;display:flex;align-items:center;justify-content:center;gap:.5rem}
.btn-login:hover{transform:translateY(-1px);box-shadow:0 8px 24px rgba(255,107,53,.35)}
.btn-login:active{transform:none}
.forgot-link{text-align:center;margin-top:1rem;font-size:.82rem;color:#718096}
.forgot-link a{color:var(--primary);font-weight:600;text-decoration:none}
.alert{padding:.75rem 1rem;border-radius:10px;font-size:.85rem;font-weight:500;margin-bottom:1rem;display:flex;align-items:center;gap:.6rem}
.alert-error{background:#FFF5F5;color:#E53E3E;border:1px solid #FED7D7}
.alert-success{background:#F0FFF4;color:#38A169;border:1px solid #C6F6D5}
.demo-accounts{margin-top:1.5rem;padding-top:1.25rem;border-top:1px solid var(--border)}
.demo-title{font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#A0AEC0;text-align:center;margin-bottom:.75rem}
.demo-grid{display:grid;grid-template-columns:1fr 1fr;gap:.5rem}
.demo-btn{padding:.5rem;border:1.5px solid var(--border);border-radius:8px;background:#fff;cursor:pointer;text-align:center;font-family:var(--font);transition:all .2s}
.demo-btn:hover{border-color:var(--primary);background:#FFF0EB}
.demo-btn .role{display:block;font-size:.72rem;font-weight:700;color:#4A5568}
.demo-btn .email{display:block;font-size:.65rem;color:#A0AEC0;margin-top:.1rem}
.divider{display:flex;align-items:center;gap:.75rem;margin:1rem 0;color:#A0AEC0;font-size:.78rem}
.divider::before,.divider::after{content:'';flex:1;height:1px;background:var(--border)}
.saas-badge{text-align:center;margin-top:1.25rem}
.saas-badge span{font-size:.72rem;color:#718096}
.saas-badge strong{color:var(--primary)}
.loading-spinner{display:none;width:18px;height:18px;border:2.5px solid rgba(255,255,255,.3);border-top-color:#fff;border-radius:50%;animation:spin 1s linear infinite}
@keyframes spin{to{transform:rotate(360deg)}}
</style>
</head>
<body>
<div class="login-bg"></div>
<div class="login-wrap">
  <div class="login-card">
    <div class="login-logo">
      <div class="logo-circle">🍽</div>
      <h1>RestoCRM</h1>
      <p>Restaurant Management Platform</p>
    </div>

    <?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-error"><i class="fa fa-circle-exclamation"></i><?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success"><i class="fa fa-check-circle"></i><?= session()->getFlashdata('success') ?></div>
    <?php endif; ?>
    <?php if (!empty($errors)): ?>
    <div class="alert alert-error"><i class="fa fa-circle-exclamation"></i><?= implode('<br>', $errors) ?></div>
    <?php endif; ?>

    <form action="<?= base_url('login') ?>" method="POST" id="loginForm">
      <?= csrf_field() ?>

      <div class="form-group">
        <label class="form-label" for="email">Email Address</label>
        <div class="input-wrap">
          <i class="fa fa-envelope input-icon"></i>
          <input type="email" class="form-control" id="email" name="email"
                 placeholder="you@restaurant.com"
                 value="<?= old('email') ?>" autocomplete="email" required>
        </div>
      </div>

      <div class="form-group">
        <label class="form-label" for="password">Password</label>
        <div class="input-wrap">
          <i class="fa fa-lock input-icon"></i>
          <input type="password" class="form-control" id="password" name="password"
                 placeholder="Enter your password" autocomplete="current-password" required>
          <button type="button" class="input-toggle" onclick="togglePwd()">
            <i class="fa fa-eye" id="eyeIcon"></i>
          </button>
        </div>
      </div>

      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.5rem">
        <label style="display:flex;align-items:center;gap:.4rem;font-size:.82rem;color:#4A5568;cursor:pointer">
          <input type="checkbox" name="remember" style="accent-color:var(--primary)"> Remember me
        </label>
        <a href="<?= base_url('forgot-password') ?>" style="font-size:.82rem;color:var(--primary);font-weight:600;text-decoration:none">Forgot password?</a>
      </div>

      <button type="submit" class="btn-login" id="loginBtn">
        <span id="loginText"><i class="fa fa-right-to-bracket"></i> Sign In</span>
        <div class="loading-spinner" id="loginSpinner"></div>
      </button>
    </form>

    <!-- Demo Accounts -->
    <div class="demo-accounts">
      <div class="demo-title">— Quick Demo Access —</div>
      <div class="demo-grid">
        <button class="demo-btn" onclick="fillLogin('superadmin@restoCRM.com','admin@123')">
          <span class="role">⚡ Super Admin</span>
          <span class="email">Platform owner</span>
        </button>
        <button class="demo-btn" onclick="fillLogin('owner@spicegarden.com','admin@123')">
          <span class="role">🏪 Restaurant Admin</span>
          <span class="email">Full access</span>
        </button>
        <button class="demo-btn" onclick="fillLogin('manager@spicegarden.com','admin@123')">
          <span class="role">👔 Branch Manager</span>
          <span class="email">Branch control</span>
        </button>
        <button class="demo-btn" onclick="fillLogin('cashier@spicegarden.com','admin@123')">
          <span class="role">💰 Cashier / POS</span>
          <span class="email">Billing only</span>
        </button>
      </div>
    </div>

    <div class="saas-badge">
      <span>Powered by <strong>RestoCRM</strong> SaaS Platform</span>
    </div>
  </div>
</div>

<script>
function togglePwd() {
  const inp = document.getElementById('password');
  const ico = document.getElementById('eyeIcon');
  if (inp.type === 'password') {
    inp.type = 'text';
    ico.className = 'fa fa-eye-slash';
  } else {
    inp.type = 'password';
    ico.className = 'fa fa-eye';
  }
}

function fillLogin(email, pwd) {
  document.getElementById('email').value = email;
  document.getElementById('password').value = pwd;
  document.getElementById('email').focus();
}

document.getElementById('loginForm').addEventListener('submit', function() {
  document.getElementById('loginText').style.display = 'none';
  document.getElementById('loginSpinner').style.display = 'block';
  document.getElementById('loginBtn').disabled = true;
});
</script>
</body>
</html>
