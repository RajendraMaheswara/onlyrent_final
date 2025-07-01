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

    // Ambil daftar barang yang tersedia
    $barang_query = "SELECT b.id_barang, b.nama_barang, b.harga_sewa, pb.nama as nama_pemilik 
                     FROM barang b
                     JOIN pemilik_barang pb ON b.id_pemilik = pb.id_pemilik
                     WHERE b.status = 1";
    $barang_result = $conn->query($barang_query);
    $daftar_barang = [];
    if ($barang_result && $barang_result->num_rows > 0) {
        while ($row = $barang_result->fetch_assoc()) {
            $daftar_barang[] = $row;
        }
    }

    // Ambil daftar penyewa
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
        .date-picker-icon {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            pointer-events: none;
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
                                <i class="fas fa-exchange-alt"></i>
                                Tambah Transaksi Sewa
                            </h4>
                        </div>
                        <div class="card-body">
                            <form id="transaksiForm" action="/controllers/TransaksiController.php?action=create" method="POST" novalidate>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="id_barang" class="form-label">Barang Disewa</label>
                                        <select class="form-select" id="id_barang" name="id_barang" required>
                                            <option value="" selected disabled>Pilih Barang</option>
                                            <?php foreach ($daftar_barang as $barang): ?>
                                                <option value="<?php echo $barang['id_barang']; ?>" data-harga="<?php echo $barang['harga_sewa']; ?>">
                                                    <?php echo htmlspecialchars($barang['nama_barang'] . ' - ' . $barang['nama_pemilik'] . ' (Rp ' . number_format($barang['harga_sewa'], 0, ',', '.') . '/hari)'); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="invalid-feedback">Silakan pilih barang</div>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
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
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="tanggal_sewa" class="form-label">Tanggal Sewa</label>
                                        <div class="position-relative">
                                            <input type="date" class="form-control" id="tanggal_sewa" name="tanggal_sewa" required>
                                            <i class="fas fa-calendar-alt date-picker-icon"></i>
                                        </div>
                                        <div class="invalid-feedback">Tanggal sewa harus diisi</div>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="tanggal_kembali" class="form-label">Tanggal Kembali</label>
                                        <div class="position-relative">
                                            <input type="date" class="form-control" id="tanggal_kembali" name="tanggal_kembali" required>
                                            <i class="fas fa-calendar-alt date-picker-icon"></i>
                                        </div>
                                        <div class="invalid-feedback">Tanggal kembali harus diisi</div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="jumlah" class="form-label">Jumlah Hari</label>
                                        <input type="number" class="form-control" id="jumlah" name="jumlah" min="1" required>
                                        <div class="invalid-feedback">Jumlah hari harus diisi (minimal 1)</div>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Harga Sewa per Hari</label>
                                        <div class="price-display" id="harga-per-hari">Rp 0</div>
                                        <input type="hidden" id="harga_sewa" name="harga_sewa">
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Total Bayar</label>
                                    <div class="price-display" id="total-bayar">Rp 0</div>
                                    <input type="hidden" id="totalBayar" name="totalBayar">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="status" class="form-label">Status Transaksi</label>
                                    <select class="form-select" id="status" name="status" required>
                                        <option value="0" selected>Pending</option>
                                        <option value="1">Sukses</option>
                                        <option value="2">Gagal</option>
                                    </select>
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

    // Hitung total bayar
    let hargaPerHari = 0;
    
    // Update harga ketika barang dipilih
    document.getElementById('id_barang').addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        hargaPerHari = selectedOption.dataset.harga ? parseInt(selectedOption.dataset.harga) : 0;
        
        document.getElementById('harga-per-hari').textContent = 'Rp ' + hargaPerHari.toLocaleString('id-ID');
        document.getElementById('harga_sewa').value = hargaPerHari;
        
        hitungTotalBayar();
    });
    
    // Update total ketika jumlah hari berubah
    document.getElementById('jumlah').addEventListener('input', hitungTotalBayar);
    
    function hitungTotalBayar() {
        const jumlahHari = parseInt(document.getElementById('jumlah').value) || 0;
        const totalBayar = hargaPerHari * jumlahHari;
        
        document.getElementById('total-bayar').textContent = 'Rp ' + totalBayar.toLocaleString('id-ID');
        document.getElementById('totalBayar').value = totalBayar;
    }
    
    // Validasi tanggal kembali tidak boleh sebelum tanggal sewa
    document.getElementById('tanggal_kembali').addEventListener('change', function() {
        const tanggalSewa = document.getElementById('tanggal_sewa').value;
        const tanggalKembali = this.value;
        
        if (tanggalSewa && tanggalKembali && new Date(tanggalKembali) < new Date(tanggalSewa)) {
            alert('Tanggal kembali tidak boleh sebelum tanggal sewa');
            this.value = '';
        }
    });
    
    // Form validation
    document.getElementById('transaksiForm').addEventListener('submit', function(e) {
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
    document.getElementById('tanggal_kembali').addEventListener('blur', validateField);
    document.getElementById('jumlah').addEventListener('blur', validateField);

    function validateField(e) {
        const field = e.target;
        if (!field.checkValidity()) {
            field.classList.add('is-invalid');
        } else {
            field.classList.remove('is-invalid');
        }
    }
    
    // Set tanggal minimal ke hari ini
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('tanggal_sewa').min = today;
    document.getElementById('tanggal_kembali').min = today;
</script>
</body>
</html>