<?php
$u = require_user('/account');
$meta = meta(['title' => 'My account', 'description' => 'Your verification status and reviews.', 'path' => '/account', 'noindex' => true]);

if (is_post()) {
    check_csrf();
    if (post('action') === 'profile') {
        q('UPDATE users SET name = NULLIF(?, ""), job_title = NULLIF(?, ""), company = NULLIF(?, "") WHERE id = ?',
            [mb_substr(post('name'), 0, 80), mb_substr(post('job_title'), 0, 80), mb_substr(post('company'), 0, 80), $u['id']]);
        flash('Profile saved.');
    } elseif (post('action') === 'export') {
        header('Content-Type: application/json; charset=utf-8');
        header('Content-Disposition: attachment; filename="immigrationcrm-shop-my-data-' . gmdate('Y-m-d') . '.json"');
        header('Cache-Control: no-store');
        echo json_encode(export_user_data($u), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    } elseif (post('action') === 'delete') {
        if (post('confirm') !== 'DELETE') {
            flash('Type DELETE in capital letters to confirm, then press Delete my account.', 'error');
            redirect('/account#your-data');
        }
        delete_user_account($u);
        logout_user();
        flash('Your account and reviews have been deleted.');
        redirect('/');
    } elseif (post('action') === 'proof') {
        $r = one('SELECT * FROM reviews WHERE id = ? AND user_id = ?', [(int)post('review_id'), $u['id']]);
        if ($r) {
            [$pid, $err] = store_proof_upload('proof');
            if ($err || !$pid) flash($err ?: 'Choose a file before uploading.', 'error');
            else {
                q("UPDATE reviews SET proof_file_id = ?, proof_status = 'PENDING', updated_at = UTC_TIMESTAMP() WHERE id = ?", [$pid, $r['id']]);
                refresh_review_status((int)$r['id']);
                flash("Proof received. We'll check it within one business day.");
            }
        }
    }
    redirect('/account');
}

$mine = all('SELECT r.*, s.name AS s_name, s.slug AS s_slug FROM reviews r JOIN software s ON s.id = r.software_id WHERE r.user_id = ? ORDER BY r.created_at DESC', [$u['id']]);
$owned = (int)val('SELECT COUNT(*) FROM software WHERE owner_id = ?', [$u['id']]);
$li = [
    'connected' => ['', 'LinkedIn connected. Any reviews waiting on it are now live.'],
    'cancelled' => ['warn', 'LinkedIn connection was cancelled. You can try again any time.'],
    'expired' => ['warn', 'That LinkedIn sign-in link expired. Try connecting again.'],
    'failed' => ['error', "LinkedIn didn't confirm your profile. Try again, or upload proof of use instead."],
    'in-use' => ['error', 'That LinkedIn profile is already linked to another account. Each profile can verify one reviewer.'],
    'not-configured' => ['warn', "LinkedIn verification isn't switched on yet. Upload proof of use for now."],
];
$msg = $li[get('linkedin')] ?? take_flash();
$submitted = null;
foreach ($mine as $m) if ((string)$m['id'] === get('submitted')) $submitted = $m;
$proofOk = (bool)array_filter($mine, fn($m) => $m['proof_status'] === 'APPROVED');
?>
<div class="wrap" style="max-width:860px">
  <div class="page-head"><h1>My account</h1><p>Signed in as <?= e($u['email']) ?>.</p></div>
  <?= notice($msg) ?>
  <?php if ($submitted): [$lab, $help] = REVIEW_STATUS[$submitted['status']]; ?>
    <div class="notice<?= $submitted['status'] === 'PUBLISHED' ? '' : ' warn' ?>" role="status"><p><strong>Review of <?= e($submitted['s_name']) ?> received — <?= e(strtolower($lab)) ?>.</strong> <?= e($help) ?></p></div>
  <?php endif; ?>

  <div class="stack">
    <section class="panel" aria-labelledby="ver">
      <h2 id="ver" style="font-size:var(--step-2)">Your verification</h2>
      <ul class="checklist">
        <li class="<?= $u['is_work_email'] ? 'done' : '' ?>"><span class="tick"><?= $u['is_work_email'] ? '✓' : '' ?></span>
          <div><strong>Work email</strong><div class="small muted"><?= $u['is_work_email'] ? e($u['email_domain']) . ' confirmed ' . e(format_date($u['email_verified_at'])) : 'Personal address — sign in with a work email to review.' ?></div></div><span></span></li>
        <li class="<?= $u['linkedin_verified_at'] ? 'done' : '' ?>"><span class="tick"><?= $u['linkedin_verified_at'] ? '✓' : '' ?></span>
          <div><strong>LinkedIn profile</strong><div class="small muted"><?= $u['linkedin_verified_at'] ? 'Connected as ' . e($u['linkedin_name']) : 'Instantly publishes your reviews. We only read your name and LinkedIn ID.' ?></div></div>
          <?php if (!$u['linkedin_verified_at']): ?><a class="btn small secondary" href="/auth/linkedin/start">Connect LinkedIn</a><?php else: ?><span></span><?php endif; ?></li>
        <li class="<?= $proofOk ? 'done' : '' ?>"><span class="tick"><?= $proofOk ? '✓' : '' ?></span>
          <div><strong>Proof of use</strong><div class="small muted">Attached per review. Use it if you'd rather not connect LinkedIn.</div></div><span></span></li>
      </ul>
    </section>

    <section aria-labelledby="my-reviews">
      <h2 id="my-reviews" style="font-size:var(--step-2)">My reviews</h2>
      <?php if (!$mine): ?>
        <div class="empty"><p>You haven't reviewed anything yet.</p><a class="btn stamp" href="/software">Find software you use</a></div>
      <?php else: ?><div class="stack">
        <?php foreach ($mine as $r): [$lab, $help] = REVIEW_STATUS[$r['status']]; ?>
        <div class="panel">
          <div style="display:flex;justify-content:space-between;gap:16px;flex-wrap:wrap">
            <div><h3 style="margin-bottom:4px"><a href="/software/<?= e($r['s_slug']) ?>"><?= e($r['s_name']) ?></a>: <?= e($r['title']) ?></h3><p class="small muted" style="margin:0">Submitted <?= e(format_date($r['created_at'])) ?></p></div>
            <span class="status <?= e($r['status']) ?>"><?= e($lab) ?></span>
          </div>
          <p class="small" style="margin-top:12px"><?= e($r['status'] === 'REJECTED' && $r['reject_reason'] ? $r['reject_reason'] : $help) ?></p>
          <?php if ($r['status'] === 'AWAITING_VERIFICATION'): ?>
          <form method="post" enctype="multipart/form-data" class="inline-form">
            <?= csrf_field() ?><input type="hidden" name="action" value="proof"><input type="hidden" name="review_id" value="<?= (int)$r['id'] ?>">
            <label for="p-<?= (int)$r['id'] ?>" class="sr-only">Proof of use file</label>
            <input id="p-<?= (int)$r['id'] ?>" type="file" name="proof" accept="image/png,image/jpeg,image/webp,application/pdf">
            <button class="btn small">Upload proof</button>
            <?php if (!$u['linkedin_verified_at']): ?><a class="btn small secondary" href="/auth/linkedin/start">Or connect LinkedIn</a><?php endif; ?>
          </form>
          <?php endif; ?>
          <?php if ($r['proof_status'] === 'REJECTED'): ?><p class="small" style="color:var(--cons)">Your last proof couldn't be accepted. Upload a clearer screenshot or an invoice.</p><?php endif; ?>
        </div>
        <?php endforeach; ?>
      </div><?php endif; ?>
    </section>

    <section class="panel" aria-labelledby="prof">
      <h2 id="prof" style="font-size:var(--step-2)">Profile</h2>
      <p class="small muted">Shown on a review only if you tick “Show my name” when writing it.</p>
      <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="profile">
        <div class="grid-2">
          <div class="field"><label for="name">Name</label><input id="name" name="name" value="<?= e($u['name']) ?>"></div>
          <div class="field"><label for="job_title">Job title</label><input id="job_title" name="job_title" value="<?= e($u['job_title']) ?>"></div>
          <div class="field"><label for="company">Firm</label><input id="company" name="company" value="<?= e($u['company']) ?>"></div>
        </div>
        <button class="btn secondary">Save profile</button>
      </form>
    </section>

    <section class="panel" id="your-data" aria-labelledby="data-h">
      <h2 id="data-h" style="font-size:var(--step-2)">Your data</h2>
      <p class="small muted">See our <a href="/privacy">Privacy Policy</a> for what we hold and why.</p>
      <form method="post" style="margin-bottom:28px"><?= csrf_field() ?><input type="hidden" name="action" value="export">
        <h3 style="font-size:1rem;margin-bottom:6px">Download my data</h3>
        <p class="small muted">A file with your account details, reviews, claims and listings.</p>
        <button class="btn secondary">Download my data</button>
      </form>
      <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="delete">
        <h3 style="font-size:1rem;margin-bottom:6px">Delete my account</h3>
        <p class="small muted">Permanently deletes your account, all <?= count($mine) ?> of your reviews and any proof files. Listings you manage stay on the site but become unclaimed. This can't be undone.</p>
        <div class="field" style="max-width:320px"><label for="confirm">Type DELETE to confirm</label><input id="confirm" name="confirm" autocomplete="off" spellcheck="false"></div>
        <button class="btn danger">Delete my account</button>
      </form>
    </section>

    <div style="display:flex;gap:12px;flex-wrap:wrap">
      <?php if ($owned): ?><a class="btn secondary" href="/vendor">Vendor dashboard</a><?php endif; ?>
      <form method="post" action="/signout"><?= csrf_field() ?><button class="btn secondary">Sign out</button></form>
    </div>
  </div>
</div>
