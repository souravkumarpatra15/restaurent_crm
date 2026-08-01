<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1">
<title>Book a Table — DinoViX</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Plus Jakarta Sans',sans-serif;background:#F8FAFC;color:#0F172A}
a{text-decoration:none;color:inherit}

/* Hero */
.hero{background:linear-gradient(135deg,#1A202C 0%,#2D3748 60%,#FF6B35 100%);padding:3rem 1rem 4rem;text-align:center;position:relative;overflow:hidden}
.hero::before{content:'';position:absolute;inset:0;background:url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Ccircle cx='30' cy='30' r='20'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");pointer-events:none}
.hero-logo{font-size:1.5rem;font-weight:900;color:#fff;margin-bottom:1.5rem;display:flex;align-items:center;justify-content:center;gap:.5rem}
.hero-logo span{background:linear-gradient(135deg,#FF6B35,#FF8C5A);-webkit-background-clip:text;-webkit-text-fill-color:transparent}
.hero h1{font-size:clamp(1.5rem,5vw,2.5rem);font-weight:900;color:#fff;margin-bottom:.75rem;line-height:1.15}
.hero p{color:rgba(255,255,255,.7);font-size:.95rem;margin-bottom:2rem}

/* Search bar */
.search-bar{background:#fff;border-radius:16px;padding:.875rem 1rem;display:flex;flex-wrap:wrap;gap:.625rem;max-width:680px;margin:0 auto;box-shadow:0 8px 40px rgba(0,0,0,.25)}
.sb-field{display:flex;align-items:center;gap:.5rem;flex:1;min-width:140px;border-right:1px solid #E2E8F0;padding-right:.75rem}
.sb-field:last-of-type{border-right:none}
.sb-field i{color:#FF6B35;font-size:.9rem;flex-shrink:0}
.sb-field input,.sb-field select{border:none;outline:none;font-size:.875rem;font-family:inherit;width:100%;background:transparent;color:#0F172A}
.sb-btn{padding:.75rem 1.5rem;background:linear-gradient(135deg,#FF6B35,#FF8C5A);color:#fff;border:none;border-radius:10px;font-weight:800;font-size:.9rem;cursor:pointer;white-space:nowrap;font-family:inherit}
.sb-btn:hover{opacity:.9}

/* Body */
.body{max-width:1100px;margin:0 auto;padding:1.5rem 1rem}
.section-title{font-weight:800;font-size:1.1rem;margin-bottom:1rem;display:flex;align-items:center;gap:.5rem}
.result-count{font-size:.78rem;font-weight:500;color:#64748B;margin-left:auto}

/* Restaurant Card */
.rest-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(290px,1fr));gap:1rem}
.rcard{background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,.07);transition:transform .2s,box-shadow .2s;cursor:pointer}
.rcard:hover{transform:translateY(-3px);box-shadow:0 8px 28px rgba(0,0,0,.13)}
.rcard-img{height:170px;background:#E2E8F0;position:relative;overflow:hidden}
.rcard-img img{width:100%;height:100%;object-fit:cover}
.rcard-img-placeholder{width:100%;height:100%;display:flex;align-items:center;justify-content:center;font-size:3.5rem;background:linear-gradient(135deg,#f8fafc,#e2e8f0)}
.rcard-badge{position:absolute;top:.625rem;right:.625rem;padding:.25rem .7rem;border-radius:20px;font-size:.68rem;font-weight:800;letter-spacing:.04em}
.rcard-badge.avail{background:#D1FAE5;color:#065F46}
.rcard-badge.full{background:#FEE2E2;color:#991B1B}
.rcard-body{padding:.875rem}
.rcard-name{font-weight:800;font-size:1rem;margin-bottom:.2rem}
.rcard-meta{font-size:.75rem;color:#64748B;display:flex;align-items:center;gap:.375rem;flex-wrap:wrap;margin-bottom:.5rem}
.rcard-meta span{display:flex;align-items:center;gap:.2rem}
.rcard-tags{display:flex;gap:.35rem;flex-wrap:wrap;margin-bottom:.625rem}
.rcard-tag{background:#F1F5F9;color:#475569;padding:.2rem .6rem;border-radius:12px;font-size:.68rem;font-weight:600}
.rcard-foot{display:flex;align-items:center;justify-content:space-between;border-top:1px solid #F1F5F9;padding-top:.625rem}
.rcard-cost{font-size:.78rem;color:#64748B}
.rcard-cost b{color:#0F172A}
.rcard-book{padding:.45rem 1rem;background:linear-gradient(135deg,#FF6B35,#FF8C5A);color:#fff;border:none;border-radius:8px;font-size:.78rem;font-weight:800;cursor:pointer;font-family:inherit}

/* Empty state */
.empty{text-align:center;padding:4rem 1rem;color:#64748B}
.empty-icon{font-size:4rem;margin-bottom:1rem}
.empty h3{font-weight:800;font-size:1.1rem;color:#0F172A;margin-bottom:.5rem}

/* Nav */
.nav{background:#fff;border-bottom:1px solid #E2E8F0;padding:.75rem 1rem;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:100}
.nav-logo{font-weight:900;font-size:1rem;display:flex;align-items:center;gap:.4rem}
.nav-logo .dv{color:#FF6B35}
.nav-links{display:flex;gap:1rem;font-size:.82rem;font-weight:600;color:#475569}
.nav-links a:hover{color:#FF6B35}

@media(max-width:640px){
  .sb-field:nth-child(2){display:none}
  .rest-grid{grid-template-columns:1fr}
}
</style>
</head>
<body>

<nav class="nav">
  <div class="nav-logo">🍽 Dino<span class="dv">ViX</span> <span style="font-size:.7rem;font-weight:500;color:#94A3B8;border-left:1px solid #E2E8F0;padding-left:.5rem;margin-left:.1rem">Table Booking</span></div>
  <div class="nav-links">
    <a href="<?= base_url('book/my-bookings') ?>">My Bookings</a>
    <a href="<?= base_url() ?>">Home</a>
  </div>
</nav>

<!-- Hero -->
<div class="hero">
  <h1>Book a Table at<br>Your Favourite Restaurant</h1>
  <p>Reserve instantly · No calling needed · Confirmed in seconds</p>

  <form method="GET" action="<?= base_url('book') ?>">
    <div class="search-bar">
      <div class="sb-field">
        <i class="fa fa-search"></i>
        <input type="text" name="q" value="<?= esc($q) ?>" placeholder="Restaurant, cuisine, vibe...">
      </div>
      <div class="sb-field">
        <i class="fa fa-location-dot"></i>
        <select name="city">
          <option value="">Any City</option>
          <?php foreach ($cities as $c): ?>
          <option value="<?= esc($c) ?>" <?= $city===$c?'selected':'' ?>><?= esc($c) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="sb-field">
        <i class="fa fa-calendar"></i>
        <input type="date" name="date" value="<?= esc($date) ?>" min="<?= date('Y-m-d') ?>">
      </div>
      <div class="sb-field" style="border:none">
        <i class="fa fa-users"></i>
        <select name="pax">
          <?php for($i=1;$i<=20;$i++): ?>
          <option value="<?= $i ?>" <?= $pax==$i?'selected':'' ?>><?= $i ?> Guest<?= $i>1?'s':'' ?></option>
          <?php endfor; ?>
        </select>
      </div>
      <button class="sb-btn" type="submit"><i class="fa fa-search"></i> Search</button>
    </div>
  </form>
</div>

<!-- Results -->
<div class="body">
  <div class="section-title">
    <i class="fa fa-store" style="color:#FF6B35"></i>
    <?= empty($q) && empty($city) ? 'All Restaurants' : 'Search Results' ?>
    <span class="result-count"><?= count($restaurants) ?> found</span>
  </div>

  <?php if (empty($restaurants)): ?>
  <div class="empty">
    <div class="empty-icon">🍽</div>
    <h3>No Restaurants Found</h3>
    <p>Try a different city or search term</p>
  </div>
  <?php else: ?>
  <div class="rest-grid">
    <?php foreach ($restaurants as $r): ?>
    <a href="<?= base_url('book/'.($r['booking_slug'] ?: $r['slug'])) ?>?date=<?= esc($date) ?>&pax=<?= $pax ?>" class="rcard">
      <div class="rcard-img">
        <?php if (!empty($r['cover_image'])): ?>
        <img src="<?= base_url('images/uploads/'.$r['cover_image']) ?>" alt="<?= esc($r['name']) ?>" loading="lazy">
        <?php else: ?>
        <div class="rcard-img-placeholder">🍽</div>
        <?php endif; ?>
        <span class="rcard-badge <?= $r['has_slots']?'avail':'full' ?>">
          <?= $r['has_slots'] ? '● Available' : '● Full Today' ?>
        </span>
        <?php if ($r['accepts_online_payment']): ?>
        <span style="position:absolute;top:.625rem;left:.625rem;background:#EEF2FF;color:#4F46E5;padding:.2rem .6rem;border-radius:12px;font-size:.65rem;font-weight:800">💳 Pay Online</span>
        <?php endif; ?>
      </div>
      <div class="rcard-body">
        <div class="rcard-name"><?= esc($r['name']) ?></div>
        <div class="rcard-meta">
          <span><i class="fa fa-location-dot"></i><?= esc($r['city']) ?></span>
          <?php if ($r['cuisine_type']): ?>
          <span>·</span><span><?= esc($r['cuisine_type']) ?></span>
          <?php endif; ?>
          <?php if ($r['restaurant_type']): ?>
          <span>·</span><span><?= ucfirst(str_replace('_',' ',$r['restaurant_type'])) ?></span>
          <?php endif; ?>
        </div>
        <?php if ($r['short_desc']): ?>
        <div style="font-size:.78rem;color:#475569;margin-bottom:.5rem;line-height:1.4"><?= esc($r['short_desc']) ?></div>
        <?php endif; ?>
        <?php if ($r['tags']): ?>
        <div class="rcard-tags">
          <?php foreach (array_slice(explode(',',trim($r['tags'])),0,3) as $tag): ?>
          <span class="rcard-tag"><?= esc(trim($tag)) ?></span>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
        <div class="rcard-foot">
          <div class="rcard-cost">
            <?php if ($r['avg_cost_for_two'] > 0): ?>
            <b>₹<?= number_format($r['avg_cost_for_two']) ?></b> for two
            <?php else: ?>
            <span>Price on request</span>
            <?php endif; ?>
            <?php if ($r['deposit_required']): ?>
            <span style="color:#F59E0B;font-weight:700;display:block;font-size:.68rem">⚡ Deposit: ₹<?= number_format($r['deposit_amount']) ?></span>
            <?php endif; ?>
          </div>
          <button class="rcard-book">Book Table →</button>
        </div>
      </div>
    </a>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>

</body>
</html>
