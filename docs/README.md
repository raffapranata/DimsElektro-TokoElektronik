# DimsElektro — Toko Elektronik

Full-stack Laravel 13 e-commerce platform with admin panel. Pixel-art retro aesthetic, atomic checkout transactions, concurrent stock safety.

## Quick Start

```bash
composer install && npm install
cp .env.example .env && php artisan key:generate
php artisan migrate --seed && npm run build
php artisan serve  # http://localhost:8000
```

**Admin**: `admin@dimselektro.test` / `password`  
**Customer**: `budi@example.com` / `password`

Full setup guide: **[installation.md](installation.md)**

## Documentation

| Document | Content |
|----------|---------|
| [installation.md](installation.md) | Step-by-step setup, config, database access |
| [accounts.md](accounts.md) | Seeder credentials, security, rate limits |
| [architecture.md](architecture.md) | File structure, models, services, enums, transactions |
| [design-system.md](design-system.md) | Colors, typography, components, retro aesthetic |

## Core Features

### Customer Etalase

- **Catalog**: Search + filter (category, brand) + sort (newest, price, name)
- **Session cart**: Prices always from DB, tamper-proof
- **Checkout**: Atomic transaction, stock lock (`lockForUpdate`), snapshot prices
- **Order history**: Customer can view & cancel orders in open status
- **Safe sort**: All ordering via allow-listed enum, no SQL injection

### Admin Panel (`/admin`)

- **Dashboard**: Product stats, low-stock alerts, revenue, recent orders
- **Category CRUD**: Slug auto-generated, prevent delete if products exist
- **Product CRUD**: Upload images (validated by content), specs JSON (max 30 rows), toggle active/inactive
- **Order management**: List + detail, update order/payment status with validation
- **Products with history**: Deactivated (not deleted) if they have order items

### Payment Gateway Abstraction

- **Default**: `FakePaymentGateway` — local manual transfer, no network calls
- **Midtrans ready**: Adapter exists, fails loud if Midtrans creds missing (not implemented)
- **Extensible**: New gateways via `PaymentGatewayInterface`

### Security

- Role-based access: Admin middleware enforces 403 for customers on `/admin`
- Self-registration always `Customer` role (input role ignored, tested)
- Query string cleaning: malformed params degrade to "no filter", never 422
- LIKE escape: User input matched literally, `%` and `_` safe
- Stock lock: Concurrent checkouts block at `lockForUpdate`, prevent oversell
- Cart validation: Prices re-read from DB, never from session

## Database Schema

| Table | Purpose | Constraints |
|-------|---------|------------|
| `users` | Authentication (email, password, role) | email unique |
| `categories` | Product categories (slug unique) | name unique, FK products RESTRICT |
| `products` | Catalog (price, stock, specs JSON, image) | slug unique, FK categories RESTRICT |
| `orders` | Customer orders (snapshot recipient, total) | order_number unique, index status+created_at, FK users RESTRICT |
| `order_items` | Line items (snapshot product_name, price) | FK orders CASCADE, FK products RESTRICT |
| `payments` | Payment records per order (status, tx_id, raw_response) | unique order_id, FK orders RESTRICT |
| `sessions` | Cart (session driver database) | session_id, user_id nullable |

See [architecture.md](architecture.md) for full model relationships & scopes.

## Enums (Lifecycle Rules)

### OrderStatus

```
Pending ──→ Processing ──→ Shipped ──→ Completed [final]
  ↓                ↓
  └─ Cancelled [final] ─────────────────────┘
```

Cancellation restores stock + cancels pending payment.

### PaymentStatus

```
Pending ──→ Paid ──→ Refunded [final]
  ↓
  ├─ Failed ──┐
  ├─ Expired ─┼─ retry → Pending
  └─ Cancelled┘
```

Failed/Expired/Cancelled can retry (→ Pending). Paid → Refunded only.

### UserRole

- `Admin`: Access `/admin`, manage catalog & orders
- `Customer`: Browse catalog, place orders, view own orders

Self-registration always Customer. Role guarded from mass assignment.

### ProductSort

Allow-listed sort options (safe enum, not user SQL):
- Newest (default)
- PriceLowToHigh
- PriceHighToLow
- NameAscending

Unknown values silently fallback to Newest.

## Key Services

| Service | Responsibility |
|---------|-----------------|
| `CartService` | Session cart (raw + hydrated + subtotal) |
| `CheckoutService` | Atomic order creation: lock products → validate stock → create order/items → decrement stock → create payment → commit → clear cart |
| `OrderService` | Status transitions (validate + side effects: restore stock, cancel payment) |
| `OrderNumberGenerator` | INV-YYYYMMDD-XXXXXX unique generator (8 attempts) |
| `ProductImageService` | Upload/delete/replace (stored under random dir, client filename untrusted) |

See [architecture.md](architecture.md) for full service documentation.

## Testing

116 tests, real MySQL (not SQLite — concurrent stock tests need `FOR UPDATE`).

```bash
php artisan test           # All tests
php artisan test --filter=CheckoutTest  # Single test class
vendor/bin/phpunit tests/Feature/CheckoutTest.php --filter=testCheckoutRollsBackIfPaymentFails
```

Coverage:
- Authentication & registration (role safe)
- Catalog (search, filter, sort)
- Cart (add, update, remove, prune)
- Checkout (transaction, stock lock, rollback on payment fail)
- Stock concurrency (multiple simultaneous checkouts)
- Admin CRUD (categories, products, orders)
- Order cancellation (stock restore, payment cancel)

