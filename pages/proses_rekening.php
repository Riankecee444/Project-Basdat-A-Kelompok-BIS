<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: ../index.php");
    exit;
}

require_once "../config/database.php";

$user_id = $_SESSION["user_id"];

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: accounts.php");
    exit;
}

$jenis_akad = $_POST["jenis_akad"] ?? "";

if (!in_array($jenis_akad, ["Wadiah", "Mudharabah"], true)) {
    header("Location: accounts.php?error=akad");
    exit;
}

// Membuat nomor rekening 10 digit
do {
    $nomor_rekening = (string) random_int(1000000000, 9999999999);

    $cek = mysqli_prepare(
        $conn,
        "SELECT id FROM accounts WHERE nomor_rekening = ?"
    );

    mysqli_stmt_bind_param($cek, "s", $nomor_rekening);
    mysqli_stmt_execute($cek);

    $hasil = mysqli_stmt_get_result($cek);
    $sudahAda = mysqli_num_rows($hasil) > 0;

    mysqli_stmt_close($cek);

} while ($sudahAda);

// Menyimpan rekening baru
$saldo = 0;
$status = "Aktif";

$query = "INSERT INTO accounts
          (user_id, nomor_rekening, jenis_akad, saldo, status)
          VALUES (?, ?, ?, ?, ?)";

$stmt = mysqli_prepare($conn, $query);

mysqli_stmt_bind_param(
    $stmt,
    "issds",
    $user_id,
    $nomor_rekening,
    $jenis_akad,
    $saldo,
    $status
);

if (mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);
    header("Location: accounts.php?success=1");
    exit;
}

mysqli_stmt_close($stmt);

header("Location: accounts.php?error=gagal");
exit;
?>