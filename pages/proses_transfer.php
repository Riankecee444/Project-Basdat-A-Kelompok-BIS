
<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: ../index.php");
    exit;
}

require_once "../config/database.php";

$user_id = (int) $_SESSION["user_id"];

function kembali($pesan, $rekening_id)
{
    $_SESSION["pesan_transfer"] = $pesan;

    header("Location: transfer.php?rekening_id=" . (int) $rekening_id);
    exit;
}

// Pastikan hanya menerima POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: accounts.php");
    exit;
}

// Ambil data form
$rekening_id = (int) ($_POST["rekening_id"] ?? 0);
$rekening_tujuan = trim($_POST["rekening_tujuan"] ?? "");
$nominal = filter_var(
    $_POST["nominal"] ?? null,
    FILTER_VALIDATE_INT
);
$keterangan = trim($_POST["keterangan"] ?? "");
$pin = trim($_POST["pin"] ?? "");

// Validasi input
if ($rekening_id <= 0) {
    kembali("Rekening sumber tidak valid.", $rekening_id);
}

if ($rekening_tujuan === "") {
    kembali("Nomor rekening tujuan wajib diisi.", $rekening_id);
}

if ($nominal === false || $nominal <= 0) {
    kembali("Nominal transfer tidak valid.", $rekening_id);
}

if (!preg_match('/^[0-9]{6}$/', $pin)) {
    kembali("PIN harus terdiri dari 6 angka.", $rekening_id);
}

if (strlen($keterangan) > 200) {
    kembali("Keterangan maksimal 200 karakter.", $rekening_id);
}

mysqli_begin_transaction($conn);

