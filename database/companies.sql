-- ============================================================
-- MariRent - Tabel khusus COMPANY
-- Tabel terpisah untuk data perusahaan/toko pemilik merchant
-- (akun dengan role = owner). Dapat dijalankan langsung di HeidiSQL.
-- ============================================================

DROP TABLE IF EXISTS `companies`;

CREATE TABLE `companies` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `logo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `banner` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `company_email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `website` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `instagram` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pickup_address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `operational_hours` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `commission_rate` decimal(5,2) NOT NULL DEFAULT '0.00',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `status` enum('active','suspended','pending') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `verified_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `companies_slug_unique` (`slug`),
  KEY `companies_user_id_foreign` (`user_id`),
  CONSTRAINT `companies_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Backfill data perusahaan dari tabel merchants (khusus akun owner)
INSERT INTO `companies`
    (`user_id`, `slug`, `name`, `description`, `logo`, `banner`, `phone`, `company_email`,
     `website`, `instagram`, `address`, `city`, `pickup_address`, `operational_hours`,
     `commission_rate`, `is_active`, `status`, `verified_at`,
     `deleted_at`, `created_at`, `updated_at`)
SELECT
    m.`user_id`, m.`slug`, m.`name`, m.`description`, m.`logo`, m.`banner`, m.`phone`, m.`company_email`,
    m.`website`, m.`instagram`, m.`address`, m.`city`, m.`pickup_address`, m.`operational_hours`,
    m.`commission_rate`, m.`is_active`, m.`status`, m.`verified_at`,
    m.`deleted_at`, m.`created_at`, m.`updated_at`
FROM `merchants` m
JOIN `users` u ON u.`id` = m.`user_id`
WHERE u.`role` = 'owner';