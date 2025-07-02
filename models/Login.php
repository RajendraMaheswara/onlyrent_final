<?php
class Login {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function authenticate($username, $password) {
        // Get user data including role
        $sql = "SELECT id_pengguna, username, email, password, role FROM pengguna WHERE username = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            if (password_verify($password, $user['password'])) {
                // Get additional data based on role
                switch ($user['role']) {
                    case 1: // Admin
                        // No additional data needed for admin
                        break;
                        
                    case 2: // Penyewa
                        $penyewaData = $this->getPenyewaData($user['id_pengguna']);
                        if ($penyewaData) {
                            $user['id_penyewa'] = $penyewaData['id_penyewa'];
                        } else {
                            return false;
                        }
                        break;
                        
                    case 3: // Pemilik
                        $pemilikData = $this->getPemilikData($user['id_pengguna']);
                        if ($pemilikData) {
                            $user['id_pemilik'] = $pemilikData['id_pemilik'];
                        } else {
                            return false;
                        }
                        break;
                }
                return $user;
            }
        }
        return false;
    }

    private function getPenyewaData($id_pengguna) {
        $sql = "SELECT id_penyewa FROM penyewa WHERE id_pengguna = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id_pengguna);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    private function getPemilikData($id_pengguna) {
        $sql = "SELECT id_pemilik FROM pemilik_barang WHERE id_pengguna = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id_pengguna);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
}