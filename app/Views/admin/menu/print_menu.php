<?php $this->extend('layouts/main'); $this->section('content'); ?>
<?php
$themeColor = $restaurant['theme_color']   ?? '#FF6B35';
$accentDark = $restaurant['theme_color']   ?? '#E55A20';
$restName   = $restaurant['name']          ?? session('restaurant_name');
$tagline    = $restaurant['tagline']       ?? '';
$address    = $restaurant['address']       ?? '';
$city       = $restaurant['city']          ?? '';
$phone      = $restaurant['phone']         ?? '';
$gst        = $restaurant['gst_number']    ?? '';
$sym        = $restaurant['currency_symbol'] ?? '₹';
$logo       = $restaurant['logo']          ?? '';
$branchName = $branch['name']              ?? '';
$fullAddr   = trim($address . ($city ? ', ' . $city : ''));
?>

<style>
/* ─────────────────────────────────────────────────────────
   SCREEN UI
───────────────────────────────────────────────────────── */
.pm-root { padding:0 1rem 4rem; }

/* Toolbar */
.pm-toolbar {
  position:sticky; top:0; z-index:50; background:#fff;
  border-bottom:1px solid #E2E8F0;
  padding:.875rem 1rem;
  display:flex; align-items:center; gap:.75rem; flex-wrap:wrap;
  margin:0 -1rem 1.5rem;
  box-shadow:0 1px 8px rgba(0,0,0,.06);
}
.pm-toolbar-title { font-weight:900; font-size:1rem; color:#0F172A; }
.pm-toolbar-meta  { font-size:.75rem; color:#94A3B8; }

/* Controls */
.ctrl-row { display:flex; gap:1rem; flex-wrap:wrap; margin-bottom:1.5rem; }
.ctrl-group { background:#fff; border:1px solid #E2E8F0; border-radius:12px; padding:.875rem 1rem; flex:1; min-width:200px; }
.ctrl-group h4 { font-size:.7rem; font-weight:900; color:#94A3B8; text-transform:uppercase; letter-spacing:.08em; margin-bottom:.75rem; }
.ctrl-btns { display:flex; gap:.4rem; flex-wrap:wrap; }
.ctrl-btn {
  padding:.3rem .75rem; border-radius:8px; border:1.5px solid #E2E8F0;
  background:#fff; font-size:.75rem; font-weight:700; color:#64748B;
  cursor:pointer; transition:all .15s;
}
.ctrl-btn.on { background:var(--primary); color:#fff; border-color:var(--primary); }
.ctrl-btn:hover:not(.on) { border-color:#94A3B8; }
.ctrl-toggle { display:flex; align-items:center; gap:.5rem; margin-bottom:.4rem; cursor:pointer; }
.ctrl-toggle input[type=checkbox] { width:15px; height:15px; accent-color:var(--primary); cursor:pointer; }
.ctrl-toggle label { font-size:.8rem; font-weight:600; color:#334155; cursor:pointer; }

/* Preview badge */
.preview-badge {
  display:inline-flex; align-items:center; gap:.4rem;
  background:#F0FDF4; border:1px solid #86EFAC;
  color:#15803D; font-size:.72rem; font-weight:700;
  padding:.3rem .75rem; border-radius:20px; margin-bottom:1rem;
}

/* ─────────────────────────────────────────────────────────
   MENU CARD (shared screen + print)
───────────────────────────────────────────────────────── */
#menuCard {
  background:#fff;
  max-width:800px;
  margin:0 auto;
  border-radius:16px;
  border:1px solid #E2E8F0;
  box-shadow:0 4px 32px rgba(0,0,0,.08);
  overflow:hidden;
  font-family:'Georgia',serif;
}

/* ── Cover header */
.mc-cover {
  background:var(--mc-theme, <?= esc($themeColor) ?>);
  padding:2.5rem 2rem 2rem;
  text-align:center;
  position:relative;
  overflow:hidden;
}
.mc-cover::before {
  content:'';
  position:absolute; inset:0;
  background:url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
}
.mc-logo-wrap {
  width:72px; height:72px; border-radius:50%;
  background:rgba(255,255,255,.2);
  border:3px solid rgba(255,255,255,.5);
  margin:0 auto 1rem;
  display:flex; align-items:center; justify-content:center;
  overflow:hidden; position:relative; z-index:1;
}
.mc-logo-wrap img { width:100%; height:100%; object-fit:cover; border-radius:50%; }
.mc-logo-wrap .mc-logo-icon { font-size:2rem; }
.mc-rest-name {
  font-family:'Georgia',serif;
  font-size:2rem; font-weight:700;
  color:#fff; letter-spacing:.02em;
  text-shadow:0 2px 8px rgba(0,0,0,.2);
  position:relative; z-index:1;
}
.mc-tagline {
  color:rgba(255,255,255,.8); font-size:.9rem;
  font-style:italic; margin-top:.35rem;
  position:relative; z-index:1;
}
.mc-branch-pill {
  display:inline-block; background:rgba(255,255,255,.2);
  color:#fff; font-size:.7rem; font-weight:700;
  padding:.25rem .875rem; border-radius:20px;
  margin-top:.625rem; letter-spacing:.04em;
  position:relative; z-index:1;
}
.mc-divider {
  display:flex; align-items:center; gap:.75rem;
  margin:1.25rem 0 0; position:relative; z-index:1;
}
.mc-divider::before,.mc-divider::after {
  content:''; flex:1; height:1px; background:rgba(255,255,255,.3);
}
.mc-divider span { color:rgba(255,255,255,.7); font-size:.75rem; letter-spacing:.12em; text-transform:uppercase; }

/* ── Category section */
.mc-category { padding:1.75rem 2rem 0; }
.mc-cat-header {
  display:flex; align-items:center; gap:.875rem;
  margin-bottom:1.25rem;
}
.mc-cat-icon {
  width:40px; height:40px; border-radius:10px;
  background:var(--mc-theme, <?= esc($themeColor) ?>);
  display:flex; align-items:center; justify-content:center;
  font-size:1.1rem; flex-shrink:0;
}
.mc-cat-name {
  font-family:'Georgia',serif;
  font-size:1.2rem; font-weight:700;
  color:#1E293B; letter-spacing:.01em;
}
.mc-cat-count {
  font-size:.72rem; color:#94A3B8; font-family:sans-serif; margin-top:.1rem;
}
.mc-cat-line {
  flex:1; height:2px;
  background:linear-gradient(90deg, var(--mc-theme, <?= esc($themeColor) ?>), transparent);
  opacity:.25;
}

/* ── Item grid modes */
.mc-items-grid  { display:grid; grid-template-columns:1fr 1fr; gap:1px; background:#F1F5F9; border:1px solid #F1F5F9; border-radius:10px; overflow:hidden; margin-bottom:1.5rem; }
.mc-items-list  { display:flex; flex-direction:column; gap:0; border:1px solid #F1F5F9; border-radius:10px; overflow:hidden; margin-bottom:1.5rem; }
.mc-items-3col  { display:grid; grid-template-columns:1fr 1fr 1fr; gap:1px; background:#F1F5F9; border:1px solid #F1F5F9; border-radius:10px; overflow:hidden; margin-bottom:1.5rem; }

/* ── Item card — WITH image */
.mc-item {
  background:#fff; padding:.875rem; display:flex; flex-direction:column; gap:.4rem;
  position:relative; transition:background .15s;
}
.mc-item-img-wrap {
  width:100%; aspect-ratio:4/3; border-radius:8px; overflow:hidden;
  background:#F8FAFC; margin-bottom:.3rem;
  display:flex; align-items:center; justify-content:center; font-size:2.5rem;
}
.mc-item-img-wrap img { width:100%; height:100%; object-fit:cover; }
.mc-item-top { display:flex; align-items:flex-start; gap:.375rem; }
.mc-dot { width:10px; height:10px; border-radius:2px; border:1.5px solid; flex-shrink:0; margin-top:3px; }
.mc-dot.veg  { border-color:#15803D; background:#15803D; }
.mc-dot.nveg { border-color:#B91C1C; background:#B91C1C; }
.mc-item-name {
  font-family:'Georgia',serif; font-weight:700;
  font-size:.9rem; color:#0F172A; line-height:1.3;
}
.mc-item-desc {
  font-size:.72rem; color:#64748B; line-height:1.55;
  display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden;
  font-family:sans-serif;
}
.mc-item-foot { display:flex; align-items:center; justify-content:space-between; margin-top:auto; }
.mc-item-price {
  font-family:'Georgia',serif; font-weight:700;
  font-size:1rem; color:var(--mc-theme, <?= esc($themeColor) ?>);
}
.mc-item-badges { display:flex; gap:.25rem; flex-wrap:wrap; }
.mc-badge {
  font-size:.55rem; font-weight:800; padding:.1rem .35rem;
  border-radius:6px; font-family:sans-serif; letter-spacing:.03em;
}
.mc-badge.best  { background:#FEF3C7; color:#92400E; }
.mc-badge.spicy { background:#FEF2F2; color:#B91C1C; }
.mc-badge.rec   { background:#EFF6FF; color:#1D4ED8; }
.mc-badge.new   { background:#F0FDF4; color:#15803D; }

/* Variants */
.mc-variants { display:flex; gap:.35rem; flex-wrap:wrap; margin-top:.25rem; }
.mc-var { font-size:.6rem; font-weight:700; color:#64748B; background:#F8FAFC; border:1px solid #E2E8F0; border-radius:6px; padding:.1rem .4rem; font-family:sans-serif; }
.mc-var b { color:var(--mc-theme, <?= esc($themeColor) ?>); }

/* ── List item (no image layout) */
.mc-list-item {
  background:#fff; padding:.875rem 1rem;
  display:flex; align-items:center; gap:.75rem;
  border-bottom:1px solid #F8FAFC;
}
.mc-list-item:last-child { border-bottom:none; }
.mc-list-body { flex:1; min-width:0; }
.mc-list-name { font-family:'Georgia',serif; font-weight:700; font-size:.9rem; color:#0F172A; }
.mc-list-desc { font-size:.72rem; color:#64748B; margin-top:.1rem; font-family:sans-serif; }
.mc-list-price { font-family:'Georgia',serif; font-weight:700; font-size:.95rem; color:var(--mc-theme, <?= esc($themeColor) ?>); flex-shrink:0; }

/* ── Footer */
.mc-footer {
  background:#F8FAFC; border-top:2px solid var(--mc-theme, <?= esc($themeColor) ?>);
  padding:1.25rem 2rem; text-align:center;
}
.mc-footer-name { font-family:'Georgia',serif; font-size:.95rem; font-weight:700; color:#0F172A; margin-bottom:.25rem; }
.mc-footer-addr { font-size:.75rem; color:#64748B; line-height:1.6; font-family:sans-serif; }
.mc-footer-row  { display:flex; gap:1.5rem; justify-content:center; flex-wrap:wrap; margin-top:.5rem; }
.mc-footer-info { font-size:.72rem; color:#64748B; display:flex; align-items:center; gap:.3rem; font-family:sans-serif; }
.mc-footer-info i { color:var(--mc-theme, <?= esc($themeColor) ?>); }
.mc-footer-note { font-size:.65rem; color:#94A3B8; margin-top:.625rem; font-family:sans-serif; }

/* Page break between categories */
.mc-page-break { page-break-after:always; height:1px; }

/* Spacing between categories on screen */
.mc-cat-sep { height:1px; background:#F1F5F9; margin:0 2rem; }

/* ─────────────────────────────────────────────────────────
   PRINT STYLES
───────────────────────────────────────────────────────── */
@media print {
  body > *:not(#printWrap) { display:none !important; }
  #printWrap { display:block !important; }

  #menuCard {
    border:none !important;
    box-shadow:none !important;
    border-radius:0 !important;
    max-width:none !important;
    margin:0 !important;
  }

  .mc-cover {
    -webkit-print-color-adjust:exact !important;
    print-color-adjust:exact !important;
    background:var(--mc-theme, <?= esc($themeColor) ?>) !important;
  }
  .mc-cat-icon {
    -webkit-print-color-adjust:exact !important;
    print-color-adjust:exact !important;
  }
  .mc-footer {
    -webkit-print-color-adjust:exact !important;
    print-color-adjust:exact !important;
  }

  .mc-items-grid { break-inside:avoid; }
  .mc-item { break-inside:avoid; }

  @page { size:A4; margin:8mm; }
}
</style>

<div class="pm-root">

  <!-- Toolbar -->
  <div class="pm-toolbar">
    <div>
      <div class="pm-toolbar-title"><i class="fa fa-book-open" style="color:var(--primary)"></i> Print Menu Card</div>
      <div class="pm-toolbar-meta"><?= esc($restName) ?> · <?= count($categories) ?> categories · <?= $totalItems ?> items</div>
    </div>
    <div style="display:flex;gap:.5rem;margin-left:auto;flex-wrap:wrap">
      <a href="<?= base_url('admin/menu') ?>" class="btn btn-outline btn-sm"><i class="fa fa-arrow-left"></i> Back</a>
      <button onclick="printMenu()" class="btn btn-primary"><i class="fa fa-print"></i> Print Menu</button>
    </div>
  </div>

  <?php if (empty($categories)): ?>
  <div class="empty-state"><i class="fa fa-utensils"></i><p>No menu items found. Add items to your menu first.</p></div>
  <?php else: ?>

  <!-- Controls row -->
  <div class="ctrl-row">

    <!-- Layout -->
    <div class="ctrl-group">
      <h4><i class="fa fa-table-cells"></i> Layout</h4>
      <div class="ctrl-btns">
        <button class="ctrl-btn" onclick="setLayout('list',this)"><i class="fa fa-bars"></i> List</button>
        <button class="ctrl-btn on" onclick="setLayout('grid',this)"><i class="fa fa-th-large"></i> 2 Column</button>
        <button class="ctrl-btn" onclick="setLayout('3col',this)"><i class="fa fa-th"></i> 3 Column</button>
      </div>
    </div>

    <!-- Show/Hide fields -->
    <div class="ctrl-group">
      <h4><i class="fa fa-eye"></i> Show / Hide</h4>
      <label class="ctrl-toggle"><input type="checkbox" id="togImg" checked onchange="applyToggles()"><label for="togImg">Item Images</label></label>
      <label class="ctrl-toggle"><input type="checkbox" id="togDesc" checked onchange="applyToggles()"><label for="togDesc">Descriptions</label></label>
      <label class="ctrl-toggle"><input type="checkbox" id="togBadge" checked onchange="applyToggles()"><label for="togBadge">Badges (Best, Spicy)</label></label>
      <label class="ctrl-toggle"><input type="checkbox" id="togVar" checked onchange="applyToggles()"><label for="togVar">Size Variants</label></label>
    </div>

    <!-- Theme color -->
    <div class="ctrl-group">
      <h4><i class="fa fa-palette"></i> Color Theme</h4>
      <div class="ctrl-btns">
        <button class="ctrl-btn on" style="border-color:#FF6B35" onclick="setTheme('#FF6B35',this)"><span style="display:inline-block;width:12px;height:12px;border-radius:50%;background:#FF6B35;vertical-align:middle"></span> Orange</button>
        <button class="ctrl-btn" style="" onclick="setTheme('#1E293B',this)"><span style="display:inline-block;width:12px;height:12px;border-radius:50%;background:#1E293B;vertical-align:middle"></span> Dark</button>
        <button class="ctrl-btn" style="" onclick="setTheme('#15803D',this)"><span style="display:inline-block;width:12px;height:12px;border-radius:50%;background:#15803D;vertical-align:middle"></span> Green</button>
        <button class="ctrl-btn" style="" onclick="setTheme('#B91C1C',this)"><span style="display:inline-block;width:12px;height:12px;border-radius:50%;background:#B91C1C;vertical-align:middle"></span> Red</button>
        <button class="ctrl-btn" style="" onclick="setTheme('#1D4ED8',this)"><span style="display:inline-block;width:12px;height:12px;border-radius:50%;background:#1D4ED8;vertical-align:middle"></span> Blue</button>
        <button class="ctrl-btn" style="" onclick="setTheme('#6D28D9',this)"><span style="display:inline-block;width:12px;height:12px;border-radius:50%;background:#6D28D9;vertical-align:middle"></span> Purple</button>
      </div>
    </div>

  </div>

  <div class="preview-badge"><i class="fa fa-eye"></i> Live preview — exactly how it prints</div>

  <!-- The Menu Card -->
  <div id="printWrap">
  <div id="menuCard" style="--mc-theme:<?= esc($themeColor) ?>">

    <!-- Cover -->
    <div class="mc-cover">
      <div class="mc-logo-wrap">
        <?php if ($logo): ?>
        <img src="<?= base_url('images/uploads/'.$logo) ?>" alt="Logo">
        <?php else: ?>
        <span class="mc-logo-icon">🍽</span>
        <?php endif; ?>
      </div>
      <div class="mc-rest-name"><?= esc($restName) ?></div>
      <?php if ($tagline): ?><div class="mc-tagline"><?= esc($tagline) ?></div><?php endif; ?>
      <?php if ($branchName): ?><div class="mc-branch-pill"><i class="fa fa-location-dot"></i> <?= esc($branchName) ?></div><?php endif; ?>
      <div class="mc-divider"><span>✦ MENU ✦</span></div>
    </div>

    <!-- Categories -->
    <?php
    $catIcons = ['🍽','🥗','🍜','🍛','🍲','🥘','🍱','🥩','🐟','🍗','🥪','🌮','🍕','🍝','🥚','🍰','🧁','🍩','☕','🥤','🍹','🧃','🍺','🍦'];
    $ci = 0;
    foreach ($categories as $idx => $cat):
      $icon = $catIcons[$ci++ % count($catIcons)];
    ?>
    <div class="mc-category">
      <!-- Category header -->
      <div class="mc-cat-header">
        <div class="mc-cat-icon"><?= $icon ?></div>
        <div>
          <div class="mc-cat-name"><?= esc($cat['name']) ?></div>
          <div class="mc-cat-count"><?= count($cat['items']) ?> item<?= count($cat['items'])>1?'s':'' ?></div>
        </div>
        <div class="mc-cat-line"></div>
      </div>

      <!-- Items grid (class toggled by JS) -->
      <div class="mc-items-grid mc-items-container">
        <?php foreach ($cat['items'] as $item):
          $isVeg = in_array($item['item_type'], ['veg','vegan']);
          $price = $sym . number_format($item['base_price'], 2);
        ?>

        <!-- GRID CARD (with image) -->
        <div class="mc-item mc-grid-item">
          <!-- Image -->
          <div class="mc-item-img mc-item-img-wrap">
            <?php if (!empty($item['image'])): ?>
            <img src="<?= base_url('images/uploads/'.$item['image']) ?>" alt="<?= esc($item['name']) ?>" loading="lazy">
            <?php else: ?>
            <?= in_array($item['food_type'],['beverage']) ? '☕' :
                (in_array($item['food_type'],['dessert'])  ? '🍰' :
                ($isVeg ? '🥗' : '🍗')) ?>
            <?php endif; ?>
          </div>
          <!-- Name + dot -->
          <div class="mc-item-top">
            <span class="mc-dot <?= $isVeg?'veg':'nveg' ?>"></span>
            <span class="mc-item-name"><?= esc($item['name']) ?></span>
          </div>
          <!-- Description -->
          <?php if (!empty($item['description'])): ?>
          <div class="mc-item-desc mc-desc"><?= esc($item['description']) ?></div>
          <?php endif; ?>
          <!-- Variants -->
          <?php if (!empty($item['variants'])): ?>
          <div class="mc-variants mc-var-row">
            <?php foreach ($item['variants'] as $v): ?>
            <span class="mc-var"><?= esc($v['name']) ?> <b><?= $sym.number_format($v['price'],0) ?></b></span>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>
          <!-- Price + badges -->
          <div class="mc-item-foot">
            <span class="mc-item-price"><?php if (empty($item['variants'])) echo $price; ?></span>
            <div class="mc-item-badges mc-badges-row">
              <?php if ($item['is_bestseller']): ?><span class="mc-badge best">🔥 Best</span><?php endif; ?>
              <?php if ($item['is_spicy']): ?><span class="mc-badge spicy">🌶 Spicy</span><?php endif; ?>
              <?php if ($item['is_recommended'] && !$item['is_bestseller']): ?><span class="mc-badge rec">⭐ Chef's Pick</span><?php endif; ?>
            </div>
          </div>
        </div>

        <!-- LIST ROW (no image) -->
        <div class="mc-list-item mc-list-only" style="display:none">
          <span class="mc-dot <?= $isVeg?'veg':'nveg' ?>" style="flex-shrink:0;margin-top:4px"></span>
          <div class="mc-list-body">
            <div class="mc-list-name"><?= esc($item['name']) ?>
              <span class="mc-item-badges mc-badges-row" style="margin-left:.35rem">
                <?php if ($item['is_bestseller']): ?><span class="mc-badge best">🔥 Best</span><?php endif; ?>
                <?php if ($item['is_spicy']): ?><span class="mc-badge spicy">🌶</span><?php endif; ?>
                <?php if ($item['is_recommended'] && !$item['is_bestseller']): ?><span class="mc-badge rec">⭐</span><?php endif; ?>
              </span>
            </div>
            <?php if (!empty($item['description'])): ?>
            <div class="mc-list-desc mc-desc"><?= esc($item['description']) ?></div>
            <?php endif; ?>
            <?php if (!empty($item['variants'])): ?>
            <div class="mc-variants mc-var-row" style="margin-top:.25rem">
              <?php foreach ($item['variants'] as $v): ?>
              <span class="mc-var"><?= esc($v['name']) ?> <b><?= $sym.number_format($v['price'],0) ?></b></span>
              <?php endforeach; ?>
            </div>
            <?php endif; ?>
          </div>
          <div class="mc-list-price"><?php if (empty($item['variants'])) echo $price; ?></div>
        </div>

        <?php endforeach; ?>
      </div>
    </div>

    <?php if ($idx < count($categories)-1): ?>
    <div class="mc-cat-sep"></div>
    <?php endif; ?>

    <?php endforeach; ?>

    <!-- Footer -->
    <div class="mc-footer">
      <div class="mc-footer-name"><?= esc($restName) ?></div>
      <?php if ($fullAddr): ?><div class="mc-footer-addr"><?= esc($fullAddr) ?></div><?php endif; ?>
      <div class="mc-footer-row">
        <?php if ($phone): ?><div class="mc-footer-info"><i class="fa fa-phone"></i> <?= esc($phone) ?></div><?php endif; ?>
        <?php if ($gst):   ?><div class="mc-footer-info"><i class="fa fa-receipt"></i> GST: <?= esc($gst) ?></div><?php endif; ?>
      </div>
      <div class="mc-footer-note">Prices inclusive of all taxes · Subject to change without notice</div>
    </div>

  </div><!-- #menuCard -->
  </div><!-- #printWrap -->

  <?php endif; ?>
</div>

<script>
let currentLayout = 'grid';

// ── Layout switcher ──────────────────────────────────────
function setLayout(mode, btn) {
  currentLayout = mode;
  document.querySelectorAll('[onclick*="setLayout"]').forEach(b => b.classList.remove('on'));
  btn.classList.add('on');

  document.querySelectorAll('.mc-items-container').forEach(el => {
    el.classList.remove('mc-items-grid','mc-items-list','mc-items-3col');
    el.classList.add(mode==='list'?'mc-items-list':mode==='3col'?'mc-items-3col':'mc-items-grid');
  });

  // List mode: hide image cards, show list rows
  const isListMode = mode === 'list';
  document.querySelectorAll('.mc-grid-item').forEach(el => el.style.display = isListMode?'none':'');
  document.querySelectorAll('.mc-list-only').forEach(el => el.style.display = isListMode?'flex':'none');
}

// ── Show/Hide toggles ────────────────────────────────────
function applyToggles() {
  const showImg   = document.getElementById('togImg').checked;
  const showDesc  = document.getElementById('togDesc').checked;
  const showBadge = document.getElementById('togBadge').checked;
  const showVar   = document.getElementById('togVar').checked;

  document.querySelectorAll('.mc-item-img').forEach(el   => el.style.display  = showImg   ? '' : 'none');
  document.querySelectorAll('.mc-desc').forEach(el        => el.style.display  = showDesc  ? '' : 'none');
  document.querySelectorAll('.mc-badges-row').forEach(el  => el.style.display  = showBadge ? '' : 'none');
  document.querySelectorAll('.mc-var-row').forEach(el     => el.style.display  = showVar   ? '' : 'none');
}

// ── Theme color ──────────────────────────────────────────
function setTheme(color, btn) {
  document.querySelectorAll('[onclick*="setTheme"]').forEach(b => b.classList.remove('on'));
  btn.classList.add('on');
  document.getElementById('menuCard').style.setProperty('--mc-theme', color);
  // Update all gradient lines
  document.querySelectorAll('.mc-cat-line').forEach(el => {
    el.style.background = `linear-gradient(90deg, ${color}, transparent)`;
  });
}

// ── Print ────────────────────────────────────────────────
function printMenu() {
  window.print();
}
</script>

<?php $this->endSection(); ?>
