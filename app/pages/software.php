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
<div class="wrap">
  <?= breadcrumbs_html($crumbs) ?>
  <div class="profile-head">
    <?= logo_box($s['name'], $s['logo_url'], true) ?>
    <div>
      <?php if ($featured): ?><span class="featured-tag">★ Featured listing</span><?php endif; ?>
      <h1><?= e($s['name']) ?> reviews</h1>
      <p style="font-size:var(--step-1);color:var(--ink-2);margin-bottom:8px"><?= e($s['tagline']) ?></p>
      <p class="small muted" style="margin:0"><?= $s['vendor_name'] ? 'By ' . e($s['vendor_name']) . '. ' : '' ?><?= $s['claimed'] ? 'Listing managed by the vendor.' : 'Unclaimed listing — details compiled by our editors.' ?> Updated <?= e(format_date($s['updated_at'])) ?>.</p>
      <div class="profile-actions">
        <a class="btn stamp" href="/software/<?= e($s['slug']) ?>/review">Write a verified review</a>
        <a class="btn secondary" href="/compare?add=<?= e($s['slug']) ?>">Compare</a>
        <?php if ($s['website']): ?><a class="btn secondary" href="<?= e($s['website']) ?>" target="_blank" rel="noopener nofollow">Visit website</a><?php endif; ?>
        <?php if ($u && (int)$s['owner_id'] === (int)$u['id']): ?><a class="btn secondary" href="/vendor/<?= $id ?>">Manage listing</a>
        <?php elseif (!$s['claimed']): ?><a class="btn secondary" href="/software/<?= e($s['slug']) ?>/claim">Claim this listing</a><?php endif; ?>
      </div>
    </div>
  </div>

  <div class="summary-box"><p><?= $summary ?></p></div>

  <div class="profile-grid">
    <div class="stack">
      <section class="panel" aria-labelledby="rating-h">
        <h2 id="rating-h" class="sr-only"><?= e($s['name']) ?> rating</h2>
        <?php if ($n): ?>
          <div class="score">
            <span class="big"><?= number_format($avg, 1) ?></span>
            <div><?= stars($avg) ?><p class="small muted" style="margin:0"><?= $n ?> verified <?= $n === 1 ? 'review' : 'reviews' ?> · <?= round($agg['recommend'] * 100) ?>% would recommend</p></div>
          </div>
          <div class="bars">
            <?php foreach (['Ease of use' => $agg['ease'], 'Support' => $agg['support'], 'Value for money' => $agg['value'], 'Features' => $agg['features']] as $label => $v): ?>
            <div class="bar"><span><?= e($label) ?></span><span class="track"><span class="fill" style="width:<?= round($v / 5 * 100) ?>%;display:block"></span></span><span><?= number_format((float)$v, 1) ?></span></div>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <h3>No verified reviews yet</h3>
          <p class="muted">If your firm uses <?= e($s['name']) ?>, yours could be the first. We'll confirm your work email and either your LinkedIn or proof that you use it.</p>
          <a class="btn stamp" href="/software/<?= e($s['slug']) ?>/review">Review <?= e($s['name']) ?></a>
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
      <?php if ($features): ?>
        <h3 style="font-size:var(--step-0)">Key features</h3>
        <ul class="tag-list"><?php foreach ($features as $f): ?><li><?= e($f) ?></li><?php endforeach; ?></ul>
      <?php endif; ?>
    </aside>
  </div>
</div>
