<?php
    session_start();
    // Check if user is logged in
    if (!isset($_SESSION['user'])) {
        header("Location: ../login.php");
        exit();
    }

    // Get pemilik ID from session
    $id_pemilik = $_SESSION['user']['id_pemilik'] ?? 0;
    if ($id_pemilik == 0) {
        $_SESSION['error'] = "Data pemilik tidak valid";
        header("Location: ../../login.php");
        exit();
    }

    // Koneksi ke database
    require_once '../../config/connect_db.php';
    $conn = getDBConnection();

    // Inisialisasi variabel
    $barang = [];
    $total_barang = 0;
    $active_count = 0;
    $inactive_count = 0;

    // Hitung total barang milik pemilik ini
    $total_query = "SELECT COUNT(*) as total FROM barang WHERE id_pemilik = ?";
    $total_stmt = $conn->prepare($total_query);
    $total_stmt->bind_param("i", $id_pemilik);
    $total_stmt->execute();
    $total_result = $total_stmt->get_result();

    if ($total_result) {
        $total_row = $total_result->fetch_assoc();
        $total_barang = $total_row['total'];
    }

    // Hitung status barang milik pemilik ini
    $status_query = "SELECT status, COUNT(*) as count FROM barang WHERE id_pemilik = ? GROUP BY status";
    $status_stmt = $conn->prepare($status_query);
    $status_stmt->bind_param("i", $id_pemilik);
    $status_stmt->execute();
    $status_result = $status_stmt->get_result();

    if ($status_result) {
        while ($row = $status_result->fetch_assoc()) {
            if ($row['status'] == 1) {
                $active_count = $row['count'];
            } else {
                $inactive_count = $row['count'];
            }
        }
    }

    // Konfigurasi pagination
    $per_page = 10;
    $total_pages = ceil($total_barang / $per_page);
    $current_page = isset($_GET['page']) ? max(1, min($total_pages, (int)$_GET['page'])) : 1;
    $offset = ($current_page - 1) * $per_page;

    // Query dengan pagination untuk barang milik pemilik ini
    $query = "SELECT * FROM barang WHERE id_pemilik = ? LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iii", $id_pemilik, $per_page, $offset);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // Decode JSON gambar dan ambil gambar pertama
            $images = json_decode($row['gambar']);
            $firstImage = '';

            if (json_last_error() === JSON_ERROR_NONE && is_array($images) && count($images) > 0) {
                $firstImage = $images[0];
            } elseif (!empty($row['gambar'])) {
                // Jika bukan JSON, gunakan gambar langsung
                $firstImage = $row['gambar'];
            }

            $row['first_image'] = $firstImage;
            $barang[] = $row;
        }
    }

    $conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OnlyRent - Daftar Barang Saya</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets//css//admin//index.css">
