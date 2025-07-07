<?php
include_once '../../config/connect_db.php';
include_once '../../models/penyewa/Sewa.php';
include_once '../../models/admin/Barang.php';

class SewaController {
    private $sewaModel;
    private $barangModel;

    public function __construct($db) {
        $this->sewaModel = new SewaModel($db);
        $this->barangModel = new Barang($db);
    }

    // Create new rental
    public function createSewa() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            try {
                session_start();
                $id_penyewa = $_SESSION['user']['id_penyewa'];
                $id_barang = $_POST['id_barang'];
                $tgl_mulai = $_POST['tgl_mulai'];
                $tgl_selesai = $_POST['tgl_selesai'];
                $total_harga = $_POST['total_harga'];
                
                // Validate input
                if (empty($id_penyewa)) {
                    throw new Exception("ID Penyewa tidak valid");
                }
                
                if (empty($id_barang)) {
                    throw new Exception("Barang tidak valid");
                }
                
                $today = date('Y-m-d');
                if ($tgl_mulai < $today) {
                    throw new Exception("Tanggal mulai tidak boleh sebelum hari ini");
                }
                
                if ($tgl_selesai <= $tgl_mulai) {
                    throw new Exception("Tanggal selesai harus setelah tanggal mulai");
                }
                
                // Create rental
                $id_sewa = $this->sewaModel->createSewa(
                    $id_penyewa, 
                    $id_barang, 
                    $tgl_mulai, 
                    $tgl_selesai, 
                    $total_harga, 
                    'pending'
                );
                
                // Update barang status to not available
                $this->sewaModel->updateBarangStatus($id_barang, 0);
                
                $_SESSION['success'] = "Penyewaan berhasil dibuat! Silakan tunggu konfirmasi dari pemilik.";
                header("Location: ../../views/penyewa/riwayat_sewa.php");
                exit();
            } catch (Exception $e) {
                error_log("Error: " . $e->getMessage());
                $_SESSION['error'] = $e->getMessage();
                header("Location: ../../views/penyewa/sewa_barang.php?id=" . $_POST['id_barang']);
                exit();
            }
        }
    }

    // Get rental history
    public function getRiwayatSewa() {
        session_start();
        if (!isset($_SESSION['user']['id_penyewa'])) {
            header("Location: ../../login.php");
            exit();
        }
        
        return $this->sewaModel->getRiwayatSewa($_SESSION['user']['id_penyewa']);
    }

    // Get rental details
    public function getDetailSewa($id_sewa) {
        session_start();
        if (!isset($_SESSION['user']['id_penyewa'])) {
            header("Location: ../../login.php");
            exit();
        }
        
        return $this->sewaModel->getDetailSewa($id_sewa, $_SESSION['user']['id_penyewa']);
    }
}

// Handle request
    $db = getDBConnection();
    $controller = new SewaController($db);

    if (isset($_GET['action'])) {
        switch ($_GET['action']) {
            case 'create':
                $controller->createSewa();
                break;
        }
    }
?>