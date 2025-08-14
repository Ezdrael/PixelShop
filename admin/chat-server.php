<?php
// chat-server.php
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use MyChat\Chat;

// Підключаємо автозавантажувач Composer
require __DIR__ . '/vendor/autoload.php';

define('ROOT', __DIR__);
require_once ROOT . '/config.php';
require_once ROOT . '/core/DB.php';
require_once ROOT . '/mvc/m_users.php'; // Потрібен для M_Messages
require_once ROOT . '/mvc/m_messages.php'; 
require_once ROOT . '/core/Chat.php'; // Підключаємо сам клас чату

// Створюємо наш WebSocket-сервер на порту 8080
$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new Chat()
        )
    ),
    8080
);

echo "WebSocket Server запущено на порту 8080\n";
$server->run();