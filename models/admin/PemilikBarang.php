<?php
class PemilikBarang {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Get pemilik by ID
    public function getById($id_pemilik) {
        $stmt = $this->conn->prepare("SELECT pb.*, pg.username, pg.email 
                                    FROM pemilik_barang pb
                                    JOIN pengguna pg ON pb.id_pengguna = pg.id_pengguna
                                    WHERE pb.id_pemilik = ?");
        $stmt->bind_param("i", $id_pemilik);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    // Get pemilik by ID pengguna
    public function getByIdPengguna($id_pengguna) {
        $stmt = $this->conn->prepare("SELECT * FROM pemilik_barang WHERE id_pengguna = ?");
        $stmt->bind_param("i", $id_pengguna);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    // Create pemilik with user account (for admin)
    public function createWithUser($username, $email, $password, $nama, $no_telp) {
        try {
            $this->conn->begin_transaction();
            
            // 1. First create the user account (role 3 for pemilik)
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
            
            $user_stmt = $this->conn->prepare("INSERT INTO pengguna (username, email, password, role) VALUES (?, ?, ?, 3)");
            $user_stmt->bind_param("sss", $username, $email, $hashed_password);
            
            if (!$user_stmt->execute()) {
                throw new Exception("Gagal membuat akun pengguna");
            }
            
            $id_pengguna = $this->conn->insert_id;
            
            // 2. Then create pemilik profile
            $pemilik_stmt = $this->conn->prepare("INSERT INTO pemilik_barang (nama, no_telp, id_pengguna) VALUES (?, ?, ?)");
            $pemilik_stmt->bind_param("ssi", $nama, $no_telp, $id_pengguna);
            
            if (!$pemilik_stmt->execute()) {
                throw new Exception("Gagal membuat profil pemilik");
            }
            
            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollback();
            throw new Exception("Database error: " . $e->getMessage());
        }
    }

    // Update pemilik and user data
    public function updatePemilikWithUser($id_pemilik, $username, $email, $password = null, $nama, $no_telp) {
        try {
            $this->conn->begin_transaction();
            
            // 1. First update user account
            $user_stmt = null;
            if ($password) {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $user_stmt = $this->conn->prepare("UPDATE pengguna SET username = ?, email = ?, password = ? WHERE id_pengguna = (SELECT id_pengguna FROM pemilik_barang WHERE id_pemilik = ?)");
                $user_stmt->bind_param("sssi", $username, $email, $hashed_password, $id_pemilik);
            } else {
                $user_stmt = $this->conn->prepare("UPDATE pengguna SET username = ?, email = ? WHERE id_pengguna = (SELECT id_pengguna FROM pemilik_barang WHERE id_pemilik = ?)");
                $user_stmt->bind_param("ssi", $username, $email, $id_pemilik);
            }
            
            if (!$user_stmt->execute()) {
                throw new Exception("Gagal memperbarui akun pengguna");
            }
            
            // 2. Then update pemilik profile
            $pemilik_stmt = $this->conn->prepare("UPDATE pemilik_barang SET nama = ?, no_telp = ? WHERE id_pemilik = ?");
            $pemilik_stmt->bind_param("ssi", $nama, $no_telp, $id_pemilik);
            
            if (!$pemilik_stmt->execute()) {
                throw new Exception("Gagal memperbarui profil pemilik");
            }
            
            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollback();
            throw new Exception("Database error: " . $e->getMessage());
        }
    }

    // Get all pemilik with user info
    public function getAllWithUserInfo() {
        $query = "SELECT pb.id_pemilik, pb.nama, pb.no_telp, 
                         pg.id_pengguna, pg.username, pg.email, pg.status
                  FROM pemilik_barang pb
                  JOIN pengguna pg ON pb.id_pengguna = pg.id_pengguna";
        $result = $this->conn->query($query);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // Delete pemilik and associated user
    public function delete($id_pemilik) {
        try {
            $this->conn->begin_transaction();
            
            // First get the user ID
            $stmt = $this->conn->prepare("SELECT id_pengguna FROM pemilik_barang WHERE id_pemilik = ?");
            $stmt->bind_param("i", $id_pemilik);
            $stmt->execute();
            $result = $stmt->get_result();
            $pemilik = $result->fetch_assoc();
            
            if (!$pemilik) {
                throw new Exception("Pemilik tidak ditemukan");
            }
            
            // Delete pemilik
            $delete_pemilik = $this->conn->prepare("DELETE FROM pemilik_barang WHERE id_pemilik = ?");
            $delete_pemilik->bind_param("i", $id_pemilik);
            
            if (!$delete_pemilik->execute()) {
                throw new Exception("Gagal menghapus data pemilik");
            }
            
            // Delete user account
            $delete_user = $this->conn->prepare("DELETE FROM pengguna WHERE id_pengguna = ?");
            $delete_user->bind_param("i", $pemilik['id_pengguna']);
            
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