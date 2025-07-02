<?php
class Transaksi {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Create new transaction
    public function create($id_sewa, $jumlah, $totalBayar, $gambar) {
        $stmt = $this->conn->prepare("INSERT INTO transaksi 
            (id_sewa, jumlah, tanggal, totalBayar, gambar, status) 
            VALUES (?, ?, NOW(), ?, ?, 0)");
        $stmt->bind_param("iiis", $id_sewa, $jumlah, $totalBayar, $gambar);
        
        if ($stmt->execute()) {
            return $this->conn->insert_id;
        } else {
            throw new Exception("Gagal membuat transaksi: " . $stmt->error);
        }
    }

    // Update transaction
    public function update($id_transaksi, $data) {
        $query = "UPDATE transaksi SET ";
        $params = [];
        $types = "";
        
        foreach ($data as $key => $value) {
            $query .= "$key = ?, ";
            $params[] = $value;
            $types .= $this->getParamType($value);
        }
        
        $query = rtrim($query, ", ");
        $query .= " WHERE id_transaksi = ?";
        $params[] = $id_transaksi;
        $types .= "i";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param($types, ...$params);
        
        if (!$stmt->execute()) {
            throw new Exception("Gagal memperbarui transaksi: " . $stmt->error);
        }
        return true;
    }

    // Get transaction by ID
    public function getById($id_transaksi) {
        $stmt = $this->conn->prepare("SELECT t.*, s.id_barang, s.id_penyewa 
                                    FROM transaksi t
                                    JOIN sewa s ON t.id_sewa = s.id_sewa
                                    WHERE t.id_transaksi = ?");
        $stmt->bind_param("i", $id_transaksi);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    // Get all transactions
    public function getAll($filter = []) {
        $query = "SELECT t.*, s.id_barang, s.id_penyewa, b.nama_barang, p.nama as nama_penyewa 
                 FROM transaksi t
                 JOIN sewa s ON t.id_sewa = s.id_sewa
                 JOIN barang b ON s.id_barang = b.id_barang
                 JOIN penyewa p ON s.id_penyewa = p.id_penyewa
                 WHERE 1=1";
        
        $params = [];
        $types = "";
        
        // Add filters
        if (!empty($filter['id_sewa'])) {
            $query .= " AND t.id_sewa = ?";
            $params[] = $filter['id_sewa'];
            $types .= "i";
        }
        
        if (!empty($filter['id_penyewa'])) {
            $query .= " AND s.id_penyewa = ?";
            $params[] = $filter['id_penyewa'];
            $types .= "i";
        }
        
        if (!empty($filter['status'])) {
            $query .= " AND t.status = ?";
            $params[] = $filter['status'];
            $types .= "i";
        }
        
        $query .= " ORDER BY t.tanggal DESC";
        
        $stmt = $this->conn->prepare($query);
        
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // Update transaction status
    public function updateStatus($id_transaksi, $status) {
        $stmt = $this->conn->prepare("UPDATE transaksi SET status = ? WHERE id_transaksi = ?");
        $stmt->bind_param("ii", $status, $id_transaksi);
        
        if (!$stmt->execute()) {
            throw new Exception("Gagal mengupdate status transaksi: " . $stmt->error);
        }
        return true;
    }

    // Helper function to determine parameter type
    private function getParamType($value) {
        if (is_int($value)) return "i";
        if (is_double($value)) return "d";
        return "s";
    }
}
?>