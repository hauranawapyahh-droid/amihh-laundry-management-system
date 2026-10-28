const auth = checkAuth("ADMIN");
document.getElementById("userName").textContent = auth.user.nama;
const inisial = auth.user.nama.split(" ").map(w => w[0]).join("").substring(0, 2).toUpperCase();
document.getElementById("sidebarAvatarInitial").textContent = inisial;

function formatRupiah(angka) {
  return "Rp " + Number(angka).toLocaleString("id-ID");
}

// Warna badge cycle buat avatar huruf inisial di tabel (Pelanggan, Pengguna)
const AVATAR_COLORS = ["var(--color-primary)", "var(--color-accent)", "var(--color-danger)", "var(--color-secondary)"];
function avatarLetter(nama, index) {
  const color = AVATAR_COLORS[index % AVATAR_COLORS.length];
  return `<span style="color:${color}; font-weight:700;">${nama.charAt(0).toUpperCase()}</span>`;
}

// Badge status generik (pill soft-color)
const STATUS_BADGE_MAP = {
  AKTIF: "badge-soft-success",
  NONAKTIF: "badge-soft-muted",
  LUNAS: "badge-soft-success",
  SELESAI: "badge-soft-success",
  DIPROSES: "badge-soft-secondary",
  "BARU MASUK": "badge-soft-accent",
  BARU_MASUK: "badge-soft-accent",
  "PERLU DIPROSES": "badge-soft-accent",
  PERLU_DIPROSES: "badge-soft-accent",
  "BELUM BAYAR": "badge-soft-accent",
  BELUM: "badge-soft-accent",
};
function badgeStatus(status) {
  const key = (status || "").toUpperCase();
  const cls = STATUS_BADGE_MAP[key] || "badge-soft-muted";
  return `<span class="badge-pill ${cls}">${status || "-"}</span>`;
}

// Warna status buat chart, mengikuti token resmi S1-02
const STATUS_COLOR_MAP = {
  DIPROSES: "#14B8A6",
  SELESAI: "#16A34A",
  BARU_MASUK: "#F59E0B",
  PERLU_DIPROSES: "#F59E0B",
};
function getStatusColor(status) {
  const key = (status || "").toUpperCase().replace(/\s+/g, "_");
  return STATUS_COLOR_MAP[key] || "#767C88";
}

// NAVIGASI SIDEBAR
const menuLinks = document.querySelectorAll(".menu-link");
const pageSections = document.querySelectorAll(".page-section");

menuLinks.forEach((link) => {
  link.addEventListener("click", (e) => {
    e.preventDefault();
    const page = link.dataset.page;
    menuLinks.forEach((l) => l.classList.remove("active"));
    link.classList.add("active");
    pageSections.forEach((s) => s.classList.add("d-none"));

    const target = document.getElementById("page-" + page);
    if (target) {
      target.classList.remove("d-none");
    } else {
      document.getElementById("placeholderTitle").textContent = "Fitur";
      document.getElementById("page-placeholder").classList.remove("d-none");
    }
  });
});

// TOPBAR TANGGAL
document.getElementById("topbarDate").textContent = new Date().toLocaleDateString("id-ID", {
  weekday: "short", day: "numeric", month: "short", year: "numeric",
});

// MENU CEPAT (dashboard) navigasi ke sidebar
document.querySelectorAll(".quickmenu-item").forEach((item) => {
  item.addEventListener("click", () => {
    const link = document.querySelector(`.menu-link[data-page="${item.dataset.goto}"]`);
    if (link) link.click();
  });
});

