<?php
session_start();
require_once '../../config/connect_db.php';
require_once '../../models/Sewa.php';
require_once '../../models/Transaksi.php';
require_once '../../models/Barang.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

try {
    // Validate request method
    if ($_SERVER['REQUEST_METHOD'] != 'POST') {
        throw new Exception("Invalid request method");
    }

    // Validate user session
    if (!isset($_SESSION['user'])) {  // Fixed: Added the missing parenthesis here
        throw new Exception("Anda harus login terlebih dahulu");
    }

    // Validate input
    $required = ['id_barang', 'tanggal_sewa', 'tanggal_kembali', 'harga_sewa'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            throw new Exception("Field $field harus diisi");
        }
    }

    if (!isset($_FILES['bukti_pembayaran']) || $_FILES['bukti_pembayaran']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception("Bukti pembayaran harus diupload");
    }

    // Process the rental
    $id_barang = $_POST['id_barang'];
    $id_penyewa = $_SESSION['user']['id_penyewa'];
    $tanggal_sewa = $_POST['tanggal_sewa'];
    $tanggal_kembali = $_POST['tanggal_kembali'];
    $harga_sewa = $_POST['harga_sewa'];

    // Calculate rental days and total
    $start = new DateTime($tanggal_sewa);
    $end = new DateTime($tanggal_kembali);
    $days = $start->diff($end)->days + 1;
    $total_bayar = $days * $harga_sewa;
    $admin_fee = $total_bayar * 0.125;
    $total_with_fee = $total_bayar + $admin_fee;

    // Handle file upload
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

    // Create models
    $db = getDBConnection();
    $sewaModel = new Sewa($db);
    $transaksiModel = new Transaksi($db);
    $barangModel = new Barang($db);

    // Create rental
    $id_sewa = $sewaModel->create(
        $id_barang,
        $id_penyewa,
        $tanggal_sewa,
        $tanggal_kembali,
        $total_bayar
    );

    // Create transaction
    $transaksiModel->create(
        $id_sewa,
        1,
        $total_with_fee,
        $gambarJson
    );

    // Update item status
    $barangModel->updateStatus($id_barang, 0);

    $response['success'] = true;
    $response['message'] = "Sewa berhasil dibuat! Total Bayar: Rp " . number_format($total_with_fee, 0, ',', '.');

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
