# Deploying immigrationcrm.shop

Two ways to run it:

- **Option A — Hostinger Node.js Web App** (deploy from GitHub in hPanel, no terminal). The database comes from Supabase, because Hostinger's web and cloud plans don't include PostgreSQL. See [HOSTINGER.md](HOSTINGER.md).
- **Option B — your own VPS** (Ubuntu + Nginx + PM2, everything on one server) — the rest of this file.

---

# Option B: VPS

This assumes a Linux VPS (for example a Hostinger VPS) running **Ubuntu 22.04 or 24.04**, with the domain **immigrationcrm.shop** managed in Hostinger's hPanel. Time needed: about 45 minutes. Commands are copy-paste; run them over SSH.

---

## Step 1 — Point the domain at the server

In hPanel → **Domains → immigrationcrm.shop → DNS / Nameservers → DNS records**:

| Type | Name | Points to | TTL |
|---|---|---|---|
| A | `@` | your VPS IP address | 3600 |
| A | `www` | your VPS IP address | 3600 |

**Don't delete the MX, SPF (TXT), DKIM or DMARC records** — those make `otp@immigrationcrm.shop` work.

While you're there, make sure email authentication is on, or login codes will land in spam. In hPanel → **Emails → immigrationcrm.shop → Email authentication / DNS settings**, confirm that SPF, DKIM and DMARC all show as active (Hostinger can add them for you). If DMARC is missing, add a TXT record named `_dmarc` with value `v=DMARC1; p=quarantine; rua=mailto:kamal@thedigisparrow.com`.

## Step 2 — Install the software on the server

```bash
ssh root@YOUR_SERVER_IP

# System updates and basic tools
apt update && apt upgrade -y
apt install -y git nginx postgresql ufw curl

# Node.js 22 LTS
curl -fsSL https://deb.nodesource.com/setup_22.x | bash -
apt install -y nodejs
npm install -g pm2

# Firewall: allow SSH and web traffic only
ufw allow OpenSSH && ufw allow 'Nginx Full' && ufw --force enable
```

## Step 3 — Create the database

Pick a strong password and use it in place of `CHOOSE_A_DB_PASSWORD` below.

```bash
sudo -u postgres psql -c "CREATE USER immigrationcrm WITH PASSWORD 'CHOOSE_A_DB_PASSWORD';"
sudo -u postgres psql -c "CREATE DATABASE immigrationcrm OWNER immigrationcrm;"
```

## Step 4 — Download the code and configure it

```bash
mkdir -p /var/www && cd /var/www
git clone https://github.com/PeenuKamal/immigrationcrm.shop.git immigrationcrm
cd immigrationcrm

cp .env.example .env
nano .env
```

Fill in `.env` (save with Ctrl+O, Enter, then Ctrl+X):

| Setting | What to put |
|---|---|
| `DATABASE_URL` | `postgres://immigrationcrm:CHOOSE_A_DB_PASSWORD@localhost:5432/immigrationcrm` |
| `SESSION_SECRET` | output of `openssl rand -base64 48` |
| `SMTP_PASS` | the password of the `otp@immigrationcrm.shop` mailbox |
| `INDEXNOW_KEY` | output of `openssl rand -hex 16` |
| Everything else | already filled in, or add later (LinkedIn, Stripe, Google, Bing — steps 8–11) |

Hostinger email settings are already set: `smtp.hostinger.com`, port `465`, SSL, user `otp@immigrationcrm.shop`.

```bash
chmod 600 .env
```

## Step 5 — Build and start

```bash
npm ci
npm run build        # on first start the site creates its tables and adds the 39 starter listings
pm2 start ecosystem.config.cjs
pm2 save
pm2 startup          # run the command it prints, so the site restarts after a reboot
```

Check it: `curl -I http://localhost:3000` should show `200 OK`.

## Step 6 — Connect the web server and add HTTPS

```bash
cat > /etc/nginx/sites-available/immigrationcrm <<'NGINX'
server {
    listen 80;
    server_name immigrationcrm.shop www.immigrationcrm.shop;
    client_max_body_size 8M;

    location / {
        proxy_pass http://127.0.0.1:3000;
        proxy_http_version 1.1;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "upgrade";
    }
    location /_next/static/ {
        proxy_pass http://127.0.0.1:3000;
        expires 365d;
        add_header Cache-Control "public, max-age=31536000, immutable";
    }
}
NGINX
ln -sf /etc/nginx/sites-available/immigrationcrm /etc/nginx/sites-enabled/
rm -f /etc/nginx/sites-enabled/default
nginx -t && systemctl reload nginx

# Free HTTPS certificate, auto-renewing
apt install -y certbot python3-certbot-nginx
certbot --nginx -d immigrationcrm.shop -d www.immigrationcrm.shop --redirect -m kamal@thedigisparrow.com --agree-tos -n
```

