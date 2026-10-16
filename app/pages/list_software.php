<?php
$u = current_user();
$meta = meta(['title' => 'List your software', 'description' => 'Add your immigration software to the directory for free.', 'path' => '/list-your-software']);
$error = null;
$f = ['deployment' => 'Cloud'];
$cats = [];
if (is_post() && $u && $u['is_work_email']) {
    check_csrf();
    [$f, $cats] = read_listing_form();
    $error = validate_listing($f, $cats);
    if (!$error) {
        $domain = domain_from_url($f['website']);
        $slug = slugify($f['name']);
        $dupe = one("SELECT name, slug, domain FROM software WHERE (slug = ? OR domain = ?) AND status <> 'REJECTED' LIMIT 1", [$slug, $domain]);
        if ($dupe) {
            $error = "{$dupe['name']} is already listed. Open its page and choose “Claim this listing” to manage it.";
        } else {
            if (val('SELECT COUNT(*) FROM software WHERE slug = ?', [$slug])) $slug .= '-' . base_convert((string)time(), 10, 36);
            $staff = same_org($u['email_domain'], $domain);
            q('INSERT INTO software (name, website, vendor_name, tagline, description, deployment, regions, audiences, features, pricing_note, logo_url,
                slug, domain, status, claimed, owner_id, submitted_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
                array_merge(listing_values($f), [$slug, $domain, $staff ? 'PUBLISHED' : 'PENDING', $staff ? 1 : 0, $staff ? $u['id'] : null, $u['email']]));
            $id = insert_id();
            save_listing_categories($id, $cats);
            if ($staff) {
                ping_software($id);
                flash('Your listing is live.');
                redirect("/vendor/$id");
            }
            redirect('/list-your-software?queued=' . rawurlencode($f['name']));
        }
    }
}
?>
<div class="wrap" style="max-width:820px">
  <div class="page-head"><h1>List your software</h1><p>Listing is free. If your work email is on the product's domain, your listing goes live immediately and you manage it from the vendor dashboard.</p></div>
  <?php if ($q = get('queued')): ?>
    <div class="notice"><p><strong><?= e($q) ?> is in the review queue.</strong> Your email domain doesn't match the product's website, so an editor will check it before it's published. This usually takes one business day.</p></div>
  <?php elseif (!$u): ?>
    <div class="panel">
      <h2 style="font-size:var(--step-2)">First, sign in with your company email</h2>
      <p class="muted">We use it to confirm you work for the vendor. Already listed? Find your product and choose “Claim this listing” instead.</p>
      <div style="display:flex;gap:12px;flex-wrap:wrap"><a class="btn" href="/signin?next=/list-your-software">Sign in to continue</a><a class="btn secondary" href="/software">Find an existing listing</a></div>
    </div>
  <?php elseif (!$u['is_work_email']): ?>
    <div class="notice warn"><p>You're signed in with a personal address. <a href="/signin?next=/list-your-software">Sign in with your company email</a> to list software.</p></div>
  <?php else: ?>
    <p class="small muted">Check the <a href="/software">directory</a> first — if your product is already there, claim it rather than adding it twice.</p>
    <?= listing_form($f, $cats, 'Submit listing', null, $error) ?>
  <?php endif; ?>
</div>
