<?php
    session_start();
    if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 1) {
        header("Location: ../../login.php");
        exit();
    }

    require_once '../../config/connect_db.php';
    require_once '../../models/admin/Transaksi.php';
    require_once '../../models/admin/Sewa.php';
    require_once '../../models/admin/.php';
    require_once '../../models/admin/PemilikBarang.php';
    require_once '../../models/admin/Barang.php';

    if (!isset($_GET['id'])) {
        $_SESSION['error'] = "ID Transaksi tidak valid";
        header("Location: tabel_transaksi.php");
        exit();
    }

    $db = getDBConnection();
    $transaksiModel = new Transaksi($db);
    $sewaModel = new Sewa($db);
    $penyewaModel = new Penyewa($db);
    $pemilikModel = new PemilikBarang($db);
    $barangModel = new Barang($db);
    
    $transaksi = $transaksiModel->getById($_GET['id']);

    if (!$transaksi) {
        $_SESSION['error'] = "Data transaksi tidak ditemukan";
        header("Location: tabel_transaksi.php");
        exit();
    }

    // Get related data
    $sewa = $sewaModel->getById($transaksi['id_sewa']);
    $penyewa = $penyewaModel->getById($sewa['id_penyewa']);
    $barang = $barangModel->getById($sewa['id_barang']);
    $pemilik = $pemilikModel->getById($barang['id_pemilik']);

    $username = $_SESSION['user']['username'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OnlyRent - Edit Transaksi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/admin/index.css">
    <style>
        .readonly-input {
            background-color: #f8f9fa;
            cursor: not-allowed;
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
        .status-success {
            background-color: #d4edda;
            color: #155724;
        }
        .status-failed {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="mobile-overlay"></div>
    <?php include('nav.php'); ?>

    <!-- Main Content -->
    <div class="main-content">
        <?php include('top_nav.php'); ?>

        <div class="content-wrapper">
            <div class="row justify-content-center animate-fade-in">
                <div class="col-lg-8">
                    <!-- Success/Error Messages -->
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <div class="card form-card">
                        <div class="card-header">
                            <h4>
                                <i class="fas fa-money-bill-wave me-2"></i>
                                Edit Transaksi - ID: <?php echo htmlspecialchars($transaksi['id_transaksi']); ?>
                            </h4>
                        </div>
                        <div class="card-body">
                            <form id="transaksiForm" action="/controllers/admin/TransaksiController.php?action=update" method="POST">
                                <input type="hidden" name="id_transaksi" value="<?php echo htmlspecialchars($transaksi['id_transaksi']); ?>">

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <input type="text" class="form-control readonly-input" id="id_sewa" 
                                                   value="<?php echo htmlspecialchars($transaksi['id_sewa']); ?>" readonly>
                                            <label for="id_sewa">ID Sewa</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <input type="text" class="form-control readonly-input" id="tanggal" 
                                                   value="<?php echo date('d M Y H:i', strtotime($transaksi['tanggal'])); ?>" readonly>
                                            <label for="tanggal">Tanggal Transaksi</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <input type="text" class="form-control readonly-input" 
                                                   value="<?php echo htmlspecialchars($barang['nama_barang']); ?>" readonly>
                                            <label>Nama Barang</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <input type="text" class="form-control readonly-input" 
                                                   value="<?php echo htmlspecialchars($penyewa['nama']); ?>" readonly>
                                            <label>Nama Penyewa</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <input type="text" class="form-control readonly-input" 
                                                   value="Rp <?php echo number_format($sewa['totalBayar'], 0, ',', '.'); ?>" readonly>
                                            <label>Total Sewa</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <input type="text" class="form-control readonly-input" 
                                                   value="Rp <?php echo number_format($transaksi['totalBayar'] - $sewa['totalBayar'], 0, ',', '.'); ?>" readonly>
                                            <label>Biaya Admin (12.5%)</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <input type="text" class="form-control readonly-input" 
                                                   value="Rp <?php echo number_format($transaksi['totalBayar'], 0, ',', '.'); ?>" readonly>
                                            <label>Total Bayar</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <select class="form-select" id="status" name="status" required>
                                                <option value="0" <?php echo ($transaksi['status'] == 0) ? 'selected' : ''; ?>>Pending</option>
                                                <option value="1" <?php echo ($transaksi['status'] == 1) ? 'selected' : ''; ?>>Sukses</option>
                                                <option value="2" <?php echo ($transaksi['status'] == 2) ? 'selected' : ''; ?>>Gagal</option>
                                            </select>
                                            <label for="status">Status Transaksi</label>
                                        </div>
                                    </div>
                                </div>

                                <?php if (!empty($transaksi['gambar'])): 
                                    $images = json_decode($transaksi['gambar'], true);
                                    if (is_array($images) && count($images) > 0): ?>
                                        <div class="mb-3">
                                            <label class="form-label">Bukti Pembayaran</label>
                                            <div>
                                                <img src="/assets/images/transaksi/<?php echo htmlspecialchars($images[0]); ?>" 
                                                     class="img-thumbnail" style="max-height: 200px;">
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                <?php endif; ?>

                                <div class="d-flex justify-content-between align-items-center mt-4">
                                    <a href="tabel_transaksi.php" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left me-2"></i>
                                        Kembali
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i>
                                        Update Status
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function viewImage(imageUrl) {
        document.getElementById('modalImage').src = imageUrl;
        const imageModal = new bootstrap.Modal(document.getElementById('imageModal'));
        imageModal.show();
    }
    document.addEventListener('DOMContentLoaded', function() {
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
    });
    </script>
</body>
</html>