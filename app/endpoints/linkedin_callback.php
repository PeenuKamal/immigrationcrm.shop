<?php
$u = current_user();
if (!$u) redirect('/signin?next=/account');
$fail = fn(string $why) => redirect("/account?linkedin=$why");
if (get('error')) $fail('cancelled');
$state = $_SESSION['li_state'] ?? '';
unset($_SESSION['li_state']);
if (!get('code') || !$state || !hash_equals($state, get('state'))) $fail('expired');

[$st, $tok] = http_json('POST', 'https://www.linkedin.com/oauth/v2/accessToken', ['form' => [
    'grant_type' => 'authorization_code',
    'code' => get('code'),
    'redirect_uri' => url('/auth/linkedin/callback'),
    'client_id' => setting('linkedin_client_id'),
    'client_secret' => setting('linkedin_client_secret'),
]]);
if ($st !== 200 || empty($tok['access_token'])) $fail('failed');
[$st, $info] = http_json('GET', 'https://api.linkedin.com/v2/userinfo', ['headers' => ['Authorization: Bearer ' . $tok['access_token']]]);
if ($st !== 200 || empty($info['sub'])) $fail('failed');

// One LinkedIn profile can only vouch for one reviewer account.
$taken = one('SELECT id FROM users WHERE linkedin_sub = ? AND id <> ?', [$info['sub'], $u['id']]);
if ($taken) $fail('in-use');
q('UPDATE users SET linkedin_sub = ?, linkedin_name = ?, linkedin_email = ?, linkedin_verified_at = UTC_TIMESTAMP(), name = COALESCE(name, ?) WHERE id = ?',
    [$info['sub'], $info['name'] ?? null, $info['email'] ?? null, $info['name'] ?? null, $u['id']]);
refresh_user_reviews(current_user(true));
$next = $_SESSION['li_next'] ?? '/account';
unset($_SESSION['li_next']);
redirect($next . (str_contains($next, '?') ? '&' : '?') . 'linkedin=connected');
