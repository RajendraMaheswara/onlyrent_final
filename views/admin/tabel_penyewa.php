<?php
    session_start();
    if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 1) {
        header("Location: ../../login.php");
        exit();
    }

    // Ambil data pengguna dari session
    $username = $_SESSION['user']['username'];

    // Koneksi ke database
    require_once '../../config/connect_db.php';
    $conn = getDBConnection();

    // Inisialisasi variabel $penyewa dengan array kosong
    $penyewa = [];
    $total_penyewa = 0;

    // Hitung total penyewa (role = 2)
    $total_query = "SELECT COUNT(*) as total FROM pengguna p JOIN penyewa py ON p.id_pengguna = py.id_pengguna WHERE p.role = 2";
    $total_result = $conn->query($total_query);

    if ($total_result) {
        $total_row = $total_result->fetch_assoc();
        $total_penyewa = $total_row['total'];
    }

    // Konfigurasi pagination
    $per_page = 10;
    $total_pages = ceil($total_penyewa / $per_page);
    $current_page = isset($_GET['page']) ? max(1, min($total_pages, (int)$_GET['page'])) : 1;
    $offset = ($current_page - 1) * $per_page;

    // Query dengan pagination
    $query = "SELECT 
                p.id_pengguna, 
                p.username, 
                p.email, 
                py.id_penyewa,
                py.nama as nama_penyewa,
                py.alamat,
                py.no_telp
              FROM pengguna p
              JOIN penyewa py ON p.id_pengguna = py.id_pengguna
              WHERE p.role = 2
              LIMIT $per_page OFFSET $offset";
    $result = $conn->query($query);

    if ($result && $result->num_rows > 0) {
        // Ambil data penyewa
        while ($row = $result->fetch_assoc()) {
            $penyewa[] = $row;
        }
    }

    $conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OnlyRent - Kelola Penyewa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets//css/admin/index.css">
