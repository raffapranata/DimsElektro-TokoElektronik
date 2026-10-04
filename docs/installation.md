# Instalasi DimsElektro

Panduan setup dari nol sampai jalan.

## Prasyarat

| Kebutuhan | Versi |
|-----------|-------|
| PHP | 8.3+ (ekstensi: `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`) |
| Composer | 2.x |
| Node | 20+ |
| MySQL | 8+ atau MariaDB 10.4+ |

GD **tidak** dibutuhkan. Upload gambar tervalidasi dari isi file, bukan render.

## Langkah Instalasi

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

## Konfigurasi `.env` Penting

| Variabel | Default | Keterangan |
|----------|---------|------------|
| `DB_CONNECTION` | `mysql` | Hanya MySQL/MariaDB. SQLite tidak didukung (tes konkurensi butuh `FOR UPDATE` InnoDB) |
| `DB_TEST_DATABASE` | `dimselektro_test` | Dipakai otomatis oleh `php artisan test` |
| `SESSION_DRIVER` | `database` | Keranjang session tersimpan di DB |
| `PAYMENT_GATEWAY` | `fake` | `fake` = gateway lokal tanpa panggilan eksternal |
| `MIDTRANS_*` | kosong | Placeholder Midtrans — sengaja belum dikonfigurasi; aplikasi tetap jalan normal dengan `fake` |
| `STORE_LOW_STOCK_THRESHOLD` | `5` | Ambang badge "stok menipis" |
| `STORE_SHIPPING_COST` | `0` | Ongkir flat, default gratis |
| `FILESYSTEM_DISK` | `public` | Disk gambar produk (`storage:link`) |

## Catatan Lingkungan Mesin Ini

MariaDB jalan di ruang pengguna pada **port 3307** (bukan 3306). Semua perintah PHP/Composer/Artisan harus didahului:

```bash
source "$HOME/.local/share/dimselektro/env.sh"
```

## Perintah Berguna

```bash
php artisan migrate:fresh --seed   # reset + data contoh
php artisan route:list             # semua rute (web, auth, admin)
php artisan test                   # seluruh suite (116 tes)
vendor/bin/pint                    # perapian gaya kode
npm run build                      # build aset Tailwind/Vite
```

## Akses Database (DBeaver / klien MySQL)

| Parameter | Nilai |
|-----------|-------|
| Host | `127.0.0.1` |
| Port | `3307` |
| Database | `dimselektro` |
| User | `dimselektro` |
| Password | `dimselektro` |

Database tes: `dimselektro_test` (dipakai otomatis oleh `php artisan test`).
