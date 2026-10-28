const db = require("../config/database");

const STATUS_VALID = [
  "DITERIMA",
  "DICUCI",
  "DIKERINGKAN",
  "DISETRIKA",
  "SELESAI",
];

// Ambil semua proses cucian
const getAllProses = async (req, res) => {
  try {
    const [rows] = await db.query(
      `SELECT
        pc.id_proses,
        pc.id_transaksi,
        t.kode_transaksi,
        pc.status_proses,
        pc.catatan_proses,
        pc.created_at,
        u.nama AS nama_staff
      FROM proses_cucian pc
      JOIN transaksi t ON pc.id_transaksi = t.id_transaksi
      JOIN users u ON pc.id_staff = u.id_user
      ORDER BY pc.created_at DESC`
    );

    res.json({
      success: true,
      data: rows,
    });
  } catch (error) {
    console.error(error);

    res.status(500).json({
      success: false,
      message: "Gagal mengambil data proses cucian",
    });
  }
};

// Ambil riwayat proses berdasarkan transaksi
const getProsesByTransaksi = async (req, res) => {
  try {
    const { id_transaksi } = req.params;

    const [transaksiRows] = await db.query(
      `SELECT id_transaksi, kode_transaksi
       FROM transaksi
       WHERE id_transaksi = ?`,
      [id_transaksi]
    );

    if (transaksiRows.length === 0) {
      return res.status(404).json({
        success: false,
        message: "Transaksi tidak ditemukan",
      });
    }

    const [rows] = await db.query(
      `SELECT
        pc.id_proses,
        pc.id_transaksi,
        pc.status_proses,
        pc.catatan_proses,
        pc.created_at,
        u.nama AS nama_staff
      FROM proses_cucian pc
      JOIN users u ON pc.id_staff = u.id_user
      WHERE pc.id_transaksi = ?
      ORDER BY pc.id_proses ASC`,
      [id_transaksi]
    );

    res.json({
      success: true,
      data: {
        transaksi: transaksiRows[0],
        riwayat_proses: rows,
      },
    });
  } catch (error) {
    console.error(error);

    res.status(500).json({
      success: false,
      message: "Gagal mengambil riwayat proses",
    });
  }
};

// Tambah proses cucian
const createProses = async (req, res) => {
  try {
    const {
      id_transaksi,
      status_proses,
      catatan_proses,
    } = req.body;

    if (!id_transaksi || !status_proses) {
      return res.status(400).json({
        success: false,
        message: "id_transaksi dan status_proses wajib diisi",
      });
    }

    if (!STATUS_VALID.includes(status_proses)) {
      return res.status(400).json({
        success: false,
        message: `status_proses harus salah satu dari: ${STATUS_VALID.join(", ")}`,
      });
    }

    const [transaksiRows] = await db.query(
      `SELECT id_transaksi
       FROM transaksi
       WHERE id_transaksi = ?`,
      [id_transaksi]
    );

    if (transaksiRows.length === 0) {
      return res.status(404).json({
        success: false,
        message: "Transaksi tidak ditemukan",
      });
    }

    const [result] = await db.query(
      `INSERT INTO proses_cucian
       (id_transaksi, id_staff, status_proses, catatan_proses)
       VALUES (?, ?, ?, ?)`,
      [
        id_transaksi,
        req.user.id_user,
        status_proses,
        catatan_proses || null,
      ]
    );

    res.status(201).json({
      success: true,
      message: "Proses cucian berhasil dicatat",
      data: {
        id_proses: result.insertId,
        id_transaksi,
        status_proses,
        catatan_proses: catatan_proses || null,
      },
    });
  } catch (error) {
    console.error(error);

    res.status(500).json({
      success: false,
      message: "Gagal mencatat proses cucian",
    });
  }
};

module.exports = {
  getAllProses,
  getProsesByTransaksi,
  createProses,
};