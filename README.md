# Amih Laundry Management System

**Aplikasi Manajemen Operasional Laundry Berbasis Web**

Amih Laundry Management System merupakan aplikasi berbasis web yang dikembangkan untuk membantu digitalisasi dan pengelolaan operasional usaha laundry secara terintegrasi. Aplikasi ini dirancang untuk menggantikan pencatatan transaksi secara manual menggunakan kertas serta pengelolaan laporan terpisah menggunakan Microsoft Excel.

Sistem menyediakan pengelolaan data pelanggan, layanan laundry, transaksi, pembayaran, proses pengerjaan, pengambilan, hingga laporan keuangan dalam satu platform. Dengan penerapan sistem ini, pengelolaan operasional diharapkan menjadi lebih terstruktur, efisien, akurat, dan mudah dipantau sesuai hak akses masing-masing pengguna.

---

## 1. Informasi Project

| Keterangan | Detail |
|---|---|
| Nama Aplikasi | Amih Laundry Management System |
| Jenis Aplikasi | Web-Based Management System |
| Bidang | Laundry Management |
| Arsitektur | Client-Server |
| Framework | Laravel 10 |
| Bahasa Pemrograman | PHP 8.2 |
| Database | MySQL / MariaDB |
| Web Server Lokal | Apache (XAMPP) |
| Pengelolaan Dependency | Composer |
| Version Control | Git |
| Tahun Pengembangan | 2026/2027 |

## 2. Latar Belakang

Pengelolaan usaha laundry yang masih menggunakan pencatatan manual memiliki beberapa kendala, seperti:

- Data pelanggan dan transaksi belum terintegrasi.
- Pencatatan transaksi masih bergantung pada nota atau kertas.
- Pemantauan status pengerjaan laundry membutuhkan pengecekan secara langsung.
- Pengelolaan pembayaran dan riwayat transaksi belum terpusat.
- Rekapitulasi pendapatan, biaya, dan keuntungan membutuhkan pengolahan data secara terpisah.

Amih Laundry Management System dikembangkan sebagai solusi digital untuk mengintegrasikan proses tersebut ke dalam satu sistem informasi yang dapat digunakan oleh beberapa jenis pengguna sesuai tanggung jawabnya.

## 3. Tujuan Pengembangan

1. Mendigitalisasi pencatatan transaksi dan data pelanggan.
2. Mengintegrasikan pengelolaan layanan, pembayaran, dan proses laundry.
3. Mempermudah pemantauan status pengerjaan hingga pengambilan.
4. Menyediakan informasi operasional secara terpusat.
5. Membantu pengelolaan pendapatan, biaya operasional, dan keuntungan.
6. Meningkatkan keamanan serta keteraturan pengelolaan data berdasarkan hak akses pengguna.

## 4. Hak Akses dan Tanggung Jawab Pengguna

Sistem menerapkan pembagian hak akses berdasarkan lima role utama.

### Admin
- Mengelola data pelanggan.
- Mengelola layanan laundry.
- Mengelola akun dan data pengguna.
- Mengelola biaya operasional.
- Mengakses dan mengelola laporan.

### Kasir
- Mengelola transaksi laundry.
- Mendaftarkan pelanggan baru.
- Membuat akun customer melalui proses pendaftaran pelanggan.
- Mengelola pembayaran dan status pelunasan.
- Melihat serta mencari riwayat transaksi.
- Mengelola proses pengambilan laundry.

### Staff Laundry
- Melihat daftar pekerjaan laundry.
- Mengakses detail transaksi dan informasi pengerjaan.
- Memperbarui status proses laundry.
- Menambahkan catatan pengerjaan sesuai kebutuhan operasional.

### Owner
- Mengakses dashboard usaha.
- Memantau informasi pendapatan.
- Melihat rekapitulasi biaya operasional.
- Memantau keuntungan usaha.
- Mengakses laporan berdasarkan periode.
- Melihat riwayat dan perkembangan operasional usaha.

### Customer
- Login menggunakan akun yang telah dibuat oleh kasir.
- Melihat informasi akun pelanggan.
- Melihat riwayat transaksi miliknya.
- Memantau status pengerjaan laundry.
- Melihat informasi pembayaran dan transaksi terkait.

## 5. Fitur Utama Sistem

### 5.1 Manajemen Pelanggan
Pengelolaan informasi pelanggan yang tersimpan secara terpusat, sehingga data dapat digunakan dalam proses transaksi dan penelusuran riwayat.

