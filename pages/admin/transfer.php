
<?php
session_start();

if (
    !isset($_SESSION["user_id"]) ||
    ($_SESSION["role"] ?? "") !== "admin"
) {
    header("Location: ../../index.php");
    exit;
}

require_once "../../config/database.php";

$activePage = "transfer";

// Ambil data transfer keluar
$query = "
    SELECT
        t.id,
        t.referensi_transfer,
        t.kode_transaksi,
        t.keterangan,
        t.nominal,
        t.tanggal,
        a1.nomor_rekening AS rekening_asal,
        u1.nama AS nama_pengirim,
        a2.nomor_rekening AS rekening_tujuan,
        u2.nama AS nama_penerima
    FROM transactions t
    JOIN accounts a1 ON t.account_id = a1.id
    JOIN users u1 ON a1.user_id = u1.id
    JOIN accounts a2 ON t.rekening_lawan_id = a2.id
    JOIN users u2 ON a2.user_id = u2.id
    WHERE t.referensi_transfer IS NOT NULL
      AND t.jenis = 'keluar'
    ORDER BY t.tanggal DESC
";

$result = mysqli_query($conn, $query);

if (!$result) {
    die("Query error: " . mysqli_error($conn));
}

// Statistik transfer
$totalQuery = "
    SELECT
        COUNT(*) AS total,
        COALESCE(SUM(nominal), 0) AS nominal
    FROM transactions
    WHERE referensi_transfer IS NOT NULL
      AND jenis = 'keluar'
";

$totalResult = mysqli_query($conn, $totalQuery);

if (!$totalResult) {
    die("Query statistik error: " . mysqli_error($conn));
}

$totalData = mysqli_fetch_assoc($totalResult);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Data Transfer - Bank Iman Syariah</title>

    <link rel="stylesheet" href="../../assets/css/admin.css">
</head>

<body>

<div class="admin-layout">

    <?php include __DIR__ . "/../../includes/admin_sidebar.php"; ?>

    <main class="main-content transfer-content">

        <div class="transfer-header">
            <div>
                <h1>Data Transfer</h1>
                <p>Kelola dan pantau riwayat transfer nasabah.</p>
            </div>
        </div>

        <!-- Statistik -->
        <div class="transfer-summary">

            <div class="transfer-card">
                <div class="transfer-card-icon">T</div>

                <div>
                    <p>Total Transaksi Transfer</p>
                    <h2>
                        <?= number_format((int)$totalData["total"], 0, ",", "."); ?>
                    </h2>
                    <span>Transaksi berhasil</span>
                </div>
            </div>

            <div class="transfer-card">
                <div class="transfer-card-icon">Rp</div>

                <div>
                    <p>Total Nominal Transfer</p>
                    <h2>
                        Rp <?= number_format((float)$totalData["nominal"], 0, ",", "."); ?>
                    </h2>
                    <span>Akumulasi transfer keluar</span>
                </div>
            </div>

        </div>

        <!-- Tabel transfer -->
        <div class="transfer-panel">

            <div class="transfer-panel-header">
                <div>
                    <h3>Riwayat Transfer Nasabah</h3>
                    <p>Daftar seluruh transaksi transfer yang tercatat.</p>
                </div>

                <span class="transfer-total">
                    <?= number_format((int)$totalData["total"], 0, ",", "."); ?> Transfer
                </span>
            </div>

            <div class="transfer-table-wrapper">

                <table class="transfer-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Referensi</th>
                            <th>Pengirim</th>
                            <th>Rekening Asal</th>
                            <th>Penerima</th>
                            <th>Rekening Tujuan</th>
                            <th>Nominal</th>
                            <th>Tanggal</th>
                            <th>Status</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php if (mysqli_num_rows($result) > 0): ?>

                            <?php $no = 1; ?>

                            <?php while ($row = mysqli_fetch_assoc($result)): ?>

                                <tr>
                                    <td><?= $no++; ?></td>

                                    <td>
                                        <strong>
                                            <?= htmlspecialchars($row["referensi_transfer"]); ?>
                                        </strong>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($row["nama_pengirim"]); ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($row["rekening_asal"]); ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($row["nama_penerima"]); ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($row["rekening_tujuan"]); ?>
                                    </td>

                                    <td class="transfer-nominal">
                                        Rp <?= number_format((float)$row["nominal"], 0, ",", "."); ?>
                                    </td>

                                    <td>
                                        <?= date("d-m-Y H:i", strtotime($row["tanggal"])); ?>
                                    </td>

                                    <td>
                                        <span class="transfer-badge">
                                            Berhasil
                                        </span>
                                    </td>
                                </tr>

                            <?php endwhile; ?>

                        <?php else: ?>

                            <tr>
                                <td colspan="9" class="transfer-empty">
                                    Belum ada transaksi transfer.
                                </td>
                            </tr>

                        <?php endif; ?>

                    </tbody>
                </table>

            </div>

        </div>

    </main>

</div>

</body>
</html>
