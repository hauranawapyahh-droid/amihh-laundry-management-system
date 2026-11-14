# Amih Laundry Management System

Aplikasi manajemen laundry berbasis web untuk membantu pengelolaan pelanggan, transaksi, pembayaran, proses laundry, dan laporan.

## Teknologi
- Laravel 10
- PHP 8.2
- MySQL / MariaDB
- HTML, CSS, JavaScript
- XAMPP

## Hak Akses Pengguna
- **Admin:** Mengelola pelanggan, layanan, pengguna, biaya, dan laporan.
- **Kasir:** Mengelola transaksi, pembayaran, riwayat, dan pengambilan laundry.
- **Owner:** Melihat dashboard dan laporan usaha.
- **Staff Laundry:** Melihat dan memperbarui proses laundry.
- **Customer:** Melihat informasi dan transaksi laundry miliknya.

## Fitur Utama
- Manajemen data pelanggan dan layanan.
- Pencatatan transaksi laundry.
- Pengelolaan pembayaran.
- Pemantauan proses laundry.
- Pengelolaan pengambilan laundry.
- Laporan pendapatan, biaya, dan keuntungan.

## Persyaratan Sistem
- PHP 8.2 atau kompatibel.
- Composer.
- MySQL / MariaDB.
- XAMPP.
- Web browser.

## Instalasi
1. Clone atau salin repository project.
2. Masuk ke folder `laravel`.
3. Install dependency menggunakan Composer:

   `composer install`

4. Salin `.env.example` menjadi `.env`.
5. Sesuaikan konfigurasi database pada `.env`.
6. Buat database sesuai konfigurasi.
7. Jalankan:

   `php artisan key:generate`

8. Pastikan database sudah disiapkan sesuai struktur project.
9. Jalankan aplikasi:

   `php artisan serve`

10. Buka `http://127.0.0.1:8000` melalui browser.

## Pengujian
Jalankan pengujian otomatis dengan:

`php artisan test`

## Developer
**Tim:** Just Us  
**Developer:** Haura Nawapyah dan Amel  
**Sekolah:** SMK Budi Bakti Ciwidey  
**Program Keahlian:** PPLG / RPL  
**Tahun:** 2026/2027
