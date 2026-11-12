
<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: ../index.php");
    exit;
}

require_once "../config/database.php";

$user_id = (int) $_SESSION["user_id"];
$activePage = "financing";

// Mengambil data pembiayaan aktif
$query = "SELECT * FROM financings WHERE user_id = ? LIMIT 1";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$pembiayaan = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

// Mengambil pengajuan terbaru
$queryPengajuan = "SELECT * FROM financing_applications
                   WHERE user_id = ?
                   ORDER BY tanggal_pengajuan DESC";

$stmtPengajuan = mysqli_prepare($conn, $queryPengajuan);
mysqli_stmt_bind_param($stmtPengajuan, "i", $user_id);
mysqli_stmt_execute($stmtPengajuan);

$resultPengajuan = mysqli_stmt_get_result($stmtPengajuan);
$pengajuan = [];

while ($row = mysqli_fetch_assoc($resultPengajuan)) {
    $pengajuan[] = $row;
}

mysqli_stmt_close($stmtPengajuan);

// Riwayat pembayaran sementara
$riwayat = [
    ["9", "21 Nov 2026", 2416667, "Akan datang"],
    ["8", "21 Okt 2026", 2416667, "Lunas"],
    ["7", "21 Sep 2026", 2416667, "Lunas"],
    ["6", "21 Agu 2026", 2416667, "Lunas"],
    ["5", "21 Jul 2026", 2416667, "Lunas"],
];

// Pesan dari proses pengajuan
$pesan = $_SESSION["pesan_pembiayaan"] ?? "";
unset($_SESSION["pesan_pembiayaan"]);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembiayaan - Bank Iman Syariah</title>
    <link rel="stylesheet" href="../assets/css/style.css">

    <style>
        /* STATUS PENGAJUAN */

        .payment-status.disetujui {
            background: #dcfce7;
            color: #15803d;
            border: 1px solid #bbf7d0;
        }

        .payment-status.menunggu {
            background: #fef3c7;
            color: #b45309;
            border: 1px solid #fde68a;
        }

        .payment-status.ditolak {
            background: #fee2e2;
            color: #b91c1c;
            border: 1px solid #fecaca;
        }
    </style>
</head>

<body>

