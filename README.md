# MariRent Universal - Platform Rental Universal

**MariRent Universal** adalah sistem manajemen rental komprehensif berbasis Laravel yang mendukung berbagai jenis aset sewa: kendaraan (mobil, motor), gadget (HP, kamera, drone, playstation), alat musik, elektronik, dan peralatan lainnya. Sistem ini dirancang untuk mengelola proses rental mulai dari pemesanan, pembayaran, penagihan langganan bulanan, hingga pelaporan operasional.

## Tentang Platform

MariRent Universal mengelola beberapa entitas bisnis utama:

- **Master Data:** Kategori, Merek (Brand), Toko/Toko Merchants, dan Company (cabang usaha)
- **Aset Sewa:** Mobil, Motor, HP, Kamera, Drone, Playstation, Alat Musik, Elektronik, dll.
- **User Management:** Pelanggan (user), Supir (driver), Inspektur, Admin, Owner (pemilik perusahaan), dan Superadmin
- **Proses Bisnis:** Booking, Inspeksi, Laporan Trip, Gaji Driver, Penggantian Barang, Penjadwalan Operasional, dan Manajemen Keuangan

## Fitur Utama

### Manajemen Aset Sewa

- CRUD untuk semua jenis kendaraan dan barang sewaan
- Manajemen foto katalog merek (untuk merchant)
- Filter berdasarkan kategori, merek, dan ketersediaan

### Alur Booking Rental

- **Multi-step booking:** Pilih aset → Pilih driver (opsional) → Upload KTP → Konfirmasi
- **Flexible booking types:** Regular, Multi-asset, Manual create
- **Trip management:** Mulai trip, Selesaikan trip, Assign driver, Reschedule
- **Cancellation & replacement:** Proses pengembalian dan penggantian kendaraan

### Sistem Tagihan & Keuangan

- **Invoice Generation:** Pembuatan invoice otomatis per booking
- **Subscription Billing:** Tagihan bulanan untuk merchant (berbagai paket)
- **Payment Verification:** Admin dapat memverifikasi dan menolak pembayaran
- **Revenue Management:** Pelaporan pendapatan per merchant, owner, dan superadmin
- **Payroll:** Manajemen dan pembayaran gaji driver

### Manajemen Langganan (Subscription)

- **Subscription Plans:** Paket langganan berbeda untuk setiap merchant
- **Monthly Billing:** Tagihan bulanan otomatis untuk merchant
- **Overdue Management:** Notifikasi dan sistem untuk subscription yang telat
- **Reminder System:** Pengingat melalui email/SMS untuk merchant

### Notifikasi & Komunikasi

- **System Notifications:** Notifikasi real-time untuk booking, invoice, overdue, dll.
- **Mail System:** Inbox/outbox untuk komunikasi dengan pelanggan dan merchant
- **Live Chat:** Sistem pesan untuk tim operasional

### Pelaporan & Analisis

- **Financial Reports:** Laporan pendapatan, tagihan, dan pengeluaran
- **Operational Reports:** Jadwal operasional per kategori (superadmin, owner, admin)
- **Attendance & Performance:** Rekam jejak kehadiran driver/inspector
- **Trip Reports:** Dokumentasi lengkap setiap perjalanan rental

### Manajemen User Multi-role

- **Superadmin:** Akses penuh ke semua fitur
- **Owner/Admin:** Kelola bisnis mereka, driver, dan operasional
- **Driver:** Kelola trip, kirim laporan, absen
- **Inspector:** Lakukan inspeksi sebelum/masa rental
- **Customer:** Booking aset, kelola profil

## Arsitektur Sistem

### Web Routes (`routes/web.php`)

- **Frontend:** Panel kontrol untuk berbagai role (superadmin, owner, driver, customer)
- **Public:** Halaman produk, demo, berita, kontak, dll.
- **Protected:** Grouped by role-based middleware with dedicated controllers

### API Routes (`routes/api.php`)

- **Mobile Integration:** API untuk aplikasi mobile dengan Sanctum authentication
- **Public Auth:** Register, Login endpoints
- **Protected Resources:** Semua fitur tersedia melalui REST API

