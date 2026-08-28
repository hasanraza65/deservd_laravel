# DESERV'D — Backend & Admin (Laravel 10)

Backend/API and admin system for the DESERV'D storefront. **Separate from the
React frontend** (`../deservd-frontend`) — this app never renders or imports
any React code, and the React app talks to it exclusively through
`routes/api.php`.

## Stack

Laravel 10 · PHP 8.3 · MySQL · Sanctum (token auth) · Blade + Tailwind 4 +
Alpine.js (admin panel only) · Chart.js (dashboard)

## Setup

```bash
composer install
npm install
npm run build          # compiles the admin panel's CSS/JS
php artisan migrate --seed
php artisan serve       # http://127.0.0.1:8000
```

No `storage:link` step — product images are written directly under
`public/images/products` (a real directory, not a symlink target). See
"Known limitations" below for why.

`.env` is already configured for the XAMPP MySQL instance described in the
brief (`cookies_site` on `127.0.0.1:3306`, root, no password). `.env.example`
mirrors it with the secrets stripped, for reference.

## Logging in

**Admin panel** (session-based, Blade UI): `http://127.0.0.1:8000/admin`
**API** (token-based, for the React frontend): `POST /api/v1/auth/login`

```
Email:    admin@mail.com
Password: f17@AYDS
```

Seeded by `AdminUserSeeder`, which is idempotent (`updateOrCreate` keyed on
email) — re-running `db:seed` never creates a duplicate admin.

## What's real vs. demo data

- **Real catalogue**: `CategorySeeder` and `ProductSeeder` seed the actual
  8-product DESERV'D lineup (matching the React frontend's mock catalogue),
  plus `BoxOptionSeeder` and `ShippingMethodSeeder` for real store config.
  These always run.
- **Demo data**: `CustomerSeeder` (12 fake customers) and `DemoOrderSeeder`
  (10 orders built by calling the real `OrderService` — the same code path a
  live checkout uses — so they exercise real pricing/inventory/order-number
  logic, not hand-inserted rows). These only run in `local`/`testing`
  environments and are clearly separated in `DatabaseSeeder`.
- **No fake reviews are ever seeded**, per the brief.

## Architecture

```
app/
  Enums/          OrderStatus, PaymentStatus, FulfillmentStatus, ProductType,
                  ProductStatus, CouponType, ReviewStatus, UserRole, ...
  Models/         Eloquent models + relationships
  Services/       CheckoutPricingService, OrderService, InventoryService,
                  CouponService, ProductImageService, DashboardAnalyticsService
  Http/
    Controllers/
      Api/V1/         Public + customer JSON API
      Api/V1/Admin/    Admin JSON API (role-gated)
      Admin/           Blade admin panel controllers (session-gated)
    Requests/       Form Request validation, one per action
    Resources/      API Resources — the {success, message, data} envelope
  Policies/       OrderPolicy, AddressPolicy, ReviewPolicy (ownership checks)
  Exceptions/     Domain exceptions (InsufficientStockException, etc.),
                  all rendered through the same JSON envelope
  Support/Money.php   Cents ⇄ dollars conversion (see below)
```

**Two admin surfaces, one set of services.** The Blade admin panel
(`Http/Controllers/Admin/*`, session auth) and the JSON admin API
(`Http/Controllers/Api/V1/Admin/*`, Sanctum token auth) both call the same
Form Requests and Services, so business logic exists in exactly one place.

**Money is stored as integer cents**, never floats — `orders.total`,
`products.price`, etc. Eloquent attribute casts on each model convert
transparently to/from decimal dollars at the boundary, so nothing outside
`app/Support/Money.php` needs to think about cents. This is what makes
discount/tax/total arithmetic exact rather than float-drift-prone.

**The backend is the source of truth for pricing.** `CheckoutPricingService`
recomputes subtotal, discount, shipping and tax from the database on every
checkout and every cart-preview call — it never trusts a price, quantity, or
total from the request body. `OrderService` wraps order creation in a DB
transaction that locks every touched product row (`lockForUpdate()`) before
checking stock, so two simultaneous checkouts can't both oversell the last
unit. See `OrderService::createFromCheckout()`.