<div class="app-layout financing-container">

    <?php include "../includes/sidebar.php"; ?>

    <main class="main-content financing-content">

        <header class="financing-header">
            <div>
                <h1>Pembiayaan</h1>
                <p>Pantau pembiayaan dan jadwal angsuran Anda.</p>
            </div>

            <a href="#form-pengajuan" class="btn-ajukan-top">
                Ajukan pembiayaan
            </a>
        </header>

        <?php if ($pesan): ?>
            <div class="financing-alert">
                <?= htmlspecialchars($pesan); ?>
            </div>
        <?php endif; ?>

        <?php if ($pembiayaan): ?>

            <?php
            $tenor = max((int)$pembiayaan["tenor"], 1);
            $terbayar = min(8, $tenor);
            $persentase = ($terbayar / $tenor) * 100;

            $sisaKewajiban = max(
                0,
                ($pembiayaan["angsuran"] * $tenor) -
                ($pembiayaan["angsuran"] * $terbayar)
            );
            ?>

            <section class="financing-card">

                <div class="financing-card-heading">
                    <div>
                        <h2><?= htmlspecialchars($pembiayaan["jenis_akad"]); ?></h2>
                        <p>
                            ID pembiayaan PBY-<?= str_pad($pembiayaan["id"], 4, "0", STR_PAD_LEFT); ?>
                        </p>
                    </div>

                    <span class="financing-status">
                        <?= htmlspecialchars($pembiayaan["status"]); ?>
                    </span>
                </div>

                <div class="financing-summary">

                    <div class="financing-box">
                        <span>Plafon</span>
                        <h3>Rp <?= number_format($pembiayaan["plafon"], 0, ',', '.'); ?></h3>
                    </div>

                    <div class="financing-box">
                        <span>Margin</span>
                        <h3><?= htmlspecialchars($pembiayaan["margin"]); ?>% per tahun</h3>
                    </div>

                    <div class="financing-box">
                        <span>Tenor</span>
                        <h3><?= $tenor; ?> bulan</h3>
                    </div>

                    <div class="financing-box">
                        <span>Angsuran per bulan</span>
                        <h3>Rp <?= number_format($pembiayaan["angsuran"], 0, ',', '.'); ?></h3>
                    </div>

                </div>

                <div class="financing-progress">
                    <div class="progress-track">
                        <div class="progress-fill"
                             style="width: <?= $persentase; ?>%;">
                        </div>
                    </div>

                    <div class="progress-info">
                        <span><?= $terbayar; ?> dari <?= $tenor; ?> angsuran terbayar</span>
                        <strong>Sisa kewajiban Rp <?= number_format($sisaKewajiban, 0, ',', '.'); ?></strong>
                    </div>
                </div>

            </section>

        <?php else: ?>

            <section class="financing-empty">
                <h2>Belum Ada Pembiayaan Aktif</h2>
                <p>Silakan ajukan pembiayaan melalui formulir di bawah.</p>
            </section>

        <?php endif; ?>


        <div class="financing-columns">

            <!-- Jadwal angsuran -->
            <section class="payment-history">
                <h2>Jadwal angsuran</h2>
                <p>Angsuran terdekat dan yang sudah dibayar.</p>

                <?php if ($pembiayaan): ?>

                    <div class="table-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th>Angsuran ke-</th>
                                    <th>Jatuh tempo</th>
                                    <th>Nominal</th>
                                    <th>Status</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php foreach ($riwayat as $data): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($data[0]); ?></td>
                                        <td><?= htmlspecialchars($data[1]); ?></td>
                                        <td>Rp <?= number_format($data[2], 0, ',', '.'); ?></td>
                                        <td>
                                            <span class="payment-status <?= $data[3] === 'Lunas' ? 'lunas' : 'akan-datang'; ?>">
                                                <?= htmlspecialchars($data[3]); ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <small class="financing-note">
                        Jadwal dan riwayat pembayaran saat ini masih berupa data simulasi.
                    </small>

                <?php else: ?>

                    <p class="empty-text">Belum ada jadwal angsuran.</p>

                <?php endif; ?>
            </section>


            <!-- Form pengajuan -->
            <section class="application-card" id="form-pengajuan">

                <h2>Ajukan pembiayaan baru</h2>
                <p>Simulasi sebelum mengajukan.</p>

                <form action="proses_pengajuan_pembiayaan.php" method="POST">

                    <label for="jenis_akad">Jenis akad</label>
                    <select name="jenis_akad" id="jenis_akad" required>
                        <option value="Murabahah">Murabahah</option>
                        <option value="Mudharabah">Mudharabah</option>
                        <option value="Musyarakah">Musyarakah</option>
                    </select>

                    <label for="plafon">Plafon</label>
                    <input
                        type="number"
                        name="plafon"
                        id="plafon"
                        min="1000000"
                        max="100000000"
                        step="100000"
                        placeholder="Contoh: 25000000"
                        required
                    >

                    <label for="tenor">Tenor</label>
                    <select name="tenor" id="tenor" required>
                        <option value="12">12 bulan</option>
                        <option value="24">24 bulan</option>
                        <option value="36">36 bulan</option>
                        <option value="48">48 bulan</option>
                        <option value="60">60 bulan</option>
                    </select>

                    <div class="estimate-box">
                        <span>Estimasi angsuran</span>
                        <h3 id="estimasi">Rp 0 / bulan</h3>
                        <small>Indikatif, dengan margin 8% per tahun (flat).</small>
                    </div>

                    <button type="submit" class="btn-submit-financing">
                        Ajukan
                    </button>

                </form>

            </section>

        </div>


        <!-- Daftar pengajuan pengguna -->
        <section class="application-history">

            <h2>Pengajuan pembiayaan saya</h2>
            <p>Daftar pengajuan yang sudah tersimpan.</p>

            <?php if (count($pengajuan) > 0): ?>

                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>Nomor</th>
                                <th>Jenis akad</th>
                                <th>Plafon</th>
                                <th>Tenor</th>
                                <th>Estimasi angsuran</th>
                                <th>Status</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php foreach ($pengajuan as $item): ?>

                                <?php
                                $status = $item["status"];

                                if ($status === "Disetujui") {
                                    $statusClass = "disetujui";
                                } elseif ($status === "Ditolak") {
                                    $statusClass = "ditolak";
                                } elseif ($status === "Menunggu Persetujuan") {
                                    $statusClass = "menunggu";
                                } else {
                                    $statusClass = "akan-datang";
                                }
                                ?>

                                <tr>
                                    <td>PPA-<?= str_pad($item["id"], 4, "0", STR_PAD_LEFT); ?></td>
                                    <td><?= htmlspecialchars($item["jenis_akad"]); ?></td>
                                    <td>Rp <?= number_format($item["plafon"], 0, ',', '.'); ?></td>
                                    <td><?= (int)$item["tenor"]; ?> bulan</td>
                                    <td>Rp <?= number_format($item["estimasi_angsuran"], 0, ',', '.'); ?></td>
                                    <td>
                                        <span class="payment-status <?= $statusClass; ?>">
                                            <?= htmlspecialchars($status); ?>
                                        </span>
                                    </td>
                                </tr>

                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

            <?php else: ?>

                <p class="empty-text">Belum ada pengajuan pembiayaan.</p>

            <?php endif; ?>

        </section>

    </main>
</div>


<script>
const plafonInput = document.getElementById("plafon");
const tenorInput = document.getElementById("tenor");
const estimasiText = document.getElementById("estimasi");

function hitungEstimasi() {
    const plafon = Number(plafonInput.value);
    const tenor = Number(tenorInput.value);

    const margin = 8;
    const total = plafon * (1 + (margin / 100 * tenor / 12));
    const angsuran = tenor > 0 ? total / tenor : 0;

    estimasiText.textContent = "Rp " +
        Math.round(angsuran).toLocaleString("id-ID") +
        " / bulan";
}

plafonInput.addEventListener("input", hitungEstimasi);
tenorInput.addEventListener("change", hitungEstimasi);
</script>

</body>
</html>