### Struktur Controllers

- **Web Controllers:** Tampilan antarmuka HTML dan proses bisnis
- **API Controllers:** Logika API, format JSON responses
- **Service Classes:** Logika bisnis terpisah (InvoiceService, SubscriptionService)

### Database Models

- **45+ Model:** Semua jenis aset, user, booking, transaksi, dll.
- **Tenant Isolation:** Mendukung multi-company/cabang melalui Company model
- **Soft Deletes:** Banyak model menggunakan soft deletes untuk keamanan data

## Instalasi & Konfigurasi

### Prasyarat

```bash
PHP >= 8.2
Composer
MySQL/MariaDB (5.7+)
Node.js >= 18 (untuk assets)
```

### Langkah Instalasi

1. **Clone Proyek**

   ```bash
   git clone <url-proyek>
   cd marirent-universal
   ```

2. **Instal Dependensi**

   ```bash
   # PHP Dependencies
   composer install

   # Frontend Assets
   npm install
   ```

3. **Konfigurasi Lingkungan**

   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Edit File .env**

   ```env
   APP_NAME="MariRent Universal"
   APP_ENV=local
   APP_DEBUG=true
   APP_URL=http://localhost:8000

   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=mari_rent
   DB_USERNAME=root
   DB_PASSWORD= (isi sesuai database Anda)

   CACHE_DRIVER=file
   SESSION_DRIVER=file
   QUEUE_DRIVER=sync
   ```

5. **Jalankan Database Migrations**

   ```bash
   php artisan migrate
   ```

6. **Jalankan Database Seeders** (untuk data awal)

   ```bash
   php artisan db:seed
   ```

7. **Jalankan Queue Worker** (jika menggunakan queue)

   ```bash
   php artisan queue:work
   ```

8. **Jalankan Server**

   ```bash
   php artisan serve
   npm run dev
   ```

9. **Akses Aplikasi**
   - Web: http://localhost:8000
   - API: http://localhost:8000/api

## Pengembangan

### Lingkungan Development

```bash
# Jalankan lokal development
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000
```

### Penjadwalan (Scheduler)

MariRent menggunakan Laravel Scheduler untuk tugas operasional:

- **Update Expired Bookings:** Daily
- **Process Subscriptions:** Daily
- **Clean Old Logs:** 30 days
- **Send Booking Reminders:** Daily
- **Update Expired Subscriptions:** Daily

Aktifkan dengan menambahkan job ke cron daemon Anda.

### Manajemen Database

```bash
# Backup database
php artisan db:backup

# Format database (hanya untuk development)
php artisan migrate:refresh
php artisan db:seed

# Cek status database
php artisan migrate:status
php artisan optimize:clear
```

### Penanganan Static Assets

```bash
# Kompilasi assets (Laravel Mix/Vite)
npm run dev (development)
npm run build (production)

# Bersihkan compiled assets
npm run clean
```

### Penanganan Log

```bash
# Cek log terbaru
php artisan tinker --command="Log::latest()"

# Bersihkan log lama (hanya development)
php artisan log:clear
```

## Testing

### Struktur Testing

```
/tests/
├── Feature/     # Feature testing dengan browser-level pengujian
├── Unit/        # Unit test untuk aplikasi
├── Feature/InvoiceConsolidateTest.php
├── Feature/InvoiceCategoryTest.php
├── Feature/SubscriptionExpiredTest.php
└── ... (10+ test files)
```

### Jalankan Test

```bash
# Jalankan semua test
php artisan test

# Atau menggunakan phpunit
phpunit

# Jalankan hanya feature test
php artisan test --path=tests/Feature

# Jalankan unit test
phpunit --testsuite=Unit
```

### Coverage Test

MariRent menggunakan PHPUnit untuk coverage testing. Jalankan:

```bash
phpunit --coverage-html storage/framework/reports/coverage.html
```

## Lisensi

MIT License (MIT)

Hak Cipta (c) 2026 MariRent

Diperbolehkan menggunakan, modifikasi, dan mendistribusikan kode ini asalkan mematuhi ketentuan lisensi MIT.
