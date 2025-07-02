<?php
    session_start();
    if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 3) {
        header("Location: ../login.php");
        exit();
    }

    // Include koneksi database dan model
    require_once '../../config/connect_db.php';
    require_once '../../models/pemilik/Barang.php';
    require_once '../../models/PemilikBarang.php';

    // Ambil data barang yang akan dilihat
    if (!isset($_GET['id'])) {
        $_SESSION['error'] = "Barang ID tidak valid";
        header("Location: tabel_barang.php");
        exit();
    }

    $db = getDBConnection();
    $barangModel = new Barang($db);
    $pemilikModel = new PemilikBarang($db);
    
    $barang = $barangModel->getById($_GET['id']);

    if (!$barang) {
        $_SESSION['error'] = "Barang tidak ditemukan";
        header("Location: tabel_barang.php");
        exit();
    }

    // Ambil data pemilik barang
    $pemilik = $pemilikModel->getById($barang['id_pemilik']);
    $nama_pemilik = $pemilik ? $pemilik['nama'] : 'N/A';

    $username = $_SESSION['user']['username'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OnlyRent - Lihat Data Barang</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/admin/index.css">
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
            transition: transform 0.3s;
        }
        .image-item img:hover {
            transform: scale(1.05);
        }
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
                                <i class="fas fa-box-open"></i>
                                Detail Barang - <?php echo htmlspecialchars($barang['nama_barang']); ?>
                            </h4>
                        </div>
                        <div class="card-body">
                            <!-- Tampilkan detail barang -->
                             <div class="row">
                                <div class="col-md-12">
                                    <div class="form-floating mb-3">
                                        <input type="text" class="form-control" id="nama_barang" name="nama_barang" value="<?php echo htmlspecialchars($barang['nama_barang']); ?>" disabled>
                                        <label for="nama_barang">Nama Barang</label>
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="harga_sewa" name="harga_sewa" value="<?php echo 'Rp ' . number_format($barang['harga_sewa'], 0, ',', '.'); ?>" disabled>
                                        <label for="harga_sewa">Harga Sewa (per hari)</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="status" name="status" value="<?php echo $barang['status'] == 1 ? 'Tersedia' : 'Tidak Tersedia'; ?>" disabled>
                                        <label for="status">Status</label>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="form-floating">
                                    <textarea class="form-control" id="deskripsi" name="deskripsi" style="height: 120px" disabled><?php echo htmlspecialchars($barang['deskripsi']); ?></textarea>
                                    <label for="deskripsi">Deskripsi Barang</label>
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

                            <div class="d-flex justify-content-between align-items-center mt-4">
                                <a href="tabel_barang.php" class="btn btn-back">
                                    <i class="fas fa-arrow-left me-2"></i>
                                    Kembali
                                </a>
                                <a href="edit_barang.php?id=<?php echo $barang['id_barang']; ?>" class="btn btn-editt">
                                    <i class="fas fa-edit me-2"></i>
                                    Edit Barang
                                </a>
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