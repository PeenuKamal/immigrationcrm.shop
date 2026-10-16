<?php
declare(strict_types=1);

/**
 * Country guides: "Best immigration CRM software in Canada" and similar pages.
 * Regulatory context is stable public information; product facts come only from listings and verified reviews.
 */
const COUNTRIES = [
    'canada' => [
        'name' => 'Canada', 'region' => 'Canada', 'code' => 'CA', 'class' => 'ca',
        'who' => 'RCICs and immigration lawyers',
        'audience_rx' => '/RCIC|consultant|immigration lawyer/i',
        'feature_rx' => '/IRCC|CRS|Express Entry|PNP|Canad/i',
        'context' => [
            'Immigration consultants in Canada must be licensed by the College of Immigration and Citizenship Consultants (CICC) to practise as Regulated Canadian Immigration Consultants (RCICs). Immigration lawyers are regulated by their provincial or territorial law society.',
            'Applications go to Immigration, Refugees and Citizenship Canada (IRCC), mostly online, using forms such as IMM 5257 (visitor visa), IMM 1294 (study permit), IMM 1295 (work permit) and IMM 5476 (use of a representative). Common programs include Express Entry, Provincial Nominee Programs (PNP), family sponsorship, and study and work permits.',
        ],
        'look_for' => [
            'IRCC form generation or autofill from client intake data',
            'Program-specific document checklists (Express Entry, PNP, study and work permits, sponsorship)',
            'CRS score tracking for Express Entry clients',
            'Written service agreements and client-account records that support your CICC or law-society obligations',
            'A secure client portal for document collection, in English and French where needed',
            'Data storage and privacy practices that meet Canadian privacy law (PIPEDA)',
        ],
    ],
    'usa' => [
        'name' => 'the United States', 'short' => 'USA', 'in' => 'the USA', 'region' => 'USA', 'code' => 'US', 'class' => 'us',
        'who' => 'immigration attorneys and law firms',
        'audience_rx' => '/law firm|attorney|lawyer|corporate/i',
        'feature_rx' => '/USCIS|petition|H-1B|EB-|RFE|forms|I-9|E-Verify/i',
        'context' => [
            'In the United States, immigration legal services are provided by licensed attorneys and by accredited representatives working for organizations recognized by the Department of Justice (EOIR Recognition and Accreditation program).',
            'Filings go mainly to U.S. Citizenship and Immigration Services (USCIS), with court matters before the Executive Office for Immigration Review (EOIR). Common forms include I-130, I-129, I-140, I-485, I-765 and G-28. Employment-based work (H-1B, L-1, O-1, EB-1, EB-2 NIW) and family-based cases dominate most practices.',
        ],
        'look_for' => [
            'A current USCIS forms library that fills forms from one client questionnaire',
            'Case status tracking by USCIS receipt number, with deadline and RFE reminders',
            'AI or template-based drafting for petitions, support letters and RFE responses',
            'Client questionnaires in the languages your clients speak',
            'Billing and trust-account (IOLTA) handling, or an integration with your accounting tool',
            'For employer clients: I-9, E-Verify and visa-expiry tracking',
        ],
    ],
    'uk' => [
        'name' => 'the United Kingdom', 'short' => 'UK', 'in' => 'the UK', 'region' => 'UK', 'code' => 'UK', 'class' => 'uk',
        'who' => 'immigration advisers and solicitors',
        'audience_rx' => '/law firm|lawyer|solicitor|consultant|advis/i',
        'feature_rx' => '/sponsor|UK|Home Office|compliance/i',
        'context' => [
            'In the UK, immigration advice must be given by a qualified person: solicitors are regulated by the Solicitors Regulation Authority (SRA), and other immigration advisers by the Immigration Advice Authority (formerly the OISC).',
            'Applications go to UK Visas and Immigration (UKVI), part of the Home Office. Many firms also manage sponsor licences for employers under the Skilled Worker route, which brings its own record-keeping and reporting duties.',
        ],
        'look_for' => [
            'Matter management with visa-expiry and leave-to-remain deadline tracking',
            'Sponsor licence and Certificate of Sponsorship (CoS) tracking for employer clients',
            'Document checklists per route (Skilled Worker, family, settlement, citizenship)',
            'UK GDPR-compliant data storage and access controls',
            'Time recording and billing for private and fixed-fee work',
        ],
    ],
    'australia' => [
        'name' => 'Australia', 'region' => 'Australia', 'code' => 'AU', 'class' => 'au',
        'who' => 'registered migration agents',
        'audience_rx' => '/migration agent|consultant|lawyer/i',
        'feature_rx' => '/MARA|Home Affairs|migration|Australia|templates/i',
        'context' => [
            'Migration agents in Australia must be registered with the Office of the Migration Agents Registration Authority (OMARA) and follow its Code of Conduct, which covers written service agreements, fees and client records. Australian legal practitioners can also give immigration assistance.',
            'Applications are lodged with the Department of Home Affairs, mostly through ImmiAccount. Common visas include skilled (subclasses 189, 190, 491), employer-sponsored (subclass 482 and 186), student (subclass 500) and partner visas.',
        ],
        'look_for' => [
            'Visa-subclass templates and checklists for skilled, employer, student and partner visas',
            'Client data collection that matches what ImmiAccount asks for',
            'Service agreements and client-account records that support the OMARA Code of Conduct',
            'Bridging-visa and visa-expiry deadline tracking',
            'If you also place students: education-agent features such as course applications and commissions',
        ],
    ],
    'india' => [
        'name' => 'India', 'region' => 'India', 'code' => 'IN', 'class' => 'in',
        'who' => 'visa and immigration consultancies',
        'audience_rx' => '/consultant|education|visa|agenc/i',
        'feature_rx' => '/lead|multi-branch|sub-agent|IELTS|PTE|mobile|invoic/i',
        'context' => [
            'Most visa and immigration consultancies in India help clients apply to other countries — commonly Canada, Australia, the UK, the USA and Europe — for study, work, visitor and permanent-residence visas.',
            'There is no single licensing body for visa consultants in India. Agencies that recruit workers for jobs abroad need a recruiting-agent registration under the Emigration Act, 1983. Firms advising on another country\'s immigration law should check that country\'s rules — for example, only licensed RCICs or lawyers can be paid to represent clients for Canadian applications.',
        ],
        'look_for' => [
            'Lead capture from Facebook, Google and WhatsApp, with automatic follow-ups',
            'Multi-branch and counsellor assignment with performance reports',
            'Sub-agent and commission management',
            'IELTS / PTE coaching and student application tracking if you run classes',
            'Invoicing with GST and payment tracking',
            'A mobile app for counsellors in the field',
        ],
    ],
];

