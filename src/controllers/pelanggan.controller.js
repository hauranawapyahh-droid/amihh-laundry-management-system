const db = require("../config/database");

const getAllPelanggan = async (req, res) => {
  try {
    const [rows] = await db.query(`
      SELECT
        id_pelanggan,
        nama,
        no_telepon,
        alamat,
        created_at
      FROM pelanggan
      ORDER BY id_pelanggan DESC
    `);

    res.json({
      success: true,
      data: rows,
    });
  } catch (error) {
    console.error(error);

    res.status(500).json({
      success: false,
      message: "Gagal mengambil data pelanggan",
    });
  }
};

const getPelangganById = async (req, res) => {
  try {
    const { id } = req.params;

    const [rows] = await db.query(
      `
      SELECT
        id_pelanggan,
        nama,
        no_telepon,
        alamat,
        created_at
      FROM pelanggan
      WHERE id_pelanggan = ?
      `,
      [id]
    );

    if (rows.length === 0) {
      return res.status(404).json({
        success: false,
        message: "Pelanggan tidak ditemukan",
      });
    }

    res.json({
      success: true,
      data: rows[0],
    });
  } catch (error) {
    console.error(error);

    res.status(500).json({
      success: false,
      message: "Gagal mengambil detail pelanggan",
    });
  }
};

const createPelanggan = async (req, res) => {
  try {
    const { nama, no_telepon, alamat } = req.body;

    if (!nama || !no_telepon) {
      return res.status(400).json({
        success: false,
        message: "Nama dan nomor telepon wajib diisi",
      });
    }

    const [existing] = await db.query(
      "SELECT id_pelanggan FROM pelanggan WHERE no_telepon = ?",
      [no_telepon]
    );

    if (existing.length > 0) {
      return res.status(409).json({
        success: false,
        message: "Nomor telepon sudah terdaftar",
      });
    }

    const [result] = await db.query(
      `
      INSERT INTO pelanggan
        (nama, no_telepon, alamat)
      VALUES
        (?, ?, ?)
      `,
      [nama, no_telepon, alamat || null]
    );

    res.status(201).json({
      success: true,
      message: "Pelanggan berhasil ditambahkan",
      data: {
        id_pelanggan: result.insertId,
        nama,
        no_telepon,
        alamat: alamat || null,
      },
    });
  } catch (error) {
    console.error(error);

    res.status(500).json({
      success: false,
      message: "Gagal menambahkan pelanggan",
    });
  }
};

const updatePelanggan = async (req, res) => {
  try {
    const { id } = req.params;
    const { nama, no_telepon, alamat } = req.body;

    if (!nama || !no_telepon) {
      return res.status(400).json({
        success: false,
        message: "Nama dan nomor telepon wajib diisi",
      });
    }

    const [existing] = await db.query(
      `
      SELECT id_pelanggan
      FROM pelanggan
      WHERE no_telepon = ?
      AND id_pelanggan != ?
      `,
      [no_telepon, id]
    );

    if (existing.length > 0) {
      return res.status(409).json({
        success: false,
        message: "Nomor telepon sudah digunakan pelanggan lain",
      });
    }

    const [result] = await db.query(
      `
      UPDATE pelanggan
      SET
        nama = ?,
        no_telepon = ?,
        alamat = ?
      WHERE id_pelanggan = ?
      `,
      [nama, no_telepon, alamat || null, id]
    );

    if (result.affectedRows === 0) {
      return res.status(404).json({
        success: false,
        message: "Pelanggan tidak ditemukan",
      });
    }

    res.json({
      success: true,
      message: "Pelanggan berhasil diperbarui",
    });
  } catch (error) {
    console.error(error);

    res.status(500).json({
      success: false,
      message: "Gagal memperbarui pelanggan",
    });
  }
};

const deletePelanggan = async (req, res) => {
  try {
    const { id } = req.params;

    const [result] = await db.query(
      "DELETE FROM pelanggan WHERE id_pelanggan = ?",
      [id]
    );

    if (result.affectedRows === 0) {
      return res.status(404).json({
        success: false,
        message: "Pelanggan tidak ditemukan",
      });
    }

    res.json({
      success: true,
      message: "Pelanggan berhasil dihapus",
    });
  } catch (error) {
    console.error(error);

    res.status(500).json({
      success: false,
      message: "Gagal menghapus pelanggan",
    });
  }
};

const searchPelanggan = async (req, res) => {
  try {
    const { q } = req.query;

    if (!q) {
      return res.status(400).json({
        success: false,
        message: "Kata pencarian wajib diisi",
      });
    }

    const [rows] = await db.query(
      `
      SELECT
        id_pelanggan,
        nama,
        no_telepon,
        alamat,
        created_at
      FROM pelanggan
      WHERE nama LIKE ?
         OR no_telepon LIKE ?
      ORDER BY id_pelanggan DESC
      `,
      [`%${q}%`, `%${q}%`]
    );

    res.json({
      success: true,
      data: rows,
    });
  } catch (error) {
    console.error(error);

    res.status(500).json({
      success: false,
      message: "Gagal mencari pelanggan",
    });
  }
};

module.exports = {
  getAllPelanggan,
  getPelangganById,
  createPelanggan,
  updatePelanggan,
  deletePelanggan,
  searchPelanggan,
};