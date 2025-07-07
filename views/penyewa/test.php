<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 2) {
    header("Location: ../../login.php");
    exit();
}

require_once '../../config/connect_db.php';
require_once '../../models/admin/Barang.php';

if (!isset($_GET['id'])) {
    $_SESSION['error'] = "Barang tidak valid";
    header("Location: daftar_barang.php");
    exit();
}

$db = getDBConnection();
$barangModel = new Barang($db);
$barang = $barangModel->getById($_GET['id']);

if (!$barang || $barang['status'] != 1) {
    $_SESSION['error'] = "Barang tidak tersedia untuk disewa";
    header("Location: daftar_barang.php");
    exit();
}

$username = $_SESSION['user']['username'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OnlyRent - Sewa Barang</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .image-gallery {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-top: 20px;
        }
        .image-item {
            width: 200px;
            height: 200px;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .image-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .btn-back {
            background-color: #6c757d;
            color: white;
        }
        .btn-back:hover {
            background-color: #5a6268;
            color: white;
        }
    </style>
</head>
<body>
    <?php include('nav.php'); ?>

    <div class="main-content">
        <?php include('top_nav.php'); ?>

        <div class="content-wrapper">
            <div class="row justify-content-center animate-fade-in">
                <div class="col-lg-8">
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <div class="card form-card">
                        <div class="card-header">
                            <h4>
                                <i class="fas fa-shopping-cart"></i>
                                Sewa Barang - <?php echo htmlspecialchars($barang['nama_barang']); ?>
                            </h4>
                        </div>
                        <div class="card-body">
                            <form id="sewaForm" action="/controllers/penyewa/SewaController.php?action=create" method="POST">
                                <input type="hidden" name="id_barang" value="<?php echo $barang['id_barang']; ?>">

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($barang['nama_barang']); ?>" disabled>
                                            <label>Nama Barang</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <input type="text" class="form-control" value="Rp <?php echo number_format($barang['harga_sewa'], 0, ',', '.'); ?>/hari" disabled>
                                            <label>Harga Sewa</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Gambar Barang</label>
                                    <div class="image-gallery">
                                        <?php 
                                        $gambar = json_decode($barang['gambar'], true) ?? [];
                                        foreach ($gambar as $img): 
                                        ?>
                                            <div class="image-item">
                                                <img src="/assets/images/barang/<?php echo htmlspecialchars($img); ?>" alt="<?php echo htmlspecialchars($barang['nama_barang']); ?>">
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <input type="date" class="form-control" id="tgl_mulai" name="tgl_mulai" required min="<?php echo date('Y-m-d'); ?>">
                                            <label for="tgl_mulai">Tanggal Mulai</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <input type="date" class="form-control" id="tgl_selesai" name="tgl_selesai" required>
                                            <label for="tgl_selesai">Tanggal Selesai</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="total_harga" name="total_harga" value="Rp 0" disabled>
                                        <label for="total_harga">Total Harga</label>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between align-items-center mt-4">
                                    <a href="daftar_barang.php" class="btn btn-back">
                                        <i class="fas fa-arrow-left me-2"></i>
                                        Kembali
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-check me-2"></i>
                                        Konfirmasi Sewa
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
            const hargaPerHari = <?php echo $barang['harga_sewa']; ?>;
            const tglMulai = document.getElementById('tgl_mulai');
            const tglSelesai = document.getElementById('tgl_selesai');
            const totalHarga = document.getElementById('total_harga');
            
            function calculateTotal() {
                if (tglMulai.value && tglSelesai.value) {
                    const start = new Date(tglMulai.value);
                    const end = new Date(tglSelesai.value);
                    
                    if (end <= start) {
                        tglSelesai.setCustomValidity('Tanggal selesai harus setelah tanggal mulai');
                        return;
                    } else {
                        tglSelesai.setCustomValidity('');
                    }
                    
                    const diffTime = Math.abs(end - start);
                    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
                    const total = diffDays * hargaPerHari;
                    
                    totalHarga.value = 'Rp ' + new Intl.NumberFormat('id-ID').format(total) + 
                                     ' (' + diffDays + ' hari)';
                    
                    // Set hidden total value for form submission
                    document.getElementById('total_harga_hidden').value = total;
                }
            }
            
            tglMulai.addEventListener('change', function() {
                tglSelesai.min = this.value;
                calculateTotal();
            });
            
            tglSelesai.addEventListener('change', calculateTotal);
            
            // Form validation
            document.getElementById('sewaForm').addEventListener('submit', function(e) {
                if (!tglMulai.value || !tglSelesai.value) {
                    e.preventDefault();
                    alert('Silakan isi tanggal mulai dan selesai');
                    return false;
                }
                
                if (new Date(tglSelesai.value) <= new Date(tglMulai.value)) {
                    e.preventDefault();
                    alert('Tanggal selesai harus setelah tanggal mulai');
                    return false;
                }
                
                return true;
            });
        });
    </script>
</body>
</html>