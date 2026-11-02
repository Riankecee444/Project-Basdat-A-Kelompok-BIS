<?php

header("Content-Type: application/json; charset=UTF-8");

require_once "../config/database.php";

function responseJSON($status, $message, $data = null) {
    http_response_code($status);

    echo json_encode([
        "status" => $status,
        "message" => $message,
        "data" => $data
    ], JSON_PRETTY_PRINT);

    exit;
}

?>