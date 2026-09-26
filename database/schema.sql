-- =========================================================
-- AMIH LAUNDRY MANAGEMENT SYSTEM
-- Database Schema
-- MySQL / InnoDB
-- =========================================================

CREATE DATABASE IF NOT EXISTS amih_laundry_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE amih_laundry_db;

-- =========================================================
-- 1. USERS
-- =========================================================

CREATE TABLE users (
    id_user INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM(
        'ADMIN',
        'KASIR',
        'STAFF_LAUNDRY',
        'OWNER'
    ) NOT NULL,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- =========================================================
-- 2. PELANGGAN
-- =========================================================

CREATE TABLE pelanggan (
    id_pelanggan INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    no_telepon VARCHAR(20) NOT NULL UNIQUE,
    alamat VARCHAR(255),
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- =========================================================
-- 3. LAYANAN
-- =========================================================

CREATE TABLE layanan (
    id_layanan INT AUTO_INCREMENT PRIMARY KEY,
    nama_layanan VARCHAR(100) NOT NULL,
    jenis_satuan ENUM('KG', 'PCS') NOT NULL,
    harga_per_satuan DECIMAL(12,2) NOT NULL,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    id_dibuat_oleh INT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_layanan_pembuat
        FOREIGN KEY (id_dibuat_oleh)
        REFERENCES users(id_user)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB;

-- =========================================================
-- 4. TRANSAKSI
-- =========================================================

CREATE TABLE transaksi (
    id_transaksi INT AUTO_INCREMENT PRIMARY KEY,
    kode_transaksi VARCHAR(30) NOT NULL UNIQUE,
    id_pelanggan INT NOT NULL,
    id_kasir INT NOT NULL,
    id_verifikator INT NULL,
    total_harga DECIMAL(12,2) NOT NULL DEFAULT 0.00,

    status_pembayaran ENUM(
        'BELUM_DIBAYAR',
        'LUNAS'
    ) NOT NULL DEFAULT 'BELUM_DIBAYAR',

    status_pengambilan ENUM(
        'BELUM_DIAMBIL',
        'SUDAH_DIAMBIL'
    ) NOT NULL DEFAULT 'BELUM_DIAMBIL',

    waktu_pengambilan DATETIME NULL,
    catatan TEXT NULL,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_transaksi_pelanggan
        FOREIGN KEY (id_pelanggan)
        REFERENCES pelanggan(id_pelanggan)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_transaksi_kasir
        FOREIGN KEY (id_kasir)
        REFERENCES users(id_user)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_transaksi_verifikator
        FOREIGN KEY (id_verifikator)
        REFERENCES users(id_user)
        ON UPDATE CASCADE
        ON DELETE SET NULL
) ENGINE=InnoDB;

-- =========================================================
-- 5. DETAIL_TRANSAKSI
-- =========================================================

CREATE TABLE detail_transaksi (
    id_detail INT AUTO_INCREMENT PRIMARY KEY,
    id_transaksi INT NOT NULL,
    id_layanan INT NOT NULL,
    berat_jumlah DECIMAL(10,2) NOT NULL,
    harga_satuan DECIMAL(12,2) NOT NULL,
    subtotal DECIMAL(12,2) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_detail_transaksi
        FOREIGN KEY (id_transaksi)
        REFERENCES transaksi(id_transaksi)
        ON UPDATE CASCADE
        ON DELETE CASCADE,

    CONSTRAINT fk_detail_layanan
        FOREIGN KEY (id_layanan)
        REFERENCES layanan(id_layanan)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB;

-- =========================================================
-- 6. PEMBAYARAN
-- =========================================================

CREATE TABLE pembayaran (
    id_pembayaran INT AUTO_INCREMENT PRIMARY KEY,
    id_transaksi INT NOT NULL,

    metode_pembayaran ENUM(
        'TUNAI',
        'TRANSFER_BANK',
        'QRIS'
    ) NOT NULL,

    jumlah_bayar DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    kembalian DECIMAL(12,2) NOT NULL DEFAULT 0.00,

    status_konfirmasi ENUM(
        'PENDING',
        'CONFIRMED'
    ) NOT NULL DEFAULT 'PENDING',

    catatan_pelunasan TEXT NULL,
    id_konfirmator INT NULL,
    waktu_konfirmasi DATETIME NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_pembayaran_transaksi
        FOREIGN KEY (id_transaksi)
        REFERENCES transaksi(id_transaksi)
        ON UPDATE CASCADE
        ON DELETE CASCADE,

    CONSTRAINT fk_pembayaran_konfirmator
        FOREIGN KEY (id_konfirmator)
        REFERENCES users(id_user)
        ON UPDATE CASCADE
        ON DELETE SET NULL
) ENGINE=InnoDB;

-- =========================================================
-- 7. PROSES_CUCIAN
-- =========================================================

CREATE TABLE proses_cucian (
    id_proses INT AUTO_INCREMENT PRIMARY KEY,
    id_transaksi INT NOT NULL,
    id_staff INT NOT NULL,

    status_proses ENUM(
        'DITERIMA',
        'DICUCI',
        'DIKERINGKAN',
        'DISETRIKA',
        'SELESAI'
    ) NOT NULL DEFAULT 'DITERIMA',

    catatan_proses TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_proses_transaksi
        FOREIGN KEY (id_transaksi)
        REFERENCES transaksi(id_transaksi)
        ON UPDATE CASCADE
        ON DELETE CASCADE,

    CONSTRAINT fk_proses_staff
        FOREIGN KEY (id_staff)
        REFERENCES users(id_user)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB;

-- =========================================================
-- 8. BIAYA_OPERASIONAL
-- =========================================================

CREATE TABLE biaya_operasional (
    id_biaya INT AUTO_INCREMENT PRIMARY KEY,
    id_pencatat INT NOT NULL,
    nama_biaya VARCHAR(100) NOT NULL,
    kategori VARCHAR(100) NOT NULL,
    jumlah_biaya DECIMAL(12,2) NOT NULL,
    tanggal_biaya DATE NOT NULL,
    keterangan TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_biaya_pencatat
        FOREIGN KEY (id_pencatat)
        REFERENCES users(id_user)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB;

-- =========================================================
-- INDEX
-- =========================================================

CREATE INDEX idx_transaksi_pelanggan
    ON transaksi(id_pelanggan);

CREATE INDEX idx_transaksi_kasir
    ON transaksi(id_kasir);

CREATE INDEX idx_transaksi_verifikator
    ON transaksi(id_verifikator);

CREATE INDEX idx_detail_transaksi
    ON detail_transaksi(id_transaksi);

CREATE INDEX idx_detail_layanan
    ON detail_transaksi(id_layanan);

CREATE INDEX idx_pembayaran_transaksi
    ON pembayaran(id_transaksi);

CREATE INDEX idx_proses_transaksi
    ON proses_cucian(id_transaksi);

CREATE INDEX idx_proses_staff
    ON proses_cucian(id_staff);

CREATE INDEX idx_biaya_pencatat
    ON biaya_operasional(id_pencatat);