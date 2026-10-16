"use client";
import { useActionState } from "react";
import Link from "next/link";
import { requestCode, verifyCode } from "@/app/actions/auth";

export function SignInForms({ step, email, next, resentWait }: { step: "email" | "code"; email: string; next: string; resentWait: boolean }) {
  const [reqState, reqAction, reqPending] = useActionState(requestCode, undefined);
  const [verState, verAction, verPending] = useActionState(verifyCode, undefined);

  if (step === "code") {
    return (
      <>
        {resentWait && <div className="notice warn"><p>A code was sent less than a minute ago. Use that one, or wait a moment to request another.</p></div>}
        <form action={verAction}>
          <input type="hidden" name="email" value={email} />
          <input type="hidden" name="next" value={next} />
          <div className="field">
            <label htmlFor="code">6-digit code</label>
            <input id="code" name="code" inputMode="numeric" autoComplete="one-time-code" maxLength={7} autoFocus
              aria-invalid={!!verState?.error} aria-describedby={verState?.error ? "code-err" : undefined}
              style={{ fontSize: "var(--step-2)", letterSpacing: "0.3em", maxWidth: 220 }} />
            {verState?.error && <p id="code-err" className="hint" style={{ color: "var(--cons)" }}>{verState.error}</p>}
          </div>
          <button className="btn" disabled={verPending}>{verPending ? "Checking…" : "Sign in"}</button>
        </form>
        <form action={reqAction} style={{ marginTop: 24 }}>
          <input type="hidden" name="email" value={email} />
          <input type="hidden" name="next" value={next} />
          <p className="small muted" style={{ marginBottom: 0 }}>
            No email? Check spam, then <button className="linklike" disabled={reqPending}>send a new code</button> or{" "}
            <Link href={`/signin?next=${encodeURIComponent(next)}`}>use a different address</Link>.
          </p>
          {reqState?.error && <p className="hint" style={{ color: "var(--cons)" }}>{reqState.error}</p>}
        </form>
      </>
    );
  }

  return (
    <form action={reqAction}>
      <input type="hidden" name="next" value={next} />
      <div className="field">
        <label htmlFor="email">Work email</label>
        <input id="email" name="email" type="email" autoComplete="email" defaultValue={email} required autoFocus
          aria-invalid={!!reqState?.error} aria-describedby="email-hint" />
        <p id="email-hint" className="hint" style={reqState?.error ? { color: "var(--cons)" } : undefined}>
          {reqState?.error ?? "For example name@yourfirm.com. Personal Gmail, Yahoo and Outlook addresses can't be used."}
        </p>
      </div>
      <button className="btn" disabled={reqPending}>{reqPending ? "Sending…" : "Email me a code"}</button>
    </form>
  );
}
