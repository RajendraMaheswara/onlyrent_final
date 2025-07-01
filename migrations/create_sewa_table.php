<?php
    include_once '../config/connect_db.php';

    $conn = getDBConnection();

    $sql = "CREATE TABLE IF NOT EXISTS sewa (
        id_sewa INT(11) AUTO_INCREMENT PRIMARY KEY,
        id_barang INT(11) NOT NULL,
        id_penyewa INT(11) NOT NULL,
        tanggalSewa DATETIME NOT NULL,
        tanggalKembali DATETIME NOT NULL,
        totalBayar INT(12) NOT NULL,
        status INT(1) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (id_barang) REFERENCES barang(id_barang) ON DELETE CASCADE,
        FOREIGN KEY (id_penyewa) REFERENCES penyewa(id_penyewa) ON DELETE CASCADE
    )";

    if ($conn->query($sql) === TRUE) {
        echo "Table 'sewa' created successfully.\n";
    } else {
        echo "Error creating table: " . $conn->error;
    }

    $conn->close();
?>