Search engines need one main address. Send `www` to the bare domain:

```bash
cat > /etc/nginx/conf.d/www-redirect.conf <<'NGINX'
server {
    listen 443 ssl;
    server_name www.immigrationcrm.shop;
    ssl_certificate /etc/letsencrypt/live/immigrationcrm.shop/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/immigrationcrm.shop/privkey.pem;
    return 301 https://immigrationcrm.shop$request_uri;
}
NGINX
nginx -t && systemctl reload nginx
```

Open **https://immigrationcrm.shop** — the site is live.

## Step 7 — Test sign-in and become admin

1. Go to **https://immigrationcrm.shop/signin** and enter `kamal@thedigisparrow.com`.
2. The code arrives from `otp@immigrationcrm.shop`. Enter it.
3. **Moderation** now appears in the menu.

No email? Run `pm2 logs immigrationcrm --lines 50` to see the error. The usual causes are a wrong `SMTP_PASS` or SPF/DKIM not yet active (step 1).

---

## Step 8 — LinkedIn verification

1. Go to **linkedin.com/developers** → **Create app**. It needs a LinkedIn company page (create one for ImmigrationCRM.shop if needed).
2. **Products** tab → request **Sign In with LinkedIn using OpenID Connect** (approved instantly).
3. **Auth** tab → add the redirect URL `https://immigrationcrm.shop/api/auth/linkedin/callback`.
4. Copy the **Client ID** and **Client Secret** into `.env`, then restart (see "Updating" below).

## Step 9 — Stripe for featured listings

1. **dashboard.stripe.com** → Developers → API keys → copy the **Secret key** into `STRIPE_SECRET_KEY`.
2. Developers → Webhooks → **Add endpoint** `https://immigrationcrm.shop/api/stripe/webhook`, event `checkout.session.completed` → copy the **Signing secret** into `STRIPE_WEBHOOK_SECRET`.
3. Set your price: `FEATURED_PRICE_CENTS=9900` is $99/month. For rupees, use `FEATURED_CURRENCY=inr` and `FEATURED_PRICE_CENTS=799900` (₹7,999).

## Step 10 — Google (Search + Gemini)

1. **search.google.com/search-console** → Add property → **URL prefix** `https://immigrationcrm.shop` → **HTML tag** → copy only the `content="…"` value into `GOOGLE_SITE_VERIFICATION`, restart, click **Verify**.
2. **Sitemaps** → submit `sitemap.xml`.
3. Test a listing in **search.google.com/test/rich-results**, e.g. `https://immigrationcrm.shop/software/maple-crm`.

## Step 11 — Bing (Bing, ChatGPT search, Copilot, DuckDuckGo)

1. **bing.com/webmasters** → **Import from Google Search Console** (fastest), or add the site and paste the meta tag value into `BING_SITE_VERIFICATION`.
2. Submit `https://immigrationcrm.shop/sitemap.xml`.
3. IndexNow is already built in: with `INDEXNOW_KEY` set, every new review or listing is reported to Bing within seconds. Check that `https://immigrationcrm.shop/indexnow.txt` shows your key.

---

## Updating the site later

```bash
cd /var/www/immigrationcrm
git pull
npm ci
npm run build
pm2 reload immigrationcrm    # database changes are applied automatically on start
```

After editing `.env` only: `pm2 reload immigrationcrm --update-env`.

## Backups

Add a nightly database backup (keeps 14 days):

```bash
mkdir -p /var/backups/immigrationcrm
( crontab -l 2>/dev/null; echo '30 2 * * * sudo -u postgres pg_dump immigrationcrm | gzip > /var/backups/immigrationcrm/db-$(date +\%F).sql.gz && find /var/backups/immigrationcrm -mtime +14 -delete' ) | crontab -
```

Proof-of-use uploads are stored in the database, so this backup covers them. Also keep a copy of your `.env`.

---

## Docker (alternative to PM2 on a VPS)

If you prefer Docker, install Docker on the server, then:

```bash
git clone https://github.com/PeenuKamal/immigrationcrm.shop.git immigrationcrm && cd immigrationcrm
cp .env.example .env && nano .env            # also add a line: POSTGRES_PASSWORD=choose-one
docker compose up -d --build     # tables and starter listings are created automatically
```

Then follow step 6 (Nginx and HTTPS) as above — the app listens on `127.0.0.1:3000`.
