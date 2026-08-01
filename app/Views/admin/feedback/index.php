<?php $this->extend('layouts/main'); $this->section('content'); ?>
<div style="padding:0 1rem">

  <!-- Rating Overview -->
  <?php $avg = $avgRatings; $total = (int)($avg['total'] ?? 0); ?>
  <div style="background:linear-gradient(135deg,#667eea,#764ba2);border-radius:var(--radius);padding:1.25rem;margin-bottom:1rem;color:#fff">
    <div style="display:flex;align-items:center;gap:1.5rem;flex-wrap:wrap">
      <div style="text-align:center">
        <div style="font-size:3.5rem;font-weight:900;line-height:1"><?= number_format($avg['overall']??0,1) ?></div>
        <div style="font-size:1.2rem;letter-spacing:.1em">
          <?php for($i=1;$i<=5;$i++) echo ($avg['overall']??0) >= $i ? '★' : '☆'; ?>
        </div>
        <div style="font-size:.78rem;opacity:.8;margin-top:.2rem"><?= $total ?> reviews</div>
      </div>
      <div style="flex:1;min-width:200px">
        <?php foreach (['food'=>'🍽 Food','service'=>'👨‍🍳 Service','ambience'=>'✨ Ambience'] as $k=>$label): ?>
        <div style="margin-bottom:.5rem">
          <div style="display:flex;justify-content:space-between;font-size:.8rem;margin-bottom:.2rem">
            <span><?= $label ?></span><span style="font-weight:700"><?= number_format($avg[$k]??0,1) ?>/5</span>
          </div>
          <div style="height:6px;background:rgba(255,255,255,.25);border-radius:3px">
            <div style="height:100%;width:<?= ($avg[$k]??0)/5*100 ?>%;background:#fff;border-radius:3px"></div>
          </div>
        </div>
        <?php endforeach; ?>
        <div style="font-size:.78rem;opacity:.85;margin-top:.5rem">
          🔄 <?= $total > 0 ? round(($avg['would_return']??0)/$total*100) : 0 ?>% would visit again
        </div>
      </div>
    </div>
  </div>

  <!-- Low Rating Alerts -->
  <?php if (!empty($lowRatings)): ?>
  <div class="alert alert-error" style="margin-bottom:1rem">
    <i class="fa fa-triangle-exclamation"></i>
    <strong><?= count($lowRatings) ?> low rating<?= count($lowRatings)>1?'s':'' ?></strong> (≤2 stars) in last 7 days — needs attention!
  </div>
  <?php foreach ($lowRatings as $lr): ?>
  <div style="background:#FFF5F5;border:1px solid #FEB2B2;border-radius:var(--radius);padding:.875rem;margin-bottom:.5rem">
    <div style="display:flex;justify-content:space-between;align-items:flex-start">
      <div>
        <div style="font-weight:700;font-size:.875rem"><?= esc($lr['customer_name'] ?? 'Anonymous') ?></div>
        <div style="font-size:.72rem;color:var(--text-muted)"><?= esc($lr['order_number']) ?> · <?= date('d M h:i A',strtotime($lr['submitted_at'])) ?></div>
        <?php if ($lr['comment']): ?>
        <div style="font-size:.82rem;margin-top:.35rem;color:var(--text)"><?= esc($lr['comment']) ?></div>
        <?php endif; ?>
      </div>
      <div style="font-size:1.25rem;font-weight:900;color:var(--danger)">⭐ <?= $lr['overall_rating'] ?>/5</div>
    </div>
    <?php if ($lr['customer_phone']): ?>
    <a href="tel:<?= esc($lr['customer_phone']) ?>" style="display:inline-flex;align-items:center;gap:.35rem;margin-top:.5rem;font-size:.78rem;font-weight:700;color:var(--primary)">
      <i class="fa fa-phone"></i> Call to resolve: <?= esc($lr['customer_phone']) ?>
    </a>
    <?php endif; ?>
  </div>
  <?php endforeach; ?>
  <?php endif; ?>

  <!-- Rating Distribution -->
  <div class="card" style="margin-bottom:1rem">
    <div class="card-header"><span class="card-title"><i class="fa fa-chart-bar" style="color:var(--primary)"></i> Rating Distribution</span></div>
    <div class="card-body">
      <?php $distMap = array_column($distribution,'cnt','rating'); $maxDist = max($distMap ?: [1]); ?>
      <?php for($s=5;$s>=1;$s--): $cnt=$distMap[$s]??0; $pct=$maxDist>0?round($cnt/$maxDist*100):0; ?>
      <div style="display:flex;align-items:center;gap:.625rem;margin-bottom:.45rem">
        <span style="font-size:.8rem;font-weight:700;width:28px;text-align:right;color:var(--text-muted)"><?= $s ?>★</span>
        <div style="flex:1;height:10px;background:var(--bg);border-radius:5px;overflow:hidden">
          <div style="height:100%;width:<?= $pct ?>%;background:<?= $s>=4?'var(--success)':($s>=3?'var(--warning)':'var(--danger)') ?>;border-radius:5px;transition:width .6s"></div>
        </div>
        <span style="font-size:.78rem;color:var(--text-muted);width:28px"><?= $cnt ?></span>
      </div>
      <?php endfor; ?>
    </div>
  </div>

  <!-- All Reviews -->
  <div class="card">
    <div class="card-header"><span class="card-title"><i class="fa fa-comments" style="color:var(--primary)"></i> All Reviews</span></div>
    <div style="max-height:480px;overflow-y:auto">
      <?php if (empty($feedback)): ?>
      <div class="empty-state" style="padding:3rem"><i class="fa fa-star"></i><p>No feedback yet</p><small>Share your bill slip with customers to get reviews</small></div>
      <?php else: foreach ($feedback as $fb): ?>
      <div style="padding:.875rem 1.25rem;border-bottom:1px solid var(--border)">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:.5rem">
          <div>
            <div style="font-weight:700;font-size:.875rem"><?= esc($fb['customer_name'] ?? 'Anonymous') ?></div>
            <div style="font-size:.7rem;color:var(--text-muted)"><?= esc($fb['order_number']) ?> · <?= date('d M Y', strtotime($fb['submitted_at'])) ?></div>
            <div style="font-size:.8rem;margin-top:.3rem;color:var(--text-muted)">
              🍽 <?= $fb['food_rating'] ?> · 👨 <?= $fb['service_rating'] ?> · ✨ <?= $fb['ambience_rating'] ?>
              <?= $fb['would_return'] ? ' · <span style="color:var(--success)">↩ Will return</span>' : '' ?>
            </div>
            <?php if ($fb['comment']): ?>
            <div style="font-size:.83rem;margin-top:.4rem;color:var(--text);font-style:italic">"<?= esc($fb['comment']) ?>"</div>
            <?php endif; ?>
          </div>
          <div style="font-size:1.1rem;font-weight:900;white-space:nowrap">
            <?php $rc=$fb['overall_rating'];$rclr=$rc>=4?'var(--success)':($rc>=3?'var(--warning)':'var(--danger)'); ?>
            <span style="color:<?= $rclr ?>">⭐<?= $rc ?></span>
          </div>
        </div>
      </div>
      <?php endforeach; endif; ?>
    </div>
  </div>
</div>
<?php $this->endSection(); ?>
