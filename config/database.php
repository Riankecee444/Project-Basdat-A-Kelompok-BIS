<?php

$host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "bank_iman_syariah";

$conn = mysqli_connect($host, $db_user, $db_pass, $db_name);

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

?>