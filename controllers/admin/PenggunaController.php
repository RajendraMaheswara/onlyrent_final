<?php
include_once '../../config/connect_db.php';
include_once '../../models/admin/Pengguna.php';

class PenggunaController {
    private $model;

    public function __construct($db) {
        $this->model = new Pengguna($db);
    }

    // Create Pengguna
    public function create() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            try {
                // Validasi input form
                $username = trim($_POST['username']);
                $email = trim($_POST['email']);
                $password = $_POST['password'];
                $confirm_password = $_POST['confirm_password'];
                $role = $_POST['role'];

                // Validasi dasar
                if (empty($username) || empty($email) || empty($password) || empty($role)) {
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

                // Hash password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // Panggil model untuk menambah pengguna baru
                $result = $this->model->create($username, $email, $hashed_password, $role);

                if ($result) {
                    $_SESSION['success'] = "User berhasil ditambahkan!";
                    header("Location: ../../views/admin/tabel_user.php");
                    exit();
                } else {
                    throw new Exception("Gagal menambahkan user");
                }
            } catch (Exception $e) {
                error_log("Error: " . $e->getMessage()); 
                $_SESSION['error'] = $e->getMessage();
                header("Location: ../../views/admin/tambah_user.php"); // Redirect kembali ke form tambah
                exit();
            }
        }
    }

    // Edit Pengguna
    public function edit() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            try {
                // Validasi input form
                $user_id = $_POST['user_id'];
                $username = trim($_POST['username']);
                $email = trim($_POST['email']);
                $role = $_POST['role'];
                $password = !empty($_POST['password']) ? $_POST['password'] : null;
                $confirm_password = !empty($_POST['confirm_password']) ? $_POST['confirm_password'] : null;

                // Validasi dasar
                if (empty($username) || empty($email) || empty($role)) {
                    throw new Exception("Semua field wajib harus diisi");
                }

                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    throw new Exception("Format email tidak valid");
                }

                // Validasi password jika diisi
                if ($password || $confirm_password) {
                    if ($password !== $confirm_password) {
                        throw new Exception("Password tidak cocok");
                    }

                    if (strlen($password) < 8) {
                        throw new Exception("Password minimal 8 karakter");
                    }
                    
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                }

                // Panggil model untuk update pengguna
                $result = $this->model->update(
                    $user_id, 
                    $username, 
                    $email, 
                    $hashed_password ?? null, 
                    $role
                );

                if ($result) {
                    $_SESSION['success'] = "User berhasil diupdate!";
                    header("Location: ../views/admin/tabel_user.php");
                    exit();
                } else {
                    throw new Exception("Gagal mengupdate user");
                }
            } catch (Exception $e) {
                error_log("Error: " . $e->getMessage()); 
                $_SESSION['error'] = $e->getMessage();
                header("Location: ../../views/admin/edit_user.php?id=" . $_POST['user_id']);
                exit();
            }
        }
    }

    // Delete Pengguna
    public function delete($id) {
        try {
            // Validasi ID
            if (empty($id) || !is_numeric($id)) {
                throw new Exception("ID user tidak valid");
            }

            // Panggil model untuk delete pengguna
            $result = $this->model->delete($id);

            if ($result) {
                $_SESSION['success'] = "User berhasil dihapus!";
            } else {
                throw new Exception("Gagal menghapus user");
            }
        } catch (Exception $e) {
            error_log("Error: " . $e->getMessage()); 
            $_SESSION['error'] = $e->getMessage();
        }
        
        header("Location: ../../views/admin/tabel_user.php");
        exit();
    }
}

// Handle request
session_start();
$db = getDBConnection();
$controller = new PenggunaController($db);

if (isset($_GET['action'])) {
    switch ($_GET['action']) {
        case 'create':
            $controller->create();
            break;
        case 'edit':
            $controller->edit();
            break;
        case 'delete':
            if (isset($_GET['id'])) {
                $controller->delete($_GET['id']);
            }
            break;
    }
}