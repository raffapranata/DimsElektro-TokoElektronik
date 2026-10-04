# DimsElektro — Toko Elektronik

Aplikasi toko elektronik berbasis **Laravel 13** dengan panel admin dan area pelanggan.
Dibangun sebagai full-stack Laravel + MySQL/MariaDB dengan Blade + Tailwind CSS (Vite),
tanpa JavaScript framework di sisi server.

## Fitur

### Etalase (pelanggan)
- Katalog produk dengan pencarian, filter kategori/merek, dan pengurutan yang di-allow-list
  lewat enum `ProductSort` (tanpa orderBy dari input mentah).
- Detail produk: spesifikasi (JSON), masa garansi, status stok, dan badge stok menipis.
- Keranjang berbasis **session** (driver `database`) — harga selalu diambil ulang dari DB,
  tidak pernah dari input klien.
- Checkout transaksional: validasi stok dengan `lockForUpdate` (anti oversell), snapshot
  harga & nama produk ke `order_items`, nomor pesanan `INV-YYYYMMDD-XXXXXX`.
- Riwayat pesanan milik sendiri (diisolasi lewat `OrderPolicy`), dan pembatalan pesanan
  yang masih terbuka (stok otomatis dikembalikan).

### Panel admin (`/admin`, middleware `auth` + `admin`)
- Dashboard: ringkasan produk, pesanan, pendapatan pesanan selesai, produk stok menipis,
  dan pesanan terbaru.
- CRUD kategori: slug otomatis, unik, tidak dapat dihapus bila masih memiliki produk.
- CRUD produk: spesifikasi dinamis (key/value, maks 30 baris), unggah gambar (validasi
  isi file, bukan nama file), aktif/nonaktif, dan penghapusan yang aman — produk dengan
  riwayat pesanan **dinonaktifkan**, bukan dihapus, agar histori pesanan tetap utuh.
- Manajemen pesanan: ubah status pesanan & status pembayaran sesuai aturan transisi
  di enum; perubahan ilegal ditolak dengan pesan yang jelas.

### Keamanan
- Registrasi selalu menghasilkan peran `Customer`; peran dari klien diabaikan (ada tes).
- Akses `/admin` dijaga middleware `EnsureUserIsAdmin` (403 untuk pelanggan).
- Pelanggan tidak dapat melihat/membatalkan pesanan milik orang lain (policy + tes).
- Query string listing dibersihkan lewat trait `CleansQueryString` (nilai bukan string
  diperlakukan sebagai "tanpa filter").
- LIKE di-escape agar `%`/`_` dari input dicocokkan secara literal.

## Persyaratan

- PHP 8.3+ (ekstensi: `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`; **tanpa** GD —
  upload gambar tervalidasi dari isi file, bukan render)
- Composer 2, Node 20+
- MySQL 8+ atau MariaDB 10.4+

## Instalasi

```bash
composer install
cp .env.example .env
php artisan key:generate

# sesuaikan DB_* di .env, lalu:
php artisan migrate --seed
php artisan storage:link

npm install
npm run build
```

Jalankan server:

```bash
php artisan serve   # http://localhost:8000
```

### Kredensial hasil seeder

| Peran     | Email                    | Password   |
|-----------|--------------------------|------------|
| Admin     | `admin@dimselektro.test` | `password` |
| Pelanggan | `budi@example.com`       | `password` |
| Pelanggan | `siti@example.com`       | `password` |

Seeder membuat 5 kategori, 13 produk (termasuk 1 produk stok habis dan beberapa stok
menipis sebagai contoh badge), dan 10 pesanan sampel di berbagai status.

> **Catatan lingkungan mesin ini:** MariaDB berjalan di ruang pengguna pada **port 3307**
> (bukan 3306) dan PHP memuat ekstensi `pdo_mysql` dari
> `~/.local/share/dimselektro/php-ext/`. Semua perintah PHP/Composer/Artisan pada mesin
> ini harus didahului `source "$HOME/.local/share/dimselektro/env.sh"`.

## Konfigurasi penting

### `.env`

