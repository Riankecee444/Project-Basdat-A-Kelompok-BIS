<?php

require_once "config.php";

function cekToken()
{
    global $conn;

    // Mengambil token dari header Authorization
    $headers = getallheaders();
    $authorization = $headers["Authorization"]
        ?? $headers["authorization"]
        ?? "";

    if (!preg_match('/Bearer\s+(.+)/i', $authorization, $matches)) {
        responseJSON(401, "Token tidak ditemukan");
    }

    $token = trim($matches[1]);

    // Mengubah token menjadi hash
    $token_hash = hash("sha256", $token);

    // Memeriksa token di database
    $query = "SELECT
                api_tokens.user_id,
                users.nama,
                users.username
              FROM api_tokens
              JOIN users ON api_tokens.user_id = users.id
              WHERE api_tokens.token_hash = ?
              AND api_tokens.expires_at > NOW()
              LIMIT 1";

    $stmt = mysqli_prepare($conn, $query);

    if (!$stmt) {
        responseJSON(500, "Gagal memeriksa token");
    }

    mysqli_stmt_bind_param($stmt, "s", $token_hash);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);

    mysqli_stmt_close($stmt);

    if (!$user) {
        responseJSON(401, "Token tidak valid atau sudah kedaluwarsa");
    }

    return $user;
}

?>