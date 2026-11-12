# API DOCUMENTATION
# BANK IMAN SYARIAH

## 1. Deskripsi Proyek

Bank Iman Syariah merupakan aplikasi perbankan berbasis web yang menyediakan layanan pengelolaan rekening, transaksi, transfer, setor tunai, pembiayaan, pembayaran zakat, serta pengelolaan profil nasabah.

Aplikasi memiliki dua jenis pengguna, yaitu:
- **Nasabah:** Mengakses informasi rekening, transaksi, pembiayaan, zakat, dan profil.
- **Admin:** Mengelola data nasabah, rekening, setor tunai, transfer, transaksi, laporan, dan pengajuan pembiayaan.

REST API dikembangkan menggunakan PHP Native dan MySQL untuk menyediakan layanan pertukaran data antara aplikasi dengan database.

## 2. Teknologi yang Digunakan

- PHP Native
- MySQL
- XAMPP
- REST API
- JSON
- Postman
- Visual Studio Code
- HTML
- CSS
- JavaScript

## 3. Base URL

Alamat API ketika dijalankan secara lokal:

`http://localhost/bank_iman_syariah/api/`

## 4. Autentikasi

API menggunakan sistem autentikasi Bearer Token.

Pengguna harus melakukan login terlebih dahulu untuk mendapatkan token akses. Token digunakan pada endpoint yang membutuhkan autentikasi.

Format Header:

`Authorization: Bearer TOKEN_ANDA`

Ketentuan autentikasi:
- Password pengguna diverifikasi menggunakan password hashing.
- Token akses disimpan dalam bentuk hash SHA-256.
- Token memiliki masa berlaku 24 jam.
- Endpoint data pengguna memerlukan token yang valid.
- Data pengguna diambil berdasarkan user ID dari token.

## 5. Daftar Endpoint REST API

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

**Catatan:** Daftar di atas merupakan endpoint yang tercantum dalam struktur API proyek. Fitur transfer, setor tunai, dan persetujuan pembiayaan tersedia pada aplikasi web, tetapi belum dicantumkan sebagai endpoint REST API tersendiri.

## 6. Detail Endpoint

### A. Login

**Endpoint:** `POST /login.php`

Request Body:

```json
{
    "username": "username_pengguna",
    "password": "password_pengguna"
}
```

Fungsi:
- Memeriksa username dan password.
- Memverifikasi password pengguna.
- Membuat token akses.
- Menyimpan hash token ke database.

### B. Dashboard

**Endpoint:** `GET /dashboard.php`

Header:

`Authorization: Bearer TOKEN_ANDA`

Fungsi:
- Mengambil total saldo rekening aktif.
- Mengambil tiga transaksi terbaru.

### C. Accounts

**Endpoint:** `GET /accounts.php`

Header:

`Authorization: Bearer TOKEN_ANDA`

Fungsi:

Mengambil informasi rekening pengguna, meliputi:
- Nomor rekening.
- Jenis akad.
- Saldo.
- Status rekening.

### D. Transactions

**Endpoint:** `GET /transactions.php`

Header:

`Authorization: Bearer TOKEN_ANDA`

Fungsi:

Mengambil riwayat transaksi pengguna, meliputi:
- Kode transaksi.
- Keterangan.
- Jenis transaksi.
- Nominal.
- Tanggal transaksi.

### E. Financing

**Endpoint:** `GET /financing.php`

Header:

`Authorization: Bearer TOKEN_ANDA`

Fungsi:

Mengambil informasi pembiayaan pengguna, meliputi:
- Jenis akad.
- Plafon.
- Margin.
- Tenor.
- Angsuran.
- Status pembiayaan.

### F. Zakat

**Endpoint:** `GET /zakat.php`

Header:

`Authorization: Bearer TOKEN_ANDA`

Fungsi:

Mengambil riwayat pembayaran zakat dan total pembayaran zakat pengguna.

