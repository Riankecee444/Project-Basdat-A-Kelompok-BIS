<?php

require_once __DIR__ . "/config.php";

responseJSON(200, "API Bank Iman Syariah berhasil terhubung!", [
    "database" => "bank_iman_syariah",
    "status" => "connected"
]);