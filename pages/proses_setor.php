<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: ../index.php");
    exit;
}

require_once "../config/database.php";

$user_id = $_SESSION["user_id"];

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: setor_tunai.php");
    exit;
}

$account_id = filter_input(INPUT_POST, "account_id", FILTER_VALIDATE_INT);
$nominal = filter_input(INPUT_POST, "nominal", FILTER_VALIDATE_INT);

if (!$account_id || !$nominal || $nominal < 1) {
    header("Location: setor_tunai.php?error=1");
    exit;
}

mysqli_begin_transaction($conn);

try {
    // Pastikan rekening milik pengguna dan masih aktif
    $query = "SELECT id FROM accounts
              WHERE id = ? AND user_id = ? AND status = 'Aktif'
              FOR UPDATE";

    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ii", $account_id, $user_id);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    $rekening = mysqli_fetch_assoc($result);

    mysqli_stmt_close($stmt);

    if (!$rekening) {
        throw new Exception("Rekening tidak ditemukan.");
    }

    // Tambahkan saldo
    $query = "UPDATE accounts
              SET saldo = saldo + ?
              WHERE id = ? AND user_id = ?";

    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "dii", $nominal, $account_id, $user_id);

    if (!mysqli_stmt_execute($stmt) || mysqli_stmt_affected_rows($stmt) !== 1) {
        throw new Exception("Gagal memperbarui saldo.");
    }

    mysqli_stmt_close($stmt);

    // Buat kode transaksi
    $kode_transaksi = "STR" . date("YmdHis") . random_int(100, 999);
    $keterangan = "Setor tunai";
    $jenis = "masuk";

    // Simpan riwayat transaksi
    $query = "INSERT INTO transactions
              (account_id, kode_transaksi, keterangan, jenis, nominal, tanggal)
              VALUES (?, ?, ?, ?, ?, NOW())";

    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param(
        $stmt,
        "isssd",
        $account_id,
        $kode_transaksi,
        $keterangan,
        $jenis,
        $nominal
    );

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Gagal menyimpan transaksi.");
    }

    mysqli_stmt_close($stmt);

    mysqli_commit($conn);

    header("Location: setor_tunai.php?success=1");
    exit;

} catch (Throwable $e) {
    mysqli_rollback($conn);

    error_log($e->getMessage());

    header("Location: setor_tunai.php?error=1");
    exit;
}