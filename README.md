# DataCore — Data Refinery Marketplace

A Laravel 13 application: collect survey responses or import CSVs, refine them through a
two-step cleaning pipeline (**Clean 1 free, Clean 2 paid**), and sell the results in a
marketplace. Wallet, escrowed survey rewards, identity verification, admin console, and
English/Indonesian localisation are all included.

---

## Stack

| Layer      | Choice                                            |
| ---------- | ------------------------------------------------- |
| Backend    | Laravel 13, PHP 8.4                               |
| Frontend   | Blade + Alpine.js + Lucide icons                  |
| Styling    | Tailwind CSS 4 via Vite (build step required)     |
| Database   | PostgreSQL in production, MySQL/SQLite locally    |
| Auth       | Session auth + Google OAuth (Socialite)           |
| Payments   | Simulated wallet top-ups (no provider required)   |
| Deployment | Docker → Render                                   |

---

## Layout

```
app/
  Models/            User, Profile, Wallet, PaymentMethod, Category, Collection,
                     Question, Entry, Review, Activity, Verification, Transaction, Purchase
  Services/          CleaningService (CSV → FastAPI), WalletService (atomic credit/debit/settle)
  Http/Requests/     one Form Request per write endpoint — all validation lives here
  Http/Middleware/   SetLocale, EnsureUserIsAdmin, EnsureCanRespondToSurvey,
                     PreventBackHistoryCache
  Http/Controllers/  thin controllers; business rules live in services and models
config/
  datacore.php       cleaning URL, fees, rewards, top-up limits, payment gateway
database/
  migrations/        24 migrations (portable across SQLite / MySQL / PostgreSQL)
  seeders/           DatabaseSeeder (guarded) → DemoSeeder + AdminSeeder
lang/
  id.json            413 keys — full Indonesian coverage of every __() string
resources/views/
  errors/            custom 403 / 404 / 419 / 429 / 500 / 503 pages
docker/
  entrypoint.sh      migrate, guarded seed, cache, then serve
  apache.conf        binds to $PORT, document root at public/
  php.ini            opcache + upload limits
```

---

## Local development

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
composer dev          # serve + queue worker + vite, all at once
```

`composer dev` runs `php artisan serve`, `queue:listen`, and `npm run dev` together.

### Seeded logins

| Account | Email                  | Password   |
| ------- | ---------------------- | ---------- |
| Demo    | `demo@datacore.test`   | `password` |
| Sellers | `maya@`, `bagus@`, `sari@` `datacore.test` | `password` |
| Admin   | `admin@datacore.test`  | `password` |

Override the admin with `ADMIN_EMAIL` / `ADMIN_NAME` / `ADMIN_PASSWORD` in `.env`.

---

## Deploying to Render

The repo ships a `Dockerfile` and a `render.yaml` blueprint, so the whole stack —
web service plus a free PostgreSQL instance — comes up from one file.

### 1. Push to GitHub, then create a Blueprint

In Render: **New → Blueprint**, point it at the repo. It reads `render.yaml` and creates:

- `datacore-db` — free PostgreSQL
- `datacore` — Docker web service, health check on `/up`

`APP_KEY` is generated automatically and `DB_URL` is wired from the database.

### 2. Set the values marked `sync: false`

These are deliberately left blank in the blueprint so no secret is committed:

| Variable                | Needed for                                                |
| ----------------------- | --------------------------------------------------------- |
| `ADMIN_PASSWORD`        | the seeded admin login                                     |
| `GOOGLE_CLIENT_ID`      | Google sign-in                                             |
| `GOOGLE_CLIENT_SECRET`  | Google sign-in                                             |
| `GOOGLE_REDIRECT_URI`   | `https://<your-app>.onrender.com/auth/google/callback`     |

Add the same callback URL to **Authorised redirect URIs** in the Google Cloud console,
otherwise OAuth returns `redirect_uri_mismatch`.

### 3. Deploy

On every boot the entrypoint:

1. binds Apache to Render's `$PORT`
2. links storage
3. runs `php artisan migrate --force`, retrying while the database wakes up
4. runs `php artisan db:seed --force`
5. caches config, routes and views

Seeding is safe to repeat: `DatabaseSeeder` inserts demo data **only when the `users`
table is empty**, and `AdminSeeder` uses `updateOrCreate`. Set `RUN_SEEDERS=false` to
skip it entirely.

### Notes on the free tier

- Free Render PostgreSQL is deleted after 30 days — fine for a semester, not for real use.
- The container filesystem is ephemeral. Uploaded avatars and verification documents in
  `storage/app` are lost on redeploy; the database is not. Point `FILESYSTEM_DISK` at S3
  if uploads need to survive.
- Free web services sleep after inactivity, so the first request after idling is slow.

---

## Wallet top-ups

Top-ups are **simulated** — there is no real payment provider wired in. Choosing an amount
and a channel generates a plausible-looking Virtual Account number, QRIS payload, or
e-wallet number, stores it in the session, and shows the payment instructions. Clicking
*I've paid* credits the wallet and writes a `transactions` row.

That means the whole economy works end to end for a demo without any third-party account
or API key.

---

## The refine flow

- **Clean 1 — free.** Builds a CSV from the collection's entries and POSTs it to
  `CLEANING_API_URL?mode=step1` (PII drop/hash + quality scoring). Results land in each
  entry's `clean1_data`.
- **Clean 2 — paid.** Checks the wallet first; if the balance is under `clean2_fee`
  (Rp 25,000 default) the user is sent to top up. Otherwise the fee is debited and the CSV
  goes to `?mode=full` (dedup, validation, type conversion, normalisation) into `clean2_data`.

Collections over `DATACORE_CLEANING_SYNC_LIMIT` entries (300 by default) are dispatched as a
job rather than processed inline. The Render blueprint sets `QUEUE_CONNECTION=sync` so those
jobs still run immediately — a free Render service has no worker process, and with the
`database` driver the jobs would queue up and never be picked up. If you add a worker
service, switch that back to `database`.

---

## Localisation

Every user-facing string goes through `__()`, including controller flash messages and
validation messages. `lang/id.json` holds the Indonesian side with **100% key coverage**;
English is the source language, so it needs no file.

The locale is a URL segment (`/en/...`, `/id/...`) resolved by `SetLocale`, which also sets
a `URL::defaults` so route helpers keep the current language. The supported list lives in
one place — `config/app.php` → `supported_locales` — and drives the route constraint, the
middleware, and the language switcher.

To re-audit coverage after adding strings, compare the `__()` calls in `app/`,
`routes/` and `resources/` against the keys in `lang/id.json`.

> **Not yet translated:** the in-app user guide (`app/Support/GuideContent.php` and
> `resources/views/guide/index.blade.php`) is authored as English prose rather than
> `__()` keys. Everything else switches language correctly.

---

## Economy

- New accounts start with an empty wallet and top up to buy datasets or run Full Clean.
- Publishing a survey escrows `reward × target × (1 + platform fee)` from the creator's
  wallet; unused reward slots are refunded when the survey ends, is paused, or is deleted.
  The platform fee is never refunded.
- Buying a dataset debits the buyer and credits the seller in one transaction.
- Every movement is recorded in `transactions` with a unique reference.
