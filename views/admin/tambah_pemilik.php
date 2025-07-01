<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 1) {
    header("Location: ../login.php");
    exit();
}

$username = $_SESSION['user']['username'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OnlyRent - Tambah Pemilik</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/admin/index.css">
    <style>
        .pemilik-header {
            color: #36b9cc;
        }
        .btn-pemilik {
            background-color: #36b9cc;
            border-color: #36b9cc;
        }
        .btn-pemilik:hover {
            background-color: #2c9faf;
            border-color: #2c9faf;
        }
    </style>
</head>
<body>
    <?php include('nav.php'); ?>
    <div class="main-content">
        <?php include('top_nav.php'); ?>

        <div class="content-wrapper">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <div class="card form-card">
                        <div class="card-header">
                            <h4 class="pemilik-header">
                                <i class="fas fa-user-plus me-2"></i>
                                Tambah Pemilik Baru
                            </h4>
                        </div>
                        <div class="card-body">
                            <form id="pemilikForm" action="/controllers/PemilikBarangController.php?action=create" method="POST">
                                <h5 class="mb-3">Informasi Akun</h5>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="username" name="username" required>
                                            <label for="username">Username</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <input type="email" class="form-control" id="email" name="email" required>
                                            <label for="email">Email</label>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <input type="password" class="form-control" id="password" name="password" required minlength="8">
                                            <label for="password">Password</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                            <label for="confirm_password">Konfirmasi Password</label>
                                        </div>
                                    </div>
                                </div>

                                <hr class="my-4">
                                
                                <h5 class="mb-3">Informasi Pemilik</h5>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="nama" name="nama" required>
                                            <label for="nama">Nama Lengkap</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <input type="tel" class="form-control" id="no_telp" name="no_telp" required pattern="[0-9]{10,15}">
                                            <label for="no_telp">Nomor Telepon</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between align-items-center mt-4">
                                    <a href="tabel_pemilik.php" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left me-2"></i>Kembali
                                    </a>
                                    <button type="submit" class="btn btn-pemilik">
                                        <i class="fas fa-save me-2"></i>Simpan Pemilik
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
            const password = document.getElementById('password');
            const confirmPassword = document.getElementById('confirm_password');
            const noTelp = document.getElementById('no_telp');
            
            // Reset validation
            document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
            
            let isValid = true;
            
            // Check password match
            if (password.value !== confirmPassword.value) {
                confirmPassword.classList.add('is-invalid');
                isValid = false;
            }
            
            // Check password length
            if (password.value.length < 8) {
                password.classList.add('is-invalid');
                isValid = false;
            }
            
            // Check phone number format
            const phoneRegex = /^[0-9]{10,15}$/;
            if (!phoneRegex.test(noTelp.value)) {
                noTelp.classList.add('is-invalid');
                isValid = false;
            }
            
            if (!isValid) {
                e.preventDefault();
            }
        });

        // Live validation
        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('password');
            if (this.value !== password.value) {
                this.classList.add('is-invalid');
            } else {
                this.classList.remove('is-invalid');
            }
        });

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