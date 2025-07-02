<?php
    session_start();
    if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 1) {
        header("Location: ../../login.php");
        exit();
    }

    // Include database connection and models
    require_once '../../config/connect_db.php';
    require_once '../../models/Penyewa.php';

    // Get penyewa data to view
    if (!isset($_GET['id'])) {
        $_SESSION['error'] = "ID Penyewa tidak valid";
        header("Location: tabel_penyewa.php");
        exit();
    }

    $db = getDBConnection();
    $model = new Penyewa($db);
    $penyewa = $model->getById($_GET['id']);

    if (!$penyewa) {
        $_SESSION['error'] = "Penyewa tidak ditemukan";
        header("Location: tabel_penyewa.php");
        exit();
    }

    $username = $_SESSION['user']['username'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OnlyRent - Detail Penyewa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/admin/index.css">
    <style>
        .penyewa-header {
            color: #1cc88a;
        }
        .btn-edit-penyewa {
            background-color: #1cc88a;
            border-color: #1cc88a;
        }
        .btn-edit-penyewa:hover {
            background-color: #17a673;
            border-color: #17a673;
        }
        .detail-card {
            border-left: 4px solid #1cc88a;
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

        <!-- Content -->
        <div class="content-wrapper">
            <div class="row justify-content-center animate-fade-in">
                <div class="col-lg-8">
                    <!-- Success/Error Messages -->
                    <div id="alertContainer"></div>

                    <div class="card form-card detail-card">
                        <div class="card-header">
                            <h4 class="penyewa-header">
                                <i class="fas fa-user-circle me-2"></i>
                                Detail Penyewa - <?php echo htmlspecialchars($penyewa['nama']); ?>
                            </h4>
                        </div>
                        <div class="card-body">
                            <h5 class="mb-3"><i class="fas fa-user-tag me-2"></i>Informasi Akun</h5>
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="form-floating mb-3">
                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($penyewa['username']); ?>" disabled>
                                        <label>Username</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating mb-3">
                                        <input type="email" class="form-control" value="<?php echo htmlspecialchars($penyewa['email']); ?>" disabled>
                                        <label>Email</label>
                                    </div>
                                </div>
                            </div>

                            <h5 class="mb-3"><i class="fas fa-id-card me-2"></i>Informasi Pribadi</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-floating mb-3">
                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($penyewa['nama']); ?>" disabled>
                                        <label>Nama Lengkap</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating mb-3">
                                        <input type="tel" class="form-control" value="<?php echo htmlspecialchars($penyewa['no_telp']); ?>" disabled>
                                        <label>Nomor Telepon</label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-12">
                                    <div class="form-floating mb-3">
                                        <textarea class="form-control" style="height: 100px" disabled><?php echo htmlspecialchars($penyewa['alamat']); ?></textarea>
                                        <label>Alamat Lengkap</label>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mt-4">
                                <a href="tabel_penyewa.php" class="btn-back">
                                    <i class="fas fa-arrow-left me-2"></i>
                                    Kembali
                                </a>
                                <a href="edit_penyewa.php?id=<?php echo $penyewa['id_penyewa']; ?>" class="btn btn-edit-penyewa">
                                    <i class="fas fa-edit me-2"></i>
                                    Edit Penyewa
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