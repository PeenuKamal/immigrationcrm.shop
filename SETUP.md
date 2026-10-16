# Put immigrationcrm.shop online — 4 steps

Everything happens in Hostinger's hPanel and your browser. No coding, no terminal.

> Use the **normal website hosting** for immigrationcrm.shop — **not** "Node.js Web App". If you started a Node.js app, cancel or delete it first.

---

## Step 1 — Create a database (2 minutes)

1. hPanel → **Websites** → **immigrationcrm.shop** → **Manage**.
2. Left menu → **Databases** → **MySQL Databases**.
3. Fill in:
   - **Database name:** `immigrationcrm`
   - **Username:** `admin`
   - **Password:** make one up (letters and numbers) and write it down
4. Click **Create**.

Hostinger adds a code at the start, like `u123456789_immigrationcrm` and `u123456789_admin`. **Write down the full names exactly as shown in the list** — you'll need them in step 3.

## Step 2 — Copy the website from GitHub (2 minutes)

1. Still in **Manage** → left menu → **Advanced** → **GIT**.
2. Fill in:
   - **Repository:** `https://github.com/PeenuKamal/immigrationcrm.shop.git`
   - **Branch:** `main`
   - **Directory:** leave empty
3. Click **Create**, then click **Deploy** next to the repository.

If Hostinger says the folder isn't empty: open **Files → File Manager → public_html**, delete the default files there (like `default.php` or `index.html`), and click **Deploy** again.

## Step 3 — Open the setup page (2 minutes)

1. Go to **https://immigrationcrm.shop/install.php**
2. Fill in the form:
   - the **database name, username and password** from step 1
   - your admin email: `kamal@thedigisparrow.com`
   - the email password for `otp@immigrationcrm.shop`
3. Click **Set up my site**.

The page creates everything, adds the 39 starter listings, and switches itself off.

## Step 4 — Sign in as admin

1. Go to **https://immigrationcrm.shop/signin**
2. Enter `kamal@thedigisparrow.com` and type the code from your inbox.
3. **Moderation** appears in the menu. That's your control panel.

**Your site is live.**

---

## Later, when you're ready

Everything is in **Moderation → Settings** — paste the codes there and click **Save settings**:

| To turn on… | Where to get the codes |
|---|---|
| **LinkedIn verification** | linkedin.com/developers → Create app → Products → *Sign In with LinkedIn using OpenID Connect*. Add the redirect URL shown in Settings. Copy *Client ID* and *Client secret*. |
| **Paid featured listings** | dashboard.stripe.com → Developers → API keys (*Secret key*). Then Developers → Webhooks → add the URL shown in Settings, event `checkout.session.completed` → copy the *Signing secret*. |
| **Google** | search.google.com/search-console → add `https://immigrationcrm.shop` → *HTML tag* → copy only the code inside `content="…"`. Then submit `sitemap.xml`. |
| **Legal details** for the Terms and Privacy Policy | Fill in the *Legal details* section: business name, registered address, governing law, courts, Grievance Officer and data location. Until then, those spots show as highlighted blanks. Have your lawyer approve both pages. |
| **Bing** (also powers ChatGPT search and Copilot) | bing.com/webmasters → *Import from Google Search Console*, or paste the meta-tag code in Settings. Submit `sitemap.xml`. |

Use **Send me a test email** at the bottom of Settings to check that login emails work.

## Updating the site

When the code on GitHub changes: hPanel → **Advanced → GIT** → **Deploy**. Your data and settings are kept, and database changes apply automatically.

## If something goes wrong

| What you see | What to do |
|---|---|
| Setup page says it can't connect to the database | Copy the database name and username again from **Databases → MySQL Databases** — including the `u123…_` part — and check the password. |
| Setup page says something is missing | **Advanced → PHP Configuration** → choose PHP 8.2 or newer. |
| Login code email doesn't arrive | Check spam. In **Moderation → Settings**, re-enter the email password and click **Send me a test email**. In hPanel → **Emails**, make sure SPF and DKIM show as active. |
| Site shows a security warning | hPanel → **Security → SSL** → install the free SSL certificate for immigrationcrm.shop. |
| Setup page says "already set up" | That's normal after step 3. To change settings, use **Moderation → Settings**. |
