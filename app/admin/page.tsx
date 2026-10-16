import Link from "next/link";
import { desc, eq, inArray } from "drizzle-orm";
import { db, reviews, software, users, listingClaims } from "@/db";
import { requireAdmin } from "@/lib/session";
import { moderateProof, moderateReview, moderateListing, moderateClaim, setListingDomain, setFeaturedManually } from "@/app/actions/admin";
import { formatDate, sameOrganisation } from "@/lib/util";

export const metadata = { title: "Moderation", robots: { index: false } };

const TABS = [
  { key: "proofs", label: "Proofs to check" },
  { key: "approval", label: "Reviews to approve" },
  { key: "listings", label: "New listings" },
  { key: "claims", label: "Listing claims" },
  { key: "published", label: "Published reviews" },
  { key: "directory", label: "Directory" },
] as const;

function reviewRows(status: ("PENDING_PROOF" | "PENDING_ADMIN" | "PUBLISHED")[]) {
  return db
      .select({ r: reviews, s: { name: software.name, slug: software.slug, domain: software.domain }, u: { email: users.email, linkedinName: users.linkedinName, emailDomain: users.emailDomain } })
      .from(reviews)
      .innerJoin(software, eq(software.id, reviews.softwareId))
      .innerJoin(users, eq(users.id, reviews.userId))
      .where(inArray(reviews.status, status))
      .orderBy(desc(reviews.createdAt))
      .limit(100);
}
type ReviewRow = Awaited<ReturnType<typeof reviewRows>>[number];

function Decide({ action, id, rejectLabel = "Reject", withReason }: { action: (f: FormData) => Promise<void>; id: string; rejectLabel?: string; withReason?: boolean }) {
  return (
    <div className="inline-form">
      <form action={action}><input type="hidden" name="id" value={id} /><input type="hidden" name="decision" value="approve" /><button className="btn small stamp">Approve</button></form>
      <form action={action} className="inline-form">
        <input type="hidden" name="id" value={id} /><input type="hidden" name="decision" value="reject" />
        {withReason && <><label className="sr-only" htmlFor={`reason-${id}`}>Reason</label><input id={`reason-${id}`} type="text" name="reason" placeholder="Reason shown to reviewer" /></>}
        <button className="btn small danger">{rejectLabel}</button>
      </form>
    </div>
  );
}

export default async function Admin({ searchParams }: { searchParams: Promise<{ tab?: string }> }) {
  await requireAdmin();
  const tab = (await searchParams).tab ?? "proofs";


  const [proofs, approval, pendingListings, claims] = await Promise.all([
    reviewRows(["PENDING_PROOF"]),
    reviewRows(["PENDING_ADMIN"]),
    db.select().from(software).where(eq(software.status, "PENDING")).orderBy(desc(software.createdAt)),
    db
      .select({ c: listingClaims, s: { name: software.name, domain: software.domain, slug: software.slug }, u: { email: users.email } })
      .from(listingClaims)
      .innerJoin(software, eq(software.id, listingClaims.softwareId))
      .innerJoin(users, eq(users.id, listingClaims.userId))
      .where(eq(listingClaims.status, "PENDING")),
  ]);
  const counts: Record<string, number> = { proofs: proofs.length, approval: approval.length, listings: pendingListings.length, claims: claims.length };

  return (
    <div className="wrap">
      <div className="page-head"><h1>Moderation</h1></div>
      <nav className="tabs" aria-label="Queues">
        {TABS.map((t) => (
          <Link key={t.key} className="chip" href={`/admin?tab=${t.key}`} aria-current={tab === t.key}>
            {t.label}{counts[t.key] ? ` (${counts[t.key]})` : ""}
          </Link>
        ))}
      </nav>

      {(tab === "proofs" || tab === "approval") && (() => {
        const rows = tab === "proofs" ? proofs : approval;
        if (!rows.length) return <div className="empty"><p>Nothing waiting. New items appear here as reviewers submit them.</p></div>;
        return (
          <div className="stack">
            {rows.map(({ r, s, u }) => (
              <div key={r.id} className="panel">
                <div style={{ display: "flex", justifyContent: "space-between", gap: 16, flexWrap: "wrap" }}>
                  <div>
                    <h3 style={{ marginBottom: 4 }}>{s.name}: {r.title} <span className="muted small">· {r.rating}/5</span></h3>
                    <p className="small muted" style={{ margin: 0 }}>{u.email} · {r.reviewerRole} · {r.firmSize} · {formatDate(r.createdAt)} · LinkedIn: {u.linkedinName ?? "not connected"}</p>
                    {sameOrganisation(u.emailDomain, s.domain) && <p className="small" style={{ color: "var(--cons)" }}>Reviewer's domain matches the vendor's domain.</p>}
                  </div>
                  {tab === "proofs" && r.proofKey && <a className="btn small secondary" href={`/api/admin/proof/${r.id}`} target="_blank" rel="noopener">Open proof file</a>}
                </div>
                <details style={{ margin: "12px 0" }}>
                  <summary className="small">Read review</summary>
                  <p className="small"><strong>Pros:</strong> {r.pros}</p>
                  <p className="small"><strong>Cons:</strong> {r.cons}</p>
                  <p className="small"><strong>Use:</strong> {r.useCase}</p>
                </details>
                {tab === "proofs" ? <Decide action={moderateProof} id={r.id} rejectLabel="Reject proof" /> : <Decide action={moderateReview} id={r.id} withReason />}
              </div>
            ))}
          </div>
        );
      })()}

      {tab === "listings" && (pendingListings.length ? (
        <div className="stack">
          {pendingListings.map((s) => (
            <div key={s.id} className="panel">
              <h3 style={{ marginBottom: 4 }}>{s.name}</h3>
              <p className="small muted">{s.website} · submitted by {s.submittedBy} · {formatDate(s.createdAt)}</p>
              <p>{s.tagline}</p>
              <details style={{ marginBottom: 12 }}><summary className="small">Description</summary><p className="small">{s.description}</p></details>
              <Decide action={moderateListing} id={s.id} />
            </div>
          ))}
        </div>
      ) : <div className="empty"><p>No listings waiting.</p></div>)}

      {tab === "claims" && (claims.length ? (
        <div className="stack">
          {claims.map(({ c, s, u }) => (
            <div key={c.id} className="panel">
              <h3 style={{ marginBottom: 4 }}><Link href={`/software/${s.slug}`}>{s.name}</Link></h3>
              <p className="small muted">{u.email} wants to manage this listing · listing domain: {s.domain ?? "none set"}</p>
              {c.note && <p className="small">“{c.note}”</p>}
              <Decide action={moderateClaim} id={c.id} />
            </div>
          ))}
        </div>
      ) : <div className="empty"><p>No claims waiting.</p></div>)}

      {tab === "published" && <PublishedReviews rows={await reviewRows(["PUBLISHED"])} />}
      {tab === "directory" && <Directory />}
    </div>
  );
}

