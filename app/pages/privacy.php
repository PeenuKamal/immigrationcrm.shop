<?php
$effective = setting('legal_effective_date', '2026-10-16');
$entity = legal('legal_entity_name', 'Legal name of the business');
$pe = e(privacy_email());
$crumbs = [['Home', '/'], ['Privacy Policy', '/privacy']];
$toc = [
    'summary' => 'Summary', 'who' => 'Who we are', 'scope' => 'Scope', 'collect' => 'Information we collect', 'sources' => 'Where it comes from',
    'use' => 'How we use it and our legal bases', 'verification' => 'Verification, moderation and automated checks', 'public' => 'What is published',
    'share' => 'Who we share it with', 'transfers' => 'International transfers', 'retention' => 'How long we keep it', 'security' => 'Security',
    'rights' => 'Your choices', 'regional' => 'Your rights by region', 'cookies' => 'Cookies and similar technologies', 'children' => 'Children',
    'third' => 'Third-party websites', 'changes' => 'Changes to this policy', 'contact' => 'Contact and complaints',
];
$meta = meta([
    'title' => 'Privacy Policy',
    'description' => 'How ImmigrationCRM.shop collects, uses, shares and protects personal information, how long we keep it, and how to exercise your privacy rights.',
    'path' => '/privacy',
    'jsonld' => [webpage_ld('/privacy', 'Privacy Policy', ['dateModified' => iso_date($effective)]), breadcrumb_ld($crumbs)],
]);
?>
<?= page_band($crumbs, 'Privacy Policy', 'How we collect, use, share and protect personal information — written to be read, not just agreed to.',
    '<p class="legal-meta"><span>Effective ' . e(format_date($effective)) . '</span><span>Applies to immigrationcrm.shop and its subdomains</span></p>') ?>
