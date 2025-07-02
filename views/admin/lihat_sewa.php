<?php
    session_start();
    if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 1) {
        header("Location: ../../login.php");
        exit();
    }

    // Include koneksi database dan model
    require_once '../../config/connect_db.php';
    require_once '../../models/Sewa.php';
    require_once '../../models/Barang.php';
    require_once '../../models/Penyewa.php';

    // Ambil data sewa yang akan dilihat
    if (!isset($_GET['id'])) {
        $_SESSION['error'] = "ID Sewa tidak valid";
        header("Location: tabel_sewa.php");
        exit();
    }

    $db = getDBConnection();
    $sewaModel = new Sewa($db);
    $barangModel = new Barang($db);
    $penyewaModel = new Penyewa($db);
    
    $sewa = $sewaModel->getById($_GET['id']);

    if (!$sewa) {
        $_SESSION['error'] = "Data sewa tidak ditemukan";
        header("Location: tabel_sewa.php");
        exit();
    }

    // Ambil data barang dan penyewa
    $barang = $barangModel->getById($sewa['id_barang']);
    $penyewa = $penyewaModel->getById($sewa['id_penyewa']);

    $username = $_SESSION['user']['username'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OnlyRent - Lihat Data Sewa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/admin/index.css">
    <style>
        .btn-back {
            background-color: #6c757d;
            color: white;
        }
        .btn-back:hover {
            background-color: #5a6268;
            color: white;
        }
        .btn-edit {
            background-color: #0d6efd;
            color: white;
        }
        .btn-edit:hover {
            background-color: #0b5ed7;
            color: white;
        }
        .status-badge {
            padding: 8px 12px;
            border-radius: 20px;
            font-weight: bold;
        }
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        .status-active {
            background-color: #d1ecf1;
            color: #0c5460;
        }
        .status-completed {
            background-color: #d4edda;
            color: #155724;
        }
        .status-cancelled {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <?php include('nav.php'); ?>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Navigation -->
        <?php include('top_nav.php'); ?>

        <!-- Form Content -->
        <div class="content-wrapper">
            <div class="row justify-content-center animate-fade-in">
                <div class="col-lg-8">
                    <!-- Success/Error Messages -->
                    <div id="alertContainer"></div>

                    <div class="card form-card">
                        <div class="card-header">
                            <h4>
                                <i class="fas fa-calendar-alt"></i>
                                Detail Sewa - ID: <?php echo htmlspecialchars($sewa['id_sewa']); ?>
                            </h4>
                        </div>
                        <div class="card-body">
                            <!-- Tampilkan detail sewa -->
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="penyewa" name="penyewa" 
                                            value="<?php echo htmlspecialchars($penyewa['nama']); ?>" disabled>
                                        <label for="penyewa">Penyewa</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="status" name="status" 
                                            value="<?php 
                                                switch($sewa['status']) {
                                                    case 0: echo 'Dibatalkan'; break;
                                                    case 1: echo 'Aktif'; break;
                                                    case 2: echo 'Selesai'; break;
                                                    default: echo 'Menunggu'; break;
                                                }
                                            ?>" disabled>
                                        <label for="status">Status Sewa</label>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="barang" name="barang" 
                                            value="<?php echo htmlspecialchars($barang['nama_barang']); ?>" disabled>
                                        <label for="barang">Barang Disewa</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="harga_sewa" name="harga_sewa" 
                                            value="<?php echo 'Rp ' . number_format($barang['harga_sewa'], 0, ',', '.'); ?>/hari" disabled>
                                        <label for="harga_sewa">Harga Sewa</label>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="tanggal_sewa" name="tanggal_sewa" 
                                            value="<?php echo date('d F Y', strtotime($sewa['tanggalSewa'])); ?>" disabled>
                                        <label for="tanggal_sewa">Tanggal Sewa</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="tanggal_kembali" name="tanggal_kembali" 
                                            value="<?php echo date('d F Y', strtotime($sewa['tanggalKembali'])); ?>" disabled>
                                        <label for="tanggal_kembali">Tanggal Kembali</label>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="lama_sewa" name="lama_sewa" 
                                            value="<?php 
                                                $start = new DateTime($sewa['tanggalSewa']);
                                                $end = new DateTime($sewa['tanggalKembali']);
                                                $diff = $start->diff($end);
                                                echo $diff->days . ' hari';
                                            ?>" disabled>
                                        <label for="lama_sewa">Lama Sewa</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="total_bayar" name="total_bayar" 
                                            value="<?php echo 'Rp ' . number_format($sewa['totalBayar'], 0, ',', '.'); ?>" disabled>
                                        <label for="total_bayar">Total Bayar</label>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mt-4">
                                <a href="tabel_sewa.php" class="btn btn-back">
                                    <i class="fas fa-arrow-left me-2"></i>
                                    Kembali
                                </a>
                                <div>
                                    <?php if ($sewa['status'] == 1): ?>
                                        <a href="SewaController.php?action=update_status&id=<?php echo $sewa['id_sewa']; ?>&status=2" class="btn btn-success me-2">
                                            <i class="fas fa-check-circle me-2"></i>
                                            Tandai Selesai
                                        </a>
                                    <?php endif; ?>
                                    <a href="edit_sewa.php?id=<?php echo $sewa['id_sewa']; ?>" class="btn btn-editt">
                                        <i class="fas fa-edit me-2"></i>
                                        Edit Sewa
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
</body>
</html>