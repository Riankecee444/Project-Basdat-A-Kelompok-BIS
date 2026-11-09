<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: ../index.php");
    exit;
}

require_once "../config/database.php";

$user_id = (int) $_SESSION["user_id"];
$nama = $_SESSION["nama"] ?? "Pengguna";
$activePage = "zakat";

$pesan = $_SESSION["pesan_zakat"] ?? "";
unset($_SESSION["pesan_zakat"]);

// Proses pembayaran zakat
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $penghasilan = (float) ($_POST["penghasilan"] ?? 0);
    $aksi = $_POST["aksi"] ?? "";

    if ($penghasilan <= 0 || $penghasilan > 1000000000000) {
        $_SESSION["pesan_zakat"] = "Masukkan nominal penghasilan yang valid.";
        header("Location: zakat.php");
        exit;
    }

    if ($aksi === "bayar") {

    $jenis_zakat = $_POST["jenis_zakat"] ?? "";

    $jenisValid = [
        "Zakat Penghasilan",
        "Zakat Maal",
        "Zakat Fitrah"
    ];

    if (!in_array($jenis_zakat, $jenisValid, true)) {
        $_SESSION["pesan_zakat"] = "Jenis zakat tidak valid.";
        header("Location: zakat.php");
        exit;
    }

    $hasilZakat = round($penghasilan * 0.025);

    $queryBayar = "INSERT INTO zakat_payments
        (user_id, jenis_zakat, nominal, status, tanggal)
        VALUES (?, ?, ?, 'Berhasil', NOW())";

    $stmtBayar = mysqli_prepare($conn, $queryBayar);

    mysqli_stmt_bind_param(
        $stmtBayar,
        "isd",
        $user_id,
        $jenis_zakat,
        $hasilZakat
    );

    if (mysqli_stmt_execute($stmtBayar)) {
        $_SESSION["pesan_zakat"] = "Pembayaran zakat berhasil disimpan!";
    } else {
        $_SESSION["pesan_zakat"] = "Gagal menyimpan pembayaran zakat.";
    }

    mysqli_stmt_close($stmtBayar);

    header("Location: zakat.php");
    exit;
}
}

// Ringkasan pembayaran tahun berjalan
$queryRingkasan = "SELECT
    COALESCE(SUM(nominal), 0) AS total_zakat,
    COUNT(*) AS jumlah_pembayaran
    FROM zakat_payments
    WHERE user_id = ?
    AND status = 'Berhasil'
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
$queryTerakhir = "SELECT tanggal, jenis_zakat
    FROM zakat_payments
    WHERE user_id = ? AND status = 'Berhasil'
    ORDER BY tanggal DESC, id DESC
    LIMIT 1";

$stmt = mysqli_prepare($conn, $queryTerakhir);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);

$resultTerakhir = mysqli_stmt_get_result($stmt);
$dataTerakhir = mysqli_fetch_assoc($resultTerakhir);

$bulanIndonesia = [
    1 => "Jan", 2 => "Feb", 3 => "Mar",
    4 => "Apr", 5 => "Mei", 6 => "Jun",
    7 => "Jul", 8 => "Agu", 9 => "Sep",
    10 => "Okt", 11 => "Nov", 12 => "Des"
];

$tanggalTerakhir = "Belum ada pembayaran";
$jenisTerakhir = "-";

if ($dataTerakhir) {
    $waktu = strtotime($dataTerakhir["tanggal"]);
    $tanggalTerakhir = date("d", $waktu) . " " .
        $bulanIndonesia[(int)date("n", $waktu)] . " " .
        date("Y", $waktu);

    $jenisTerakhir = $dataTerakhir["jenis_zakat"];
}

mysqli_stmt_close($stmt);

// Riwayat zakat
$queryRiwayat = "SELECT id, jenis_zakat, nominal, status, tanggal
    FROM zakat_payments
    WHERE user_id = ?
    ORDER BY tanggal DESC, id DESC";

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

