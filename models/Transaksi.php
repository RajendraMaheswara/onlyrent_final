<?php
class Transaksi {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Create new transaksi
    public function create($id_sewa, $jumlah, $totalBayar, $status) {
        $stmt = $this->conn->prepare("INSERT INTO transaksi (id_sewa, jumlah, tanggal, totalBayar, status) VALUES (?, ?, NOW(), ?, ?)");
        $stmt->bind_param("iiii", $id_sewa, $jumlah, $totalBayar, $status);
        
        if ($stmt->execute()) {
            return $this->conn->insert_id;
        } else {
            throw new Exception("Gagal menambahkan transaksi: " . $stmt->error);
        }
    }

    // Get transaksi by ID
    public function getById($id_transaksi) {
        $stmt = $this->conn->prepare("SELECT * FROM transaksi WHERE id_transaksi = ?");
        $stmt->bind_param("i", $id_transaksi);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    // Get all transaksi by sewa ID
    public function getAllBySewa($id_sewa) {
        $stmt = $this->conn->prepare("SELECT * FROM transaksi WHERE id_sewa = ? ORDER BY tanggal DESC");
        $stmt->bind_param("i", $id_sewa);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // Update transaksi status
    public function updateStatus($id_transaksi, $status) {
        $stmt = $this->conn->prepare("UPDATE transaksi SET status = ? WHERE id_transaksi = ?");
        $stmt->bind_param("ii", $status, $id_transaksi);
        
        if (!$stmt->execute()) {
            throw new Exception("Gagal memperbarui status transaksi: " . $stmt->error);
        }
        return true;
    }

    // Get all transaksi with optional filters
    public function getAll($filter = []) {
        $query = "SELECT t.*, s.id_barang, s.id_penyewa, b.nama_barang, p.nama as nama_penyewa 
                  FROM transaksi t
                  JOIN sewa s ON t.id_sewa = s.id_sewa
                  JOIN barang b ON s.id_barang = b.id_barang
                  JOIN penyewa p ON s.id_penyewa = p.id_penyewa
                  WHERE 1=1";
        
        $params = [];
        $types = "";
        
        // Filter by status
        if (isset($filter['status'])) {
            $query .= " AND t.status = ?";
            $params[] = $filter['status'];
            $types .= "i";
        }
        
        // Filter by sewa ID
        if (isset($filter['id_sewa'])) {
            $query .= " AND t.id_sewa = ?";
            $params[] = $filter['id_sewa'];
            $types .= "i";
        }
        
        // Filter by barang ID
        if (isset($filter['id_barang'])) {
            $query .= " AND s.id_barang = ?";
            $params[] = $filter['id_barang'];
            $types .= "i";
        }
        
        // Filter by penyewa ID
        if (isset($filter['id_penyewa'])) {
            $query .= " AND s.id_penyewa = ?";
            $params[] = $filter['id_penyewa'];
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
}
?>