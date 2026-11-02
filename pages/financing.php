<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: ../index.php");
    exit;
}

require_once "../config/database.php";

$user_id = $_SESSION["user_id"];
$activePage = "financing";

// Mengambil data pembiayaan
$query = "SELECT * FROM financings WHERE user_id = ?";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$pembiayaan = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

$riwayat = [
    ["21 Okt 2026", "Cicilan ke-8", 2416667, "Lunas"],
    ["21 Sep 2026", "Cicilan ke-7", 2416667, "Lunas"],
    ["21 Agu 2026", "Cicilan ke-6", 2416667, "Lunas"],
    ["21 Jul 2026", "Cicilan ke-5", 2416667, "Lunas"],
    ["21 Jun 2026", "Cicilan ke-4", 2416667, "Lunas"],
];

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembiayaan - Bank Iman Syariah</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<div class="financing-container">

    <?php include "../includes/sidebar.php"; ?>

    <main class="financing-content">

        <header class="financing-header">
            <div>
                <h1>Pembiayaan Saya</h1>
                <p>Informasi pembiayaan syariah Anda</p>
            </div>
            <div class="user-label">
                <?php echo htmlspecialchars($_SESSION["nama"]); ?>
            </div>
        </header>

        <?php if ($pembiayaan): ?>

        <section class="financing-card">
            <div>
                <span class="financing-tag">
                    <?php echo htmlspecialchars($pembiayaan["jenis_akad"]); ?>
                </span>

                <h2>Pembiayaan <?php echo htmlspecialchars($pembiayaan["jenis_akad"]); ?></h2>

                <p>Nomor Pembiayaan: PBY-<?php echo str_pad($pembiayaan["id"], 4, "0", STR_PAD_LEFT); ?></p>
            </div>

            <div class="financing-status">
                <?php echo htmlspecialchars($pembiayaan["status"]); ?>
            </div>

            <div class="financing-amount">
                <p>Total Plafond Pembiayaan</p>
                <h1>Rp<?php echo number_format($pembiayaan["plafon"], 0, ',', '.'); ?></h1>
            </div>
        </section>

        <section class="financing-summary">
            <div class="financing-box">
                <p>Margin</p>
                <h3><?php echo $pembiayaan["margin"]; ?>%</h3>
            </div>

            <div class="financing-box">
                <p>Tenor</p>
                <h3><?php echo $pembiayaan["tenor"]; ?> Bulan</h3>
            </div>

            <div class="financing-box">
                <p>Cicilan Per Bulan</p>
                <h3>Rp<?php echo number_format($pembiayaan["angsuran"], 0, ',', '.'); ?></h3>
            </div>
        </section>

        <section class="payment-progress">
            <div class="progress-heading">
                <h2>Progres Pembayaran</h2>
                <strong>8 dari <?php echo $pembiayaan["tenor"]; ?> bulan</strong>
            </div>

            <div class="progress-track">
                <div class="progress-fill"
                     style="width: <?php echo min(8 / max((int)$pembiayaan["tenor"], 1) * 100, 100); ?>%;">
                </div>
            </div>

            <p>Anda telah menyelesaikan 8 dari <?php echo $pembiayaan["tenor"]; ?> kali pembayaran.</p>
        </section>

        <section class="next-payment">
            <div>
                <h2>Cicilan Berikutnya</h2>
                <p>Jadwal jatuh tempo belum tersedia.</p>
            </div>

            <div class="next-payment-amount">
                <h2>Rp<?php echo number_format($pembiayaan["angsuran"], 0, ',', '.'); ?></h2>
                <span>Belum dibayar</span>
            </div>
        </section>

        <section class="payment-history">
            <h2>Riwayat Pembayaran</h2>
            <p>Riwayat berikut masih berupa data simulasi.</p>

            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Keterangan</th>
                            <th>Jumlah</th>
                            <th>Status</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($riwayat as $data): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($data[0]); ?></td>
                            <td><?php echo htmlspecialchars($data[1]); ?></td>
                            <td>Rp<?php echo number_format($data[2], 0, ',', '.'); ?></td>
                            <td>
                                <span class="payment-status">
                                    <?php echo htmlspecialchars($data[3]); ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <?php else: ?>

            <p>Anda belum memiliki data pembiayaan.</p>

        <?php endif; ?>

    </main>
</div>

</body>
</html>