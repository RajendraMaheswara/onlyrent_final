<?php
// Database configuration - better to use config file
$config = [
    'host' => 'localhost',
    'dbname' => 'onlyrentdone',
    'username' => 'root',
    'password' => ''
];

// Database connection with better error handling
try {
    $pdo = new PDO("mysql:host={$config['host']};dbname={$config['dbname']}", 
                   $config['username'], $config['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    die("Koneksi database gagal. Silakan coba lagi nanti.");
}

// Input validation and sanitization
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category = isset($_GET['category']) ? trim($_GET['category']) : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'nama_barang';

// Validate sort parameter
$allowed_sorts = ['nama_barang', 'price_low', 'price_high', 'status'];
if (!in_array($sort, $allowed_sorts)) {
    $sort = 'nama_barang';
}

// Initialize variables
$products = [];
$categories = [];
$error_message = '';

try {
    // Build query with proper parameter binding - FIXED: Added 'gambar' column
    $query = "SELECT id_barang, id_pemilik, nama_barang, gambar, deskripsi, harga_sewa, status 
              FROM barang 
              WHERE status = 'tersedia'";
    $params = [];

    // Apply search filter in SQL
    if (!empty($search)) {
        $query .= " AND (nama_barang LIKE ? OR deskripsi LIKE ?)";
        $params[] = "%{$search}%";
        $params[] = "%{$search}%";
    }

    // Apply category filter (berdasarkan kategori yang auto-detect dari nama)
    if (!empty($category)) {
        $query .= " AND (
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
        // Add category parameter 7 times for each condition
        for($i = 0; $i < 7; $i++) {
            $params[] = $category;
        }
    }

    // Apply sorting in SQL
    switch($sort) {
        case 'price_low':
            $query .= " ORDER BY harga_sewa ASC";
            break;
        case 'price_high':
            $query .= " ORDER BY harga_sewa DESC";
            break;
        case 'status':
            $query .= " ORDER BY status ASC";
            break;
        default:
            $query .= " ORDER BY nama_barang ASC";
    }

    // Execute main query
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $raw_products = $stmt->fetchAll();

    // FIXED: Transform data sesuai dengan struktur database yang baru
    foreach($raw_products as $raw_product) {
        $product = [];
        $product['id'] = $raw_product['id_barang'];
        $product['name'] = $raw_product['nama_barang'];
        $product['description'] = $raw_product['deskripsi'];
        $product['price'] = $raw_product['harga_sewa'];
        $product['rating'] = 4.5; // Default rating, bisa disesuaikan atau ambil dari tabel rating
        $product['status'] = $raw_product['status'];
        $product['id_pemilik'] = $raw_product['id_pemilik'];
        
        // FIXED: Handle gambar dari database
        $nama_lower = strtolower($product['name']);
        
        // Prioritaskan gambar dari database
        if (!empty($raw_product['gambar'])) {
            // Check if gambar is a full URL or just filename
            if (filter_var($raw_product['gambar'], FILTER_VALIDATE_URL)) {
                $product['image'] = $raw_product['gambar'];
            } else {
                // Assume it's a filename stored in uploads directory
                $product['image'] = 'uploads/' . $raw_product['gambar'];
            }
        } else {
            // Fallback ke default images berdasarkan kategori jika gambar kosong
            if (strpos($nama_lower, 'kamera') !== false || strpos($nama_lower, 'camera') !== false) {
                $product['image'] = 'https://images.unsplash.com/photo-1606983340126-99ab4feaa64a?w=400';
            } elseif (strpos($nama_lower, 'lensa') !== false || strpos($nama_lower, 'lens') !== false) {
                $product['image'] = 'https://images.unsplash.com/photo-1617005082133-548c4dd27717?w=400';
            } elseif (strpos($nama_lower, 'tripod') !== false) {
                $product['image'] = 'https://images.unsplash.com/photo-1516035069371-29a1b244cc32?w=400';
            } elseif (strpos($nama_lower, 'flash') !== false || strpos($nama_lower, 'lighting') !== false || strpos($nama_lower, 'godox') !== false) {
                $product['image'] = 'https://images.unsplash.com/photo-1517592043066-3c9c4f6b4c7e?w=400';
            } elseif (strpos($nama_lower, 'gimbal') !== false || strpos($nama_lower, 'stabilizer') !== false || strpos($nama_lower, 'ronin') !== false) {
                $product['image'] = 'https://images.unsplash.com/photo-1551410224-699683e15636?w=400';
            } elseif (strpos($nama_lower, 'mic') !== false || strpos($nama_lower, 'audio') !== false || strpos($nama_lower, 'rode') !== false) {
                $product['image'] = 'https://images.unsplash.com/photo-1478737270239-2f02b77fc618?w=400';
            } else {
                $product['image'] = 'https://images.unsplash.com/photo-1502920917128-1aa500764cbd?w=400';
            }
        }
        
        // Auto-detect kategori berdasarkan nama barang
        if (strpos($nama_lower, 'kamera') !== false || strpos($nama_lower, 'camera') !== false) {
            $product['category'] = 'Kamera';
        } elseif (strpos($nama_lower, 'lensa') !== false || strpos($nama_lower, 'lens') !== false) {
            $product['category'] = 'Lensa';
        } elseif (strpos($nama_lower, 'tripod') !== false) {
            $product['category'] = 'Tripod';
        } elseif (strpos($nama_lower, 'flash') !== false || strpos($nama_lower, 'lighting') !== false || strpos($nama_lower, 'godox') !== false) {
            $product['category'] = 'Lighting';
        } elseif (strpos($nama_lower, 'gimbal') !== false || strpos($nama_lower, 'stabilizer') !== false || strpos($nama_lower, 'ronin') !== false) {
            $product['category'] = 'Stabilizer';
        } elseif (strpos($nama_lower, 'mic') !== false || strpos($nama_lower, 'audio') !== false || strpos($nama_lower, 'rode') !== false) {
            $product['category'] = 'Audio';
        } else {
            $product['category'] = 'Aksesoris';
        }
        
        $products[] = $product;
    }

    // Get categories for filter (berdasarkan produk yang ada)
    $categories = array_unique(array_column($products, 'category'));
    sort($categories);

} catch(PDOException $e) {
    error_log("Database query error: " . $e->getMessage());
    $error_message = "Terjadi kesalahan saat mengambil data. Silakan coba lagi nanti.";
    $products = [];
    $categories = [];
}

// Helper function for safe output
function safe_output($text, $max_length = null) {
    $safe_text = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    if ($max_length && strlen($safe_text) > $max_length) {
        return substr($safe_text, 0, $max_length) . '...';
    }
    return $safe_text;
}

// Helper function for rating stars
function render_stars($rating) {
    $rating = floatval($rating);
    $stars = '';
    for($i = 1; $i <= 5; $i++) {
        $stars .= $i <= $rating ? '★' : '☆';
    }
    return $stars;
}

// Helper function untuk status badge
function get_status_badge($status) {
    switch(strtolower($status)) {
        case 'tersedia':
            return '<span style="background: #10b981; color: white; padding: 4px 8px; border-radius: 12px; font-size: 0.8rem;">Tersedia</span>';
        case 'disewa':
            return '<span style="background: #f59e0b; color: white; padding: 4px 8px; border-radius: 12px; font-size: 0.8rem;">Disewa</span>';
        case 'maintenance':
            return '<span style="background: #ef4444; color: white; padding: 4px 8px; border-radius: 12px; font-size: 0.8rem;">Maintenance</span>';
        default:
            return '<span style="background: #6b7280; color: white; padding: 4px 8px; border-radius: 12px; font-size: 0.8rem;">' . ucfirst($status) . '</span>';
    }
}

// ADDED: Helper function untuk handle gambar
function get_safe_image_url($image_path) {
    if (empty($image_path)) {
        return null;
    }
    
    // If it's already a full URL, return as is
    if (filter_var($image_path, FILTER_VALIDATE_URL)) {
        return $image_path;
    }
    
    // Check if local file exists
    if (file_exists($image_path)) {
        return $image_path;
    }
    
    // Check in uploads directory
    $uploads_path = 'uploads/' . basename($image_path);
    if (file_exists($uploads_path)) {
        return $uploads_path;
    }
    
    return null;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OnlyRent - Camera Rental Marketplace</title>
    <meta name="description" content="Sewa kamera dan peralatan fotografi terbaik di OnlyRent">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            backdrop-filter: blur(10px);
        }

        .header {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            color: white;
            padding: 30px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            animation: rotate 20s linear infinite;
        }

        @keyframes rotate {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 10px;
            position: relative;
            z-index: 1;
        }

        .header p {
            font-size: 1.1rem;
            opacity: 0.9;
            position: relative;
            z-index: 1;
        }

        .search-section {
            padding: 30px;
            background: white;
            border-bottom: 1px solid #e5e7eb;
        }

        .search-form {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .search-input {
            flex: 1;
            min-width: 250px;
            padding: 15px 20px;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        .search-input:focus {
            outline: none;
            border-color: #4f46e5;
            box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
        }

        .filter-select {
            padding: 15px 20px;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            font-size: 16px;
            background: white;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .filter-select:focus {
            outline: none;
            border-color: #4f46e5;
        }

        .search-btn {
            padding: 15px 25px;
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            white-space: nowrap;
        }

        .search-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(79, 70, 229, 0.3);
        }

        .categories {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .category-btn {
            padding: 10px 20px;
            background: #f3f4f6;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 500;
            text-decoration: none;
            color: #374151;
        }

        .category-btn:hover, .category-btn.active {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            color: white;
            transform: translateY(-1px);
        }

        .products-section {
            padding: 30px;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .section-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1f2937;
        }

        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 25px;
        }

        .product-card {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            border: 1px solid #f3f4f6;
        }

        .product-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }

        .product-image {
            width: 100%;
            height: 200px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 3rem;
            position: relative;
            overflow: hidden;
        }

        .product-image::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.3);
        }

        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .product-info {
            padding: 20px;
        }

        .product-category {
            font-size: 0.8rem;
            color: #6b7280;
            text-transform: uppercase;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .product-name {
            font-size: 1.2rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 8px;
            line-height: 1.3;
        }

        .product-description {
            color: #6b7280;
            font-size: 0.9rem;
            margin-bottom: 15px;
            line-height: 1.5;
        }

        .product-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .product-rating {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .stars {
            color: #fbbf24;
        }

        .rating-text {
            font-size: 0.9rem;
            color: #6b7280;
        }

        .product-price {
            font-size: 1.3rem;
            font-weight: 700;
            color: #1f2937;
        }

        .price-period {
            font-size: 0.9rem;
            color: #6b7280;
            font-weight: 400;
        }

        .rent-btn {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .rent-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(79, 70, 229, 0.3);
        }

        .no-products {
            text-align: center;
            padding: 60px 20px;
            color: #6b7280;
        }

        .no-products h3 {
            font-size: 1.5rem;
            margin-bottom: 10px;
        }

        .error-message {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #dc2626;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        @media (max-width: 768px) {
            .search-form {
                flex-direction: column;
            }
            
            .search-input {
                min-width: 100%;
            }
            
            .section-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .products-grid {
                grid-template-columns: 1fr;
            }
        }
        /* Modal Styles */
.modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(5px);
    z-index: 1000;
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-content {
    background: white;
    border-radius: 20px;
    max-width: 500px;
    width: 90%;
    max-height: 80vh;
    overflow-y: auto;
    box-shadow: 0 25px 50px rgba(0, 0, 0, 0.2);
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 30px;
    border-bottom: 1px solid #e5e7eb;
}

.modal-header h3 {
    margin: 0;
    color: #1f2937;
}

.close-btn {
    font-size: 24px;
    cursor: pointer;
    color: #6b7280;
    transition: color 0.3s ease;
}

.close-btn:hover {
    color: #1f2937;
}

.modal-body {
    padding: 30px;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: 600;
    color: #374151;
}

.form-group input {
    width: 100%;
    padding: 12px 15px;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    font-size: 16px;
    transition: border-color 0.3s ease;
}

.form-group input:focus {
    outline: none;
    border-color: #4f46e5;
}

.total-price {
    background: #f3f4f6;
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 20px;
    text-align: center;
    font-size: 18px;
}

.submit-btn {
    width: 100%;
    padding: 15px;
    background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
    color: white;
    border: none;
    border-radius: 10px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
}

.submit-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(79, 70, 229, 0.3);
}

.product-detail {
    display: flex;
    gap: 15px;
    margin-bottom: 20px;
    padding: 15px;
    background: #f9fafb;
    border-radius: 10px;
}

.product-detail img {
    width: 80px;
    height: 80px;
    object-fit: cover;
    border-radius: 8px;
}

.product-detail-info h4 {
    margin: 0 0 5px 0;
    color: #1f2937;
}

.product-detail-info p {
    margin: 0;
    color: #6b7280;
    font-size: 14px;
}

.product-detail-price {
    font-weight: 700;
    color: #4f46e5;
    margin-top: 5px;
}
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>📸 OnlyRent</h1>
            <p>Rental Kamera & Peralatan Fotografi Terbaik</p>
        </div>

        <!-- Search Section -->
        <div class="search-section">
            <?php if ($error_message): ?>
                <div class="error-message">
                    <?php echo safe_output($error_message); ?>
                </div>
            <?php endif; ?>

            <form class="search-form" method="GET" action="">
                <input type="text" name="search" class="search-input" 
                       placeholder="Cari kamera, lensa, atau peralatan..." 
                       value="<?php echo safe_output($search); ?>"
                       maxlength="100">
                
                <select name="category" class="filter-select">
                    <option value="">Semua Kategori</option>
                    <?php foreach($categories as $cat): ?>
                        <option value="<?php echo safe_output($cat); ?>" 
                                <?php echo $category === $cat ? 'selected' : ''; ?>>
                            <?php echo safe_output($cat); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <select name="sort" class="filter-select">
                    <option value="name" <?php echo $sort === 'name' ? 'selected' : ''; ?>>Nama A-Z</option>
                    <option value="price_low" <?php echo $sort === 'price_low' ? 'selected' : ''; ?>>Harga Terendah</option>
                    <option value="price_high" <?php echo $sort === 'price_high' ? 'selected' : ''; ?>>Harga Tertinggi</option>
                    <option value="rating" <?php echo $sort === 'rating' ? 'selected' : ''; ?>>Rating Tertinggi</option>
                </select>

                <button type="submit" class="search-btn">🔍 Cari</button>
            </form>

            <div class="categories">
                <a href="?" class="category-btn <?php echo empty($category) ? 'active' : ''; ?>">Semua</a>
                <?php foreach($categories as $cat): ?>
                    <a href="?category=<?php echo urlencode($cat); ?>&search=<?php echo urlencode($search); ?>&sort=<?php echo urlencode($sort); ?>" 
                       class="category-btn <?php echo $category === $cat ? 'active' : ''; ?>">
                        <?php echo safe_output($cat); ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Products Section -->
        <div class="products-section">
            <div class="section-header">
                <h2 class="section-title">Produk Tersedia (<?php echo count($products); ?>)</h2>
            </div>

            <?php if (empty($products) && !$error_message): ?>
                <div class="no-products">
                    <h3>Tidak ada produk ditemukan</h3>
                    <p>Coba ubah kata kunci pencarian atau filter kategori</p>
                </div>
            <?php elseif (!empty($products)): ?>
                <div class="products-grid">
                    <?php foreach($products as $product): ?>
                        <div class="product-card">
                            <div class="product-image">
                                <?php if (!empty($product['image'])): ?>
                                    <img src="<?php echo safe_output($product['image']); ?>" 
                                         alt="<?php echo safe_output($product['name']); ?>"
                                         loading="lazy">
                                <?php else: ?>
                                    📷
                                <?php endif; ?>
                            </div>
                            
                            <div class="product-info">
                                <div class="product-category"><?php echo safe_output($product['category']); ?></div>
                                <h3 class="product-name"><?php echo safe_output($product['name']); ?></h3>
                                <p class="product-description"><?php echo safe_output($product['description'], 100); ?></p>
                                
                                <div class="product-meta">
                                    <div class="product-rating">
                                        <span class="stars">
                                            <?php echo render_stars($product['rating']); ?>
                                        </span>
                                        <span class="rating-text">(<?php echo safe_output($product['rating']); ?>)</span>
                                    </div>
                                    <div class="product-price">
                                        Rp <?php echo number_format($product['price'], 0, ',', '.'); ?>
                                        <span class="price-period">/hari</span>
                                    </div>
                                </div>
                                
                                <button class="rent-btn" onclick="rentProduct(<?php echo intval($product['id']); ?>)">
                                    Sewa Sekarang
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
<!-- Rental Modal -->
<div id="rentalModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Sewa Produk</h3>
            <span class="close-btn" onclick="closeModal()">&times;</span>
        </div>
        <div class="modal-body">
            <div id="modalProductInfo"></div>
            <form id="rentalForm">
                <div class="form-group">
                    <label>Tanggal Mulai:</label>
                    <input type="date" id="startDate" required>
                </div>
                <div class="form-group">
                    <label>Tanggal Selesai:</label>
                    <input type="date" id="endDate" required>
                </div>
                <div class="form-group">
                    <label>Nama Lengkap:</label>
                    <input type="text" id="customerName" required>
                </div>
                <div class="form-group">
                    <label>No. Telepon:</label>
                    <input type="tel" id="customerPhone" required>
                </div>
                <div class="total-price">
                    <strong>Total: <span id="totalPrice">Rp 0</span></strong>
                </div>
                <button type="submit" class="submit-btn">Konfirmasi Sewa</button>
            </form>
        </div>
    </div>
</div>
    <script>
let currentProduct = null;

function rentProduct(productId) {
    if (!productId || productId <= 0) {
        alert('ID produk tidak valid');
        return;
    }
    
    // Find product by ID
    const products = <?php echo json_encode($products); ?>;
    currentProduct = products.find(p => p.id == productId);
    
    if (!currentProduct) {
        alert('Produk tidak ditemukan');
        return;
    }
    
    // Populate modal
    const modalInfo = document.getElementById('modalProductInfo');
    modalInfo.innerHTML = `
        <div class="product-detail">
            <img src="${currentProduct.image || 'https://via.placeholder.com/80x80?text=📷'}" alt="${currentProduct.name}">
            <div class="product-detail-info">
                <h4>${currentProduct.name}</h4>
                <p>${currentProduct.category}</p>
                <div class="product-detail-price">Rp ${new Intl.NumberFormat('id-ID').format(currentProduct.price)}/hari</div>
            </div>
        </div>
    `;
    
    // Set minimum date to today
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('startDate').min = today;
    document.getElementById('endDate').min = today;
    
    // Show modal
    document.getElementById('rentalModal').style.display = 'flex';
}

function closeModal() {
    document.getElementById('rentalModal').style.display = 'none';
    document.getElementById('rentalForm').reset();
    currentProduct = null;
}

function calculateTotal() {
    const startDate = document.getElementById('startDate').value;
    const endDate = document.getElementById('endDate').value;
    
    if (startDate && endDate && currentProduct) {
        const start = new Date(startDate);
        const end = new Date(endDate);
        const diffTime = Math.abs(end - start);
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1; // +1 to include start day
        
        if (diffDays > 0) {
            const total = diffDays * currentProduct.price;
            document.getElementById('totalPrice').textContent = 
                `Rp ${new Intl.NumberFormat('id-ID').format(total)} (${diffDays} hari)`;
        }
    }
}

// Event listeners
document.getElementById('startDate').addEventListener('change', function() {
    document.getElementById('endDate').min = this.value;
    calculateTotal();
});

document.getElementById('endDate').addEventListener('change', calculateTotal);

document.getElementById('rentalForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = {
        productId: currentProduct.id,
        productName: currentProduct.name,
        startDate: document.getElementById('startDate').value,
        endDate: document.getElementById('endDate').value,
        customerName: document.getElementById('customerName').value,
        customerPhone: document.getElementById('customerPhone').value,
        totalPrice: document.getElementById('totalPrice').textContent
    };
    
    // Simulate API call
    alert(`Rental berhasil dikonfirmasi!\n\nDetail:\n${formData.productName}\n${formData.startDate} - ${formData.endDate}\nPenyewa: ${formData.customerName}\nTotal: ${formData.totalPrice}`);
    
    closeModal();
});

// Close modal when clicking outside
document.getElementById('rentalModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal();
    }
});

// Form validation
document.querySelector('.search-form').addEventListener('submit', function(e) {
    const searchInput = this.querySelector('input[name="search"]');
    if (searchInput.value.length > 100) {
        alert('Kata kunci pencarian terlalu panjang (maksimal 100 karakter)');
        e.preventDefault();
    }
});
</script>
</body>
</html>