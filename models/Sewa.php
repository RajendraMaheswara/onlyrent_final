<?php
class Sewa {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Create new rental
    public function create($id_barang, $id_penyewa, $tanggal_sewa, $tanggal_kembali, $total_bayar) {
        $stmt = $this->conn->prepare("INSERT INTO sewa 
            (id_barang, id_penyewa, tanggalSewa, tanggalKembali, totalBayar, status) 
            VALUES (?, ?, ?, ?, ?, 1)");
        $stmt->bind_param("iissi", $id_barang, $id_penyewa, $tanggal_sewa, $tanggal_kembali, $total_bayar);
        
        if ($stmt->execute()) {
            return $this->conn->insert_id;
        } else {
            throw new Exception("Gagal membuat sewa: " . $stmt->error);
        }
    }

    // Update rental
    public function update($id_sewa, $data) {
    $query = "UPDATE sewa SET ";
    $params = [];
    $types = "";
    
    foreach ($data as $key => $value) {
        $query .= "$key = ?, ";
        $params[] = $value;
        $types .= $this->getParamType($value);
    }
    
    $query = rtrim($query, ", ");
    $query .= " WHERE id_sewa = ?";
    $params[] = $id_sewa;
    $types .= "i";
    
    $stmt = $this->conn->prepare($query);
    $stmt->bind_param($types, ...$params);
    
    if (!$stmt->execute()) {
        throw new Exception("Gagal memperbarui sewa: " . $stmt->error);
    }
    return true;
}

    // Get rental by ID
    public function getById($id_sewa) {
        $stmt = $this->conn->prepare("SELECT s.*, b.nama_barang, p.nama as nama_penyewa 
                                    FROM sewa s
                                    JOIN barang b ON s.id_barang = b.id_barang
                                    JOIN penyewa p ON s.id_penyewa = p.id_penyewa
                                    WHERE s.id_sewa = ?");
        $stmt->bind_param("i", $id_sewa);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    // Get all rentals
    public function getAll($filter = []) {
        $query = "SELECT s.*, b.nama_barang, p.nama as nama_penyewa 
                 FROM sewa s
                 JOIN barang b ON s.id_barang = b.id_barang
                 JOIN penyewa p ON s.id_penyewa = p.id_penyewa
                 WHERE 1=1";
        
        $params = [];
        $types = "";
        
        // Add filters
        if (!empty($filter['id_penyewa'])) {
            $query .= " AND s.id_penyewa = ?";
            $params[] = $filter['id_penyewa'];
            $types .= "i";
        }
        
        if (!empty($filter['id_barang'])) {
            $query .= " AND s.id_barang = ?";
            $params[] = $filter['id_barang'];
            $types .= "i";
        }
        
        if (!empty($filter['status'])) {
            $query .= " AND s.status = ?";
            $params[] = $filter['status'];
            $types .= "i";
        }
        
        $query .= " ORDER BY s.tanggalSewa DESC";
        
        $stmt = $this->conn->prepare($query);
        
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // Update rental status
    public function updateStatus($id_sewa, $status) {
        $stmt = $this->conn->prepare("UPDATE sewa SET status = ? WHERE id_sewa = ?");
        $stmt->bind_param("ii", $status, $id_sewa);
        
        if (!$stmt->execute()) {
            throw new Exception("Gagal mengupdate status sewa: " . $stmt->error);
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