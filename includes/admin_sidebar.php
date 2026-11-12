<?php
$activePage = $activePage ?? "";
$namaAdmin = $_SESSION["nama"] ?? "Administrator";
?>

<aside class="main-sidebar admin-main-sidebar">

    <div class="sidebar-brand">

        <div class="sidebar-brand-logo">
            B
        </div>

        <div class="admin-brand-text">
            <h2>Bank Iman Syariah</h2>
            <small>Admin</small>
        </div>

    </div>

    <p class="sidebar-label">MENU ADMIN</p>

    <nav class="sidebar-menu">

        <a href="dashboard.php"
           class="<?= $activePage === 'dashboard' ? 'active' : '' ?>">
            <span class="menu-dot"></span>
            Dashboard
        </a>

        <a href="nasabah.php"
           class="<?= $activePage === 'nasabah' ? 'active' : '' ?>">
            <span class="menu-dot"></span>
            Data Nasabah
        </a>

        <a href="rekening.php"
           class="<?= $activePage === 'rekening' ? 'active' : '' ?>">
            <span class="menu-dot"></span>
            Data Rekening
        </a>

        <a href="setor_tunai.php"
           class="<?= $activePage === 'setor_tunai' ? 'active' : '' ?>">
            <span class="menu-dot"></span>
            Setor Tunai
        </a>

        <a href="transfer.php"
           class="<?= $activePage === 'transfer' ? 'active' : '' ?>">
            <span class="menu-dot"></span>
            Transfer
        </a>

        <a href="transaksi.php"
           class="<?= $activePage === 'transaksi' ? 'active' : '' ?>">
            <span class="menu-dot"></span>
            Transaksi
        </a>

        <a href="pengajuan_pembiayaan.php"
   class="<?= $activePage === 'pengajuan_pembiayaan' ? 'active' : '' ?>">
    <span class="menu-dot"></span>
    Pengajuan Pembiayaan
</a>

        <a href="laporan.php"
           class="<?= $activePage === 'laporan' ? 'active' : '' ?>">
            <span class="menu-dot"></span>
            Laporan
        </a>

    </nav>

    <div class="sidebar-bottom">

        <div class="sidebar-profile">

            <div class="profile-avatar">
                <?= strtoupper(substr($namaAdmin, 0, 1)); ?>
            </div>

            <div class="profile-info">
                <strong><?= htmlspecialchars($namaAdmin); ?></strong>
                <span>Administrator</span>
            </div>

        </div>

        <a href="../../index.php" class="sidebar-logout">
            Keluar
        </a>

    </div>

</aside>