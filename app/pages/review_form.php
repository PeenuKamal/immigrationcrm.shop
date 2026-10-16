<?php
$s = software_by_slug($params[0]);
if (!$s) not_found();
$slug = $s['slug'];
$u = require_user("/software/$slug/review");
$meta = meta(['title' => "Review {$s['name']}", 'description' => "Write a verified review of {$s['name']}.", 'path' => "/software/$slug/review", 'noindex' => true]);
$existing = one('SELECT id FROM reviews WHERE software_id = ? AND user_id = ?', [$s['id'], $u['id']]);
$error = null;
$f = [];

$blocker = null;
if (!$u['is_work_email']) $blocker = "You're signed in with a personal address. <a href=\"/signin?next=/software/$slug/review\">Sign in with your work email</a> to leave a review.";
elseif (same_org($u['email_domain'], $s['domain'])) $blocker = 'Your email is on ' . e($s['name']) . "'s own domain, so you can't review it. You can <a href=\"/software/$slug/claim\">manage the listing</a> instead.";
elseif ($existing) $blocker = "You've already reviewed " . e($s['name']) . '. <a href="/account">See its status on your account</a>.';

if (is_post() && !$blocker) {
    check_csrf();
    foreach (['title', 'pros', 'cons', 'use_case', 'reviewer_role', 'firm_size', 'usage_duration', 'job_title', 'company', 'recommend'] as $k) $f[$k] = post($k);
    $stars = [];
    foreach (['rating', 'rating_ease', 'rating_support', 'rating_value', 'rating_features'] as $k) {
        $v = (int)post($k);
        $stars[$k] = ($v >= 1 && $v <= 5) ? $v : null;
        $f[$k] = (string)$v;
    }
    if (in_array(null, $stars, true)) $error = 'Give a star rating for each of the five questions.';
    elseif (mb_strlen($f['title']) < 8) $error = 'Add a headline of at least a few words.';
    elseif (mb_strlen($f['pros']) < 40) $error = 'Describe what works in at least 40 characters — specifics help other firms most.';
    elseif (mb_strlen($f['cons']) < 40) $error = "Describe what doesn't work in at least 40 characters. Every tool has trade-offs.";
    elseif (mb_strlen($f['use_case']) < 30) $error = 'Tell readers how your firm uses it, in at least 30 characters.';
    elseif (!in_array($f['reviewer_role'], REVIEW_ROLES, true) || !in_array($f['firm_size'], FIRM_SIZES, true) || !in_array($f['usage_duration'], DURATIONS, true)) $error = "Choose your role, firm size and how long you've used it.";
    elseif (!in_array($f['recommend'], ['yes', 'no'], true)) $error = "Say whether you'd recommend it to a peer.";
    elseif (post('consent') !== 'on') $error = 'Confirm that the review is your own honest experience.';
    else {
        [$proofId, $proofErr] = store_proof_upload('proof');
        if ($proofErr) $error = $proofErr;
    }
    if (!$error) {
        if ($f['job_title'] || $f['company']) {
            q('UPDATE users SET job_title = COALESCE(NULLIF(?, \'\'), job_title), company = COALESCE(NULLIF(?, \'\'), company) WHERE id = ?', [mb_substr($f['job_title'], 0, 120), mb_substr($f['company'], 0, 120), $u['id']]);
        }
        q('INSERT INTO reviews (software_id, user_id, rating, rating_ease, rating_support, rating_value, rating_features, title, pros, cons, use_case,
            reviewer_role, firm_size, usage_duration, recommend, show_name, verified_email, verified_linkedin, proof_file_id, proof_status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, ?, ?, ?)', [
            $s['id'], $u['id'], $stars['rating'], $stars['rating_ease'], $stars['rating_support'], $stars['rating_value'], $stars['rating_features'],
            mb_substr($f['title'], 0, 140), mb_substr($f['pros'], 0, 4000), mb_substr($f['cons'], 0, 4000), mb_substr($f['use_case'], 0, 2000),
            $f['reviewer_role'], $f['firm_size'], $f['usage_duration'], $f['recommend'] === 'yes' ? 1 : 0, post('show_name') === 'on' ? 1 : 0,
            $u['linkedin_verified_at'] ? 1 : 0, $proofId, $proofId ? 'PENDING' : 'NONE',
        ]);
        $rid = insert_id();
        refresh_review_status($rid);
        redirect("/account?submitted=$rid");
    }
}

