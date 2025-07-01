<?php
include_once '../config/connect_db.php';
include_once '../models/Transaksi.php';

class TransaksiController {
    private $model;

    public function __construct($db) {
        $this->model = new Transaksi($db);
    }

    // Create new transaksi
    public function create() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            try {
                $id_sewa = $_POST['id_sewa'];
                $jumlah = $_POST['jumlah'];
                $totalBayar = $_POST['totalBayar'];
                $status = $_POST['status'] ?? 0; // Default status 0 (pending)
                
                // Validation
                if (empty($id_sewa) || !is_numeric($id_sewa)) {
                    throw new Exception("ID Sewa tidak valid");
                }
                
                if (empty($jumlah) || !is_numeric($jumlah) || $jumlah <= 0) {
                    throw new Exception("Jumlah harus berupa angka positif");
                }
                
                if (empty($totalBayar) || !is_numeric($totalBayar) || $totalBayar <= 0) {
                    throw new Exception("Total bayar harus berupa angka positif");
                }
                
                $id_transaksi = $this->model->create($id_sewa, $jumlah, $totalBayar, $status);

                $_SESSION['success'] = "Transaksi berhasil ditambahkan!";
                header("Location: ../views/admin/transaksi.php");
                exit();
            } catch (Exception $e) {
                error_log("Error: " . $e->getMessage());
                $_SESSION['error'] = $e->getMessage();
                header("Location: ../views/admin/tambah_transaksi.php?id_sewa=" . $_POST['id_sewa']);
                exit();
            }
        }
    }

    // Update transaksi status
    public function updateStatus() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            try {
                $id_transaksi = $_POST['id_transaksi'];
                $status = $_POST['status'];
                
                // Validation
                if (empty($id_transaksi) || !is_numeric($id_transaksi)) {
                    throw new Exception("ID Transaksi tidak valid");
                }
                
                if (!in_array($status, [0, 1, 2])) { // 0: pending, 1: success, 2: failed
                    throw new Exception("Status transaksi tidak valid");
                }
                
                $this->model->updateStatus($id_transaksi, $status);

                $_SESSION['success'] = "Status transaksi berhasil diperbarui!";
                header("Location: ../views/admin/transaksi.php");
                exit();
            } catch (Exception $e) {
                error_log("Error: " . $e->getMessage());
                $_SESSION['error'] = $e->getMessage();
                header("Location: ../views/admin/transaksi.php");
                exit();
            }
        }
    }

    // Get transaksi by ID
    public function getById($id_transaksi) {
        return $this->model->getById($id_transaksi);
    }

    // Get all transaksi by sewa ID
    public function getAllBySewa($id_sewa) {
        return $this->model->getAllBySewa($id_sewa);
    }

    // Get all transaksi with optional filters
    public function getAll($filter = []) {
        return $this->model->getAll($filter);
    }
}

// Handle request
session_start();
$db = getDBConnection();
$controller = new TransaksiController($db);

if (isset($_GET['action'])) {
    switch ($_GET['action']) {
        case 'create':
            $controller->create();
            break;
        case 'update_status':
            $controller->updateStatus();
            break;
    }
}
?>