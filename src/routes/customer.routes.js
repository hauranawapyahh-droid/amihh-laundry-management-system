const express = require("express");
const router = express.Router();

const {
  getInfoLayanan,
  getRiwayatTransaksi,
  getStatusCucian,
  getBuktiTransaksi,
} = require("../controllers/customer.controller");

// Semua endpoint di bawah ini PUBLIK (tanpa login/token)
router.get("/layanan", getInfoLayanan);
router.get("/riwayat", getRiwayatTransaksi);
router.get("/status/:kode_transaksi", getStatusCucian);
router.get("/bukti/:kode_transaksi", getBuktiTransaksi);

module.exports = router;