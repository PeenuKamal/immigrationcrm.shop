<?php
/**
 * One-time setup: fill in the form, and this page creates the database tables,
 * adds the 39 starter listings, saves your settings and writes config.php.
 * It switches itself off once the site is set up.
 */
declare(strict_types=1);

define('ROOT', __DIR__);
define('APP', __DIR__ . '/app');

if (is_file(ROOT . '/config.php')) {
    http_response_code(403);
    exit('<!doctype html><meta charset="utf-8"><title>Already set up</title><body style="font-family:system-ui;padding:40px;max-width:600px">'
        . '<h1>Your site is already set up</h1><p>This setup page is switched off. Go to <a href="/">your site</a> or <a href="/admin?tab=settings">Moderation → Settings</a> to change settings.</p></body>');
}

foreach (['helpers', 'db', 'settings', 'mailer', 'setup'] as $lib) require APP . "/lib/$lib.php";

$errors = [];
$done = null;
$host = $_SERVER['HTTP_HOST'] ?? 'immigrationcrm.shop';
$f = [
    'app_url' => 'https://' . preg_replace('/^www\./', '', $host),
    'db_host' => 'localhost',
    'db_name' => '',
    'db_user' => '',
    'db_pass' => '',
    'admin_email' => 'kamal@thedigisparrow.com',
    'smtp_user' => 'otp@' . preg_replace(['/^www\./', '/:\d+$/'], '', $host),
    'smtp_pass' => '',
];

