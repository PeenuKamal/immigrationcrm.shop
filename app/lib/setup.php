<?php
declare(strict_types=1);

const SCHEMA_VERSION = '1';

/** Creates any missing tables. Safe to run on every upgrade. */
function run_schema(): void
{
    $sql = file_get_contents(APP . '/schema.sql');
    foreach (array_filter(array_map('trim', explode(';', $sql))) as $stmt) db()->exec($stmt);
    save_setting('schema_version', SCHEMA_VERSION);
}

/** Adds the starter categories and listings. Skips anything that already exists. */
function seed_directory(): int
{
    $data = json_decode(file_get_contents(APP . '/seed-data.json'), true);
    $catIds = [];
    foreach ($data['categories'] as $i => $c) {
        $id = val('SELECT id FROM categories WHERE slug = ?', [$c['slug']]);
        if (!$id) {
            q('INSERT INTO categories (slug, name, description, sort_order) VALUES (?, ?, ?, ?)', [$c['slug'], $c['name'], $c['description'], $i]);
            $id = insert_id();
        }
        $catIds[$c['slug']] = (int)$id;
    }
    $added = 0;
    foreach ($data['software'] as $s) {
        $slug = slugify($s['name']);
        if (val('SELECT id FROM software WHERE slug = ?', [$slug])) continue;
        q("INSERT INTO software (slug, name, tagline, description, website, domain, vendor_name, deployment, regions, audiences, features, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'PUBLISHED')", [
            $slug, $s['name'], $s['tagline'], $s['description'], $s['website'] ?? null, domain_from_url($s['website'] ?? null),
            $s['vendorName'] ?? null, $s['deployment'] ?? null, json_encode($s['regions']), json_encode($s['audiences']), json_encode($s['features']),
        ]);
        $sid = insert_id();
        foreach ($s['categories'] as $c) q('INSERT INTO software_categories (software_id, category_id) VALUES (?, ?)', [$sid, $catIds[$c]]);
        $added++;
    }
    return $added;
}
