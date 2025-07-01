<?php
    session_start();
    if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 1) {
        header("Location: ../login.php");
        exit();
    }
    $username = $_SESSION['user']['username'];

    // Koneksi ke database
    require_once '../../config/connect_db.php';
    $conn = getDBConnection();

    // Ambil daftar pemilik barang untuk dropdown
    $pemilik_query = "SELECT pb.id_pemilik, pb.nama, p.username 
                      FROM pemilik_barang pb
                      JOIN pengguna p ON pb.id_pengguna = p.id_pengguna";
    $pemilik_result = $conn->query($pemilik_query);
    $daftar_pemilik = [];
    if ($pemilik_result && $pemilik_result->num_rows > 0) {
        while ($row = $pemilik_result->fetch_assoc()) {
            $daftar_pemilik[] = $row;
        }
    }
    $conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OnlyRent - Tambah Barang</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/admin/index.css">
    <style>
        .image-preview-container {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 15px;
        }
        .image-preview {
            position: relative;
            width: 120px;
            height: 120px;
            border: 1px dashed #ccc;
            border-radius: 5px;
            overflow: hidden;
        }
        .image-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .remove-image {
            position: absolute;
            top: 5px;
            right: 5px;
            background: rgba(0,0,0,0.5);
            color: white;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }
        .upload-area {
            border: 2px dashed #0d6efd;
            border-radius: 5px;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            margin-bottom: 15px;
        }
        .upload-area:hover {
            background-color: #f8f9fa;
            border-color: #0b5ed7;
        }
        .upload-area i {
            font-size: 2rem;
            color: #0d6efd;
            margin-bottom: 10px;
        }
        #gambar {
            display: none;
        }
        .highlight {
            border-color: #0b5ed7 !important;
            background-color: #f8f9fa !important;
        }
        .invalid-feedback {
            display: none;
            width: 100%;
            margin-top: 0.25rem;
            font-size: .875em;
            color: #dc3545;
        }
        .was-validated .form-control:invalid ~ .invalid-feedback,
        .was-validated .form-control:invalid ~ .invalid-feedback,
        .form-control.is-invalid ~ .invalid-feedback {
            display: block;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="mobile-overlay"></div>
    <?php include('nav.php'); ?>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Navigation -->
        <?php include('top_nav.php'); ?>

        <!-- Form Content -->
        <div class="content-wrapper">
            <div class="row justify-content-center animate-fade-in">
                <div class="col-lg-8">
                    <!-- Success/Error Messages -->
                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    
                    <div class="card form-card">
                        <div class="card-header">
                            <h4>
                                <i class="fas fa-box-open"></i>
                                Tambah Barang Sewa
                            </h4>
                        </div>
                        <div class="card-body">
                            <form id="barangForm" action="/controllers/BarangController.php?action=create" method="POST" enctype="multipart/form-data" novalidate>
                                <input type="hidden" name="id_pemilik" value="<?php echo $id_pemilik; ?>">
                                
                                <div class="mb-3">
                                    <label for="id_pemilik" class="form-label">Pemilik Barang</label>
                                    <select class="form-select" id="id_pemilik" name="id_pemilik" required>
                                        <option value="" selected disabled>Pilih Pemilik Barang</option>
                                        <?php foreach ($daftar_pemilik as $pemilik): ?>
                                            <option value="<?php echo $pemilik['id_pemilik']; ?>">
                                                <?php echo htmlspecialchars($pemilik['nama'] . ' (' . $pemilik['username'] . ')'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="invalid-feedback">Silakan pilih pemilik barang</div>
                                </div>
                                
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" id="nama_barang" name="nama_barang" placeholder="Nama Barang" required>
                                    <label for="nama_barang">Nama Barang</label>
                                    <div class="invalid-feedback">Nama barang harus diisi</div>
                                </div>
                                
                                <div class="form-floating mb-3">
                                    <textarea class="form-control" id="deskripsi" name="deskripsi" placeholder="Deskripsi" style="height: 120px" required></textarea>
                                    <label for="deskripsi">Deskripsi Barang</label>
                                    <div class="invalid-feedback">Deskripsi harus diisi</div>
                                </div>
                                
                                <div class="form-floating mb-3">
                                    <input type="number" class="form-control" id="harga_sewa" name="harga_sewa" placeholder="Harga Sewa" min="1000" required>
                                    <label for="harga_sewa">Harga Sewa (per hari)</label>
                                    <div class="invalid-feedback">Harga sewa harus angka minimal Rp 1.000</div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Gambar Barang (Minimal 1 gambar, maksimal 5 gambar)</label>
                                    <div class="upload-area" id="uploadArea">
                                        <i class="fas fa-cloud-upload-alt"></i>
                                        <p>Klik atau seret gambar ke sini</p>
                                        <small class="text-muted">Format: JPG, PNG (Maks. 2MB per gambar)</small>
                                    </div>
                                    <input type="file" id="gambar" name="gambar[]" multiple accept="image/*" required>
                                    <div class="image-preview-container" id="imagePreviewContainer"></div>
                                    <div class="invalid-feedback">Minimal 1 gambar harus diupload</div>
                                </div>

                                <div class="d-flex justify-content-between align-items-center mt-4">
                                    <a href="tabel_barang.php" class="btn btn-outline-secondary">
                                        <i class="fas fa-arrow-left me-2"></i>
                                        Kembali
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i>
                                        Simpan Barang
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Mobile Menu Toggle
    document.querySelector('.mobile-menu-toggle')?.addEventListener('click', function() {
        document.querySelector('.sidebar').classList.add('active');
        document.querySelector('.mobile-overlay').classList.add('active');
    });

    // Close Sidebar
    document.querySelector('.mobile-overlay')?.addEventListener('click', function() {
        document.querySelector('.sidebar').classList.remove('active');
        this.classList.remove('active');
    });

    // Toggle Mobile Search
    document.querySelector('.search-toggle')?.addEventListener('click', function() {
        document.querySelector('.mobile-search-box').classList.toggle('d-none');
    });

    // Image upload handling
    const uploadArea = document.getElementById('uploadArea');
    const fileInput = document.getElementById('gambar');
    const imagePreviewContainer = document.getElementById('imagePreviewContainer');
    const maxFiles = 5;
    const maxFileSize = 2 * 1024 * 1024; // 2MB

    // Prevent default drag behaviors
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        uploadArea.addEventListener(eventName, preventDefaults, false);
    });

    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    // Highlight drop area when item is dragged over it
    ['dragenter', 'dragover'].forEach(eventName => {
        uploadArea.addEventListener(eventName, highlight, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        uploadArea.addEventListener(eventName, unhighlight, false);
    });

    function highlight() {
        uploadArea.classList.add('highlight');
    }

    function unhighlight() {
        uploadArea.classList.remove('highlight');
    }

    // Handle dropped files
    uploadArea.addEventListener('drop', handleDrop, false);
    
    function handleDrop(e) {
        const dt = e.dataTransfer;
        const files = dt.files;
        handleFiles(files);
    }

    // Handle clicked files
    uploadArea.addEventListener('click', () => fileInput.click());
    
    fileInput.addEventListener('change', function() {
        handleFiles(this.files);
    });

    function handleFiles(files) {
        // Hapus preview yang ada
        imagePreviewContainer.innerHTML = '';
        
        // Reset file input
        const dataTransfer = new DataTransfer();
        
        // Validasi jumlah file
        if (files.length > maxFiles) {
            alert(`Maksimal ${maxFiles} gambar yang dapat diupload`);
            fileInput.files = dataTransfer.files;
            return;
        }
        
        // Proses setiap file
        for (let i = 0; i < files.length; i++) {
            const file = files[i];
            
            if (!file.type.match('image.*')) {
                alert('Hanya file gambar yang diperbolehkan (JPG, PNG)');
                continue;
            }
            
            if (file.size > maxFileSize) {
                alert(`File ${file.name} terlalu besar (maksimal 2MB)`);
                continue;
            }
            
            // Tambahkan ke file input
            dataTransfer.items.add(file);
            
            // Buat preview
            const reader = new FileReader();
            reader.onload = function(e) {
                const previewDiv = document.createElement('div');
                previewDiv.className = 'image-preview';
                
                const img = document.createElement('img');
                img.src = e.target.result;
                img.alt = 'Preview';
                
                const removeBtn = document.createElement('div');
                removeBtn.className = 'remove-image';
                removeBtn.innerHTML = '<i class="fas fa-times"></i>';
                removeBtn.addEventListener('click', function() {
                    removeImage(file.name);
                    previewDiv.remove();
                });
                
                previewDiv.appendChild(img);
                previewDiv.appendChild(removeBtn);
                imagePreviewContainer.appendChild(previewDiv);
            };
            reader.readAsDataURL(file);
        }
        
        // Update file input
        fileInput.files = dataTransfer.files;
        validateImageUpload();
    }
    
    function removeImage(fileName) {
        const dataTransfer = new DataTransfer();
        let fileRemoved = false;
        
        // Add all files except the one to remove
        for (let i = 0; i < fileInput.files.length; i++) {
            if (fileInput.files[i].name !== fileName) {
                dataTransfer.items.add(fileInput.files[i]);
            } else {
                fileRemoved = true;
            }
        }
        
        fileInput.files = dataTransfer.files;
        
        // If no files left, show the upload area message again
        if (fileInput.files.length === 0) {
            validateImageUpload();
        }
        
        return fileRemoved;
    }
    
    function validateImageUpload() {
        const form = document.getElementById('barangForm');
        if (fileInput.files.length === 0) {
            fileInput.classList.add('is-invalid');
        } else {
            fileInput.classList.remove('is-invalid');
        }
    }
    
    // Form validation
    document.getElementById('barangForm').addEventListener('submit', function(e) {
        // Validate images
        if (fileInput.files.length === 0) {
            e.preventDefault();
            fileInput.classList.add('is-invalid');
            this.classList.add('was-validated');
            return false;
        }
        
        // Validate other fields
        if (!this.checkValidity()) {
            e.preventDefault();
            this.classList.add('was-validated');
            return false;
        }
        
        return true;
    });

    // Validate fields on blur
    document.getElementById('nama_barang').addEventListener('blur', validateField);
    document.getElementById('deskripsi').addEventListener('blur', validateField);
    document.getElementById('harga_sewa').addEventListener('blur', validateField);

    function validateField(e) {
        const field = e.target;
        if (!field.checkValidity()) {
            field.classList.add('is-invalid');
        } else {
            field.classList.remove('is-invalid');
        }
    }
</script>
</body>
</html>