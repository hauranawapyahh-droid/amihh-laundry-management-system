# LAPORAN SPRINT 3 - AMIH LAUNDRY
Tanggal pemeriksaan: 03-11-2026 17:40
Framework: Laravel 10.50.4

### VERSI FRAMEWORK
``text

Laravel Framework 10.50.4
``

### FUNCTIONAL TESTING
``text


   PASS  Tests\Unit\ExampleTest
  Γ£ô that true is true                                                                                    0.02s  










   PASS  Tests\Feature\ApiSecurityTest
  Γ£ô login rejects missing credentials                                                                    0.40s  
  Γ£ô customer dashboard rejects missing token                                                             0.09s  
  Γ£ô user management rejects missing token                                                                0.03s  
  Γ£ô login rejects invalid credentials                                                                    0.10s  
  Γ£ô transaction list rejects missing token                                                               0.03s  
  Γ£ô reports reject missing token                                                                         0.03s  
  Γ£ô laundry process rejects missing token                                                                0.02s  
  Γ£ô customer password change rejects missing token                                                       0.03s  
  Γ£ô role middleware rejects wrong role                                                                   0.05s  


   PASS  Tests\Feature\ExampleTest
  Γ£ô the application returns a successful response                                                        0.07s  

  Tests:    11 passed (15 assertions)
  Duration: 1.26s

``

### VALIDASI SINTAKS PHP
``text
LULUS: 47 file PHP diperiksa.
``

### KONFIGURASI DEBUG
``text


  Environment ................................................................................................  
  Application Name .............................................................................. Amih Laundry  
  Laravel Version .................................................................................... 10.50.4  
  PHP Version ......................................................................................... 8.2.12  
  Composer Version .................................................................................... 2.10.3  
  Environment .......................................................................................... local  
  Debug Mode ............................................................................................. OFF  
  URL .............................................................................................. localhost  
  Maintenance Mode ....................................................................................... OFF  

Tidak ditemukan APP_DEBUG=true secara eksplisit di .env.
``

### AUDIT DEPENDENCY
``text
Found 5 security vulnerability advisories affecting 1 package:
+-------------------+----------------------------------------------------------------------------------+
| Package           | laravel/framework                                                                |
| Severity          | critical                                                                         |
| Advisory ID       | PKSA-b41t-wbzk-9q51                                                              |
| CVE               | CVE-2026-114509                                                                  |
| Title             | Laravel TokenGuard authentication bypass through non-string API credentials      |
| URL               | https://github.com/advisories/GHSA-f9x3-7vq9-cph6                                |
| Affected versions | <12.68.0|>=13.0.0,<13.29.0                                                       |
| Reported at       | 2026-10-23T21:50:27+00:00                                                        |
+-------------------+----------------------------------------------------------------------------------+
+-------------------+----------------------------------------------------------------------------------+
| Package           | laravel/framework                                                                |
| Severity          | low                                                                              |
| Advisory ID       | PKSA-d5tc-s1qs-h781                                                              |
| CVE               | CVE-2026-102279                                                                  |
| Title             | Laravel: XSS in Debug Page Information                                           |
| URL               | https://github.com/advisories/GHSA-jh5r-qr3c-85q8                                |
| Affected versions | >=13.0.0,<13.30.0|<12.69.0                                                       |
| Reported at       | 2026-09-29T18:24:25+00:00                                                        |
+-------------------+----------------------------------------------------------------------------------+
+-------------------+----------------------------------------------------------------------------------+
| Package           | laravel/framework                                                                |
| Severity          | medium                                                                           |
| Advisory ID       | PKSA-m5cs-t1y6-qpcs                                                              |
| CVE               | NO CVE                                                                           |
| Title             | Laravel Framework: Temporary Signed URL Path Confusion                           |
| URL               | https://github.com/advisories/GHSA-crmm-hgp2-wgrp                                |
| Affected versions | <12.61.1|>=13.0.0,<13.12.0                                                       |
| Reported at       | 2026-06-17T13:54:13+00:00                                                        |
+-------------------+----------------------------------------------------------------------------------+
+-------------------+----------------------------------------------------------------------------------+
| Package           | laravel/framework                                                                |
| Severity          | high                                                                             |
| Advisory ID       | PKSA-3r5d-mb8f-1qw9                                                              |
| CVE               | NO CVE                                                                           |
| Title             | Laravel Framework: CRLF injection in default email rule                          |
| URL               | https://github.com/advisories/GHSA-5vg9-5847-vvmq                                |
| Affected versions | <12.60.0|>=13.0.0,<=13.9.0                                                       |
| Reported at       | 2026-06-17T13:53:44+00:00                                                        |
+-------------------+----------------------------------------------------------------------------------+
+-------------------+----------------------------------------------------------------------------------+
| Package           | laravel/framework                                                                |
| Severity          |                                                                                  |
| Advisory ID       | PKSA-mdq4-51ck-6kdq                                                              |
| CVE               | CVE-2026-48019                                                                   |
| Title             | Laravel CRLF injection in default email rule                                     |
| URL               | https://github.com/laravel/framework/security/advisories/GHSA-5vg9-5847-vvmq     |
| Affected versions | >=9.0.0,<10.0.0|>=10.0.0,<11.0.0|>=11.0.0,<12.0.0|>=12.0.0,<12.60.0|>=13.0.0,<13 |
|                   | .10.0                                                                            |
| Reported at       | 2026-05-19T18:13:00+00:00                                                        |
+-------------------+----------------------------------------------------------------------------------+
``

### RESPONSIVE CHECK
``text
index.html : viewport=True; media-query=True
login.html : viewport=True; media-query=True
dashboard-admin.html : viewport=True; media-query=True
dashboard-kasir.html : viewport=True; media-query=True
dashboard-customer.html : viewport=True; media-query=True
dashboard-owner.html : viewport=True; media-query=True
dashboard-staff.html : viewport=True; media-query=True
Pemeriksaan ini tidak menggantikan pengujian tampilan di ponsel.
``

### CACHE
``text


   INFO  Clearing cached bootstrap files.  

  events ............................................................................................ 5ms DONE
  views ............................................................................................ 22ms DONE
  cache ............................................................................................ 29ms DONE
  route ............................................................................................. 2ms DONE
  config ............................................................................................ 2ms DONE
  compiled .......................................................................................... 5ms DONE

``

## Yang belum dapat dinyatakan selesai
- Global error handling perlu diuji untuk berbagai jenis error.
- Perbaikan bug perlu divalidasi melalui alur aplikasi sebenarnya.
- Responsivitas perlu diuji secara visual pada ukuran layar berbeda.
- Temuan Composer audit perlu ditangani berdasarkan advisori yang benar-benar berlaku.
- Performa perlu diukur, dan usability perlu diuji dengan alur pengguna.

Catatan: hasil tes otomatis bukan bukti bahwa semua fungsi bebas bug.
Tidak ada upgrade framework yang dilakukan oleh skrip ini.
