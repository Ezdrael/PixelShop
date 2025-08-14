<?php
// ===================================================================
// Файл: index.php 🕰️
// Розміщення: / (коренева папка сайту)
// Призначення: Перевірка авторизації та завантаження застосунку.
// ===================================================================

// 1. ЗАПУСК СЕСІЇ ТА ВИЗНАЧЕННЯ КОНСТАНТ
session_start();
define('ROOT', __DIR__);


// 2. ПІДКЛЮЧЕННЯ ОСНОВНИХ ФАЙЛІВ СИСТЕМИs
require_once ROOT . '/vendor/autoload.php'; 
require_once ROOT . '/config.php';
require_once ROOT . '/core/DB.php';
require_once ROOT . '/mvc/m_users.php';

// 3. ПЕРЕВІРКА АВТОРИЗАЦІЇ
$isAuthorized = false;
if (isset($_SESSION['user_id']) && isset($_SESSION['user_token'])) {
    $mUsers = new M_Users();
    if ($mUsers->checkAuth($_SESSION['user_id'], $_SESSION['user_token'])) {
        $isAuthorized = true;
    }
}

// 4. ЗАВАНТАЖЕННЯ ВІДПОВІДНОЇ СТОРІНКУ
if ($isAuthorized) {
    // Якщо користувач авторизований, завантажуємо основний застосунок
    require_once ROOT . '/app.php';
} else {
    // Якщо ні - показуємо сторінку входу
    require_once ROOT . '/authorisation.php';
}
