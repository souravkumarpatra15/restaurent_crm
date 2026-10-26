<?php $this->extend('layouts/main'); $this->section('content'); ?>
<div style="padding:0 1rem">
  <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:.5rem;margin-bottom:1rem">
    <div style="font-size:.85rem;color:var(--text-muted)">
      Every UPI QR attempt for <b><?= esc($restaurantName ?? '') ?></b> — including expired and rejected ones.
      Nothing is ever deleted: abandoned QRs stay here for reconciliation.
    </div>
    <a href="<?= base_url('pos/upi/pending') ?>" class="btn btn-outline btn-sm"><i class="fa fa-clock"></i> Pending Payments</a>
  </div>

  <!-- Attempts -->
  <div class="card" style="margin-bottom:1rem">
    <div class="card-header"><span class="card-title"><i class="fa fa-qrcode" style="color:var(--primary)"></i> UPI QR attempts</span></div>
    <div class="card-body" style="padding:0">
      <?php if (empty($rows)): ?>
        <div class="empty-state" style="padding:2rem"><p>No UPI QR payment attempts yet</p></div>
      <?php else: ?>
      <table class="table" style="width:100%;font-size:.8rem">
        <thead><tr>
          <th>Ref</th><th>Order</th><th style="text-align:right">Amount</th><th>State</th>
          <th>Txn reference</th><th>Verified by</th><th>Created</th><th>Verified at</th><th>Note</th>
        </tr></thead>
        <tbody>
        <?php foreach ($rows as $r): ?>
          <tr>
            <td style="font-family:'JetBrains Mono',monospace"><?= esc($r['attempt_ref']) ?></td>
            <td><?= esc($r['order_number']) ?></td>
            <td style="text-align:right;font-weight:700">₹<?= number_format((float)$r['amount'], 2) ?></td>
            <td><span class="badge-pill badge-<?= ['paid' => 'success', 'failed' => 'danger', 'rejected' => 'danger', 'expired' => 'warning', 'pending' => 'primary'][$r['verify_status']] ?? 'gray' ?>"><?= esc(strtoupper($r['verify_status'])) ?></span></td>
            <td style="font-family:'JetBrains Mono',monospace"><?= esc($r['verify_txn_ref'] ?? '—') ?></td>
            <td><?= esc($r['verified_by_name'] ?? '—') ?></td>
            <td style="white-space:nowrap"><?= date('d M h:i A', strtotime($r['created_at'])) ?></td>
            <td style="white-space:nowrap"><?= $r['verified_at'] ? date('d M h:i A', strtotime($r['verified_at'])) : '—' ?></td>
            <td style="color:var(--text-muted);max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="<?= esc($r['verify_note'] ?? '') ?>"><?= esc($r['verify_note'] ?? '—') ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
      <?php endif; ?>
    </div>
  </div>

  <!-- Audit trail -->
  <div class="card">
    <div class="card-header"><span class="card-title"><i class="fa fa-shield-alt"></i> Audit trail (who did what, when)</span></div>
    <div class="card-body" style="padding:0">
      <?php if (empty($audit)): ?>
        <div class="empty-state" style="padding:2rem"><p>Nothing recorded yet</p></div>
      <?php else: ?>
      <table class="table" style="width:100%;font-size:.78rem">
        <thead><tr><th>When</th><th>Event</th><th>Ref</th><th>From → To</th><th>By</th><th>Txn ref</th><th>Note</th></tr></thead>
        <tbody>
        <?php foreach ($audit as $a): ?>
          <tr>
            <td style="white-space:nowrap"><?= date('d M h:i:s A', strtotime($a['created_at'])) ?></td>
            <td><span class="badge-pill badge-<?= $a['event_type'] === 'verification' ? 'primary' : ($a['event_type'] === 'claim' ? 'warning' : 'gray') ?>"><?= esc($a['event_type']) ?></span></td>
            <td style="font-family:'JetBrains Mono',monospace"><?= esc($a['attempt_ref'] ?? '(settings)') ?></td>
            <td><?= esc($a['prev_state'] ?? '—') ?> → <?= esc($a['new_state'] ?? '—') ?></td>
            <td><?= esc($a['user_name'] ?? 'Customer') ?></td>
            <td style="font-family:'JetBrains Mono',monospace"><?= esc($a['txn_ref'] ?? '—') ?></td>
            <td style="color:var(--text-muted);max-width:260px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="<?= esc($a['note'] ?? '') ?>"><?= esc($a['note'] ?? '') ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php $this->endSection(); ?>