</head>
<body>
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
                <div class="col-md-4">
                    <div class="card stats-card">
                        <div class="card-body d-flex align-items-center">
                            <div class="stats-icon stats-total me-3">
                                <i class="fas fa-box"></i>
                            </div>
                            <div>
                                <h5 class="mb-0"><?php echo $total_barang; ?></h5>
                                <small class="text-muted">Total Barang Saya</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card stats-card">
                        <div class="card-body d-flex align-items-center">
                            <div class="stats-icon stats-active me-3">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div>
                                <h5 class="mb-0"><?php echo $active_count; ?></h5>
                                <small class="text-muted">Tersedia</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card stats-card">
                        <div class="card-body d-flex align-items-center">
                            <div class="stats-icon stats-inactive me-3">
                                <i class="fas fa-times-circle"></i>
                            </div>
                            <div>
                                <h5 class="mb-0"><?php echo $inactive_count; ?></h5>
                                <small class="text-muted">Tidak Tersedia</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Barang Table -->
            <div class="row animate-fade-in">
                <div class="col-12">
                    <div class="card table-card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4>
                                <i class="fas fa-boxes"></i>
                                Daftar Barang Saya
                            </h4>
                            <a href="tambah_barang.php" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>
                                Tambah Barang
                            </a>
                        </div>
                        <div class="card-body">
                            <!-- Search Box -->
                            <div class="search-box">
                                <i class="fas fa-search"></i>
                                <input type="text" class="form-control" id="searchInput" placeholder="Cari barang berdasarkan nama atau harga...">
                            </div>

                            <!-- Table -->
                            <div class="table-responsive">
                                <table class="table" id="barangTable">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Gambar</th>
                                            <th>Nama Barang</th>
                                            <th>Harga Sewa</th>
                                            <th>Status</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($barang as $item): ?>
                                        <tr>
                                            <td><strong>#<?php echo $item['id_barang']; ?></strong></td>
                                            <td>
                                                <?php if (!empty($item['first_image'])): ?>
                                                    <img src="/assets/images/barang/<?php echo htmlspecialchars($item['first_image']); ?>?v=<?php echo time(); ?>" 
                                                        alt="Gambar Barang" 
                                                        class="img-thumbnail"
                                                        style="width: 50px; height: 50px; object-fit: cover;">
                                                <?php else: ?>
                                                    <div class="no-image d-flex align-items-center justify-content-center" 
                                                        style="width: 50px; height: 50px; background: #f0f0f0; border-radius: 4px;">
                                                        <i class="fas fa-image text-muted"></i>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($item['nama_barang']); ?></strong>
                                                <div class="text-muted small"><?php echo substr(htmlspecialchars($item['deskripsi']), 0, 50); ?>...</div>
                                            </td>
                                            <td>Rp <?php echo number_format($item['harga_sewa'], 0, ',', '.'); ?></td>
                                            <td>
                                                <?php if ($item['status'] == 1): ?>
                                                    <span class="badge bg-success">Tersedia</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Tidak Tersedia</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <button class="btn btn-action btn-view" title="Lihat Detail" onclick="viewBarang(<?php echo $item['id_barang']; ?>)">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <a href="edit_barang.php?id=<?php echo $item['id_barang']; ?>" class="btn btn-action btn-edit" title="Edit Barang">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button class="btn btn-action btn-delete" title="Hapus Barang" onclick="confirmDelete(<?php echo $item['id_barang']; ?>, '<?php echo htmlspecialchars($item['nama_barang']); ?>')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                                <button class="btn btn-action btn-status" title="Ubah Status" onclick="toggleStatus(<?php echo $item['id_barang']; ?>, <?php echo $item['status']; ?>)">
                                                    <i class="fas fa-power-off"></i>
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
                    <p>Apakah Anda yakin ingin menghapus barang <strong id="deleteBarangName"></strong>?</p>
                    <p class="text-danger"><small>Tindakan ini tidak dapat dibatalkan dan akan menghapus semua data terkait barang ini.</small></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Hapus</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <script>
    // Fungsi untuk menampilkan detail barang
    function viewBarang(barangId) {
        window.location.href = "lihat_barang.php?id=" + barangId;
    }
    
    function confirmDelete(barangId, barangName) {
        // Menampilkan modal konfirmasi dan mengganti nama barang
        document.getElementById('deleteBarangName').textContent = barangName;
        const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
        
        // Menghapus event listener sebelumnya (jika ada)
        confirmDeleteBtn.replaceWith(confirmDeleteBtn.cloneNode(true));
        const newConfirmBtn = document.getElementById('confirmDeleteBtn');
        
        // Menambahkan event listener baru
        newConfirmBtn.onclick = function() {
            // Redirect ke controller untuk proses hapus
            window.location.href = '/controllers/pemilik/BarangController.php?action=delete&id=' + barangId;
        };

        // Menampilkan modal konfirmasi
        const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
        deleteModal.show();
    }

    // Fungsi untuk mengubah status barang
    function toggleStatus(barangId, currentStatus) {
        const newStatus = currentStatus == 1 ? 0 : 1;
        window.location.href = '/controllers/pemilik/BarangController.php?action=update_status&id=' + barangId + '&status=' + newStatus;
    }

    // Inisialisasi DataTable
    $(document).ready(function() {
        $('#barangTable').DataTable({
            searching: true,
            paging: false,
            info: false,
            language: {
                search: "",
                searchPlaceholder: "Cari barang..."
            },
            initComplete: function() {
                $('.dataTables_filter input').addClass('form-control');
            }
        });
    });
    </script>
</body>
</html>