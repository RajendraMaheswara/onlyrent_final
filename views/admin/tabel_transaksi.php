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

    // Inisialisasi variabel $transaksi dengan array kosong
    $transaksi = [];
    $total_transaksi = 0;
    $pending_count = 0;
    $success_count = 0;
    $failed_count = 0;
    $total_pendapatan = 0;

    // Hitung total transaksi
    $total_query = "SELECT COUNT(*) as total FROM transaksi";
    $total_result = $conn->query($total_query);

    if ($total_result) {
        $total_row = $total_result->fetch_assoc();
        $total_transaksi = $total_row['total'];
    }

    // Hitung status transaksi dan total pendapatan
    $status_query = "SELECT status, COUNT(*) as count, SUM(totalBayar) as total FROM transaksi GROUP BY status";
    $status_result = $conn->query($status_query);

    if ($status_result) {
        while ($row = $status_result->fetch_assoc()) {
            if ($row['status'] == 0) {
                $pending_count = $row['count'];
            } elseif ($row['status'] == 1) {
                $success_count = $row['count'];
                $total_pendapatan += $row['total'];
            } elseif ($row['status'] == 2) {
                $failed_count = $row['count'];
            }
        }
    }

    // Konfigurasi pagination
    $per_page = 10;
    $total_pages = ceil($total_transaksi / $per_page);
    $current_page = isset($_GET['page']) ? max(1, min($total_pages, (int)$_GET['page'])) : 1;
    $offset = ($current_page - 1) * $per_page;

    // Query dengan pagination
    $query = "SELECT t.*, 
                     s.id_barang, 
                     s.id_penyewa,
                     s.tanggalSewa,
                     s.tanggalKembali,
                     b.nama_barang, 
                     p.nama as nama_penyewa,
                     pb.nama as nama_pemilik
              FROM transaksi t
              JOIN sewa s ON t.id_sewa = s.id_sewa
              JOIN barang b ON s.id_barang = b.id_barang
              JOIN penyewa p ON s.id_penyewa = p.id_penyewa
              JOIN pemilik_barang pb ON b.id_pemilik = pb.id_pemilik
              ORDER BY t.tanggal DESC
              LIMIT $per_page OFFSET $offset";
    $result = $conn->query($query);

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // Decode JSON gambar dan ambil gambar pertama
            $images = json_decode($row['gambar']);
            $firstImage = '';
            
            if (json_last_error() === JSON_ERROR_NONE && is_array($images) && count($images) > 0) {
                $firstImage = $images[0];
            } elseif (!empty($row['gambar'])) {
                $firstImage = $row['gambar'];
            }
            
            $row['first_image'] = $firstImage;
            $transaksi[] = $row;
        }
    }

    $conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OnlyRent - Kelola Transaksi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/admin/index.css">
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
                <div class="col-md-3">
                    <div class="card stats-card">
                        <div class="card-body d-flex align-items-center">
                            <div class="stats-icon stats-total me-3">
                                <i class="fas fa-clipboard-list"></i>
                            </div>
                            <div>
                                <h5 class="mb-0"><?php echo $total_transaksi; ?></h5>
                                <small class="text-muted">Total Transaksi</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stats-card">
                        <div class="card-body d-flex align-items-center">
                            <div class="stats-icon stats-pending me-3">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div>
                                <h5 class="mb-0"><?php echo $pending_count; ?></h5>
                                <small class="text-muted">Belum Dibayar</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stats-card">
                        <div class="card-body d-flex align-items-center">
                            <div class="stats-icon stats-active me-3">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div>
                                <h5 class="mb-0"><?php echo $success_count; ?></h5>
                                <small class="text-muted">Proses Auth</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stats-card">
                        <div class="card-body d-flex align-items-center">
                            <div class="stats-icon stats-completed me-3">
                                <i class="fas fa-times-circle"></i>
                            </div>
                            <div>
                                <h5 class="mb-0"><?php echo $failed_count; ?></h5>
                                <small class="text-muted">Berhasil</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pendapatan Card -->
            <div class="row mb-4 animate-fade-in">
                <div class="col-12">
                    <div class="card stats-card bg-primary text-white">
                        <div class="card-body d-flex align-items-center">
                            <div class="stats-icon me-3">
                                <i class="fas fa-money-bill-wave"></i>
                            </div>
                            <div>
                                <h5 class="mb-0">Rp <?php echo number_format($total_pendapatan, 0, ',', '.'); ?></h5>
                                <small>Total Pendapatan</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Transaksi Table -->
            <div class="row animate-fade-in">
                <div class="col-12">
                    <div class="card table-card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4>
                                <i class="fas fa-money-bill-wave"></i>
                                Daftar Transaksi
                            </h4>
                            <a href="tambah_transaksi.php" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>
                                Tambah Transaksi
                            </a>
                        </div>
                        <div class="card-body">
                            <!-- Search Box -->
                            <div class="search-box">
                                <i class="fas fa-search"></i>
                                <input type="text" class="form-control" id="searchInput" placeholder="Cari transaksi berdasarkan nama penyewa, barang, atau status...">
                            </div>

                            <!-- Table -->
                            <div class="table-responsive">
                                <table class="table" id="transaksiTable">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Bukti Pembayaran</th>
                                            <th>Barang</th>
                                            <th>Penyewa</th>
                                            <th>Tanggal Transaksi</th>
                                            <th>Total Bayar</th>
                                            <th>Status</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($transaksi as $item): ?>
                                        <tr>
                                            <td><strong>#<?php echo $item['id_transaksi']; ?></strong></td>
                                            <td>
                                                <?php if (!empty($item['first_image'])): ?>
                                                    <img src="/assets/images/transaksi/<?php echo htmlspecialchars($item['first_image']); ?>?v=<?php echo time(); ?>" 
                                                        alt="Bukti Pembayaran" 
                                                        class="img-thumbnail"
                                                        style="width: 60px; height: 40px; object-fit: cover; cursor: pointer;"
                                                        onclick="viewImage('/assets/images/transaksi/<?php echo htmlspecialchars($item['first_image']); ?>')">
                                                <?php else: ?>
                                                    <div class="no-image d-flex align-items-center justify-content-center" 
                                                        style="width: 60px; height: 40px; background: #f0f0f0; border-radius: 4px;">
                                                        <i class="fas fa-image text-muted"></i>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div>
                                                    <strong><?php echo htmlspecialchars($item['nama_barang']); ?></strong><br>
                                                    <small class="text-muted">
                                                        Sewa: <?php echo date('d M Y', strtotime($item['tanggalSewa'])); ?> - 
                                                        <?php echo date('d M Y', strtotime($item['tanggalKembali'])); ?>
                                                    </small>
                                                </div>
                                            </td>
                                            <td><?php echo htmlspecialchars($item['nama_penyewa']); ?></td>
                                            <td><?php echo date('d M Y H:i', strtotime($item['tanggal'])); ?></td>
                                            <td>Rp <?php echo number_format($item['totalBayar'], 0, ',', '.'); ?></td>
                                            <td>
                                                <?php if ($item['status'] == 0): ?>
                                                    <span class="badge bg-warning">Pending</span>
                                                <?php elseif ($item['status'] == 1): ?>
                                                    <span class="badge bg-success">Sukses</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger">Gagal</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <a href="edit_transaksi.php?id=<?php echo $item['id_transaksi']; ?>" class="btn btn-action btn-edit" title="Edit Transaksi">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button class="btn btn-action btn-status" title="Ubah Status" onclick="changeStatus(<?php echo $item['id_transaksi']; ?>, <?php echo $item['status']; ?>)">
                                                    <i class="fas fa-sync-alt"></i>
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

    <!-- Status Change Modal -->
    <div class="modal fade" id="statusModal" tabindex="-1" aria-labelledby="statusModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="statusModalLabel">Ubah Status Transaksi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Pilih status baru untuk transaksi ini:</p>
                    <select class="form-select" id="newStatus">
                        <option value="0">Pending</option>
                        <option value="1">Sukses</option>
                        <option value="2">Gagal</option>
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="confirmStatusBtn">Simpan</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Image View Modal -->
    <div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Bukti Pembayaran</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <img id="modalImage" src="" class="img-fluid" alt="Bukti Pembayaran">
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <script>
    
    // Fungsi untuk menampilkan gambar besar
    function viewImage(imageUrl) {
        document.getElementById('modalImage').src = imageUrl;
        const imageModal = new bootstrap.Modal(document.getElementById('imageModal'));
        imageModal.show();
    }
    
    // Fungsi untuk mengubah status transaksi
    function changeStatus(transaksiId, currentStatus) {
        const statusModal = new bootstrap.Modal(document.getElementById('statusModal'));
        const confirmStatusBtn = document.getElementById('confirmStatusBtn');
        
        // Set current status in dropdown
        document.getElementById('newStatus').value = currentStatus;
        
        // Update button click handler
        confirmStatusBtn.onclick = function() {
            const newStatus = document.getElementById('newStatus').value;
            window.location.href = '/controllers/TransaksiController.php?action=update_status&id=' + transaksiId + '&status=' + newStatus;
        };
        
        statusModal.show();
    }

    // Inisialisasi DataTable
    $(document).ready(function() {
        $('#transaksiTable').DataTable({
            searching: true,
            paging: false,
            info: false,
            language: {
                search: "",
                searchPlaceholder: "Cari transaksi..."
            },
            initComplete: function() {
                $('.dataTables_filter input').addClass('form-control');
            }
        });
    });
    </script>
</body>
</html>