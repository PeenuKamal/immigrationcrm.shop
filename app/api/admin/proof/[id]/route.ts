import { NextResponse } from "next/server";
import { eq } from "drizzle-orm";
import { db, reviews } from "@/db";
import { currentUser, isAdmin } from "@/lib/session";
import { readProof } from "@/lib/storage";

export async function GET(_: Request, { params }: { params: Promise<{ id: string }> }) {
  const user = await currentUser();
  if (!isAdmin(user)) return new NextResponse("Not found", { status: 404 });
  const { id } = await params;
  const r = await db.query.reviews.findFirst({ where: eq(reviews.id, id) });
  if (!r?.proofKey) return new NextResponse("Not found", { status: 404 });
  const file = await readProof(r.proofKey);
  if (!file) return new NextResponse("Not found", { status: 404 });
  return new NextResponse(new Uint8Array(file.body), {
    headers: { "Content-Type": file.type, "Cache-Control": "private, no-store", "X-Content-Type-Options": "nosniff", "Content-Disposition": "inline" },
  });
}
