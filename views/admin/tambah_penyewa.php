<?php
    session_start();
    if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 1) {
        header("Location: ../../login.php");
        exit();
    }

    // Get user data from session
    $username = $_SESSION['user']['username'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OnlyRent - Tambah Penyewa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/admin/index.css">
    <style>
        .form-card {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .penyewa-icon {
            color: #1cc88a;
            font-size: 1.2rem;
            margin-right: 8px;
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
        <!-- Top Navigation -->
        <?php include('top_nav.php'); ?>

        <!-- Form Content -->
        <div class="content-wrapper">
            <div class="row justify-content-center animate-fade-in">
                <div class="col-lg-8">
                    <!-- Success/Error Messages -->
                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    
                    <div class="card form-card">
                        <div class="card-header">
                            <h4>
                                <i class="fas fa-user-plus penyewa-icon"></i>
                                Tambah Penyewa Baru
                            </h4>
                        </div>
                        <div class="card-body">
                            <form id="penyewaForm" action="/controllers/PenyewaController.php?action=create" method="POST">
                                <h5 class="mb-4">Informasi Akun</h5>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="username" name="username" placeholder="Username" required>
                                            <label for="username">Username</label>
                                            <div class="invalid-feedback">Username harus diisi</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <input type="email" class="form-control" id="email" name="email" placeholder="Email" required>
                                            <label for="email">Email</label>
                                            <div class="invalid-feedback">Email harus valid</div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <input type="password" class="form-control" id="password" name="password" placeholder="Password" required minlength="8">
                                            <label for="password">Password</label>
                                            <div class="invalid-feedback">Password minimal 8 karakter</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Konfirmasi Password" required>
                                            <label for="confirm_password">Konfirmasi Password</label>
                                            <div class="invalid-feedback">Password tidak cocok</div>
                                        </div>
                                    </div>
                                </div>

                                <hr class="my-4">
                                
                                <h5 class="mb-4">Informasi Penyewa</h5>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="nama" name="nama" placeholder="Nama Lengkap" required>
                                            <label for="nama">Nama Lengkap</label>
                                            <div class="invalid-feedback">Nama lengkap harus diisi</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <input type="tel" class="form-control" id="no_telp" name="no_telp" placeholder="Nomor Telepon" required pattern="[0-9]{10,15}">
                                            <label for="no_telp">Nomor Telepon</label>
                                            <div class="invalid-feedback">Format nomor telepon tidak valid (10-15 digit)</div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-12">
                                        <div class="form-floating mb-3">
                                            <textarea class="form-control" id="alamat" name="alamat" placeholder="Alamat" style="height: 100px" required></textarea>
                                            <label for="alamat">Alamat Lengkap</label>
                                            <div class="invalid-feedback">Alamat harus diisi</div>
                                        </div>
                                    </div>
                                </div>

                                <input type="hidden" name="role" value="2">

                                <div class="d-flex justify-content-between align-items-center mt-4">
                                    <a href="tabel_penyewa.php" class="btn-back">
                                        <i class="fas fa-arrow-left me-2"></i>
                                        Kembali
                                    </a>
                                    <button type="submit" class="btn btn-submit-penyewa btn-submit">
                                        <i class="fas fa-save me-2"></i>
                                        Simpan Penyewa
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
        // Mobile Menu Toggle
        document.querySelector('.mobile-menu-toggle').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.add('active');
            document.querySelector('.mobile-overlay').classList.add('active');
        });

        // Close Sidebar
        document.querySelector('.mobile-overlay').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.remove('active');
            this.classList.remove('active');
        });

        // Toggle Mobile Search
        document.querySelector('.search-toggle').addEventListener('click', function() {
            document.querySelector('.mobile-search-box').classList.toggle('d-none');
        });

        // Close sidebar when clicking on nav links (for mobile)
        document.querySelectorAll('.sidebar .nav-link').forEach(link => {
            link.addEventListener('click', function() {
                document.querySelector('.sidebar').classList.remove('active');
                document.querySelector('.mobile-overlay').classList.remove('active');
            });
        });

        // Client-side form validation
        document.getElementById('penyewaForm').addEventListener('submit', function(e) {
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
                return false;
            }
            
            return true;
        });

        // Live password match validation
        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('password');
            const confirmPassword = this;
            
            if (confirmPassword.value !== password.value) {
                confirmPassword.classList.add('is-invalid');
            } else {
                confirmPassword.classList.remove('is-invalid');
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