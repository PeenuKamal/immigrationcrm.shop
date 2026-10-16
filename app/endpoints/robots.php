<?php
// Search engines and AI assistants are explicitly welcome so the site can be cited by
// ChatGPT, Claude, Gemini, Perplexity, Copilot and others. Private pages stay out.
header('Content-Type: text/plain; charset=utf-8');
$bots = ['Googlebot', 'Google-Extended', 'GoogleOther', 'Bingbot', 'Applebot', 'Applebot-Extended', 'DuckDuckBot', 'YandexBot',
    'GPTBot', 'OAI-SearchBot', 'ChatGPT-User', 'ClaudeBot', 'Claude-SearchBot', 'Claude-User', 'anthropic-ai', 'PerplexityBot', 'Perplexity-User',
    'Meta-ExternalAgent', 'Meta-ExternalFetcher', 'Amazonbot', 'DuckAssistBot', 'MistralAI-User', 'cohere-ai', 'YouBot', 'CCBot', 'Diffbot'];
$private = ['/admin', '/account', '/vendor', '/auth/', '/signin', '/signout', '/stripe/', '/install.php', '/app/', '/*/review$', '/*/claim$', '/*?*sort=', '/*?*region='];
$o = [];
foreach ($bots as $b) $o[] = "User-agent: $b";
$o[] = 'Allow: /';
$o[] = 'Allow: /llms.txt';
$o[] = 'Allow: /llms-full.txt';
$o[] = 'Allow: /*.md$';
foreach ($private as $p) $o[] = "Disallow: $p";
$o[] = '';
$o[] = 'User-agent: *';
$o[] = 'Allow: /';
foreach ($private as $p) $o[] = "Disallow: $p";
$o[] = '';
$o[] = 'Sitemap: ' . url('/sitemap.xml');
echo implode("\n", $o) . "\n";
