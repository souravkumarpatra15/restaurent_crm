<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
  <title>Sign Up — DinoviX</title>
  <meta name="theme-color" content="#1A202C">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="icon" type="image/png" href="<?= base_url('images/favicon.png') ?>">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0 }
    :root { --primary: #FF6B35; --sidebar: #1A202C; --border: #E2E8F0; --radius: 14px; --font: 'Plus Jakarta Sans', sans-serif }
    body {
      font-family: var(--font);
      min-height: 100vh;
      background: linear-gradient(160deg, var(--sidebar) 0%, #2D3748 100%);
      display: flex; align-items: center; justify-content: center;
      padding: 1.5rem .75rem; overflow-x: hidden; color: #0F172A;
    }
    .card {
      width: 100%; max-width: 560px;
      background: #fff; border-radius: var(--radius);
      padding: 1.75rem 1.5rem; box-shadow: 0 20px 50px rgba(0,0,0,.35);
      max-height: 96vh; overflow-y: auto;
    }
    .logo { text-align: center; margin-bottom: 1.1rem }
    .logo i { font-size: 2rem; color: var(--primary) }
    .logo h1 { font-size: 1.25rem; font-weight: 800; margin-top: .35rem }
    .logo p { font-size: .8rem; color: #64748B; margin-top: .15rem }
    .trial { background: #FFFBEB; border: 1px solid #FDE68A; color: #92400E; font-size: .78rem; font-weight: 700; text-align: center; border-radius: 10px; padding: .5rem .75rem; margin-bottom: 1rem }
    .alert-error { background: #FEF2F2; border: 1px solid #FECACA; color: #B91C1C; font-size: .78rem; font-weight: 700; border-radius: 10px; padding: .5rem .75rem; margin-bottom: .9rem }
    .grid2 { display: grid; grid-template-columns: 1fr 1fr; gap: .6rem }
    @media (max-width: 520px) { .grid2 { grid-template-columns: 1fr } }
    .group { margin-bottom: .65rem }
    .label { display: block; font-size: .72rem; font-weight: 800; text-transform: uppercase; letter-spacing: .04em; color: #475569; margin-bottom: .25rem }
    .input {
      width: 100%; padding: .55rem .75rem; font: 500 .88rem var(--font);
      border: 1.5px solid var(--border); border-radius: 10px; background: #F8FAFC; outline: none; transition: border-color .15s
    }
    .input:focus { border-color: var(--primary); background: #fff }
    .plans { display: grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap: .5rem; margin-bottom: .65rem }
    .plan { border: 1.5px solid var(--border); border-radius: 12px; padding: .6rem .7rem; cursor: pointer; background: #F8FAFC; position: relative; transition: all .15s }
    .plan input { position: absolute; opacity: 0 }
    .plan.sel { border-color: var(--primary); background: #FFF4EF; box-shadow: 0 0 0 3px rgba(255,107,53,.15) }
    .plan b { display: block; font-size: .82rem }
    .plan span.price { font-size: .9rem; font-weight: 800; color: var(--primary) }
    .plan small { display: block; color: #64748B; font-size: .64rem; margin-top: .15rem; line-height: 1.35 }
    .terms { font-size: .68rem; color: #64748B; line-height: 1.5; margin: .4rem 0 .8rem }
    .btn {
      width: 100%; padding: .7rem; border: none; border-radius: 10px;
      background: var(--primary); color: #fff; font: 800 .9rem var(--font);
      cursor: pointer; transition: transform .1s, filter .15s;
    }
    .btn:hover { filter: brightness(1.08) }
    .btn:active { transform: scale(.985) }
    .alt { text-align: center; margin-top: .9rem; font-size: .8rem; color: #64748B }
    .alt a { color: var(--primary); font-weight: 800; text-decoration: none }
  </style>
</head>

<body>
  <div class="card">
    <div class="logo">
      <i class="fa fa-utensils"></i>
      <h1>Create your restaurant account</h1>
      <p>Run orders, tables, billing & customers on DinoviX</p>
    </div>

    <div class="trial"><i class="fa fa-gift"></i>&nbsp; 14-day free trial — no card needed</div>

    <?php if (session()->getFlashdata('error')): ?>
      <div class="alert-error"><i class="fa fa-circle-exclamation"></i>&nbsp; <?= esc(session()->getFlashdata('error')) ?></div>
    <?php endif; ?>

    <form method="POST" action="<?= base_url('signup') ?>" autocomplete="off">
      <?= csrf_field() ?>
      <div class="group">
        <label class="label">Restaurant name *</label>
        <input class="input" type="text" name="restaurant_name" required placeholder="e.g. Blue Orchid Kitchen" value="<?= esc(old('restaurant_name')) ?>">
      </div>
      <div class="grid2">
        <div class="group">
          <label class="label">City</label>
          <input class="input" type="text" name="city" placeholder="Kolkata" value="<?= esc(old('city')) ?>">
        </div>
        <div class="group">
          <label class="label">Restaurant type</label>
          <select class="input" name="restaurant_type">
            <?php foreach (['casual_dining' => 'Casual Dining', 'qsr' => 'Quick Service', 'cafe' => 'Cafe', 'fine_dining' => 'Fine Dining', 'bakery' => 'Bakery', 'bar' => 'Bar', 'cloud_kitchen' => 'Cloud Kitchen', 'food_truck' => 'Food Truck', 'dhaba' => 'Dhaba', 'pizza' => 'Pizzeria', 'ice_cream' => 'Ice Cream', 'buffet' => 'Buffet', 'hotel' => 'Hotel', 'juice_bar' => 'Juice Bar', 'other' => 'Other'] as $v => $l): ?>
              <option value="<?= $v ?>"><?= $l ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      <div class="group">
        <label class="label">Owner name *</label>
        <input class="input" type="text" name="owner_name" required placeholder="Your full name" value="<?= esc(old('owner_name')) ?>">
      </div>
      <div class="grid2">
        <div class="group">
          <label class="label">Email (login) *</label>
          <input class="input" type="email" name="email" required placeholder="you@restaurant.com" value="<?= esc(old('email')) ?>">
        </div>
        <div class="group">
          <label class="label">Phone *</label>
          <input class="input" type="tel" name="phone" required placeholder="9876543210" value="<?= esc(old('phone')) ?>">
        </div>
      </div>
      <div class="group">
        <label class="label">Password * (min 6 chars)</label>
        <input class="input" type="password" name="password" required minlength="6" placeholder="Create a password">
      </div>

      <label class="label">Choose your plan</label>
      <div class="plans">
        <?php foreach ($plans as $p): ?>
        <label class="plan<?= $p['price_monthly'] == 0 || (int)($p['id']) === (int)($plans[0]['id'] ?? 0) ? ' sel' : '' ?>">
          <input type="radio" name="plan_id" value="<?= (int)$p['id'] ?>" data-plan-name="<?= esc($p['name']) ?>" <?= $p['price_monthly'] == 0 ? 'checked' : '' ?>>
          <b><?= esc($p['name']) ?></b>
          <span class="price">₹<?= number_format((float)$p['price_monthly'], 0) ?>/mo</span>
          <small><?= esc(str_replace(['"', '{', '}', '[', ']'], ' ', substr((string)$p['features_json'], 0, 80))) ?></small>
        </label>
        <?php endforeach; ?>
      </div>

      <p class="terms">By signing up you agree to use DinoviX for your restaurant operations. You can change plans anytime. Your trial ends after <strong>14 days</strong> — upload staff, menu and go live while it runs.</p>
      <button class="btn" type="submit"><i class="fa fa-rocket"></i>&nbsp; Start free trial</button>
    </form>

    <div class="alt">Already have an account? <a href="<?= base_url('login') ?>">Login</a></div>
  </div>

  <script>
    document.querySelectorAll('.plan').forEach(pl => {
      pl.addEventListener('click', () => {
        document.querySelectorAll('.plan').forEach(x => x.classList.remove('sel'));
        pl.classList.add('sel');
        pl.querySelector('input').checked = true;
      });
    });
  </script>
</body>
</html>
