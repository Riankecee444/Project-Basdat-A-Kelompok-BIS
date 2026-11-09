<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: ../index.php");
    exit;
}

require_once "../config/database.php";

$nama = $_SESSION["nama"];
$user_id = $_SESSION["user_id"];

// TOTAL SALDO
$query = "SELECT SUM(saldo) AS total_saldo
          FROM accounts
          WHERE user_id = ? AND status = 'aktif'";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($result);
$saldo = $data["total_saldo"] ?? 0;
mysqli_stmt_close($stmt);

// DATA REKENING
$queryAkun = "SELECT * FROM accounts WHERE user_id = ? AND status = 'aktif'";
$stmtAkun = mysqli_prepare($conn, $queryAkun);
mysqli_stmt_bind_param($stmtAkun, "i", $user_id);
mysqli_stmt_execute($stmtAkun);
$resultAkun = mysqli_stmt_get_result($stmtAkun);

// TRANSAKSI TERBARU
$queryTransaksi = "SELECT
                    t.kode_transaksi,
                    t.keterangan,
                    t.jenis,
                    t.nominal,
                    t.tanggal
                   FROM transactions t
                   JOIN accounts a ON t.account_id = a.id
                   WHERE a.user_id = ?
                   ORDER BY t.tanggal DESC
                   LIMIT 5";

$stmtTransaksi = mysqli_prepare($conn, $queryTransaksi);
mysqli_stmt_bind_param($stmtTransaksi, "i", $user_id);
mysqli_stmt_execute($stmtTransaksi);
$resultTransaksi = mysqli_stmt_get_result($stmtTransaksi);

$activePage = "dashboard";
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Bank Iman Syariah</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body class="dashboard-page">

<div class="dashboard-container">

    <?php include "../includes/sidebar.php"; ?>

    <div class="dashboard">

        <main class="dashboard-content">

            <header class="figma-header">
                <div>
                    <h1>Assalamu'alaikum, <?= htmlspecialchars($nama); ?></h1>
                    <p>Selamat datang di Bank Iman Syariah.</p>
                </div>

                <a href="accounts.php" class="transfer-button">
                    Transfer
                </a>
            </header>

            <section class="summary-grid">

                <div class="summary-card saldo-card">
                    <span>Total saldo</span>
                    <h2>Rp <?= number_format($saldo, 0, ',', '.'); ?></h2>
                    <p><?= mysqli_num_rows($resultAkun); ?> rekening aktif</p>
                </div>

                <div class="summary-card">
                    <span>Pembiayaan aktif</span>
                    <h2>Rp 0</h2>
                    <p>Belum ada data pembiayaan</p>
                </div>

                <div class="summary-card">
                    <span>Zakat tahun 2026</span>
                    <h2>Rp 0</h2>
                    <p>Status: Belum ada data</p>
                </div>

            </section>

            <section class="dashboard-grid">

                <div class="dashboard-left">

                    <div class="content-card">
                        <h2>Rekening saya</h2>
                        <p class="card-description">
                            Jenis akad dan saldo rekening Anda.
                        </p>

                        <?php if (mysqli_num_rows($resultAkun) > 0): ?>

                            <?php while ($akun = mysqli_fetch_assoc($resultAkun)): ?>

                                <div class="account-item">
                                    <div>
                                        <h3>
                                            Tabungan iB <?= htmlspecialchars($akun["jenis_akad"]); ?>
                                        </h3>

                                        <p>
                                            No. <?= htmlspecialchars($akun["nomor_rekening"]); ?>
                                        </p>
                                    </div>

                                    <div class="account-right">
                                        <span><?= htmlspecialchars($akun["jenis_akad"]); ?></span>
                                        <strong>
                                            Rp <?= number_format($akun["saldo"], 0, ',', '.'); ?>
                                        </strong>
                                    </div>
                                </div>

                            <?php endwhile; ?>

                        <?php else: ?>

                            <p>Belum memiliki rekening aktif.</p>

                        <?php endif; ?>

                    </div>

                    <div class="content-card">
                        <h2>Transaksi terakhir</h2>
                        <p class="card-description">
                            Mutasi dari seluruh rekening Anda.
                        </p>

                        <div class="transaction-table">

                            <div class="transaction-heading">
                                <span>Tanggal</span>
                                <span>Keterangan</span>
                                <span>Jenis</span>
                                <span>Nominal</span>
                            </div>

                            <?php if (mysqli_num_rows($resultTransaksi) > 0): ?>

                                <?php while ($t = mysqli_fetch_assoc($resultTransaksi)): ?>

                                    <div class="transaction-row">

                                        <span>
                                            <?= date("d M Y", strtotime($t["tanggal"])); ?>
                                        </span>

                                        <strong>
                                            <?= htmlspecialchars($t["keterangan"]); ?>
                                        </strong>

                                        <span>
                                            <?= htmlspecialchars($t["jenis"]); ?>
                                        </span>

                                        <strong class="<?= $t["jenis"] === "masuk" ? "masuk" : "keluar"; ?>">
                                            <?= $t["jenis"] === "masuk" ? "+" : "-"; ?>
                                            Rp <?= number_format($t["nominal"], 0, ',', '.'); ?>
                                        </strong>

                                    </div>

                                <?php endwhile; ?>

                            <?php else: ?>

                                <p>Belum ada transaksi.</p>

                            <?php endif; ?>

                        </div>
                    </div>

                </div>

                <div class="dashboard-right">

                    <div class="content-card">
                        <div class="card-title">
                            <h2>Pembiayaan</h2>
                            <span class="status-badge">Aktif</span>
                        </div>

                        <h3>Informasi pembiayaan</h3>
                        <p>Silakan buka menu Pembiayaan untuk melihat detail.</p>
                        <a href="financing.php" class="card-button">
                            Lihat pembiayaan
                        </a>
                    </div>

                    <div class="content-card">
                        <div class="card-title">
                            <h2>Zakat</h2>
                            <span class="status-badge">Informasi</span>
                        </div>

                        <p>Zakat dan pembayaran Anda.</p>

                        <a href="zakat.php" class="card-button">
                            Bayar zakat
                        </a>
                    </div>

                </div>

            </section>

        </main>

    </div>

</div>

</body>
</html>