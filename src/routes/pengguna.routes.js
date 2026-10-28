const express = require("express");

const router = express.Router();

const { verifyToken } = require("../middleware/auth.middleware");
const { allowRoles } = require("../middleware/role.middleware");

const {
  getAllPengguna,
  getPenggunaById,
  createPengguna,
  updatePengguna,
  deletePengguna,
  resetPassword,
} = require("../controllers/pengguna.controller");

router.get("/", verifyToken, allowRoles("ADMIN"), getAllPengguna);
router.get("/:id", verifyToken, allowRoles("ADMIN"), getPenggunaById);
router.post("/", verifyToken, allowRoles("ADMIN"), createPengguna);
router.put("/:id", verifyToken, allowRoles("ADMIN"), updatePengguna);
router.delete("/:id", verifyToken, allowRoles("ADMIN"), deletePengguna);
router.put("/:id/reset-password", verifyToken, allowRoles("ADMIN"), resetPassword);

module.exports = router;