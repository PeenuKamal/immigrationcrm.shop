<?php
$raw = $params[0];
$slugs = parse_compare($raw);
if (count($slugs) < 2) not_found();
if (count($slugs) > MAX_COMPARE) redirect(compare_path(array_slice($slugs, 0, MAX_COMPARE)), 301);
$items = load_compare($slugs);
if (count($items) < 2) not_found();
$canonical = compare_path(array_map(fn($i) => $i['s']['slug'], $items));
if ("/compare/$raw" !== $canonical) redirect($canonical, 301);

$names = array_map(fn($i) => $i['s']['name'], $items);
$t = implode(' vs ', $names);
$faqs = compare_faqs($items);
$collect = function (string $field) use ($items) {
    $all = [];
    foreach ($items as $i) $all = array_merge($all, json_list($i['s'][$field]));
    $all = array_values(array_unique($all));
    natcasesort($all);
    return array_values($all);
};
$features = $collect('features');
$markets = $collect('regions');
$audiences = $collect('audiences');
$updated = max(array_map(fn($i) => strtotime($i['s']['updated_at']), $items));
$others = array_slice(array_values(array_filter(competitors_of((int)$items[0]['s']['id'], 8), fn($c) => !in_array($c['slug'], $slugs, true))), 0, 6);
$crumbs = [['Home', '/'], ['Compare', '/compare'], [$t, $canonical]];
$cols = count($items) + 1;
$fmt = fn($v, $has) => $has ? number_format((float)$v, 1) : '—';
$yes = fn(bool $v) => $v ? '<td class="yes">✓ Yes</td>' : '<td class="no">—</td>';

