<?php $this->extend('layouts/main'); $this->section('content'); ?>
<div style="padding:0 1rem">
  <div class="card" style="margin-bottom:1rem">
    <div class="card-body" style="padding:.75rem">
      <form method="GET" style="display:flex;gap:.5rem;align-items:flex-end;flex-wrap:wrap">
        <div class="form-group" style="margin:0"><label class="form-label">From</label><input type="date" class="form-control" name="from" value="<?= $from ?>"></div>
        <div class="form-group" style="margin:0"><label class="form-label">To</label><input type="date" class="form-control" name="to" value="<?= $to ?>"></div>
        <button class="btn btn-primary"><i class="fa fa-search"></i> Filter</button>
      </form>
    </div>
  </div>
  <div class="card" style="margin-bottom:1rem">
    <div class="card-header"><span class="card-title">Total Expenses</span><span style="font-weight:800;font-size:1rem;color:var(--danger)">₹<?= number_format($total,2) ?></span></div>
    <div class="table-wrap">
      <table class="table">
        <thead><tr><th>Date</th><th>Title</th><th>Category</th><th>Staff</th><th>Amount</th><th>Status</th></tr></thead>
        <tbody>
          <?php if (empty($expenses)): ?>
          <tr><td colspan="6"><div class="empty-state"><i class="fa fa-wallet"></i><p>No expenses</p></div></td></tr>
          <?php else: foreach ($expenses as $e): ?>
          <tr>
            <td style="font-size:.8rem"><?= date('d M Y',strtotime($e['expense_date'])) ?></td>
            <td><div style="font-weight:600"><?= esc($e['title']) ?></div><div style="font-size:.72rem;color:var(--text-muted)"><?= esc($e['description'] ?? '') ?></div></td>
            <td><?= esc($e['category_name'] ?? '-') ?></td>
            <td style="font-size:.8rem"><?= esc($e['staff_name'] ?? '-') ?></td>
            <td><strong>₹<?= number_format($e['amount'],2) ?></strong></td>
            <td><span class="badge-pill badge-<?= $e['is_approved']?'success':'warning' ?>"><?= $e['is_approved']?'Approved':'Pending' ?></span></td>
          </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php $this->endSection(); ?>
