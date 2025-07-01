<?php
    session_start();
    if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 1) {
        header("Location: ../login.php");
        exit();
    }

    // Include koneksi database dan model
    require_once '../../config/connect_db.php';
    require_once '../../models/PemilikBarang.php';

    // Ambil data pemilik yang akan diedit
    if (!isset($_GET['id'])) {
        $_SESSION['error'] = "Pemilik ID tidak valid";
        header("Location: tabel_pemilik.php");
        exit();
    }

    $db = getDBConnection();
    $model = new PemilikBarang($db);
    $pemilik_data = $model->getById($_GET['id']);

    if (!$pemilik_data) {
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
    <title>OnlyRent - Edit Pemilik</title>
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
                                Edit Pemilik - <?php echo htmlspecialchars($pemilik_data['nama']); ?>
                            </h4>
                        </div>
                        <div class="card-body">
                            <form id="pemilikForm" action="/controllers/PemilikBarangController.php?action=update" method="POST">
                                <input type="hidden" name="id_pemilik" value="<?php echo htmlspecialchars($pemilik_data['id_pemilik']); ?>">

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="username" name="username" 
                                                   placeholder="Username" value="<?php echo htmlspecialchars($pemilik_data['username']); ?>" required>
                                            <label for="username">Username</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <input type="email" class="form-control" id="email" name="email" 
                                                   placeholder="Email" value="<?php echo htmlspecialchars($pemilik_data['email']); ?>" required>
                                            <label for="email">Email</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="nama" name="nama" 
                                                   placeholder="Nama Lengkap" value="<?php echo htmlspecialchars($pemilik_data['nama']); ?>" required>
                                            <label for="nama">Nama Lengkap</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <input type="tel" class="form-control" id="no_telp" name="no_telp" 
                                                   placeholder="Nomor Telepon" value="<?php echo htmlspecialchars($pemilik_data['no_telp']); ?>" required>
                                            <label for="no_telp">Nomor Telepon</label>
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
                                    <a href="tabel_pemilik.php" class="btn-back">
                                        <i class="fas fa-arrow-left me-2"></i>
                                        Kembali
                                    </a>
                                    <button type="submit" class="btn btn-primary btn-submit">
                                        <i class="fas fa-save me-2"></i>
                                        Update Pemilik
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
        document.getElementById('pemilikForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;

            if (password || confirmPassword) {
                if (password !== confirmPassword) {
                    e.preventDefault();
                    alert('Password dan konfirmasi password tidak cocok!');
                    return false;
                }

                if (password.length < 8) {
                    e.preventDefault();
                    alert('Password minimal 8 karakter!');
                    return false;
                }
            }
            
            // Validasi nomor telepon
            const noTelp = document.getElementById('no_telp').value;
            if (!/^[0-9]{10,15}$/.test(noTelp)) {
                e.preventDefault();
                alert('Nomor telepon harus 10-15 digit angka!');
                return false;
            }
        });
    </script>
</body>
</html>