## Routes

### Public

```
GET  /                          Home + categories + featured
GET  /products                  Catalog
GET  /products/{slug}           Product detail

GET  /register                  Register form
POST /register                  Create account
GET  /login                     Login form
POST /login                     Authenticate
POST /logout                    Logout
```

### Authenticated (Customer)

```
GET  /cart                      Cart
POST /cart                      Add to cart (throttled 30/min)
PATCH /cart/{product}           Update quantity
DELETE /cart/{product}          Remove line

GET  /checkout                  Checkout form (prune cart)
POST /checkout                  Place order (throttled 10/min)

GET  /orders                    Order history
GET  /orders/{order_number}     Order detail
POST /orders/{order}/cancel     Cancel order (if cancellable)
```

### Admin (`/admin`, middleware `auth` + `admin`)

```
GET  /admin                     Dashboard
GET  /admin/categories          List
GET  /admin/categories/create   Form
POST /admin/categories          Store
GET  /admin/categories/{cat}/edit Edit form
PATCH /admin/categories/{cat}   Update
DELETE /admin/categories/{cat}  Delete

GET  /admin/products            List (search, filter, sort)
GET  /admin/products/create     Form
POST /admin/products            Store + image upload
GET  /admin/products/{prod}/edit Edit form
PATCH /admin/products/{prod}    Update + image replace/delete
DELETE /admin/products/{prod}   Delete (or deactivate if has orders)
PATCH /admin/products/{prod}/status Toggle active/inactive

GET  /admin/orders              List
GET  /admin/orders/{order}      Detail
PATCH /admin/orders/{order}     Update status/payment_status
```

## Middleware

| Middleware | Purpose |
|------------|---------|
| `web` | Session, CSRF, cookies (default Laravel) |
| `guest` | Redirect to home if already auth (register/login) |
| `auth` | Redirect to login if not auth |
| `admin` | `EnsureUserIsAdmin`: 403 if not admin (applied to `/admin` routes) |
| `throttle:30,1` | Cart add: 30 per minute per IP |
| `throttle:10,1` | Checkout: 10 per minute per IP |

Rate limit key in LoginRequest: `email|ip` (prevent account hammer + distributed attack).

## Configuration

| File | Variables |
|------|-----------|
| `.env` | `DB_*`, `PAYMENT_GATEWAY`, `MIDTRANS_*`, `STORE_LOW_STOCK_THRESHOLD`, `STORE_SHIPPING_COST` |
| `config/app.php` | Laravel core (name, timezone, locale) |
| `config/payment.php` | Gateway selection + Midtrans config |
| `config/store.php` | Low stock threshold, shipping cost |

See [installation.md](installation.md) for full list.

## Technology Stack

| Layer | Technology |
|-------|-----------|
| Framework | Laravel 13.33.0 |
| Language | PHP 8.3 |
| Database | MySQL 8+ / MariaDB 10.4+ |
| Frontend | Blade + Tailwind CSS 4 + Vite |
| Styling | Custom design system (retro pixel art) |
| Testing | PHPUnit + feature tests |
| Linting | Laravel Pint |

No JavaScript framework (React/Vue). Blade + Tailwind only.

## Design System

Retro GameBoy aesthetic:
- **Color**: Brand green (emerald), amber accents, cream background
- **Typography**: Press Start 2P (labels), Inter (body)
- **Components**: Sharp 2px borders, 3-6px shadows (lifted), no radius
- **Interactive**: Hover lift, active press, smooth focus ring

Full guide: [design-system.md](design-system.md)

## Useful Commands

```bash
php artisan route:list              # Show all routes
php artisan config:show app.name    # Read config
php artisan migrate --seed          # Fresh DB + data
php artisan test                    # Run test suite
vendor/bin/pint                     # Format code
npm run build                       # Build assets
npm run dev                         # Dev watch mode
```

## Performance Notes

- **N+1 queries**: Caught at dev (Model::shouldBeStrict)
- **Stock lock**: 5ms typical in InnoDB, prevents oversell
- **Cart pricing**: Always from DB, session carries IDs only
- **Image storage**: Public disk with `storage:link`, client filename untrusted
- **Session cart**: Database-backed (persistent across browser close)

## Project Rules (from `.ai/rules`)

Key decisions to maintain:

1. **Products with order history**: Deactivated (not deleted) to preserve order snapshots
2. **Stock restores only on cancel**: Refund is manual admin action, not automatic
3. **Slug route keys**: Categories & products use `slug`, not `id`, in URLs
4. **Session cart trust**: Prices always re-read from DB at cart view & checkout
5. **Allow-listed sort/filter**: Enum-based, never raw user input in ORDER BY
6. **Midtrans optional**: App boots with `fake` gateway, Midtrans never breaks boot
7. **Transaction windows**: Payment gateway called inside lock (default local, no I/O wait)
8. **Rate limits**: Per `email|ip` (prevent both single-account hammer and distributed attacks)

## Contributing

- Follow existing code style (checked via `vendor/bin/pint`)
- Write tests for new features (place in `tests/Feature/`)
- Do not delete code; read [architecture.md](architecture.md) first
- All passwords, API keys in `.env`; never commit to repo

## License

Internal project for DimsElektro (Dims Electronics).
