<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: ../index.php");
    exit;
}

require_once "../config/database.php";

$user_id = $_SESSION["user_id"];
$activePage = "transactions";

// Mengambil transaksi berdasarkan rekening pengguna
$query = "SELECT
            t.id,
            t.kode_transaksi,
            t.keterangan,
            t.jenis,
            t.nominal,
            t.tanggal,
            a.nomor_rekening
          FROM transactions t
          JOIN accounts a ON t.account_id = a.id
          WHERE a.user_id = ?
          ORDER BY t.tanggal DESC";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$transaksi = [];
$totalMasuk = 0;
$totalKeluar = 0;

while ($row = mysqli_fetch_assoc($result)) {
    $transaksi[] = $row;

    if ($row["jenis"] == "masuk") {
        $totalMasuk += $row["nominal"];
    } elseif ($row["jenis"] == "keluar") {
        $totalKeluar += $row["nominal"];
    }
}

mysqli_stmt_close($stmt);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transactions - Bank Iman Syariah</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<div class="app-layout">

    <?php include "../includes/sidebar.php"; ?>

    <main class="main-content">

        <header class="page-header">
            <div>
                <h1>Transaksi</h1>
                <p>Riwayat mutasi seluruh rekening Anda.</p>
            </div>

            <button class="btn-primary" onclick="window.print()">
                Unduh / Cetak PDF
            </button>
        </header>

        <section class="transaction-summary">

            <div class="summary-card">
                <p>Total masuk</p>
                <h2>
                    Rp <?php echo number_format($totalMasuk, 0, ',', '.'); ?>
                </h2>
            </div>

            <div class="summary-card">
                <p>Total keluar</p>
                <h2>
                    Rp <?php echo number_format($totalKeluar, 0, ',', '.'); ?>
                </h2>
            </div>

            <div class="summary-card">
                <p>Jumlah transaksi</p>
                <h2><?php echo count($transaksi); ?> transaksi</h2>
            </div>

        </section>

        <section class="transaction-table-section">

            <h2>Riwayat transaksi</h2>

            <div class="table-responsive">
                <table class="transaction-table">

                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>ID Transaksi</th>
                            <th>No. Rekening</th>
                            <th>Keterangan</th>
                            <th>Jenis</th>
                            <th>Nominal</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php if (count($transaksi) > 0): ?>

                            <?php foreach ($transaksi as $t): ?>

                            <tr>
                                <td>
                                    <?php echo date("d M Y", strtotime($t["tanggal"])); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($t["kode_transaksi"]); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($t["nomor_rekening"]); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($t["keterangan"]); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($t["jenis"]); ?>
                                </td>

                                <td class="<?php echo $t["jenis"] == "masuk" ? "masuk" : "keluar"; ?>">
                                    <?php echo $t["jenis"] == "masuk" ? "+ " : "- "; ?>
                                    Rp <?php echo number_format($t["nominal"], 0, ',', '.'); ?>
                                </td>
                            </tr>

                            <?php endforeach; ?>

                        <?php else: ?>

                            <tr>
                                <td colspan="6">Belum ada transaksi.</td>
                            </tr>

                        <?php endif; ?>

                    </tbody>

                </table>
            </div>

            <p class="transaction-count">
                Menampilkan <?php echo count($transaksi); ?> transaksi.
            </p>

        </section>

    </main>
</div>

</body>
</html>