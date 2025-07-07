<?php
    session_start();
    if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 1) {
        header("Location: ../../login.php");
        exit();
    }

    // Include koneksi database dan model
    require_once '../../config/connect_db.php';
    require_once '../../models/admin/Pengguna.php';

    // Ambil data user yang akan dilihat
    if (!isset($_GET['id'])) {
        $_SESSION['error'] = "User ID tidak valid";
        header("Location: tabel_user.php");
        exit();
    }

    $db = getDBConnection();
    $model = new Pengguna($db);
    $user = $model->getById($_GET['id']);

    if (!$user) {
        $_SESSION['error'] = "User tidak ditemukan";
        header("Location: tabel_user.php");
        exit();
    }

    $username = $_SESSION['user']['username'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OnlyRent - Lihat Data User</title>
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
                                <i class="fas fa-user-plus"></i>
                                Detail Pengguna - <?php echo htmlspecialchars($user['username']); ?>
                            </h4>
                        </div>
                        <div class="card-body">
                            <!-- Tampilkan detail pengguna -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
                                        <label for="username">Username</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                                        <label for="email">Email</label>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="role" name="role" value="<?php echo $user['role'] == 1 ? 'Admin' : ($user['role'] == 2 ? 'User/Penyewa' : 'Owner/Pemilik'); ?>" disabled>
                                        <label for="role">Role</label>
                                    </div>
                                </div>
                                <!-- <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="status" name="status" 
                                            value="<?php echo isset($user['status']) ? ($user['status'] == 1 ? 'Aktif' : 'Non-Aktif') : 'Status Tidak Ditemukan'; ?>" 
                                            disabled>
                                        <label for="status">Status</label>
                                    </div>
                                </div> -->
                            </div>

                            <div class="d-flex justify-content-between align-items-center mt-4">
                                <a href="tabel_user.php" class="btn-back">
                                    <i class="fas fa-arrow-left me-2"></i>
                                    Kembali
                                </a>
                                <a href="edit_user.php?id=<?php echo $user['id_pengguna']; ?>" class="btn-editt">
                                    <i class="fas fa-edit me-2"></i>
                                    Edit User
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
