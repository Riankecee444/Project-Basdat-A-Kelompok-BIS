
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
$activePage = "setor_tunai";
$pesan = "";
$jenisPesan = "";

// Token keamanan form
if (empty($_SESSION["csrf_setor"])) {
    $_SESSION["csrf_setor"] = bin2hex(random_bytes(32));
}

// Proses setor tunai
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $token = $_POST["csrf_token"] ?? "";
    $rekeningId = (int) ($_POST["rekening_id"] ?? 0);
    $nominalInput = $_POST["nominal"] ?? "";
    $keterangan = trim($_POST["keterangan"] ?? "");

    if (!hash_equals($_SESSION["csrf_setor"], $token)) {
        $pesan = "Permintaan tidak valid. Silakan muat ulang halaman.";
        $jenisPesan = "error";
    } elseif (
        $rekeningId <= 0 ||
        !ctype_digit((string) $nominalInput) ||
        (int) $nominalInput <= 0
    ) {
        $pesan = "Pilih rekening dan masukkan nominal yang valid.";
        $jenisPesan = "error";
    } else {
        $nominal = (int) $nominalInput;

        mysqli_begin_transaction($conn);

        try {
            // Kunci rekening selama proses setor tunai
            $query = "SELECT id, nomor_rekening, saldo, status
                      FROM accounts
                      WHERE id = ?
                      FOR UPDATE";

            $stmt = mysqli_prepare($conn, $query);

            if (!$stmt) {
                throw new Exception("Gagal memeriksa rekening.");
            }

            mysqli_stmt_bind_param($stmt, "i", $rekeningId);
            mysqli_stmt_execute($stmt);

            $result = mysqli_stmt_get_result($stmt);
            $rekening = mysqli_fetch_assoc($result);

            mysqli_stmt_close($stmt);

            if (!$rekening || $rekening["status"] !== "Aktif") {
                throw new Exception(
                    "Rekening tidak ditemukan atau tidak aktif."
                );
            }

            // Tambahkan saldo rekening
            $update = mysqli_prepare(
                $conn,
                "UPDATE accounts
                 SET saldo = saldo + ?
                 WHERE id = ?"
            );

            if (!$update) {
                throw new Exception("Gagal menyiapkan perubahan saldo.");
            }

            mysqli_stmt_bind_param(
                $update,
                "ii",
                $nominal,
                $rekeningId
            );

            if (!mysqli_stmt_execute($update)) {
                throw new Exception("Gagal memperbarui saldo rekening.");
            }

            mysqli_stmt_close($update);

            // Buat kode transaksi
            $kodeTransaksi = "ST" . date("YmdHis") . random_int(100, 999);

            if ($keterangan === "") {
                $keterangan = "Setor tunai oleh admin";
            }

            // Catat transaksi setoran
            $insert = mysqli_prepare(
                $conn,
                "INSERT INTO transactions
                (account_id, kode_transaksi, keterangan, jenis, nominal)
                VALUES (?, ?, ?, 'masuk', ?)"
            );

            if (!$insert) {
                throw new Exception("Gagal menyiapkan pencatatan transaksi.");
            }

            mysqli_stmt_bind_param(
                $insert,
                "issi",
                $rekeningId,
                $kodeTransaksi,
                $keterangan,
                $nominal
            );

            if (!mysqli_stmt_execute($insert)) {
                throw new Exception("Gagal mencatat transaksi setoran.");
            }

            mysqli_stmt_close($insert);

            // Simpan seluruh perubahan
            mysqli_commit($conn);

            $pesan = "Setor tunai berhasil. Saldo rekening telah bertambah.";
            $jenisPesan = "success";

        } catch (Throwable $e) {
            mysqli_rollback($conn);

            $pesan = $e->getMessage();
            $jenisPesan = "error";
        }
    }
}

