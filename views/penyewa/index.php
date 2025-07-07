<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../login.php");
    exit();
}

require_once '../../config/connect_db.php';
require_once '../../models/penyewa/Barang.php';

$db = getDBConnection();
$barang_model = new Barang($db);

// Input validation and sanitization
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category = isset($_GET['category']) ? trim($_GET['category']) : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'nama_barang';

$allowed_sorts = ['nama_barang', 'price_low', 'price_high', 'status'];
if (!in_array($sort, $allowed_sorts)) {
    $sort = 'nama_barang';
}

try {
    $products = $barang_model->getAvailableProducts($search, $category, $sort);
    $categories = array_unique(array_column($products, 'category'));
    sort($categories);
} catch(Exception $e) {
    error_log("Error: " . $e->getMessage());
    $error_message = "Terjadi kesalahan saat mengambil data. Silakan coba lagi nanti.";
    $products = [];
    $categories = [];
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
    $query = "SELECT b.id_barang, b.id_pemilik, b.nama_barang, b.gambar, 
                 b.deskripsi, b.harga_sewa, b.status,
                 pb.nama as nama_pemilik
          FROM barang b
          JOIN pemilik_barang pb ON b.id_pemilik = pb.id_pemilik
          WHERE b.status = 1"; // 1 = tersedia
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

    // Execute main query menggunakan MySQLi
        $stmt = $db->prepare($query);

        // Bind parameter jika ada
        if (!empty($params)) {
            $types = str_repeat('s', count($params)); // Semua parameter dianggap string
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $raw_products = $result->fetch_all(MYSQLI_ASSOC);

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
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OnlyRent - Camera Rental Marketplace</title>
    <meta name="description" content="Sewa kamera dan peralatan fotografi terbaik di OnlyRent">
    <link rel="stylesheet" href="../../assets/css/penyewa/index.css">
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

                <button type="" class="search-btn">Transaksi</button>
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
                                <?php 
                                // Use the processed image path from the model
                                $imagePath = $product['image'] ?? 'https://via.placeholder.com/400x300?text=No+Image';
                                
                                // Ensure path is properly formatted
                                if (!filter_var($imagePath, FILTER_VALIDATE_URL)) {
                                    // If it's not a full URL, prepend with base URL if needed
                                    $imagePath = '/' . ltrim($imagePath, '/');
                                }
                                ?>
                                <img src="<?php echo htmlspecialchars($imagePath); ?>" 
                                    alt="<?php echo htmlspecialchars($product['name']); ?>"
                                    loading="lazy"
                                    onerror="this.src='https://via.placeholder.com/400x300?text=Image+Not+Found'">
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
<!-- Update the Rental Modal in index.php -->
<div id="rentalModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Sewa Produk</h3>
            <span class="close-btn" onclick="closeModal()">&times;</span>
        </div>
        <div class="modal-body">
            <div id="modalProductInfo"></div>
            <form id="rentalForm" method="POST" action="process_rental.php" enctype="multipart/form-data" onsubmit="return submitRentalForm(event)">
                <input type="hidden" name="id_barang" id="modalProductId">
                <input type="hidden" name="harga_sewa" id="modalProductPrice">
                
                <div class="form-group">
                    <label>Tanggal Mulai:</label>
                    <input type="date" name="tanggal_sewa" id="startDate" required>
                </div>
                <div class="form-group">
                    <label>Tanggal Selesai:</label>
                    <input type="date" name="tanggal_kembali" id="endDate" required>
                </div>
                <div class="form-group">
                    <label>Bukti Pembayaran:</label>
                    <input type="file" name="bukti_pembayaran" accept="image/*" required>
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

// Update the rentProduct function
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
    
    // Set product ID and price in hidden fields
    document.getElementById('modalProductId').value = currentProduct.id;
    document.getElementById('modalProductPrice').value = currentProduct.price;
    
    // Set minimum date to today
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('startDate').min = today;
    document.getElementById('endDate').min = today;
    
    // Show modal
    document.getElementById('rentalModal').style.display = 'flex';
}

function submitRentalForm(event) {
    event.preventDefault(); // Prevent default form submission
    
    // Validate form
    const form = document.getElementById('rentalForm');
    const startDate = form.elements['tanggal_sewa'].value;
    const endDate = form.elements['tanggal_kembali'].value;
    const paymentProof = form.elements['bukti_pembayaran'].files[0];
    
    if (!startDate || !endDate) {
        alert('Harap isi tanggal sewa dan tanggal kembali');
        return false;
    }
    
    if (!paymentProof) {
        alert('Harap upload bukti pembayaran');
        return false;
    }
    
    // Submit form via AJAX
    const formData = new FormData(form);
    
    fetch('process_rental.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Sewa berhasil dikonfirmasi!');
            closeModal();
            window.location.reload(); // Refresh page to update status
        } else {
            alert('Error: ' + (data.message || 'Gagal memproses sewa'));
        }
    })
    .catch(error => {
        alert('Error: ' + error.message);
    });
    
    return false;
}

// Keep the rest of the JavaScript functions the same

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

document.querySelectorAll('.product-image img').forEach(img => {
    img.onerror = function() {
        this.src = 'https://media.licdn.com/dms/image/v2/C5112AQEw1fXuabCTyQ/article-inline_image-shrink_1500_2232/article-inline_image-shrink_1500_2232/0/1581099611064?e=1756944000&v=beta&t=BmiOV7zE4n6uu9FyS4bB1ajJtQhYZNvHu2Q6bsQPXYg';
    };
});
</script>
</body>
</html>