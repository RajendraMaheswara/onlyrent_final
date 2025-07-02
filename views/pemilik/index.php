<?php
session_start();
require_once '../../config/connect_db.php';

// Verify user is logged in as pemilik (role 3)
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 3) {
    header("Location: ../../login.php");
    exit();
}

// Get pemilik ID from session
$id_pemilik = $_SESSION['user']['id_pemilik'] ?? 0;
if ($id_pemilik == 0) {
    $_SESSION['error'] = "Data pemilik tidak valid";
    header("Location: ../../login.php");
    exit();
}

// Database connection
$conn = getDBConnection();

// Get statistics for this pemilik only
// 1. Total Barang
$query_barang = "SELECT COUNT(*) as total FROM barang WHERE id_pemilik = ?";
$stmt_barang = $conn->prepare($query_barang);
$stmt_barang->bind_param("i", $id_pemilik);
$stmt_barang->execute();
$result_barang = $stmt_barang->get_result();
$total_barang = $result_barang->fetch_assoc()['total'] ?? 0;

// 2. Total Sewa
$query_sewa = "SELECT COUNT(*) as total FROM sewa s 
               JOIN barang b ON s.id_barang = b.id_barang 
               WHERE b.id_pemilik = ?";
$stmt_sewa = $conn->prepare($query_sewa);
$stmt_sewa->bind_param("i", $id_pemilik);
$stmt_sewa->execute();
$result_sewa = $stmt_sewa->get_result();
$total_sewa = $result_sewa->fetch_assoc()['total'] ?? 0;

// 3. Total Transaksi
$query_transaksi = "SELECT COUNT(*) as total FROM transaksi t
                    JOIN sewa s ON t.id_sewa = s.id_sewa
                    JOIN barang b ON s.id_barang = b.id_barang
                    WHERE b.id_pemilik = ?";
$stmt_transaksi = $conn->prepare($query_transaksi);
$stmt_transaksi->bind_param("i", $id_pemilik);
$stmt_transaksi->execute();
$result_transaksi = $stmt_transaksi->get_result();
$total_transactions = $result_transaksi->fetch_assoc()['total'] ?? 0;

$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OnlyRent - Dashboard Pemilik</title>
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
                <div class="col-xl-4 col-md-6 mb-4">
                    <div class="card stats-card orders-card text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title">Barang Saya</h6>
                                    <h3 class="mb-0"><?php echo number_format($total_barang); ?></h3>
                                </div>
                                <i class="fas fa-box fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4 col-md-6 mb-4">
                    <div class="card stats-card sales-card text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title">Total Penyewaan</h6>
                                    <h3 class="mb-0"><?php echo number_format($total_sewa); ?></h3>
                                </div>
                                <i class="fas fa-handshake fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4 col-md-6 mb-4">
                    <div class="card stats-card subscribers-card text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title">Total Transaksi</h6>
                                    <h3 class="mb-0"><?php echo number_format($total_transactions); ?></h3>
                                </div>
                                <i class="fas fa-money-bill-wave fa-2x"></i>
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
        document.querySelector('.mobile-menu-toggle')?.addEventListener('click', function() {
            document.querySelector('.sidebar').classList.add('active');
            document.querySelector('.mobile-overlay').classList.add('active');
        });

        // Close Sidebar
        document.querySelector('.mobile-overlay')?.addEventListener('click', function() {
            document.querySelector('.sidebar').classList.remove('active');
            this.classList.remove('active');
        });

        // Toggle Mobile Search
        document.querySelector('.search-toggle')?.addEventListener('click', function() {
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