// =========================
// STAT CARDS + CHARTS DASHBOARD
// =========================
async function loadDashboardStats() {
  try {
    const [resPelanggan, resTransaksi, resProses] = await Promise.all([
      apiFetch("/api/pelanggan"),
      apiFetch("/api/transaksi"),
      apiFetch("/api/proses-cucian"),
    ]);
    const dataPelanggan = await resPelanggan.json();
    const dataTransaksi = await resTransaksi.json();
    const dataProses = await resProses.json();

    const transaksiList = dataTransaksi.data || [];
    const prosesList = dataProses.data || [];

    document.getElementById("statTotalPelanggan").textContent = (dataPelanggan.data || []).length;
    document.getElementById("statTotalTransaksi").textContent = transaksiList.length;
    document.getElementById("statCucianDiproses").textContent =
      prosesList.filter((p) => (p.status_proses || "").toUpperCase().includes("PROSES")).length;

    const hariIni = new Date().toDateString();
    const pendapatanHariIni = transaksiList
      .filter((t) => new Date(t.created_at).toDateString() === hariIni && t.status_pembayaran === "LUNAS")
      .reduce((sum, t) => sum + Number(t.total_harga), 0);
    document.getElementById("statPendapatanHariIni").textContent = formatRupiah(pendapatanHariIni);

    renderChartPendapatan(transaksiList);
    renderChartStatusCucian(prosesList);
    renderAktivitasTerbaru(transaksiList);
  } catch (error) {
    console.error("Gagal memuat statistik dashboard:", error);
  }
}

function renderChartPendapatan(transaksiList) {
  const labels = [];
  const totals = [];

  for (let i = 6; i >= 0; i--) {
    const d = new Date();
    d.setDate(d.getDate() - i);
    labels.push(d.toLocaleDateString("id-ID", { weekday: "short" }));

    const totalHariItu = transaksiList
      .filter((t) => {
        const tgl = new Date(t.created_at);
        return tgl.toDateString() === d.toDateString() && t.status_pembayaran === "LUNAS";
      })
      .reduce((sum, t) => sum + Number(t.total_harga), 0);

    totals.push(totalHariItu);
  }

  // Highlight hari ini (index terakhir) pakai warna accent, sisanya primary
  const barColors = totals.map((_, i) => (i === totals.length - 1 ? "#F59E0B" : "#2F6FED"));

  new Chart(document.getElementById("chartPendapatan7Hari"), {
    type: "bar",
    data: { labels, datasets: [{ label: "Pendapatan", data: totals, backgroundColor: barColors, borderRadius: 6 }] },
    options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } },
  });
}

function renderChartStatusCucian(prosesList) {
  const counts = {};
  prosesList.forEach((p) => {
    const status = p.status_proses || "Tidak diketahui";
    counts[status] = (counts[status] || 0) + 1;
  });

  const labels = Object.keys(counts);
  const values = Object.values(counts);
  const colors = labels.map(getStatusColor);

  new Chart(document.getElementById("chartStatusCucian"), {
    type: "doughnut",
    data: { labels, datasets: [{ data: values, backgroundColor: colors, borderWidth: 0 }] },
    options: { plugins: { legend: { display: false } } },
  });

  const legendBox = document.getElementById("legendStatusCucian");
  legendBox.innerHTML = labels.length
    ? labels.map((l, i) => `
        <div class="legend-item">
          <span class="legend-dot" style="background:${colors[i]}"></span>
          <span class="flex-grow-1">${l}</span>
          <strong>${values[i]}</strong>
        </div>`).join("")
    : `<div class="text-muted small">Belum ada data</div>`;
}

function renderAktivitasTerbaru(transaksiList) {
  const box = document.getElementById("aktivitasTerbaru");
  if (transaksiList.length === 0) {
    box.innerHTML = `<div class="text-muted">Belum ada aktivitas</div>`;
    return;
  }
  const terbaru = [...transaksiList].sort((a, b) => new Date(b.created_at) - new Date(a.created_at)).slice(0, 5);
  box.innerHTML = terbaru.map((t) => `
    <div class="activity-item">
      <div class="activity-icon"><i class="bi bi-receipt"></i></div>
      <div>
        <div class="activity-title">Transaksi #${t.kode_transaksi}</div>
        <div class="activity-subtitle">${formatRupiah(t.total_harga)}</div>
      </div>
      <div class="activity-time">${new Date(t.created_at).toLocaleDateString("id-ID")}</div>
    </div>
  `).join("");
}

