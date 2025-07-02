<?php 
// Pastikan helpers.php sudah di-load
require_once __DIR__ . '../../../config/helpers.php';

// Default value jika $current_page belum didefinisikan
$current_page = $current_page ?? basename($_SERVER['PHP_SELF']);
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
            <a class="nav-link <?= isActive('index.php', $current_page) ?>" href="index.php">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= isActive(['tabel_user.php', 'tambah_user.php', 'edit_user.php', 'lihat_user.php'], $current_page) ?>" href="tabel_user.php">
                <i class="fas fa-users"></i> Tabel User
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= isActive(['tabel_penyewa.php', 'tambah_penyewa.php', 'edit_penyewa.php', 'lihat_penyewa.php'], $current_page) ?>" href="tabel_penyewa.php">
                <i class="fas fa-user-check"></i> Tabel Penyewa
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= isActive(['tabel_pemilik.php', 'tambah_pemilik.php', 'edit_pemilik.php', 'lihat_pemilik.php'], $current_page) ?>" href="tabel_pemilik.php">
                <i class="fas fa-user-tie"></i> Tabel Pemilik
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= isActive(['tabel_barang.php', 'tambah_barang.php', 'edit_barang.php', 'lihat_barang.php'], $current_page) ?>" href="tabel_barang.php">
                <i class="fas fa-box"></i> Tabel Barang
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= isActive(['tabel_sewa.php', 'tambah_sewa.php', 'edit_sewa.php', 'lihat_sewa.php'], $current_page) ?>" href="tabel_sewa.php">
                <i class="fas fa-handshake"></i> Tabel Sewa
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= isActive(['tabel_transaksi.php', 'tambah_transaksi.php', 'edit_transaksi.php', 'lihat_transaksi.php'], $current_page) ?>" href="tabel_transaksi.php">
                <i class="fas fa-receipt"></i> Tabel Transaksi
            </a>
        </li>
    </ul>
</nav>