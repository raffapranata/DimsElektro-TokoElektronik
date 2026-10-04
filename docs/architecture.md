# Arsitektur & Model Data

Panduan teknis struktur codebase, model, enum, service, dan alur checkout.

## File Structure

```
app/
  Enums/
    OrderStatus.php          # Lifecycle pesanan: Pending → Processing → Shipped → Completed; Cancelled
    PaymentStatus.php        # Lifecycle pembayaran: Pending → Paid/Failed/Expired/Cancelled → Refunded
    UserRole.php             # Admin, Customer
    ProductSort.php          # Enum sort: Newest, PriceLowToHigh, PriceHighToLow, NameAscending
  Exceptions/
    EmptyCartException       # Keranjang kosong saat checkout
    InsufficientStockException # Stok kurang dari quantity
    ProductUnavailableException # Produk dihapus/dinonaktifkan saat checkout
    InvalidOrderStatusTransitionException # Transisi status ilegal
    InvalidPaymentStatusTransitionException # Transisi payment status ilegal
    PaymentGatewayNotConfiguredException # Gateway dipilih tapi tidak dikonfigurasi
    PaymentFailedException   # Gateway gagal proses
  Models/
    User                     # Autentikasi (email, password, role)
    Category                 # Kategori produk (slug unik)
    Product                  # Produk (harga, stok, spek JSON, gambar)
    Order                    # Pesanan customer (snapshot penerima, total)
    OrderItem                # Line item pesanan (snapshot produk_name, price)
    Payment                  # Record pembayaran per order (status, gateway, raw_response)
  Services/
    CartService              # Session cart (product_id => qty)
    CheckoutService          # Transaksi: order + items + stock decrement + payment
    OrderService             # Status transitions + stock restore (cancel)
    OrderNumberGenerator     # INV-YYYYMMDD-XXXXXX unique generator
    ProductImageService      # Upload/delete/replace gambar
  Payment/
    PaymentGatewayInterface  # Kontrak gateway
    PaymentResult            # Output gateway (status, tx_id, redirect_url)
    FakePaymentGateway       # Default: manual transfer, pending
    MidtransPaymentGateway   # Placeholder Midtrans (tidak implementasi tanpa cred)
  Policies/
    OrderPolicy              # view/cancel milik sendiri saja (customer)
  Http/
    Controllers/
      HomeController         # Katalog featured + categories
      ProductController      # Catalog index + detail (filter, sort)
      CartController         # Cart index, add, update qty, remove
      CheckoutController     # Checkout form create + store (place order)
      OrderController        # Customer order history + detail + cancel
      Admin/
        DashboardController  # Stats + low stock + recent orders
        CategoryController   # CRUD kategori
        ProductController    # CRUD produk + toggle active
        OrderController      # List + detail + update status/payment
    Requests/
      StoreCartItemRequest   # product_id, quantity (validate stok)
      UpdateCartItemRequest  # quantity (validate stok)
      CheckoutRequest        # recipient_name, phone, address, notes (validate phone format)
      StoreProductRequest    # name, category_id, brand, price, stock, specs (file)
      UpdateProductRequest   # sama seperti Store
      StoreCategoryRequest   # name, description (slug unique)
      UpdateCategoryRequest  # sama dengan Store (unique ignore current)
      LoginRequest           # email, password (rate limit 5/IP+email)
      RegisterRequest        # name, email, password (role always Customer)
      UpdateOrderStatusRequest # status, payment_status (at least 1)
    Middleware/
      EnsureUserIsAdmin      # 401 jika null user, 403 jika bukan admin
    Concerns/
      CleansQueryString      # Baca query param string; non-string → null
  Providers/
    AppServiceProvider       # View composer (nav categories + cart count)
    PaymentServiceProvider   # Bind PaymentGatewayInterface (fake/midtrans)

database/
  migrations/
    create_categories        # FK RESTRICT (products)
    create_products          # FK RESTRICT (order_items); index is_active+category
    create_orders            # FK RESTRICT (users); index status+created_at
    create_order_items       # FK CASCADE (orders), RESTRICT (products)
    create_payments          # FK RESTRICT (orders); unique order_id
  seeders/
    AdminUserSeeder          # 1 admin (admin@dimselektro.test)
    CustomerSeeder           # 2 customers (budi@, siti@example.com)
    CategorySeeder           # 5 categories
    ProductSeeder            # 13 products (1 out-of-stock)
    OrderSeeder              # 10 orders per customer × status scenario

routes/
  web.php                    # Public: home, catalog, cart, checkout (auth)
  auth.php                   # Register, login, logout
  admin.php                  # Admin panel (auth + admin middleware)

resources/
  views/
    layouts/                 # app (shared header), guest (login/register), admin
    home.blade.php           # Hero + categories + featured products
    products/
      index.blade.php        # Catalog grid (filter + sort)
      show.blade.php         # Product detail + related
    cart/
      index.blade.php        # Cart lines + checkout button
    checkout/
      create.blade.php       # Shipping form preview
    orders/
      index.blade.php        # Customer order history
      show.blade.php         # Order detail + payment status + cancel button
    admin/
      dashboard.blade.php    # Stats + low stock + recent orders
      categories/            # Create/edit/delete categories
      products/              # Create/edit/delete products (image upload)
      orders/                # List + detail + status/payment update form

config/
  app.php                    # Laravel core
  payment.php                # PAYMENT_GATEWAY, MIDTRANS_*
  store.php                  # STORE_LOW_STOCK_THRESHOLD, STORE_SHIPPING_COST

tests/
  Feature/
    AuthenticationTest       # Login, register, role safe
    CatalogTest              # Product listing, detail
    CartTest                 # Add, update, remove, clear
    CheckoutTest             # Place order, validate stock, transaction rollback
    StockConcurrencyTest     # Multiple simultaneous checkouts (oversell prevented)
    CategoryTest             # Admin CRUD
    AdminProductTest         # Admin CRUD + image upload
    OrderTest                # Customer order history + cancel
```

