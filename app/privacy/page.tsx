import { SITE } from "@/lib/site";
export const metadata = { title: "Privacy" };
export default function Privacy() {
  return (
    <div className="wrap prose">
      <h1>Privacy</h1>
      <p className="muted">Have this reviewed by a lawyer for the markets you serve before launch.</p>
      <h2>What we collect</h2>
      <ul>
        <li>Your work email, to sign you in and confirm where you work.</li>
        <li>If you connect LinkedIn: your name, LinkedIn member ID, LinkedIn email and profile photo. We never post to LinkedIn.</li>
        <li>Proof-of-use files you upload. They're stored privately and seen only by moderators.</li>
        <li>The reviews you write and the profile details you choose to add.</li>
      </ul>
      <h2>What we publish</h2>
      <p>Your review text, ratings, role, firm size and usage duration. Your name and firm only if you choose to show them. Never your email.</p>
      <h2>Your choices</h2>
      <p>Email {SITE.contactEmail} to get a copy of your data, or to delete your account and reviews.</p>
    </div>
  );
}
