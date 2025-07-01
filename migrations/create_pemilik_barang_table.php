<?php
    include_once '../config/connect_db.php';

    $conn = getDBConnection();

    $sql = "CREATE TABLE IF NOT EXISTS pemilik_barang (
        id_pemilik INT(11) AUTO_INCREMENT PRIMARY KEY,
        nama VARCHAR(100) NOT NULL,
        no_telp VARCHAR(25) NOT NULL,
        id_pengguna INT(1) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (id_pengguna) REFERENCES pengguna(id_pengguna) ON DELETE CASCADE
    )";

    if ($conn->query($sql) === TRUE) {
        echo "Table 'pemilik_barang' created successfully.\n";
    } else {
        echo "Error creating table: " . $conn->error;
    }

    $conn->close();
?>