<?php
    session_start();
    if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 1) {
        header("Location: ../../login.php");
        exit();
    }

    // Include koneksi database dan model
    require_once '../../config/connect_db.php';
    require_once '../../models/Penyewa.php';

    // Ambil data penyewa yang akan diedit
    if (!isset($_GET['id'])) {
        $_SESSION['error'] = "ID Penyewa tidak valid";
        header("Location: tabel_penyewa.php");
        exit();
    }

    $db = getDBConnection();
    $model = new Penyewa($db);
    $penyewa_data = $model->getById($_GET['id']);

    if (!$penyewa_data) {
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
    <title>OnlyRent - Edit Penyewa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/admin/index.css">
    <style>
        .penyewa-header {
            color: #1cc88a;
        }
        .btn-submit-penyewa {
            background-color: #1cc88a;
            border-color: #1cc88a;
        }
        .btn-submit-penyewa:hover {
            background-color: #17a673;
            border-color: #17a673;
        }
    </style>
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
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <div class="card form-card">
                        <div class="card-header">
                            <h4 class="penyewa-header">
                                <i class="fas fa-user-edit me-2"></i>
                                Edit Penyewa - <?php echo htmlspecialchars($penyewa_data['nama']); ?>
                            </h4>
                        </div>
                        <div class="card-body">
                            <form id="penyewaForm" action="/controllers/PenyewaController.php?action=update" method="POST">
                                <input type="hidden" name="id_penyewa" value="<?php echo htmlspecialchars($penyewa_data['id_penyewa']); ?>">

                                <h5 class="mb-3">Informasi Akun</h5>
                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="username" name="username"
                                                value="<?php echo htmlspecialchars($penyewa_data['username']); ?>" required>
                                            <label for="username">Username</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <input type="email" class="form-control" id="email" name="email"
                                                value="<?php echo htmlspecialchars($penyewa_data['email']); ?>" required>
                                            <label for="email">Email</label>
                                        </div>
                                    </div>
                                </div>

                                <h5 class="mb-3">Informasi Penyewa</h5>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="nama" name="nama" 
                                                   placeholder="Nama Lengkap" value="<?php echo htmlspecialchars($penyewa_data['nama']); ?>" required>
                                            <label for="nama">Nama Lengkap</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <input type="tel" class="form-control" id="no_telp" name="no_telp" 
                                                   placeholder="Nomor Telepon" value="<?php echo htmlspecialchars($penyewa_data['no_telp']); ?>" required
                                                   pattern="[0-9]{10,15}">
                                            <label for="no_telp">Nomor Telepon</label>
                                            <div class="invalid-feedback">Format nomor telepon tidak valid (10-15 digit)</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-12">
                                        <div class="form-floating mb-3">
                                            <textarea class="form-control" id="alamat" name="alamat" 
                                                      placeholder="Alamat" style="height: 100px" required><?php echo htmlspecialchars($penyewa_data['alamat']); ?></textarea>
                                            <label for="alamat">Alamat Lengkap</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="password-note mb-3">
                                    <h6 class="fw-bold mb-2">
                                        <i class="fas fa-info-circle me-2"></i>
                                        Reset Password
                                    </h6>
                                    <p class="mb-0 small text-muted">
                                        Kosongkan jika tidak ingin mengubah password. Isi kedua field jika ingin reset password.
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
                                    <a href="tabel_penyewa.php" class="btn-back">
                                        <i class="fas fa-arrow-left me-2"></i>
                                        Kembali
                                    </a>
                                    <button type="submit" class="btn btn-submit-penyewa btn-submit">
                                        <i class="fas fa-save me-2"></i>
                                        Update Penyewa
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
        document.getElementById('penyewaForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const noTelp = document.getElementById('no_telp');

            // Reset validation
            document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
            
            let isValid = true;

            // Validate phone number
            const phoneRegex = /^[0-9]{10,15}$/;
            if (!phoneRegex.test(noTelp.value)) {
                noTelp.classList.add('is-invalid');
                isValid = false;
            }

            // Validate password if provided
            if (password || confirmPassword) {
                if (password !== confirmPassword) {
                    e.preventDefault();
                    alert('Password dan konfirmasi password tidak cocok!');
                    isValid = false;
                }

                if (password.length < 8) {
                    e.preventDefault();
                    alert('Password minimal 8 karakter!');
                    isValid = false;
                }
            }

            if (!isValid) {
                e.preventDefault();
            }
        });

        // Live phone number validation
        document.getElementById('no_telp').addEventListener('input', function() {
            const phoneRegex = /^[0-9]{10,15}$/;
            if (!phoneRegex.test(this.value)) {
                this.classList.add('is-invalid');
            } else {
                this.classList.remove('is-invalid');
            }
        });
    </script>
</body>
</html>