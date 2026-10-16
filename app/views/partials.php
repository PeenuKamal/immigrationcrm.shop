<?php
declare(strict_types=1);

const ICONS = [
    'search' => '<circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>',
    'users' => '<path d="M16 19v-1.5a3.5 3.5 0 0 0-3.5-3.5h-5A3.5 3.5 0 0 0 4 17.5V19"/><circle cx="10" cy="7.5" r="3.5"/><path d="M20 19v-1.5a3.5 3.5 0 0 0-2.5-3.35M15.5 4.2a3.5 3.5 0 0 1 0 6.6"/>',
    'folder' => '<path d="M3 7a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><path d="m9 13 2 2 4-4"/>',
    'scale' => '<path d="M12 4v16M7 20h10M5 8h14M12 4l-1 1M12 4l1 1"/><path d="m5 8-3 6a3 3 0 0 0 6 0zM19 8l-3 6a3 3 0 0 0 6 0z"/>',
    'sparkles' => '<path d="M12 3c.6 3.6 1.9 4.9 5.5 5.5-3.6.6-4.9 1.9-5.5 5.5-.6-3.6-1.9-4.9-5.5-5.5C10.1 7.9 11.4 6.6 12 3z"/><path d="M18.5 14c.3 1.8 1 2.5 2.8 2.8-1.8.3-2.5 1-2.8 2.8-.3-1.8-1-2.5-2.8-2.8 1.8-.3 2.5-1 2.8-2.8zM5.5 15c.25 1.4.8 2 2.2 2.2-1.4.25-1.95.8-2.2 2.2-.25-1.4-.8-1.95-2.2-2.2 1.4-.2 1.95-.8 2.2-2.2z"/>',
    'building' => '<path d="M4 20V6a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v14M16 10h2a2 2 0 0 1 2 2v8M2 20h20M8 8h4M8 12h4M8 16h4"/>',
    'chart' => '<path d="M4 20V10M10 20V4M16 20v-7M22 20H2"/>',
    'mail' => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m4 7 8 6 8-6"/>',
    'linkedin' => '<rect x="3" y="3" width="18" height="18" rx="4"/><path d="M8 10v7M8 7v.01M12 17v-4a2 2 0 0 1 4 0v4M12 10v7"/>',
    'file' => '<path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z"/><path d="M14 3v5h5M9 14l2 2 4-4"/>',
    'shield' => '<path d="M12 3 5 5.5V11c0 4.6 3 8.3 7 10 4-1.7 7-5.4 7-10V5.5z"/><path d="m9 12 2 2 4-4"/>',
    'check' => '<path d="m5 12.5 4.5 4.5L19 7.5"/>',
    'ban' => '<circle cx="12" cy="12" r="9"/><path d="m5.6 5.6 12.8 12.8"/>',
    'globe' => '<circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3a14 14 0 0 1 0 18M12 3a14 14 0 0 0 0 18"/>',
    'clock' => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
    'thumb-up' => '<path d="M7 11v9H4v-9zM7 11l4-8a2 2 0 0 1 2 2v4h5.5a2 2 0 0 1 2 2.3l-1.2 7A2 2 0 0 1 17.3 20H7"/>',
    'thumb-down' => '<path d="M17 13V4h3v9zM17 13l-4 8a2 2 0 0 1-2-2v-4H5.5a2 2 0 0 1-2-2.3l1.2-7A2 2 0 0 1 6.7 4H17"/>',
    'menu' => '<path d="M4 7h16M4 12h16M4 17h16"/>',
    'compare' => '<path d="M8 3v18M16 3v18M3 8h5M16 16h5M3 16h5M16 8h5"/>',
];

function icon(string $name, int $size = 22, string $extra = ''): string
{
    return '<svg width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" ' . $extra . '>' . (ICONS[$name] ?? '') . '</svg>';
}

const CATEGORY_ICONS = [
    'immigration-crm' => 'users', 'immigration-case-management' => 'folder', 'law-practice-management' => 'scale',
    'ai-immigration' => 'sparkles', 'corporate-immigration' => 'building', 'general-crm' => 'chart',
];

