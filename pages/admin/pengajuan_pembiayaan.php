
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

$namaAdmin = $_SESSION["nama"] ?? "Administrator";
$activePage = "pengajuan_pembiayaan";

// Token keamanan form
if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

// Ambil seluruh pengajuan beserta nama nasabah
$sql = "SELECT
            fa.id,
            fa.user_id,
            fa.jenis_akad,
            fa.plafon,
            fa.tenor,
            fa.margin,
            fa.estimasi_angsuran,
            fa.status,
            fa.tanggal_pengajuan,
            u.nama,
            u.username
        FROM financing_applications fa
        INNER JOIN users u ON fa.user_id = u.id
        ORDER BY fa.tanggal_pengajuan DESC";

$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Query gagal: " . mysqli_error($conn));
}

$totalPengajuan = mysqli_num_rows($result);

$pesan = $_SESSION["pesan_pengajuan"] ?? "";
unset($_SESSION["pesan_pengajuan"]);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengajuan Pembiayaan - Bank Iman Syariah</title>
    <link rel="stylesheet" href="../../assets/css/admin.css">
</head>
<body>

<div class="admin-layout">

    <?php include __DIR__ . "/../../includes/admin_sidebar.php"; ?>

    <main class="main-content">

        <header class="topbar">
            <div>
                <h1>Pengajuan Pembiayaan</h1>
                <p>Kelola dan tinjau pengajuan pembiayaan dari nasabah.</p>
            </div>

            <span class="admin-badge">ADMIN</span>
        </header>

        <section class="nasabah-summary">
            <div>
                <span>Total Pengajuan</span>
                <h2><?= number_format($totalPengajuan, 0, ',', '.'); ?></h2>
                <small>Seluruh pengajuan pembiayaan nasabah</small>
            </div>
        </section>

        <?php if ($pesan !== ""): ?>
    <div class="pengajuan-alert">
        <?= htmlspecialchars($pesan); ?>
    </div>
<?php endif; ?>

        <section class="nasabah-table-card">

            <div class="nasabah-table-header">
                <div>
                    <h2>Daftar Pengajuan</h2>
                    <p>Periksa pengajuan sebelum memberikan keputusan.</p>
                </div>
            </div>

            <div class="nasabah-table-wrapper">

                <table class="nasabah-table">

                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Nasabah</th>
                            <th>Jenis Akad</th>
                            <th>Plafon</th>
                            <th>Tenor</th>
                            <th>Estimasi Angsuran</th>
                            <th>Tanggal Pengajuan</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php if ($totalPengajuan > 0): ?>

                            <?php $no = 1; ?>

                            <?php while ($data = mysqli_fetch_assoc($result)): ?>

                                <tr>
                                    <td><?= $no++; ?></td>

                                    <td>
                                        <strong>
                                            <?= htmlspecialchars($data["nama"]); ?>
                                        </strong>
                                        <br>
                                        <small>
                                            @<?= htmlspecialchars($data["username"]); ?>
                                        </small>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($data["jenis_akad"]); ?>
                                    </td>

                                    <td>
                                        Rp <?= number_format((float)$data["plafon"], 0, ',', '.'); ?>
                                    </td>

                                    <td>
                                        <?= (int)$data["tenor"]; ?> Bulan
                                    </td>

                                    <td>
                                        Rp <?= number_format((float)$data["estimasi_angsuran"], 0, ',', '.'); ?>
                                    </td>

                                    <td>
                                        <?= date("d-m-Y H:i", strtotime($data["tanggal_pengajuan"])); ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($data["status"]); ?>
                                    </td>

                                    <td>

                                        <?php if ($data["status"] === "Menunggu Persetujuan"): ?>

                                            <form
                                                method="POST"
                                                action="proses_pengajuan_pembiayaan.php"
                                                class="pengajuan-action-form"
                                                onsubmit="return confirm('Yakin ingin menyetujui pengajuan ini?');"
                                            >
                                                <input type="hidden" name="csrf_token"
                                                    value="<?= htmlspecialchars($_SESSION["csrf_token"]); ?>">

                                                <input type="hidden" name="pengajuan_id"
                                                    value="<?= (int)$data["id"]; ?>">

                                                <input type="hidden" name="aksi" value="setujui">

                                                <button type="submit" class="btn-setujui">
                                                    Setujui
                                                </button>
                                            </form>

                                            <form
                                                method="POST"
                                                action="proses_pengajuan_pembiayaan.php"
                                                class="pengajuan-action-form"
                                                onsubmit="return confirm('Yakin ingin menolak pengajuan ini?');"
                                            >
                                                <input type="hidden" name="csrf_token"
                                                    value="<?= htmlspecialchars($_SESSION["csrf_token"]); ?>">

                                                <input type="hidden" name="pengajuan_id"
                                                    value="<?= (int)$data["id"]; ?>">

                                                <input type="hidden" name="aksi" value="tolak">

                                                <button type="submit" class="btn-tolak">
                                                    Tolak
                                                </button>
                                            </form>

                                        <?php else: ?>

                                            <span>-</span>

                                        <?php endif; ?>

                                    </td>
                                </tr>

                            <?php endwhile; ?>

                        <?php else: ?>

                            <tr>
                                <td colspan="9" class="empty-data">
                                    Belum ada pengajuan pembiayaan.
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
mysqli_free_result($result);
?>
