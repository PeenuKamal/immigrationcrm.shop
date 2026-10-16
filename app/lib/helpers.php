<?php
declare(strict_types=1);

function e(?string $s): string
{
    return htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function is_https(): bool
{
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https'
        || ($_SERVER['SERVER_PORT'] ?? '') === '443';
}

function app_url(): string
{
    $u = setting('app_url') ?: ($GLOBALS['CONFIG']['app_url'] ?? '');
    if (!$u) $u = (is_https() ? 'https://' : 'http://') . ($_SERVER['HTTP_HOST'] ?? 'localhost');
    return rtrim($u, '/');
}

function url(string $path = '/'): string
{
    return app_url() . ($path === '' || $path[0] !== '/' ? '/' . $path : $path);
}

function redirect(string $to, int $code = 303): never
{
    header('Location: ' . $to, true, $code);
    exit;
}

function slugify(string $s): string
{
    $s = strtolower(trim($s));
    $s = preg_replace('/[^a-z0-9]+/', '-', iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $s) ?: $s);
    return substr(trim((string)$s, '-'), 0, 60);
}

function email_domain(string $email): string
{
    return strtolower(trim(substr(strrchr($email, '@') ?: '', 1)));
}

function domain_from_url(?string $u): ?string
{
    if (!$u) return null;
    if (!preg_match('#^https?://#i', $u)) $u = 'https://' . $u;
    $h = parse_url($u, PHP_URL_HOST);
    if (!$h || !str_contains($h, '.')) return null;
    return preg_replace('/^www\./', '', strtolower($h));
}

function same_org(?string $a, ?string $b): bool
{
    if (!$a || !$b) return false;
    $a = strtolower($a);
    $b = strtolower($b);
    return $a === $b || str_ends_with($a, ".$b") || str_ends_with($b, ".$a");
}

function json_list(?string $v): array
{
    $d = json_decode((string)$v, true);
    return is_array($d) ? array_values(array_filter($d, 'is_string')) : [];
}

function list_from_text(string $v, string $sepRegex): array
{
    $out = array_values(array_filter(array_map('trim', preg_split($sepRegex, $v) ?: []), fn($x) => $x !== ''));
    return array_slice($out, 0, 20);
}

function lines(string $text): array
{
    $out = [];
    foreach (preg_split('/\r?\n/', $text) ?: [] as $l) {
        $l = trim(preg_replace('/^[-•*]\s*/u', '', $l));
        if ($l !== '') $out[] = $l;
    }
    return $out;
}

function is_featured(?string $until): bool
{
    return $until && strtotime($until) > time();
}

function format_date(?string $d): string
{
    return $d ? date('j M Y', strtotime($d)) : '';
}

function iso_date(?string $d): string
{
    return $d ? gmdate('c', strtotime($d)) : gmdate('c');
}

function now(): string
{
    return gmdate('Y-m-d H:i:s');
}

function year(): int
{
    return (int)date('Y');
}

function clip(string $text, int $max = 160): string
{
    if (mb_strlen($text) <= $max) return $text;
    $cut = mb_substr($text, 0, $max - 1);
    $sp = mb_strrpos($cut, ' ');
    if ($sp !== false && $sp > $max - 25) $cut = mb_substr($cut, 0, $sp);
    return rtrim($cut, ' ,;:.') . '…';
}

function join_names(array $xs, string $last = 'and'): string
{
    $xs = array_values($xs);
    $n = count($xs);
    if ($n <= 1) return implode('', $xs);
    return implode(', ', array_slice($xs, 0, -1)) . " $last " . $xs[$n - 1];
}

function lcfirst_tagline(string $t): string
{
    return preg_replace_callback('/^([A-Z][a-z]+)\b/', fn($m) => strtolower($m[1]), rtrim($t, '.'));
}

function post(string $k, string $default = ''): string
{
    $v = $_POST[$k] ?? $default;
    return is_string($v) ? trim($v) : $default;
}

function get(string $k, string $default = ''): string
{
    $v = $_GET[$k] ?? $default;
    return is_string($v) ? trim($v) : $default;
}

function is_post(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

function check_csrf(): void
{
    if (!hash_equals($_SESSION['csrf'] ?? '', (string)($_POST['_csrf'] ?? ''))) {
        http_response_code(419);
        exit('Your session expired. Go back, refresh the page and try again.');
    }
}

function flash(string $msg, string $kind = ''): void
{
    $_SESSION['flash'] = [$kind, $msg];
}

function take_flash(): ?array
{
    $f = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $f;
}

function http_json(string $method, string $url, array $opts = []): array
{
    $ch = curl_init($url);
    $headers = $opts['headers'] ?? [];
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_TIMEOUT => $opts['timeout'] ?? 20,
        CURLOPT_CONNECTTIMEOUT => 10,
    ]);
    if (isset($opts['form'])) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($opts['form']));
        $headers[] = 'Content-Type: application/x-www-form-urlencoded';
    } elseif (isset($opts['json'])) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($opts['json']));
        $headers[] = 'Content-Type: application/json; charset=utf-8';
    }
    if (isset($opts['user'])) curl_setopt($ch, CURLOPT_USERPWD, $opts['user']);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $body = curl_exec($ch);
    $status = (int)curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    curl_close($ch);
    return [$status, is_string($body) ? (json_decode($body, true) ?? []) : []];
}

/** "Immigration CRM for consultants" -> "immigration CRM for consultants" (keeps acronyms like CRM, AI, RCIC). */
function lower_words(string $s): string
{
    return preg_replace_callback('/\b([A-Z][a-z]+)\b/', fn($m) => strtolower($m[1]), $s);
}
