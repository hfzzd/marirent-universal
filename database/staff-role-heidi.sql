-- ============================================================
-- MariRent — FIX ERROR role `staff` (jalankan di HeidiSQL)
-- ============================================================
-- ERROR YANG MUNCUL (contoh):
--   "Data truncated for column 'role'"  ATAU
--   "Check constraint 'users_role_check' is violated"  ATAU
--   error 1265 / 3819 saat simpan user role = staff
--
-- PENYEBAB:
--   Kolom `users.role` di database ini masih ENUM lama TANPA 'staff',
--   sedangkan aplikasi sudah memakai role `staff`.
--
-- CARA PAKAI (HeidiSQL):
--   1. Buka database yang dipakai aplikasi (cek file .env baris DB_DATABASE).
--   2. Klik database tsb > tab "Query" > copy-paste SELURUH isi file ini.
--   3. Tekan F9 (jalankan semua) dari atas sampai bawah, berurutan.
--   4. Setiap blok SELECT akan menampilkan hasil — pastikan sesuai
--      keterangan "HASIL YANG DIHARAPKAN" di bawahnya.
-- ============================================================

-- LANGKAH 0 — backup tabel users (wajib sebelum ubah struktur)
-- HASIL YANG DIHARAPKAN: 1 baris "Query OK", tabel users_backup_... terbuat.
CREATE TABLE IF NOT EXISTS users_backup_staff_fix LIKE users;
INSERT INTO users_backup_staff_fix SELECT * FROM users;

-- LANGKAH 1 — lihat enum & role yang sekarang dipakai
-- HASIL YANG DIHARAPKAN: kolom Type berisi daftar enum (cek ada/tidaknya 'staff').
SHOW COLUMNS FROM users LIKE 'role';
SELECT role, COUNT(*) AS total FROM users GROUP BY role;

-- LANGKAH 2 — TAMBAHKAN 'staff' ke enum (INTI PERBAIKAN, aman diulang)
-- HASIL YANG DIHARAPKAN: "Query OK, 0 rows affected".
ALTER TABLE users
    MODIFY role ENUM(
        'superadmin',
        'admin',
        'owner',
        'user',
        'driver',
        'staff',
        'employee',
        'inspector'
    ) NOT NULL DEFAULT 'user';

-- LANGKAH 3 — verifikasi enum (wajib ada kata 'staff' di hasilnya)
-- HASIL YANG DIHARAPKAN: Type = enum(...,'staff',...).
SHOW COLUMNS FROM users LIKE 'role';

-- LANGKAH 4 — ubah personel NON-SOPIR menjadi `staff`
--   (a) role driver tapi posisinya bukan sopir
-- HASIL YANG DIHARAPKAN: N rows affected (boleh 0 bila tidak ada).
UPDATE users u
JOIN drivers d ON d.user_id = u.id
SET u.role = 'staff'
WHERE u.role = 'driver'
  AND d.position IS NOT NULL
  AND (
      LOWER(TRIM(d.position)) LIKE '%non driver%'
      OR LOWER(TRIM(d.position)) LIKE '%non-driver%'
      OR LOWER(TRIM(d.position)) LIKE '%bukan driver%'
      OR LOWER(TRIM(d.position)) LIKE '%staff%'
      OR LOWER(TRIM(d.position)) LIKE '%admin%'
      OR LOWER(TRIM(d.position)) LIKE '%karyawan%'
      OR LOWER(TRIM(d.position)) LIKE '%kasir%'
      OR LOWER(TRIM(d.position)) LIKE '%mekanik%'
      OR LOWER(TRIM(d.position)) LIKE '%cleaning%'
      OR (
          LOWER(TRIM(d.position)) NOT LIKE '%driver%'
          AND LOWER(TRIM(d.position)) NOT LIKE '%supir%'
          AND LOWER(TRIM(d.position)) NOT LIKE '%pengemudi%'
      )
  );

--   (b) role `employee` lama digabung menjadi `staff`
-- HASIL YANG DIHARAPKAN: N rows affected (boleh 0 bila tidak ada).
UPDATE users SET role = 'staff' WHERE role = 'employee';

-- LANGKAH 5 — cek hasil akhir
-- HASIL YANG DIHARAPKAN: tidak ada error; role staff muncul bila ada datanya.
SELECT role, COUNT(*) AS total FROM users GROUP BY role;
SELECT id, name, email, role, owner_id, category_id
FROM users
WHERE role = 'staff'
ORDER BY id;

-- LANGKAH 6 (opsional) — hapus tabel backup bila SEMUA sudah benar
-- DROP TABLE users_backup_staff_fix;

-- ============================================================
-- LANGKAH TAMBAHAN — kolom/tabel yang sering tertinggal
-- (Jalankan ini bila muncul error seperti:
--  "Unknown column 'mark_maintenance'" atau
--  "Table 'demo_requests' doesn't exist".
--  Semua perintah di bawah AMAN DIULANG.)
-- ============================================================

-- A) Kolom mark_maintenance di vehicle_replacements
--    (Abaikan error "Duplicate column" bila kolomnya sudah ada.)
ALTER TABLE vehicle_replacements
    ADD COLUMN mark_maintenance TINYINT(1) NOT NULL DEFAULT 1;

-- B) Kolom-kolom pelacakan di item_replacements
--    (Jalankan satu per satu; abaikan yang error "Duplicate column".)
ALTER TABLE item_replacements ADD COLUMN mark_maintenance TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE item_replacements ADD COLUMN damage_notes TEXT NULL;
ALTER TABLE item_replacements ADD COLUMN return_notes TEXT NULL;
ALTER TABLE item_replacements ADD COLUMN return_condition TINYINT UNSIGNED NULL;
ALTER TABLE item_replacements ADD COLUMN is_returned TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE item_replacements ADD COLUMN returned_at TIMESTAMP NULL;
ALTER TABLE item_replacements ADD COLUMN returned_by BIGINT UNSIGNED NULL;
ALTER TABLE item_replacements ADD COLUMN return_is_damaged TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE item_replacements ADD COLUMN return_damage_notes TEXT NULL;

-- C) Tabel permintaan jadwal demo
CREATE TABLE IF NOT EXISTS demo_requests (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    business_name VARCHAR(255) NULL,
    preferred_date DATE NOT NULL,
    preferred_time VARCHAR(20) NOT NULL,
    notes TEXT NULL,
    status ENUM('pending','contacted','scheduled','done','cancelled') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

-- ============================================================
-- CONTOH: buat akun staff baru di bawah satu merchant (owner)
-- Ganti NILAI sesuai kebutuhan, lalu jalankan blok ini saja.
-- Password di bawah = "staff123" (sudah di-bcrypt).
-- ============================================================
-- INSERT INTO users (name, email, password, phone, role, owner_id, category_id, is_active, created_at, updated_at)
-- VALUES (
--     'Nama Staff',
--     'staff@toko.com',
--     '$2y$12$KIXxQG8bBvVZ3mQwErTyOu7xampleHashGantiViaTinkerXXXXXXXXXXXX',
--     '081200000000',
--     'staff',
--     1,      -- owner_id = id user owner merchant (WAJIB agar staff terlingkup ke tokonya)
--     NULL,   -- category_id = NULL (semua kategori) atau id kategori tertentu
--     1,
--     NOW(),
--     NOW()
-- );
-- NOTE: hash di atas CONTOH — buat hash asli via:
--   php artisan tinker --execute="echo bcrypt('staff123');"
-- lalu tempel hasilnya menggantikan nilai password di atas.
