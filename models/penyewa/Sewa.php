<?php
class Sewa {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Mendapatkan semua transaksi sewa oleh penyewa tertentu
     */
    public function getAllByPenyewa($id_penyewa, $filter = []) {
        $query = "SELECT s.*, b.nama_barang, b.gambar, 
                         t.id_transaksi, t.tanggal as tanggal_transaksi, 
                         t.totalBayar, t.gambar as bukti_pembayaran, t.status as status_transaksi
                  FROM sewa s
                  JOIN barang b ON s.id_barang = b.id_barang
                  JOIN transaksi t ON s.id_sewa = t.id_sewa
                  WHERE s.id_penyewa = ?";
        
        $params = [$id_penyewa];
        $types = "i";
        
        // Tambahkan filter status jika ada
        if (!empty($filter['status'])) {
            $query .= " AND t.status = ?";
            $params[] = $filter['status'];
            $types .= "i";
        }
        
        // Filter tanggal jika ada
        if (!empty($filter['start_date'])) {
            $query .= " AND s.tanggalSewa >= ?";
            $params[] = $filter['start_date'];
            $types .= "s";
        }
        
        if (!empty($filter['end_date'])) {
            $query .= " AND s.tanggalKembali <= ?";
            $params[] = $filter['end_date'];
            $types .= "s";
        }
        
        $query .= " ORDER BY s.tanggalSewa DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $transactions = [];
        while ($row = $result->fetch_assoc()) {
            // Decode gambar barang
            $row['gambar_barang'] = json_decode($row['gambar'], true)[0] ?? null;
            
            // Decode bukti pembayaran
            $row['bukti_pembayaran'] = json_decode($row['bukti_pembayaran'], true)[0] ?? null;
            
            // Format status
            $row['status_text'] = $this->getStatusText($row['status_transaksi']);
            
            $transactions[] = $row;
        }
        
        return $transactions;
    }

    /**
     * Mendapatkan detail transaksi oleh penyewa
     */
    public function getDetailByPenyewa($id_sewa, $id_penyewa) {
    // Pastikan tidak ada output sebelumnya
    if (ob_get_length()) ob_clean();
    
    $query = "SELECT s.*, b.nama_barang, b.deskripsi, b.harga_sewa, b.gambar,
                 t.id_transaksi, t.tanggal as tanggal_transaksi, 
                 t.totalBayar, t.gambar as bukti_pembayaran, t.status as status_transaksi,
                 p.nama as nama_penyewa
          FROM sewa s
          JOIN barang b ON s.id_barang = b.id_barang
          JOIN transaksi t ON s.id_sewa = t.id_sewa
          JOIN penyewa p ON s.id_penyewa = p.id_penyewa
          WHERE s.id_sewa = ? AND s.id_penyewa = ?";
    
    $stmt = $this->conn->prepare($query);
    
    if ($stmt === false) {
        http_response_code(500);
        die(json_encode(['error' => 'Internal server error', 'details' => $this->conn->error]));
    }
    
    $stmt->bind_param("ii", $id_sewa, $id_penyewa);
    
    if (!$stmt->execute()) {
        http_response_code(500);
        die(json_encode(['error' => 'Failed to execute query', 'details' => $stmt->error]));
    }
    
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        http_response_code(404);
        die(json_encode(['error' => 'Transaksi tidak ditemukan']));
    }
    
    $transaction = $result->fetch_assoc();
    
    // Process images
    $gambar = json_decode($transaction['gambar'], true);
    $transaction['gambar_barang'] = (!empty($gambar) && is_array($gambar)) ? $gambar[0] : null;
    
    $bukti = json_decode($transaction['bukti_pembayaran'], true);
    $transaction['bukti_pembayaran'] = (!empty($bukti) && is_array($bukti)) ? $bukti[0] : null;
    
    $transaction['status_text'] = $this->getStatusText($transaction['status_transaksi']);
    
    // Hitung durasi
    $start = new DateTime($transaction['tanggalSewa']);
    $end = new DateTime($transaction['tanggalKembali']);
    $transaction['durasi'] = $start->diff($end)->days + 1;
    
    header('Content-Type: application/json');
    echo json_encode($transaction);
    exit;
}


    /**
     * Membatalkan sewa oleh penyewa
     */
    public function cancelByPenyewa($id_sewa, $id_penyewa) {
        // Hanya bisa dibatalkan jika status masih pending (0)
        $query = "UPDATE transaksi t
                  JOIN sewa s ON t.id_sewa = s.id_sewa
                  SET t.status = 3, -- 3 = cancelled
                      b.status = 1  -- 1 = available
                  WHERE s.id_sewa = ? 
                  AND s.id_penyewa = ?
                  AND t.status = 0"; // 0 = pending
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ii", $id_sewa, $id_penyewa);
        
        if (!$stmt->execute()) {
            throw new Exception("Gagal membatalkan sewa: " . $stmt->error);
        }
        
        return $stmt->affected_rows > 0;
    }

    private function getStatusText($status) {
        $statuses = [
            0 => 'Pending',
            1 => 'Approved',
            2 => 'Completed',
            3 => 'Cancelled',
            4 => 'Rejected'
        ];
        
        return $statuses[$status] ?? 'Unknown';
    }
}