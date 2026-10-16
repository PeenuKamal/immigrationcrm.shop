<?php
$crumbs = [['Home', '/'], ['Best by country', '/best']];
$items = [];
foreach (COUNTRIES as $cs => $_) foreach (country_categories(country($cs)) as $x) $items[] = ["Best " . category_seo($x['cat']['slug'], $x['cat']['name']) . ' in ' . country($cs)['in'], guide_path($cs, $x['cat']['slug'])];
$meta = meta([
    'title' => 'Best immigration software by country (' . year() . ')',
    'description' => 'Country-by-country rankings of immigration CRMs, case management, practice management and AI tools for Canada, the USA, the UK, Australia and India.',
    'path' => '/best',
    'jsonld' => [breadcrumb_ld($crumbs), item_list_ld('Immigration software buying guides by country', $items)],
]);
?>
<?= page_band($crumbs, 'Best immigration software by country', 'Each guide ranks tools for the regulators, forms and programs of one country — with verified practitioner reviews.') ?>
<div class="wrap">
  <?php foreach (COUNTRIES as $cs => $cc): $c = country($cs); ?>
  <section class="panel" style="margin-bottom:20px">
    <div style="display:flex;gap:16px;align-items:center;margin-bottom:14px">
      <span class="flag <?= $cc['class'] ?>"><?= $cc['code'] ?></span>
      <div><h2 style="font-size:1.35rem;margin:0"><a href="/best/<?= $cs ?>" style="color:var(--ink)">Best immigration software in <?= e($c['in']) ?></a></h2><p class="muted small" style="margin:0">For <?= e($c['who']) ?></p></div>
    </div>
    <ul class="link-grid plain"><?php foreach (country_categories($c) as $x): ?><li><a href="<?= e(guide_path($cs, $x['cat']['slug'])) ?>"><?= e(category_seo($x['cat']['slug'], $x['cat']['name'])) ?></a></li><?php endforeach; ?></ul>
  </section>
  <?php endforeach; ?>
</div>
