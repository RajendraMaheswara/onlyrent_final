<?php
session_start();
include_once 'config/connect_db.php';
include_once 'models/Register.php';

class RegisterController {
    private $model;

    public function __construct($db) {
        $this->model = new Register($db);
    }

    // Handle registration logic
    public function register() {
        $error = '';
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Get form data
            $username = trim($_POST['username']);
            $email = trim($_POST['email']);
            $password = $_POST['password'];
            $confirm_password = $_POST['confirm_password'];
            
            // Validate input
            if (empty($username) || empty($email) || empty($password)) {
                $error = "All fields are required.";
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = "Invalid email format.";
            } elseif ($password !== $confirm_password) {
                $error = "Passwords do not match.";
            } elseif (strlen($password) < 8) {
                $error = "Password must be at least 8 characters.";
            } else {
                // Check if username or email already exists
                if ($this->model->checkExistingUser($username, $email)) {
                    $error = "Username or email already exists.";
                } else {
                    // Register new user
                    if ($this->model->register($username, $email, $password)) {
                        // Redirect to login page after successful registration
                        header("Location: login.php");
                        exit();
                    } else {
                        $error = "Registration failed. Please try again.";
                    }
                }
            }
        }

        return $error;
    }
}
?>
