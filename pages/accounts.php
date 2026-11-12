<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: ../index.php");
    exit;
}

require_once "../config/database.php";

$user_id = $_SESSION["user_id"];
$activePage = "accounts";

$query = "SELECT id, nomor_rekening, jenis_akad, saldo, status
          FROM accounts
          WHERE user_id = ?
          ORDER BY id ASC";

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
    <title>Rekening - Bank Iman Syariah</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<div class="app-layout">

    <?php include "../includes/sidebar.php"; ?>

    <main class="main-content accounts-page">

        <header class="page-header">
            <div>
                <h1>Rekening</h1>
                <p>Kelola rekening tabungan syariah Anda.</p>
            </div>

            <button class="btn-primary" type="button"
                    onclick="bukaFormRekening()">
                + Buka rekening
            </button>
        </header>

        <?php if (isset($_GET["success"])): ?>
            <div class="account-alert success">
                Rekening berhasil dibuka!
            </div>
        <?php elseif (isset($_GET["error"])): ?>
            <div class="account-alert error">
                Pembukaan rekening gagal. Silakan coba lagi.
            </div>
        <?php endif; ?>

        <div id="formRekening" class="form-rekening" style="display:none;">
            <h2>Form Pembukaan Rekening</h2>

            <form action="proses_rekening.php" method="POST">
                <label for="jenis_akad">Jenis Akad</label>

                <select name="jenis_akad" id="jenis_akad" required>
                    <option value="">Pilih jenis akad</option>
                    <option value="Wadiah">Wadiah</option>
                    <option value="Mudharabah">Mudharabah</option>
                </select>

                <div class="form-rekening-actions">
                    <button type="submit" class="btn-primary">
                        Ajukan Pembukaan
                    </button>

                    <button type="button" class="btn-cancel"
                            onclick="tutupFormRekening()">
                        Batal
                    </button>
                </div>
            </form>
        </div>

        <section class="accounts-section">
            <h2>Rekening saya</h2>
            <p>Jenis akad dan saldo rekening Anda.</p>

            <div class="account-grid">

                <?php if (count($rekening) > 0): ?>

                    <?php foreach ($rekening as $r): ?>

                        <div class="account-card">

                            <div class="account-top">
                                <h3>
                                    Tabungan iB <?= htmlspecialchars($r["jenis_akad"]); ?>
                                </h3>

                                <span class="akad-badge">
                                    <?= htmlspecialchars($r["jenis_akad"]); ?>
                                </span>
                            </div>

                            <p class="account-label">Saldo</p>

                            <h2 class="account-balance">
                                Rp <?= number_format($r["saldo"], 0, ',', '.'); ?>
                            </h2>

                            <div class="account-detail">

                                <div>
                                    <small>No. rekening</small>
                                    <strong>
                                        <?= htmlspecialchars($r["nomor_rekening"]); ?>
                                    </strong>
                                </div>

                                <div>
                                    <small>Jenis akad</small>
                                    <strong>
                                        <?= htmlspecialchars($r["jenis_akad"]); ?>
                                    </strong>
                                </div>

                                <div>
                                    <small>ID rekening</small>
                                    <strong>
                                        <?= (int) $r["id"]; ?>
                                    </strong>
                                </div>

                                <div>
                                    <small>Status</small>
                                    <strong class="status-active">
                                        <?= htmlspecialchars($r["status"]); ?>
                                    </strong>
                                </div>

                            </div>

                            <div class="account-actions">
                                <a href="transactions.php">Lihat mutasi</a>

                                <a href="detail_rekening.php?id=<?= (int) $r["id"]; ?>">
                                    Lihat detail
                                </a>
                            </div>

                        <div class="account-cash-actions">
    <a href="setor_tunai.php">Setor tunai</a>

    <a href="tarik_tunai.php">Tarik tunai</a>

    <?php if (strtolower(trim($r["status"])) === "aktif"): ?>
        <a href="transfer.php?rekening_id=<?= (int) $r["id"]; ?>"
           class="transfer-account-button">
            Transfer
        </a>
    <?php endif; ?>
</div>

                        </div>

                    <?php endforeach; ?>

                <?php else: ?>

                    <div class="empty-account">
                        Anda belum memiliki rekening.
                    </div>

                <?php endif; ?>

            </div>
        </section>

        <section class="akad-section">
            <h2>Mengenal akad rekening</h2>
            <p>Perbedaan akad pada tabungan syariah.</p>

            <div class="akad-grid">

                <div class="akad-info">
                    <span class="akad-badge">Wadiah</span>
                    <p>
                        Titipan dana yang aman dan dapat diambil
                        kapan saja. Bank boleh memberikan bonus
                        secara sukarela, tanpa dijanjikan di awal.
                    </p>
                </div>

                <div class="akad-info">
                    <span class="akad-badge">Mudharabah</span>
                    <p>
                        Kerja sama pengelolaan dana. Keuntungan
                        dibagi sesuai nisbah yang disepakati
                        di awal.
                    </p>
                </div>

            </div>
        </section>

    </main>
</div>

<script>
function bukaFormRekening() {
    document.getElementById("formRekening").style.display = "block";
}

function tutupFormRekening() {
    document.getElementById("formRekening").style.display = "none";
}
</script>

</body>
</html>