<?php
// public/logout.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --- 1. ВИЗНАЧАЄМО ПРАВИЛЬНІ ШЛЯХИ ---
define('BASE_PATH', dirname(__DIR__));
define('ROOT', BASE_PATH . '/app'); 

// --- 2. ПІДКЛЮЧАЄМО АВТОЗАВАНТАЖУВАЧ ТА КОНФІГУРАЦІЮ ---
require_once BASE_PATH . '/vendor/autoload.php';
require_once BASE_PATH . '/config.php';

// --- 3. ІМПОРТУЄМО НЕОБХІДНИЙ КЛАС ---
// ✅ ВИПРАВЛЕНО: Використовуємо новий, правильний шлях до моделі
use App\Mvc\Models\Users;

// Перевіряємо, чи є користувач в сесії
if (isset($_SESSION['user_id'])) {
    // ✅ ВИПРАВЛЕНО: Використовуємо нову назву класу 'Users'
    $mUsers = new Users();
    // Видаляємо токен з бази даних
    $mUsers->clearToken($_SESSION['user_id']);
}

// Знищуємо всі дані сесії
$_SESSION = [];
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
session_destroy();

// --- 4. ПЕРЕНАПРАВЛЯЄМО НА ГОЛОВНУ СТОРІНКУ АДМІН-ПАНЕЛІ ---
// ✅ ВИПРАВЛЕНО: Перенаправлення тепер веде на сторінку входу адмінки
header('Location: ' . PROJECT_URL . '/admin/');
exit();