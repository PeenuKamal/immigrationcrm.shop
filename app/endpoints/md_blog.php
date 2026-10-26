<?php
$a = article($params[0]);
if (!$a) not_found();
header('Content-Type: text/markdown; charset=utf-8');
header('Cache-Control: public, max-age=3600');
header('Link: <' . url(article_url($a['slug'])) . '>; rel="canonical"');
header('X-Robots-Tag: noindex');
echo article_md($a), "\n---\nSource: " . SITE_NAME . ' — ' . url(article_url($a['slug'])) . "\n";
