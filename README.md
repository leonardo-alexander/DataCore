# DataCore — Data Refinery Marketplace

A full-stack Laravel 12 application: collect survey/CSV data, refine it through a two-step
cleaning pipeline (**Clean 1 free, Clean 2 paid**), and sell the results in a marketplace.
Backend, frontend, wallet, and the cleaning-API integration are all included.

The frontend uses the Tailwind Play CDN + Alpine.js + Lucide icons, so there is **no build
step** — no `npm install`, no Vite. Just drop the files in, migrate, and serve.

---

## What's inside

```
app/
  Models/            User, Profile, Wallet, PaymentMethod, Category, Collection,
                     Question, Entry, Review, Activity, Verification, Transaction, Purchase
  Services/          CleaningService (CSV → FastAPI), WalletService (atomic credit/debit)
  Exceptions/        InsufficientBalanceException
  Providers/         AppServiceProvider (@rupiah directive + topbar view composer)
  Http/Controllers/  Auth, Dashboard, Marketplace, Collection, Entry, Upload,
                     Clean1, Clean2, Purchase, Wallet, Transaction, PaymentMethod,
                     Profile, Verification, Activity, Review
database/
  migrations/        13 migrations (matches the ERD, SQLite-friendly)
  seeders/           DatabaseSeeder + DemoSeeder (full demo dataset)
config/
  datacore.php       cleaning URL, fees, reward, top-up limits
routes/
  web.php            all named routes, guest + auth middleware groups
resources/views/     layouts, partials (sidebar/topbar/flash), dashboard, marketplace,
                     collections, entries, wallet, transactions, profile, verification,
                     purchases, auth
```

---

## Install into a fresh Laravel 12 project

1. **Create a Laravel 12 app** (skip if you already have one):
   ```bash
   composer create-project laravel/laravel datacore
   cd datacore
   ```

2. **Copy these folders over** the generated ones, merging where prompted:
   `app/`, `database/`, `config/datacore.php`, `routes/web.php`, `resources/views/`.

   > Note on the `User` model: this package ships a conventional `app/Models/User.php`
   > with the `is_admin` cast and all relationships. If you keep your own User model,
   > just add the relationships and `'is_admin' => 'boolean'` cast from the included one.

3. **Add to `.env`** (the cleaning API is the only required line):
   ```env
   CLEANING_API_URL=https://leonardo-alexander-data-cleaning.hf.space/process

   # optional — these have sensible defaults in config/datacore.php
   DATACORE_CLEAN2_FEE=25000
   DATACORE_ENTRY_REWARD=2000
   DATACORE_MIN_TOPUP=10000
   DATACORE_MAX_TOPUP=10000000
   ```
   The default DB is SQLite (Laravel 12 default). To use it, create the file and point `.env`:
   ```bash
   touch database/database.sqlite
   ```
   ```env
   DB_CONNECTION=sqlite
   ```

4. **Migrate and seed**:
   ```bash
   php artisan migrate --seed
   ```

5. **Link storage** (profile avatars + verification uploads use the `public` disk):
   ```bash
   php artisan storage:link
   ```

6. **Run**:
   ```bash
   php artisan serve
   ```

### Demo login
```
Email:    demo@datacore.test
Password: password
```
Other seeded sellers (`maya@`, `bagus@`, `sari@` `datacore.test`) also use `password`.

---

## How the refine flow works

- **Clean 1 — free.** Owner opens their dataset → *Run Clean 1*. The app builds a CSV from
  the collection's entries and POSTs it to `CLEANING_API_URL?mode=step1` (PII drop/hash +
  quality scoring). Results are stored in each entry's `clean1_data`.
- **Clean 2 — paid.** *Run Clean 2* first checks the wallet. If the balance is below the
  `clean2_fee` (Rp 25,000 by default) the user is redirected to the wallet to top up. Otherwise
  the fee is debited, the CSV is sent to `?mode=full` (dedup, validation, type conversion,
  normalization), and results land in `clean2_data`.

The cleaning calls go to **your deployed FastAPI pipeline**, so they work wherever the app can
reach that URL. Point `CLEANING_API_URL` at any compatible `/process` endpoint to swap it out.

## UX highlights
- **Auto-advancing carousel** of featured (trending) datasets on the marketplace, with dots + arrows.
- **Toast feedback on every action** — a top-right success/error toast fires from session flash
  and validation errors, auto-dismissing after a few seconds.
- **Live wallet balance + notifications** in the topbar (driven by a view composer).
- Raw / Clean 1 / Clean 2 preview tabs on each dataset; CSV export of the cleanest version.

## Economy
- New users get a **Rp 50,000 welcome bonus**. Contributing an entry pays **Rp 2,000**.
- Buying a dataset debits the buyer and credits the seller in one transaction.
- Every money movement is recorded in `transactions` with a unique reference and shows up in
  the wallet and transactions pages.

## Key routes
`dashboard` · `marketplace.index` / `marketplace.show` · `collections.*` (CRUD + `export`,
`import`, `clean1`, `clean2`) · `entries.create` / `entries.store` · `purchases.index` /
`purchases.store` · `reviews.store` · `wallet.index` / `wallet.topup` · `payment-methods.*` ·
`transactions.index` · `profile.*` · `verification.*` · `activities.read`.
