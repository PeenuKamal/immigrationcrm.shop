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
<div class="wrap">
  <?= breadcrumbs_html($crumbs) ?>
  <div class="page-head">
    <h1><?= $q ? 'Results for “' . e($q) . '”' : 'All immigration software' ?></h1>
    <p>Every tool used to run an immigration practice, with reviews only from verified practitioners.</p>
    <form class="search" action="/software" role="search" style="margin-top:16px">
      <label for="q" class="sr-only">Search software</label>
      <input id="q" name="q" type="search" value="<?= e($q) ?>" placeholder="Search by name or feature">
      <button class="btn" type="submit">Search</button>
    </form>
  </div>
  <?= directory_body(null) ?>
</div>
