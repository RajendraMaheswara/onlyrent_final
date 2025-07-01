<?php
include_once 'config/connect_db.php';
include_once 'models/Sewa.php';

class SewaController {
    private $model;

    public function __construct($db) {
        $this->model = new Sewa($db);
    }

    // Create Sewa
    public function create() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id_barang = $_POST['id_barang'];
            $id_penyewa = $_POST['id_penyewa'];
            $tanggalSewa = $_POST['tanggalSewa'];
            $tanggalKembali = $_POST['tanggalKembali'];
            $totalBayar = $_POST['totalBayar'];
            $status = $_POST['status'];
            $this->model->create($id_barang, $id_penyewa, $tanggalSewa, $tanggalKembali, $totalBayar, $status);
            echo "Sewa created successfully!";
        }
    }

    // Get All Sewa
    public function index() {
        $sewaList = $this->model->getAll();
        include 'views/sewa/index.php'; // Show all sewa
    }

    // Get Sewa by ID
    public function show($id) {
        $sewa = $this->model->getById($id);
        include 'views/sewa/show.php'; // Show sewa details
    }
}
?>