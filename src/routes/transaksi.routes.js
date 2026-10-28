const express = require("express");

const router = express.Router();

const { verifyToken } = require("../middleware/auth.middleware");
const { allowRoles } = require("../middleware/role.middleware");

const {
  createTransaksi,
  getAllTransaksi,
  cariTransaksi,
  ambilTransaksi,
} = require("../controllers/transaksi.controller");

router.get(
  "/cari",
  verifyToken,
  allowRoles("KASIR", "ADMIN", "OWNER"),
  cariTransaksi
);

router.get(
  "/",
  verifyToken,
  allowRoles("KASIR", "ADMIN", "OWNER", "STAFF_LAUNDRY"),
  getAllTransaksi
);

router.post(
  "/",
  verifyToken,
  allowRoles("KASIR"),
  createTransaksi
);

router.put(
  "/:id/ambil",
  verifyToken,
  allowRoles("KASIR"),
  ambilTransaksi
);

module.exports = router;