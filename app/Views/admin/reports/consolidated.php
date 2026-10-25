<?php $this->extend('layouts/main'); $this->section('content'); ?>
<div style="padding:0 1rem">
  <div class="card" style="margin-bottom:1rem">
    <div class="card-body" style="padding:.75rem;display:flex;gap:.5rem;flex-wrap:wrap;align-items:flex-end">
      <form method="GET" style="display:flex;gap:.5rem;align-items:flex-end">
        <div class="form-group" style="margin:0">
          <label class="form-label">From</label><input type="date" class="form-control" name="from" value="<?= esc($from) ?>">
        </div>
        <div class="form-group" style="margin:0">
          <label class="form-label">To</label><input type="date" class="form-control" name="to" value="<?= esc($to) ?>">
        </div>
        <button class="btn btn-primary"><i class="fa fa-search"></i> Load</button>
      </form>
      <a class="btn btn-outline" href="<?= base_url('admin/reports/export/consolidated?from=' . $from . '&to=' . $to) ?>"><i class="fa fa-download"></i> Export CSV</a>
      <a class="btn btn-outline" href="<?= base_url('admin/reports/print/consolidated?from=' . $from . '&to=' . $to) ?>" target="_blank"><i class="fa fa-print"></i> PDF (print)</a>
    </div>
  </div>

  <div class="card" style="margin-bottom:1rem">
    <div class="card-body">
      <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:.75rem">
        <?php
        $cells = [
          'Branches'  => number_format((float)$grand['branches']),
          'Orders'    => number_format((float)$grand['orders']),
          'Revenue'   => '₹' . number_format((float)$grand['revenue'], 2),
          'Discounts' => '₹' . number_format((float)$grand['discount'], 2),
          'Tax'       => '₹' . number_format((float)$grand['tax'], 2),
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

  <div class="card">
    <div class="card-header" style="padding:.75rem 1rem;font-weight:800">Branch performance <?= esc($from) ?> → <?= esc($to) ?></div>
    <div class="card-body" style="padding:0">
      <table style="width:100%;border-collapse:collapse;font-size:.82rem">
        <thead><tr style="background:#F8FAFC;text-align:left">
          <th style="padding:.5rem .75rem">Branch</th>
          <th style="padding:.5rem .75rem">Orders</th>
          <th style="padding:.5rem .75rem">Revenue</th>
          <th style="padding:.5rem .75rem">Discounts</th>
          <th style="padding:.5rem .75rem">Tax</th>
          <th style="padding:.5rem .75rem">Avg Order</th>
        </tr></thead>
        <tbody>
          <?php foreach ($rows as $r): ?>
          <tr style="border-top:1px solid #F1F5F9">
            <td style="padding:.5rem .75rem;font-weight:700"><?= esc($r['branch'] ?? 'Unassigned') ?></td>
            <td style="padding:.5rem .75rem"><?= number_format((float)$r['orders']) ?></td>
            <td style="padding:.5rem .75rem;font-weight:800">₹<?= number_format((float)$r['revenue'], 2) ?></td>
            <td style="padding:.5rem .75rem">₹<?= number_format((float)$r['discount'], 2) ?></td>
            <td style="padding:.5rem .75rem">₹<?= number_format((float)$r['tax'], 2) ?></td>
            <td style="padding:.5rem .75rem">₹<?= number_format((float)$r['avg_order'], 2) ?></td>
          </tr>
          <?php endforeach; ?>
          <?php if (empty($rows)): ?>
          <tr><td colspan="6" style="padding:1.25rem;text-align:center;color:#94A3B8">No orders in this period</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php $this->endSection(); ?>
