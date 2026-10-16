<?php
declare(strict_types=1);

const FREE_EMAIL_DOMAINS = [
    'gmail.com', 'googlemail.com', 'yahoo.com', 'yahoo.co.in', 'yahoo.co.uk', 'yahoo.ca', 'ymail.com', 'rocketmail.com',
    'hotmail.com', 'hotmail.co.uk', 'hotmail.ca', 'outlook.com', 'outlook.in', 'live.com', 'live.ca', 'msn.com', 'aol.com',
    'icloud.com', 'me.com', 'mac.com', 'proton.me', 'protonmail.com', 'pm.me', 'gmx.com', 'gmx.net', 'gmx.de', 'mail.com',
    'zoho.com', 'zohomail.com', 'zohomail.in', 'yandex.com', 'yandex.ru', 'mail.ru', 'rediffmail.com', 'tutanota.com',
    'tuta.io', 'fastmail.com', 'hey.com', 'qq.com', '163.com', '126.com', 'sina.com', 'inbox.com', 'mailinator.com',
    'guerrillamail.com', '10minutemail.com', 'tempmail.com', 'temp-mail.org', 'sharklasers.com', 'yopmail.com',
    'trashmail.com', 'dispostable.com', 'getnada.com', 'maildrop.cc',
];

function is_free_email_domain(string $domain): bool
{
    return in_array(strtolower($domain), FREE_EMAIL_DOMAINS, true);
}

function admin_emails(): array
{
    return array_values(array_filter(array_map(fn($x) => strtolower(trim($x)), explode(',', setting('admin_emails')))));
}

function current_user(bool $refresh = false): ?array
{
    static $user = false;
    if ($user === false || $refresh) {
        $id = (int)($_SESSION['uid'] ?? 0);
        $user = $id ? one('SELECT * FROM users WHERE id = ?', [$id]) : null;
    }
    return $user;
}

function login_user(int $id): void
{
    session_regenerate_id(true);
    $_SESSION['uid'] = $id;
    current_user(true);
}

function logout_user(): void
{
    $_SESSION = [];
    session_regenerate_id(true);
}

function is_admin(?array $u): bool
{
    if (!$u) return false;
    return $u['role'] === 'ADMIN' || in_array(strtolower($u['email']), admin_emails(), true);
}

function require_user(string $next = '/account'): array
{
    $u = current_user();
    if (!$u) redirect('/signin?next=' . rawurlencode($next));
    return $u;
}

function require_admin(): array
{
    $u = require_user('/admin');
    if (!is_admin($u)) redirect('/');
    return $u;
}

function safe_next(string $n): string
{
    return ($n !== '' && $n[0] === '/' && !str_starts_with($n, '//')) ? $n : '/account';
}
