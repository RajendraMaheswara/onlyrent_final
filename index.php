<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>OnlyRent | Premium Rental Platform</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <link rel="stylesheet" href="./assets/css/landing.css" />
</head>
<body>
  <!-- Theme & Language Switcher -->
  <div class="settings-container">
    <div class="dropdown">
      <button class="settings-btn" title="Change Language">
        <i class="fas fa-language"></i>
      </button>
      <div class="dropdown-content" id="language-dropdown">
        <a href="#" data-lang="en">English</a>
        <a href="#" data-lang="id">Indonesia</a>
        <a href="#" data-lang="ja">日本語</a>
        <a href="#" data-lang="zh">中文</a>
      </div>
    </div>
    <button class="settings-btn" id="theme-toggle" title="Toggle Dark Mode">
      <i class="fas fa-moon"></i>
    </button>
  </div>

  <!-- Navbar -->
  <header>
    <nav class="navbar">
      <a href="#" class="logo">
        <i class="fas fa-camera-retro logo-icon"></i>
        <span data-i18n="app.title">OnlyRent</span>
      </a>

      <div class="nav-links">
        <a href="#inventory" class="nav-link" data-i18n="nav.inventory">Inventory</a>
        <a href="#how-it-works" class="nav-link" data-i18n="nav.how_it_works">How It Works</a>
        <a href="#contact" class="nav-link" data-i18n="nav.contact">Contact</a>
        <a href="login.php" class="nav-link">Login</a>
      </div>
    </nav>
  </header>

  <!-- Hero Section -->
  <section class="hero">
    <div class="hero-content">
      <h1 class="hero-title" data-i18n="hero.title">
        Premium Equipment Rentals
      </h1>
      <p class="hero-subtitle" data-i18n="hero.subtitle">
        Professional gear for your creative projects at affordable rates
      </p>
      <a href="#inventory" class="btn btn-primary" data-i18n="hero.button">Browse Inventory</a>
    </div>
  </section>

  <!-- Main Content -->
  <main class="container">
    <!-- How It Works Section -->
    <section id="how-it-works" class="section">
      <h2 class="section-title" data-i18n="section.how_it_works">How It Works</h2>
      <div class="how-it-works">
        <p style="text-align: center; max-width: 700px; margin: 0 auto" data-i18n="section.how_it_works_desc">
          Renting equipment with OnlyRent is simple and hassle-free. Follow these easy steps to get the gear you need for your next project.
        </p>

        <div class="steps">
          <div class="step">
            <div class="step-number">1</div>
            <h3 class="step-title" data-i18n="step.1.title">Browse & Select</h3>
            <p data-i18n="step.1.desc">
              Explore our extensive inventory of professional equipment and select the items you need for your project.
            </p>
          </div>

          <div class="step">
            <div class="step-number">2</div>
            <h3 class="step-title" data-i18n="step.2.title">Choose Rental Period</h3>
            <p data-i18n="step.2.desc">
              Select your rental dates and duration. Prices are calculated based on daily rates.
            </p>
          </div>

          <div class="step">
            <div class="step-number">3</div>
            <h3 class="step-title" data-i18n="step.3.title">Make Payment</h3>
            <p data-i18n="step.3.desc">
              Pay securely online. We accept various payment methods including credit cards and e-wallets.
            </p>
          </div>

          <div class="step">
            <div class="step-number">4</div>
            <h3 class="step-title" data-i18n="step.4.title">Pickup or Delivery</h3>
            <p data-i18n="step.4.desc">
              Collect your equipment at our location or opt for delivery (additional fees may apply).
            </p>
          </div>

          <div class="step">
            <div class="step-number">5</div>
            <h3 class="step-title" data-i18n="step.5.title">Return & Get Deposit Back</h3>
            <p data-i18n="step.5.desc">
              Return the equipment on time and in good condition to receive your security deposit refund.
            </p>
          </div>
        </div>
      </div>
    </section>

    <!-- Inventory Section -->
    <section id="inventory" class="section">
      <h2 class="section-title" data-i18n="section.inventory">Our Equipment</h2>
      <div class="grid">
        <!-- Sample Equipment Cards -->
        <div class="card">
          <div class="card-badge">Popular</div>
          <div class="card-img-container">
            <img src="https://images.unsplash.com/photo-1502920917128-1aa500764cbd?w=400&h=300&fit=crop" alt="Canon EOS R5" class="card-img">
          </div>
          <div class="card-body">
            <h3 class="card-title">Canon EOS R5</h3>
            <div class="card-meta">
              <span><i class="fas fa-camera"></i> DSLR Camera</span>
              <span><i class="fas fa-star"></i> 4.9</span>
            </div>
            <div class="card-price">
              Rp 150,000 <small>/day</small>
            </div>
            <div class="stars">
              <i class="fas fa-star"></i>
              <i class="fas fa-star"></i>
              <i class="fas fa-star"></i>
              <i class="fas fa-star"></i>
              <i class="fas fa-star"></i>
            </div>
            <button class="btn btn-primary btn-block">Rent Now</button>
          </div>
        </div>

        <div class="card">
          <div class="card-badge">New</div>
          <div class="card-img-container">
            <img src="https://images.unsplash.com/photo-1567450024020-5b5c5f2e5d6a?w=400&h=300&fit=crop" alt="Sony FX3" class="card-img">
          </div>
          <div class="card-body">
            <h3 class="card-title">Sony FX3</h3>
            <div class="card-meta">
              <span><i class="fas fa-video"></i> Cinema Camera</span>
              <span><i class="fas fa-star"></i> 4.8</span>
            </div>
            <div class="card-price">
              Rp 200,000 <small>/day</small>
            </div>
            <div class="stars">
              <i class="fas fa-star"></i>
              <i class="fas fa-star"></i>
              <i class="fas fa-star"></i>
              <i class="fas fa-star"></i>
              <i class="fas fa-star"></i>
            </div>
            <button class="btn btn-primary btn-block">Rent Now</button>
          </div>
        </div>

        <div class="card">
          <div class="card-img-container">
            <img src="https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=400&h=300&fit=crop" alt="MacBook Pro" class="card-img">
          </div>
          <div class="card-body">
            <h3 class="card-title">MacBook Pro 16"</h3>
            <div class="card-meta">
              <span><i class="fas fa-laptop"></i> Laptop</span>
              <span><i class="fas fa-star"></i> 4.7</span>
            </div>
            <div class="card-price">
              Rp 100,000 <small>/day</small>
            </div>
            <div class="stars">
              <i class="fas fa-star"></i>
              <i class="fas fa-star"></i>
              <i class="fas fa-star"></i>
              <i class="fas fa-star"></i>
              <i class="fas fa-star"></i>
            </div>
            <button class="btn btn-primary btn-block">Rent Now</button>
          </div>
        </div>

        <div class="card">
          <div class="card-img-container">
            <img src="https://images.unsplash.com/photo-1558618047-b34eb8254eb8?w=400&h=300&fit=crop" alt="Lighting Kit" class="card-img">
          </div>
          <div class="card-body">
            <h3 class="card-title">Professional Lighting Kit</h3>
            <div class="card-meta">
              <span><i class="fas fa-lightbulb"></i> Lighting</span>
              <span><i class="fas fa-star"></i> 4.6</span>
            </div>
            <div class="card-price">
              Rp 75,000 <small>/day</small>
            </div>
            <div class="stars">
              <i class="fas fa-star"></i>
              <i class="fas fa-star"></i>
              <i class="fas fa-star"></i>
              <i class="fas fa-star"></i>
              <i class="fas fa-star"></i>
            </div>
            <button class="btn btn-primary btn-block">Rent Now</button>
          </div>
        </div>

        <div class="card">
          <div class="card-img-container">
            <img src="https://images.unsplash.com/photo-1583394838336-acd977736f90?w=400&h=300&fit=crop" alt="Tripod" class="card-img">
          </div>
          <div class="card-body">
            <h3 class="card-title">Carbon Fiber Tripod</h3>
            <div class="card-meta">
              <span><i class="fas fa-camera-retro"></i> Tripod</span>
              <span><i class="fas fa-star"></i> 4.5</span>
            </div>
            <div class="card-price">
              Rp 25,000 <small>/day</small>
            </div>
            <div class="stars">
              <i class="fas fa-star"></i>
              <i class="fas fa-star"></i>
              <i class="fas fa-star"></i>
              <i class="fas fa-star"></i>
              <i class="fas fa-star"></i>
            </div>
            <button class="btn btn-primary btn-block">Rent Now</button>
          </div>
        </div>

        <div class="card">
          <div class="card-badge">Hot</div>
          <div class="card-img-container">
            <img src="https://images.unsplash.com/photo-1572025442646-866d16c84817?w=400&h=300&fit=crop" alt="Audio Equipment" class="card-img">
          </div>
          <div class="card-body">
            <h3 class="card-title">Audio Recording Kit</h3>
            <div class="card-meta">
              <span><i class="fas fa-microphone"></i> Audio</span>
              <span><i class="fas fa-star"></i> 4.8</span>
            </div>
            <div class="card-price">
              Rp 50,000 <small>/day</small>
            </div>
            <div class="stars">
              <i class="fas fa-star"></i>
              <i class="fas fa-star"></i>
              <i class="fas fa-star"></i>
              <i class="fas fa-star"></i>
              <i class="fas fa-star"></i>
            </div>
            <button class="btn btn-primary btn-block">Rent Now</button>
          </div>
        </div>
      </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="section">
      <h2 class="section-title" data-i18n="section.contact">Contact Us</h2>
      <div class="contact-section">
        <p style="max-width: 600px; margin: 0 auto 1rem" data-i18n="contact.desc">
          Have questions or need assistance? Reach out to us through our social media channels. We're here to help!
        </p>

        <div class="contact-methods">
          <div class="contact-method">
            <div class="contact-icon">
              <i class="fab fa-instagram"></i>
            </div>
            <h3 class="contact-title" data-i18n="contact.instagram">Instagram</h3>
            <p data-i18n="contact.instagram_desc">Follow us for updates</p>
            <a href="https://www.instagram.com/randompictc_/" class="contact-link" target="_blank">
              <i class="fab fa-instagram"></i>
              <span data-i18n="contact.instagram_handle">@onlyrent</span>
            </a>
          </div>

          <div class="contact-method">
            <div class="contact-icon">
              <i class="fab fa-whatsapp"></i>
            </div>
            <h3 class="contact-title" data-i18n="contact.whatsapp">WhatsApp</h3>
            <p data-i18n="contact.whatsapp_desc">Fast response for inquiries</p>
            <a href="https://wa.me/6281234567890" class="contact-link" target="_blank">
              <i class="fab fa-whatsapp"></i>
              <span data-i18n="contact.whatsapp_number">+62 812-3456-7890</span>
            </a>
          </div>
        </div>
      </div>
    </section>
  </main>

  <!-- Footer -->
  <footer class="footer">
    <!-- Wave decoration -->
    <div class="wave-decoration">
      <svg style="display: block; width: calc(100% + 1.3px); height: 50px" viewBox="0 0 1200 120" preserveAspectRatio="none">
        <path d="M0,0V46.29c47.79,22.2,103.59,32.17,158,28,70.36-5.37,136.33-33.31,206.8-37.5C438.64,32.43,512.34,53.67,583,72.05c69.27,18,138.3,24.88,209.4,13.08,36.15-6,69.85-17.84,104.45-29.34C989.49,25,1113-14.29,1200,52.47V0Z" opacity=".25" fill="var(--bg-light)"></path>
        <path d="M0,0V15.81C13,36.92,27.64,56.86,47.69,72.05,99.41,111.27,165,111,224.58,91.58c31.15-10.15,60.09-26.07,89.67-39.8,40.92-19,84.73-46,130.83-49.67,36.26-2.85,70.9,9.42,98.6,31.56,31.77,25.39,62.32,62,103.63,73,40.44,10.79,81.35-6.69,119.13-24.28s75.16-39,116.92-43.05c59.73-5.85,113.28,22.88,168.9,38.84,30.2,8.66,59,6.17,87.09-7.5,22.43-10.89,48-26.93,60.65-49.24V0Z" opacity=".5" fill="var(--bg-light)"></path>
        <path d="M0,0V5.63C149.93,59,314.09,71.32,475.83,42.57c43-7.64,84.23-20.12,127.61-26.46,59-8.63,112.48,12.24,165.56,35.4C827.93,77.22,886,95.24,951.2,90c86.53-7,172.46-45.71,248.8-84.81V0Z" fill="var(--bg-light)"></path>
      </svg>
    </div>

    <div class="container footer-content">
      <div class="footer-info">
        <!-- Logo with animation -->
        <div class="footer-logo">
          <i class="fas fa-camera-retro footer-logo-icon"></i>
          <span class="footer-logo-text">OnlyRent</span>
        </div>

        <!-- Tagline with animated underline -->
        <div class="footer-tagline-container">
          <p class="footer-tagline" data-i18n="footer.tagline">Premium equipment rental service</p>
          <div class="footer-underline"></div>
        </div>

        <!-- Social icons -->
        <div class="footer-social">
          <a href="https://instagram.com/onlyrent" class="social-link" target="_blank">
            <i class="fab fa-instagram"></i>
          </a>
          <a href="https://wa.me/6281234567890" class="social-link" target="_blank">
            <i class="fab fa-whatsapp"></i>
          </a>
          <a href="#" class="social-link">
            <i class="fab fa-facebook-f"></i>
          </a>
          <a href="#" class="social-link">
            <i class="fab fa-twitter"></i>
          </a>
        </div>

        <!-- Copyright -->
        <div class="footer-copyright">
          <p class="copyright-text" data-i18n="footer.copyright">&copy; 2025 OnlyRent. All rights reserved.</p>
          <div class="copyright-line"></div>
        </div>
      </div>
    </div>
  </footer>

  <script>
    // Translations
    const translations = {
      en: {
        'app.title': 'OnlyRent',
        'nav.inventory': 'Inventory',
        'nav.how_it_works': 'How It Works',
        'nav.contact': 'Contact',
        'hero.title': 'Premium Equipment Rentals',
        'hero.subtitle': 'Professional gear for your creative projects at affordable rates',
        'hero.button': 'Browse Inventory',
        'section.how_it_works': 'How It Works',
        'section.how_it_works_desc': 'Renting equipment with OnlyRent is simple and hassle-free. Follow these easy steps to get the gear you need for your next project.',
        'step.1.title': 'Browse & Select',
        'step.1.desc': 'Explore our extensive inventory of professional equipment and select the items you need for your project.',
        'step.2.title': 'Choose Rental Period',
        'step.2.desc': 'Select your rental dates and duration. Prices are calculated based on daily rates.',
        'step.3.title': 'Make Payment',
        'step.3.desc': 'Pay securely online. We accept various payment methods including credit cards and e-wallets.',
        'step.4.title': 'Pickup or Delivery',
        'step.4.desc': 'Collect your equipment at our location or opt for delivery (additional fees may apply).',
        'step.5.title': 'Return & Get Deposit Back',
        'step.5.desc': 'Return the equipment on time and in good condition to receive your security deposit refund.',
        'section.inventory': 'Our Equipment',
        'section.contact': 'Contact Us',
        'contact.desc': 'Have questions or need assistance? Reach out to us through our social media channels. We\'re here to help!',
        'contact.instagram': 'Instagram',
        'contact.instagram_desc': 'Follow us for updates',
        'contact.instagram_handle': '@onlyrent',
        'contact.whatsapp': 'WhatsApp',
        'contact.whatsapp_desc': 'Fast response for inquiries',
        'contact.whatsapp_number': '+62 812-3456-7890',
        'footer.tagline': 'Premium equipment rental service',
        'footer.copyright': '© 2025 OnlyRent. All rights reserved.'
      },
      id: {
        'app.title': 'OnlyRent',
        'nav.inventory': 'Inventaris',
        'nav.how_it_works': 'Cara Kerja',
        'nav.contact': 'Kontak',
        'hero.title': 'Rental Peralatan Premium',
        'hero.subtitle': 'Peralatan profesional untuk proyek kreatif Anda dengan harga terjangkau',
        'hero.button': 'Lihat Inventaris',
        'section.how_it_works': 'Cara Kerja',
        'section.how_it_works_desc': 'Menyewa peralatan dengan OnlyRent mudah dan tanpa ribet. Ikuti langkah-langkah mudah ini untuk mendapatkan peralatan yang Anda butuhkan.',
        'step.1.title': 'Jelajahi & Pilih',
        'step.1.desc': 'Jelajahi inventaris lengkap peralatan profesional kami dan pilih item yang Anda butuhkan.',
        'step.2.title': 'Pilih Periode Rental',
        'step.2.desc': 'Pilih tanggal dan durasi rental. Harga dihitung berdasarkan tarif harian.',
        'step.3.title': 'Lakukan Pembayaran',
        'step.3.desc': 'Bayar dengan aman secara online. Kami menerima berbagai metode pembayaran termasuk kartu kredit dan e-wallet.',
        'step.4.title': 'Ambil atau Antar',
        'step.4.desc': 'Ambil peralatan di lokasi kami atau pilih layanan antar (biaya tambahan mungkin berlaku).',
        'step.5.title': 'Kembalikan & Dapatkan Deposit',
        'step.5.desc': 'Kembalikan peralatan tepat waktu dan dalam kondisi baik untuk menerima pengembalian deposit.',
        'section.inventory': 'Peralatan Kami',
        'section.contact': 'Hubungi Kami',
        'contact.desc': 'Punya pertanyaan atau butuh bantuan? Hubungi kami melalui media sosial. Kami siap membantu!',
        'contact.instagram': 'Instagram',
        'contact.instagram_desc': 'Ikuti kami untuk update',
        'contact.instagram_handle': '@onlyrent',
        'contact.whatsapp': 'WhatsApp',
        'contact.whatsapp_desc': 'Respon cepat untuk pertanyaan',
        'contact.whatsapp_number': '+62 812-3456-7890',
        'footer.tagline': 'Layanan rental peralatan premium',
        'footer.copyright': '© 2025 OnlyRent. Semua hak dilindungi.'
      }
    };

    // Theme toggle
    const themeToggle = document.getElementById('theme-toggle');
    const themeIcon = themeToggle.querySelector('i');
    
    themeToggle.addEventListener('click', () => {
      const currentTheme = document.documentElement.getAttribute('data-theme') || 'light';
      const newTheme = currentTheme === 'light' ? 'dark' : 'light';
      
      document.documentElement.setAttribute('data-theme', newTheme);
      themeIcon.className = newTheme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
    });

    // Language switcher
    const languageLinks = document.querySelectorAll('#language-dropdown a');
    let currentLang = 'en';

    languageLinks.forEach(link => {
      link.addEventListener('click', (e) => {
        e.preventDefault();
        const lang = e.target.getAttribute('data-lang');
        if (translations[lang]) {
          currentLang = lang;
          updateLanguage(lang);
        }
      });
    });

    function updateLanguage(lang) {
      const elements = document.querySelectorAll('[data-i18n]');
      elements.forEach(element => {
        const key = element.getAttribute('data-i18n');
        if (translations[lang] && translations[lang][key]) {
          element.textContent = translations[lang][key];
        }
      });
    }
  </script>
</body>
</html>