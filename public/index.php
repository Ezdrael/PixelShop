<?php
// public/index.php
session_start();

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

    $isAuthorized = false;
    if (isset($_SESSION['user_id']) && isset($_SESSION['user_token'])) {
        $mUsers = new Users();
        if ($mUsers->checkAuth($_SESSION['user_id'], $_SESSION['user_token'])) {
            $isAuthorized = true;
        }
    }

    if ($isAuthorized) {
        require_once BASE_PATH . '/app/routes/admin.php';
    } else {
        require_once BASE_PATH . '/public/authorisation.php';
    }

} else {

    // --- ЦЕ ЗАПИТ ДО ПУБЛІЧНОГО САЙТУ ---
    define('BASE_URL', PROJECT_URL);
    require_once BASE_PATH . '/app/routes/web.php';
}