<?php
session_start();
require_once './config/connect_db.php';

// Redirect if already logged in
if (isset($_SESSION['user'])) {
    header("Location: index.php");
    exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    include_once 'controllers/RegisterController.php';
    $controller = new RegisterController(getDBConnection());
    $error = $controller->register();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>OnlyRent | Register</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <link rel="stylesheet" href="./assets/css/register.css">
</head>
<body>
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

  <!-- Register Form -->
  <div class="register-container">
    <div class="register-card">
      <div class="register-logo">
        <i class="fas fa-camera-retro"></i>
        <h1 data-i18n="app.title">OnlyRent</h1>
      </div>

      <?php if (!empty($error)): ?>
        <div class="error-message"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form method="POST">
        <div class="form-group">
          <label class="form-label" data-i18n="register.username">Username</label>
          <input type="text" class="form-control" name="username" required>
        </div>

        <div class="form-group">
          <label class="form-label" data-i18n="register.email">Email</label>
          <input type="email" class="form-control" name="email" required>
        </div>

        <div class="form-group">
          <label class="form-label" data-i18n="register.password">Password</label>
          <input type="password" class="form-control" name="password" required>
        </div>

        <div class="form-group">
          <label class="form-label" data-i18n="register.confirm_password">Confirm Password</label>
          <input type="password" class="form-control" name="confirm_password" required>
        </div>

        <button type="submit" class="btn btn-primary" data-i18n="register.button">Register</button>
        
        <div style="margin-top: 1.5rem; text-align: center;">
          <p style="color: var(--text-light); margin-bottom: 0.5rem;" data-i18n="register.have_account">Already have an account?</p>
          <a href="login.php" class="btn btn-outline" style="width: 100%;" data-i18n="register.login">
            Login Here
          </a>
        </div>
      </form>
    </div>
  </div>


  <script>
    // Extended translations object
    const translations = {
      en: {
        "app.title": "OnlyRent",
        "register.username": "Username",
        "register.email": "Email",
        "register.password": "Password",
        "register.confirm_password": "Confirm Password",
        "register.button": "Register",
        "register.have_account": "Already have an account?",
        "register.login": "Login here"
      },
      id: {
        "app.title": "OnlyRent",
        "register.username": "Nama Pengguna",
        "register.email": "Email",
        "register.password": "Kata Sandi",
        "register.confirm_password": "Konfirmasi Kat  a Sandi",
        "register.button": "Daftar",
        "register.have_account": "Sudah punya akun?",
        "register.login": "Masuk disini"
      },
      ja: {
        "app.title": "OnlyRent",
        "register.username": "ユーザー名",
        "register.email": "メールアドレス",
        "register.password": "パスワード",
        "register.confirm_password": "パスワード確認",
        "register.button": "登録",
        "register.have_account": "すでにアカウントをお持ちの場合",
        "register.login": "こちらからログイン"
      },
      zh: {
        "app.title": "OnlyRent",
        "register.username": "用户名",
        "register.email": "电子邮箱",
        "register.password": "密码",
        "register.confirm_password": "确认密码",
        "register.button": "注册",
        "register.have_account": "已有账户？",
        "register.login": "在此登录"
      }
    };

    // Theme Toggle (same as index.php)
    const themeToggle = document.getElementById("theme-toggle");
    const currentTheme = localStorage.getItem("theme") || "light";

    // Apply saved theme
    document.documentElement.setAttribute("data-theme", currentTheme);
    updateThemeIcon(currentTheme);

    themeToggle.addEventListener("click", () => {
      const currentTheme = document.documentElement.getAttribute("data-theme");
      const newTheme = currentTheme === "dark" ? "light" : "dark";

      document.documentElement.setAttribute("data-theme", newTheme);
      localStorage.setItem("theme", newTheme);
      updateThemeIcon(newTheme);
    });

    function updateThemeIcon(theme) {
      const icon = themeToggle.querySelector("i");
      icon.className = theme === "dark" ? "fas fa-sun" : "fas fa-moon";
      themeToggle.title = theme === "dark" ? "Switch to Light Mode" : "Switch to Dark Mode";
    }

    // Language Switcher (same as index.php)
    const languageDropdown = document.getElementById("language-dropdown");
    const currentLanguage = localStorage.getItem("language") || "en";

    // Apply saved language
    changeLanguage(currentLanguage);

    languageDropdown.querySelectorAll("a").forEach((link) => {
      link.addEventListener("click", (e) => {
        e.preventDefault();
        const lang = e.target.getAttribute("data-lang");
        changeLanguage(lang);
        localStorage.setItem("language", lang);
      });
    });

    function changeLanguage(lang) {
      const elements = document.querySelectorAll("[data-i18n]");
      elements.forEach((el) => {
        const key = el.getAttribute("data-i18n");
        if (translations[lang] && translations[lang][key]) {
          el.textContent = translations[lang][key];
        } else {
          el.textContent = translations["en"][key]; // Fallback to English
        }
      });
    }
  </script>
</body>
</html>