**Build-a-Box** is enforced server-side in the same place: a box's flavour
quantities must sum to exactly its size, checked both at validation time
(`StoreOrderRequest`, for a fast field-level error) and again during pricing
(`CheckoutPricingService`, the actual authority — the validation check is a
convenience, not the guarantee).

## API

Versioned under `/api/v1`. Full route list: `php artisan route:list --path=api`.

Every response follows:
```json
{ "success": true, "message": "...", "data": {} }
{ "success": false, "message": "...", "errors": {} }
```
enforced globally in `app/Exceptions/Handler.php` — validation errors, domain
exceptions (insufficient stock, invalid coupon, invalid box selection),
framework HTTP exceptions (404, 429 rate-limit, etc.) and truly unexpected
errors all render through this one envelope. In production
(`APP_DEBUG=false`) unexpected-error messages are generic; the real message
only shows with debug on.

Auth is **token-based Sanctum** (`Authorization: Bearer <token>`), not
cookie/session SPA auth — simpler for a frontend on a different origin/port,
no CSRF dance required. `/api/v1/checkout` and `/api/v1/cart/calculate` are
intentionally public (guest checkout support) but still resolve an
authenticated user if a token is sent, via `auth('sanctum')->user()` — see
the comment on `OrderController::store()` for why `$request->user()` alone
would not work on those two routes.

## Admin panel

Plain Blade + Tailwind + a little Alpine for the sidebar/dropdown — no SPA
framework, per the brief. Sidebar: Dashboard, Orders, Products, Categories,
Customers, Coupons, Reviews, Inventory, Settings. The order detail page
mirrors a real bakery fulfilment workflow (Confirmed → Preparing → Baked →
Packaged → Shipped → Delivered, cancel/refund handled separately from that
progression). Dashboard charts are bundled via Vite (`resources/js/dashboard.js`),
not a CDN `<script>` tag — this runs on a local XAMPP machine that may have no
internet access, and a silently-failed CDN script previously left the chart
blank with no visible error.

## Known limitations / flags for the next stage

- **Laravel 10 is past its security-support window** as of this project's
  build date, and `composer audit` reports an unpatched CRLF-injection CVE in
  the built-in `email` validation rule with no 10.x backport
  (GHSA-5vg9-5847-vvmq). The brief asked for Laravel 10 by name, so that's
  what's installed; upgrading to 11/12 is a bigger, separate decision. The
  app never hand-builds raw email headers anywhere (all mail goes through
  Laravel's Mail/Notification abstractions), which is what makes this CVE
  exploitable — so exposure here is low, but it's worth knowing about.
- `npm audit` flags a moderate advisory in Vite's dev server (`esbuild`,
  dev-only, not present in the built assets) — irrelevant to `npm run build`
  output, only relevant if `npm run dev` is exposed to an untrusted network.
- Tax is a flat percentage (`tax_rate_percent` setting, defaults to 0%) —
  no jurisdiction-aware tax logic. Free shipping threshold is a single global
  setting (`free_shipping_threshold_cents`), matching the frontend's existing
  behaviour.
- Email notifications (order confirmation, status changed, etc.) are not
  wired up yet — `MAIL_MAILER=log` so nothing is sent, but the
  Notifications/Mail architecture is standard Laravel, so this is a matter of
  adding Notification classes and dispatching them from `OrderService`, not
  restructuring anything.
- Product images are stored on the `product_images` disk (config/filesystems.php),
  which writes directly under `public/images/products` — a real directory the
  webserver already serves, not a `storage:link` symlink (per explicit
  instruction — no symlink to lose or forget to re-create on a fresh clone).
  `ProductImageService` is the only place that touches disk paths, so swapping
  to S3 later is a filesystems.php config change, not a code change.
