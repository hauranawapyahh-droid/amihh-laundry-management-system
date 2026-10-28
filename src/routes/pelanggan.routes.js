const express = require("express");

const {
  getAllPelanggan,
  getPelangganById,
  createPelanggan,
  updatePelanggan,
  deletePelanggan,
  searchPelanggan,
} = require("../controllers/pelanggan.controller");

const { verifyToken } = require("../middleware/auth.middleware");
const { allowRoles } = require("../middleware/role.middleware");

const router = express.Router();

// Semua route pelanggan membutuhkan login
router.use(verifyToken);

// Hanya ADMIN yang bisa mengelola data pelanggan
router.get("/", allowRoles("ADMIN"), getAllPelanggan);
router.get("/search", allowRoles("ADMIN"), searchPelanggan);
router.get("/:id", allowRoles("ADMIN"), getPelangganById);

router.post("/", allowRoles("ADMIN"), createPelanggan);

router.put("/:id", allowRoles("ADMIN"), updatePelanggan);

router.delete("/:id", allowRoles("ADMIN"), deletePelanggan);

module.exports = router;