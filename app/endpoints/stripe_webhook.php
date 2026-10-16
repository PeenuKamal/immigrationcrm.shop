<?php
$secret = setting('stripe_webhook_secret');
$payload = file_get_contents('php://input') ?: '';
header('Content-Type: application/json');
if (!$secret || !stripe_verify_signature($payload, $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '', $secret)) {
    http_response_code(400);
    exit('{"error":"bad signature"}');
}
$event = json_decode($payload, true);
if (($event['type'] ?? '') === 'checkout.session.completed' && ($event['data']['object']['payment_status'] ?? '') === 'paid') {
    apply_featured_payment($event['data']['object']['id']);
}
echo '{"received":true}';
