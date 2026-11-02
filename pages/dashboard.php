<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: ../index.php");
    exit;
}

require_once "../config/database.php";

$nama = $_SESSION["nama"];
$user_id = $_SESSION["user_id"];

// Mengambil total saldo rekening aktif milik pengguna
$query = "SELECT SUM(saldo) AS total_saldo
          FROM accounts
          WHERE user_id = ? AND status = 'aktif'";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($result);

$saldo = $data["total_saldo"] ?? 0;

// Mengambil 3 transaksi terbaru
$queryTransaksi = "SELECT
                    t.kode_transaksi,
                    t.keterangan,
                    t.jenis,
                    t.nominal
                   FROM transactions t
                   JOIN accounts a ON t.account_id = a.id
                   WHERE a.user_id = ?
                   ORDER BY t.tanggal DESC
                   LIMIT 3";

$stmtTransaksi = mysqli_prepare($conn, $queryTransaksi);
mysqli_stmt_bind_param($stmtTransaksi, "i", $user_id);
mysqli_stmt_execute($stmtTransaksi);

$resultTransaksi = mysqli_stmt_get_result($stmtTransaksi);

mysqli_stmt_close($stmt);

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
<body>

<div class="dashboard-container">

    <?php include "../includes/sidebar.php"; ?>

    <div class="dashboard">

        <header class="dashboard-header">
            <h2>Bank Iman Syariah</h2>
            <div>
                Assalamu'alaikum,
                <?php echo htmlspecialchars($nama); ?>
            </div>
        </header>

        <main class="dashboard-content">
            <h1>Dashboard</h1>
            <p>Selamat datang di layanan M-Banking Bank Iman Syariah.</p>

            <section class="balance-card">
                <p>Total Saldo Anda</p>
                <h2>Rp <?php echo number_format($saldo, 0, ',', '.'); ?></h2>
                <span>Rekening aktif</span>
            </section>

            <section class="menu-grid">
                <a href="accounts.php">Akun</a>
                <a href="transactions.php">Transaksi</a>
                <a href="financing.php">Pembiayaan</a>
                <a href="zakat.php">Zakat</a>
                <a href="profile.php">Profil</a>
            </section>

           <section class="recent-transactions">
    <h2>Transaksi Terbaru</h2>

    <?php if (mysqli_num_rows($resultTransaksi) > 0): ?>

        <?php while ($t = mysqli_fetch_assoc($resultTransaksi)): ?>

            <div class="transaction-item">
                <span>
                    <?php echo htmlspecialchars($t["keterangan"]); ?>
                </span>

                <strong class="<?php echo $t["jenis"] == "masuk" ? "masuk" : "keluar"; ?>">
                    <?php echo $t["jenis"] == "masuk" ? "+ " : "- "; ?>
                    Rp <?php echo number_format($t["nominal"], 0, ',', '.'); ?>
                </strong>
            </div>

        <?php endwhile; ?>

    <?php else: ?>

        <p>Belum ada transaksi.</p>

    <?php endif; ?>
</section>

<?php mysqli_stmt_close($stmtTransaksi); ?>
        </main>

    </div>
</div>

</body>
</html>