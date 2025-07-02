<?php
    session_start();

    // Pastikan pengguna sudah login, jika tidak redirect ke halaman login
    if (!isset($_SESSION['user'])) {
        header("Location: login.php");
        exit();
    }

    // Ambil data pengguna dari session
    $username = $_SESSION['user']['username']; // Ambil username pengguna

    // Menghubungkan ke database
    require_once('../../config/connect_db.php'); // Asumsikan DB config berada di db_config.php

    try {
        $conn = getDBConnection();
        
        // Ambil jumlah user
        $result = $conn->query("SELECT COUNT(*) AS total_users FROM penyewa");
        $user_data = $result->fetch_assoc();
        $total_users = $user_data['total_users'];

        // Ambil jumlah pemilik barang
        $result = $conn->query("SELECT COUNT(*) AS total_owners FROM pemilik_barang");
        $owner_data = $result->fetch_assoc();
        $total_owners = $owner_data['total_owners'];

        // Ambil jumlah transaksi
        $result = $conn->query("SELECT COUNT(*) AS total_transactions FROM transaksi");
        $transaction_data = $result->fetch_assoc();
        $total_transactions = $transaction_data['total_transactions'];

        // Ambil jumlah barang
        $result = $conn->query("SELECT COUNT(*) AS total_items FROM barang");
        $item_data = $result->fetch_assoc();
        $total_items = $item_data['total_items'];
        
    } catch (Exception $e) {
        // Menangani kesalahan koneksi database
        echo "Terjadi kesalahan: " . $e->getMessage();
        exit;
    }
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OnlyRent - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/admin/index.css">
</head>
<body>
    <!-- Mobile Menu Overlay -->
    <div class="mobile-overlay"></div>

    <!-- Sidebar -->
    <?php include('nav.php'); ?>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Navigation -->
        <?php include('top_nav.php'); ?>

        <!-- Dashboard Content -->
        <div class="content-wrapper">
            <!-- Stats Cards -->
            <div class="row stats-row animate-fade-in">
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card stats-card visitors-card text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title">Jumlah Penyewa</h6>
                                    <h3 class="mb-0"><?php echo number_format($total_users); ?></h3>
                                </div>
                                <i class="fas fa-users fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card stats-card sales-card text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title">Jumlah Pemilik Barang</h6>
                                    <h3 class="mb-0"><?php echo number_format($total_owners); ?></h3>
                                </div>
                                <i class="fas fa-chart-line fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card stats-card subscribers-card text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title">Jumlah Transaksi</h6>
                                    <h3 class="mb-0"><?php echo number_format($total_transactions); ?></h3>
                                </div>
                                <i class="fas fa-chart-pie fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card stats-card orders-card text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title">Jumlah Barang</h6>
                                    <h3 class="mb-0"><?php echo number_format($total_items); ?></h3>
                                </div>
                                <i class="fas fa-shopping-cart fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Management Section -->
            <div class="row animate-fade-in">
                <div class="col-12">
                    <div class="card management-card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-cogs me-2"></i>
                                Manajemen Data
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-lg-3 col-md-6">
                                    <a href="tambah_user.php" class="btn btn-primary management-btn w-100">
                                        <i class="fas fa-user-plus"></i>
                                        <span class="btn-text">Tambah User</span>
                                    </a>
                                </div>
                                <div class="col-lg-3 col-md-6">
                                    <a href="tambah_barang.php" class="btn btn-success management-btn w-100">
                                        <i class="fas fa-box"></i>
                                        <span class="btn-text">Tambah Barang</span>
                                    </a>
                                </div>
                                <div class="col-lg-3 col-md-6">
                                    <a href="tambah_pemilik.php" class="btn btn-warning management-btn w-100">
                                        <i class="fas fa-user-tie"></i>
                                        <span class="btn-text">Tambah Pemilik</span>
                                    </a>
                                </div>
                                <div class="col-lg-3 col-md-6">
                                    <a href="tambah_penyewa.php" class="btn btn-info management-btn w-100">
                                        <i class="fas fa-user-check"></i>
                                        <span class="btn-text">Tambah Penyewa</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Mobile Menu Toggle
        document.querySelector('.mobile-menu-toggle').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.add('active');
            document.querySelector('.mobile-overlay').classList.add('active');
        });

        // Close Sidebar
        document.querySelector('.mobile-overlay').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.remove('active');
            this.classList.remove('active');
        });

        // Toggle Mobile Search
        document.querySelector('.search-toggle').addEventListener('click', function() {
            document.querySelector('.mobile-search-box').classList.toggle('d-none');
        });

        // Close sidebar when clicking on nav links (for mobile)
        document.querySelectorAll('.sidebar .nav-link').forEach(link => {
            link.addEventListener('click', function() {
                document.querySelector('.sidebar').classList.remove('active');
                document.querySelector('.mobile-overlay').classList.remove('active');
            });
        });

        // Responsive adjustments
        function handleResponsive() {
            const screenWidth = window.innerWidth;
            const userDropdown = document.querySelector('.dropdown .btn');
            
            if (screenWidth < 576) {
                // Hide button text on extra small screens
                document.querySelectorAll('.btn-text').forEach(text => {
                    text.classList.add('d-none');
                });
            } else {
                // Show button text on larger screens
                document.querySelectorAll('.btn-text').forEach(text => {
                    text.classList.remove('d-none');
                });
            }
        }

        // Run on load and resize
        window.addEventListener('load', handleResponsive);
        window.addEventListener('resize', handleResponsive);
    </script>
</body>
</html>