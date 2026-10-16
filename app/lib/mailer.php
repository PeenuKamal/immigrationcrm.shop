<?php
declare(strict_types=1);

/**
 * Minimal SMTP client (SSL on 465, STARTTLS on 587) so no extra libraries are needed.
 * Falls back to PHP mail() when no SMTP password is set.
 */
function send_mail(string $to, string $subject, string $text): bool
{
    $host = setting('smtp_host');
    $user = setting('smtp_user');
    $pass = setting('smtp_pass');
    $fromName = setting('email_from_name', SITE_NAME);
    $from = $user ?: contact_email();

    $headers = [
        'Date: ' . date('r'),
        'From: ' . mime_header($fromName) . " <$from>",
        "To: <$to>",
        'Subject: ' . mime_header($subject),
        'Message-ID: <' . bin2hex(random_bytes(12)) . '@' . (email_domain($from) ?: 'localhost') . '>',
        'MIME-Version: 1.0',
        'Content-Type: text/plain; charset=UTF-8',
        'Content-Transfer-Encoding: 8bit',
    ];
    $body = str_replace(["\r\n", "\r"], "\n", $text);
    $body = str_replace("\n", "\r\n", wordwrap($body, 900, "\n", true));

    if (!$host || !$user || !$pass) {
        if (!empty($GLOBALS['CONFIG']['debug'])) {
            error_log("[email] To: $to\n[email] Subject: $subject\n$text");
            return true;
        }
        return @mail($to, mime_header($subject), $body, implode("\r\n", array_slice($headers, 1, 1)) . "\r\nContent-Type: text/plain; charset=UTF-8");
    }

    try {
        smtp_send($host, (int)setting('smtp_port', '465'), setting('smtp_encryption', 'ssl'), $user, $pass, $from, $to, implode("\r\n", $headers) . "\r\n\r\n" . $body);
        return true;
    } catch (Throwable $ex) {
        error_log('[email] SMTP error: ' . $ex->getMessage());
        return false;
    }
}

function mime_header(string $s): string
{
    return preg_match('/[^\x20-\x7e]/', $s) ? '=?UTF-8?B?' . base64_encode($s) . '?=' : $s;
}

function smtp_send(string $host, int $port, string $enc, string $user, string $pass, string $from, string $to, string $data): void
{
    $remote = ($enc === 'ssl' ? 'ssl://' : 'tcp://') . "$host:$port";
    $ctx = stream_context_create(['ssl' => ['verify_peer' => true, 'verify_peer_name' => true, 'SNI_enabled' => true]]);
    $fp = @stream_socket_client($remote, $errno, $errstr, 20, STREAM_CLIENT_CONNECT, $ctx);
    if (!$fp) throw new RuntimeException("Can't connect to $host:$port ($errstr)");
    stream_set_timeout($fp, 20);

    $read = function () use ($fp): string {
        $resp = '';
        while (($line = fgets($fp, 515)) !== false) {
            $resp .= $line;
            if (strlen($line) < 4 || $line[3] === ' ') break;
        }
        return $resp;
    };
    $cmd = function (?string $c, array $ok) use ($fp, $read): string {
        if ($c !== null) fwrite($fp, $c . "\r\n");
        $r = $read();
        if (!in_array((int)substr($r, 0, 3), $ok, true)) {
            $safe = $c && str_starts_with($c, 'AUTH') ? 'AUTH' : (string)$c;
            throw new RuntimeException("SMTP said '" . trim($r) . "' to '" . substr($safe, 0, 40) . "'");
        }
        return $r;
    };

    $cmd(null, [220]);
    $ehloHost = parse_url(app_url(), PHP_URL_HOST) ?: 'localhost';
    $cmd("EHLO $ehloHost", [250]);
    if ($enc === 'tls') {
        $cmd('STARTTLS', [220]);
        if (!stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT | STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT)) {
            throw new RuntimeException('STARTTLS failed');
        }
        $cmd("EHLO $ehloHost", [250]);
    }
    $cmd('AUTH LOGIN', [334]);
    $cmd(base64_encode($user), [334]);
    $cmd(base64_encode($pass), [235]);
    $cmd("MAIL FROM:<$from>", [250]);
    $cmd("RCPT TO:<$to>", [250, 251]);
    $cmd('DATA', [354]);
    // Dot-stuffing: lines starting with "." get an extra "."
    $data = preg_replace('/^\./m', '..', $data);
    $cmd($data . "\r\n.", [250]);
    $cmd('QUIT', [221]);
    fclose($fp);
}
