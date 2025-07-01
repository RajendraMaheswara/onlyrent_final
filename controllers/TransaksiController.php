<?php
include_once 'config/connect_db.php';
include_once 'models/Transaksi.php';

class TransaksiController {
    private $model;

    public function __construct($db) {
        $this->model = new Transaksi($db);
    }

    // Create Transaksi
    public function create() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id_sewa = $_POST['id_sewa'];
            $jumlah = $_POST['jumlah'];
            $tanggal = $_POST['tanggal'];
            $totalBayar = $_POST['totalBayar'];
            $status = $_POST['status'];
            $this->model->create($id_sewa, $jumlah, $tanggal, $totalBayar, $status);
            echo "Transaksi created successfully!";
        }
    }

    // Get Transaksi by ID
    public function show($id) {
        $transaksi = $this->model->getById($id);
        include 'views/transaksi/show.php'; // Show transaksi details
    }

    // Get All Transaksi
    public function index() {
        $transaksiList = $this->model->getAll();
        include 'views/transaksi/index.php'; // Show all transaksi
    }
}
?>