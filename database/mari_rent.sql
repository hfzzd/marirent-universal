-- =============================================
-- MariRent Universal - Database Schema & Complete Seed Data
-- Untuk phpMyAdmin / MySQL / MariaDB
-- =============================================

SET FOREIGN_KEY_CHECKS = 0;

CREATE DATABASE IF NOT EXISTS `mari_rent` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `mari_rent`;

-- --------------------------------------------------------
-- 1. Users Table
-- --------------------------------------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `email_verified_at` TIMESTAMP NULL,
  `password` VARCHAR(255) NOT NULL,
  `phone` VARCHAR(20) NULL,
  `address` VARCHAR(500) NULL,
  `avatar` VARCHAR(255) NULL,
  `role` ENUM('superadmin','owner','user','driver') NOT NULL DEFAULT 'user',
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `remember_token` VARCHAR(100) NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  `deleted_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 2. Personal Access Tokens (Sanctum)
-- --------------------------------------------------------
DROP TABLE IF EXISTS `personal_access_tokens`;
CREATE TABLE `personal_access_tokens` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tokenable_type` VARCHAR(255) NOT NULL,
  `tokenable_id` BIGINT UNSIGNED NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `token` VARCHAR(64) NOT NULL,
  `abilities` TEXT NULL,
  `last_used_at` TIMESTAMP NULL,
  `expires_at` TIMESTAMP NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 3. Password Reset Tokens
