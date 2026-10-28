const bcrypt = require("bcryptjs");
const jwt = require("jsonwebtoken");
const db = require("../config/database");

const login = async (req, res) => {
  console.log("LOGIN ENDPOINT KEPANGGIL, body:", req.body);
  try {
    const { username, password } = req.body;

    if (!username || !password) {
      return res.status(400).json({
        success: false,
        message: "Username dan password wajib diisi",
      });
    }

    const [users] = await db.query(
      `SELECT id_user, nama, username, password_hash, role, is_active
       FROM users
       WHERE username = ?`,
      [username]
    );

    if (users.length === 0) {
      return res.status(401).json({
        success: false,
        message: "Username atau password salah",
      });
    }

    const user = users[0];

    if (!user.is_active) {
      return res.status(403).json({
        success: false,
        message: "Akun tidak aktif",
      });
    }

    const passwordValid = await bcrypt.compare(
      password,
      user.password_hash
    );

    if (!passwordValid) {
      return res.status(401).json({
        success: false,
        message: "Username atau password salah",
      });
    }

    const token = jwt.sign(
      {
        id_user: user.id_user,
        username: user.username,
        role: user.role,
      },
      process.env.JWT_SECRET,
      {
        expiresIn: "8h",
      }
    );

    res.json({
      success: true,
      message: "Login berhasil",
      data: {
        token,
        user: {
          id_user: user.id_user,
          nama: user.nama,
          username: user.username,
          role: user.role,
        },
      },
    });
  } catch (error) {
    console.error(error);

    res.status(500).json({
      success: false,
      message: "Terjadi kesalahan pada server",
    });
  }
};

module.exports = {
  login,
};