# Akun & Kredensial

Kredensial hasil seeder untuk development.

## Akun Seeder

| Peran | Email | Password | Catatan |
|-------|-------|----------|---------|
| Admin | `admin@dimselektro.test` | `password` | Andi Pratama — akses `/admin` |
| Pelanggan | `budi@example.com` | `password` | Budi Santoso — etalase & pesanan |
| Pelanggan | `siti@example.com` | `password` | Siti Rahmawati — etalase & pesanan |

Password trivial intentional: seeder development-only, tidak pernah production.

## Registrasi Baru

Self-registration (`/register`) **selalu** membuat akun Customer. Peran dari input diabaikan; hanya admin yang bisa membuat akun baru via database/seeder.

Tes: `RegisteredUserController` memastikan `role` tidak bisa ditimpa di request.

## Data Sampel

Seeder membuat:

- **5 kategori**: Laptop, Monitor, Smartphone, Keyboard, Mouse
- **13 produk**: 12 aktif + stok bervariasi, 1 out-of-stock (Razer DeathAdder V3)
  - 3 laptop
  - 3 monitor
  - 3 smartphone
  - 2 keyboard
  - 2 mouse
- **10 pesanan sampel** di berbagai status (per customer × status)
  - Pending + Pending payment
  - Processing + Paid
  - Shipped + Paid
  - Completed + Paid
  - Cancelled + Expired payment

Seeder konservatif: setiap pesanan 1-2 unit, stok produk tidak habis.

## Akses Admin

Rute admin:
- `/admin` — dashboard
- `/admin/categories` — CRUD kategori
- `/admin/products` — CRUD produk
- `/admin/orders` — manajemen pesanan

Middleware `EnsureUserIsAdmin` di setiap rute (`auth` + `admin`). Non-admin mendapat 403.

## Keamanan Akun

- Password di-hash bcrypt (cost 12)
- Email unique per user
- Token session database-backed
- Rate limit login: 5 percobaan / IP+email, lockout 60 detik
- Rate limit cart add: 30/menit per IP
- Rate limit checkout: 10/menit per IP
- Rate limit register: 10/menit per IP

Tes: `AuthenticationTest` memastikan peran & rate limit bekerja.
