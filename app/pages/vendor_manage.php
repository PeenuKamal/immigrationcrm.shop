<?php
$id = (int)$params[0];
$u = require_user("/vendor/$id");
$s = one('SELECT * FROM software WHERE id = ?', [$id]);
if (!$s) not_found();
if ((int)$s['owner_id'] !== (int)$u['id']) redirect('/vendor');
$meta = meta(['title' => "Manage {$s['name']}", 'description' => 'Manage your listing.', 'path' => "/vendor/$id", 'noindex' => true]);
$error = null;
$f = listing_to_form($s);
$selected = array_column(categories_of($id), 'slug');

if (is_post()) {
    check_csrf();
    $action = post('action');
    if ($action === 'listing') {
        [$f, $selected] = read_listing_form();
        $error = validate_listing($f, $selected);
        if (!$error) {
            $vals = listing_values($f);
            // The verified domain only changes through an admin, so owners can't dodge the self-review check.
            q('UPDATE software SET name = ?, website = ?, vendor_name = ?, tagline = ?, description = ?, deployment = ?, regions = ?, audiences = ?,
                features = ?, pricing_note = ?, logo_url = ?, domain = COALESCE(domain, ?), updated_at = UTC_TIMESTAMP() WHERE id = ?',
                array_merge($vals, [domain_from_url($f['website']), $id]));
            save_listing_categories($id, $selected);
            if ($s['status'] === 'PUBLISHED') ping_software($id);
            flash('Changes saved.');
            redirect("/vendor/$id");
        }
    } elseif ($action === 'respond') {
        $r = one('SELECT id FROM reviews WHERE id = ? AND software_id = ?', [(int)post('review_id'), $id]);
        if ($r) q('UPDATE reviews SET vendor_response = NULLIF(?, "") WHERE id = ?', [mb_substr(post('response'), 0, 1500), $r['id']]);
        flash('Response saved.');
        redirect("/vendor/$id#reviews");
    } elseif ($action === 'feature') {
        $months = (int)post('months');
        if (!isset(FEATURED_PLANS[$months])) redirect("/vendor/$id");
        if (!stripe_enabled()) { flash("Online payments aren't set up yet. Email " . contact_email() . ' to feature this listing.', 'warn'); redirect("/vendor/$id"); }
        $amount = featured_price($months);
        $cur = strtolower(setting('featured_currency', 'usd'));
        try {
            $session = stripe_api('POST', 'checkout/sessions', [
                'mode' => 'payment',
                'customer_email' => $u['email'],
                'line_items' => [['quantity' => 1, 'price_data' => ['currency' => $cur, 'unit_amount' => $amount,
                    'product_data' => ['name' => "Featured listing: {$s['name']}", 'description' => "$months month" . ($months > 1 ? 's' : '') . ' of featured placement on ' . SITE_NAME]]]],
                'metadata' => ['software_id' => $id, 'months' => $months],
                'success_url' => url('/vendor/featured') . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => url("/vendor/$id") . '?payment=cancelled',
            ]);
        } catch (Throwable $ex) {
            error_log('Stripe: ' . $ex->getMessage());
            flash("We couldn't start the payment. Try again in a minute.", 'error');
            redirect("/vendor/$id");
        }
        q('INSERT INTO payments (software_id, stripe_session_id, amount_cents, currency, months) VALUES (?, ?, ?, ?, ?)', [$id, $session['id'], $amount, $cur, $months]);
        redirect($session['url']);
    }
}
$revs = all("SELECT * FROM reviews WHERE software_id = ? AND status = 'PUBLISHED' ORDER BY published_at DESC", [$id]);
$msg = take_flash() ?? (get('payment') === 'cancelled' ? ['warn', 'Payment cancelled — nothing was charged.'] : null);
?>
<div class="wrap" style="max-width:860px">
  <p class="small"><a href="/vendor">Vendor dashboard</a></p>
  <div class="page-head"><h1><?= e($s['name']) ?></h1><p><?= $s['status'] === 'PUBLISHED' ? '<a href="/software/' . e($s['slug']) . '">View public listing</a>' : "This listing is waiting for an editor's check." ?></p></div>
  <?= notice($msg) ?>
  <div class="stack">
    <section class="panel" aria-labelledby="feat">
      <h2 id="feat" style="font-size:var(--step-2)">Featured placement</h2>
      <?php if (is_featured($s['featured_until'])): ?><p><strong>Featured until <?= e(format_date($s['featured_until'])) ?>.</strong> Buying more time extends it.</p><?php endif; ?>
      <p class="muted">Featured listings appear first in their categories, with a gold edge and a “Featured” label. It never affects ratings or reviews.</p>
      <div style="display:flex;gap:12px;flex-wrap:wrap">
        <?php foreach (FEATURED_PLANS as $m => $label): ?>
        <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="feature"><input type="hidden" name="months" value="<?= $m ?>">
          <button class="btn <?= $m === 3 ? '' : 'secondary' ?>">Feature for <?= e($label) ?> · <?= e(format_money(featured_price($m))) ?></button></form>
        <?php endforeach; ?>
      </div>
    </section>
    <section aria-labelledby="edit"><h2 id="edit" style="font-size:var(--step-2)">Listing details</h2><?= listing_form($f, $selected, 'Save changes', $id, $error) ?></section>
    <section id="reviews" aria-labelledby="rv">
      <h2 id="rv" style="font-size:var(--step-2)">Reply to reviews</h2>
      <?php if (!$revs): ?><p class="muted">No verified reviews yet. Share your listing link with customers and ask them to review you — they'll go through the same verification as everyone else.</p>
      <?php else: ?><div class="stack"><?php foreach ($revs as $r): ?>
        <div class="panel">
          <h3><?= e($r['title']) ?> <span class="muted small">· <?= (int)$r['rating'] ?>/5</span></h3>
          <p class="small muted"><?= e($r['reviewer_role']) ?> · <?= e($r['firm_size']) ?> · <?= e(format_date($r['published_at'])) ?></p>
          <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="respond"><input type="hidden" name="review_id" value="<?= (int)$r['id'] ?>">
            <div class="field"><label for="resp-<?= (int)$r['id'] ?>">Your public response</label><textarea id="resp-<?= (int)$r['id'] ?>" name="response" style="min-height:90px"><?= e($r['vendor_response']) ?></textarea></div>
            <button class="btn small secondary">Save response</button></form>
        </div>
      <?php endforeach; ?></div><?php endif; ?>
    </section>
  </div>
</div>
