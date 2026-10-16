<?php
// "Sign In with LinkedIn using OpenID Connect" — gives a verified name, member id and email.
$u = current_user();
if (!$u) redirect('/signin?next=/account');
$clientId = setting('linkedin_client_id');
if (!$clientId) redirect('/account?linkedin=not-configured');
$_SESSION['li_state'] = bin2hex(random_bytes(16));
$_SESSION['li_next'] = safe_next(get('next', '/account'));
redirect('https://www.linkedin.com/oauth/v2/authorization?' . http_build_query([
    'response_type' => 'code',
    'client_id' => $clientId,
    'redirect_uri' => url('/auth/linkedin/callback'),
    'state' => $_SESSION['li_state'],
    'scope' => 'openid profile email',
]), 302);
