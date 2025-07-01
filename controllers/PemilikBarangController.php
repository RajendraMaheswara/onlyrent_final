<?php
include_once '../config/connect_db.php';
include_once '../models/PemilikBarang.php';

class PemilikBarangController {
    private $model;

    public function __construct($db) {
        $this->model = new PemilikBarang($db);
    }

    // Create Pemilik (for admin)
    public function create() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            try {
                // Validate all inputs
                $username = trim($_POST['username']);
                $email = trim($_POST['email']);
                $password = $_POST['password'];
                $confirm_password = $_POST['confirm_password'];
                $nama = trim($_POST['nama']);
                $no_telp = trim($_POST['no_telp']);

                // Basic validation
                if (empty($username) || empty($email) || empty($password) || 
                    empty($nama) || empty($no_telp)) {
                    throw new Exception("Semua field harus diisi");
                }

                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    throw new Exception("Format email tidak valid");
                }

                if ($password !== $confirm_password) {
                    throw new Exception("Password tidak cocok");
                }

                if (strlen($password) < 8) {
                    throw new Exception("Password minimal 8 karakter");
                }

                if (!preg_match('/^[0-9]{10,15}$/', $no_telp)) {
                    throw new Exception("Format nomor telepon tidak valid (10-15 digit)");
                }

                // Create both user account and pemilik profile
                $result = $this->model->createWithUser(
                    $username, 
                    $email, 
                    $password, 
                    $nama, 
                    $no_telp
                );

                if ($result) {
                    $_SESSION['success'] = "Pemilik baru berhasil ditambahkan!";
                    header("Location: ../views/admin/tabel_pemilik.php");
                    exit();
                } else {
                    throw new Exception("Gagal menambahkan pemilik baru");
                }
            } catch (Exception $e) {
                error_log("Error: " . $e->getMessage()); 
                $_SESSION['error'] = $e->getMessage();
                header("Location: ../views/admin/tambah_pemilik.php");
                exit();
            }
        }
    }

    // Update Pemilik
    public function update() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            try {
                $id_pemilik = $_POST['id_pemilik'];
                $username = trim($_POST['username']);
                $email = trim($_POST['email']);
                $nama = trim($_POST['nama']);
                $no_telp = trim($_POST['no_telp']);
                $password = !empty($_POST['password']) ? $_POST['password'] : null;
                $confirm_password = !empty($_POST['confirm_password']) ? $_POST['confirm_password'] : null;

                // Basic validation
                if (empty($username) || empty($email) || empty($nama) || empty($no_telp)) {
                    throw new Exception("Semua field harus diisi");
                }

                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    throw new Exception("Format email tidak valid");
                }

                if (!preg_match('/^[0-9]{10,15}$/', $no_telp)) {
                    throw new Exception("Format nomor telepon tidak valid (10-15 digit)");
                }

                // Password validation if provided
                if ($password || $confirm_password) {
                    if ($password !== $confirm_password) {
                        throw new Exception("Password tidak cocok");
                    }

                    if (strlen($password) < 8) {
                        throw new Exception("Password minimal 8 karakter");
                    }
                }

                // Update both user account and pemilik profile
                $result = $this->model->updatePemilikWithUser(
                    $id_pemilik,
                    $username,
                    $email,
                    $password,
                    $nama,
                    $no_telp
                );

                if ($result) {
                    $_SESSION['success'] = "Data pemilik berhasil diperbarui!";
                    header("Location: ../views/admin/tabel_pemilik.php");
                    exit();
                } else {
                    throw new Exception("Gagal memperbarui data pemilik");
                }
            } catch (Exception $e) {
                error_log("Error: " . $e->getMessage()); 
                $_SESSION['error'] = $e->getMessage();
                header("Location: ../views/admin/edit_pemilik.php?id=" . $_POST['id_pemilik']);
                exit();
            }
        }
    }

    // Delete Pemilik
    public function delete($id_pemilik) {
        try {
            if (empty($id_pemilik) || !is_numeric($id_pemilik)) {
                throw new Exception("ID pemilik tidak valid");
            }

            $result = $this->model->delete($id_pemilik);

            if ($result) {
                $_SESSION['success'] = "Pemilik berhasil dihapus!";
            } else {
                throw new Exception("Gagal menghapus pemilik");
            }
        } catch (Exception $e) {
            error_log("Error: " . $e->getMessage()); 
            $_SESSION['error'] = $e->getMessage();
        }
        
        header("Location: ../views/admin/tabel_pemilik.php");
        exit();
    }

    // Get all pemilik for admin
    public function getAllPemilik() {
        return $this->model->getAllWithUserInfo();
    }

    // Get pemilik by ID
    public function getPemilikById($id_pemilik) {
        return $this->model->getById($id_pemilik);
    }
}

// Handle request
session_start();
$db = getDBConnection();
$controller = new PemilikBarangController($db);

if (isset($_GET['action'])) {
    switch ($_GET['action']) {
        case 'create':
            $controller->create();
            break;
        case 'update':
            $controller->update();
            break;
        case 'delete':
            if (isset($_GET['id'])) {
                $controller->delete($_GET['id']);
            }
            break;
    }
}