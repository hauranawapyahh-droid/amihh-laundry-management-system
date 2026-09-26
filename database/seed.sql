-- =========================================================
-- AMIH LAUNDRY MANAGEMENT SYSTEM
-- Seed Data
-- =========================================================

USE amih_laundry_db;

-- =========================================================
-- USERS
-- Password sementara untuk development.
-- Akan diganti dengan bcrypt saat authentication dibuat.
-- =========================================================

INSERT INTO users
    (id_user, nama, username, password_hash, role, is_active)
VALUES
    (1, 'Administrator', 'admin', 'CHANGE_ME', 'ADMIN', TRUE),
    (2, 'Kasir Amih Laundry', 'kasir', 'CHANGE_ME', 'KASIR', TRUE),
    (3, 'Staff Laundry', 'staff', 'CHANGE_ME', 'STAFF_LAUNDRY', TRUE),
    (4, 'Owner Amih Laundry', 'owner', 'CHANGE_ME', 'OWNER', TRUE);

-- =========================================================
-- PELANGGAN
-- =========================================================

INSERT INTO pelanggan
    (id_pelanggan, nama, no_telepon, alamat)
VALUES
    (1, 'Pelanggan Contoh 1', '081234567890', 'Ciwidey'),
    (2, 'Pelanggan Contoh 2', '081298765432', 'Panundaan');

-- =========================================================
-- LAYANAN
-- =========================================================

INSERT INTO layanan
    (id_layanan, nama_layanan, jenis_satuan, harga_per_satuan, is_active, id_dibuat_oleh)
VALUES
    (1, 'Cuci Kering', 'KG', 7000.00, TRUE, 1),
    (2, 'Cuci Setrika', 'KG', 9000.00, TRUE, 1),
    (3, 'Setrika', 'KG', 5000.00, TRUE, 1),
    (4, 'Cuci Satuan', 'PCS', 10000.00, TRUE, 1);

-- =========================================================
-- BIAYA OPERASIONAL
-- =========================================================

INSERT INTO biaya_operasional
    (id_biaya, id_pencatat, nama_biaya, kategori, jumlah_biaya, tanggal_biaya, keterangan)
VALUES
    (1, 1, 'Pembelian Deterjen', 'Bahan Laundry', 150000.00, '2026-09-01', 'Data contoh'),
    (2, 1, 'Listrik', 'Utilitas', 300000.00, '2026-09-05', 'Data contoh'),
    (3, 1, 'Air', 'Utilitas', 150000.00, '2026-09-05', 'Data contoh');