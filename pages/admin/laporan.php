
<?php
session_start();

require_once __DIR__ . "/../../config/database.php";

// Proteksi halaman admin
if (
    !isset($_SESSION["user_id"]) ||
    ($_SESSION["role"] ?? "") !== "admin"
) {
    header("Location: ../../index.php");
    exit;
}

$activePage = "laporan";

$namaAdmin = $_SESSION["nama"] ?? "Administrator";

// Filter tanggal
$tanggalAwal = $_GET["awal"] ?? "";
$tanggalAkhir = $_GET["akhir"] ?? "";

// Validasi format tanggal
$validTanggal = function ($tanggal) {
    $date = DateTime::createFromFormat("!Y-m-d", $tanggal);

    return $date && $date->format("Y-m-d") === $tanggal;
};

if (
    ($tanggalAwal !== "" && !$validTanggal($tanggalAwal)) ||
    ($tanggalAkhir !== "" && !$validTanggal($tanggalAkhir)) ||
    ($tanggalAwal !== "" && $tanggalAkhir !== "" && $tanggalAwal > $tanggalAkhir)
) {
    $tanggalAwal = "";
    $tanggalAkhir = "";
}

// Kondisi filter SQL
$where = [];
$params = [];
$types = "";

if ($tanggalAwal !== "") {
    $where[] = "DATE(t.tanggal) >= ?";
    $params[] = $tanggalAwal;
    $types .= "s";
}

if ($tanggalAkhir !== "") {
    $where[] = "DATE(t.tanggal) <= ?";
    $params[] = $tanggalAkhir;
    $types .= "s";
}

$filterSql = count($where) > 0
    ? " WHERE " . implode(" AND ", $where)
    : "";

// Statistik laporan
$queryStatistik = "
    SELECT
        COUNT(*) AS total,
        COALESCE(SUM(
            CASE WHEN t.jenis = 'masuk' THEN t.nominal ELSE 0 END
        ), 0) AS masuk,
        COALESCE(SUM(
            CASE WHEN t.jenis = 'keluar' THEN t.nominal ELSE 0 END
        ), 0) AS keluar
    FROM transactions t
    " . $filterSql;

$stmtStatistik = mysqli_prepare($conn, $queryStatistik);

if (!$stmtStatistik) {
    die("Gagal menyiapkan statistik: " . mysqli_error($conn));
}

if ($types !== "") {
    mysqli_stmt_bind_param($stmtStatistik, $types, ...$params);
}

mysqli_stmt_execute($stmtStatistik);

$statistik = mysqli_fetch_assoc(
    mysqli_stmt_get_result($stmtStatistik)
);

mysqli_stmt_close($stmtStatistik);

// Data detail laporan
$queryLaporan = "
    SELECT
        t.kode_transaksi,
        t.keterangan,
        t.jenis,
        t.nominal,
        t.tanggal,
        u.nama,
        a.nomor_rekening
    FROM transactions t
    INNER JOIN accounts a ON t.account_id = a.id
    INNER JOIN users u ON a.user_id = u.id
    " . $filterSql . "
    ORDER BY t.tanggal DESC, t.id DESC
";

$stmt = mysqli_prepare($conn, $queryLaporan);

if (!$stmt) {
    die("Gagal menyiapkan laporan: " . mysqli_error($conn));
}

