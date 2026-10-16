<?php
if (($c = get('category')) !== '') {
    $rest = array_filter(['q' => get('q'), 'region' => get('region'), 'sort' => get('sort')]);
    redirect('/category/' . rawurlencode($c) . ($rest ? '?' . http_build_query($rest) : ''), 301);
}
$q = get('q');
$results = search_software(['q' => $q]);
$crumbs = [['Home', '/'], ['All software', '/software']];
$meta = meta([
    'title' => $q ? "“{$q}” — immigration software search" : 'All immigration software: verified reviews (' . year() . ')',
    'description' => 'Every immigration CRM, case management, law practice management and AI tool, with ratings only from verified practitioners. Filter by market and compare side by side.',
    'path' => '/software',
    'noindex' => (bool)($q || get('region') || get('sort')),
    'jsonld' => [breadcrumb_ld($crumbs), item_list_ld('Immigration software directory', array_map(fn($s) => [$s['name'], "/software/{$s['slug']}"], $results))],
]);
?>
<?= page_band($crumbs, $q ? 'Results for “' . e($q) . '”' : 'All immigration software', 'Every tool used to run an immigration practice, with reviews only from verified practitioners.',
  '<form class="search" action="/software" role="search" style="margin-top:20px"><label for="q" class="sr-only">Search software</label><input id="q" name="q" type="search" value="' . e($q) . '" placeholder="Search by name or feature"><button class="btn" type="submit">' . icon('search', 18) . 'Search</button></form>') ?>
<div class="wrap">
  <?= directory_body(null) ?>
</div>
