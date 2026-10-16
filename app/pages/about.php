<?php
$faqs = [
    ['Is ImmigrationCRM.shop independent?', "Yes. We're not owned by any software vendor. Vendors can pay for clearly labelled featured placement, but payment never changes ratings, review order within a listing, or which reviews are published."],
    ['How does ImmigrationCRM.shop make money?', 'From optional featured placement for vendors. Listing a product and writing reviews are free.'],
    ['Who can write a review?', 'Anyone who uses the software at work: immigration lawyers, RCICs, migration agents, paralegals, case managers and corporate mobility teams. Reviewers verify a work email plus LinkedIn or proof of use.'],
    ['How do I correct a listing?', 'If you work for the vendor, claim the listing and edit it. Otherwise, email ' . contact_email() . '.'],
];
$crumbs = [['Home', '/'], ['About', '/about']];
$meta = meta([
    'title' => 'About ImmigrationCRM.shop — independent immigration software reviews', 'absolute' => true,
    'description' => 'Who runs ImmigrationCRM.shop, how listings and verified reviews work, how we make money, and how to contact us.',
    'path' => '/about',
    'jsonld' => [webpage_ld('/about', 'About ' . SITE_NAME, ['about' => ['@id' => org_id()]], 'AboutPage'), breadcrumb_ld($crumbs), faq_ld($faqs)],
]);
?>
<div class="wrap"><?= breadcrumbs_html($crumbs) ?>
<div class="prose">
  <h1>About <?= e(SITE_NAME) ?></h1>
  <p style="font-size:var(--step-1);color:var(--ink-2)"><?= e(SITE_NAME) ?> helps immigration professionals choose software using reviews from people who actually use it at work — not anonymous ratings or vendor marketing.</p>
  <h2>What we cover</h2>
  <ul>
    <li><a href="/category/immigration-crm">Immigration CRMs</a> for RCICs, migration agents and visa consultancies</li>
    <li><a href="/category/immigration-case-management">Immigration case management</a> for law firms</li>
    <li><a href="/category/law-practice-management">Law practice management</a> suites used by immigration practices</li>
    <li><a href="/category/ai-immigration">AI tools for immigration law</a>: petition drafting, research and RFE responses</li>
    <li><a href="/category/corporate-immigration">Corporate immigration and global mobility</a> platforms</li>
  </ul>
  <h2>Editorial policy</h2>
  <p>Starter listings are compiled by our editors from vendors' public information and independent directories, and are marked “unclaimed” until the vendor verifies them. Ratings come only from verified reviews — see <a href="/how-we-verify">how we verify</a>. We never write, buy or edit reviews, and we publish negative reviews that meet our guidelines.</p>
  <h2>Contact</h2>
  <p>Email <a href="mailto:<?= e(contact_email()) ?>"><?= e(contact_email()) ?></a>.</p>
  <?= faq_html($faqs) ?>
</div></div>
