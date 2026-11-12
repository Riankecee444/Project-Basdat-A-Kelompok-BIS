
<?php
session_start();

require_once __DIR__ . "/../../config/database.php";

// Proteksi admin
if (
    !isset($_SESSION["user_id"]) ||
    ($_SESSION["role"] ?? "") !== "admin"
) {
    header("Location: ../../index.php");
    exit;
}

function kembali($pesan)
{
    $_SESSION["pesan_pengajuan"] = $pesan;
    header("Location: pengajuan_pembiayaan.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: pengajuan_pembiayaan.php");
    exit;
}

// Validasi CSRF
$token = $_POST["csrf_token"] ?? "";

if (
    empty($_SESSION["csrf_token"]) ||
    !hash_equals($_SESSION["csrf_token"], $token)
) {
    kembali("Permintaan tidak valid. Silakan coba lagi.");
}

$pengajuanId = (int) ($_POST["pengajuan_id"] ?? 0);
$aksi = $_POST["aksi"] ?? "";

if ($pengajuanId <= 0 || !in_array($aksi, ["setujui", "tolak"], true)) {
    kembali("Data pengajuan tidak valid.");
}

mysqli_begin_transaction($conn);

try {
    // Kunci pengajuan agar tidak diproses bersamaan
    $query = "SELECT *
              FROM financing_applications
              WHERE id = ?
              FOR UPDATE";

    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $pengajuanId);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    $pengajuan = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if (!$pengajuan) {
        throw new Exception("Pengajuan tidak ditemukan.");
    }

    if ($pengajuan["status"] !== "Menunggu Persetujuan") {
        throw new Exception("Pengajuan ini sudah diproses sebelumnya.");
    }

    if ($aksi === "setujui") {

        // Cek apakah nasabah sudah memiliki pembiayaan aktif
        $queryCek = "SELECT id
                     FROM financings
                     WHERE user_id = ?
                     AND status = 'Aktif'
                     LIMIT 1
                     FOR UPDATE";

        $stmtCek = mysqli_prepare($conn, $queryCek);
        mysqli_stmt_bind_param($stmtCek, "i", $pengajuan["user_id"]);
        mysqli_stmt_execute($stmtCek);

        $resultCek = mysqli_stmt_get_result($stmtCek);
        $pembiayaanAktif = mysqli_fetch_assoc($resultCek);
        mysqli_stmt_close($stmtCek);

        if ($pembiayaanAktif) {
            throw new Exception(
                "Nasabah masih memiliki pembiayaan aktif."
            );
        }

        // Masukkan pembiayaan yang telah disetujui
        $queryInsert = "INSERT INTO financings
                        (user_id, jenis_akad, plafon, margin, tenor, angsuran, status)
                        VALUES (?, ?, ?, ?, ?, ?, 'Aktif')";

        $stmtInsert = mysqli_prepare($conn, $queryInsert);

        $userId = (int) $pengajuan["user_id"];
        $jenisAkad = $pengajuan["jenis_akad"];
        $plafon = (float) $pengajuan["plafon"];
        $margin = (float) $pengajuan["margin"];
        $tenor = (int) $pengajuan["tenor"];
        $angsuran = (float) $pengajuan["estimasi_angsuran"];

        mysqli_stmt_bind_param(
            $stmtInsert,
            "isddid",
            $userId,
            $jenisAkad,
            $plafon,
            $margin,
            $tenor,
            $angsuran
        );

        if (!mysqli_stmt_execute($stmtInsert)) {
            throw new Exception("Gagal membuat data pembiayaan.");
        }

        mysqli_stmt_close($stmtInsert);

        // Ubah status pengajuan
        $statusBaru = "Disetujui";

    } else {
        $statusBaru = "Ditolak";
    }

    $queryUpdate = "UPDATE financing_applications
                    SET status = ?
                    WHERE id = ?";

    $stmtUpdate = mysqli_prepare($conn, $queryUpdate);
    mysqli_stmt_bind_param(
        $stmtUpdate,
        "si",
        $statusBaru,
        $pengajuanId
    );

    if (!mysqli_stmt_execute($stmtUpdate)) {
        throw new Exception("Gagal memperbarui status pengajuan.");
    }

    mysqli_stmt_close($stmtUpdate);

    mysqli_commit($conn);

    kembali(
        $aksi === "setujui"
            ? "Pengajuan berhasil disetujui."
            : "Pengajuan berhasil ditolak."
    );

} catch (Throwable $e) {
    mysqli_rollback($conn);
    kembali($e->getMessage());
}