$meta = meta([
    'title' => "$t: side-by-side comparison (" . year() . ')',
    'description' => "$t compared on verified user ratings, features, pricing, markets served and real pros and cons from immigration practitioners.",
    'path' => $canonical,
    'image' => url("$canonical/og.png"),
    'modified' => gmdate('Y-m-d H:i:s', $updated),
    'jsonld' => array_merge(
        [webpage_ld($canonical, "$t comparison", ['dateModified' => gmdate('c', $updated), 'about' => array_map(fn($i) => ['@id' => url("/software/{$i['s']['slug']}#software")], $items)]), breadcrumb_ld($crumbs)],
        array_map(fn($i) => software_ld($i['s'], array_column($i['cats'], 'name')), $items),
        [faq_ld($faqs)]
    ),
]);
?>
<?= page_band($crumbs, e($t), 'Side-by-side comparison using verified practitioner reviews and listing data.', updated_line(gmdate('Y-m-d H:i:s', $updated))) ?>
<div class="wrap">
  <div class="answer"><span class="label"><?= icon('sparkles', 14) ?>At a glance</span>
    <?php foreach ($items as $i): $s = $i['s']; $reg = array_slice(json_list($s['regions']), 0, 4); ?>
    <p><strong><?= e($s['name']) ?></strong> is <?= e(lcfirst_tagline($s['tagline'])) ?><?= $reg ? ', serving ' . e(implode(', ', $reg)) : '' ?>.
      <?= (int)$s['review_count'] ? sprintf('Rated %.1f/5 from %d verified review%s.', $s['rating_avg'], $s['review_count'], $s['review_count'] > 1 ? 's' : '') : 'No verified reviews yet.' ?></p>
    <?php endforeach; ?>
  </div>

  <div class="compare-scroll">
    <table class="compare">
      <caption class="sr-only"><?= e($t) ?> feature and rating comparison</caption>
      <thead><tr><th scope="col"><span class="sr-only">Attribute</span></th>
        <?php foreach ($items as $i): ?><th scope="col"><div style="display:flex;gap:12px;align-items:center"><?= logo_box($i['s']['name'], $i['s']['logo_url']) ?><a href="/software/<?= e($i['s']['slug']) ?>"><?= e($i['s']['name']) ?></a></div></th><?php endforeach; ?>
      </tr></thead>
      <tbody>
        <tr class="group"><th colspan="<?= $cols ?>" scope="colgroup">Verified ratings</th></tr>
        <tr><th scope="row">Overall</th><?php foreach ($items as $i): $n = (int)$i['s']['review_count']; ?><td><?= $n ? '<span class="rating">' . stars((float)$i['s']['rating_avg']) . ' ' . number_format((float)$i['s']['rating_avg'], 1) . '</span>' : '<span class="no-reviews">No reviews yet</span>' ?></td><?php endforeach; ?></tr>
        <tr><th scope="row">Verified reviews</th><?php foreach ($items as $i): ?><td><?= (int)$i['s']['review_count'] ?></td><?php endforeach; ?></tr>
        <tr><th scope="row">Would recommend</th><?php foreach ($items as $i): ?><td><?= (int)$i['s']['review_count'] ? round($i['agg']['recommend'] * 100) . '%' : '—' ?></td><?php endforeach; ?></tr>
        <?php foreach (['Ease of use' => 'ease', 'Customer support' => 'support', 'Value for money' => 'value', 'Immigration features' => 'features'] as $label => $k): ?>
        <tr><th scope="row"><?= e($label) ?></th><?php foreach ($items as $i): ?><td><?= $fmt($i['agg'][$k], (int)$i['s']['review_count'] > 0) ?></td><?php endforeach; ?></tr>
        <?php endforeach; ?>

        <tr class="group"><th colspan="<?= $cols ?>" scope="colgroup">Basics</th></tr>
        <tr><th scope="row">Category</th><?php foreach ($items as $i): ?><td><?= e(implode(', ', array_column($i['cats'], 'name'))) ?></td><?php endforeach; ?></tr>
        <tr><th scope="row">Vendor</th><?php foreach ($items as $i): ?><td><?= e($i['s']['vendor_name'] ?: $i['s']['name']) ?></td><?php endforeach; ?></tr>
        <tr><th scope="row">Deployment</th><?php foreach ($items as $i): ?><td><?= e($i['s']['deployment'] ?: '—') ?></td><?php endforeach; ?></tr>
        <tr><th scope="row">Pricing</th><?php foreach ($items as $i): ?><td><?= e($i['s']['pricing_note'] ?: 'Contact vendor') ?></td><?php endforeach; ?></tr>

        <?php foreach (['Markets served' => [$markets, 'regions'], 'Built for' => [$audiences, 'audiences'], 'Key features listed' => [$features, 'features']] as $group => [$vals, $field]): if (!$vals) continue; ?>
        <tr class="group"><th colspan="<?= $cols ?>" scope="colgroup"><?= e($group) ?></th></tr>
          <?php foreach ($vals as $v): ?>
          <tr><th scope="row"><?= e($v) ?></th><?php foreach ($items as $i) echo $yes(in_array($v, json_list($i['s'][$field]), true)); ?></tr>
          <?php endforeach; ?>
        <?php endforeach; ?>
        <tr><th scope="row"><span class="sr-only">Actions</span></th><?php foreach ($items as $i): ?><td><a class="btn small secondary" href="/software/<?= e($i['s']['slug']) ?>/review">Review <?= e($i['s']['name']) ?></a></td><?php endforeach; ?></tr>
      </tbody>
    </table>
  </div>
  <p class="small muted" style="margin-top:8px">“—” means the feature isn't listed on the product's profile, not necessarily that it's missing. Vendors keep their own listings up to date.</p>

  <section class="section" aria-labelledby="what-users-say">
    <h2 id="what-users-say">What verified users say</h2>
    <div class="grid-2" style="gap:24px">
      <?php foreach ($items as $i): ?>
      <div class="panel">
        <h3><?= e($i['s']['name']) ?></h3>
        <?php if ($i['latest']):
          $pros = array_slice(array_merge(...array_map(fn($r) => array_slice(lines($r['pros']), 0, 2), $i['latest'])), 0, 4);
          $cons = array_slice(array_merge(...array_map(fn($r) => array_slice(lines($r['cons']), 0, 2), $i['latest'])), 0, 4); ?>
          <div class="pc" style="grid-template-columns:1fr">
            <div><h4>Most recent pros</h4><ul><?php foreach ($pros as $l): ?><li><?= e($l) ?></li><?php endforeach; ?></ul></div>
            <div class="cons"><h4>Most recent cons</h4><ul><?php foreach ($cons as $l): ?><li><?= e($l) ?></li><?php endforeach; ?></ul></div>
          </div>
        <?php else: ?><p class="muted small">No verified reviews yet.</p><?php endif; ?>
        <a href="/software/<?= e($i['s']['slug']) ?>#reviews-h">Read all <?= e($i['s']['name']) ?> reviews</a>
      </div>
      <?php endforeach; ?>
    </div>
  </section>

  <section class="section"><?= faq_html($faqs) ?></section>

  <?php if ($others): ?>
  <section class="section" aria-labelledby="more-cmp">
    <h2 id="more-cmp" style="font-size:var(--step-2)">More comparisons</h2>
    <ul class="link-grid">
      <?php foreach ($others as $o) foreach ($items as $i): ?><li><a href="<?= e(compare_path([$i['s']['slug'], $o['slug']])) ?>"><?= e("{$i['s']['name']} vs {$o['name']}") ?></a></li><?php endforeach; ?>
      <?php if (count($items) < MAX_COMPARE): ?><li><a href="<?= e(compare_path(array_merge($slugs, [$others[0]['slug']]))) ?>">Add <?= e($others[0]['name']) ?> to this comparison</a></li><?php endif; ?>
    </ul>
    <p style="margin-top:16px"><a href="/compare">Build your own comparison</a></p>
  </section>
  <?php endif; ?>
</div>
