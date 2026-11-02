<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: ../index.php");
    exit;
}

require_once "../config/database.php";

$user_id = $_SESSION["user_id"];
$nama = $_SESSION["nama"];

$hasilZakat = null;
$pesan = "";

if (isset($_SESSION["pesan_zakat"])) {
    $pesan = $_SESSION["pesan_zakat"];
    unset($_SESSION["pesan_zakat"]);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $penghasilan = (int) ($_POST["penghasilan"] ?? 0);
    $hasilZakat = $penghasilan * 0.025;

    if ($penghasilan > 0 && ($_POST["aksi"] ?? "") === "bayar") {

        $queryBayar = "INSERT INTO zakat_payments
                       (user_id, jenis_zakat, nominal, status, tanggal)
                       VALUES (?, 'Zakat Penghasilan', ?, 'Berhasil', NOW())";

        $stmtBayar = mysqli_prepare($conn, $queryBayar);

        mysqli_stmt_bind_param(
            $stmtBayar,
            "ii",
            $user_id,
            $hasilZakat
        );

        if (mysqli_stmt_execute($stmtBayar)) {
            $_SESSION["pesan_zakat"] = "Pembayaran zakat berhasil disimpan!";
            mysqli_stmt_close($stmtBayar);

            header("Location: zakat.php");
            exit;
        } else {
            $pesan = "Gagal menyimpan pembayaran zakat.";
        }

        mysqli_stmt_close($stmtBayar);
    }
}

// Ringkasan zakat
$queryRingkasan = "SELECT
                    COALESCE(SUM(CASE
                        WHEN status = 'Berhasil' THEN nominal
                        ELSE 0
                    END), 0) AS total_zakat,
                    SUM(CASE
                        WHEN status = 'Berhasil' THEN 1
                        ELSE 0
                    END) AS jumlah_pembayaran
                   FROM zakat_payments
                   WHERE user_id = ?
                   AND YEAR(tanggal) = YEAR(CURDATE())";

$stmt = mysqli_prepare($conn, $queryRingkasan);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);

$resultRingkasan = mysqli_stmt_get_result($stmt);
$ringkasan = mysqli_fetch_assoc($resultRingkasan);

$totalZakat = $ringkasan["total_zakat"] ?? 0;
$jumlahPembayaran = $ringkasan["jumlah_pembayaran"] ?? 0;

mysqli_stmt_close($stmt);

// Pembayaran terakhir
$queryTerakhir = "SELECT tanggal
                  FROM zakat_payments
                  WHERE user_id = ?
                  AND status = 'Berhasil'
                  ORDER BY tanggal DESC
                  LIMIT 1";

$stmt = mysqli_prepare($conn, $queryTerakhir);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);

$resultTerakhir = mysqli_stmt_get_result($stmt);
$dataTerakhir = mysqli_fetch_assoc($resultTerakhir);

$tanggalTerakhir = $dataTerakhir
    ? date("d F Y", strtotime($dataTerakhir["tanggal"]))
    : "Belum ada pembayaran";

mysqli_stmt_close($stmt);

// Riwayat zakat
$queryRiwayat = "SELECT jenis_zakat, nominal, status, tanggal
                 FROM zakat_payments
                 WHERE user_id = ?
                 ORDER BY tanggal DESC";

$stmt = mysqli_prepare($conn, $queryRiwayat);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);

$resultRiwayat = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zakat - Bank Iman Syariah</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<div class="zakat-container">

    <aside class="zakat-sidebar">
        <h2>Bank Iman Syariah</h2>
        <p class="sidebar-label">MENU UTAMA</p>

        <a href="dashboard.php">Dashboard</a>
        <a href="accounts.php">Rekening</a>
        <a href="transactions.php">Transaksi</a>
        <a href="financing.php">Pembiayaan</a>
        <a href="zakat.php" class="active">Zakat</a>
        <a href="profile.php">Profil</a>
        <a href="../index.php">Keluar</a>
    </aside>

    <main class="zakat-content">

        <header class="zakat-header">
            <div>
                <h1>Zakat</h1>
                <p>Hitung dan lihat riwayat pembayaran zakat Anda.</p>
            </div>

            <strong>
                <?= htmlspecialchars($nama) ?>
            </strong>
        </header>

        <section class="zakat-summary">

            <div>
                <p>Total Zakat Tahun <?= date("Y") ?></p>
                <h1>
                    Rp<?= number_format($totalZakat, 0, ',', '.') ?>
                </h1>
                <span>Data dari database</span>
            </div>

            <div>
                <p>Jumlah Pembayaran</p>
                <h2><?= $jumlahPembayaran ?> Kali</h2>
            </div>

            <div>
                <p>Pembayaran Terakhir</p>
                <h2><?= htmlspecialchars($tanggalTerakhir) ?></h2>
            </div>

        </section>

        <section class="zakat-calculator">

    <h2>Kalkulator Zakat Penghasilan</h2>
    <p>Masukkan penghasilan bulanan untuk menghitung estimasi 2,5%.</p>

    <form method="POST" action="">

        <label for="penghasilan">
            Penghasilan Bulanan (Rp)
        </label>

        <input
            type="number"
            id="penghasilan"
            name="penghasilan"
            min="1"
            placeholder="Contoh: 8500000"
            value="<?= isset($penghasilan) ? $penghasilan : '' ?>"
            required
        >

        <button type="submit" name="aksi" value="hitung">
            Hitung Zakat
        </button>

    </form>

    <?php if ($pesan !== ""): ?>
        <p><?= htmlspecialchars($pesan) ?></p>
    <?php endif; ?>

    <?php if ($hasilZakat !== null && $hasilZakat > 0): ?>

        <div class="zakat-result">

            <p>Estimasi zakat 2,5%:</p>

            <h2>
                Rp<?= number_format($hasilZakat, 0, ',', '.') ?>
            </h2>

            <form method="POST" action="">

                <input
                    type="hidden"
                    name="penghasilan"
                    value="<?= $penghasilan ?>"
                >

                <button type="submit" name="aksi" value="bayar">
                    Bayar Zakat
                </button>

            </form>

        </div>

    <?php endif; ?>

</section>

        <section class="zakat-history">

            <h2>Riwayat Pembayaran Zakat</h2>

            <div class="table-wrapper">

                <table>
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Jenis Zakat</th>
                            <th>Jumlah</th>
                            <th>Status</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php if (mysqli_num_rows($resultRiwayat) > 0): ?>

                            <?php while ($data = mysqli_fetch_assoc($resultRiwayat)): ?>

                                <tr>
                                    <td>
                                        <?= date("d M Y", strtotime($data["tanggal"])) ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($data["jenis_zakat"]) ?>
                                    </td>

                                    <td>
                                        Rp<?= number_format($data["nominal"], 0, ',', '.') ?>
                                    </td>

                                    <td>
                                        <span class="zakat-status">
                                            <?= htmlspecialchars($data["status"]) ?>
                                        </span>
                                    </td>
                                </tr>

                            <?php endwhile; ?>

                        <?php else: ?>

                            <tr>
                                <td colspan="4" style="text-align: center;">
                                    Belum ada riwayat pembayaran zakat.
                                </td>
                            </tr>

                        <?php endif; ?>

                    </tbody>
                </table>

            </div>

        </section>

    </main>

</div>

</body>
</html>

<?php
mysqli_stmt_close($stmt);
?>