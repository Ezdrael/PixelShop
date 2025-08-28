<?php
// chat-server.php

// Встановлюємо правильні шляхи та завантажуємо класи
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use React\EventLoop\Factory as LoopFactory;
use React\ZMQ\Context as ZmqContext;
use React\Socket\Server as ReactServer;

require dirname(__FILE__) . '/vendor/autoload.php';
require dirname(__FILE__) . '/config.php';

// Імпортуємо нашу модель для роботи з повідомленнями
use App\Mvc\Models\Messages;

class Chat implements MessageComponentInterface {
    protected $clients;
    protected $users; // Масив для зв'язку ID користувача з його з'єднанням
    protected $messagesModel;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
        $this->users = [];
        // Створюємо екземпляр моделі для роботи з групами
        $this->messagesModel = new Messages();
        echo "Chat server started...\n";
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
        echo "New connection! ({$conn->resourceId})\n";
    }

    // Цей метод обробляє повідомлення, що приходять напряму від клієнта (лише для авторизації)
    public function onMessage(ConnectionInterface $from, $msg) {
        $data = json_decode($msg);
        if (isset($data->type) && $data->type === 'auth' && isset($data->user_id)) {
            // Прив'язуємо ID користувача до конкретного з'єднання
            $this->users[$data->user_id] = $from;
            echo "User {$data->user_id} authenticated.\n";
        }
    }

    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);
        // Видаляємо користувача зі списку при відключенні
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

    /**
     * ЦЕ ГОЛОВНИЙ МЕТОД! Він отримує нове повідомлення від контролера через ZMQ
     * і розсилає його потрібним користувачам.
     */
    public function broadcastMessage($jsonMessage) {
        $data = json_decode($jsonMessage, true);
        echo "Broadcasting message to recipients...\n";

        // Якщо це повідомлення для групи
        if (isset($data['group_id']) && $data['group_id']) {
            // Отримуємо всіх учасників групи (потрібно додати метод в модель)
            $members = $this->messagesModel->getGroupMembers($data['group_id']);
            foreach ($members as $member) {
                $userId = $member['user_id'];
                // Якщо користувач онлайн, відправляємо йому повідомлення
                if (isset($this->users[$userId])) {
                    $this->users[$userId]->send($jsonMessage);
                }
            }
        }
        // Якщо це приватне повідомлення
        elseif (isset($data['recipient_id'])) {
            $recipientId = $data['recipient_id'];
            if (isset($this->users[$recipientId])) {
                $this->users[$recipientId]->send($jsonMessage);
            }
        }
    }
}

// Створюємо головний цикл подій
$loop = LoopFactory::create();
$chat = new Chat();

// Налаштовуємо ZMQ-слухач, який чекає на повідомлення від контролера
$context = new ZmqContext($loop);
$pull = $context->getSocket(\ZMQ::SOCKET_PULL);
$pull->bind('tcp://127.0.0.1:5555'); // Слухаємо на порту 5555
$pull->on('message', [$chat, 'broadcastMessage']);

// Налаштовуємо Веб-сокет сервер, який чекає на підключення клієнтів
$socket = new ReactServer('0.0.0.0:8080', $loop);
$server = new IoServer(new HttpServer(new WsServer($chat)), $socket);

// Запускаємо сервер
$loop->run();