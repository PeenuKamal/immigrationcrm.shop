<?php
declare(strict_types=1);

const SCHEMA_VERSION = '2';

/** Listings added to the starter directory in later versions; added once on upgrade. */
const SEED_ADDITIONS = ['2' => ['ImmiPractice']];

/** Creates any missing tables. Safe to run on every upgrade. */
function run_schema(): void
{
    $sql = file_get_contents(APP . '/schema.sql');
    foreach (array_filter(array_map('trim', explode(';', $sql))) as $stmt) db()->exec($stmt);
    $from = setting('schema_version');
    if ($from !== '' && $from !== null) {
        // Existing site: add only the listings introduced since its version.
        $names = [];
        foreach (SEED_ADDITIONS as $v => $list) if (version_compare((string)$v, (string)$from, '>')) $names = array_merge($names, $list);
        if ($names) seed_directory($names);
    }
    save_setting('schema_version', SCHEMA_VERSION);
}

/** Adds the starter categories and listings. Skips anything that already exists. */
function seed_directory(?array $only = null): int
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
        if ($only !== null && !in_array($s['name'], $only, true)) continue;
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

/**
 * Routine data cleanup promised in the Privacy Policy. Runs on a small share of requests,
 * so it needs no cron job on shared hosting.
 */
function housekeeping(bool $force = false): void
{
    if (!$force && random_int(1, 50) !== 1) return;
    try {
        q('DELETE FROM otp_codes WHERE created_at < UTC_TIMESTAMP() - INTERVAL 1 DAY');
        // Proof files: 12 months after the moderation decision
        $old = all("SELECT id, proof_file_id FROM reviews WHERE proof_file_id IS NOT NULL AND proof_status IN ('APPROVED','REJECTED') AND updated_at < UTC_TIMESTAMP() - INTERVAL 12 MONTH");
        foreach ($old as $r) {
            q('UPDATE reviews SET proof_file_id = NULL WHERE id = ?', [$r['id']]);
            q('DELETE FROM proof_files WHERE id = ?', [$r['proof_file_id']]);
        }
        // Uploaded files that never got attached to a review
        q('DELETE p FROM proof_files p LEFT JOIN reviews r ON r.proof_file_id = p.id WHERE r.id IS NULL AND p.created_at < UTC_TIMESTAMP() - INTERVAL 7 DAY');
        // Rejected reviews: 12 months for appeals, then deleted
        q("DELETE FROM reviews WHERE status = 'REJECTED' AND updated_at < UTC_TIMESTAMP() - INTERVAL 12 MONTH");
    } catch (Throwable $ex) {
        error_log('[housekeeping] ' . $ex->getMessage());
    }
}

/** Everything we hold about a user, for "Download my data". */
function export_user_data(array $u): array
{
    $reviews = all('SELECT r.id, s.name AS software, r.rating, r.rating_ease, r.rating_support, r.rating_value, r.rating_features, r.title, r.pros, r.cons,
        r.use_case, r.reviewer_role, r.firm_size, r.usage_duration, r.recommend, r.show_name, r.verified_email, r.verified_linkedin,
        r.proof_status, (r.proof_file_id IS NOT NULL) AS proof_file_on_record, r.status, r.reject_reason, r.vendor_response, r.published_at, r.created_at, r.updated_at
        FROM reviews r JOIN software s ON s.id = r.software_id WHERE r.user_id = ?', [$u['id']]);
    return [
        'exported_at' => gmdate('c'),
        'service' => SITE_NAME,
        'account' => array_intersect_key($u, array_flip(['email', 'email_domain', 'email_verified_at', 'is_work_email', 'name', 'job_title', 'company', 'linkedin_name', 'linkedin_email', 'linkedin_verified_at', 'role', 'created_at'])),
        'reviews' => $reviews,
        'listing_claims' => all('SELECT s.name AS software, c.note, c.status, c.created_at FROM listing_claims c JOIN software s ON s.id = c.software_id WHERE c.user_id = ?', [$u['id']]),
        'listings_managed' => all('SELECT name, website, status, featured_until, created_at FROM software WHERE owner_id = ?', [$u['id']]),
        'listings_submitted' => all('SELECT name, website, status, created_at FROM software WHERE submitted_by = ?', [$u['email']]),
        'payments' => all('SELECT s.name AS software, p.amount_cents, p.currency, p.months, p.status, p.created_at FROM payments p JOIN software s ON s.id = p.software_id WHERE s.owner_id = ?', [$u['id']]),
        'note' => 'Proof-of-use files are not included in this export. Ask us if you need a copy. Server logs are held by our hosting provider and are not linked to your account.',
    ];
}

/** Deletes an account and everything tied to it, then recalculates affected ratings. */
function delete_user_account(array $u): void
{
    $softwareIds = array_column(all('SELECT DISTINCT software_id FROM reviews WHERE user_id = ?', [$u['id']]), 'software_id');
    $proofIds = array_filter(array_column(all('SELECT proof_file_id FROM reviews WHERE user_id = ?', [$u['id']]), 'proof_file_id'));
    db()->beginTransaction();
    try {
        q('DELETE FROM reviews WHERE user_id = ?', [$u['id']]);
        if ($proofIds) q('DELETE FROM proof_files WHERE id IN (' . in_list($proofIds) . ')', array_values($proofIds));
        q('UPDATE software SET claimed = 0, owner_id = NULL WHERE owner_id = ?', [$u['id']]);
        q('UPDATE software SET submitted_by = NULL WHERE submitted_by = ?', [$u['email']]);
        q('DELETE FROM listing_claims WHERE user_id = ?', [$u['id']]);
        q('DELETE FROM otp_codes WHERE email = ?', [$u['email']]);
        q('DELETE FROM users WHERE id = ?', [$u['id']]);
        db()->commit();
    } catch (Throwable $ex) {
        db()->rollBack();
        throw $ex;
    }
    foreach ($softwareIds as $sid) recalc_rating((int)$sid);
}
