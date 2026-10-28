const db = require("../config/database");

const getAllLayanan = async (req, res) => {
  try {
    const [rows] = await db.query(`
      SELECT
        id_layanan,
        nama_layanan,
        jenis_satuan,
        harga_per_satuan,
        is_active,
        id_dibuat_oleh,
        created_at
      FROM layanan
      ORDER BY id_layanan DESC
    `);

    res.json({
      success: true,
      data: rows,
    });
  } catch (error) {
    console.error(error);

    res.status(500).json({
      success: false,
      message: "Gagal mengambil data layanan",
    });
  }
};

const getLayananById = async (req, res) => {
  try {
    const { id } = req.params;

    const [rows] = await db.query(
      `
      SELECT
        id_layanan,
        nama_layanan,
        jenis_satuan,
        harga_per_satuan,
        is_active,
        id_dibuat_oleh,
        created_at
      FROM layanan
      WHERE id_layanan = ?
      `,
      [id]
    );

    if (rows.length === 0) {
      return res.status(404).json({
        success: false,
        message: "Layanan tidak ditemukan",
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
      message: "Gagal mengambil detail layanan",
    });
  }
};

const createLayanan = async (req, res) => {
  try {
    const {
      nama_layanan,
      jenis_satuan,
      harga_per_satuan,
    } = req.body;

    if (!nama_layanan || !jenis_satuan || harga_per_satuan === undefined) {
      return res.status(400).json({
        success: false,
        message:
          "Nama layanan, jenis satuan, dan harga per satuan wajib diisi",
      });
    }

    if (!["KG", "PCS"].includes(jenis_satuan)) {
      return res.status(400).json({
        success: false,
        message: "Jenis satuan hanya boleh KG atau PCS",
      });
    }

    if (Number(harga_per_satuan) < 0) {
      return res.status(400).json({
        success: false,
        message: "Harga tidak boleh kurang dari 0",
      });
    }

    const [existing] = await db.query(
      `
      SELECT id_layanan
      FROM layanan
      WHERE nama_layanan = ?
      AND is_active = 1
      `,
      [nama_layanan]
    );

    if (existing.length > 0) {
      return res.status(409).json({
        success: false,
        message: "Nama layanan sudah terdaftar",
      });
    }

    const [result] = await db.query(
      `
      INSERT INTO layanan
        (
          nama_layanan,
          jenis_satuan,
          harga_per_satuan,
          is_active,
          id_dibuat_oleh
        )
      VALUES
        (?, ?, ?, 1, ?)
      `,
      [
        nama_layanan,
        jenis_satuan,
        harga_per_satuan,
        req.user.id_user,
      ]
    );

    res.status(201).json({
      success: true,
      message: "Layanan berhasil ditambahkan",
      data: {
        id_layanan: result.insertId,
        nama_layanan,
        jenis_satuan,
        harga_per_satuan,
        is_active: 1,
        id_dibuat_oleh: req.user.id_user,
      },
    });
  } catch (error) {
    console.error(error);

    res.status(500).json({
      success: false,
      message: "Gagal menambahkan layanan",
    });
  }
};

const updateLayanan = async (req, res) => {
  try {
    const { id } = req.params;

    const {
      nama_layanan,
      jenis_satuan,
      harga_per_satuan,
      is_active,
    } = req.body;

    if (!nama_layanan || !jenis_satuan || harga_per_satuan === undefined) {
      return res.status(400).json({
        success: false,
        message:
          "Nama layanan, jenis satuan, dan harga per satuan wajib diisi",
      });
    }

    if (!["KG", "PCS"].includes(jenis_satuan)) {
      return res.status(400).json({
        success: false,
        message: "Jenis satuan hanya boleh KG atau PCS",
      });
    }

    if (Number(harga_per_satuan) < 0) {
      return res.status(400).json({
        success: false,
        message: "Harga tidak boleh kurang dari 0",
      });
    }

    const [existingName] = await db.query(
      `
      SELECT id_layanan
      FROM layanan
      WHERE nama_layanan = ?
      AND id_layanan != ?
      AND is_active = 1
      `,
      [nama_layanan, id]
    );

    if (existingName.length > 0) {
      return res.status(409).json({
        success: false,
        message: "Nama layanan sudah digunakan",
      });
    }

    const [result] = await db.query(
      `
      UPDATE layanan
      SET
        nama_layanan = ?,
        jenis_satuan = ?,
        harga_per_satuan = ?,
        is_active = ?
      WHERE id_layanan = ?
      `,
      [
        nama_layanan,
        jenis_satuan,
        harga_per_satuan,
        is_active === undefined ? 1 : is_active ? 1 : 0,
        id,
      ]
    );

    if (result.affectedRows === 0) {
      return res.status(404).json({
        success: false,
        message: "Layanan tidak ditemukan",
      });
    }

    res.json({
      success: true,
      message: "Layanan berhasil diperbarui",
    });
  } catch (error) {
    console.error(error);

    res.status(500).json({
      success: false,
      message: "Gagal memperbarui layanan",
    });
  }
};

const deleteLayanan = async (req, res) => {
  try {
    const { id } = req.params;

    const [result] = await db.query(
      `
      DELETE FROM layanan
      WHERE id_layanan = ?
      `,
      [id]
    );

    if (result.affectedRows === 0) {
      return res.status(404).json({
        success: false,
        message: "Layanan tidak ditemukan",
      });
    }

    res.json({
      success: true,
      message: "Layanan berhasil dihapus",
    });
  } catch (error) {
    console.error(error);

    res.status(500).json({
      success: false,
      message: "Gagal menghapus layanan",
    });
  }
};

module.exports = {
  getAllLayanan,
  getLayananById,
  createLayanan,
  updateLayanan,
  deleteLayanan,
};