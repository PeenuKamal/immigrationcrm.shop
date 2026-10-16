<?php
declare(strict_types=1);

function software_md(array $s, bool $full = true, int $reviewsLimit = 50): string
{
    $h1 = $full ? '#' : '##';
    $h2 = $full ? '##' : '###';
    $cats = categories_of((int)$s['id']);
    $revs = all("SELECT * FROM reviews WHERE software_id = ? AND status = 'PUBLISHED' ORDER BY published_at DESC LIMIT " . (int)$reviewsLimit, [$s['id']]);
    $comps = competitors_of((int)$s['id'], 6);
    $o = ["$h1 {$s['name']}", '', "> {$s['tagline']}", '', '- URL: ' . url("/software/{$s['slug']}")];
    if ($s['website']) $o[] = "- Official website: {$s['website']}";
    if ($s['vendor_name']) $o[] = "- Vendor: {$s['vendor_name']}";
    if ($cats) $o[] = '- Category: ' . implode(', ', array_column($cats, 'name'));
    if ($s['deployment']) $o[] = "- Deployment: {$s['deployment']}";
    if ($r = json_list($s['regions'])) $o[] = '- Markets served: ' . implode(', ', $r);
    if ($a = json_list($s['audiences'])) $o[] = '- Built for: ' . implode(', ', $a);
    if ($s['pricing_note']) $o[] = "- Pricing: {$s['pricing_note']}";
    $o[] = '- Verified rating: ' . ((int)$s['review_count'] ? sprintf('%.1f/5 from %d verified review%s', $s['rating_avg'], $s['review_count'], $s['review_count'] > 1 ? 's' : '') : 'no verified reviews yet');
    $o[] = '- Last updated: ' . substr($s['updated_at'], 0, 10);
    array_push($o, '', "$h2 About", '', $s['description'], '');
    if ($f = json_list($s['features'])) { $o[] = "$h2 Key features"; $o[] = ''; foreach ($f as $x) $o[] = "- $x"; $o[] = ''; }
    if ($revs) {
        $o[] = "$h2 Verified reviews"; $o[] = '';
        foreach ($revs as $r) {
            $o[] = sprintf('**%s** — %d/5 · %s, %s, used %s · %s', $r['title'], $r['rating'], $r['reviewer_role'], $r['firm_size'], strtolower($r['usage_duration']), format_date($r['published_at']));
            $o[] = ''; $o[] = 'Pros:'; foreach (lines($r['pros']) as $l) $o[] = "- $l";
            $o[] = 'Cons:'; foreach (lines($r['cons']) as $l) $o[] = "- $l";
            $o[] = 'How they use it: ' . preg_replace('/\s+/', ' ', $r['use_case']); $o[] = '';
        }
    }
    if ($comps) {
        $o[] = "$h2 Alternatives and comparisons"; $o[] = '';
        foreach ($comps as $c) $o[] = "- [{$s['name']} vs {$c['name']}](" . url(compare_path([$s['slug'], $c['slug']])) . ')';
        $o[] = "- [All {$s['name']} alternatives](" . url("/software/{$s['slug']}/alternatives") . ')'; $o[] = '';
    }
    if ($full) {
        $o[] = "$h2 FAQ"; $o[] = '';
        foreach (profile_faqs($s, $cats, $comps) as [$q, $a]) array_push($o, "**$q**", '', $a, '');
        $o[] = '---';
        $o[] = 'Source: ' . SITE_NAME . ' (' . url('/') . '). Every review is from a practitioner with a verified work email plus LinkedIn or proof of use.';
    }
    return implode("\n", $o);
}
