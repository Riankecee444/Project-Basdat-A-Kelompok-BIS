# API DOCUMENTATION
# BANK IMAN SYARIAH

## 1. Deskripsi Proyek

Bank Iman Syariah merupakan aplikasi perbankan berbasis web yang menyediakan informasi rekening, transaksi, pembiayaan, pembayaran zakat, dan profil pengguna.

REST API dikembangkan menggunakan PHP Native dan MySQL untuk menghubungkan aplikasi dengan database.

## 2. Teknologi yang Digunakan

- PHP Native
- MySQL
- XAMPP
- REST API
- JSON
- Postman
- Visual Studio Code

## 3. Base URL

Alamat API saat dijalankan secara lokal:

http://localhost/bank_iman_syariah/api/

## 4. Autentikasi

API menggunakan sistem autentikasi Bearer Token.

Pengguna harus melakukan login terlebih dahulu untuk mendapatkan token akses.

Token digunakan pada endpoint yang membutuhkan autentikasi.

Format Header:

Authorization: Bearer TOKEN_ANDA

## 5. Daftar Endpoint API

| No. | Endpoint | Method | Keterangan |
|---|---|---|---|
| 1 | login.php | POST | Login dan mendapatkan token |
| 2 | dashboard.php | GET | Mengambil data dashboard |
| 3 | accounts.php | GET | Mengambil data rekening |
| 4 | transactions.php | GET | Mengambil data transaksi |
| 5 | financing.php | GET | Mengambil data pembiayaan |
| 6 | zakat.php | GET | Mengambil data pembayaran zakat |
| 7 | profile.php | GET | Mengambil data profil pengguna |
| 8 | test.php | GET | Menguji koneksi API dan database |

## 6. Detail Endpoint

### A. Login

Endpoint:
POST /login.php

Request Body:
{
    "username": "rian",
    "password": "rian123"
}

Fungsi:
- Memeriksa username dan password.
- Memverifikasi password pengguna.
- Membuat token akses.
- Menyimpan hash token ke database.

### B. Dashboard

Endpoint:
GET /dashboard.php

Header:
Authorization: Bearer TOKEN_ANDA

Fungsi:
- Mengambil total saldo rekening aktif.
- Mengambil tiga transaksi terbaru.

### C. Accounts

Endpoint:
GET /accounts.php

Header:
Authorization: Bearer TOKEN_ANDA

Fungsi:
Mengambil informasi rekening pengguna, seperti nomor rekening, jenis akad, saldo, dan status rekening.

### D. Transactions

Endpoint:
GET /transactions.php

Header:
Authorization: Bearer TOKEN_ANDA

Fungsi:
Mengambil riwayat transaksi pengguna, termasuk kode transaksi, keterangan, jenis transaksi, nominal, dan tanggal.

### E. Financing

Endpoint:
GET /financing.php

Header:
Authorization: Bearer TOKEN_ANDA

Fungsi:
Mengambil informasi pembiayaan pengguna, seperti jenis akad, plafon, margin, tenor, angsuran, dan status.

### F. Zakat

Endpoint:
GET /zakat.php

Header:
Authorization: Bearer TOKEN_ANDA

Fungsi:
Mengambil riwayat pembayaran zakat dan total pembayaran zakat pengguna.

### G. Profile

Endpoint:
GET /profile.php

Header:
Authorization: Bearer TOKEN_ANDA

Fungsi:
Mengambil informasi profil pengguna, seperti nama, username, nomor HP, dan alamat.

### H. Test API

Endpoint:
GET /test.php

Fungsi:
Menguji apakah REST API berhasil terhubung dengan database MySQL.

## 7. HTTP Response Code

| Status Code | Keterangan |
|---|---|
| 200 | Request berhasil |
| 400 | Request tidak valid |
| 401 | Token tidak valid atau tidak ditemukan |
| 405 | Method tidak diizinkan |
| 500 | Kesalahan pada server |

## 8. Pengujian API

Pengujian REST API dilakukan menggunakan aplikasi Postman.

Pengujian meliputi:
1. Menguji koneksi API dengan database.
2. Menguji login pengguna.
3. Menguji pengambilan data dashboard.
4. Menguji pengambilan data rekening.
5. Menguji pengambilan data transaksi.
6. Menguji pengambilan data pembiayaan.
7. Menguji pengambilan data zakat.
8. Menguji pengambilan data profil.
9. Menguji akses tanpa token.
10. Menguji akses menggunakan token palsu.

## 9. Keamanan API

Sistem API menerapkan beberapa mekanisme keamanan:

- Password disimpan menggunakan password hashing.
- Autentikasi menggunakan Bearer Token.
- Token disimpan dalam bentuk hash SHA-256.
- Token memiliki masa berlaku 24 jam.
- Endpoint data pengguna dilindungi autentikasi.
- Data pengguna diambil berdasarkan user ID dari token.

## 10. Struktur Folder API

bank_iman_syariah/
|
|-- api/
|   |-- config.php
|   |-- auth.php
|   |-- test.php
|   |-- login.php
|   |-- dashboard.php
|   |-- accounts.php
|   |-- transactions.php
|   |-- financing.php
|   |-- zakat.php
|   |-- profile.php
|
|-- config/
|   |-- database.php
|
|-- database/
|-- pages/
|-- assets/
|-- includes/
|-- auth/
|-- index.php

## 11. Kesimpulan

REST API Bank Iman Syariah berhasil dikembangkan menggunakan PHP Native dan MySQL.

API menyediakan layanan autentikasi pengguna serta pengambilan data rekening, transaksi, dashboard, pembiayaan, zakat, dan profil.

Pengujian dilakukan menggunakan Postman untuk memastikan endpoint dapat berjalan dan sistem autentikasi dapat melindungi data pengguna.