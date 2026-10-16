export function LogoBox({ name, url, large }: { name: string; url?: string | null; large?: boolean }) {
  const initials = name
    .replace(/[^A-Za-z0-9 ]/g, "")
    .split(" ")
    .filter(Boolean)
    .slice(0, 2)
    .map((w) => w[0])
    .join("")
    .toUpperCase();
  return (
    <div className={`logo-box${large ? " lg" : ""}`} aria-hidden="true">
      {url ? <img src={url} alt="" /> : initials}
    </div>
  );
}
