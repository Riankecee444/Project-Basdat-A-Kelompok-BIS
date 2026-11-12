
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
$activePage = "rekening";
$keyword = trim($_GET["q"] ?? "");
$pesan = "";

// Token keamanan CSRF
if (empty($_SESSION["csrf_rekening"])) {
    $_SESSION["csrf_rekening"] = bin2hex(random_bytes(32));
}

// Proses ubah status rekening
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $token = $_POST["csrf_token"] ?? "";
    $idRekening = (int) ($_POST["id_rekening"] ?? 0);
    $statusBaru = $_POST["status"] ?? "";

    $statusValid = ["Aktif", "Nonaktif", "Diblokir"];

    if (!hash_equals($_SESSION["csrf_rekening"], $token)) {
        $pesan = "Permintaan tidak valid.";
    } elseif (
        $idRekening <= 0 ||
        !in_array($statusBaru, $statusValid, true)
    ) {
        $pesan = "Data perubahan status tidak valid.";
    } else {
        $stmtUpdate = mysqli_prepare(
            $conn,
            "UPDATE accounts SET status = ? WHERE id = ?"
        );

        if ($stmtUpdate) {
            mysqli_stmt_bind_param(
                $stmtUpdate,
                "si",
                $statusBaru,
                $idRekening
            );

            if (mysqli_stmt_execute($stmtUpdate)) {
                $pesan = "Status rekening berhasil diperbarui.";
            } else {
                $pesan = "Gagal memperbarui status rekening.";
            }

            mysqli_stmt_close($stmtUpdate);
        } else {
            $pesan = "Gagal menyiapkan perubahan status.";
        }
    }
}

// Statistik rekening
$queryStatistik = mysqli_query(
    $conn,
    "SELECT
        COUNT(*) AS total_rekening,
        COALESCE(SUM(saldo), 0) AS total_saldo
     FROM accounts"
);

$statistik = mysqli_fetch_assoc($queryStatistik);

$totalRekening = $statistik["total_rekening"] ?? 0;
$totalSaldo = $statistik["total_saldo"] ?? 0;

// Query data rekening
$sql = "SELECT
            accounts.id,
            accounts.nomor_rekening,
            accounts.jenis_akad,
            accounts.saldo,
            accounts.status,
            users.nama
        FROM accounts
        INNER JOIN users ON accounts.user_id = users.id";

if ($keyword !== "") {
    $sql .= " WHERE users.nama LIKE ?
              OR accounts.nomor_rekening LIKE ?";
}

$sql .= " ORDER BY accounts.id DESC";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    die("Query rekening gagal: " . mysqli_error($conn));
}

