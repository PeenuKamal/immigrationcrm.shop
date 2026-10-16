<?php
$c = category_by_slug($params[0]);
if (!$c) { http_response_code(404); exit('Not found'); }
header('Content-Type: text/markdown; charset=utf-8');
header('X-Robots-Tag: noindex, follow');
header('Link: <' . url("/category/{$c['slug']}") . '>; rel="canonical"');
$list = search_software(['category' => $c['slug'], 'sort' => 'rating']);
$o = ['# Best ' . category_seo($c['slug'], $c['name']), '', "> {$c['description']}", '', 'Ranked by verified rating, then number of verified reviews. Source: ' . url("/category/{$c['slug']}"), ''];
foreach ($list as $i => $s) {
    $o[] = ($i + 1) . ". [{$s['name']}](" . url("/software/{$s['slug']}") . ") — {$s['tagline']} "
        . ((int)$s['review_count'] ? sprintf('Rated %.1f/5 (%d verified reviews).', $s['rating_avg'], $s['review_count']) : 'No verified reviews yet.')
        . ' Markets: ' . (implode(', ', json_list($s['regions'])) ?: 'n/a') . '.';
}
echo implode("\n", $o);
