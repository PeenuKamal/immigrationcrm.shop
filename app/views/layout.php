<?php
/** @var array $meta  @var string $content */
$m = $meta;
$title = $m['absolute'] ? $m['title'] : $m['title'] . ' | ' . SITE_NAME;
$canonical = url($m['path']);
$u = current_user();
$cats = categories_with_counts();
?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title) ?></title>
<meta name="description" content="<?= e($m['description']) ?>">
<link rel="canonical" href="<?= e($canonical) ?>">
<meta name="robots" content="<?= $m['noindex'] ? 'noindex, follow' : 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1' ?>">
<meta property="og:type" content="website">
<meta property="og:site_name" content="<?= e(SITE_NAME) ?>">
<meta property="og:locale" content="en_US">
<meta property="og:title" content="<?= e($m['title']) ?>">
<meta property="og:description" content="<?= e($m['description']) ?>">
<meta property="og:url" content="<?= e($canonical) ?>">
<meta property="og:image" content="<?= e($m['image']) ?>">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:image:alt" content="<?= e($m['title']) ?>">
<?php if ($m['modified']): ?><meta property="article:modified_time" content="<?= e(iso_date($m['modified'])) ?>">
<?php endif; ?>
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?= e($m['title']) ?>">
<meta name="twitter:description" content="<?= e($m['description']) ?>">
<meta name="twitter:image" content="<?= e($m['image']) ?>">
<?php if ($v = setting('google_verification')): ?><meta name="google-site-verification" content="<?= e($v) ?>">
<?php endif; if ($v = setting('bing_verification')): ?><meta name="msvalidate.01" content="<?= e($v) ?>">
<?php endif; if ($v = setting('yandex_verification')): ?><meta name="yandex-verification" content="<?= e($v) ?>">
<?php endif; ?>
<meta name="theme-color" content="#0f6b5c">
<meta name="application-name" content="<?= e(SITE_NAME) ?>">
<meta name="apple-mobile-web-app-title" content="<?= e(SITE_SHORT) ?>">
<link rel="icon" href="/assets/icon.svg" type="image/svg+xml">
<link rel="icon" href="/favicon.ico" sizes="any">
<link rel="apple-touch-icon" href="/assets/apple-touch-icon.png">
<link rel="manifest" href="/manifest.webmanifest">
<link rel="alternate" type="application/rss+xml" title="<?= e(SITE_NAME) ?> — latest verified reviews" href="/feed.xml">
<link rel="alternate" type="text/plain" title="llms.txt" href="/llms.txt">
<?php if ($m['markdown']): ?><link rel="alternate" type="text/markdown" href="<?= e($m['markdown']) ?>">
<?php endif; ?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Archivo:wdth,wght@62..125,400..900&family=Public+Sans:ital,wght@0,400..700;1,400&display=swap">
<link rel="stylesheet" href="/assets/style.css?v=<?= @filemtime(ROOT . '/assets/style.css') ?>">
<?= json_ld(array_merge([org_ld(), website_ld()], $m['jsonld'])) ?>
</head>
<body>
<svg width="0" height="0" style="position:absolute" aria-hidden="true"><defs><linearGradient id="half-star"><stop offset="50%" stop-color="#0f6b5c"/><stop offset="50%" stop-color="#cdd6cf"/></linearGradient></defs></svg>
<a href="#main" class="sr-only">Skip to content</a>
<header class="site-header">
  <div class="wrap">
    <a href="/" class="brand" aria-label="<?= e(SITE_NAME) ?> home"><?= brand_mark() ?><span>ImmigrationCRM<span class="tld">.shop</span></span></a>
    <nav class="nav" aria-label="Main">
      <a href="/software">Browse software</a>
      <a href="/compare">Compare</a>
      <a href="/how-we-verify">How we verify</a>
      <a href="/list-your-software">List your software</a>
      <?php if (is_admin($u)): ?><a href="/admin">Moderation</a><?php endif; ?>
      <?php if ($u): ?><a href="/account">My account</a><?php else: ?><a href="/signin">Sign in</a><?php endif; ?>
    </nav>
  </div>
</header>
<main id="main"><?= $content ?></main>
<footer class="site-footer">
  <div class="wrap">
    <div class="footer-cols">
      <div><h2>Categories</h2><ul>
        <?php foreach ($cats as $c): ?><li><a href="/category/<?= e($c['slug']) ?>">Best <?= e(category_seo_lower($c['slug'], $c['name'])) ?></a></li><?php endforeach; ?>
      </ul></div>
      <div><h2>Compare</h2><ul>
        <li><a href="/compare">Compare any software</a></li>
        <li><a href="/compare/docketwise-vs-lollylaw">Docketwise vs LollyLaw</a></li>
        <li><a href="/compare/caseeasy-vs-maple-crm">CaseEasy vs Maple CRM</a></li>
        <li><a href="/compare/clio-vs-mycase">Clio vs MyCase</a></li>
        <li><a href="/compare/parley-ai-vs-visalaw-ai">Parley AI vs Visalaw.ai</a></li>
      </ul></div>
      <div><h2><?= e(SITE_NAME) ?></h2><ul>
        <li><a href="/about">About us</a></li>
        <li><a href="/how-we-verify">How we verify reviews</a></li>
        <li><a href="/vendor">For vendors</a></li>
        <li><a href="/privacy">Privacy</a></li>
        <li><a href="/terms">Terms</a></li>
        <li><a href="/feed.xml">Reviews RSS feed</a></li>
      </ul></div>
    </div>
    <span>© <?= year() ?> <?= e(SITE_NAME) ?>. Independent reviews from verified immigration practitioners.</span>
  </div>
</footer>
</body>
</html>