if ($keyword !== "") {
    $cari = "%" . $keyword . "%";
    mysqli_stmt_bind_param($stmt, "ss", $cari, $cari);
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$totalHasil = mysqli_num_rows($result);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Data Rekening - Bank Iman Syariah</title>

    <link rel="stylesheet" href="../../assets/css/admin.css">
</head>

<body>

<div class="admin-layout">

    <?php include __DIR__ . "/../../includes/admin_sidebar.php"; ?>

    <main class="main-content rekening-content">

        <!-- HEADER -->
        <header class="topbar">
            <div>
                <h1>Data Rekening</h1>
                <p>Kelola informasi dan status rekening nasabah.</p>
            </div>

            <span class="admin-badge">ADMIN</span>
        </header>

        <!-- PESAN -->
        <?php if ($pesan !== ""): ?>
            <div class="rekening-alert">
                <?= htmlspecialchars($pesan); ?>
            </div>
        <?php endif; ?>

        <!-- STATISTIK -->
        <section class="rekening-summary">

            <div class="rekening-stat-card">
                <div class="rekening-stat-icon">R</div>

                <div>
                    <span>Total Rekening</span>
                    <h2><?= number_format($totalRekening, 0, ',', '.'); ?></h2>
                    <small>Seluruh rekening terdaftar</small>
                </div>
            </div>

            <div class="rekening-stat-card">
                <div class="rekening-stat-icon">Rp</div>

                <div>
                    <span>Total Saldo</span>
                    <h2>Rp <?= number_format($totalSaldo, 0, ',', '.'); ?></h2>
                    <small>Akumulasi saldo rekening</small>
                </div>
            </div>

        </section>

        <!-- TABEL REKENING -->
        <section class="rekening-panel">

            <div class="rekening-panel-header">
                <div>
                    <h2>Daftar Rekening</h2>
                    <p>Informasi seluruh rekening nasabah Bank Iman Syariah.</p>
                </div>

                <form method="GET" class="rekening-search">

                    <input
                        type="text"
                        name="q"
                        placeholder="Cari nama atau nomor rekening..."
                        value="<?= htmlspecialchars($keyword); ?>"
                    >

                    <button type="submit">Cari</button>

                    <?php if ($keyword !== ""): ?>
                        <a href="rekening.php">Reset</a>
                    <?php endif; ?>

                </form>
            </div>

            <div class="rekening-table-wrapper">

                <table class="rekening-table">

                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Nama Nasabah</th>
                            <th>Nomor Rekening</th>
                            <th>Jenis Akad</th>
                            <th>Saldo</th>
                            <th>Status</th>
                            <th>Ubah Status</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php if ($totalHasil > 0): ?>

                            <?php $no = 1; ?>

                            <?php while ($rekening = mysqli_fetch_assoc($result)): ?>

                                <tr>
                                    <td><?= $no++; ?></td>

                                    <td>
                                        <strong>
                                            <?= htmlspecialchars($rekening["nama"]); ?>
                                        </strong>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($rekening["nomor_rekening"]); ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($rekening["jenis_akad"]); ?>
                                    </td>

                                    <td class="rekening-saldo">
                                        Rp <?= number_format($rekening["saldo"], 0, ',', '.'); ?>
                                    </td>

                                    <td>
                                        <?php
                                        $statusClass = strtolower($rekening["status"]);
                                        ?>

                                        <span class="rekening-status status-<?= htmlspecialchars($statusClass); ?>">
                                            <?= htmlspecialchars($rekening["status"]); ?>
                                        </span>
                                    </td>

                                    <td>
                                        <form method="POST" class="rekening-status-form"
                                            onsubmit="return confirm('Yakin ingin mengubah status rekening ini?');">

                                            <input
                                                type="hidden"
                                                name="csrf_token"
                                                value="<?= htmlspecialchars($_SESSION["csrf_rekening"]); ?>"
                                            >

                                            <input
                                                type="hidden"
                                                name="id_rekening"
                                                value="<?= (int) $rekening["id"]; ?>"
                                            >

                                            <select name="status" required>
                                                <option value="Aktif"
                                                    <?= $rekening["status"] === "Aktif" ? "selected" : ""; ?>>
                                                    Aktif
                                                </option>

                                                <option value="Nonaktif"
                                                    <?= $rekening["status"] === "Nonaktif" ? "selected" : ""; ?>>
                                                    Nonaktif
                                                </option>

                                                <option value="Diblokir"
                                                    <?= $rekening["status"] === "Diblokir" ? "selected" : ""; ?>>
                                                    Diblokir
                                                </option>
                                            </select>

                                            <button type="submit">Simpan</button>
                                        </form>
                                    </td>
                                </tr>

                            <?php endwhile; ?>

                        <?php else: ?>

                            <tr>
                                <td colspan="7" class="rekening-empty">
                                    Belum ada data rekening.
                                </td>
                            </tr>

                        <?php endif; ?>

                    </tbody>

                </table>

            </div>

            <div class="rekening-panel-footer">
                Menampilkan <?= number_format($totalHasil, 0, ',', '.'); ?> data rekening
            </div>

        </section>

    </main>

</div>

<?php
mysqli_stmt_close($stmt);
?>

</body>
</html>
