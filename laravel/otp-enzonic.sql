CREATE TABLE IF NOT EXISTS `pemulihan_otp` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `no_telepon` VARCHAR(20) NOT NULL,
  `kode_hash` VARCHAR(64) NOT NULL,
  `percobaan` TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `kedaluwarsa` TIMESTAMP NULL DEFAULT NULL,
  `diverifikasi_pada` TIMESTAMP NULL DEFAULT NULL,
  `token_hash` VARCHAR(64) NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pemulihan_otp_no_telepon_index` (`no_telepon`),
  KEY `pemulihan_otp_token_hash_index` (`token_hash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;