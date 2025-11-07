CREATE DATABASE IF NOT EXISTS `si.vmi_pendaftaran` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `si.vmi_pendaftaran`;

CREATE TABLE IF NOT EXISTS `schools` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `type` VARCHAR(10) NOT NULL,
  `city` VARCHAR(120) NOT NULL,
  `province` VARCHAR(120) NOT NULL,
  `level_group` ENUM('SD','SMP','SMA') NOT NULL DEFAULT 'SMA',
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `schools_type_index` (`type`),
  KEY `schools_city_index` (`city`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `programs` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(150) NOT NULL,
  `code` VARCHAR(20) NOT NULL,
  `class_category` ENUM('SD_SMP','X_XI','XII') NOT NULL,
  `registration_fee` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `tuition_fee` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `target_students` INT UNSIGNED NOT NULL DEFAULT 0,
  `target_revenue` DECIMAL(14,2) NOT NULL DEFAULT 0.00,
  `description` TEXT NULL,
  `image_path` VARCHAR(255) NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `programs_class_category_index` (`class_category`),
  UNIQUE KEY `programs_name_code_unique` (`name`,`code`,`class_category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `registrations` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `full_name` VARCHAR(255) NOT NULL,
  `school_id` INT UNSIGNED NULL,
  `school_name` VARCHAR(255) NOT NULL,
  `class_level` VARCHAR(10) NOT NULL,
  `phone_number` VARCHAR(20) NOT NULL,
  `province` VARCHAR(120) NOT NULL,
  `city` VARCHAR(120) NOT NULL,
  `district` VARCHAR(120) NOT NULL,
  `subdistrict` VARCHAR(120) NOT NULL,
  `postal_code` VARCHAR(10) NOT NULL,
  `address_detail` VARCHAR(255) NULL,
  `program_id` INT UNSIGNED NOT NULL,
  `student_status` ENUM('pending','active','graduated','dropped') NOT NULL DEFAULT 'pending',
  `payment_status` ENUM('unpaid','partial','paid') NOT NULL DEFAULT 'unpaid',
  `payment_notes` VARCHAR(255) NULL,
  `program_fee` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `registration_fee` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `discount_amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `total_due` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `amount_paid` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `balance_due` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `last_payment_at` DATE NULL,
  `study_location` ENUM('Bandung','Jaksel','Jaktim') DEFAULT NULL,
  `registration_number` VARCHAR(20) DEFAULT NULL,
  `invoice_number` VARCHAR(20) DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `registrations_school_id_index` (`school_id`),
  KEY `registrations_program_id_index` (`program_id`),
  KEY `registrations_study_location_index` (`study_location`),
  UNIQUE KEY `registrations_registration_number_unique` (`registration_number`),
  UNIQUE KEY `registrations_invoice_number_unique` (`invoice_number`),
  CONSTRAINT `registrations_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE SET NULL,
  CONSTRAINT `registrations_program_id_foreign` FOREIGN KEY (`program_id`) REFERENCES `programs` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `users` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(150) NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `status` ENUM('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `roles` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(120) NOT NULL,
  `slug` VARCHAR(120) NOT NULL,
  `description` VARCHAR(255) NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `permissions` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(150) NOT NULL,
  `slug` VARCHAR(150) NOT NULL,
  `description` VARCHAR(255) NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `role_user` (
  `role_id` BIGINT UNSIGNED NOT NULL,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`role_id`, `user_id`),
  KEY `role_user_user_id_index` (`user_id`),
  CONSTRAINT `role_user_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `permission_role` (
  `permission_id` BIGINT UNSIGNED NOT NULL,
  `role_id` BIGINT UNSIGNED NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`permission_id`, `role_id`),
  KEY `permission_role_role_id_index` (`role_id`),
  CONSTRAINT `permission_role_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `permission_role_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `permission_user` (
  `permission_id` BIGINT UNSIGNED NOT NULL,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`permission_id`, `user_id`),
  KEY `permission_user_user_id_index` (`user_id`),
  CONSTRAINT `permission_user_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `permission_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `schools` (`name`, `type`, `city`, `province`, `level_group`) VALUES
  ('SMAN 2 Bandung', 'SMAN', 'Kota Bandung', 'Jawa Barat', 'SMA'),
  ('SMAN 3 Bandung', 'SMAN', 'Kota Bandung', 'Jawa Barat', 'SMA'),
  ('SMAN 5 Bandung', 'SMAN', 'Kota Bandung', 'Jawa Barat', 'SMA'),
  ('SMAN 8 Bandung', 'SMAN', 'Kota Bandung', 'Jawa Barat', 'SMA'),
  ('SMAN 10 Bandung', 'SMAN', 'Kota Bandung', 'Jawa Barat', 'SMA'),
  ('SMAS 1 BPK Penabur Bandung', 'SMAS', 'Kota Bandung', 'Jawa Barat', 'SMA'),
  ('SMAS 2 BPK Penabur Bandung', 'SMAS', 'Kota Bandung', 'Jawa Barat', 'SMA'),
  ('SMAS Alfa Centauri Bandung', 'SMAS', 'Kota Bandung', 'Jawa Barat', 'SMA'),
  ('SMAS Santa Angela Bandung', 'SMAS', 'Kota Bandung', 'Jawa Barat', 'SMA'),
  ('SMAS Labschool Bandung', 'SMAS', 'Kota Bandung', 'Jawa Barat', 'SMA'),
  ('SMK Negeri 2 Bandung', 'SMK', 'Kota Bandung', 'Jawa Barat', 'SMA'),
  ('SMK Negeri 4 Bandung', 'SMK', 'Kota Bandung', 'Jawa Barat', 'SMA'),
  ('SMK Negeri 5 Bandung', 'SMK', 'Kota Bandung', 'Jawa Barat', 'SMA'),
  ('SMK Telkom Bandung', 'SMK', 'Kota Bandung', 'Jawa Barat', 'SMA'),
  ('MA Negeri 1 Kota Bandung', 'MA', 'Kota Bandung', 'Jawa Barat', 'SMA'),
  ('MA Persis 01 Bandung', 'MA', 'Kota Bandung', 'Jawa Barat', 'SMA'),
  ('SMAN 3 Jakarta', 'SMAN', 'Kota Jakarta Pusat', 'DKI Jakarta', 'SMA'),
  ('SMAN 8 Jakarta', 'SMAN', 'Kota Jakarta Selatan', 'DKI Jakarta', 'SMA'),
  ('SMAN 28 Jakarta', 'SMAN', 'Kota Jakarta Selatan', 'DKI Jakarta', 'SMA'),
  ('SMAN 34 Jakarta', 'SMAN', 'Kota Jakarta Selatan', 'DKI Jakarta', 'SMA'),
  ('SMAN 61 Jakarta', 'SMAN', 'Kota Jakarta Timur', 'DKI Jakarta', 'SMA'),
  ('SMAN 81 Jakarta', 'SMAN', 'Kota Jakarta Timur', 'DKI Jakarta', 'SMA'),
  ('SMAS Santa Ursula Jakarta', 'SMAS', 'Kota Jakarta Pusat', 'DKI Jakarta', 'SMA'),
  ('SMAS Labschool Kebayoran', 'SMAS', 'Kota Jakarta Selatan', 'DKI Jakarta', 'SMA'),
  ('SMAS Labschool Rawamangun', 'SMAS', 'Kota Jakarta Timur', 'DKI Jakarta', 'SMA'),
  ('SMAS BPK Penabur Gading Serpong', 'SMAS', 'Kota Tangerang Selatan', 'Banten', 'SMA'),
  ('SMAS Bina Nusantara Serpong', 'SMAS', 'Kota Tangerang Selatan', 'Banten', 'SMA'),
  ('SMAS Global Jaya', 'SMAS', 'Kota Tangerang Selatan', 'Banten', 'SMA'),
  ('SMK Negeri 26 Jakarta', 'SMK', 'Kota Jakarta Pusat', 'DKI Jakarta', 'SMA'),
  ('SMK Negeri 5 Jakarta', 'SMK', 'Kota Jakarta Timur', 'DKI Jakarta', 'SMA'),
  ('SMK Telkom Jakarta', 'SMK', 'Kota Jakarta Selatan', 'DKI Jakarta', 'SMA'),
  ('MA Negeri 1 Jakarta', 'MA', 'Kota Jakarta Pusat', 'DKI Jakarta', 'SMA'),
  ('MA Negeri 3 Jakarta', 'MA', 'Kota Jakarta Timur', 'DKI Jakarta', 'SMA'),
  ('SMAN 1 Depok', 'SMAN', 'Kota Depok', 'Jawa Barat', 'SMA'),
  ('SMAN 2 Depok', 'SMAN', 'Kota Depok', 'Jawa Barat', 'SMA'),
  ('SMAN 1 Bogor', 'SMAN', 'Kota Bogor', 'Jawa Barat', 'SMA'),
  ('SMAN 2 Bogor', 'SMAN', 'Kota Bogor', 'Jawa Barat', 'SMA'),
  ('SMAN 3 Tangerang', 'SMAN', 'Kota Tangerang', 'Banten', 'SMA'),
  ('SMAN 2 Bekasi', 'SMAN', 'Kota Bekasi', 'Jawa Barat', 'SMA'),
  ('SMAN 5 Bekasi', 'SMAN', 'Kota Bekasi', 'Jawa Barat', 'SMA');

INSERT INTO `programs` (`name`, `code`, `class_category`) VALUES
  ('SNBP', '1121209', 'XII'),
  ('SNBP - SNBT', '1121210', 'XII'),
  ('SNBT', '1121211', 'XII'),
  ('Seleksi Mandiri', '1121201', 'XII'),
  ('Beasiswa 1111202', '1111202', 'XII'),
  ('Bronze 1111202', '1111202', 'XII'),
  ('Silver 1111203', '1111203', 'XII'),
  ('Gold 1111205', '1111205', 'XII'),
  ('Advance 1111205', '1111205', 'XII'),
  ('Platinum 1111206', '1111206', 'XII'),
  ('Minat Seni 1111207', '1111207', 'XII'),
  ('Beasiswa 1221201', '1221201', 'XII'),
  ('Bronze 1221202', '1221202', 'XII'),
  ('Gold Short Course', '1221219', 'XII'),
  ('Gold 1211204', '1211204', 'XII'),
  ('Minsen', '1221007', 'X_XI'),
  ('Minat Seni', '1220907', 'SD_SMP');

INSERT INTO `roles` (`name`, `slug`, `description`) VALUES
  ('Administrator', 'admin', 'Akses penuh ke seluruh fitur dan konfigurasi.'),
  ('Staff', 'staff', 'Mengelola data pendaftar serta status pembayaran.'),
  ('Viewer', 'viewer', 'Melihat dashboard dan data ringkasan.')
ON DUPLICATE KEY UPDATE
  `name` = VALUES(`name`),
  `description` = VALUES(`description`);

INSERT INTO `permissions` (`name`, `slug`, `description`) VALUES
  ('Melihat dashboard pendaftar', 'view_dashboard', 'Mengakses halaman dashboard dan melihat data pendaftar.'),
  ('Memperbarui status pendaftar', 'update_registration_status', 'Mengubah status siswa dan pembayaran.'),
  ('Mengunduh data pendaftar', 'export_registrations', 'Menjalankan ekspor data pendaftar ke CSV.'),
  ('Melihat invoice pendaftar', 'view_invoice', 'Menerbitkan dan melihat dokumen invoice.'),
  ('Mengelola pengguna', 'manage_users', 'Membuat, memperbarui, dan menghapus akun pengguna.'),
  ('Mengelola peran', 'manage_roles', 'Membuat, memperbarui, dan menghapus peran beserta hak aksesnya.'),
  ('Mengelola izin', 'manage_permissions', 'Membuat, memperbarui, dan menghapus daftar izin.')
ON DUPLICATE KEY UPDATE
  `name` = VALUES(`name`),
  `description` = VALUES(`description`);

INSERT INTO `users` (`name`, `email`, `password`, `status`) VALUES
  ('Administrator', 'admin@si-vmi.local', '$2y$12$s92Nae1oSMEJJh/an0Yd4eKmA/iDcI.qYByuQdiPbBSFIKhqnIbRW', 'active')
ON DUPLICATE KEY UPDATE
  `name` = VALUES(`name`),
  `password` = VALUES(`password`),
  `status` = VALUES(`status`);

INSERT IGNORE INTO `role_user` (`role_id`, `user_id`)
SELECT r.id, u.id
FROM roles r
JOIN users u ON u.email = 'admin@si-vmi.local'
WHERE r.slug = 'admin';

INSERT IGNORE INTO `permission_role` (`permission_id`, `role_id`)
SELECT p.id, r.id
FROM permissions p
JOIN roles r ON r.slug = 'admin';

INSERT IGNORE INTO `permission_role` (`permission_id`, `role_id`)
SELECT p.id, r.id
FROM permissions p
JOIN roles r ON r.slug = 'staff'
WHERE p.slug IN ('view_dashboard', 'update_registration_status', 'export_registrations', 'view_invoice');

INSERT IGNORE INTO `permission_role` (`permission_id`, `role_id`)
SELECT p.id, r.id
FROM permissions p
JOIN roles r ON r.slug = 'viewer'
WHERE p.slug IN ('view_dashboard', 'view_invoice');
