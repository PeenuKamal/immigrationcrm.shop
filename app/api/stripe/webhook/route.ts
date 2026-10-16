import { NextResponse, type NextRequest } from "next/server";
import { stripe } from "@/lib/stripe";
import { applyFeaturedPayment } from "@/lib/featured";

export async function POST(req: NextRequest) {
  const client = stripe();
  const secret = process.env.STRIPE_WEBHOOK_SECRET;
  if (!client || !secret) return NextResponse.json({ error: "Stripe not configured" }, { status: 503 });
  const sig = req.headers.get("stripe-signature");
  const body = await req.text();
  let event;
  try {
    event = client.webhooks.constructEvent(body, sig!, secret);
  } catch {
    return NextResponse.json({ error: "Bad signature" }, { status: 400 });
  }
  if (event.type === "checkout.session.completed" && event.data.object.payment_status === "paid") {
    await applyFeaturedPayment(event.data.object.id);
  }
  return NextResponse.json({ received: true });
}