function country(string $slug): ?array
{
    $c = COUNTRIES[$slug] ?? null;
    if ($c) { $c['slug'] = $slug; $c['short'] ??= $c['name']; $c['in'] ??= $c['short']; }
    return $c;
}

/** "the United States" -> "The United States" for sentence starts and titles. */
function country_title(array $c): string
{
    return ucfirst($c['name']);
}

/**
 * Ranks published software for a country (optionally within one category).
 * Score = how well the listing fits the market + verified-review strength. Featured placement is ignored.
 */
function rank_for_country(array $c, ?string $category = null): array
{
    $rows = search_software(['category' => $category, 'sort' => 'name']);
    $out = [];
    foreach ($rows as $s) {
        $regions = json_list($s['regions']);
        $local = in_array($c['region'], $regions, true);
        $global = in_array('Global', $regions, true);
        if (!$local && !$global) continue;
        $aud = json_list($s['audiences']);
        $feat = json_list($s['features']);
        $why = [];
        $fit = $local ? 3 : 1;
        $why[] = $local ? 'Serves ' . $c['short'] : 'Serves clients globally';
        $audMatch = array_values(preg_grep($c['audience_rx'], $aud));
        if ($audMatch) { $fit += 2; $why[] = 'Built for ' . lower_words($audMatch[0]); }
        $featMatch = array_values(preg_grep($c['feature_rx'], $feat));
        $fit += min(count($featMatch), 3);
        foreach (array_slice($featMatch, 0, 3) as $fm) $why[] = $fm;
        if (preg_grep('/portal/i', $feat) && !preg_grep('/portal/i', $featMatch)) { $fit += 0.5; $why[] = 'Client portal'; }
        $n = (int)$s['review_count'];
        $avg = (float)$s['rating_avg'];
        $bayes = $n ? ($n * $avg + 3 * 3.5) / ($n + 3) : 0;
        $score = $fit + ($n ? ($bayes - 3) * 2 + min($n, 10) * 0.3 : 0);
        $miss = $n ? null : 'No verified reviews yet';
        $out[] = ['s' => $s, 'score' => $score, 'fit' => $fit, 'why' => $why, 'miss' => $miss, 'reviews' => $n, 'avg' => $avg, 'local' => $local];
    }
    usort($out, fn($a, $b) => [$b['score'], $b['reviews'], $a['s']['name']] <=> [$a['score'], $a['reviews'], $b['s']['name']]);
    return $out;
}

