<?php
    define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
    define('DB_USER', getenv('DB_USER') ?: 'root'); 
    define('DB_PASS', getenv('DB_PASS') ?: '');
    define('DB_NAME', getenv('DB_NAME') ?: 'onlyrent');

    function getDBConnection() {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        if ($conn->connect_error) {
            // Log the error instead of halting execution
            error_log("Connection failed: " . $conn->connect_error);
            throw new Exception("Database connection failed.");
        }
        return $conn;
    }
?>