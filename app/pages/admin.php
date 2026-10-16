<?php
$admin = require_admin();
$meta = meta(['title' => 'Moderation', 'description' => 'Moderation dashboard.', 'path' => '/admin', 'noindex' => true]);
$tab = get('tab', 'proofs');

function notify_user(int $userId, string $subject, string $body): void
{
    $u = one('SELECT email FROM users WHERE id = ?', [$userId]);
    if ($u) send_mail($u['email'], $subject, $body . "\n\n— " . SITE_NAME);
}

if (is_post()) {
    check_csrf();
    $id = (int)post('id');
    $approve = post('decision') === 'approve';
    switch (post('action')) {
        case 'proof':
            $r = one('SELECT * FROM reviews WHERE id = ?', [$id]);
            if ($r) {
                q('UPDATE reviews SET proof_status = ?, updated_at = UTC_TIMESTAMP() WHERE id = ?', [$approve ? 'APPROVED' : 'REJECTED', $id]);
                refresh_review_status($id);
                $after = one('SELECT status FROM reviews WHERE id = ?', [$id]);
                notify_user((int)$r['user_id'], $approve ? 'Your proof of use was accepted' : "We couldn't accept your proof of use",
                    $approve ? ($after['status'] === 'PUBLISHED' ? "Your review \"{$r['title']}\" is now live." : "Your proof was accepted. Your review \"{$r['title']}\" is moving to the next step.")
                        : "Your upload for \"{$r['title']}\" didn't show that you use the software. Upload a clearer screenshot or an invoice at " . url('/account') . '.');
            }
            break;
        case 'review':
            $r = one('SELECT * FROM reviews WHERE id = ?', [$id]);
            if ($r) {
                if ($approve) {
                    q("UPDATE reviews SET status = 'PUBLISHED', published_at = COALESCE(published_at, UTC_TIMESTAMP()), updated_at = UTC_TIMESTAMP() WHERE id = ?", [$id]);
                    notify_user((int)$r['user_id'], 'Your review is live', "Your review \"{$r['title']}\" has been published.");
                } else {
                    $reason = post('reason') ?: "This review didn't meet our guidelines.";
                    q("UPDATE reviews SET status = 'REJECTED', reject_reason = ?, updated_at = UTC_TIMESTAMP() WHERE id = ?", [mb_substr($reason, 0, 500), $id]);
                    notify_user((int)$r['user_id'], "Your review wasn't published", "Your review \"{$r['title']}\" wasn't published. Reason: $reason");
                }
                recalc_rating((int)$r['software_id']);
                ping_software((int)$r['software_id']);
            }
            break;
        case 'listing':
            q('UPDATE software SET status = ?, updated_at = UTC_TIMESTAMP() WHERE id = ?', [$approve ? 'PUBLISHED' : 'REJECTED', $id]);
            if ($approve) ping_software($id);
            break;
        case 'claim':
            $c = one('SELECT * FROM listing_claims WHERE id = ?', [$id]);
            if ($c) {
                q('UPDATE listing_claims SET status = ? WHERE id = ?', [$approve ? 'PUBLISHED' : 'REJECTED', $id]);
                if ($approve) {
                    $owner = one('SELECT email_domain FROM users WHERE id = ?', [$c['user_id']]);
                    // Without a verified domain, adopt the claimant's company domain so self-review blocking works.
                    q('UPDATE software SET claimed = 1, owner_id = ?, domain = COALESCE(domain, ?) WHERE id = ?', [$c['user_id'], $owner['email_domain'] ?? null, $c['software_id']]);
                    notify_user((int)$c['user_id'], 'Your listing claim was approved', 'You can now manage your listing at ' . url("/vendor/{$c['software_id']}") . '.');
                }
            }
            break;
        case 'domain':
            q('UPDATE software SET domain = ? WHERE id = ?', [domain_from_url(post('domain')), $id]);
            break;
        case 'featured':
            q('UPDATE software SET featured_until = ? WHERE id = ?', [post('until') ? post('until') . ' 23:59:59' : null, $id]);
            break;
        case 'settings':
            foreach (array_keys(SETTING_DEFAULTS) as $k) {
                if (!array_key_exists($k, $_POST)) continue;
                $v = post($k);
                if (in_array($k, ['smtp_pass', 'linkedin_client_secret', 'stripe_secret_key', 'stripe_webhook_secret'], true) && $v === '') continue; // blank = keep
                save_setting($k, $k === 'app_url' ? rtrim($v, '/') : $v);
            }
            save_setting('require_admin_approval', post('require_admin_approval') === '1' ? '1' : '0');
            settings_all(true);
            flash('Settings saved.');
            break;
        case 'test_email':
            $ok = send_mail($admin['email'], 'Test email from ' . SITE_NAME, 'Email sending works.');
            flash($ok ? "Test email sent to {$admin['email']}." : "The test email couldn't be sent. Check the email settings.", $ok ? '' : 'error');
            break;
    }
    redirect("/admin?tab=$tab");
}