<div class="app-layout zakat-container">

    <?php include "../includes/sidebar.php"; ?>

    <main class="main-content zakat-content">

        <header class="zakat-header">
            <div>
                <h1>Zakat</h1>
                <p>Hitung, tunaikan, dan lacak zakat Anda.</p>
            </div>
        </header>

        <?php if ($pesan !== ""): ?>
            <div class="zakat-alert">
                <?= htmlspecialchars($pesan); ?>
            </div>
        <?php endif; ?>


        <!-- RINGKASAN ZAKAT -->
        <section class="zakat-summary">

            <div class="zakat-summary-card zakat-total">
                <p>Total zakat <?= date("Y"); ?></p>
                <h2>Rp <?= number_format($totalZakat, 0, ',', '.'); ?></h2>
                <span>Seluruh jenis zakat</span>
            </div>

            <div class="zakat-summary-card">
                <p>Jumlah pembayaran</p>
                <h2><?= (int)$jumlahPembayaran; ?> kali</h2>
                <span>Sepanjang tahun <?= date("Y"); ?></span>
            </div>

            <div class="zakat-summary-card">
                <p>Pembayaran terakhir</p>
                <h2><?= htmlspecialchars($tanggalTerakhir); ?></h2>
                <span><?= htmlspecialchars($jenisTerakhir); ?></span>
            </div>

        </section>


        <!-- RIWAYAT DAN KALKULATOR -->
        <div class="zakat-columns">

            <section class="zakat-history">

                <h2>Riwayat zakat</h2>
                <p>Pembayaran zakat yang sudah Anda tunaikan.</p>

                <div class="table-wrapper">

                    <table>
                        <thead>
                            <tr>
                                <th>Tanggal bayar</th>
                                <th>ID zakat</th>
                                <th>Jenis zakat</th>
                                <th>Nominal</th>
                                <th>Status</th>
                            </tr>
                        </thead>

                        <tbody>

                        <?php if (mysqli_num_rows($resultRiwayat) > 0): ?>

                            <?php while ($data = mysqli_fetch_assoc($resultRiwayat)): ?>

                                <?php
                                $waktu = strtotime($data["tanggal"]);
                                $tanggal = date("d", $waktu) . " " .
                                    $bulanIndonesia[(int)date("n", $waktu)] . " " .
                                    date("Y", $waktu);
                                ?>

                                <tr>
                                    <td><?= htmlspecialchars($tanggal); ?></td>

                                    <td>
                                        ZKT-<?= str_pad($data["id"], 4, "0", STR_PAD_LEFT); ?>
                                    </td>

                                    <td><?= htmlspecialchars($data["jenis_zakat"]); ?></td>

                                    <td>
                                        Rp <?= number_format($data["nominal"], 0, ',', '.'); ?>
                                    </td>

                                    <td>
                                        <span class="zakat-status">
                                            <?= htmlspecialchars($data["status"]); ?>
                                        </span>
                                    </td>
                                </tr>

                            <?php endwhile; ?>

                        <?php else: ?>

                            <tr>
                                <td colspan="5" class="zakat-empty">
                                    Belum ada riwayat pembayaran zakat.
                                </td>
                            </tr>

                        <?php endif; ?>

                        </tbody>
                    </table>

                </div>

            </section>


            <!-- KALKULATOR ZAKAT -->
            <section class="zakat-calculator">

                <h2>Kalkulator zakat</h2>
                <p>Hitung zakat penghasilan bulanan.</p>

                <form method="POST" action="zakat.php">

                <label for="jenis_zakat">Jenis zakat</label>

<select name="jenis_zakat" id="jenis_zakat" required>
    <option value="Zakat Penghasilan">Zakat Penghasilan</option>
    <option value="Zakat Maal">Zakat Maal</option>
    <option value="Zakat Fitrah">Zakat Fitrah</option>
</select>

                    <label for="penghasilan">Penghasilan per bulan</label>

                    <input
                        type="number"
                        id="penghasilan"
                        name="penghasilan"
                        min="1"
                        max="1000000000000"
                        step="1"
                        placeholder="Contoh: 8500000"
                        required
                    >

                    <div class="zakat-result">
                        <span>Zakat yang harus dibayar</span>
                        <h2 id="hasil-zakat">Rp 0</h2>
                        <small>2,5% dari penghasilan</small>
                    </div>

                    <button
                        type="submit"
                        name="aksi"
                        value="bayar"
                        class="btn-bayar-zakat"
                    >
                        Bayar zakat
                    </button>

                </form>

                <small class="zakat-disclaimer">
                    Kalkulator ini merupakan simulasi. Kewajiban zakat penghasilan
                    juga perlu memperhatikan ketentuan nisab.
                </small>

            </section>

        </div>

    </main>

</div>


<script>
const inputPenghasilan = document.getElementById("penghasilan");
const hasilZakat = document.getElementById("hasil-zakat");

function hitungZakat() {
    const penghasilan = Number(inputPenghasilan.value);
    const zakat = Math.round(penghasilan * 0.025);

    hasilZakat.textContent = "Rp " +
        zakat.toLocaleString("id-ID");
}

inputPenghasilan.addEventListener("input", hitungZakat);
</script>

</body>
</html>

<?php
mysqli_stmt_close($stmt);
?>