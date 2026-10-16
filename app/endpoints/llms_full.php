<?php
header('Content-Type: text/plain; charset=utf-8');
header('Cache-Control: public, max-age=3600');
$o = ['# ' . SITE_NAME . ' — full content', '', '> ' . SITE_DESCRIPTION, '',
    'Generated ' . gmdate('Y-m-d') . '. Canonical site: ' . url('/') . '. Review verification: work email plus LinkedIn or moderator-checked proof of use; vendors cannot review their own products.', '', '## Categories'];
foreach (categories_with_counts() as $c) $o[] = "- {$c['name']} ({$c['cnt']} tools): {$c['description']} — " . url("/category/{$c['slug']}");
$o[] = '';
foreach (all("SELECT * FROM software WHERE status = 'PUBLISHED' ORDER BY name") as $s) { $o[] = software_md($s, false, 10); $o[] = ''; }
echo implode("\n", $o);
