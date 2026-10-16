<?php
$faqs = [
    ['Are the reviews on ImmigrationCRM.shop verified?', 'Yes, every one. Reviewers confirm a work email with a one-time code (personal email providers are refused) and then either connect LinkedIn or upload proof of use that a moderator checks.'],
    ['Can software vendors review their own product?', "No. If a reviewer's email domain matches the vendor's domain, they can't review that product. Vendors also can't edit or hide reviews."],
    ['Does paying for featured placement change ratings?', 'No. Featured placement only changes where a listing appears and is always labelled. Ratings and reviews are unaffected.'],
];
$crumbs = [['Home', '/'], ['How we verify', '/how-we-verify']];
$meta = meta([
    'title' => 'How we verify reviews: work email, LinkedIn & proof of use',
    'description' => "Every review on ImmigrationCRM.shop comes from a verified practitioner: work email confirmed, plus LinkedIn or moderator-checked proof of use. Vendors can't review themselves.",
    'path' => '/how-we-verify',
    'jsonld' => [webpage_ld('/how-we-verify', 'How we verify reviews'), breadcrumb_ld($crumbs), faq_ld($faqs)],
]);
?>
<div class="wrap"><?= breadcrumbs_html($crumbs) ?>
  <div class="page-head" style="display:grid;gap:32px;grid-template-columns:minmax(0,1fr) auto;align-items:center">
    <div><h1>How we verify reviews</h1><p>A review only goes live after we've confirmed who wrote it and that they actually use the software. No exceptions, including for vendors who pay to be featured.</p></div>
    <?= stamp_svg('review-stamp', 'EVERY REVIEW') ?>
  </div>
  <div class="prose">
    <h2>Every reviewer passes two checks</h2>
    <ol>
      <li><strong>A work email, confirmed with a one-time code.</strong> Personal addresses from Gmail, Yahoo, Outlook, iCloud, Proton and similar providers are refused, along with disposable inboxes.</li>
      <li><strong>One more proof, chosen by the reviewer:</strong><ul>
        <li><strong>LinkedIn.</strong> The reviewer signs in with LinkedIn and we record their verified name and member ID. One LinkedIn profile can back only one reviewer account.</li>
        <li><strong>Proof of use.</strong> A screenshot of the software with client data hidden, or an invoice or receipt. A moderator checks it by hand. Files are stored privately and never published.</li>
      </ul></li>
    </ol>
    <p>Each published review shows a stamp listing which checks it passed.</p>
    <h2>What we block</h2>
    <ul>
      <li><strong>Self-reviews.</strong> If a reviewer's email domain matches the vendor's domain, they can't review that product.</li>
      <li><strong>Duplicates.</strong> One review per person per product.</li>
      <li><strong>Paid or incentivised reviews.</strong> Reviewers confirm they aren't paid or employed by the vendor or a competitor. Reviews that break this are removed.</li>
    </ul>
    <h2>What vendors can and can't do</h2>
    <p>Vendors can list for free, claim their listing, reply publicly to reviews and pay for featured placement. They can't edit, hide or reorder reviews, and paying never changes a rating.</p>
    <h2>Your privacy</h2>
    <p>Reviews are anonymous by default and show only your role, firm size and how long you've used the product. Your name and firm appear only if you choose to show them.</p>
    <?= faq_html($faqs) ?>
    <p style="margin-top:32px"><a class="btn stamp" href="/software">Find software to review</a></p>
  </div>
</div>
