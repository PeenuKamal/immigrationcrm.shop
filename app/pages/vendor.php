<?php
$u = current_user();
$meta = meta(['title' => 'For vendors', 'description' => 'List your immigration software for free, reply to verified reviews and get featured.', 'path' => '/vendor']);
$mine = $u ? all('SELECT * FROM software WHERE owner_id = ? ORDER BY name', [$u['id']]) : [];
?>
<div class="wrap" style="max-width:860px">
  <div class="page-head"><h1>For vendors</h1><p>Keep your listing accurate, reply to verified reviews, and reach firms that are actively comparing tools.</p></div>
  <?= notice(take_flash()) ?>
  <?php if ($mine): ?>
    <ul class="listing-rows">
      <?php foreach ($mine as $s): ?>
      <li class="panel" style="display:flex;justify-content:space-between;gap:16px;flex-wrap:wrap;align-items:center">
        <div><h3 style="margin-bottom:4px"><?= e($s['name']) ?></h3>
          <p class="small muted" style="margin:0"><span class="status <?= e($s['status']) ?>"><?= $s['status'] === 'PUBLISHED' ? 'Live' : ($s['status'] === 'PENDING' ? 'In review' : 'Not published') ?></span>
          <?= (int)$s['review_count'] ?> verified reviews<?= is_featured($s['featured_until']) ? ' · Featured until ' . e(format_date($s['featured_until'])) : '' ?></p></div>
        <a class="btn secondary" href="/vendor/<?= (int)$s['id'] ?>">Manage</a>
      </li>
      <?php endforeach; ?>
    </ul>
  <?php else: ?>
    <div class="panel">
      <h2 style="font-size:var(--step-2)">What vendors can do</h2>
      <ul>
        <li><strong>List for free.</strong> Every immigration, legal and AI tool belongs in the directory.</li>
        <li><strong>Reply to reviews.</strong> Your public response appears under each review.</li>
        <li><strong>Get featured.</strong> Featured listings show first in their categories and are marked with a gold edge. Featuring never changes ratings or which reviews appear.</li>
      </ul>
      <p class="muted">Vendors can't edit, hide or remove reviews, and staff can't review their own product.</p>
      <div style="display:flex;gap:12px;flex-wrap:wrap"><a class="btn" href="/list-your-software">List your software</a><a class="btn secondary" href="/software">Claim an existing listing</a></div>
    </div>
  <?php endif; ?>
</div>
