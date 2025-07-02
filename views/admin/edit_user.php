<?php
    session_start();
    if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 1) {
        header("Location: ../../login.php");
        exit();
    }

    // Include koneksi database dan model
    require_once '../../config/connect_db.php';
    require_once '../../models/Pengguna.php';

    // Ambil data user yang akan diedit
    if (!isset($_GET['id'])) {
        $_SESSION['error'] = "User ID tidak valid";
        header("Location: tabel_user.php");
        exit();
    }

    $db = getDBConnection();
    $model = new Pengguna($db);
    $user_data = $model->getById($_GET['id']);

    if (!$user_data) {
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
    <title>OnlyRent - Edit User</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/admin/index.css">
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
                                <i class="fas fa-user-edit"></i>
                                Edit User - <?php echo htmlspecialchars($user_data['username']); ?>
                            </h4>
                        </div>
                        <div class="card-body">
                            <form id="userForm" action="/controllers/PenggunaController.php?action=edit" method="POST">
                                <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user_data['id_pengguna']); ?>">

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="username" name="username" 
                                                   placeholder="Username" value="<?php echo htmlspecialchars($user_data['username']); ?>" required>
                                            <label for="username">Username</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <input type="email" class="form-control" id="email" name="email" 
                                                   placeholder="Email" value="<?php echo htmlspecialchars($user_data['email']); ?>" required>
                                            <label for="email">Email</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-floating mb-3">
                                            <select class="form-control" id="role" name="role" required>
                                                <option value="">Pilih Role</option>
                                                <option value="1" <?php echo ($user_data['role'] == '1') ? 'selected' : ''; ?>>Admin</option>
                                                <option value="2" <?php echo ($user_data['role'] == '2') ? 'selected' : ''; ?>>User/Penyewa</option>
                                                <option value="3" <?php echo ($user_data['role'] == '3') ? 'selected' : ''; ?>>Owner/Pemilik</option>
                                            </select>
                                            <label for="role">Role Pengguna</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="password-note mb-3">
                                    <h6 class="fw-bold mb-2">
                                        <i class="fas fa-info-circle me-2"></i>
                                        Ubah Password
                                    </h6>
                                    <p class="mb-0 small text-muted">
                                        Biarkan kosong jika tidak ingin mengubah password. Isi kedua field jika ingin mengubah password.
                                    </p>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <input type="password" class="form-control" id="password" name="password" placeholder="Password Baru">
                                            <label for="password">Password Baru</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Konfirmasi Password Baru">
                                            <label for="confirm_password">Konfirmasi Password Baru</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between align-items-center mt-4">
                                    <a href="tabel_user.php" class="btn-back">
                                        <i class="fas fa-arrow-left me-2"></i>
                                        Kembali
                                    </a>
                                    <button type="submit" class="btn btn-primary btn-submit">
                                        <i class="fas fa-save me-2"></i>
                                        Update User
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
        document.getElementById('userForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;

            if (password || confirmPassword) {
                if (password !== confirmPassword) {
                    e.preventDefault();
                    alert('Password dan konfirmasi password tidak cocok!');
                    return false;
                }

                if (password.length < 6) {
                    e.preventDefault();
                    alert('Password minimal 6 karakter!');
                    return false;
                }
            }
        });
    </script>
</body>
</html>