const express = require("express");

const router = express.Router();

const { verifyToken } = require("../middleware/auth.middleware");
const { allowRoles } = require("../middleware/role.middleware");

const {
  getAllBiaya,
  getBiayaById,
  createBiaya,
  updateBiaya,
  deleteBiaya,
} = require("../controllers/biayaoperasional.controller");

router.get("/", verifyToken, allowRoles("ADMIN", "OWNER"), getAllBiaya);
router.get("/:id", verifyToken, allowRoles("ADMIN", "OWNER"), getBiayaById);
router.post("/", verifyToken, allowRoles("ADMIN"), createBiaya);
router.put("/:id", verifyToken, allowRoles("ADMIN"), updateBiaya);
router.delete("/:id", verifyToken, allowRoles("ADMIN"), deleteBiaya);

module.exports = router;