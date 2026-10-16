<?php
// Social share image (1200×630) for the site, products, categories and comparisons. Uses GD.
$eyebrow = 'Independent software reviews';
$title = 'Immigration software, reviewed by people who file the cases';
$subtitle = 'CRMs · case management · practice management · AI';
$rating = null;

if ($kind === 'software') {
    $s = software_by_slug($params[0]);
    if ($s) {
        $n = (int)$s['review_count'];
        $eyebrow = $n ? "$n verified review" . ($n > 1 ? 's' : '') : 'Reviews, features & alternatives';
        $title = "{$s['name']} reviews";
        $subtitle = $s['tagline'];
        $rating = $n ? number_format((float)$s['rating_avg'], 1) : null;
    }
} elseif ($kind === 'category') {
    $c = category_by_slug($params[0]);
    if ($c) { $eyebrow = 'Best of ' . year(); $title = 'Best ' . category_seo($c['slug'], $c['name']); $subtitle = $c['description']; }
} elseif ($kind === 'compare') {
    $items = load_compare(parse_compare($params[0]));
    if ($items) { $eyebrow = 'Side-by-side comparison'; $title = implode(' vs ', array_map(fn($i) => $i['s']['name'], $items)); $subtitle = 'Verified ratings, features, markets and pros & cons'; }
}

if (!function_exists('imagecreatetruecolor')) {
    header('Content-Type: image/png');
    readfile(ROOT . '/assets/icon-512.png');
    exit;
}

$W = 1200; $H = 630;
$im = imagecreatetruecolor($W, $H);
imageantialias($im, true);
$col = fn($hex) => imagecolorallocate($im, hexdec(substr($hex, 1, 2)), hexdec(substr($hex, 3, 2)), hexdec(substr($hex, 5, 2)));
$paper = $col('#edf1ec'); $ink = $col('#172033'); $ink2 = $col('#3c4859'); $muted = $col('#5d6877'); $teal = $col('#0f6b5c');
imagefilledrectangle($im, 0, 0, $W, $H, $paper);
$bold = ROOT . '/assets/fonts/DejaVuSansCondensed-Bold.ttf';
$reg = ROOT . '/assets/fonts/DejaVuSans.ttf';

$wrap = function (string $text, string $font, float $size, int $maxW, int $maxLines) {
    $words = preg_split('/\s+/', trim($text));
    $lines = [];
    $cur = '';
    foreach ($words as $w) {
        $try = $cur === '' ? $w : "$cur $w";
        $box = imagettfbbox($size, 0, $font, $try);
        if ($box[2] - $box[0] > $maxW && $cur !== '') { $lines[] = $cur; $cur = $w; } else $cur = $try;
    }
    if ($cur !== '') $lines[] = $cur;
    if (count($lines) > $maxLines) { $lines = array_slice($lines, 0, $maxLines); $lines[$maxLines - 1] = rtrim($lines[$maxLines - 1], ' ,.') . '…'; }
    return $lines;
};

// brand
imagesetthickness($im, 5);
imageellipse($im, 94, 82, 46, 46, $teal);
imageline($im, 84, 83, 91, 90, $teal); imageline($im, 91, 90, 104, 74, $teal);
imagettftext($im, 26, 0, 130, 94, $ink, $bold, 'ImmigrationCRM');
$bb = imagettfbbox(26, 0, $bold, 'ImmigrationCRM');
imagettftext($im, 26, 0, 130 + $bb[2] - $bb[0], 94, $teal, $bold, '.shop');

// text block
$x = 72; $maxW = 800;
imagettftext($im, 24, 0, $x, 210, $muted, $reg, $eyebrow);
$size = mb_strlen($title) > 40 ? 48 : 60;
$y = 210 + $size + 26;
foreach ($wrap($title, $bold, $size, $maxW, 3) as $line) { imagettftext($im, $size, 0, $x, $y, $ink, $bold, $line); $y += (int)($size * 1.25); }
$y += 6;
foreach ($wrap($subtitle, $reg, 24, $maxW, 2) as $line) { imagettftext($im, 24, 0, $x, $y, $ink2, $reg, $line); $y += 36; }

// stamp
$cx = 1010; $cy = 330; $r = 115;
for ($t = 0; $t < 8; $t++) imageellipse($im, $cx, $cy, 2 * $r - $t, 2 * $r - $t, $teal);
imagesetthickness($im, 2);
imageellipse($im, $cx, $cy, 2 * $r - 36, 2 * $r - 36, $teal);
$center = function (string $txt, string $font, float $size, int $yy) use ($im, $cx, $teal) {
    $b = imagettfbbox($size, 0, $font, $txt);
    imagettftext($im, $size, 0, (int)($cx - ($b[2] - $b[0]) / 2), $yy, $teal, $font, $txt);
};
if ($rating) { $center($rating, $bold, 60, $cy + 18); $center('VERIFIED', $bold, 18, $cy + 56); }
else { $center('VERIFIED', $bold, 30, $cy - 2); $center('REVIEWS', $bold, 30, $cy + 38); }

imagettftext($im, 20, 0, $x, $H - 56, $muted, $reg, 'Every review from a verified immigration practitioner');

header('Content-Type: image/png');
header('Cache-Control: public, max-age=86400');
imagepng($im, null, 6);
imagedestroy($im);
