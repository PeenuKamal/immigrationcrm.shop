import type { Metadata } from "next";
import { SignInForms } from "./forms";

export const metadata: Metadata = { title: "Sign in", robots: { index: false } };

export default async function SignIn({ searchParams }: { searchParams: Promise<{ step?: string; email?: string; next?: string; resent?: string }> }) {
  const sp = await searchParams;
  return (
    <div className="wrap" style={{ maxWidth: 560 }}>
      <div className="page-head">
        <h1>{sp.step === "code" ? "Check your inbox" : "Sign in with your work email"}</h1>
        <p>
          {sp.step === "code"
            ? `We sent a 6-digit code to ${sp.email}. It expires in 10 minutes.`
            : "We'll email you a one-time code. No password needed. Your work address is how we confirm you're a practitioner."}
        </p>
      </div>
      <div className="panel">
        <SignInForms step={sp.step === "code" ? "code" : "email"} email={sp.email ?? ""} next={sp.next ?? "/account"} resentWait={sp.resent === "wait"} />
      </div>
    </div>
  );
}
