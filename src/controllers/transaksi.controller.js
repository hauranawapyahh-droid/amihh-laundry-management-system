const db = require("../config/database");

// =========================
// CREATE TRANSAKSI
// =========================
const createTransaksi = async (req, res) => {
  const connection = await db.getConnection();

  try {
    const { id_pelanggan, items, catatan } = req.body;

    if (!id_pelanggan) {
      return res.status(400).json({
        success: false,
        message: "id_pelanggan wajib diisi",
      });
    }

    if (!items || !Array.isArray(items) || items.length === 0) {
      return res.status(400).json({
        success: false,
        message: "Items transaksi wajib diisi",
      });
    }

    await connection.beginTransaction();

    let total_harga = 0;

    for (const item of items) {
      const [layanan] = await connection.query(
        `SELECT id_layanan, harga_per_satuan FROM layanan WHERE id_layanan = ?`,
        [item.id_layanan]
      );

      if (layanan.length === 0) {
        throw new Error(`Layanan ID ${item.id_layanan} tidak ditemukan`);
      }

      total_harga +=
        Number(layanan[0].harga_per_satuan) * Number(item.berat_jumlah);
    }

    const kode_transaksi = "TRX" + Date.now();

    const [transaksiResult] = await connection.query(
      `INSERT INTO transaksi
      (kode_transaksi, id_pelanggan, id_kasir, total_harga, status_pembayaran, status_pengambilan, catatan)
      VALUES (?, ?, ?, ?, 'BELUM_DIBAYAR', 'BELUM_DIAMBIL', ?)`,
      [kode_transaksi, id_pelanggan, req.user.id_user, total_harga, catatan || null]
    );

    const id_transaksi = transaksiResult.insertId;

    for (const item of items) {
      const [layanan] = await connection.query(
        `SELECT harga_per_satuan FROM layanan WHERE id_layanan = ?`,
        [item.id_layanan]
      );

      const harga = Number(layanan[0].harga_per_satuan);
      const subtotal = harga * Number(item.berat_jumlah);

      await connection.query(
        `INSERT INTO detail_transaksi (id_transaksi, id_layanan, berat_jumlah, harga_satuan, subtotal)
        VALUES (?, ?, ?, ?, ?)`,
        [id_transaksi, item.id_layanan, item.berat_jumlah, harga, subtotal]
      );
    }

    await connection.commit();

    res.status(201).json({
      success: true,
      message: "Transaksi berhasil dibuat",
      data: { id_transaksi, kode_transaksi, total_harga },
    });
  } catch (error) {
    await connection.rollback();
    console.error(error);
    res.status(500).json({
      success: false,
      message: "Gagal membuat transaksi",
      error: error.message,
    });
  } finally {
    connection.release();
  }
};

// =========================
// GET ALL TRANSAKSI
// =========================
const getAllTransaksi = async (req, res) => {
  try {
    const [rows] = await db.query(
      `SELECT
        t.id_transaksi, t.kode_transaksi, p.nama AS nama_pelanggan,
        t.total_harga, t.status_pembayaran, t.status_pengambilan, t.created_at
      FROM transaksi t
      JOIN pelanggan p ON t.id_pelanggan = p.id_pelanggan
      ORDER BY t.id_transaksi DESC`
    );

    res.json({ success: true, data: rows });
  } catch (error) {
    console.error(error);
    res.status(500).json({ success: false, message: "Gagal mengambil data transaksi" });
  }
};

// =========================
// CARI TRANSAKSI (by kode_transaksi)
// =========================
const cariTransaksi = async (req, res) => {
  try {
    const { kode } = req.query;

    if (!kode) {
      return res.status(400).json({
        success: false,
        message: "Parameter kode wajib diisi",
      });
    }

    const [rows] = await db.query(
      `SELECT
        t.id_transaksi, t.kode_transaksi, p.nama AS nama_pelanggan,
        t.total_harga, t.status_pembayaran, t.status_pengambilan,
        t.waktu_pengambilan, t.created_at
      FROM transaksi t
      JOIN pelanggan p ON t.id_pelanggan = p.id_pelanggan
      WHERE t.kode_transaksi LIKE ?
      ORDER BY t.id_transaksi DESC`,
      [`%${kode}%`]
    );

    res.json({ success: true, data: rows });
  } catch (error) {
    console.error(error);
    res.status(500).json({ success: false, message: "Gagal mencari transaksi" });
  }
};

// =========================
// UPDATE STATUS PENGAMBILAN
// =========================
const ambilTransaksi = async (req, res) => {
  try {
    const { id } = req.params;

    const [rows] = await db.query(
      `SELECT id_transaksi, status_pembayaran, status_pengambilan FROM transaksi WHERE id_transaksi = ?`,
      [id]
    );

    if (rows.length === 0) {
      return res.status(404).json({ success: false, message: "Transaksi tidak ditemukan" });
    }

    const transaksi = rows[0];

    if (transaksi.status_pembayaran !== "LUNAS") {
      return res.status(400).json({
        success: false,
        message: "Transaksi belum lunas, tidak bisa diambil",
      });
    }

    if (transaksi.status_pengambilan === "SUDAH_DIAMBIL") {
      return res.status(400).json({
        success: false,
        message: "Cucian ini sudah diambil sebelumnya",
      });
    }

    await db.query(
      `UPDATE transaksi
       SET status_pengambilan = 'SUDAH_DIAMBIL', waktu_pengambilan = NOW()
       WHERE id_transaksi = ?`,
      [id]
    );

    res.json({
      success: true,
      message: "Status pengambilan berhasil diperbarui",
      data: { id_transaksi: id, status_pengambilan: "SUDAH_DIAMBIL" },
    });
  } catch (error) {
    console.error(error);
    res.status(500).json({ success: false, message: "Gagal memperbarui status pengambilan" });
  }
};

module.exports = {
  createTransaksi,
  getAllTransaksi,
  cariTransaksi,
  ambilTransaksi,
};