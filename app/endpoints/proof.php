<?php
if (!is_admin(current_user())) { http_response_code(404); exit('Not found'); }
$r = one('SELECT p.mime, p.data FROM reviews r JOIN proof_files p ON p.id = r.proof_file_id WHERE r.id = ?', [(int)$params[0]]);
if (!$r) { http_response_code(404); exit('Not found'); }
header('Content-Type: ' . $r['mime']);
header('Cache-Control: private, no-store');
header('X-Content-Type-Options: nosniff');
header('Content-Disposition: inline');
header("Content-Security-Policy: default-src 'none'; img-src 'self'; style-src 'unsafe-inline'; plugin-types application/pdf");
echo $r['data'];
