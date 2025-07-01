<?php
class Barang {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // In Barang.php
    public function getAll($filter = []) {
    $query = "SELECT * FROM barang WHERE 1=1";
    $params = [];
    $types = "";
    
    // Filter status (jika disediakan)
    if (!empty($filter['status']) && empty($filter['include_current'])) {
        $query .= " AND status = ?";
        $params[] = $filter['status'];
        $types .= "i";
    }
    
    // Sertakan barang tertentu (misalnya yang sedang disewa)
    if (!empty($filter['include_current'])) {
        $query .= " AND (status = ? OR id_barang = ?)";
        $params[] = 1; // Status tersedia
        $params[] = $filter['include_current'];
        $types .= "ii";
    }
    
    $stmt = $this->conn->prepare($query);
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

    public function create($id_pemilik, $nama_barang, $gambar, $deskripsi, $harga_sewa) {
        $stmt = $this->conn->prepare("INSERT INTO barang (id_pemilik, nama_barang, gambar, deskripsi, harga_sewa, status) VALUES (?, ?, ?, ?, ?, 1)");
        $stmt->bind_param("issss", $id_pemilik, $nama_barang, $gambar, $deskripsi, $harga_sewa);
        
        if ($stmt->execute()) {
            return $this->conn->insert_id;
        } else {
            throw new Exception("Gagal menambahkan barang: " . $stmt->error);
        }
    }

    // Update barang
    public function update($id_barang, $nama_barang, $gambar, $deskripsi, $harga_sewa) {
        if ($gambar) {
            $stmt = $this->conn->prepare("UPDATE barang SET nama_barang = ?, gambar = ?, deskripsi = ?, harga_sewa = ? WHERE id_barang = ?");
            $stmt->bind_param("ssssi", $nama_barang, $gambar, $deskripsi, $harga_sewa, $id_barang);
        } else {
            $stmt = $this->conn->prepare("UPDATE barang SET nama_barang = ?, deskripsi = ?, harga_sewa = ? WHERE id_barang = ?");
            $stmt->bind_param("sssi", $nama_barang, $deskripsi, $harga_sewa, $id_barang);
        }
        
        if (!$stmt->execute()) {
            throw new Exception("Gagal memperbarui barang: " . $stmt->error);
        }
        return true;
    }

    // Delete barang
    public function delete($id_barang) {
        $stmt = $this->conn->prepare("DELETE FROM barang WHERE id_barang = ?");
        $stmt->bind_param("i", $id_barang);
        
        if (!$stmt->execute()) {
            throw new Exception("Gagal menghapus barang: " . $stmt->error);
        }
        return true;
    }

    // Get barang by ID
    public function getById($id_barang) {
        $stmt = $this->conn->prepare("SELECT * FROM barang WHERE id_barang = ?");
        $stmt->bind_param("i", $id_barang);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    // Get all barang by pemilik
    public function getAllByPemilik($id_pemilik) {
        $stmt = $this->conn->prepare("SELECT * FROM barang WHERE id_pemilik = ?");
        $stmt->bind_param("i", $id_pemilik);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // Get all available barang
    public function getAllAvailable() {
        $query = "SELECT b.*, pb.nama as nama_pemilik 
                  FROM barang b
                  JOIN pemilik_barang pb ON b.id_pemilik = pb.id_pemilik
                  WHERE b.status = 1";
        $result = $this->conn->query($query);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // Update barang status
    public function updateStatus($id_barang, $status) {
    $stmt = $this->conn->prepare("UPDATE barang SET status = ? WHERE id_barang = ?");
    $stmt->bind_param("ii", $status, $id_barang);
    if (!$stmt->execute()) {
        throw new Exception("Gagal mengupdate status barang: " . $stmt->error);
    }
    return true;
}
}