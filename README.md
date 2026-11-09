# BANK IMAN SYARIAH

**Aplikasi M-Banking Berbasis Web dengan Sistem Perbankan Syariah**

## 1. Deskripsi Proyek

Bank Iman Syariah merupakan aplikasi M-Banking berbasis web yang dikembangkan untuk menyediakan layanan informasi perbankan syariah secara digital.

Aplikasi ini dibuat menggunakan PHP Native dan MySQL dengan fitur pengelolaan informasi rekening, transaksi, pembiayaan, pembayaran zakat, serta profil pengguna.

Proyek ini juga dilengkapi REST API untuk menyediakan akses data dalam format JSON dengan sistem autentikasi Bearer Token.

## 2. Tujuan Proyek

- Menerapkan konsep pengembangan aplikasi berbasis web.
- Mengembangkan sistem frontend dan backend.
- Mengelola data menggunakan database MySQL.
- Mengimplementasikan REST API.
- Menerapkan autentikasi dan keamanan akses pengguna.

## 3. Fitur Aplikasi

| No. | Fitur | Keterangan |
|---|---|---|
| 1 | Login | Autentikasi pengguna |
| 2 | Dashboard | Informasi saldo dan transaksi terbaru |
| 3 | Rekening | Informasi nomor rekening, akad, dan saldo |
| 4 | Transaksi | Riwayat transaksi masuk dan keluar |
| 5 | Pembiayaan | Informasi pembiayaan syariah |
| 6 | Zakat | Informasi dan riwayat pembayaran zakat |
| 7 | Profil | Informasi dan pengelolaan profil pengguna |
| 8 | REST API | Layanan pertukaran data menggunakan JSON |

## 4. Teknologi yang Digunakan

- HTML
- CSS
- PHP Native
- MySQL
- XAMPP
- REST API
- JSON
- Postman
- Visual Studio Code
- Figma

## 5. Struktur Folder

    bank_iman_syariah/
    ├── api/
    │   ├── config.php
    │   ├── auth.php
    │   ├── test.php
    │   ├── login.php
    │   ├── dashboard.php
    │   ├── accounts.php
    │   ├── transactions.php
    │   ├── financing.php
    │   ├── zakat.php
    │   └── profile.php
    ├── assets/
    │   └── css/
    │       └── style.css
    ├── auth/
    │   └── login.php
    ├── config/
    │   └── database.php
    ├── database/
    ├── docs/
    │   └── API_DOCUMENTATION.md
    ├── includes/
    │   └── sidebar.php
    ├── pages/
    │   ├── dashboard.php
    │   ├── accounts.php
    │   ├── transactions.php
    │   ├── financing.php
    │   ├── zakat.php
    │   └── profile.php
    ├── index.php
    └── README.md

## 6. Persyaratan Menjalankan Aplikasi

- PHP
- MySQL
- XAMPP
- Web Browser
- Visual Studio Code (opsional)

## 7. Cara Instalasi dan Menjalankan Aplikasi

1. Download atau clone repository proyek.
2. Pindahkan folder proyek ke direktori `htdocs`.
3. Buka aplikasi XAMPP.
4. Jalankan Apache dan MySQL.
5. Buka phpMyAdmin.
6. Buat database dengan nama `bank_iman_syariah`.
7. Import file database proyek.
8. Sesuaikan konfigurasi database pada `config/database.php`.
9. Buka browser.
10. Akses alamat berikut:

    http://localhost/bank_iman_syariah/

## 8. Akun Demo

| Keterangan | Data |
|---|---|
| Nama | Rian Pratama |
| Username | rian |
| Password | rian123 |

Catatan: Akun demo hanya digunakan untuk pengujian lokal.

## 9. Dokumentasi REST API

REST API digunakan untuk mengakses data aplikasi melalui endpoint dengan format JSON.

Dokumentasi lengkap API tersedia pada:

`docs/API_DOCUMENTATION.md`

### Daftar Endpoint

| No. | Endpoint | Method | Fungsi |
|---|---|---|---|
| 1 | `/api/test.php` | GET | Menguji koneksi API |
| 2 | `/api/login.php` | POST | Login dan mendapatkan token |
| 3 | `/api/dashboard.php` | GET | Mengambil data dashboard |
| 4 | `/api/accounts.php` | GET | Mengambil data rekening |
| 5 | `/api/transactions.php` | GET | Mengambil data transaksi |
| 6 | `/api/financing.php` | GET | Mengambil data pembiayaan |
| 7 | `/api/zakat.php` | GET | Mengambil data zakat |
| 8 | `/api/profile.php` | GET | Mengambil data profil |

### Autentikasi API

Endpoint data pengguna menggunakan Bearer Token.

Format header:

    Authorization: Bearer TOKEN_ANDA

Token diperoleh melalui endpoint login dan memiliki masa berlaku 24 jam.

## 10. Pengujian API

Pengujian dilakukan menggunakan Postman dengan beberapa skenario:

- Menguji koneksi API dan database.
- Menguji login pengguna.
- Menguji pengambilan data setiap endpoint.
- Menguji akses tanpa token.
- Menguji penggunaan token palsu.
- Menguji masa berlaku token.

## 11. Keamanan Sistem

Beberapa mekanisme keamanan yang diterapkan:

- Password pengguna disimpan menggunakan password hashing.
- Autentikasi API menggunakan Bearer Token.
- Token disimpan dalam bentuk hash SHA-256.
- Token memiliki masa berlaku.
- Endpoint data pengguna dilindungi autentikasi.
- Data pengguna diakses berdasarkan identitas dari token.

## 12. Dokumentasi Tampilan

Screenshot aplikasi dan hasil pengujian dapat ditambahkan pada folder `screenshots` setelah tersedia.

## 13. Status Pengembangan

**Status:** Dalam tahap pengembangan dan pengujian.

Pengembangan mencakup frontend, backend, database, REST API, dokumentasi, dan persiapan deployment.

## 14. Lisensi

Proyek ini dikembangkan untuk keperluan pembelajaran dan tugas perkuliahan.