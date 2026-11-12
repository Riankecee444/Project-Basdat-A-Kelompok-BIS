
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

$activePage = "transaksi";

$namaAdmin = $_SESSION["nama"] ?? "Administrator";
$keyword = trim($_GET["q"] ?? "");

// Statistik transaksi
$queryStatistik = mysqli_query(
    $conn,
    "SELECT
        COUNT(*) AS total,
        COALESCE(SUM(CASE WHEN jenis = 'masuk' THEN nominal ELSE 0 END), 0) AS masuk,
        COALESCE(SUM(CASE WHEN jenis = 'keluar' THEN nominal ELSE 0 END), 0) AS keluar
     FROM transactions"
);

if (!$queryStatistik) {
    die("Gagal mengambil statistik: " . mysqli_error($conn));
}

$statistik = mysqli_fetch_assoc($queryStatistik);

$totalTransaksi = (int) ($statistik["total"] ?? 0);
$totalMasuk = (float) ($statistik["masuk"] ?? 0);
$totalKeluar = (float) ($statistik["keluar"] ?? 0);

// Query riwayat transaksi
$sql = "SELECT
            t.id,
            t.kode_transaksi,
            t.keterangan,
            t.jenis,
            t.nominal,
            t.tanggal,
            a.nomor_rekening,
            u.nama
        FROM transactions t
        INNER JOIN accounts a ON t.account_id = a.id
        INNER JOIN users u ON a.user_id = u.id";

if ($keyword !== "") {
    $sql .= " WHERE
              t.kode_transaksi LIKE ?
              OR u.nama LIKE ?
              OR a.nomor_rekening LIKE ?";
}

$sql .= " ORDER BY t.tanggal DESC, t.id DESC";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    die("Gagal menyiapkan query: " . mysqli_error($conn));
}

if ($keyword !== "") {
    $cari = "%" . $keyword . "%";
    mysqli_stmt_bind_param($stmt, "sss", $cari, $cari, $cari);
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Riwayat Transaksi - Bank Iman Syariah</title>

    <link rel="stylesheet" href="../../assets/css/admin.css">
</head>

<body>

<div class="admin-layout">

    <?php include __DIR__ . "/../../includes/admin_sidebar.php"; ?>

    <main class="main-content transaksi-content">

        <!-- Header -->
        <div class="transaksi-header">
            <div>
                <h1>Riwayat Transaksi</h1>
                <p>Pantau seluruh aktivitas transaksi Bank Iman Syariah.</p>
            </div>
        </div>

        <!-- Statistik -->
        <section class="transaksi-stats">

            <div class="transaksi-stat-card">
                <div class="transaksi-stat-icon">T</div>

                <div>
                    <p>Total Transaksi</p>
                    <h2>
                        <?= number_format($totalTransaksi, 0, ",", "."); ?>
                    </h2>
                    <span>Seluruh transaksi tercatat</span>
                </div>
            </div>

            <div class="transaksi-stat-card">
                <div class="transaksi-stat-icon">+</div>

                <div>
                    <p>Total Uang Masuk</p>
                    <h2>
                        Rp <?= number_format($totalMasuk, 0, ",", "."); ?>
                    </h2>
                    <span>Akumulasi transaksi masuk</span>
                </div>
            </div>

            <div class="transaksi-stat-card">
                <div class="transaksi-stat-icon">−</div>

                <div>
                    <p>Total Uang Keluar</p>
                    <h2>
                        Rp <?= number_format($totalKeluar, 0, ",", "."); ?>
                    </h2>
                    <span>Akumulasi transaksi keluar</span>
                </div>
            </div>

        </section>

        <!-- Tabel transaksi -->
        <section class="transaksi-panel">

            <div class="transaksi-panel-header">

                <div>
                    <h3>Daftar Transaksi</h3>
                    <p>Riwayat transaksi seluruh nasabah.</p>
                </div>

                <form method="GET" class="transaksi-search">

                    <input
                        type="text"
                        name="q"
                        placeholder="Cari nama, rekening, atau kode..."
                        value="<?= htmlspecialchars($keyword); ?>"
                    >

                    <button type="submit">Cari</button>

                    <?php if ($keyword !== ""): ?>
                        <a href="transaksi.php">Reset</a>
                    <?php endif; ?>

                </form>

            </div>

            <div class="transaksi-table-wrapper">

                <table class="transaksi-table">

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

                            <?php while ($transaksi = mysqli_fetch_assoc($result)): ?>

                                <tr>
                                    <td><?= $no++; ?></td>

                                    <td>
                                        <strong class="transaksi-kode">
                                            <?= htmlspecialchars($transaksi["kode_transaksi"]); ?>
                                        </strong>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($transaksi["nama"]); ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($transaksi["nomor_rekening"]); ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($transaksi["keterangan"]); ?>
                                    </td>

                                    <td>
                                        <?php if ($transaksi["jenis"] === "masuk"): ?>
                                            <span class="transaksi-badge-masuk">
                                                Masuk
                                            </span>
                                        <?php else: ?>
                                            <span class="transaksi-badge-keluar">
                                                Keluar
                                            </span>
                                        <?php endif; ?>
                                    </td>

                                    <td class="transaksi-nominal">
                                        Rp <?= number_format((float)$transaksi["nominal"], 0, ",", "."); ?>
                                    </td>

                                    <td>
                                        <?= date("d-m-Y H:i", strtotime($transaksi["tanggal"])); ?>
                                    </td>
                                </tr>

                            <?php endwhile; ?>

                        <?php else: ?>

                            <tr>
                                <td colspan="8" class="transaksi-empty">
                                    Belum ada transaksi.
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
