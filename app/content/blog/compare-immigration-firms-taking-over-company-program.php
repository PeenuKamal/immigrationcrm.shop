<?php
return [
    'title' => 'Took over your company\'s immigration program? How to compare immigration firms',
    'dek' => 'A step-by-step way to audit what you inherited, compare firms and providers fairly, and switch without missing a deadline.',
    'question' => 'How do I compare immigration firms when taking over an existing company immigration program?',
    'answer' => 'Start by auditing what you inherited (open cases, deadlines, visa types, costs and who owns the data), then compare firms on the same written brief: case types and countries covered, a fee schedule per case type, technology and reporting, service levels, compliance support and a transition plan. Score every firm against the same weighted criteria, check references from companies like yours, and only give notice once you hold a complete export of your case data.',
    'published' => '2026-10-26',
    'updated' => '2026-10-26',
    'category' => 'Corporate immigration',
    'placements' => ['alternatives:envoy-global', 'software:envoy-global', 'category:corporate-immigration', 'software:inszoom', 'software:lawlogix', 'software:immigrationtracker'],
    'software' => ['envoy-global', 'inszoom', 'lawlogix', 'immigrationtracker'],
    'blocks' => [
        ['p', 'Inheriting an immigration program usually means inheriting a provider you did not choose, cases already in flight and deadlines you cannot see yet. The safest order is: **audit, define, compare, then transition** — never the other way round.'],

        ['h2', 'Step 1: Audit what you inherited', 'audit'],
        ['p', 'Ask your current provider for a full case report in a spreadsheet. You are entitled to your company\'s data; get it before any conversation about switching.'],
        ['ul', [
            '**Headcount by status:** H-1B, L-1, O-1, TN, E-3, F-1 OPT/STEM OPT, green cards in progress (PERM, I-140, I-485) and employees outside the US.',
            '**Every deadline in the next 12 months:** status expirations, H-1B extensions and amendments, OPT end dates, PERM recruitment windows, I-9 reverifications.',
            '**Priority dates and green card stage** for each sponsored employee.',
            '**Costs for the last 12–24 months**, split into legal fees, government fees and premium processing.',
            '**Compliance files:** LCA public access files, I-9 records and any audits or site visits.',
            '**Policies:** who qualifies for sponsorship, who pays for what, and repayment agreements.',
        ]],

        ['h2', 'Step 2: Decide what kind of provider you need', 'provider-types'],
        ['table', ['Provider type', 'Examples', 'Choose it when'], [
            ['Global corporate immigration firm', 'Fragomen, BAL', 'You move people across many countries and want one firm with its own technology'],
            ['Legal service plus platform', '[Envoy Global](/software/envoy-global)', 'You want attorneys and an employer dashboard in one contract'],
            ['Tech-enabled firm', 'Alma, Manifest Law', 'You are a startup or growth company and want flat fees and a modern dashboard'],
            ['Employment firm with an immigration practice', 'Ogletree Deakins', 'You want immigration and employment counsel together'],
            ['Visa sponsorship through an employer of record', 'Deel Immigration', 'You hire abroad through an EOR rather than your own entity'],
            ['In-house team plus software', '[INSZoom](/software/inszoom), [LawLogix](/software/lawlogix), [ImmigrationTracker](/software/immigrationtracker)', 'You have in-house counsel and use outside firms only for overflow'],
        ]],

        ['h2', 'Step 3: Compare firms on the same brief', 'compare'],
        ['p', 'Send every firm the same request for proposal with your anonymised case mix and volumes, and score their answers on these criteria. Agree the weights with HR, legal and finance first.'],
        ['table', ['Criterion', 'What to ask', 'Suggested weight'], [
            ['Coverage', 'Which of our case types and countries do you handle in-house, and which through partners?', '20%'],
            ['Fees', 'A fixed fee per case type, what is included, and what triggers extra charges (RFEs, amendments, premium processing)', '20%'],
            ['Quality', 'RFE and denial rates for our case types, attorney-to-case ratio, who signs filings', '20%'],
            ['Technology and reporting', 'Employer dashboard, HRIS integration, deadline alerts, exportable data, reporting by cost centre', '15%'],
            ['Service levels', 'Response times, named attorney and paralegal, escalation path, employee experience', '10%'],
            ['Compliance', 'I-9 and LCA support, audit help, policy updates', '10%'],
            ['Transition', 'How they take over open cases, timeline, any transfer fees', '5%'],
        ]],
        ['p', 'Then **check references** from two companies of your size and industry, ideally ones that switched to the firm rather than started with it.'],

        ['h2', 'Step 4: Plan the transition before you give notice', 'transition'],
        ['ol', [
            'Get a complete export: case data, documents, receipt notices, approval notices and correspondence.',
            'Map every open case and deadline to the new firm, starting with anything due in the next 90 days.',
            'Have the new firm file new G-28 notices of appearance for pending USCIS cases so notices reach them.',
            'Tell affected employees who their new contact is and what will not change.',
            'Keep the old provider engaged for any filing due during the handover window.',
            'Review the program after 90 days against the scores you used to choose the firm.',
        ]],

        ['h2', 'Software for in-house immigration teams', 'software'],
        ['p', 'If you plan to manage more of the program yourself, compare corporate immigration case management tools side by side: [INSZoom vs LawLogix](/compare/inszoom-vs-lawlogix), or browse [all corporate immigration software](/category/corporate-immigration).'],
    ],
    'faqs' => [
        ['How do I compare immigration firms for taking over an existing program?', 'Audit what you inherited first (open cases, deadlines, priority dates, costs and data ownership). Then send every firm the same written brief and score them on coverage, a fee schedule per case type, quality (RFE and denial rates), technology and reporting, service levels, compliance support and a transition plan. Check references from similar companies and secure a full data export before giving notice.'],
        ['What should I ask an immigration firm in a request for proposal?', 'Which case types and countries they handle in-house, a fixed fee per case type and what triggers extra charges, RFE and denial rates for your case types, who signs filings, their employer dashboard and HRIS integrations, response-time commitments, I-9 and LCA compliance support, and how they would take over your open cases.'],
        ['Can we switch immigration firms in the middle of pending cases?', 'Yes. The new firm files new G-28 notices of appearance for pending USCIS cases, and you should transfer the full case file. Map every deadline in the next 90 days first and keep the outgoing firm engaged for filings due during the handover.'],
    ],
    'sources' => [
        ['Ogletree Deakins — Immigration practice', 'https://ogletree.com/practices/immigration/'],
        ['Envoy Global — site summary', 'https://www.envoyglobal.com/llms.txt'],
        ['Deel — Deel launches immigration', 'https://deel.com/blog/deel-launches-immigration'],
        ['Manifest Law — Manifest vs Fragomen vs BAL', 'https://manifestlaw.com/blog/manifest-vs-fragomen-vs-bal'],
    ],
];
