<?php
    include_once '../config/connect_db.php';

    $conn = getDBConnection();

    $sql = "CREATE TABLE IF NOT EXISTS pengguna (
    id_pengguna INT(11) AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    email VARCHAR(50) NOT NULL,
    password VARCHAR(255) NOT NULL,
    role INT(1) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";

    if ($conn->query($sql) === TRUE) {
    echo "Table 'pengguna' created successfully.\n";
    } else {
    echo "Error creating table: " . $conn->error;
    }

    $conn->close();
?>