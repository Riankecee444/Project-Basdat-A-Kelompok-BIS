<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: ../index.php");
    exit;
}

require_once "../config/database.php";

$user_id = $_SESSION["user_id"];
$activePage = "accounts";

// Mengambil rekening milik pengguna
$query = "SELECT id, nomor_rekening, jenis_akad, saldo, status
          FROM accounts
          WHERE user_id = ?
          ORDER BY id ASC";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$rekening = [];

while ($row = mysqli_fetch_assoc($result)) {
    $rekening[] = $row;
}

mysqli_stmt_close($stmt);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekening - Bank Iman Syariah</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<div class="app-layout">

    <?php include "../includes/sidebar.php"; ?>

    <main class="main-content">

        <header class="page-header">
            <div>
                <h1>Rekening</h1>
                <p>Kelola rekening tabungan syariah Anda.</p>
            </div>

            <button class="btn-primary" type="button">
                + Buka rekening
            </button>
        </header>

        <section class="accounts-section">
            <h2>Rekening saya</h2>
            <p>Jenis akad dan saldo rekening Anda.</p>

            <div class="account-grid">

                <?php if (count($rekening) > 0): ?>

                    <?php foreach ($rekening as $r): ?>

                    <div class="account-card">

                        <div class="account-top">
                            <h3>
                                Tabungan iB <?php echo htmlspecialchars($r["jenis_akad"]); ?>
                            </h3>

                            <span class="akad-badge">
                                <?php echo htmlspecialchars($r["jenis_akad"]); ?>
                            </span>
                        </div>

                        <p>Saldo</p>

                        <h2>
                            Rp <?php echo number_format($r["saldo"], 0, ',', '.'); ?>
                        </h2>

                        <div class="account-detail">

                            <div>
                                <small>No. rekening</small>
                                <strong>
                                    <?php echo htmlspecialchars($r["nomor_rekening"]); ?>
                                </strong>
                            </div>

                            <div>
                                <small>Jenis akad</small>
                                <strong>
                                    <?php echo htmlspecialchars($r["jenis_akad"]); ?>
                                </strong>
                            </div>

                            <div>
                                <small>ID rekening</small>
                                <strong>
                                    <?php echo htmlspecialchars($r["id"]); ?>
                                </strong>
                            </div>

                            <div>
                                <small>Status</small>
                                <strong class="status-active">
                                    <?php echo htmlspecialchars($r["status"]); ?>
                                </strong>
                            </div>

                        </div>

                        <div class="account-actions">
                            <a href="transactions.php">Lihat mutasi</a>
                            <a href="transactions.php">Lihat transaksi</a>
                        </div>

                    </div>

                    <?php endforeach; ?>

                <?php else: ?>

                    <p>Anda belum memiliki rekening.</p>

                <?php endif; ?>

            </div>
        </section>

        <section class="akad-section">
            <h2>Mengenal akad rekening</h2>
            <p>Perbedaan akad pada tabungan syariah.</p>

            <div class="akad-grid">

                <div class="akad-info">
                    <h3>Wadiah</h3>
                    <p>
                        Titipan dana yang aman dan dapat diambil
                        kapan saja. Bank boleh memberikan bonus
                        secara sukarela, tanpa dijanjikan di awal.
                    </p>
                </div>

                <div class="akad-info">
                    <h3>Mudharabah</h3>
                    <p>
                        Kerja sama pengelolaan dana. Keuntungan
                        dibagi sesuai nisbah yang disepakati
                        di awal.
                    </p>
                </div>

            </div>
        </section>

    </main>
</div>

</body>
</html>