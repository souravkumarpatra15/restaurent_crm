<?php $this->extend('layouts/main'); $this->section('content'); ?>

<style>
.w-card { background:#fff;border:1px solid var(--border);border-radius:14px;padding:1rem;margin-bottom:.8rem }
.w-txn { display:grid;grid-template-columns:110px 1fr 110px 110px;gap:.4rem;align-items:center;padding:.55rem .4rem;border-bottom:1px solid #F1F5F9;font-size:.78rem }
.w-txn:last-child { border-bottom:none }
@media (max-width:760px){ .w-txn { grid-template-columns:90px 1fr 80px 80px } .w-txn .hide-sm { display:none } }
.w-in { color:#047857;font-weight:800 }
.w-out { color:#B91C1C;font-weight:800 }
</style>

<div style="display:flex;align-items:center;gap:.7rem;flex-wrap:wrap;margin-bottom:1rem">
  <a href="<?= base_url('admin/riders') ?>" class="btn btn-sm btn-outline"><i class="fa fa-arrow-left"></i> Riders</a>
  <div>
    <h1 style="font-size:1.3rem;font-weight:900;margin:0">👛 <?= esc($rider['name']) ?> — Wallet</h1>
    <div style="font-size:.76rem;color:var(--text-muted)"><?= esc($rider['email']) ?><?= $rider['phone'] ? ' · ' . esc($rider['phone']) : '' ?>
      · <?= $rider['commission_type'] === 'percent' ? (float)$rider['commission_value'] . '% per order' : '₹' . number_format((float)$rider['commission_value'], 2) . ' per order' ?>
      <?= $rider['vehicle_no'] ? ' · 🛵 ' . esc($rider['vehicle_no']) : '' ?></div>
  </div>
</div>

<div style="display:grid;grid-template-columns:repeat(2,1fr);gap:.6rem;margin-bottom:1rem">
  <div class="w-card" style="text-align:center">
    <b style="font-size:1.7rem;color:#D97706">₹<?= number_format((float)$wallet['cod_in_hand'], 2) ?></b>
    <div style="font-size:.7rem;color:var(--text-muted);font-weight:800">CASH IN RIDER'S HAND (COD)</div>
    <div style="font-size:.68rem;color:var(--text-muted)">to be deposited back to the restaurant</div>
  </div>
  <div class="w-card" style="text-align:center">
    <b style="font-size:1.7rem;color:#16A34A">₹<?= number_format((float)$wallet['payable'], 2) ?></b>
    <div style="font-size:.7rem;color:var(--text-muted);font-weight:800">COMMISSION PAYABLE</div>
    <div style="font-size:.68rem;color:var(--text-muted)">owed to the rider</div>
  </div>
</div>

<!-- Money actions -->
<div class="w-card">
  <div style="font-weight:900;margin-bottom:.6rem">💰 Money actions</div>
  <form method="post" action="<?= base_url('admin/riders/wallet-action/' . $rider['user_id']) ?>" style="display:grid;grid-template-columns:180px 130px 1fr auto;gap:.5rem;align-items:end;flex-wrap:wrap">
    <?= csrf_field() ?>
    <div>
      <div class="form-label" style="font-size:.68rem;font-weight:800;color:#94A3B8">ACTION</div>
      <select name="type" class="form-control">
        <option value="payout">🏆 Pay commission to rider</option>
        <option value="deposit">💵 Record COD cash deposit</option>
        <option value="adjust_earn">± Adjust earnings</option>
        <option value="adjust_cod">± Adjust cash in hand</option>
      </select>
    </div>
    <div>
      <div class="form-label" style="font-size:.68rem;font-weight:800;color:#94A3B8">AMOUNT ₹</div>
      <input type="number" name="amount" step="0.01" min="0" class="form-control" placeholder="0.00" required>
    </div>
    <div>
      <div class="form-label" style="font-size:.68rem;font-weight:800;color:#94A3B8">NOTE</div>
      <input type="text" name="notes" class="form-control" placeholder="reason / receipt no. (optional)">
    </div>
    <button type="submit" class="btn btn-primary"><i class="fa fa-check"></i> Record</button>
  </form>
  <div style="font-size:.68rem;color:var(--text-muted);margin-top:.5rem">
    Payout debits the rider's payable · Deposit clears collected cash (rider hands it to you) · Adjustments are manual corrections
  </div>
</div>

<!-- Ledger -->
<div class="w-card" style="padding:.4rem .8rem">
  <div style="font-weight:900;margin:.5rem 0 .3rem">📜 Ledger (latest first)</div>
  <?php if (!$txns): ?>
    <div style="text-align:center;color:var(--text-muted);padding:1.5rem;font-size:.8rem">No transactions yet — deliver an order to start the money flowing.</div>
  <?php endif; ?>
  <?php foreach ($txns as $t): ?>
  <div class="w-txn">
    <div>
      <span class="r-badge" style="background:#F1F5F9;color:#475569"><?= [
          'cod_collect' => '💵 COD collect',
          'cod_deposit' => '🏦 COD deposit',
          'commission'  => '🏆 Commission',
          'payout'      => '💳 Payout',
          'adjust'      => '⚖️ Adjust',
      ][$t['txn_type']] ?></span>
    </div>
    <div>
      <div style="font-weight:700"><?= esc($t['notes'] ?: ($t['order_number'] ?: ($t['ghost'] ?: 'entry'))) ?></div>
      <div class="hide-sm" style="font-size:.66rem;color:var(--text-muted)">
        <?= date('d M h:i A', strtotime($t['created_at'])) ?> · <?= $t['creator_name'] ? esc($t['creator_name']) : 'system' ?>
      </div>
    </div>
    <div style="text-align:right">
      <?php if ((float)$t['cod_delta'] != 0.0): ?>
        <div class="<?= $t['cod_delta'] > 0 ? 'w-out' : 'w-in' ?>">₹<?= number_format((float)$t['cod_delta'], 2) ?></div>
        <span style="font-size:.66rem;color:var(--text-muted)"><?= $t['cod_delta'] > 0 ? 'rider holding' : 'handed in' ?></span>
      <?php endif; ?>
    </div>
    <div style="text-align:right">
      <?php if ((float)$t['earn_delta'] != 0.0): ?>
        <div class="<?= $t['earn_delta'] > 0 ? 'w-in' : 'w-out' ?>">₹<?= number_format((float)$t['earn_delta'], 2) ?></div>
        <span style="font-size:.66rem;color:var(--text-muted)"><?= $t['earn_delta'] > 0 ? 'earned' : 'paid out' ?></span>
      <?php endif; ?>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<?php $this->endSection(); ?>
