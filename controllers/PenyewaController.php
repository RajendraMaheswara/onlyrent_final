<?php
include_once '../config/connect_db.php';
include_once '../models/Penyewa.php';

class PenyewaController {
    private $model;

    public function __construct($db) {
        $this->model = new Penyewa($db);
    }

    // Create Penyewa (for admin)
    public function create() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            try {
                // Validate all inputs
                $username = trim($_POST['username']);
                $email = trim($_POST['email']);
                $password = $_POST['password'];
                $confirm_password = $_POST['confirm_password'];
                $nama = trim($_POST['nama']);
                $alamat = trim($_POST['alamat']);
                $no_telp = trim($_POST['no_telp']);

                // Basic validation
                if (empty($username) || empty($email) || empty($password) || 
                    empty($nama) || empty($alamat) || empty($no_telp)) {
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

                // Create both user account and penyewa profile
                $result = $this->model->createWithUser(
                    $username, 
                    $email, 
                    $password, 
                    $nama, 
                    $alamat, 
                    $no_telp
                );

                if ($result) {
                    $_SESSION['success'] = "Penyewa baru berhasil ditambahkan!";
                    header("Location: ../views/admin/tabel_penyewa.php");
                    exit();
                } else {
                    throw new Exception("Gagal menambahkan penyewa baru");
                }
            } catch (Exception $e) {
                error_log("Error: " . $e->getMessage()); 
                $_SESSION['error'] = $e->getMessage();
                header("Location: ../views/admin/tambah_penyewa.php");
                exit();
            }
        }
    }

    // Update Penyewa
    public function update() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            try {
                $id_penyewa = $_POST['id_penyewa'];
                $username = trim($_POST['username']);
                $email = trim($_POST['email']);
                $nama = trim($_POST['nama']);
                $alamat = trim($_POST['alamat']);
                $no_telp = trim($_POST['no_telp']);
                $password = !empty($_POST['password']) ? $_POST['password'] : null;
                $confirm_password = !empty($_POST['confirm_password']) ? $_POST['confirm_password'] : null;

                // Basic validation
                if (empty($username) || empty($email) || empty($nama) || empty($alamat) || empty($no_telp)) {
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

                // Update both user account and penyewa profile
                $result = $this->model->updatePenyewaWithUser(
                    $id_penyewa,
                    $username,
                    $email,
                    $password,
                    $nama,
                    $alamat,
                    $no_telp
                );

                if ($result) {
                    $_SESSION['success'] = "Data penyewa berhasil diperbarui!";
                    header("Location: ../views/admin/tabel_penyewa.php");
                    exit();
                } else {
                    throw new Exception("Gagal memperbarui data penyewa");
                }
            } catch (Exception $e) {
                error_log("Error: " . $e->getMessage()); 
                $_SESSION['error'] = $e->getMessage();
                header("Location: ../views/admin/edit_penyewa.php?id=" . $_POST['id_penyewa']);
                exit();
            }
        }
    }

    // Delete Penyewa
    public function delete($id_penyewa) {
        try {
            if (empty($id_penyewa) || !is_numeric($id_penyewa)) {
                throw new Exception("ID penyewa tidak valid");
            }

            $result = $this->model->delete($id_penyewa);

            if ($result) {
                $_SESSION['success'] = "Penyewa berhasil dihapus!";
            } else {
                throw new Exception("Gagal menghapus penyewa");
            }
        } catch (Exception $e) {
            error_log("Error: " . $e->getMessage()); 
            $_SESSION['error'] = $e->getMessage();
        }
        
        header("Location: ../views/admin/tabel_penyewa.php");
        exit();
    }

    // Get all penyewa for admin
    public function getAllPenyewa() {
        return $this->model->getAllWithUserInfo();
    }

    // Get penyewa by ID
    public function getPenyewaById($id_penyewa) {
        return $this->model->getById($id_penyewa);
    }
}

// Handle request
session_start();
$db = getDBConnection();
$controller = new PenyewaController($db);

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