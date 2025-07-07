<?php
include_once '../../config/connect_db.php';
include_once '../../models/penyewa/Sewa.php';
include_once '../../models/penyewa/Barang.php';

class SewaController {
    private $model;
    private $barangModel;

    public function __construct($db) {
        $this->model = new Sewa($db);
        $this->barangModel = new Barang($db);
    }

    /**
     * Menampilkan semua transaksi penyewa
     */
    public function index() {
        session_start();
        
        if (!isset($_SESSION['user']) || !isset($_SESSION['user']['id_penyewa'])) {
            header("Location: ../../login.php");
            exit();
        }
        
        $id_penyewa = $_SESSION['user']['id_penyewa'];
        $filter = [];
        
        // Ambil parameter filter
        if (!empty($_GET['status'])) {
            $filter['status'] = (int)$_GET['status'];
        }
        
        if (!empty($_GET['start_date'])) {
            $filter['start_date'] = $_GET['start_date'];
        }
        
        if (!empty($_GET['end_date'])) {
            $filter['end_date'] = $_GET['end_date'];
        }
        
        $transactions = $this->model->getAllByPenyewa($id_penyewa, $filter);
        
        // Tampilkan view
        include '../../views/penyewa/transaksi.php';
    }

    /**
     * Menampilkan detail transaksi
     */
    public function show($id_sewa) {
        session_start();
        
        if (!isset($_SESSION['user']) || !isset($_SESSION['user']['id_penyewa'])) {
            header("Location: ../../login.php");
            exit();
        }
        
        $id_penyewa = $_SESSION['user']['id_penyewa'];
        $transaction = $this->model->getDetailByPenyewa($id_sewa, $id_penyewa);
        
        if (!$transaction) {
            $_SESSION['error'] = "Transaksi tidak ditemukan";
            header("Location: transaksi.php");
            exit();
        }
        
        // Tampilkan view detail
        include '../../views/penyewa/detail_transaksi.php';
    }

    /**
     * Membatalkan transaksi
     */
    public function cancel($id_sewa) {
        session_start();
        
        if (!isset($_SESSION['user']) || !isset($_SESSION['user']['id_penyewa'])) {
            header("Location: ../../login.php");
            exit();
        }
        
        $id_penyewa = $_SESSION['user']['id_penyewa'];
        
        try {
            $success = $this->model->cancelByPenyewa($id_sewa, $id_penyewa);
            
            if ($success) {
                $_SESSION['success'] = "Sewa berhasil dibatalkan";
            } else {
                $_SESSION['error'] = "Gagal membatalkan sewa. Mungkin status sudah berubah.";
            }
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }
        
        header("Location: transaksi.php");
        exit();
    }
}

// Handle request
$db = getDBConnection();
$controller = new SewaController($db);

if (isset($_GET['action'])) {
    switch ($_GET['action']) {
        case 'show':
            if (isset($_GET['id'])) {
                $controller->show($_GET['id']);
            }
            break;
        case 'cancel':
            if (isset($_GET['id'])) {
                $controller->cancel($_GET['id']);
            }
            break;
        default:
            $controller->index();
    }
} else {
    $controller->index();
}