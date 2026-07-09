<?php $this->extend('layouts/main'); $this->section('content'); ?>
<?php
$plansJs = [];
foreach ($plans as $p) {
    $plansJs[$p['id']] = ['price_monthly'=>$p['price_monthly'],'price_yearly'=>$p['price_yearly'],'name'=>$p['name']];
}
?>
<div style="padding:0 1rem">

  <!-- Stats row -->
  <div class="stats-grid" style="margin-bottom:1rem">
    <div class="stat-card green"><div class="stat-icon green"><i class="fa fa-check-circle"></i></div>
      <div><div class="stat-value"><?= $stats['active'] ?></div><div class="stat-label">Active</div></div></div>
    <div class="stat-card orange"><div class="stat-icon orange"><i class="fa fa-clock"></i></div>
      <div><div class="stat-value"><?= $stats['trial'] ?></div><div class="stat-label">Trial</div></div></div>
    <div class="stat-card red"><div class="stat-icon red"><i class="fa fa-ban"></i></div>
      <div><div class="stat-value"><?= $stats['expired']+$stats['suspended'] ?></div><div class="stat-label">Inactive</div></div></div>
    <div class="stat-card"><div class="stat-icon"><i class="fa fa-indian-rupee-sign"></i></div>
      <div><div class="stat-value">₹<?= number_format($stats['mrr']) ?></div><div class="stat-label">MRR</div></div></div>
  </div>

  <!-- Expiring soon alert -->
  <?php if (!empty($expiring)): ?>
  <div class="alert alert-warning" style="margin-bottom:1rem;display:flex;flex-wrap:wrap;gap:.5rem;align-items:center">
    <span><i class="fa fa-triangle-exclamation"></i> <strong><?= count($expiring) ?></strong> subscription<?= count($expiring)>1?'s':'' ?> expiring in 7 days:</span>
    <?php foreach ($expiring as $e): ?>
      <button onclick="openPaymentModal(<?= $e['id'] ?>,'<?= esc(addslashes($e['name'])) ?>')"
              class="btn btn-sm btn-primary" style="font-size:.72rem">
        <i class="fa fa-money-bill-wave"></i> <?= esc($e['name']) ?>
      </button>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <!-- Flash messages -->
  <?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success" style="margin-bottom:1rem"><i class="fa fa-check-circle"></i> <?= session()->getFlashdata('success') ?></div>
  <?php endif; ?>

  <!-- Filter tabs -->
  <div style="display:flex;gap:.4rem;margin-bottom:1rem;overflow-x:auto;scrollbar-width:none">
    <?php foreach ([''=> 'All','active'=>'Active','trial'=>'Trial','expired'=>'Expired','suspended'=>'Suspended','cancelled'=>'Cancelled'] as $k=>$v): ?>
    <a href="?status=<?= $k ?>" class="btn btn-sm <?= $filter===$k?'btn-primary':'btn-outline' ?>" style="flex-shrink:0"><?= $v ?></a>
    <?php endforeach; ?>
  </div>

  <!-- Table -->
  <div class="card">
    <div class="card-header">
      <span class="card-title">Subscriptions (<?= count($subs) ?>)</span>
    </div>
    <div class="table-wrap">
      <table class="table">
        <thead>
          <tr><th>Restaurant</th><th>Plan</th><th>Cycle</th><th>Status</th><th>Expires</th><th style="min-width:200px">Actions</th></tr>
        </thead>
        <tbody>
          <?php if (empty($subs)): ?>
          <tr><td colspan="6"><div class="empty-state" style="padding:2rem"><i class="fa fa-credit-card"></i><p>No subscriptions</p></div></td></tr>
          <?php else: foreach ($subs as $s):
            $sc = ['active'=>'success','trial'=>'warning','expired'=>'danger','suspended'=>'danger','cancelled'=>'gray'];
            $daysLeft = $s['subscription_ends_at'] ? ceil((strtotime($s['subscription_ends_at'])-time())/86400) : null;
          ?>
          <tr>
            <td>
              <div style="font-weight:600;font-size:.875rem"><?= esc($s['name']) ?></div>
              <div style="font-size:.72rem;color:var(--text-muted)"><?= esc($s['email']) ?></div>
            </td>
            <td><span class="badge-pill badge-primary"><?= esc($s['plan_name'] ?? 'N/A') ?></span></td>
            <td style="font-size:.82rem"><?= ucfirst($s['billing_cycle']??'-') ?></td>
            <td>
              <span class="badge-pill badge-<?= $sc[$s['subscription_status']]??'gray' ?>"><?= ucfirst($s['subscription_status']) ?></span>
              <?php if ($daysLeft!==null && $daysLeft<=7 && $daysLeft>=0 && $s['subscription_status']==='active'): ?>
              <span class="badge-pill badge-danger" style="font-size:.62rem"><?= $daysLeft ?>d</span>
              <?php endif; ?>
            </td>
            <td style="font-size:.8rem;white-space:nowrap"><?= $s['subscription_ends_at'] ? date('d M Y',strtotime($s['subscription_ends_at'])) : '—' ?></td>
            <td>
              <div style="display:flex;gap:.3rem;flex-wrap:wrap">
                <!-- Record Payment -->
                <button onclick="openPaymentModal(<?= $s['id'] ?>,'<?= esc(addslashes($s['name'])) ?>',<?= (int)$s['plan_id'] ?>,'<?= $s['billing_cycle'] ?>')"
                        class="btn btn-sm btn-success" title="Record Payment">
                  <i class="fa fa-money-bill-wave"></i>
                </button>
                <!-- Payment History -->
                <button onclick="viewPayments(<?= $s['id'] ?>,'<?= esc(addslashes($s['name'])) ?>')"
                        class="btn btn-sm btn-outline" title="Payment History" style="color:var(--info)">
                  <i class="fa fa-history"></i>
                </button>
                <!-- Change Plan -->
                <button onclick="openManageModal(<?= htmlspecialchars(json_encode($s),ENT_QUOTES) ?>)"
                        class="btn btn-sm btn-primary" title="Change Plan">
                  <i class="fa fa-gear"></i>
                </button>
                <?php if ($s['subscription_status']==='active'): ?>
                <button onclick="suspend(<?= $s['id'] ?>)" class="btn btn-sm btn-outline" style="color:var(--danger)" title="Suspend">
                  <i class="fa fa-ban"></i>
                </button>
                <?php elseif (in_array($s['subscription_status'],['suspended','expired','cancelled'])): ?>
                <button onclick="activate(<?= $s['id'] ?>)" class="btn btn-sm btn-outline" style="color:var(--success)" title="Activate">
                  <i class="fa fa-check"></i>
                </button>
                <?php endif; ?>
                <!-- Send payment link -->
                <button onclick="sendPaymentLink(<?= $s['id'] ?>,'<?= esc($s['email']) ?>')" class="btn btn-sm btn-outline" title="Generate &amp; send payment link" style="color:var(--warning)">
                  <i class="fa fa-link"></i>
                </button>
                <!-- Login as admin -->
                <form method="POST" action="<?= base_url('super/restaurants/login-as/'.$s['id']) ?>" style="margin:0">
                  <?= csrf_field() ?>
                  <button type="submit" class="btn btn-sm btn-outline" title="Login as Admin"><i class="fa fa-right-to-bracket"></i></button>
                </form>
              </div>
            </td>
          </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- ══ RECORD PAYMENT MODAL ══════════════════════════════════ -->
