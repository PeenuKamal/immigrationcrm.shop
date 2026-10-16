<?php
require_user('/vendor');
$meta = meta(['title' => 'Payment', 'description' => 'Featured placement payment.', 'path' => '/vendor/featured', 'noindex' => true]);
$paid = false;
$result = null;
if (stripe_enabled() && ($sid = get('session_id'))) {
    try {
        $session = stripe_api('GET', 'checkout/sessions/' . rawurlencode($sid));
        $paid = ($session['payment_status'] ?? '') === 'paid';
        if ($paid) $result = apply_featured_payment($sid); // the webhook may already have done this
    } catch (Throwable $ex) {
        error_log('Stripe: ' . $ex->getMessage());
    }
}
?>
<div class="wrap" style="max-width:640px">
  <div class="page-head"><h1><?= $paid ? 'Payment received' : 'Payment pending' ?></h1></div>
  <div class="notice"><p><?= $paid
      ? ($result ? e($result['software']['name']) . ' is featured until ' . e(format_date($result['until'])) . '.' : 'Your featured placement is active. A receipt is on its way from Stripe.')
      : "We haven't received confirmation from Stripe yet. Refresh in a minute; if it doesn't update, email " . e(contact_email()) . '.' ?></p></div>
  <a class="btn" href="/vendor">Back to vendor dashboard</a>
</div>