## Model Relationships

```
User (1) ─── (N) Order
User (1) ─── (N) Session (cart)

Category (1) ─── (N) Product

Product (1) ─── (N) OrderItem
Product (1) ─── (N) Cart line (session)

Order (1) ─── (N) OrderItem
Order (1) ─── (1) Payment

Payment (1) ─── (1) Order
```

## Enum: OrderStatus

```php
enum OrderStatus {
  Pending      → Processing, Cancelled
  Processing   → Shipped, Cancelled
  Shipped      → Completed
  Completed    [final]
  Cancelled    [final]
}

isFinal()       // Completed, Cancelled: no transitions allowed
isCancellable() // Can move to Cancelled (Pending, Processing, Shipped)
isOpen()        // NOT isFinal()
```

## Enum: PaymentStatus

```php
enum PaymentStatus {
  Pending      → Paid, Failed, Expired, Cancelled
  Paid         → Refunded
  Failed       → Pending  [retry]
  Expired      → Pending  [retry]
  Cancelled    → Pending  [retry]
  Refunded     [final]
}

isPaid()     // Paid only
isSettled()  // Paid or Refunded
```

## Enum: ProductSort

Allow-listed sort (input safe via enum):

```php
enum ProductSort {
  Newest            → created_at DESC, id DESC
  PriceLowToHigh    → price ASC, id DESC
  PriceHighToLow    → price DESC, id DESC
  NameAscending     → name ASC, id DESC
}

fromRequest(?string) // Unknown → Newest (safe fallback)
```

## Key Database Constraints

| Table | Constraint | Behavior | Reason |
|-------|-----------|----------|--------|
| products | FK category_id | RESTRICT | Kategori tidak bisa dihapus jika masih ada produk |
| order_items | FK order_id | CASCADE | Item otomatis dihapus saat order dihapus |
| order_items | FK product_id | RESTRICT | Produk tidak bisa dihapus jika ada di pesanan (history utuh) |
| orders | FK user_id | RESTRICT | User tidak bisa dihapus jika ada pesanan (history utuh) |
| payments | FK order_id | RESTRICT | Pembayaran tidak bisa orphan |
| payments | UNIQUE order_id | — | Satu pembayaran per pesanan |

## Key Service: CheckoutService

Atomic transaction:

1. **Lock products** (`lockForUpdate` per 5 detik di InnoDB)
2. **Validate stock** per line (dalam lock)
3. **Create order** + order_items (snapshot product_name, price)
4. **Decrement stock** per line
5. **Create payment** (via PaymentGatewayInterface)
6. Commit transaction
7. **Clear session cart** (hanya setelah commit)

Failure → rollback, customer retry.

## Key Service: OrderService

**updateStatus(order, status)**:
- Lock order (prevent race condition)
- Validate transition (enum rule)
- If Cancelled: restore stock + cancel pending payment
- Save new status

**updatePaymentStatus(order, status)**:
- Lock order + payment
- Validate transition (enum rule)
- Update payment.status + paid_at
- Save

## Models: Concerns/HasSlug

Trait untuk `Category` & `Product`:

- Auto-generate slug dari name (trim, lowercase, dash)
- Regenerate saat name berubah
- Enforce unique slug di database (unik index)
- Route key: `getRouteKeyName() → 'slug'` (URL tidak expose id)

## Models: Product Scopes

```php
active()                              // is_active = true
search(?string $term)                 // LIKE name/brand (escaped)
inCategory(?string $slug)             // whereHas category.slug
byBrand(?string $brand)               // brand = $brand
lowStock(int $threshold)              // stock <= $threshold
withStockState(?string, $threshold)   // 'low'/'out'/'available'
withActiveState(?string)              // 'active'/'inactive'
```

## Models: Product Helpers

```php
isInStock()            // stock > 0
imageUrl()             // Storage::disk('public')->url($image)
availableBrands()      // distinct brands (select)
subtotalFor(int $qty)  // price × qty (rounded)
```

## Models: Order Scopes & Helpers

```php
withStatus(OrderStatus|string|null)   // filter by status
search(?string $term)                 // LIKE order_number/recipient_name/user.name+email
canTransitionTo(OrderStatus)          // status.canTransitionTo()
isCancellable()                        // status.isCancellable()
isOpen()                               // status.isOpen()
isPaid()                               // payment?.status === Paid
```

Rute key: `getRouteKeyName() → 'order_number'` (URL: `/orders/INV-20260929-ABC123`).