/** Kept for older templates: the verification badge used in hero/guide pages. */
function stamp_svg(string $class, string $sub = '', string $title = 'Verified review'): string
{
    return '<img class="' . e($class) . '" src="/assets/logo-mark.svg" alt="' . e($title) . '" width="120" height="120">';
}

function stars(float $v, ?string $label = null): string
{
    $rounded = round($v * 2) / 2;
    $out = '<span class="stars" role="img" aria-label="' . e($label ?? sprintf('%.1f out of 5', $v)) . '">';
    for ($i = 1; $i <= 5; $i++) {
        $fill = $rounded >= $i ? 'currentColor' : ($rounded >= $i - 0.5 ? 'url(#half-star)' : '#dfe3ec');
        $out .= '<svg viewBox="0 0 20 20" aria-hidden="true"><path d="M10 1.5l2.6 5.3 5.9.9-4.3 4.1 1 5.8L10 14.9l-5.2 2.7 1-5.8L1.5 7.7l5.9-.9z" fill="' . $fill . '"/></svg>';
    }
    return $out . '</span>';
}

function rating_inline(float $avg, int $count): string
{
    if (!$count) return '<span class="no-reviews">' . icon('clock', 15) . 'Awaiting first verified review</span>';
    return '<span class="rating">' . stars($avg) . number_format($avg, 1) . ' <span class="muted" style="font-weight:500">(' . $count . ' verified)</span></span>';
}

/** Initials tile with a stable brand-friendly gradient per product. */
function logo_box(string $name, ?string $url, bool $large = false): string
{
    $words = array_slice(array_filter(explode(' ', preg_replace('/[^A-Za-z0-9 ]/', '', $name))), 0, 2);
    $ini = strtoupper(implode('', array_map(fn($w) => $w[0], $words)));
    $palettes = [['#3b63f3', '#1b2a80'], ['#0e9f6e', '#0b5e43'], ['#f59e0b', '#c2410c'], ['#7c5cff', '#3b23a6'], ['#0ea5e9', '#1e40af'], ['#e1543f', '#9f1d2c'], ['#14b8a6', '#0f5f68'], ['#475569', '#0f172a']];
    [$a, $b] = $palettes[abs(crc32($name)) % count($palettes)];
    $inner = $url ? '<img src="' . e($url) . '" alt="" loading="lazy" width="84" height="84">' : e($ini);
    return '<div class="logo-box' . ($large ? ' lg' : '') . '" style="--lb:linear-gradient(135deg,' . $a . ',' . $b . ')" aria-hidden="true">' . $inner . '</div>';
}

function listing_row(array $s, array $catNames = [], ?int $rank = null, string $extra = ''): string
{
    $featured = is_featured($s['featured_until']);
    $regions = array_slice(json_list($s['regions']), 0, 4);
    $h = '<li class="listing' . ($featured ? ' featured' : '') . '">' . ($rank ? '<span class="rank' . ($rank === 1 ? ' r1' : '') . '">' . $rank . '</span>' : '') . logo_box($s['name'], $s['logo_url']) . '<div>';
    $h .= '<h3><a href="/software/' . e($s['slug']) . '">' . e($s['name']) . '</a></h3>';
    $h .= '<p class="tagline">' . e($s['tagline']) . '</p><div class="meta">';
    if ($featured) $h .= '<span class="featured-tag">★ Featured</span>';
    $h .= rating_inline((float)$s['rating_avg'], (int)$s['review_count']);
    if ($catNames) $h .= '<span>' . e(implode(' · ', $catNames)) . '</span>';
    if ($regions) $h .= '<span>' . icon('globe', 14, 'style="vertical-align:-2px"') . ' ' . e(implode(', ', $regions)) . '</span>';
    $h .= '<a class="compare-tag" href="/compare?add=' . e($s['slug']) . '" rel="nofollow">' . icon('compare', 14) . 'Compare</a>';
    return $h . '</div>' . $extra . '</div></li>';
}

