<?php
header('Content-Type: application/rss+xml; charset=utf-8');
$x = fn($s) => htmlspecialchars((string)$s, ENT_XML1 | ENT_QUOTES, 'UTF-8');
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom"><channel>';
echo '<title>' . $x(SITE_NAME . ' — latest verified reviews') . '</title><link>' . $x(url('/')) . '</link>';
echo '<atom:link href="' . $x(url('/feed.xml')) . '" rel="self" type="application/rss+xml"/>';
echo '<description>' . $x(SITE_DESCRIPTION) . '</description><language>en</language>' . "\n";
foreach (recent_reviews(50) as $r) {
    echo '<item><title>' . $x("{$r['s_name']}: {$r['title']} ({$r['rating']}/5)") . '</title>';
    echo '<link>' . $x(url("/software/{$r['s_slug']}#review-{$r['id']}")) . '</link><guid isPermaLink="false">review-' . (int)$r['id'] . '</guid>';
    echo '<pubDate>' . gmdate('r', strtotime($r['published_at'] ?: $r['created_at'])) . '</pubDate>';
    echo '<description>' . $x("Verified {$r['reviewer_role']} ({$r['firm_size']}). Pros: " . implode('; ', lines($r['pros'])) . ' Cons: ' . implode('; ', lines($r['cons']))) . "</description></item>\n";
}
echo '</channel></rss>';
