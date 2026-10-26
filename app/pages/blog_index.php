<?php
$list = articles();
$crumbs = [['Home', '/'], ['Blog', '/blog']];
$meta = meta([
    'title' => 'Immigration software blog: buying guides and answers',
    'description' => 'Practical answers for immigration law firms, RCICs and corporate immigration teams: choosing software, comparing providers, USCIS e-filing and case tracking.',
    'path' => '/blog',
    'jsonld' => [
        webpage_ld('/blog', SITE_NAME . ' blog', ['description' => 'Buying guides and answers for immigration practitioners.'], 'CollectionPage'),
        breadcrumb_ld($crumbs),
        item_list_ld(SITE_NAME . ' blog', array_map(fn($a) => [$a['title'], article_url($a['slug'])], $list)),
    ],
]);
?>
<?= page_band($crumbs, 'Blog', 'Straight answers to the questions immigration practitioners and HR teams ask about software and providers. Researched from vendor documentation and official sources; never sponsored.') ?>
<div class="wrap">
  <ul class="post-grid">
    <?php foreach ($list as $a): ?>
    <li class="post-card">
      <?php if (!empty($a['category'])): ?><span class="eyebrow"><?= e($a['category']) ?></span><?php endif; ?>
      <h2><a href="<?= e(article_url($a['slug'])) ?>"><?= e($a['title']) ?></a></h2>
      <p><?= e($a['dek']) ?></p>
      <p class="small muted"><time datetime="<?= e($a['updated']) ?>"><?= e(format_date($a['updated'])) ?></time></p>
    </li>
    <?php endforeach; ?>
  </ul>
</div>
