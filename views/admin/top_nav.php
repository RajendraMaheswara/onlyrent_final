<nav class="navbar navbar-expand-lg top-navbar">
    <div class="container-fluid">
        <div>
            <div class="page-subtitle">Manajemen Pengguna</div>
            <h4 class="page-title">Kelola User</h4>
        </div>
        <div class="d-flex align-items-center">
            <div class="dropdown">
                <button class="btn btn-link text-dark d-flex align-items-center" type="button" data-bs-toggle="dropdown">
                    <div class="user-avatar me-2">H</div>
                    <span class="fw-semibold"><?php echo htmlspecialchars($username); ?></span>
                    <i class="fas fa-chevron-down ms-2"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="#"><i class="fas fa-user me-2"></i>Profile</a></li>
                    <li><a class="dropdown-item" href="#"><i class="fas fa-cog me-2"></i>Settings</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="/logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                </ul>
            </div>
        </div>
    </div>
</nav>