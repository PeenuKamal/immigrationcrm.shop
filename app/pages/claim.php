<?php
$s = software_by_slug($params[0], false);
if (!$s) not_found();
$slug = $s['slug'];
$u = require_user("/software/$slug/claim");
$meta = meta(['title' => "Claim {$s['name']}", 'description' => "Manage the {$s['name']} listing.", 'path' => "/software/$slug/claim", 'noindex' => true]);
$instant = $s['domain'] && same_org($u['email_domain'], $s['domain']);
if (is_post() && !$s['claimed'] && $u['is_work_email']) {
    check_csrf();
    if ($instant) {
        q('UPDATE software SET claimed = 1, owner_id = ? WHERE id = ?', [$u['id'], $s['id']]);
        q("UPDATE listing_claims SET status = 'REJECTED' WHERE software_id = ? AND user_id <> ?", [$s['id'], $u['id']]);
        flash('Listing claimed. You can now edit it and reply to reviews.');
        redirect("/vendor/{$s['id']}");
    }
    if (!val('SELECT COUNT(*) FROM listing_claims WHERE software_id = ? AND user_id = ?', [$s['id'], $u['id']])) {
        q('INSERT INTO listing_claims (software_id, user_id, note) VALUES (?, ?, ?)', [$s['id'], $u['id'], mb_substr(post('note'), 0, 1000)]);
    }
    redirect("/software/$slug/claim?sent=1");
}
?>
<div class="wrap" style="max-width:680px">
  <p class="small"><a href="/software/<?= e($slug) ?>">Back to <?= e($s['name']) ?></a></p>
  <div class="page-head"><h1>Claim <?= e($s['name']) ?></h1><p>Claiming lets you edit the listing, reply to reviews and buy featured placement. It doesn't let you edit or remove reviews.</p></div>
  <?php if ($s['claimed']): ?>
    <div class="notice warn"><p>This listing is already managed by the vendor. If that's wrong, email <?= e(contact_email()) ?>.</p></div>
  <?php elseif (get('sent')): ?>
    <div class="notice"><p><strong>Claim request sent.</strong> An editor will confirm you work for <?= e($s['vendor_name'] ?: $s['name']) ?> and email you, usually within one business day.</p></div>
  <?php elseif (!$u['is_work_email']): ?>
    <div class="notice warn"><p>Sign in with your company email to claim a listing. <a href="/signin?next=/software/<?= e($slug) ?>/claim">Switch account</a></p></div>
  <?php else: ?>
    <form method="post" class="panel"><?= csrf_field() ?>
      <?php if ($instant): ?>
        <p>Your email (<?= e($u['email']) ?>) matches <?= e($s['domain']) ?>, so you can claim this listing right away.</p>
      <?php else: ?>
        <p>Your email domain (<?= e($u['email_domain']) ?>) <?= $s['domain'] ? "doesn't match " . e($s['domain']) : "can't be matched automatically" ?>, so an editor will check your claim.</p>
        <div class="field"><label for="note">How are you connected to <?= e($s['name']) ?>?</label><textarea id="note" name="note" placeholder="Your role, and the product's website if it's missing" style="min-height:90px"></textarea></div>
      <?php endif; ?>
      <button class="btn"><?= $instant ? 'Claim listing' : 'Send claim request' ?></button>
    </form>
  <?php endif; ?>
</div>