// RINGKASAN (Laporan + Pendapatan + Keuntungan)
async function loadRingkasan() {
  try {
    const [resPendapatan, resBiaya, resKeuntungan] = await Promise.all([
      apiFetch("/api/laporan/pendapatan"),
      apiFetch("/api/laporan/biaya"),
      apiFetch("/api/laporan/keuntungan"),
    ]);
    const dp = await resPendapatan.json();
    const db = await resBiaya.json();
    const dk = await resKeuntungan.json();

    const totalPendapatan = dp.data.total_pendapatan || 0;
    const totalBiaya = db.data.total_biaya || 0;
    const keuntungan = dk.data.keuntungan || 0;

    document.getElementById("laporanPendapatan").textContent = formatRupiah(totalPendapatan);
    document.getElementById("laporanBiaya").textContent = formatRupiah(totalBiaya);
    document.getElementById("laporanKeuntungan").textContent = formatRupiah(keuntungan);
    document.getElementById("pendapatanTotal").textContent = formatRupiah(totalPendapatan);
    document.getElementById("pendapatanJumlahTrx").textContent = dp.data.jumlah_transaksi || 0;
    document.getElementById("keuntunganNilai").textContent = formatRupiah(keuntungan);
    document.getElementById("keuntunganPendapatan").textContent = formatRupiah(totalPendapatan);
    document.getElementById("keuntunganBiaya").textContent = formatRupiah(totalBiaya);
  } catch (error) {
    console.error("Gagal memuat ringkasan:", error);
  }
}

// DATA PELANGGAN
async function loadPelanggan() {
  const tbody = document.getElementById("tabelPelanggan");
  try {
    const res = await apiFetch("/api/pelanggan");
    const data = await res.json();
    if (!data.data || data.data.length === 0) {
      tbody.innerHTML = `<tr><td colspan="4" class="text-center text-muted">Belum ada data</td></tr>`;
      return;
    }
    tbody.innerHTML = data.data.map((p, i) => `
      <tr>
        <td>${avatarLetter(p.nama, i)} ${p.nama}</td>
        <td>${p.no_telepon}</td>
        <td>${p.alamat || "-"}</td>
        <td><button class="btn btn-sm btn-outline-danger" onclick="hapusPelanggan(${p.id_pelanggan})"><i class="bi bi-trash"></i></button></td>
      </tr>`).join("");
  } catch (error) {
    console.error(error);
    tbody.innerHTML = `<tr><td colspan="4" class="text-center text-danger">Gagal memuat data</td></tr>`;
  }
}

document.getElementById("formTambahPelanggan").addEventListener("submit", async (e) => {
  e.preventDefault();
  const body = {
    nama: document.getElementById("pelangganNama").value,
    no_telepon: document.getElementById("pelangganNoTelepon").value,
    alamat: document.getElementById("pelangganAlamat").value,
  };
  try {
    const res = await apiFetch("/api/pelanggan", { method: "POST", body: JSON.stringify(body) });
    const data = await res.json();
    if (!data.success) { alert(data.message || "Gagal menambah pelanggan"); return; }
    alert("Pelanggan berhasil ditambahkan");
    bootstrap.Modal.getInstance(document.getElementById("modalTambahPelanggan")).hide();
    e.target.reset();
    loadPelanggan();
  } catch (error) {
    console.error(error);
    alert("Terjadi kesalahan saat menambah pelanggan");
  }
});

