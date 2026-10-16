<?php
$effective = setting('legal_effective_date', '2026-10-16');
$entity = legal('legal_entity_name', 'Legal name of the business');
$ce = e(contact_email());
$crumbs = [['Home', '/'], ['Terms of Service', '/terms']];
$toc = [
    'about' => 'About these Terms', 'eligibility' => 'Eligibility and accounts', 'service' => 'What the service is — and isn\'t', 'verification' => 'Verification',
    'guidelines' => 'Review guidelines', 'licence' => 'Your content and the licence you give us', 'moderation' => 'Moderation, removal and appeals',
    'vendors' => 'Vendor terms', 'featured' => 'Featured placement and payments', 'conduct' => 'Acceptable use', 'reporting' => 'Reporting content and grievances',
    'ip' => 'Our intellectual property', 'thirdparty' => 'Third-party services', 'disclaimers' => 'Disclaimers', 'liability' => 'Limitation of liability',
    'indemnity' => 'Indemnity', 'termination' => 'Suspension and termination', 'changes' => 'Changes', 'law' => 'Governing law and disputes',
    'general' => 'General', 'contact' => 'Contact',
];
$meta = meta([
    'title' => 'Terms of Service',
    'description' => 'The rules for using ImmigrationCRM.shop: accounts, verified reviews, review guidelines, vendor listings, featured placement, content removal, liability and disputes.',
    'path' => '/terms',
    'jsonld' => [webpage_ld('/terms', 'Terms of Service', ['dateModified' => iso_date($effective)]), breadcrumb_ld($crumbs)],
]);
?>
<?= page_band($crumbs, 'Terms of Service', 'The rules for using ImmigrationCRM.shop — for readers, reviewers and software vendors.',
    '<p class="legal-meta"><span>Effective ' . e(format_date($effective)) . '</span><span>Please also read our <a href="/privacy">Privacy Policy</a></span></p>') ?>
