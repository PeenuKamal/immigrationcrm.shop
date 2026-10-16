<?php
declare(strict_types=1);

const FEATURED_PLANS = [1 => '1 month', 3 => '3 months', 12 => '12 months'];

function featured_price(int $months): int
{
    $monthly = (int)setting('featured_price_cents', '9900');
    $discount = $months >= 12 ? 0.8 : ($months >= 3 ? 0.9 : 1);
    return (int)round($monthly * $months * $discount);
}

function format_money(int $cents): string
{
    $cur = strtoupper(setting('featured_currency', 'usd'));
    $sym = ['USD' => '$', 'INR' => '₹', 'CAD' => 'CA$', 'AUD' => 'A$', 'GBP' => '£', 'EUR' => '€'][$cur] ?? "$cur ";
    return $sym . number_format($cents / 100, 2);
}

function stripe_enabled(): bool
{
    return setting('stripe_secret_key') !== '';
}

function stripe_api(string $method, string $path, array $form = []): array
{
    [$status, $body] = http_json($method, "https://api.stripe.com/v1/$path", [
        'user' => setting('stripe_secret_key') . ':',
        'form' => $method === 'POST' ? $form : null,
    ] + ($method === 'POST' ? [] : []));
    if ($status >= 400) throw new RuntimeException($body['error']['message'] ?? "Stripe error $status");
    return $body;
}

/** Marks a Stripe payment as paid and extends featured placement. Safe to call twice. */
function apply_featured_payment(string $sessionId): ?array
{
    $p = one("SELECT * FROM payments WHERE stripe_session_id = ? AND status = 'pending'", [$sessionId]);
    if (!$p) return null;
    q("UPDATE payments SET status = 'paid' WHERE id = ? AND status = 'pending'", [$p['id']]);
    $s = one('SELECT * FROM software WHERE id = ?', [$p['software_id']]);
    if (!$s) return null;
    $start = is_featured($s['featured_until']) ? strtotime($s['featured_until']) : time();
    $until = gmdate('Y-m-d H:i:s', strtotime("+{$p['months']} months", $start));
    q('UPDATE software SET featured_until = ? WHERE id = ?', [$until, $s['id']]);
    return ['software' => $s, 'until' => $until];
}

function stripe_verify_signature(string $payload, string $header, string $secret): bool
{
    $parts = [];
    foreach (explode(',', $header) as $kv) {
        [$k, $v] = array_pad(explode('=', $kv, 2), 2, '');
        $parts[$k][] = $v;
    }
    $t = $parts['t'][0] ?? '';
    if (!$t || abs(time() - (int)$t) > 600) return false;
    $expected = hash_hmac('sha256', "$t.$payload", $secret);
    foreach ($parts['v1'] ?? [] as $sig) if (hash_equals($expected, $sig)) return true;
    return false;
}
