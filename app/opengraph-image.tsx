import { ogCard, OG_SIZE } from "@/components/og";
export const size = OG_SIZE;
export const contentType = "image/png";
export const alt = "ImmigrationCRM.shop — verified reviews of immigration software";
export default function Image() {
  return ogCard({ eyebrow: "Independent software reviews", title: "Immigration software, reviewed by people who file the cases", subtitle: "CRMs · case management · practice management · AI" });
}