// Ambil rekening yang masih aktif
$queryRekening = mysqli_query(
    $conn,
    "SELECT
        accounts.id,
        accounts.nomor_rekening,
        accounts.jenis_akad,
        accounts.saldo,
        users.nama
     FROM accounts
     INNER JOIN users ON accounts.user_id = users.id
     WHERE accounts.status = 'Aktif'
     ORDER BY users.nama ASC"
);

if (!$queryRekening) {
    die("Gagal mengambil data rekening: " . mysqli_error($conn));
}

$totalRekeningAktif = mysqli_num_rows($queryRekening);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Setor Tunai - Bank Iman Syariah</title>

    <link rel="stylesheet" href="../../assets/css/admin.css">
</head>

<body>

<div class="admin-layout">

    <?php include __DIR__ . "/../../includes/admin_sidebar.php"; ?>

    <main class="main-content setor-content">

        <!-- HEADER -->
        <header class="topbar">
            <div>
                <h1>Setor Tunai</h1>
                <p>Tambahkan saldo ke rekening nasabah.</p>
            </div>

            <span class="admin-badge">ADMIN</span>
        </header>

        <!-- INFORMASI REKENING -->
        <section class="setor-summary">

            <div class="setor-summary-icon">R</div>

            <div>
                <span>Rekening Aktif</span>
                <h2><?= number_format($totalRekeningAktif, 0, ',', '.'); ?></h2>
                <small>Rekening yang dapat menerima setoran</small>
            </div>

        </section>

        <!-- PESAN -->
        <?php if ($pesan !== ""): ?>
            <div class="setor-alert <?= $jenisPesan === "success" ? "success" : "error"; ?>">
                <?= htmlspecialchars($pesan); ?>
            </div>
        <?php endif; ?>

        <!-- FORM SETOR -->
        <section class="setor-panel">

            <div class="setor-panel-header">
                <div>
                    <h2>Form Setor Tunai</h2>
                    <p>Lengkapi informasi setoran nasabah di bawah ini.</p>
                </div>
            </div>

            <form method="POST" class="setor-form">

                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= htmlspecialchars($_SESSION["csrf_setor"]); ?>"
                >

                <div class="setor-field">
                    <label for="rekening_id">Rekening Nasabah</label>

                    <select
                        name="rekening_id"
                        id="rekening_id"
                        required
                    >
                        <option value="">-- Pilih Rekening Nasabah --</option>

                        <?php while ($r = mysqli_fetch_assoc($queryRekening)): ?>
                            <option value="<?= (int) $r["id"]; ?>">
                                <?= htmlspecialchars($r["nama"]); ?>
                                - <?= htmlspecialchars($r["nomor_rekening"]); ?>
                                (<?= htmlspecialchars($r["jenis_akad"]); ?>)
                            </option>
                        <?php endwhile; ?>

                    </select>

                    <small>Hanya rekening dengan status Aktif yang ditampilkan.</small>
                </div>

                <div class="setor-field">
                    <label for="nominal">Nominal Setoran</label>

                    <div class="setor-input-group">
                        <span>Rp</span>

                        <input
                            type="number"
                            name="nominal"
                            id="nominal"
                            min="1"
                            step="1"
                            placeholder="Contoh: 500000"
                            required
                        >
                    </div>

                    <small>Masukkan nominal dalam rupiah tanpa tanda titik atau koma.</small>
                </div>

                <div class="setor-field">
                    <label for="keterangan">Keterangan</label>

                    <textarea
                        name="keterangan"
                        id="keterangan"
                        rows="4"
                        placeholder="Contoh: Setoran tunai nasabah"
                    ></textarea>

                    <small>Bagian ini boleh dikosongkan.</small>
                </div>

                <div class="setor-form-footer">
                    <button type="reset" class="setor-reset-button">
                        Reset
                    </button>

                    <button type="submit" class="setor-submit-button">
                        Proses Setor Tunai
                    </button>
                </div>

            </form>

        </section>

    </main>

</div>

</body>
</html>
