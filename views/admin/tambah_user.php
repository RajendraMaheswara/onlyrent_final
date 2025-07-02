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
    <title>OnlyRent - Tambah User</title>
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
        <!-- Top Navigation -->
        <?php include('top_nav.php'); ?>

        <!-- Form Content -->
        <div class="content-wrapper">
            <div class="row justify-content-center animate-fade-in">
                <div class="col-lg-8">
                    <!-- Success/Error Messages -->
                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    
                    <div class="card form-card">
                        <div class="card-header">
                            <h4>
                                <i class="fas fa-user-plus"></i>
                                Tambah User Baru
                            </h4>
                        </div>
                        <div class="card-body">
                            <form id="userForm" action="/controllers/PenggunaController.php?action=create" method="POST">
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
                                            <input type="password" class="form-control" id="password" name="password" placeholder="Password" required minlength="3">
                                            <label for="password">Password</label>
                                            <div class="invalid-feedback">Password minimal 8 karakter</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <select class="form-control" id="role" name="role" required>
                                                <option value="">Pilih Role</option>
                                                <option value="1">Admin</option>
                                                <option value="2">User/Penyewa</option>
                                                <option value="3">Owner/Pemilik</option>
                                            </select>
                                            <label for="role">Role Pengguna</label>
                                            <div class="invalid-feedback">Role harus dipilih</div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-12">
                                        <div class="form-floating mb-3">
                                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Konfirmasi Password" required>
                                            <label for="confirm_password">Konfirmasi Password</label>
                                            <div class="invalid-feedback">Password tidak cocok</div>
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
                                        Simpan User
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Role Information -->
                    <div class="card form-card mt-4">
                        <div class="card-body">
                            <h6 class="fw-bold mb-3">Informasi Role:</h6>
                            <div class="d-flex flex-wrap gap-2">
                                <span class="role-badge role-admin">
                                    <i class="fas fa-shield-alt me-1"></i>
                                    Admin - Akses penuh sistem
                                </span>
                                <span class="role-badge role-user">
                                    <i class="fas fa-user me-1"></i>
                                    User - Dapat menyewa barang
                                </span>
                                <span class="role-badge role-owner">
                                    <i class="fas fa-user-tie me-1"></i>
                                    Owner - Dapat menyewakan barang
                                </span>
                            </div>
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

        // Responsive adjustments
        function handleResponsive() {
            const screenWidth = window.innerWidth;
            const userDropdown = document.querySelector('.dropdown .btn');
            
            if (screenWidth < 576) {
                // Hide button text on extra small screens
                document.querySelectorAll('.btn-text').forEach(text => {
                    text.classList.add('d-none');
                });
            } else {
                // Show button text on larger screens
                document.querySelectorAll('.btn-text').forEach(text => {
                    text.classList.remove('d-none');
                });
            }
        }

        // Run on load and resize
        window.addEventListener('load', handleResponsive);
        window.addEventListener('resize', handleResponsive);

        // Client-side form validation
        document.getElementById('userForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password');
            const confirmPassword = document.getElementById('confirm_password');
            
            // Reset validation
            document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
            
            // Check password match
            if (password.value !== confirmPassword.value) {
                e.preventDefault();
                confirmPassword.classList.add('is-invalid');
                return false;
            }
            
            // Check password length
            if (password.value.length < 8) {
                e.preventDefault();
                password.classList.add('is-invalid');
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
    </script>
</body>
</html>