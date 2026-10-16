<?php
$s = software_by_slug($params[0]);
if (!$s) { http_response_code(404); exit('Not found'); }
header('Content-Type: text/markdown; charset=utf-8');
header('X-Robots-Tag: noindex, follow');
header('Link: <' . url("/software/{$s['slug']}") . '>; rel="canonical"');
header('Cache-Control: public, max-age=3600');
echo software_md($s);
