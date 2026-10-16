<?php
declare(strict_types=1);

function categories_with_counts(): array
{
    static $c = null;
    return $c ??= all("SELECT c.*, COUNT(CASE WHEN s.status = 'PUBLISHED' THEN 1 END) AS cnt
        FROM categories c
        LEFT JOIN software_categories sc ON sc.category_id = c.id
        LEFT JOIN software s ON s.id = sc.software_id
        GROUP BY c.id ORDER BY c.sort_order");
}

function category_by_slug(string $slug): ?array
{
    return one('SELECT * FROM categories WHERE slug = ?', [$slug]);
}

const SORTS = ['featured' => 'Recommended', 'rating' => 'Highest rated', 'reviews' => 'Most reviewed', 'name' => 'A–Z', 'newest' => 'Newest'];

function search_software(array $o = []): array
{
    $where = ["s.status = 'PUBLISHED'"];
    $p = [];
    if (!empty($o['q'])) {
        $like = '%' . str_replace(['%', '_'], '', $o['q']) . '%';
        $where[] = '(s.name LIKE ? OR s.tagline LIKE ? OR s.description LIKE ? OR s.vendor_name LIKE ? OR s.features LIKE ?)';
        array_push($p, $like, $like, $like, $like, $like);
    }
    if (!empty($o['category'])) {
        $where[] = 's.id IN (SELECT sc.software_id FROM software_categories sc JOIN categories c ON c.id = sc.category_id WHERE c.slug = ?)';
        $p[] = $o['category'];
    }
    if (!empty($o['region'])) {
        $where[] = 'JSON_CONTAINS(s.regions, ?)';
        $p[] = json_encode($o['region']);
    }
    $order = match ($o['sort'] ?? 'featured') {
        'rating' => 's.rating_avg DESC, s.review_count DESC, s.name',
        'reviews' => 's.review_count DESC, s.rating_avg DESC, s.name',
        'name' => 's.name',
        'newest' => 's.created_at DESC',
        default => '(s.featured_until > UTC_TIMESTAMP()) DESC, s.review_count DESC, s.rating_avg DESC, s.name',
    };
    $limit = (int)($o['limit'] ?? 300);
    return all('SELECT s.* FROM software s WHERE ' . implode(' AND ', $where) . " ORDER BY $order LIMIT $limit", $p);
}

function software_by_slug(string $slug, bool $publishedOnly = true): ?array
{
    return one('SELECT * FROM software WHERE slug = ?' . ($publishedOnly ? " AND status = 'PUBLISHED'" : ''), [$slug]);
}

/** [software_id => [category names]] */
function category_names_for(array $ids): array
{
    if (!$ids) return [];
    $rows = all('SELECT sc.software_id, c.name, c.slug FROM software_categories sc JOIN categories c ON c.id = sc.category_id
        WHERE sc.software_id IN (' . in_list($ids) . ') ORDER BY c.sort_order', array_values($ids));
    $m = [];
    foreach ($rows as $r) $m[$r['software_id']][] = $r['name'];
    return $m;
}

function categories_of(int $softwareId): array
{
    return all('SELECT c.name, c.slug FROM software_categories sc JOIN categories c ON c.id = sc.category_id WHERE sc.software_id = ? ORDER BY c.sort_order', [$softwareId]);
}

function all_regions(): array
{
    $counts = [];
    foreach (all("SELECT regions FROM software WHERE status = 'PUBLISHED'") as $r) {
        foreach (json_list($r['regions']) as $reg) $counts[$reg] = ($counts[$reg] ?? 0) + 1;
    }
    uksort($counts, fn($a, $b) => [$counts[$b], $a] <=> [$counts[$a], $b]);
    return $counts;
}

function recent_reviews(int $limit = 4): array
{
    return all("SELECT r.*, s.name AS s_name, s.slug AS s_slug, u.name AS u_name, u.company AS u_company, u.linkedin_name AS u_linkedin_name
        FROM reviews r JOIN software s ON s.id = r.software_id JOIN users u ON u.id = r.user_id
        WHERE r.status = 'PUBLISHED' ORDER BY r.published_at DESC LIMIT " . (int)$limit);
}

function published_reviews_for(int $softwareId): array
{
    return all("SELECT r.*, u.name AS u_name, u.company AS u_company, u.linkedin_name AS u_linkedin_name
        FROM reviews r JOIN users u ON u.id = r.user_id
        WHERE r.software_id = ? AND r.status = 'PUBLISHED' ORDER BY r.published_at DESC", [$softwareId]);
}

function sub_ratings(int $softwareId): array
{
    return one("SELECT COALESCE(AVG(rating_ease),0) ease, COALESCE(AVG(rating_support),0) support, COALESCE(AVG(rating_value),0) value,
        COALESCE(AVG(rating_features),0) features, COALESCE(AVG(recommend),0) recommend
        FROM reviews WHERE software_id = ? AND status = 'PUBLISHED'", [$softwareId]) ?? [];
}

function published_count(): int
{
    return (int)val("SELECT COUNT(*) FROM software WHERE status = 'PUBLISHED'");
}
