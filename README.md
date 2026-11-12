# 🏦 BANK IMAN SYARIAH
### Aplikasi Internet Banking Berbasis Web

Bank Iman Syariah merupakan aplikasi perbankan berbasis web yang dikembangkan untuk menyediakan layanan perbankan digital dengan konsep perbankan syariah.

Aplikasi ini memiliki dua jenis pengguna, yaitu Nasabah dan Admin. Nasabah dapat mengakses informasi rekening, transaksi, transfer, pembiayaan, zakat, dan profil. Admin dapat mengelola data nasabah, rekening, transaksi, serta pengajuan pembiayaan.

---

## 📌 Fitur Aplikasi

### 1. Fitur Nasabah
- Login dan autentikasi pengguna.
- Dashboard informasi saldo.
- Melihat data rekening.
- Melihat riwayat transaksi.
- Transfer antar rekening.
- Setor tunai melalui layanan admin.
- Mengajukan pembiayaan.
- Melihat status pengajuan pembiayaan.
- Melihat informasi pembiayaan aktif.
- Informasi dan riwayat pembayaran zakat.
- Mengelola profil dan foto pengguna.

### 2. Fitur Admin
- Dashboard admin.
- Mengelola data nasabah.
- Mengelola data rekening.
- Mengelola setor tunai.
- Mengelola transfer.
- Melihat data transaksi.
- Melihat laporan transaksi.
- Melihat pengajuan pembiayaan.
- Menyetujui atau menolak pengajuan pembiayaan.

### 3. REST API
REST API digunakan untuk menghubungkan aplikasi dengan database melalui format JSON.

Endpoint API yang tersedia:

| No. | Endpoint | Method | Fungsi |
|---|---|---|---|
| 1 | `login.php` | POST | Login pengguna |
| 2 | `dashboard.php` | GET | Data dashboard |
| 3 | `accounts.php` | GET | Data rekening |
| 4 | `transactions.php` | GET | Riwayat transaksi |
| 5 | `financing.php` | GET | Data pembiayaan |
| 6 | `zakat.php` | GET | Data zakat |
| 7 | `profile.php` | GET | Data profil |
| 8 | `test.php` | GET | Pengujian koneksi API |

Dokumentasi lengkap API tersedia di:

`docs/API_DOCUMENTATION.md`

---

## 🛠️ Teknologi yang Digunakan

| Teknologi | Kegunaan |
|---|---|
| PHP Native | Backend aplikasi |
| MySQL | Database |
| HTML | Struktur halaman |
| CSS | Desain antarmuka |
| JavaScript | Interaksi halaman |
| REST API | Komunikasi data |
| JSON | Format pertukaran data |
| XAMPP | Server lokal |
| Postman | Pengujian API |
| Visual Studio Code | Editor kode |

---

## 📂 Struktur Folder

```text
bank_iman_syariah/
│
├── api/
│   ├── accounts.php
│   ├── auth.php
│   ├── config.php
│   ├── dashboard.php
│   ├── financing.php
│   ├── login.php
│   ├── profile.php
│   ├── test.php
│   ├── transactions.php
│   └── zakat.php
│
├── assets/
│   ├── css/
│   │   ├── admin.css
│   │   └── style.css
│   └── uploads/
│       └── profiles/
│
├── auth/
│   ├── login.php
│   ├── otp.php
│   └── verify_otp.php
│
├── config/
│   ├── config.php
│   └── database.php
│
├── database/
│   └── bank_iman_syariah.sql
│
├── docs/
│   └── API_DOCUMENTATION.md
│
├── includes/
│   ├── admin_sidebar.php
│   └── sidebar.php
│
├── pages/
│   └── admin/
│
├── index.php
└── README.md
```

*Catatan: Struktur di atas merupakan gambaran direktori utama proyek. File lainnya dapat ditambahkan sesuai pengembangan aplikasi.*

---

## ⚙️ Persyaratan Sistem

Sebelum menjalankan aplikasi, pastikan perangkat sudah memiliki:

1. XAMPP.
2. PHP.
3. MySQL atau MariaDB.
4. Visual Studio Code.
5. Web Browser.

---

## 🚀 Cara Instalasi dan Menjalankan Aplikasi

