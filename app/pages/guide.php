<?php
$cat = category_by_slug($params[0]);
$c = country($params[1]);
if (!$cat || !$c) not_found();
$g = guide_data($c, $cat);
if (!$g['ranked']) not_found();
['ranked' => $ranked, 'seo' => $seo, 'seoLower' => $seoLower, 'top' => $top, 'faqs' => $faqs] = $g;
$path = guide_path($c['slug'], $cat['slug']);
$where = $c['in'];
$updated = max(array_map(fn($r) => strtotime($r['s']['updated_at']), $ranked));
$updatedStr = gmdate('Y-m-d H:i:s', max($updated, strtotime('first day of this month 00:00')));
$title = "Best $seo in $where (" . year() . ')';
$crumbs = [['Home', '/'], ['Best by country', '/best'], [$where, guide_path($c['slug'])], [$seo, $path]];
$compareTop = array_map(fn($r) => $r['s']['slug'], array_slice($ranked, 0, min(4, count($ranked))));
$lookFor = array_merge($c['look_for'], CATEGORY_LOOK_FOR[$cat['slug']] ?? []);
$otherCats = array_filter(country_categories($c), fn($x) => $x['cat']['slug'] !== $cat['slug']);
$otherCountries = array_filter(array_keys(COUNTRIES), fn($cs) => $cs !== $c['slug'] && rank_for_country(country($cs), $cat['slug']));

$meta = meta([
    'title' => $title,
    'description' => "The best $seoLower for {$c['who']} in {$c['name']}, ranked on fit and verified reviews: " . implode(', ', $g['topNames']) . ' and more. Compare features, pricing and pros and cons.',
    'path' => $path,
    'image' => url("/category/{$cat['slug']}/og.png"),
    'modified' => $updatedStr,
    'markdown' => "$path.md",
    'jsonld' => [
        ['@type' => 'Article', '@id' => url($path) . '#article', 'headline' => $title, 'description' => $g['answer'], 'url' => url($path),
            'datePublished' => '2026-10-16T00:00:00+00:00', 'dateModified' => iso_date($updatedStr), 'inLanguage' => 'en',
            'author' => ['@id' => org_id()], 'publisher' => ['@id' => org_id()], 'isPartOf' => ['@id' => site_id()],
            'about' => [['@type' => 'Thing', 'name' => $seo], ['@type' => 'Country', 'name' => ucfirst($c['name'])]],
            'mainEntityOfPage' => url($path), 'image' => url("/category/{$cat['slug']}/og.png")],
        ['@type' => 'ItemList', 'name' => $title, 'itemListOrder' => 'https://schema.org/ItemListOrderDescending', 'numberOfItems' => count($ranked),
            'itemListElement' => array_map(fn($r, $i) => ['@type' => 'ListItem', 'position' => $i + 1, 'name' => $r['s']['name'], 'url' => url("/software/{$r['s']['slug']}")], $ranked, array_keys($ranked))],
        breadcrumb_ld($crumbs),
        faq_ld($faqs),
    ],
]);
?>
<?= page_band($crumbs, e("Best $seo in $where"), 'Ranked for ' . e($c['who']) . ' using verified practitioner reviews and fit with how immigration work is done in ' . e($c['name']) . '.', updated_line($updatedStr)) ?>
<div class="wrap">
  <div class="guide-layout">
    <article>
      <div class="answer">
        <span class="label"><?= icon('sparkles', 14) ?>Quick answer</span>
        <p><strong>The best <?= e($seoLower) ?> in <?= e($where) ?></strong> for <?= e($c['who']) ?> in <?= year() ?>, according to ImmigrationCRM.shop:</p>
        <ol><?php foreach ($top as $r): ?><li><a href="/software/<?= e($r['s']['slug']) ?>"><strong><?= e($r['s']['name']) ?></strong></a> — <?= e(lc_lead(ranked_reason($r))) ?>.</li><?php endforeach; ?></ol>
      </div>

      <section id="ranking" aria-labelledby="ranking-h">
        <h2 id="ranking-h">The full ranking</h2>
        <ol class="listing-rows" style="margin-top:20px">
          <?php foreach ($ranked as $i => $r):
            $why = '<ul class="why">' . implode('', array_map(fn($w) => '<li>✓ ' . e($w) . '</li>', $r['why'])) . '</ul>';
            echo listing_row($r['s'], [], $i + 1, $why);
          endforeach; ?>
        </ol>
        <?php if (count($compareTop) >= 2): ?>
        <p style="margin-top:20px"><a class="btn secondary" href="<?= e(compare_path($compareTop)) ?>"><?= icon('compare', 18) ?>Compare the top <?= count($compareTop) ?> side by side</a></p>
        <?php endif; ?>
      </section>

      <section id="choose" class="section prose" aria-labelledby="choose-h" style="padding-top:48px">
        <h2 id="choose-h">What to look for in <?= e($seoLower) ?> in <?= e($where) ?></h2>
        <ul><?php foreach ($lookFor as $l): ?><li><?= e($l) ?></li><?php endforeach; ?></ul>
      </section>

      <section id="context" class="prose" aria-labelledby="context-h">
        <h2 id="context-h">Immigration practice in <?= e($c['name']) ?>: what your software must handle</h2>
        <?php foreach ($c['context'] as $para): ?><p><?= e($para) ?></p><?php endforeach; ?>
      </section>

      <section id="method" class="prose" aria-labelledby="method-h">
        <h2 id="method-h">How we rank</h2>
        <p><?= e(METHODOLOGY) ?></p>
        <p>Every review on ImmigrationCRM.shop comes from a practitioner with a verified work email plus LinkedIn or moderator-checked proof of use. <a href="/how-we-verify">Read our verification policy</a>.</p>
      </section>

      <section id="faq" class="section" style="padding-top:40px"><?= faq_html($faqs) ?></section>
    </article>

    <aside class="toc" aria-label="On this page">
      <h2>On this page</h2>
      <ol>
        <li><a href="#ranking">The full ranking</a></li>
        <li><a href="#choose">What to look for</a></li>
        <li><a href="#context">Practice in <?= e($where) ?></a></li>
        <li><a href="#method">How we rank</a></li>
        <li><a href="#faq">FAQ</a></li>
      </ol>
      <?php if ($otherCats): ?>
      <h2 style="margin-top:20px">More for <?= e($where) ?></h2>
      <ol style="list-style:none;padding:0"><?php foreach ($otherCats as $oc): ?><li><a href="<?= e(guide_path($c['slug'], $oc['cat']['slug'])) ?>"><?= e(category_seo($oc['cat']['slug'], $oc['cat']['name'])) ?></a></li><?php endforeach; ?></ol>
      <?php endif; ?>
      <?php if ($otherCountries): ?>
      <h2 style="margin-top:20px">Other countries</h2>
      <ol style="list-style:none;padding:0"><?php foreach ($otherCountries as $cs): ?><li><a href="<?= e(guide_path($cs, $cat['slug'])) ?>"><?= e(COUNTRIES[$cs]['short'] ?? COUNTRIES[$cs]['name']) ?></a></li><?php endforeach; ?></ol>
      <?php endif; ?>
    </aside>
  </div>
</div>
