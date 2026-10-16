<?php
declare(strict_types=1);

/** Site settings stored in the database, editable from Moderation → Settings. */
const SETTING_DEFAULTS = [
    'app_url' => '',
    'admin_emails' => '',
    'contact_email' => '',
    'smtp_host' => 'smtp.hostinger.com',
    'smtp_port' => '465',
    'smtp_encryption' => 'ssl',
    'smtp_user' => '',
    'smtp_pass' => '',
    'email_from_name' => 'ImmigrationCRM.shop',
    'linkedin_client_id' => '',
    'linkedin_client_secret' => '',
    'stripe_secret_key' => '',
    'stripe_webhook_secret' => '',
    'featured_price_cents' => '9900',
    'featured_currency' => 'usd',
    'require_admin_approval' => '0',
    'google_verification' => '',
    'bing_verification' => '',
    'yandex_verification' => '',
    'indexnow_key' => '',
    // Legal details used in the Terms and Privacy Policy (Moderation → Settings → Legal)
    'legal_entity_name' => '',
    'legal_entity_type' => '',
    'legal_address' => '',
    'legal_country' => '',
    'governing_law' => '',
    'legal_venue' => '',
    'grievance_officer_name' => '',
    'grievance_officer_email' => '',
    'privacy_email' => '',
    'hosting_location' => '',
    'legal_effective_date' => '2026-10-16',
];

function settings_all(bool $refresh = false): array
{
    static $cache = null;
    if ($cache === null || $refresh) {
        $cache = SETTING_DEFAULTS;
        try {
            foreach (all('SELECT name, value FROM settings') as $r) $cache[$r['name']] = (string)$r['value'];
        } catch (Throwable) {
        }
    }
    return $cache;
}

function setting(string $name, string $default = ''): string
{
    $v = settings_all()[$name] ?? '';
    return $v !== '' ? $v : $default;
}

function save_setting(string $name, string $value): void
{
    q('INSERT INTO settings (name, value) VALUES (?, ?) ON DUPLICATE KEY UPDATE value = VALUES(value)', [$name, $value]);
}

function contact_email(): string
{
    $admins = array_filter(array_map('trim', explode(',', setting('admin_emails'))));
    return setting('contact_email', $admins[0] ?? 'hello@' . (parse_url(app_url(), PHP_URL_HOST) ?: 'example.com'));
}

const SITE_NAME = 'ImmigrationCRM.shop';
const SITE_SHORT = 'ImmigrationCRM';
const SITE_TAGLINE = 'Verified reviews of immigration software';
const SITE_DESCRIPTION = 'Independent directory and verified-review platform for immigration CRMs, immigration case management, law practice management and AI tools for immigration lawyers, RCICs and migration agents.';

const CATEGORY_SEO = [
    'immigration-case-management' => 'Immigration Case Management Software',
    'immigration-crm' => 'Immigration CRM Software',
    'law-practice-management' => 'Law Practice Management Software for Immigration Firms',
    'ai-immigration' => 'AI Tools for Immigration Lawyers',
    'corporate-immigration' => 'Corporate Immigration Software',
    'general-crm' => 'CRMs for Immigration Consultants',
];

function category_seo(string $slug, string $name): string
{
    return CATEGORY_SEO[$slug] ?? "$name Software";
}

function category_seo_lower(string $slug, string $name): string
{
    return preg_replace_callback('/\b([A-Z][a-z]+)\b/', fn($m) => strtolower($m[1]), category_seo($slug, $name));
}

/** A legal detail from Settings, or a highlighted placeholder for the lawyer to complete. */
function legal(string $key, string $label): string
{
    $v = setting($key);
    return $v !== '' ? e($v) : '<mark class="todo" title="Complete in Moderation → Settings → Legal">[' . e($label) . ']</mark>';
}

function privacy_email(): string
{
    return setting('privacy_email', contact_email());
}
