
<?php
session_start();

require_once __DIR__ . "/../config/database.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../index.php");
    exit;
}

$username = trim($_POST["username"] ?? "");
$password = $_POST["password"] ?? "";
$role = $_POST["role"] ?? "";

// Validasi pilihan login
if (!in_array($role, ["admin", "nasabah"], true)) {
    header("Location: ../index.php?error=1");
    exit;
}

// Cari pengguna berdasarkan username
$query = "SELECT * FROM users WHERE username = ? LIMIT 1";
$stmt = mysqli_prepare($conn, $query);

if (!$stmt) {
    header("Location: ../index.php?error=1");
    exit;
}

mysqli_stmt_bind_param($stmt, "s", $username);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

// Periksa password dan kecocokan role
if (
    $user &&
    password_verify($password, $user["password"]) &&
    $user["role"] === $role
) {

    session_regenerate_id(true);

    // Simpan identitas sementara
    $_SESSION["pending_user_id"] = $user["id"];
    $_SESSION["pending_nama"] = $user["nama"];
    $_SESSION["pending_username"] = $user["username"];

    // Buat OTP 6 digit
    $otp = (string) random_int(100000, 999999);

    $_SESSION["otp_hash"] = password_hash($otp, PASSWORD_DEFAULT);
    $_SESSION["otp_expiry"] = time() + 300;
    $_SESSION["otp_attempts"] = 0;

    // OTP simulasi untuk demo
    $_SESSION["demo_otp"] = $otp;

    header("Location: otp.php");
    exit;

} else {
    header("Location: ../index.php?error=1");
    exit;
}
?>
