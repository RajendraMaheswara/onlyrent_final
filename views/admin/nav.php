<?php
    if (!isset($current_page)) {
        $current_page = basename($_SERVER['PHP_SELF']);
    }
?>

<nav class="sidebar animate-slide-in">
    <div class="brand-section">
        <div class="d-flex align-items-center">
            <div class="brand-logo">OR</div>
            <div>
                <h6 class="brand-text">OnlyRent</h6>
                <small class="brand-subtitle">Admin Panel</small>
            </div>
        </div>
    </div>
    
    <ul class="nav nav-pills flex-column mt-3">
        <li class="nav-item">
            <a class="nav-link <?php echo ($current_page == 'index.php') ? 'active' : ''; ?>" href="index.php">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo ($current_page == 'tabel_user.php' || $current_page == 'tambah_user.php' || $current_page == 'edit_user.php' || $current_page == 'lihat_user.php') ? 'active' : ''; ?>" href="tabel_user.php">
                <i class="fas fa-users"></i> Tabel User
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo ($current_page == 'tabel_penyewa.php' || $current_page == 'tambah_penyewa.php' || $current_page == 'edit_penyewa.php' || $current_page == 'lihat_penyewa.php') ? 'active' : ''; ?>" href="tabel_penyewa.php">
                <i class="fas fa-user-check"></i> Tabel Penyewa
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo ($current_page == 'tabel_pemilik.php' || $current_page == 'tambah_pemilik.php' || $current_page == 'edit_pemilik.php' || $current_page == 'lihat_pemilik.php') ? 'active' : ''; ?>" href="tabel_pemilik.php">
                <i class="fas fa-user-tie"></i> Tabel Pemilik
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo ($current_page == 'tabel_barang.php' || $current_page == 'tambah_barang.php' || $current_page == 'edit_barang.php' || $current_page == 'lihat_barang.php') ? 'active' : ''; ?>" href="tabel_barang.php">
                <i class="fas fa-box"></i> Tabel Barang
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo ($current_page == 'tabel_sewa.php' || $current_page == 'tambah_sewa.php' || $current_page == 'edit_sewa.php' || $current_page == 'lihat_sewa.php') ? 'active' : ''; ?>" href="tabel_sewa.php">
                <i class="fas fa-box"></i> Tabel Sewa
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo ($current_page == 'tabel_transaksi.php' || $current_page == 'tambah_transaksi.php' || $current_page == 'edit_transaksi.php' || $current_page == 'lihat_transaksi.php') ? 'active' : ''; ?>" href="tabel_transaksi.php">
                <i class="fas fa-box"></i> Tabel Transaksi
            </a>
        </li>
    </ul>
</nav>