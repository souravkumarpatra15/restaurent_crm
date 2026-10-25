<?php $this->extend('layouts/main'); $this->section('content'); ?>
<div style="padding:0 1rem">
  <div class="card" style="margin-bottom:1rem">
    <div class="card-body" style="padding:.75rem;display:flex;gap:.5rem;flex-wrap:wrap;align-items:flex-end">
      <form method="GET" style="display:flex;gap:.5rem;align-items:flex-end">
        <div class="form-group" style="margin:0">
          <label class="form-label">Billing month</label>
          <input type="month" class="form-control" name="month" value="<?= esc($month) ?>">
        </div>
        <button class="btn btn-primary"><i class="fa fa-search"></i> Load</button>
      </form>
      <a class="btn btn-outline" href="<?= base_url('admin/reports/export/gstr1?month=' . $month) ?>"><i class="fa fa-download"></i> Export CSV</a>
      <a class="btn btn-outline" href="<?= base_url('admin/reports/print/gstr1?month=' . $month) ?>" target="_blank"><i class="fa fa-print"></i> PDF (print)</a>
    </div>
  </div>

  <!-- Totals -->
  <div class="card" style="margin-bottom:1rem">
    <div class="card-body">
      <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:.75rem">
        <?php
        $cells = [
          'Invoices'       => number_format((float)$totals['invoices']),
          'Taxable Value'  => '₹' . number_format((float)$totals['gross'] - (float)$totals['discount'] - (float)$totals['tax'], 2),
          'CGST'           => '₹' . number_format((float)$totals['cgst'], 2),
          'SGST'           => '₹' . number_format((float)$totals['sgst'], 2),
          'IGST'           => '₹' . number_format((float)$totals['igst'], 2),
          'Cess'           => '₹' . number_format((float)$totals['cess'], 2),
          'Total Tax'      => '₹' . number_format((float)$totals['tax'], 2),
          'Gross'          => '₹' . number_format((float)$totals['gross'], 2),
        ];
        foreach ($cells as $l => $v): ?>
        <div style="padding:.6rem .75rem;background:#F8FAFC;border-radius:10px">
          <div style="font-size:.68rem;font-weight:700;color:#64748B;text-transform:uppercase"><?= $l ?></div>
          <div style="font-weight:900;font-size:1rem;color:#0F172A"><?= $v ?></div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <!-- Rate-wise slabs -->
  <div class="card" style="margin-bottom:1rem">
    <div class="card-header" style="padding:.75rem 1rem;font-weight:800">Rate-wise liability</div>
    <div class="card-body" style="padding:0">
      <table style="width:100%;border-collapse:collapse;font-size:.82rem">
        <thead><tr style="background:#F8FAFC;text-align:left">
          <th style="padding:.5rem .75rem">Rate</th>
          <th style="padding:.5rem .75rem">Taxable Value</th>
          <th style="padding:.5rem .75rem">Tax</th>
        </tr></thead>
        <tbody>
          <?php foreach ($slabs as $s): ?>
          <tr style="border-top:1px solid #F1F5F9">
            <td style="padding:.5rem .75rem;font-weight:700"><?= rtrim(rtrim(number_format((float)$s['rate'],2),'0'),'.') ?>%</td>
            <td style="padding:.5rem .75rem">₹<?= number_format((float)$s['taxable'], 2) ?></td>
            <td style="padding:.5rem .75rem">₹<?= number_format((float)$s['tax'], 2) ?></td>
          </tr>
          <?php endforeach; ?>
          <?php if (empty($slabs)): ?>
          <tr><td colspan="3" style="padding:1.25rem;text-align:center;color:#94A3B8">No billed orders this month</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Invoice-wise -->
  <div class="card">
    <div class="card-header" style="padding:.75rem 1rem;font-weight:800">Invoice-wise (first 100)</div>
    <div class="card-body" style="padding:0">
      <table style="width:100%;border-collapse:collapse;font-size:.78rem">
        <thead><tr style="background:#F8FAFC;text-align:left">
          <th style="padding:.5rem .75rem">Invoice</th>
          <th style="padding:.5rem .75rem">Date</th>
          <th style="padding:.5rem .75rem">Guest</th>
          <th style="padding:.5rem .75rem">Tax</th>
          <th style="padding:.5rem .75rem">Total</th>
        </tr></thead>
        <tbody>
          <?php foreach ($invoices as $i): ?>
          <tr style="border-top:1px solid #F1F5F9">
            <td style="padding:.45rem .75rem;font-weight:700"><?= esc($i['order_number']) ?></td>
            <td style="padding:.45rem .75rem"><?= date('d M', strtotime($i['created_at'])) ?></td>
            <td style="padding:.45rem .75rem"><?= esc($i['customer_name'] ?: 'Walk-in') ?></td>
            <td style="padding:.45rem .75rem">₹<?= number_format((float)$i['tax_amount'], 2) ?></td>
            <td style="padding:.45rem .75rem;font-weight:800">₹<?= number_format((float)$i['total_amount'], 2) ?></td>
          </tr>
          <?php endforeach; ?>
          <?php if (empty($invoices)): ?>
          <tr><td colspan="5" style="padding:1.25rem;text-align:center;color:#94A3B8">No invoices this month</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php $this->endSection(); ?>