/** Lowercases only a plain leading word ("Serves Canada" -> "serves Canada", "IRCC forms" unchanged). */
function lc_lead(string $s): string
{
    return preg_replace_callback('/^([A-Z][a-z]+)\b/', fn($m) => strtolower($m[1]), $s);
}

function ranked_reason(array $r): string
{
    $parts = array_slice($r['why'], 0, 3);
    $txt = implode(', ', array_map('lc_lead', $parts));
    if ($r['reviews']) $txt .= sprintf('; rated %.1f/5 from %d verified review%s', $r['avg'], $r['reviews'], $r['reviews'] > 1 ? 's' : '');
    return ucfirst($txt);
}

/** Categories that have at least one tool for this country. */
function country_categories(array $c): array
{
    $out = [];
    foreach (categories_with_counts() as $cat) {
        $ranked = rank_for_country($c, $cat['slug']);
        if ($ranked) $out[] = ['cat' => $cat, 'ranked' => $ranked];
    }
    return $out;
}

function guide_path(string $country, ?string $category = null): string
{
    return $category ? "/best/$category/$country" : "/best/$country";
}

const METHODOLOGY = 'Tools are ranked by how closely their listed markets, audiences and features match this country\'s immigration practice, plus the strength of their verified reviews (average rating weighted by number of reviews). Featured placement never affects these rankings. Until a tool has verified reviews, its position is based on fit alone.';

const CATEGORY_LOOK_FOR = [
    'immigration-crm' => ['Lead capture from your website, ads and messaging apps, with automatic follow-ups', 'Assessment and intake forms that feed straight into case files', 'Retainer or service-agreement generation and e-signature', 'A visual pipeline from enquiry to visa decision'],
    'immigration-case-management' => ['A government forms library that stays current when forms change', 'Deadline, expiry and status tracking for every case', 'Evidence and document collection through a client portal', 'Reporting across caseloads and staff'],
    'law-practice-management' => ['Matter management with immigration-friendly custom fields', 'Time, billing and trust-account handling', 'Integrations with a dedicated immigration forms or AI drafting tool', 'Client portal and secure messaging'],
    'ai-immigration' => ['Clear data-security terms, including whether your client data trains the model', 'Drafts grounded in current law and policy, with sources you can check', 'Coverage of the visa types you actually file', 'Easy review and editing by a licensed professional before anything is filed'],
    'corporate-immigration' => ['A dashboard of every employee\'s status and expiry dates', 'Integrations with your HR system', 'Cost tracking and forecasting', 'Collaboration between HR, outside counsel and employees'],
    'general-crm' => ['How much setup it takes to model immigration stages and documents', 'Email, WhatsApp and phone integration', 'Automation for follow-ups and reminders', 'Whether you will need a separate tool for government forms'],
];

