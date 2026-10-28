const db = require("../config/database");

// =========================
// INFO LAYANAN (publik)
// =========================
const getInfoLayanan = async (req, res) => {
  try {
    const [rows] = await db.query(
      `SELECT nama_layanan, jenis_satuan, harga_per_satuan
       FROM layanan
       WHERE is_active = 1
       ORDER BY nama_layanan ASC`
    );

    res.json({ success: true, data: rows });
  } catch (error) {
    console.error(error);
    res.status(500).json({ success: false, message: "Gagal mengambil informasi layanan" });
  }
};

// =========================
// RIWAYAT TRANSAKSI (by no_telepon)
// =========================
const getRiwayatTransaksi = async (req, res) => {
  try {
    const { no_telepon } = req.query;

    if (!no_telepon) {
      return res.status(400).json({
        success: false,
        message: "Parameter no_telepon wajib diisi",
      });
    }

    const [pelangganRows] = await db.query(
      `SELECT id_pelanggan, nama FROM pelanggan WHERE no_telepon = ?`,
      [no_telepon]
    );

    if (pelangganRows.length === 0) {
      return res.status(404).json({
        success: false,
        message: "Data pelanggan dengan nomor telepon tersebut tidak ditemukan",
      });
    }

    const [rows] = await db.query(
      `SELECT
        kode_transaksi, total_harga, status_pembayaran,
        status_pengambilan, created_at
      FROM transaksi
      WHERE id_pelanggan = ?
      ORDER BY created_at DESC`,
      [pelangganRows[0].id_pelanggan]
    );

    res.json({
      success: true,
      data: {
        nama_pelanggan: pelangganRows[0].nama,
        riwayat_transaksi: rows,
      },
    });
  } catch (error) {
    console.error(error);
    res.status(500).json({ success: false, message: "Gagal mengambil riwayat transaksi" });
  }
};

// =========================
// STATUS CUCIAN (by kode_transaksi)
// =========================
const getStatusCucian = async (req, res) => {
  try {
    const { kode_transaksi } = req.params;

    const [transaksiRows] = await db.query(
      `SELECT id_transaksi, kode_transaksi, status_pembayaran, status_pengambilan
       FROM transaksi
       WHERE kode_transaksi = ?`,
      [kode_transaksi]
    );

    if (transaksiRows.length === 0) {
      return res.status(404).json({
        success: false,
        message: "Transaksi tidak ditemukan",
      });
    }

    const transaksi = transaksiRows[0];

    const [prosesRows] = await db.query(
      `SELECT status_proses, catatan_proses, created_at
       FROM proses_cucian
       WHERE id_transaksi = ?
       ORDER BY id_proses ASC`,
      [transaksi.id_transaksi]
    );

    res.json({
      success: true,
      data: {
        kode_transaksi: transaksi.kode_transaksi,
        status_pembayaran: transaksi.status_pembayaran,
        status_pengambilan: transaksi.status_pengambilan,
        riwayat_proses: prosesRows,
      },
    });
  } catch (error) {
    console.error(error);
    res.status(500).json({ success: false, message: "Gagal mengambil status cucian" });
  }
};

// =========================
// BUKTI TRANSAKSI (by kode_transaksi)
// =========================
const getBuktiTransaksi = async (req, res) => {
  try {
    const { kode_transaksi } = req.params;

    const [transaksiRows] = await db.query(
      `SELECT
        t.id_transaksi, t.kode_transaksi, p.nama AS nama_pelanggan,
        p.no_telepon, t.total_harga, t.status_pembayaran,
        t.status_pengambilan, t.created_at
      FROM transaksi t
      JOIN pelanggan p ON t.id_pelanggan = p.id_pelanggan
      WHERE t.kode_transaksi = ?`,
      [kode_transaksi]
    );

    if (transaksiRows.length === 0) {
      return res.status(404).json({
        success: false,
        message: "Transaksi tidak ditemukan",
      });
    }

    const transaksi = transaksiRows[0];

    const [detailRows] = await db.query(
      `SELECT
        l.nama_layanan, dt.berat_jumlah, dt.harga_satuan, dt.subtotal
      FROM detail_transaksi dt
      JOIN layanan l ON dt.id_layanan = l.id_layanan
      WHERE dt.id_transaksi = ?`,
      [transaksi.id_transaksi]
    );

    res.json({
      success: true,
      data: {
        kode_transaksi: transaksi.kode_transaksi,
        nama_pelanggan: transaksi.nama_pelanggan,
        no_telepon: transaksi.no_telepon,
        tanggal: transaksi.created_at,
        detail_layanan: detailRows,
        total_harga: transaksi.total_harga,
        status_pembayaran: transaksi.status_pembayaran,
        status_pengambilan: transaksi.status_pengambilan,
      },
    });
  } catch (error) {
    console.error(error);
    res.status(500).json({ success: false, message: "Gagal mengambil bukti transaksi" });
  }
};

module.exports = {
  getInfoLayanan,
  getRiwayatTransaksi,
  getStatusCucian,
  getBuktiTransaksi,
};