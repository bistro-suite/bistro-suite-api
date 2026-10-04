# Bistro Suite API

Laravel API for the Bistro Suite demo, deployed at Railway and kept separate from the historical application at the repository root. The legacy code and planning documents remain available as business-flow references.

**Live demo:** [bistro-web-production.up.railway.app](https://bistro-web-production.up.railway.app/). The public menu API is available through the web service's same-origin proxy at [`/api/v1/public/bistros/demo-bistro/menu`](https://bistro-web-production.up.railway.app/api/v1/public/bistros/demo-bistro/menu). The API itself stays private inside Railway.

## Local demo stack

The Compose stack runs Laravel with PostgreSQL and the Mithril/Vite client. Docker Compose starts PostgreSQL, waits for it from the API container, applies migrations, and seeds a fictional bistro menu.

From the API repository root:

```sh
docker-compose up --build -d
```

Open the storefront at `http://localhost:5173`; the API health check is `http://localhost:8000/health`. The public route is `GET /api/v1/public/bistros/{slug}/menu`. For example, `demo-bistro` returns the seeded menu. Every product query is scoped through the resolved bistro; inactive bistros return 404, and unavailable products are excluded.

The seed provides 16 available fictional products in five categories, plus one unavailable product used to verify filtering. Six entries feature Nortesantanderean dishes and locally generated illustrative menu photos. It also creates eight fictional sample orders spanning pending, confirmed, delivered and cancelled states, with both pickup and delivery, multiple items, notes and sample contact details. Their fixed references make the seed idempotent: redeploys neither duplicate them nor overwrite an existing order. Names, descriptions, prices, categories, tags, illustrations and order details are demonstration content, not imported business records. See the [asset provenance notes](https://github.com/bistro-suite/bistro-suite-web/blob/main/modern-web/ASSET_PROVENANCE.md). Product seed uses `firstOrCreate` as well, so subsequent starts preserve edits to existing demo products; the one-time migration that curates the existing demo menu runs before seeding.

The Compose database and API bind only to localhost. Its credentials and seeded admin account are for local demo use and must not be reused in production. Open `http://localhost:5173/admin` and sign in with `admin@demo-bistro.local` / `BistroDemo-Local-2026!`. The local seeder creates this account only when both `DEMO_ADMIN_EMAIL` and `DEMO_ADMIN_PASSWORD` are set; production uses a different generated password. The panel can list, create, edit, mark available/unavailable, upload an image (5 MB maximum), and delete products. It also shows the 50 most recent orders, refreshes every minute, and permits only pending → confirmed/cancelled and confirmed → delivered/cancelled transitions. Every admin query is scoped to the signed-in user's bistro. Login uses Laravel Sanctum's stateful session and CSRF cookie flow. On Railway, the web service reverse-proxies API requests through the private network so the browser stays on one origin and the API needs no public domain.

The public checkout accepts guest orders with name and phone, optional email, product quantities and item notes, plus delivery or pickup. The authenticated admin changes fulfillment settings at `/admin`: enable either method, set one flat delivery fee in COP, manage up to 100 neighborhood names, and set the pickup address. Delivery orders require a configured neighborhood and address; the API matches neighborhood names case-insensitively and stores the configured spelling. Pickup orders snapshot the configured pickup address. Laravel rechecks each item against the selected bistro's available catalog and stores product name and price snapshots; clients cannot set prices or totals. Each attempt uses a UUID persisted with the current tab's cart and protected by a per-bistro unique database constraint. Repeating the same request returns its existing confirmation, including when concurrent requests race. The order total is product subtotal plus the delivery fee when applicable. The seeded zones, $5,000 COP fee and pickup address are fictional demo settings, not real coverage information. Payment and tax calculation are not configured. Public order creation is rate-limited, and its response reveals only the reference, status, fulfillment method and total.

## API feature tests

The Laravel feature suite runs against a dedicated PostgreSQL database so `RefreshDatabase` never migrates or clears the local demo database:

```sh
docker-compose exec db createdb -U bistro bistro_suite_test
docker-compose exec -T -e APP_ENV=testing -e DB_URL= -e DB_DATABASE=bistro_suite_test api composer test
```

Create `bistro_suite_test` once. The suite verifies delivery and pickup totals, server-side price snapshots, idempotent retries, invalid coverage and unavailable products, authenticated order status transitions, read-only demo writes, and repeatable sample-order seeding. Keep `DB_DATABASE=bistro_suite_test`: the test framework migrates this database from scratch. Never point the test command at a production database.

## Railway

The live Railway service uses this directory as its root (`/modern-api`) and follows the backend layout in `gestion-monitorias/backend/railway.toml`, adapted to Laravel and Railpack. On each deployment, `railway-pre-deploy.sh` runs migrations and seeds the fictional menu and demo administrator before the app starts. The start command creates the public storage link and listens on Railway's `PORT`; `/health` is the service health check. The API is private and receives browser requests through the web service's proxy.

The production service connects to Railway Postgres through `DB_URL`, mounts a persistent volume at `/data`, and stores uploaded menu images there using `APP_PUBLIC_STORAGE_PATH=/data`. Production uses its own `APP_KEY` and `DEMO_ADMIN_PASSWORD`; neither is committed here. The demo web client may expose matching credentials publicly, so use a dedicated demo account and enable `ADMIN_DEMO_READ_ONLY=true` on the API. This setting blocks all authenticated admin write routes while leaving reads, sign-in and sign-out available. Keep it false only in environments where administrators should make changes. The predeploy seeder creates or updates the demo account from `DEMO_ADMIN_EMAIL` and `DEMO_ADMIN_PASSWORD`.

The `railway.toml` is the checked-in service configuration. Keep its root directory, pre-deploy command, start command and health check aligned with the Railway service settings. Railway documents pre-deploy commands as running in a separate container with access to environment variables and private networking; do not make that command depend on mounted-volume files. See [Railway pre-deploy command docs](https://docs.railway.com/deployments/pre-deploy-command) and [config-as-code reference](https://docs.railway.com/config-as-code/reference).
