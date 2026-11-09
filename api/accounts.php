<?php

require_once "auth.php";

if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    responseJSON(405, "Method tidak diizinkan");
}

// Memeriksa token
$user = cekToken();
$user_id = (int) $user["user_id"];

// Mengambil rekening milik pengguna
$query = "SELECT
            id,
            nomor_rekening,
            jenis_akad,
            saldo,
            status
          FROM accounts
          WHERE user_id = ?";

$stmt = mysqli_prepare($conn, $query);

if (!$stmt) {
    responseJSON(500, "Query database gagal");
}

mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$rekening = [];

while ($row = mysqli_fetch_assoc($result)) {
    $rekening[] = $row;
}

mysqli_stmt_close($stmt);

responseJSON(200, "Data rekening berhasil diambil", [
    "jumlah_rekening" => count($rekening),
    "data_rekening" => $rekening
]);

?>