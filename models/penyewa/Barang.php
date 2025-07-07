<?php
class Barang {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Mendapatkan semua barang yang tersedia untuk disewa
     * dengan filter dan sorting
     */
    public function getAvailableProducts($search = '', $category = '', $sort = 'nama_barang') {
        $query = "SELECT b.id_barang, b.id_pemilik, b.nama_barang, b.gambar, 
                         b.deskripsi, b.harga_sewa, b.status,
                         pb.nama as nama_pemilik
                  FROM barang b
                  JOIN pemilik_barang pb ON b.id_pemilik = pb.id_pemilik
                  WHERE b.status = 1"; // 1 = tersedia

        $params = [];
        $types = "";

        // Filter pencarian
        if (!empty($search)) {
            $query .= " AND (b.nama_barang LIKE ? OR b.deskripsi LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
            $types .= "ss";
        }

        // Filter kategori
        if (!empty($category)) {
            $query .= $this->getCategoryFilterQuery();
            
            // Tambahkan parameter kategori 7 kali (sesuai kondisi dalam query)
            for($i = 0; $i < 7; $i++) {
                $params[] = $category;
                $types .= "s";
            }
        }

        // Sorting
        switch($sort) {
            case 'price_low':
                $query .= " ORDER BY b.harga_sewa ASC";
                break;
            case 'price_high':
                $query .= " ORDER BY b.harga_sewa DESC";
                break;
            case 'rating':
                $query .= " ORDER BY (SELECT AVG(rating) FROM ulasan WHERE id_barang = b.id_barang) DESC";
                break;
            default:
                $query .= " ORDER BY b.nama_barang ASC";
        }

        $stmt = $this->conn->prepare($query);
        
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        $raw_products = $result->fetch_all(MYSQLI_ASSOC);

        // Transform data untuk tampilan
        $products = [];
        foreach($raw_products as $raw_product) {
            $product = $this->transformProductData($raw_product);
            $products[] = $product;
        }

        return $products;
    }

