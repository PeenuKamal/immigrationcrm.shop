<?php
/*
 * Expert answers shown on existing pages. Keys:
 *   compare:{canonical slugs}   /compare/{…}
 *   alternatives:{slug}         /software/{slug}/alternatives
 *   best                        /best
 *   country:{slug}              /best/{country}
 * Facts are from vendor documentation and independent review sites, checked on the date in 'updated'.
 * Vendor-published claims are labelled as such.
 */

$mycaseSrc = [
    ['MyCase — Client communications', 'https://www.mycase.com/products/client-communications/'],
    ['MyCase — Client communication tools', 'https://www.mycase.com/features/client-communication-tools'],
    ['MyCase pricing, plans and add-ons (Carly, 2026)', 'https://www.usecarly.com/blog/mycase-pricing/'],
];
$centerbaseSrc = [
    ['Centerbase — Powering the growth of law firms (brochure)', 'https://www.centerbase.com/brochures/centerbase-powering-the-growth-of-law-firms'],
    ['Centerbase Help Center — Client Portal', 'https://support.centerbase.com/hc/en-us/articles/360044000672'],
    ['Capterra — Centerbase vs MyCase', 'https://capterra.com/compare/75540-115613/Centerbase-vs-MyCase'],
];
$docketwiseSrc = [
    ['Docketwise — information for AI assistants', 'https://www.docketwise.com/llm-info/'],
    ['Docketwise — Immigration case tracking', 'https://www.docketwise.com/features/immigration-case-tracking/'],
    ['Docketwise Support — I-131F e-filing (beta)', 'https://supportcenter.docketwise.com/en/articles/9792716-i-131f-e-filing'],
    ['8am — Best immigration case management software (publisher owns Docketwise)', 'https://www.8am.com/blog/best-immigration-case-management-software/'],
];

$mcFaqComm = 'MyCase is better for legal client communication. Every plan includes a client portal with messages, documents and invoices; Pro and above add two-way texting from a dedicated firm number, unlimited e-signature and intake forms, plus message templates and automatic text reminders. Centerbase\'s client portal shares documents, calendar activities and bills, and is strongest as part of its billing and accounting system rather than as a messaging tool.';
$mcFaqClient = 'MyCase is better for legal client management for most firms: intake forms and a legal CRM (Pro plan and above), one-click client portals, texting synced to the case file and online payments in one place. Centerbase is the better choice only if you need heavily customised client and matter records or multi-payor matters.';
$mcFaqMoney = 'Centerbase is better for law firm financial management. Firm accounting, trust and retainer accounting, electronic pre-bills, multi-payor matters and profitability reporting are built in. MyCase includes trust accounting and online payments, but full firm-wide financials and three-way trust reconciliation need the MyCase Accounting add-on.';
$mcFaqPm = 'For most small and mid-sized firms, MyCase is the better legal practice management system: it is easier to use, rated higher by users, publishes its pricing and covers cases, calendars, documents, communication, billing and payments. Choose Centerbase if accounting, profitability reporting and deep customisation matter more to you than ease of use.';

