import { SITE } from "@/lib/site";
import Link from "next/link";
import { notFound, redirect } from "next/navigation";
import { and, desc, eq } from "drizzle-orm";
import { db, software, softwareCategories, categories, reviews } from "@/db";
import { requireUser } from "@/lib/session";
import { allCategoriesWithCounts } from "@/lib/queries";
import { ListingForm } from "@/components/ListingForm";
import { updateListing, respondToReview, startFeaturedCheckout } from "@/app/actions/vendor";
import { FEATURED_PLANS, featuredPrice, formatMoney } from "@/lib/stripe";
import { isFeatured, formatDate } from "@/lib/util";

export const metadata = { title: "Manage listing", robots: { index: false } };

export default async function ManageListing({ params, searchParams }: { params: Promise<{ id: string }>; searchParams: Promise<Record<string, string | undefined>> }) {
  const { id } = await params;
  const sp = await searchParams;
  const user = await requireUser(`/vendor/${id}`);
  const s = await db.query.software.findFirst({ where: eq(software.id, id) });
  if (!s) notFound();
  if (s.ownerId !== user.id) redirect("/vendor");

  const [cats, myCats, revs] = await Promise.all([
    allCategoriesWithCounts(),
    db.select({ slug: categories.slug }).from(softwareCategories).innerJoin(categories, eq(categories.id, softwareCategories.categoryId)).where(eq(softwareCategories.softwareId, s.id)),
    db.select().from(reviews).where(and(eq(reviews.softwareId, s.id), eq(reviews.status, "PUBLISHED"))).orderBy(desc(reviews.publishedAt)),
  ]);
  const note =
    (sp.created && "Your listing is live.") ||
    (sp.claimed && "Listing claimed. You can now edit it and reply to reviews.") ||
    (sp.saved && "Changes saved.") ||
    (sp.replied && "Response saved.") ||
    (sp.payments === "off" && `Online payments aren't set up yet. Email ${SITE.contactEmail} to feature this listing.`) ||
    (sp.payment === "cancelled" && "Payment cancelled — nothing was charged.");

  return (
    <div className="wrap" style={{ maxWidth: 860 }}>
      <p className="small"><Link href="/vendor">Vendor dashboard</Link></p>
      <div className="page-head">
        <h1>{s.name}</h1>
        <p>{s.status === "PUBLISHED" ? <Link href={`/software/${s.slug}`}>View public listing</Link> : "This listing is waiting for an editor's check."}</p>
      </div>
      {note && <div className={`notice${sp.payment || sp.payments ? " warn" : ""}`} role="status"><p>{note}</p></div>}

      <div className="stack">
        <section className="panel" aria-labelledby="feat">
          <h2 id="feat" style={{ fontSize: "var(--step-2)" }}>Featured placement</h2>
          {isFeatured(s.featuredUntil) && <p><strong>Featured until {formatDate(s.featuredUntil)}.</strong> Buying more time extends it.</p>}
          <p className="muted">Featured listings appear first in their categories and on the home page, with a gold edge and a “Featured” label. It never affects ratings or reviews.</p>
          <div style={{ display: "flex", gap: 12, flexWrap: "wrap" }}>
            {FEATURED_PLANS.map((p) => (
              <form key={p.months} action={startFeaturedCheckout}>
                <input type="hidden" name="id" value={s.id} />
                <input type="hidden" name="months" value={p.months} />
                <button className={`btn ${p.months === 3 ? "" : "secondary"}`}>Feature for {p.label} · {formatMoney(featuredPrice(p.months))}</button>
              </form>
            ))}
          </div>
        </section>

        <section aria-labelledby="edit">
          <h2 id="edit" style={{ fontSize: "var(--step-2)" }}>Listing details</h2>
          <ListingForm
            action={updateListing}
            cats={cats}
            cta="Save changes"
            initial={{
              id: s.id, name: s.name, website: s.website ?? "", vendorName: s.vendorName ?? "", tagline: s.tagline, description: s.description,
              deployment: s.deployment ?? "Cloud", regions: s.regions.join(", "), audiences: s.audiences.join(", "), features: s.features.join("\n"),
              pricingNote: s.pricingNote ?? "", logoUrl: s.logoUrl ?? "", categories: myCats.map((c) => c.slug),
            }}
          />
        </section>

        <section id="reviews" aria-labelledby="rv">
          <h2 id="rv" style={{ fontSize: "var(--step-2)" }}>Reply to reviews</h2>
          {revs.length === 0 ? (
            <p className="muted">No verified reviews yet. Share your listing link with customers and ask them to review you — they'll go through the same verification as everyone else.</p>
          ) : (
            <div className="stack">
              {revs.map((r) => (
                <div key={r.id} className="panel">
                  <h3>{r.title} <span className="muted small">· {r.rating}/5</span></h3>
                  <p className="small muted">{r.reviewerRole} · {r.firmSize} · {formatDate(r.publishedAt)}</p>
                  <form action={respondToReview}>
                    <input type="hidden" name="reviewId" value={r.id} />
                    <div className="field">
                      <label htmlFor={`resp-${r.id}`}>Your public response</label>
                      <textarea id={`resp-${r.id}`} name="response" defaultValue={r.vendorResponse ?? ""} style={{ minHeight: 90 }} />
                    </div>
                    <button className="btn small secondary">Save response</button>
                  </form>
                </div>
              ))}
            </div>
          )}
        </section>
      </div>
    </div>
  );
}
