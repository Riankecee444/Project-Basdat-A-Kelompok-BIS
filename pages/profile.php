<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: ../index.php");
    exit;
}

require_once "../config/database.php";

$user_id = (int) $_SESSION["user_id"];
$activePage = "profile";

// Proses upload foto profil
if (isset($_POST["upload_foto"])) {

    if (!isset($_FILES["foto"]) || $_FILES["foto"]["error"] !== UPLOAD_ERR_OK) {
        $_SESSION["pesan_profil"] = "Pilih foto terlebih dahulu.";
        header("Location: profile.php");
        exit;
    }

    $file = $_FILES["foto"];

    // Batas ukuran 2 MB
    if ($file["size"] > 2 * 1024 * 1024) {
        $_SESSION["pesan_profil"] = "Ukuran foto maksimal 2 MB.";
        header("Location: profile.php");
        exit;
    }

    // Validasi tipe file berdasarkan isi file
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file["tmp_name"]);

    $formatFoto = [
        "image/jpeg" => "jpg",
        "image/png"  => "png",
        "image/webp" => "webp"
    ];

    if (!isset($formatFoto[$mime])) {
        $_SESSION["pesan_profil"] = "Foto harus JPG, PNG, atau WEBP.";
        header("Location: profile.php");
        exit;
    }

    $folderFoto = "../assets/uploads/profiles/";

    if (!is_dir($folderFoto)) {
        mkdir($folderFoto, 0755, true);
    }

    $namaFile = bin2hex(random_bytes(16)) . "." . $formatFoto[$mime];
    $lokasiFoto = $folderFoto . $namaFile;
    $pathDatabase = "assets/uploads/profiles/" . $namaFile;

    if (move_uploaded_file($file["tmp_name"], $lokasiFoto)) {

        $stmtFoto = mysqli_prepare(
            $conn,
            "UPDATE users SET foto_profil = ? WHERE id = ?"
        );

        mysqli_stmt_bind_param($stmtFoto, "si", $pathDatabase, $user_id);
        mysqli_stmt_execute($stmtFoto);
        mysqli_stmt_close($stmtFoto);

        $_SESSION["pesan_profil"] = "Foto profil berhasil diperbarui!";
    } else {
        $_SESSION["pesan_profil"] = "Gagal menyimpan foto.";
    }

    header("Location: profile.php");
    exit;
}

// Pesan notifikasi
$pesan = $_SESSION["pesan_profil"] ?? "";
unset($_SESSION["pesan_profil"]);

// Proses update profil
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nama = trim($_POST["nama"] ?? "");
    $telepon = trim($_POST["telepon"] ?? "");

    if ($nama === "") {
        $_SESSION["pesan_profil"] = "Nama tidak boleh kosong.";
    } else {

        $queryUpdate = "UPDATE users
                        SET nama = ?, no_hp = ?
                        WHERE id = ?";

        $stmtUpdate = mysqli_prepare($conn, $queryUpdate);

        mysqli_stmt_bind_param(
            $stmtUpdate,
            "ssi",
            $nama,
            $telepon,
            $user_id
        );

        if (mysqli_stmt_execute($stmtUpdate)) {
            $_SESSION["nama"] = $nama;
            $_SESSION["pesan_profil"] = "Profil berhasil diperbarui!";
        } else {
            $_SESSION["pesan_profil"] = "Gagal memperbarui profil.";
        }

        mysqli_stmt_close($stmtUpdate);
    }

    header("Location: profile.php");
    exit;
}

$queryProfil = "SELECT id, nama, username, no_hp, alamat, foto_profil
                FROM users
                WHERE id = ?";

$stmtProfil = mysqli_prepare($conn, $queryProfil);
mysqli_stmt_bind_param($stmtProfil, "i", $user_id);
mysqli_stmt_execute($stmtProfil);

$resultProfil = mysqli_stmt_get_result($stmtProfil);
$profil = mysqli_fetch_assoc($resultProfil);

mysqli_stmt_close($stmtProfil);

if (!$profil) {
    die("Data pengguna tidak ditemukan.");
}

// Nomor nasabah
$nomorNasabah = "NSB-" . str_pad($user_id, 4, "0", STR_PAD_LEFT);

// Inisial nama
$inisial = strtoupper(substr($profil["nama"], 0, 1));

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil - Bank Iman Syariah</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>

