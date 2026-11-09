<?php

require_once "auth.php";

if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    responseJSON(405, "Method tidak diizinkan");
}

// Memeriksa token pengguna
$user = cekToken();
$user_id = (int) $user["user_id"];

// Mengambil riwayat transaksi
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
          ORDER BY t.tanggal DESC, t.id DESC";

$stmt = mysqli_prepare($conn, $query);

if (!$stmt) {
    responseJSON(500, "Query database gagal");
}

mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$transaksi = [];

while ($row = mysqli_fetch_assoc($result)) {
    $transaksi[] = $row;
}

mysqli_stmt_close($stmt);

responseJSON(200, "Data transaksi berhasil diambil", [
    "jumlah_transaksi" => count($transaksi),
    "riwayat" => $transaksi
]);

?>