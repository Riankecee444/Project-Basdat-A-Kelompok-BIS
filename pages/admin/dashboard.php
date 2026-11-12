
<?php
session_start();

require_once __DIR__ . "/../../config/database.php";

// Pemeriksaan hak akses admin
if (!isset($_SESSION["user_id"]) || ($_SESSION["role"] ?? "") !== "admin") {
    header("Location: ../../index.php");
    exit;
}

// Statistik nasabah
$totalNasabah = mysqli_fetch_assoc(mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM users WHERE role = 'nasabah'"
))["total"] ?? 0;

// Statistik rekening
$totalRekening = mysqli_fetch_assoc(mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM accounts"
))["total"] ?? 0;

// Total saldo
$totalSaldo = mysqli_fetch_assoc(mysqli_query(
    $conn,
    "SELECT COALESCE(SUM(saldo), 0) AS total FROM accounts"
))["total"] ?? 0;

// Total transaksi
$totalTransaksi = mysqli_fetch_assoc(mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM transactions"
))["total"] ?? 0;

$namaAdmin = $_SESSION["nama"] ?? "Administrator";
$activePage = "dashboard";
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Bank Iman Syariah</title>

    <link rel="stylesheet" href="../../assets/css/admin.css">
</head>

<body>

<div class="admin-layout">

    <?php include __DIR__ . "/../../includes/admin_sidebar.php"; ?>

    <main class="main-content">

        <header class="topbar">
            <div>
                <h1>Dashboard Admin</h1>
                <p>Selamat datang di panel pengelolaan Bank Iman Syariah.</p>
            </div>

            <span class="admin-badge">ADMIN</span>
        </header>

        <section class="welcome">
            <div>
                <h2>Assalamu'alaikum, <?= htmlspecialchars($namaAdmin); ?>!</h2>
                <p>Berikut ringkasan aktivitas Bank Iman Syariah.</p>
            </div>
        </section>

        <section class="stats-grid">

            <div class="stat-card">
                <span>Total Nasabah</span>
                <h2><?= number_format($totalNasabah, 0, ',', '.'); ?></h2>
                <small>Pengguna terdaftar</small>
            </div>

            <div class="stat-card">
                <span>Total Rekening</span>
                <h2><?= number_format($totalRekening, 0, ',', '.'); ?></h2>
                <small>Seluruh rekening</small>
            </div>

            <div class="stat-card">
                <span>Total Saldo</span>
                <h2>Rp <?= number_format($totalSaldo, 0, ',', '.'); ?></h2>
                <small>Akumulasi saldo rekening</small>
            </div>

            <div class="stat-card">
                <span>Total Transaksi</span>
                <h2><?= number_format($totalTransaksi, 0, ',', '.'); ?></h2>
                <small>Transaksi tercatat</small>
            </div>

        </section>

        <section class="information">
            <h2>Informasi Admin</h2>

            <p>
                Kamu sedang masuk menggunakan akun administrator.
                Gunakan menu di sebelah kiri untuk mengelola data bank.
            </p>
        </section>

    </main>

</div>

</body>
</html>
