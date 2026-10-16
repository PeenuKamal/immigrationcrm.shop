import { NextResponse, type NextRequest } from "next/server";
import { eq } from "drizzle-orm";
import { db, users } from "@/db";
import { currentUser } from "@/lib/session";
import { appUrl } from "@/lib/util";
import { refreshUserReviews } from "@/lib/reviews";

export async function GET(req: NextRequest) {
  const base = appUrl();
  const fail = (reason: string) => NextResponse.redirect(`${base}/account?linkedin=${reason}`);
  const user = await currentUser();
  if (!user) return NextResponse.redirect(`${base}/signin?next=/account`);

  const code = req.nextUrl.searchParams.get("code");
  const state = req.nextUrl.searchParams.get("state");
  if (req.nextUrl.searchParams.get("error")) return fail("cancelled");
  if (!code || !state || state !== req.cookies.get("li_state")?.value) return fail("expired");

  const tokenRes = await fetch("https://www.linkedin.com/oauth/v2/accessToken", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: new URLSearchParams({
      grant_type: "authorization_code",
      code,
      redirect_uri: `${base}/api/auth/linkedin/callback`,
      client_id: process.env.LINKEDIN_CLIENT_ID!,
      client_secret: process.env.LINKEDIN_CLIENT_SECRET!,
    }),
  });
  if (!tokenRes.ok) return fail("failed");
  const { access_token } = (await tokenRes.json()) as { access_token: string };

  const infoRes = await fetch("https://api.linkedin.com/v2/userinfo", { headers: { Authorization: `Bearer ${access_token}` } });
  if (!infoRes.ok) return fail("failed");
  const info = (await infoRes.json()) as { sub: string; name?: string; email?: string; picture?: string };

  // One LinkedIn profile can only vouch for one reviewer account.
  const taken = await db.query.users.findFirst({ where: eq(users.linkedinSub, info.sub) });
  if (taken && taken.id !== user.id) return fail("in-use");

  const [updated] = await db
    .update(users)
    .set({
      linkedinSub: info.sub,
      linkedinName: info.name ?? null,
      linkedinEmail: info.email ?? null,
      linkedinPicture: info.picture ?? null,
      linkedinVerifiedAt: new Date(),
      name: user.name ?? info.name ?? null,
    })
    .where(eq(users.id, user.id))
    .returning();
  await refreshUserReviews(updated);

  const next = req.cookies.get("li_next")?.value || "/account";
  const res = NextResponse.redirect(`${base}${next}${next.includes("?") ? "&" : "?"}linkedin=connected`);
  res.cookies.delete("li_state");
  res.cookies.delete("li_next");
  return res;
}
