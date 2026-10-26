<?php
header('Content-Type: application/xml; charset=utf-8');
$list = all("SELECT slug, updated_at FROM software WHERE status = 'PUBLISHED' ORDER BY updated_at DESC");
$mod = array_column($list, 'updated_at', 'slug');
$newest = $list[0]['updated_at'] ?? now();
$urls = [
    ['/', $newest, 'daily', '1.0'], ['/software', $newest, 'daily', '0.9'], ['/compare', $newest, 'weekly', '0.8'],
    ['/how-we-verify', null, 'monthly', '0.5'], ['/about', null, 'monthly', '0.5'], ['/list-your-software', null, 'monthly', '0.4'],
];
foreach (categories_with_counts() as $c) $urls[] = ["/category/{$c['slug']}", $newest, 'daily', '0.9'];
$urls[] = ['/best', $newest, 'weekly', '0.9'];
foreach (array_keys(COUNTRIES) as $cs) {
    $urls[] = [guide_path($cs), $newest, 'weekly', '0.9'];
    foreach (country_categories(country($cs)) as $x) $urls[] = [guide_path($cs, $x['cat']['slug']), $newest, 'weekly', '0.9'];
}
$urls[] = ['/blog', max(array_column(articles(), 'updated') ?: [null]), 'weekly', '0.8'];
foreach (articles() as $a) $urls[] = [article_url($a['slug']), $a['updated'], 'monthly', '0.8'];
foreach (array_keys(insights()) as $k) if (str_starts_with($k, 'compare:')) $urls[] = ['/compare/' . substr($k, 8), $newest, 'weekly', '0.8'];
foreach ($list as $s) $urls[] = ["/software/{$s['slug']}", $s['updated_at'], 'weekly', '0.8'];
foreach ($list as $s) $urls[] = ["/software/{$s['slug']}/alternatives", $s['updated_at'], 'weekly', '0.7'];
foreach (popular_pairs() as $p) $urls[] = [compare_path([$p['a'], $p['b']]), max($mod[$p['a']] ?? $newest, $mod[$p['b']] ?? $newest), 'weekly', '0.6'];
$seen = [];
$urls = array_values(array_filter($urls, function ($u) use (&$seen) { if (isset($seen[$u[0]])) return false; return $seen[$u[0]] = true; }));
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
foreach ($urls as [$p, $m, $f, $pr]) {
    echo '<url><loc>' . e(url($p)) . '</loc>' . ($m ? '<lastmod>' . gmdate('c', strtotime($m)) . '</lastmod>' : '') . "<changefreq>$f</changefreq><priority>$pr</priority></url>\n";
}
echo '</urlset>';
