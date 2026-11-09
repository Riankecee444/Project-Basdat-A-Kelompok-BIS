<?php

require_once "auth.php";

if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    responseJSON(405, "Method tidak diizinkan");
}

// Memeriksa token pengguna
$user = cekToken();
$user_id = (int) $user["user_id"];

// Mengambil total saldo rekening aktif
$querySaldo = "SELECT COALESCE(SUM(saldo), 0) AS total_saldo
               FROM accounts
               WHERE user_id = ? AND status = 'aktif'";

$stmtSaldo = mysqli_prepare($conn, $querySaldo);
mysqli_stmt_bind_param($stmtSaldo, "i", $user_id);
mysqli_stmt_execute($stmtSaldo);

$resultSaldo = mysqli_stmt_get_result($stmtSaldo);
$saldo = mysqli_fetch_assoc($resultSaldo);

mysqli_stmt_close($stmtSaldo);

// Mengambil 3 transaksi terbaru
$queryTransaksi = "SELECT
                    t.kode_transaksi,
                    t.keterangan,
                    t.jenis,
                    t.nominal,
                    t.tanggal
                   FROM transactions t
                   JOIN accounts a ON t.account_id = a.id
                   WHERE a.user_id = ?
                   ORDER BY t.tanggal DESC, t.id DESC
                   LIMIT 3";

$stmtTransaksi = mysqli_prepare($conn, $queryTransaksi);
mysqli_stmt_bind_param($stmtTransaksi, "i", $user_id);
mysqli_stmt_execute($stmtTransaksi);

$resultTransaksi = mysqli_stmt_get_result($stmtTransaksi);

$transaksi = [];

while ($row = mysqli_fetch_assoc($resultTransaksi)) {
    $transaksi[] = $row;
}

mysqli_stmt_close($stmtTransaksi);

// Mengirim data dashboard
responseJSON(200, "Data dashboard berhasil diambil", [
    "nama" => $user["nama"],
    "total_saldo" => $saldo["total_saldo"],
    "transaksi_terbaru" => $transaksi
]);

?>