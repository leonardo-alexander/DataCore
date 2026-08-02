# DataCore — Data Refinery Marketplace

A Laravel 12 application: collect survey responses or import CSVs, refine them through a
two-step cleaning pipeline (**Clean 1 free, Clean 2 paid**), and sell the results in a
marketplace. Wallet, escrowed survey rewards, identity verification, admin console, and
English/Indonesian localisation are all included.

---

## Tech stack

### Backend

| Package                     | Version  | Role                                              |
| --------------------------- | -------- | ------------------------------------------------- |
| PHP                         | 8.4      | Runtime (`composer.lock` pins ≥ 8.4.1)            |
| `laravel/framework`         | ^12.0    | MVC framework, Eloquent ORM, queues, validation    |
| `laravel/socialite`         | ^5.29    | Google OAuth sign-in                               |
| `laravel/tinker`            | ^2.10    | REPL for poking at models                          |

### Frontend

| Package                     | Version  | Role                                              |
| --------------------------- | -------- | ------------------------------------------------- |
| Blade                       | —        | Server-rendered templating                         |
| `tailwindcss`               | ^4.0     | Utility-first CSS                                  |
| `@tailwindcss/vite`         | ^4.0     | Tailwind's Vite plugin (no PostCSS config needed)  |
| `vite`                      | ^8.0     | Asset bundler and dev server                       |
| `laravel-vite-plugin`       | ^3.1     | Manifest wiring between Vite and Blade             |
| `alpinejs`                  | ^3.15    | Client-side interactivity (dropdowns, modals)      |
| `@alpinejs/collapse`        | ^3.15    | Accordion transitions                              |
| `lucide`                    | ^1.23    | Icon set                                           |
| `@fontsource/*`             | latest   | Self-hosted Inter, Space Grotesk, JetBrains Mono   |

Fonts are imported at the top of `resources/css/app.css` and bundled into `public/build` by
Vite, so there is no runtime call to a font CDN. The families are wired to Tailwind through
`--font-sans`, `--font-display` and `--font-mono` in the `@theme` block of the same file.

### Data & infrastructure

| Piece                       | Choice                                            |
| --------------------------- | ------------------------------------------------- |
| Database (development)      | **MySQL 8**                                       |
| Database (production)       | **PostgreSQL 16** on Render                       |
| Sessions / cache / queue    | Database-backed (no Redis needed)                 |
| File storage                | Local disk (`storage/app`)                        |
| Web server                  | Apache 2.4 + mod_php, opcache enabled             |
| Container                   | Multi-stage Docker: `node:22` → `composer:2` → `php:8.4-apache` |
| PHP extensions              | `pdo_mysql`, `pdo_pgsql`, `opcache`, `zip`, `intl` |
| Hosting                     | Render (Docker web service + managed Postgres)    |

### Tooling & external services

| Piece                       | Role                                              |
| --------------------------- | ------------------------------------------------- |
| `pestphp/pest` ^4.7         | Test runner                                       |
| `laravel/pint` ^1.27        | Code style                                        |
| `laravel/pail` ^1.2         | Live log tailing in development                    |
| `nunomaduro/collision`      | Readable CLI error output                          |
| `fakerphp/faker`            | Seed data generation                               |
| `concurrently`              | Runs server + queue + Vite from one command        |
| Cleaning pipeline           | External FastAPI service (`CLEANING_API_URL`)      |
| Wallet top-ups              | Simulated in-app — no payment provider             |

---

## Layout

```
app/
  Models/            User, Profile, Wallet, PaymentMethod, Category, Collection,
                     Question, Entry, Review, Activity, Verification, Transaction, Purchase
  Services/          CleaningService (CSV → FastAPI), WalletService (atomic credit/debit)
  Http/Requests/     one Form Request per write endpoint — all validation lives here
  Http/Middleware/   SetLocale, EnsureUserIsAdmin, EnsureCanRespondToSurvey,
                     PreventBackHistoryCache
  Http/Controllers/  thin controllers; business rules live in services and models
config/
  datacore.php       cleaning URL, fees, rewards, top-up limits, seeded admin
database/
  migrations/        28 migrations, schema-builder only — run on MySQL and PostgreSQL alike
  seeders/           DatabaseSeeder (guarded) → DemoSeeder + AdminSeeder
lang/
  id.json            408 keys — full Indonesian coverage of every __() string
resources/views/
  errors/            custom 403 / 404 / 419 / 429 / 500 / 503 pages
docker/
  entrypoint.sh      migrate, guarded seed, cache, then serve
  apache.conf        binds to $PORT, document root at public/
  php.ini            opcache + upload limits
```

