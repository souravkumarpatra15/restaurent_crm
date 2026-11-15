<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1">
<title>Rate Your Experience — <?= esc($order['rname']) ?></title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800;900&display=swap" rel="stylesheet">
<style>
:root {
  --p: <?= esc($order['theme_color'] ?? '#FF6B35') ?>;
  --pg: linear-gradient(135deg, #0B132B 0%, #17244A 100%);
  --card: #1C2A4A;
  --card2: rgba(255,255,255,.06);
  --ink: #EDF1FB;
  --mut: rgba(237,241,251,.55);
  --ok: #34D399;
  --bad: #F87171;
}
* { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Plus Jakarta Sans', system-ui, sans-serif; }
body { background: var(--pg); color: var(--ink); min-height: 100vh; padding-bottom: 2rem; -webkit-font-smoothing: antialiased; }

/* hero */
.hero { padding: 1.9rem 1.25rem 1.25rem; text-align: center; }
.hero-logo { width: 58px; height: 58px; margin: 0 auto .8rem; border-radius: 16px; background: var(--p); display: flex;
  align-items: center; justify-content: center; font-size: 1.5rem; color: #fff; box-shadow: 0 12px 30px -8px color-mix(in srgb, var(--p) 65%, transparent); }
.hero-rest { font-size: 1.02rem; font-weight: 900; letter-spacing: .01em; }
.hero-sub { font-size: .74rem; color: var(--mut); margin-top: .3rem; }

.wrap { max-width: 430px; margin: 0 auto; padding: 0 1rem; }
.card { background: var(--card); border: 1px solid rgba(255,255,255,.08); border-radius: 18px; padding: 1.25rem; margin-bottom: .875rem;
        box-shadow: 0 16px 40px rgba(2,6,23,.4); }
.card-title { font-weight: 800; font-size: .95rem; margin-bottom: .875rem; color: var(--ink); text-align: center; }
.kicker { font-size: .64rem; font-weight: 900; letter-spacing: .12em; text-transform: uppercase; color: var(--mut); }

/* star rating */
.stars { display: flex; gap: .5rem; justify-content: center; margin-bottom: .25rem; }
.star { font-size: 2.2rem; cursor: pointer; transition: transform .15s, opacity .15s; user-select: none; opacity: .22; color: var(--p);
        text-shadow: 0 6px 18px color-mix(in srgb, var(--p) 40%, transparent); }
.star.on { opacity: 1; transform: scale(1.18); }
.star:hover { opacity: .95; }
.star-label { text-align: center; font-size: .75rem; color: var(--mut); min-height: 1.2em; margin-bottom: .5rem; font-weight: 700; }

.row { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: .6rem; }
.group { margin-bottom: .75rem; text-align: center; }
.group label { display: block; font-size: .66rem; font-weight: 800; color: var(--mut); margin-bottom: .4rem; text-transform: uppercase; letter-spacing: .07em; }

textarea { width: 100%; border: 1.5px solid rgba(255,255,255,.14); border-radius: 12px; padding: .8rem; font-size: .88rem; resize: none;
           font-family: inherit; outline: none; transition: border-color .2s; background: var(--card2); color: var(--ink); }
textarea::placeholder { color: rgba(237,241,251,.35); }
textarea:focus { border-color: var(--p); }

.return-row { display: flex; align-items: center; gap: .625rem; padding: .8rem .85rem; background: var(--card2);
              border: 1px solid rgba(255,255,255,.08); border-radius: 12px; cursor: pointer; }
.return-row input { accent-color: var(--p); width: 20px; height: 20px; }
.return-row span { font-size: .84rem; font-weight: 700; }

.submit-btn { width: 100%; padding: .95rem; border: none; border-radius: 14px; font-size: .98rem; font-weight: 900; cursor: pointer;
  color: #1A1200; background: linear-gradient(135deg, var(--p), color-mix(in srgb, var(--p) 70%, #fff 12%));
  box-shadow: 0 14px 30px -10px color-mix(in srgb, var(--p) 70%, transparent); transition: filter .2s; }
.submit-btn:hover { filter: brightness(1.08); }
.submit-btn:disabled { opacity: .5; cursor: not-allowed; }

/* inline validation — never a native alert */
.form-err { display: none; margin-top: .6rem; text-align: center; font-size: .78rem; font-weight: 800; color: var(--bad);
            background: rgba(248,113,113,.12); border: 1px solid rgba(248,113,113,.35); border-radius: 10px; padding: .55rem .75rem; }

.success-screen { text-align: center; padding: 2.6rem 1rem; }
.success-screen .big { font-size: 3.6rem; margin-bottom: .7rem; }
.success-screen h2 { font-weight: 900; font-size: 1.3rem; margin-bottom: .5rem; }
.success-screen p { color: var(--mut); font-size: .86rem; line-height: 1.6; }
.already { text-align: center; padding: 2rem; color: var(--mut); }
</style>
</head>
<body>
<div class="hero">
  <div class="hero-logo"><i class="fa fa-utensils"></i></div>
  <div class="hero-rest"><?= esc($order['rname']) ?></div>
  <div class="hero-sub"><?= esc($order['bname'] ?? '') ?> · Order #<?= esc($order['order_number']) ?></div>
</div>

<?php if ($existing): ?>
<div class="wrap"><div class="card already"><div style="font-size:3rem">✅</div><div style="font-weight:900;margin:.5rem 0;color:var(--ink)">Already Submitted!</div><div style="font-size:.85rem">Thank you for your feedback.</div></div></div>

<?php else: ?>
<div class="wrap" id="formWrap">
  <div id="successScreen" style="display:none" class="card success-screen">
    <div class="big">🙏</div>
    <h2>Thank You!</h2>
    <p>Your feedback helps us serve you better.<br>See you again soon!</p>
  </div>

  <div id="formBody">
    <div class="card">
      <div class="card-title">How was your experience?</div>

      <!-- Overall -->
      <div style="margin-bottom:1.1rem">
        <div class="kicker" style="text-align:center;margin-bottom:.5rem">Overall</div>
        <div class="stars" id="stars-overall" data-key="overall">
          <?php for($i=1;$i<=5;$i++): ?><span class="star" data-val="<?= $i ?>">★</span><?php endfor; ?>
        </div>
        <div class="star-label" id="lbl-overall"></div>
      </div>

      <!-- Food / Service / Ambience -->
      <div class="row">
        <?php foreach(['food'=>'🍽 Food','service'=>'👨‍🍳 Service','ambience'=>'✨ Ambience'] as $k=>$lbl): ?>
        <div class="group">
          <label><?= $lbl ?></label>
          <div class="stars" id="stars-<?= $k ?>" data-key="<?= $k ?>" style="gap:.25rem">
            <?php for($i=1;$i<=5;$i++): ?><span class="star" data-val="<?= $i ?>" style="font-size:1.4rem">★</span><?php endfor; ?>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="card">
      <div class="card-title">Tell us more <span class="kicker" style="letter-spacing:.06em">(optional)</span></div>
      <textarea id="comment" rows="3" placeholder="What did you love? What can we improve?"></textarea>
      <div style="margin-top:.75rem">
        <label class="return-row">
          <input type="checkbox" id="wouldReturn" checked>
          <span>I would visit again! 🔄</span>
        </label>
      </div>
    </div>

    <button class="submit-btn" id="submitBtn" onclick="submitFeedback()">Submit Feedback ✨</button>
    <div class="form-err" id="formErr"></div>
  </div>
</div>

<script>
const ratings = {overall:0, food:0, service:0, ambience:0};
const labels  = ['','😞 Poor','😐 Average','🙂 Good','😊 Very Good','🤩 Excellent'];

document.querySelectorAll('.stars').forEach(group => {
  const key   = group.dataset.key;
  const stars = group.querySelectorAll('.star');
  stars.forEach(star => {
    star.addEventListener('click', () => {
      const val = parseInt(star.dataset.val);
      ratings[key] = val;
      stars.forEach((s,i) => s.classList.toggle('on', i < val));
      const lbl = document.getElementById('lbl-'+key);
      if (lbl) lbl.textContent = labels[val] || '';
      document.getElementById('formErr').style.display = 'none';
    });
    star.addEventListener('mouseenter', () => {
      const val = parseInt(star.dataset.val);
      stars.forEach((s,i) => s.style.opacity = i < val ? '1' : '.22');
    });
    group.addEventListener('mouseleave', () => {
      stars.forEach((s,i) => s.style.opacity = s.classList.contains('on') ? '1' : '.22');
    });
  });
});

function formError(msg){
  const el = document.getElementById('formErr');
  el.textContent = msg; el.style.display = 'block';
}

async function submitFeedback() {
  if (!ratings.overall) { formError('Please rate your overall experience first ⭐'); return; }
  const btn = document.getElementById('submitBtn');
  btn.disabled = true; btn.textContent = 'Submitting...';

  const body = new URLSearchParams({
    overall_rating:  ratings.overall,
    food_rating:     ratings.food,
    service_rating:  ratings.service,
    ambience_rating: ratings.ambience,
    comment:         document.getElementById('comment').value,
    would_return:    document.getElementById('wouldReturn').checked ? 1 : 0,
  });

  const r = await fetch('<?= base_url('feedback/'.$token.'/submit') ?>', {method:'POST', body});
  const d = await r.json();
  if (d.success) {
    document.getElementById('formBody').style.display = 'none';
    document.getElementById('successScreen').style.display = 'block';
  } else {
    btn.disabled = false; btn.textContent = 'Submit Feedback ✨';
    formError(d.message || 'Could not submit — please try again');
  }
}
</script>
<?php endif; ?>
</body>
</html>