-- --------------------------------------------------------
DROP TABLE IF EXISTS `password_reset_tokens`;
CREATE TABLE `password_reset_tokens` (
  `email` VARCHAR(255) NOT NULL,
  `token` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 4. Categories Table
-- --------------------------------------------------------
DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(255) NOT NULL,
  `description` TEXT NULL,
  `icon` VARCHAR(255) NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `categories_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 5. Vehicles Table (Mobil & Motor)
-- --------------------------------------------------------
DROP TABLE IF EXISTS `vehicles`;
CREATE TABLE `vehicles` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `category_id` BIGINT UNSIGNED NOT NULL,
  `owner_id` BIGINT UNSIGNED NOT NULL,
  `vehicle_type` ENUM('car','motorcycle') NOT NULL DEFAULT 'car',
  `name` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(255) NOT NULL,
  `brand` VARCHAR(100) NULL,
  `model` VARCHAR(100) NULL,
  `year` INT NULL,
  `color` VARCHAR(50) NULL,
  `license_plate` VARCHAR(20) NOT NULL,
  `description` TEXT NULL,
  `daily_price` DECIMAL(12,2) NOT NULL,
  `weekly_price` DECIMAL(12,2) NULL,
  `monthly_price` DECIMAL(12,2) NULL,
  `hourly_price` DECIMAL(12,2) NULL,
  `with_driver_daily_price` DECIMAL(12,2) NULL,
  `image` VARCHAR(255) NULL,
  `gallery` JSON NULL,
  `status` ENUM('available','rented','maintenance','reserved') NOT NULL DEFAULT 'available',
  `condition` ENUM('excellent','good','fair','poor') NOT NULL DEFAULT 'good',
  `seats` INT NULL,
  `transmission` VARCHAR(20) NULL,
  `fuel_type` VARCHAR(20) NULL,
  `mileage` INT NULL,
  `with_driver` TINYINT(1) NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  `deleted_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `vehicles_slug_unique` (`slug`),
  UNIQUE KEY `vehicles_license_plate_unique` (`license_plate`),
  KEY `vehicles_category_id_foreign` (`category_id`),
  KEY `vehicles_owner_id_foreign` (`owner_id`),
  CONSTRAINT `vehicles_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE,
  CONSTRAINT `vehicles_owner_id_foreign` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 6. Phones Table (Sewa Smartphone & HP)
-- --------------------------------------------------------
DROP TABLE IF EXISTS `phones`;
CREATE TABLE `phones` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `category_id` BIGINT UNSIGNED NOT NULL,
  `owner_id` BIGINT UNSIGNED NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(255) NOT NULL,
  `brand` VARCHAR(100) NOT NULL,
  `model` VARCHAR(100) NOT NULL,
  `storage_gb` INT NOT NULL,
  `ram_gb` INT NULL,
  `color` VARCHAR(50) NULL,
  `imei` VARCHAR(50) NULL,
  `description` TEXT NULL,
  `daily_price` DECIMAL(12,2) NOT NULL,
  `weekly_price` DECIMAL(12,2) NULL,
  `monthly_price` DECIMAL(12,2) NULL,
  `image` VARCHAR(255) NULL,
  `status` ENUM('available','rented','maintenance','reserved') NOT NULL DEFAULT 'available',
  `condition` ENUM('excellent','good','fair','poor') NOT NULL DEFAULT 'excellent',
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  `deleted_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `phones_slug_unique` (`slug`),
  KEY `phones_category_id_foreign` (`category_id`),
  KEY `phones_owner_id_foreign` (`owner_id`),
  CONSTRAINT `phones_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE,
  CONSTRAINT `phones_owner_id_foreign` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 7. Cameras Table (Sewa Kamera & Lensa)
-- --------------------------------------------------------
DROP TABLE IF EXISTS `cameras`;
CREATE TABLE `cameras` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `category_id` BIGINT UNSIGNED NOT NULL,
  `owner_id` BIGINT UNSIGNED NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(255) NOT NULL,
  `brand` VARCHAR(100) NOT NULL,
  `model` VARCHAR(100) NOT NULL,
  `sensor_type` VARCHAR(50) NULL,
  `resolution_mp` INT NULL,
  `included_lens` VARCHAR(255) NULL,
  `serial_number` VARCHAR(100) NULL,
  `description` TEXT NULL,
  `daily_price` DECIMAL(12,2) NOT NULL,
  `weekly_price` DECIMAL(12,2) NULL,
  `monthly_price` DECIMAL(12,2) NULL,
  `image` VARCHAR(255) NULL,
  `status` ENUM('available','rented','maintenance','reserved') NOT NULL DEFAULT 'available',
  `condition` ENUM('excellent','good','fair','poor') NOT NULL DEFAULT 'excellent',
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  `deleted_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cameras_slug_unique` (`slug`),
  KEY `cameras_category_id_foreign` (`category_id`),
  KEY `cameras_owner_id_foreign` (`owner_id`),
  CONSTRAINT `cameras_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cameras_owner_id_foreign` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 8. Camping Equipments Table (Sewa Alat Tenda & Outdoor)
-- --------------------------------------------------------
DROP TABLE IF EXISTS `camping_equipments`;
CREATE TABLE `camping_equipments` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `category_id` BIGINT UNSIGNED NOT NULL,
  `owner_id` BIGINT UNSIGNED NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(255) NOT NULL,
  `brand` VARCHAR(100) NULL,
  `equipment_type` ENUM('tenda','sleeping_bag','kompor_nesting','carrier','flysheet','penerangan','aksesoris') NOT NULL DEFAULT 'tenda',
  `capacity_persons` INT NULL,
  `color` VARCHAR(50) NULL,
  `description` TEXT NULL,
  `daily_price` DECIMAL(12,2) NOT NULL,
  `weekly_price` DECIMAL(12,2) NULL,
  `stock_quantity` INT NOT NULL DEFAULT 1,
  `available_quantity` INT NOT NULL DEFAULT 1,
  `image` VARCHAR(255) NULL,
  `status` ENUM('available','rented','maintenance','out_of_stock') NOT NULL DEFAULT 'available',
  `condition` ENUM('excellent','good','fair','poor') NOT NULL DEFAULT 'good',
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  `deleted_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `camping_equipments_slug_unique` (`slug`),
  KEY `camping_equipments_category_id_foreign` (`category_id`),
  KEY `camping_equipments_owner_id_foreign` (`owner_id`),
  CONSTRAINT `camping_equipments_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE,
  CONSTRAINT `camping_equipments_owner_id_foreign` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 9. Drivers Table
-- --------------------------------------------------------
DROP TABLE IF EXISTS `drivers`;
CREATE TABLE `drivers` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `owner_id` BIGINT UNSIGNED NOT NULL,
  `license_number` VARCHAR(50) NULL,
  `license_expiry` DATE NULL,
  `license_type` VARCHAR(20) NULL,
  `daily_salary` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `trip_salary` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `status` ENUM('active','inactive','on_trip','off_duty') NOT NULL DEFAULT 'off_duty',
  `notes` TEXT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  `deleted_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  KEY `drivers_user_id_foreign` (`user_id`),
  KEY `drivers_owner_id_foreign` (`owner_id`),
  CONSTRAINT `drivers_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `drivers_owner_id_foreign` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 10. Bookings Table (Mendukung Universal Rental)
-- --------------------------------------------------------
DROP TABLE IF EXISTS `bookings`;
CREATE TABLE `bookings` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `booking_code` VARCHAR(50) NOT NULL,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `category_id` BIGINT UNSIGNED NOT NULL,
  `item_type` ENUM('vehicle','phone','camera','camping') NOT NULL DEFAULT 'vehicle',
  `item_id` BIGINT UNSIGNED NOT NULL,
  `vehicle_id` BIGINT UNSIGNED NULL,
  `driver_id` BIGINT UNSIGNED NULL,
  `rental_type` ENUM('hourly','daily','weekly','monthly') NOT NULL DEFAULT 'daily',
  `start_date` DATETIME NOT NULL,
  `end_date` DATETIME NOT NULL,
  `actual_start_date` DATETIME NULL,
  `actual_end_date` DATETIME NULL,
  `pickup_location` VARCHAR(255) NULL,
  `dropoff_location` VARCHAR(255) NULL,
  `with_driver` TINYINT(1) NOT NULL DEFAULT 0,
  `base_price` DECIMAL(12,2) NOT NULL,
  `driver_price` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `total_price` DECIMAL(12,2) NOT NULL,
  `discount` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `final_price` DECIMAL(12,2) NOT NULL,
  `status` ENUM('pending','confirmed','ongoing','completed','cancelled') NOT NULL DEFAULT 'pending',
  `payment_status` ENUM('unpaid','partial','paid','refunded') NOT NULL DEFAULT 'unpaid',
  `notes` TEXT NULL,
  `cancellation_reason` TEXT NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  `deleted_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `bookings_booking_code_unique` (`booking_code`),
  KEY `bookings_user_id_foreign` (`user_id`),
  KEY `bookings_driver_id_foreign` (`driver_id`),
  KEY `bookings_category_id_foreign` (`category_id`),
  CONSTRAINT `bookings_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `bookings_driver_id_foreign` FOREIGN KEY (`driver_id`) REFERENCES `drivers` (`id`) ON DELETE SET NULL,
  CONSTRAINT `bookings_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 11. Inspections Table
-- --------------------------------------------------------
DROP TABLE IF EXISTS `inspections`;
CREATE TABLE `inspections` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `booking_id` BIGINT UNSIGNED NOT NULL,
  `item_type` ENUM('vehicle','phone','camera','camping') NOT NULL DEFAULT 'vehicle',
  `item_id` BIGINT UNSIGNED NOT NULL,
  `inspector_id` BIGINT UNSIGNED NOT NULL,
  `type` ENUM('pre_rental','post_rental') NOT NULL,
  `exterior_condition` INT NOT NULL DEFAULT 5,
  `interior_condition` INT NOT NULL DEFAULT 5,
  `engine_condition` INT NOT NULL DEFAULT 5,
  `tire_condition` INT NOT NULL DEFAULT 5,
  `brake_condition` INT NOT NULL DEFAULT 5,
  `electrical_condition` INT NOT NULL DEFAULT 5,
  `overall_condition` INT NOT NULL DEFAULT 5,
  `fuel_level` DECIMAL(5,2) NOT NULL DEFAULT 100,
  `odometer_reading` DECIMAL(12,2) NULL,
  `damages` JSON NULL,
  `photos` JSON NULL,
  `notes` TEXT NULL,
  `recommendations` TEXT NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  KEY `inspections_booking_id_foreign` (`booking_id`),
  KEY `inspections_inspector_id_foreign` (`inspector_id`),
  CONSTRAINT `inspections_booking_id_foreign` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE,
  CONSTRAINT `inspections_inspector_id_foreign` FOREIGN KEY (`inspector_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 12. Trip Reports Table
-- --------------------------------------------------------
DROP TABLE IF EXISTS `trip_reports`;
CREATE TABLE `trip_reports` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `booking_id` BIGINT UNSIGNED NOT NULL,
  `driver_id` BIGINT UNSIGNED NULL,
  `vehicle_id` BIGINT UNSIGNED NOT NULL,
  `start_odometer` DECIMAL(12,2) NULL,
  `end_odometer` DECIMAL(12,2) NULL,
  `total_distance` DECIMAL(10,2) NULL,
  `fuel_used` DECIMAL(5,2) NULL,
  `fuel_cost` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `toll_cost` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `parking_cost` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `other_cost` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `total_operational_cost` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `route_points` JSON NULL,
  `photos` JSON NULL,
  `notes` TEXT NULL,
  `issues_reported` TEXT NULL,
  `status` ENUM('in_progress','completed','has_issues') NOT NULL DEFAULT 'in_progress',
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  KEY `trip_reports_booking_id_foreign` (`booking_id`),
  KEY `trip_reports_driver_id_foreign` (`driver_id`),
  KEY `trip_reports_vehicle_id_foreign` (`vehicle_id`),
  CONSTRAINT `trip_reports_booking_id_foreign` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE,
  CONSTRAINT `trip_reports_driver_id_foreign` FOREIGN KEY (`driver_id`) REFERENCES `drivers` (`id`) ON DELETE SET NULL,
  CONSTRAINT `trip_reports_vehicle_id_foreign` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 13. Vehicle Replacements Table
-- --------------------------------------------------------
DROP TABLE IF EXISTS `vehicle_replacements`;
CREATE TABLE `vehicle_replacements` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `booking_id` BIGINT UNSIGNED NOT NULL,
  `original_vehicle_id` BIGINT UNSIGNED NOT NULL,
  `replacement_vehicle_id` BIGINT UNSIGNED NOT NULL,
  `requested_by` BIGINT UNSIGNED NOT NULL,
  `approved_by` BIGINT UNSIGNED NULL,
  `status` ENUM('pending','approved','rejected','completed') NOT NULL DEFAULT 'pending',
  `reason` TEXT NOT NULL,
  `admin_notes` TEXT NULL,
  `price_difference` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  KEY `vehicle_replacements_booking_id_foreign` (`booking_id`),
  KEY `vehicle_replacements_original_vehicle_id_foreign` (`original_vehicle_id`),
  KEY `vehicle_replacements_replacement_vehicle_id_foreign` (`replacement_vehicle_id`),
  CONSTRAINT `vehicle_replacements_booking_id_foreign` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE,
  CONSTRAINT `vehicle_replacements_original_vehicle_id_foreign` FOREIGN KEY (`original_vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `vehicle_replacements_replacement_vehicle_id_foreign` FOREIGN KEY (`replacement_vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 14. Invoices Table
-- --------------------------------------------------------
DROP TABLE IF EXISTS `invoices`;
CREATE TABLE `invoices` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `invoice_number` VARCHAR(50) NOT NULL,
  `booking_id` BIGINT UNSIGNED NOT NULL,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `owner_id` BIGINT UNSIGNED NOT NULL,
  `type` ENUM('rental','driver_salary','replacement','damage','other') NOT NULL DEFAULT 'rental',
  `subtotal` DECIMAL(14,2) NOT NULL,
  `tax_amount` DECIMAL(14,2) NOT NULL DEFAULT 0,
  `discount_amount` DECIMAL(14,2) NOT NULL DEFAULT 0,
  `total_amount` DECIMAL(14,2) NOT NULL,
  `paid_amount` DECIMAL(14,2) NOT NULL DEFAULT 0,
  `due_amount` DECIMAL(14,2) NOT NULL,
  `status` ENUM('draft','sent','paid','partial','overdue','cancelled') NOT NULL DEFAULT 'draft',
  `payment_method` ENUM('cash','transfer','ewallet','credit_card','other') NULL,
  `payment_reference` VARCHAR(100) NULL,
  `paid_at` DATETIME NULL,
  `due_date` DATETIME NOT NULL,
  `notes` TEXT NULL,
  `terms` TEXT NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  `deleted_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `invoices_invoice_number_unique` (`invoice_number`),
  KEY `invoices_booking_id_foreign` (`booking_id`),
  KEY `invoices_user_id_foreign` (`user_id`),
  KEY `invoices_owner_id_foreign` (`owner_id`),
  CONSTRAINT `invoices_booking_id_foreign` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE,
  CONSTRAINT `invoices_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `invoices_owner_id_foreign` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 15. Invoice Items Table
-- --------------------------------------------------------
DROP TABLE IF EXISTS `invoice_items`;
CREATE TABLE `invoice_items` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `invoice_id` BIGINT UNSIGNED NOT NULL,
  `description` VARCHAR(500) NOT NULL,
  `quantity` INT NOT NULL DEFAULT 1,
  `unit_price` DECIMAL(12,2) NOT NULL,
  `total_price` DECIMAL(12,2) NOT NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  KEY `invoice_items_invoice_id_foreign` (`invoice_id`),
  CONSTRAINT `invoice_items_invoice_id_foreign` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 16. Payments Table
-- --------------------------------------------------------
DROP TABLE IF EXISTS `payments`;
CREATE TABLE `payments` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `payment_code` VARCHAR(50) NOT NULL,
  `invoice_id` BIGINT UNSIGNED NOT NULL,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `amount` DECIMAL(14,2) NOT NULL,
  `method` ENUM('cash','transfer','ewallet','credit_card','other') NOT NULL,
  `reference_number` VARCHAR(100) NULL,
  `proof_photo` VARCHAR(500) NULL,
  `status` ENUM('pending','verified','rejected') NOT NULL DEFAULT 'pending',
  `notes` TEXT NULL,
  `paid_at` TIMESTAMP NOT NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `payments_payment_code_unique` (`payment_code`),
  KEY `payments_invoice_id_foreign` (`invoice_id`),
  KEY `payments_user_id_foreign` (`user_id`),
  CONSTRAINT `payments_invoice_id_foreign` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE,
  CONSTRAINT `payments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 17. Driver Salaries Table
-- --------------------------------------------------------
DROP TABLE IF EXISTS `driver_salaries`;
CREATE TABLE `driver_salaries` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `driver_id` BIGINT UNSIGNED NOT NULL,
  `owner_id` BIGINT UNSIGNED NOT NULL,
  `period_month` VARCHAR(7) NOT NULL,
  `base_salary` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `trip_bonus` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `overtime_pay` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `deductions` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `total_salary` DECIMAL(12,2) NOT NULL,
  `status` ENUM('draft','approved','paid') NOT NULL DEFAULT 'draft',
  `invoice_id` BIGINT UNSIGNED NULL,
  `notes` TEXT NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  `deleted_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  KEY `driver_salaries_driver_id_foreign` (`driver_id`),
  KEY `driver_salaries_owner_id_foreign` (`owner_id`),
  KEY `driver_salaries_invoice_id_foreign` (`invoice_id`),
  CONSTRAINT `driver_salaries_driver_id_foreign` FOREIGN KEY (`driver_id`) REFERENCES `drivers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `driver_salaries_owner_id_foreign` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `driver_salaries_invoice_id_foreign` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 18. Reviews Table
-- --------------------------------------------------------
DROP TABLE IF EXISTS `reviews`;
CREATE TABLE `reviews` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `booking_id` BIGINT UNSIGNED NOT NULL,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `item_type` ENUM('vehicle','phone','camera','camping') NOT NULL DEFAULT 'vehicle',
  `item_id` BIGINT UNSIGNED NOT NULL,
  `rating` TINYINT NOT NULL DEFAULT 5,
  `comment` TEXT NULL,
  `is_visible` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  KEY `reviews_booking_id_foreign` (`booking_id`),
  KEY `reviews_user_id_foreign` (`user_id`),
  CONSTRAINT `reviews_booking_id_foreign` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reviews_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 19. Notifications Table
-- --------------------------------------------------------
DROP TABLE IF EXISTS `notifications`;
CREATE TABLE `notifications` (
  `id` CHAR(36) NOT NULL,
  `type` VARCHAR(255) NOT NULL,
  `notifiable_type` VARCHAR(255) NOT NULL,
  `notifiable_id` BIGINT UNSIGNED NOT NULL,
  `data` TEXT NOT NULL,
  `read_at` TIMESTAMP NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ========================================================
-- SEED DATA (ISIAN DATA LENGKAP BISA LANGSUNG DIPAKAI)
-- Password default semua user: password
-- Hash: $2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi
-- ========================================================

-- 1. Insert Users
INSERT INTO `users` (`id`, `name`, `email`, `password`, `phone`, `address`, `role`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Super Admin', 'admin@marirent.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081111111111', 'Kantor Pusat MariRent, Banjar', 'superadmin', 1, NOW(), NOW()),
(2, 'Owner Utama (Rental Kendaraan)', 'owner@marirent.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081234567890', 'Jl. Letjen Suwarto No. 45, Banjar', 'owner', 1, NOW(), NOW()),
(3, 'Owner Outdoor & Gadget', 'outdoor.owner@marirent.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081299887766', 'Jl. Mayjen Didi Kartasasmita No. 12, Banjar', 'owner', 1, NOW(), NOW()),
(4, 'Budi Santoso', 'user@marirent.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081987654321', 'Jl. Perintis Kemerdekaan No. 8, Banjar', 'user', 1, NOW(), NOW()),
(5, 'Ahmad Driver', 'driver@marirent.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081555666777', 'Jl. Stasiun No. 3, Banjar', 'driver', 1, NOW(), NOW()),
(6, 'Siti Rahma', 'siti@gmail.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '082112233445', 'Jl. Brigjen M. Isa No. 90, Banjar', 'user', 1, NOW(), NOW());

-- 2. Insert Categories
INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `icon`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Mobil', 'mobil', 'Sewa mobil keluarga, premium, dan operasional', 'car', 1, NOW(), NOW()),
(2, 'Motor', 'motor', 'Sewa motor matic dan sport harian/mingguan', 'bike', 1, NOW(), NOW()),
(3, 'Sewa HP', 'sewa-hp', 'Sewa smartphone flagship & iOS/Android', 'smartphone', 1, NOW(), NOW()),
(4, 'Sewa Kamera', 'sewa-kamera', 'Sewa kamera DSLR, Mirrorless, & Action Cam', 'camera', 1, NOW(), NOW()),
(5, 'Alat Tenda & Outdoor', 'alat-tenda', 'Sewa perlengkapan kemping & pendakian gunung', 'tent', 1, NOW(), NOW());

-- 3. Insert Driver Profile
INSERT INTO `drivers` (`id`, `user_id`, `owner_id`, `license_number`, `license_type`, `daily_salary`, `trip_salary`, `status`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 5, 2, 'SIM-A-9876543210', 'A', 150000.00, 50000.00, 'off_duty', 1, NOW(), NOW());

-- 4. Insert Vehicles (Mobil & Motor)
INSERT INTO `vehicles` (`id`, `category_id`, `owner_id`, `vehicle_type`, `name`, `slug`, `brand`, `model`, `year`, `color`, `license_plate`, `description`, `daily_price`, `weekly_price`, `monthly_price`, `hourly_price`, `with_driver_daily_price`, `status`, `condition`, `seats`, `transmission`, `fuel_type`, `mileage`, `with_driver`, `is_active`, `created_at`, `updated_at`) VALUES
-- Mobil
(1, 1, 2, 'car', 'Toyota Avanza Veloz', 'toyota-avanza-veloz-2023', 'Toyota', 'Avanza', 2023, 'Putih', 'Z 1234 YB', 'Toyota Avanza Veloz 2023, AC dingin, siap luar kota.', 350000.00, 2200000.00, 8000000.00, 50000.00, 200000.00, 'available', 'excellent', 7, 'automatic', 'gasoline', 25000, 1, 1, NOW(), NOW()),
(2, 1, 2, 'car', 'Honda Brio RS', 'honda-brio-rs-2022', 'Honda', 'Brio', 2022, 'Merah', 'Z 5678 AC', 'Honda Brio RS 2022, irit BBM, cocok untuk area perkotaan.', 275000.00, 1800000.00, 6500000.00, 40000.00, 175000.00, 'available', 'good', 5, 'automatic', 'gasoline', 32000, 1, 1, NOW(), NOW()),
(3, 1, 2, 'car', 'Toyota Innova Reborn', 'toyota-innova-reborn-2023', 'Toyota', 'Innova Reborn', 2023, 'Hitam', 'Z 9012 B', 'Toyota Innova Reborn Diesel 2.4, nyaman & kabin senyap.', 550000.00, 3500000.00, 12000000.00, 75000.00, 250000.00, 'rented', 'excellent', 7, 'automatic', 'diesel', 18000, 1, 1, NOW(), NOW()),
-- Motor
(4, 2, 2, 'motorcycle', 'Honda Vario 160 ABS', 'honda-vario-160-2023', 'Honda', 'Vario 160', 2023, 'Matte Black', 'Z 4321 YC', 'Honda Vario 160, helm 2 + jas hujan gratis.', 80000.00, 500000.00, 1600000.00, 15000.00, NULL, 'available', 'excellent', 2, 'automatic', 'gasoline', 12000, 0, 1, NOW(), NOW()),
(5, 2, 2, 'motorcycle', 'Yamaha NMAX 155 Connected', 'yamaha-nmax-155-2023', 'Yamaha', 'NMAX', 2023, 'Biru', 'Z 8765 YD', 'Yamaha NMAX 155, nyaman touring jauh.', 110000.00, 680000.00, 2200000.00, 20000.00, NULL, 'available', 'excellent', 2, 'automatic', 'gasoline', 15000, 0, 1, NOW(), NOW()),
(6, 2, 2, 'motorcycle', 'Honda Beat Street', 'honda-beat-street-2022', 'Honda', 'Beat', 2022, 'Hitam', 'Z 3456 YE', 'Honda Beat Street, sangat irit dan gampang diselip.', 65000.00, 400000.00, 1300000.00, 12000.00, NULL, 'available', 'good', 2, 'automatic', 'gasoline', 28000, 0, 1, NOW(), NOW());

-- 5. Insert Phones (Sewa HP)
INSERT INTO `phones` (`id`, `category_id`, `owner_id`, `name`, `slug`, `brand`, `model`, `storage_gb`, `ram_gb`, `color`, `imei`, `description`, `daily_price`, `weekly_price`, `monthly_price`, `status`, `condition`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 3, 3, 'iPhone 15 Pro Max 256GB', 'iphone-15-pro-max-256gb', 'Apple', 'iPhone 15 Pro Max', 256, 8, 'Natural Titanium', '358921109283011', 'Lengkap charger & case, kamera bening untuk konsert / event.', 200000.00, 1200000.00, 4000000.00, 'available', 'excellent', 1, NOW(), NOW()),
(2, 3, 3, 'Samsung Galaxy S24 Ultra 512GB', 'samsung-galaxy-s24-ultra-512gb', 'Samsung', 'Galaxy S24 Ultra', 512, 12, 'Titanium Black', '358921109283022', 'Zoom 100x jernih, S-Pen aktif, pas buat event / liputan.', 180000.00, 1100000.00, 3600000.00, 'rented', 'excellent', 1, NOW(), NOW()),
(3, 3, 3, 'iPhone 13 128GB', 'iphone-13-128gb', 'Apple', 'iPhone 13', 128, 4, 'Pink', '358921109283033', 'Kondisi mulus, battery health 92%, cocok untuk pembuatan konten.', 120000.00, 750000.00, 2400000.00, 'available', 'good', 1, NOW(), NOW());

-- 6. Insert Cameras (Sewa Kamera)
INSERT INTO `cameras` (`id`, `category_id`, `owner_id`, `name`, `slug`, `brand`, `model`, `sensor_type`, `resolution_mp`, `included_lens`, `serial_number`, `description`, `daily_price`, `weekly_price`, `monthly_price`, `status`, `condition`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 4, 3, 'Sony A7 IV Kit 28-70mm', 'sony-a7-iv-kit', 'Sony', 'A7 IV', 'Full Frame', 33, 'FE 28-70mm f/3.5-5.6 OSS', 'SN-SONY-998811', 'Termasuk 2 baterai, SD Card 128GB High Speed, Charger, & Tas.', 350000.00, 2100000.00, 7000000.00, 'available', 'excellent', 1, NOW(), NOW()),
(2, 4, 3, 'Canon EOS R6 Mark II Body', 'canon-eos-r6-mark-ii', 'Canon', 'EOS R6 II', 'Full Frame', 24, 'Adapter EF to RF Included', 'SN-CANON-887722', 'Kamera video & foto profesional, slow-mo 4K 60fps.', 325000.00, 1950000.00, 6500000.00, 'available', 'excellent', 1, NOW(), NOW()),
(3, 4, 3, 'GoPro Hero 12 Black Creator Edition', 'gopro-hero-12-black', 'GoPro', 'Hero 12', '1/1.9 inch', 27, 'Built-in Wide Lens', 'SN-GOPRO-776633', 'Termasuk Media Mod, Volta Grip, Light Mod, & 3 Baterai.', 120000.00, 700000.00, 2200000.00, 'rented', 'good', 1, NOW(), NOW());

-- 7. Insert Camping Equipments (Sewa Alat Tenda & Outdoor)
INSERT INTO `camping_equipments` (`id`, `category_id`, `owner_id`, `name`, `slug`, `brand`, `equipment_type`, `capacity_persons`, `color`, `description`, `daily_price`, `weekly_price`, `stock_quantity`, `available_quantity`, `status`, `condition`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 5, 3, 'Tenda Eiger XT Trail 4 Person Double Layer', 'tenda-eiger-xt-trail-4p', 'Eiger', 'tenda', 4, 'Orange', 'Tenda waterproof double layer anti badai, frame alumunium, pasak lengkap.', 60000.00, 350000.00, 5, 4, 'available', 'excellent', 1, NOW(), NOW()),
(2, 5, 3, 'Tenda Consina Magnum 4 Person', 'tenda-consina-magnum-4p', 'Consina', 'tenda', 4, 'Merah', 'Tenda kemping keluarga/pendaki, teras luas untuk masak.', 50000.00, 300000.00, 8, 8, 'available', 'good', 1, NOW(), NOW()),
(3, 5, 3, 'Sleeping Bag Deuter Orbit +5°', 'sleeping-bag-deuter-orbit', 'Deuter', 'sleeping_bag', 1, 'Biru', 'Sleeping bag hangat bahan dacron tebal, kenyamanan hingga 5 derajat.', 20000.00, 100000.00, 10, 8, 'available', 'excellent', 1, NOW(), NOW()),
(4, 5, 3, 'Kompor Portable Mawar + Cooking Set Nesting DS-308', 'kompor-portable-nesting-set', 'Kovar', 'kompor_nesting', NULL, 'Silver', 'Paket kompor mawar windproof + nesting aluminium 3 panci.', 25000.00, 130000.00, 6, 5, 'available', 'good', 1, NOW(), NOW()),
(5, 5, 3, 'Carrier Eiger Rhinos 60L + Raincover', 'carrier-eiger-rhinos-60l', 'Eiger', 'carrier', NULL, 'Hitam', 'Tas gunung ergonomis busa tebal + gratis raincover.', 40000.00, 220000.00, 4, 3, 'available', 'excellent', 1, NOW(), NOW());

-- 8. Insert Sample Bookings
INSERT INTO `bookings` (`id`, `booking_code`, `user_id`, `category_id`, `item_type`, `item_id`, `vehicle_id`, `driver_id`, `rental_type`, `start_date`, `end_date`, `pickup_location`, `dropoff_location`, `with_driver`, `base_price`, `driver_price`, `total_price`, `discount`, `final_price`, `status`, `payment_status`, `notes`, `created_at`, `updated_at`) VALUES
-- Booking Mobil dengan Supir
(1, 'BOOK-202608-001', 4, 1, 'vehicle', 3, 3, 1, 'daily', '2026-08-20 08:00:00', '2026-08-22 08:00:00', 'Jl. Perintis Kemerdekaan No. 8', 'Bandung City Center', 1, 1100000.00, 500000.00, 1600000.00, 50000.00, 1550000.00, 'confirmed', 'paid', 'Sewa Innova 2 Hari plus Driver ke Bandung', NOW(), NOW()),
-- Booking Motor
(2, 'BOOK-202608-002', 6, 2, 'vehicle', 4, 4, NULL, 'daily', '2026-08-21 09:00:00', '2026-08-23 09:00:00', 'Garasi MariRent Banjar', 'Garasi MariRent Banjar', 0, 160000.00, 0.00, 160000.00, 0.00, 160000.00, 'confirmed', 'paid', 'Sewa Vario 160 selama 2 hari', NOW(), NOW()),
-- Booking HP
(3, 'BOOK-202608-003', 4, 3, 'phone', 2, NULL, NULL, 'daily', '2026-08-25 10:00:00', '2026-08-26 10:00:00', 'Toko MariRent Outdoor', 'Toko MariRent Outdoor', 0, 180000.00, 0.00, 180000.00, 0.00, 180000.00, 'pending', 'unpaid', 'Sewa Samsung S24 Ultra untuk konser', NOW(), NOW()),
-- Booking Alat Tenda Outdoor
(4, 'BOOK-202608-004', 6, 5, 'camping', 1, NULL, NULL, 'daily', '2026-08-28 14:00:00', '2026-08-30 14:00:00', 'MariRent Outdoor Banjar', 'MariRent Outdoor Banjar', 0, 120000.00, 0.00, 120000.00, 0.00, 120000.00, 'confirmed', 'paid', 'Sewa Tenda Eiger 4P 2 Hari untuk ke Gunung Sawal', NOW(), NOW());

-- 9. Insert Invoices
INSERT INTO `invoices` (`id`, `invoice_number`, `booking_id`, `user_id`, `owner_id`, `type`, `subtotal`, `tax_amount`, `discount_amount`, `total_amount`, `paid_amount`, `due_amount`, `status`, `payment_method`, `payment_reference`, `paid_at`, `due_date`, `notes`, `created_at`, `updated_at`) VALUES
(1, 'INV-202608-001', 1, 4, 2, 'rental', 1600000.00, 0.00, 50000.00, 1550000.00, 1550000.00, 0.00, 'paid', 'transfer', 'TRX-BCA-992831', NOW(), '2026-08-20 07:00:00', 'Lunas via Transfer BCA', NOW(), NOW()),
(2, 'INV-202608-002', 2, 6, 2, 'rental', 1600000.00, 0.00, 0.00, 160000.00, 160000.00, 0.00, 'paid', 'ewallet', 'QRIS-GOPAY-8821', NOW(), '2026-08-21 08:00:00', 'Lunas via QRIS', NOW(), NOW()),
(3, 'INV-202608-003', 3, 4, 3, 'rental', 180000.00, 0.00, 0.00, 180000.00, 0.00, 180000.00, 'sent', NULL, NULL, NULL, '2026-08-25 09:00:00', 'Menunggu Pembayaran', NOW(), NOW()),
(4, 'INV-202608-004', 4, 6, 3, 'rental', 120000.00, 0.00, 0.00, 120000.00, 120000.00, 0.00, 'paid', 'transfer', 'TRX-MANDIRI-1293', NOW(), '2026-08-28 12:00:00', 'Lunas Mandiri', NOW(), NOW());

-- 10. Insert Payments
INSERT INTO `payments` (`id`, `payment_code`, `invoice_id`, `user_id`, `amount`, `method`, `reference_number`, `proof_photo`, `status`, `paid_at`, `created_at`, `updated_at`) VALUES
(1, 'PAY-202608-001', 1, 4, 1550000.00, 'transfer', 'TRX-BCA-992831', 'proofs/pay1.jpg', 'verified', NOW(), NOW(), NOW()),
(2, 'PAY-202608-002', 2, 6, 160000.00, 'ewallet', 'QRIS-GOPAY-8821', 'proofs/pay2.jpg', 'verified', NOW(), NOW(), NOW()),
(3, 'PAY-202608-003', 4, 6, 120000.00, 'transfer', 'TRX-MANDIRI-1293', 'proofs/pay3.jpg', 'verified', NOW(), NOW(), NOW());

-- 11. Insert Inspections
INSERT INTO `inspections` (`id`, `booking_id`, `item_type`, `item_id`, `inspector_id`, `type`, `exterior_condition`, `interior_condition`, `engine_condition`, `overall_condition`, `fuel_level`, `notes`, `created_at`, `updated_at`) VALUES
(1, 1, 'vehicle', 3, 1, 'pre_rental', 5, 5, 5, 5, 100.00, 'Mobil Innova mulus, bensin penuh, AC dingin.', NOW(), NOW()),
(2, 2, 'vehicle', 4, 1, 'pre_rental', 5, 5, 5, 5, 100.00, 'Vario 160 rem mulus, helm 2 siap pakai.', NOW(), NOW());

-- 12. Insert Reviews
INSERT INTO `reviews` (`id`, `booking_id`, `user_id`, `item_type`, `item_id`, `rating`, `comment`, `is_visible`, `created_at`, `updated_at`) VALUES
(1, 1, 4, 'vehicle', 3, 5, 'Sangat puas sewa Innova! Mobil bersih dan Pak Ahmad drivernya sangat ramah.', 1, NOW(), NOW()),
(2, 2, 6, 'vehicle', 4, 5, 'Motor Vario masih sangat baru, tarikan enteng, mantap!', 1, NOW(), NOW());

SET FOREIGN_KEY_CHECKS = 1;