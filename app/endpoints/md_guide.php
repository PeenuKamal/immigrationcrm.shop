<?php
// Markdown versions of the country guides, for AI assistants (linked from llms.txt).
header('Content-Type: text/markdown; charset=utf-8');
header('X-Robots-Tag: noindex, follow');
if (count($params) === 1) {
    $c = country($params[0]);
    if (!$c) { http_response_code(404); exit('Not found'); }
    header('Link: <' . url(guide_path($c['slug'])) . '>; rel="canonical"');
    $o = ['# Best immigration software in ' . $c['in'] . ' (' . year() . ')', '', "> Rankings for {$c['who']} on " . SITE_NAME . '. Source: ' . url(guide_path($c['slug'])), ''];
    foreach (country_categories($c) as $x) {
        $o[] = '## Best ' . category_seo_lower($x['cat']['slug'], $x['cat']['name']) . ' in ' . $c['in'];
        $o[] = '';
        foreach (array_slice($x['ranked'], 0, 5) as $i => $r) $o[] = ($i + 1) . ". [{$r['s']['name']}](" . url("/software/{$r['s']['slug']}") . ') — ' . ranked_reason($r) . '.';
        $o[] = 'Full ranking: ' . url(guide_path($c['slug'], $x['cat']['slug']));
        $o[] = '';
    }
    $o[] = '## Context';
    foreach ($c['context'] as $p) { $o[] = $p; $o[] = ''; }
    if ($in = insight('country:' . $c['slug'])) { $o[] = insight_md($in); $o[] = ''; }
    $o[] = 'Methodology: ' . METHODOLOGY;
    echo implode("\n", $o);
    return;
}
$cat = category_by_slug($params[0]);
$c = country($params[1]);
if (!$cat || !$c) { http_response_code(404); exit('Not found'); }
$g = guide_data($c, $cat);
if (!$g['ranked']) { http_response_code(404); exit('Not found'); }
$path = guide_path($c['slug'], $cat['slug']);
header('Link: <' . url($path) . '>; rel="canonical"');
$o = ["# Best {$g['seo']} in {$c['in']} (" . year() . ')', '', "> {$g['answer']}", '', 'Source: ' . url($path) . ' · Updated ' . gmdate('F Y'), '', '## Ranking', ''];
foreach ($g['ranked'] as $i => $r) {
    $s = $r['s'];
    $o[] = ($i + 1) . ". **[{$s['name']}](" . url("/software/{$s['slug']}") . ")** — {$s['tagline']}";
    $o[] = '   - Why: ' . implode('; ', $r['why']);
    $o[] = '   - Verified rating: ' . ($r['reviews'] ? sprintf('%.1f/5 from %d verified review%s', $r['avg'], $r['reviews'], $r['reviews'] > 1 ? 's' : '') : 'no verified reviews yet');
    if ($s['pricing_note']) $o[] = "   - Pricing: {$s['pricing_note']}";
}
$o[] = '';
$o[] = '## What to look for';
foreach (array_merge($c['look_for'], CATEGORY_LOOK_FOR[$cat['slug']] ?? []) as $l) $o[] = "- $l";
$o[] = '';
$o[] = "## Immigration practice in {$c['name']}";
foreach ($c['context'] as $p) { $o[] = $p; $o[] = ''; }
$o[] = '## FAQ';
foreach ($g['faqs'] as [$q, $a]) { $o[] = "**$q**"; $o[] = ''; $o[] = $a; $o[] = ''; }
echo implode("\n", $o);