$checks = [
    'PHP 8.1 or newer' => version_compare(PHP_VERSION, '8.1', '>='),
    'PDO MySQL' => extension_loaded('pdo_mysql'),
    'cURL' => extension_loaded('curl'),
    'OpenSSL' => extension_loaded('openssl'),
    'mbstring' => extension_loaded('mbstring'),
    'GD (share images)' => extension_loaded('gd'),
];
$blocking = array_keys(array_filter(array_slice($checks, 0, 5, true), fn($ok) => !$ok));

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && !$blocking) {
    foreach ($f as $k => $_) $f[$k] = trim((string)($_POST[$k] ?? ''));
    $f['app_url'] = rtrim($f['app_url'], '/');
    if (!filter_var($f['app_url'], FILTER_VALIDATE_URL)) $errors[] = 'Enter the site address, like https://immigrationcrm.shop.';
    if (!$f['db_name'] || !$f['db_user']) $errors[] = 'Enter the database name and database username from hPanel.';
    if (!filter_var($f['admin_email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Enter your admin email address.';
    if (!filter_var($f['smtp_user'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Enter the email address that sends login codes.';

    if (!$errors) {
        $GLOBALS['CONFIG'] = ['db' => ['host' => $f['db_host'] ?: 'localhost', 'port' => 3306, 'name' => $f['db_name'], 'user' => $f['db_user'], 'pass' => $f['db_pass']], 'secret' => bin2hex(random_bytes(32)), 'debug' => false];
        try {
            db();
        } catch (Throwable $ex) {
            $errors[] = "Couldn't connect to the database. Check the database name, username and password — they're on hPanel → Databases → MySQL Databases. (" . $ex->getMessage() . ')';
        }
    }

    if (!$errors) {
        try {
            run_schema();
            $added = seed_directory();
            $settings = [
                'app_url' => $f['app_url'],
                'admin_emails' => strtolower($f['admin_email']),
                'contact_email' => strtolower($f['admin_email']),
                'smtp_host' => 'smtp.hostinger.com',
                'smtp_port' => '465',
                'smtp_encryption' => 'ssl',
                'smtp_user' => strtolower($f['smtp_user']),
                'smtp_pass' => $f['smtp_pass'],
                'email_from_name' => 'ImmigrationCRM.shop',
                'indexnow_key' => bin2hex(random_bytes(16)),
            ];
            foreach ($settings as $k => $v) save_setting($k, $v);
            settings_all(true);

            $config = "<?php\n// Created by install.php on " . gmdate('Y-m-d H:i') . " UTC. Keep this file private.\nreturn " . var_export($GLOBALS['CONFIG'], true) . ";\n";
            $written = @file_put_contents(ROOT . '/config.php', $config, LOCK_EX) !== false;
            if ($written) @chmod(ROOT . '/config.php', 0640);

            $mailOk = $f['smtp_pass'] !== '' && send_mail($f['admin_email'], 'ImmigrationCRM.shop is set up', "Your site is live at {$f['app_url']}.\n\nSign in at {$f['app_url']}/signin with this email address to open Moderation.");
            $done = ['added' => $added, 'written' => $written, 'config' => $config, 'mail' => $mailOk];
        } catch (Throwable $ex) {
            $errors[] = 'Setup stopped: ' . $ex->getMessage();
        }
    }
}
$h = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex">
<title>Set up ImmigrationCRM.shop</title>
<link rel="stylesheet" href="/assets/style.css">
</head>
<body>
<main><div class="wrap" style="max-width:720px">
  <div class="page-head">
    <h1>Set up your site</h1>
    <p>Fill in this one form. It takes about two minutes.</p>
  </div>

<?php if ($done): ?>
  <div class="notice"><p><strong>Done — your site is set up.</strong> <?= (int)$done['added'] ?> starter listings were added.</p></div>
  <?php if (!$done['written']): ?>
    <div class="notice warn">
      <p><strong>One more step:</strong> the server didn't let this page save the settings file. In hPanel → File Manager → <code>public_html</code>, create a new file named <code>config.php</code> and paste this into it:</p>
    </div>
    <textarea readonly style="min-height:240px;font-family:monospace;font-size:13px"><?= $h($done['config']) ?></textarea>
  <?php endif; ?>
  <div class="panel" style="margin-top:24px">
    <h2 style="font-size:var(--step-2)">Next</h2>
    <ol>
      <li><?= $done['mail'] ? 'Check your inbox — we sent a test email to ' . $h($f['admin_email']) . '.' : "The test email couldn't be sent. You can fix the email password later in Moderation → Settings." ?></li>
      <li>Go to <a href="/signin"><?= $h($f['app_url']) ?>/signin</a>, enter <strong><?= $h($f['admin_email']) ?></strong> and type the code from your inbox.</li>
      <li><strong>Moderation</strong> appears in the menu. Its <em>Settings</em> tab is where you add LinkedIn, Stripe and Google/Bing codes later.</li>
    </ol>
    <a class="btn stamp" href="/">Open my site</a>
  </div>
<?php else: ?>
  <?php if ($blocking): ?>
    <div class="notice error"><p>This hosting is missing: <?= $h(implode(', ', $blocking)) ?>. In hPanel → Advanced → PHP Configuration, choose PHP 8.2 or newer and turn these on, then reload this page.</p></div>
  <?php endif; ?>
  <?php foreach ($errors as $err): ?><div class="notice error" role="alert"><p><?= $h($err) ?></p></div><?php endforeach; ?>

  <form method="post" class="stack">
    <section class="panel">
      <h2 style="font-size:var(--step-2)">1. Database</h2>
      <p class="small muted">Create it first in hPanel → <strong>Databases → MySQL Databases</strong>. Hostinger adds a prefix like <code>u123456789_</code> — copy the full names shown there.</p>
      <div class="field"><label for="db_name">Database name</label><input id="db_name" name="db_name" value="<?= $h($f['db_name']) ?>" placeholder="u123456789_immigrationcrm" required></div>
      <div class="field"><label for="db_user">Database username</label><input id="db_user" name="db_user" value="<?= $h($f['db_user']) ?>" placeholder="u123456789_admin" required></div>
      <div class="field"><label for="db_pass">Database password</label><input id="db_pass" name="db_pass" type="password" value="<?= $h($f['db_pass']) ?>" autocomplete="off"></div>
      <details><summary class="small">Advanced</summary>
        <div class="field" style="margin-top:12px"><label for="db_host">Database host</label><input id="db_host" name="db_host" value="<?= $h($f['db_host']) ?>"><p class="hint">Leave as <code>localhost</code> on Hostinger.</p></div>
      </details>
    </section>

    <section class="panel">
      <h2 style="font-size:var(--step-2)">2. You</h2>
      <div class="field"><label for="admin_email">Your admin email</label><input id="admin_email" name="admin_email" type="email" value="<?= $h($f['admin_email']) ?>" required><p class="hint">Signing in with this address opens the Moderation dashboard.</p></div>
      <div class="field"><label for="app_url">Site address</label><input id="app_url" name="app_url" value="<?= $h($f['app_url']) ?>" required></div>
    </section>

    <section class="panel">
      <h2 style="font-size:var(--step-2)">3. Email for login codes</h2>
      <p class="small muted">The Hostinger mailbox that sends sign-in codes. Server settings (smtp.hostinger.com, port 465, SSL) are filled in for you.</p>
      <div class="field"><label for="smtp_user">Email address</label><input id="smtp_user" name="smtp_user" type="email" value="<?= $h($f['smtp_user']) ?>" required></div>
      <div class="field"><label for="smtp_pass">Email password</label><input id="smtp_pass" name="smtp_pass" type="password" value="<?= $h($f['smtp_pass']) ?>" autocomplete="off"></div>
    </section>

    <div><button class="btn stamp" <?= $blocking ? 'disabled' : '' ?>>Set up my site</button></div>
  </form>

  <details class="small muted" style="margin-top:24px"><summary>Server check</summary>
    <ul><?php foreach ($checks as $name => $ok): ?><li><?= $ok ? '✓' : '✗' ?> <?= $h($name) ?></li><?php endforeach; ?></ul>
  </details>
<?php endif; ?>
</div></main>
</body>
</html>
