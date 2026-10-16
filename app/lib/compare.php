<?php
declare(strict_types=1);

const MAX_COMPARE = 4;

function parse_compare(string $pair): array
{
    return array_values(array_unique(array_filter(array_map('trim', explode('-vs-', strtolower(rawurldecode($pair)))))));
}

function compare_path(array $slugs): string
{
    $slugs = array_values(array_unique($slugs));
    sort($slugs);
    return '/compare/' . implode('-vs-', $slugs);
}

function load_compare(array $slugs): array
{
    if (!$slugs) return [];
    $rows = all("SELECT * FROM software WHERE status = 'PUBLISHED' AND slug IN (" . in_list($slugs) . ')', $slugs);
    $order = array_flip($slugs);
    usort($rows, fn($a, $b) => $order[$a['slug']] <=> $order[$b['slug']]);
    $items = [];
    foreach ($rows as $s) {
        $id = (int)$s['id'];
        $items[] = [
            's' => $s,
            'cats' => categories_of($id),
            'agg' => sub_ratings($id),
            'latest' => all("SELECT * FROM reviews WHERE software_id = ? AND status = 'PUBLISHED' ORDER BY published_at DESC LIMIT 3", [$id]),
        ];
    }
    return $items;
}

/** Other published listings sharing a category, most categories in common first. */
function competitors_of(int $softwareId, int $limit = 12): array
{
    return all("SELECT s.*, COUNT(*) AS shared FROM software s
        JOIN software_categories sc ON sc.software_id = s.id
        WHERE s.status = 'PUBLISHED' AND s.id <> ?
          AND sc.category_id IN (SELECT category_id FROM software_categories WHERE software_id = ?)
        GROUP BY s.id ORDER BY shared DESC, s.review_count DESC, s.rating_avg DESC, s.name LIMIT " . (int)$limit, [$softwareId, $softwareId]);
}

/** Every pair of published products sharing a category. */
function popular_pairs(): array
{
    $rows = all("SELECT LEAST(sa.slug, sb.slug) AS a, GREATEST(sa.slug, sb.slug) AS b,
            IF(sa.slug < sb.slug, sa.name, sb.name) AS an, IF(sa.slug < sb.slug, sb.name, sa.name) AS bn,
            MIN(c.sort_order) AS sort_order
        FROM software_categories x
        JOIN software_categories y ON x.category_id = y.category_id AND x.software_id < y.software_id
        JOIN software sa ON sa.id = x.software_id AND sa.status = 'PUBLISHED'
        JOIN software sb ON sb.id = y.software_id AND sb.status = 'PUBLISHED'
        JOIN categories c ON c.id = x.category_id
        GROUP BY LEAST(sa.slug, sb.slug), GREATEST(sa.slug, sb.slug), IF(sa.slug < sb.slug, sa.name, sb.name), IF(sa.slug < sb.slug, sb.name, sa.name)
        ORDER BY 1, 2");
    $cats = [];
    foreach (categories_with_counts() as $c) $cats[(int)$c['sort_order']] = $c['slug'];
    foreach ($rows as &$r) $r['cat'] = $cats[(int)$r['sort_order']] ?? '';
    return $rows;
}

function compare_faqs(array $items): array
{
    $names = array_map(fn($i) => $i['s']['name'], $items);
    $title = join_names($names);
    $either = join_names($names, 'or');
    $faqs = [];
    $reviewed = array_values(array_filter($items, fn($i) => (int)$i['s']['review_count'] > 0));
    if (count($reviewed) >= 2) {
        usort($reviewed, fn($a, $b) => $b['s']['rating_avg'] <=> $a['s']['rating_avg']);
        $parts = array_map(fn($i) => sprintf('%s averages %.1f/5 from %d verified review%s', $i['s']['name'], $i['s']['rating_avg'], $i['s']['review_count'], $i['s']['review_count'] > 1 ? 's' : ''), $reviewed);
        $faqs[] = ["Which is rated higher: $either?", 'On ImmigrationCRM.shop, ' . implode('; ', $parts) . ". {$reviewed[0]['s']['name']} currently has the highest average. Every review comes from a practitioner with a verified work email plus LinkedIn or proof of use."];
    } else {
        $faqs[] = ["Which is better: $either?", "There aren't enough verified reviews yet to compare ratings. The right choice depends on your market and practice type: " . implode('; ', array_map(fn($i) => $i['s']['name'] . ' is ' . lcfirst_tagline($i['s']['tagline']), $items)) . '.'];
    }
    foreach (['Canada' => 'Canada', 'USA' => 'the USA', 'Australia' => 'Australia', 'India' => 'India', 'UK' => 'the UK'] as $m => $inM) {
        $yes = array_map(fn($i) => $i['s']['name'], array_filter($items, fn($i) => in_array($m, json_list($i['s']['regions']), true)));
        if ($yes && count($yes) < count($items)) {
            $no = array_diff($names, $yes);
            $faqs[] = ["Which of $either supports immigration work in $inM?", join_names($yes) . (count($yes) === 1 ? ' lists ' : ' list ') . "$inM as a market served. " . join_names($no) . (count($no) === 1 ? " doesn't" : " don't") . " list $inM."];
        }
    }
    $has = fn(array $i, string $field, string $rx) => (bool)preg_grep($rx, json_list($i['s'][$field]));
    $rcic = array_map(fn($i) => $i['s']['name'], array_filter($items, fn($i) => $has($i, 'audiences', '/RCIC|consultant|migration agent/i')));
    if ($rcic && count($rcic) < count($items)) $faqs[] = ['Which is built for immigration consultants and RCICs?', join_names($rcic) . (count($rcic) === 1 ? ' is' : ' are') . ' built for immigration consultants, RCICs or migration agents. The others are aimed mainly at law firms or employers.'];
    $ai = array_map(fn($i) => $i['s']['name'], array_filter($items, fn($i) => in_array('ai-immigration', array_column($i['cats'], 'slug'), true) || $has($i, 'features', '/\bAI\b|drafting/i')));
    if ($ai && count($ai) < count($items)) $faqs[] = ['Which one has AI drafting or AI features?', join_names($ai) . (count($ai) === 1 ? ' offers' : ' offer') . ' AI drafting or AI-assisted features according to ' . (count($ai) === 1 ? 'its' : 'their') . ' listing.'];
    $portal = array_map(fn($i) => $i['s']['name'], array_filter($items, fn($i) => $has($i, 'features', '/portal/i')));
    if ($portal && count($portal) < count($items)) $faqs[] = ['Which has a client portal?', join_names($portal) . (count($portal) === 1 ? ' lists' : ' list') . ' a client portal as a key feature.'];
    $faqs[] = ["Can I use $title together?", "Many firms combine tools — for example a practice management suite for billing with an immigration-specific forms or AI drafting tool. Check each vendor's integrations before buying."];
    return array_slice($faqs, 0, 7);
}

function profile_faqs(array $s, array $cats, array $competitors): array
{
    $aud = json_list($s['audiences']);
    $reg = json_list($s['regions']);
    $feat = json_list($s['features']);
    $n = $s['name'];
    $faqs = [["What is $n?", $s['description']]];
    $faqs[] = ["Who is $n for?", $aud ? "$n is built for " . join_names(array_map('strtolower', $aud)) . ($reg ? ', serving ' . join_names($reg) : '') . '.' : "$n is used by immigration practices" . ($reg ? ' in ' . join_names($reg) : '') . '.'];
    if ($feat) $faqs[] = ["What are $n's key features?", "$n's key features include " . join_names(array_map('strtolower', array_slice($feat, 0, 8))) . '.'];
    $faqs[] = ["Is $n any good? What do users say?", (int)$s['review_count']
        ? sprintf('%s has an average rating of %.1f out of 5 from %d verified review%s on ImmigrationCRM.shop. Each reviewer verified a work email plus LinkedIn or proof of use.', $n, $s['rating_avg'], $s['review_count'], $s['review_count'] > 1 ? 's' : '')
        : "$n has no verified reviews on ImmigrationCRM.shop yet. If your firm uses it, you can write the first one."];
    if ($s['pricing_note']) $faqs[] = ["How much does $n cost?", rtrim($s['pricing_note'], '.') . '. Confirm current pricing with the vendor.'];
    if ($s['deployment']) $faqs[] = ["Is $n cloud-based?", $s['deployment'] === 'Cloud' ? "Yes. $n is cloud-based software you use in a web browser." : "$n is listed as " . strtolower($s['deployment']) . ' software.'];
    if ($competitors) $faqs[] = ["What are the best alternatives to $n?", 'Popular alternatives in ' . lower_words($cats[0]['name'] ?? 'this category') . ' include ' . join_names(array_column(array_slice($competitors, 0, 5), 'name')) . '.'];
    return $faqs;
}
