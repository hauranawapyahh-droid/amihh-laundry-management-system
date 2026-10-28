const express = require("express");

const router = express.Router();

const { verifyToken } = require("../middleware/auth.middleware");
const { allowRoles } = require("../middleware/role.middleware");

const {
  laporanPendapatan,
  laporanBiaya,
  laporanKeuntungan,
} = require("../controllers/laporan.controller");

router.get("/pendapatan", verifyToken, allowRoles("ADMIN", "OWNER"), laporanPendapatan);
router.get("/biaya", verifyToken, allowRoles("ADMIN", "OWNER"), laporanBiaya);
router.get("/keuntungan", verifyToken, allowRoles("ADMIN", "OWNER"), laporanKeuntungan);

module.exports = router;