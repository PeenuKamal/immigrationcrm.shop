import type { Metadata, Viewport } from "next";
import Link from "next/link";
import "./globals.css";
import { currentUser, isAdmin } from "@/lib/session";
import { BrandMark } from "@/components/Stamp";
import { appUrl } from "@/lib/util";
import { SITE, categorySeoLower } from "@/lib/site";
import { JsonLd, graph, orgLd, websiteLd } from "@/lib/seo";
import { allCategoriesWithCounts } from "@/lib/queries";

export const metadata: Metadata = {
  metadataBase: new URL(appUrl()),
  title: { default: `${SITE.name} — verified reviews of immigration software`, template: `%s | ${SITE.name}` },
  description: SITE.description,
  applicationName: SITE.name,
  keywords: ["immigration CRM", "immigration case management software", "immigration software reviews", "RCIC software", "migration agent software", "immigration law practice management", "AI for immigration lawyers", "visa consultant CRM"],
  authors: [{ name: SITE.name, url: appUrl() }],
  creator: SITE.name,
  publisher: SITE.name,
  category: "Business software reviews",
  formatDetection: { email: false, telephone: false, address: false },
  alternates: {
    canonical: "/",
    types: { "application/rss+xml": [{ url: "/feed.xml", title: `${SITE.name} — latest verified reviews` }], "text/plain": [{ url: "/llms.txt", title: "llms.txt" }] },
  },
  openGraph: { siteName: SITE.name, type: "website", locale: "en_US", url: appUrl() },
  twitter: { card: "summary_large_image" },
  robots: { index: true, follow: true, googleBot: { index: true, follow: true, "max-image-preview": "large", "max-snippet": -1, "max-video-preview": -1 } },
  verification: {
    google: process.env.GOOGLE_SITE_VERIFICATION || undefined,
    yandex: process.env.YANDEX_VERIFICATION || undefined,
    other: process.env.BING_SITE_VERIFICATION ? { "msvalidate.01": process.env.BING_SITE_VERIFICATION } : undefined,
  },
  appleWebApp: { capable: true, title: SITE.shortName, statusBarStyle: "default" },
  icons: { icon: [{ url: "/icon.svg", type: "image/svg+xml" }, { url: "/favicon.ico" }], apple: "/apple-touch-icon.png" },
};

export const viewport: Viewport = { themeColor: "#0f6b5c", width: "device-width", initialScale: 1 };

export default async function RootLayout({ children }: { children: React.ReactNode }) {
  const [user, cats] = await Promise.all([currentUser(), allCategoriesWithCounts()]);
  return (
    <html lang="en">
      <head>
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossOrigin="" />
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Archivo:wdth,wght@62..125,400..900&family=Public+Sans:ital,wght@0,400..700;1,400&display=swap" />
        <JsonLd data={graph(orgLd(), websiteLd())} />
      </head>
      <body>
        <a href="#main" className="sr-only">Skip to content</a>
        <header className="site-header">
          <div className="wrap">
            <Link href="/" className="brand" aria-label={`${SITE.name} home`}>
              <BrandMark />
              <span>ImmigrationCRM<span className="tld">.shop</span></span>
            </Link>
            <nav className="nav" aria-label="Main">
              <Link href="/software">Browse software</Link>
              <Link href="/compare">Compare</Link>
              <Link href="/how-we-verify">How we verify</Link>
              <Link href="/list-your-software">List your software</Link>
              {user && isAdmin(user) && <Link href="/admin">Moderation</Link>}
              {user ? <Link href="/account">My account</Link> : <Link href="/signin">Sign in</Link>}
            </nav>
          </div>
        </header>
        <main id="main">{children}</main>
        <footer className="site-footer">
          <div className="wrap">
            <div className="footer-cols">
              <div>
                <h2>Categories</h2>
                <ul>{cats.map((c) => <li key={c.slug}><Link href={`/category/${c.slug}`}>Best {categorySeoLower(c.slug, c.name)}</Link></li>)}</ul>
              </div>
              <div>
                <h2>Compare</h2>
                <ul>
                  <li><Link href="/compare">Compare any software</Link></li>
                  <li><Link href="/compare/docketwise-vs-lollylaw">Docketwise vs LollyLaw</Link></li>
                  <li><Link href="/compare/caseeasy-vs-maple-crm">CaseEasy vs Maple CRM</Link></li>
                  <li><Link href="/compare/clio-vs-mycase">Clio vs MyCase</Link></li>
                  <li><Link href="/compare/parley-ai-vs-visalaw-ai">Parley AI vs Visalaw.ai</Link></li>
                </ul>
              </div>
              <div>
                <h2>{SITE.name}</h2>
                <ul>
                  <li><Link href="/about">About us</Link></li>
                  <li><Link href="/how-we-verify">How we verify reviews</Link></li>
                  <li><Link href="/vendor">For vendors</Link></li>
                  <li><Link href="/privacy">Privacy</Link></li>
                  <li><Link href="/terms">Terms</Link></li>
                  <li><a href="/feed.xml">Reviews RSS feed</a></li>
                </ul>
              </div>
            </div>
            <span>© {SITE.year} {SITE.name}. Independent reviews from verified immigration practitioners.</span>
          </div>
        </footer>
      </body>
    </html>
  );
}