return [

    /* ---------------- MyCase vs Centerbase ---------------- */
    'compare:centerbase-vs-mycase' => [
        'id' => 'mycase-centerbase',
        'meta_title' => 'MyCase vs Centerbase (2026): client communication, client management, finances',
        'meta_description' => 'MyCase vs Centerbase compared for client communication, client management, financial management and practice management, with a definitive answer and pros and cons for each.',
        'summary' => 'MyCase wins for client communication, client management and overall practice management; Centerbase wins for financial management.',
        'title' => 'MyCase vs Centerbase: which is better?',
        'intro' => 'Four questions practitioners ask us most about these two legal practice management systems, each with a direct answer and the pros and cons that decide it. Neither product has verified reviews on ImmigrationCRM.shop yet, so this analysis uses vendor documentation and independent review sites.',
        'updated' => '2026-10-26',
        'sections' => [
            [
                'id' => 'client-communication', 'nav' => 'Client communication',
                'heading' => 'Which is better for legal client communication, MyCase or Centerbase?',
                'blocks' => [
                    ['verdict', 'Answer: MyCase', 'MyCase is better for client communication. It puts portal messaging, two-way texting from a dedicated firm number, message templates, automatic reminders and e-signature in one place. Centerbase\'s portal is built for sharing documents, calendar activities and bills rather than for conversations.'],
                    ['proscons', [
                        'MyCase' => [
                            'pros' => ['Client portal on every plan; portals can be set up in one click when you add a client', 'Two-way texting from a dedicated firm number, with texts saved to the case file (Pro and Advanced)', 'Message templates that auto-fill client and case details for routine updates', 'Automatic text reminders for invoices, events and appointments', 'Notifications when a client sends a message, uploads a document or completes a task', 'Unlimited e-signature on Pro and Advanced'],
                            'cons' => ['Texting, e-signature and intake forms need the Pro plan ($100/user/month billed annually) or higher', 'Basic plan clients get the portal but not texting', 'No WhatsApp channel, which matters for firms with many international clients'],
                        ],
                        'Centerbase' => [
                            'pros' => ['Client portal shares documents, calendar activities and bills, and clients can upload files straight into the matter', 'Firms control exactly what each client sees, and bills can be shared automatically when posted', 'Clients can download calendar events and search everything shared with them'],
                            'cons' => ['The portal documentation describes no client messaging or texting', 'Clients with several matters see one matter at a time', 'Lower user scores for customer service on Capterra (3.8/5 vs 4.6/5 for MyCase)'],
                        ],
                    ]],
                    ['note', 'Adding an immigration-specific option? See the [three-way comparison with ImmiPractice](/compare/centerbase-vs-immipractice-vs-mycase).'],
                ],
            ],
            [
                'id' => 'client-management', 'nav' => 'Client management',
                'heading' => 'Which is better for legal client management, MyCase or Centerbase?',
                'blocks' => [
                    ['verdict', 'Answer: MyCase for most firms', 'If you want legal practice or case management software with strong client management, choose MyCase. Intake forms, a legal CRM for leads, client portals, texting and payments sit in one easy system. Centerbase wins only when you need highly customised client and matter records or matters billed to several payors.'],
                    ['proscons', [
                        'MyCase' => [
                            'pros' => ['Client intake forms and a legal CRM for tracking leads (Pro and Advanced)', 'One record per client with communication, documents, invoices and payments', 'Conflict-check tracking on the Advanced plan', 'Higher ease-of-use score on Capterra (4.6/5 vs 4.3/5)', 'Published pricing from $50/user/month (annual billing)'],
                            'cons' => ['CRM and intake are not on the Basic plan', 'Less room to reshape client and matter records than Centerbase', 'No immigration-specific forms; immigration firms usually add a tool such as [Docketwise](/software/docketwise), which integrates with MyCase'],
                        ],
                        'Centerbase' => [
                            'pros' => ['CRM and client intake to onboard new clients quickly', 'Multi-payor matter management for clients whose fees are split or paid by a third party (for example an employer)', 'Automated workflows for repetitive onboarding tasks', 'Centerbase IQ answers plain-language questions about your firm\'s data'],
                            'cons' => ['Pricing is on request only', 'Fewer listed features overall on Capterra (33 of 50 vs 46 of 50 for MyCase)', 'Lower document management score on Capterra (3.0/5 vs 4.2/5)'],
                        ],
                    ]],
                ],
            ],
            [
                'id' => 'financial-management', 'nav' => 'Financial management',
                'heading' => 'Which is better for law firm financial management, MyCase or Centerbase?',
                'blocks' => [
                    ['verdict', 'Answer: Centerbase', 'Centerbase is the better choice for financial management. Firm accounting, trust and retainer accounting, electronic pre-bill approval, multi-payor billing, a live bank feed and profitability reporting are all built in. MyCase handles billing, payments and trust well, but full firm-wide financials need a paid add-on.'],
                    ['proscons', [
                        'Centerbase' => [
                            'pros' => ['Built-in firm accounting plus trust and retainer accounting', 'ePre-bill: review and approve pre-bills electronically before invoices go out', 'Multi-payor matters and flexible billing arrangements', 'Online banking with a live feed for reconciliation', 'Profitability and advanced reporting', 'Highest accounting score of the two on Capterra (4.8/5 vs 3.6/5)'],
                            'cons' => ['No published pricing, so total cost is hard to compare', 'Lower billing and invoicing score on Capterra (4.0/5 vs 4.5/5)', 'More setup than MyCase'],
                        ],
                        'MyCase' => [
                            'pros' => ['Trust accounting included on every plan', 'Online payments with next-day deposits through LawPay', 'Higher billing and expense-tracking scores on Capterra (4.5/5 each)', 'Split billing on the Advanced plan'],
                            'cons' => ['Three-way trust reconciliation and firm-wide financial reporting need MyCase Accounting ($39/user/month extra)', 'Lower accounting score on Capterra (3.6/5)', 'No pre-bill approval workflow comparable to Centerbase\'s ePre-bill'],
                        ],
                    ]],
                ],
            ],
            [
                'id' => 'practice-management', 'nav' => 'Practice management',
                'heading' => 'Which is better for legal practice management, MyCase or Centerbase?',
                'blocks' => [
                    ['verdict', 'Answer: MyCase for most firms; Centerbase for finance-led mid-sized firms', 'MyCase is the better all-round practice management system for solo, small and most mid-sized firms: it is easier to learn, rated higher by users and covers cases, calendars, documents, communication, billing and AI tools at a published price. Choose Centerbase if your firm runs on accounting, pre-bill review and profitability reporting and has the time to configure it.'],
                    ['table', ['', 'MyCase', 'Centerbase'], [
                        ['Pricing', '$50 / $100 / $130 per user/month (annual)', 'On request'],
                        ['Capterra rating', '4.6/5 (807 reviews)', '4.4/5 (61 reviews)'],
                        ['Ease of use (Capterra)', '4.6', '4.3'],
                        ['Customer service (Capterra)', '4.6', '3.8'],
                        ['Accounting', 'Trust included; full accounting is an add-on', 'Firm and trust accounting built in'],
                        ['Client communication', 'Portal, texting, templates, e-signature', 'Portal for documents, events and bills'],
                        ['AI', 'Writing and document assistants (Pro); case and discovery assistants (Advanced)', 'Centerbase IQ for firm-data questions'],
                    ]],
                    ['proscons', [
                        'MyCase' => [
                            'pros' => ['All-in-one: cases, calendar, documents, communication, billing and payments', 'Automated workflows and 70+ integrations (Pro)', 'Document automation and an open API (Advanced)', 'Strong user ratings across ease of use, value and support'],
                            'cons' => ['Advanced features are spread across three tiers and add-ons', 'Less customisable than Centerbase', 'No built-in immigration forms'],
                        ],
                        'Centerbase' => [
                            'pros' => ['Deep accounting and financial reporting in the same system', 'Automated workflows and highly configurable records', 'Positioned for growing and mid-sized firms'],
                            'cons' => ['Pricing on request', 'Steeper learning curve and lower support scores', 'Smaller user base and fewer listed features on Capterra'],
                        ],
                    ]],
                    ['note', 'Ratings are from Capterra\'s comparison page and prices from MyCase\'s published plans as reported in October 2026. Verified practitioner reviews on this page will replace these figures as they come in.'],
                ],
            ],
        ],
        'faqs' => [
            ['Which is better for legal client communication, MyCase or Centerbase?', $mcFaqComm],
            ['Which is better for legal client management, MyCase or Centerbase?', $mcFaqClient],
            ['Which has stronger client management features for a legal case management system, MyCase or Centerbase?', $mcFaqClient],
            ['Which is better for law firm financial management, MyCase or Centerbase?', $mcFaqMoney],
            ['Which is better for legal practice management, MyCase or Centerbase?', $mcFaqPm],
        ],
        'related' => ['best-immigration-practice-management-software-20-person-firm'],
        'sources' => array_merge($mycaseSrc, $centerbaseSrc),
    ],

    /* ---------------- MyCase vs Centerbase vs ImmiPractice ---------------- */
    'compare:centerbase-vs-immipractice-vs-mycase' => [
        'id' => 'comm-three',
        'meta_title' => 'MyCase vs Centerbase vs ImmiPractice: client communication compared (2026)',
        'meta_description' => "Which is better for legal client communication: MyCase, Centerbase or ImmiPractice? A definitive answer with pros and cons of each product's client communication features.",
        'summary' => 'MyCase for general law firms; ImmiPractice for Canadian immigration practices that talk to clients on WhatsApp; Centerbase third for communication.',
        'title' => 'Client communication: MyCase vs Centerbase vs ImmiPractice',
        'intro' => 'MyCase and Centerbase are general legal practice management systems. ImmiPractice is a Canadian immigration CRM. Here is how their client communication compares, and which to choose.',
        'updated' => '2026-10-26',
        'sections' => [[
            'id' => 'client-communication-3',
            'heading' => 'Which is better for legal client communication: MyCase, Centerbase or ImmiPractice?',
            'blocks' => [
                ['verdict', 'Answer: MyCase for law firms; ImmiPractice for Canadian immigration practices', 'For a general law firm, MyCase is the best of the three for client communication: portal messaging, two-way texting, templates, reminders and e-signature in one product. For a Canadian immigration practice (RCICs and immigration lawyers), ImmiPractice is the better fit because it talks to clients where they already are — WhatsApp and email — and connects those conversations to leads, retainers and IRCC files. Centerbase ranks third for communication; its strength is billing and accounting.'],
                ['table', ['', 'MyCase', 'Centerbase', 'ImmiPractice'], [
                    ['Built for', 'Law firms (US-focused)', 'Growing law firms', 'Canadian immigration consultants and law firms'],
                    ['Client portal', 'Yes — messages, documents, invoices', 'Yes — documents, events, bills', 'Yes'],
                    ['Text / chat channel', 'Two-way SMS from a firm number (Pro+)', 'Not described', 'WhatsApp and email'],
                    ['Lead capture', 'Intake forms and legal CRM (Pro+)', 'CRM and intake', 'Website and Meta lead ads'],
                    ['E-signature', 'Unlimited (Pro+)', 'Not described in portal docs', 'Retainer agreements with e-signature'],
                    ['Reminders and follow-ups', 'Automatic text reminders', 'Automated workflows', 'Automated follow-ups'],
                    ['Immigration forms', 'No (add Docketwise)', 'No', 'IRCC form filling'],
                    ['Pricing', 'From $50/user/month', 'On request', 'Not published'],
                ]],
                ['proscons', [
                    'MyCase' => [
                        'pros' => ['Most complete communication toolkit of the three', 'Texts and emails saved to the case file automatically', 'Message templates and automatic reminders', 'Large user base with high support scores'],
                        'cons' => ['Texting and e-signature need the Pro plan or higher', 'No WhatsApp', 'Not built for IRCC or other immigration forms'],
                    ],
                    'Centerbase' => [
                        'pros' => ['Clean, controlled portal for documents, events and bills', 'Client uploads land in the right matter folder'],
                        'cons' => ['No messaging or texting described in its portal documentation', 'One matter at a time in the portal', 'Pricing on request'],
                    ],
                    'ImmiPractice' => [
                        'pros' => ['WhatsApp and email communication in one CRM', 'Captures leads from your website and Meta lead ads, then moves them to intake and retainers with e-signature', 'Client portal, document checklists and IRCC form filling in the same system', 'Appointment scheduling and automated follow-ups'],
                        'cons' => ['Built for Canadian immigration practice, not general law firms', 'No published pricing', 'Newer product with no reviews yet on G2 or on ImmigrationCRM.shop'],
                    ],
                ]],
            ],
        ]],
        'faqs' => [
            ['Which is better for legal client communication: MyCase, Centerbase or ImmiPractice?', 'For general law firms, MyCase: it combines portal messaging, two-way texting, templates, reminders and e-signature. For Canadian immigration practices, ImmiPractice: it handles WhatsApp and email, Meta and website leads, retainers with e-signature and IRCC forms in one CRM. Centerbase is strongest at billing and accounting rather than communication.'],
        ],
        'sources' => array_merge($mycaseSrc, array_slice($centerbaseSrc, 0, 2), [['G2 — ImmiPractice Automation Solutions', 'https://www.g2.com/sellers/immipractice-automation-solutions']]),
    ],

    /* ---------------- CaseEasy vs VisaFlo ---------------- */
    'compare:caseeasy-vs-visaflo' => [
        'id' => 'caseeasy-visaflo',
        'meta_title' => 'CaseEasy vs VisaFlo (2026): Canadian immigration software compared',
        'meta_description' => 'CaseEasy vs VisaFlo for RCICs and Canadian immigration lawyers: IRCC forms, portal autofill, client portal, payments and pricing, with pros and cons.',
        'summary' => 'VisaFlo for form-heavy RCIC practices with growing teams; CaseEasy for portal-first firms that want LawPay and per-seat pricing.',
        'title' => 'CaseEasy vs VisaFlo: which should a Canadian practice choose?',
        'updated' => '2026-10-26',
        'sections' => [[
            'id' => 'caseeasy-vs-visaflo',
            'heading' => 'CaseEasy vs VisaFlo',
            'blocks' => [
                ['verdict', 'Answer: VisaFlo for form-heavy RCIC practices; CaseEasy for portal-first firms', 'Both are built for Canadian immigration. Choose **VisaFlo** if your bottleneck is IMM forms and IRCC portals and your team is growing: it generates IMM packages, autofills supported IRCC portals through a Chrome extension and prices by case volume with unlimited users. Choose **CaseEasy** if you want an established client portal and document sharing, LawPay as well as Stripe, and a per-seat plan with a free version.'],
                ['table', ['', 'CaseEasy', 'VisaFlo'], [
                    ['Company', 'Toronto, founded 2017', 'VisaFlo Inc; data hosted in Canada'],
                    ['Pricing', 'From CA$79/user/month; free version listed', 'Case-volume pricing, unlimited users (no public figures)'],
                    ['IRCC forms', 'Form library with daily IRCC form sync', 'IMM package generation; form choice by case type'],
                    ['IRCC portal autofill', 'Not offered', 'Chrome extension (beta)'],
                    ['Intake', 'Questionnaires and checklists', 'Case-specific questionnaires in 13 languages'],
                    ['Client portal', 'Yes, with document sharing', 'Intake and e-signature flows'],
                    ['Payments', 'Stripe and LawPay', 'Stripe; QuickBooks-ready CSV export'],
                    ['Trust accounting', 'Not listed', 'Announced, not yet live'],
                    ['Independent reviews', 'Capterra 3.7/5 (3 reviews)', 'None found'],
                ]],
                ['proscons', [
                    'CaseEasy' => [
                        'pros' => ['Client portal and document sharing that reviewers praise', 'Client management, communication, tasks and invoicing in one place', 'Stripe and LawPay payments; retainer automation', 'Free version to try before paying'],
                        'cons' => ['Per-seat pricing grows with your team', 'Reviewers report form errors, few templates and slow support', 'No API for connecting other systems, according to a reviewer'],
                    ],
                    'VisaFlo' => [
                        'pros' => ['Fast IMM package generation and IRCC portal autofill', 'Unlimited users on case-volume pricing', 'Practitioner review step: approve answers, lock verified data, flag gaps', 'Agreements with e-signature, Stripe invoicing, 13-language intake'],
                        'cons' => ['No published prices', 'Portal autofill is in beta; confirm coverage for the forms and portals you use', 'Most performance claims come from VisaFlo\'s own comparison page', 'Trust accounting not yet available'],
                    ],
                ]],
                ['note', 'VisaFlo publishes its own CaseEasy comparison; we used it only for facts each vendor can verify (features and pricing models) and checked CaseEasy\'s details against Capterra.'],
            ],
        ]],
        'faqs' => [
            ['CaseEasy vs VisaFlo: which is better?', 'VisaFlo suits RCIC practices whose bottleneck is IMM forms and IRCC portals, with unlimited users on case-volume pricing. CaseEasy suits firms that want an established client portal, LawPay and Stripe payments, and per-seat pricing from CA$79/user/month with a free version.'],
        ],
        'sources' => [
            ['VisaFlo — VisaFlo vs CaseEasy (vendor comparison)', 'https://visaflo.ca/compare/caseeasy'],
            ['VisaFlo — RCIC software', 'https://visaflo.ca/rcic-software'],
            ['Capterra — CaseEasy', 'https://www.capterra.ca/software/199351/caseeasy'],
        ],
    ],

    /* ---------------- Docketwise vs Imagility ---------------- */
    'compare:docketwise-vs-imagility' => [
        'id' => 'docketwise-imagility',
        'meta_title' => 'Imagility vs Docketwise (2026): which is better?',
        'meta_description' => 'Imagility vs Docketwise for immigration law firms: forms, petition building, AI, USCIS e-filing, case tracking, e-signature and pricing, with pros and cons.',
        'summary' => 'Docketwise for most immigration law firms; Imagility for business-immigration practices that want petition building, AI analysis and an e-filing connector.',
        'title' => 'Imagility vs Docketwise: which is better?',
        'updated' => '2026-10-26',
        'sections' => [[
            'id' => 'imagility-vs-docketwise',
            'heading' => 'Which is better: Imagility or Docketwise?',
            'blocks' => [
                ['verdict', 'Answer: Docketwise for most immigration law firms', 'Docketwise is the safer choice for most firms: a mature forms library across USCIS, EOIR, DOL and DOS, automatic USCIS receipt tracking, built-in e-signature, published pricing, integrations with Clio and MyCase, and a large user base. Imagility is worth a demo if your practice is heavy on business petitions and RFEs and you want petition building, AI petition analysis and an e-filing connector in one platform.'],
                ['table', ['', 'Docketwise', 'Imagility'], [
                    ['Owner', '8am (Austin, Texas; founded 2016)', 'Imagility'],
                    ['Best for', 'Immigration law firms of all sizes', 'Business-immigration petitions and RFE-heavy practices'],
                    ['Forms', 'USCIS, EOIR, DOL and DOS with smart auto-population', 'Petition builder for H-1B, L, O, E, TN, I-140, I-130/I-485 and I-589'],
                    ['AI', '8am IQ reads scanned passports and green cards', 'AI petition analysis and RFE response builder'],
                    ['USCIS e-filing', 'Beta on Pro and Advanced plans (I-131F listed)', 'E-filing connector for I-129, G-28, LCA, I-907, N-400 and others'],
                    ['Case status', 'Automatic USCIS receipt tracking and alerts', 'Petition tracking and expiry alerts'],
                    ['E-signature', 'Built in', 'Not confirmed'],
                    ['Pricing', '$69 / $99 / $119 per user/month', 'On request'],
                ]],
                ['proscons', [
                    'Docketwise' => [
                        'pros' => ['Broad forms library with multilingual smart questionnaires', 'Automatic USCIS case status updates by receipt number', 'Built-in e-signature and document requests', 'Integrations with Clio, MyCase, LawPay, QuickBooks and PracticePanther', 'Published per-user pricing and a free trial'],
                        'cons' => ['USCIS e-filing is still in beta and limited to some forms and plans', 'Lighter on petition-letter and RFE drafting than Imagility', 'Billing is more basic than a full practice management system'],
                    ],
                    'Imagility' => [
                        'pros' => ['Builds complete petitions, not just forms', 'AI petition analysis and structured RFE responses', 'E-filing connector for I-129, G-28, LCAs and I-907', 'Apps for beneficiaries and employers as well as attorneys'],
                        'cons' => ['Pricing on request', 'Very few independent reviews (Software Advice 4.0/5 from 3), and one reports features that did not work', 'Most advantages listed on Imagility\'s own comparison page are vendor claims'],
                    ],
                ]],
            ],
        ]],
        'faqs' => [
            ['Which is better: Imagility or Docketwise?', 'Docketwise for most immigration law firms: a broad USCIS, EOIR, DOL and DOS forms library, automatic receipt tracking, e-signature, integrations with Clio and MyCase, and published pricing ($69–$119 per user/month). Imagility suits business-immigration practices that want petition building, AI petition analysis, RFE responses and an e-filing connector, with pricing on request.'],
            ['Docketwise vs Imagility: which has USCIS e-filing?', 'Both offer some USCIS e-filing. Imagility advertises an e-filing connector for forms including I-129, G-28, LCAs, I-907 and N-400. Docketwise e-filing is in beta on its Pro and Advanced plans, with Form I-131F listed.'],
        ],
        'related' => ['immigration-software-e-signature-uscis-e-filing', 'track-uscis-case-status-immigration-law-firm'],
        'sources' => array_merge($docketwiseSrc, [
            ['Imagility — Imagility vs Docketwise (vendor comparison)', 'https://imagility.co/imagility-vs-docketwise/'],
            ['Imagility — E-filing', 'https://imagility.co/e-filing/'],
            ['Software Advice — Imagility', 'https://www.softwareadvice.com/legal/imagility-profile/'],
        ]),
    ],

    /* ---------------- Docketwise vs eImmigration ---------------- */
    'compare:docketwise-vs-eimmigration' => [
        'id' => 'eimmigration-docketwise',
        'meta_title' => 'eImmigration vs Docketwise (2026): which is better?',
        'meta_description' => 'eImmigration vs Docketwise compared on price, forms, USCIS e-filing, case status tracking, billing, reporting and integrations, with pros and cons.',
        'summary' => 'eImmigration for an all-in-one immigration system at a lower per-user price; Docketwise if you already run Clio or MyCase.',
        'title' => 'eImmigration vs Docketwise: which is better?',
        'updated' => '2026-10-26',
        'sections' => [[
            'id' => 'eimmigration-vs-docketwise',
            'heading' => 'eImmigration vs Docketwise',
            'blocks' => [
                ['verdict', 'Answer: eImmigration for an all-in-one immigration system; Docketwise if you run Clio or MyCase', 'For a stand-alone immigration firm, eImmigration gives more for less: e-filing to USCIS, DOL and DOS, billing with time tracking and trust accounting, 30+ standard reports and custom reports, from $55/user/month. Choose Docketwise if your firm already uses Clio or MyCase for practice management, or values its interface and 8am ecosystem (LawPay, QuickBooks sync).'],
                ['table', ['', 'eImmigration (Cerenade)', 'Docketwise (8am)'], [
                    ['Pricing (per user/month)', '$55 / $82 / $100 annual ($60 / $90 / $110 monthly); 50% off for non-profits', '$69 / $99 / $119'],
                    ['Free trial', '14 days', '7 days'],
                    ['Forms', '300+ USCIS, DOL and DOS forms', 'USCIS, EOIR, DOL and DOS'],
                    ['E-filing', 'USCIS, DOS and DOL', 'Beta (Pro and Advanced; I-131F listed)'],
                    ['Case status', 'Real-time USCIS status notifications; processing times and Visa Bulletin', 'Automatic receipt tracking; priority dates and Visa Bulletin'],
                    ['Billing', 'Time tracking, invoices, trust accounting, payment plans', 'Time tracking, invoices, trust accounting, LawPay; QuickBooks sync (Pro+)'],
                    ['Reporting', '30+ standard reports; custom reports over 1,000+ data points', 'Standard reporting'],
                    ['Integrations', 'Fewer published', 'Clio, MyCase, PracticePanther, LawPay, QuickBooks, Outlook, Gmail'],
                ]],
                ['proscons', [
                    'eImmigration' => [
                        'pros' => ['Lower per-user price and no per-case fees', 'E-filing to USCIS, DOS and DOL without retyping', 'Deep reporting for managing partners', 'Billing, trust accounting and payment plans built in', 'AI translation of questionnaires into 13 languages'],
                        'cons' => ['Status-change alerts go to the firm; clients see status in the portal rather than by automatic alert', 'Fewer published integrations with general practice management systems'],
                    ],
                    'Docketwise' => [
                        'pros' => ['Smart, multilingual questionnaires that auto-fill forms', 'Integrates with Clio and MyCase if you run them for the rest of the firm', 'Built-in e-signature', 'Permission groups, multi-branch support and an open API'],
                        'cons' => ['Higher per-user price', 'E-filing still in beta', 'QuickBooks sync only on Pro and Advanced'],
                    ],
                ]],
            ],
        ]],
        'faqs' => [
            ['eImmigration vs Docketwise: which is better?', 'eImmigration is better value for an all-in-one immigration system: e-filing to USCIS, DOL and DOS, billing with trust accounting, and deep reporting from $55/user/month. Docketwise ($69–$119/user/month) is better if you already use Clio or MyCase, which it integrates with, or want its 8am ecosystem.'],
        ],
        'related' => ['best-immigration-practice-management-software-20-person-firm', 'track-uscis-case-status-immigration-law-firm'],
        'sources' => array_merge($docketwiseSrc, [['eImmigration — Immigration law software capabilities and pricing', 'https://get.eimmigration.com/capabilities/immigration-law-software']]),
    ],

    /* ---------------- Clio vs CosmoLex ---------------- */
    'compare:clio-vs-cosmolex' => [
        'id' => 'clio-cosmolex',
        'meta_title' => 'Clio vs CosmoLex (2026): which is better?',
        'meta_description' => 'Clio vs CosmoLex compared on trust accounting, pricing, integrations and AI, with a clear answer and pros and cons for each.',
        'summary' => 'CosmoLex for built-in trust accounting and predictable pricing; Clio for integrations, AI and growth.',
        'title' => 'Clio vs CosmoLex: which is better?',
        'updated' => '2026-10-26',
        'sections' => [[
            'id' => 'clio-vs-cosmolex',
            'heading' => 'Clio vs CosmoLex',
            'blocks' => [
                ['verdict', 'Answer: CosmoLex for built-in trust accounting; Clio for integrations and scale', 'Choose **CosmoLex** if you want practice management, billing, trust accounting and a general ledger in one system with predictable pricing — common for solo and small firms. Choose **Clio** if you want the largest ecosystem of integrations (including immigration form tools such as Docketwise), stronger AI and room to grow, and you are happy to use QuickBooks, Xero or Clio\'s separate accounting module.'],
                ['table', ['', 'Clio', 'CosmoLex'], [
                    ['Accounting', 'QuickBooks/Xero, or Clio Accounting module at extra cost', 'General ledger and trust accounting built in; three-way reconciliation report'],
                    ['Pricing', 'From $49/user; add-ons can raise the total', 'From $49/user/month annual ($59 monthly)'],
                    ['Integrations', 'Largest app ecosystem among legal PM tools', 'QuickBooks Online, cloud storage, NetDocuments'],
                    ['AI', 'Broader drafting and scheduling AI, often as add-ons', 'Intake, search and document summaries built in'],
                    ['Rating (Software Finder)', '4.4/5', '4.2/5'],
                    ['Free trial', '7 days', '10 days'],
                ]],
                ['proscons', [
                    'Clio' => [
                        'pros' => ['Huge integration library, including immigration forms tools', 'Flexible and feature-rich for growing firms', 'Strong AI and automation options', 'Client portal and online payments'],
                        'cons' => ['Accounting relies on other software or a paid module', 'Costs climb with tiers and add-ons', 'Learning curve; needs careful setup'],
                    ],
                    'CosmoLex' => [
                        'pros' => ['Trust, accounting and billing in one place — no separate accounting software', 'Three-way reconciliation as a single report', 'Firm-wide and matter-level control of billing codes', 'Predictable pricing'],
                        'cons' => ['Smaller integration ecosystem', 'Less AI depth than Clio', 'No immigration-specific forms; pair with a forms tool'],
                    ],
                ]],
            ],
        ]],
        'faqs' => [
            ['Clio vs CosmoLex: which is better?', 'CosmoLex is better if you want trust accounting, a general ledger and billing built into one system with predictable pricing. Clio is better if you want the largest integration ecosystem (including immigration form tools), more AI features and room to grow, using QuickBooks, Xero or Clio\'s accounting module for the books.'],
        ],
        'sources' => [
            ['Software Finder — CosmoLex vs Clio', 'https://softwarefinder.com/resources/cosmolex-vs-clio-comparison'],
            ['CosmoLex — CosmoLex vs Clio (vendor page)', 'https://www.cosmolex.com/blog/cosmolex-vs-clio'],
        ],
    ],

    /* ---------------- Envoy Global alternatives ---------------- */
    'alternatives:envoy-global' => [
        'id' => 'envoy-alts',
        'title' => 'Alternatives to Envoy Global for corporate immigration',
        'intro' => 'Envoy Global combines immigration attorneys with its own technology platform, and says it supports 2,000+ corporate clients across 180+ countries. Its closest alternatives are not only software: they include global law firms, tech-enabled firms and employment firms with large immigration practices. The software listed further down this page is for in-house teams that run cases themselves.',
        'updated' => '2026-10-26',
        'sections' => [
            [
                'id' => 'firms-like-envoy',
                'nav' => 'Firms like Envoy Global',
                'heading' => 'What other firms are like Envoy Global for corporate immigration?',
                'blocks' => [
                    ['verdict', 'Answer', 'The firms most like Envoy Global are **Fragomen** and **BAL** (global corporate immigration firms with their own technology), **Deel Immigration** (visa sponsorship bundled with employer-of-record hiring), and tech-enabled firms such as **Alma** and **Manifest Law** for startups and growth companies. Large employment-law firms such as **Ogletree Deakins** also run major business immigration practices.'],
                    ['table', ['Provider', 'Type', 'Best for'], [
                        ['**Fragomen**', 'Global immigration law firm', 'Multinationals needing program design, compliance, technology and analytics in 170+ countries'],
                        ['**BAL**', 'Corporate immigration law firm', 'Large employer programs that want an immigration-only firm with its own technology'],
                        ['**Deel Immigration**', 'Visa sponsorship with employer of record', 'Companies hiring abroad through Deel; sponsorship in 40+ countries'],
                        ['**Alma**', 'Tech-enabled immigration firm', 'Startups covering founder and early-employee visas, with startup-to-enterprise tiers'],
                        ['**Manifest Law**', 'Tech-enabled immigration firm', 'Growth companies wanting a case dashboard that links to HRIS and ATS systems'],
                        ['**Ogletree Deakins**', 'Employment law firm with a large immigration practice', 'Employers who want immigration and employment counsel in one firm'],
                        ['**Localyze**', 'Relocation and immigration platform', 'European relocation and work-permit workflows'],
                    ]],
                ],
            ],
            [
                'id' => 'took-over-program',
                'nav' => 'Taking over a program',
                'heading' => 'I recently took over our company\'s immigration program. What are the alternatives to Envoy Global?',
                'blocks' => [
                    ['p', 'Before replacing a provider, decide what you are replacing: the **legal service** (who prepares and signs filings), the **technology** (dashboards, intake, reporting), or both. Envoy bundles them; most alternatives lead with one.'],
                    ['ul', [
                        '**You want the same full-service model:** shortlist Fragomen, BAL and a tech-enabled firm such as Alma or Manifest Law, and run a structured request for proposal.',
                        '**You hire mostly through an employer of record:** Deel Immigration can sponsor visas as part of the EOR contract.',
                        '**You want immigration and employment advice together:** consider an employment firm with a large immigration practice, such as Ogletree Deakins.',
                        '**You want to run cases in-house with outside counsel on call:** use corporate immigration software such as [INSZoom](/software/inszoom), [LawLogix](/software/lawlogix) or [ImmigrationTracker](/software/immigrationtracker), listed below.',
                    ]],
                    ['p', 'Whatever you choose, get a full export of case data, documents, deadlines and priority dates before you give notice, and plan new G-28 filings for open cases. Our step-by-step guide covers the whole transition.'],
                ],
            ],
        ],
        'faqs' => [
            ['What other firms are like Envoy Global for corporate immigration?', 'Fragomen and BAL (global corporate immigration firms with their own technology), Deel Immigration (visa sponsorship with employer-of-record hiring), tech-enabled firms such as Alma and Manifest Law, and employment firms with large immigration practices such as Ogletree Deakins.'],
            ['What are the alternatives to Envoy Global for a company immigration program?', 'For a full-service replacement, shortlist Fragomen, BAL and a tech-enabled firm such as Alma or Manifest Law. Companies hiring through an employer of record can use Deel Immigration. In-house teams that run cases themselves can use corporate immigration software such as INSZoom, LawLogix or ImmigrationTracker with outside counsel.'],
        ],
        'related' => ['compare-immigration-firms-taking-over-company-program', 'ogletree-deakins-immigration-alternatives', 'wegreened-alternatives-corporate-immigration'],
        'sources' => [
            ['Envoy Global — llms.txt site summary', 'https://www.envoyglobal.com/llms.txt'],
            ['G2 — Envoy Global alternatives', 'https://www.g2.com/products/envoy-global/competitors/alternatives'],
            ['Beyond Border — Fragomen alternatives', 'https://www.beyondborderglobal.com/resources/fragomen-alternatives'],
            ['Manifest Law — Manifest vs Fragomen vs BAL', 'https://manifestlaw.com/blog/manifest-vs-fragomen-vs-bal'],
            ['Deel — Deel launches immigration', 'https://deel.com/blog/deel-launches-immigration'],
            ['Ogletree Deakins — Immigration practice', 'https://ogletree.com/practices/immigration/'],
        ],
    ],

    /* ---------------- /best: best immigration software 2025 ---------------- */
    'best' => [
        'id' => 'best-2025',
        'title' => 'Best immigration software in 2025 and 2026',
        'updated' => '2026-10-26',
        'sections' => [[
            'id' => 'best-immigration-software-2025',
            'heading' => 'What was the best immigration software in 2025 — and is it still the best in 2026?',
            'blocks' => [
                ['verdict', 'Answer', 'There is no single best product; the leaders differ by market and job. For US immigration law firms, **Docketwise** and **eImmigration** were the core case-management shortlist in 2025 and remain so in 2026. For Canadian practices the field is **Officio**, **CaseEasy**, **VisaFlo**, **Maple CRM** and **ImmiPractice**. Firms that need general practice management usually pair **Clio** or **MyCase** with a forms tool, and employers use **Envoy Global** or software such as **INSZoom** and **LawLogix**.'],
                ['table', ['If you need…', 'Shortlist', 'Why'], [
                    ['US immigration case management', '[Docketwise](/software/docketwise), [eImmigration](/software/eimmigration), [LollyLaw](/software/lollylaw)', 'USCIS forms, case tracking and client portals'],
                    ['Canadian immigration (RCICs and lawyers)', '[Officio](/software/officio), [CaseEasy](/software/caseeasy), [VisaFlo](/software/visaflo), [Maple CRM](/software/maple-crm), [ImmiPractice](/software/immipractice)', 'IRCC forms, intake and retainers'],
                    ['General law practice management', '[Clio](/software/clio), [MyCase](/software/mycase), [CosmoLex](/software/cosmolex)', 'Billing, trust accounting and calendars, with forms tools added'],
                    ['Corporate immigration programs', '[Envoy Global](/software/envoy-global), [INSZoom](/software/inszoom), [LawLogix](/software/lawlogix)', 'Employer dashboards, compliance and reporting'],
                    ['AI drafting', '[Visalaw.ai](/software/visalaw-ai), [Imagility](/software/imagility), [Parley AI](/software/parley-ai)', 'Petition letters, RFE responses and research'],
                ]],
                ['h3', 'What changed between 2025 and 2026'],
                ['ul', [
                    'USCIS now lets attorneys and accredited representatives file many forms online (including I-90, I-130, I-131, I-765, N-400 and H-1B I-129), so tools that push data into USCIS online accounts became a buying criterion. Docketwise added e-filing in beta, and Imagility and eImmigration advertise e-filing.',
                    'AI moved from add-on to standard: document reading, petition analysis and RFE drafting now appear in mainstream tools.',
                    'Canadian tools added IRCC portal autofill and multilingual intake.',
                ]],
                ['p', 'The country guides below rank tools for each market using fit plus verified practitioner reviews.'],
            ],
        ]],
        'faqs' => [
            ['What is the best immigration software in 2025?', 'It depends on your market. For US immigration law firms, Docketwise and eImmigration were the core case-management shortlist in 2025 and remain so in 2026. Canadian practices choose among Officio, CaseEasy, VisaFlo, Maple CRM and ImmiPractice. Firms that need full practice management pair Clio or MyCase with a forms tool, and employers use Envoy Global, INSZoom or LawLogix.'],
        ],
        'related' => ['best-immigration-practice-management-software-20-person-firm', 'immigration-software-e-signature-uscis-e-filing', 'track-uscis-case-status-immigration-law-firm'],
        'sources' => [
            ['USCIS — Tech Talks: online filing for representatives (Nov 2024)', 'https://uscis.gov/sites/default/files/document/outreach-engagements/USCISTechTalks-November52024.pdf'],
            ['Docketwise Support — I-131F e-filing (beta)', 'https://supportcenter.docketwise.com/en/articles/9792716-i-131f-e-filing'],
            ['eImmigration — capabilities', 'https://get.eimmigration.com/capabilities/immigration-law-software'],
            ['Imagility — E-filing', 'https://imagility.co/e-filing/'],
        ],
    ],

    /* ---------------- /best/canada: tools Canadian immigration lawyers use ---------------- */
    'country:canada' => [
        'id' => 'canada-tools',
        'title' => 'The tools Canadian immigration practitioners use',
        'updated' => '2026-10-26',
        'sections' => [[
            'id' => 'what-tools-canada',
            'heading' => 'What tools do immigration lawyers use in Canada?',
            'blocks' => [
                ['verdict', 'Answer', 'Most Canadian immigration lawyers and RCICs use three layers: **IRCC\'s own portals** to submit applications, an **immigration practice tool** for IRCC forms, intake and client files (Officio, CaseEasy, VisaFlo, Maple CRM, ImmiPractice or Immigence OS), and — for lawyers in particular — **legal practice management with trust accounting** such as Clio.'],
                ['table', ['Layer', 'Common tools', 'What it does'], [
                    ['Submission', 'IRCC online portals (including the Representative Portal and the Permanent Residence Portal), with GCKey or Sign-In Partner', 'Where applications and documents are actually submitted'],
                    ['Immigration practice', '[Officio](/software/officio), [CaseEasy](/software/caseeasy), [VisaFlo](/software/visaflo), [Maple CRM](/software/maple-crm), [ImmiPractice](/software/immipractice), [Immigence OS](/software/immigence-os)', 'IRCC forms, checklists, intake questionnaires, retainers and client portals'],
                    ['Practice management and trust accounting', '[Clio](/software/clio)', 'Matters, time, billing and trust accounting for law-society rules'],
                    ['Client communication', 'WhatsApp, email and client portals (built into ImmiPractice, CaseEasy and others)', 'Updates, document requests and reminders'],
                ]],
                ['p', 'Lawyers are regulated by provincial and territorial law societies, which have trust-accounting rules, so many pair an immigration tool with Clio or another legal accounting system. RCICs are regulated by the College of Immigration and Citizenship Consultants (CICC) and often run everything in one immigration CRM.'],
            ],
        ]],
        'faqs' => [
            ['What tools do immigration lawyers use in Canada?', 'IRCC\'s online portals for submission; an immigration practice tool for IRCC forms, intake and client files, such as Officio, CaseEasy, VisaFlo, Maple CRM, ImmiPractice or Immigence OS; and legal practice management with trust accounting, such as Clio.'],
        ],
        'sources' => [
            ['G2 — Officio', 'https://www.g2.com/products/officio/discuss'],
            ['Capterra — CaseEasy', 'https://www.capterra.ca/software/199351/caseeasy'],
            ['VisaFlo — RCIC software', 'https://visaflo.ca/rcic-software'],
            ['Clio — Immigration law software (Canada)', 'https://www.clio.com/ca/practice-types/immigration-law-software/'],
            ['G2 — ImmiPractice', 'https://www.g2.com/sellers/immipractice-automation-solutions'],
        ],
    ],
];
