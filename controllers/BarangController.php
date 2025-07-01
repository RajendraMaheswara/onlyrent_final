<?php
include_once '../config/connect_db.php';
include_once '../models/Barang.php';

function sanitizeFileName($name) {
    $name = preg_replace('/[^a-zA-Z0-9]/', '-', $name);
    $name = preg_replace('/-+/', '-', $name);
    $name = trim($name, '-');
    return strtolower($name);
}

class BarangController {
    private $model;

    public function __construct($db) {
        $this->model = new Barang($db);
    }

    // Create new barang
    // In BarangController.php - update the create method
    public function create() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            try {
                $id_pemilik = $_POST['id_pemilik']; // Sudah ada di form
                $nama_barang = trim($_POST['nama_barang']);
                $deskripsi = trim($_POST['deskripsi']);
                $harga_sewa = trim($_POST['harga_sewa']);
                
                // Validasi id_pemilik
                if (empty($id_pemilik) || !is_numeric($id_pemilik)) {
                    throw new Exception("Pemilik barang tidak valid");
                }
                
                // Handle multiple file uploads
                $gambarPaths = [];
                if (isset($_FILES['gambar'])) {
                    $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/assets/images/barang/';
                    
                    // Buat direktori jika belum ada
                    if (!file_exists($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }
                    
                    $gambarPaths = [];
                    foreach ($_FILES['gambar']['tmp_name'] as $key => $tmp_name) {
                        // Hanya proses file yang benar-benar diupload
                        if ($_FILES['gambar']['error'][$key] === UPLOAD_ERR_OK) {
                            $fileName = $_FILES['gambar']['name'][$key];
                            $fileExt = pathinfo($fileName, PATHINFO_EXTENSION);
                            $newFileName = date('His-dmY') . '-' . sanitizeFileName($nama_barang) . '-' . ($key + 1) . '.' . $fileExt;
                            $targetPath = $uploadDir . $newFileName;
                            
                            if (move_uploaded_file($tmp_name, $targetPath)) {
                                $gambarPaths[] = $newFileName;
                            }
                        }
                    }
                    
                    $gambarJson = json_encode($gambarPaths);
                }
                
                // Validation
                if (empty($nama_barang) || empty($deskripsi) || empty($harga_sewa)) {
                    throw new Exception("Semua field harus diisi");
                }

                if (!is_numeric($harga_sewa) || $harga_sewa <= 0) {
                    throw new Exception("Harga sewa harus berupa angka positif");
                }

                if (empty($gambarPaths)) {
                    throw new Exception("Minimal 1 gambar harus diupload");
                }

                $id_barang = $this->model->create($id_pemilik, $nama_barang, $gambarJson, $deskripsi, $harga_sewa);

                $_SESSION['success'] = "Barang berhasil ditambahkan!";
                header("Location: ../views/admin/tabel_barang.php");
                exit();
            } catch (Exception $e) {
                error_log("Error: " . $e->getMessage());
                $_SESSION['error'] = $e->getMessage();
                header("Location: ../views/admin/tabel_barang.php");
                exit();
            }
        }
    }

    // Update barang
    // Update barang
    public function update() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            try {
                $id_barang = $_POST['id_barang'];
                $nama_barang = trim($_POST['nama_barang']);
                $deskripsi = trim($_POST['deskripsi']);
                $harga_sewa = trim($_POST['harga_sewa']);
                
                // Get existing images
                $existingBarang = $this->model->getById($id_barang);
                $existingImages = json_decode($existingBarang['gambar'], true) ?? [];
                
                // Handle deleted images
                $deletedImages = [];
                if (!empty($_POST['deleted_images'])) {
                    $deletedImages = json_decode($_POST['deleted_images'], true) ?? [];
                    
                    // Remove deleted images from server
                    $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/assets/images/barang/';
                    foreach ($deletedImages as $image) {
                        $filePath = $uploadDir . $image;
                        if (file_exists($filePath)) {
                            unlink($filePath);
                        }
                    }
                }
                
                // Keep only non-deleted existing images
                $remainingImages = array_diff($existingImages, $deletedImages);
                $gambarPaths = array_values($remainingImages); // Reset array keys
                
                if (!empty($_FILES['gambar']['name'][0])) {
                    $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/assets/images/barang/';
                    
                    // Create directory if not exists
                    if (!file_exists($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }
                    
                    foreach ($_FILES['gambar']['tmp_name'] as $key => $tmp_name) {
                        $fileName = $_FILES['gambar']['name'][$key];
                        $fileSize = $_FILES['gambar']['size'][$key];
                        $fileError = $_FILES['gambar']['error'][$key];
                        
                        // Validate file
                        if ($fileError === UPLOAD_ERR_OK) {
                            // Generate new filename: jammenit-tanggalbulantahun-barang-(index).ext
                            $fileExt = pathinfo($fileName, PATHINFO_EXTENSION);
                            $currentTime = date('His-dmY');
                            $newFileName = $currentTime . '-' . sanitizeFileName($nama_barang) . '-' . ($key + 1) . '.' . $fileExt;
                            
                            $targetPath = $uploadDir . $newFileName;
                            
                            if (move_uploaded_file($tmp_name, $targetPath)) {
                                $gambarPaths[] = $newFileName;
                            } else {
                                throw new Exception("Gagal mengupload gambar " . $fileName);
                            }
                        } else {
                            throw new Exception("Error uploading gambar " . $fileName);
                        }
                    }
                }

                // Convert array to JSON for storage
                $gambarJson = json_encode($gambarPaths);
                
                // Validation
                if (empty($nama_barang) || empty($deskripsi) || empty($harga_sewa)) {
                    throw new Exception("Semua field harus diisi");
                }

                if (!is_numeric($harga_sewa) || $harga_sewa <= 0) {
                    throw new Exception("Harga sewa harus berupa angka positif");
                }

                $this->model->update($id_barang, $nama_barang, $gambarJson, $deskripsi, $harga_sewa);

                $_SESSION['success'] = "Barang berhasil diperbarui!";
                header("Location: ../views/admin/tabel_barang.php");
                exit();
            } catch (Exception $e) {
                error_log("Error: " . $e->getMessage());
                $_SESSION['error'] = $e->getMessage();
                header("Location: ../views/admin/edit_barang.php?id=" . $_POST['id_barang']);
                exit();
            }
        }
    }

    // Delete barang
    public function delete($id_barang) {
        try {
            if (empty($id_barang) || !is_numeric($id_barang)) {
                throw new Exception("ID barang tidak valid");
            }

            $result = $this->model->delete($id_barang);

            if ($result) {
                $_SESSION['success'] = "Barang berhasil dihapus!";
            } else {
                throw new Exception("Gagal menghapus barang");
            }
        } catch (Exception $e) {
            error_log("Error: " . $e->getMessage());
            $_SESSION['error'] = $e->getMessage();
        }
        
        header("Location: ../views/admin/tabel_barang.php");
        exit();
    }

    // Get barang by ID
    public function getById($id_barang) {
        return $this->model->getById($id_barang);
    }

    // Get all barang by pemilik
    public function getAllByPemilik($id_pemilik) {
        return $this->model->getAllByPemilik($id_pemilik);
    }

    // Get all available barang
    public function getAllAvailable() {
        return $this->model->getAllAvailable();
    }

    // Update status
    public function updateStatus($id_barang, $status) {
        try {
            if (empty($id_barang) || !is_numeric($id_barang)) {
                throw new Exception("ID barang tidak valid");
            }

            $result = $this->model->updateStatus($id_barang, $status);

            if ($result) {
                $_SESSION['success'] = "Status barang berhasil diperbarui!";
            } else {
                throw new Exception("Gagal memperbarui status barang");
            }
        } catch (Exception $e) {
            error_log("Error: " . $e->getMessage());
            $_SESSION['error'] = $e->getMessage();
        }
        
        header("Location: ../views/admin/tabel_barang.php");
        exit();
    }
}

// Handle request
session_start();
$db = getDBConnection();
$controller = new BarangController($db);

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
        case 'update_status':
            if (isset($_GET['id']) && isset($_GET['status'])) {
                $controller->updateStatus($_GET['id'], $_GET['status']);
            }
            break;
    }
}