async function hapusPelanggan(id) {
  if (!confirm("Yakin ingin menghapus pelanggan ini?")) return;
  try {
    const res = await apiFetch(`/api/pelanggan/${id}`, { method: "DELETE" });
    const data = await res.json();
    if (!data.success) { alert(data.message || "Gagal menghapus pelanggan"); return; }
    alert("Pelanggan berhasil dihapus");
    loadPelanggan();
  } catch (error) {
    console.error(error);
    alert("Terjadi kesalahan saat menghapus pelanggan");
  }
}

// LAYANAN & HARGA
async function loadLayanan() {
  const tbody = document.getElementById("tabelLayanan");
  try {
    const res = await apiFetch("/api/layanan");
    const data = await res.json();
    if (!data.data || data.data.length === 0) {
      tbody.innerHTML = `<tr><td colspan="5" class="text-center text-muted">Belum ada data</td></tr>`;
      return;
    }
    tbody.innerHTML = data.data.map((l) => `
      <tr>
        <td><strong>${l.nama_layanan}</strong></td>
        <td>${l.jenis_satuan}</td>
        <td>${formatRupiah(l.harga_per_satuan)}</td>
        <td>${badgeStatus(l.is_active ? "Aktif" : "Nonaktif")}</td>
        <td><button class="btn btn-sm btn-outline-danger" onclick="hapusLayanan(${l.id_layanan})"><i class="bi bi-trash"></i></button></td>
      </tr>`).join("");
  } catch (error) {
    console.error(error);
    tbody.innerHTML = `<tr><td colspan="5" class="text-center text-danger">Gagal memuat data</td></tr>`;
  }
}

document.getElementById("formTambahLayanan").addEventListener("submit", async (e) => {
  e.preventDefault();
  const body = {
    nama_layanan: document.getElementById("layananNama").value,
    jenis_satuan: document.getElementById("layananSatuan").value,
    harga_per_satuan: document.getElementById("layananHarga").value,
  };
  try {
    const res = await apiFetch("/api/layanan", { method: "POST", body: JSON.stringify(body) });
    const data = await res.json();
    if (!data.success) { alert(data.message || "Gagal menambah layanan"); return; }
    alert("Layanan berhasil ditambahkan");
    bootstrap.Modal.getInstance(document.getElementById("modalTambahLayanan")).hide();
    e.target.reset();
    loadLayanan();
  } catch (error) {
    console.error(error);
    alert("Terjadi kesalahan saat menambah layanan");
  }
});

async function hapusLayanan(id) {
  if (!confirm("Yakin ingin menghapus layanan ini?")) return;
  try {
    const res = await apiFetch(`/api/layanan/${id}`, { method: "DELETE" });
    const data = await res.json();
    if (!data.success) { alert(data.message || "Gagal menghapus layanan"); return; }
    alert("Layanan berhasil dihapus");
    loadLayanan();
  } catch (error) {
    console.error(error);
    alert("Terjadi kesalahan saat menghapus layanan");
  }
}

// DATA CUCIAN (read-only)
async function loadCucian() {
  const tbody = document.getElementById("tabelCucian");
  try {
    const res = await apiFetch("/api/transaksi");
    const data = await res.json();
    if (!data.data || data.data.length === 0) {
      tbody.innerHTML = `<tr><td colspan="5" class="text-center text-muted">Belum ada data</td></tr>`;
      return;
    }
    tbody.innerHTML = data.data.map((t) => `
      <tr>
        <td class="text-primary fw-semibold">${t.kode_transaksi}</td>
        <td>${formatRupiah(t.total_harga)}</td>
        <td>${badgeStatus(t.status_pembayaran)}</td>
        <td>${badgeStatus(t.status_pengambilan)}</td>
        <td>${new Date(t.created_at).toLocaleDateString("id-ID")}</td>
      </tr>`).join("");
  } catch (error) {
    console.error(error);
    tbody.innerHTML = `<tr><td colspan="5" class="text-center text-danger">Gagal memuat data</td></tr>`;
  }
}

