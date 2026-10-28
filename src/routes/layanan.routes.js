const express = require("express");

const {
  getAllLayanan,
  getLayananById,
  createLayanan,
  updateLayanan,
  deleteLayanan,
} = require("../controllers/layanan.controller");

const { verifyToken } = require("../middleware/auth.middleware");
const { allowRoles } = require("../middleware/role.middleware");

const router = express.Router();

router.use(verifyToken);

router.get("/", allowRoles("ADMIN"), getAllLayanan);
router.get("/:id", allowRoles("ADMIN"), getLayananById);

router.post("/", allowRoles("ADMIN"), createLayanan);
router.put("/:id", allowRoles("ADMIN"), updateLayanan);
router.delete("/:id", allowRoles("ADMIN"), deleteLayanan);

module.exports = router;