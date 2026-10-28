const db = require("../config/database");

// =========================
// CREATE PEMBAYARAN
// =========================
const createPembayaran = async (req, res) => {
  try {
    const { id_transaksi, metode_pembayaran, jumlah_bayar } = req.body;

    if (!id_transaksi || !metode_pembayaran || jumlah_bayar === undefined) {
      return res.status(400).json({
        success: false,
        message:
          "id_transaksi, metode_pembayaran, dan jumlah_bayar wajib diisi",
      });
    }

    const metodeValid = ["TUNAI", "TRANSFER_BANK", "QRIS"];
    if (!metodeValid.includes(metode_pembayaran)) {
      return res.status(400).json({
        success: false,
        message:
          "metode_pembayaran harus salah satu dari: TUNAI, TRANSFER_BANK, QRIS",
      });
    }

    const [transaksiRows] = await db.query(
      `SELECT id_transaksi, total_harga, status_pembayaran
       FROM transaksi
       WHERE id_transaksi = ?`,
      [id_transaksi]
    );

    if (transaksiRows.length === 0) {
      return res.status(404).json({
        success: false,
        message: "Transaksi tidak ditemukan",
      });
    }

    const transaksi = transaksiRows[0];

    if (transaksi.status_pembayaran === "LUNAS") {
      return res.status(400).json({
        success: false,
        message: "Transaksi ini sudah lunas",
      });
    }

    const totalHarga = Number(transaksi.total_harga);
    const jumlahBayar = Number(jumlah_bayar);

    let kembalian = 0;
    if (metode_pembayaran === "TUNAI" && jumlahBayar > totalHarga) {
      kembalian = jumlahBayar - totalHarga;
    }

    const [result] = await db.query(
      `INSERT INTO pembayaran
       (id_transaksi, metode_pembayaran, jumlah_bayar, kembalian, status_konfirmasi)
       VALUES (?, ?, ?, ?, 'PENDING')`,
      [id_transaksi, metode_pembayaran, jumlahBayar, kembalian]
    );

    res.status(201).json({
      success: true,
      message: "Pembayaran berhasil dicatat, menunggu konfirmasi",
      data: {
        id_pembayaran: result.insertId,
        id_transaksi,
        metode_pembayaran,
        jumlah_bayar: jumlahBayar,
        kembalian,
        status_konfirmasi: "PENDING",
      },
    });
  } catch (error) {
    console.error(error);

    res.status(500).json({
      success: false,
      message: "Gagal mencatat pembayaran",
      error: error.message,
    });
  }
};

// =========================
// KONFIRMASI PEMBAYARAN
// =========================
const konfirmasiPembayaran = async (req, res) => {
  const connection = await db.getConnection();

  try {
    const { id } = req.params;
    const { catatan_pelunasan } = req.body;

    await connection.beginTransaction();

    const [pembayaranRows] = await connection.query(
      `SELECT id_pembayaran, id_transaksi, status_konfirmasi
       FROM pembayaran
       WHERE id_pembayaran = ?`,
      [id]
    );

    if (pembayaranRows.length === 0) {
      await connection.rollback();
      return res.status(404).json({
        success: false,
        message: "Data pembayaran tidak ditemukan",
      });
    }

    const pembayaran = pembayaranRows[0];

    if (pembayaran.status_konfirmasi === "CONFIRMED") {
      await connection.rollback();
      return res.status(400).json({
        success: false,
        message: "Pembayaran ini sudah dikonfirmasi sebelumnya",
      });
    }

    await connection.query(
      `UPDATE pembayaran
       SET status_konfirmasi = 'CONFIRMED',
           catatan_pelunasan = ?,
           id_konfirmator = ?,
           waktu_konfirmasi = NOW()
       WHERE id_pembayaran = ?`,
      [catatan_pelunasan || null, req.user.id_user, id]
    );

    await connection.query(
      `UPDATE transaksi
       SET status_pembayaran = 'LUNAS'
       WHERE id_transaksi = ?`,
      [pembayaran.id_transaksi]
    );

    await connection.commit();

    res.json({
      success: true,
      message: "Pembayaran berhasil dikonfirmasi, transaksi lunas",
      data: {
        id_pembayaran: pembayaran.id_pembayaran,
        id_transaksi: pembayaran.id_transaksi,
        status_konfirmasi: "CONFIRMED",
        status_pembayaran_transaksi: "LUNAS",
      },
    });
  } catch (error) {
    await connection.rollback();

    console.error(error);

    res.status(500).json({
      success: false,
      message: "Gagal mengonfirmasi pembayaran",
      error: error.message,
    });
  } finally {
    connection.release();
  }
};

module.exports = {
  createPembayaran,
  konfirmasiPembayaran,
};