// PROSES CUCIAN (read-only)
async function loadProses() {
  const tbody = document.getElementById("tabelProses");
  try {
    const res = await apiFetch("/api/proses-cucian");
    const data = await res.json();
    if (!data.data || data.data.length === 0) {
      tbody.innerHTML = `<tr><td colspan="4" class="text-center text-muted">Belum ada data</td></tr>`;
      return;
    }
    tbody.innerHTML = data.data.map((p) => `
      <tr>
        <td class="text-primary fw-semibold">${p.id_transaksi}</td>
        <td>${badgeStatus(p.status_proses)}</td>
        <td>${p.catatan_proses || "-"}</td>
        <td>${new Date(p.created_at).toLocaleString("id-ID")}</td>
      </tr>`).join("");
  } catch (error) {
    console.error(error);
    tbody.innerHTML = `<tr><td colspan="4" class="text-center text-danger">Gagal memuat data</td></tr>`;
  }
}

// MANAJEMEN PENGGUNA
async function loadPengguna() {
  const tbody = document.getElementById("tabelPengguna");

  try {
    const res = await apiFetch("/api/pengguna");
    const data = await res.json();

    if (!data.data || data.data.length === 0) {
      tbody.innerHTML = `<tr><td colspan="5" class="text-center text-muted">Belum ada data</td></tr>`;
      return;
    }

    tbody.innerHTML = data.data.map((u, i) => `
      <tr>
        <td>${avatarLetter(u.nama, i)} ${u.nama}</td>
        <td>${u.username}</td>
        <td>${u.role}</td>
        <td>${badgeStatus(Number(u.is_active) === 1 ? "AKTIF" : "NONAKTIF")}</td>
                <td>
          <button class="btn btn-sm btn-outline-danger" onclick="hapusUser(${u.id_user})">
            <i class="bi bi-trash"></i> Hapus
          </button>
        </td>
      </tr>
    `).join("");

  } catch (error) {
    console.error(error);
    tbody.innerHTML = `<tr><td colspan="5" class="text-center text-danger">Gagal memuat data</td></tr>`;
  }
}
async function hapusUser(id) {
  if (!confirm("Yakin ingin menghapus pengguna ini?")) return;

  try {
    const res = await apiFetch(`/api/pengguna/${id}`, {
      method: "DELETE"
    });

    const data = await res.json();

    if (!data.success) {
      alert(data.message || "Gagal menghapus pengguna");
      return;
    }

    alert("Pengguna berhasil dihapus");
    loadPengguna();

  } catch (error) {
    console.error(error);
    alert("Terjadi kesalahan saat menghapus pengguna");
  }
}
 // BIAYA OPERASIONAL
async function loadBiaya() {
  const tbody = document.getElementById("tabelBiaya");

  try {
    const res = await apiFetch("/api/biaya-operasional");
    const data = await res.json();

    if (!data.data || data.data.length === 0) {
      tbody.innerHTML = `<tr><td colspan="5" class="text-center text-muted">Belum ada data</td></tr>`;
      return;
    }

    tbody.innerHTML = data.data.map((b) => `
      <tr>
        <td>${new Date(b.tanggal_biaya).toLocaleDateString("id-ID")}</td>
        <td>${b.nama_biaya}</td>
        <td>${b.kategori}</td>
        <td>${formatRupiah(b.jumlah_biaya)}</td>
        <td><button class="btn btn-sm btn-outline-danger" onclick="hapusBiaya(${b.id_biaya})"><i class="bi bi-trash"></i></button></td>
      </tr>
    `).join("");

  } catch (error) {
    console.error(error);
    tbody.innerHTML = `<tr><td colspan="5" class="text-center text-danger">Gagal memuat data</td></tr>`;
  }
}
// INIT
loadDashboardStats();
loadRingkasan();
loadPelanggan();
loadLayanan();
loadCucian();
loadProses();
loadPengguna();
loadBiaya();