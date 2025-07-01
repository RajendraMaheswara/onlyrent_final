<?php
class Register {
    private $conn;

    // Constructor
    public function __construct($db) {
        $this->conn = $db;
    }

    // Check if username or email already exists
    public function checkExistingUser($username, $email) {
        $sql = "SELECT id_pengguna FROM pengguna WHERE username = ? OR email = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $stmt->store_result();
        return $stmt->num_rows > 0; // Return true if user exists
    }

    // Register new user
    public function register($username, $email, $password) {
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert new user into the database
        $sql = "INSERT INTO pengguna (username, email, password, role) VALUES (?, ?, ?, 2)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sss", $username, $email, $hashed_password);
        return $stmt->execute(); // Return true if registration is successful
    }
}
?>