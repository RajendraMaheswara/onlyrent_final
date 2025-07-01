<?php
class Login {
    private $conn;

    // Constructor
    public function __construct($db) {
        $this->conn = $db;
    }

    // Check user credentials
    public function authenticate($username, $password) {
        $sql = "SELECT id_pengguna, username, email, password, role FROM pengguna WHERE username = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // Verify password
            if (password_verify($password, $user['password'])) {
                return $user; // Return user data on successful login
            }
        }
        return false; // Return false if authentication fails
    }
}
?>