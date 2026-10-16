import Link from "next/link";
import { currentUser } from "@/lib/session";
import { allCategoriesWithCounts } from "@/lib/queries";
import { ListingForm } from "@/components/ListingForm";
import { submitListing } from "@/app/actions/vendor";

export const metadata = { title: "List your software", description: "Add your immigration software to the directory for free." };

export default async function ListYourSoftware({ searchParams }: { searchParams: Promise<{ queued?: string }> }) {
  const sp = await searchParams;
  const user = await currentUser();
  const cats = await allCategoriesWithCounts();
  return (
    <div className="wrap" style={{ maxWidth: 820 }}>
      <div className="page-head">
        <h1>List your software</h1>
        <p>Listing is free. If your work email is on the product's domain, your listing goes live immediately and you manage it from the vendor dashboard.</p>
      </div>
      {sp.queued ? (
        <div className="notice">
          <p><strong>{sp.queued} is in the review queue.</strong> Your email domain doesn't match the product's website, so an editor will check it before it's published. This usually takes one business day.</p>
        </div>
      ) : !user ? (
        <div className="panel">
          <h2 style={{ fontSize: "var(--step-2)" }}>First, sign in with your company email</h2>
          <p className="muted">We use it to confirm you work for the vendor. Already listed? Find your product and choose “Claim this listing” instead.</p>
          <div style={{ display: "flex", gap: 12, flexWrap: "wrap" }}>
            <Link className="btn" href="/signin?next=/list-your-software">Sign in to continue</Link>
            <Link className="btn secondary" href="/software">Find an existing listing</Link>
          </div>
        </div>
      ) : !user.isWorkEmail ? (
        <div className="notice warn"><p>You're signed in with a personal address. <Link href="/signin?next=/list-your-software">Sign in with your company email</Link> to list software.</p></div>
      ) : (
        <>
          <p className="small muted">Check the <Link href="/software">directory</Link> first — if your product is already there, claim it rather than adding it twice.</p>
          <ListingForm action={submitListing} cats={cats} cta="Submit listing" />
        </>
      )}
    </div>
  );
}
