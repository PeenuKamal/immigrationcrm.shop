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
<meta name="theme-color" content="#0e1a47">
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
<link rel="preload" href="/assets/fonts/poppins-latin-800-normal.woff2" as="font" type="font/woff2" crossorigin>
<link rel="preload" href="/assets/fonts/inter-latin-400-normal.woff2" as="font" type="font/woff2" crossorigin>
<link rel="stylesheet" href="/assets/style.css?v=<?= @filemtime(ROOT . '/assets/style.css') ?>">
<?= json_ld(array_merge([org_ld(), website_ld()], $m['jsonld'])) ?>
</head>
<body>
<svg width="0" height="0" style="position:absolute" aria-hidden="true"><defs><linearGradient id="half-star"><stop offset="50%" stop-color="#f6b93b"/><stop offset="50%" stop-color="#dfe3ec"/></linearGradient></defs></svg>
<a href="#main" class="skip">Skip to content</a>
<?php $navLinks = [['/software', 'Browse software'], ['/best', 'Best by country'], ['/compare', 'Compare'], ['/how-we-verify', 'How we verify']];
$here = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH); ?>
<header class="site-header">
  <div class="wrap">
    <a href="/" class="brand"><img src="/assets/logo.svg" alt="<?= e(SITE_NAME) ?> home" width="245" height="40"></a>
    <nav class="nav" aria-label="Main">
      <?php foreach ($navLinks as [$href, $label]): ?><a href="<?= $href ?>"<?= str_starts_with((string)$here, $href) ? ' aria-current="page"' : '' ?>><?= e($label) ?></a><?php endforeach; ?>
      <?php if (is_admin($u)): ?><a href="/admin">Moderation</a><?php endif; ?>
      <?php if ($u): ?><a href="/account">My account</a><?php else: ?><a href="/signin">Sign in</a><?php endif; ?>
      <a class="btn small" href="/software">Write a review</a>
    </nav>
    <details class="menu">
      <summary aria-label="Open menu"><?= icon('menu') ?></summary>
      <div class="menu-panel">
        <?php foreach ($navLinks as [$href, $label]): ?><a href="<?= $href ?>"><?= e($label) ?></a><?php endforeach; ?>
        <a href="/list-your-software">List your software</a>
        <?php if (is_admin($u)): ?><a href="/admin">Moderation</a><?php endif; ?>
        <?php if ($u): ?><a href="/account">My account</a><?php else: ?><a href="/signin">Sign in</a><?php endif; ?>
        <a class="btn" href="/software">Write a review</a>
      </div>
    </details>
  </div>
</header>
<main id="main"><?php if ($f = take_flash()): ?><div class="wrap" style="padding-top:20px"><?= notice($f) ?></div><?php endif; ?><?= $content ?></main>
<footer class="site-footer">
  <div class="wrap">
    <div class="footer-top">
      <div>
        <img src="/assets/logo-white.svg" alt="<?= e(SITE_NAME) ?>" width="245" height="40" loading="lazy">
        <p style="color:#aab6dc;max-width:34ch">The independent directory of immigration software, with reviews only from verified immigration practitioners.</p>
        <a class="btn gold small" href="/list-your-software">List your software free</a>
      </div>
      <div><h2>Best software by country</h2><ul>
        <?php foreach (COUNTRIES as $slug => $c): ?><li><a href="/best/immigration-crm/<?= $slug ?>">Best immigration CRM in <?= e($c['short'] ?? $c['name']) ?></a></li><?php endforeach; ?>
      </ul></div>
      <div><h2>Categories</h2><ul>
        <?php foreach ($cats as $c): ?><li><a href="/category/<?= e($c['slug']) ?>"><?= e(category_seo($c['slug'], $c['name'])) ?></a></li><?php endforeach; ?>
      </ul></div>
      <div><h2><?= e(SITE_NAME) ?></h2><ul>
        <li><a href="/compare">Compare software</a></li>
        <li><a href="/about">About us</a></li>
        <li><a href="/how-we-verify">How we verify reviews</a></li>
        <li><a href="/vendor">For vendors</a></li>
        <li><a href="/privacy">Privacy</a></li>
        <li><a href="/terms">Terms</a></li>
        <li><a href="/feed.xml">Reviews RSS feed</a></li>
      </ul></div>
    </div>
    <div class="footer-bottom">
      <span>© <?= year() ?> <?= e(SITE_NAME) ?>. Independent reviews from verified immigration practitioners.</span>
      <span>Featured listings are labelled and never affect ratings.</span>
    </div>
  </div>
</footer>
</body>
</html>
