# ImmigrationCRM.shop

A verified-review directory for immigration software: immigration CRMs, immigration case management, law practice management, AI tools for immigration law and corporate immigration platforms.

**To put it online:** Hostinger Node.js Web App → [HOSTINGER.md](HOSTINGER.md) · your own VPS → [DEPLOY.md](DEPLOY.md).

## What it does

**For practitioners**
- Browse 39 starter listings across 6 categories, filter by market (USA, Canada, Australia, India, UK…) and search by feature.
- **Compare any 2–4 products side by side** — from any category — at `/compare`: verified ratings, sub-ratings, recommend rate, markets, audiences, every listed feature, pricing and recent pros and cons.
- Read and write **verified reviews only**:
  1. Work email confirmed with a one-time code from `otp@immigrationcrm.shop` (Gmail, Yahoo, Outlook, iCloud, Proton and disposable inboxes are refused).
  2. Plus **one** of: LinkedIn sign-in (publishes instantly) or a proof-of-use upload (screenshot or invoice, checked by a moderator, stored privately).
  3. Nobody can review their employer's own product; one review per person per product.

**For vendors**
- List free. A work email on the product's domain publishes and claims the listing instantly; otherwise it waits for a moderator.
- Claim existing listings, edit them, reply publicly to reviews. Vendors can never edit or hide reviews.
- Buy featured placement (1, 3 or 12 months) through Stripe. Featured listings are labelled and never change ratings.

**For you (admin)** — `/admin`, for anyone in `ADMIN_EMAILS` (`kamal@thedigisparrow.com`)
- Approve or reject proofs, new listings and vendor claims; unpublish reviews; set each listing's verified domain; set featured dates by hand.

## Search engine and AI-search optimisation

Built so Google, Bing, ChatGPT, Claude, Gemini, Perplexity and Copilot can find, understand and cite every page.

| Feature | Where |
|---|---|
| Unique title, meta description (≤160 chars), canonical URL, Open Graph and Twitter card on every page | `lib/seo.tsx` → `meta()` |
| Auto-generated social share image for every product, category and comparison | `opengraph-image.tsx` files |
| schema.org structured data: Organization, WebSite + SearchAction, WebPage, BreadcrumbList, SoftwareApplication with AggregateRating and Review (with pros/cons), ItemList, CollectionPage, AboutPage, FAQPage | `lib/seo.tsx`, each page |
| Landing pages for high-intent searches: "best X software" (6 categories), "X reviews" (every product), "X alternatives" (every product), "X vs Y" (every pair, 206 in the sitemap and any combination on demand) | `/category/*`, `/software/*`, `/software/*/alternatives`, `/compare/*` |
| Answer-first summary box and factual FAQ on every key page — the passages AI answer engines quote | product, category, compare, alternatives, home |
| `llms.txt` and `llms-full.txt` (site map and full content for AI assistants) | `/llms.txt`, `/llms-full.txt` |
| Markdown version of every product and category page | add `.md`, e.g. `/software/maple-crm.md` |
| `robots.txt` explicitly welcoming search and AI crawlers (Googlebot, Google-Extended, Bingbot, GPTBot, OAI-SearchBot, ChatGPT-User, ClaudeBot, Claude-SearchBot, PerplexityBot, Applebot-Extended and more) while keeping private pages out | `app/robots.ts` |
| XML sitemap with last-modified dates (≈290 URLs at launch, grows automatically) | `/sitemap.xml` |
| IndexNow: new reviews and listings are reported to Bing and Yandex within seconds | `lib/indexnow.ts` |
| RSS feed of verified reviews | `/feed.xml` |
| Google, Bing and Yandex verification tags from `.env` | `app/layout.tsx` |
| Installable web app (manifest, icons, theme colour) | `app/manifest.ts` |
| Fast, server-rendered pages; security headers; one H1 per page; breadcrumbs; semantic HTML; mobile-friendly down to 375px | throughout |

What still decides rankings and AI citations — and only time can provide — is real verified reviews, vendors claiming and enriching their listings, and other sites linking here. The fastest wins: ask vendors to claim their listings and invite their customers to review, and ask practitioners you know to review the tools they use.

## Tech

Next.js 15 (App Router, server actions) · PostgreSQL + Drizzle ORM · Nodemailer over Hostinger SMTP · LinkedIn OpenID Connect · Stripe Checkout · PM2 + Nginx (or Docker).

## Local development

```bash
npm install
cp .env.example .env     # set DATABASE_URL and SESSION_SECRET at minimum
npm run dev              # http://localhost:3000 — tables and starter listings are created on first start
```

Without SMTP settings, login codes print in the terminal instead of being emailed.

## Project map

```
app/
  page.tsx                      home
  software/[slug]/              product page, alternatives, review form, claim, share image
  category/[slug]/              "best X software" pages
  compare/, compare/[pair]/     comparison builder and X-vs-Y pages
  account/ vendor/ admin/       reviewer, vendor and moderation dashboards
  actions/                      server actions: auth, reviews, vendor, admin
  api/                          LinkedIn sign-in, Stripe webhook, private proof viewer
  llms.txt/ llms-full.txt/ md/  AI-readable content
  sitemap.ts robots.ts manifest.ts feed.xml/ indexnow.txt/
components/                     UI pieces (stamp, review card, compare picker…)
db/                             schema, starter data, seed script
lib/                            verification rules (reviews.ts), SEO helpers, email, storage, Stripe
```

## Before launch
- Have a lawyer review `/privacy` and `/terms` for the markets you serve.
- Starter listings come from public sources (October 2026) and carry no ratings. Ten have no website yet; set them in Moderation → Directory, or the vendor adds one when claiming.