<div class="wrap">
  <div class="guide-layout">
    <article class="legal">
      <?php if (!setting('legal_entity_name')): ?>
      <div class="notice warn legal-draft"><p><strong>Draft for legal review.</strong> Highlighted items are filled in from Moderation → Settings → Legal.</p></div>
      <?php endif; ?>

      <section class="key-points" aria-labelledby="kp-h">
        <h2 id="kp-h">The short version</h2>
        <ul>
          <li>Write reviews only about software you have used yourself, at work. Be honest and keep client information out.</li>
          <li>You must not review your employer's product or a competitor's, or accept anything in return for a review.</li>
          <li>You keep ownership of your reviews; you give us a licence to publish and share them.</li>
          <li>Vendors can list, claim and reply for free. Paid featured placement is labelled and never changes ratings.</li>
          <li>The information here helps you choose software. It is not legal advice and not an endorsement.</li>
        </ul>
      </section>

      <h2 id="about">1. About these Terms</h2>
      <p>1.1 These Terms of Service (“<strong>Terms</strong>”) are a legal agreement between you and <?= $entity ?>, <?= legal('legal_entity_type', 'type of entity and registration details') ?>, with its registered address at <?= legal('legal_address', 'registered address') ?> (“<strong>ImmigrationCRM.shop</strong>”, “<strong>we</strong>”, “<strong>us</strong>”). They govern your use of immigrationcrm.shop, its subdomains, feeds, machine-readable files and related services (the “<strong>Service</strong>”).</p>
      <p>1.2 By using the Service you agree to these Terms. If you use the Service for an organisation — for example, a law firm, consultancy or software company — you agree on its behalf and confirm you are authorised to do so; “you” then includes that organisation.</p>
      <p>1.3 Our <a href="/privacy">Privacy Policy</a> explains how we handle personal information and forms part of these Terms. Our <a href="/how-we-verify">verification policy</a> describes how reviews are checked.</p>
      <p>1.4 In these Terms: a “<strong>Reviewer</strong>” is anyone who submits a review; a “<strong>Vendor</strong>” is anyone who lists, claims or manages a software listing or buys featured placement; “<strong>Content</strong>” means anything submitted to the Service, including reviews, ratings, listing information, replies, files and messages.</p>

      <h2 id="eligibility">2. Eligibility and accounts</h2>
      <p>2.1 You must be at least 18 years old and able to form a binding contract. The Service is intended for professional use.</p>
      <p>2.2 You sign in with a one-time code sent to your email address. Reviews and listings require a work email address at an organisation you belong to. You must give accurate information, keep your email account secure, and tell us promptly if you think someone has accessed your account.</p>
      <p>2.3 You may hold only one reviewer account. You must not create accounts using another person's email address, impersonate anyone, misrepresent your role or employer, or use temporary or shared mailboxes to get around our checks.</p>
      <p>2.4 You are responsible for activity under your account unless it results from our failure to take reasonable security measures.</p>

      <h2 id="service">3. What the Service is — and isn't</h2>
      <p>3.1 ImmigrationCRM.shop is an independent directory of software used by immigration professionals, with verified user reviews, comparisons and buying guides.</p>
      <p>3.2 <strong>Not legal or immigration advice.</strong> Nothing on the Service is legal advice, immigration advice, or advice about any person's immigration matter. References to regulators, government departments, forms and programs are general background and may change. Always check current requirements with the relevant authority or a licensed professional.</p>
      <p>3.3 <strong>Not an endorsement.</strong> Listing a product, ranking it, or showing reviews does not mean we endorse, certify or guarantee it. Reviews are the opinions of individual Reviewers, not our opinions, and reflect their own experience.</p>
      <p>3.4 <strong>Listing information.</strong> Product information comes from Vendors and from public sources, including vendors' websites and independent directories. “Unclaimed” listings were compiled by our editors and have not been confirmed by the Vendor. We try to keep information accurate, but features, prices, availability and ownership change. Confirm details with the Vendor before you buy.</p>
      <p>3.5 <strong>Rankings.</strong> Category pages, comparisons and country guides are ordered by the methods we publish on those pages — based on fit with the market and on verified reviews. Paid featured placement may change where a listing appears in some lists, and is always labelled, but never changes ratings, reviews or the order of our country-guide rankings. A ranking is a starting point for your own evaluation, not a recommendation for your particular situation.</p>
      <p>3.6 <strong>Machine-readable content.</strong> We make public pages available to search engines and AI assistants, including through Markdown versions, feeds and our llms.txt file. Third parties may summarise or quote this content; we are not responsible for how they present it.</p>
      <p>3.7 We may add, change or remove features, categories or listings at any time.</p>

      <h2 id="verification">4. Verification</h2>
      <p>4.1 Before a review is published, a Reviewer must confirm a work email and either connect LinkedIn or provide proof of use that a moderator accepts. We also block reviews where the Reviewer's email domain matches the product's verified domain.</p>
      <p>4.2 Verification reduces fake and conflicted reviews; it does not guarantee that every statement in a review is true, that a Reviewer has no undisclosed conflict, or that verification cannot be circumvented. We may change our checks to keep them effective.</p>
      <p>4.3 You must not try to defeat, mislead or bypass verification, including by submitting altered or someone else's proof of use.</p>

      <h2 id="guidelines">5. Review guidelines</h2>
      <p>5.1 <strong>When you may review.</strong> You may review a product only if you have used it yourself in a professional setting within the last 24 months, and you are not — and within the last 12 months were not — employed by, contracted to, an investor in, or a reseller or close competitor of the Vendor.</p>
      <p>5.2 <strong>No incentives.</strong> You must not accept or be offered money, discounts, gifts, credits, entries into prize draws, or any other benefit in exchange for writing a review or for writing it a particular way. Vendors may invite customers to leave a review, but only with neutral wording sent to all customers, not only happy ones.</p>
      <p>5.3 <strong>What a review must be.</strong> Honest and based on your own experience; specific about what worked and what didn't; fair, even when critical; and in English (or another language we support).</p>
      <p>5.4 <strong>What a review must not contain:</strong></p>
      <ul>
        <li>client names, case numbers, application numbers, or any confidential or privileged information about clients or matters;</li>
        <li>personal information about other people, such as a vendor employee's contact details;</li>
        <li>statements of fact you know or suspect are false, or allegations of crimes or professional misconduct without a factual basis;</li>
        <li>threats, harassment, hate speech, discriminatory content, obscene material or personal attacks;</li>
        <li>content that infringes copyright, trade marks, trade secrets or other rights, or that breaches a confidentiality agreement (for example, contract prices you agreed to keep private);</li>
        <li>advertising, referral links, links to competitors, or contact details for soliciting business;</li>
        <li>malware, code or anything designed to interfere with the Service.</li>
      </ul>
      <p>5.5 <strong>One review per product.</strong> If your experience changes, contact us and we will help you update your review. Updated reviews go through verification again.</p>
      <p>5.6 <strong>Proof of use.</strong> Hide client information and anything unnecessary before uploading. Files are used only for verification and are deleted automatically as described in the Privacy Policy.</p>

      <h2 id="licence">6. Your content and the licence you give us</h2>
      <p>6.1 You keep ownership of the Content you submit.</p>
      <p>6.2 You grant us a worldwide, non-exclusive, royalty-free, transferable licence, with the right to sublicense to our hosting and service providers and to search engines and AI services that index our public pages, to host, store, reproduce, publish, display, translate, format, excerpt, summarise, aggregate (for example, into average ratings and comparisons) and distribute your Content in connection with the Service and its promotion, in any media. This licence lasts while the Content is on the Service and for a reasonable period afterwards for backups, legal compliance, and copies already in caches and feeds.</p>
      <p>6.3 Ratings and statistics derived from your review (such as averages) may remain after your review is removed, recalculated without it.</p>
      <p>6.4 To the extent the law allows, you waive, or agree not to assert, moral rights that would prevent us from editing Content for length, formatting, redaction of confidential or personal information, or display — we will not change the meaning of a review.</p>
      <p>6.5 You confirm that you own or have the right to submit your Content, that it complies with these Terms and the law, and that publishing it will not breach anyone's rights or any confidentiality obligation.</p>
      <p>6.6 We do not pay for Content. Reviews are anonymous by default; your name and firm are shown only if you choose. We may disclose a Reviewer's identity only as described in the Privacy Policy, for example when required by law.</p>

      <h2 id="moderation">7. Moderation, removal and appeals</h2>
      <p>7.1 We may review, decline to publish, edit for formatting or redaction, label, or remove any Content, and may pause or restrict accounts, where we reasonably believe it breaches these Terms, the law or the rights of others, or puts users or the Service at risk. We do not edit the substance of reviews.</p>
      <p>7.2 We are not obliged to monitor all Content and do not check every statement. We act on reports as described in section 11.</p>
      <p>7.3 When we reject or remove your review, we will tell you why, unless the law prevents us or doing so would compromise an investigation. You may appeal by replying to our message or emailing <a href="mailto:<?= $ce ?>"><?= $ce ?></a> within 30 days. A person who did not make the original decision will reconsider it where practicable.</p>
      <p>7.4 Vendors cannot edit, hide, reorder or remove reviews, and paying us never changes the outcome of moderation.</p>

      <h2 id="vendors">8. Vendor terms</h2>
      <p>8.1 <strong>Listings.</strong> Listing is free. You must be authorised by the company that owns the product to list, claim or manage it. Listing information must be accurate, current and not misleading, including features, prices, markets served and certifications. Logo links must point to images you are allowed to use.</p>
      <p>8.2 <strong>Claims.</strong> A listing may be claimed automatically if your work email matches the product's verified domain, or after we confirm your authority. We may reverse a claim if we learn it was made without authority.</p>
      <p>8.3 <strong>Replies.</strong> You may reply publicly to reviews of your product. Replies must be professional and factual, must not reveal any information about the Reviewer, their firm or their account (even if you think you know who wrote the review), and must not offer incentives to change or remove a review.</p>
      <p>8.4 <strong>Review integrity.</strong> You must not write, buy, commission or incentivise reviews of your own or a competitor's product; invite only satisfied customers; pressure, threaten or contact Reviewers about their reviews; or use legal threats to suppress honest reviews. Breaches may lead to removal of affected reviews, a public notice on your listing, suspension of featured placement without refund, and termination.</p>
      <p>8.5 <strong>Trade marks.</strong> You grant us a non-exclusive, royalty-free licence to display your product names, logos and screenshots on the Service to identify your product. Product names and logos remain the property of their owners. Third-party trade marks on the Service are used for identification only and do not imply affiliation.</p>
      <p>8.6 <strong>Listing removal.</strong> We may keep a listing of a commercially available product, together with its reviews, even if you ask us to remove it, because accurate information about products serves our readers. We will correct factual errors you tell us about and will remove a listing for a product that is discontinued. You may stop managing a listing at any time.</p>

      <h2 id="featured">9. Featured placement and payments</h2>
      <p>9.1 Featured placement is advertising. It may show your listing first in categories and on some pages, with a “Featured” label and highlighted design. It does not affect ratings, reviews, verification, moderation, or the order of our country-guide rankings.</p>
      <p>9.2 Prices are shown before you pay and are charged in advance for a fixed period (for example 1, 3 or 12 months). Featured placement does not renew automatically; buying again extends the current period.</p>
      <p>9.3 Payments are processed by Stripe, subject to Stripe's terms. We do not receive your full card details. You are responsible for any taxes, duties or bank charges that apply, other than taxes on our income. Where we must charge Goods and Services Tax (GST), VAT or a similar tax, it will be shown at checkout or on your invoice.</p>
      <p>9.4 <strong>Refunds.</strong> Featured placement starts when payment is confirmed. Because it begins immediately, fees are non-refundable once the placement is active, except where we end it for reasons that are not your fault, in which case we refund the unused portion, or where the law gives you a right to a refund.</p>
      <p>9.5 We may decline or end featured placement for products we reasonably consider misleading, unlawful or harmful. If you dispute a charge with your bank without first contacting us, we may suspend featured placement while the dispute is resolved.</p>

      <h2 id="conduct">10. Acceptable use</h2>
      <p>You must not:</p>
      <ul>
        <li>use the Service in breach of any law, or to infringe anyone's rights;</li>
        <li>post fake, duplicate, incentivised or conflicted reviews, or manipulate ratings, rankings or verification;</li>
        <li>impersonate any person or organisation, or misrepresent your role, employer or relationship with a Vendor;</li>
        <li>access accounts, moderation tools or data you are not authorised to access, probe or test the Service's security without our written permission, or interfere with its operation (including by overloading it);</li>
        <li>copy, scrape or harvest the Service by automated means, except (a) search engines and AI services following our robots.txt rules, and (b) reasonable personal or research use that respects robots.txt and does not burden the Service. You may not use automated collection to build or train a competing directory, review database or ranking product without our written permission;</li>
        <li>remove or obscure attribution when quoting the Service, or present our rankings or reviews as your own;</li>
        <li>use information from the Service to contact Reviewers, or to send unsolicited marketing;</li>
        <li>upload malware or anything harmful;</li>
        <li>help or encourage anyone else to do any of these things.</li>
      </ul>
      <p>Quoting short extracts of reviews or rankings with a link to the source page is welcome.</p>

      <h2 id="reporting">11. Reporting content and grievances</h2>
      <p>11.1 If you think Content is false, defamatory, infringes your rights, contains personal or confidential information, or otherwise breaches these Terms, email <a href="mailto:<?= $ce ?>"><?= $ce ?></a> with: the page address; the specific Content; why you believe it is unlawful or in breach (including, for intellectual property, the work and the rights you own); your name and contact details; and a statement that your report is accurate and made in good faith.</p>
      <p>11.2 We will review reports promptly and may remove or restrict Content, ask the Reviewer to respond, or decide not to act. We may share the substance of a report (without your contact details unless needed) with the Reviewer so they can respond. Knowingly false reports may make you liable for resulting harm.</p>
      <p>11.3 Disagreeing with a negative review is not on its own a reason for removal. Vendors may reply publicly (section 8.3).</p>
      <p>11.4 <strong>Grievance Officer (India).</strong> In line with the Information Technology Act, 2000 and the rules made under it, complaints about Content or the Service may be sent to our Grievance Officer: <?= legal('grievance_officer_name', 'Grievance Officer name') ?>, <?= setting('grievance_officer_email') ? '<a href="mailto:' . e(setting('grievance_officer_email')) . '">' . e(setting('grievance_officer_email')) . '</a>' : legal('grievance_officer_email', 'Grievance Officer email') ?>, <?= legal('legal_address', 'registered address') ?>. We acknowledge complaints within 24 hours and aim to resolve them within 15 days, or sooner where the law requires.</p>

      <h2 id="ip">12. Our intellectual property</h2>
      <p>12.1 The Service — including its design, logo, software, text written by us, rankings, compilations of listings and reviews, and data structure — belongs to us or our licensors and is protected by law. Except for the limited use in section 10 and what the law allows, you may not copy, adapt or redistribute it without our written permission.</p>
      <p>12.2 “ImmigrationCRM.shop”, our logo and related marks are ours. You may use them only to refer to the Service accurately.</p>
      <p>12.3 If you send us suggestions or feedback, we may use them without restriction or payment.</p>

      <h2 id="thirdparty">13. Third-party services</h2>
      <p>The Service links to Vendors' websites and uses third-party services including LinkedIn (identity verification) and Stripe (payments). Those services are governed by their own terms and privacy policies. We are not responsible for third-party websites, products or services, including any software you buy after using the Service. Your contract for any software is with its Vendor.</p>

      <h2 id="disclaimers">14. Disclaimers</h2>
      <p>14.1 We provide the Service with reasonable care and skill. To the extent the law allows, the Service and all Content are otherwise provided “as is” and “as available”, and we exclude all other warranties, conditions and representations, express or implied, including about accuracy, completeness, fitness for a particular purpose, merchantability and non-infringement.</p>
      <p>14.2 We do not guarantee that the Service will be uninterrupted, error-free or secure, that defects will be corrected, or that any product will meet your needs or comply with the rules that apply to your practice.</p>
      <p>14.3 Nothing in these Terms excludes rights you have as a consumer that cannot be excluded by contract.</p>

      <h2 id="liability">15. Limitation of liability</h2>
      <p>15.1 Nothing in these Terms limits liability for death or personal injury caused by negligence, for fraud or fraudulent misrepresentation, or for anything else that cannot be limited by law.</p>
      <p>15.2 Subject to 15.1, we are not liable for any indirect, incidental, special, consequential or punitive loss, or for loss of profits, revenue, business, clients, goodwill, data or opportunity, arising from or related to the Service, Content, or any software you choose — whether in contract, tort (including negligence), breach of statutory duty or otherwise — even if we were told such loss was possible.</p>
      <p>15.3 Subject to 15.1, our total liability to you for all claims arising from or related to the Service or these Terms is limited to the greater of (a) the amounts you paid us in the 12 months before the event giving rise to the claim and (b) USD 100 (or its equivalent in your local currency).</p>
      <p>15.4 We are not responsible for Content posted by Reviewers or Vendors, or for decisions you make based on it.</p>

      <h2 id="indemnity">16. Indemnity</h2>
      <p>To the extent the law allows, you agree to indemnify and hold harmless ImmigrationCRM.shop and its officers, employees and contractors against claims, losses, liabilities and reasonable costs (including legal fees) arising from your Content, your breach of these Terms, or your violation of law or of anyone's rights. We will tell you promptly about any such claim and let you take part in its defence. This section does not apply to consumers where the law does not permit it.</p>

      <h2 id="termination">17. Suspension and termination</h2>
      <p>17.1 You may stop using the Service and delete your account at any time from <a href="/account">My account</a>. Deleting your account deletes your reviews.</p>
      <p>17.2 We may suspend or close your account, or restrict features, if you seriously or repeatedly breach these Terms, if we must do so by law, or if your use creates risk for others or for the Service. Where reasonable, we will tell you first and give you a chance to respond.</p>
      <p>17.3 Sections that by their nature should continue — including 6 (licence, to the extent described), 12, 14, 15, 16, 19 and 20 — survive termination.</p>

      <h2 id="changes">18. Changes</h2>
      <p>We may update these Terms to reflect changes to the Service, our business or the law. The effective date at the top shows when they last changed. For material changes, we will give at least 14 days' notice by email or a prominent notice on the Service before they take effect, except where a change is required sooner by law or to address abuse. If you continue to use the Service after changes take effect, you accept them; if you do not agree, you should stop using the Service and may delete your account.</p>

      <h2 id="law">19. Governing law and disputes</h2>
      <p>19.1 These Terms and any dispute arising from them or the Service are governed by <?= legal('governing_law', 'governing law') ?>, without regard to conflict-of-law rules.</p>
      <p>19.2 Before starting formal proceedings, please contact us at <a href="mailto:<?= $ce ?>"><?= $ce ?></a> so we can try to resolve the dispute informally. Both sides will try in good faith for 30 days.</p>
      <p>19.3 Subject to 19.4, the courts at <?= legal('legal_venue', 'courts / venue') ?> have exclusive jurisdiction.</p>
      <p>19.4 If you are a consumer, you also benefit from any mandatory protections of the law of the country where you live, and you may bring proceedings in your local courts where that law allows.</p>

      <h2 id="general">20. General</h2>
      <p>20.1 <strong>Entire agreement.</strong> These Terms, the Privacy Policy and any terms shown at checkout are the whole agreement between you and us about the Service.</p>
      <p>20.2 <strong>Severability.</strong> If any part of these Terms is found unenforceable, the rest stays in effect, and the affected part is applied to the fullest extent permitted.</p>
      <p>20.3 <strong>No waiver.</strong> If we do not enforce a right immediately, we may still enforce it later.</p>
      <p>20.4 <strong>Assignment.</strong> You may not transfer your rights under these Terms without our consent. We may transfer ours to a successor of our business, and will tell you if this happens.</p>
      <p>20.5 <strong>Force majeure.</strong> We are not responsible for delays or failures caused by events outside our reasonable control.</p>
      <p>20.6 <strong>Notices.</strong> We may send notices to the email address on your account or show them on the Service. You may send notices to <a href="mailto:<?= $ce ?>"><?= $ce ?></a> or our registered address.</p>
      <p>20.7 <strong>Relationship.</strong> Nothing in these Terms creates a partnership, agency, employment or fiduciary relationship.</p>
      <p>20.8 <strong>Language.</strong> These Terms are written in English. Any translation is for convenience; the English version prevails.</p>

      <h2 id="contact">21. Contact</h2>
      <p><?= $entity ?>, <?= legal('legal_address', 'registered address') ?>. Email: <a href="mailto:<?= $ce ?>"><?= $ce ?></a>.</p>
      <p class="small muted">See also: <a href="/privacy">Privacy Policy</a> · <a href="/how-we-verify">How we verify reviews</a> · <a href="/about">About us</a></p>
    </article>

    <details class="toc legal-toc" open>
      <summary>Contents</summary>
      <ol class="toc-list"><?php $n = 0; foreach ($toc as $id => $label): ?><li><a href="#<?= $id ?>"><?php if ($id !== 'summary') { $n++; echo '<span class="toc-n">' . $n . '.</span> '; } ?><?= e($label) ?></a></li><?php endforeach; ?></ol>
    </details>
    <script>if (matchMedia('(max-width: 999px)').matches) document.currentScript.previousElementSibling.removeAttribute('open');</script>
  </div>
</div>
