<?php
    session_start();
    if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 1) {
        header("Location: ../../login.php");
        exit();
    }
    $username = $_SESSION['user']['username'];

    require_once '../../config/connect_db.php';
    $conn = getDBConnection();

    $sewa_query = "SELECT s.id_sewa, s.tanggalSewa, s.tanggalKembali, s.totalBayar, 
                          b.nama_barang, p.nama as nama_penyewa
                   FROM sewa s
                   JOIN barang b ON s.id_barang = b.id_barang
                   JOIN penyewa p ON s.id_penyewa = p.id_penyewa
                   WHERE s.status = 1 AND 
                   NOT EXISTS (SELECT 1 FROM transaksi t WHERE t.id_sewa = s.id_sewa)";
    $sewa_result = $conn->query($sewa_query);
    $daftar_sewa = [];
    if ($sewa_result && $sewa_result->num_rows > 0) {
        while ($row = $sewa_result->fetch_assoc()) {
            $daftar_sewa[] = $row;
        }
    }

    $conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OnlyRent - Tambah Transaksi</title>
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
        .preview-image {
            max-width: 200px;
            max-height: 200px;
            margin-top: 10px;
            display: none;
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
                                <i class="fas fa-money-bill-wave"></i>
                                Tambah Transaksi Pembayaran
                            </h4>
                        </div>
                        <div class="card-body">
                            <form id="transaksiForm" action="/controllers/TransaksiController.php?action=create" method="POST" enctype="multipart/form-data" novalidate>
                                <div class="mb-3">
                                    <label for="id_sewa" class="form-label">Sewa yang Dibayar</label>
                                    <select class="form-select" id="id_sewa" name="id_sewa" required>
                                        <option value="" selected disabled>Pilih Sewa</option>
                                        <?php foreach ($daftar_sewa as $sewa): ?>
                                            <option value="<?php echo $sewa['id_sewa']; ?>" data-total="<?php echo $sewa['totalBayar']; ?>">
                                                <?php echo htmlspecialchars(
                                                    '#' . $sewa['id_sewa'] . ' - ' . $sewa['nama_barang'] . 
                                                    ' (Penyewa: ' . $sewa['nama_penyewa'] . ') - ' .
                                                    'Rp ' . number_format($sewa['totalBayar'], 0, ',', '.')
                                                ); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="invalid-feedback">Silakan pilih sewa</div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="bukti_pembayaran" class="form-label">Bukti Pembayaran</label>
                                    <input type="file" class="form-control" id="bukti_pembayaran" name="bukti_pembayaran" accept="image/*" required>
                                    <div class="invalid-feedback">Harap upload bukti pembayaran (format gambar)</div>
                                    <img id="imagePreview" class="preview-image img-thumbnail" src="#" alt="Preview Bukti Pembayaran">
                                </div>
                                
                                <div class="mb-3">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5>Rincian Pembayaran</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <p>Total Sewa: <span id="totalSewa">Rp 0</span></p>
                                                    <p>Biaya Admin (12.5%): <span id="biayaAdmin">Rp 0</span></p>
                                                </div>
                                                <div class="col-md-6 text-end">
                                                    <h4>Total Bayar: <span id="totalBayar">Rp 0</span></h4>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between align-items-center mt-4">
                                    <a href="tabel_transaksi.php" class="btn btn-outline-secondary">
                                        <i class="fas fa-arrow-left me-2"></i>
                                        Kembali
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i>
                                        Simpan Transaksi
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

    // Image preview
    document.getElementById('bukti_pembayaran').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(event) {
                const preview = document.getElementById('imagePreview');
                preview.src = event.target.result;
                preview.style.display = 'block';
            }
            reader.readAsDataURL(file);
        }
    });

    // Calculate and display payment details
    const idSewa = document.getElementById('id_sewa');
    const totalSewa = document.getElementById('totalSewa');
    const biayaAdmin = document.getElementById('biayaAdmin');
    const totalBayar = document.getElementById('totalBayar');
    
    function calculatePaymentDetails() {
        const total = idSewa.selectedOptions[0]?.dataset.total || 0;
        const adminFee = total * 0.125;
        const totalPayment = parseInt(total) + parseInt(adminFee);
        
        totalSewa.textContent = 'Rp ' + parseInt(total).toLocaleString('id-ID');
        biayaAdmin.textContent = 'Rp ' + parseInt(adminFee).toLocaleString('id-ID');
        totalBayar.textContent = 'Rp ' + totalPayment.toLocaleString('id-ID');
    }
    
    idSewa.addEventListener('change', calculatePaymentDetails);
    
    // Form validation
    document.getElementById('transaksiForm').addEventListener('submit', function(e) {
        // Validate file type
        const fileInput = document.getElementById('bukti_pembayaran');
        if (fileInput.files.length > 0) {
            const fileType = fileInput.files[0].type;
            if (!fileType.match('image.*')) {
                e.preventDefault();
                fileInput.classList.add('is-invalid');
                this.classList.add('was-validated');
                return false;
            }
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
    document.getElementById('id_sewa').addEventListener('blur', validateField);
    document.getElementById('bukti_pembayaran').addEventListener('change', function() {
        if (this.files.length === 0) {
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
    
    // Initialize display
    calculatePaymentDetails();
    </script>
</body>
</html>