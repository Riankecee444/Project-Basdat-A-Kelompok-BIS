<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: ../index.php");
    exit;
}

require_once "../config/database.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: financing.php");
    exit;
}

$user_id = (int) $_SESSION["user_id"];
$jenis_akad = $_POST["jenis_akad"] ?? "";
$plafon = (float) ($_POST["plafon"] ?? 0);
$tenor = (int) ($_POST["tenor"] ?? 0);

$akad_valid = ["Murabahah", "Mudharabah", "Musyarakah"];
$tenor_valid = [12, 24, 36, 48, 60];

if (
    !in_array($jenis_akad, $akad_valid, true) ||
    $plafon < 1000000 ||
    $plafon > 100000000 ||
    !in_array($tenor, $tenor_valid, true)
) {
    $_SESSION["pesan_pembiayaan"] = "Data pengajuan tidak valid.";
    header("Location: financing.php");
    exit;
}

// Simulasi margin tetap 8% per tahun
$margin = 8;
$total_pembayaran = $plafon * (1 + ($margin / 100 * $tenor / 12));
$estimasi_angsuran = $total_pembayaran / $tenor;

$query = "INSERT INTO financing_applications
          (user_id, jenis_akad, plafon, tenor, margin, estimasi_angsuran)
          VALUES (?, ?, ?, ?, ?, ?)";

$stmt = mysqli_prepare($conn, $query);

mysqli_stmt_bind_param(
    $stmt,
    "isdiid",
    $user_id,
    $jenis_akad,
    $plafon,
    $tenor,
    $margin,
    $estimasi_angsuran
);

if (mysqli_stmt_execute($stmt)) {
    $_SESSION["pesan_pembiayaan"] = "Pengajuan berhasil disimpan dan menunggu persetujuan.";
} else {
    $_SESSION["pesan_pembiayaan"] = "Pengajuan gagal disimpan.";
}

mysqli_stmt_close($stmt);

header("Location: financing.php");
exit;