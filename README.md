# ImmigrationCRM.shop

A verified-review directory for immigration software: immigration CRMs, immigration case management, law practice management, AI tools for immigration law and corporate immigration platforms.

**To put it online, follow [SETUP.md](SETUP.md)** — 4 steps in Hostinger's hPanel, no coding.

Built in plain PHP 8.1+ and MySQL, so it runs on ordinary Hostinger web hosting. No frameworks or extra libraries to install. (The earlier Node.js version is kept in the `nextjs` branch.)

## Features

**Brand**
- Logo: shield (trust) + check (verified) + golden sparkle and flight path (premium, the immigration journey). Files in `assets/` — `logo.svg`, `logo-white.svg`, `logo-mark.svg`, app icons and favicon. Source script: `assets/brand/build_logo.py`.
- Colours: royal blue `#2747D4` and navy `#0E1A47` for trust, gold `#F6B93B` for premium, green `#0E9F6E` for verified. Fonts: Poppins (headings) and Inter (text), self-hosted.

**Country buying guides** — built to be the answer when someone asks ChatGPT, Perplexity, Gemini or Google “what is the best immigration CRM in Canada?”
- `/best` index, a hub per country (`/best/canada`) and a ranked guide per category and country (`/best/immigration-crm/canada`) for Canada, the USA, the UK, Australia and India.
- Each has a direct answer at the top, a ranked list with reasons, a buying checklist, the country's regulatory context (CICC/IRCC, USCIS/EOIR, SRA/Immigration Advice Authority, OMARA/Home Affairs), the ranking method, FAQs, Article + ItemList + FAQPage schema, and a Markdown version.

**Practitioners**
- 42 starter listings across 6 categories; search, filter by market, sort by rating.
- **Compare any 2–4 products side by side**, from any category: verified ratings, sub-ratings, recommend rate, markets, audiences, every listed feature, pricing, recent pros and cons.
- **Verified reviews only:** work-email code (Gmail, Yahoo, Outlook and disposable inboxes refused) **plus** LinkedIn sign-in or a moderator-checked proof-of-use upload. No self-reviews; one review per person per product.

**Vendors**
- Free listings. A work email on the product's domain publishes and claims instantly; otherwise a moderator checks it.
- Claim listings, edit them, reply publicly to reviews (never edit or hide them).
- Paid featured placement through Stripe (1, 3 or 12 months), always labelled, never affects ratings.

**Admin** — Moderation menu for anyone in the admin email list
- Approve proofs, new listings and vendor claims; unpublish reviews; set verified domains and featured dates.
- **Settings tab** for email, LinkedIn, Stripe, Google, Bing and IndexNow — no file editing.

**Expert answers and blog**
- Researched answers to real search questions, placed on the pages where they belong: definitive verdicts with pros and cons on comparison pages (for example `/compare/centerbase-vs-mycase`), the Envoy Global alternatives page, `/best` and `/best/canada`. Content lives in `app/content/insights.php`.
- `/blog` for questions that have no natural home elsewhere, one file per article in `app/content/blog/`. Each article has BlogPosting and FAQPage schema, a Markdown version (`.md`) and appears in the sitemap, `llms.txt` and `llms-full.txt`.

## Search engine and AI-search optimisation

| Feature | Where |
|---|---|
| Unique title, meta description, canonical URL, Open Graph and Twitter card on every page | `app/lib/seo.php`, `app/views/layout.php` |
| Share image generated for every product, category and comparison | `/…/og.png` |
| schema.org structured data: Organization, WebSite + SearchAction, WebPage, BreadcrumbList, SoftwareApplication with AggregateRating and Reviews (with pros/cons), ItemList, CollectionPage, AboutPage, FAQPage | every page |
| Landing pages for "best X software", "X reviews", "X alternatives", "X vs Y" (206 pairs in the sitemap, any combination on demand) | `/category/*`, `/software/*`, `/software/*/alternatives`, `/compare/*` |
| Answer-first summary box and factual FAQ on key pages | product, category, compare, alternatives, home |
| `llms.txt` and `llms-full.txt` for AI assistants | `/llms.txt`, `/llms-full.txt` |
| Markdown version of every product and category page | add `.md`, e.g. `/software/maple-crm.md` |
| `robots.txt` welcoming search and AI crawlers (Googlebot, Bingbot, GPTBot, OAI-SearchBot, ClaudeBot, PerplexityBot, Google-Extended, Applebot-Extended…) | `/robots.txt` |
| XML sitemap with last-modified dates | `/sitemap.xml` |
| IndexNow: new reviews and listings reported to Bing and Yandex within seconds | `app/lib/indexnow.php` |
| RSS feed of verified reviews | `/feed.xml` |
| Installable web app manifest and icons | `/manifest.webmanifest` |
| HTTPS and www redirects, security headers, compression, caching | `.htaccess` |

Rankings and AI citations still depend on real verified reviews, vendors claiming listings, and links from other sites.

## Files

```
index.php            front door: routes every page
install.php          one-time setup form (switches itself off afterwards)
.htaccess            HTTPS, clean URLs, security, caching
assets/              styles, comparison script, icons, fonts
app/                 (blocked from the web)
  pages/             one file per page
  endpoints/         llms.txt, sitemap, robots, feed, share images, LinkedIn, Stripe…
  lib/               database, sign-in, email (built-in SMTP), reviews, SEO, comparisons, Stripe
  views/             layout and shared pieces
  schema.sql         database tables
  seed-data.json     the 42 starter listings
config.php           created by install.php — database password and secret (never in Git)
```

## Privacy and legal

- `/privacy` (≈3,200 words) and `/terms` (≈3,500 words) cover GDPR / UK GDPR, India's DPDP Act 2023 and IT Rules grievance process, Canada's PIPEDA, US state privacy laws (CCPA/CPRA and others), and Australia/New Zealand; review guidelines; vendor rules; featured-placement payments and refunds; content reporting and takedown; liability; and disputes.
- Business-specific details (legal name, address, governing law, courts, Grievance Officer, hosting location) are entered in **Moderation → Settings → Legal** and appear as highlighted blanks until then.
- The policies describe what the code actually does: account self-service in **My account → Your data** (download as JSON, delete account), automatic deletion of sign-in codes after 24 hours, proof files 12 months after moderation, and rejected reviews after 12 months.

## Before launch
- Fill in Moderation → Settings → Legal, then have a lawyer approve `/privacy` and `/terms`.
- Starter listings come from public sources (October 2026) and carry no ratings. Ten have no website yet; vendors add it when they claim, or set it in Moderation → Directory.
