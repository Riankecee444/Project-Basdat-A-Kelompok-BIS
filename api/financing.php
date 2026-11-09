<?php

require_once "auth.php";

if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    responseJSON(405, "Method tidak diizinkan");
}

// Memeriksa token pengguna
$user = cekToken();
$user_id = (int) $user["user_id"];

// Mengambil data pembiayaan
$query = "SELECT
            id,
            jenis_akad,
            plafon,
            margin,
            tenor,
            angsuran,
            status
          FROM financings
          WHERE user_id = ?";

$stmt = mysqli_prepare($conn, $query);

if (!$stmt) {
    responseJSON(500, "Query database gagal");
}

mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$pembiayaan = [];

while ($row = mysqli_fetch_assoc($result)) {
    $pembiayaan[] = $row;
}

mysqli_stmt_close($stmt);

responseJSON(200, "Data pembiayaan berhasil diambil", [
    "jumlah_pembiayaan" => count($pembiayaan),
    "data_pembiayaan" => $pembiayaan
]);

?>