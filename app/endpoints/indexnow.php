<?php
$key = setting('indexnow_key');
if (!$key) { http_response_code(404); exit('Not found'); }
header('Content-Type: text/plain; charset=utf-8');
echo $key;
