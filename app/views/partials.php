<?php
declare(strict_types=1);

function brand_mark(): string
{
    return '<svg viewBox="0 0 28 28" class="brand-mark" aria-hidden="true"><circle cx="14" cy="14" r="12.5" fill="none" stroke="#0f6b5c" stroke-width="2.5"/><path d="M8.5 14.5 l4 4 l7 -8.5" fill="none" stroke="#0f6b5c" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round"/></svg>';
}

/** Circular ink stamp — the site's signature mark for verified reviews. */
function stamp_svg(string $class, string $sub = '', string $title = 'Verified review'): string
{
    static $n = 0;
    $id = 's' . (++$n);
    $sub = $sub !== '' ? '<text x="100" y="138" text-anchor="middle" fill="currentColor" stroke="none" font-family="Archivo, sans-serif" font-weight="700" font-size="11" letter-spacing="1">' . e($sub) . '</text>' : '';
    return <<<SVG
<svg viewBox="0 0 200 200" class="$class" role="img" aria-label="{$title}">
<defs><path id="ring-$id" d="M100,100 m-74,0 a74,74 0 1,1 148,0 a74,74 0 1,1 -148,0"/>
<filter id="ink-$id"><feTurbulence type="fractalNoise" baseFrequency="0.9" numOctaves="2" seed="3" result="n"/><feColorMatrix in="n" type="matrix" values="0 0 0 0 0  0 0 0 0 0  0 0 0 0 0  0 0 0 -2.2 1.6" result="mask"/><feComposite in="SourceGraphic" in2="mask" operator="in"/></filter></defs>
<g filter="url(#ink-$id)" fill="none" stroke="currentColor">
<circle cx="100" cy="100" r="94" stroke-width="5"/><circle cx="100" cy="100" r="60" stroke-width="2"/>
<text fill="currentColor" stroke="none" font-family="Archivo, Arial Narrow, sans-serif" font-weight="800" font-size="15"><textPath href="#ring-$id" startOffset="0" textLength="458" lengthAdjust="spacing">VERIFIED REVIEW • IMMIGRATIONCRM.SHOP • </textPath></text>
<rect x="22" y="84" width="156" height="34" fill="var(--surface, #fff)" stroke="currentColor" stroke-width="3"/>
<text x="100" y="109" text-anchor="middle" fill="currentColor" stroke="none" font-family="Archivo, Arial Narrow, sans-serif" font-weight="900" font-size="24" letter-spacing="2">VERIFIED</text>
$sub
<path d="M86 62 l9 9 l18 -20" stroke-width="6" stroke-linecap="round" stroke-linejoin="round"/></g></svg>
SVG;
}

function stars(float $v, ?string $label = null): string
{
    $rounded = round($v * 2) / 2;
    $out = '<span class="stars" role="img" aria-label="' . e($label ?? sprintf('%.1f out of 5', $v)) . '">';
    for ($i = 1; $i <= 5; $i++) {
        $fill = $rounded >= $i ? 'currentColor' : ($rounded >= $i - 0.5 ? 'url(#half-star)' : '#cdd6cf');
        $out .= '<svg viewBox="0 0 20 20" aria-hidden="true"><path d="M10 1.5l2.6 5.3 5.9.9-4.3 4.1 1 5.8L10 14.9l-5.2 2.7 1-5.8L1.5 7.7l5.9-.9z" fill="' . $fill . '"/></svg>';
    }
    return $out . '</span>';
}

function rating_inline(float $avg, int $count): string
{
    if (!$count) return '<span class="no-reviews">No verified reviews yet</span>';
    return '<span class="rating">' . stars($avg) . number_format($avg, 1) . ' <span class="muted" style="font-weight:400">(' . $count . ' verified ' . ($count === 1 ? 'review' : 'reviews') . ')</span></span>';
}

