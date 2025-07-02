<?php
class Chat {
    private $conn;

    // Constructor
    public function __construct($db) {
        $this->conn = $db;
    }

    // Get all chat messages
    public function getAll() {
        $sql = "SELECT * FROM chat";
        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get chat by ID
    public function getById($id) {
        $sql = "SELECT * FROM chat WHERE id_chat = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Send a message
    public function sendMessage($id_pengirim, $id_penerima, $pesan, $tanggal) {
        $sql = "INSERT INTO chat (id_pengirim, id_penerima, pesan, tanggal) VALUES (:id_pengirim, :id_penerima, :pesan, :tanggal)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id_pengirim', $id_pengirim);
        $stmt->bindParam(':id_penerima', $id_penerima);
        $stmt->bindParam(':pesan', $pesan);
        $stmt->bindParam(':tanggal', $tanggal);
        return $stmt->execute();
    }

    // Delete message
    public function delete($id) {
        $sql = "DELETE FROM chat WHERE id_chat = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
}
?>
