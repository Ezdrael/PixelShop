<?php
// public/index.php
session_start();

// --- ЛОГІКА ІНСТАЛЯТОРА ---
// Якщо файлу конфігурації немає, але є папка інсталяції, запускаємо її.
if (!file_exists(dirname(__DIR__) . '/config.php') && is_dir(__DIR__ . '/install')) {
    header('Location: install/');
    exit();
}
// Якщо конфігурація є, але папка інсталяції все ще існує, виводимо попередження безпеки.
/*
if (file_exists(dirname(__DIR__) . '/config.php') && is_dir(__DIR__ . '/install')) {
    die('<div style="font-family: sans-serif; text-align: center; padding: 40px;">
            <h2 style="color: #dc3545;">ПОПЕРЕДЖЕННЯ БЕЗПЕКИ!</h2>
            <p>Будь ласка, видаліть папку <strong>/public/install</strong> з вашого сервера.</p>
         </div>');
}
*/

// --- ІНІЦІАЛІЗАЦІЯ ПРОЄКТУ ---
define('BASE_PATH', dirname(__DIR__));
define('ROOT', BASE_PATH . '/app'); 

require_once BASE_PATH . '/vendor/autoload.php';
require_once BASE_PATH . '/config.php';

use App\Mvc\Models\Users;

// Отримуємо повний URI запиту
$requestUri = trim($_SERVER['REQUEST_URI'], '/');

// --- ГОЛОВНА ЛОГІКА РОЗПОДІЛУ ---
// Перевіряємо, чи починається URI зі слова "admin"
if (preg_match('/^admin/', $requestUri)) {
    
    // --- ЦЕ ЗАПИТ ДО АДМІН-ПАНЕЛІ ---
    define('BASE_URL', PROJECT_URL . '/admin');

    // --- ОНОВЛЕНА ЛОГІКА АВТОРИЗАЦІЇ ---
    // Тепер ми перевіряємо лише наявність user_id в сесії,
    // оскільки вхід відбувається за паролем, а не за токеном.
    $isAuthorized = false;
    if (isset($_SESSION['user_id'])) {
        $isAuthorized = true;
    }

    if ($isAuthorized) {
        // Якщо користувач авторизований, завантажуємо маршрути адмін-панелі
        require_once BASE_PATH . '/app/routes/admin.php';
    } else {
        // Якщо ні - показуємо сторінку входу
        require_once BASE_PATH . '/public/authorisation.php';
    }

} else {

    // --- ЦЕ ЗАПИТ ДО ПУБЛІЧНОГО САЙТУ ---
    define('BASE_URL', PROJECT_URL);
    require_once BASE_PATH . '/app/routes/web.php';
}