/** Everything a category × country guide needs, shared by the page and its Markdown version. */
function guide_data(array $c, array $cat): array
{
    $ranked = rank_for_country($c, $cat['slug']);
    $seo = category_seo($cat['slug'], $cat['name']);
    $seoLower = category_seo_lower($cat['slug'], $cat['name']);
    $where = $c['name'];
    $top = array_slice($ranked, 0, 5);
    $topNames = array_map(fn($r) => $r['s']['name'], array_slice($ranked, 0, 3));
    $answer = $top
        ? 'Based on ImmigrationCRM.shop\'s rankings for ' . $c['who'] . ' in ' . $where . ', the best ' . $seoLower . ' in ' . year() . ' are: '
            . implode('; ', array_map(fn($r, $i) => ($i + 1) . '. ' . $r['s']['name'] . ' — ' . lc_lead(ranked_reason($r)), $top, array_keys($top))) . '.'
        : '';
    $portal = array_map(fn($r) => $r['s']['name'], array_filter($ranked, fn($r) => (bool)preg_grep('/portal/i', json_list($r['s']['features']))));
    $marketFeature = array_map(fn($r) => $r['s']['name'], array_filter($ranked, fn($r) => (bool)preg_grep($c['feature_rx'], json_list($r['s']['features']))));
    $whoTools = array_map(fn($r) => $r['s']['name'], array_filter($ranked, fn($r) => (bool)preg_grep($c['audience_rx'], json_list($r['s']['audiences']))));
    $priced = array_values(array_filter($ranked, fn($r) => $r['s']['pricing_note']));
    $faqs = [];
    if ($top) $faqs[] = ["What is the best $seoLower in {$c['in']}?", $answer . ' The best choice depends on your practice size, the programs you file and whether you need sales (lead) features as well as case management.'];
    if ($whoTools) $faqs[] = ['Which tools are built specifically for ' . $c['who'] . '?', join_names(array_slice($whoTools, 0, 6)) . (count($whoTools) === 1 ? ' lists ' : ' list ') . $c['who'] . ' (or a close equivalent) as a core audience.'];
    if ($marketFeature) {
        $mf = [];
        foreach ($ranked as $r) foreach (preg_grep($c['feature_rx'], json_list($r['s']['features'])) as $f) $mf[$f] = true;
        $label = $c['slug'] === 'canada' ? 'IRCC and Canada-specific' : 'country-specific';
        $faqs[] = ["Which $seoLower has $label features?", join_names(array_slice($marketFeature, 0, 6)) . (count($marketFeature) === 1 ? ' lists' : ' list') . " features specific to immigration work in $where, such as " . lower_words(join_names(array_slice(array_keys($mf), 0, 4))) . '.'];
    }
    if ($portal) $faqs[] = ['Which options include a client portal?', join_names(array_slice($portal, 0, 6)) . (count($portal) === 1 ? ' lists' : ' list') . ' a client portal for document collection and updates.'];
    $faqs[] = ['How much does ' . $seoLower . ' cost?', $priced
        ? 'Published pricing on ImmigrationCRM.shop: ' . implode('; ', array_map(fn($r) => $r['s']['name'] . ': ' . rtrim($r['s']['pricing_note'], '.'), array_slice($priced, 0, 4))) . '. Most other vendors quote on request, usually per user per month.'
        : 'Most vendors in this category quote on request, usually per user per month, with discounts for annual billing. Ask for a trial with your own sample case before committing.'];
    $faqs[] = ['How does ImmigrationCRM.shop rank these tools?', METHODOLOGY];
    $faqs[] = ['Are the reviews verified?', 'Yes. Every reviewer confirms a work email and then either connects LinkedIn or uploads proof of use that a moderator checks. Vendor staff cannot review their own products.'];
    return compact('ranked', 'seo', 'seoLower', 'top', 'topNames', 'answer', 'faqs');
}
