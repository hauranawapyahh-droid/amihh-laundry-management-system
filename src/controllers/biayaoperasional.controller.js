const db = require("../config/database");

const getAllBiaya = async (req, res) => {
  try {
    const [rows] = await db.query(
      `SELECT
        b.id_biaya, b.nama_biaya, b.kategori, b.jumlah_biaya,
        b.tanggal_biaya, b.keterangan, b.created_at, u.nama AS nama_pencatat
      FROM biaya_operasional b
      JOIN users u ON b.id_pencatat = u.id_user
      ORDER BY b.tanggal_biaya DESC`
    );
    res.json({ success: true, data: rows });
  } catch (error) {
    console.error(error);
    res.status(500).json({ success: false, message: "Gagal mengambil data biaya operasional" });
  }
};

const getBiayaById = async (req, res) => {
  try {
    const { id } = req.params;
    const [rows] = await db.query(
      `SELECT id_biaya, nama_biaya, kategori, jumlah_biaya, tanggal_biaya, keterangan, created_at
       FROM biaya_operasional WHERE id_biaya = ?`,
      [id]
    );
    if (rows.length === 0) {
      return res.status(404).json({ success: false, message: "Biaya tidak ditemukan" });
    }
    res.json({ success: true, data: rows[0] });
  } catch (error) {
    console.error(error);
    res.status(500).json({ success: false, message: "Gagal mengambil data biaya" });
  }
};

const createBiaya = async (req, res) => {
  try {
    const { nama_biaya, kategori, jumlah_biaya, tanggal_biaya, keterangan } = req.body;

    if (!nama_biaya || !kategori || jumlah_biaya === undefined || !tanggal_biaya) {
      return res.status(400).json({
        success: false,
        message: "nama_biaya, kategori, jumlah_biaya, dan tanggal_biaya wajib diisi",
      });
    }

    const [result] = await db.query(
      `INSERT INTO biaya_operasional
       (id_pencatat, nama_biaya, kategori, jumlah_biaya, tanggal_biaya, keterangan)
       VALUES (?, ?, ?, ?, ?, ?)`,
      [req.user.id_user, nama_biaya, kategori, jumlah_biaya, tanggal_biaya, keterangan || null]
    );

    res.status(201).json({
      success: true,
      message: "Biaya operasional berhasil ditambahkan",
      data: { id_biaya: result.insertId, nama_biaya, kategori, jumlah_biaya, tanggal_biaya },
    });
  } catch (error) {
    console.error(error);
    res.status(500).json({ success: false, message: "Gagal menambahkan biaya" });
  }
};

const updateBiaya = async (req, res) => {
  try {
    const { id } = req.params;
    const { nama_biaya, kategori, jumlah_biaya, tanggal_biaya, keterangan } = req.body;

    if (!nama_biaya || !kategori || jumlah_biaya === undefined || !tanggal_biaya) {
      return res.status(400).json({
        success: false,
        message: "nama_biaya, kategori, jumlah_biaya, dan tanggal_biaya wajib diisi",
      });
    }

    const [existing] = await db.query(`SELECT id_biaya FROM biaya_operasional WHERE id_biaya = ?`, [id]);
    if (existing.length === 0) {
      return res.status(404).json({ success: false, message: "Biaya tidak ditemukan" });
    }

    await db.query(
      `UPDATE biaya_operasional
       SET nama_biaya = ?, kategori = ?, jumlah_biaya = ?, tanggal_biaya = ?, keterangan = ?
       WHERE id_biaya = ?`,
      [nama_biaya, kategori, jumlah_biaya, tanggal_biaya, keterangan || null, id]
    );

    res.json({ success: true, message: "Biaya operasional berhasil diperbarui" });
  } catch (error) {
    console.error(error);
    res.status(500).json({ success: false, message: "Gagal memperbarui biaya" });
  }
};

const deleteBiaya = async (req, res) => {
  try {
    const { id } = req.params;

    const [existing] = await db.query(`SELECT id_biaya FROM biaya_operasional WHERE id_biaya = ?`, [id]);
    if (existing.length === 0) {
      return res.status(404).json({ success: false, message: "Biaya tidak ditemukan" });
    }

    await db.query(`DELETE FROM biaya_operasional WHERE id_biaya = ?`, [id]);

    res.json({ success: true, message: "Biaya operasional berhasil dihapus" });
  } catch (error) {
    console.error(error);
    res.status(500).json({ success: false, message: "Gagal menghapus biaya" });
  }
};

module.exports = {
  getAllBiaya,
  getBiayaById,
  createBiaya,
  updateBiaya,
  deleteBiaya,
};