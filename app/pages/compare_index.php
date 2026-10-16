<?php
$all = all("SELECT id, slug, name FROM software WHERE status = 'PUBLISHED' ORDER BY name");
$catNames = category_names_for(array_column($all, 'id'));
$options = array_map(fn($a) => ['slug' => $a['slug'], 'name' => $a['name'], 'cat' => $catNames[$a['id']][0] ?? ''], $all);
$pairs = popular_pairs();
$crumbs = [['Home', '/'], ['Compare', '/compare']];
$meta = meta([
    'title' => 'Compare immigration software side by side (' . year() . ')',
    'description' => 'Compare any 2–4 immigration CRMs, case management, practice management or AI tools side by side: verified ratings, features, markets served, pricing and real pros and cons.',
    'path' => '/compare',
    'jsonld' => [breadcrumb_ld($crumbs), item_list_ld('Popular immigration software comparisons', array_map(fn($p) => ["{$p['an']} vs {$p['bn']}", compare_path([$p['a'], $p['b']])], array_slice($pairs, 0, 50)))],
]);
$initial = get('add');
?>
<?= page_band($crumbs, 'Compare immigration software', 'Pick any two to four tools — from any category — and see verified ratings, features, markets and real pros and cons side by side.') ?>
<div class="wrap">

  <div class="compare-picker panel" id="picker" data-initial="<?= e($initial) ?>">
    <div>
      <p class="small muted" style="margin-bottom:8px" id="picked-label">Choose 2 to 4 products.</p>
      <div class="picked" id="picked" aria-labelledby="picked-label"></div>
    </div>
    <div class="field" style="margin:0">
      <label for="cmp-q">Find software</label>
      <input id="cmp-q" type="search" placeholder="Name or category, e.g. Docketwise or AI" autocomplete="off">
    </div>
    <ul class="pick-results" id="pick-results" aria-label="Software to compare"></ul>
    <div><button type="button" class="btn stamp" id="cmp-go" disabled>Choose at least 2 to compare</button></div>
    <noscript><p class="small">Turn on JavaScript to build a comparison, or use the links below.</p></noscript>
  </div>
  <script type="application/json" id="cmp-options"><?= json_encode($options, JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) ?></script>
  <script src="/assets/compare.js?v=1" defer></script>

  <?php foreach (categories_with_counts() as $c):
    $list = array_filter($pairs, fn($p) => $p['cat'] === $c['slug']);
    if (!$list) continue; ?>
  <section class="section" aria-labelledby="pairs-<?= e($c['slug']) ?>">
    <div class="section-head"><h2 id="pairs-<?= e($c['slug']) ?>" style="font-size:var(--step-2)"><?= e($c['name']) ?> comparisons</h2></div>
    <ul class="link-grid"><?php foreach ($list as $p): ?><li><a href="<?= e(compare_path([$p['a'], $p['b']])) ?>"><?= e("{$p['an']} vs {$p['bn']}") ?></a></li><?php endforeach; ?></ul>
  </section>
  <?php endforeach; ?>
</div>
