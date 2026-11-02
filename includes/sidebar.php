<?php
$activePage = $activePage ?? "";
?>

<aside class="main-sidebar">
    <h2>Bank Iman Syariah</h2>

    <p class="sidebar-label">MENU UTAMA</p>

    <a href="dashboard.php"
       class="<?= $activePage === 'dashboard' ? 'active' : '' ?>">
        Dashboard
    </a>

    <a href="accounts.php"
       class="<?= $activePage === 'accounts' ? 'active' : '' ?>">
        Rekening
    </a>

    <a href="transactions.php"
       class="<?= $activePage === 'transactions' ? 'active' : '' ?>">
        Transaksi
    </a>

    <a href="financing.php"
       class="<?= $activePage === 'financing' ? 'active' : '' ?>">
        Pembiayaan
    </a>

    <a href="zakat.php"
       class="<?= $activePage === 'zakat' ? 'active' : '' ?>">
        Zakat
    </a>

    <a href="profile.php"
       class="<?= $activePage === 'profile' ? 'active' : '' ?>">
        Profil
    </a>

    <a href="../index.php">Keluar</a>
</aside>