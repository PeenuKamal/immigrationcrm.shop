<?php
$cats = categories_with_counts();
$top = search_software(['category' => 'immigration-crm', 'sort' => 'featured', 'limit' => 6]);
$names = category_names_for(array_column($top, 'id'));
$latest = recent_reviews(3);
$total = published_count();
$reviewsTotal = (int)val("SELECT COUNT(*) FROM reviews WHERE status = 'PUBLISHED'");
$pairs = popular_pairs();
$want = ['docketwise-vs-lollylaw', 'caseeasy-vs-maple-crm', 'clio-vs-mycase', 'parley-ai-vs-visalaw-ai', 'agentcis-vs-maple-crm', 'inszoom-vs-lollylaw'];
$featuredPairs = array_values(array_filter($pairs, fn($p) => in_array("{$p['a']}-vs-{$p['b']}", $want, true)));
$canada = country('canada');
$caTop = array_slice(rank_for_country($canada, 'immigration-crm'), 0, 3);
$faqs = [
    ['What is the best immigration CRM in Canada?', 'Based on ImmigrationCRM.shop\'s fit ranking and verified reviews, leading immigration CRMs for Canadian RCICs and immigration lawyers include ' . join_names(array_map(fn($r) => $r['s']['name'], $caTop)) . '. See the full, regularly updated ranking at ' . url('/best/immigration-crm/canada') . '.'],
    ['What is an immigration CRM?', 'An immigration CRM is software that immigration consultants, RCICs, migration agents and law firms use to manage enquiries and leads, client intake, document checklists, government forms, case stages, client communication and billing in one place.'],
    ['What is the difference between an immigration CRM and immigration case management software?', 'Immigration CRMs focus on the sales side — enquiries, assessments, follow-ups and retainers — plus case tracking, and are popular with consultancies. Immigration case management software focuses on filing: government forms libraries (such as USCIS or IRCC forms), deadlines, evidence and case status, and is popular with law firms. Many products now do both.'],
    ['How are reviews on ImmigrationCRM.shop verified?', "Each reviewer confirms a work email with a one-time code, then either connects LinkedIn or uploads proof of use that a moderator checks. Vendors can't review their own products."],
    ['Can I compare immigration software side by side?', 'Yes. Choose any two to four products on the Compare page to see verified ratings, features, markets served, pricing and real pros and cons side by side.'],
    ['Is listing software on ImmigrationCRM.shop free?', 'Yes. Vendors list for free and can claim their listing to keep it accurate and reply to reviews. Optional featured placement is paid and always labelled.'],
];
$meta = meta([
    'title' => 'Best Immigration CRM & Case Management Software (' . year() . ') — Verified Reviews | ' . SITE_NAME,
    'absolute' => true,
    'description' => 'Compare immigration CRMs, case management, law practice management and AI tools for RCICs, immigration lawyers and migration agents. Every review is verified.',
    'path' => '/',
    'jsonld' => [webpage_ld('/', 'Immigration software reviews', ['about' => 'Immigration CRM and case management software']), faq_ld($faqs)],
]);
?>
<section class="hero">
  <svg class="hero-path" viewBox="0 0 1440 640" preserveAspectRatio="xMidYMid slice" aria-hidden="true">
    <defs><linearGradient id="hp" x1="0" x2="1"><stop offset="0" stop-color="#ffd66b" stop-opacity="0"/><stop offset=".35" stop-color="#ffd66b" stop-opacity=".55"/><stop offset="1" stop-color="#f6b93b" stop-opacity=".9"/></linearGradient></defs>
    <path d="M-40 640 C 320 650, 640 600, 860 470 S 1220 150, 1480 50" fill="none" stroke="url(#hp)" stroke-width="3" stroke-linecap="round" stroke-dasharray="0.1 14"/>
  </svg>
  <div class="wrap">
    <div>
      <span class="badge gold" style="margin-bottom:18px"><?= icon('shield', 14) ?>Every review verified by work email</span>
      <h1>Find the right immigration software with confidence.</h1>
      <p class="lede">Honest, verified reviews of immigration CRMs, case management, practice management and AI tools — written by RCICs, immigration lawyers and migration agents who use them every day.</p>
      <form class="hero-search" action="/software" role="search">
        <label for="q" class="sr-only">Search immigration software</label>
        <input id="q" name="q" type="search" placeholder="Search tools or features, e.g. IRCC forms">
        <button class="btn gold" type="submit"><?= icon('search', 18) ?>Search</button>
      </form>
      <div class="popular">
        <span>Popular:</span>
        <a href="/best/immigration-crm/canada">Best CRM in Canada</a>
        <a href="/best/immigration-case-management/usa">Case management in the USA</a>
        <a href="/category/ai-immigration">AI drafting tools</a>
        <a href="/compare">Compare tools</a>
      </div>
    </div>
    <aside class="trust-card" aria-labelledby="trust-h">
      <h2 id="trust-h">How every review is verified</h2>
      <p>No anonymous ratings. No vendor-written reviews.</p>
      <div class="check-step"><span class="ico"><?= icon('mail') ?></span><div><strong>Work email confirmed</strong><span>Gmail, Yahoo and Outlook addresses refused</span></div><span class="ok"><?= icon('check', 16) ?></span></div>
      <div class="check-step"><span class="ico"><?= icon('linkedin') ?></span><div><strong>LinkedIn or proof of use</strong><span>Screenshots and invoices checked by hand</span></div><span class="ok"><?= icon('check', 16) ?></span></div>
      <div class="check-step"><span class="ico"><?= icon('ban') ?></span><div><strong>No self-reviews</strong><span>Vendor staff can't review their own tool</span></div><span class="ok"><?= icon('check', 16) ?></span></div>
      <p class="note"><?= icon('shield', 16) ?> Featured placement never changes a rating.</p>
    </aside>
  </div>
