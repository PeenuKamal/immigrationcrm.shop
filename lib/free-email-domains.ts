// Personal mailbox providers. Reviews need a work address, so these are refused.
export const FREE_EMAIL_DOMAINS = new Set([
  "gmail.com", "googlemail.com", "yahoo.com", "yahoo.co.in", "yahoo.co.uk", "yahoo.ca", "ymail.com",
  "rocketmail.com", "hotmail.com", "hotmail.co.uk", "hotmail.ca", "outlook.com", "outlook.in", "live.com",
  "live.ca", "msn.com", "aol.com", "icloud.com", "me.com", "mac.com", "proton.me", "protonmail.com",
  "pm.me", "gmx.com", "gmx.net", "gmx.de", "mail.com", "zoho.com", "zohomail.com", "zohomail.in",
  "yandex.com", "yandex.ru", "mail.ru", "rediffmail.com", "tutanota.com", "tuta.io", "fastmail.com",
  "hey.com", "qq.com", "163.com", "126.com", "sina.com", "inbox.com", "mailinator.com",
  "guerrillamail.com", "10minutemail.com", "tempmail.com", "temp-mail.org", "sharklasers.com",
  "yopmail.com", "trashmail.com", "dispostable.com", "getnada.com", "maildrop.cc",
]);

export function isFreeEmailDomain(domain: string) {
  return FREE_EMAIL_DOMAINS.has(domain.toLowerCase());
}