    /**
     * Mendapatkan query filter kategori
     */
    private function getCategoryFilterQuery() {
        return " AND (
            (? = 'Kamera' AND (LOWER(nama_barang) LIKE '%kamera%' OR LOWER(nama_barang) LIKE '%camera%')) OR
            (? = 'Lensa' AND (LOWER(nama_barang) LIKE '%lensa%' OR LOWER(nama_barang) LIKE '%lens%')) OR
            (? = 'Tripod' AND LOWER(nama_barang) LIKE '%tripod%') OR
            (? = 'Lighting' AND (LOWER(nama_barang) LIKE '%flash%' OR LOWER(nama_barang) LIKE '%lighting%' OR LOWER(nama_barang) LIKE '%godox%')) OR
            (? = 'Stabilizer' AND (LOWER(nama_barang) LIKE '%gimbal%' OR LOWER(nama_barang) LIKE '%stabilizer%' OR LOWER(nama_barang) LIKE '%ronin%')) OR
            (? = 'Audio' AND (LOWER(nama_barang) LIKE '%mic%' OR LOWER(nama_barang) LIKE '%audio%' OR LOWER(nama_barang) LIKE '%rode%')) OR
            (? = 'Aksesoris' AND NOT (
                LOWER(nama_barang) LIKE '%kamera%' OR LOWER(nama_barang) LIKE '%camera%' OR
                LOWER(nama_barang) LIKE '%lensa%' OR LOWER(nama_barang) LIKE '%lens%' OR
                LOWER(nama_barang) LIKE '%tripod%' OR LOWER(nama_barang) LIKE '%flash%' OR 
                LOWER(nama_barang) LIKE '%lighting%' OR LOWER(nama_barang) LIKE '%godox%' OR
                LOWER(nama_barang) LIKE '%gimbal%' OR LOWER(nama_barang) LIKE '%stabilizer%' OR 
                LOWER(nama_barang) LIKE '%ronin%' OR LOWER(nama_barang) LIKE '%mic%' OR 
                LOWER(nama_barang) LIKE '%audio%' OR LOWER(nama_barang) LIKE '%rode%'
            ))
        )";
    }

    /**
     * Transform data produk untuk tampilan
     */
    private function transformProductData($raw_product) {
    $product = [];
    $product['id'] = $raw_product['id_barang'];
    $product['name'] = $raw_product['nama_barang'];
    $product['description'] = $raw_product['deskripsi'];
    $product['price'] = $raw_product['harga_sewa'];
    $product['id_pemilik'] = $raw_product['id_pemilik'];
    $product['nama_pemilik'] = $raw_product['nama_pemilik'];
    $product['status'] = $raw_product['status'] == 1 ? 'tersedia' : 'tidak tersedia';
    $product['rating'] = $this->getProductRating($raw_product['id_barang']);
    
    // Handle image path properly
    $gambar = json_decode($raw_product['gambar'], true) ?? [];
    
    if (!empty($gambar)) {
        // Get the first image and ensure it's a proper path
        $firstImage = is_array($gambar) ? reset($gambar) : $gambar;
        
        // If it's already a full URL, use it as is
        if (filter_var($firstImage, FILTER_VALIDATE_URL)) {
            $product['image'] = $firstImage;
        } else {
            // Otherwise, prepend the correct base path
            $product['image'] = '/assets/images/barang/' . ltrim($firstImage, '/');
        }
    } else {
        $product['image'] = $this->getDefaultImage($raw_product['nama_barang']);
    }
    
    return $product;
}

    /**
     * Mendapatkan rating produk
     */
    private function getProductRating($id_barang) {
        // Pertama, cek apakah tabel ulasan ada
        $checkTable = $this->conn->query("SHOW TABLES LIKE 'ulasan'");
        if ($checkTable->num_rows == 0) {
            return 4.5; // Return default rating jika tabel tidak ada
        }

        // Query yang lebih aman dengan error handling
        $query = "SELECT AVG(rating) as avg_rating FROM ulasan WHERE id_barang = ?";
        $stmt = $this->conn->prepare($query);
        
        // Jika prepare gagal, tampilkan error dan return default
        if ($stmt === false) {
            error_log("Error preparing rating query: " . $this->conn->error);
            return 4.5;
        }
        
        // Bind parameter dengan error handling
        $bound = $stmt->bind_param("i", $id_barang);
        if ($bound === false) {
            error_log("Error binding rating parameter: " . $stmt->error);
            return 4.5;
        }
        
        // Execute dengan error handling
        $executed = $stmt->execute();
        if ($executed === false) {
            error_log("Error executing rating query: " . $stmt->error);
            return 4.5;
        }
        
        $result = $stmt->get_result();
        if ($result === false) {
            error_log("Error getting rating result: " . $stmt->error);
            return 4.5;
        }
        
        $rating = $result->fetch_assoc();
        return $rating['avg_rating'] ?? 4.5; // Default 4.5 jika tidak ada rating
    }

    /**
     * Mendapatkan path gambar produk
     */
    private function getProductImage($raw_product) {
        $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
        
        $gambar = json_decode($raw_product['gambar'], true);
        
        if (json_last_error() === JSON_ERROR_NONE && is_array($gambar)) {
            if (!empty($gambar)) {
                return $baseUrl . '/assets/images/barang/' . $gambar[0];
            }
        } elseif (!empty($raw_product['gambar'])) {
            if (filter_var($raw_product['gambar'], FILTER_VALIDATE_URL)) {
                return $raw_product['gambar'];
            }
            return $baseUrl . '/assets/images/barang/' . $raw_product['gambar'];
        }
        
        return $this->getDefaultImage($raw_product['nama_barang']);
    }


    /**
     * Mendapatkan gambar default berdasarkan kategori
     */
    private function getDefaultImage($productName) {
        $nama_lower = strtolower($productName);
        
        if (strpos($nama_lower, 'kamera') !== false || strpos($nama_lower, 'camera') !== false) {
            return 'https://images.unsplash.com/photo-1606983340126-99ab4feaa64a?w=400';
        } elseif (strpos($nama_lower, 'lensa') !== false || strpos($nama_lower, 'lens') !== false) {
            return 'https://images.unsplash.com/photo-1617005082133-548c4dd27717?w=400';
        } elseif (strpos($nama_lower, 'tripod') !== false) {
            return 'https://images.unsplash.com/photo-1516035069371-29a1b244cc32?w=400';
        } elseif (strpos($nama_lower, 'flash') !== false || strpos($nama_lower, 'lighting') !== false || strpos($nama_lower, 'godox') !== false) {
            return 'https://images.unsplash.com/photo-1517592043066-3c9c4f6b4c7e?w=400';
        } elseif (strpos($nama_lower, 'gimbal') !== false || strpos($nama_lower, 'stabilizer') !== false || strpos($nama_lower, 'ronin') !== false) {
            return 'https://images.unsplash.com/photo-1551410224-699683e15636?w=400';
        } elseif (strpos($nama_lower, 'mic') !== false || strpos($nama_lower, 'audio') !== false || strpos($nama_lower, 'rode') !== false) {
            return 'https://images.unsplash.com/photo-1478737270239-2f02b77fc618?w=400';
        } else {
            return 'https://images.unsplash.com/photo-1502920917128-1aa500764cbd?w=400';
        }
    }

    /**
     * Mendeteksi kategori produk berdasarkan nama
     */
    private function detectProductCategory($productName) {
        $nama_lower = strtolower($productName);
        
        if (strpos($nama_lower, 'kamera') !== false || strpos($nama_lower, 'camera') !== false) {
            return 'Kamera';
        } elseif (strpos($nama_lower, 'lensa') !== false || strpos($nama_lower, 'lens') !== false) {
            return 'Lensa';
        } elseif (strpos($nama_lower, 'tripod') !== false) {
            return 'Tripod';
        } elseif (strpos($nama_lower, 'flash') !== false || strpos($nama_lower, 'lighting') !== false || strpos($nama_lower, 'godox') !== false) {
            return 'Lighting';
        } elseif (strpos($nama_lower, 'gimbal') !== false || strpos($nama_lower, 'stabilizer') !== false || strpos($nama_lower, 'ronin') !== false) {
            return 'Stabilizer';
        } elseif (strpos($nama_lower, 'mic') !== false || strpos($nama_lower, 'audio') !== false || strpos($nama_lower, 'rode') !== false) {
            return 'Audio';
        } else {
            return 'Aksesoris';
        }
    }

    /**
     * Mendapatkan detail produk by ID
     */
    public function getProductDetail($id_barang) {
        $query = "SELECT b.*, pb.nama as nama_pemilik 
                  FROM barang b
                  JOIN pemilik_barang pb ON b.id_pemilik = pb.id_pemilik
                  WHERE b.id_barang = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id_barang);
        $stmt->execute();
        $result = $stmt->get_result();
        $raw_product = $result->fetch_assoc();

        if (!$raw_product) {
            return null;
        }

        return $this->transformProductData($raw_product);
    }
}