</section>

<div class="wrap">
  <section class="stats" aria-label="Directory at a glance">
    <ul>
      <li><strong><?= $total ?></strong><span>tools listed</span></li>
      <li><strong><?= count($cats) ?></strong><span>software categories</span></li>
      <li><strong><?= count(COUNTRIES) ?></strong><span>country buying guides</span></li>
      <li><strong>100%</strong><span>verified reviews<?= $reviewsTotal ? " · $reviewsTotal published" : '' ?></span></li>
    </ul>
  </section>

  <section class="section" aria-labelledby="countries-h">
    <div class="section-head"><div><h2 id="countries-h">Best immigration software by country</h2><p>Rankings built around each country's forms, regulators and programs.</p></div><a href="/best">All country guides</a></div>
    <div class="country-grid">
      <?php foreach (COUNTRIES as $slug => $c): ?>
      <a class="country-card" href="/best/<?= $slug ?>"><span class="flag <?= $c['class'] ?>"><?= $c['code'] ?></span><div><strong><?= e($c['short'] ?? $c['name']) ?></strong><span>For <?= e($c['who']) ?></span></div></a>
      <?php endforeach; ?>
    </div>
  </section>

  <section class="section" aria-labelledby="cats">
    <div class="section-head"><div><h2 id="cats">Browse by category</h2><p>Every kind of software an immigration practice runs on.</p></div><a href="/software">All <?= $total ?> tools</a></div>
    <div class="card-grid">
      <?php foreach ($cats as $c): ?>
      <a class="cat-card" href="/category/<?= e($c['slug']) ?>">
        <span class="ico"><?= icon(CATEGORY_ICONS[$c['slug']] ?? 'folder', 24) ?></span>
        <h3><?= e($c['name']) ?></h3>
        <p><?= e($c['description']) ?></p>
        <span class="count"><?= (int)$c['cnt'] ?> tools</span>
      </a>
      <?php endforeach; ?>
    </div>
  </section>

  <section class="section" aria-labelledby="top">
    <div class="section-head"><div><h2 id="top">Immigration CRMs for consultants</h2><p>Lead-to-visa platforms for RCICs, migration agents and visa consultancies.</p></div><a href="/category/immigration-crm">See all</a></div>
    <ul class="listing-rows"><?php foreach ($top as $s) echo listing_row($s, $names[$s['id']] ?? []); ?></ul>
  </section>

  <section class="section" aria-labelledby="how">
    <div class="section-head"><div><h2 id="how">Reviews you can trust</h2><p>Three checks stand between every review and the page.</p></div><a href="/how-we-verify">Our verification policy</a></div>
    <div class="steps-grid">
      <div class="step"><span class="ico"><?= icon('mail', 24) ?></span><h3>Work email confirmed</h3><p>Reviewers sign in with a one-time code sent to their work address. Personal and disposable inboxes are refused.</p></div>
      <div class="step"><span class="ico"><?= icon('file', 24) ?></span><h3>Real usage proven</h3><p>They connect LinkedIn, or upload a screenshot or invoice that our moderators check by hand.</p></div>
      <div class="step"><span class="ico"><?= icon('shield', 24) ?></span><h3>Conflicts blocked</h3><p>Vendor staff can't review their own product, everyone gets one review per tool, and paid placement never touches ratings.</p></div>
    </div>
  </section>

  <section class="section" aria-labelledby="latest">
    <div class="section-head"><div><h2 id="latest">Latest verified reviews</h2></div></div>
    <?php if ($latest): ?>
      <div class="stack"><?php foreach ($latest as $r) echo review_card($r, $r['s_name']); ?></div>
    <?php else: ?>
      <div class="empty">
        <h3>Be one of the first verified reviewers</h3>
        <p>Use one of these tools at your firm? Your review helps other practitioners choose — it takes about five minutes.</p>
        <a class="btn" href="/software">Pick the software you use</a>
      </div>
    <?php endif; ?>
  </section>

  <section class="section" aria-labelledby="cmp">
    <div class="section-head"><div><h2 id="cmp">Popular comparisons</h2><p>Put any two to four tools side by side.</p></div><a href="/compare">Build a comparison</a></div>
    <ul class="link-grid">
      <?php foreach ($featuredPairs as $p): ?><li><a href="<?= e(compare_path([$p['a'], $p['b']])) ?>"><?= e("{$p['an']} vs {$p['bn']}") ?></a></li><?php endforeach; ?>
    </ul>
  </section>

  <section class="section"><?= faq_html($faqs) ?></section>

  <section class="section">
    <div class="cta-band">
      <div><h2>Build software for immigration professionals?</h2><p>Listing is free. Claim your profile to keep it accurate and reply to verified reviews.</p></div>
      <a class="btn gold large" href="/list-your-software">List your software</a>
    </div>
  </section>
</div>
