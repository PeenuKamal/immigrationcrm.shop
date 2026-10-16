import "server-only";
import { eq } from "drizzle-orm";
import { db, proofFiles } from "@/db";

export const ALLOWED_PROOF_TYPES = ["image/png", "image/jpeg", "image/webp", "application/pdf"];
export const MAX_PROOF_BYTES = 5 * 1024 * 1024;

const MAGIC: Record<string, (b: Buffer) => boolean> = {
  "image/png": (b) => b.subarray(0, 4).toString("hex") === "89504e47",
  "image/jpeg": (b) => b.subarray(0, 3).toString("hex") === "ffd8ff",
  "image/webp": (b) => b.subarray(0, 4).toString() === "RIFF" && b.subarray(8, 12).toString() === "WEBP",
  "application/pdf": (b) => b.subarray(0, 4).toString() === "%PDF",
};

/** Saves a private proof-of-use file in the database and returns its key. */
export async function storeProof(file: File): Promise<string> {
  if (!ALLOWED_PROOF_TYPES.includes(file.type)) throw new Error("Upload a PNG, JPG, WebP or PDF file.");
  if (file.size > MAX_PROOF_BYTES) throw new Error("Keep the file under 5 MB.");
  const buf = Buffer.from(await file.arrayBuffer());
  if (!MAGIC[file.type](buf)) throw new Error("That file doesn't look like a real image or PDF. Try exporting it again.");
  const [row] = await db.insert(proofFiles).values({ mime: file.type, size: buf.length, data: buf }).returning({ id: proofFiles.id });
  return `db:${row.id}`;
}

export async function readProof(key: string): Promise<{ body: Buffer; type: string } | null> {
  if (!key.startsWith("db:")) return null;
  const row = await db.query.proofFiles.findFirst({ where: eq(proofFiles.id, key.slice(3)) });
  return row ? { body: Buffer.from(row.data), type: row.mime } : null;
}