try {

    // Ambil rekening sumber dan tujuan sekaligus
    // FOR UPDATE mengunci data selama transaksi berlangsung
    $query = "
        SELECT id, user_id, nomor_rekening, saldo, status
        FROM accounts
        WHERE id = ? OR nomor_rekening = ?
        ORDER BY id ASC
        FOR UPDATE
    ";

    $stmt = mysqli_prepare($conn, $query);

    mysqli_stmt_bind_param(
        $stmt,
        "is",
        $rekening_id,
        $rekening_tujuan
    );

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    $rekening_sumber = null;
    $rekening_penerima = null;

    while ($row = mysqli_fetch_assoc($result)) {

        if ((int) $row["id"] === $rekening_id) {
            $rekening_sumber = $row;
        }

        if ($row["nomor_rekening"] === $rekening_tujuan) {
            $rekening_penerima = $row;
        }
    }

    mysqli_stmt_close($stmt);

    // Validasi rekening sumber
    if (!$rekening_sumber) {
        throw new Exception("Rekening sumber tidak ditemukan.");
    }

    if ((int) $rekening_sumber["user_id"] !== $user_id) {
        throw new Exception("Rekening sumber bukan milik Anda.");
    }

    // Validasi rekening tujuan
    if (!$rekening_penerima) {
        throw new Exception("Rekening tujuan tidak ditemukan.");
    }

    if ((int) $rekening_sumber["id"] === (int) $rekening_penerima["id"]) {
        throw new Exception("Tidak dapat transfer ke rekening sendiri.");
    }

    if ((int) $rekening_sumber["user_id"] === (int) $rekening_penerima["user_id"]) {
        throw new Exception("Transfer harus menuju rekening nasabah lain.");
    }

    if (
        strtolower(trim($rekening_sumber["status"])) !== "aktif" ||
        strtolower(trim($rekening_penerima["status"])) !== "aktif"
    ) {
        throw new Exception("Salah satu rekening tidak aktif.");
    }

    // Cek saldo
    if ((float) $rekening_sumber["saldo"] < $nominal) {
        throw new Exception("Saldo tidak mencukupi.");
    }

    // Validasi PIN nasabah
    $stmtPin = mysqli_prepare(
        $conn,
        "SELECT pin_transaksi FROM users WHERE id = ? LIMIT 1"
    );

    mysqli_stmt_bind_param($stmtPin, "i", $user_id);
    mysqli_stmt_execute($stmtPin);

    $resultPin = mysqli_stmt_get_result($stmtPin);
    $dataPin = mysqli_fetch_assoc($resultPin);

    mysqli_stmt_close($stmtPin);

    if (
        !$dataPin ||
        empty($dataPin["pin_transaksi"]) ||
        !password_verify($pin, $dataPin["pin_transaksi"])
    ) {
        throw new Exception("PIN transaksi salah atau belum dibuat.");
    }

    $id_sumber = (int) $rekening_sumber["id"];
    $id_penerima = (int) $rekening_penerima["id"];

    // Kurangi saldo pengirim
    $updateSumber = mysqli_prepare(
        $conn,
        "UPDATE accounts
         SET saldo = saldo - ?
         WHERE id = ? AND saldo >= ?"
    );

    mysqli_stmt_bind_param(
        $updateSumber,
        "iii",
        $nominal,
        $id_sumber,
        $nominal
    );

    if (!mysqli_stmt_execute($updateSumber)) {
        throw new Exception("Gagal mengurangi saldo pengirim.");
    }

    if (mysqli_stmt_affected_rows($updateSumber) !== 1) {
        throw new Exception("Saldo pengirim gagal diperbarui.");
    }

    mysqli_stmt_close($updateSumber);

    // Tambah saldo penerima
    $updatePenerima = mysqli_prepare(
        $conn,
        "UPDATE accounts SET saldo = saldo + ? WHERE id = ?"
    );

    mysqli_stmt_bind_param(
        $updatePenerima,
        "ii",
        $nominal,
        $id_penerima
    );

    if (!mysqli_stmt_execute($updatePenerima)) {
        throw new Exception("Gagal menambahkan saldo penerima.");
    }

    if (mysqli_stmt_affected_rows($updatePenerima) !== 1) {
        throw new Exception("Saldo penerima gagal diperbarui.");
    }

    mysqli_stmt_close($updatePenerima);

    // Buat kode transaksi
    $referensi = "TRF" . date("YmdHis") . random_int(100, 999);

    $kodeKeluar = "OUT" . date("YmdHis") . random_int(100, 999);
    $kodeMasuk = "IN" . date("YmdHis") . random_int(100, 999);

    if ($keterangan === "") {
        $keterangan = "Transfer antar rekening";
    }

    // Catat transaksi keluar
    $jenisKeluar = "keluar";

    $insertKeluar = mysqli_prepare(
        $conn,
        "INSERT INTO transactions
        (
            account_id,
            rekening_lawan_id,
            kode_transaksi,
            referensi_transfer,
            keterangan,
            jenis,
            nominal
        )
        VALUES (?, ?, ?, ?, ?, ?, ?)"
    );

    mysqli_stmt_bind_param(
        $insertKeluar,
        "iissssd",
        $id_sumber,
        $id_penerima,
        $kodeKeluar,
        $referensi,
        $keterangan,
        $jenisKeluar,
        $nominal
    );

    if (!mysqli_stmt_execute($insertKeluar)) {
        throw new Exception("Gagal mencatat transaksi keluar.");
    }

    mysqli_stmt_close($insertKeluar);

    // Catat transaksi masuk
    $jenisMasuk = "masuk";

    $insertMasuk = mysqli_prepare(
        $conn,
        "INSERT INTO transactions
        (
            account_id,
            rekening_lawan_id,
            kode_transaksi,
            referensi_transfer,
            keterangan,
            jenis,
            nominal
        )
        VALUES (?, ?, ?, ?, ?, ?, ?)"
    );

    mysqli_stmt_bind_param(
        $insertMasuk,
        "iissssd",
        $id_penerima,
        $id_sumber,
        $kodeMasuk,
        $referensi,
        $keterangan,
        $jenisMasuk,
        $nominal
    );

    if (!mysqli_stmt_execute($insertMasuk)) {
        throw new Exception("Gagal mencatat transaksi masuk.");
    }

    mysqli_stmt_close($insertMasuk);

    // Simpan seluruh perubahan
    mysqli_commit($conn);

    kembali(
        "Transfer berhasil! Referensi: " . $referensi,
        $rekening_id
    );

} catch (Throwable $e) {

    mysqli_rollback($conn);

    error_log("Transfer error: " . $e->getMessage());

    kembali(
        "Transfer gagal: " . $e->getMessage(),
        $rekening_id
    );
}
