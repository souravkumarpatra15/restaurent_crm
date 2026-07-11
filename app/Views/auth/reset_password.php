<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reset Password — DinoviX</title>
  <meta name="theme-color" content="#1A202C">
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="icon" type="image/png" href="<?= base_url('images/favicon.png') ?>">
  <link rel="apple-touch-icon" href="<?= base_url('images/favicon.png') ?>">
  <style>
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0
    }

    body {
      font-family: 'Plus Jakarta Sans', sans-serif;
      min-height: 100vh;
      background: #1A202C;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 1rem;
      overflow-x: hidden
    }

    /* ---------- Restaurant-themed background (matches login / forgot-password) ---------- */
    .login-bg {
      position: fixed;
      inset: 0;
      z-index: 0;
      background:
        radial-gradient(ellipse 620px 420px at 50% -8%, rgba(255, 107, 53, .16), transparent 62%),
        radial-gradient(ellipse 520px 520px at 102% 105%, rgba(255, 107, 53, .10), transparent 60%),
        radial-gradient(ellipse 480px 480px at -5% 100%, rgba(255, 107, 53, .07), transparent 60%),
        linear-gradient(135deg, #1A202C 0%, #2D3748 50%, #1A202C 100%)
    }

    .login-bg::before {
      content: '';
      position: fixed;
      inset: 0;
      opacity: .5;
      background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='90' height='90'%3E%3Cg fill='none' stroke='%23ffffff' stroke-opacity='0.05' stroke-width='1.1' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M16 10 L16 27 M20 10 L20 27 M24 10 L24 27 M16 27 Q16 34 20 34 Q24 34 24 27 M20 34 L20 58'/%3E%3Cpath d='M62 10 Q52 16 52 26 Q52 33 62 34 L62 58'/%3E%3Ccircle cx='40' cy='70' r='11'/%3E%3Ccircle cx='40' cy='70' r='6'/%3E%3C/g%3E%3C/svg%3E");
      background-size: 90px 90px
    }

    .login-bg::after {
      content: '';
      position: fixed;
      left: 0;
      right: 0;
      bottom: -12%;
      height: 45%;
      background: radial-gradient(ellipse 80% 100% at 50% 100%, rgba(255, 107, 53, .14), transparent 72%);
      z-index: 0
    }

    .steam-accent {
      position: fixed;
      top: 6%;
      left: 6%;
      width: 34px;
      height: 46px;
      opacity: .35;
      z-index: 0;
      pointer-events: none;
      display: none
    }

    @media (min-width:640px) {
      .steam-accent {
        display: block
      }
    }

    .steam-accent span {
      position: absolute;
      bottom: 10px;
      width: 3px;
      height: 14px;
      border-radius: 3px;
      background: rgba(255, 255, 255, .5);
      filter: blur(1px);
      animation: steamRise 3.6s ease-in-out infinite
    }

    .steam-accent span:nth-child(1) {
      left: 6px;
      animation-delay: 0s
    }

    .steam-accent span:nth-child(2) {
      left: 15px;
      animation-delay: .8s
    }

    .steam-accent span:nth-child(3) {
      left: 24px;
      animation-delay: 1.6s
    }

    .steam-accent .cup {
      position: absolute;
      bottom: 0;
      left: 4px;
      width: 26px;
      height: 12px;
      border: 2px solid rgba(255, 255, 255, .4);
      border-top: none;
      border-radius: 0 0 8px 8px
    }

    @keyframes steamRise {
      0% {
        transform: translateY(0) scaleY(.6);
        opacity: 0
      }

      25% {
        opacity: .8
      }

      100% {
        transform: translateY(-26px) scaleY(1.3);
        opacity: 0
      }
    }

    @media (prefers-reduced-motion:reduce) {
      .steam-accent span {
        animation: none;
        opacity: .3
      }
    }

    /* -------------------------------------------------------------------------------------- */

    .card {
      position: relative;
      z-index: 1;
      background: #fff;
      border-radius: 16px;
      padding: 2rem;
      width: 100%;
      max-width: 400px;
      box-shadow: 0 20px 60px rgba(0, 0, 0, .4);
      overflow: hidden
    }

    .card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 4px;
      background: linear-gradient(90deg, #FF6B35, #FF8C5A)
    }

    .logo {
      text-align: center;
      margin-bottom: 1.75rem
    }

    .logo-img {
      width: 200px;
      height: auto;
      display: block;
      margin: 0 auto .75rem;
      object-fit: contain
    }

    h2 {
      font-size: 1.25rem;
      font-weight: 800;
      color: #1A202C;
      text-align: center
    }

    p.sub {
      color: #718096;
      font-size: .85rem;
      text-align: center;
      margin: .5rem 0 1.5rem
    }

    .form-group {
      margin-bottom: 1rem
    }

    label {
      display: block;
      font-size: .8rem;
      font-weight: 600;
      color: #4A5568;
      margin-bottom: .35rem
    }

    .input-wrap {
      position: relative
    }

    .input-wrap i.icon {
      position: absolute;
      left: .85rem;
      top: 50%;
      transform: translateY(-50%);
      color: #A0AEC0
    }

    .toggle-pwd {
      position: absolute;
      right: .85rem;
      top: 50%;
      transform: translateY(-50%);
      background: none;
      border: none;
      color: #A0AEC0;
      cursor: pointer
    }

    input {
      width: 100%;
      padding: .65rem .9rem .65rem 2.5rem;
      border: 1.5px solid #E2E8F0;
      border-radius: 10px;
      font-size: .875rem;
      font-family: inherit
    }

    input:focus {
      outline: none;
      border-color: #FF6B35;
      box-shadow: 0 0 0 3px rgba(255, 107, 53, .12)
    }

    .btn {
      width: 100%;
      padding: .75rem;
      background: linear-gradient(135deg, #FF6B35, #FF8C5A);
      color: #fff;
      border: none;
      border-radius: 10px;
      font-size: .95rem;
      font-weight: 700;
      cursor: pointer;
      margin-top: .5rem;
      font-family: inherit
    }

    .back {
      text-align: center;
      margin-top: 1rem;
      font-size: .82rem;
      color: #718096
    }

    .back a {
      color: #FF6B35;
      font-weight: 600;
      text-decoration: none
    }

    .alert-error {
      background: #FFF5F5;
      color: #E53E3E;
      border: 1px solid #FED7D7;
      padding: .75rem 1rem;
      border-radius: 8px;
      font-size: .85rem;
      margin-bottom: 1rem
    }
  </style>
</head>

<body>
  <div class="login-bg"></div>
  <div class="steam-accent" aria-hidden="true">
    <span></span><span></span><span></span>
    <div class="cup"></div>
  </div>
  <div class="card">
    <div class="logo">
      <img src="<?= base_url('images/logo.png') ?>" alt="DinoviX" class="logo-img">
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