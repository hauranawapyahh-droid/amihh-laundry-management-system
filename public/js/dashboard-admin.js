const auth = checkAuth("ADMIN");

const $ = (id) => document.getElementById(id);

// Escape teks supaya data dari database tidak dianggap kode HTML
const esc = (s) => String(s == null ? "" : s).replace(/[&<>"']/g, (c) => ({
  "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;",
}[c]));

const namaAdmin = auth.user.nama || "Admin";
const inisial = namaAdmin.split(" ").filter(Boolean).map((w) => w[0]).join("").substring(0, 2).toUpperCase() || "A";
$("userName").textContent = namaAdmin;
$("sidebarUserName").textContent = namaAdmin;
$("dashboardUserName").textContent = namaAdmin.split(" ")[0];
$("sidebarAvatarInitial").textContent = inisial;
$("topAvatarInitial").textContent = inisial;

function formatRupiah(angka) {
  return "Rp " + Number(angka || 0).toLocaleString("id-ID");
}

// Warna huruf inisial di tabel (Pelanggan, Pengguna)
const AVATAR_COLORS = ["var(--color-primary)", "var(--color-accent)", "var(--color-danger)", "var(--color-secondary)"];
function avatarLetter(nama, index) {
  const color = AVATAR_COLORS[index % AVATAR_COLORS.length];
  return `<span style="color:${color}; font-weight:700;">${esc(String(nama || "?").charAt(0).toUpperCase())}</span>`;
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
  return `<span class="badge-pill ${cls}">${esc(status || "-")}</span>`;
}

// Warna status buat chart
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

function modal(id) {
  return bootstrap.Modal.getOrCreateInstance($(id));
}

// Cache data supaya form edit bisa langsung terisi
let DATA_PELANGGAN = [];
let DATA_LAYANAN = [];
let DATA_PENGGUNA = [];
let DATA_BIAYA = [];

// =========================
// NAVIGASI SIDEBAR
// =========================
const menuLinks = document.querySelectorAll(".menu-link[data-page]");
const pageSections = document.querySelectorAll(".page-section");

menuLinks.forEach((link) => {
  link.addEventListener("click", (e) => {
    e.preventDefault();
    const page = link.dataset.page;
    menuLinks.forEach((l) => l.classList.remove("active"));
    link.classList.add("active");
    pageSections.forEach((s) => s.classList.add("d-none"));

    const target = $("page-" + page);
    if (target) target.classList.remove("d-none");
  });
});

