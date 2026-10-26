<?php $this->extend('layouts/main'); $this->section('content'); ?>
<div style="padding:0 1rem">
  <?php if (session()->getFlashdata('success')): ?>
  <div class="card" style="border-left:4px solid var(--success);margin-bottom:1rem"><div class="card-body" style="color:var(--success);font-weight:600"><?= esc(session()->getFlashdata('success')) ?></div></div>
  <?php endif; ?>
  <?php if (session()->getFlashdata('error')): ?>
  <div class="card" style="border-left:4px solid var(--danger);margin-bottom:1rem"><div class="card-body" style="color:var(--danger);font-weight:600"><?= esc(session()->getFlashdata('error')) ?></div></div>
  <?php endif; ?>

  <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;align-items:start">
    <!-- Left: forms -->
    <div class="card">
      <div class="card-header"><span class="card-title"><i class="fa fa-paper-plane" style="color:var(--primary)"></i> Feedback forms to restaurant owners</span></div>
      <div class="card-body">
        <form action="<?= base_url('super/feedback/store') ?>" method="POST">
          <?= csrf_field() ?>
          <div class="form-group">
            <label class="form-label">Title</label>
            <input type="text" name="title" class="form-input" maxlength="150" required placeholder="e.g. How is DinoviX working for your restaurant?">
          </div>
          <div class="form-group">
            <label class="form-label">Message (shown in the owner's notification)</label>
            <textarea name="message" class="form-input" rows="3" maxlength="500" placeholder="A short note asking the owner to rate and share how things are going…"></textarea>
          </div>
          <button type="submit" class="btn btn-primary"><i class="fa fa-plus"></i> Create form</button>
        </form>
      </div>
      <div class="card-body" style="padding-top:0">
        <?php if (empty($forms)): ?>
          <div class="empty-state" style="padding:1.5rem"><p>No feedback forms yet</p></div>
        <?php else: foreach ($forms as $f): ?>
        <div style="border:1px solid var(--border);border-radius:10px;padding:.75rem;margin-bottom:.6rem">
          <div style="display:flex;justify-content:space-between;gap:.5rem;align-items:flex-start">
            <div>
              <div style="font-weight:800"><?= esc($f['title']) ?></div>
              <?php if ($f['message']): ?><div style="font-size:.75rem;color:var(--text-muted);margin-top:.2rem"><?= esc(mb_substr($f['message'], 0, 140)) ?><?= mb_strlen($f['message']) > 140 ? '…' : '' ?></div><?php endif; ?>
              <div style="font-size:.7rem;color:var(--text-muted);margin-top:.35rem">
                <?= date('d M Y', strtotime($f['created_at'])) ?> ·
                <?= (int)$f['responses'] ?> response<?= (int)$f['responses'] === 1 ? '' : 's' ?> ·
                <?= (int)$f['published'] ?> published
                <?= $f['is_active'] ? '<span class="badge-pill badge-success">open</span>' : '<span class="badge-pill badge-gray">closed</span>' ?>
              </div>
            </div>
            <div style="display:flex;flex-direction:column;gap:.3rem">
              <form action="<?= base_url('super/feedback/send/' . $f['id']) ?>" method="POST" onsubmit="return confirm('Send this form to all <?= (int)$tenantCount ?> active restaurant owners?')">
                <?= csrf_field() ?>
                <button class="btn btn-primary btn-sm" <?= $f['is_active'] ? '' : 'disabled' ?>><i class="fa fa-paper-plane"></i> Send to owners</button>
              </form>
              <form action="<?= base_url('super/feedback/toggle/' . $f['id']) ?>" method="POST">
                <?= csrf_field() ?>
                <button class="btn btn-outline btn-sm"><?= $f['is_active'] ? 'Close form' : 'Re-open' ?></button>
              </form>
            </div>
          </div>
        </div>
        <?php endforeach; endif; ?>
      </div>
    </div>

    <!-- Right: responses -->
    <div class="card">
      <div class="card-header"><span class="card-title"><i class="fa fa-comments" style="color:var(--primary)"></i> Owner responses</span></div>
      <div class="card-body" style="padding:0">
        <?php if (empty($responses)): ?>
          <div class="empty-state" style="padding:2rem"><p>No responses yet — send a form to the owners first</p></div>
        <?php else: ?>
          <?php foreach ($responses as $r): ?>
          <div style="border-bottom:1px solid var(--border);padding:.75rem 1rem">
            <div style="display:flex;justify-content:space-between;gap:.5rem;align-items:flex-start">
              <div style="min-width:0">
                <div style="display:flex;gap:.4rem;align-items:center">
                  <div class="testi-avatar" style="width:30px;height:30px;font-size:.72rem"><?= esc(mb_substr($r['display_name'] ?? 'R', 0, 2)) ?></div>
                  <div>
                    <div style="font-weight:700;font-size:.85rem"><?= esc($r['display_name'] ?? 'Owner') ?></div>
                    <div style="font-size:.7rem;color:var(--text-muted)"><?= esc($r['restaurant_name'] ?? '') ?> · <?= esc($r['form_title'] ?? '') ?></div>
                  </div>
                </div>
                <div class="stars" style="color:var(--primary);font-size:.85rem;margin:.3rem 0"><?= str_repeat('★', (int)$r['rating']) . str_repeat('☆', 5 - (int)$r['rating']) ?></div>
                <?php if ($r['comment']): ?><div style="font-size:.78rem;color:var(--text)">&ldquo;<?= esc($r['comment']) ?>&rdquo;</div><?php endif; ?>
                <div style="font-size:.68rem;color:var(--text-muted);margin-top:.25rem">
                  <?= $r['responded_at'] ? date('d M, h:i A', strtotime($r['responded_at'])) : '' ?>
                  <?= $r['can_publish'] ? ' · consented to publish' : ' · no publish consent' ?>
                  <?= $r['is_published'] ? ' · <b style="color:var(--success)">live on homepage</b>' : '' ?>
                </div>
              </div>
              <div style="display:flex;flex-direction:column;gap:.3rem;flex-shrink:0">
                <form action="<?= base_url('super/feedback/publish/' . $r['id']) ?>" method="POST">
                  <?= csrf_field() ?>
                  <button class="btn btn-sm <?= $r['is_published'] ? 'btn-outline' : 'btn-primary' ?>" <?= $r['can_publish'] ? '' : 'disabled title="Owner did not consent"' ?>>
                    <?= $r['is_published'] ? 'Unpublish' : 'Publish' ?>
                  </button>
                </form>
                <form action="<?= base_url('super/feedback/delete/' . $r['id']) ?>" method="POST" onsubmit="return confirm('Delete this response permanently?')">
                  <?= csrf_field() ?>
                  <button class="btn btn-outline btn-sm" style="color:var(--danger)">Delete</button>
                </form>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
<?php $this->endSection(); ?>
