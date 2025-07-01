<?php
    include_once '../config/connect_db.php';

    $conn = getDBConnection();

    $sql = "CREATE TABLE IF NOT EXISTS chat (
        id_chat INT(11) AUTO_INCREMENT PRIMARY KEY,
        id_pengirim INT(11) NOT NULL,
        id_penerima INT(11) NOT NULL,
        pesan TEXT NOT NULL,
        tanggal DATETIME NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (id_pengirim) REFERENCES pengguna(id_pengguna) ON DELETE CASCADE,
        FOREIGN KEY (id_penerima) REFERENCES pengguna(id_pengguna) ON DELETE CASCADE
    )";

    if ($conn->query($sql) === TRUE) {
        echo "Table 'chat' created successfully.\n";
    } else {
        echo "Error creating table: " . $conn->error;
    }

    $conn->close();
?>