// TOPBAR TANGGAL
$("topbarDate").textContent = new Date().toLocaleDateString("id-ID", {
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

    $("statTotalPelanggan").textContent = (dataPelanggan.data || []).length;
    $("statTotalTransaksi").textContent = transaksiList.length;
    $("statCucianDiproses").textContent =
      prosesList.filter((p) => (p.status_proses || "").toUpperCase().includes("PROSES")).length;

    const hariIni = new Date().toDateString();
    const pendapatanHariIni = transaksiList
      .filter((t) => new Date(t.created_at).toDateString() === hariIni && t.status_pembayaran === "LUNAS")
      .reduce((sum, t) => sum + Number(t.total_harga), 0);
    $("statPendapatanHariIni").textContent = formatRupiah(pendapatanHariIni);

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

  const barColors = totals.map((_, i) => (i === totals.length - 1 ? "#F59E0B" : "#2F6FED"));

  new Chart($("chartPendapatan7Hari"), {
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

  new Chart($("chartStatusCucian"), {
    type: "doughnut",
    data: { labels, datasets: [{ data: values, backgroundColor: colors, borderWidth: 0 }] },
    options: { plugins: { legend: { display: false } } },
  });

  $("legendStatusCucian").innerHTML = labels.length
    ? labels.map((l, i) => `
        <div class="legend-item">
          <span class="legend-dot" style="background:${colors[i]}"></span>
          <span class="flex-grow-1">${esc(l)}</span>
          <strong>${values[i]}</strong>
        </div>`).join("")
    : `<div class="text-muted small">Belum ada data</div>`;
}

// Aktivitas tampil di Dashboard dan di halaman Aktivitas Terbaru
function renderAktivitasTerbaru(transaksiList) {
  const boxes = document.querySelectorAll(".aktivitas-box");

  if (transaksiList.length === 0) {
    boxes.forEach((b) => { b.innerHTML = `<div class="text-muted">Belum ada aktivitas</div>`; });
    return;
  }

  const terbaru = [...transaksiList].sort((a, b) => new Date(b.created_at) - new Date(a.created_at)).slice(0, 5);
  const html = terbaru.map((t) => `
    <div class="activity-item">
      <div class="activity-icon"><i class="bi bi-receipt"></i></div>
      <div>
        <div class="activity-title">Transaksi #${esc(t.kode_transaksi)}</div>
        <div class="activity-subtitle">${formatRupiah(t.total_harga)}</div>
      </div>
      <div class="activity-time">${new Date(t.created_at).toLocaleDateString("id-ID")}</div>
    </div>
  `).join("");

  boxes.forEach((b) => { b.innerHTML = html; });
}

// =========================
// RINGKASAN (Laporan + Pendapatan + Keuntungan)
// =========================
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

    $("laporanPendapatan").textContent = formatRupiah(totalPendapatan);
    $("laporanBiaya").textContent = formatRupiah(totalBiaya);
    $("laporanKeuntungan").textContent = formatRupiah(keuntungan);
    $("pendapatanTotal").textContent = formatRupiah(totalPendapatan);
    $("pendapatanJumlahTrx").textContent = dp.data.jumlah_transaksi || 0;
    $("keuntunganNilai").textContent = formatRupiah(keuntungan);
    $("keuntunganPendapatan").textContent = formatRupiah(totalPendapatan);
    $("keuntunganBiaya").textContent = formatRupiah(totalBiaya);
  } catch (error) {
    console.error("Gagal memuat ringkasan:", error);
  }
}

// =========================
// DATA PELANGGAN
// =========================
async function loadPelanggan() {
  const tbody = $("tabelPelanggan");
  try {
    const res = await apiFetch("/api/pelanggan");
    const data = await res.json();
    DATA_PELANGGAN = data.data || [];

    if (DATA_PELANGGAN.length === 0) {
      tbody.innerHTML = `<tr><td colspan="4" class="text-center text-muted">Belum ada data</td></tr>`;
      return;
    }
    tbody.innerHTML = DATA_PELANGGAN.map((p, i) => `
      <tr>
        <td>${avatarLetter(p.nama, i)} ${esc(p.nama)}</td>
        <td>${esc(p.no_telepon || "-")}</td>
        <td>${esc(p.alamat || "-")}</td>
        <td>
          <button class="btn btn-sm btn-outline-primary" title="Edit" onclick="editPelanggan(${p.id_pelanggan})"><i class="bi bi-pencil-square"></i></button>
          <button class="btn btn-sm btn-outline-danger" title="Hapus" onclick="hapusPelanggan(${p.id_pelanggan})"><i class="bi bi-trash"></i></button>
        </td>
      </tr>`).join("");
  } catch (error) {
    console.error(error);
    tbody.innerHTML = `<tr><td colspan="4" class="text-center text-danger">Gagal memuat data</td></tr>`;
  }
}

$("formTambahPelanggan").addEventListener("submit", async (e) => {
  e.preventDefault();
  const body = {
    nama: $("pelangganNama").value,
    no_telepon: $("pelangganNoTelepon").value,
    alamat: $("pelangganAlamat").value,
  };
  try {
    const res = await apiFetch("/api/pelanggan", { method: "POST", body: JSON.stringify(body) });
    const data = await res.json();
    if (!data.success) { alert(data.message || "Gagal menambah pelanggan"); return; }
    alert("Pelanggan berhasil ditambahkan");
    modal("modalTambahPelanggan").hide();
    e.target.reset();
    loadPelanggan();
  } catch (error) {
    console.error(error);
    alert("Terjadi kesalahan saat menambah pelanggan");
  }
});

function editPelanggan(id) {
  const p = DATA_PELANGGAN.find((x) => Number(x.id_pelanggan) === Number(id));
  if (!p) return;
  $("editPelangganId").value = p.id_pelanggan;
  $("editPelangganNama").value = p.nama || "";
  $("editPelangganNoTelepon").value = p.no_telepon || "";
  $("editPelangganAlamat").value = p.alamat || "";
  modal("modalEditPelanggan").show();
}

$("formEditPelanggan").addEventListener("submit", async (e) => {
  e.preventDefault();
  const id = $("editPelangganId").value;
  const body = {
    nama: $("editPelangganNama").value,
    no_telepon: $("editPelangganNoTelepon").value,
    alamat: $("editPelangganAlamat").value,
  };
  try {
    const res = await apiFetch(`/api/pelanggan/${id}`, { method: "PUT", body: JSON.stringify(body) });
    const data = await res.json();
    if (!data.success) { alert(data.message || "Gagal mengubah pelanggan"); return; }
    alert("Data pelanggan berhasil diperbarui");
    modal("modalEditPelanggan").hide();
    loadPelanggan();
  } catch (error) {
    console.error(error);
    alert("Terjadi kesalahan saat mengubah pelanggan");
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

// =========================
// LAYANAN & HARGA
// =========================
async function loadLayanan() {
  const tbody = $("tabelLayanan");
  try {
    const res = await apiFetch("/api/layanan");
    const data = await res.json();
    DATA_LAYANAN = data.data || [];

    if (DATA_LAYANAN.length === 0) {
      tbody.innerHTML = `<tr><td colspan="5" class="text-center text-muted">Belum ada data</td></tr>`;
      return;
    }
    tbody.innerHTML = DATA_LAYANAN.map((l) => `
      <tr>
        <td><strong>${esc(l.nama_layanan)}</strong></td>
        <td>${esc(l.jenis_satuan)}</td>
        <td>${formatRupiah(l.harga_per_satuan)}</td>
        <td>${badgeStatus(l.is_active ? "Aktif" : "Nonaktif")}</td>
        <td>
          <button class="btn btn-sm btn-outline-primary" title="Edit" onclick="editLayanan(${l.id_layanan})"><i class="bi bi-pencil-square"></i></button>
          <button class="btn btn-sm btn-outline-danger" title="Hapus" onclick="hapusLayanan(${l.id_layanan})"><i class="bi bi-trash"></i></button>
        </td>
      </tr>`).join("");
  } catch (error) {
    console.error(error);
    tbody.innerHTML = `<tr><td colspan="5" class="text-center text-danger">Gagal memuat data</td></tr>`;
  }
}

$("formTambahLayanan").addEventListener("submit", async (e) => {
  e.preventDefault();
  const body = {
    nama_layanan: $("layananNama").value,
    jenis_satuan: $("layananSatuan").value,
    harga_per_satuan: $("layananHarga").value,
  };
  try {
    const res = await apiFetch("/api/layanan", { method: "POST", body: JSON.stringify(body) });
    const data = await res.json();
    if (!data.success) { alert(data.message || "Gagal menambah layanan"); return; }
    alert("Layanan berhasil ditambahkan");
    modal("modalTambahLayanan").hide();
    e.target.reset();
    loadLayanan();
  } catch (error) {
    console.error(error);
    alert("Terjadi kesalahan saat menambah layanan");
  }
});

function editLayanan(id) {
  const l = DATA_LAYANAN.find((x) => Number(x.id_layanan) === Number(id));
  if (!l) return;
  $("editLayananId").value = l.id_layanan;
  $("editLayananNama").value = l.nama_layanan || "";
  $("editLayananSatuan").value = l.jenis_satuan || "";
  $("editLayananHarga").value = l.harga_per_satuan;
  $("editLayananAktif").value = l.is_active ? "1" : "0";
  modal("modalEditLayanan").show();
}

$("formEditLayanan").addEventListener("submit", async (e) => {
  e.preventDefault();
  const id = $("editLayananId").value;
  const body = {
    nama_layanan: $("editLayananNama").value,
    jenis_satuan: $("editLayananSatuan").value,
    harga_per_satuan: $("editLayananHarga").value,
    is_active: Number($("editLayananAktif").value),
  };
  try {
    const res = await apiFetch(`/api/layanan/${id}`, { method: "PUT", body: JSON.stringify(body) });
    const data = await res.json();
    if (!data.success) { alert(data.message || "Gagal mengubah layanan"); return; }
    alert("Layanan berhasil diperbarui");
    modal("modalEditLayanan").hide();
    loadLayanan();
  } catch (error) {
    console.error(error);
    alert("Terjadi kesalahan saat mengubah layanan");
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

// =========================
// DATA CUCIAN (read-only)
// =========================
async function loadCucian() {
  const tbody = $("tabelCucian");
  try {
    const res = await apiFetch("/api/transaksi");
    const data = await res.json();
    if (!data.data || data.data.length === 0) {
      tbody.innerHTML = `<tr><td colspan="5" class="text-center text-muted">Belum ada data</td></tr>`;
      return;
    }
    tbody.innerHTML = data.data.map((t) => `
      <tr>
        <td class="text-primary fw-semibold">${esc(t.kode_transaksi)}</td>
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

// =========================
// PROSES CUCIAN (read-only)
// =========================
async function loadProses() {
  const tbody = $("tabelProses");
  try {
    const res = await apiFetch("/api/proses-cucian");
    const data = await res.json();
    if (!data.data || data.data.length === 0) {
      tbody.innerHTML = `<tr><td colspan="4" class="text-center text-muted">Belum ada data</td></tr>`;
      return;
    }
    tbody.innerHTML = data.data.map((p) => `
      <tr>
        <td class="text-primary fw-semibold">${esc(p.id_transaksi)}</td>
        <td>${badgeStatus(p.status_proses)}</td>
        <td>${esc(p.catatan_proses || "-")}</td>
        <td>${new Date(p.created_at).toLocaleString("id-ID")}</td>
      </tr>`).join("");
  } catch (error) {
    console.error(error);
    tbody.innerHTML = `<tr><td colspan="4" class="text-center text-danger">Gagal memuat data</td></tr>`;
  }
}

// =========================
// MANAJEMEN PENGGUNA
// =========================
async function loadPengguna() {
  const tbody = $("tabelPengguna");
  try {
    const res = await apiFetch("/api/pengguna");
    const data = await res.json();
    DATA_PENGGUNA = data.data || [];

    if (DATA_PENGGUNA.length === 0) {
      tbody.innerHTML = `<tr><td colspan="5" class="text-center text-muted">Belum ada data</td></tr>`;
      return;
    }
    tbody.innerHTML = DATA_PENGGUNA.map((u, i) => `
      <tr>
        <td>${avatarLetter(u.nama, i)} ${esc(u.nama)}</td>
        <td>${esc(u.username)}</td>
        <td>${esc(u.role)}</td>
        <td>${badgeStatus(Number(u.is_active) === 1 ? "Aktif" : "Nonaktif")}</td>
        <td>
          <button class="btn btn-sm btn-outline-primary" title="Edit" onclick="editUser(${u.id_user})"><i class="bi bi-pencil-square"></i></button>
          <button class="btn btn-sm btn-outline-warning" title="Reset password" onclick="resetPassword(${u.id_user})"><i class="bi bi-key"></i></button>
          <button class="btn btn-sm btn-outline-danger" title="Hapus" onclick="hapusUser(${u.id_user})"><i class="bi bi-trash"></i></button>
        </td>
      </tr>`).join("");
  } catch (error) {
    console.error(error);
    tbody.innerHTML = `<tr><td colspan="5" class="text-center text-danger">Gagal memuat data</td></tr>`;
  }
}

$("formTambahUser").addEventListener("submit", async (e) => {
  e.preventDefault();
  const body = {
    nama: $("userNama").value,
    username: $("userUsername").value.trim(),
    password: $("userPassword").value,
    role: $("userRole").value,
  };
  try {
    const res = await apiFetch("/api/pengguna", { method: "POST", body: JSON.stringify(body) });
    const data = await res.json();
    if (!data.success) { alert(data.message || "Gagal menambah pengguna"); return; }
    alert("Pengguna berhasil ditambahkan");
    modal("modalTambahUser").hide();
    e.target.reset();
    loadPengguna();
  } catch (error) {
    console.error(error);
    alert("Terjadi kesalahan saat menambah pengguna");
  }
});

function editUser(id) {
  const u = DATA_PENGGUNA.find((x) => Number(x.id_user) === Number(id));
  if (!u) return;
  $("editUserId").value = u.id_user;
  $("editUserNama").value = u.nama || "";
  $("editUserUsername").value = u.username || "";
  $("editUserRole").value = u.role;
  $("editUserAktif").value = Number(u.is_active) === 1 ? "1" : "0";
  $("editUserPassword").value = "";
  modal("modalEditUser").show();
}

$("formEditUser").addEventListener("submit", async (e) => {
  e.preventDefault();
  const id = $("editUserId").value;
  const body = {
    nama: $("editUserNama").value,
    username: $("editUserUsername").value.trim(),
    role: $("editUserRole").value,
    is_active: $("editUserAktif").value,
  };
  // Password hanya dikirim kalau diisi
  const pw = $("editUserPassword").value;
  if (pw) body.password = pw;

  try {
    const res = await apiFetch(`/api/pengguna/${id}`, { method: "PUT", body: JSON.stringify(body) });
    const data = await res.json();
    if (!data.success) { alert(data.message || "Gagal mengubah pengguna"); return; }
    alert("Pengguna berhasil diperbarui");
    modal("modalEditUser").hide();
    loadPengguna();
  } catch (error) {
    console.error(error);
    alert("Terjadi kesalahan saat mengubah pengguna");
  }
});

async function resetPassword(id) {
  const passwordBaru = prompt("Masukkan password baru untuk user ini (minimal 6 karakter):");
  if (!passwordBaru) return;
  try {
    const res = await apiFetch(`/api/pengguna/${id}/reset-password`, { method: "PUT", body: JSON.stringify({ password_baru: passwordBaru }) });
    const data = await res.json();
    if (!data.success) { alert(data.message || "Gagal reset password"); return; }
    alert("Password berhasil direset");
  } catch (error) {
    console.error(error);
    alert("Terjadi kesalahan saat reset password");
  }
}

async function hapusUser(id) {
  if (!confirm("Yakin ingin menghapus pengguna ini?")) return;
  try {
    const res = await apiFetch(`/api/pengguna/${id}`, { method: "DELETE" });
    const data = await res.json();
    if (!data.success) { alert(data.message || "Gagal menghapus pengguna"); return; }
    alert("Pengguna berhasil dihapus");
    loadPengguna();
  } catch (error) {
    console.error(error);
    alert("Terjadi kesalahan saat menghapus pengguna");
  }
}

// =========================
// BIAYA OPERASIONAL
// =========================
async function loadBiaya() {
  const tbody = $("tabelBiaya");
  try {
    const res = await apiFetch("/api/biaya-operasional");
    const data = await res.json();
    DATA_BIAYA = data.data || [];

    if (DATA_BIAYA.length === 0) {
      tbody.innerHTML = `<tr><td colspan="5" class="text-center text-muted">Belum ada data</td></tr>`;
      return;
    }
    tbody.innerHTML = DATA_BIAYA.map((b) => `
      <tr>
        <td>${new Date(b.tanggal_biaya).toLocaleDateString("id-ID")}</td>
        <td>${esc(b.nama_biaya)}</td>
        <td>${esc(b.kategori)}</td>
        <td>${formatRupiah(b.jumlah_biaya)}</td>
        <td>
          <button class="btn btn-sm btn-outline-primary" title="Edit" onclick="editBiaya(${b.id_biaya})"><i class="bi bi-pencil-square"></i></button>
          <button class="btn btn-sm btn-outline-danger" title="Hapus" onclick="hapusBiaya(${b.id_biaya})"><i class="bi bi-trash"></i></button>
        </td>
      </tr>`).join("");
  } catch (error) {
    console.error(error);
    tbody.innerHTML = `<tr><td colspan="5" class="text-center text-danger">Gagal memuat data</td></tr>`;
  }
}

$("formTambahBiaya").addEventListener("submit", async (e) => {
  e.preventDefault();
  const body = {
    nama_biaya: $("biayaNama").value,
    kategori: $("biayaKategori").value,
    jumlah_biaya: $("biayaJumlah").value,
    tanggal_biaya: $("biayaTanggal").value,
    keterangan: $("biayaKeterangan").value,
  };
  try {
    const res = await apiFetch("/api/biaya-operasional", { method: "POST", body: JSON.stringify(body) });
    const data = await res.json();
    if (!data.success) { alert(data.message || "Gagal menambah biaya"); return; }
    alert("Biaya berhasil ditambahkan");
    modal("modalTambahBiaya").hide();
    e.target.reset();
    loadBiaya();
    loadRingkasan();
  } catch (error) {
    console.error(error);
    alert("Terjadi kesalahan saat menambah biaya");
  }
});

function editBiaya(id) {
  const b = DATA_BIAYA.find((x) => Number(x.id_biaya) === Number(id));
  if (!b) return;
  $("editBiayaId").value = b.id_biaya;
  $("editBiayaNama").value = b.nama_biaya || "";
  $("editBiayaKategori").value = b.kategori || "";
  $("editBiayaJumlah").value = b.jumlah_biaya;
  $("editBiayaTanggal").value = String(b.tanggal_biaya || "").substring(0, 10);
  $("editBiayaKeterangan").value = b.keterangan || "";
  modal("modalEditBiaya").show();
}

$("formEditBiaya").addEventListener("submit", async (e) => {
  e.preventDefault();
  const id = $("editBiayaId").value;
  const body = {
    nama_biaya: $("editBiayaNama").value,
    kategori: $("editBiayaKategori").value,
    jumlah_biaya: $("editBiayaJumlah").value,
    tanggal_biaya: $("editBiayaTanggal").value,
    keterangan: $("editBiayaKeterangan").value,
  };
  try {
    const res = await apiFetch(`/api/biaya-operasional/${id}`, { method: "PUT", body: JSON.stringify(body) });
    const data = await res.json();
    if (!data.success) { alert(data.message || "Gagal mengubah biaya"); return; }
    alert("Biaya berhasil diperbarui");
    modal("modalEditBiaya").hide();
    loadBiaya();
    loadRingkasan();
  } catch (error) {
    console.error(error);
    alert("Terjadi kesalahan saat mengubah biaya");
  }
});

async function hapusBiaya(id) {
  if (!confirm("Yakin ingin menghapus biaya ini?")) return;
  try {
    const res = await apiFetch(`/api/biaya-operasional/${id}`, { method: "DELETE" });
    const data = await res.json();
    if (!data.success) { alert(data.message || "Gagal menghapus biaya"); return; }
    alert("Biaya berhasil dihapus");
    loadBiaya();
    loadRingkasan();
  } catch (error) {
    console.error(error);
    alert("Terjadi kesalahan saat menghapus biaya");
  }
}

// =========================
// INIT
// =========================
loadDashboardStats();
loadRingkasan();
loadPelanggan();
loadLayanan();
loadCucian();
loadProses();
loadPengguna();
loadBiaya();