<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: ../index.php");
    exit;
}

require_once "../config/database.php";

$user_id = $_SESSION["user_id"];
$pesan = "";

// Memproses perubahan profil
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nama = trim($_POST["nama"] ?? "");
    $telepon = trim($_POST["telepon"] ?? "");
    $alamat = trim($_POST["alamat"] ?? "");

    if ($nama !== "") {

        $queryUpdate = "UPDATE users
                        SET nama = ?, no_hp = ?, alamat = ?
                        WHERE id = ?";

        $stmtUpdate = mysqli_prepare($conn, $queryUpdate);

        mysqli_stmt_bind_param(
            $stmtUpdate,
            "sssi",
            $nama,
            $telepon,
            $alamat,
            $user_id
        );

        if (mysqli_stmt_execute($stmtUpdate)) {
            $_SESSION["nama"] = $nama;
            $pesan = "Profil berhasil diperbarui!";
        } else {
            $pesan = "Gagal memperbarui profil.";
        }

        mysqli_stmt_close($stmtUpdate);

    } else {
        $pesan = "Nama tidak boleh kosong.";
    }
}

// Mengambil data pengguna
$queryProfil = "SELECT id, nama, username, no_hp, alamat
                FROM users
                WHERE id = ?";

$stmtProfil = mysqli_prepare($conn, $queryProfil);
mysqli_stmt_bind_param($stmtProfil, "i", $user_id);
mysqli_stmt_execute($stmtProfil);

$resultProfil = mysqli_stmt_get_result($stmtProfil);
$profil = mysqli_fetch_assoc($resultProfil);

mysqli_stmt_close($stmtProfil);

// Mengambil jenis rekening
$queryRekening = "SELECT DISTINCT jenis_akad
                  FROM accounts
                  WHERE user_id = ?";

$stmtRekening = mysqli_prepare($conn, $queryRekening);
mysqli_stmt_bind_param($stmtRekening, "i", $user_id);
mysqli_stmt_execute($stmtRekening);

$resultRekening = mysqli_stmt_get_result($stmtRekening);

$jenisRekening = [];

while ($rekening = mysqli_fetch_assoc($resultRekening)) {
    $jenisRekening[] = $rekening["jenis_akad"];
}

mysqli_stmt_close($stmtRekening);

$nomorNasabah = "NSB-" . str_pad($user_id, 4, "0", STR_PAD_LEFT);
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

<div class="profile-container">

    <aside class="profile-sidebar">
        <h2>Bank Iman Syariah</h2>
        <p class="sidebar-label">MENU UTAMA</p>

        <a href="dashboard.php">Dashboard</a>
        <a href="accounts.php">Rekening</a>
        <a href="transactions.php">Transaksi</a>
        <a href="financing.php">Pembiayaan</a>
        <a href="zakat.php">Zakat</a>
        <a href="profile.php" class="active">Profil</a>
        <a href="../index.php">Keluar</a>
    </aside>

    <main class="profile-content">

        <header class="profile-header">
            <div>
                <h1>Profil Pengguna</h1>
                <p>Informasi akun nasabah Bank Iman Syariah</p>
            </div>
        </header>

        <?php if ($pesan !== ""): ?>
            <p><?= htmlspecialchars($pesan) ?></p>
        <?php endif; ?>

        <section class="profile-card">

            <div class="profile-avatar">
                <?= strtoupper(substr($profil["nama"], 0, 1)) ?>
            </div>

            <h2><?= htmlspecialchars($profil["nama"]) ?></h2>

            <p>Nasabah Bank Iman Syariah</p>

            <span class="profile-number">
                <?= htmlspecialchars($nomorNasabah) ?>
            </span>

        </section>

        <section class="profile-details">

            <h2>Informasi Pribadi</h2>

            <form method="POST" action="">

                <div class="profile-row">
                    <label for="nama">Nama Lengkap</label>
                    <input
                        type="text"
                        id="nama"
                        name="nama"
                        value="<?= htmlspecialchars($profil["nama"]) ?>"
                        required
                    >
                </div>

                <div class="profile-row">
                    <span>Username</span>
                    <strong>
                        <?= htmlspecialchars($profil["username"]) ?>
                    </strong>
                </div>

                <div class="profile-row">
                    <label for="telepon">Nomor Telepon</label>
                    <input
                        type="text"
                        id="telepon"
                        name="telepon"
                        value="<?= htmlspecialchars($profil["no_hp"] ?? "") ?>"
                    >
                </div>

                <div class="profile-row">
                    <label for="alamat">Alamat</label>
                    <input
                        type="text"
                        id="alamat"
                        name="alamat"
                        value="<?= htmlspecialchars($profil["alamat"] ?? "") ?>"
                    >
                </div>

                <div class="profile-row">
                    <span>Jenis Rekening</span>
                    <strong>
                        <?= htmlspecialchars(
                            !empty($jenisRekening)
                                ? implode(" dan ", $jenisRekening)
                                : "Belum ada rekening"
                        ) ?>
                    </strong>
                </div>

                <button type="submit">Simpan Perubahan</button>

            </form>

        </section>

        <section class="profile-security">

            <h2>Keamanan Akun</h2>

            <div class="security-item">
                <div>
                    <strong>Kata Sandi</strong>
                    <p>Perlindungan kata sandi akun</p>
                </div>
                <span>••••••••</span>
            </div>

            <div class="security-item">
                <div>
                    <strong>Verifikasi OTP</strong>
                    <p>Verifikasi tambahan saat login</p>
                </div>
                <span class="security-active">Aktif (Simulasi)</span>
            </div>

        </section>

    </main>

</div>

</body>
</html>