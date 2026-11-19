# API Documentation — Amih Laundry

Dokumentasi ini berisi daftar endpoint API yang tersedia pada aplikasi Amih Laundry Management System.

**Base URL lokal:** `http://127.0.0.1:8000/api`

## 1. Authentication

| Method | Endpoint | Fungsi |
|---|---|---|
| POST | `/auth/login` | Login pengguna |
| GET | `/auth/me` | Informasi pengguna yang sedang login |

## 2. Pelanggan

| Method | Endpoint | Fungsi |
|---|---|---|
| GET | `/pelanggan` | Menampilkan daftar pelanggan |
| POST | `/pelanggan` | Menambahkan pelanggan |
| POST | `/pelanggan-umum` | Membuat transaksi pelanggan umum |
| GET | `/pelanggan/{id}` | Melihat detail pelanggan |
| PUT | `/pelanggan/{id}` | Memperbarui pelanggan |
| DELETE | `/pelanggan/{id}` | Menghapus pelanggan |

## 3. Transaksi

| Method | Endpoint | Fungsi |
|---|---|---|
| GET | `/transaksi` | Menampilkan daftar transaksi |
| POST | `/transaksi` | Membuat transaksi |
| GET | `/transaksi/{id}` | Melihat detail transaksi |
| POST | `/transaksi/{id}/pembayaran` | Mencatat pembayaran |
| POST | `/transaksi/{id}/proses` | Memperbarui proses laundry |
| GET | `/proses-cucian` | Menampilkan daftar proses cucian |

## 4. Pembayaran

| Method | Endpoint | Fungsi |
|---|---|---|
| GET | `/pembayaran/menunggu` | Menampilkan pembayaran yang menunggu |
| POST | `/pembayaran/{id}/tolak` | Menolak pembayaran |
| POST | `/pembayaran/{id}/verifikasi` | Memverifikasi pembayaran |

## 5. Layanan

| Method | Endpoint | Fungsi |
|---|---|---|
| GET | `/layanan` | Menampilkan daftar layanan |
| POST | `/layanan` | Menambahkan layanan |
| GET | `/layanan/{id}` | Melihat detail layanan |
| PUT | `/layanan/{id}` | Memperbarui layanan |
| DELETE | `/layanan/{id}` | Menghapus layanan |

## 6. Pengguna

| Method | Endpoint | Fungsi |
|---|---|---|
| GET | `/pengguna` | Menampilkan daftar pengguna |
| POST | `/pengguna` | Menambahkan pengguna |
| PUT | `/pengguna/{id}` | Memperbarui pengguna |
| DELETE | `/pengguna/{id}` | Menghapus pengguna |
| PUT | `/pengguna/{id}/reset-password` | Mengatur ulang password |

## 7. Biaya Operasional

| Method | Endpoint | Fungsi |
|---|---|---|
| GET | `/biaya-operasional` | Menampilkan biaya operasional |
| POST | `/biaya-operasional` | Menambahkan biaya |
| PUT | `/biaya-operasional/{id}` | Memperbarui biaya |
| DELETE | `/biaya-operasional/{id}` | Menghapus biaya |

## 8. Laporan

| Method | Endpoint | Fungsi |
|---|---|---|
| GET | `/laporan/biaya` | Menampilkan laporan biaya |
| GET | `/laporan/keuntungan` | Menampilkan laporan keuntungan |
| GET | `/laporan/pendapatan` | Menampilkan laporan pendapatan |

## 9. Owner

| Method | Endpoint | Fungsi |
|---|---|---|
| GET | `/owner/overview` | Menampilkan ringkasan dashboard owner |
| GET | `/owner/biaya` | Menampilkan data biaya owner |
| GET | `/owner/transaksi` | Menampilkan data transaksi owner |

## 10. Customer

| Method | Endpoint | Fungsi |
|---|---|---|
| GET | `/customer/dashboard` | Menampilkan dashboard customer |
| PUT | `/customer/password` | Mengubah password customer |
| POST | `/customer/transaksi/{id}/bukti-pembayaran` | Mengunggah bukti pembayaran |

**Total:** 40 endpoint API.

**Catatan:** Method, alamat endpoint, dan nama fungsi disesuaikan dengan hasil `php artisan route:list --path=api`. Detail request body, parameter, autentikasi, dan response belum dicantumkan karena perlu diperiksa langsung pada implementasi masing-masing endpoint.