function PublishedReviews({ rows }: { rows: ReviewRow[] }) {
  if (!rows.length) return <div className="empty"><p>No published reviews yet.</p></div>;
  return (
    <div className="table-scroll panel">
      <table className="data">
        <thead><tr><th>Software</th><th>Review</th><th>Reviewer</th><th>Checks</th><th>Unpublish</th></tr></thead>
        <tbody>
          {rows.map(({ r, s, u }) => (
            <tr key={r.id}>
              <td>{s.name}</td>
              <td><Link href={`/software/${s.slug}#review-${r.id}`}>{r.title}</Link> ({r.rating}/5)</td>
              <td>{u.email}</td>
              <td>{[r.verifiedEmail && "email", r.verifiedLinkedin && "LinkedIn", r.proofStatus === "APPROVED" && "proof"].filter(Boolean).join(", ")}</td>
              <td>
                <form action={moderateReview} className="inline-form">
                  <input type="hidden" name="id" value={r.id} /><input type="hidden" name="decision" value="reject" />
                  <input type="text" name="reason" placeholder="Reason" aria-label="Reason" />
                  <button className="btn small danger">Unpublish</button>
                </form>
              </td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
}

async function Directory() {
  const rows = await db.select().from(software).where(eq(software.status, "PUBLISHED")).orderBy(software.name);
  return (
    <div className="table-scroll panel">
      <p className="small muted">The domain is used to block self-reviews and to approve vendor claims automatically.</p>
      <table className="data">
        <thead><tr><th>Listing</th><th>Verified domain</th><th>Featured until</th><th>Owner</th></tr></thead>
        <tbody>
          {rows.map((s) => (
            <tr key={s.id}>
              <td><Link href={`/software/${s.slug}`}>{s.name}</Link></td>
              <td>
                <form action={setListingDomain} className="inline-form">
                  <input type="hidden" name="id" value={s.id} />
                  <input type="text" name="domain" defaultValue={s.domain ?? ""} placeholder="example.com" aria-label={`Domain for ${s.name}`} />
                  <button className="btn small secondary">Save</button>
                </form>
              </td>
              <td>
                <form action={setFeaturedManually} className="inline-form">
                  <input type="hidden" name="id" value={s.id} />
                  <input type="date" name="until" defaultValue={s.featuredUntil ? s.featuredUntil.toISOString().slice(0, 10) : ""} aria-label={`Featured until for ${s.name}`} />
                  <button className="btn small secondary">Set</button>
                </form>
              </td>
              <td className="small">{s.claimed ? "Claimed" : "—"}</td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
}
