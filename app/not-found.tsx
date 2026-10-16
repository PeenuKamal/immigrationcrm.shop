import Link from "next/link";
export default function NotFound() {
  return (
    <div className="wrap" style={{ maxWidth: 640 }}>
      <h1>Page not found</h1>
      <p>The listing may have been renamed or removed. Search the directory to find it.</p>
      <Link className="btn" href="/software">Browse software</Link>
    </div>
  );
}
