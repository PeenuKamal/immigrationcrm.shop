<?php
declare(strict_types=1);

/**
 * Tells IndexNow search engines (Bing — which powers ChatGPT search and Copilot — plus Yandex, Seznam, Naver)
 * that pages changed, so new reviews and listings are crawled within minutes.
 */
function ping_indexnow(array $paths): void
{
    $key = setting('indexnow_key');
    $base = app_url();
    if (!$key || str_contains($base, 'localhost') || !$paths) return;
    $ch = curl_init('https://api.indexnow.org/indexnow');
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 3,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json; charset=utf-8'],
        CURLOPT_POSTFIELDS => json_encode([
            'host' => parse_url($base, PHP_URL_HOST),
            'key' => $key,
            'keyLocation' => "$base/indexnow.txt",
            'urlList' => array_values(array_unique(array_map(fn($p) => $base . $p, $paths))),
        ]),
    ]);
    @curl_exec($ch);
    curl_close($ch);
}
