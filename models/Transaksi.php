<?php
class Transaksi {
    private $conn;

    // Constructor
    public function __construct($db) {
        $this->conn = $db;
    }

    // Get all transaksi
    public function getAll() {
        $sql = "SELECT * FROM transaksi";
        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get transaksi by ID
    public function getById($id) {
        $sql = "SELECT * FROM transaksi WHERE id_transaksi = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Create new transaksi
    public function create($id_sewa, $jumlah, $tanggal, $totalBayar, $status) {
        $sql = "INSERT INTO transaksi (id_sewa, jumlah, tanggal, totalBayar, status) 
                VALUES (:id_sewa, :jumlah, :tanggal, :totalBayar, :status)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id_sewa', $id_sewa);
        $stmt->bindParam(':jumlah', $jumlah);
        $stmt->bindParam(':tanggal', $tanggal);
        $stmt->bindParam(':totalBayar', $totalBayar);
        $stmt->bindParam(':status', $status);
        return $stmt->execute();
    }

    // Update transaksi
    public function update($id, $id_sewa, $jumlah, $tanggal, $totalBayar, $status) {
        $sql = "UPDATE transaksi SET id_sewa = :id_sewa, jumlah = :jumlah, tanggal = :tanggal, totalBayar = :totalBayar, 
                status = :status WHERE id_transaksi = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':id_sewa', $id_sewa);
        $stmt->bindParam(':jumlah', $jumlah);
        $stmt->bindParam(':tanggal', $tanggal);
        $stmt->bindParam(':totalBayar', $totalBayar);
        $stmt->bindParam(':status', $status);
        return $stmt->execute();
    }

    // Delete transaksi
    public function delete($id) {
        $sql = "DELETE FROM transaksi WHERE id_transaksi = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
}
?>
