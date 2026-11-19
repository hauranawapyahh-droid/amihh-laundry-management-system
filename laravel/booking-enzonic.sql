CREATE TABLE IF NOT EXISTS `booking` (
  `id_booking` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_pelanggan` BIGINT UNSIGNED NOT NULL,
  `tanggal_booking` DATE NOT NULL,
  `urutan` INT UNSIGNED NOT NULL,
  `nomor_antrean` VARCHAR(20) NOT NULL,
  `status` VARCHAR(20) NOT NULL DEFAULT 'MENUNGGU',
  `catatan` TEXT NULL,
  `alasan_batal` VARCHAR(255) NULL,
  `id_transaksi` BIGINT UNSIGNED NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id_booking`),
  UNIQUE KEY `booking_tanggal_urutan_unique` (`tanggal_booking`,`urutan`),
  UNIQUE KEY `booking_id_transaksi_unique` (`id_transaksi`),
  KEY `booking_id_pelanggan_index` (`id_pelanggan`),
  KEY `booking_status_index` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `booking_detail` (
  `id_booking_detail` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_booking` BIGINT UNSIGNED NOT NULL,
  `id_layanan` BIGINT UNSIGNED NOT NULL,
  `jumlah` DECIMAL(10,2) NOT NULL,
  PRIMARY KEY (`id_booking_detail`),
  KEY `booking_detail_id_booking_index` (`id_booking`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;