| Variabel                    | Default | Keterangan                                              |
|-----------------------------|---------|---------------------------------------------------------|
| `PAYMENT_GATEWAY`           | `fake`  | `fake` = gateway lokal tanpa panggilan eksternal        |
| `MIDTRANS_*`                | kosong  | Placeholder Midtrans — **sengaja belum dikonfigurasi**; aplikasi tetap berjalan normal dengan `fake` |
| `STORE_LOW_STOCK_THRESHOLD` | `5`     | Ambang badge "stok menipis" di katalog & admin          |
| `FILESYSTEM_DISK`           | `public`| Disk penyimpanan gambar produk (`storage:link`)         |

### Gateway pembayaran (abstraksi)

`PaymentGatewayInterface` di-bind di `App\Providers\PaymentServiceProvider` berdasarkan
`PAYMENT_GATEWAY`: `fake` → `FakePaymentGateway`, `midtrans` → `MidtransPaymentGateway`
(lembar kerja siap pakai), driver tak dikenal → fallback `fake`. Konfigurasi ada di
`config/payment.php`.

## Arsitektur singkat

```
app/
  Enums/         OrderStatus, PaymentStatus, UserRole, ProductSort
                 (aturan transisi status hidup di enum, bukan di controller)
  Exceptions/    InsufficientStock, EmptyCart, ProductUnavailable,
                 Invalid{Order,Payment}StatusTransition, PaymentGatewayNotConfigured,
                 PaymentFailed — semua punya userMessage() berbahasa Indonesia
  Models/        User, Category, Product, Order, OrderItem, Payment
                 + Concerns/HasSlug (slug otomatis & unik, regenerasi saat nama berubah)
  Policies/      OrderPolicy (view/cancel milik sendiri)
  Services/      CartService (session cart), CheckoutService (transaksi + lockForUpdate),
                 OrderService (transisi status, restore stok, batalkan payment pending),
                 ProductImageService, OrderNumberGenerator
  Http/Requests/ Validasi per aksi (Store/UpdateProduct, Store/UpdateCategory,
                 Checkout, Cart, Login, Register, UpdateOrderStatus)
database/
  migrations/    FK RESTRICT ke tabel histori (order_items, payments) agar histori
                 tidak ikut terhapus; payments unik per order
  factories/     User, Category, Product, Order, OrderItem, Payment (faker id_ID)
  seeders/       DatabaseSeeder
resources/views/ layouts/{app,guest,admin}, admin/{dashboard,categories,products,orders},
                 products, cart, checkout, orders, auth + components kecil (badge, money, dll.)
```

Aturan lini masa (ringkas):

- `OrderStatus`: Pending → Processing → Shipped → Completed; Pending/Processing dapat
  Dibatalkan. Status final tidak bisa berubah.
- `PaymentStatus`: Pending → Paid/Failed/Expired/Cancelled; Failed/Expired/Cancelled dapat
  dicoba ulang (→ Pending); Paid → Refunded.
- Membatalkan pesanan (pelanggan atau admin) mengembalikan stok dan membatalkan payment
  yang masih pending; payment yang sudah dibayar tidak disentuh otomatis.

## Pengujian

Suite menggunakan MySQL/MariaDB sungguhan (`dimselektro_test`) — bukan SQLite — karena
tes konkurensi stok memerlukan `FOR UPDATE` InnoDB yang sebenarnya.

```bash
source "$HOME/.local/share/dimselektro/env.sh"   # mesin ini saja
php artisan test
```

Cakupan: autentikasi & registrasi (role aman), katalog, keranjang, checkout
(termasuk assert SQL `for update` dan rollback saat payment gagal via mock gateway),
konkurensi stok (oversell tercegah), CRUD admin kategori & produk (termasuk unggah/
ganti/hapus gambar), pesanan pelanggan (isolasi & pembatalan), pesanan admin
(transisi valid/ilegal), dan unit test generator nomor pesanan.

## Mengakses database (DBeaver / klien MySQL)

| Parameter | Nilai          |
|-----------|----------------|
| Host      | `127.0.0.1`    |
| Port      | `3307`         |
| Database  | `dimselektro`  |
| User      | `dimselektro`  |
| Password  | `dimselektro`  |

Database untuk tes: `dimselektro_test` (dipakai otomatis oleh `php artisan test`).

## Perintah berguna

```bash
php artisan migrate:fresh --seed   # reset + data contoh
php artisan route:list             # semua rute (web, auth, admin)
php artisan test                   # seluruh suite
vendor/bin/pint                    # perapian gaya kode
npm run build                      # build aset Tailwind/Vite
```
