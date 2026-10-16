<?php
$cats = categories_with_counts();
$top = search_software(['category' => 'immigration-crm', 'sort' => 'featured', 'limit' => 6]);
$names = category_names_for(array_column($top, 'id'));
$latest = recent_reviews(3);
$total = published_count();
$pairs = popular_pairs();
$want = ['docketwise-vs-lollylaw', 'caseeasy-vs-maple-crm', 'clio-vs-mycase', 'parley-ai-vs-visalaw-ai', 'agentcis-vs-maple-crm', 'inszoom-vs-lollylaw'];
$featuredPairs = array_values(array_filter($pairs, fn($p) => in_array("{$p['a']}-vs-{$p['b']}", $want, true)));
$faqs = [
    ['What is an immigration CRM?', 'An immigration CRM is software that immigration consultants, RCICs, migration agents and law firms use to manage enquiries and leads, client intake, document checklists, government forms, case stages, client communication and billing in one place.'],
    ['What is the difference between an immigration CRM and immigration case management software?', 'Immigration CRMs focus on the sales side — enquiries, assessments, follow-ups and retainers — plus case tracking, and are popular with consultancies. Immigration case management software focuses on filing: government forms libraries (such as USCIS or IRCC forms), deadlines, evidence and case status, and is popular with law firms. Many products now do both.'],
    ['How are reviews on ImmigrationCRM.shop verified?', "Each reviewer confirms a work email with a one-time code, then either connects LinkedIn or uploads proof of use that a moderator checks. Vendors can't review their own products."],
    ['Can I compare immigration software side by side?', 'Yes. Choose any two to four products on the Compare page to see verified ratings, features, markets served, pricing and real pros and cons side by side.'],
    ['Is listing software on ImmigrationCRM.shop free?', 'Yes. Vendors list for free and can claim their listing to keep it accurate and reply to reviews. Optional featured placement is paid and always labelled.'],
];
$meta = meta([
    'title' => 'Immigration CRM & case management software reviews (' . year() . ') | ' . SITE_NAME,
    'absolute' => true,
    'description' => 'Compare immigration CRMs, case management, law practice management and AI tools. Every review is from a verified immigration lawyer, RCIC or migration agent.',
    'path' => '/',
    'jsonld' => [webpage_ld('/', 'Immigration software reviews', ['about' => 'Immigration CRM and case management software']), faq_ld($faqs)],
]);
?>
<div class="wrap">
  <section class="hero">
    <div>
      <h1>Immigration software, reviewed only by people who file the cases.</h1>
      <p class="hero-lede">Compare immigration CRMs, case management, practice management and AI tools. Every review comes from a practitioner whose work email and employment we've checked.</p>
      <form class="search" action="/software" role="search">
        <label for="q" class="sr-only">Search software</label>
        <input id="q" name="q" type="search" placeholder="Try “IRCC forms” or “RFE drafting”">
        <button class="btn" type="submit">Search</button>
      </form>
      <div class="quick-cats">
        <?php foreach ($cats as $c): ?><a class="chip" href="/category/<?= e($c['slug']) ?>"><?= e($c['name']) ?></a><?php endforeach; ?>
      </div>
    </div>
    <div class="hero-stamp-block">
      <?= stamp_svg('stamp-big', 'EVERY REVIEW', 'Every review is verified') ?>
      <ol class="steps" aria-label="How a review gets verified">
        <li><div><strong>Work email confirmed</strong><span>Personal Gmail, Yahoo and Outlook addresses are refused.</span></div></li>
        <li><div><strong>Identity or usage proven</strong><span>A LinkedIn profile, or a screenshot or invoice we check by hand.</span></div></li>
        <li><div><strong>No self-reviews</strong><span>Staff can't review their own employer's product.</span></div></li>
      </ol>
    </div>
  </section>

  <section class="section" aria-labelledby="cats">
    <div class="section-head"><h2 id="cats">Browse by what you need</h2><a href="/software">All <?= $total ?> listings</a></div>
    <div class="cat-list">
      <?php foreach ($cats as $c): ?>
      <a href="/category/<?= e($c['slug']) ?>"><strong><?= e($c['name']) ?></strong><span class="count"><?= (int)$c['cnt'] ?> tools</span><span class="desc"><?= e($c['description']) ?></span></a>
      <?php endforeach; ?>
    </div>
  </section>

  <section class="section" aria-labelledby="top">
    <div class="section-head"><h2 id="top">Immigration CRMs for consultants</h2><a href="/category/immigration-crm">See all in this category</a></div>
    <ul class="listing-rows"><?php foreach ($top as $s) echo listing_row($s, $names[$s['id']] ?? []); ?></ul>
  </section>

  <section class="section" aria-labelledby="latest">
    <div class="section-head"><h2 id="latest">Latest verified reviews</h2></div>
    <?php if ($latest): ?>
      <div class="stack"><?php foreach ($latest as $r) echo review_card($r, $r['s_name']); ?></div>
    <?php else: ?>
      <div class="empty">
        <h3>The first verified reviews are being written now</h3>
        <p>Use one of these tools at your firm? Your review helps other practitioners choose — it takes about five minutes.</p>
        <a class="btn stamp" href="/software">Pick the software you use</a>
      </div>
    <?php endif; ?>
  </section>

  <section class="section" aria-labelledby="cmp">
    <div class="section-head"><h2 id="cmp">Compare side by side</h2><a href="/compare">Compare any 2–4 products</a></div>
    <ul class="link-grid">
      <?php foreach ($featuredPairs as $p): ?><li><a href="<?= e(compare_path([$p['a'], $p['b']])) ?>"><?= e("{$p['an']} vs {$p['bn']}") ?></a></li><?php endforeach; ?>
    </ul>
  </section>

  <section class="section"><?= faq_html($faqs) ?></section>

  <section class="section">
    <div class="panel" style="display:flex;gap:24px;flex-wrap:wrap;justify-content:space-between;align-items:center">
      <div><h2 style="font-size:var(--step-2)">Build immigration software?</h2><p style="margin:0" class="muted">Listing is free. Claim your profile to keep it accurate and reply to reviews.</p></div>
      <a class="btn" href="/list-your-software">List your software</a>
    </div>
  </section>
</div>
