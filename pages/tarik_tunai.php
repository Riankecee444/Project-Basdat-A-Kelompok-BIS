<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: ../index.php");
    exit;
}

require_once "../config/database.php";

$user_id = $_SESSION["user_id"];
$activePage = "accounts";

$query = "SELECT id, nomor_rekening, jenis_akad, saldo
          FROM accounts
          WHERE user_id = ? AND status = 'Aktif'";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$rekening = [];

while ($row = mysqli_fetch_assoc($result)) {
    $rekening[] = $row;
}

mysqli_stmt_close($stmt);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tarik Tunai - Bank Iman Syariah</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<div class="app-layout">

    <?php include "../includes/sidebar.php"; ?>

    <main class="main-content">

        <header class="page-header">
            <div>
                <h1>Tarik Tunai</h1>
                <p>Tarik saldo dari rekening Anda.</p>
            </div>
        </header>

        <section class="accounts-section">

            <div class="account-card">

                <h2>Form Tarik Tunai</h2>

                <?php if (isset($_GET["success"])): ?>
                    <p style="color: green;">
                        Tarik tunai berhasil!
                    </p>
                <?php elseif (isset($_GET["error"])): ?>
                    <p style="color: red;">
                        Penarikan gagal. Periksa saldo dan data Anda.
                    </p>
                <?php endif; ?>

                <?php if (count($rekening) > 0): ?>

                    <form action="proses_tarik.php" method="POST">

                        <label>Pilih Rekening</label>

                        <select name="account_id" required>
                            <option value="">Pilih rekening</option>

                            <?php foreach ($rekening as $r): ?>
                                <option value="<?php echo (int) $r["id"]; ?>">
                                    <?php echo htmlspecialchars($r["nomor_rekening"]); ?>
                                    - <?php echo htmlspecialchars($r["jenis_akad"]); ?>
                                    (Rp <?php echo number_format($r["saldo"], 0, ',', '.'); ?>)
                                </option>
                            <?php endforeach; ?>

                        </select>

                        <br><br>

                        <label>Nominal Penarikan (Rp)</label>

                        <input
                            type="number"
                            name="nominal"
                            min="1"
                            step="1"
                            placeholder="Masukkan nominal"
                            required
                        >

                        <br><br>

                        <button type="submit" class="btn-primary">
                            Tarik Sekarang
                        </button>

                    </form>

                <?php else: ?>

                    <p>Anda belum memiliki rekening aktif.</p>

                <?php endif; ?>

            </div>

        </section>

    </main>

</div>

</body>
</html>