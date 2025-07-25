<?php
// public/index.php

// Починаємо сесію, якщо вона ще не розпочата
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Підключаємо файл конфігурації бази даних
require_once __DIR__ . '/../app/config/database.php';

// Підключаємо контролери
require_once __DIR__ . '/../app/controllers/HomeController.php';
require_once __DIR__ . '/../app/controllers/ProductController.php';
require_once __DIR__ . '/../app/controllers/CartController.php';
require_once __DIR__ . '/../app/controllers/OrderController.php';
require_once __DIR__ . '/../app/controllers/AuthController.php'; // Додано AuthController


// Отримуємо запитуваний URL
$requestUri = $_SERVER['REQUEST_URI'];
$basePath = '/pixelshop/public'; // Базовий шлях вашого застосунку

// Видаляємо базовий шлях та параметри запиту (GET)
$route = str_replace($basePath, '', $requestUri);
$route = strtok($route, '?'); // Видаляємо все після '?'

// Визначаємо маршрутизацію
// Розбиваємо маршрут на частини
$segments = explode('/', trim($route, '/'));
$controllerName = ucfirst(array_shift($segments)); // Перший сегмент - контролер
$actionName = array_shift($segments); // Другий сегмент - дія
$param = array_shift($segments); // Третій сегмент - параметр (наприклад, ID товару)

// Якщо контролер не вказано, використовуємо HomeController
if (empty($controllerName)) {
    $controllerName = 'Home';
}
// Якщо дія не вказана, використовуємо 'index'
if (empty($actionName)) {
    $actionName = 'index';
}

$controllerClass = $controllerName . 'Controller';
$controllerFile = __DIR__ . '/../app/controllers/' . $controllerClass . '.php';


if (file_exists($controllerFile)) {
    // Створюємо екземпляр контролера
    $controller = new $controllerClass();

    // Перевіряємо, чи існує метод дії
    if (method_exists($controller, $actionName)) {
        // Викликаємо метод дії з параметром, якщо він є
        if ($param !== null) {
            $controller->$actionName($param);
        } else {
            $controller->$actionName();
        }
    } else {
        // Метод дії не знайдено
        header("HTTP/1.0 404 Not Found");
        echo "404 - Дія не знайдена.";
    }
} else {
    // Контролер не знайдено
    header("HTTP/1.0 404 Not Found");
    echo "404 - Контролер не знайдено.";
}
