const bcrypt = require("bcryptjs");
const db = require("../config/database");

const ROLE_VALID = ["ADMIN", "KASIR", "STAFF_LAUNDRY", "OWNER"];

const getAllPengguna = async (req, res) => {
  try {
    const [rows] = await db.query(
      `SELECT id_user, nama, username, role, is_active, created_at FROM users ORDER BY id_user ASC`
    );
    res.json({ success: true, data: rows });
  } catch (error) {
    console.error(error);
    res.status(500).json({ success: false, message: "Gagal mengambil data pengguna" });
  }
};

const getPenggunaById = async (req, res) => {
  try {
    const { id } = req.params;
    const [rows] = await db.query(
      `SELECT id_user, nama, username, role, is_active, created_at FROM users WHERE id_user = ?`,
      [id]
    );
    if (rows.length === 0) {
      return res.status(404).json({ success: false, message: "Pengguna tidak ditemukan" });
    }
    res.json({ success: true, data: rows[0] });
  } catch (error) {
    console.error(error);
    res.status(500).json({ success: false, message: "Gagal mengambil data pengguna" });
  }
};

const createPengguna = async (req, res) => {
  try {
    const { nama, username, password, role } = req.body;

    if (!nama || !username || !password || !role) {
      return res.status(400).json({
        success: false,
        message: "nama, username, password, dan role wajib diisi",
      });
    }

    if (!ROLE_VALID.includes(role)) {
      return res.status(400).json({
        success: false,
        message: `role harus salah satu dari: ${ROLE_VALID.join(", ")}`,
      });
    }

    const [existing] = await db.query(`SELECT id_user FROM users WHERE username = ?`, [username]);
    if (existing.length > 0) {
      return res.status(400).json({ success: false, message: "Username sudah digunakan" });
    }

    const password_hash = await bcrypt.hash(password, 10);

    const [result] = await db.query(
      `INSERT INTO users (nama, username, password_hash, role, is_active) VALUES (?, ?, ?, ?, 1)`,
      [nama, username, password_hash, role]
    );

    res.status(201).json({
      success: true,
      message: "Pengguna berhasil ditambahkan",
      data: { id_user: result.insertId, nama, username, role, is_active: 1 },
    });
  } catch (error) {
    console.error(error);
    res.status(500).json({ success: false, message: "Gagal menambahkan pengguna" });
  }
};

const updatePengguna = async (req, res) => {
  try {
    const { id } = req.params;
    const { nama, role, is_active } = req.body;

    if (!nama || !role || is_active === undefined) {
      return res.status(400).json({
        success: false,
        message: "nama, role, dan is_active wajib diisi",
      });
    }

    if (!ROLE_VALID.includes(role)) {
      return res.status(400).json({
        success: false,
        message: `role harus salah satu dari: ${ROLE_VALID.join(", ")}`,
      });
    }

    const [existing] = await db.query(`SELECT id_user FROM users WHERE id_user = ?`, [id]);
    if (existing.length === 0) {
      return res.status(404).json({ success: false, message: "Pengguna tidak ditemukan" });
    }

    await db.query(
      `UPDATE users SET nama = ?, role = ?, is_active = ? WHERE id_user = ?`,
      [nama, role, is_active ? 1 : 0, id]
    );

    res.json({ success: true, message: "Pengguna berhasil diperbarui" });
  } catch (error) {
    console.error(error);
    res.status(500).json({ success: false, message: "Gagal memperbarui pengguna" });
  }
};

const deletePengguna = async (req, res) => {
  try {
    const { id } = req.params;

    const [existing] = await db.query(`SELECT id_user FROM users WHERE id_user = ?`, [id]);
    if (existing.length === 0) {
      return res.status(404).json({ success: false, message: "Pengguna tidak ditemukan" });
    }

    await db.query(`DELETE FROM users WHERE id_user = ?`, [id]);

    res.json({ success: true, message: "Pengguna berhasil dihapus" });
  } catch (error) {
    console.error(error);
    res.status(500).json({
      success: false,
      message: "Gagal menghapus pengguna. Kemungkinan pengguna ini masih terkait data lain (transaksi/proses).",
    });
  }
};

const resetPassword = async (req, res) => {
  try {
    const { id } = req.params;
    const { password_baru } = req.body;

    if (!password_baru) {
      return res.status(400).json({ success: false, message: "password_baru wajib diisi" });
    }

    const [existing] = await db.query(`SELECT id_user FROM users WHERE id_user = ?`, [id]);
    if (existing.length === 0) {
      return res.status(404).json({ success: false, message: "Pengguna tidak ditemukan" });
    }

    const password_hash = await bcrypt.hash(password_baru, 10);

    await db.query(`UPDATE users SET password_hash = ? WHERE id_user = ?`, [password_hash, id]);

    res.json({ success: true, message: "Password berhasil direset" });
  } catch (error) {
    console.error(error);
    res.status(500).json({ success: false, message: "Gagal mereset password" });
  }
};

module.exports = {
  getAllPengguna,
  getPenggunaById,
  createPengguna,
  updatePengguna,
  deletePengguna,
  resetPassword,
};