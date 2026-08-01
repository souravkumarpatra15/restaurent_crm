<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1">
<title>Book at <?= esc($rest['name']) ?> — DinoViX</title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Plus Jakarta Sans',sans-serif;background:#F8FAFC;color:#0F172A;min-height:100vh}
a{text-decoration:none;color:inherit}
.topbar{background:#1A202C;color:#fff;padding:.75rem 1rem;display:flex;align-items:center;gap:.75rem;position:sticky;top:0;z-index:100}
.topbar-back{width:34px;height:34px;border-radius:8px;background:rgba(255,255,255,.1);color:#fff;border:none;display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:.9rem}
.topbar-name{font-weight:800;font-size:.95rem}
/* Hero */
.hero-img{height:220px;position:relative;overflow:hidden;background:#E2E8F0}
.hero-img img{width:100%;height:100%;object-fit:cover}
.hero-img-ph{width:100%;height:100%;display:flex;align-items:center;justify-content:center;font-size:5rem;background:linear-gradient(135deg,#E2E8F0,#CBD5E1)}
/* Info */
.info{background:#fff;border-radius:16px 16px 0 0;margin-top:-20px;padding:1.25rem;position:relative}
.rest-name{font-size:1.35rem;font-weight:900;margin-bottom:.3rem}
.rest-meta{display:flex;align-items:center;gap:.5rem;flex-wrap:wrap;font-size:.8rem;color:#64748B;margin-bottom:.75rem}
.tags{display:flex;gap:.35rem;flex-wrap:wrap;margin-bottom:.75rem}
.tag{background:#F1F5F9;color:#475569;padding:.25rem .7rem;border-radius:12px;font-size:.72rem;font-weight:600}
.details-grid{display:grid;grid-template-columns:1fr 1fr;gap:.5rem;margin-bottom:.875rem}
.detail-box{background:#F8FAFC;border-radius:10px;padding:.7rem;text-align:center}
.detail-val{font-weight:800;font-size:.95rem}
.detail-lbl{font-size:.68rem;color:#64748B;margin-top:.1rem}
/* Step cards */
.steps{padding:.875rem 1rem;max-width:520px;margin:0 auto}
.step-card{background:#fff;border-radius:14px;padding:1rem;margin-bottom:.75rem;border:1.5px solid #E2E8F0}
.step-title{font-weight:800;font-size:.9rem;margin-bottom:.75rem;display:flex;align-items:center;gap:.5rem;color:#0F172A}
.step-num{width:24px;height:24px;border-radius:50%;background:#FF6B35;color:#fff;display:flex;align-items:center;justify-content:center;font-size:.72rem;font-weight:900;flex-shrink:0}
/* Date pills */
.date-scroll{display:flex;gap:.5rem;overflow-x:auto;scrollbar-width:none;padding-bottom:.25rem;margin-bottom:.5rem}
.date-scroll::-webkit-scrollbar{display:none}
.dpill{flex-shrink:0;padding:.5rem .875rem;border-radius:10px;border:1.5px solid #E2E8F0;background:#fff;text-align:center;cursor:pointer;transition:all .15s;min-width:64px}
.dpill.on{border-color:#FF6B35;background:#FFF0EB}
.dpill-day{font-size:.65rem;color:#64748B;text-transform:uppercase;letter-spacing:.05em}
.dpill-date{font-size:.95rem;font-weight:800}
/* Guest selector */
.guest-row{display:flex;align-items:center;justify-content:center;gap:1.25rem;padding:.875rem 0}
.g-btn{width:44px;height:44px;border-radius:50%;border:2px solid #E2E8F0;background:#fff;font-size:1.3rem;font-weight:700;display:flex;align-items:center;justify-content:center;cursor:pointer;transition:all .15s}
.g-btn.plus{border-color:#FF6B35;background:#FF6B35;color:#fff}
.g-val{font-size:2.25rem;font-weight:900;min-width:52px;text-align:center}
/* Time slots */
.slot-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(90px,1fr));gap:.4rem}
.slot-btn{padding:.55rem .4rem;border:1.5px solid #E2E8F0;border-radius:10px;background:#fff;font-size:.8rem;font-weight:700;cursor:pointer;text-align:center;transition:all .15s}
.slot-btn:hover{border-color:#FF6B35;color:#FF6B35}
.slot-btn.on{border-color:#FF6B35;background:#FFF0EB;color:#FF6B35}
.slot-btn.full{opacity:.4;pointer-events:none;background:#F1F5F9}
.slots-loading{text-align:center;padding:1rem;color:#64748B;font-size:.85rem}
/* Form */
.field{margin-bottom:.75rem}
.field label{display:block;font-size:.72rem;font-weight:700;color:#64748B;text-transform:uppercase;letter-spacing:.05em;margin-bottom:.35rem}
.field input,.field select,.field textarea{width:100%;padding:.7rem .875rem;border:1.5px solid #E2E8F0;border-radius:10px;font-size:.9rem;font-family:inherit;outline:none;transition:border-color .2s}
.field input:focus,.field select:focus,.field textarea:focus{border-color:#FF6B35}
.field-row{display:grid;grid-template-columns:1fr 1fr;gap:.625rem}
/* Deposit notice */
.deposit-notice{background:#FFFBEB;border:1px solid #FDE68A;border-radius:10px;padding:.75rem;font-size:.82rem;margin-bottom:.75rem}
/* Submit */
.book-btn{width:100%;padding:.875rem;border:none;border-radius:12px;background:linear-gradient(135deg,#FF6B35,#FF8C5A);color:#fff;font-size:1rem;font-weight:800;cursor:pointer;font-family:inherit;transition:all .2s;box-shadow:0 4px 16px rgba(255,107,53,.35)}
.book-btn:hover{transform:translateY(-1px);box-shadow:0 6px 24px rgba(255,107,53,.45)}
.book-btn:disabled{opacity:.5;cursor:not-allowed;transform:none;box-shadow:none}
/* Summary bar */
.summary-bar{background:#1A202C;color:#fff;padding:.75rem 1rem;display:flex;align-items:center;justify-content:space-between;border-radius:12px;margin-bottom:.75rem;font-size:.82rem}
.summary-bar.hidden{display:none}
</style>
</head>
<body>

<div class="topbar">
  <button class="topbar-back" onclick="history.back()"><i class="fa fa-arrow-left"></i></button>
  <div class="topbar-name"><?= esc($rest['name']) ?></div>
</div>

<!-- Cover Image -->
<div class="hero-img">
  <?php if (!empty($rest['cover_image'])): ?>
  <img src="<?= base_url('images/uploads/'.$rest['cover_image']) ?>" alt="<?= esc($rest['name']) ?>">
  <?php else: ?>
  <div class="hero-img-ph">🍽</div>
  <?php endif; ?>
</div>

<!-- Info -->
<div style="padding:0 1rem;max-width:520px;margin:0 auto">
<div class="info">
  <div class="rest-name"><?= esc($rest['name']) ?></div>
  <div class="rest-meta">
    <span><i class="fa fa-location-dot" style="color:#FF6B35"></i> <?= esc($rest['city'] ?? '') ?></span>
    <?php if ($rest['cuisine_type']): ?><span>·</span><span><?= esc($rest['cuisine_type']) ?></span><?php endif; ?>
    <?php if ($rest['avg_cost_for_two'] > 0): ?><span>·</span><span>₹<?= number_format($rest['avg_cost_for_two']) ?> for 2</span><?php endif; ?>
  </div>
  <?php if (!empty($rest['short_desc'])): ?>
  <div style="font-size:.85rem;color:#475569;margin-bottom:.75rem;line-height:1.5"><?= esc($rest['short_desc']) ?></div>
  <?php endif; ?>
  <?php if ($rest['tags']): ?>
  <div class="tags">
    <?php foreach (explode(',',$rest['tags']) as $t): if(trim($t)): ?>
    <span class="tag"><?= esc(trim($t)) ?></span>
    <?php endif; endforeach; ?>
  </div>
  <?php endif; ?>
  <div class="details-grid">
    <div class="detail-box">
      <div class="detail-val"><i class="fa fa-clock" style="color:#FF6B35"></i> <?= date('g A',strtotime($rest['open_time']??'10:00')) ?>–<?= date('g A',strtotime($rest['close_time']??'23:00')) ?></div>
      <div class="detail-lbl">Hours</div>
    </div>
    <div class="detail-box">
      <div class="detail-val"><?= $rest['min_guests'] ?>–<?= $rest['max_guests'] ?> guests</div>
      <div class="detail-lbl">Party Size</div>
    </div>
    <?php if ($rest['deposit_required']): ?>
    <div class="detail-box" style="grid-column:1/-1">
      <div class="detail-val" style="color:#F59E0B">⚡ ₹<?= number_format($rest['deposit_amount']) ?> <?= $rest['deposit_type']==='per_person'?'per person':'deposit' ?> required</div>
      <div class="detail-lbl">Refundable on arrival</div>
    </div>
    <?php endif; ?>
  </div>
</div>

<!-- Booking Form Steps -->
<div class="steps">

  <!-- Step 1: Date -->
  <div class="step-card">
    <div class="step-title"><span class="step-num">1</span> Choose Date</div>
    <div class="date-scroll" id="dateScroll">
      <?php foreach (array_slice($availDates,0,14) as $d): ?>
      <div class="dpill <?= $d===date('Y-m-d')?'on':'' ?>" data-date="<?= $d ?>" onclick="selectDate(this)">
        <div class="dpill-day"><?= date('D',$d===date('Y-m-d')?time():strtotime($d)) ?></div>
        <div class="dpill-date"><?= date('d',strtotime($d)) ?></div>
        <div style="font-size:.6rem;color:#94A3B8"><?= date('M',strtotime($d)) ?></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Step 2: Guests -->
  <div class="step-card">
    <div class="step-title"><span class="step-num">2</span> Number of Guests</div>
    <div class="guest-row">
      <button class="g-btn" onclick="changeGuests(-1)">−</button>
      <span class="g-val" id="guestVal">2</span>
      <button class="g-btn plus" onclick="changeGuests(1)">+</button>
    </div>
    <div style="text-align:center;font-size:.75rem;color:#94A3B8">Min <?= $rest['min_guests'] ?> · Max <?= $rest['max_guests'] ?> guests</div>
  </div>

  <!-- Step 3: Time Slot -->
  <div class="step-card">
    <div class="step-title"><span class="step-num">3</span> Pick a Time</div>
    <div id="slotArea"><div class="slots-loading"><i class="fa fa-spinner fa-spin"></i> Select date first</div></div>
  </div>

  <!-- Summary Bar -->
  <div class="summary-bar hidden" id="summaryBar">
    <div>
      <div style="font-weight:800" id="sumDate">—</div>
      <div style="opacity:.7;font-size:.75rem" id="sumDetail">—</div>
    </div>
    <div style="font-size:.82rem;font-weight:700;color:#FF6B35" id="sumSlot">—</div>
  </div>

  <!-- Step 4: Guest Details -->
  <div class="step-card" id="detailsCard" style="display:none">
    <div class="step-title"><span class="step-num">4</span> Your Details</div>
    <div class="field-row">
      <div class="field"><label>Name *</label><input type="text" id="gName" placeholder="Your name"></div>
      <div class="field"><label>Phone *</label><input type="tel" id="gPhone" inputmode="numeric" placeholder="Mobile number"></div>
    </div>
    <div class="field"><label>Email</label><input type="email" id="gEmail" placeholder="Optional — for confirmation"></div>
    <div class="field-row">
      <div class="field">
        <label>Occasion</label>
        <select id="gOccasion">
          <option value="none">None</option>
          <option value="birthday">🎂 Birthday</option>
          <option value="anniversary">💍 Anniversary</option>
          <option value="date">❤️ Date Night</option>
          <option value="business">💼 Business</option>
          <option value="family">👨‍👩‍👧 Family</option>
          <option value="other">Other</option>
        </select>
      </div>
      <?php if (count($branches) > 1): ?>
      <div class="field">
        <label>Branch</label>
        <select id="gBranch">
          <?php foreach ($branches as $b): ?>
          <option value="<?= $b['id'] ?>"><?= esc($b['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <?php endif; ?>
    </div>
    <div class="field"><label>Special Requests</label><textarea id="gRequests" rows="2" placeholder="e.g. Window table, high chair needed, cake arrangement..."></textarea></div>

    <?php if ($rest['deposit_required']): ?>
    <div class="deposit-notice">
      ⚡ A deposit of ₹<?= number_format($rest['deposit_amount']) ?><?= $rest['deposit_type']==='per_person'?' per person':'' ?> is required to confirm.
      <?= $rest['accepts_online_payment'] ? 'Pay securely online after booking.' : 'Please arrange payment on arrival.' ?>
    </div>
    <?php endif; ?>

    <?php if (!empty($rest['booking_instructions'])): ?>
    <div style="background:#F0FFF4;border-radius:10px;padding:.75rem;font-size:.8rem;color:#065F46;margin-bottom:.75rem">
      <i class="fa fa-info-circle"></i> <?= esc($rest['booking_instructions']) ?>
    </div>
    <?php endif; ?>

    <button class="book-btn" id="bookBtn" onclick="submitBooking()">
      <i class="fa fa-calendar-check"></i> Confirm Booking
    </button>
  </div>

</div>
</div>

<script>
const REST_ID  = <?= $rest['id'] ?>;
const BASE     = '<?= base_url() ?>';
const CN       = '<?= csrf_token() ?>';
let   CT       = '<?= csrf_hash() ?>';
const MIN_PAX  = <?= $rest['min_guests'] ?? 1 ?>;
const MAX_PAX  = <?= $rest['max_guests'] ?? 20 ?>;

let selDate  = '<?= date('Y-m-d') ?>';
let selTime  = '';
let selGuests= 2;

// Select date
function selectDate(el) {
  document.querySelectorAll('.dpill').forEach(d=>d.classList.remove('on'));
  el.classList.add('on');
  selDate = el.dataset.date;
  selTime = '';
  loadSlots();
  updateSummary();
}

// Guests
function changeGuests(d) {
  selGuests = Math.max(MIN_PAX, Math.min(MAX_PAX, selGuests+d));
  document.getElementById('guestVal').textContent = selGuests;
  if (selDate) loadSlots();
  updateSummary();
}

// Load time slots
async function loadSlots() {
  const area = document.getElementById('slotArea');
  area.innerHTML = '<div class="slots-loading"><i class="fa fa-spinner fa-spin"></i> Loading slots...</div>';

  const body = new URLSearchParams({[CN]:CT, restaurant_id:REST_ID, date:selDate, pax:selGuests});
  const d    = await fetch(BASE+'book/slots',{method:'POST',body}).then(r=>r.json());

  if (!d.slots || !d.slots.length) {
    area.innerHTML = '<div class="slots-loading">😔 No available slots for this date. Try another date or fewer guests.</div>';
    return;
  }

  area.innerHTML = '<div class="slot-grid">' +
    d.slots.map(s => `<button class="slot-btn" data-time="${s.time}" onclick="selectSlot(this,'${s.time_fmt}')">${s.time_fmt}</button>`).join('') +
  '</div>';
}

// Select slot
function selectSlot(el, label) {
  document.querySelectorAll('.slot-btn').forEach(s=>s.classList.remove('on'));
  el.classList.add('on');
  selTime = el.dataset.time;
  updateSummary();
  document.getElementById('detailsCard').style.display = '';
  document.getElementById('detailsCard').scrollIntoView({behavior:'smooth',block:'start'});
}

function updateSummary() {
  const bar = document.getElementById('summaryBar');
  if (!selDate) { bar.classList.add('hidden'); return; }
  bar.classList.remove('hidden');
  const d = new Date(selDate+'T00:00:00');
  document.getElementById('sumDate').textContent = d.toLocaleDateString('en-IN',{weekday:'short',day:'numeric',month:'short'});
  document.getElementById('sumDetail').textContent = selGuests + ' guest' + (selGuests>1?'s':'');
  document.getElementById('sumSlot').textContent = selTime ? selTime.replace(/(\d+):(\d+):.*/,(_,h,m)=>{const hh=parseInt(h);return `${hh>12?hh-12:hh||12}:${m} ${hh>=12?'PM':'AM'}`}) : 'Pick time';
}

// Submit booking
async function submitBooking() {
  if (!selDate || !selTime) { alert('Please select a date and time slot'); return; }
  const name  = document.getElementById('gName').value.trim();
  const phone = document.getElementById('gPhone').value.trim();
  if (!name)  { alert('Please enter your name'); document.getElementById('gName').focus(); return; }
  if (!phone) { alert('Please enter your phone'); document.getElementById('gPhone').focus(); return; }

  const btn = document.getElementById('bookBtn');
  btn.disabled = true;
  btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Confirming...';

  const body = new URLSearchParams({
    [CN]: CT,
    restaurant_id:   REST_ID,
    branch_id:       document.getElementById('gBranch')?.value || '',
    date:            selDate,
    time:            selTime,
    guests:          selGuests,
    name,
    phone,
    email:           document.getElementById('gEmail').value,
    special_requests:document.getElementById('gRequests').value,
    occasion:        document.getElementById('gOccasion').value,
  });

  try {
    const d = await fetch(BASE+'book/reserve',{method:'POST',body}).then(r=>r.json());
    if (d.success) {
      window.location.href = d.confirm_url;
    } else {
      alert(d.message || 'Booking failed. Please try again.');
      btn.disabled = false;
      btn.innerHTML = '<i class="fa fa-calendar-check"></i> Confirm Booking';
    }
  } catch(e) {
    alert('Network error. Please try again.');
    btn.disabled = false;
    btn.innerHTML = '<i class="fa fa-calendar-check"></i> Confirm Booking';
  }
}

// Load initial slots
loadSlots();
updateSummary();
</script>
</body>
</html>
