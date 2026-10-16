import Link from "next/link";
import { eq } from "drizzle-orm";
import { db, software } from "@/db";
import { currentUser } from "@/lib/session";
import { isFeatured, formatDate } from "@/lib/util";

export const metadata = { title: "For vendors" };

export default async function VendorHome() {
  const user = await currentUser();
  const mine = user ? await db.select().from(software).where(eq(software.ownerId, user.id)) : [];
  return (
    <div className="wrap" style={{ maxWidth: 860 }}>
      <div className="page-head">
        <h1>For vendors</h1>
        <p>Keep your listing accurate, reply to verified reviews, and reach firms that are actively comparing tools.</p>
      </div>
      {mine.length > 0 ? (
        <ul className="listing-rows">
          {mine.map((s) => (
            <li key={s.id} className="panel" style={{ display: "flex", justifyContent: "space-between", gap: 16, flexWrap: "wrap", alignItems: "center" }}>
              <div>
                <h3 style={{ marginBottom: 4 }}>{s.name}</h3>
                <p className="small muted" style={{ margin: 0 }}>
                  <span className={`status ${s.status}`}>{s.status === "PUBLISHED" ? "Live" : s.status === "PENDING" ? "In review" : "Not published"}</span>{" "}
                  {s.reviewCount} verified reviews{isFeatured(s.featuredUntil) ? ` · Featured until ${formatDate(s.featuredUntil)}` : ""}
                </p>
              </div>
              <Link className="btn secondary" href={`/vendor/${s.id}`}>Manage</Link>
            </li>
          ))}
        </ul>
      ) : (
        <div className="stack">
          <div className="panel">
            <h2 style={{ fontSize: "var(--step-2)" }}>What vendors can do</h2>
            <ul>
              <li><strong>List for free.</strong> Every immigration, legal and AI tool belongs in the directory.</li>
              <li><strong>Reply to reviews.</strong> Your public response appears under each review.</li>
              <li><strong>Get featured.</strong> Featured listings show first in their categories and are marked with a gold edge. Featuring never changes ratings or which reviews appear.</li>
            </ul>
            <p className="muted">Vendors can't edit, hide or remove reviews, and staff can't review their own product.</p>
            <div style={{ display: "flex", gap: 12, flexWrap: "wrap" }}>
              <Link className="btn" href="/list-your-software">List your software</Link>
              <Link className="btn secondary" href="/software">Claim an existing listing</Link>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