$reviewRows = fn(string $status) => all("SELECT r.*, s.name AS s_name, s.slug AS s_slug, s.domain AS s_domain, u.email AS u_email, u.linkedin_name AS u_linkedin, u.email_domain AS u_domain
    FROM reviews r JOIN software s ON s.id = r.software_id JOIN users u ON u.id = r.user_id WHERE r.status = ? ORDER BY r.created_at DESC LIMIT 100", [$status]);
$counts = [
    'proofs' => (int)val("SELECT COUNT(*) FROM reviews WHERE status = 'PENDING_PROOF'"),
    'approval' => (int)val("SELECT COUNT(*) FROM reviews WHERE status = 'PENDING_ADMIN'"),
    'listings' => (int)val("SELECT COUNT(*) FROM software WHERE status = 'PENDING'"),
    'claims' => (int)val("SELECT COUNT(*) FROM listing_claims WHERE status = 'PENDING'"),
];
$tabs = ['proofs' => 'Proofs to check', 'approval' => 'Reviews to approve', 'listings' => 'New listings', 'claims' => 'Listing claims', 'published' => 'Published reviews', 'directory' => 'Directory', 'settings' => 'Settings'];
$decide = function (string $action, int $id, bool $reason = false, string $rejectLabel = 'Reject') {
    return '<div class="inline-form">'
        . '<form method="post">' . csrf_field() . '<input type="hidden" name="action" value="' . $action . '"><input type="hidden" name="id" value="' . $id . '"><input type="hidden" name="decision" value="approve"><button class="btn small stamp">Approve</button></form>'
        . '<form method="post" class="inline-form">' . csrf_field() . '<input type="hidden" name="action" value="' . $action . '"><input type="hidden" name="id" value="' . $id . '"><input type="hidden" name="decision" value="reject">'
        . ($reason ? '<label class="sr-only" for="reason-' . $id . '">Reason</label><input id="reason-' . $id . '" type="text" name="reason" placeholder="Reason shown to reviewer">' : '')
        . '<button class="btn small danger">' . e($rejectLabel) . '</button></form></div>';
};
?>
<div class="wrap">
  <div class="page-head"><h1>Moderation</h1></div>
  <nav class="tabs" aria-label="Queues">
    <?php foreach ($tabs as $k => $label): ?><a class="chip" href="/admin?tab=<?= $k ?>" aria-current="<?= $tab === $k ? 'true' : 'false' ?>"><?= e($label) ?><?= !empty($counts[$k]) ? " ({$counts[$k]})" : '' ?></a><?php endforeach; ?>
  </nav>
  <?= notice(take_flash()) ?>

<?php if ($tab === 'proofs' || $tab === 'approval'):
  $rows = $reviewRows($tab === 'proofs' ? 'PENDING_PROOF' : 'PENDING_ADMIN');
  if (!$rows): ?><div class="empty"><p>Nothing waiting. New items appear here as reviewers submit them.</p></div>
  <?php else: ?><div class="stack"><?php foreach ($rows as $r): ?>
    <div class="panel">
      <div style="display:flex;justify-content:space-between;gap:16px;flex-wrap:wrap">
        <div><h3 style="margin-bottom:4px"><?= e($r['s_name']) ?>: <?= e($r['title']) ?> <span class="muted small">· <?= (int)$r['rating'] ?>/5</span></h3>
          <p class="small muted" style="margin:0"><?= e($r['u_email']) ?> · <?= e($r['reviewer_role']) ?> · <?= e($r['firm_size']) ?> · <?= e(format_date($r['created_at'])) ?> · LinkedIn: <?= e($r['u_linkedin'] ?: 'not connected') ?></p>
          <?php if (same_org($r['u_domain'], $r['s_domain'])): ?><p class="small" style="color:var(--cons)">Reviewer's domain matches the vendor's domain.</p><?php endif; ?></div>
        <?php if ($tab === 'proofs' && $r['proof_file_id']): ?><a class="btn small secondary" href="/admin/proof/<?= (int)$r['id'] ?>" target="_blank" rel="noopener">Open proof file</a><?php endif; ?>
      </div>
      <details style="margin:12px 0"><summary class="small">Read review</summary>
        <p class="small"><strong>Pros:</strong> <?= nl2br(e($r['pros'])) ?></p><p class="small"><strong>Cons:</strong> <?= nl2br(e($r['cons'])) ?></p><p class="small"><strong>Use:</strong> <?= e($r['use_case']) ?></p></details>
      <?= $tab === 'proofs' ? $decide('proof', (int)$r['id'], false, 'Reject proof') : $decide('review', (int)$r['id'], true) ?>
    </div>
  <?php endforeach; ?></div><?php endif; ?>

<?php elseif ($tab === 'listings'):
  $rows = all("SELECT * FROM software WHERE status = 'PENDING' ORDER BY created_at DESC");
  if (!$rows): ?><div class="empty"><p>No listings waiting.</p></div>
  <?php else: ?><div class="stack"><?php foreach ($rows as $s): ?>
    <div class="panel"><h3 style="margin-bottom:4px"><?= e($s['name']) ?></h3>
      <p class="small muted"><?= e($s['website']) ?> · submitted by <?= e($s['submitted_by']) ?> · <?= e(format_date($s['created_at'])) ?></p>
      <p><?= e($s['tagline']) ?></p><details style="margin-bottom:12px"><summary class="small">Description</summary><p class="small"><?= e($s['description']) ?></p></details>
      <?= $decide('listing', (int)$s['id']) ?></div>
  <?php endforeach; ?></div><?php endif; ?>

<?php elseif ($tab === 'claims'):
  $rows = all("SELECT c.*, s.name, s.slug, s.domain, u.email FROM listing_claims c JOIN software s ON s.id = c.software_id JOIN users u ON u.id = c.user_id WHERE c.status = 'PENDING'");
  if (!$rows): ?><div class="empty"><p>No claims waiting.</p></div>
  <?php else: ?><div class="stack"><?php foreach ($rows as $c): ?>
    <div class="panel"><h3 style="margin-bottom:4px"><a href="/software/<?= e($c['slug']) ?>"><?= e($c['name']) ?></a></h3>
      <p class="small muted"><?= e($c['email']) ?> wants to manage this listing · listing domain: <?= e($c['domain'] ?: 'none set') ?></p>
      <?php if ($c['note']): ?><p class="small">“<?= e($c['note']) ?>”</p><?php endif; ?>
      <?= $decide('claim', (int)$c['id']) ?></div>
  <?php endforeach; ?></div><?php endif; ?>

<?php elseif ($tab === 'published'):
  $rows = $reviewRows('PUBLISHED');
  if (!$rows): ?><div class="empty"><p>No published reviews yet.</p></div>
  <?php else: ?><div class="table-scroll panel"><table class="data">
    <thead><tr><th>Software</th><th>Review</th><th>Reviewer</th><th>Checks</th><th>Unpublish</th></tr></thead><tbody>
    <?php foreach ($rows as $r): ?><tr>
      <td><?= e($r['s_name']) ?></td><td><a href="/software/<?= e($r['s_slug']) ?>#review-<?= (int)$r['id'] ?>"><?= e($r['title']) ?></a> (<?= (int)$r['rating'] ?>/5)</td><td><?= e($r['u_email']) ?></td>
      <td><?= e(implode(', ', array_filter([$r['verified_email'] ? 'email' : '', $r['verified_linkedin'] ? 'LinkedIn' : '', $r['proof_status'] === 'APPROVED' ? 'proof' : '']))) ?></td>
      <td><form method="post" class="inline-form"><?= csrf_field() ?><input type="hidden" name="action" value="review"><input type="hidden" name="id" value="<?= (int)$r['id'] ?>"><input type="hidden" name="decision" value="reject">
        <input type="text" name="reason" placeholder="Reason" aria-label="Reason"><button class="btn small danger">Unpublish</button></form></td>
    </tr><?php endforeach; ?></tbody></table></div><?php endif; ?>

<?php elseif ($tab === 'directory'):
  $rows = all("SELECT * FROM software WHERE status = 'PUBLISHED' ORDER BY name"); ?>
  <div class="table-scroll panel">
    <p class="small muted">The domain blocks self-reviews and lets vendors claim listings instantly.</p>
    <table class="data"><thead><tr><th>Listing</th><th>Verified domain</th><th>Featured until</th><th>Owner</th></tr></thead><tbody>
    <?php foreach ($rows as $s): ?><tr>
      <td><a href="/software/<?= e($s['slug']) ?>"><?= e($s['name']) ?></a></td>
      <td><form method="post" class="inline-form"><?= csrf_field() ?><input type="hidden" name="action" value="domain"><input type="hidden" name="id" value="<?= (int)$s['id'] ?>">
        <input type="text" name="domain" value="<?= e($s['domain']) ?>" placeholder="example.com" aria-label="Domain for <?= e($s['name']) ?>"><button class="btn small secondary">Save</button></form></td>
      <td><form method="post" class="inline-form"><?= csrf_field() ?><input type="hidden" name="action" value="featured"><input type="hidden" name="id" value="<?= (int)$s['id'] ?>">
        <input type="date" name="until" value="<?= $s['featured_until'] ? e(substr($s['featured_until'], 0, 10)) : '' ?>" aria-label="Featured until for <?= e($s['name']) ?>"><button class="btn small secondary">Set</button></form></td>
      <td class="small"><?= $s['claimed'] ? 'Claimed' : '—' ?></td>
    </tr><?php endforeach; ?></tbody></table>
  </div>

<?php elseif ($tab === 'settings'):
  $st = settings_all();
  $field = function (string $k, string $label, string $hint = '', string $type = 'text') use ($st) {
      $secret = $type === 'password';
      $val = $secret ? '' : $st[$k];
      $ph = $secret && $st[$k] !== '' ? 'Saved — leave blank to keep' : '';
      return '<div class="field"><label for="s-' . $k . '">' . e($label) . '</label>' . ($hint ? '<p class="hint">' . $hint . '</p>' : '')
          . '<input id="s-' . $k . '" name="' . $k . '" type="' . $type . '" value="' . e($val) . '" placeholder="' . e($ph) . '" autocomplete="off"></div>';
  }; ?>
  <form method="post" class="stack"><?= csrf_field() ?><input type="hidden" name="action" value="settings">
    <section class="panel"><h2 style="font-size:var(--step-2)">Site</h2>
      <div class="grid-2">
        <?= $field('app_url', 'Site address', 'For example https://immigrationcrm.shop') ?>
        <?= $field('admin_emails', 'Admin emails', 'Separate several with commas.') ?>
        <?= $field('contact_email', 'Contact email shown on the site') ?>
      </div>
      <div class="field checks"><label><input type="checkbox" name="require_admin_approval" value="1"<?= $st['require_admin_approval'] === '1' ? ' checked' : '' ?>><span>Hold every review for my approval, even when fully verified</span></label></div>
    </section>
    <section class="panel"><h2 style="font-size:var(--step-2)">Email (login codes)</h2>
      <div class="grid-2">
        <?= $field('smtp_host', 'SMTP server', 'Hostinger: smtp.hostinger.com') ?>
        <?= $field('smtp_port', 'Port', '465 (SSL) or 587 (TLS)') ?>
        <?= $field('smtp_encryption', 'Encryption', 'ssl for port 465, tls for port 587') ?>
        <?= $field('smtp_user', 'Email address', 'e.g. otp@immigrationcrm.shop') ?>
        <?= $field('smtp_pass', 'Email password', '', 'password') ?>
        <?= $field('email_from_name', 'Sender name') ?>
      </div>
    </section>
    <section class="panel"><h2 style="font-size:var(--step-2)">LinkedIn verification</h2>
      <p class="small muted">Create an app at linkedin.com/developers, add the product “Sign In with LinkedIn using OpenID Connect”, and add this redirect URL: <code><?= e(url('/auth/linkedin/callback')) ?></code></p>
      <div class="grid-2"><?= $field('linkedin_client_id', 'Client ID') ?><?= $field('linkedin_client_secret', 'Client secret', '', 'password') ?></div>
    </section>
    <section class="panel"><h2 style="font-size:var(--step-2)">Stripe (featured listings)</h2>
      <p class="small muted">In Stripe → Developers → Webhooks, add <code><?= e(url('/stripe/webhook')) ?></code> with the event <code>checkout.session.completed</code>.</p>
      <div class="grid-2">
        <?= $field('stripe_secret_key', 'Secret key', '', 'password') ?>
        <?= $field('stripe_webhook_secret', 'Webhook signing secret', '', 'password') ?>
        <?= $field('featured_price_cents', 'Price per month, in cents/paise', '9900 = $99.00 · 799900 = ₹7,999') ?>
        <?= $field('featured_currency', 'Currency', 'usd, inr, cad, aud, gbp or eur') ?>
      </div>
    </section>
    <section class="panel"><h2 style="font-size:var(--step-2)">Search engines</h2>
      <p class="small muted">Paste only the code inside <code>content="…"</code> from each verification meta tag.</p>
      <div class="grid-2">
        <?= $field('google_verification', 'Google Search Console') ?>
        <?= $field('bing_verification', 'Bing Webmaster Tools') ?>
        <?= $field('yandex_verification', 'Yandex Webmaster') ?>
        <?= $field('indexnow_key', 'IndexNow key', 'Created for you during setup.') ?>
      </div>
    </section>
    <section class="panel"><h2 style="font-size:var(--step-2)">Legal details (Terms and Privacy Policy)</h2>
      <p class="small muted">These fill in the highlighted blanks on the <a href="/terms" target="_blank">Terms</a> and <a href="/privacy" target="_blank">Privacy Policy</a>. Have your lawyer confirm them.</p>
      <div class="grid-2">
        <?= $field('legal_entity_name', 'Legal name of the business', 'e.g. The Digi Sparrow Private Limited') ?>
        <?= $field('legal_entity_type', 'Type and registration', 'e.g. a private limited company incorporated in India (CIN …)') ?>
        <?= $field('legal_address', 'Registered address') ?>
        <?= $field('legal_country', 'Country of the business', 'e.g. India') ?>
        <?= $field('governing_law', 'Governing law', 'e.g. the laws of India') ?>
        <?= $field('legal_venue', 'Courts / venue for disputes', 'e.g. the courts at Ludhiana, Punjab') ?>
        <?= $field('grievance_officer_name', 'Grievance Officer name', 'Required in India for platforms hosting user content') ?>
        <?= $field('grievance_officer_email', 'Grievance Officer email') ?>
        <?= $field('privacy_email', 'Privacy contact email', 'Where people send data requests') ?>
        <?= $field('hosting_location', 'Where data is hosted', 'e.g. Hostinger data centres in India (or the region you chose)') ?>
        <?= $field('legal_effective_date', 'Effective date of the current versions', 'YYYY-MM-DD') ?>
      </div>
    </section>
    <div><button class="btn">Save settings</button></div>
  </form>
  <form method="post" style="margin-top:16px"><?= csrf_field() ?><input type="hidden" name="action" value="test_email"><button class="btn secondary">Send me a test email</button></form>
<?php endif; ?>
</div>
