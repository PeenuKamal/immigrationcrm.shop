<?php
$c = country($params[0]);
if (!$c) not_found();
$sections = country_categories($c);
$path = guide_path($c['slug']);
$where = $c['in'];
$title = 'Best Immigration Software in ' . ucfirst($where) . ' (' . year() . ')';
$crumbs = [['Home', '/'], ['Best by country', '/best'], [$c['short'], $path]];
$picks = array_map(fn($x) => [$x['cat'], $x['ranked'][0]], $sections);
$answer = 'The top-ranked immigration software for ' . $c['who'] . ' in ' . $where . ' on ImmigrationCRM.shop: '
    . implode('; ', array_map(fn($p) => category_seo($p[0]['slug'], $p[0]['name']) . ': ' . $p[1]['s']['name'], $picks)) . '.';
$crm = array_values(array_filter($sections, fn($x) => $x['cat']['slug'] === 'immigration-crm'))[0] ?? null;
$faqs = [
    ["What is the best immigration software in $where?", $answer . ' The right choice depends on whether you need lead management (an immigration CRM), forms and case tracking (case management), or both.'],
];
if ($crm) $faqs[] = ["What is the best CRM for immigration practices in $where?", 'Top-ranked immigration CRMs for ' . $c['who'] . ' in ' . $where . ' include ' . join_names(array_map(fn($r) => $r['s']['name'], array_slice($crm['ranked'], 0, 4))) . '. See the full ranking at ' . url(guide_path($c['slug'], 'immigration-crm')) . '.'];
$faqs[] = ["What should immigration software for $where support?", implode('; ', array_slice($c['look_for'], 0, 4)) . '.'];
$faqs[] = ['How does ImmigrationCRM.shop rank software?', METHODOLOGY];
$insight = insight('country:' . $c['slug']);
if ($insight) $insight['related'] = array_values(array_unique(array_merge($insight['related'] ?? [], articles_for('country:' . $c['slug']))));
$faqs = merge_faqs($faqs, insight_faqs($insight));
$meta = meta([
    'title' => $title,
    'description' => "The best immigration CRM, case management, practice management and AI software for {$c['who']} in {$c['name']}, ranked with verified reviews.",
    'path' => $path,
    'markdown' => "$path.md",
    'jsonld' => [
        ['@type' => 'Article', '@id' => url($path) . '#article', 'headline' => $title, 'description' => $answer, 'url' => url($path),
            'datePublished' => '2026-10-16T00:00:00+00:00', 'dateModified' => gmdate('c', strtotime('first day of this month 00:00')),
            'author' => ['@id' => org_id()], 'publisher' => ['@id' => org_id()], 'isPartOf' => ['@id' => site_id()], 'about' => ['@type' => 'Country', 'name' => ucfirst($c['name'])]],
        breadcrumb_ld($crumbs),
        faq_ld($faqs),
    ],
]);
?>
<?= page_band($crumbs, e($title), 'Rankings for ' . e($c['who']) . ', built around ' . e($c['name']) . '\'s regulators, forms and programs.', updated_line(gmdate('Y-m-d H:i:s', strtotime('first day of this month 00:00')))) ?>
<div class="wrap">
  <div class="answer">
    <span class="label"><?= icon('sparkles', 14) ?>Quick answer</span>
    <p><strong>Top pick in each category for <?= e($where) ?>:</strong></p>
    <ol><?php foreach ($picks as [$cat, $r]): ?><li><?= e(category_seo($cat['slug'], $cat['name'])) ?>: <a href="/software/<?= e($r['s']['slug']) ?>"><strong><?= e($r['s']['name']) ?></strong></a></li><?php endforeach; ?></ol>
  </div>

  <?php foreach ($sections as $sec): $cat = $sec['cat']; $top = array_slice($sec['ranked'], 0, 3); ?>
  <section class="section" style="padding-top:40px" aria-labelledby="h-<?= e($cat['slug']) ?>">
    <div class="section-head">
      <div><h2 id="h-<?= e($cat['slug']) ?>">Best <?= e(category_seo_lower($cat['slug'], $cat['name'])) ?> in <?= e($where) ?></h2><p><?= count($sec['ranked']) ?> tools ranked</p></div>
      <a href="<?= e(guide_path($c['slug'], $cat['slug'])) ?>">See the full ranking</a>
    </div>
    <ol class="listing-rows"><?php foreach ($top as $i => $r) echo listing_row($r['s'], [], $i + 1, '<ul class="why">' . implode('', array_map(fn($w) => '<li>✓ ' . e($w) . '</li>', array_slice($r['why'], 0, 4))) . '</ul>'); ?></ol>
  </section>
  <?php endforeach; ?>

  <?= insight_html($insight) ?>

  <section class="section prose">
    <h2>Immigration practice in <?= e($c['name']) ?></h2>
    <?php foreach ($c['context'] as $para): ?><p><?= e($para) ?></p><?php endforeach; ?>
    <h2>What to look for</h2>
    <ul><?php foreach ($c['look_for'] as $l): ?><li><?= e($l) ?></li><?php endforeach; ?></ul>
  </section>

  <section class="section"><?= faq_html($faqs) ?></section>

  <section class="section">
    <h2 style="font-size:1.4rem">Other countries</h2>
    <div class="country-grid" style="margin-top:16px">
      <?php foreach (COUNTRIES as $cs => $cc): if ($cs === $c['slug']) continue; ?>
      <a class="country-card" href="/best/<?= $cs ?>"><span class="flag <?= $cc['class'] ?>"><?= $cc['code'] ?></span><div><strong><?= e($cc['short'] ?? $cc['name']) ?></strong><span>For <?= e($cc['who']) ?></span></div></a>
      <?php endforeach; ?>
    </div>
  </section>
</div>
