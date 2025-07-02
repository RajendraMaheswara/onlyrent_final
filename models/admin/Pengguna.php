<?php
class Pengguna {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Get pengguna by ID
    public function getById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM pengguna WHERE id_pengguna = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public function getAll() {
        $result = $this->conn->query("SELECT * FROM pengguna");
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // Create pengguna
    public function create($username, $email, $password, $role) {
        try {
            // Check if username or email already exists
            $check_stmt = $this->conn->prepare("SELECT COUNT(*) as count FROM pengguna WHERE username = ? OR email = ?");
            $check_stmt->bind_param("ss", $username, $email);
            $check_stmt->execute();
            
            // Get result
            $result = $check_stmt->get_result();
            $row = $result->fetch_assoc();
            $check_stmt->close();
            
            if ($row['count'] > 0) {
                throw new Exception("Username atau email sudah digunakan");
            }
            
            // Insert new user
            $stmt = $this->conn->prepare("INSERT INTO pengguna (username, email, password, role) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $username, $email, $password, $role);
            
            return $stmt->execute();
        } catch (Exception $e) {
            throw new Exception("Database error: " . $e->getMessage());
        }
    }

    // Update pengguna
    public function update($id, $username, $email, $password = null, $role) {
        try {
            // Check if username or email already exists (excluding current user)
            $check_stmt = $this->conn->prepare("SELECT COUNT(*) as count FROM pengguna WHERE (username = ? OR email = ?) AND id_pengguna != ?");
            $check_stmt->bind_param("ssi", $username, $email, $id);
            $check_stmt->execute();
            
            $result = $check_stmt->get_result();
            $row = $result->fetch_assoc();
            $check_stmt->close();
            
            if ($row['count'] > 0) {
                throw new Exception("Username atau email sudah digunakan");
            }
            
            // Update with or without password
            if ($password) {
                $stmt = $this->conn->prepare("UPDATE pengguna SET username = ?, email = ?, password = ?, role = ? WHERE id_pengguna = ?");
                $stmt->bind_param("ssssi", $username, $email, $password, $role, $id);
            } else {
                $stmt = $this->conn->prepare("UPDATE pengguna SET username = ?, email = ?, role = ? WHERE id_pengguna = ?");
                $stmt->bind_param("sssi", $username, $email, $role, $id);
            }
            
            return $stmt->execute();
        } catch (Exception $e) {
            throw new Exception("Database error: " . $e->getMessage());
        }
    }

    // Delete pengguna
    public function delete($id) {
        try {
            // Cek apakah user ada
            $check_stmt = $this->conn->prepare("SELECT COUNT(*) as count FROM pengguna WHERE id_pengguna = ?");
            $check_stmt->bind_param("i", $id);
            $check_stmt->execute();
            
            $result = $check_stmt->get_result();
            $row = $result->fetch_assoc();
            $check_stmt->close();
            
            if ($row['count'] == 0) {
                throw new Exception("User tidak ditemukan");
            }
            
            // Hapus user
            $stmt = $this->conn->prepare("DELETE FROM pengguna WHERE id_pengguna = ?");
            $stmt->bind_param("i", $id);
            
            return $stmt->execute();
        } catch (Exception $e) {
            throw new Exception("Database error: " . $e->getMessage());
        }
    }

}