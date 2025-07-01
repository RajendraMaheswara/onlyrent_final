<?php
class Penyewa {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Get penyewa by ID
    public function getById($id_penyewa) {
        $stmt = $this->conn->prepare("SELECT p.*, pg.username, pg.email 
                                     FROM penyewa p
                                     JOIN pengguna pg ON p.id_pengguna = pg.id_pengguna
                                     WHERE p.id_penyewa = ?");
        $stmt->bind_param("i", $id_penyewa);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    // Get penyewa by ID pengguna
    public function getByIdPengguna($id_pengguna) {
        $stmt = $this->conn->prepare("SELECT * FROM penyewa WHERE id_pengguna = ?");
        $stmt->bind_param("i", $id_pengguna);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    // Create penyewa (new version for admin)
    public function createWithUser($username, $email, $password, $nama, $alamat, $no_telp) {
        try {
            $this->conn->begin_transaction();
            
            // 1. First create the user account
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Check if username or email already exists
            $check_stmt = $this->conn->prepare("SELECT COUNT(*) as count FROM pengguna WHERE username = ? OR email = ?");
            $check_stmt->bind_param("ss", $username, $email);
            $check_stmt->execute();
            $result = $check_stmt->get_result();
            $row = $result->fetch_assoc();
            
            if ($row['count'] > 0) {
                throw new Exception("Username atau email sudah digunakan");
            }
            
            // Insert new user (role 2 for penyewa)
            $user_stmt = $this->conn->prepare("INSERT INTO pengguna (username, email, password, role) VALUES (?, ?, ?, 2)");
            $user_stmt->bind_param("sss", $username, $email, $hashed_password);
            
            if (!$user_stmt->execute()) {
                throw new Exception("Gagal membuat akun pengguna");
            }
            
            $id_pengguna = $this->conn->insert_id;
            
            // 2. Then create penyewa profile
            $penyewa_stmt = $this->conn->prepare("INSERT INTO penyewa (nama, alamat, no_telp, id_pengguna) VALUES (?, ?, ?, ?)");
            $penyewa_stmt->bind_param("sssi", $nama, $alamat, $no_telp, $id_pengguna);
            
            if (!$penyewa_stmt->execute()) {
                throw new Exception("Gagal membuat profil penyewa");
            }
            
            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollback();
            throw new Exception("Database error: " . $e->getMessage());
        }
    }

    // Update penyewa
    public function updatePenyewaWithUser($id_penyewa, $username, $email, $password = null, $nama, $alamat, $no_telp) {
        try {
            $this->conn->begin_transaction();
            
            // 1. First update user account
            $user_stmt = null;
            if ($password) {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $user_stmt = $this->conn->prepare("UPDATE pengguna SET username = ?, email = ?, password = ? WHERE id_pengguna = (SELECT id_pengguna FROM penyewa WHERE id_penyewa = ?)");
                $user_stmt->bind_param("sssi", $username, $email, $hashed_password, $id_penyewa);
            } else {
                $user_stmt = $this->conn->prepare("UPDATE pengguna SET username = ?, email = ? WHERE id_pengguna = (SELECT id_pengguna FROM penyewa WHERE id_penyewa = ?)");
                $user_stmt->bind_param("ssi", $username, $email, $id_penyewa);
            }
            
            if (!$user_stmt->execute()) {
                throw new Exception("Gagal memperbarui akun pengguna");
            }
            
            // 2. Then update penyewa profile
            $penyewa_stmt = $this->conn->prepare("UPDATE penyewa SET nama = ?, alamat = ?, no_telp = ? WHERE id_penyewa = ?");
            $penyewa_stmt->bind_param("sssi", $nama, $alamat, $no_telp, $id_penyewa);
            
            if (!$penyewa_stmt->execute()) {
                throw new Exception("Gagal memperbarui profil penyewa");
            }
            
            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollback();
            throw new Exception("Database error: " . $e->getMessage());
        }
    }

    // Get all penyewa with user info
    public function getAllWithUserInfo() {
        $query = "SELECT p.id_penyewa, p.nama, p.alamat, p.no_telp, 
                         pg.id_pengguna, pg.username, pg.email
                  FROM penyewa p
                  JOIN pengguna pg ON p.id_pengguna = pg.id_pengguna";
        $result = $this->conn->query($query);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // Delete penyewa and associated user
    public function delete($id_penyewa) {
        try {
            $this->conn->begin_transaction();
            
            // First get the user ID
            $stmt = $this->conn->prepare("SELECT id_pengguna FROM penyewa WHERE id_penyewa = ?");
            $stmt->bind_param("i", $id_penyewa);
            $stmt->execute();
            $result = $stmt->get_result();
            $penyewa = $result->fetch_assoc();
            
            if (!$penyewa) {
                throw new Exception("Penyewa tidak ditemukan");
            }
            
            // Delete penyewa
            $delete_penyewa = $this->conn->prepare("DELETE FROM penyewa WHERE id_penyewa = ?");
            $delete_penyewa->bind_param("i", $id_penyewa);
            
            if (!$delete_penyewa->execute()) {
                throw new Exception("Gagal menghapus data penyewa");
            }
            
            // Delete user account
            $delete_user = $this->conn->prepare("DELETE FROM pengguna WHERE id_pengguna = ?");
            $delete_user->bind_param("i", $penyewa['id_pengguna']);
            
            if (!$delete_user->execute()) {
                throw new Exception("Gagal menghapus akun pengguna");
            }
            
            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollback();
            throw new Exception("Database error: " . $e->getMessage());
        }
    }
}