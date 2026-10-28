const express = require("express");

const router = express.Router();

const { verifyToken } = require("../middleware/auth.middleware");
const { allowRoles } = require("../middleware/role.middleware");

const {
  getDashboard,
  getLaporanPeriode,
  exportLaporan,
} = require("../controllers/owner.controller");

router.get("/dashboard", verifyToken, allowRoles("OWNER"), getDashboard);
router.get("/laporan", verifyToken, allowRoles("OWNER"), getLaporanPeriode);
router.get("/laporan/export", verifyToken, allowRoles("OWNER"), exportLaporan);

module.exports = router;