<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Halaman Tidak Ditemukan</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --secondary-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
            --hover-shadow: 0 30px 80px rgba(0, 0, 0, 0.15);
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .error-container {
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: var(--box-shadow);
            transition: all 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .error-container:hover {
            transform: translateY(-5px);
            box-shadow: var(--hover-shadow);
        }

        .error-number {
            font-size: 8rem;
            font-weight: 900;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            line-height: 1;
            text-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .error-icon {
            font-size: 4rem;
            background: var(--secondary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }

        .btn-gradient {
            background: var(--primary-gradient);
            border: none;
            border-radius: 50px;
            padding: 12px 30px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
        }

        .btn-gradient:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 35px rgba(102, 126, 234, 0.4);
            background: var(--secondary-gradient);
        }

        .decorative-element {
            position: absolute;
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            animation: pulse 4s ease-in-out infinite;
        }

        .decorative-element:nth-child(1) {
            top: 10%;
            left: 10%;
            animation-delay: 0s;
        }

        .decorative-element:nth-child(2) {
            top: 20%;
            right: 15%;
            width: 60px;
            height: 60px;
            animation-delay: 2s;
        }

        .decorative-element:nth-child(3) {
            bottom: 15%;
            left: 20%;
            width: 80px;
            height: 80px;
            animation-delay: 1s;
        }

        @keyframes pulse {
            0%, 100% { 
                transform: scale(1);
                opacity: 0.3;
            }
            50% { 
                transform: scale(1.1);
                opacity: 0.6;
            }
        }

        .text-muted-custom {
            color: #6c757d !important;
            font-size: 1.1rem;
            line-height: 1.6;
        }

        @media (max-width: 768px) {
            .error-number {
                font-size: 5rem;
            }
            .error-icon {
                font-size: 2.5rem;
            }
            .decorative-element {
                display: none;
            }
        }

        .search-container {
            position: relative;
            max-width: 400px;
            margin: 0 auto;
        }

        .search-input {
            border-radius: 50px;
            border: 2px solid rgba(102, 126, 234, 0.2);
            padding: 12px 20px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .search-input:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
    </style>
</head>
<body>
    <!-- Decorative Elements -->
    <div class="decorative-element"></div>
    <div class="decorative-element"></div>
    <div class="decorative-element"></div>

    <div class="container-fluid d-flex align-items-center justify-content-center min-vh-100 p-4">
        <div class="row justify-content-center w-100">
            <div class="col-lg-6 col-md-8 col-sm-10">
                <div class="error-container p-5 text-center">
                    <!-- Error Icon -->
                    <div class="mb-4">
                        <i class="fas fa-exclamation-triangle error-icon"></i>
                    </div>
                    
                    <!-- Error Number -->
                    <div class="error-number mb-4">404</div>
                    
                    <!-- Error Message -->
                    <h2 class="h3 mb-3 fw-bold text-dark">Oops! Halaman Tidak Ditemukan</h2>
                    <p class="text-muted-custom mb-4">
                        Maaf, halaman yang Anda cari tidak dapat ditemukan. 
                        Mungkin halaman telah dipindahkan atau tidak tersedia lagi.
                    </p>
                    

                    
                    <!-- Action Buttons -->
                    <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center align-items-center">
                        <button class="btn btn-gradient text-white" onclick="goHome()">
                            <i class="fas fa-home me-2"></i>
                            Kembali ke Beranda
                        </button>
                        <button class="btn btn-outline-secondary rounded-pill px-4" onclick="goBack()">
                            <i class="fas fa-arrow-left me-2"></i>
                            Halaman Sebelumnya
                        </button>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        function goHome() {
            // Redirect to home page
            window.location.href = '/';
        }
        
        function goBack() {
            // Go back to previous page
            window.history.back();
        }
        
        // Search functionality - removed
        
        // Add some interactive effects
        document.addEventListener('DOMContentLoaded', function() {
            const container = document.querySelector('.error-container');
            
            // Add subtle mouse move effect
            container.addEventListener('mousemove', function(e) {
                const rect = container.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;
                
                const xRotation = (y - rect.height / 2) / rect.height * 5;
                const yRotation = (rect.width / 2 - x) / rect.width * 5;
                
                container.style.transform = `perspective(1000px) rotateX(${xRotation}deg) rotateY(${yRotation}deg) translateY(-5px)`;
            });
            
            container.addEventListener('mouseleave', function() {
                container.style.transform = 'perspective(1000px) rotateX(0deg) rotateY(0deg) translateY(0px)';
            });
        });
    </script>
</body>
</html>