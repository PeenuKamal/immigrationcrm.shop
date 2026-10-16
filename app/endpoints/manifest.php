<?php
// Web app manifest: lets people install the site on phones and desktops like an app.
header('Content-Type: application/manifest+json');
echo json_encode([
    'name' => SITE_NAME . ' — ' . SITE_TAGLINE, 'short_name' => SITE_SHORT, 'description' => SITE_DESCRIPTION,
    'start_url' => '/', 'scope' => '/', 'display' => 'standalone', 'background_color' => '#edf1ec', 'theme_color' => '#0f6b5c',
    'categories' => ['business', 'productivity', 'utilities'], 'lang' => 'en',
    'icons' => [
        ['src' => '/assets/icon-192.png', 'sizes' => '192x192', 'type' => 'image/png'],
        ['src' => '/assets/icon-512.png', 'sizes' => '512x512', 'type' => 'image/png'],
        ['src' => '/assets/icon-maskable-512.png', 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'maskable'],
    ],
    'shortcuts' => [['name' => 'Browse software', 'url' => '/software'], ['name' => 'Compare software', 'url' => '/compare']],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
