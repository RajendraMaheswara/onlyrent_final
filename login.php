<?php
session_start();
require_once './config/connect_db.php';

// Redirect if already logged in
if (isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    include_once 'controllers/LoginController.php';
    $controller = new LoginController(getDBConnection());
    $error = $controller->login();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>OnlyRent | Login</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <link rel="stylesheet" href="./assets/css/login.css">
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

  <!-- Login Form -->
  <div class="login-container">
    <div class="login-card">
      <div class="login-logo">
        <i class="fas fa-camera-retro"></i>
        <h1 data-i18n="app.title">OnlyRent</h1>
      </div>

      <?php if (!empty($error)): ?>
        <div class="error-message"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form method="POST">
        <div class="form-group">
          <label class="form-label" data-i18n="login.username">Username</label>
          <input type="text" class="form-control" name="username" required>
        </div>

        <div class="form-group">
          <label class="form-label" data-i18n="login.password">Password</label>
          <input type="password" class="form-control" name="password" required>
        </div>

        <button type="submit" class="btn btn-primary" data-i18n="login.button">Login</button>
        
        <div style="margin-top: 1.5rem; text-align: center;">
          <p style="color: var(--text-light);" data-i18n="login.no_account">Don't have an account?</p>
          <a href="register.php" style="color: var(--primary); font-weight: 500;" data-i18n="login.register">Register here</a>
        </div>
      </form>
    </div>
  </div>

  <script>
const translations = {
      en: {
        "app.title": "OnlyRent",
        "login.username": "Username",
        "login.password": "Password",
        "login.button": "Login",
        "login.no_account": "Don't have an account?",
        "login.register": "Register here"
      },
      id: {
        "app.title": "OnlyRent",
        "login.username": "Nama Pengguna",
        "login.password": "Kata Sandi",
        "login.button": "Masuk",
        "login.no_account": "Tidak punya akun?",
        "login.register": "Daftar disini"
      },
      ja: {
        "app.title": "OnlyRent",
        "login.username": "ユーザー名",
        "login.password": "パスワード",
        "login.button": "ログイン",
        "login.no_account": "アカウントをお持ちでない場合",
        "login.register": "こちらから登録"
      },
      zh: {
        "app.title": "OnlyRent",
        "login.username": "用户名",
        "login.password": "密码",
        "login.button": "登录",
        "login.no_account": "没有账户？",
        "login.register": "在此注册"
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