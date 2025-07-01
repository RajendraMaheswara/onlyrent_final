<?php
    include_once '../config/connect_db.php';

    $conn = getDBConnection();

    $sql = "CREATE TABLE IF NOT EXISTS transaksi (
        id_transaksi INT(11) AUTO_INCREMENT PRIMARY KEY,
        id_sewa INT(11) NOT NULL,
        jumlah INT(11) NOT NULL,
        tanggal DATETIME NOT NULL,
        totalBayar INT(12) NOT NULL,
        status INT(1) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (id_sewa) REFERENCES sewa(id_sewa) ON DELETE CASCADE
    )";

    if ($conn->query($sql) === TRUE) {
        echo "Table 'transaksi' created successfully.\n";
    } else {
        echo "Error creating table: " . $conn->error;
    }

    $conn->close();
?>