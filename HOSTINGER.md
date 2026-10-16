# Deploying on Hostinger (Node.js Web App from GitHub)

No terminal needed. About 20 minutes.

Hostinger's Node.js hosting doesn't include PostgreSQL, so the database runs on **Supabase** (free plan is enough to start). The site creates its own tables and adds the 39 starter listings the first time it starts.

---

## 1. Create the database on Supabase

1. Go to **supabase.com** → sign up → **New project**.
2. Name: `immigrationcrm`. Choose a **database password** and save it somewhere safe. Region: pick the one closest to most of your visitors (for example *Mumbai* for India, *East US* for North America).
3. When the project is ready, click **Connect** (top of the dashboard).
4. Under **Connection string**, choose the **Session pooler** tab. Copy the URI. It looks like:
   `postgresql://postgres.abcdefgh:[YOUR-PASSWORD]@aws-0-ap-south-1.pooler.supabase.com:5432/postgres`
5. Replace `[YOUR-PASSWORD]` (including the brackets) with your database password. This full line is your `DATABASE_URL`.

> Use the **Session pooler** string, not "Direct connection" — the direct one only works over IPv6, which Hostinger may not support.
> If your password has symbols like `@ # / ? %`, either choose a password with only letters and numbers, or encode the symbols (`@` → `%40`, `#` → `%23`, `/` → `%2F`, `?` → `%3F`, `%` → `%25`).

## 2. Create the app in Hostinger

1. hPanel → **Websites** → **Add website** → **Node.js Web App** → **Import Git repository**.
2. Connect GitHub and choose **PeenuKamal/immigrationcrm.shop**, branch **main**.
3. Build settings (Hostinger usually detects these — check them):

   | Setting | Value |
   |---|---|
   | Framework | Next.js |
   | Node.js version | 22.x (20.x also works) |
   | Root directory | `/` (leave default) |
   | Build command | `npm run build` |
   | Start command | `npm start` |
   | Package manager | npm |

## 3. Environment variables

On the **Set environment variables** screen, add these keys and values. Values go in exactly as shown — no quotes.

| Key | Value |
|---|---|
| `APP_URL` | `https://immigrationcrm.shop` |
| `DATABASE_URL` | your Supabase Session pooler string from step 1 |
| `SESSION_SECRET` | a long random string (at least 40 characters) — keep it private |
| `ADMIN_EMAILS` | `kamal@thedigisparrow.com` |
| `CONTACT_EMAIL` | `kamal@thedigisparrow.com` |
| `SMTP_HOST` | `smtp.hostinger.com` |
| `SMTP_PORT` | `465` |
| `SMTP_USER` | `otp@immigrationcrm.shop` |
| `SMTP_PASS` | the password of the `otp@immigrationcrm.shop` mailbox |
| `EMAIL_FROM` | `ImmigrationCRM.shop <otp@immigrationcrm.shop>` |
| `INDEXNOW_KEY` | a random 32-character string of 0–9 and a–f |
| `FEATURED_PRICE_CENTS` | `9900` (= $99/month) |
| `FEATURED_CURRENCY` | `usd` (or `inr` with `799900` = ₹7,999) |
| `REQUIRE_ADMIN_APPROVAL` | `false` |

Add these later, when you set up each service (see [DEPLOY.md](DEPLOY.md) steps 8–11 for where to get them), then redeploy:

| Key | From |
|---|---|
| `LINKEDIN_CLIENT_ID`, `LINKEDIN_CLIENT_SECRET` | LinkedIn developer app (redirect URL `https://immigrationcrm.shop/api/auth/linkedin/callback`) |
| `STRIPE_SECRET_KEY`, `STRIPE_WEBHOOK_SECRET` | Stripe dashboard (webhook URL `https://immigrationcrm.shop/api/stripe/webhook`) |
| `GOOGLE_SITE_VERIFICATION` | Google Search Console → HTML tag → the `content` value |
| `BING_SITE_VERIFICATION` | Bing Webmaster Tools → meta tag → the `content` value |

Click **Finish** / **Deploy**.

## 4. Connect the domain

In the app's dashboard → **Domains** → connect **immigrationcrm.shop** (and `www`). Hostinger sets the DNS and HTTPS automatically when the domain is in the same account.

Keep the existing **MX, SPF, DKIM and DMARC** DNS records — they make `otp@immigrationcrm.shop` work. In hPanel → **Emails → immigrationcrm.shop**, check that email authentication (SPF, DKIM, DMARC) shows as active, otherwise login codes may land in spam.

## 5. Check it works

1. Open **https://immigrationcrm.shop** — you should see the directory with 39 listings.
2. Go to **/signin**, enter `kamal@thedigisparrow.com`, enter the code from your inbox.
3. **Moderation** appears in the menu.

If something's wrong, open the app's **Runtime logs** in hPanel:

| Log message | Fix |
|---|---|
| `[setup] Database ready` | All good |
| `[setup] Database setup failed` | `DATABASE_URL` is wrong — check the password and that you used the Session pooler string |
| `Invalid login` / `535` | `SMTP_PASS` is wrong |
| No email arrives, no error | Check spam; confirm SPF/DKIM in step 4 |

## Updating the site

Push changes to the `main` branch on GitHub, then click **Redeploy** in hPanel (or enable automatic deployments). Database changes are applied automatically when the site starts.

## Good to know

- **Backups:** Supabase → Database → Backups. The free plan keeps daily backups for a short period; upgrade for point-in-time recovery once you have real reviews.
- **Free plan pausing:** Supabase pauses free projects after a week with no database activity. Normal site traffic keeps it active; if it ever pauses, click **Restore** in Supabase.
- **Proof-of-use uploads** are stored privately in the database, so redeploys never lose them.