function logo_box(string $name, ?string $url, bool $large = false): string
{
    $words = array_slice(array_filter(explode(' ', preg_replace('/[^A-Za-z0-9 ]/', '', $name))), 0, 2);
    $ini = strtoupper(implode('', array_map(fn($w) => $w[0], $words)));
    $inner = $url ? '<img src="' . e($url) . '" alt="" loading="lazy" width="72" height="72">' : e($ini);
    return '<div class="logo-box' . ($large ? ' lg' : '') . '" aria-hidden="true">' . $inner . '</div>';
}

function listing_row(array $s, array $catNames = []): string
{
    $featured = is_featured($s['featured_until']);
    $regions = array_slice(json_list($s['regions']), 0, 4);
    $h = '<li class="listing' . ($featured ? ' featured' : '') . '">' . logo_box($s['name'], $s['logo_url']) . '<div>';
    $h .= '<h3><a href="/software/' . e($s['slug']) . '">' . e($s['name']) . '</a></h3>';
    $h .= '<p class="tagline">' . e($s['tagline']) . '</p><div class="meta">';
    if ($featured) $h .= '<span class="featured-tag">★ Featured</span>';
    $h .= rating_inline((float)$s['rating_avg'], (int)$s['review_count']);
    if ($catNames) $h .= '<span>' . e(implode(', ', $catNames)) . '</span>';
    if ($regions) $h .= '<span>' . e(implode(', ', $regions)) . '</span>';
    $h .= '<a class="compare-tag" href="/compare?add=' . e($s['slug']) . '" rel="nofollow">Compare</a>';
    return $h . '</div></div></li>';
}

function review_card(array $r, ?string $softwareName = null): string
{
    $checks = array_values(array_filter([
        $r['verified_email'] ? 'Work email' : null,
        $r['verified_linkedin'] ? 'LinkedIn profile' : null,
        $r['proof_status'] === 'APPROVED' ? 'Proof of use' : null,
    ]));
    $display = $r['show_name'] ? ($r['u_linkedin_name'] ?: $r['u_name']) : null;
    $who = $display ? e($display) : 'Verified reviewer · ' . e($r['reviewer_role']);
    $company = ($r['show_name'] && $r['u_company']) ? ', ' . e($r['u_company']) : '';
    $pros = implode('', array_map(fn($l) => '<li>' . e($l) . '</li>', lines($r['pros'])));
    $cons = implode('', array_map(fn($l) => '<li>' . e($l) . '</li>', lines($r['cons'])));
    $h = '<article class="review" id="review-' . (int)$r['id'] . '"><div class="review-body">';
    $h .= '<div class="rating" style="margin-bottom:8px">' . stars((float)$r['rating']) . ' ' . (int)$r['rating'] . '.0'
        . ($softwareName ? ' <span class="muted" style="font-weight:400">· ' . e($softwareName) . '</span>' : '') . '</div>';
    $h .= '<h3>' . e($r['title']) . '</h3>';
    $h .= '<p class="reviewer"><strong>' . $who . '</strong>' . $company . ' · ' . e($r['firm_size']) . ' · used for ' . e(strtolower($r['usage_duration']))
        . ' · <time datetime="' . e(iso_date($r['published_at'] ?: $r['created_at'])) . '">' . e(format_date($r['published_at'] ?: $r['created_at'])) . '</time></p>';
    $h .= '<div class="pc"><div><h4>What works</h4><ul>' . $pros . '</ul></div><div class="cons"><h4>What doesn\'t</h4><ul>' . $cons . '</ul></div></div>';
    $h .= '<p class="use-case"><strong>How they use it:</strong> ' . e($r['use_case']) . '</p>';
    $h .= sprintf('<p class="small muted" style="margin-bottom:0">%s Ease of use %d/5 · Support %d/5 · Value %d/5 · Features %d/5</p>',
        $r['recommend'] ? 'Would recommend to a peer.' : 'Would not recommend to a peer.', $r['rating_ease'], $r['rating_support'], $r['rating_value'], $r['rating_features']);
    if ($r['vendor_response']) $h .= '<div class="vendor-reply" style="margin-top:16px"><strong>Response from the vendor</strong><p style="margin:4px 0 0">' . nl2br(e($r['vendor_response'])) . '</p></div>';
    $h .= '</div><div>' . stamp_svg('review-stamp', count($checks) . ' CHECKS', 'Verified by ' . implode(', ', $checks));
    $h .= '<ul class="stamp-legend">' . implode('', array_map(fn($c) => '<li>' . e($c) . '</li>', $checks)) . '</ul></div></article>';
    return $h;
}

