<?php
$activePage = $activePage ?? "";

$namaSidebar = $_SESSION["nama"] ?? "Pengguna";
$noNasabah = $_SESSION["no_nasabah"] ?? "NSB-0021";

$user_id = $_SESSION["user_id"] ?? 0;
$fotoSidebar = null;

if ($user_id > 0 && isset($conn)) {
    $stmtSidebar = mysqli_prepare(
        $conn,
        "SELECT foto_profil FROM users WHERE id = ?"
    );

    mysqli_stmt_bind_param($stmtSidebar, "i", $user_id);
    mysqli_stmt_execute($stmtSidebar);

    $hasilSidebar = mysqli_stmt_get_result($stmtSidebar);
    $dataSidebar = mysqli_fetch_assoc($hasilSidebar);

    if ($dataSidebar) {
        $fotoSidebar = $dataSidebar["foto_profil"];
    }

    mysqli_stmt_close($stmtSidebar);
}
?>

<aside class="main-sidebar">

    <div class="sidebar-brand">
        <div class="sidebar-brand-logo">B</div>
        <h2>Bank Iman Syariah</h2>
    </div>

    <p class="sidebar-label">MENU UTAMA</p>

    <nav class="sidebar-menu">

        <a href="dashboard.php"
           class="<?= $activePage === 'dashboard' ? 'active' : '' ?>">
            <span class="menu-dot"></span>
            Beranda
        </a>

        <a href="accounts.php"
           class="<?= $activePage === 'accounts' ? 'active' : '' ?>">
            <span class="menu-dot"></span>
            Rekening
        </a>

        <a href="transactions.php"
           class="<?= $activePage === 'transactions' ? 'active' : '' ?>">
            <span class="menu-dot"></span>
            Transaksi
        </a>

        <a href="financing.php"
           class="<?= $activePage === 'financing' ? 'active' : '' ?>">
            <span class="menu-dot"></span>
            Pembiayaan
        </a>

        <a href="zakat.php"
           class="<?= $activePage === 'zakat' ? 'active' : '' ?>">
            <span class="menu-dot"></span>
            Zakat
        </a>

        <a href="profile.php"
       class="<?= $activePage === 'profile' ? 'active' : '' ?>">
        <span class="menu-dot"></span>Profil
    </a>

    </nav>

    <div class="sidebar-bottom">

        <a href="profile.php"
           class="sidebar-profile <?= $activePage === 'profile' ? 'active' : '' ?>">

          <div class="profile-avatar">
    <?php if (!empty($fotoSidebar)): ?>
        <img
            src="../<?= htmlspecialchars($fotoSidebar); ?>"
            alt="Foto profil"
            class="sidebar-profile-photo"
        >
    <?php else: ?>
        <?= strtoupper(substr($namaSidebar, 0, 1)); ?>
    <?php endif; ?>
</div>

            <div class="profile-info">
                <strong><?= htmlspecialchars($namaSidebar); ?></strong>
                <span><?= htmlspecialchars($noNasabah); ?></span>
            </div>

        </a>

        <a href="../index.php" class="sidebar-logout">
            <span>↪</span>
            Keluar
        </a>

    </div>

</aside>