<div class="app-layout profile-container">

    <?php include "../includes/sidebar.php"; ?>

    <main class="main-content profile-content">

        <header class="profile-header">
            <div>
                <h1>Profil</h1>
                <p>Data nasabah dan pengaturan keamanan akun.</p>
            </div>
        </header>

        <?php if ($pesan !== ""): ?>
            <div class="profile-alert">
                <?= htmlspecialchars($pesan); ?>
            </div>
        <?php endif; ?>


        <!-- IDENTITAS NASABAH -->
        <section class="profile-identity">

<div class="identity-avatar">
    <?php if (!empty($profil["foto_profil"])): ?>
        <img
            src="../<?= htmlspecialchars($profil["foto_profil"]); ?>"
            alt="Foto profil"
            class="profile-photo"
        >
    <?php else: ?>
        <?= htmlspecialchars($inisial); ?>
    <?php endif; ?>
</div>

<form method="POST" enctype="multipart/form-data" class="upload-photo-form">
    <input
        type="file"
        name="foto"
        id="foto"
        accept="image/jpeg,image/png,image/webp"
        required
    >

    <button type="submit" name="upload_foto" class="security-action">
        Upload Foto
    </button>

    <small>JPG, PNG, atau WEBP. Maksimal 2 MB.</small>
</form>

            <div class="identity-info">
                <h2><?= htmlspecialchars($profil["nama"]); ?></h2>
                <p>ID Nasabah <?= htmlspecialchars($nomorNasabah); ?></p>
            </div>

            <span class="identity-status">Nasabah aktif</span>

        </section>


        <!-- DATA DAN KEAMANAN -->
        <div class="profile-columns">

            <!-- DATA NASABAH -->
            <section class="profile-details">

                <h2>Data nasabah</h2>
                <p>Sesuai data yang terdaftar di bank.</p>

                <form method="POST" action="profile.php">

                    <div class="profile-form-grid">

                        <div class="profile-field">
                            <label for="nama">Nama</label>
                            <input
                                type="text"
                                id="nama"
                                name="nama"
                                value="<?= htmlspecialchars($profil["nama"]); ?>"
                                required
                            >
                        </div>

                        <div class="profile-field">
                            <label for="nik">NIK</label>
                            <input
                                type="text"
                                id="nik"
                                value="Belum tersedia"
                                readonly
                            >
                        </div>

                        <div class="profile-field">
                            <label for="telepon">No. HP</label>
                            <input
                                type="text"
                                id="telepon"
                                name="telepon"
                                value="<?= htmlspecialchars($profil["no_hp"] ?? ""); ?>"
                            >
                        </div>

                        <div class="profile-field">
                            <label for="nomor-nasabah">ID nasabah</label>
                            <input
                                type="text"
                                id="nomor-nasabah"
                                value="<?= htmlspecialchars($nomorNasabah); ?>"
                                readonly
                            >
                        </div>

                        <div class="profile-field profile-field-full">
                            <label for="alamat">Alamat</label>
                            <input
                                type="text"
                                id="alamat"
                                value="<?= htmlspecialchars($profil["alamat"] ?? ""); ?>"
                                readonly
                            >
                        </div>

                    </div>

                    <small class="profile-note">
                        NIK, ID nasabah, dan alamat hanya dapat diubah
                        melalui kantor cabang.
                    </small>

                    <button type="submit" class="profile-save">
                        Simpan Perubahan
                    </button>

                </form>

            </section>


            <!-- KEAMANAN -->
            <section class="profile-security">

                <h2>Keamanan</h2>
                <p>Lindungi akun dan transaksi Anda.</p>

                <div class="security-card">

                    <strong>Kata sandi</strong>

                    <p>
                        Ubah kata sandi secara berkala untuk
                        menjaga keamanan akun.
                    </p>

                  <a href="ubah_password.php" class="security-action">
    Ubah kata sandi
</a>

                </div>

                <div class="security-card">

                    <strong>PIN transaksi</strong>

                    <p>
                        PIN 6 digit untuk konfirmasi transaksi
                        dan pembayaran.
                    </p>

                    <button type="button" disabled>
                        Ubah PIN
                    </button>

                </div>

                <small class="security-note">
                    Pengaturan keamanan belum diaktifkan.
                </small>

            </section>

        </div>

    </main>

</div>

</body>
</html>