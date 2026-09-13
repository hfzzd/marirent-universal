-- ============================================================
-- MariRent — TAMBAH ROLE STAFF (versi cepat, 2 langkah)
-- Jalankan di HeidiSQL > pilih database aplikasi > tab Query > F9.
-- Aman diulang (boleh dijalankan berkali-kali).
-- ============================================================

-- LANGKAH 1 — tambah 'staff' (+ 'employee') ke enum users.role
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

-- LANGKAH 2 — verifikasi (hasilnya HARUS memuat kata 'staff')
SHOW COLUMNS FROM users LIKE 'role';
SELECT role, COUNT(*) AS total FROM users GROUP BY role;

-- SELESAI. Panduan lengkap + backfill non-sopir ada di:
-- database/staff-role-heidi.sql
