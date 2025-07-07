<?php
session_start();
require_once '../../config/connect_db.php';
require_once '../../models/admin/Sewa.php';
require_once '../../models/admin/Transaksi.php';
require_once '../../models/admin/Barang.php';

// Validasi user login
if (!isset($_SESSION['user']) || !isset($_SESSION['user']['id_penyewa'])) {
    $_SESSION['sewa_error'] = "Anda harus login terlebih dahulu";
    header("Location: index.php");
    exit();
}

try {
    // Validasi input
    if ($_SERVER['REQUEST_METHOD'] != 'POST') {
        throw new Exception("Metode request tidak valid");
    }

    $required = ['id_barang', 'tanggal_sewa', 'tanggal_kembali', 'harga_sewa'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            throw new Exception("Field $field harus diisi");
        }
    }

    if (!isset($_FILES['bukti_pembayaran']) || $_FILES['bukti_pembayaran']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception("Bukti pembayaran harus diupload");
    }

    // Proses data
    $id_barang = $_POST['id_barang'];
    $id_penyewa = $_SESSION['user']['id_penyewa'];
    $tanggal_sewa = $_POST['tanggal_sewa'];
    $tanggal_kembali = $_POST['tanggal_kembali'];
    $harga_sewa = $_POST['harga_sewa'];

    // Hitung total bayar
    $start = new DateTime($tanggal_sewa);
    $end = new DateTime($tanggal_kembali);
    $days = $start->diff($end)->days + 1;
    $total_bayar = $days * $harga_sewa;
    $admin_fee = $total_bayar * 0.125;
    $total_with_fee = $total_bayar + $admin_fee;

    // Upload bukti pembayaran
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

    // Buat koneksi database dan model
    $db = getDBConnection();
    $sewaModel = new Sewa($db);
    $transaksiModel = new Transaksi($db);
    $barangModel = new Barang($db);

    // Buat sewa
    $id_sewa = $sewaModel->create(
        $id_barang,
        $id_penyewa,
        $tanggal_sewa,
        $tanggal_kembali,
        $total_bayar
    );

    // Buat transaksi
    $transaksiModel->create(
        $id_sewa,
        1, // Jumlah
        $total_with_fee,
        $gambarJson
    );

    // Update status barang
    $barangModel->updateStatus($id_barang, 0);

    // Set session untuk halaman transaksi
    $_SESSION['sewa_success'] = [
        'id_sewa' => $id_sewa,
        'total_bayar' => $total_with_fee,
        'tanggal_sewa' => $tanggal_sewa,
        'tanggal_kembali' => $tanggal_kembali
    ];

    header("Location: transaksi.php");
    exit();

} catch (Exception $e) {
    $_SESSION['sewa_error'] = $e->getMessage();
    header("Location: index.php");
    exit();
}