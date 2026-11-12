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

// TOKEN KEAMANAN FORM PIN
if (empty($_SESSION["csrf_pin"])) {
    $_SESSION["csrf_pin"] = bin2hex(random_bytes(32));
}

// PROSES BUAT / GANTI PIN
if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    ($_POST["aksi"] ?? "") === "ubah_pin"
) {
    $token = $_POST["csrf_pin"] ?? "";

    if (!hash_equals($_SESSION["csrf_pin"], $token)) {
        $_SESSION["pesan_profil"] = "Permintaan tidak valid.";
        header("Location: profile.php");
        exit;
    }

    $pinLama = trim($_POST["pin_lama"] ?? "");
    $pinBaru = trim($_POST["pin_baru"] ?? "");
    $konfirmasiPin = trim($_POST["konfirmasi_pin"] ?? "");

    // Ambil PIN yang tersimpan
    $stmtCekPin = mysqli_prepare(
        $conn,
        "SELECT pin_transaksi FROM users WHERE id = ?"
    );

    mysqli_stmt_bind_param($stmtCekPin, "i", $user_id);
    mysqli_stmt_execute($stmtCekPin);

    $resultPin = mysqli_stmt_get_result($stmtCekPin);
    $dataPin = mysqli_fetch_assoc($resultPin);

    mysqli_stmt_close($stmtCekPin);

    $pinTersimpan = $dataPin["pin_transaksi"] ?? null;

    if (!preg_match('/^[0-9]{6}$/', $pinBaru)) {
        $_SESSION["pesan_profil"] = "PIN baru harus terdiri dari 6 angka.";
    } elseif ($pinBaru !== $konfirmasiPin) {
        $_SESSION["pesan_profil"] = "Konfirmasi PIN tidak cocok.";
    } elseif (
        !empty($pinTersimpan) &&
        !password_verify($pinLama, $pinTersimpan)
    ) {
        $_SESSION["pesan_profil"] = "PIN lama yang kamu masukkan salah.";
    } elseif (
        !empty($pinTersimpan) &&
        $pinLama === $pinBaru
    ) {
        $_SESSION["pesan_profil"] = "PIN baru harus berbeda dari PIN lama.";
    } else {
        $hashPin = password_hash($pinBaru, PASSWORD_DEFAULT);

        $stmtUpdatePin = mysqli_prepare(
            $conn,
            "UPDATE users SET pin_transaksi = ? WHERE id = ?"
        );

        mysqli_stmt_bind_param(
            $stmtUpdatePin,
            "si",
            $hashPin,
            $user_id
        );

        if (mysqli_stmt_execute($stmtUpdatePin)) {
            $_SESSION["pesan_profil"] = "PIN transaksi berhasil diperbarui!";
        } else {
            $_SESSION["pesan_profil"] = "Gagal memperbarui PIN.";
        }

        mysqli_stmt_close($stmtUpdatePin);
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
<form method="POST"
      enctype="multipart/form-data"
      class="upload-photo-form"
      id="formUploadFoto">

    <input
        type="hidden"
        name="upload_foto"
        value="1"
    >

    <input
        type="file"
        name="foto"
        id="foto"
        accept="image/jpeg,image/png,image/webp"
        hidden
        onchange="this.form.submit()"
    >

    <button
        type="button"
        class="security-action"
        onclick="document.getElementById('foto').click()"
    >
        Ganti Foto
    </button>

    <small>JPG, PNG, atau WEBP. Maksimal 2 MB.</small>

</form>
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

             <div class="security-card">

    <strong>PIN transaksi</strong>

    <p>
        PIN 6 digit digunakan untuk mengonfirmasi
        transfer dan transaksi Anda.
    </p>

    <button
        type="button"
        class="security-action"
        onclick="bukaFormPIN()"
    >
        Buat / Ubah PIN
    </button>

</div>

<div id="formPIN" class="security-card" style="display:none;">

    <strong>Pengaturan PIN transaksi</strong>

    <form method="POST" action="profile.php">

        <input type="hidden" name="aksi" value="ubah_pin">

        <input
            type="hidden"
            name="csrf_pin"
            value="<?= htmlspecialchars($_SESSION["csrf_pin"]); ?>"
        >

        <div class="profile-field">
            <label for="pin_lama">PIN lama</label>
            <input
                type="password"
                id="pin_lama"
                name="pin_lama"
                maxlength="6"
                inputmode="numeric"
                pattern="[0-9]{6}"
                placeholder="Masukkan PIN lama"
            >
            <small>Kosongkan jika belum pernah membuat PIN.</small>
        </div>

        <div class="profile-field">
            <label for="pin_baru">PIN baru</label>
            <input
                type="password"
                id="pin_baru"
                name="pin_baru"
                maxlength="6"
                minlength="6"
                inputmode="numeric"
                pattern="[0-9]{6}"
                placeholder="Masukkan 6 angka"
                required
            >
        </div>

        <div class="profile-field">
            <label for="konfirmasi_pin">Konfirmasi PIN baru</label>
            <input
                type="password"
                id="konfirmasi_pin"
                name="konfirmasi_pin"
                maxlength="6"
                minlength="6"
                inputmode="numeric"
                pattern="[0-9]{6}"
                placeholder="Ulangi PIN baru"
                required
            >
        </div>

        <button type="submit" class="security-action">
            Simpan PIN
        </button>

        <button
            type="button"
            class="btn-cancel"
            onclick="tutupFormPIN()"
        >
            Batal
        </button>

    </form>

</div>

                </div>

                <small class="security-note">
                    Pengaturan keamanan belum diaktifkan.
                </small>

            </section>

        </div>

    </main>

</div>

<script>
function bukaFormPIN() {
    document.getElementById("formPIN").style.display = "block";
}

function tutupFormPIN() {
    document.getElementById("formPIN").style.display = "none";
}
</script>

</body>
</html>