function review_card(array $r, ?string $softwareName = null): string
{
    $checks = array_values(array_filter([
        $r['verified_email'] ? 'Work email' : null,
        $r['verified_linkedin'] ? 'LinkedIn' : null,
        $r['proof_status'] === 'APPROVED' ? 'Proof of use' : null,
    ]));
    $display = $r['show_name'] ? ($r['u_linkedin_name'] ?: $r['u_name']) : null;
    $who = $display ?: 'Verified ' . $r['reviewer_role'];
    $initials = strtoupper(implode('', array_map(fn($w) => $w[0] ?? '', array_slice(preg_split('/\s+/', preg_replace('/[^A-Za-z ]/', '', $who)) ?: [], 0, 2))));
    $company = ($r['show_name'] && $r['u_company']) ? ' · ' . e($r['u_company']) : '';
    $pros = implode('', array_map(fn($l) => '<li>' . e($l) . '</li>', lines($r['pros'])));
    $cons = implode('', array_map(fn($l) => '<li>' . e($l) . '</li>', lines($r['cons'])));
    $date = $r['published_at'] ?: $r['created_at'];
    $h = '<article class="review" id="review-' . (int)$r['id'] . '">';
    $h .= '<div class="review-top"><span class="rating">' . stars((float)$r['rating']) . ' ' . (int)$r['rating'] . '.0'
        . ($softwareName ? ' <span class="muted" style="font-weight:500">· ' . e($softwareName) . '</span>' : '') . '</span>';
    $h .= '<span class="checks-row"><span class="badge verified">' . icon('shield', 14) . 'Verified</span>'
        . implode('', array_map(fn($c) => '<span class="badge">' . icon('check', 13) . e($c) . '</span>', $checks)) . '</span></div>';
    $h .= '<h3>' . e($r['title']) . '</h3>';
    $h .= '<div class="reviewer"><span class="avatar">' . e($initials ?: 'VR') . '</span><div><strong>' . e($who) . $company . '</strong>'
        . e($r['firm_size']) . ' · used ' . e(strtolower($r['usage_duration'])) . ' · <time datetime="' . e(iso_date($date)) . '">' . e(format_date($date)) . '</time></div></div>';
    $h .= '<div class="pc"><div><h4>' . icon('thumb-up', 16) . 'What works</h4><ul>' . $pros . '</ul></div><div class="cons"><h4>' . icon('thumb-down', 16) . 'What doesn\'t</h4><ul>' . $cons . '</ul></div></div>';
    $h .= '<p class="use-case"><strong>How they use it:</strong> ' . e($r['use_case']) . '</p>';
    $h .= sprintf('<p class="sub-scores"><span>%s</span><span>Ease of use %d/5</span><span>Support %d/5</span><span>Value %d/5</span><span>Features %d/5</span></p>',
        $r['recommend'] ? 'Would recommend to a peer' : 'Would not recommend', $r['rating_ease'], $r['rating_support'], $r['rating_value'], $r['rating_features']);
    if ($r['vendor_response']) $h .= '<div class="vendor-reply"><strong>Response from the vendor</strong><p style="margin:4px 0 0">' . nl2br(e($r['vendor_response'])) . '</p></div>';
    return $h . '</article>';
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

/** Light page header band used on inner pages. */
function page_band(array $crumbs, string $title, string $lede = '', string $extra = ''): string
{
    return '<div class="page-band"><div class="wrap">' . breadcrumbs_html($crumbs) . '<h1>' . $title . '</h1>'
        . ($lede !== '' ? '<p class="lede">' . $lede . '</p>' : '') . $extra . '</div></div>';
}

function updated_line(?string $date): string
{
    return '<p class="updated">' . icon('clock', 15) . 'Updated <time datetime="' . e(iso_date($date)) . '">' . e(format_date($date ?: now())) . '</time> · Rankings never paid for</p>';
}

function notice(?array $flash): string
{
    if (!$flash) return '';
    return '<div class="notice ' . e($flash[0]) . '" role="status"><p>' . e($flash[1]) . '</p></div>';
}
