<?php
// app/routes/web.php
use App\Core\Router;

$router = new Router();

// Маршрути публічного сайту
$router->add('', ['controller' => 'Home', 'action' => 'index']);
$router->add('product/(?P<id>\d+)', ['controller' => 'Product', 'action' => 'show']);
// ...

$requestUri = trim($_SERVER['REQUEST_URI'], '/');

// Запускаємо роутер з контекстом 'Public'
$router->dispatch($requestUri, 'Public');