<?php
declare(strict_types=1);

/** Page metadata consumed by the layout. */
function meta(array $o): array
{
    return [
        'title' => $o['title'],
        'absolute' => $o['absolute'] ?? false,
        'description' => clip($o['description']),
        'path' => $o['path'],
        'image' => $o['image'] ?? url('/og.png'),
        'noindex' => $o['noindex'] ?? false,
        'modified' => $o['modified'] ?? null,
        'markdown' => $o['markdown'] ?? null,
        'jsonld' => $o['jsonld'] ?? [],
    ];
}

function json_ld(array $nodes): string
{
    $data = ['@context' => 'https://schema.org', '@graph' => array_values(array_filter($nodes))];
    return '<script type="application/ld+json">' . str_replace('<', '<', json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)) . '</script>';
}

function org_id(): string { return url('/#organization'); }
function site_id(): string { return url('/#website'); }

function org_ld(): array
{
    return [
        '@type' => 'Organization', '@id' => org_id(), 'name' => SITE_NAME, 'url' => url('/'),
        'logo' => ['@type' => 'ImageObject', 'url' => url('/assets/icon-512.png'), 'width' => 512, 'height' => 512],
        'description' => SITE_DESCRIPTION, 'email' => contact_email(),
        'knowsAbout' => ['Immigration CRM software', 'Immigration case management software', 'Legal practice management software', 'Legal AI for immigration law'],
    ];
}

function website_ld(): array
{
    return [
        '@type' => 'WebSite', '@id' => site_id(), 'name' => SITE_NAME, 'url' => url('/'), 'description' => SITE_DESCRIPTION,
        'publisher' => ['@id' => org_id()], 'inLanguage' => 'en',
        'potentialAction' => ['@type' => 'SearchAction', 'target' => ['@type' => 'EntryPoint', 'urlTemplate' => url('/software') . '?q={search_term_string}'], 'query-input' => 'required name=search_term_string'],
    ];
}

function webpage_ld(string $path, string $name, array $extra = [], string $type = 'WebPage'): array
{
    return ['@type' => $type, '@id' => url($path), 'url' => url($path), 'name' => $name, 'isPartOf' => ['@id' => site_id()]] + $extra;
}

function breadcrumb_ld(array $crumbs): array
{
    $items = [];
    foreach ($crumbs as $i => [$name, $path]) $items[] = ['@type' => 'ListItem', 'position' => $i + 1, 'name' => $name, 'item' => url($path)];
    return ['@type' => 'BreadcrumbList', 'itemListElement' => $items];
}

function breadcrumbs_html(array $crumbs): string
{
    $out = '<nav aria-label="Breadcrumb" class="crumbs"><ol>';
    $last = count($crumbs) - 1;
    foreach ($crumbs as $i => [$name, $path]) {
        $out .= '<li>' . ($i < $last ? '<a href="' . e($path) . '">' . e($name) . '</a>' : '<span aria-current="page">' . e($name) . '</span>') . '</li>';
    }
    return $out . '</ol></nav>';
}

function faq_ld(array $faqs): array
{
    return ['@type' => 'FAQPage', 'mainEntity' => array_map(fn($f) => ['@type' => 'Question', 'name' => $f[0], 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f[1]]], $faqs)];
}

function faq_html(array $faqs, string $heading = 'Frequently asked questions'): string
{
    if (!$faqs) return '';
    $out = '<section aria-labelledby="faq-h" class="faq"><h2 id="faq-h">' . e($heading) . '</h2>';
    foreach ($faqs as [$q, $a]) $out .= '<details><summary><h3>' . e($q) . '</h3></summary><p>' . e($a) . '</p></details>';
    return $out . '</section>';
}

function item_list_ld(string $name, array $items): array
{
    $els = [];
    foreach (array_values($items) as $i => [$n, $p]) $els[] = ['@type' => 'ListItem', 'position' => $i + 1, 'name' => $n, 'url' => url($p)];
    return ['@type' => 'ItemList', 'name' => $name, 'numberOfItems' => count($els), 'itemListElement' => $els];
}

function software_ld(array $s, array $categoryNames = [], array $reviews = []): array
{
    $features = json_list($s['features']);
    $regions = json_list($s['regions']);
    $aud = json_list($s['audiences']);
    $n = [
        '@type' => 'SoftwareApplication',
        '@id' => url("/software/{$s['slug']}#software"),
        'name' => $s['name'],
        'url' => url("/software/{$s['slug']}"),
        'description' => $s['description'],
        'applicationCategory' => 'BusinessApplication',
        'operatingSystem' => $s['deployment'] === 'On-premise' ? 'Windows' : 'Web browser',
        'dateModified' => iso_date($s['updated_at']),
    ];
    if ($categoryNames) $n['applicationSubCategory'] = implode(', ', $categoryNames);
    if ($s['website']) $n['sameAs'] = [$s['website']];
    if ($s['logo_url']) $n['image'] = $s['logo_url'];
    if ($s['vendor_name']) $n['publisher'] = ['@type' => 'Organization', 'name' => $s['vendor_name']];
    if ($features) $n['featureList'] = implode(', ', $features);
    if ($regions) $n['areaServed'] = $regions;
    if ($aud) $n['audience'] = ['@type' => 'BusinessAudience', 'audienceType' => implode(', ', $aud)];
    if ((int)$s['review_count'] > 0) {
        $n['aggregateRating'] = ['@type' => 'AggregateRating', 'ratingValue' => number_format((float)$s['rating_avg'], 1), 'reviewCount' => (int)$s['review_count'], 'bestRating' => 5, 'worstRating' => 1];
    }
    if ($reviews) {
        $n['review'] = array_map(fn($r) => [
            '@type' => 'Review',
            'name' => $r['title'],
            'reviewBody' => $r['use_case'],
            'datePublished' => iso_date($r['published_at'] ?: $r['created_at']),
            'author' => ['@type' => 'Person', 'name' => 'Verified ' . $r['reviewer_role']],
            'reviewRating' => ['@type' => 'Rating', 'ratingValue' => (int)$r['rating'], 'bestRating' => 5, 'worstRating' => 1],
            'positiveNotes' => ['@type' => 'ItemList', 'itemListElement' => array_map(fn($l, $i) => ['@type' => 'ListItem', 'position' => $i + 1, 'name' => $l], lines($r['pros']), array_keys(lines($r['pros'])))],
            'negativeNotes' => ['@type' => 'ItemList', 'itemListElement' => array_map(fn($l, $i) => ['@type' => 'ListItem', 'position' => $i + 1, 'name' => $l], lines($r['cons']), array_keys(lines($r['cons'])))],
            'publisher' => ['@id' => org_id()],
        ], array_slice($reviews, 0, 20));
    }
    return $n;
}
