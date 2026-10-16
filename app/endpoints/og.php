<?php
// Branded share image (1200×630) for the site, products, categories, comparisons and guides. Uses GD.
$eyebrow = 'Independent software reviews';
$title = 'Find the right immigration software with confidence';
$subtitle = 'Verified reviews of CRMs, case management and AI tools';
$rating = null;

if ($kind === 'software') {
    $s = software_by_slug($params[0]);
    if ($s) {
        $n = (int)$s['review_count'];
        $eyebrow = $n ? "$n verified review" . ($n > 1 ? 's' : '') : 'Reviews · features · alternatives';
        $title = "{$s['name']} reviews";
        $subtitle = $s['tagline'];
        $rating = $n ? number_format((float)$s['rating_avg'], 1) : null;
    }
} elseif ($kind === 'category') {
    $c = category_by_slug($params[0]);
    if ($c) { $eyebrow = 'Buyer\'s guide ' . year(); $title = 'Best ' . category_seo($c['slug'], $c['name']); $subtitle = $c['description']; }
} elseif ($kind === 'compare') {
    $items = load_compare(parse_compare($params[0]));
    if ($items) { $eyebrow = 'Side-by-side comparison'; $title = implode(' vs ', array_map(fn($i) => $i['s']['name'], $items)); $subtitle = 'Verified ratings, features, markets, pros and cons'; }
}

if (!function_exists('imagecreatetruecolor')) { header('Content-Type: image/png'); readfile(ROOT . '/assets/icon-512.png'); exit; }

$W = 1200; $H = 630;
$im = imagecreatetruecolor($W, $H);
imagealphablending($im, true);
imageantialias($im, true);
$rgb = fn($hex, $a = 0) => imagecolorallocatealpha($im, hexdec(substr($hex, 1, 2)), hexdec(substr($hex, 3, 2)), hexdec(substr($hex, 5, 2)), $a);
// navy background with a soft blue glow top-right
imagefilledrectangle($im, 0, 0, $W, $H, $rgb('#0e1a47'));
for ($r = 520; $r > 0; $r -= 8) imagefilledellipse($im, 1080, 40, $r * 2, $r * 2, $rgb('#3b63f3', (int)(127 - 12 * (1 - $r / 520))));
// dotted golden flight path
$gold = $rgb('#f6b93b');
for ($t = 0.0; $t <= 1.0; $t += 0.012) {
    $x = (1 - $t) ** 3 * -40 + 3 * (1 - $t) ** 2 * $t * 380 + 3 * (1 - $t) * $t ** 2 * 860 + $t ** 3 * 1240;
    $y = (1 - $t) ** 3 * 600 + 3 * (1 - $t) ** 2 * $t * 640 + 3 * (1 - $t) * $t ** 2 * 300 + $t ** 3 * 20;
    imagefilledellipse($im, (int)$x, (int)$y, 5, 5, $rgb('#f6b93b', 50));
}
$bold = ROOT . '/assets/fonts/Poppins-Bold.ttf';
$med = ROOT . '/assets/fonts/Poppins-Medium.ttf';
$logo = @imagecreatefrompng(ROOT . '/assets/logo-white-og.png');
if ($logo) imagecopyresampled($im, $logo, 64, 52, 0, 0, 391, 64, imagesx($logo), imagesy($logo));

$wrap = function (string $text, string $font, float $size, int $maxW, int $maxLines) {
    $words = preg_split('/\s+/', trim($text));
    $lines = []; $cur = '';
    foreach ($words as $w) {
        $try = $cur === '' ? $w : "$cur $w";
        $box = imagettfbbox($size, 0, $font, $try);
        if ($box[2] - $box[0] > $maxW && $cur !== '') { $lines[] = $cur; $cur = $w; } else $cur = $try;
    }
    if ($cur !== '') $lines[] = $cur;
    if (count($lines) > $maxLines) { $lines = array_slice($lines, 0, $maxLines); $lines[$maxLines - 1] = rtrim($lines[$maxLines - 1], ' ,.') . '…'; }
    return $lines;
};
$x = 72; $maxW = $rating ? 760 : 1000;
// eyebrow pill
$eb = imagettfbbox(18, 0, $med, $eyebrow);
imagefilledrectangle($im, $x, 196, $x + ($eb[2] - $eb[0]) + 36, 236, $rgb('#f6b93b', 90));
imagettftext($im, 18, 0, $x + 18, 224, $rgb('#ffd66b'), $med, $eyebrow);
$size = mb_strlen($title) > 44 ? 46 : 58;
$y = 236 + $size + 36;
foreach ($wrap($title, $bold, $size, $maxW, 3) as $line) { imagettftext($im, $size, 0, $x, $y, $rgb('#ffffff'), $bold, $line); $y += (int)($size * 1.22); }
$y += 4;
foreach ($wrap($subtitle, $med, 24, $maxW, 2) as $line) { imagettftext($im, 24, 0, $x, $y, $rgb('#c9d3f5'), $med, $line); $y += 36; }

if ($rating) {
    $cx = 1010; $cy = 340;
    imagefilledellipse($im, $cx, $cy, 260, 260, $rgb('#ffffff'));
    imagesetthickness($im, 8);
    imageellipse($im, $cx, $cy, 244, 244, $gold);
    $b = imagettfbbox(70, 0, $bold, $rating);
    imagettftext($im, 70, 0, (int)($cx - ($b[2] - $b[0]) / 2), $cy + 20, $rgb('#0e1a47'), $bold, $rating);
    $b = imagettfbbox(18, 0, $med, 'VERIFIED RATING');
    imagettftext($im, 18, 0, (int)($cx - ($b[2] - $b[0]) / 2), $cy + 62, $rgb('#0b7f58'), $med, 'VERIFIED RATING');
}
imagettftext($im, 19, 0, $x, $H - 52, $rgb('#9fb0e6'), $med, 'Every review from a verified immigration practitioner · immigrationcrm.shop');

header('Content-Type: image/png');
header('Cache-Control: public, max-age=86400');
imagepng($im, null, 6);
imagedestroy($im);