<div class="wrap">
  <div class="guide-layout">
    <article class="legal">
      <?php if (!setting('legal_entity_name')): ?>
      <div class="notice warn legal-draft"><p><strong>Draft for legal review.</strong> Highlighted items are filled in from Moderation → Settings → Legal.</p></div>
      <?php endif; ?>

      <section class="key-points" id="summary" aria-labelledby="summary-h">
        <h2 id="summary-h">Summary</h2>
        <ul>
          <li>We collect what we need to run a verified review platform: your work email, your reviews, and — only if you choose — your LinkedIn identity or a proof-of-use file.</li>
          <li>Reviews are anonymous by default. Your email address is never published or shared with software vendors.</li>
          <li>We don't sell or rent personal information, don't run advertising trackers, and don't use analytics cookies.</li>
          <li>Proof-of-use files are private, seen only by our moderators, and deleted automatically after 12 months.</li>
          <li>You can download your data or delete your account at any time from <a href="/account">My account</a>.</li>
        </ul>
      </section>

      <h2 id="who">1. Who we are</h2>
      <p>ImmigrationCRM.shop (“<strong>ImmigrationCRM.shop</strong>”, “<strong>we</strong>”, “<strong>us</strong>”) is an independent directory and verified-review platform for software used by immigration professionals. The website is operated by <?= $entity ?>, <?= legal('legal_entity_type', 'type of entity and registration details') ?>, with its registered address at <?= legal('legal_address', 'registered address') ?>.</p>
      <p>For the purposes of data-protection law, <?= $entity ?> is the <strong>controller</strong> (in India, the <strong>data fiduciary</strong>) of the personal information described in this policy. You can reach us about privacy at <a href="mailto:<?= $pe ?>"><?= $pe ?></a>.</p>

      <h2 id="scope">2. Scope</h2>
      <p>This policy applies to personal information we process when you:</p>
      <ul>
        <li>visit or browse immigrationcrm.shop, including product pages, comparisons and country guides;</li>
        <li>sign in, create an account, or verify your identity;</li>
        <li>write, edit or manage a review;</li>
        <li>list, claim or manage a software listing, reply to reviews, or buy featured placement as a vendor;</li>
        <li>contact us, report content, or make a privacy or legal request.</li>
      </ul>
      <p>It does not cover the websites or services of software vendors listed on our site, or of LinkedIn, Stripe or other third parties. Their own privacy policies apply when you use them.</p>

      <h2 id="collect">3. Information we collect</h2>
      <h3>3.1 Information you give us</h3>
      <div class="table-scroll"><table>
        <thead><tr><th>Category</th><th>What it includes</th><th>When</th></tr></thead>
        <tbody>
          <tr><td>Account and sign-in</td><td>Your work email address and its domain; the date your email was verified; whether the domain is a work or personal email provider. Sign-in codes are stored only in scrambled (hashed) form.</td><td>When you sign in</td></tr>
          <tr><td>Profile (optional)</td><td>Your name, job title and firm name.</td><td>If you add them to your account or review</td></tr>
          <tr><td>Review content</td><td>Ratings, headline, pros, cons, how your firm uses the software, your role, firm size, how long you've used it, whether you'd recommend it, and whether to show your name.</td><td>When you write a review</td></tr>
          <tr><td>Proof of use (optional)</td><td>A screenshot, invoice, receipt or similar file showing that you use the software. It may contain personal information about you or others — please hide client details before uploading.</td><td>If you choose this verification route</td></tr>
          <tr><td>Vendor information</td><td>Listing details (product name, website, description, features, pricing, logo link), claim requests and notes, and your public replies to reviews.</td><td>When you list or manage software</td></tr>
          <tr><td>Payment information</td><td>The listing, plan, amount, currency and payment status of featured placement purchases, and the Stripe Checkout reference. <strong>We never receive or store your full card number.</strong> Card details go directly to Stripe.</td><td>When you buy featured placement</td></tr>
          <tr><td>Communications</td><td>Messages you send us, reports about content, and data or legal requests, with our replies.</td><td>When you contact us</td></tr>
        </tbody>
      </table></div>

      <h3>3.2 Information from third parties (only if you choose)</h3>
      <ul>
        <li><strong>LinkedIn.</strong> If you connect LinkedIn using “Sign In with LinkedIn using OpenID Connect”, LinkedIn shares your LinkedIn member identifier, name and the email address on your LinkedIn account. We do not receive your connections, posts or messages, and we never post to LinkedIn.</li>
        <li><strong>Stripe.</strong> When you pay for featured placement, Stripe tells us whether the payment succeeded and gives us a payment reference.</li>
      </ul>

      <h3>3.3 Information collected automatically</h3>
      <ul>
        <li><strong>Session cookie.</strong> A single essential cookie keeps you signed in and protects forms against cross-site request forgery. See <a href="#cookies">section 14</a>.</li>
        <li><strong>Server logs.</strong> Like almost every website, our hosting provider records technical information when pages are requested: IP address, date and time, page requested, browser type (user agent), referring page and response status. These logs are used for security, troubleshooting and preventing abuse.</li>
        <li><strong>Security signals.</strong> We record the number of sign-in codes requested and failed attempts for an email address to prevent abuse.</li>
      </ul>
      <p>We do <strong>not</strong> use analytics tools, advertising pixels, fingerprinting, or third-party tracking scripts. Our fonts and icons are served from our own server, so loading a page does not send your information to font providers.</p>

      <h3>3.4 Information we do not want</h3>
      <p>Please do not include in reviews, proof files or messages any information about your clients or their immigration matters, government identification numbers, health information, or other sensitive information. If we find such information we will remove or redact it.</p>

      <h2 id="sources">4. Where information comes from</h2>
      <p>Most information comes directly from you. Some comes from LinkedIn or Stripe as described above. Information about software products (not about individuals) comes from vendors and from public sources such as vendors' own websites and independent software directories. Where a product listing names a company, that is business information rather than personal information.</p>

      <h2 id="use">5. How we use information and our legal bases</h2>
      <p>Where the EU or UK General Data Protection Regulation (GDPR) applies, we rely on the legal bases below. Under India's Digital Personal Data Protection Act, 2023 (DPDP Act), we process personal data for the specified purposes you agree to when you use the service, and for legitimate uses permitted by law.</p>
      <div class="table-scroll"><table>
        <thead><tr><th>Purpose</th><th>Information used</th><th>Legal basis (GDPR)</th></tr></thead>
        <tbody>
          <tr><td>Create and secure your account, send sign-in codes</td><td>Email, security signals, session cookie</td><td>Performance of a contract; legitimate interests in security</td></tr>
          <tr><td>Verify that reviewers are genuine practitioners</td><td>Email domain, LinkedIn identity, proof of use</td><td>Legitimate interests in a trustworthy platform; consent for LinkedIn connection</td></tr>
          <tr><td>Publish reviews and show verification badges</td><td>Review content; name and firm only if you opt in</td><td>Performance of a contract; consent for showing your name</td></tr>
          <tr><td>Detect fake, conflicted or duplicate reviews</td><td>Email domain, vendor domain, account and review history</td><td>Legitimate interests in preventing fraud and protecting readers</td></tr>
          <tr><td>Moderate content and handle reports, appeals and legal requests</td><td>Review content, proof files, communications</td><td>Legitimate interests; legal obligation</td></tr>
          <tr><td>Operate vendor listings, claims and replies</td><td>Vendor information, email domain</td><td>Performance of a contract</td></tr>
          <tr><td>Process featured-placement payments, keep financial records</td><td>Payment information</td><td>Performance of a contract; legal obligation (tax and accounting)</td></tr>
          <tr><td>Send service emails (codes, review status, claim decisions)</td><td>Email address</td><td>Performance of a contract</td></tr>
          <tr><td>Keep the site secure, fix problems, prevent abuse</td><td>Server logs, security signals</td><td>Legitimate interests in security and reliability</td></tr>
          <tr><td>Comply with law and enforce our Terms</td><td>Any relevant information</td><td>Legal obligation; legitimate interests in defending legal claims</td></tr>
        </tbody>
      </table></div>
      <p>We do not send marketing emails. If we ever offer newsletters, they will be opt-in only and every email will include an unsubscribe link.</p>
      <p>Where we rely on legitimate interests, we have balanced them against your rights. You can object at any time (see <a href="#rights">section 13</a>).</p>

      <h2 id="verification">6. Verification, moderation and automated checks</h2>
      <p>To keep reviews trustworthy, some checks happen automatically:</p>
      <ul>
        <li>Email addresses from personal providers (for example Gmail, Yahoo, Outlook, iCloud and disposable inboxes) cannot be used to write reviews or list software.</li>
        <li>If your email domain matches a product's verified domain, you cannot review that product, and you may be able to claim its listing.</li>
        <li>One review per person per product, and one LinkedIn profile per reviewer account.</li>
      </ul>
      <p>These checks decide only whether you can use a particular feature. Proof-of-use files are reviewed by a person, and any decision to reject or remove a review is made or confirmed by a moderator. We do not make decisions based solely on automated processing that produce legal or similarly significant effects on you. If you think a check got it wrong, contact us and a person will look at it.</p>

      <h2 id="public">7. What is published</h2>
      <ul>
        <li><strong>Always public with a review:</strong> ratings, headline, pros, cons, how your firm uses the software, your role, firm size, how long you've used it, whether you'd recommend it, the publication date, and which verification checks you passed (for example “Work email” and “LinkedIn”).</li>
        <li><strong>Public only if you tick “Show my name”:</strong> your name (or your LinkedIn name) and firm.</li>
        <li><strong>Never published:</strong> your email address, LinkedIn identifier or email, proof-of-use files, and sign-in information.</li>
      </ul>
      <p>Published reviews can be seen by anyone, indexed by search engines, included in our <a href="/feed.xml">RSS feed</a>, and read by AI assistants and answer engines that use our public pages, Markdown versions and <a href="/llms.txt">llms.txt</a> file. Once content is public, others may copy or cache it; we cannot control copies made by third parties, but we will remove content from our own site when required.</p>
      <p>Even without your name, details such as your role, firm size and how you use the software could allow people who know you to guess who wrote a review. Please consider this when writing.</p>

      <h2 id="share">8. Who we share information with</h2>
      <p>We do not sell personal information, and we do not share it for cross-context behavioural advertising. We share it only as follows:</p>
      <div class="table-scroll"><table>
        <thead><tr><th>Recipient</th><th>Why</th><th>What</th></tr></thead>
        <tbody>
          <tr><td>Hosting and email provider (Hostinger)</td><td>Hosts the website and database, and sends our emails</td><td>All information stored on the site; email addresses and email content</td></tr>
          <tr><td>LinkedIn</td><td>Only if you connect LinkedIn, to confirm your identity</td><td>The sign-in request you approve on LinkedIn</td></tr>
          <tr><td>Stripe</td><td>Payment processing for featured placement</td><td>Email address, purchase details; Stripe collects card details directly</td></tr>
          <tr><td>Search engines via IndexNow</td><td>To tell search engines a public page changed</td><td>Page addresses only — no personal information</td></tr>
          <tr><td>Professional advisers</td><td>Legal, accounting and audit advice</td><td>Information relevant to the advice, under confidentiality</td></tr>
          <tr><td>Authorities and courts</td><td>When required by law, court order or valid legal process, or to protect rights and safety</td><td>Only what the request lawfully requires</td></tr>
          <tr><td>A buyer or successor</td><td>If the business is sold, merged or reorganised</td><td>Information needed to continue the service, under this policy</td></tr>
        </tbody>
      </table></div>
      <p><strong>Software vendors</strong> do not receive reviewers' email addresses or identities. They see only what is published. If a vendor disputes a review, we may tell them whether the reviewer passed our verification checks, but not who the reviewer is, unless the law requires us to disclose it.</p>
      <p>Product logos may be loaded from the vendor's own web address. When your browser loads such an image, the vendor's server can see your IP address, as with any image on the web.</p>

      <h2 id="transfers">9. International transfers</h2>
      <p>Our website and database are hosted in <?= legal('hosting_location', 'hosting location') ?>. Our service providers, including Stripe and LinkedIn, may process information in other countries, including the United States. Where we transfer personal information out of the EU, UK, Switzerland or another country that restricts transfers, we rely on adequacy decisions, the European Commission's Standard Contractual Clauses (with the UK addendum where relevant), or other lawful safeguards, and we assess the protections offered by recipients. You can ask us for more information about these safeguards.</p>
      <p>Where Indian law applies, we will not transfer personal data to any country the Government of India has restricted.</p>

      <h2 id="retention">10. How long we keep information</h2>
      <div class="table-scroll"><table>
        <thead><tr><th>Information</th><th>How long</th></tr></thead>
        <tbody>
          <tr><td>Sign-in codes and attempt records</td><td>Deleted automatically within 24 hours</td></tr>
          <tr><td>Account and profile</td><td>Until you delete your account. We may delete accounts with no sign-in for 3 years, after emailing you first.</td></tr>
          <tr><td>Published reviews</td><td>While your account exists, unless you or we remove them. Deleting your account deletes your reviews.</td></tr>
          <tr><td>Proof-of-use files</td><td>Deleted automatically 12 months after the moderation decision, or immediately when you delete your account</td></tr>
          <tr><td>Rejected or removed reviews</td><td>Up to 12 months, to handle appeals and repeat abuse, then deleted</td></tr>
          <tr><td>Vendor listings</td><td>While the product is listed. Factual product information may remain after a vendor account is deleted.</td></tr>
          <tr><td>Payment records</td><td>As long as tax and accounting law requires — usually up to 8 years</td></tr>
          <tr><td>Server logs</td><td>For the limited period set by our hosting provider (typically up to 90 days), unless needed to investigate abuse</td></tr>
          <tr><td>Communications and legal requests</td><td>Up to 3 years after the matter is closed, or longer if a legal claim is possible</td></tr>
        </tbody>
      </table></div>
      <p>We may keep information longer where the law requires it or where it is needed to establish, exercise or defend legal claims. Backups are overwritten on a rolling basis.</p>

      <h2 id="security">11. Security</h2>
      <p>We protect personal information with measures appropriate to the risk, including:</p>
      <ul>
        <li>encrypted connections (HTTPS) for the whole site, and encrypted connections to our email server;</li>
        <li>passwordless sign-in with one-time codes that expire after 10 minutes, are stored only in hashed form, and are locked after repeated wrong attempts;</li>
        <li>protection against cross-site request forgery on every form, and secure, HTTP-only session cookies;</li>
        <li>proof-of-use files stored privately in the database, checked to be genuine images or PDFs, and viewable only by signed-in moderators;</li>
        <li>application code and configuration files blocked from public access, and database access limited to the application;</li>
        <li>access to moderation tools limited to named administrators.</li>
      </ul>
      <p>No system is completely secure. If a personal-data breach is likely to put you at risk, we will tell you and the relevant authorities as the law requires — under GDPR, the supervisory authority within 72 hours where required; under the DPDP Act, the Data Protection Board of India and affected individuals.</p>

      <h2 id="rights">12. Your choices</h2>
      <ul>
        <li><strong>Download your data:</strong> <a href="/account#your-data">My account → Your data</a> gives you a copy of your account, reviews and listings in a machine-readable file.</li>
        <li><strong>Delete your account:</strong> from the same page. This deletes your account, your reviews and any proof files straight away.</li>
        <li><strong>Edit your profile</strong> or hide your name on reviews at any time.</li>
        <li><strong>Disconnect LinkedIn:</strong> remove our app in your LinkedIn settings, and ask us to delete the LinkedIn details we hold.</li>
      </ul>

      <h2 id="regional">13. Your rights</h2>
      <p>Depending on where you live, you may have the rights below. We honour these requests from anyone, wherever the law does not require us to refuse. To make a request, use the self-service tools above or email <a href="mailto:<?= $pe ?>"><?= $pe ?></a>. We may need to confirm your identity — usually by asking you to reply from, or sign in with, the email address on your account. We respond within one month (or the shorter period your law requires), and requests are free unless they are clearly unfounded or excessive.</p>

      <h3>13.1 European Economic Area, United Kingdom and Switzerland</h3>
      <p>You have the right to access your personal data; correct it; have it erased; restrict or object to processing (including processing based on legitimate interests); receive it in a portable format; and withdraw consent at any time without affecting earlier processing. You can complain to your local data-protection authority — in the UK, the Information Commissioner's Office (ICO).</p>

      <h3>13.2 India</h3>
      <p>Under the Digital Personal Data Protection Act, 2023, you have the right to obtain a summary of the personal data we process and the processing activities; to correct, complete, update and erase your personal data; to withdraw consent as easily as you gave it; to grievance redressal; and to nominate another person to exercise your rights in the event of death or incapacity. If you are not satisfied with our response to a grievance, you may complain to the Data Protection Board of India.</p>
      <p><strong>Grievance Officer:</strong> <?= legal('grievance_officer_name', 'Grievance Officer name') ?>, <?= setting('grievance_officer_email') ? '<a href="mailto:' . e(setting('grievance_officer_email')) . '">' . e(setting('grievance_officer_email')) . '</a>' : legal('grievance_officer_email', 'Grievance Officer email') ?>, <?= legal('legal_address', 'registered address') ?>. We acknowledge grievances within 24 hours and aim to resolve them within 15 days.</p>

      <h3>13.3 Canada</h3>
      <p>Under the Personal Information Protection and Electronic Documents Act (PIPEDA) and applicable provincial laws, you can access and correct your personal information and withdraw consent, subject to legal and contractual limits. You can complain to the Office of the Privacy Commissioner of Canada, or to the provincial commissioner where provincial law applies (for example in Quebec, Alberta or British Columbia).</p>

      <h3>13.4 United States</h3>
      <p>If you live in a US state with a consumer privacy law (such as California, Colorado, Connecticut, Virginia or others), you may have the right to know what personal information we collect, use and disclose; to access, correct and delete it; to obtain a portable copy; and to opt out of the “sale” or “sharing” of personal information and of targeted advertising and profiling. <strong>We do not sell or share personal information</strong> as those laws define it, and we do not use it for targeted advertising. We will not discriminate against you for exercising your rights. You may use an authorised agent, and you may appeal our decision on a request by replying to our response; if we deny your appeal you can contact your state Attorney General. We honour Global Privacy Control signals as an opt-out, although we do not sell or share data in any case.</p>
      <p>Categories collected in the last 12 months: identifiers (email address, LinkedIn identifier), professional information (role, firm, firm size), commercial information (featured-placement purchases), internet activity (server logs), and content you create (reviews). Sources, purposes and recipients are described in sections 3, 5 and 8. We do not collect sensitive personal information for the purpose of inferring characteristics about you.</p>

      <h3>13.5 Australia and New Zealand</h3>
      <p>We handle personal information in line with the Australian Privacy Principles and the New Zealand Information Privacy Principles where they apply. You can request access and correction, and complain to the Office of the Australian Information Commissioner (OAIC) or the New Zealand Privacy Commissioner if you are not satisfied with our response.</p>

      <h2 id="cookies">14. Cookies and similar technologies</h2>
      <p>We use one strictly necessary cookie. Because it is essential to sign in and keep forms secure, it does not require consent under the EU and UK ePrivacy rules.</p>
      <div class="table-scroll"><table>
        <thead><tr><th>Name</th><th>Purpose</th><th>Type</th><th>Expires</th></tr></thead>
        <tbody>
          <tr><td><code>icrm_session</code></td><td>Keeps you signed in; protects forms against forgery; remembers temporary state during LinkedIn sign-in and short on-screen messages</td><td>First-party, strictly necessary, HTTP-only, secure</td><td>30 days, or when you sign out</td></tr>
        </tbody>
      </table></div>
      <p>The comparison tool runs entirely in your browser and does not store anything. Stripe sets its own cookies on its checkout pages, and LinkedIn on its sign-in pages; their policies apply there. If we ever add analytics or other non-essential cookies, we will update this policy and ask for your consent where required.</p>
      <p><strong>Do Not Track.</strong> Because we do not track you across sites, Do Not Track signals do not change how the site behaves.</p>

      <h2 id="children">15. Children</h2>
      <p>ImmigrationCRM.shop is a professional service for adults. You must be at least 18 to create an account. We do not knowingly collect personal information from anyone under 18. If you believe a child has given us personal information, contact us and we will delete it.</p>

      <h2 id="third">16. Third-party websites</h2>
      <p>Product pages link to vendors' websites, and some features rely on LinkedIn and Stripe. We are not responsible for the privacy practices of other websites. Please read their privacy policies before giving them personal information.</p>

      <h2 id="changes">17. Changes to this policy</h2>
      <p>We may update this policy as the service or the law changes. The effective date at the top shows when it last changed. If a change materially affects how we use personal information you have already given us, we will tell you by email or a prominent notice before it takes effect and, where required, ask for your consent.</p>

      <h2 id="contact">18. Contact and complaints</h2>
      <p>For privacy questions, requests or complaints, email <a href="mailto:<?= $pe ?>"><?= $pe ?></a> or write to <?= $entity ?>, <?= legal('legal_address', 'registered address') ?>. We will try to resolve any concern directly. You also have the right to complain to the data-protection authority where you live, as described in section 13.</p>
      <p class="small muted">See also: <a href="/terms">Terms of Service</a> · <a href="/how-we-verify">How we verify reviews</a></p>
    </article>

    <details class="toc legal-toc" open>
      <summary>Contents</summary>
      <ol class="toc-list"><?php $n = 0; foreach ($toc as $id => $label): ?><li><a href="#<?= $id ?>"><?php if ($id !== 'summary') { $n++; echo '<span class="toc-n">' . $n . '.</span> '; } ?><?= e($label) ?></a></li><?php endforeach; ?></ol>
    </details>
    <script>if (matchMedia('(max-width: 999px)').matches) document.currentScript.previousElementSibling.removeAttribute('open');</script>
  </div>
</div>
