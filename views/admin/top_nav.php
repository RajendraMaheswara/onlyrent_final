<!-- <nav class="navbar navbar-expand-lg top-navbar">
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
</nav> -->

<nav class="navbar navbar-expand-lg top-navbar">
    <div class="container-fluid">
        <button class="mobile-menu-toggle d-lg-none me-2" type="button">
            <i class="fas fa-bars"></i>
        </button>
        
        <div class="d-flex flex-column">
            <div class="welcome-text">Selamat datang kembali,</div>
            <h4 class="dashboard-title">Dashboard</h4>
        </div>
        
        <div class="d-flex align-items-center ms-auto">
            <!-- <div class="search-box me-3 d-none d-md-flex">
                <i class="fas fa-search"></i>
                <input type="text" class="form-control" placeholder="Cari data...">
            </div> -->
            
            <div class="d-flex">
                <!-- <button class="notification-btn search-toggle d-md-none me-2" type="button">
                    <i class="fas fa-search"></i>
                </button>
                
                <button class="notification-btn me-2" type="button">
                    <i class="fas fa-envelope"></i>
                </button>
                
                <button class="notification-btn me-2" type="button">
                    <i class="fas fa-bell"></i>
                    <span class="notification-badge">3</span>
                </button> -->
                
                <div class="dropdown">
                    <button class="btn btn-link text-dark d-flex align-items-center" type="button" data-bs-toggle="dropdown">
                        <div class="user-avatar me-2"><?php echo strtoupper(substr(htmlspecialchars($username), 0, 1)); ?></div>
                        <span class="fw-semibold d-none d-sm-inline"><?php echo htmlspecialchars($username); ?></span>
                        <i class="fas fa-chevron-down ms-2 d-none d-sm-inline"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end dropdown-fixed">
                        <li><a class="dropdown-item" href="#"><i class="fas fa-user me-2"></i>Profile</a></li>
                        <li><a class="dropdown-item" href="#"><i class="fas fa-cog me-2"></i>Settings</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="../../logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Mobile Search Box -->
    <div class="mobile-search-box container-fluid d-md-none mt-2">
        <div class="search-box w-100">
            <i class="fas fa-search"></i>
            <input type="text" class="form-control" placeholder="Cari data...">
        </div>
    </div>
</nav>