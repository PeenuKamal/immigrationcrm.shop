<?php
$a = article($params[0]);
if (!$a) not_found();
$path = article_url($a['slug']);
$crumbs = [['Home', '/'], ['Blog', '/blog'], [$a['title'], $path]];
$words = str_word_count(strip_tags(blocks_html($a['blocks'])) . ' ' . $a['answer']);
$minutes = max(2, (int)round($words / 220));
$mentioned = array_values(array_filter(array_map('software_by_slug', $a['software'] ?? [])));
$toc = array_values(array_filter($a['blocks'], fn($b) => $b[0] === 'h2' && isset($b[2])));
$more = array_slice(array_values(array_filter(articles(), fn($x) => $x['slug'] !== $a['slug'])), 0, 3);

$meta = meta([
    'title' => $a['title'],
    'description' => md_plain($a['answer']),
    'path' => $path,
    'markdown' => "$path.md",
    'modified' => $a['updated'] . ' 00:00:00',
    'jsonld' => [
        [
            '@type' => 'BlogPosting', '@id' => url($path) . '#article', 'headline' => $a['title'], 'description' => md_plain($a['dek']),
            'abstract' => md_plain($a['answer']), 'url' => url($path), 'mainEntityOfPage' => url($path),
            'datePublished' => $a['published'] . 'T00:00:00+00:00', 'dateModified' => $a['updated'] . 'T00:00:00+00:00',
            'author' => ['@type' => 'Organization', '@id' => org_id(), 'name' => SITE_NAME . ' editorial team'],
            'publisher' => ['@id' => org_id()], 'isPartOf' => ['@id' => site_id()], 'inLanguage' => 'en', 'wordCount' => $words,
            'articleSection' => $a['category'] ?? 'Guides', 'image' => url('/og.png'),
            'mentions' => array_map(fn($s) => ['@type' => 'SoftwareApplication', '@id' => url("/software/{$s['slug']}#software"), 'name' => $s['name']], $mentioned),
            'citation' => array_map(fn($src) => ['@type' => 'CreativeWork', 'name' => $src[0], 'url' => $src[1]], $a['sources'] ?? []),
        ],
        breadcrumb_ld($crumbs),
        !empty($a['faqs']) ? faq_ld(array_map(fn($f) => [$f[0], md_plain($f[1])], $a['faqs'])) : null,
    ],
]);
?>
<div class="page-band"><div class="wrap">
  <?= breadcrumbs_html($crumbs) ?>
  <?php if (!empty($a['category'])): ?><span class="eyebrow"><?= e($a['category']) ?></span><?php endif; ?>
  <h1><?= e($a['title']) ?></h1>
  <p class="lede"><?= e($a['dek']) ?></p>
  <p class="updated"><?= icon('clock', 15) ?>Updated <time datetime="<?= e($a['updated']) ?>"><?= e(format_date($a['updated'])) ?></time> · <?= $minutes ?> min read · <?= e(SITE_NAME) ?> editorial team · Not sponsored</p>
</div></div>

<div class="wrap">
  <div class="answer">
    <span class="label"><?= icon('sparkles', 14) ?>Quick answer</span>
    <p class="question"><?= e($a['question']) ?></p>
    <p><?= md_inline($a['answer']) ?></p>
  </div>

  <div class="article-grid">
    <article class="prose article-body"><?= blocks_html($a['blocks']) ?>
      <?php if (!empty($a['faqs'])): ?><section class="section" style="padding-top:28px"><?= faq_html(array_map(fn($f) => [$f[0], md_plain($f[1])], $a['faqs'])) ?></section><?php endif; ?>
      <?= sources_html($a['sources'] ?? []) ?>
      <p class="small muted" style="margin-top:20px">We research vendor documentation, government sources and independent review sites. Vendors cannot pay for coverage or rankings. Spotted something out of date? Email <a href="mailto:<?= e(contact_email()) ?>"><?= e(contact_email()) ?></a>.</p>
    </article>

    <aside class="article-aside">
      <?php if (count($toc) > 2): ?>
      <nav class="panel toc-panel" aria-label="On this page"><h2>On this page</h2><ol><?php foreach ($toc as $b): ?><li><a href="#<?= e($b[2]) ?>"><?= e($b[1]) ?></a></li><?php endforeach; ?></ol></nav>
      <?php endif; ?>
      <?php if ($mentioned): ?>
      <div class="panel"><h2>Software in this article</h2>
        <ul class="mini-list"><?php foreach ($mentioned as $s): ?>
          <li><?= logo_box($s['name'], $s['logo_url']) ?><div><a href="/software/<?= e($s['slug']) ?>"><strong><?= e($s['name']) ?></strong></a><span><?= (int)$s['review_count'] ? sprintf('%.1f/5 · %d verified', $s['rating_avg'], $s['review_count']) : 'Awaiting verified reviews' ?></span></div></li>
        <?php endforeach; ?></ul>
        <?php if (count($mentioned) >= 2): ?><a class="btn secondary small" style="width:100%;margin-top:12px" href="<?= e(compare_path(array_column(array_slice($mentioned, 0, 3), 'slug'))) ?>"><?= icon('compare', 15) ?>Compare these side by side</a><?php endif; ?>
      </div>
      <?php endif; ?>
    </aside>
  </div>

  <?php if ($more): ?><section class="section"><?= related_articles_html(array_column($more, 'slug'), 'More from the blog') ?></section><?php endif; ?>
</div>
