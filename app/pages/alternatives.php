<?php
$s = software_by_slug($params[0]);
if (!$s) not_found();
$alts = competitors_of((int)$s['id'], 12);
$cats = category_names_for(array_merge([$s['id']], array_column($alts, 'id')));
$mainCat = $cats[$s['id']][0] ?? 'immigration software';
$rated = array_values(array_filter($alts, fn($a) => (int)$a['review_count'] > 0));
$top5 = implode(', ', array_column(array_slice($alts, 0, 5), 'name'));
$faqs = [
    ["What are the best alternatives to {$s['name']}?", "The closest alternatives to {$s['name']} in " . lower_words($mainCat) . " are $top5." . ($rated ? sprintf(' The highest rated by verified users is %s (%.1f/5).', $rated[0]['name'], $rated[0]['rating_avg']) : '')],
    ["Why do firms look for {$s['name']} alternatives?", "Common reasons are pricing, support for a specific country's immigration forms (for example IRCC, USCIS or Home Affairs), client portal needs, AI drafting and integrations. Compare features side by side before switching."],
    ['How are these alternatives ranked?', "Alternatives that share the most categories with {$s['name']} appear first, then by number of verified reviews and average rating. Featured placement does not change this list."],
];
$crumbs = [['Home', '/'], [$s['name'], "/software/{$s['slug']}"], ['Alternatives', "/software/{$s['slug']}/alternatives"]];
$meta = meta([
    'title' => "Best {$s['name']} alternatives & competitors (" . year() . ')',
    'description' => "Compare the top alternatives to {$s['name']} with verified reviews from immigration practitioners: ratings, features, markets served and side-by-side comparisons.",
    'path' => "/software/{$s['slug']}/alternatives",
    'image' => url("/software/{$s['slug']}/og.png"),
    'jsonld' => [breadcrumb_ld($crumbs), item_list_ld("{$s['name']} alternatives", array_map(fn($a) => [$a['name'], "/software/{$a['slug']}"], $alts)), faq_ld($faqs)],
]);
?>
<div class="wrap">
  <?= breadcrumbs_html($crumbs) ?>
  <div class="page-head"><h1><?= e($s['name']) ?> alternatives</h1><p><?= count($alts) ?> tools that do the same job as <?= e($s['name']) ?>, with verified reviews from immigration practitioners.</p></div>
  <div class="summary-box"><p><strong><?= e($s['name']) ?></strong> is <?= e(lcfirst_tagline($s['tagline'])) ?>. The top alternatives are <?= e($top5) ?>.</p></div>
  <ol class="listing-rows">
    <?php foreach ($alts as $a): ?>
    <li class="listing"><?= logo_box($a['name'], $a['logo_url']) ?><div>
      <h2 style="font-size:var(--step-1);margin:0"><a href="/software/<?= e($a['slug']) ?>"><?= e($a['name']) ?></a></h2>
      <p class="tagline"><?= e($a['tagline']) ?></p>
      <div class="meta"><?= rating_inline((float)$a['rating_avg'], (int)$a['review_count']) ?><span><?= e(implode(', ', $cats[$a['id']] ?? [])) ?></span>
        <a class="compare-tag" href="<?= e(compare_path([$s['slug'], $a['slug']])) ?>">Compare with <?= e($s['name']) ?></a></div>
    </div></li>
    <?php endforeach; ?>
  </ol>
  <section class="section"><?= faq_html($faqs) ?></section>
</div>