/** Shared directory body (filters + results) for /software and /category/{slug}. */
function directory_body(?string $category): string
{
    $sp = ['q' => get('q'), 'region' => get('region'), 'sort' => get('sort')];
    $sort = array_key_exists($sp['sort'], SORTS) ? $sp['sort'] : 'featured';
    $results = search_software(['q' => $sp['q'], 'category' => $category, 'region' => $sp['region'], 'sort' => $sort]);
    $names = category_names_for(array_column($results, 'id'));
    $base = $category ? "/category/$category" : '/software';
    $href = function (array $patch, ?string $to = null) use ($sp, $base) {
        $m = array_filter(array_merge($sp, $patch), fn($v) => $v !== null && $v !== '');
        return ($to ?? $base) . ($m ? '?' . http_build_query($m) : '');
    };
    ob_start(); ?>
<div class="directory">
  <aside class="filters" aria-label="Filters">
    <h3>Category</h3>
    <ul>
      <li><a href="<?= e($href([], '/software')) ?>" aria-current="<?= $category ? 'false' : 'true' ?>">All categories</a></li>
      <?php foreach (categories_with_counts() as $c): ?>
      <li><a href="<?= e($href([], '/category/' . $c['slug'])) ?>" aria-current="<?= $category === $c['slug'] ? 'true' : 'false' ?>"><span><?= e($c['name']) ?></span><span><?= (int)$c['cnt'] ?></span></a></li>
      <?php endforeach; ?>
    </ul>
    <h3>Market served</h3>
    <ul>
      <li><a href="<?= e($href(['region' => null])) ?>" rel="nofollow" aria-current="<?= $sp['region'] ? 'false' : 'true' ?>">Any market</a></li>
      <?php foreach (all_regions() as $reg => $n): ?>
      <li><a href="<?= e($href(['region' => $reg])) ?>" rel="nofollow" aria-current="<?= $sp['region'] === $reg ? 'true' : 'false' ?>"><span><?= e($reg) ?></span><span><?= $n ?></span></a></li>
      <?php endforeach; ?>
    </ul>
  </aside>
  <section aria-label="Results">
    <div class="section-head" style="margin-bottom:16px">
      <p class="muted" style="margin:0"><?= count($results) ?> <?= count($results) === 1 ? 'tool' : 'tools' ?></p>
      <div class="tabs" style="margin:0" aria-label="Sort">
        <?php foreach (SORTS as $k => $label): ?>
        <a class="chip" rel="nofollow" href="<?= e($href(['sort' => $k === 'featured' ? null : $k])) ?>" aria-current="<?= $sort === $k ? 'true' : 'false' ?>"><?= e($label) ?></a>
        <?php endforeach; ?>
      </div>
    </div>
    <?php if ($results): ?>
    <ol class="listing-rows"><?php foreach ($results as $s) echo listing_row($s, $names[$s['id']] ?? []); ?></ol>
    <?php else: ?>
    <div class="empty">
      <h3>Nothing matches yet</h3>
      <p>Try a broader search, or clear the filters. If the software you're looking for isn't listed, you can add it.</p>
      <div style="display:flex;gap:12px;flex-wrap:wrap"><a class="btn secondary" href="<?= e($base) ?>">Clear filters</a><a class="btn" href="/list-your-software">Add a listing</a></div>
    </div>
    <?php endif; ?>
  </section>
</div>
<?php
    return (string)ob_get_clean();
}

function notice(?array $flash): string
{
    if (!$flash) return '';
    return '<div class="notice ' . e($flash[0]) . '" role="status"><p>' . e($flash[1]) . '</p></div>';
}