---

## Two databases: MySQL locally, PostgreSQL in production

This project deliberately runs on **MySQL during development** and **PostgreSQL once
deployed**. Render's free tier offers managed Postgres but no MySQL, while MySQL is what
Herd/XAMPP/Laragon give you out of the box locally — so rather than fight either, the app
is written to run on both.

That works because nothing is tied to a specific engine:

- All schema changes go through Laravel's **schema builder**, never raw `CREATE TABLE`.
- All queries go through **Eloquent / the query builder**, so identifiers and placeholders
  are quoted per-driver.
- The one query that genuinely differs between engines — grouping entries by calendar day
  for the analytics chart — is isolated in `Collection::entriesPerDay()`, which picks the
  right expression per driver (`date_format` on MySQL, `to_char` on Postgres,
  `strftime` on SQLite).

**If you add a raw query, put it behind a method that branches on
`getConnection()->getDriverName()`,** the same way `entriesPerDay()` does — otherwise it
will pass locally and 500 in production.

> Worth knowing: Postgres is stricter than MySQL. It rejects `GROUP BY` columns that aren't
> aggregated or grouped, and it is case-sensitive about quoted identifiers. If something
> works locally but breaks on Render, a raw SQL string is the first place to look.

---

## Local development

**1 — Create the MySQL database and user.** Matching the defaults in `.env.example`:

```sql
CREATE DATABASE datacore_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'datacore_admin'@'localhost' IDENTIFIED BY '';
GRANT ALL PRIVILEGES ON datacore_db.* TO 'datacore_admin'@'localhost';
FLUSH PRIVILEGES;
```

**2 — Install and boot.**

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

The relevant `.env` block, already set in `.env.example`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=datacore_db
DB_USERNAME=datacore_admin
DB_PASSWORD=
```

### Optional: test against Postgres before deploying

Worth doing once if you touch queries or migrations, since production is Postgres:

```bash
docker run -d --name dc-pg -p 5432:5432 \
  -e POSTGRES_USER=datacore -e POSTGRES_PASSWORD=secret -e POSTGRES_DB=datacore \
  postgres:16-alpine
```

Then point `.env` at it and re-run the migrations:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=datacore
DB_USERNAME=datacore
DB_PASSWORD=secret
```

```bash
php artisan migrate:fresh --seed
```

`php artisan db:show` confirms which engine you are actually connected to.

### Seeded logins

| Account | Email                  | Password   |
| ------- | ---------------------- | ---------- |
| Demo    | `demo@datacore.test`   | `password` |
| Sellers | `maya@`, `bagus@`, `sari@` `datacore.test` | `password` |
| Admin   | `admin@datacore.test`  | `password` |

Override the admin with `ADMIN_EMAIL` / `ADMIN_NAME` / `ADMIN_PASSWORD` in `.env`.

> The `password` default for the admin applies **only outside production**. In production,
> if `ADMIN_PASSWORD` is unset, `AdminSeeder` generates a random one and prints it in the
> deploy log rather than shipping a guessable admin account.

---

## Deploying to Render

The repo ships a `Dockerfile` and a `render.yaml` blueprint, so the whole stack —
web service plus a free PostgreSQL instance — comes up from one file.

### 1. Push to GitHub, then create a Blueprint

In Render: **New → Blueprint**, point it at the repo. It reads `render.yaml` and creates:

- `datacore-db` — free PostgreSQL 16, region `singapore`
- `datacore` — Docker web service, same region, health check on `/up`

`APP_KEY` is generated automatically and `DB_URL` is wired from the database.

**The engine switch happens entirely through environment variables** — no code or migration
changes. The blueprint sets `DB_CONNECTION=pgsql` and injects `DB_URL` from the managed
database, overriding the `mysql` defaults you use locally. Your local `.env` is never
deployed (it is in both `.gitignore` and `.dockerignore`).

Both services must stay in the **same region**: Render's internal connection string only
works within a region, so a mismatch means the app can never reach the database.

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