function star_field(string $name, string $label, string $value): string
{
    $h = '<div class="row" role="radiogroup" aria-label="' . e($label) . '"><span>' . e($label) . '</span><span class="star-input">';
    for ($n = 5; $n >= 1; $n--) {
        $h .= '<input type="radio" id="' . $name . '-' . $n . '" name="' . $name . '" value="' . $n . '"' . ($value === (string)$n ? ' checked' : '') . '>'
            . '<label for="' . $name . '-' . $n . '" title="' . $n . ' star' . ($n > 1 ? 's' : '') . '">★<span class="sr-only">' . $n . ' stars</span></label>';
    }
    return $h . '</span></div>';
}
$sel = function (string $name, array $opts) use ($f) {
    $h = '<select id="' . $name . '" name="' . $name . '"><option value="" disabled' . (empty($f[$name]) ? ' selected' : '') . '>Choose one</option>';
    foreach ($opts as $o) $h .= '<option' . (($f[$name] ?? '') === $o ? ' selected' : '') . '>' . e($o) . '</option>';
    return $h . '</select>';
};
?>
<div class="wrap" style="max-width:760px">
  <p class="small"><a href="/software/<?= e($slug) ?>">Back to <?= e($s['name']) ?></a></p>
  <div class="page-head"><h1>Review <?= e($s['name']) ?></h1><p>Tell other immigration practitioners what it's really like to use. Your name stays hidden unless you choose to show it.</p></div>
  <?php if ($blocker): ?>
    <div class="notice warn"><p><?= $blocker ?></p></div>
  <?php else: ?>
    <div class="notice<?= $u['linkedin_verified_at'] ? '' : ' warn' ?>"><p>
      <?php if ($u['linkedin_verified_at']): ?>Your work email and LinkedIn are verified, so this review goes live as soon as you submit it.
      <?php else: ?>Your work email (<?= e($u['email']) ?>) is verified. To publish, we need one more proof: <a href="/auth/linkedin/start?next=/software/<?= e($slug) ?>/review">connect LinkedIn</a> (instant), or attach proof of use below (checked within one business day).<?php endif; ?>
    </p></div>
    <form method="post" enctype="multipart/form-data" class="panel">
      <?= csrf_field() ?>
      <?php if ($error): ?><div class="notice error" role="alert"><p><?= e($error) ?></p></div><?php endif; ?>
      <fieldset class="field"><legend>Your ratings</legend><div class="rating-grid">
        <?= star_field('rating', 'Overall', $f['rating'] ?? '') ?>
        <?= star_field('rating_ease', 'Ease of use', $f['rating_ease'] ?? '') ?>
        <?= star_field('rating_support', 'Customer support', $f['rating_support'] ?? '') ?>
        <?= star_field('rating_value', 'Value for money', $f['rating_value'] ?? '') ?>
        <?= star_field('rating_features', 'Immigration features', $f['rating_features'] ?? '') ?>
      </div></fieldset>
      <div class="field"><label for="title">Headline</label><input id="title" name="title" maxlength="140" value="<?= e($f['title'] ?? '') ?>" placeholder="e.g. Saved our team hours on H-1B forms"></div>
      <div class="field"><label for="pros">What works well</label><p class="hint">One point per line. Specific workflows, forms or features help most.</p><textarea id="pros" name="pros"><?= e($f['pros'] ?? '') ?></textarea></div>
      <div class="field"><label for="cons">What doesn't work</label><p class="hint">One point per line. Bugs, missing features, pricing, onboarding.</p><textarea id="cons" name="cons"><?= e($f['cons'] ?? '') ?></textarea></div>
      <div class="field"><label for="use_case">How your firm uses <?= e($s['name']) ?></label><p class="hint">Case types, visa programs, countries, team size using it.</p><textarea id="use_case" name="use_case" style="min-height:90px"><?= e($f['use_case'] ?? '') ?></textarea></div>
      <div class="grid-2">
        <div class="field"><label for="reviewer_role">Your role</label><?= $sel('reviewer_role', REVIEW_ROLES) ?></div>
        <div class="field"><label for="firm_size">Firm size</label><?= $sel('firm_size', FIRM_SIZES) ?></div>
        <div class="field"><label for="usage_duration">How long you've used it</label><?= $sel('usage_duration', DURATIONS) ?></div>
        <fieldset class="field"><legend>Would you recommend it to a peer?</legend><div class="checks">
          <label><input type="radio" name="recommend" value="yes" <?= ($f['recommend'] ?? '') === 'yes' ? 'checked' : '' ?>> Yes</label>
          <label><input type="radio" name="recommend" value="no" <?= ($f['recommend'] ?? '') === 'no' ? 'checked' : '' ?>> No</label>
        </div></fieldset>
        <div class="field"><label for="job_title">Job title <span class="muted">(optional)</span></label><input id="job_title" name="job_title" value="<?= e($f['job_title'] ?? $u['job_title'] ?? '') ?>"></div>
        <div class="field"><label for="company">Firm name <span class="muted">(optional)</span></label><input id="company" name="company" value="<?= e($f['company'] ?? $u['company'] ?? '') ?>"></div>
      </div>
      <div class="field">
        <label for="proof">Proof of use <span class="muted"><?= $u['linkedin_verified_at'] ? '(optional)' : '(needed unless you connect LinkedIn)' ?></span></label>
        <p class="hint">A screenshot of your dashboard with client data hidden, or an invoice. PNG, JPG, WebP or PDF up to 5 MB. Only our moderators see it.</p>
        <input id="proof" name="proof" type="file" accept="image/png,image/jpeg,image/webp,application/pdf">
      </div>
      <div class="field checks" style="display:grid;gap:8px">
        <label><input type="checkbox" name="show_name"><span>Show my name and firm on the review</span></label>
        <label><input type="checkbox" name="consent"><span>This is my own honest experience from the last 24 months. I'm not employed by, paid by or connected to the vendor or a competitor, I haven't been offered anything for this review, and it follows the <a href="/terms#guidelines" target="_blank">review guidelines</a>.</span></label>
      </div>
      <button class="btn stamp">Submit review</button>
    </form>
  <?php endif; ?>
</div>
