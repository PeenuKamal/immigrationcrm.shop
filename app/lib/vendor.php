<?php
declare(strict_types=1);

const LISTING_FIELDS = ['name', 'website', 'vendor_name', 'tagline', 'description', 'deployment', 'regions', 'audiences', 'features', 'pricing_note', 'logo_url'];

function read_listing_form(): array
{
    $f = [];
    foreach (LISTING_FIELDS as $k) $f[$k] = post($k);
    $cats = array_values(array_filter((array)($_POST['categories'] ?? []), 'is_string'));
    if ($f['website'] && !preg_match('#^https?://#i', $f['website'])) $f['website'] = 'https://' . $f['website'];
    return [$f, $cats];
}

function validate_listing(array $f, array $cats): ?string
{
    if (mb_strlen($f['name']) < 2) return 'Add the product name.';
    if (!domain_from_url($f['website'])) return "Add the product's website, like https://yourproduct.com.";
    if (mb_strlen($f['tagline']) < 10 || mb_strlen($f['tagline']) > 120) return 'Write a one-line summary between 10 and 120 characters.';
    if (mb_strlen($f['description']) < 80) return "Describe the product in at least 80 characters: who it's for and what it does.";
    if (!$cats) return 'Pick at least one category.';
    if ($f['logo_url'] && !str_starts_with($f['logo_url'], 'https://')) return 'The logo link must start with https://.';
    return null;
}

function listing_values(array $f): array
{
    return [
        mb_substr($f['name'], 0, 80), $f['website'], $f['vendor_name'] ?: null, $f['tagline'], mb_substr($f['description'], 0, 4000),
        in_array($f['deployment'], ['Cloud', 'On-premise', 'Hybrid'], true) ? $f['deployment'] : null,
        json_encode(list_from_text($f['regions'], '/,/')), json_encode(list_from_text($f['audiences'], '/,/')),
        json_encode(list_from_text($f['features'], '/\r?\n|,/')), $f['pricing_note'] ?: null, $f['logo_url'] ?: null,
    ];
}

function save_listing_categories(int $softwareId, array $slugs): void
{
    q('DELETE FROM software_categories WHERE software_id = ?', [$softwareId]);
    if (!$slugs) return;
    foreach (all('SELECT id FROM categories WHERE slug IN (' . in_list($slugs) . ')', $slugs) as $c) {
        q('INSERT INTO software_categories (software_id, category_id) VALUES (?, ?)', [$softwareId, $c['id']]);
    }
}

function listing_form(array $f, array $selected, string $cta, ?int $id = null, ?string $error = null): string
{
    $v = fn($k) => e($f[$k] ?? '');
    ob_start(); ?>
<form method="post" class="panel">
  <?= csrf_field() ?><input type="hidden" name="action" value="listing">
  <?php if ($id): ?><input type="hidden" name="id" value="<?= $id ?>"><?php endif; ?>
  <?php if ($error): ?><div class="notice error" role="alert"><p><?= e($error) ?></p></div><?php endif; ?>
  <div class="grid-2">
    <div class="field"><label for="name">Product name</label><input id="name" name="name" maxlength="80" value="<?= $v('name') ?>"></div>
    <div class="field"><label for="website">Website</label><input id="website" name="website" type="url" value="<?= $v('website') ?>" placeholder="https://"></div>
    <div class="field"><label for="vendor_name">Company <span class="muted">(if different)</span></label><input id="vendor_name" name="vendor_name" value="<?= $v('vendor_name') ?>"></div>
    <div class="field"><label for="deployment">Deployment</label><select id="deployment" name="deployment">
      <?php foreach (['Cloud', 'On-premise', 'Hybrid'] as $o): ?><option<?= ($f['deployment'] ?? 'Cloud') === $o ? ' selected' : '' ?>><?= $o ?></option><?php endforeach; ?>
    </select></div>
  </div>
  <div class="field"><label for="tagline">One-line summary</label><input id="tagline" name="tagline" maxlength="120" value="<?= $v('tagline') ?>"><p class="hint">Up to 120 characters. Say what it does, not how great it is.</p></div>
  <div class="field"><label for="description">Description</label><textarea id="description" name="description"><?= $v('description') ?></textarea></div>
  <fieldset class="field"><legend>Categories</legend><div class="checks">
    <?php foreach (categories_with_counts() as $c): ?><label><input type="checkbox" name="categories[]" value="<?= e($c['slug']) ?>"<?= in_array($c['slug'], $selected, true) ? ' checked' : '' ?>> <?= e($c['name']) ?></label><?php endforeach; ?>
  </div></fieldset>
  <div class="grid-2">
    <div class="field"><label for="regions">Markets served</label><input id="regions" name="regions" value="<?= $v('regions') ?>" placeholder="Canada, USA, Australia"><p class="hint">Separate with commas.</p></div>
    <div class="field"><label for="audiences">Built for</label><input id="audiences" name="audiences" value="<?= $v('audiences') ?>" placeholder="RCICs, Immigration law firms"><p class="hint">Separate with commas.</p></div>
  </div>
  <div class="field"><label for="features">Key features</label><textarea id="features" name="features" style="min-height:100px"><?= $v('features') ?></textarea><p class="hint">One per line, up to 20.</p></div>
  <div class="grid-2">
    <div class="field"><label for="pricing_note">Pricing <span class="muted">(optional)</span></label><input id="pricing_note" name="pricing_note" value="<?= $v('pricing_note') ?>" placeholder="From $49 per user per month"></div>
    <div class="field"><label for="logo_url">Logo image link <span class="muted">(optional)</span></label><input id="logo_url" name="logo_url" type="url" value="<?= $v('logo_url') ?>" placeholder="https://…/logo.png"></div>
  </div>
  <button class="btn"><?= e($cta) ?></button>
</form>
<?php
    return (string)ob_get_clean();
}

function listing_to_form(array $s): array
{
    return [
        'name' => $s['name'], 'website' => $s['website'], 'vendor_name' => $s['vendor_name'], 'tagline' => $s['tagline'],
        'description' => $s['description'], 'deployment' => $s['deployment'] ?: 'Cloud', 'regions' => implode(', ', json_list($s['regions'])),
        'audiences' => implode(', ', json_list($s['audiences'])), 'features' => implode("\n", json_list($s['features'])),
        'pricing_note' => $s['pricing_note'], 'logo_url' => $s['logo_url'],
    ];
}
