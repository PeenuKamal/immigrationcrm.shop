<?php
declare(strict_types=1);

require __DIR__ . '/app/bootstrap.php';

$path = rawurldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');
if ($path !== '/' && str_ends_with($path, '/')) redirect(rtrim($path, '/') . (($qs = $_SERVER['QUERY_STRING'] ?? '') ? "?$qs" : ''), 301);

$slug = '([a-z0-9-]+)';
$routes = [
    // pages (rendered inside the layout)
    ['#^/$#', 'pages/home.php'],
    ['#^/software$#', 'pages/directory.php'],
    ["#^/software/$slug\\.md$#", 'endpoints/md_software.php'],
    ["#^/software/$slug/og\\.png$#", 'endpoints/og.php', 'software'],
    ["#^/software/$slug$#", 'pages/software.php'],
    ["#^/software/$slug/alternatives$#", 'pages/alternatives.php'],
    ["#^/software/$slug/review$#", 'pages/review_form.php'],
    ["#^/software/$slug/claim$#", 'pages/claim.php'],
    ["#^/category/$slug\\.md$#", 'endpoints/md_category.php'],
    ["#^/category/$slug/og\\.png$#", 'endpoints/og.php', 'category'],
    ["#^/category/$slug$#", 'pages/category.php'],
    ['#^/best$#', 'pages/guides_index.php'],
    ["#^/best/$slug\\.md$#", 'endpoints/md_guide.php'],
    ["#^/best/$slug/$slug\\.md$#", 'endpoints/md_guide.php'],
    ["#^/best/$slug$#", 'pages/guide_country.php'],
    ["#^/best/$slug/$slug$#", 'pages/guide.php'],
    ['#^/compare$#', 'pages/compare_index.php'],
    ['#^/compare/([a-z0-9-]+-vs-[a-z0-9-]+)/og\.png$#', 'endpoints/og.php', 'compare'],
    ['#^/compare/([a-z0-9-]+-vs-[a-z0-9-]+)$#', 'pages/compare_pair.php'],
    ['#^/signin$#', 'pages/signin.php'],
    ['#^/signout$#', 'endpoints/signout.php'],
    ['#^/account$#', 'pages/account.php'],
    ['#^/auth/linkedin/start$#', 'endpoints/linkedin_start.php'],
    ['#^/auth/linkedin/callback$#', 'endpoints/linkedin_callback.php'],
    ['#^/list-your-software$#', 'pages/list_software.php'],
    ['#^/vendor$#', 'pages/vendor.php'],
    ['#^/vendor/featured$#', 'pages/vendor_featured.php'],
    ['#^/vendor/(\d+)$#', 'pages/vendor_manage.php'],
    ['#^/stripe/webhook$#', 'endpoints/stripe_webhook.php'],
    ['#^/admin$#', 'pages/admin.php'],
    ['#^/admin/proof/(\d+)$#', 'endpoints/proof.php'],
    ['#^/about$#', 'pages/about.php'],
    ['#^/how-we-verify$#', 'pages/how_we_verify.php'],
    ['#^/privacy$#', 'pages/privacy.php'],
    ['#^/terms$#', 'pages/terms.php'],
    // machine-readable
    ['#^/og\.png$#', 'endpoints/og.php', 'site'],
    ['#^/llms\.txt$#', 'endpoints/llms.php'],
    ['#^/llms-full\.txt$#', 'endpoints/llms_full.php'],
    ['#^/sitemap\.xml$#', 'endpoints/sitemap.php'],
    ['#^/robots\.txt$#', 'endpoints/robots.php'],
    ['#^/feed\.xml$#', 'endpoints/feed.php'],
    ['#^/manifest\.webmanifest$#', 'endpoints/manifest.php'],
    ['#^/indexnow\.txt$#', 'endpoints/indexnow.php'],
];

$file = null;
$params = [];
$kind = null;
foreach ($routes as $r) {
    if (preg_match($r[0], $path, $mm)) {
        $file = $r[1];
        $params = array_slice($mm, 1);
        $kind = $r[2] ?? null;
        break;
    }
}

function render_page(string $file, array $params): void
{
    $meta = null;
    ob_start();
    try {
        require APP . '/' . $file;
    } catch (Throwable $ex) {
        ob_end_clean();
        throw $ex;
    }
    $content = (string)ob_get_clean();
    $meta ??= meta(['title' => SITE_NAME, 'description' => SITE_DESCRIPTION, 'path' => '/']);
    require APP . '/views/layout.php';
}

function not_found(): never
{
    http_response_code(404);
    render_page('pages/not_found.php', []);
    exit;
}

housekeeping();

try {
    if (!$file) not_found();
    if (str_starts_with($file, 'endpoints/')) {
        require APP . '/' . $file;
    } else {
        render_page($file, $params);
    }
} catch (Throwable $ex) {
    error_log((string)$ex);
    if (!headers_sent()) http_response_code(500);
    $isAdmin = false;
    try { $cu = current_user(); $isAdmin = $cu && in_array(strtolower($cu['email']), admin_emails(), true); } catch (Throwable) {}
    if (!empty($GLOBALS['CONFIG']['debug'])) {
        echo '<pre>' . e((string)$ex) . '</pre>';
    } elseif ($isAdmin) {
        echo '<!doctype html><title>Something went wrong</title><div style="font-family:sans-serif;padding:40px;max-width:900px"><p>Something went wrong on our side. Only admins see the details below — send them to your developer.</p><pre style="white-space:pre-wrap;background:#f4f5f9;padding:16px;border-radius:8px">' . e($ex->getMessage() . ' in ' . basename($ex->getFile()) . ':' . $ex->getLine()) . '</pre><p><a href="/">Back to the home page</a></p></div>';
    } else {
        echo '<!doctype html><title>Something went wrong</title><p style="font-family:sans-serif;padding:40px">Something went wrong on our side. Refresh the page in a moment. <a href="/">Back to the home page</a></p>';
    }
}
