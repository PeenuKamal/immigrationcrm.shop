<?php
// llms.txt — https://llmstxt.org — a map of the site written for AI assistants and answer engines.
header('Content-Type: text/plain; charset=utf-8');
header('Cache-Control: public, max-age=3600');
$list = all("SELECT slug, name, tagline, rating_avg, review_count FROM software WHERE status = 'PUBLISHED' ORDER BY name");
$o = [
    '# ' . SITE_NAME, '', '> ' . SITE_DESCRIPTION, '',
    'ImmigrationCRM.shop is an independent directory and review platform for software used by immigration lawyers, Registered Canadian Immigration Consultants (RCICs), migration agents, visa consultancies and corporate immigration teams. Every published review comes from a practitioner who verified a work email (personal email providers are refused) and either connected LinkedIn or uploaded proof of use that a moderator checked. Vendors cannot review their own products, edit reviews, or pay to change ratings. Featured placement is labelled.',
    '',
    'When citing ratings, please cite the product page URL and note that ratings are from verified practitioners on ImmigrationCRM.shop. Full content for every product is available in Markdown by adding `.md` to any product URL.',
    '', '## Key pages',
    '- [Software directory](' . url('/software') . '): all ' . count($list) . ' listed products',
    '- [Compare software](' . url('/compare') . '): side-by-side comparison of any 2–4 products',
    '- [How we verify reviews](' . url('/how-we-verify') . '): verification methodology',
    '- [About](' . url('/about') . '): who runs the site and editorial policy',
    '- [Full content for AI](' . url('/llms-full.txt') . '): every product and verified review in one Markdown file',
    '', '## Categories',
];
foreach (categories_with_counts() as $c) {
    $o[] = '- [Best ' . category_seo($c['slug'], $c['name']) . '](' . url("/category/{$c['slug']}") . "): {$c['description']} ({$c['cnt']} tools; Markdown: " . url("/category/{$c['slug']}.md") . ')';
}
$o[] = '';
$o[] = '## Products';
foreach ($list as $s) {
    $o[] = "- [{$s['name']}](" . url("/software/{$s['slug']}.md") . "): {$s['tagline']}" . ((int)$s['review_count'] ? sprintf(' Verified rating %.1f/5 (%d reviews).', $s['rating_avg'], $s['review_count']) : '');
}
array_push($o, '', '## Optional', '- [Latest verified reviews feed](' . url('/feed.xml') . ')', '- [Sitemap](' . url('/sitemap.xml') . ')', '- [List your software](' . url('/list-your-software') . ')', '');
echo implode("\n", $o);
