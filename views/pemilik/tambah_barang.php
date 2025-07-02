<?php
session_start();
// Only allow pemilik (role 3) to access
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 3) {
    header("Location: ../login.php");
    exit();
}

// Verify pemilik has an id_pemilik
$id_pemilik = $_SESSION['user']['id_pemilik'] ?? 0;
if ($id_pemilik == 0) {
    $_SESSION['error'] = "Data pemilik tidak valid";
    header("Location: ../login.php");
    exit();
}

// Include database connection and models
require_once '../../config/connect_db.php';
require_once '../../models/Barang.php';

$db = getDBConnection();
$barang_model = new Barang($db);
$username = $_SESSION['user']['username'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OnlyRent - Tambah Barang</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/admin/index.css">
    <style>
        .image-preview-container {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 20px;
        }
        .image-preview-item {
            position: relative;
            width: 150px;
            height: 150px;
            border: 1px solid #ddd;
            border-radius: 5px;
            overflow: hidden;
        }
        .image-preview-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .image-preview-item .delete-btn {
            position: absolute;
            top: 5px;
            right: 5px;
            background: rgba(0,0,0,0.5);
            color: white;
            border: none;
            border-radius: 50%;
            width: 25px;
            height: 25px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }
        .delete-btn:hover {
            background: rgba(255,0,0,0.7);
        }
    </style>
</head>
<body>
    <!-- Mobile Menu Overlay -->
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
                                <i class="fas fa-box me-2"></i>
                                Tambah Barang Baru
                            </h4>
                        </div>
                        <div class="card-body">
                            <form id="barangForm" action="/controllers/BarangController.php?action=create" method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="id_pemilik" value="<?php echo $id_pemilik; ?>">

                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" id="nama_barang" name="nama_barang" 
                                           placeholder="Nama Barang" required>
                                    <label for="nama_barang">Nama Barang</label>
                                </div>

                                <div class="form-floating mb-3">
                                    <textarea class="form-control" id="deskripsi" name="deskripsi" 
                                              placeholder="Deskripsi" style="height: 100px" required></textarea>
                                    <label for="deskripsi">Deskripsi Barang</label>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <input type="number" class="form-control" id="harga_sewa" name="harga_sewa" 
                                                   placeholder="Harga Sewa" required>
                                            <label for="harga_sewa">Harga Sewa (per hari)</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <select class="form-select" id="status" name="status" required>
                                                <option value="1" selected>Tersedia</option>
                                                <option value="0">Tidak Tersedia</option>
                                            </select>
                                            <label for="status">Status Barang</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">Gambar Barang</label>
                                    <p class="small text-muted">Upload gambar untuk barang ini (minimal 1 gambar).</p>
                                    
                                    <!-- File upload -->
                                    <input type="file" class="form-control" id="gambar" name="gambar[]" multiple accept="image/*" required>
                                    <div class="form-text">Format: JPG, PNG, JPEG. Maksimal 5MB per gambar.</div>
                                </div>

                                <div class="d-flex justify-content-between align-items-center mt-4">
                                    <a href="tabel_barang.php" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left me-2"></i>
                                        Kembali
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i>
                                        Tambah Barang
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
            // Form validation
            document.getElementById('barangForm').addEventListener('submit', function(e) {
                const hargaSewa = document.getElementById('harga_sewa').value;
                
                if (!hargaSewa || isNaN(hargaSewa) || parseFloat(hargaSewa) <= 0) {
                    e.preventDefault();
                    alert('Harga sewa harus berupa angka positif!');
                    return false;
                }

                // Check if at least one image is selected
                const fileInput = document.getElementById('gambar');
                if (fileInput.files.length === 0) {
                    e.preventDefault();
                    alert('Minimal harus ada 1 gambar untuk barang!');
                    return false;
                }
            });
        });
    </script>
</body>
</html>