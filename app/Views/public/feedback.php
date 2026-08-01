<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1">
<title>Rate Your Experience — <?= esc($order['rname']) ?></title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Segoe UI',system-ui,sans-serif;background:#f8fafc;min-height:100vh;padding-bottom:2rem}
.topbar{background:<?= esc($order['theme_color'] ?? '#FF6B35') ?>;color:#fff;padding:1rem 1.25rem;text-align:center}
.topbar-name{font-size:1.1rem;font-weight:800}
.topbar-sub{font-size:.78rem;opacity:.85;margin-top:.2rem}
.wrap{max-width:420px;margin:1.25rem auto;padding:0 1rem}
.card{background:#fff;border-radius:16px;padding:1.25rem;margin-bottom:.875rem;box-shadow:0 2px 12px rgba(0,0,0,.07)}
.card-title{font-weight:800;font-size:.95rem;margin-bottom:.875rem;color:#0F172A}
/* Star rating */
.stars{display:flex;gap:.5rem;justify-content:center;margin-bottom:.25rem}
.star{font-size:2.2rem;cursor:pointer;transition:transform .15s;user-select:none;opacity:.3}
.star.on{opacity:1;transform:scale(1.15)}
.star:hover{opacity:.9}
.star-label{text-align:center;font-size:.75rem;color:#64748B;min-height:1.2em;margin-bottom:.5rem}
.row{display:grid;grid-template-columns:1fr 1fr;gap:.75rem}
.group{margin-bottom:.75rem}
.group label{display:block;font-size:.72rem;font-weight:700;color:#64748B;margin-bottom:.35rem;text-transform:uppercase;letter-spacing:.05em}
textarea{width:100%;border:1.5px solid #E2E8F0;border-radius:10px;padding:.75rem;font-size:.9rem;resize:none;font-family:inherit;outline:none;transition:border-color .2s}
textarea:focus{border-color:<?= esc($order['theme_color'] ?? '#FF6B35') ?>}
.return-row{display:flex;align-items:center;gap:.625rem;padding:.75rem;background:#F8FAFC;border-radius:10px;cursor:pointer}
.return-row input{accent-color:<?= esc($order['theme_color'] ?? '#FF6B35') ?>;width:20px;height:20px}
.return-row span{font-size:.875rem;font-weight:600}
.submit-btn{width:100%;padding:.875rem;border:none;border-radius:12px;background:<?= esc($order['theme_color'] ?? '#FF6B35') ?>;color:#fff;font-size:1rem;font-weight:800;cursor:pointer;transition:opacity .2s}
.submit-btn:hover{opacity:.9}
.submit-btn:disabled{opacity:.5;cursor:not-allowed}
.success-screen{text-align:center;padding:3rem 1rem}
.success-screen .big{font-size:4rem;margin-bottom:.75rem}
.success-screen h2{font-weight:900;font-size:1.3rem;margin-bottom:.5rem}
.success-screen p{color:#64748B;font-size:.9rem}
.already{text-align:center;padding:2rem;color:#64748B}
</style>
</head>
<body>
<div class="topbar">
  <div class="topbar-name"><?= esc($order['rname']) ?></div>
  <div class="topbar-sub"><?= esc($order['bname'] ?? '') ?> · Order #<?= esc($order['order_number']) ?></div>
</div>

<?php if ($existing): ?>
<div class="wrap"><div class="card already"><div style="font-size:3rem">✅</div><div style="font-weight:800;margin:.5rem 0">Already Submitted!</div><div style="font-size:.85rem">Thank you for your feedback.</div></div></div>

<?php else: ?>
<div class="wrap" id="formWrap">
  <div id="successScreen" style="display:none" class="card success-screen">
    <div class="big">🙏</div>
    <h2>Thank You!</h2>
    <p>Your feedback helps us serve you better.<br>See you again soon!</p>
  </div>

  <div id="formBody">
    <div class="card">
      <div class="card-title" style="text-align:center">How was your experience?</div>

      <!-- Overall -->
      <div style="margin-bottom:1rem">
        <div style="font-size:.72rem;font-weight:700;color:#64748B;text-align:center;text-transform:uppercase;letter-spacing:.05em;margin-bottom:.5rem">Overall</div>
        <div class="stars" id="stars-overall" data-key="overall">
          <?php for($i=1;$i<=5;$i++): ?><span class="star" data-val="<?= $i ?>">★</span><?php endfor; ?>
        </div>
        <div class="star-label" id="lbl-overall"></div>
      </div>

      <!-- Food / Service / Ambience -->
      <div class="row">
        <?php foreach(['food'=>'🍽 Food Quality','service'=>'👨‍🍳 Service','ambience'=>'✨ Ambience'] as $k=>$lbl): ?>
        <div style="text-align:center">
          <div style="font-size:.72rem;font-weight:700;color:#64748B;text-transform:uppercase;letter-spacing:.05em;margin-bottom:.4rem"><?= $lbl ?></div>
          <div class="stars" id="stars-<?= $k ?>" data-key="<?= $k ?>" style="gap:.3rem">
            <?php for($i=1;$i<=5;$i++): ?><span class="star" data-val="<?= $i ?>" style="font-size:1.5rem">★</span><?php endfor; ?>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="card">
      <div class="card-title">Tell us more (optional)</div>
      <textarea id="comment" rows="3" placeholder="What did you love? What can we improve?"></textarea>
      <div style="margin-top:.75rem">
        <label class="return-row">
          <input type="checkbox" id="wouldReturn" checked>
          <span>I would visit again! 🔄</span>
        </label>
      </div>
    </div>

    <button class="submit-btn" id="submitBtn" onclick="submitFeedback()">Submit Feedback ✨</button>
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
    });
    star.addEventListener('mouseenter', () => {
      const val = parseInt(star.dataset.val);
      stars.forEach((s,i) => s.style.opacity = i < val ? '1' : '.3');
    });
    group.addEventListener('mouseleave', () => {
      stars.forEach((s,i) => s.style.opacity = i < ratings[key] ? '1' : '.3');
    });
  });
});

async function submitFeedback() {
  if (!ratings.overall) { alert('Please rate your overall experience'); return; }
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
    document.getElementById('formBody').style.display    = 'none';
    document.getElementById('successScreen').style.display = 'block';
  } else {
    alert(d.message || 'Error submitting');
    btn.disabled = false; btn.textContent = 'Submit Feedback ✨';
  }
}
</script>
<?php endif; ?>
</body>
</html>
