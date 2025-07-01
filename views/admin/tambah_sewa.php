<?php
    session_start();
    if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 1) {
        header("Location: ../login.php");
        exit();
    }
    $username = $_SESSION['user']['username'];

    // Koneksi ke database
    require_once '../../config/connect_db.php';
    $conn = getDBConnection();

    // Ambil daftar barang yang tersedia untuk dropdown
    $barang_query = "SELECT b.id_barang, b.nama_barang, b.harga_sewa, pb.nama as nama_pemilik 
                     FROM barang b
                     JOIN pemilik_barang pb ON b.id_pemilik = pb.id_pemilik
                     WHERE b.status = 1"; // Hanya barang yang tersedia
    $barang_result = $conn->query($barang_query);
    $daftar_barang = [];
    if ($barang_result && $barang_result->num_rows > 0) {
        while ($row = $barang_result->fetch_assoc()) {
            $daftar_barang[] = $row;
        }
    }

    // Ambil daftar penyewa untuk dropdown
    $penyewa_query = "SELECT p.id_penyewa, p.nama, pg.username 
                      FROM penyewa p
                      JOIN pengguna pg ON p.id_pengguna = pg.id_pengguna";
    $penyewa_result = $conn->query($penyewa_query);
    $daftar_penyewa = [];
    if ($penyewa_result && $penyewa_result->num_rows > 0) {
        while ($row = $penyewa_result->fetch_assoc()) {
            $daftar_penyewa[] = $row;
        }
    }
    $conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OnlyRent - Tambah Sewa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/admin/index.css">
    <style>
        .invalid-feedback {
            display: none;
            width: 100%;
            margin-top: 0.25rem;
            font-size: .875em;
            color: #dc3545;
        }
        .was-validated .form-control:invalid ~ .invalid-feedback,
        .was-validated .form-control:invalid ~ .invalid-feedback,
        .form-control.is-invalid ~ .invalid-feedback {
            display: block;
        }
        .price-display {
            font-size: 1.2rem;
            font-weight: bold;
            color: #0d6efd;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="mobile-overlay"></div>
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
                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    
                    <div class="card form-card">
                        <div class="card-header">
                            <h4>
                                <i class="fas fa-calendar-plus"></i>
                                Tambah Transaksi Sewa
                            </h4>
                        </div>
                        <div class="card-body">
                            <form id="sewaForm" action="/controllers/SewaController.php?action=create" method="POST" novalidate>
                                <div class="mb-3">
                                    <label for="id_barang" class="form-label">Barang yang Disewa</label>
                                    <select class="form-select" id="id_barang" name="id_barang" required>
                                        <option value="" selected disabled>Pilih Barang</option>
                                        <?php foreach ($daftar_barang as $barang): ?>
                                            <option value="<?php echo $barang['id_barang']; ?>" data-harga="<?php echo $barang['harga_sewa']; ?>">
                                                <?php echo htmlspecialchars($barang['nama_barang'] . ' - Rp ' . number_format($barang['harga_sewa'], 0, ',', '.') . '/hari (Pemilik: ' . $barang['nama_pemilik'] . ')'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="invalid-feedback">Silakan pilih barang</div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="id_penyewa" class="form-label">Penyewa</label>
                                    <select class="form-select" id="id_penyewa" name="id_penyewa" required>
                                        <option value="" selected disabled>Pilih Penyewa</option>
                                        <?php foreach ($daftar_penyewa as $penyewa): ?>
                                            <option value="<?php echo $penyewa['id_penyewa']; ?>">
                                                <?php echo htmlspecialchars($penyewa['nama'] . ' (' . $penyewa['username'] . ')'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="invalid-feedback">Silakan pilih penyewa</div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="tanggal_sewa" class="form-label">Tanggal Sewa</label>
                                        <input type="date" class="form-control" id="tanggal_sewa" name="tanggal_sewa" required min="<?php echo date('Y-m-d'); ?>">
                                        <div class="invalid-feedback">Tanggal sewa harus diisi dan tidak boleh tanggal yang sudah lewat</div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="tanggal_kembali" class="form-label">Tanggal Kembali</label>
                                        <input type="date" class="form-control" id="tanggal_kembali" name="tanggal_kembali" required>
                                        <div class="invalid-feedback">Tanggal kembali harus diisi dan setelah tanggal sewa</div>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5>Rincian Harga</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <p>Harga per hari: <span id="hargaPerHari">Rp 0</span></p>
                                                    <p>Lama sewa: <span id="lamaSewa">0 hari</span></p>
                                                </div>
                                                <div class="col-md-6 text-end">
                                                    <h4>Total Bayar: <span id="totalBayar">Rp 0</span></h4>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between align-items-center mt-4">
                                    <a href="tabel_sewa.php" class="btn btn-outline-secondary">
                                        <i class="fas fa-arrow-left me-2"></i>
                                        Kembali
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i>
                                        Simpan Sewa
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
            tanggalKembali.classList.add('is-invalid');
            this.classList.add('was-validated');
            return false;
        }
        
        // Validate other fields
        if (!this.checkValidity()) {
            e.preventDefault();
            this.classList.add('was-validated');
            return false;
        }
        
        return true;
    });

    // Validate fields on blur
    document.getElementById('id_barang').addEventListener('blur', validateField);
    document.getElementById('id_penyewa').addEventListener('blur', validateField);
    document.getElementById('tanggal_sewa').addEventListener('blur', validateField);
    document.getElementById('tanggal_kembali').addEventListener('blur', function() {
        const startDate = new Date(tanggalSewa.value);
        const endDate = new Date(this.value);
        
        if (endDate <= startDate) {
            this.classList.add('is-invalid');
        } else {
            this.classList.remove('is-invalid');
        }
    });

    function validateField(e) {
        const field = e.target;
        if (!field.checkValidity()) {
            field.classList.add('is-invalid');
        } else {
            field.classList.remove('is-invalid');
        }
    }
    
    // Initialize price display
    calculatePrice();
    </script>
</body>
</html>