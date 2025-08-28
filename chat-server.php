<?php
// chat-server.php

define('BASE_PATH', __DIR__);

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use React\EventLoop\Factory as LoopFactory;
use React\Socket\Server as ReactServer;
use App\Core\TokenManager;

require dirname(__FILE__) . '/vendor/autoload.php';
require dirname(__FILE__) . '/config.php';

use App\Mvc\Models\Messages;

class Chat implements MessageComponentInterface {
    protected $clients;
    protected $users; // [userId => connection, ...]
    protected $messagesModel;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
        $this->users = [];
        $this->messagesModel = new Messages();
        echo "Chat server started successfully (WebSocket-only mode).\n";
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
        echo "New connection! ({$conn->resourceId})\n";
    }

    /**
     * ГОЛОВНА ЛОГІКА СЕРВЕРА
     * Обробляє повідомлення, що приходять від клієнтів.
     */
    public function onMessage(ConnectionInterface $from, $msg) {
        $data = json_decode($msg);

        // 1. Обробка авторизації за допомогою одноразового токена
        if (isset($data->type) && $data->type === 'auth' && isset($data->token)) {
            // Перевіряємо токен через наш менеджер
            $userId = TokenManager::validate($data->token);

            if ($userId !== null) {
                $this->users[$userId] = $from;
                echo "User {$userId} authenticated successfully for connection {$from->resourceId}.\n";
            } else {
                echo "Failed authentication attempt with invalid token from connection {$from->resourceId}.\n";
                $from->close(); // Закриваємо з'єднання при невдалій автентифікації
            }
            return;
        }

        // 2. Обробка нового повідомлення (цей код залишається без змін)
        if (isset($data->type) && $data->type === 'message') {
            echo "Received message from user {$data->sender_id}.\n";

            $messageData = [
                'sender_id' => $data->sender_id,
                'body' => $data->body,
                'group_id' => ($data->chat_type === 'group') ? $data->chat_id : null,
                'recipient_id' => ($data->chat_type === 'user') ? $data->chat_id : null,
            ];

            $newMessage = $this->messagesModel->createMessage($messageData);
            if (!$newMessage) {
                echo "Failed to save message to DB.\n";
                return;
            }

            $jsonMessage = json_encode($newMessage);

            if ($newMessage['group_id']) {
                $members = $this->messagesModel->getGroupMembers($newMessage['group_id']);
                foreach ($members as $member) {
                    if (isset($this->users[$member['user_id']])) {
                        $this->users[$member['user_id']]->send($jsonMessage);
                    }
                }
            } else {
                if (isset($this->users[$newMessage['recipient_id']])) {
                    $this->users[$newMessage['recipient_id']]->send($jsonMessage);
                }
                if (isset($this->users[$newMessage['sender_id']])) {
                     $this->users[$newMessage['sender_id']]->send($jsonMessage);
                }
            }
        }
    }

    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);
        foreach ($this->users as $userId => $connection) {
            if ($connection === $conn) {
                unset($this->users[$userId]);
                break;
            }
        }
        echo "Connection {$conn->resourceId} has disconnected.\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";
        $conn->close();
    }
}

// Запускаємо сервер
$loop = LoopFactory::create();
$socket = new ReactServer('0.0.0.0:8080', $loop);
$server = new IoServer(
    new HttpServer(new WsServer(new Chat())),
    $socket
);

$loop->run();