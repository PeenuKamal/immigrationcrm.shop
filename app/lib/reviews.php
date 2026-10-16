<?php
declare(strict_types=1);

/**
 * Verification policy
 * - Every reviewer signs in with a verified work email (personal mailboxes are refused).
 * - On top of that, a review needs ONE more proof: a connected LinkedIn profile, or an
 *   uploaded proof of use that an admin has approved.
 * - Nobody can review software made by their own employer.
 */
function next_status(array $r): string
{
    if ($r['status'] === 'REJECTED') return 'REJECTED';
    if (!$r['verified_email']) return 'AWAITING_VERIFICATION';
    $extra = $r['verified_linkedin'] || $r['proof_status'] === 'APPROVED';
    if (!$extra) return $r['proof_status'] === 'PENDING' ? 'PENDING_PROOF' : 'AWAITING_VERIFICATION';
    if (setting('require_admin_approval') === '1' && $r['status'] !== 'PUBLISHED') return 'PENDING_ADMIN';
    return 'PUBLISHED';
}

function refresh_review_status(int $reviewId): void
{
    $r = one('SELECT * FROM reviews WHERE id = ?', [$reviewId]);
    if (!$r) return;
    $status = next_status($r);
    if ($status !== $r['status']) {
        q('UPDATE reviews SET status = ?, published_at = ?, updated_at = UTC_TIMESTAMP() WHERE id = ?', [
            $status, $status === 'PUBLISHED' ? ($r['published_at'] ?: now()) : $r['published_at'], $reviewId,
        ]);
    }
    recalc_rating((int)$r['software_id']);
    if ($status === 'PUBLISHED' && $r['status'] !== 'PUBLISHED') ping_software((int)$r['software_id']);
}

function refresh_user_reviews(array $u): void
{
    q('UPDATE reviews SET verified_email = ?, verified_linkedin = ? WHERE user_id = ?', [
        ($u['email_verified_at'] && $u['is_work_email']) ? 1 : 0, $u['linkedin_verified_at'] ? 1 : 0, $u['id'],
    ]);
    foreach (all('SELECT id FROM reviews WHERE user_id = ?', [$u['id']]) as $r) refresh_review_status((int)$r['id']);
}

function recalc_rating(int $softwareId): void
{
    q("UPDATE software s SET
        rating_avg = (SELECT COALESCE(ROUND(AVG(rating), 1), 0) FROM reviews WHERE software_id = s.id AND status = 'PUBLISHED'),
        review_count = (SELECT COUNT(*) FROM reviews WHERE software_id = s.id AND status = 'PUBLISHED')
        WHERE s.id = ?", [$softwareId]);
}

function ping_software(int $softwareId): void
{
    $s = one('SELECT slug FROM software WHERE id = ?', [$softwareId]);
    if ($s) ping_indexnow(["/software/{$s['slug']}", "/software/{$s['slug']}/alternatives", '/']);
}

const REVIEW_STATUS = [
    'AWAITING_VERIFICATION' => ['Needs one more proof', 'Connect LinkedIn or upload proof that you use this software. Your review goes live as soon as one is confirmed.'],
    'PENDING_PROOF' => ['Proof under review', "We're checking your proof of use. This usually takes one business day."],
    'PENDING_ADMIN' => ['Awaiting approval', 'Your review is verified and waiting for a final editorial check.'],
    'PUBLISHED' => ['Live', 'Your verified review is public.'],
    'REJECTED' => ['Not published', "This review didn't meet our guidelines."],
];

const REVIEW_ROLES = ['Immigration attorney', 'RCIC / licensed consultant', 'Migration agent', 'Paralegal / case manager', 'Firm owner / partner', 'Operations / IT', 'HR / global mobility', 'Other'];
const FIRM_SIZES = ['Solo', '2–10 people', '11–50 people', '51–200 people', '200+ people'];
const DURATIONS = ['Less than 6 months', '6–12 months', '1–2 years', '2+ years'];

const PROOF_TYPES = ['image/png' => "\x89PNG", 'image/jpeg' => "\xFF\xD8\xFF", 'image/webp' => 'RIFF', 'application/pdf' => '%PDF'];

/** Validates and stores an uploaded proof file in the database. Returns [id, error]. */
function store_proof_upload(string $field): array
{
    $f = $_FILES[$field] ?? null;
    if (!$f || ($f['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) return [null, null];
    if ($f['error'] !== UPLOAD_ERR_OK) return [null, 'The upload failed. Keep the file under 5 MB and try again.'];
    if ($f['size'] > 5 * 1024 * 1024) return [null, 'Keep the file under 5 MB.'];
    $data = file_get_contents($f['tmp_name']);
    $mime = null;
    foreach (PROOF_TYPES as $type => $magic) {
        if (str_starts_with($data, $magic) && ($type !== 'image/webp' || substr($data, 8, 4) === 'WEBP')) $mime = $type;
    }
    if (!$mime) return [null, 'Upload a PNG, JPG, WebP or PDF file.'];
    q('INSERT INTO proof_files (mime, size, data) VALUES (?, ?, ?)', [$mime, strlen($data), $data]);
    return [insert_id(), null];
}
