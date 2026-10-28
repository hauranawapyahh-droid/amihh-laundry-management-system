const express = require("express");

const router = express.Router();

const { verifyToken } = require("../middleware/auth.middleware");
const { allowRoles } = require("../middleware/role.middleware");

const {
  createPembayaran,
  konfirmasiPembayaran,
} = require("../controllers/pembayaran.controller");

router.post(
  "/",
  verifyToken,
  allowRoles("KASIR"),
  createPembayaran
);

router.put(
  "/:id/konfirmasi",
  verifyToken,
  allowRoles("KASIR"),
  konfirmasiPembayaran
);

module.exports = router;