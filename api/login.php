<?php

require_once "config.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    responseJSON(405, "Method tidak diizinkan");
}

$input = json_decode(file_get_contents("php://input"), true);

$username = trim($input["username"] ?? "");
$password = $input["password"] ?? "";

if ($username === "" || $password === "") {
    responseJSON(400, "Username dan password wajib diisi");
}

// Mencari pengguna
$query = "SELECT id, nama, username, password
          FROM users
          WHERE username = ?";

$stmt = mysqli_prepare($conn, $query);

if (!$stmt) {
    responseJSON(500, "Query database gagal");
}

mysqli_stmt_bind_param($stmt, "s", $username);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

if (!$user || !password_verify($password, $user["password"])) {
    responseJSON(401, "Username atau password salah");
}

// Membuat token acak
$token = bin2hex(random_bytes(32));

// Mengubah token menjadi hash
$token_hash = hash("sha256", $token);

// Masa berlaku token: 1 hari
$expires_at = date("Y-m-d H:i:s", time() + 86400);

// Menyimpan token ke database
$queryToken = "INSERT INTO api_tokens
               (user_id, token_hash, expires_at)
               VALUES (?, ?, ?)";

$stmtToken = mysqli_prepare($conn, $queryToken);

if (!$stmtToken) {
    responseJSON(500, "Gagal membuat token");
}

mysqli_stmt_bind_param(
    $stmtToken,
    "iss",
    $user["id"],
    $token_hash,
    $expires_at
);

if (!mysqli_stmt_execute($stmtToken)) {
    mysqli_stmt_close($stmtToken);
    responseJSON(500, "Gagal menyimpan token");
}

mysqli_stmt_close($stmtToken);

// Mengirim token ke pengguna
responseJSON(200, "Login berhasil", [
    "id" => $user["id"],
    "nama" => $user["nama"],
    "username" => $user["username"],
    "token" => $token,
    "berlaku_sampai" => $expires_at
]);

?>