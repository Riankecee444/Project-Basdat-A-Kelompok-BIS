<?php

require_once "auth.php";

if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    responseJSON(405, "Method tidak diizinkan");
}

// Memeriksa token pengguna
$user = cekToken();
$user_id = (int) $user["user_id"];

// Mengambil data profil pengguna
$query = "SELECT
            id,
            nama,
            username,
            no_hp,
            alamat
          FROM users
          WHERE id = ?";

$stmt = mysqli_prepare($conn, $query);

if (!$stmt) {
    responseJSON(500, "Query database gagal");
}

mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$profil = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

if (!$profil) {
    responseJSON(404, "Pengguna tidak ditemukan");
}

responseJSON(200, "Data profil berhasil diambil", $profil);

?>