<?php
session_start();
include_once 'config/connect_db.php';
include_once 'models/Login.php';

class LoginController {
    private $model;

    public function __construct($db) {
        $this->model = new Login($db);
    }

    public function login() {
        $error = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim($_POST['username']);
            $password = $_POST['password'];
            
            if (empty($username) || empty($password)) {
                $error = "Username dan password harus diisi";
            } else {
                $user = $this->model->authenticate($username, $password);
                
                if ($user) {
                    // Set session data
                    $_SESSION['user'] = [
                        'id_pengguna' => $user['id_pengguna'],
                        'username' => $user['username'],
                        'email' => $user['email'],
                        'role' => $user['role']
                    ];

                    // Add role-specific data to session
                    switch ($user['role']) {
                        case 1: // Admin
                            $_SESSION['user']['is_admin'] = true;
                            $redirect = 'views/admin/index.php';
                            break;
                            
                        case 2: // Penyewa
                            $_SESSION['user']['id_penyewa'] = $user['id_penyewa'];
                            $redirect = 'views/penyewa/index.php';
                            break;
                            
                        case 3: // Pemilik
                            $_SESSION['user']['id_pemilik'] = $user['id_pemilik'];
                            $redirect = 'views/pemilik/index.php';
                            break;
                            
                        default:
                            $error = "Role tidak valid";
                            break;
                    }

                    if (empty($error)) {
                        header("Location: ../$redirect");
                        exit();
                    }
                } else {
                    $error = "Username atau password salah";
                }
            }
        }

        return $error;
    }
}

// Handle login request
$db = getDBConnection();
$controller = new LoginController($db);

if (isset($_POST['login'])) {
    $error = $controller->login();
    if ($error) {
        $_SESSION['error'] = $error;
        header("Location: ../login.php");
        exit();
    }
}