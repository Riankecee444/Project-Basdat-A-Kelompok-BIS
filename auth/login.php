<?php
session_start();

require_once "../config/database.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../index.php");
    exit;
}

$username = trim($_POST["username"] ?? "");
$password = $_POST["password"] ?? "";

$query = "SELECT * FROM users WHERE username = ?";
$stmt = mysqli_prepare($conn, $query);

mysqli_stmt_bind_param($stmt, "s", $username);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

if ($user && password_verify($password, $user["password"])) {

    session_regenerate_id(true);

    // Simpan identitas sementara, belum login penuh
    $_SESSION["pending_user_id"] = $user["id"];
    $_SESSION["pending_nama"] = $user["nama"];
    $_SESSION["pending_username"] = $user["username"];

    // Buat OTP 6 digit, berlaku 5 menit
    $otp = (string) random_int(100000, 999999);

    $_SESSION["otp_hash"] = password_hash($otp, PASSWORD_DEFAULT);
    $_SESSION["otp_expiry"] = time() + 300;
    $_SESSION["otp_attempts"] = 0;

    // Untuk simulasi demo kuliah
    $_SESSION["demo_otp"] = $otp;

    header("Location: otp.php");
    exit;

} else {
    header("Location: ../index.php?error=1");
    exit;
}
?>