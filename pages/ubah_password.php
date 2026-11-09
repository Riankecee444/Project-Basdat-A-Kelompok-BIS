<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: ../index.php");
    exit;
}

require_once "../config/database.php";

$user_id = (int) $_SESSION["user_id"];
$pesan = "";
$jenisPesan = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $passwordLama = $_POST["password_lama"] ?? "";
    $passwordBaru = $_POST["password_baru"] ?? "";
    $konfirmasi = $_POST["konfirmasi_password"] ?? "";

    if ($passwordLama === "" || $passwordBaru === "" || $konfirmasi === "") {
        $pesan = "Semua kolom wajib diisi.";
        $jenisPesan = "error";

    } elseif (strlen($passwordBaru) < 8) {
        $pesan = "Password baru minimal 8 karakter.";
        $jenisPesan = "error";

    } elseif ($passwordBaru !== $konfirmasi) {
        $pesan = "Konfirmasi password tidak cocok.";
        $jenisPesan = "error";

    } else {

        $query = "SELECT password FROM users WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result);

        mysqli_stmt_close($stmt);

        if (!$user || !password_verify($passwordLama, $user["password"])) {

            $pesan = "Password lama tidak sesuai.";
            $jenisPesan = "error";

        } else {

            $passwordHash = password_hash($passwordBaru, PASSWORD_DEFAULT);

            $queryUpdate = "UPDATE users SET password = ? WHERE id = ?";
            $stmtUpdate = mysqli_prepare($conn, $queryUpdate);

            mysqli_stmt_bind_param(
                $stmtUpdate,
                "si",
                $passwordHash,
                $user_id
            );

            if (mysqli_stmt_execute($stmtUpdate)) {
                $pesan = "Password berhasil diperbarui!";
                $jenisPesan = "success";
            } else {
                $pesan = "Gagal memperbarui password.";
                $jenisPesan = "error";
            }

            mysqli_stmt_close($stmtUpdate);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ubah Kata Sandi - Bank Iman Syariah</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<div class="app-layout profile-container">

    <?php
    $activePage = "profile";
    include "../includes/sidebar.php";
    ?>

    <main class="main-content profile-content">

        <header class="profile-header">
            <h1>Ubah Kata Sandi</h1>
            <p>Perbarui kata sandi untuk menjaga keamanan akun Anda.</p>
        </header>

        <section class="password-form-card">

            <h2>Ganti Kata Sandi</h2>
            <p>Masukkan kata sandi lama dan kata sandi baru Anda.</p>

            <?php if ($pesan !== ""): ?>
                <div class="profile-alert <?= $jenisPesan === 'error' ? 'password-error' : ''; ?>">
                    <?= htmlspecialchars($pesan); ?>
                </div>
            <?php endif; ?>

            <form method="POST">

                <label for="password_lama">Kata sandi lama</label>
                <input
                    type="password"
                    id="password_lama"
                    name="password_lama"
                    autocomplete="current-password"
                    required
                >

                <label for="password_baru">Kata sandi baru</label>
                <input
                    type="password"
                    id="password_baru"
                    name="password_baru"
                    minlength="8"
                    autocomplete="new-password"
                    required
                >

                <label for="konfirmasi_password">Konfirmasi kata sandi baru</label>
                <input
                    type="password"
                    id="konfirmasi_password"
                    name="konfirmasi_password"
                    minlength="8"
                    autocomplete="new-password"
                    required
                >

                <button type="submit" class="profile-save">
                    Simpan Kata Sandi Baru
                </button>

                <a href="profile.php" class="password-back">
                    Kembali ke Profil
                </a>

            </form>

        </section>

    </main>

</div>

</body>
</html>