<?php
class Barang {
    private $conn;

    // Constructor
    public function __construct($db) {
        $this->conn = $db;
    }

    // Get all barang
    public function getAll() {
        $sql = "SELECT * FROM barang";
        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get barang by ID
    public function getById($id) {
        $sql = "SELECT * FROM barang WHERE id_barang = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Create new barang
    public function create($id_pemilik, $nama_barang, $deskripsi, $harga_sewa, $status) {
        $sql = "INSERT INTO barang (id_pemilik, nama_barang, deskripsi, harga_sewa, status) 
                VALUES (:id_pemilik, :nama_barang, :deskripsi, :harga_sewa, :status)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id_pemilik', $id_pemilik);
        $stmt->bindParam(':nama_barang', $nama_barang);
        $stmt->bindParam(':deskripsi', $deskripsi);
        $stmt->bindParam(':harga_sewa', $harga_sewa);
        $stmt->bindParam(':status', $status);
        return $stmt->execute();
    }

    // Update barang
    public function update($id, $nama_barang, $deskripsi, $harga_sewa, $status) {
        $sql = "UPDATE barang SET nama_barang = :nama_barang, deskripsi = :deskripsi, harga_sewa = :harga_sewa, 
                status = :status WHERE id_barang = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':nama_barang', $nama_barang);
        $stmt->bindParam(':deskripsi', $deskripsi);
        $stmt->bindParam(':harga_sewa', $harga_sewa);
        $stmt->bindParam(':status', $status);
        return $stmt->execute();
    }

    // Delete barang
    public function delete($id) {
        $sql = "DELETE FROM barang WHERE id_barang = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
}
?>