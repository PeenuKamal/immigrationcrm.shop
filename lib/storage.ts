import "server-only";
import { promises as fs } from "fs";
import path from "path";
import { randomUUID } from "crypto";

// Proof-of-use files are private: stored outside the public web root and served only to admins.
const DIR = path.resolve(process.env.UPLOAD_DIR || path.join(process.cwd(), "uploads-private"));
export const ALLOWED_PROOF_TYPES = ["image/png", "image/jpeg", "image/webp", "application/pdf"];
export const MAX_PROOF_BYTES = 5 * 1024 * 1024;

const MAGIC: Record<string, (b: Buffer) => boolean> = {
  "image/png": (b) => b.subarray(0, 4).toString("hex") === "89504e47",
  "image/jpeg": (b) => b.subarray(0, 3).toString("hex") === "ffd8ff",
  "image/webp": (b) => b.subarray(0, 4).toString() === "RIFF" && b.subarray(8, 12).toString() === "WEBP",
  "application/pdf": (b) => b.subarray(0, 4).toString() === "%PDF",
};

export async function storeProof(file: File): Promise<string> {
  if (!ALLOWED_PROOF_TYPES.includes(file.type)) throw new Error("Upload a PNG, JPG, WebP or PDF file.");
  if (file.size > MAX_PROOF_BYTES) throw new Error("Keep the file under 5 MB.");
  const buf = Buffer.from(await file.arrayBuffer());
  if (!MAGIC[file.type](buf)) throw new Error("That file doesn't look like a real image or PDF. Try exporting it again.");
  const ext = file.type === "application/pdf" ? "pdf" : file.type.split("/")[1];
  const name = `${randomUUID()}.${ext}`;
  await fs.mkdir(path.join(DIR, "proofs"), { recursive: true });
  await fs.writeFile(path.join(DIR, "proofs", name), buf, { mode: 0o600 });
  return `local:proofs/${name}`;
}

export async function readProof(key: string): Promise<{ body: Buffer; type: string } | null> {
  if (!key.startsWith("local:")) return null;
  const rel = key.slice(6);
  if (!/^proofs\/[0-9a-f-]+\.(png|jpeg|webp|pdf)$/.test(rel)) return null;
  const type = rel.endsWith(".pdf") ? "application/pdf" : `image/${rel.split(".").pop()}`;
  try {
    return { body: await fs.readFile(path.join(DIR, rel)), type };
  } catch {
    return null;
  }
}
