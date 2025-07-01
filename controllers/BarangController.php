<?php
include_once 'config/connect_db.php';
include_once 'models/Barang.php';

class BarangController {
    private $model;

    public function __construct($db) {
        $this->model = new Barang($db);
    }

    // Create Barang
    public function create() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id_pemilik = $_POST['id_pemilik'];
            $nama_barang = $_POST['nama_barang'];
            $deskripsi = $_POST['deskripsi'];
            $harga_sewa = $_POST['harga_sewa'];
            $status = $_POST['status'];
            $this->model->create($id_pemilik, $nama_barang, $deskripsi, $harga_sewa, $status);
            echo "Barang created successfully!";
        }
    }

    // Get All Barang
    public function index() {
        $barangList = $this->model->getAll();
        include 'views/barang/index.php'; // Show all barang
    }

    // Get Barang by ID
    public function show($id) {
        $barang = $this->model->getById($id);
        include 'views/barang/show.php'; // Show single barang
    }

    // Update Barang
    public function update($id) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $nama_barang = $_POST['nama_barang'];
            $deskripsi = $_POST['deskripsi'];
            $harga_sewa = $_POST['harga_sewa'];
            $status = $_POST['status'];
            $this->model->update($id, $nama_barang, $deskripsi, $harga_sewa, $status);
            echo "Barang updated successfully!";
        }
    }

    // Delete Barang
    public function delete($id) {
        $this->model->delete($id);
        echo "Barang deleted successfully!";
    }
}
?>