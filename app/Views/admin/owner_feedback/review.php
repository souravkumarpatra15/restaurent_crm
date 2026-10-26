<?php $this->extend('layouts/main'); $this->section('content'); ?>
<div style="padding:0 1rem;max-width:720px">
  <?php if (session()->getFlashdata('success')): ?>
  <div class="card" style="border-left:4px solid var(--success);margin-bottom:1rem"><div class="card-body" style="color:var(--success);font-weight:600"><?= esc(session()->getFlashdata('success')) ?></div></div>
  <?php endif; ?>
  <?php if (session()->getFlashdata('error')): ?>
  <div class="card" style="border-left:4px solid var(--danger);margin-bottom:1rem"><div class="card-body" style="color:var(--danger);font-weight:600"><?= esc(session()->getFlashdata('error')) ?></div></div>
  <?php endif; ?>

  <p style="font-size:.85rem;color:var(--text-muted);margin-bottom:1rem">
    The DinoviX team occasionally asks restaurant owners how the system is working. Your answers help us improve —
    and with your permission, good ones may be shown (with your restaurant name) on our homepage.
  </p>

  <?php if (empty($forms) && empty($responded)): ?>
  <div class="card"><div class="empty-state" style="padding:3rem"><i class="fa fa-inbox" style="font-size:2rem;color:var(--text-muted)"></i><p>No feedback requests right now</p></div></div>
  <?php endif; ?>

  <?php foreach ($forms as $f): ?>
  <div class="card" style="border-left:4px solid var(--primary);margin-bottom:1rem">
    <div class="card-header"><span class="card-title"><?= esc($f['title']) ?></span></div>
    <div class="card-body">
      <?php if ($f['message']): ?><p style="font-size:.85rem;color:var(--text-muted);margin-bottom:1rem"><?= esc($f['message']) ?></p><?php endif; ?>
      <form action="<?= base_url('admin/owner-feedback/submit') ?>" method="POST">
        <?= csrf_field() ?>
        <input type="hidden" name="form_id" value="<?= (int)$f['id'] ?>">

        <div class="form-group">
          <label class="form-label">Your rating</label>
          <div id="starRow" style="display:flex;gap:.25rem;font-size:1.7rem;cursor:pointer;user-select:none">
            <?php for ($i = 1; $i <= 5; $i++): ?>
            <span data-star="<?= $i ?>" style="color:#D8DEE8" onmouseover="hoverStars(<?= $i ?>)" onmouseout="paintStars()" onclick="pickStars(<?= $i ?>)">★</span>
            <?php endfor; ?>
          </div>
          <input type="hidden" name="rating" id="ratingField" value="0">
        </div>

        <div class="form-group">
          <label class="form-label">Comment (optional)</label>
          <textarea name="comment" class="form-control" rows="3" maxlength="1000" placeholder="What works well? What should we improve?"></textarea>
        </div>

        <div class="form-group" style="display:flex;align-items:center;gap:.6rem">
          <input type="checkbox" name="can_publish" id="canPublish" value="1" style="width:18px;height:18px">
          <label for="canPublish" style="font-size:.8rem">You may show this feedback on your homepage with my name and restaurant</label>
        </div>

        <button type="submit" class="btn btn-primary"><i class="fa fa-paper-plane"></i> Send feedback</button>
      </form>
    </div>
  </div>
  <?php endforeach; ?>

  <?php if (!empty($responded)): ?>
  <div class="card">
    <div class="card-header"><span class="card-title"><i class="fa fa-check"></i> Your past feedback</span></div>
    <div class="card-body" style="padding:0">
      <table class="table" style="width:100%;font-size:.8rem">
        <thead><tr><th>Form</th><th>Rating</th><th>Comment</th><th>When</th><th>Status</th></tr></thead>
        <tbody>
        <?php foreach ($responded as $r): ?>
          <tr>
            <td><?= esc($r['form_title'] ?? '') ?></td>
            <td style="color:var(--primary)"><?= str_repeat('★', (int)$r['rating']) ?></td>
            <td style="color:var(--text-muted);max-width:240px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= esc($r['comment'] ?? '—') ?></td>
            <td style="white-space:nowrap"><?= $r['responded_at'] ? date('d M Y', strtotime($r['responded_at'])) : '' ?></td>
            <td><span class="badge-pill badge-<?= $r['is_published'] ? 'success' : 'gray' ?>"><?= $r['is_published'] ? 'Published' : 'Received' ?></span></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php endif; ?>
</div>

<script>
let picked = 0;
function hoverStars(n) { paintStars(n); }
function paintStars(hover = picked) {
  document.querySelectorAll('#starRow [data-star]').forEach(s => {
    s.style.color = (hover && +s.dataset.star <= hover) ? '#FD5A01' : '#D8DEE8';
  });
}
function pickStars(n) { picked = n; document.getElementById('ratingField').value = n; paintStars(); }
</script>
<?php $this->endSection(); ?>
