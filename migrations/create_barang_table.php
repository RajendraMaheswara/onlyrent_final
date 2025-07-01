<?php
    include_once '../config/connect_db.php';

    $conn = getDBConnection();

    $sql = "CREATE TABLE IF NOT EXISTS barang (
        id_barang INT(11) AUTO_INCREMENT PRIMARY KEY,
        id_pemilik INT(11) NOT NULL,
        nama_barang VARCHAR(100) NOT NULL,
        deskripsi TEXT NOT NULL,
        harga_sewa VARCHAR(10) NOT NULL,
        status INT(1) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (id_pemilik) REFERENCES pemilik_barang(id_pemilik) ON DELETE CASCADE
    )";

    if ($conn->query($sql) === TRUE) {
        echo "Table 'barang' created successfully.\n";
    } else {
        echo "Error creating table: " . $conn->error;
    }

    $conn->close();
?>