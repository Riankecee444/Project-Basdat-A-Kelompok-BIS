<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: ../index.php");
    exit;
}

require_once "../config/database.php";

$user_id = $_SESSION["user_id"];
$activePage = "accounts";

$rekening_id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$rekening_id) {
    header("Location: accounts.php");
    exit;
}

// Mengambil detail rekening milik pengguna yang sedang login
$query = "SELECT id, nomor_rekening, jenis_akad, saldo, status
          FROM accounts
          WHERE id = ? AND user_id = ?";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "ii", $rekening_id, $user_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$rekening = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

if (!$rekening) {
    header("Location: accounts.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Detail Rekening - Bank Iman Syariah</title>

    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<div class="app-layout">

    <?php include "../includes/sidebar.php"; ?>

    <main class="main-content">

        <header class="page-header">
            <div>
                <h1>Detail Rekening</h1>
                <p>Informasi lengkap rekening tabungan Anda.</p>
            </div>
        </header>

        <section class="accounts-section">

            <div class="account-card">

                <div class="account-top">
                    <h3>
                        Tabungan iB
                        <?php echo htmlspecialchars($rekening["jenis_akad"]); ?>
                    </h3>

                    <span class="akad-badge">
                        <?php echo htmlspecialchars($rekening["jenis_akad"]); ?>
                    </span>
                </div>

                <p>Saldo rekening</p>

                <h2>
                    Rp <?php echo number_format(
                        $rekening["saldo"],
                        0,
                        ',',
                        '.'
                    ); ?>
                </h2>

                <hr>

                <div class="account-detail">

                    <div>
                        <small>ID Rekening</small>
                        <strong>
                            <?php echo (int) $rekening["id"]; ?>
                        </strong>
                    </div>

                    <div>
                        <small>Nomor Rekening</small>
                        <strong>
                            <?php echo htmlspecialchars(
                                $rekening["nomor_rekening"]
                            ); ?>
                        </strong>
                    </div>

                    <div>
                        <small>Jenis Akad</small>
                        <strong>
                            <?php echo htmlspecialchars(
                                $rekening["jenis_akad"]
                            ); ?>
                        </strong>
                    </div>

                    <div>
                        <small>Status Rekening</small>
                        <strong>
                            <?php echo htmlspecialchars(
                                $rekening["status"]
                            ); ?>
                        </strong>
                    </div>

                </div>

                <br>

                <a href="accounts.php" class="btn-primary">
                    Kembali ke Rekening
                </a>

            </div>

        </section>

    </main>

</div>

</body>
</html>