</head>
<body>
    <div class="mobile-overlay"></div>
    <!-- Sidebar -->
    <?php include('nav.php'); ?>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Navigation -->
        <?php include('top_nav.php'); ?>

        <!-- Content -->
        <div class="content-wrapper">
            <!-- Alert Messages -->
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Statistics Cards -->
            <div class="row mb-4 animate-fade-in">
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card stats-card">
                        <div class="card-body d-flex align-items-center">
                            <div class="stats-icon stats-total me-3">
                                <i class="fas fa-users"></i>
                            </div>
                            <div>
                                <h5 class="mb-0"><?php echo $total_penyewa; ?></h5>
                                <small class="text-muted">Total Penyewa</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Penyewa Table -->
            <div class="row animate-fade-in">
                <div class="col-12">
                    <div class="card table-card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4>
                                <i class="fas fa-user-friends"></i>
                                Daftar Penyewa
                            </h4>
                            <a href="tambah_penyewa.php" class="btn-add">
                                <i class="fas fa-plus me-2"></i>
                                Tambah Penyewa
                            </a>
                        </div>
                        <div class="card-body">
                            <!-- Search Box -->
                            <div class="search-box">
                                <i class="fas fa-search"></i>
                                <input type="text" class="form-control" id="searchInput" placeholder="Cari penyewa berdasarkan nama, email, atau alamat...">
                            </div>

                            <!-- Table -->
                            <div class="table-responsive">
                                <table class="table" id="penyewaTable">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Nama Penyewa</th>
                                            <th>Username</th>
                                            <th>Email</th>
                                            <th>Alamat</th>
                                            <th>No. Telp</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($penyewa as $p): ?>
                                        <tr>
                                            <td><strong>#<?php echo $p['id_penyewa']; ?></strong></td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <strong><?php echo htmlspecialchars($p['nama_penyewa']); ?></strong>
                                                </div>
                                            </td>
                                            <td><?php echo htmlspecialchars($p['username']); ?></td>
                                            <td><?php echo htmlspecialchars($p['email']); ?></td>
                                            <td class="alamat-cell" title="<?php echo htmlspecialchars($p['alamat']); ?>">
                                                <?php echo htmlspecialchars($p['alamat']); ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($p['no_telp']); ?></td>
                                            <td>
                                                <button class="btn btn-action btn-view" title="Lihat Detail" onclick="viewPenyewa(<?php echo $p['id_penyewa']; ?>)">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="btn btn-action btn-edit" title="Edit Penyewa" onclick="editPenyewa(<?php echo $p['id_penyewa']; ?>)">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-action btn-delete" title="Hapus Penyewa" onclick="confirmDelete(<?php echo $p['id_penyewa']; ?>, '<?php echo htmlspecialchars($p['nama_penyewa']); ?>')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <div class="row mt-4">
                <div class="col-12">
                    <nav aria-label="Page navigation">
                        <ul class="pagination justify-content-center">
                            <?php if ($current_page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $current_page - 1; ?>" aria-label="Previous">
                                        <span aria-hidden="true">&laquo;</span>
                                    </a>
                                </li>
                            <?php else: ?>
                                <li class="page-item disabled">
                                    <a class="page-link" href="#" tabindex="-1" aria-disabled="true">&laquo;</a>
                                </li>
                            <?php endif; ?>

                            <?php 
                            // Tampilkan maksimal 5 nomor halaman di sekitar halaman aktif
                            $start_page = max(1, $current_page - 2);
                            $end_page = min($total_pages, $current_page + 2);
                            
                            if ($start_page > 1) {
                                echo '<li class="page-item"><a class="page-link" href="?page=1">1</a></li>';
                                if ($start_page > 2) {
                                    echo '<li class="page-item disabled"><a class="page-link" href="#">...</a></li>';
                                }
                            }
                            
                            for ($i = $start_page; $i <= $end_page; $i++): ?>
                                <li class="page-item <?php echo ($i == $current_page) ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor;
                            
                            if ($end_page < $total_pages) {
                                if ($end_page < $total_pages - 1) {
                                    echo '<li class="page-item disabled"><a class="page-link" href="#">...</a></li>';
                                }
                                echo '<li class="page-item"><a class="page-link" href="?page='.$total_pages.'">'.$total_pages.'</a></li>';
                            }
                            ?>

                            <?php if ($current_page < $total_pages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $current_page + 1; ?>" aria-label="Next">
                                        <span aria-hidden="true">&raquo;</span>
                                    </a>
                                </li>
                            <?php else: ?>
                                <li class="page-item disabled">
                                    <a class="page-link" href="#" tabindex="-1" aria-disabled="true">&raquo;</a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Konfirmasi Hapus</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin menghapus penyewa <strong id="deletePenyewaName"></strong>?</p>
                    <p class="text-danger"><small>Tindakan ini akan menghapus semua data terkait penyewa ini termasuk riwayat sewa.</small></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Hapus</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Penyewa Detail Modal -->
    <div class="modal fade" id="penyewaDetailModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detail Penyewa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="penyewaDetailContent">
                    <!-- Content will be loaded via AJAX -->
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="../../assets/js/index.js"></script>

    <script>
    // Inisialisasi DataTable
    $(document).ready(function() {
        $('#penyewaTable').DataTable({
            searching: true,
            paging: false,
            info: false,
            language: {
                search: "",
                searchPlaceholder: "Cari penyewa ..."
            },
            dom: 't'
        });
    });

    function viewPenyewa(penyewaId) {
        window.location.href = "lihat_penyewa.php?id=" + penyewaId;
    }

    function editPenyewa(penyewaId) {
        window.location.href = "edit_penyewa.php?id=" + penyewaId;
    }
    
    function confirmDelete(penyewaId, penyewaName) {
        // Menampilkan modal konfirmasi dan mengganti nama penyewa
        document.getElementById('deletePenyewaName').textContent = penyewaName;
        const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
        
        // Menghapus event listener sebelumnya (jika ada)
        confirmDeleteBtn.replaceWith(confirmDeleteBtn.cloneNode(true));
        const newConfirmBtn = document.getElementById('confirmDeleteBtn');
        
        // Menambahkan event listener baru
        newConfirmBtn.onclick = function() {
            // Redirect ke controller untuk proses hapus
            window.location.href = '/controllers/admin/PenyewaController.php?action=delete&id=' + penyewaId;
        };

        // Menampilkan modal konfirmasi
        const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
        deleteModal.show();
    }
    </script>
</body>
</html>