<?php
session_start();

require_once __DIR__ . "/../config/database.php";

if (!isset($_SESSION["pending_user_id"])) {
    header("Location: ../index.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: otp.php");
    exit;
}

$otp = trim($_POST["otp"] ?? "");

if (time() > ($_SESSION["otp_expiry"] ?? 0)) {
    session_unset();
    header("Location: ../index.php?expired=1");
    exit;
}

$_SESSION["otp_attempts"]++;

if (
    $_SESSION["otp_attempts"] > 5 ||
    !password_verify($otp, $_SESSION["otp_hash"])
) {
    if ($_SESSION["otp_attempts"] >= 5) {
        session_unset();
        header("Location: ../index.php?error=otp_limit");
        exit;
    }

    header("Location: otp.php?error=1");
    exit;
}

// OTP benar, ambil data pengguna dari database
$userId = $_SESSION["pending_user_id"];

$query = "SELECT id, nama, username, role
          FROM users
          WHERE id = ?
          LIMIT 1";

$stmt = mysqli_prepare($conn, $query);

if (!$stmt) {
    session_unset();
    header("Location: ../index.php?error=database");
    exit;
}

mysqli_stmt_bind_param($stmt, "i", $userId);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

if (!$user || !in_array($user["role"], ["admin", "nasabah"], true)) {
    session_unset();
    header("Location: ../index.php?error=user");
    exit;
}

// Aktifkan sesi login
session_regenerate_id(true);

$_SESSION["user_id"] = $user["id"];
$_SESSION["nama"] = $user["nama"];
$_SESSION["username"] = $user["username"];
$_SESSION["role"] = $user["role"];

// Hapus data sementara OTP
unset(
    $_SESSION["pending_user_id"],
    $_SESSION["pending_nama"],
    $_SESSION["pending_username"],
    $_SESSION["otp_hash"],
    $_SESSION["otp_expiry"],
    $_SESSION["otp_attempts"],
    $_SESSION["demo_otp"]
);

// Arahkan berdasarkan role
if ($_SESSION["role"] === "admin") {
    header("Location: ../pages/admin/dashboard.php");
    exit;
}

header("Location: ../pages/dashboard.php");
exit;
?>