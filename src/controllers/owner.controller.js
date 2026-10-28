const db = require("../config/database");

// Helper: tentukan rentang tanggal dari periode
function getRentangTanggal(periode) {
  const now = new Date();
  let dari, sampai;

  const pad = (n) => String(n).padStart(2, "0");
  const fmt = (d) => `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;

  if (periode === "harian") {
    dari = fmt(now);
    sampai = fmt(now);
  } else if (periode === "mingguan") {
    const hariIni = now.getDay() || 7; // Senin=1..Minggu=7
    const senin = new Date(now);
    senin.setDate(now.getDate() - hariIni + 1);
    dari = fmt(senin);
    sampai = fmt(now);
  } else if (periode === "bulanan") {
    const awalBulan = new Date(now.getFullYear(), now.getMonth(), 1);
    dari = fmt(awalBulan);
    sampai = fmt(now);
  } else if (periode === "tahunan") {
    const awalTahun = new Date(now.getFullYear(), 0, 1);
    dari = fmt(awalTahun);
    sampai = fmt(now);
  } else {
    return null;
  }

  return { dari, sampai };
}

// =========================
// DASHBOARD
// =========================
const getDashboard = async (req, res) => {
  try {
    const hariIni = getRentangTanggal("harian");
    const bulanIni = getRentangTanggal("bulanan");

    const [pendapatanHariIni] = await db.query(
      `SELECT SUM(total_harga) AS total, COUNT(*) AS jumlah
       FROM transaksi
       WHERE status_pembayaran = 'LUNAS' AND DATE(created_at) BETWEEN ? AND ?`,
      [hariIni.dari, hariIni.sampai]
    );

    const [pendapatanBulanIni] = await db.query(
      `SELECT SUM(total_harga) AS total
       FROM transaksi
       WHERE status_pembayaran = 'LUNAS' AND DATE(created_at) BETWEEN ? AND ?`,
      [bulanIni.dari, bulanIni.sampai]
    );

    const [biayaBulanIni] = await db.query(
      `SELECT SUM(jumlah_biaya) AS total
       FROM biaya_operasional
       WHERE tanggal_biaya BETWEEN ? AND ?`,
      [bulanIni.dari, bulanIni.sampai]
    );

    const totalPendapatanBulanIni = Number(pendapatanBulanIni[0].total) || 0;
    const totalBiayaBulanIni = Number(biayaBulanIni[0].total) || 0;

    res.json({
      success: true,
      data: {
        pendapatan_hari_ini: Number(pendapatanHariIni[0].total) || 0,
        jumlah_transaksi_hari_ini: pendapatanHariIni[0].jumlah,
        pendapatan_bulan_ini: totalPendapatanBulanIni,
        biaya_bulan_ini: totalBiayaBulanIni,
        keuntungan_bulan_ini: totalPendapatanBulanIni - totalBiayaBulanIni,
      },
    });
  } catch (error) {
    console.error(error);
    res.status(500).json({ success: false, message: "Gagal mengambil dashboard" });
  }
};

// =========================
// LAPORAN PERIODE (harian/mingguan/bulanan/tahunan)
// =========================
const getLaporanPeriode = async (req, res) => {
  try {
    const { periode } = req.query;
    const periodeValid = ["harian", "mingguan", "bulanan", "tahunan"];

    if (!periode || !periodeValid.includes(periode)) {
      return res.status(400).json({
        success: false,
        message: `periode wajib diisi, salah satu dari: ${periodeValid.join(", ")}`,
      });
    }

    const rentang = getRentangTanggal(periode);

    const [pendapatanRows] = await db.query(
      `SELECT SUM(total_harga) AS total, COUNT(*) AS jumlah_transaksi
       FROM transaksi
       WHERE status_pembayaran = 'LUNAS' AND DATE(created_at) BETWEEN ? AND ?`,
      [rentang.dari, rentang.sampai]
    );

    const [biayaRows] = await db.query(
      `SELECT SUM(jumlah_biaya) AS total, COUNT(*) AS jumlah_item
       FROM biaya_operasional
       WHERE tanggal_biaya BETWEEN ? AND ?`,
      [rentang.dari, rentang.sampai]
    );

    const totalPendapatan = Number(pendapatanRows[0].total) || 0;
    const totalBiaya = Number(biayaRows[0].total) || 0;

    res.json({
      success: true,
      data: {
        periode,
        rentang_tanggal: rentang,
        total_pendapatan: totalPendapatan,
        jumlah_transaksi: pendapatanRows[0].jumlah_transaksi,
        total_biaya: totalBiaya,
        jumlah_item_biaya: biayaRows[0].jumlah_item,
        keuntungan: totalPendapatan - totalBiaya,
      },
    });
  } catch (error) {
    console.error(error);
    res.status(500).json({ success: false, message: "Gagal mengambil laporan periode" });
  }
};

// =========================
// EXPORT LAPORAN (CSV sederhana) berdasarkan periode
// =========================
const exportLaporan = async (req, res) => {
  try {
    const { dari, sampai } = req.query;

    if (!dari || !sampai) {
      return res.status(400).json({
        success: false,
        message: "Parameter dari dan sampai (tanggal) wajib diisi, contoh: ?dari=2026-09-01&sampai=2026-09-30",
      });
    }

    const [transaksiRows] = await db.query(
      `SELECT
        t.kode_transaksi, p.nama AS nama_pelanggan, t.total_harga,
        t.status_pembayaran, t.status_pengambilan, t.created_at
      FROM transaksi t
      JOIN pelanggan p ON t.id_pelanggan = p.id_pelanggan
      WHERE DATE(t.created_at) BETWEEN ? AND ?
      ORDER BY t.created_at ASC`,
      [dari, sampai]
    );

    let csv = "Kode Transaksi,Nama Pelanggan,Total Harga,Status Pembayaran,Status Pengambilan,Tanggal\n";

    transaksiRows.forEach((row) => {
      csv += `${row.kode_transaksi},${row.nama_pelanggan},${row.total_harga},${row.status_pembayaran},${row.status_pengambilan},${row.created_at}\n`;
    });

    res.setHeader("Content-Type", "text/csv");
    res.setHeader(
      "Content-Disposition",
      `attachment; filename=laporan_${dari}_sampai_${sampai}.csv`
    );
    res.send(csv);
  } catch (error) {
    console.error(error);
    res.status(500).json({ success: false, message: "Gagal export laporan" });
  }
};

module.exports = {
  getDashboard,
  getLaporanPeriode,
  exportLaporan,
};