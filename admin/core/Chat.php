<?php
// core/Chat.php
namespace MyChat;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use M_Messages;

class Chat implements MessageComponentInterface {
    protected $clients;
    protected $mMessages;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
        $this->mMessages = new M_Messages();
        echo "Chat component initialized...\n";
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
        echo "New connection! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $data = json_decode($msg, true);

        // Перевіряємо, чи це подія відправки повідомлення
        if (isset($data['type']) && $data['type'] === 'message' && isset($data['sender_id'])) {
            
            $messageData = [
                'sender_id'    => $data['sender_id'],
                'body'         => $data['body'],
                'group_id'     => ($data['chat_type'] === 'group') ? $data['chat_id'] : null,
                'recipient_id' => ($data['chat_type'] === 'user') ? $data['chat_id'] : null
            ];
            
            // Зберігаємо повідомлення в БД
            $savedMessage = $this->mMessages->createMessage($messageData);

            if ($savedMessage) {
                foreach ($this->clients as $client) {
                    $client->send(json_encode(['type' => 'newMessage', 'data' => $savedMessage]));
                }
                // Якщо повідомлення збережено, розсилаємо його всім підключеним клієнтам
                foreach ($this->clients as $client) {
                    // ВАЖЛИВО: В реальному житті тут має бути логіка,
                    // яка відправляє повідомлення лише учасникам конкретного чату.
                    // Зараз для простоти відправляємо всім.
                    $client->send(json_encode($savedMessage));
                }
            }
        }
    }

    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);
        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";
        $conn->close();
    }
}