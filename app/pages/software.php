<?php
$s = software_by_slug($params[0]);
if (!$s) not_found();
$id = (int)$s['id'];
$list = published_reviews_for($id);
$cats = categories_of($id);
$agg = sub_ratings($id);
$u = current_user();
$competitors = competitors_of($id, 8);
$faqs = profile_faqs($s, $cats, $competitors);
$featured = is_featured($s['featured_until']);
$regions = json_list($s['regions']);
$aud = json_list($s['audiences']);
$features = json_list($s['features']);
$n = (int)$s['review_count'];
$avg = (float)$s['rating_avg'];
$crumbs = array_merge([['Home', '/']], $cats ? [[$cats[0]['name'], "/category/{$cats[0]['slug']}"]] : [['Software', '/software']], [[$s['name'], "/software/{$s['slug']}"]]);
$meta = meta([
    'title' => "{$s['name']} reviews, features & pricing (" . year() . ')',
    'description' => $s['tagline'] . ($n ? sprintf(' Rated %.1f/5 from %d verified reviews.', $avg, $n) : '') . ' Verified pros and cons from immigration practitioners, features, markets served and alternatives.',
    'path' => "/software/{$s['slug']}",
    'image' => url("/software/{$s['slug']}/og.png"),
    'modified' => $s['updated_at'],
    'markdown' => "/software/{$s['slug']}.md",
    'jsonld' => [
        webpage_ld("/software/{$s['slug']}", "{$s['name']} reviews", ['dateModified' => iso_date($s['updated_at']), 'mainEntity' => ['@id' => url("/software/{$s['slug']}#software")]]),
        software_ld($s, array_column($cats, 'name'), $list),
        breadcrumb_ld($crumbs),
        faq_ld($faqs),
    ],
]);
$summary = "<strong>" . e($s['name']) . '</strong> is ' . e(lcfirst_tagline($s['tagline']))
    . ($cats ? ' in the ' . e(lower_words($cats[0]['name'])) . ' category' : '')
    . ($regions ? ', serving ' . e(implode(', ', $regions)) : '') . '. '
    . ($n ? sprintf('It is rated %.1f out of 5 from %d verified review%s, and %d%% of reviewers would recommend it.', $avg, $n, $n > 1 ? 's' : '', round($agg['recommend'] * 100)) : 'It has no verified reviews yet.')
    . ($competitors ? ' Main alternatives: ' . e(implode(', ', array_column(array_slice($competitors, 0, 3), 'name'))) . '.' : '');
?>
<div class="page-band"><div class="wrap">
  <?= breadcrumbs_html($crumbs) ?>
  <div class="product-hero">
    <?= logo_box($s['name'], $s['logo_url'], true) ?>
    <div>
      <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:10px">
        <?php if ($featured): ?><span class="featured-tag">★ Featured</span><?php endif; ?>
        <?php foreach ($cats as $c): ?><a class="badge" href="/category/<?= e($c['slug']) ?>"><?= e($c['name']) ?></a><?php endforeach; ?>
        <?php if ($s['claimed']): ?><span class="badge verified"><?= icon('shield', 13) ?>Vendor-managed listing</span><?php endif; ?>
      </div>
      <h1><?= e($s['name']) ?> reviews</h1>
      <p class="tagline"><?= e($s['tagline']) ?></p>
      <p class="small muted" style="margin:0"><?= $s['vendor_name'] ? 'By ' . e($s['vendor_name']) . ' · ' : '' ?><?= $s['claimed'] ? '' : 'Unclaimed listing, compiled by our editors · ' ?>Updated <?= e(format_date($s['updated_at'])) ?></p>
      <div class="product-actions">
        <a class="btn green" href="/software/<?= e($s['slug']) ?>/review"><?= icon('shield', 18) ?>Write a verified review</a>
        <a class="btn secondary" href="/compare?add=<?= e($s['slug']) ?>"><?= icon('compare', 18) ?>Compare</a>
        <?php if ($s['website']): ?><a class="btn secondary" href="<?= e($s['website']) ?>" target="_blank" rel="noopener nofollow">Visit website</a><?php endif; ?>
        <?php if ($u && (int)$s['owner_id'] === (int)$u['id']): ?><a class="btn secondary" href="/vendor/<?= $id ?>">Manage listing</a>
        <?php elseif (!$s['claimed']): ?><a class="btn secondary" href="/software/<?= e($s['slug']) ?>/claim">Claim this listing</a><?php endif; ?>
      </div>
    </div>
    <div class="score-card">
      <?php if ($n): ?>
        <div class="big"><?= number_format($avg, 1) ?></div><?= stars($avg) ?>
        <p class="small muted" style="margin:6px 0 0"><?= $n ?> verified <?= $n === 1 ? 'review' : 'reviews' ?><br><?= round($agg['recommend'] * 100) ?>% would recommend</p>
      <?php else: ?>
        <span class="badge gold" style="margin-bottom:8px">Be the first</span>
        <p class="small muted" style="margin:0">No verified reviews yet.<br><a href="/software/<?= e($s['slug']) ?>/review">Review <?= e($s['name']) ?></a></p>
      <?php endif; ?>
    </div>
  </div>
