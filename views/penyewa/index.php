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
    <style>
/* Add these styles to your CSS */
.payment-methods {
    margin: 20px 0;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 15px;
    background: #f9f9f9;
}

.payment-tabs {
    display: flex;
    margin-bottom: 15px;
    border-bottom: 1px solid #ddd;
}

.payment-tab {
    padding: 8px 16px;
    background: none;
    border: none;
    cursor: pointer;
    font-size: 14px;
    color: #666;
    border-bottom: 2px solid transparent;
}

.payment-tab.active {
    color: #0066cc;
    border-bottom: 2px solid #0066cc;
    font-weight: bold;
}

.payment-content {
    padding: 10px 0;
}

.qr-code-container {
    text-align: center;
    margin: 15px 0;
    padding: 10px;
    background: white;
    border-radius: 8px;
    display: inline-block;
}

.qr-code {
    width: 150px;
    height: 150px;
}

.ewallet-options, .bank-details {
    margin: 15px 0;
}

.ewallet-option, .bank-option {
    margin: 8px 0;
    padding: 10px;
    background: white;
    border-radius: 6px;
    border: 1px solid #e0e0e0;
}

.bank-option label {
    display: flex;
    align-items: center;
}

.bank-logo {
    width: 30px;
    height: 30px;
    margin-right: 10px;
    object-fit: contain;
}

.payment-instruction {
    background: #f0f8ff;
    padding: 12px;
    border-radius: 6px;
    font-size: 14px;
}

.payment-instruction ol {
    padding-left: 20px;
    margin: 8px 0;
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
            <!-- <?php if ($error_message): ?>
                <div class="error-message">
                    <?php echo safe_output($error_message); ?>
                </div>
            <?php endif; ?> -->

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

                <div class="button-container">
                    <a href="transaksi.php">
                        <button type="button" class="search-btn">Transaksi</button>
                    </a>
                    <button type="submit" class="search-btn">🔍 Cari</button>
                </div>
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
                                <img src="<?php echo htmlspecialchars($product['image']); ?>" 
                                    alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                    class="img-thumbnail"
                                    style="width: 100%; height: 200px; object-fit: cover;"
                                    onerror="this.src='https://via.placeholder.com/300x200?text=Image+Not+Found'">
                            <?php else: ?>
                                <div class="no-image" 
                                    style="width: 100%; height: 200px; background: #f0f0f0; display: flex; align-items: center; justify-content: center;">
                                    <span>No Image</span>
                                </div>
                            <?php endif; ?>
                        </div>
                            
                            <div class="product-info">
                                <!-- <div class="product-category"><?php echo safe_output($product['category']); ?></div> -->
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
            <form id="rentalForm" method="POST" action="proses_rental.php" enctype="multipart/form-data" onsubmit="return submitRentalForm(event)">
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
                
                <!-- Payment Method Section -->
                <div class="payment-methods">
                    <h4>Metode Pembayaran</h4>
                    
                    <div class="payment-tabs">
                        <button type="button" class="payment-tab active" onclick="openPaymentTab('ewallet')">E-Wallet</button>
                        <button type="button" class="payment-tab" onclick="openPaymentTab('bank')">Transfer Bank</button>
                    </div>
                    
                    <!-- E-Wallet Content -->
                    <div id="ewalletContent" class="payment-content">
                        <p>Scan QR code berikut untuk pembayaran:</p>
                        <div class="qr-code-container">
                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=OnlyRentPayment-<?php echo time(); ?>" 
                                 alt="QR Code Pembayaran" class="qr-code">
                        </div>
                        <div class="ewallet-options">
                            <div class="ewallet-option">
                                <input type="radio" id="gopay" name="ewallet" value="gopay" checked>
                                <label for="gopay">GoPay</label>
                            </div>
                            <div class="ewallet-option">
                                <input type="radio" id="ovo" name="ewallet" value="ovo">
                                <label for="ovo">OVO</label>
                            </div>
                            <div class="ewallet-option">
                                <input type="radio" id="dana" name="ewallet" value="dana">
                                <label for="dana">DANA</label>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Bank Transfer Content -->
                    <div id="bankContent" class="payment-content" style="display:none;">
                        <p>Transfer ke rekening berikut:</p>
                        <div class="bank-details">
                            <div class="bank-option">
                                <input type="radio" id="bca" name="bank" value="bca" checked>
                                <label for="bca">
                                    <img src="https://images.seeklogo.com/logo-png/39/1/bca-bank-central-asia-logo-png_seeklogo-399949.png" alt="BCA" class="bank-logo">
                                    <strong>BCA</strong> - 1234567890 (OnlyRent)
                                </label>
                            </div>
                            <div class="bank-option">
                                <input type="radio" id="mandiri" name="bank" value="mandiri">
                                <label for="mandiri">
                                    <img src="https://www.cdnlogo.com/logos/b/21/bank-mandiri.svg" alt="Mandiri" class="bank-logo">
                                    <strong>Mandiri</strong> - 9876543210 (OnlyRent)
                                </label>
                            </div>
                            <div class="bank-option">
                                <input type="radio" id="bri" name="bank" value="bri">
                                <label for="bri">
                                    <img src="https://seeklogo.com/images/B/bank-bri-logo-32EFAA879E-seeklogo.com.png" alt="BRI" class="bank-logo">
                                    <strong>BRI</strong> - 5678901234 (OnlyRent)
                                </label>
                            </div>
                        </div>
                        <div class="payment-instruction">
                            <p><strong>Instruksi Pembayaran:</strong></p>
                            <ol>
                                <li>Pilih bank tujuan</li>
                                <li>Transfer sesuai total pembayaran</li>
                                <li>Masukkan kode <strong>ONLYRENT</strong> di pesan transfer</li>
                                <li>Upload bukti transfer di bawah</li>
                            </ol>
                        </div>
                    </div>
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
        event.preventDefault();
        
        const form = document.getElementById('rentalForm');
        const formData = new FormData(form);
        
        // Validasi tanggal
        const startDate = form.elements['tanggal_sewa'].value;
        const endDate = form.elements['tanggal_kembali'].value;
        
        if (!startDate || !endDate) {
            alert('Harap isi tanggal sewa dan tanggal kembali');
            return false;
        }
        
        if (new Date(startDate) > new Date(endDate)) {
            alert('Tanggal selesai harus setelah tanggal mulai');
            return false;
        }
        
        // Tampilkan loading
        const submitBtn = form.querySelector('.submit-btn');
        submitBtn.disabled = true;
        submitBtn.textContent = 'Memproses...';
        
        // Submit form secara tradisional (bukan AJAX)
        form.submit();
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

    document.querySelectorAll('.product-image img').forEach(img => {
        img.onerror = function() {
            this.src = 'https://media.licdn.com/dms/image/v2/C5112AQEw1fXuabCTyQ/article-inline_image-shrink_1500_2232/article-inline_image-shrink_1500_2232/0/1581099611064?e=1756944000&v=beta&t=BmiOV7zE4n6uu9FyS4bB1ajJtQhYZNvHu2Q6bsQPXYg';
        };
    });

    function openPaymentTab(tabName) {
    // Hide all payment content
    document.querySelectorAll('.payment-content').forEach(content => {
        content.style.display = 'none';
    });
    
    // Show the selected content
    document.getElementById(tabName + 'Content').style.display = 'block';
    
    // Update tab styles
    document.querySelectorAll('.payment-tab').forEach(tab => {
        tab.classList.remove('active');
    });
    event.currentTarget.classList.add('active');
}
    </script>
</body>
</html>