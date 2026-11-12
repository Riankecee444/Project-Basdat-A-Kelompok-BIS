<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: ../index.php");
    exit;
}

require_once "../config/database.php";

$user_id = (int) $_SESSION["user_id"];
$activePage = "accounts";

$rekening_id = (int) ($_GET["rekening_id"] ?? 0);

$query = "SELECT id, nomor_rekening, jenis_akad, saldo, status
          FROM accounts
          WHERE id = ? AND user_id = ?
          LIMIT 1";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "ii", $rekening_id, $user_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$rekening = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

if (!$rekening || $rekening["status"] !== "Aktif") {
    header("Location: accounts.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transfer - Bank Iman Syariah</title>
    <link rel="stylesheet" href="../assets/css/style.css">

    <style>
        .transfer-page {
            padding: 30px;
        }

        .transfer-header {
            margin-bottom: 25px;
        }

        .transfer-header h1 {
            color: #174c3d;
            margin-bottom: 8px;
        }

        .transfer-header p {
            color: #718078;
        }

        .transfer-wrapper {
            max-width: 720px;
        }

        .transfer-card {
            background: #ffffff;
            padding: 28px;
            border-radius: 16px;
            box-shadow: 0 4px 18px rgba(0,0,0,.05);
        }

        .transfer-source {
            background: #eef7f2;
            padding: 20px;
            border-radius: 12px;
            margin: 20px 0 25px;
        }

        .transfer-source p {
            margin-top: 8px;
        }

        .transfer-form label {
            display: block;
            font-weight: 600;
            margin: 17px 0 8px;
        }

        .transfer-form input,
        .transfer-form textarea {
            width: 100%;
            padding: 13px;
            border: 1px solid #dce5df;
            border-radius: 9px;
            font-size: 14px;
            font-family: inherit;
        }

        .transfer-form textarea {
            min-height: 90px;
            resize: vertical;
        }

        .transfer-submit {
            width: 100%;
            margin-top: 25px;
            padding: 14px;
            border: none;
            border-radius: 9px;
            background: #087957;
            color: white;
            font-weight: 600;
            cursor: pointer;
        }

        .transfer-submit:hover {
            background: #056447;
        }

 .transfer-back {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 20px;
    padding: 14px 20px;
    background: #087957;
    color: #ffffff;
    border-radius: 9px;
    text-decoration: none;
    font-size: 14px;
    font-weight: 600;
    transition: .2s;
}

.transfer-back:hover {
    background: #056447;
    color: #ffffff;
}

        .transfer-alert {
            padding: 13px;
            margin-bottom: 18px;
            background: #eef7f2;
            color: #174c3d;
            border-radius: 9px;
        }

        @media (max-width: 768px) {
            .transfer-page {
                padding: 18px;
            }

            .transfer-card {
                padding: 20px;
            }
        }
    </style>
</head>

<body>

<div class="app-layout">

    <?php include "../includes/sidebar.php"; ?>

    <main class="main-content transfer-page">

        <header class="transfer-header">
            <h1>Transfer</h1>
            <p>Transfer dana ke sesama rekening Bank Iman Syariah.</p>
        </header>

        <div class="transfer-wrapper">

            <a href="accounts.php" class="transfer-back">
                Kembali ke Rekening
            </a>

            <?php if (isset($_SESSION["pesan_transfer"])): ?>
                <div class="transfer-alert">
                    <?= htmlspecialchars($_SESSION["pesan_transfer"]); ?>
                </div>
                <?php unset($_SESSION["pesan_transfer"]); ?>
            <?php endif; ?>

            <div class="transfer-card">

                <h2>Informasi Rekening Sumber</h2>

                <div class="transfer-source">
                    <strong>
                        Tabungan iB <?= htmlspecialchars($rekening["jenis_akad"]); ?>
                    </strong>

                    <p>
                        No. Rekening:
                        <?= htmlspecialchars($rekening["nomor_rekening"]); ?>
                    </p>

                    <p>
                        Saldo tersedia:
                        <strong>
                            Rp <?= number_format($rekening["saldo"], 0, ',', '.'); ?>
                        </strong>
                    </p>
                </div>

                <h2>Form Transfer</h2>

                <form class="transfer-form"
                      method="POST"
                      action="proses_transfer.php">

                    <input type="hidden"
                           name="rekening_id"
                           value="<?= (int) $rekening["id"]; ?>">

                    <label>Nomor Rekening Tujuan</label>
                    <input type="text"
                           name="rekening_tujuan"
                           maxlength="30"
                           placeholder="Masukkan nomor rekening tujuan"
                           required>

                    <label>Nominal Transfer</label>
                    <input type="number"
                           name="nominal"
                           min="1"
                           step="1"
                           placeholder="Contoh: 100000"
                           required>

                    <label>Keterangan (Opsional)</label>
                    <textarea name="keterangan"
                              maxlength="200"
                              placeholder="Contoh: Pembayaran"></textarea>

                    <label>PIN Transaksi (6 Digit)</label>
                    <input type="password"
                           name="pin"
                           minlength="6"
                           maxlength="6"
                           pattern="[0-9]{6}"
                           inputmode="numeric"
                           placeholder="Masukkan PIN transaksi"
                           required>

                    <button type="submit" class="transfer-submit">
                        Lanjutkan Transfer
                    </button>

                </form>

            </div>
        </div>

    </main>

</div>

</body>
</html>
```