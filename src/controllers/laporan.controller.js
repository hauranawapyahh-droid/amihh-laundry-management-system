const db = require("../config/database");

// LAPORAN PENDAPATAN (dari transaksi yang LUNAS)
const laporanPendapatan = async (req, res) => {
  try {
    const { dari, sampai } = req.query;

    let query = `
      SELECT
        SUM(total_harga) AS total_pendapatan,
        COUNT(*) AS jumlah_transaksi
      FROM transaksi
      WHERE status_pembayaran = 'LUNAS'
    `;
    const params = [];

    if (dari && sampai) {
      query += ` AND DATE(created_at) BETWEEN ? AND ?`;
      params.push(dari, sampai);
    }

    const [rows] = await db.query(query, params);

    res.json({
      success: true,
      data: {
        total_pendapatan: Number(rows[0].total_pendapatan) || 0,
        jumlah_transaksi: rows[0].jumlah_transaksi,
        periode: dari && sampai ? { dari, sampai } : "semua",
      },
    });
  } catch (error) {
    console.error(error);
    res.status(500).json({ success: false, message: "Gagal mengambil laporan pendapatan" });
  }
};

// LAPORAN BIAYA OPERASIONAL
const laporanBiaya = async (req, res) => {
  try {
    const { dari, sampai } = req.query;

    let query = `
      SELECT SUM(jumlah_biaya) AS total_biaya, COUNT(*) AS jumlah_item
      FROM biaya_operasional
      WHERE 1=1
    `;
    const params = [];

    if (dari && sampai) {
      query += ` AND tanggal_biaya BETWEEN ? AND ?`;
      params.push(dari, sampai);
    }

    const [rows] = await db.query(query, params);

    res.json({
      success: true,
      data: {
        total_biaya: Number(rows[0].total_biaya) || 0,
        jumlah_item: rows[0].jumlah_item,
        periode: dari && sampai ? { dari, sampai } : "semua",
      },
    });
  } catch (error) {
    console.error(error);
    res.status(500).json({ success: false, message: "Gagal mengambil laporan biaya" });
  }
};

// LAPORAN KEUNTUNGAN (pendapatan - biaya)
const laporanKeuntungan = async (req, res) => {
  try {
    const { dari, sampai } = req.query;

    let queryPendapatan = `
      SELECT SUM(total_harga) AS total FROM transaksi WHERE status_pembayaran = 'LUNAS'
    `;
    let queryBiaya = `
      SELECT SUM(jumlah_biaya) AS total FROM biaya_operasional WHERE 1=1
    `;
    const paramsPendapatan = [];
    const paramsBiaya = [];

    if (dari && sampai) {
      queryPendapatan += ` AND DATE(created_at) BETWEEN ? AND ?`;
      paramsPendapatan.push(dari, sampai);
      queryBiaya += ` AND tanggal_biaya BETWEEN ? AND ?`;
      paramsBiaya.push(dari, sampai);
    }

    const [pendapatanRows] = await db.query(queryPendapatan, paramsPendapatan);
    const [biayaRows] = await db.query(queryBiaya, paramsBiaya);

    const totalPendapatan = Number(pendapatanRows[0].total) || 0;
    const totalBiaya = Number(biayaRows[0].total) || 0;
    const keuntungan = totalPendapatan - totalBiaya;

    res.json({
      success: true,
      data: {
        total_pendapatan: totalPendapatan,
        total_biaya: totalBiaya,
        keuntungan,
        periode: dari && sampai ? { dari, sampai } : "semua",
      },
    });
  } catch (error) {
    console.error(error);
    res.status(500).json({ success: false, message: "Gagal menghitung keuntungan" });
  }
};

module.exports = {
  laporanPendapatan,
  laporanBiaya,
  laporanKeuntungan,
};