</div></div>

<div class="wrap">
  <div class="answer"><span class="label"><?= icon('sparkles', 14) ?>At a glance</span><p><?= $summary ?></p></div>

  <div class="profile-grid">
    <div class="stack">
      <section class="panel" aria-labelledby="rating-h">
        <h2 id="rating-h" class="sr-only"><?= e($s['name']) ?> rating</h2>
        <?php if ($n): ?>
          <h3 style="font-size:1.05rem">Rating breakdown</h3>
          <div class="bars">
            <?php foreach (['Ease of use' => $agg['ease'], 'Support' => $agg['support'], 'Value for money' => $agg['value'], 'Features' => $agg['features']] as $label => $v): ?>
            <div class="bar"><span><?= e($label) ?></span><span class="track"><span class="fill" style="width:<?= round($v / 5 * 100) ?>%;display:block"></span></span><span><?= number_format((float)$v, 1) ?></span></div>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <h3>No verified reviews yet</h3>
          <p class="muted">If your firm uses <?= e($s['name']) ?>, yours could be the first. We'll confirm your work email and either your LinkedIn or proof that you use it.</p>
          <a class="btn green" href="/software/<?= e($s['slug']) ?>/review">Review <?= e($s['name']) ?></a>
        <?php endif; ?>
      </section>

      <section aria-labelledby="about">
        <h2 id="about">About <?= e($s['name']) ?></h2>
        <p><?= e($s['description']) ?></p>
        <?php if ($s['pricing_note']): ?><p><strong>Pricing:</strong> <?= e($s['pricing_note']) ?></p><?php endif; ?>
      </section>

      <section aria-labelledby="reviews-h">
        <h2 id="reviews-h">Verified <?= e($s['name']) ?> reviews</h2>
        <?php if ($list): ?><div class="stack"><?php foreach ($list as $r) echo review_card($r); ?></div>
        <?php else: ?><p class="muted">Reviews appear here once they pass verification.</p><?php endif; ?>
      </section>

      <?php if ($competitors): ?>
      <section aria-labelledby="cmp-h">
        <h2 id="cmp-h">Compare <?= e($s['name']) ?></h2>
        <ul class="link-grid"><?php foreach ($competitors as $c): ?><li><a href="<?= e(compare_path([$s['slug'], $c['slug']])) ?>"><?= e("{$s['name']} vs {$c['name']}") ?></a></li><?php endforeach; ?></ul>
        <p style="margin-top:16px"><a href="/software/<?= e($s['slug']) ?>/alternatives">See all <?= e($s['name']) ?> alternatives</a></p>
      </section>
      <?php endif; ?>

      <?= faq_html($faqs, "{$s['name']} FAQ") ?>
    </div>

    <aside class="panel" aria-label="Details" style="align-self:start">
      <dl class="facts">
        <?php if ($cats): ?><dt>Category</dt><dd><?= implode(', ', array_map(fn($c) => '<a href="/category/' . e($c['slug']) . '">' . e($c['name']) . '</a>', $cats)) ?></dd><?php endif; ?>
        <?php if ($s['vendor_name']): ?><dt>Vendor</dt><dd><?= e($s['vendor_name']) ?></dd><?php endif; ?>
        <?php if ($s['deployment']): ?><dt>Deployment</dt><dd><?= e($s['deployment']) ?></dd><?php endif; ?>
        <?php if ($regions): ?><dt>Markets served</dt><dd><?= e(implode(', ', $regions)) ?></dd><?php endif; ?>
        <?php if ($aud): ?><dt>Built for</dt><dd><?= e(implode(', ', $aud)) ?></dd><?php endif; ?>
        <dt>Last updated</dt><dd><time datetime="<?= e(iso_date($s['updated_at'])) ?>"><?= e(format_date($s['updated_at'])) ?></time></dd>
      </dl>
      <?php $inGuides = array_filter(COUNTRIES, fn($cc) => in_array($cc['region'], $regions, true)); if ($inGuides && $cats): ?>
        <h3 style="font-size:var(--step-0)">Ranked in our guides</h3>
        <ul style="list-style:none;padding:0;margin:0 0 18px;display:grid;gap:6px;font-size:14.5px"><?php foreach ($inGuides as $cs => $cc): ?><li><a href="<?= e(guide_path($cs, $cats[0]['slug'])) ?>">Best <?= e(category_seo_lower($cats[0]['slug'], $cats[0]['name'])) ?> in <?= e($cc['short'] ?? $cc['name']) ?></a></li><?php endforeach; ?></ul>
      <?php endif; ?>
      <?php if ($features): ?>
        <h3 style="font-size:var(--step-0)">Key features</h3>
        <ul class="tag-list"><?php foreach ($features as $f): ?><li><?= e($f) ?></li><?php endforeach; ?></ul>
      <?php endif; ?>
    </aside>
  </div>
</div>
