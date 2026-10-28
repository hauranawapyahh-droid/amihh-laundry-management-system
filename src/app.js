const express = require("express");
const cors = require("cors");
require("dotenv").config();

const db = require("./config/database");

const authRoutes = require("./routes/auth.routes");
const layananRoutes = require("./routes/layanan.routes");
const pelangganRoutes = require("./routes/pelanggan.routes");
const transaksiRoutes = require("./routes/transaksi.routes");
const pembayaranRoutes = require("./routes/pembayaran.routes");
const prosesCucianRoutes = require("./routes/prosescucian.routes");
const penggunaRoutes = require("./routes/pengguna.routes");
const biayaOperasionalRoutes = require("./routes/biayaoperasional.routes");
const laporanRoutes = require("./routes/laporan.routes");
const ownerRoutes = require("./routes/owner.routes");
const customerRoutes = require("./routes/customer.routes");

const app = express();

app.use(cors());
app.use(express.json());
app.use(express.static("public"));

app.use("/api/auth", authRoutes);
app.use("/api/layanan", layananRoutes);
app.use("/api/pelanggan", pelangganRoutes);
app.use("/api/transaksi", transaksiRoutes);
app.use("/api/pembayaran", pembayaranRoutes);
app.use("/api/proses-cucian", prosesCucianRoutes);
app.use("/api/pengguna", penggunaRoutes);
app.use("/api/biaya-operasional", biayaOperasionalRoutes);
app.use("/api/laporan", laporanRoutes);
app.use("/api/owner", ownerRoutes);
app.use("/api/customer", customerRoutes);

app.get("/health", async (req, res) => {
  try {
    const [rows] = await db.query(
      "SELECT 1 AS database_connected"
    );

    res.json({
      success: true,
      message: "Amih Laundry API is running",
      database: rows[0].database_connected === 1,
    });
  } catch (error) {
    console.error(error);

    res.status(500).json({
      success: false,
      message: "Database connection failed",
      error: error.message,
    });
  }
});

const PORT = process.env.PORT || 3000;

app.listen(PORT, () => {
  console.log(
    `Amih Laundry API running on http://localhost:${PORT}`
  );
});