<?php $this->extend('layouts/main'); $this->section('content'); ?>
<div style="padding:0 1rem">

  <!-- Filter + Add -->
  <div class="card" style="margin-bottom:1rem">
    <div class="card-body" style="padding:.75rem;display:flex;gap:.5rem;flex-wrap:wrap;align-items:flex-end">
      <form method="GET" style="display:flex;gap:.5rem;flex:1;flex-wrap:wrap;align-items:flex-end">
        <div class="form-group" style="margin:0;flex:1;min-width:130px">
          <label class="form-label">From</label>
          <input type="date" class="form-control" name="from" value="<?= $from ?>">
        </div>
        <div class="form-group" style="margin:0;flex:1;min-width:130px">
          <label class="form-label">To</label>
          <input type="date" class="form-control" name="to" value="<?= $to ?>">
        </div>
        <button class="btn btn-outline"><i class="fa fa-search"></i> Filter</button>
      </form>
      <button class="btn btn-primary" onclick="openModal('addExpenseModal')"><i class="fa fa-plus"></i> Add Expense</button>
    </div>
  </div>

  <!-- Summary -->
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;margin-bottom:1rem">
    <div class="stat-card red">
      <div class="stat-icon red"><i class="fa fa-wallet"></i></div>
      <div>
        <div class="stat-value">₹<?= number_format($total,2) ?></div>
        <div class="stat-label">Total Expenses</div>
      </div>
    </div>
    <div class="stat-card orange">
      <div class="stat-icon orange"><i class="fa fa-receipt"></i></div>
      <div>
        <div class="stat-value"><?= count($expenses) ?></div>
        <div class="stat-label">Transactions</div>
      </div>
    </div>
  </div>

  <!-- Expense List -->
  <div class="card">
    <div class="card-header">
      <span class="card-title">Expenses</span>
      <a href="<?= base_url('admin/reports/expenses') ?>" class="btn btn-sm btn-outline"><i class="fa fa-chart-bar"></i> Report</a>
    </div>
    <div class="table-wrap">
      <table class="table">
        <thead>
          <tr><th>Date</th><th>Title</th><th>Category</th><th>Method</th><th>Amount</th><th>Status</th><th></th></tr>
        </thead>
        <tbody>
          <?php if (empty($expenses)): ?>
          <tr><td colspan="7">
            <div class="empty-state" style="padding:2.5rem">
              <i class="fa fa-wallet"></i><p>No expenses for this period</p>
            </div>
          </td></tr>
          <?php else: foreach ($expenses as $e): ?>
          <tr>
            <td style="font-size:.8rem;white-space:nowrap"><?= date('d M Y', strtotime($e['expense_date'])) ?></td>
            <td>
              <div style="font-weight:600;font-size:.875rem"><?= esc($e['title']) ?></div>
              <?php if ($e['description']): ?>
              <div style="font-size:.72rem;color:var(--text-muted)"><?= esc(substr($e['description'],0,50)) ?></div>
              <?php endif; ?>
              <div style="font-size:.72rem;color:var(--text-muted)"><?= esc($e['staff_name'] ?? '') ?></div>
            </td>
            <td><span class="badge-pill badge-gray"><?= esc($e['category_name'] ?? 'General') ?></span></td>
            <td style="font-size:.82rem"><?= ucfirst($e['payment_method'] ?? 'cash') ?></td>
            <td><strong style="color:var(--danger)">₹<?= number_format($e['amount'],2) ?></strong></td>
            <td>
              <?php if ($e['is_approved']): ?>
                <span class="badge-pill badge-success">Approved</span>
              <?php else: ?>
                <button onclick="approveExpense(<?= $e['id'] ?>, this)" class="badge-pill badge-warning" style="border:none;cursor:pointer">Pending</button>
              <?php endif; ?>
            </td>
            <td>
              <?php if ($e['receipt_image']): ?>
              <a href="<?= base_url('public/images/uploads/'.$e['receipt_image']) ?>" target="_blank" class="btn btn-sm btn-outline"><i class="fa fa-image"></i></a>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Add Expense Modal -->
<div class="modal-overlay" id="addExpenseModal">
  <div class="modal">
    <div class="modal-header">
      <span class="modal-title"><i class="fa fa-wallet" style="color:var(--primary)"></i> Add Expense</span>
      <button class="modal-close" onclick="closeModal('addExpenseModal')"><i class="fa fa-times"></i></button>
    </div>
    <form action="<?= base_url('admin/expenses/store') ?>" method="POST" enctype="multipart/form-data">
      <?= csrf_field() ?>
      <div class="modal-body">
        <div class="form-group">
          <label class="form-label">Title <span class="req">*</span></label>
          <input type="text" class="form-control" name="title" placeholder="e.g. Gas cylinder, Cleaning supplies" required>
        </div>
        <div class="form-row cols-2">
          <div class="form-group">
            <label class="form-label">Category</label>
            <select class="form-control" name="category_id">
              <option value="">Select Category</option>
              <?php foreach ($categories as $cat): ?>
              <option value="<?= $cat['id'] ?>"><?= esc($cat['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Or New Category</label>
            <input type="text" class="form-control" name="new_category" placeholder="Type new...">
          </div>
        </div>
        <div class="form-row cols-2">
          <div class="form-group">
            <label class="form-label">Amount (₹) <span class="req">*</span></label>
            <input type="number" class="form-control" name="amount" step="0.01" min="0" required>
          </div>
          <div class="form-group">
            <label class="form-label">Date <span class="req">*</span></label>
            <input type="date" class="form-control" name="expense_date" value="<?= date('Y-m-d') ?>" required>
          </div>
        </div>
        <div class="form-row cols-2">
          <div class="form-group">
            <label class="form-label">Payment Method</label>
            <select class="form-control" name="payment_method">
              <option value="cash">Cash</option>
              <option value="card">Card</option>
              <option value="upi">UPI</option>
              <option value="bank_transfer">Bank Transfer</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Reference / Bill No.</label>
            <input type="text" class="form-control" name="reference" placeholder="Optional">
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Description</label>
          <textarea class="form-control" name="description" rows="2" placeholder="Additional notes..."></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="closeModal('addExpenseModal')">Cancel</button>
        <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save Expense</button>
      </div>
    </form>
  </div>
</div>

<script>
function approveExpense(id, btn) {
  fetch('<?= base_url('admin/expenses/approve/') ?>' + id, {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: '<?= csrf_token() ?>=<?= csrf_hash() ?>'
  }).then(r => r.json()).then(d => {
    if (d.success) {
      btn.outerHTML = '<span class="badge-pill badge-success">Approved</span>';
      showToast('Expense approved', 'success');
    }
  });
}
</script>
<?php $this->endSection(); ?>