if ($types !== "") {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Nilai statistik
$totalTransaksi = (int) ($statistik["total"] ?? 0);
$totalMasuk = (float) ($statistik["masuk"] ?? 0);
$totalKeluar = (float) ($statistik["keluar"] ?? 0);

// Waktu cetak
$waktuCetak = date("d-m-Y H:i:s");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Laporan Keuangan - Bank Iman Syariah</title>

    <link rel="stylesheet" href="../../assets/css/admin.css">
</head>

<body>

<div class="admin-layout">

    <?php include __DIR__ . "/../../includes/admin_sidebar.php"; ?>

    <main class="main-content laporan-content">

        <!-- Header -->
        <div class="laporan-page-header">
            <div>
                <h1>Laporan Keuangan</h1>
                <p>Ringkasan aktivitas keuangan Bank Iman Syariah.</p>
            </div>
        </div>

        <!-- Filter tanggal -->
        <section class="laporan-filter">

            <form method="GET">

                <div class="laporan-filter-field">
                    <label for="awal">Dari Tanggal</label>

                    <input
                        type="date"
                        id="awal"
                        name="awal"
                        value="<?= htmlspecialchars($tanggalAwal); ?>"
                    >
                </div>

                <div class="laporan-filter-field">
                    <label for="akhir">Sampai Tanggal</label>

                    <input
                        type="date"
                        id="akhir"
                        name="akhir"
                        value="<?= htmlspecialchars($tanggalAkhir); ?>"
                    >
                </div>

                <button type="submit" class="laporan-filter-button">
                    Tampilkan
                </button>

                <a href="laporan.php" class="laporan-reset-button">
                    Reset
                </a>

            </form>

        </section>

        <!-- Statistik -->
        <section class="laporan-stats">

            <div class="laporan-stat-card">
                <div class="laporan-stat-icon">T</div>

                <div>
                    <p>Total Transaksi</p>
                    <h2>
                        <?= number_format($totalTransaksi, 0, ",", "."); ?>
                    </h2>
                    <span>Transaksi berdasarkan periode</span>
                </div>
            </div>

            <div class="laporan-stat-card">
                <div class="laporan-stat-icon">+</div>

                <div>
                    <p>Total Uang Masuk</p>
                    <h2>
                        Rp <?= number_format($totalMasuk, 0, ",", "."); ?>
                    </h2>
                    <span>Akumulasi uang masuk</span>
                </div>
            </div>

            <div class="laporan-stat-card">
                <div class="laporan-stat-icon">−</div>

                <div>
                    <p>Total Uang Keluar</p>
                    <h2>
                        Rp <?= number_format($totalKeluar, 0, ",", "."); ?>
                    </h2>
                    <span>Akumulasi uang keluar</span>
                </div>
            </div>

        </section>

        <!-- Detail laporan -->
        <section class="laporan-panel">

            <div class="laporan-panel-header">

                <div>
                    <h3>Detail Laporan Transaksi</h3>
                    <p>Riwayat transaksi berdasarkan periode yang dipilih.</p>
                </div>

                <button
                    type="button"
                    class="laporan-print-button"
                    onclick="window.print()"
                >
                    Cetak Laporan
                </button>

            </div>

            <!-- Bagian khusus cetak -->
            <div class="laporan-print-heading">
                <h2>BANK IMAN SYARIAH</h2>
                <h3>LAPORAN KEUANGAN</h3>

                <p>
                    Periode:
                    <?= $tanggalAwal !== ""
                        ? date("d-m-Y", strtotime($tanggalAwal))
                        : "Semua tanggal"; ?>
                    sampai
                    <?= $tanggalAkhir !== ""
                        ? date("d-m-Y", strtotime($tanggalAkhir))
                        : "Semua tanggal"; ?>
                </p>

                <p>Dicetak pada: <?= $waktuCetak; ?></p>
            </div>

            <div class="laporan-table-wrapper">

                <table class="laporan-table">

                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Kode Transaksi</th>
                            <th>Nama Nasabah</th>
                            <th>Nomor Rekening</th>
                            <th>Keterangan</th>
                            <th>Jenis</th>
                            <th>Nominal</th>
                            <th>Tanggal</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php if ($result && mysqli_num_rows($result) > 0): ?>

                            <?php $no = 1; ?>

                            <?php while ($data = mysqli_fetch_assoc($result)): ?>

                                <tr>
                                    <td><?= $no++; ?></td>

                                    <td>
                                        <?= htmlspecialchars($data["kode_transaksi"]); ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($data["nama"]); ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($data["nomor_rekening"]); ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($data["keterangan"]); ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars(ucfirst($data["jenis"])); ?>
                                    </td>

                                    <td>
                                        Rp <?= number_format((float)$data["nominal"], 0, ",", "."); ?>
                                    </td>

                                    <td>
                                        <?= date(
                                            "d-m-Y H:i",
                                            strtotime($data["tanggal"])
                                        ); ?>
                                    </td>
                                </tr>

                            <?php endwhile; ?>

                        <?php else: ?>

                            <tr>
                                <td colspan="8" class="laporan-empty">
                                    Tidak ada transaksi pada periode ini.
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
