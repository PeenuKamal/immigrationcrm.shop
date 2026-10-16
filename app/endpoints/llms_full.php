<?php
header('Content-Type: text/plain; charset=utf-8');
header('Cache-Control: public, max-age=3600');
$o = ['# ' . SITE_NAME . ' — full content', '', '> ' . SITE_DESCRIPTION, '',
    'Generated ' . gmdate('Y-m-d') . '. Canonical site: ' . url('/') . '. Review verification: work email plus LinkedIn or moderator-checked proof of use; vendors cannot review their own products.', '', '## Categories'];
foreach (categories_with_counts() as $c) $o[] = "- {$c['name']} ({$c['cnt']} tools): {$c['description']} — " . url("/category/{$c['slug']}");
$o[] = '';
$o[] = '## Country rankings';
foreach (array_keys(COUNTRIES) as $cs) { $c = country($cs); foreach (country_categories($c) as $x) { $o[] = '### Best ' . category_seo($x['cat']['slug'], $x['cat']['name']) . ' in ' . $c['in']; foreach (array_slice($x['ranked'], 0, 5) as $i => $r) $o[] = ($i + 1) . ". {$r['s']['name']} — " . ranked_reason($r) . '.'; $o[] = 'Source: ' . url(guide_path($cs, $x['cat']['slug'])); $o[] = ''; } }
foreach (all("SELECT * FROM software WHERE status = 'PUBLISHED' ORDER BY name") as $s) { $o[] = software_md($s, false, 10); $o[] = ''; }
echo implode("\n", $o);
