<?php
session_start();

date_default_timezone_set("Asia/Jakarta");

if (!isset($_SESSION["user_id"])) {
    header("Location: ../index.php");
    exit;
}

require_once "../config/database.php";

$user_id = $_SESSION["user_id"];
$activePage = "transactions";

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

    <style>
       .print-header {
    display: none;
}

@media print {

    @page {
        size: A4 portrait;
        margin: 12mm;
    }

    html,
    body {
        width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
        background: white !important;
        color: #222 !important;
        font-family: Arial, sans-serif !important;
    }

/* Hilangkan seluruh navigasi sidebar saat cetak */
body > .app-layout > aside,
.app-layout > aside,
.main-sidebar,
.main-sidebar *,
.sidebar,
.sidebar *,
.sidebar-menu,
.sidebar-menu *,
.sidebar-label,
.sidebar-brand,
.sidebar-bottom,
.sidebar-bottom * {
    display: none !important;
    visibility: hidden !important;
}
    /* Hilangkan layout sidebar */
    .app-layout {
        display: block !important;
        width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
    }

    .main-content {
        display: block !important;
        width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
    }

    /* Area laporan */
    #laporan-transaksi {
        display: block !important;
        width: 100% !important;
        max-width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
        transform: none !important;
    }

    /* Judul laporan */
    .print-header {
        display: block !important;
        width: 100% !important;
        text-align: center !important;
        margin-bottom: 22px !important;
    }

    .print-header h1 {
        font-size: 20px !important;
        margin-bottom: 8px !important;
    }

    .print-header h2 {
        font-size: 15px !important;
        margin-bottom: 8px !important;
    }

    .print-header p {
        font-size: 11px !important;
    }

    /* Ringkasan transaksi */
    .transaction-summary {
        display: flex !important;
        flex-direction: row !important;
        gap: 8px !important;
        margin-bottom: 18px !important;
    }

    .summary-card {
        flex: 1 !important;
        min-width: 0 !important;
        padding: 10px !important;
        border: 1px solid #aaa !important;
        background: white !important;
        box-shadow: none !important;
    }

    .summary-card p {
        font-size: 10px !important;
    }

    .summary-card h2 {
        font-size: 12px !important;
    }

    /* Tabel transaksi */
    .transaction-table-section {
        width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
        background: white !important;
        box-shadow: none !important;
    }

    .transaction-table-section > h2 {
        font-size: 14px !important;
        margin-bottom: 10px !important;
    }

    .table-responsive {
        width: 100% !important;
        overflow: visible !important;
    }

    .transaction-table {
        width: 100% !important;
        table-layout: fixed !important;
        border-collapse: collapse !important;
    }

    .transaction-table th,
    .transaction-table td {
        border: 1px solid #999 !important;
        padding: 7px 4px !important;
        font-size: 9px !important;
        color: black !important;
        overflow-wrap: anywhere !important;
    }

    .transaction-table th {
        background: #e8f4ef !important;
        print-color-adjust: exact;
        -webkit-print-color-adjust: exact;
    }

    .transaction-table tr {
        page-break-inside: avoid !important;
    }

    .transaction-count {
        font-size: 10px !important;
        margin-top: 12px !important;
    }

    * {
        box-shadow: none !important;
    }
}
    </style>
</head>

<body>

<div class="app-layout">

    <?php include "../includes/sidebar.php"; ?>

    <main class="main-content">

        <header class="page-header no-print">
            <div>
                <h1>Transaksi</h1>
                <p>Riwayat mutasi seluruh rekening Anda.</p>
            </div>

            <button class="btn-primary" onclick="cetakTransaksi()">
                Unduh / Cetak PDF
            </button>
        </header>

        <div id="laporan-transaksi">

            <div class="print-header">
                <h1>BANK IMAN SYARIAH</h1>
                <h2>LAPORAN RIWAYAT TRANSAKSI</h2>
                <p>
                    Tanggal cetak:
<span id="waktuCetak">
    <?php echo date("d-m-Y H:i"); ?>
</span> WIB
                </p>
            </div>

            <section class="transaction-summary">

             <div class="summary-card">
    <p>Total masuk</p>
    <h2 class="total-masuk">
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
                    <h2>
                        <?php echo count($transaksi); ?> transaksi
                    </h2>
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

                                    <td>
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

        </div>

    </main>
</div>

<script>
function cetakTransaksi() {
    const sekarang = new Date();

    const waktu = sekarang.toLocaleString("id-ID", {
        timeZone: "Asia/Jakarta",
        day: "2-digit",
        month: "2-digit",
        year: "numeric",
        hour: "2-digit",
        minute: "2-digit",
        hour12: false
    });

    document.getElementById("waktuCetak").textContent = waktu;

    window.print();
}
</script>

</body>
</html>