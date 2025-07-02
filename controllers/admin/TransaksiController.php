<?php
include_once '../../config/connect_db.php';
include_once '../../models/admin/Transaksi.php';
include_once '../../models/admin/Sewa.php';
include_once '../../models/admin/Barang.php';
include_once '../../models/admin/PemilikBarang.php';
include_once '../../models/admin/Penyewa.php';
include_once '../../models/admin/Pengguna.php';

class TransaksiController {
    private $model;
    private $barangModel;
    private $pemilikModel;
    private $sewaModel;
    private $penyewaModel;
    private $penggunaModel;

    public function __construct($db) {
        $this->model = new Transaksi($db);
        $this->barangModel = new Barang($db);
        $this->pemilikModel = new PemilikBarang($db);
        $this->sewaModel = new Sewa($db);
        $this->penyewaModel = new Penyewa($db);
        $this->penggunaModel = new Pengguna($db);
    }

    // Create new transaction
    public function create() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            try {
                $id_sewa = $_POST['id_sewa'];
                
                // Get sewa data
                $sewa = $this->sewaModel->getById($id_sewa);
                if (!$sewa) {
                    throw new Exception("Data sewa tidak ditemukan");
                }
                
                // Calculate total with 12.5% fee
                $total_sewa = $sewa['totalBayar'];
                $admin_fee = $total_sewa * 0.125;
                $total_bayar = $total_sewa + $admin_fee;
                
                // Handle file upload
                if (!isset($_FILES['bukti_pembayaran']) || $_FILES['bukti_pembayaran']['error'] !== UPLOAD_ERR_OK) {
                    throw new Exception("Bukti pembayaran harus diupload");
                }
                
                $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/assets/images/transaksi/';
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                
                $fileName = $_FILES['bukti_pembayaran']['name'];
                $fileExt = pathinfo($fileName, PATHINFO_EXTENSION);
                $newFileName = date('Ymd-His') . '-' . uniqid() . '.' . $fileExt;
                $targetPath = $uploadDir . $newFileName;
                
                if (!move_uploaded_file($_FILES['bukti_pembayaran']['tmp_name'], $targetPath)) {
                    throw new Exception("Gagal mengupload bukti pembayaran");
                }
                
                $gambarJson = json_encode([$newFileName]);
                
                // Create transaction
                $id_transaksi = $this->model->create(
                    $id_sewa,
                    1, // Jumlah (default 1)
                    $total_bayar,
                    $gambarJson
                );

                $_SESSION['success'] = "Transaksi berhasil dibuat! Total Bayar: Rp " . number_format($total_bayar, 0, ',', '.');
                header("Location: ../../views/admin/tabel_transaksi.php");
                exit();
            } catch (Exception $e) {
                $_SESSION['error'] = $e->getMessage();
                header("Location: ../../views/admin/tambah_transaksi.php");
                exit();
            }
        }
    }

    // Update transaction
    public function update() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            try {
                $id_transaksi = $_POST['id_transaksi'];
                $status = $_POST['status'];
                
                // Get transaction data
                $transaksi = $this->model->getById($id_transaksi);
                if (!$transaksi) {
                    throw new Exception("Data transaksi tidak ditemukan");
                }
                
                // Get related data
                $sewa = $this->sewaModel->getById($transaksi['id_sewa']);
                $barang = $this->barangModel->getById($sewa['id_barang']);
                $pemilik = $this->pemilikModel->getById($barang['id_pemilik']);
                $penyewa = $this->penyewaModel->getById($sewa['id_penyewa']);
                
                // Calculate fee distribution
                $total_sewa = $sewa['totalBayar'];
                $admin_fee = $transaksi['totalBayar'] - $total_sewa;
                $pemilik_receive = $total_sewa * 0.875; // Owner gets 87.5%
                $platform_income = $total_sewa * 0.25;  // Platform gets 25% (12.5% from both sides)
                
                // Update transaction status
                $data = [
                    'status' => $status
                ];
                
                $this->model->update($id_transaksi, $data);
                
                // If status changed to success (1), process the payment distribution
                if ($status == 1) {
                    // Update owner's balance (add pemilik_receive)
                    // $this->updateOwnerBalance($pemilik['id_pemilik'], $pemilik_receive);
                    
                    // Update platform income
                    // $this->updatePlatformIncome($platform_income);
                    
                    // You would need to implement these methods based on your database structure
                    // For example:
                    // $this->pemilikModel->addBalance($pemilik['id_pemilik'], $pemilik_receive);
                    // $this->updateSystemBalance($platform_income);
                }
                
                $_SESSION['success'] = "Status transaksi berhasil diperbarui!";
                header("Location: ../../views/admin/tabel_transaksi.php");
                exit();
            } catch (Exception $e) {
                $_SESSION['error'] = $e->getMessage();
                header("Location: ../../views/admin/edit_transaksi.php?id=" . $_POST['id_transaksi']);
                exit();
            }
        }
    }

    // Delete transaction
    // public function delete($id_transaksi) {
    //     try {
    //         if (empty($id_transaksi) || !is_numeric($id_transaksi)) {
    //             throw new Exception("ID transaksi tidak valid");
    //         }

    //         // Get transaction data to delete associated image
    //         $transaksi = $this->model->getById($id_transaksi);
    //         $images = json_decode($transaksi['gambar'], true) ?? [];
            
    //         // Delete images from server
    //         $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/assets/images/transaksi/';
    //         foreach ($images as $image) {
    //             $filePath = $uploadDir . $image;
    //             if (file_exists($filePath)) {
    //                 unlink($filePath);
    //             }
    //         }

    //         $result = $this->model->delete($id_transaksi);

    //         if ($result) {
    //             $_SESSION['success'] = "Transaksi berhasil dihapus!";
    //         } else {
    //             throw new Exception("Gagal menghapus transaksi");
    //         }
    //     } catch (Exception $e) {
    //         $_SESSION['error'] = $e->getMessage();
    //     }
        
    //     header("Location: ../views/admin/tabel_transaksi.php");
    //     exit();
    // }

    // Get transaction by ID
    public function getById($id_transaksi) {
        return $this->model->getById($id_transaksi);
    }

    // Get all transactions
    public function getAll($filter = []) {
        return $this->model->getAll($filter);
    }

    // Update transaction status
    public function updateStatus($id_transaksi, $status) {
        try {
            if (empty($id_transaksi) || !is_numeric($id_transaksi)) {
                throw new Exception("ID transaksi tidak valid");
            }

            $result = $this->model->updateStatus($id_transaksi, $status);

            if ($result) {
                $_SESSION['success'] = "Status transaksi berhasil diperbarui!";
            } else {
                throw new Exception("Gagal memperbarui status transaksi");
            }
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }
        
        header("Location: ../../views/admin/tabel_transaksi.php");
        exit();
    }
}

// Handle request
session_start();
$db = getDBConnection();
$controller = new TransaksiController($db);

if (isset($_GET['action'])) {
    switch ($_GET['action']) {
        case 'create':
            $controller->create();
            break;
        case 'update':
            $controller->update();
            break;
        // case 'delete':
        //     if (isset($_GET['id'])) {
        //         $controller->delete($_GET['id']);
        //     }
        //     break;
        case 'update_status':
            if (isset($_GET['id']) && isset($_GET['status'])) {
                $controller->updateStatus($_GET['id'], $_GET['status']);
            }
            break;
    }
}