### 5.2 Manajemen Layanan
Pengelolaan jenis layanan laundry beserta informasi harga dan ketentuan layanan sebagai dasar perhitungan transaksi.

### 5.3 Manajemen Transaksi
Pencatatan transaksi laundry yang menghubungkan data pelanggan, layanan, berat atau jumlah pesanan, serta total biaya transaksi.

### 5.4 Manajemen Pembayaran
Pengelolaan informasi pembayaran, bukti pembayaran, dan status pelunasan transaksi.

### 5.5 Pemantauan Proses Laundry
Pengelolaan perkembangan pengerjaan laundry melalui pembaruan status oleh staff laundry.

### 5.6 Pengambilan Laundry
Pengelolaan informasi pengambilan pesanan untuk memastikan transaksi yang telah selesai dapat ditelusuri hingga tahap pengambilan.

### 5.7 Manajemen Biaya Operasional
Pencatatan biaya usaha sebagai komponen pendukung perhitungan dan evaluasi keuangan.

### 5.8 Dashboard dan Laporan
Penyajian informasi operasional dan keuangan yang mencakup pendapatan, biaya, keuntungan, serta rekapitulasi berdasarkan periode.

## 6. Alur Operasional Sistem

Secara umum, alur operasional Amih Laundry adalah sebagai berikut:

1. **Pendaftaran Pelanggan** — Kasir mendaftarkan data pelanggan dan membuat akun customer.
2. **Pembuatan Transaksi** — Kasir memilih pelanggan dan layanan, kemudian mencatat pesanan laundry.
3. **Pembayaran** — Kasir mengelola pembayaran dan memperbarui status pembayaran.
4. **Pengerjaan Laundry** — Staff laundry melihat daftar pekerjaan dan memperbarui status proses pengerjaan.
5. **Penyelesaian dan Pengambilan** — Setelah proses selesai, kasir mengelola pengambilan laundry.
6. **Pemantauan Pelanggan** — Customer dapat melihat informasi transaksi dan perkembangan laundry miliknya.
7. **Pengelolaan dan Evaluasi** — Admin dan Owner mengakses data serta laporan sesuai kewenangan masing-masing.

## 7. Teknologi dan Tools Pengembangan

| Teknologi | Fungsi |
|---|---|
| Laravel 10 | Framework utama pengembangan backend |
| PHP 8.2 | Bahasa pemrograman sisi server |
| MySQL / MariaDB | Penyimpanan dan pengelolaan data |
| HTML5 | Struktur halaman aplikasi |
| CSS3 | Pengaturan tampilan dan layout |
| JavaScript | Interaksi dan fungsionalitas antarmuka |
| Apache | Web server lokal |
| XAMPP | Lingkungan pengembangan lokal |
| Composer | Manajemen dependency PHP |
| Git | Version control dan pengelolaan perubahan kode |
| Visual Studio Code | Code editor |

## 8. Arsitektur dan Struktur Project

Aplikasi dikembangkan menggunakan framework Laravel dengan pemisahan komponen berdasarkan tanggung jawabnya.

Struktur direktori utama:

```text
amih-laundry-management-system/
│
├── laravel/
│   ├── app/
│   │   ├── Http/
│   │   │   ├── Controllers/
│   │   │   ├── Middleware/
│   │   │   └── Requests/
│   │   ├── Models/
│   │   └── Providers/
│   │
│   ├── bootstrap/
│   ├── config/
│   ├── database/
│   ├── public/
│   ├── resources/
│   ├── routes/
│   ├── storage/
│   ├── tests/
│   ├── vendor/
│   ├── .env
│   ├── artisan
│   └── composer.json
│
└── README.md
```

**Keterangan komponen utama:**

- `app/` — Menyimpan logika utama aplikasi, model, controller, dan middleware.
- `Http/Controllers/` — Menangani request dan proses bisnis aplikasi.
- `Http/Middleware/` — Mengatur autentikasi dan pembatasan hak akses.
- `Models/` — Merepresentasikan entitas dan interaksi data dengan database.
- `routes/` — Mendefinisikan endpoint aplikasi dan API.
- `database/` — Menyimpan struktur dan pengelolaan database.
- `resources/` — Menyimpan aset sumber tampilan aplikasi.
- `public/` — Menyimpan file yang dapat diakses melalui web server.
- `storage/` — Menyimpan file dan data pendukung aplikasi.
- `tests/` — Menyimpan pengujian otomatis.
- `config/` — Menyimpan konfigurasi aplikasi Laravel.

## 9. Keamanan Sistem

Pengembangan aplikasi memperhatikan beberapa aspek keamanan, antara lain:

- Autentikasi pengguna sebelum mengakses fitur yang dilindungi.
- Pembatasan akses berdasarkan role pengguna.
- Penggunaan middleware untuk perlindungan endpoint.
- Validasi input pada proses pengolahan data.
- Pengaturan koneksi database melalui file environment.
- Pembatasan percobaan login menggunakan mekanisme rate limiting.
- Pengujian keamanan endpoint dan hak akses API.
- Pengelolaan token autentikasi menggunakan JWT.

## 10. Persyaratan Sistem

### Perangkat Lunak
- Windows atau sistem operasi yang kompatibel.
- PHP 8.2 atau versi yang kompatibel dengan project.
- Composer.
- MySQL atau MariaDB.
- XAMPP.
- Web browser modern.
- Visual Studio Code (opsional).

### Persyaratan Database
Database MySQL/MariaDB harus tersedia dan disesuaikan dengan konfigurasi aplikasi. Struktur tabel wajib disiapkan sesuai skema database project.

## 11. Instalasi dan Konfigurasi

### A. Persiapan Project

1. Clone repository atau salin folder project.
2. Buka terminal pada direktori project.
3. Masuk ke direktori Laravel:

```bash
cd laravel
```

### B. Instalasi Dependency

Jalankan perintah:

```bash
composer install
```

### C. Konfigurasi Environment

Salin file `.env.example` menjadi `.env`, kemudian sesuaikan konfigurasi aplikasi.

Contoh konfigurasi database:

```env
APP_NAME="Amih Laundry"
APP_ENV=local
APP_DEBUG=false
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=amih_laundry_db
DB_USERNAME=root
DB_PASSWORD=
```

Sesuaikan nama database, username, dan password dengan konfigurasi MySQL/MariaDB lokal.

### D. Persiapan Database

1. Jalankan Apache dan MySQL melalui XAMPP.
2. Buat database dengan nama yang sesuai konfigurasi `.env`.
3. Siapkan struktur tabel berdasarkan skema database project.
4. Pastikan tabel dan relasi yang dibutuhkan sudah tersedia.

**Catatan:** Instalasi database mengikuti skema project. Jangan menjalankan migrasi secara sembarangan pada database yang sudah berisi data.

### E. Generate Application Key

```bash
php artisan key:generate
```

### F. Menjalankan Aplikasi

```bash
php artisan serve
```

Aplikasi dapat diakses melalui browser:

```text
http://127.0.0.1:8000
```

## 12. Pengujian Sistem

Pengujian dilakukan untuk memastikan fungsi aplikasi berjalan sesuai kebutuhan dan hak akses setiap pengguna.

Perintah menjalankan seluruh pengujian otomatis:

```bash
php artisan test
```

Aspek pengujian meliputi:

- Pengujian autentikasi dan proses login.
- Pengujian otorisasi berdasarkan role.
- Pengujian endpoint API.
- Pengujian validasi data.
- Pengujian pengelolaan transaksi.
- Pengujian penanganan kesalahan sistem.
- Pengujian keamanan akses pengguna.

## 13. Version Control

Git digunakan untuk mengelola perubahan kode selama proses pengembangan, menjaga riwayat pekerjaan, serta mendukung kolaborasi tim.

Perintah dasar:

```bash
git status
git add .
git commit -m "Deskripsi perubahan"
git push
git pull
```

## 14. Tim Pengembang

| Keterangan | Identitas |
|---|---|
| Nama Tim | Just Us |
| Backend & Database | Haura Nawapyah |
| Frontend | Amel |
| Sekolah | SMK Budi Bakti Ciwidey |
| Program Keahlian | PPLG / RPL |
| Tahun Pelajaran | 2026/2027 |

## 15. Penutup

Amih Laundry Management System diharapkan dapat menjadi solusi digital dalam mendukung pengelolaan operasional laundry secara terintegrasi. Melalui pengembangan sistem ini, proses pencatatan, transaksi, pembayaran, pemantauan pengerjaan, hingga penyusunan laporan dapat dilakukan secara lebih terstruktur.

Project ini dikembangkan sebagai bagian dari kegiatan Uji Kompetensi Keahlian (UKK) Rekayasa Perangkat Lunak dengan menerapkan teknologi pengembangan web, pengelolaan database, implementasi backend, serta integrasi sistem berdasarkan kebutuhan operasional.

---

**Amih Laundry Management System**  
*Integrated Laundry Operations Management*  

Developed by **Just Us Team**  
SMK Budi Bakti Ciwidey | 2026/2027
