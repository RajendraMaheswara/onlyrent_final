<?php
    session_start();
    if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 1) {
        header("Location: ../login.php");
        exit();
    }

    // Ambil data pengguna dari session
    $username = $_SESSION['user']['username'];

    // Koneksi ke database
    require_once '../../config/connect_db.php';
    $conn = getDBConnection();

    // Inisialisasi variabel
    $transaksi = [];
    $total_transaksi = 0;
    $success_count = 0;
    $pending_count = 0;
    $failed_count = 0;

    // Hitung total transaksi
    $total_query = "SELECT COUNT(*) as total FROM transaksi";
    $total_result = $conn->query($total_query);

    if ($total_result) {
        $total_row = $total_result->fetch_assoc();
        $total_transaksi = $total_row['total'];
    }

    // Hitung status transaksi
    $status_query = "SELECT status, COUNT(*) as count FROM transaksi GROUP BY status";
    $status_result = $conn->query($status_query);

    if ($status_result) {
        while ($row = $status_result->fetch_assoc()) {
            if ($row['status'] == 1) {
                $success_count = $row['count'];
            } elseif ($row['status'] == 0) {
                $pending_count = $row['count'];
            } else {
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
    $query = "SELECT t.*, s.id_barang, s.id_penyewa, b.nama_barang, p.nama as nama_penyewa, 
              pb.nama as nama_pemilik, b.harga_sewa
              FROM transaksi t
              JOIN sewa s ON t.id_sewa = s.id_sewa
              JOIN barang b ON s.id_barang = b.id_barang
              JOIN penyewa p ON s.id_penyewa = p.id_penyewa
              JOIN pemilik_barang pb ON b.id_pemilik = pb.id_pemilik
              ORDER BY t.tanggal DESC
              LIMIT $per_page OFFSET $offset";
    $result = $conn->query($query);

    if ($result && $result->num_rows > 0) {
        // Ambil data transaksi
        while ($row = $result->fetch_assoc()) {
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
                                <i class="fas fa-exchange-alt"></i>
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
                            <div class="stats-icon stats-success me-3">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div>
                                <h5 class="mb-0"><?php echo $success_count; ?></h5>
                                <small class="text-muted">Berhasil</small>
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
                                <small class="text-muted">Pending</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stats-card">
                        <div class="card-body d-flex align-items-center">
                            <div class="stats-icon stats-failed me-3">
                                <i class="fas fa-times-circle"></i>
                            </div>
                            <div>
                                <h5 class="mb-0"><?php echo $failed_count; ?></h5>
                                <small class="text-muted">Gagal</small>
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
                                <i class="fas fa-exchange-alt"></i>
                                Daftar Transaksi
                            </h4>
                            <div>
                                <button class="btn btn-primary me-2" onclick="printReport()">
                                    <i class="fas fa-print me-2"></i>
                                    Cetak Laporan
                                </button>
                                <button class="btn btn-success" onclick="exportToExcel()">
                                    <i class="fas fa-file-excel me-2"></i>
                                    Export Excel
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <!-- Search Box -->
                            <div class="search-box">
                                <i class="fas fa-search"></i>
                                <input type="text" class="form-control" id="searchInput" placeholder="Cari transaksi berdasarkan ID, barang, atau penyewa...">
                            </div>

                            <!-- Table -->
                            <div class="table-responsive">
                                <table class="table" id="transaksiTable">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Tanggal</th>
                                            <th>Barang</th>
                                            <th>Penyewa</th>
                                            <th>Pemilik</th>
                                            <th>Harga Sewa</th>
                                            <th>Jumlah</th>
                                            <th>Total Bayar</th>
                                            <th>Status</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($transaksi as $item): ?>
                                        <tr>
                                            <td><strong>#<?php echo $item['id_transaksi']; ?></strong></td>
                                            <td><?php echo date('d M Y H:i', strtotime($item['tanggal'])); ?></td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($item['nama_barang']); ?></strong>
                                            </td>
                                            <td><?php echo htmlspecialchars($item['nama_penyewa']); ?></td>
                                            <td><?php echo htmlspecialchars($item['nama_pemilik']); ?></td>
                                            <td>Rp <?php echo number_format($item['harga_sewa'], 0, ',', '.'); ?></td>
                                            <td><?php echo $item['jumlah']; ?> hari</td>
                                            <td>Rp <?php echo number_format($item['totalBayar'], 0, ',', '.'); ?></td>
                                            <td>
                                                <?php if ($item['status'] == 1): ?>
                                                    <span class="badge bg-success">Berhasil</span>
                                                <?php elseif ($item['status'] == 0): ?>
                                                    <span class="badge bg-warning text-dark">Pending</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger">Gagal</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <button class="btn btn-action btn-view" title="Lihat Detail" onclick="viewTransaksi(<?php echo $item['id_transaksi']; ?>)">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <?php if ($item['status'] == 0): ?>
                                                    <button class="btn btn-action btn-success" title="Setujui" onclick="updateStatus(<?php echo $item['id_transaksi']; ?>, 1)">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                    <button class="btn btn-action btn-danger" title="Tolak" onclick="updateStatus(<?php echo $item['id_transaksi']; ?>, 2)">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                <?php endif; ?>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <script>
    // Fungsi untuk menampilkan detail transaksi
    function viewTransaksi(transaksiId) {
        window.location.href = "lihat_transaksi.php?id=" + transaksiId;
    }
    
    // Fungsi untuk memperbarui status transaksi
    function updateStatus(transaksiId, status) {
        if (confirm("Apakah Anda yakin ingin mengubah status transaksi ini?")) {
            window.location.href = "/controllers/TransaksiController.php?action=update_status&id=" + transaksiId + "&status=" + status;
        }
    }
    
    // Fungsi untuk mencetak laporan
    function printReport() {
        window.open("cetak_laporan_transaksi.php", "_blank");
    }
    
    // Fungsi untuk export ke Excel
    function exportToExcel() {
        window.location.href = "export_transaksi_excel.php";
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