<div class="modal-overlay" id="paymentModal">
  <div class="modal" style="max-width:500px">
    <div class="modal-header">
      <span class="modal-title"><i class="fa fa-money-bill-wave" style="color:var(--success)"></i> Record Payment</span>
      <button class="modal-close" onclick="closeModal('paymentModal')"><i class="fa fa-times"></i></button>
    </div>
    <div class="modal-body">
      <input type="hidden" id="pmRestId">
      <div style="background:#f0fdf4;border:1px solid #86efac;border-radius:8px;padding:.75rem;margin-bottom:1rem;font-size:.85rem">
        <strong id="pmRestName"></strong>
        <div style="color:var(--text-muted);font-size:.75rem;margin-top:.2rem">Recording payment activates the subscription and sets end date automatically.</div>
      </div>
      <div class="form-row cols-2">
        <div class="form-group">
          <label class="form-label">Plan <span class="req">*</span></label>
          <select class="form-control" id="pmPlan" onchange="calcPaymentAmount()">
            <?php foreach ($plans as $p): ?>
            <option value="<?= $p['id'] ?>" data-monthly="<?= $p['price_monthly'] ?>" data-yearly="<?= $p['price_yearly'] ?>"><?= esc($p['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Billing Cycle</label>
          <select class="form-control" id="pmCycle" onchange="calcPaymentAmount()">
            <option value="monthly">Monthly</option>
            <option value="yearly">Yearly</option>
          </select>
        </div>
      </div>
      <div class="form-row cols-2">
        <div class="form-group">
          <label class="form-label">Amount (₹) <span class="req">*</span></label>
          <input type="number" class="form-control" id="pmAmount" step="0.01" min="0" placeholder="0.00" style="font-size:1.1rem;font-weight:700">
        </div>
        <div class="form-group">
          <label class="form-label">Payment Method</label>
          <select class="form-control" id="pmMethod">
            <option value="cash">Cash</option>
            <option value="upi">UPI</option>
            <option value="bank_transfer">Bank Transfer</option>
            <option value="cheque">Cheque</option>
            <option value="online">Online</option>
          </select>
        </div>
      </div>
      <div class="form-row cols-2">
        <div class="form-group">
          <label class="form-label">Reference / UTR No.</label>
          <input type="text" class="form-control" id="pmRef" placeholder="Optional">
        </div>
        <div class="form-group">
          <label class="form-label">Payment Date</label>
          <input type="date" class="form-control" id="pmDate" value="<?= date('Y-m-d') ?>">
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Notes</label>
        <input type="text" class="form-control" id="pmNotes" placeholder="Optional">
      </div>
      <!-- Subscription end date preview -->
      <div style="background:var(--bg);border-radius:8px;padding:.75rem;display:flex;justify-content:space-between;align-items:center;font-size:.84rem">
        <span><i class="fa fa-calendar-check" style="color:var(--success)"></i> Subscription valid until</span>
        <strong id="pmEndPreview" style="color:var(--success)">—</strong>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-outline" onclick="closeModal('paymentModal')">Cancel</button>
      <button class="btn btn-success btn-lg" id="pmSaveBtn" onclick="savePayment()">
        <i class="fa fa-check"></i> Record &amp; Activate
      </button>
    </div>
  </div>
</div>

<!-- ══ PAYMENT HISTORY MODAL ════════════════════════════════ -->
<div class="modal-overlay" id="historyModal">
  <div class="modal" style="max-width:640px">
    <div class="modal-header">
      <span class="modal-title" id="histTitle"><i class="fa fa-history"></i> Payment History</span>
      <button class="modal-close" onclick="closeModal('historyModal')"><i class="fa fa-times"></i></button>
    </div>
    <div class="modal-body" id="histBody" style="max-height:420px;overflow-y:auto;padding:0">
      <div style="text-align:center;padding:2rem;color:var(--text-muted)"><i class="fa fa-spinner fa-spin"></i> Loading…</div>
    </div>
  </div>
</div>

<!-- ══ CHANGE PLAN MODAL ═════════════════════════════════════ -->
<div class="modal-overlay" id="manageModal">
  <div class="modal" style="max-width:480px">
    <div class="modal-header">
      <span class="modal-title" id="manageModalTitle">Change Plan</span>
      <button class="modal-close" onclick="closeModal('manageModal')"><i class="fa fa-times"></i></button>
    </div>
    <div class="modal-body">
      <input type="hidden" id="mgRestId">
      <div class="form-group">
        <label class="form-label">Plan</label>
        <select class="form-control" id="mgPlan">
          <?php foreach ($plans as $p): ?>
          <option value="<?= $p['id'] ?>"><?= esc($p['name']) ?> — ₹<?= number_format($p['price_monthly']) ?>/mo · ₹<?= number_format($p['price_yearly']) ?>/yr</option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-row cols-2">
        <div class="form-group">
          <label class="form-label">Status</label>
          <select class="form-control" id="mgStatus">
            <option value="active">Active</option>
            <option value="trial">Trial</option>
            <option value="suspended">Suspended</option>
            <option value="expired">Expired</option>
            <option value="cancelled">Cancelled</option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Billing Cycle</label>
          <select class="form-control" id="mgCycle">
            <option value="monthly">Monthly</option>
            <option value="yearly">Yearly</option>
          </select>
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Subscription End Date</label>
        <input type="date" class="form-control" id="mgEnds">
      </div>
      <div style="background:var(--bg);border-radius:8px;padding:.625rem;font-size:.8rem;color:var(--text-muted)">
        <i class="fa fa-info-circle"></i> Use the <strong>Record Payment</strong> button (₹ icon) for proper revenue tracking.
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-outline" onclick="closeModal('manageModal')">Cancel</button>
      <button class="btn btn-primary" onclick="saveManage()"><i class="fa fa-save"></i> Save</button>
    </div>
  </div>
</div>

<script>
const CSRF_N = '<?= csrf_token() ?>';
const CSRF_V = '<?= csrf_hash() ?>';
const BASE   = '<?= base_url() ?>';
const PLANS  = <?= json_encode($plansJs) ?>;

// ── Record Payment ───────────────────────────────────────────
function openPaymentModal(id, name, planId, cycle) {
  document.getElementById('pmRestId').value = id;
  document.getElementById('pmRestName').textContent = name;
  if (planId && document.getElementById('pmPlan')) document.getElementById('pmPlan').value = planId;
  if (cycle  && document.getElementById('pmCycle')) document.getElementById('pmCycle').value = cycle;
  document.getElementById('pmRef').value   = '';
  document.getElementById('pmNotes').value = '';
  document.getElementById('pmDate').value  = new Date().toISOString().split('T')[0];
  calcPaymentAmount();
  openModal('paymentModal');
}

function calcPaymentAmount() {
  const sel   = document.getElementById('pmPlan');
  const pid   = sel ? sel.value : null;
  const cycle = document.getElementById('pmCycle').value;
  if (!pid || !PLANS[pid]) return;
  const amt = cycle === 'yearly' ? PLANS[pid].price_yearly : PLANS[pid].price_monthly;
  document.getElementById('pmAmount').value = amt;
  const now = new Date(), end = new Date(now);
  cycle === 'yearly' ? end.setFullYear(end.getFullYear()+1) : end.setMonth(end.getMonth()+1);
  document.getElementById('pmEndPreview').textContent = end.toLocaleDateString('en-IN',{day:'2-digit',month:'short',year:'numeric'});
}

function savePayment() {
  const id  = document.getElementById('pmRestId').value;
  const btn = document.getElementById('pmSaveBtn');
  btn.disabled = true; btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Saving…';
  fetch(BASE+'super/subscriptions/record-payment/'+id, {
    method:'POST',
    headers:{'Content-Type':'application/x-www-form-urlencoded','X-Requested-With':'XMLHttpRequest'},
    body: new URLSearchParams({
      [CSRF_N]:           CSRF_V,
      plan_id:            document.getElementById('pmPlan').value,
      billing_cycle:      document.getElementById('pmCycle').value,
      amount:             document.getElementById('pmAmount').value,
      payment_method:     document.getElementById('pmMethod').value,
      payment_reference:  document.getElementById('pmRef').value,
      paid_at:            document.getElementById('pmDate').value,
      notes:              document.getElementById('pmNotes').value,
    })
  }).then(r=>r.json()).then(d=>{
    if (d.success) { showToast(d.message,'success'); closeModal('paymentModal'); setTimeout(()=>location.reload(),1000); }
    else showToast(d.message||'Failed','error');
  }).catch(e=>showToast('Error: '+e.message,'error'))
  .finally(()=>{ btn.disabled=false; btn.innerHTML='<i class="fa fa-check"></i> Record &amp; Activate'; });
}

// ── Payment History ──────────────────────────────────────────
function viewPayments(id, name) {
  document.getElementById('histTitle').innerHTML = '<i class="fa fa-history"></i> '+name+' — Payments';
  document.getElementById('histBody').innerHTML  = '<div style="text-align:center;padding:2rem;color:var(--text-muted)"><i class="fa fa-spinner fa-spin"></i> Loading…</div>';
  openModal('historyModal');
  fetch(BASE+'super/subscriptions/payments/'+id,{headers:{'X-Requested-With':'XMLHttpRequest'}})
    .then(r=>r.json()).then(d=>{
      if (!d.data || !d.data.length) {
        document.getElementById('histBody').innerHTML = '<div style="text-align:center;padding:3rem;color:var(--text-muted)"><i class="fa fa-inbox fa-2x" style="margin-bottom:.75rem;display:block"></i>No payments recorded yet.</div>';
        return;
      }
      const totalRevenue = d.data.reduce((s,p)=>s+parseFloat(p.amount||0),0);
      let html = `<div style="padding:.75rem 1.25rem;background:#f0fdf4;border-bottom:1px solid #bbf7d0;font-size:.82rem;display:flex;justify-content:space-between">
        <span>${d.data.length} payment${d.data.length>1?'s':''}</span>
        <strong style="color:var(--success)">Total: ₹${totalRevenue.toLocaleString('en-IN')}</strong>
      </div>
      <table class="table" style="font-size:.8rem"><thead><tr>
        <th>Date</th><th>Plan</th><th>Cycle</th><th>Method</th><th>Ref</th><th>Amount</th><th>Valid Until</th>
      </tr></thead><tbody>`;
      d.data.forEach(p=>{
        const dateStr = (p.paid_at||p.created_at||'').substring(0,10);
        html += `<tr>
          <td>${dateStr}</td>
          <td>${p.plan_name||'—'}</td>
          <td style="text-transform:capitalize">${p.billing_cycle}</td>
          <td style="text-transform:capitalize">${(p.payment_method||'—').replace('_',' ')}</td>
          <td style="color:var(--text-muted);font-size:.72rem">${p.payment_reference||'—'}</td>
          <td style="font-weight:700;color:var(--success)">₹${parseFloat(p.amount).toLocaleString('en-IN')}</td>
          <td style="font-size:.75rem">${p.period_end||'—'}</td>
        </tr>`;
      });
      html += '</tbody></table>';
      document.getElementById('histBody').innerHTML = html;
    }).catch(()=>{
      document.getElementById('histBody').innerHTML = '<div style="color:var(--danger);padding:1.5rem;text-align:center"><i class="fa fa-triangle-exclamation"></i> Could not load payment history.</div>';
    });
}

// ── Send Payment Link ────────────────────────────────────────
function sendPaymentLink(id, email) {
  if (!confirm('Generate a 48-hour payment link for '+email+'?')) return;
  fetch(BASE+'super/subscriptions/generate-link/'+id, {
    method:'POST',
    headers:{'Content-Type':'application/x-www-form-urlencoded','X-Requested-With':'XMLHttpRequest'},
    body: new URLSearchParams({[CSRF_N]:CSRF_V})
  }).then(r=>r.json()).then(d=>{
    if (d.success) {
      navigator.clipboard?.writeText(d.link).catch(()=>{});
      showToast('Payment link generated & copied: '+d.link,'success');
    } else showToast('Failed to generate link','error');
  });
}

// ── Change Plan Modal ────────────────────────────────────────
function openManageModal(s) {
  document.getElementById('mgRestId').value = s.id;
  document.getElementById('manageModalTitle').textContent = 'Change Plan — '+s.name;
  document.getElementById('mgPlan').value   = s.plan_id;
  document.getElementById('mgStatus').value = s.subscription_status;
  document.getElementById('mgCycle').value  = s.billing_cycle||'monthly';
  document.getElementById('mgEnds').value   = s.subscription_ends_at ? s.subscription_ends_at.substring(0,10) : '';
  openModal('manageModal');
}

function saveManage() {
  const id = document.getElementById('mgRestId').value;
  fetch(BASE+'super/subscriptions/change-plan/'+id, {
    method:'POST',
    headers:{'Content-Type':'application/x-www-form-urlencoded','X-Requested-With':'XMLHttpRequest'},
    body: new URLSearchParams({
      [CSRF_N]:CSRF_V,
      plan_id:              document.getElementById('mgPlan').value,
      subscription_status:  document.getElementById('mgStatus').value,
      billing_cycle:        document.getElementById('mgCycle').value,
      subscription_ends_at: document.getElementById('mgEnds').value,
    })
  }).then(r=>r.json()).then(d=>{
    if(d.success){showToast('Updated','success');closeModal('manageModal');setTimeout(()=>location.reload(),800);}
    else showToast('Failed','error');
  });
}

function suspend(id) {
  if (!confirm('Suspend? Restaurant will lose access.')) return;
  fetch(BASE+'super/subscriptions/suspend/'+id,{
    method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded','X-Requested-With':'XMLHttpRequest'},
    body:new URLSearchParams({[CSRF_N]:CSRF_V})
  }).then(r=>r.json()).then(d=>{if(d.success){showToast('Suspended','success');setTimeout(()=>location.reload(),700);}});
}

function activate(id) {
  fetch(BASE+'super/subscriptions/activate/'+id,{
    method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded','X-Requested-With':'XMLHttpRequest'},
    body:new URLSearchParams({[CSRF_N]:CSRF_V})
  }).then(r=>r.json()).then(d=>{if(d.success){showToast('Activated','success');setTimeout(()=>location.reload(),700);}});
}

// Auto-open payment modal when redirected after restaurant creation
(function(){
  const p = new URLSearchParams(window.location.search);
  const rid = p.get('new_restaurant');
  if (!rid) return;
  // Find the record in the table data
  const allBtns = document.querySelectorAll('[onclick]');
  allBtns.forEach(btn => {
    const fn = btn.getAttribute('onclick') || '';
    if (fn.startsWith('openPaymentModal('+rid+',')) {
      setTimeout(() => btn.click(), 600);
    }
  });
})();

calcPaymentAmount();
</script>
<?php $this->endSection(); ?>
