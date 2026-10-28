const express = require("express");

const { login } = require("../controllers/auth.controller");
const { verifyToken } = require("../middleware/auth.middleware");
const { allowRoles } = require("../middleware/role.middleware");

const router = express.Router();

router.post("/login", login);

router.get("/me", verifyToken, allowRoles("ADMIN"), (req, res) => {
  res.json({
    success: true,
    message: "Token valid",
    user: req.user,
  });
});

module.exports = router;