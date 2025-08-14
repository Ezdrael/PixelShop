<?php
// ===================================================================
// Файл: logout.php 🕰️
// Розміщення: / (коренева папка сайту)
// Призначення: Обробка виходу користувача з системи.
// ===================================================================

session_start();
define('ROOT', __DIR__);

// Підключаємо конфігурацію, щоб отримати BASE_URL
require_once ROOT . '/config.php';

// Перевіряємо, чи є користувач в сесії
if (isset($_SESSION['user_id'])) {
    require_once ROOT . '/core/DB.php';
    require_once ROOT . '/mvc/m_users.php';

    $mUsers = new M_Users();
    // Видаляємо токен з бази даних
    $mUsers->clearToken($_SESSION['user_id']);
}

// Знищуємо всі дані сесії
$_SESSION = array();
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
session_destroy();

// Перенаправляємо на головну сторінку проєкту
header('Location: ' . BASE_URL . '/');
exit();