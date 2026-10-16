import Link from "next/link";
import { desc, eq } from "drizzle-orm";
import { db, reviews, software } from "@/db";
import { requireUser } from "@/lib/session";
import { STATUS_COPY } from "@/lib/reviews";
import { uploadProof, updateProfile } from "@/app/actions/reviews";
import { formatDate } from "@/lib/util";

export const metadata = { title: "My account", robots: { index: false } };

const LINKEDIN_MSG: Record<string, [string, string]> = {
  connected: ["", "LinkedIn connected. Any reviews waiting on it are now live."],
  cancelled: ["warn", "LinkedIn connection was cancelled. You can try again any time."],
  expired: ["warn", "That LinkedIn sign-in link expired. Try connecting again."],
  failed: ["error", "LinkedIn didn't confirm your profile. Try again, or upload proof of use instead."],
  "in-use": ["error", "That LinkedIn profile is already linked to another account. Each profile can verify one reviewer."],
  "not-configured": ["warn", "LinkedIn verification isn't switched on yet. Upload proof of use for now."],
};
const PROOF_MSG: Record<string, [string, string]> = {
  uploaded: ["", "Proof received. We'll check it within one business day."],
  missing: ["error", "Choose a file before uploading."],
  invalid: ["error", "Upload a PNG, JPG, WebP or PDF under 5 MB."],
};

export default async function Account({ searchParams }: { searchParams: Promise<Record<string, string | undefined>> }) {
  const sp = await searchParams;
  const user = await requireUser("/account");
  const mine = await db
    .select({ r: reviews, s: { name: software.name, slug: software.slug } })
    .from(reviews)
    .innerJoin(software, eq(software.id, reviews.softwareId))
    .where(eq(reviews.userId, user.id))
    .orderBy(desc(reviews.createdAt));
  const owned = await db.select({ id: software.id }).from(software).where(eq(software.ownerId, user.id));

  const msg = (sp.linkedin && LINKEDIN_MSG[sp.linkedin]) || (sp.proof && PROOF_MSG[sp.proof]) || (sp.saved && ["", "Profile saved."]);
  const submitted = sp.submitted && mine.find((m) => m.r.id === sp.submitted);

  return (
    <div className="wrap" style={{ maxWidth: 860 }}>
      <div className="page-head">
        <h1>My account</h1>
        <p>Signed in as {user.email}.</p>
      </div>
      {msg && <div className={`notice ${msg[0]}`} role="status"><p>{msg[1]}</p></div>}
      {submitted && (
        <div className={`notice${submitted.r.status === "PUBLISHED" ? "" : " warn"}`} role="status">
          <p><strong>Review of {submitted.s.name} received — {STATUS_COPY[submitted.r.status].label.toLowerCase()}.</strong> {STATUS_COPY[submitted.r.status].help}</p>
        </div>
      )}

      <div className="stack">
        <section className="panel" aria-labelledby="ver">
          <h2 id="ver" style={{ fontSize: "var(--step-2)" }}>Your verification</h2>
          <ul className="checklist">
            <li className={user.isWorkEmail ? "done" : ""}>
              <span className="tick">{user.isWorkEmail ? "✓" : ""}</span>
              <div><strong>Work email</strong><div className="small muted">{user.isWorkEmail ? `${user.emailDomain} confirmed ${formatDate(user.emailVerifiedAt)}` : "Personal address — sign in with a work email to review."}</div></div>
              <span />
            </li>
            <li className={user.linkedinVerifiedAt ? "done" : ""}>
              <span className="tick">{user.linkedinVerifiedAt ? "✓" : ""}</span>
              <div><strong>LinkedIn profile</strong><div className="small muted">{user.linkedinVerifiedAt ? `Connected as ${user.linkedinName}` : "Instantly publishes your reviews. We only read your name and LinkedIn ID."}</div></div>
              {!user.linkedinVerifiedAt && <a className="btn small secondary" href="/api/auth/linkedin/start">Connect LinkedIn</a>}
            </li>
            <li className={mine.some((m) => m.r.proofStatus === "APPROVED") ? "done" : ""}>
              <span className="tick">{mine.some((m) => m.r.proofStatus === "APPROVED") ? "✓" : ""}</span>
              <div><strong>Proof of use</strong><div className="small muted">Attached per review. Use it if you'd rather not connect LinkedIn.</div></div>
              <span />
            </li>
          </ul>
        </section>

        <section aria-labelledby="my-reviews">
          <h2 id="my-reviews" style={{ fontSize: "var(--step-2)" }}>My reviews</h2>
          {mine.length === 0 ? (
            <div className="empty"><p>You haven't reviewed anything yet.</p><Link className="btn stamp" href="/software">Find software you use</Link></div>
          ) : (
            <div className="stack">
              {mine.map(({ r, s }) => (
                <div key={r.id} className="panel">
                  <div style={{ display: "flex", justifyContent: "space-between", gap: 16, flexWrap: "wrap" }}>
                    <div>
                      <h3 style={{ marginBottom: 4 }}><Link href={`/software/${s.slug}`}>{s.name}</Link>: {r.title}</h3>
                      <p className="small muted" style={{ margin: 0 }}>Submitted {formatDate(r.createdAt)}</p>
                    </div>
                    <span className={`status ${r.status}`}>{STATUS_COPY[r.status].label}</span>
                  </div>
                  <p className="small" style={{ marginTop: 12 }}>{r.status === "REJECTED" && r.rejectReason ? r.rejectReason : STATUS_COPY[r.status].help}</p>
                  {r.status === "AWAITING_VERIFICATION" && (
                    <form action={uploadProof} className="inline-form">
                      <input type="hidden" name="reviewId" value={r.id} />
                      <label htmlFor={`p-${r.id}`} className="sr-only">Proof of use file</label>
                      <input id={`p-${r.id}`} type="file" name="proof" accept="image/png,image/jpeg,image/webp,application/pdf" />
                      <button className="btn small">Upload proof</button>
                      {!user.linkedinVerifiedAt && <a className="btn small secondary" href="/api/auth/linkedin/start">Or connect LinkedIn</a>}
                    </form>
                  )}
                  {r.proofStatus === "REJECTED" && <p className="small" style={{ color: "var(--cons)" }}>Your last proof couldn't be accepted. Upload a clearer screenshot or an invoice.</p>}
                </div>
              ))}
            </div>
          )}
        </section>

        <section className="panel" aria-labelledby="prof">
          <h2 id="prof" style={{ fontSize: "var(--step-2)" }}>Profile</h2>
          <p className="small muted">Shown on a review only if you tick “Show my name” when writing it.</p>
          <form action={updateProfile}>
            <div className="grid-2">
              <div className="field"><label htmlFor="name">Name</label><input id="name" name="name" defaultValue={user.name ?? ""} /></div>
              <div className="field"><label htmlFor="jobTitle">Job title</label><input id="jobTitle" name="jobTitle" defaultValue={user.jobTitle ?? ""} /></div>
              <div className="field"><label htmlFor="company">Firm</label><input id="company" name="company" defaultValue={user.company ?? ""} /></div>
            </div>
            <button className="btn secondary">Save profile</button>
          </form>
        </section>

        <div style={{ display: "flex", gap: 12, flexWrap: "wrap" }}>
          {owned.length > 0 && <Link className="btn secondary" href="/vendor">Vendor dashboard</Link>}
          <form action="/api/auth/signout" method="post"><button className="btn secondary">Sign out</button></form>
        </div>
      </div>
    </div>
  );
}
