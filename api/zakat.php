<?php

require_once "auth.php";

if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    responseJSON(405, "Method tidak diizinkan");
}

// Memeriksa token pengguna
$user = cekToken();
$user_id = (int) $user["user_id"];

// Mengambil riwayat pembayaran zakat
$query = "SELECT
            id,
            jenis_zakat,
            nominal,
            status,
            tanggal
          FROM zakat_payments
          WHERE user_id = ?
          ORDER BY tanggal DESC, id DESC";

$stmt = mysqli_prepare($conn, $query);

if (!$stmt) {
    responseJSON(500, "Query database gagal");
}

mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$zakat = [];

while ($row = mysqli_fetch_assoc($result)) {
    $zakat[] = $row;
}

mysqli_stmt_close($stmt);

responseJSON(200, "Data zakat berhasil diambil", [
    "jumlah_pembayaran" => count($zakat),
    "total_zakat" => array_sum(array_column($zakat, "nominal")),
    "riwayat_zakat" => $zakat
]);

?>