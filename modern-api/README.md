# Bistro Suite API foundation

Clean Laravel API skeleton, separate from the legacy application at the repository root. The legacy code and planning documents remain untouched.

## Local demo stack

The Compose stack runs Laravel with PostgreSQL and the Mithril/Vite client. Docker Compose starts PostgreSQL, waits for it from the API container, applies migrations, and seeds a fictional bistro menu.

From `cars-admin-api/`:

```sh
docker-compose up --build -d
```

Open the storefront at `http://localhost:5173`; the API health check is `http://localhost:8000/health`. The public route is `GET /api/v1/public/bistros/{slug}/menu`. For example, `demo-bistro` returns the seeded menu. Every product query is scoped through the resolved bistro; inactive bistros return 404, and unavailable products are excluded.

The seed provides 16 available fictional products in five categories, plus one unavailable product used to verify filtering. Six entries feature Nortesantanderean dishes and locally generated illustrative menu photos. Names, descriptions, prices, categories, tags, and illustrations are demonstration content, not imported business records. See `cars-admin-client/modern-web/ASSET_PROVENANCE.md` for asset provenance. Seed uses `firstOrCreate`, so subsequent starts preserve edits to existing demo products; the one-time migration that curates the existing demo menu runs before seeding.

The Compose database and API bind only to localhost. Its credentials and seeded admin account are for local demo use and must not be reused in production. Open `http://localhost:5173/admin` and sign in with `admin@demo-bistro.local` / `BistroDemo-Local-2026!`. The local seeder creates this account only when both `DEMO_ADMIN_EMAIL` and `DEMO_ADMIN_PASSWORD` are set; production uses a different generated password. The panel can list, create, edit, mark available/unavailable, upload an image (5 MB maximum), and delete products. It also shows the 50 most recent orders, refreshes every minute, and permits only pending → confirmed/cancelled and confirmed → delivered/cancelled transitions. Every admin query is scoped to the signed-in user's bistro. Login uses Laravel Sanctum's stateful session and CSRF cookie flow. On Railway, the web service reverse-proxies API requests through the private network so the browser stays on one origin and the API needs no public domain.

The public checkout accepts guest orders with name and phone, optional email, product quantities and item notes, plus delivery or pickup. The authenticated admin changes fulfillment settings at `/admin`: enable either method, set one flat delivery fee in COP, manage up to 100 neighborhood names, and set the pickup address. Delivery orders require a configured neighborhood and address; the API matches neighborhood names case-insensitively and stores the configured spelling. Pickup orders snapshot the configured pickup address. Laravel rechecks each item against the selected bistro's available catalog and stores product name and price snapshots; clients cannot set prices or totals. Each attempt uses a UUID persisted with the current tab's cart and protected by a per-bistro unique database constraint. Repeating the same request returns its existing confirmation, including when concurrent requests race. The order total is product subtotal plus the delivery fee when applicable. The seeded zones, $5,000 COP fee and pickup address are fictional demo settings, not real coverage information. Payment and tax calculation are not configured. Public order creation is rate-limited, and its response reveals only the reference, status, fulfillment method and total.

## Railway

This directory is intended as the Railway service root. It follows the separate backend service layout in `gestion-monitorias/backend/railway.toml`, adapted to current Railpack and Laravel: `railway.toml` runs migrations before deployment, then idempotently seeds the demo menu and administrator before starting the server. It creates the public storage symlink at startup and listens on `PORT` for private traffic from the web service. Configure the API service root directory as `/modern-api` and attach a persistent volume at `/data`; set `APP_PUBLIC_STORAGE_PATH=/data` so product image uploads survive deploys. Connect `DB_URL` to the Railway Postgres service, set production `APP_KEY`, `APP_ENV=production`, and `APP_DEBUG=false`, and provide a unique strong `DEMO_ADMIN_PASSWORD` for the demo administrator. Remove that one-time password variable after confirming the initial seed; if the demo database is reset, add a new password before redeploying to recreate the account.

Railway has announced that its legacy config-as-code files will stop applying to new deployments after 2026-12-01. Before production deployment, migrate these settings to Railway Infrastructure as Code or configure them in the service settings and remove this file. The health check and service root are the settings to preserve.
