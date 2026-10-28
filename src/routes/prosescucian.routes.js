const express = require("express");

const router = express.Router();

const { verifyToken } = require("../middleware/auth.middleware");

const { allowRoles } = require("../middleware/role.middleware");

const {
  getAllProses,
  getProsesByTransaksi,
  createProses,
} = require("../controllers/prosescucian.controller");

// Ambil semua proses cucian
router.get(
  "/",
  verifyToken,
  allowRoles(
    "STAFF_LAUNDRY",
    "ADMIN",
    "OWNER",
    "KASIR"
  ),
  getAllProses
);

// Ambil riwayat proses berdasarkan transaksi
router.get(
  "/transaksi/:id_transaksi",
  verifyToken,
  allowRoles(
    "STAFF_LAUNDRY",
    "ADMIN",
    "OWNER",
    "KASIR"
  ),
  getProsesByTransaksi
);

// Tambah proses cucian
router.post(
  "/",
  verifyToken,
  allowRoles("STAFF_LAUNDRY"),
  createProses
);

module.exports = router;