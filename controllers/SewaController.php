<?php
include_once '../config/connect_db.php';
include_once '../models/Sewa.php';
include_once '../models/Barang.php';

class SewaController {
    private $model;
    private $barangModel;

    public function __construct($db) {
        $this->model = new Sewa($db);
        $this->barangModel = new Barang($db);
    }

    // Create new rental
    public function create() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            try {
                // Validate input
                $id_barang = $_POST['id_barang'];
                $id_penyewa = $_POST['id_penyewa'];
                $tanggal_sewa = $_POST['tanggal_sewa'];
                $tanggal_kembali = $_POST['tanggal_kembali'];
                
                // Get barang data to calculate total price
                $barang = $this->barangModel->getById($id_barang);
                if (!$barang) {
                    throw new Exception("Barang tidak ditemukan");
                }
                
                // Calculate rental days and total price
                $start = new DateTime($tanggal_sewa);
                $end = new DateTime($tanggal_kembali);
                $days = $start->diff($end)->days;
                $total_bayar = $days * $barang['harga_sewa'];
                
                // Create rental
                $id_sewa = $this->model->create(
                    $id_barang,
                    $id_penyewa,
                    $tanggal_sewa,
                    $tanggal_kembali,
                    $total_bayar
                );
                
                // Update barang status to not available
                $this->barangModel->updateStatus($id_barang, 0);
                
                $_SESSION['success'] = "Sewa berhasil dibuat! Total bayar: Rp " . number_format($total_bayar, 0, ',', '.');
                header("Location: ../views/admin/tabel_sewa.php");
                exit();
            } catch (Exception $e) {
                $_SESSION['error'] = $e->getMessage();
                header("Location: ../views/admin/tambah_sewa.php");
                exit();
            }
        }
    }

    // Update rental
    public function update() {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        try {
            $id_sewa = $_POST['id_sewa'];
            $id_barang = $_POST['id_barang'];
            $id_penyewa = $_POST['id_penyewa'];
            $tanggal_sewa = $_POST['tanggal_sewa'];
            $tanggal_kembali = $_POST['tanggal_kembali'];
            $status = $_POST['status'];

            // Get the selected barang to calculate new total price
            $barang = $this->barangModel->getById($id_barang);
            if (!$barang) {
                throw new Exception("Barang tidak ditemukan");
            }

            // Calculate new total price
            $start = new DateTime($tanggal_sewa);
            $end = new DateTime($tanggal_kembali);
            $days = $start->diff($end)->days;
            $total_bayar = $days * $barang['harga_sewa'];

            // Prepare all data to update
            $data = [
                'id_barang' => $id_barang,
                'id_penyewa' => $id_penyewa,
                'tanggalSewa' => $tanggal_sewa,
                'tanggalKembali' => $tanggal_kembali,
                'totalBayar' => $total_bayar,
                'status' => $status
            ];

            // Get the original rental data
            $originalSewa = $this->model->getById($id_sewa);

            // Update the rental
            $this->model->update($id_sewa, $data);

            // Handle barang status changes
            if ($status == 0 || $status == 2) { 
                // Jika status dibatalkan (0) atau selesai (2), update status barang menjadi tersedia (1)
                $this->barangModel->updateStatus($id_barang, 1);
            } elseif ($status == 1) { 
                // Jika status aktif (1), update status barang menjadi tidak tersedia (0)
                $this->barangModel->updateStatus($id_barang, 0);
            }

            $_SESSION['success'] = "Data sewa berhasil diperbarui!";
            header("Location: ../views/admin/tabel_sewa.php");
            exit();
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            header("Location: ../views/admin/edit_sewa.php?id=" . $_POST['id_sewa']);
            exit();
        }
    }
}

    // Get all rentals
    public function getAll() {
        $filter = [];
        
        if (isset($_GET['status'])) {
            $filter['status'] = $_GET['status'];
        }
        
        if (isset($_GET['id_penyewa'])) {
            $filter['id_penyewa'] = $_GET['id_penyewa'];
        }
        
        return $this->model->getAll($filter);
    }

    // Get rental by ID
    public function getById($id_sewa) {
        return $this->model->getById($id_sewa);
    }

    // Update rental status
    public function updateStatus($id_sewa, $status) {
        try {
            $this->model->updateStatus($id_sewa, $status);
            
            // If status changed to completed, update barang availability
            if ($status == 2) { // 2 = completed
                $sewa = $this->model->getById($id_sewa);
                $this->barangModel->updateStatus($sewa['id_barang'], 1); // 1 = available
            }
            
            $_SESSION['success'] = "Status sewa berhasil diperbarui!";
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }
        
        header("Location: ../views/admin/tabel_sewa.php");
        exit();
    }
}

// Handle request
session_start();
$db = getDBConnection();
$controller = new SewaController($db);

if (isset($_GET['action'])) {
    switch ($_GET['action']) {
        case 'create':
            $controller->create();
            break;
        case 'update':
            $controller->update();
            break;
        case 'update_status':
            if (isset($_GET['id']) && isset($_GET['status'])) {
                $controller->updateStatus($_GET['id'], $_GET['status']);
            }
            break;
    }
}
?>