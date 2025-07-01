<?php
    session_start();
    if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 1) {
        header("Location: ../login.php");
        exit();
    }

    // Include koneksi database dan model
    require_once '../../config/connect_db.php';
    require_once '../../models/PemilikBarang.php';

    // Ambil data pemilik yang akan dilihat
    if (!isset($_GET['id'])) {
        $_SESSION['error'] = "Pemilik ID tidak valid";
        header("Location: tabel_pemilik.php");
        exit();
    }

    $db = getDBConnection();
    $model = new PemilikBarang($db);
    $pemilik = $model->getById($_GET['id']);

    if (!$pemilik) {
        $_SESSION['error'] = "Pemilik tidak ditemukan";
        header("Location: tabel_pemilik.php");
        exit();
    }

    $username = $_SESSION['user']['username'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OnlyRent - Lihat Data Pemilik</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/admin/index.css">
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
                                <i class="fas fa-user-tie"></i>
                                Detail Pemilik - <?php echo htmlspecialchars($pemilik['nama']); ?>
                            </h4>
                        </div>
                        <div class="card-body">
                            <!-- Tampilkan detail pemilik -->
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($pemilik['username']); ?>" disabled>
                                        <label for="username">Username</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($pemilik['email']); ?>" disabled>
                                        <label for="email">Email</label>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="nama" name="nama" value="<?php echo htmlspecialchars($pemilik['nama']); ?>" disabled>
                                        <label for="nama">Nama Lengkap</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="no_telp" name="no_telp" value="<?php echo htmlspecialchars($pemilik['no_telp']); ?>" disabled>
                                        <label for="no_telp">Nomor Telepon</label>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mt-4">
                                <a href="tabel_pemilik.php" class="btn-back">
                                    <i class="fas fa-arrow-left me-2"></i>
                                    Kembali
                                </a>
                                <a href="edit_pemilik.php?id=<?php echo $pemilik['id_pemilik']; ?>" class="btn-editt">
                                    <i class="fas fa-edit me-2"></i>
                                    Edit Pemilik
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