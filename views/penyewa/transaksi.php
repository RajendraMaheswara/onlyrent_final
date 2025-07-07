<?php
session_start();
// Check if user is logged in as penyewa
if (!isset($_SESSION['user'])) {
    header("Location: ../../login.php");
    exit();
}

// Get penyewa ID from session
$id_penyewa = $_SESSION['user']['id_penyewa'] ?? 0;
if ($id_penyewa == 0) {
    $_SESSION['error'] = "Data penyewa tidak valid";
    header("Location: ../../login.php");
    exit();
}

// Koneksi ke database
require_once '../../config/connect_db.php';
include_once '../../models/penyewa/Sewa.php';
include_once '../../models/penyewa/Barang.php';

$db = getDBConnection();
$sewaModel = new Sewa($db);
$barangModel = new Barang($db);

// Handle cancel action
if (isset($_GET['action']) && $_GET['action'] == 'cancel' && isset($_GET['id'])) {
    try {
        if ($sewaModel->cancelByPenyewa($_GET['id'], $id_penyewa)) {
            $_SESSION['success'] = "Transaksi berhasil dibatalkan";
        } else {
            $_SESSION['error'] = "Gagal membatalkan transaksi atau transaksi tidak dalam status pending";
        }
        header("Location: transaksi.php");
        exit();
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
        header("Location: transaksi.php");
        exit();
    }
}

// Ambil parameter filter
$filter = [];
if (!empty($_GET['status'])) {
    $filter['status'] = (int)$_GET['status'];
}
if (!empty($_GET['start_date'])) {
    $filter['start_date'] = $_GET['start_date'];
}
if (!empty($_GET['end_date'])) {
    $filter['end_date'] = $_GET['end_date'];
}

// Get filtered transactions
$transactions = $sewaModel->getAllByPenyewa($id_penyewa, $filter);

// Get transaction details if action=show
$transactionDetail = null;
if (isset($_GET['action']) && $_GET['action'] == 'show' && isset($_GET['id'])) {
    $transactionDetail = $sewaModel->getDetailByPenyewa($_GET['id'], $id_penyewa);
    // Jika menggunakan AJAX, response sudah dikirim oleh getDetailByPenyewa() dan script berhenti
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OnlyRent - Daftar Transaksi Saya</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/penyewa/trans.css">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">
                <i class="fas fa-camera me-2"></i>OnlyRent
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="transaksi.php">Transaksi</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../../logout.php">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container my-5">
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

        <!-- Filter Section -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="get" action="transaksi.php">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status">
                                <option value="">Semua Status</option>
                                <option value="0" <?= isset($_GET['status']) && $_GET['status'] == '0' ? 'selected' : '' ?>>Pending</option>
                                <option value="1" <?= isset($_GET['status']) && $_GET['status'] == '1' ? 'selected' : '' ?>>Approved</option>
                                <option value="2" <?= isset($_GET['status']) && $_GET['status'] == '2' ? 'selected' : '' ?>>Completed</option>
                                <option value="3" <?= isset($_GET['status']) && $_GET['status'] == '3' ? 'selected' : '' ?>>Cancelled</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Tanggal Mulai</label>
                            <input type="date" class="form-control" name="start_date" value="<?= $_GET['start_date'] ?? '' ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Tanggal Akhir</label>
                            <input type="date" class="form-control" name="end_date" value="<?= $_GET['end_date'] ?? '' ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <button type="submit" class="btn btn-primary d-block w-100">
                                <i class="fas fa-filter me-2"></i>Filter
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Transactions Table -->
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">
                    <i class="fas fa-receipt me-2"></i>
                    Daftar Transaksi Saya
                </h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>ID Transaksi</th>
                                <th>Barang</th>
                                <th>Tanggal Sewa</th>
                                <th>Tanggal Kembali</th>
                                <th>Total Bayar</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($transactions)): ?>
                                <tr>
                                    <td colspan="7" class="text-center">Anda belum memiliki transaksi</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($transactions as $transaction): ?>
                                    <tr>
                                        <td><strong>#<?php echo $transaction['id_sewa']; ?></strong></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="<?php echo $transaction['gambar_barang'] ? '../../assets/images/barang/' . htmlspecialchars($transaction['gambar_barang']) : 'https://via.placeholder.com/50x50'; ?>" 
                                                     class="rounded me-2" 
                                                     alt="<?php echo htmlspecialchars($transaction['nama_barang']); ?>" 
                                                     style="width: 50px; height: 50px; object-fit: cover;">
                                                <div>
                                                    <strong><?php echo htmlspecialchars($transaction['nama_barang']); ?></strong>
                                                    <div class="text-muted small">ID: <?php echo $transaction['id_barang']; ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?php echo date('d M Y', strtotime($transaction['tanggalSewa'])); ?></td>
                                        <td><?php echo date('d M Y', strtotime($transaction['tanggalKembali'])); ?></td>
                                        <td>Rp <?php echo number_format($transaction['totalBayar'], 0, ',', '.'); ?></td>
                                        <td>
                                            <?php 
                                                $statusClass = '';
                                                switch ($transaction['status_transaksi']) {
                                                    case 0: $statusClass = 'bg-warning'; break; // Pending
                                                    case 1: $statusClass = 'bg-primary'; break; // Approved
                                                    case 2: $statusClass = 'bg-success'; break; // Completed
                                                    case 3: $statusClass = 'bg-danger'; break; // Cancelled
                                                    case 4: $statusClass = 'bg-secondary'; break; // Rejected
                                                    default: $statusClass = 'bg-secondary';
                                                }
                                            ?>
                                            <span class="badge <?php echo $statusClass; ?>">
                                                <?php echo htmlspecialchars($transaction['status_text']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-primary" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#transactionDetailModal"
                                                    onclick="loadTransactionDetail(<?php echo $transaction['id_sewa']; ?>)">
                                                <i class="fas fa-eye"></i> Detail
                                            </button>
                                            <?php if ($transaction['status_transaksi'] == 0): ?>
                                                <a href="transaksi.php?action=cancel&id=<?php echo $transaction['id_sewa']; ?>" 
                                                   class="btn btn-sm btn-danger" 
                                                   title="Batalkan Transaksi"
                                                   onclick="return confirm('Apakah Anda yakin ingin membatalkan transaksi ini?')">
                                                    <i class="fas fa-times"></i> Batal
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Transaction Detail Modal -->
    <div class="modal fade" id="transactionDetailModal" tabindex="-1" aria-labelledby="transactionDetailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="transactionDetailModalLabel">Detail Transaksi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="transactionDetailContent">
                    <!-- Content will be loaded here via JavaScript -->
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Memuat detail transaksi...</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Proof Modal -->
    <div class="modal fade" id="paymentProofModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Bukti Pembayaran</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <img id="paymentProofImage" src="" class="img-fluid rounded" alt="Bukti Pembayaran">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="../../assets/js/transaksi_penyewa.js"></script>
</body>
</html>