### G. Profile

**Endpoint:** `GET /profile.php`

Header:

`Authorization: Bearer TOKEN_ANDA`

Fungsi:

Mengambil informasi profil pengguna, meliputi:
- Nama.
- Username.
- Nomor HP.
- Alamat.

### H. Test API

**Endpoint:** `GET /test.php`

Fungsi:

Menguji apakah REST API berhasil dijalankan dan terhubung dengan database MySQL.

## 7. Modul Website

Selain REST API, aplikasi Bank Iman Syariah memiliki fitur yang dijalankan melalui halaman PHP pada folder `pages`.

### A. Modul Nasabah

- Dashboard nasabah.
- Informasi rekening.
- Riwayat transaksi.
- Transfer.
- Pembiayaan.
- Pengajuan pembiayaan.
- Pembayaran zakat.
- Pengelolaan profil.

### B. Modul Admin

- Dashboard admin.
- Data nasabah.
- Data rekening.
- Setor tunai.
- Transfer.
- Data transaksi.
- Laporan.
- Pengajuan pembiayaan.

### C. Alur Persetujuan Pembiayaan

1. Nasabah mengisi formulir pengajuan pembiayaan.
2. Data disimpan ke tabel `financing_applications`.
3. Status awal pengajuan adalah `Menunggu Persetujuan`.
4. Admin memeriksa pengajuan melalui halaman Pengajuan Pembiayaan.
5. Admin dapat menyetujui atau menolak pengajuan.
6. Jika disetujui, data pembiayaan dimasukkan ke tabel `financings` dengan status `Aktif`.
7. Jika ditolak, status pengajuan berubah menjadi `Ditolak`.

## 8. HTTP Response Code

| Status Code | Keterangan |
|---|---|
| 200 | Request berhasil |
| 400 | Request tidak valid |
| 401 | Token tidak valid atau tidak ditemukan |
| 405 | Method tidak diizinkan |
| 500 | Kesalahan pada server |

## 9. Pengujian API

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

## 10. Keamanan API

Sistem API menerapkan beberapa mekanisme keamanan:

- Password disimpan menggunakan password hashing.
- Autentikasi menggunakan Bearer Token.
- Token disimpan dalam bentuk hash SHA-256.
- Token memiliki masa berlaku 24 jam.
- Endpoint data pengguna dilindungi autentikasi.
- Data pengguna diambil berdasarkan user ID dari token.

## 11. Struktur Folder Proyek

```text
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
|-- auth/
|   |-- login.php
|   |-- otp.php
|   |-- verify_otp.php
|
|-- assets/
|   |-- css/
|   |   |-- admin.css
|   |   |-- style.css
|   |-- uploads/
|       |-- profiles/
|
|-- config/
|   |-- config.php
|   |-- database.php
|
|-- database/
|   |-- bank_iman_syariah.sql
|
|-- docs/
|   |-- API_DOCUMENTATION.md
|
|-- includes/
|   |-- admin_sidebar.php
|   |-- sidebar.php
|
|-- pages/
|   |-- admin/
|   |-- dashboard.php
|   |-- accounts.php
|   |-- transactions.php
|   |-- financing.php
|   |-- proses_pengajuan_pembiayaan.php
|
|-- index.php
```

## 12. Kesimpulan

REST API Bank Iman Syariah dikembangkan menggunakan PHP Native dan MySQL untuk mendukung pertukaran data aplikasi dengan database.

API menyediakan layanan autentikasi pengguna serta pengambilan data dashboard, rekening, transaksi, pembiayaan, zakat, dan profil.

Aplikasi juga memiliki modul nasabah dan admin untuk mendukung pengelolaan layanan perbankan, termasuk transfer, setor tunai, laporan, dan persetujuan pengajuan pembiayaan.

Pengujian API dilakukan menggunakan Postman untuk memeriksa koneksi database, fungsi endpoint, dan mekanisme autentikasi.