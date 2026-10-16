import { SITE } from "@/lib/site";
import Link from "next/link";
import { stripe } from "@/lib/stripe";
import { applyFeaturedPayment } from "@/lib/featured";
import { requireUser } from "@/lib/session";
import { formatDate } from "@/lib/util";

export const metadata = { robots: { index: false } };

export default async function FeaturedSuccess({ searchParams }: { searchParams: Promise<{ session_id?: string }> }) {
  await requireUser("/vendor");
  const { session_id } = await searchParams;
  const client = stripe();
  let result: Awaited<ReturnType<typeof applyFeaturedPayment>> = null;
  let paid = false;
  if (client && session_id) {
    const session = await client.checkout.sessions.retrieve(session_id);
    paid = session.payment_status === "paid";
    if (paid) result = await applyFeaturedPayment(session_id); // the webhook may have done this already
  }
  return (
    <div className="wrap" style={{ maxWidth: 640 }}>
      <div className="page-head"><h1>{paid ? "Payment received" : "Payment pending"}</h1></div>
      <div className="notice">
        <p>
          {paid
            ? result
              ? `${result.software.name} is featured until ${formatDate(result.until)}.`
              : "Your featured placement is active. A receipt is on its way from Stripe."
            : `We haven't received confirmation from Stripe yet. Refresh in a minute; if it doesn't update, email ${SITE.contactEmail}.`}
        </p>
      </div>
      <Link className="btn" href="/vendor">Back to vendor dashboard</Link>
    </div>
  );
}
