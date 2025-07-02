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

    // Ambil data sewa yang akan diedit
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

    // Ambil daftar barang yang tersedia
    // Ambil daftar barang (termasuk yang sedang disewa ini)
    $daftar_barang = $barangModel->getAll([
        'status' => 1, 
        'include_current' => $sewa['id_barang'] // Sertakan barang yang sedang disewa
    ]);

    // Ambil daftar penyewa
    $daftar_penyewa = $penyewaModel->getAll();

    $username = $_SESSION['user']['username'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OnlyRent - Edit Sewa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/admin/index.css">
    <style>
        .price-display {
            font-size: 1.2rem;
            font-weight: bold;
            color: #0d6efd;
            margin-top: 5px;
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
                                <i class="fas fa-calendar-alt me-2"></i>
                                Edit Sewa - ID: <?php echo htmlspecialchars($sewa['id_sewa']); ?>
                            </h4>
                        </div>
                        <div class="card-body">
                            <form id="sewaForm" action="/controllers/SewaController.php?action=update" method="POST">
                                <input type="hidden" name="id_sewa" value="<?php echo htmlspecialchars($sewa['id_sewa']); ?>">

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <select class="form-select" id="id_barang" name="id_barang" required>
                                                <option value="" disabled>Pilih Barang</option>
                                                <?php foreach ($daftar_barang as $barang): ?>
                                                    <option value="<?php echo $barang['id_barang']; ?>" 
                                                        data-harga="<?php echo $barang['harga_sewa']; ?>"
                                                        <?php echo ($barang['id_barang'] == $sewa['id_barang']) ? 'selected' : ''; ?>
                                                        <?php echo ($barang['status'] == 0 && $barang['id_barang'] != $sewa['id_barang']) ? 'disabled' : ''; ?>>
                                                        <?php echo htmlspecialchars($barang['nama_barang'] . ' - Rp ' . number_format($barang['harga_sewa'], 0, ',', '.')); ?>
                                                        <?php echo ($barang['id_barang'] == $sewa['id_barang']) ? ' (Sedang disewa)' : ''; ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <label for="id_barang">Barang Disewa</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <select class="form-select" id="id_penyewa" name="id_penyewa" required>
                                                <option value="" disabled>Pilih Penyewa</option>
                                                <?php foreach ($daftar_penyewa as $penyewa): ?>
                                                    <option value="<?php echo $penyewa['id_penyewa']; ?>"
                                                        <?php echo ($penyewa['id_penyewa'] == $sewa['id_penyewa']) ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($penyewa['nama']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <label for="id_penyewa">Penyewa</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <input type="date" class="form-control" id="tanggal_sewa" name="tanggal_sewa" 
                                                   value="<?php echo date('Y-m-d', strtotime($sewa['tanggalSewa'])); ?>" required>
                                            <label for="tanggal_sewa">Tanggal Sewa</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <input type="date" class="form-control" id="tanggal_kembali" name="tanggal_kembali" 
                                                   value="<?php echo date('Y-m-d', strtotime($sewa['tanggalKembali'])); ?>" required>
                                            <label for="tanggal_kembali">Tanggal Kembali</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <select class="form-select" id="status" name="status" required>
                                                <option value="0" <?php echo ($sewa['status'] == 0) ? 'selected' : ''; ?>>Dibatalkan</option>
                                                <option value="1" <?php echo ($sewa['status'] == 1) ? 'selected' : ''; ?>>Aktif</option>
                                                <option value="2" <?php echo ($sewa['status'] == 2) ? 'selected' : ''; ?>>Selesai</option>
                                            </select>
                                            <label for="status">Status Sewa</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card">
                                            <div class="card-body">
                                                <h5>Rincian Harga</h5>
                                                <p>Harga per hari: <span id="hargaPerHari">Rp <?php echo number_format($barangModel->getById($sewa['id_barang'])['harga_sewa'], 0, ',', '.'); ?></span></p>
                                                <p>Lama sewa: <span id="lamaSewa">
                                                    <?php 
                                                        $start = new DateTime($sewa['tanggalSewa']);
                                                        $end = new DateTime($sewa['tanggalKembali']);
                                                        $diff = $start->diff($end);
                                                        echo $diff->days . ' hari';
                                                    ?>
                                                </span></p>
                                                <h5>Total Bayar: <span id="totalBayar">Rp <?php echo number_format($sewa['totalBayar'], 0, ',', '.'); ?></span></h5>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between align-items-center mt-4">
                                    <a href="tabel_sewa.php" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left me-2"></i>
                                        Kembali
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i>
                                        Update Sewa
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

        // Calculate rental price
        const idBarang = document.getElementById('id_barang');
        const tanggalSewa = document.getElementById('tanggal_sewa');
        const tanggalKembali = document.getElementById('tanggal_kembali');
        const hargaPerHari = document.getElementById('hargaPerHari');
        const lamaSewa = document.getElementById('lamaSewa');
        const totalBayar = document.getElementById('totalBayar');
        
        function calculatePrice() {
            const harga = idBarang.selectedOptions[0]?.dataset.harga || 0;
            const startDate = new Date(tanggalSewa.value);
            const endDate = new Date(tanggalKembali.value);
            
            // Format harga per hari
            hargaPerHari.textContent = 'Rp ' + parseInt(harga).toLocaleString('id-ID');
            
            if (tanggalSewa.value && tanggalKembali.value && startDate <= endDate) {
                const diffTime = Math.abs(endDate - startDate);
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                const total = diffDays * harga;
                
                lamaSewa.textContent = diffDays + ' hari';
                totalBayar.textContent = 'Rp ' + total.toLocaleString('id-ID');
            } else {
                lamaSewa.textContent = '0 hari';
                totalBayar.textContent = 'Rp 0';
            }
        }
        
        // Event listeners for price calculation
        idBarang.addEventListener('change', calculatePrice);
        tanggalSewa.addEventListener('change', function() {
            if (this.value) {
                tanggalKembali.min = this.value;
            }
            calculatePrice();
        });
        tanggalKembali.addEventListener('change', calculatePrice);
        
        // Form validation
        document.getElementById('sewaForm').addEventListener('submit', function(e) {
            // Validate dates
            const startDate = new Date(tanggalSewa.value);
            const endDate = new Date(tanggalKembali.value);
            
            if (endDate <= startDate) {
                e.preventDefault();
                alert('Tanggal kembali harus setelah tanggal sewa!');
                return false;
            }
            
            return true;
        });

        // Initialize price calculation
        calculatePrice();
    });

    document.addEventListener('DOMContentLoaded', function() {
    const originalBarangId = <?php echo $sewa['id_barang']; ?>;
    const barangSelect = document.getElementById('id_barang');
    let barangChanged = false;

    barangSelect.addEventListener('change', function() {
        barangChanged = true;
        calculatePrice();
    });

    // Jika form disubmit tanpa mengubah barang, kembalikan ke nilai asli
    document.getElementById('sewaForm').addEventListener('submit', function(e) {
        if (!barangChanged) {
            const originalOption = barangSelect.querySelector(`option[value="${originalBarangId}"]`);
            if (originalOption) {
                originalOption.selected = true;
            }
        }
        // ... validasi lainnya ...
    });
});
    </script>
</body>
</html>