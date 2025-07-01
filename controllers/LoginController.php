<?php
include_once 'config/connect_db.php';
include_once 'models/Login.php';

class LoginController {
    private $model;

    public function __construct($db) {
        $this->model = new Login($db);
    }

    // Process login
    public function login() {
        $error = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Get form data
            $username = trim($_POST['username']);
            $password = $_POST['password'];
            
            // Validate input
            if (empty($username) || empty($password)) {
                $error = "Username and password are required.";
            } else {
                // Authenticate user
                $user = $this->model->authenticate($username, $password);
                
                if ($user) {
                    // Set session data
                    $_SESSION['user'] = [
                        'id' => $user['id_pengguna'],
                        'username' => $user['username'],
                        'email' => $user['email'],
                        'role' => $user['role']
                    ];

                    // Redirect based on role
                    if ($user['role'] === 1) {
                        header("Location: views/admin/index.php");
                    } elseif ($user['role'] === 2) {
                        header("Location: views/penyewa/index.php");
                    } elseif ($user['role'] === 3) {
                        header("Location: views/pemilik/index.php");
                    } else {
                        header("Location: login.php");
                    }
                    exit();
                } else {
                    $error = "Invalid username or password.";
                }
            }
        }

        return $error;
    }
}
?>
