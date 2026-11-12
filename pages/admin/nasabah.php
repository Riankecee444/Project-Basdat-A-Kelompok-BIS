
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
$activePage = "nasabah";
$keyword = trim($_GET["q"] ?? "");

// Ambil data nasabah
$sql = "SELECT id, nama, username, no_hp, alamat, created_at
        FROM users
        WHERE role = 'nasabah'";

if ($keyword !== "") {
    $sql .= " AND (nama LIKE ? OR username LIKE ? OR no_hp LIKE ?)";
}

$sql .= " ORDER BY id DESC";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    die("Query gagal disiapkan: " . mysqli_error($conn));
}

if ($keyword !== "") {
    $cari = "%" . $keyword . "%";
    mysqli_stmt_bind_param($stmt, "sss", $cari, $cari, $cari);
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$totalNasabah = mysqli_num_rows($result);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Nasabah - Bank Iman Syariah</title>
    <link rel="stylesheet" href="../../assets/css/admin.css">
</head>
<body>

<div class="admin-layout">

    <?php include __DIR__ . "/../../includes/admin_sidebar.php"; ?>

    <main class="main-content">

        <header class="topbar">
            <div>
                <h1>Data Nasabah</h1>
                <p>Kelola dan lihat informasi nasabah Bank Iman Syariah.</p>
            </div>

            <span class="admin-badge">ADMIN</span>
        </header>

        <section class="nasabah-summary">
            <div>
                <span>Total Nasabah</span>
                <h2><?= number_format($totalNasabah, 0, ',', '.'); ?></h2>
                <small>Hasil data sesuai pencarian</small>
            </div>
        </section>

        <section class="nasabah-table-card">

            <div class="nasabah-table-header">
                <div>
                    <h2>Daftar Nasabah</h2>
                    <p>Informasi akun nasabah yang terdaftar.</p>
                </div>

                <form method="GET" class="nasabah-search">

                    <input
                        type="text"
                        name="q"
                        placeholder="Cari nama, username, atau nomor HP..."
                        value="<?= htmlspecialchars($keyword); ?>"
                    >

                    <button type="submit">Cari</button>

                    <?php if ($keyword !== ""): ?>
                        <a href="nasabah.php">Reset</a>
                    <?php endif; ?>

                </form>
            </div>

            <div class="nasabah-table-wrapper">

                <table class="nasabah-table">

                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Nama Nasabah</th>
                            <th>Username</th>
                            <th>No. HP</th>
                            <th>Alamat</th>
                            <th>Tanggal Daftar</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php if ($totalNasabah > 0): ?>

                            <?php $no = 1; ?>

                            <?php while ($nasabah = mysqli_fetch_assoc($result)): ?>

                                <tr>
                                    <td><?= $no++; ?></td>

                                    <td>
                                        <strong>
                                            <?= htmlspecialchars($nasabah["nama"]); ?>
                                        </strong>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($nasabah["username"]); ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($nasabah["no_hp"] ?? "-"); ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($nasabah["alamat"] ?? "-"); ?>
                                    </td>

                                    <td>
                                        <?= !empty($nasabah["created_at"])
                                            ? date("d-m-Y", strtotime($nasabah["created_at"]))
                                            : "-"; ?>
                                    </td>
                                </tr>

                            <?php endwhile; ?>

                        <?php else: ?>

                            <tr>
                                <td colspan="6" class="empty-data">
                                    Belum ada data nasabah.
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
