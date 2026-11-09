<?php
session_start();

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

// OTP benar: aktifkan sesi login
session_regenerate_id(true);

$_SESSION["user_id"] = $_SESSION["pending_user_id"];
$_SESSION["nama"] = $_SESSION["pending_nama"];
$_SESSION["username"] = $_SESSION["pending_username"];

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

header("Location: ../pages/dashboard.php");
exit;
?>