### 1. Clone Repository

```bash
git clone URL_REPOSITORY_GITHUB
```

Atau unduh repository dalam format ZIP.

### 2. Pindahkan Folder Proyek

Letakkan folder `bank_iman_syariah` ke dalam direktori:

```text
C:\xampp\htdocs\
```

### 3. Jalankan XAMPP

Aktifkan:
- Apache
- MySQL

### 4. Import Database

1. Buka `http://localhost/phpmyadmin`.
2. Buat database bernama `bank_iman_syariah`.
3. Pilih database tersebut.
4. Buka menu Import.
5. Pilih file `database/bank_iman_syariah.sql`.
6. Klik Import.

### 5. Konfigurasi Database

Buka file:

`config/database.php`

Sesuaikan konfigurasi database dengan pengaturan lokal:

```php
<?php

$host = "localhost";
$user = "root";
$password = "";
$database = "bank_iman_syariah";

$conn = mysqli_connect(
    $host,
    $user,
    $password,
    $database
);

if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}
```

Sesuaikan konfigurasi apabila username atau password MySQL berbeda.

### 6. Jalankan Aplikasi

Buka browser dan akses:

```text
http://localhost/bank_iman_syariah/
```

### 7. Mengakses REST API

Base URL:

```text
http://localhost/bank_iman_syariah/api/
```

Pengujian endpoint dapat dilakukan menggunakan Postman.

---

## 🔐 Keamanan

Aplikasi menerapkan beberapa mekanisme keamanan, antara lain:

- Password menggunakan password hashing.
- Autentikasi REST API menggunakan Bearer Token.
- Token disimpan dalam bentuk hash SHA-256.
- Token memiliki masa berlaku.
- Endpoint API tertentu membutuhkan autentikasi.
- Query database menggunakan prepared statements pada bagian yang menerapkannya.
- Halaman admin dilindungi berdasarkan role pengguna.

**Penting:** Jangan mengunggah password database, token akses, data nasabah asli, atau informasi rahasia ke repository publik.

---

## 🗄️ Database

Database yang digunakan bernama:

`bank_iman_syariah`

Database disimpan dalam format SQL pada folder:

`database/bank_iman_syariah.sql`

File tersebut digunakan untuk membuat ulang struktur database dan data yang diperlukan untuk menjalankan aplikasi.

---

## 📖 Dokumentasi

Dokumentasi tambahan proyek:

- `docs/API_DOCUMENTATION.md` — Dokumentasi REST API.
- `database/bank_iman_syariah.sql` — Struktur dan data database.

---

## 👥 Tim Pengembang

**Nama Proyek:** Bank Iman Syariah

**Jenis Proyek:** Aplikasi Perbankan Berbasis Web

**Mata Kuliah:** [Basis data]

**Dosen / Pembimbing:** [Dr. Ir. Iman Paryudi, M.Sc.]

**Anggota Kelompok:**

| No. | Nama Anggota | NIM | Kontribusi |
|---|---|---|---|
| 1 | [Muhammad Ryza Mahameru] | [4525210048] | [Kontribusi] |
| 2 | [Azzahra Navisha] | [4525210118] | [Kontribusi] |
| 3 | [Muhammad Rian Ramadhan] | [4525210047] | [Kontribusi] |
| 4 | [Muhammad Rava Alvriansyah] | [4525210040] | [Kontribusi] |
| 5 | [Muhammad Faisal Athallah] | [4525210120] | [Kontribusi] |
| 6 | [Delia Veriz Maureta] | [45252100119] | [Kontribusi] |
| 7 | [Naufalino Ahmad Saputra] | [4525210139] | [Kontribusi] |

---

## 📌 Catatan Pengembangan

Bank Iman Syariah dikembangkan sebagai proyek pembelajaran untuk menerapkan konsep pemrograman web, pengelolaan database MySQL, autentikasi pengguna, REST API, serta pengelolaan layanan perbankan berbasis web.

Pengembangan dilakukan secara bertahap dengan memperhatikan fungsionalitas, keamanan, dan kemudahan penggunaan aplikasi.

---

**BANK IMAN SYARIAH**  
*Digital Banking with Sharia Principles.*