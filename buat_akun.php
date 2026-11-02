<?php

require_once __DIR__ . "/config/database.php";

$nama = "Rian Pratama";
$username = "rian";
$password = password_hash("rian123", PASSWORD_DEFAULT);
$no_hp = "081234567890";
$alamat = "Depok";

$query = "INSERT INTO users
(nama, username, password, no_hp, alamat)
VALUES (?, ?, ?, ?, ?)";

$stmt = mysqli_prepare($conn, $query);

if (!$stmt) {
    die("Query gagal: " . mysqli_error($conn));
}

mysqli_stmt_bind_param(
    $stmt,
    "sssss",
    $nama,
    $username,
    $password,
    $no_hp,
    $alamat
);

if (mysqli_stmt_execute($stmt)) {
    echo "Akun demo berhasil dibuat!";
} else {
    echo "Gagal: " . mysqli_stmt_error($stmt);
}

mysqli_stmt_close($stmt);
mysqli_close($conn);

?>