<?php
$c = category_by_slug($params[0]);
if (!$c) not_found();
$results = search_software(['category' => $c['slug'], 'q' => get('q'), 'region' => get('region')]);
$seo = category_seo($c['slug'], $c['name']);
$seoLower = category_seo_lower($c['slug'], $c['name']);
$rated = array_values(array_filter($results, fn($s) => (int)$s['review_count'] > 0));
usort($rated, fn($a, $b) => [$b['rating_avg'], $b['review_count']] <=> [$a['rating_avg'], $a['review_count']]);
$top = array_column(array_slice($results, 0, 5), 'name');
$pairs = array_slice(array_values(array_filter(popular_pairs(), fn($p) => $p['cat'] === $c['slug'])), 0, 18);
$faqs = [
    ["What is the best $seoLower in " . year() . '?', $rated
        ? "Based on verified reviews on ImmigrationCRM.shop, the highest-rated tools are " . implode(', ', array_map(fn($s) => sprintf('%s (%.1f/5, %d reviews)', $s['name'], $s['rating_avg'], $s['review_count']), array_slice($rated, 0, 3))) . '. The best fit depends on your market and practice size.'
        : 'Widely used options include ' . implode(', ', $top) . '. Verified ratings will appear as practitioners review them; compare features and markets served in the meantime.'],
    ["What is $seoLower?", $c['description']],
    ['How many tools are listed in this category?', 'ImmigrationCRM.shop lists ' . count($results) . ' ' . lower_words($c['name']) . ' tools. Every rating comes from a practitioner with a verified work email plus LinkedIn or proof of use.'],
    ["How do I choose $seoLower?", "Check that it supports the government forms and programs you file (for example USCIS, IRCC or Home Affairs), the markets you serve, client portal and document collection, billing, and integrations with tools you already use. Then compare two to four options side by side and read verified reviews from firms like yours."],
];
$crumbs = [['Home', '/'], ['Software', '/software'], [$c['name'], "/category/{$c['slug']}"]];
$meta = meta([
    'title' => "Best $seo (" . year() . ')',
    'description' => $c['description'] . ' Compare by verified ratings, features and markets served.',
    'path' => "/category/{$c['slug']}",
    'image' => url("/category/{$c['slug']}/og.png"),
    'markdown' => "/category/{$c['slug']}.md",
    'noindex' => (bool)(get('q') || get('region') || get('sort')),
    'jsonld' => [
        webpage_ld("/category/{$c['slug']}", "Best $seo", ['description' => $c['description']], 'CollectionPage'),
        breadcrumb_ld($crumbs),
        item_list_ld("Best $seo", array_map(fn($s) => [$s['name'], "/software/{$s['slug']}"], $results)),
        faq_ld($faqs),
    ],
]);
?>
<?= page_band($crumbs, 'Best ' . e($seoLower), e($c['description']), updated_line(now())) ?>
<div class="wrap">
  <div class="answer"><span class="label"><?= icon('sparkles', 14) ?>Quick answer</span><p><?= e($faqs[0][1]) ?></p></div>
  <?php $guides = array_filter(COUNTRIES, fn($cc) => (bool)rank_for_country(country(array_search($cc, COUNTRIES, true)), $c['slug'])); if ($guides): ?>
  <div class="tabs" aria-label="By country"><?php foreach ($guides as $cs => $cc): ?><a class="chip" href="<?= e(guide_path($cs, $c['slug'])) ?>"><span class="flag <?= $cc['class'] ?>" style="width:22px;height:22px;border-radius:6px;font-size:9px"><?= $cc['code'] ?></span>Best in <?= e($cc['short'] ?? $cc['name']) ?></a><?php endforeach; ?></div>
  <?php endif; ?>
  <?= directory_body($c['slug']) ?>
  <?php if ($pairs): ?>
  <section class="section" aria-labelledby="cat-cmp">
    <h2 id="cat-cmp" style="font-size:var(--step-2)">Popular <?= e(lower_words($c['name'])) ?> comparisons</h2>
    <ul class="link-grid"><?php foreach ($pairs as $p): ?><li><a href="<?= e(compare_path([$p['a'], $p['b']])) ?>"><?= e("{$p['an']} vs {$p['bn']}") ?></a></li><?php endforeach; ?></ul>
  </section>
  <?php endif; ?>
  <section class="section"><?= faq_html($faqs) ?></section>
</div>
