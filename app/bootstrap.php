<?php
declare(strict_types=1);

define('ROOT', dirname(__DIR__));
define('APP', __DIR__);

$configFile = ROOT . '/config.php';
if (!is_file($configFile)) {
    if (PHP_SAPI !== 'cli') {
        header('Location: /install.php');
        exit;
    }
    fwrite(STDERR, "config.php missing — run install.php first\n");
    exit(1);
}
$GLOBALS['CONFIG'] = require $configFile;

$debug = !empty($GLOBALS['CONFIG']['debug']);
error_reporting(E_ALL);
ini_set('display_errors', $debug ? '1' : '0');
ini_set('log_errors', '1');
date_default_timezone_set('UTC');
mb_internal_encoding('UTF-8');

foreach (['helpers', 'db', 'settings', 'auth', 'mailer', 'queries', 'seo', 'reviews', 'compare', 'stripe', 'indexnow', 'markdown', 'vendor', 'setup'] as $lib) {
    require APP . "/lib/$lib.php";
}
require APP . '/views/partials.php';

// Apply database changes automatically after an update.
if (setting('schema_version') !== SCHEMA_VERSION) {
    run_schema();
    settings_all(true);
}

if (PHP_SAPI !== 'cli') {
    session_name('icrm_session');
    session_set_cookie_params([
        'lifetime' => 60 * 60 * 24 * 30,
        'path' => '/',
        'secure' => is_https(),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    ini_set('session.gc_maxlifetime', (string)(60 * 60 * 24 * 30));
    session_start();
}
