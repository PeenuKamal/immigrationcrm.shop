import { NextResponse, type NextRequest } from "next/server";
import { randomBytes } from "crypto";
import { currentUser } from "@/lib/session";
import { appUrl } from "@/lib/util";

// "Sign In with LinkedIn using OpenID Connect" — gives a verified name, LinkedIn member id and email.
export async function GET(req: NextRequest) {
  const user = await currentUser();
  if (!user) return NextResponse.redirect(`${appUrl()}/signin?next=/account`);
  const clientId = process.env.LINKEDIN_CLIENT_ID;
  if (!clientId) return NextResponse.redirect(`${appUrl()}/account?linkedin=not-configured`);

  const state = randomBytes(16).toString("hex");
  const next = req.nextUrl.searchParams.get("next") || "/account";
  const url = new URL("https://www.linkedin.com/oauth/v2/authorization");
  url.searchParams.set("response_type", "code");
  url.searchParams.set("client_id", clientId);
  url.searchParams.set("redirect_uri", `${appUrl()}/api/auth/linkedin/callback`);
  url.searchParams.set("state", state);
  url.searchParams.set("scope", "openid profile email");

  const res = NextResponse.redirect(url);
  const opts = { httpOnly: true, secure: process.env.NODE_ENV === "production", sameSite: "lax" as const, path: "/", maxAge: 600 };
  res.cookies.set("li_state", state, opts);
  res.cookies.set("li_next", next.startsWith("/") ? next : "/account", opts);
  return res;
}
