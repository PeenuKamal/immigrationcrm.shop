import { SITE } from "@/lib/site";
import Link from "next/link";
import { notFound } from "next/navigation";
import { eq } from "drizzle-orm";
import { db, software } from "@/db";
import { requireUser } from "@/lib/session";
import { sameOrganisation } from "@/lib/util";
import { claimListing } from "@/app/actions/vendor";

export const metadata = { robots: { index: false } };

export default async function Claim({ params, searchParams }: { params: Promise<{ slug: string }>; searchParams: Promise<{ sent?: string; error?: string }> }) {
  const { slug } = await params;
  const sp = await searchParams;
  const s = await db.query.software.findFirst({ where: eq(software.slug, slug) });
  if (!s) notFound();
  const user = await requireUser(`/software/${slug}/claim`);
  const instant = !!s.domain && sameOrganisation(user.emailDomain, s.domain);

  return (
    <div className="wrap" style={{ maxWidth: 680 }}>
      <p className="small"><Link href={`/software/${slug}`}>Back to {s.name}</Link></p>
      <div className="page-head">
        <h1>Claim {s.name}</h1>
        <p>Claiming lets you edit the listing, reply to reviews and buy featured placement. It doesn't let you edit or remove reviews.</p>
      </div>
      {s.claimed ? (
        <div className="notice warn"><p>This listing is already managed by the vendor. If that's wrong, email {SITE.contactEmail}.</p></div>
      ) : sp.sent ? (
        <div className="notice"><p><strong>Claim request sent.</strong> An editor will confirm you work for {s.vendorName ?? s.name} and email you, usually within one business day.</p></div>
      ) : sp.error === "personal" || !user.isWorkEmail ? (
        <div className="notice warn"><p>Sign in with your company email to claim a listing. <Link href={`/signin?next=/software/${slug}/claim`}>Switch account</Link></p></div>
      ) : (
        <form action={claimListing} className="panel">
          <input type="hidden" name="slug" value={slug} />
          {instant ? (
            <p>Your email ({user.email}) matches {s.domain}, so you can claim this listing right away.</p>
          ) : (
            <>
              <p>
                Your email domain ({user.emailDomain}) {s.domain ? `doesn't match ${s.domain}` : "can't be matched automatically"}, so an editor will check your claim.
              </p>
              <div className="field">
                <label htmlFor="note">How are you connected to {s.name}?</label>
                <textarea id="note" name="note" placeholder="Your role, and the product's website if it's missing" style={{ minHeight: 90 }} />
              </div>
            </>
          )}
          <button className="btn">{instant ? "Claim listing" : "Send claim request"}</button>
        </form>
      )}
    </div>
  );
}
