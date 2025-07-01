<?php
class Sewa {
    private $conn;

    // Constructor
    public function __construct($db) {
        $this->conn = $db;
    }

    // Get all sewa
    public function getAll() {
        $sql = "SELECT * FROM sewa";
        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get sewa by ID
    public function getById($id) {
        $sql = "SELECT * FROM sewa WHERE id_sewa = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Create new sewa
    public function create($id_barang, $id_penyewa, $tanggalSewa, $tanggalKembali, $totalBayar, $status) {
        $sql = "INSERT INTO sewa (id_barang, id_penyewa, tanggalSewa, tanggalKembali, totalBayar, status) 
                VALUES (:id_barang, :id_penyewa, :tanggalSewa, :tanggalKembali, :totalBayar, :status)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id_barang', $id_barang);
        $stmt->bindParam(':id_penyewa', $id_penyewa);
        $stmt->bindParam(':tanggalSewa', $tanggalSewa);
        $stmt->bindParam(':tanggalKembali', $tanggalKembali);
        $stmt->bindParam(':totalBayar', $totalBayar);
        $stmt->bindParam(':status', $status);
        return $stmt->execute();
    }

    // Update sewa
    public function update($id, $id_barang, $id_penyewa, $tanggalSewa, $tanggalKembali, $totalBayar, $status) {
        $sql = "UPDATE sewa SET id_barang = :id_barang, id_penyewa = :id_penyewa, tanggalSewa = :tanggalSewa, 
                tanggalKembali = :tanggalKembali, totalBayar = :totalBayar, status = :status WHERE id_sewa = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':id_barang', $id_barang);
        $stmt->bindParam(':id_penyewa', $id_penyewa);
        $stmt->bindParam(':tanggalSewa', $tanggalSewa);
        $stmt->bindParam(':tanggalKembali', $tanggalKembali);
        $stmt->bindParam(':totalBayar', $totalBayar);
        $stmt->bindParam(':status', $status);
        return $stmt->execute();
    }

    // Delete sewa
    public function delete($id) {
        $sql = "DELETE FROM sewa WHERE id_sewa = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
}
?>