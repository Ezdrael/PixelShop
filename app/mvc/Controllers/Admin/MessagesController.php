<?php
// mvc/c_messages.php
namespace App\Mvc\Controllers\Admin;

use App\Mvc\Models\Messages;
use App\Mvc\Models\Users;

class MessagesController extends BaseController  {
    protected $mMessages;
    protected $mUsers;

    public function __construct($params) {
        parent::__construct($params);
        // Перевіряємо базовий доступ одразу в конструкторі
        if (!$this->hasPermission('chat', 'v')) {
            // Якщо у користувача немає навіть права на перегляд,
            // він не може виконувати жодних дій з чатом.
            // Ми можемо або перенаправити його, або просто зупинити виконання.
            // Для AJAX-запитів краще просто зупинятися.
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                http_response_code(403); exit('Access Denied');
            } else {
                $this->showAccessDenied(); exit();
            }
        }
        $this->mMessages = new Messages();
        $this->mUsers = new Users();
    }

    public function indexAction() {
        if (!$this->hasPermission('chat', 'v')) {
            return $this->showAccessDenied();
        }
        $this->title = 'Повідомлення';
        $groups = $this->mMessages->getGroupsForUser($this->currentUser['id']);
        $allUsers = $this->mUsers->getAll();
        $users = array_filter($allUsers, fn($user) => $user['id'] != $this->currentUser['id']);
        $this->render('v_messages', ['groups' => $groups, 'users' => $users]);
    }

    public function fetchMessagesAction() {
        header('Content-Type: application/json');
        $chatType = $_POST['chat_type'] ?? '';
        $chatId = (int)($_POST['chat_id'] ?? 0);
        $messages = [];

        if ($chatType === 'group') {
            if ($this->mMessages->isUserInGroup($this->currentUser['id'], $chatId)) {
                $messages = $this->mMessages->getGroupMessages($chatId);
            } else {
                http_response_code(403); exit(json_encode(['error' => 'Access Denied']));
            }
        } elseif ($chatType === 'user') {
            $messages = $this->mMessages->getPrivateMessages($this->currentUser['id'], $chatId);
        }

        echo json_encode(['success' => true, 'messages' => $messages]);
        exit();
    }
    
    public function sendMessageAction() {
        header('Content-Type: application/json');
        $chatType = $_POST['chat_type'] ?? '';
        $chatId = (int)($_POST['chat_id'] ?? 0);
        $body = trim($_POST['body'] ?? '');

        if (empty($body)) {
            echo json_encode(['success' => false, 'message' => 'Message body cannot be empty.']);
            exit();
        }

        $messageData = [
            'sender_id' => $this->currentUser['id'],
            'body' => $body
        ];

        if ($chatType === 'group') {
            if (!$this->mMessages->isUserInGroup($this->currentUser['id'], $chatId)) {
                http_response_code(403); exit();
            }
            $messageData['group_id'] = $chatId;
        } elseif ($chatType === 'user') {
            $messageData['recipient_id'] = $chatId;
        }

        $newMessage = $this->mMessages->createMessage($messageData);

        if ($newMessage) {
            // --- НОВИЙ КОД: ВІДПРАВКА НА WEBSOCKET-СЕРВЕР ---
            try {
                $context = new \ZMQContext();
                $socket = $context->getSocket(\ZMQ::SOCKET_PUSH, 'my pusher');
                $socket->connect("tcp://127.0.0.1:5555");
                $socket->send(json_encode($newMessage));
            } catch (\ZMQException $e) {
                // Якщо сервер не запущено, нічого страшного, просто логуємо помилку
                error_log("ZMQ Error: " . $e->getMessage());
            }
            // --- КІНЕЦЬ НОВОГО КОДУ ---

            echo json_encode(['success' => true, 'message' => $newMessage]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to save the message.']);
        }
        exit();
    }

    public function getUnreadAction() {
        header('Content-Type: application/json');
        if (!isset($this->currentUser['id'])) {
            http_response_code(403); exit();
        }
        $counts = $this->mMessages->getUnreadCounts($this->currentUser['id']);
        echo json_encode(['success' => true, 'counts' => $counts]);
        exit();
    }

    public function markAsReadAction() {
        header('Content-Type: application/json');
        // Отримуємо дані, які надсилає фронтенд
        $chatType = $_POST['chat_type'] ?? null;
        $chatId = (int)($_POST['chat_id'] ?? 0);

        if ($chatType && $chatId > 0) {
            // (Потрібно буде додати логіку в M_Messages для позначення як прочитаного)
            // $this->mMessages->markAsRead($this->currentUser['id'], $chatType, $chatId);
            echo json_encode(['success' => true]);
        } else {
            http_response_code(400); // Bad Request
            echo json_encode(['success' => false]);
        }
        exit();
    }
    
    public function getConversationsAction()
    {
        header('Content-Type: application/json');
        if (!$this->hasPermission('chat', 'v')) {
            http_response_code(403);
            exit(json_encode(['success' => false, 'message' => 'Доступ заборонено']));
        }

        $groups = $this->mMessages->getGroupsForUser($this->currentUser['id']);
        $allUsers = $this->mUsers->getAll();
        $users = array_filter($allUsers, fn($user) => $user['id'] != $this->currentUser['id']);
        echo json_encode([
            'success' => true,
            'users' => array_values($users), // Переіндексовуємо масив
            'groups' => $groups
        ]);
        exit();
    }
    
    public function getChatSettingsAction()
    {
        header('Content-Type: application/json');
        if (!$this->hasPermission('chat', 'e')) {
            $this->sendJson(['success' => false, 'message' => 'Доступ заборонено'], 403);
        }
        $groups = $this->mMessages->getAllGroupsWithMembers($this->currentUser['id']);
        $users = $this->mUsers->getAll();
        
        $this->sendJson(['success' => true, 'groups' => $groups, 'users' => $users]);
    }

    public function createGroupAction()
    {
        header('Content-Type: application/json');
        if (!$this->hasPermission('chat', 'e')) {
            $this->sendJson(['success' => false, 'message' => 'Доступ заборонено'], 403);
        }
        $data = json_decode(file_get_contents('php://input'), true);
        $name = trim($data['name'] ?? '');
        $members = $data['members'] ?? [];

        if (empty($name) || empty($members)) {
            $this->sendJson(['success' => false, 'message' => 'Назва та учасники є обов\'язковими.'], 400);
        }

        $groupId = $this->mMessages->createGroup($name, $this->currentUser['id'], $members);
        if ($groupId) {
            $this->sendJson(['success' => true, 'message' => 'Групу створено.', 'groupId' => $groupId]);
        } else {
            $this->sendJson(['success' => false, 'message' => 'Помилка створення групи.'], 500);
        }
    }

    public function updateGroupAction()
    {
        header('Content-Type: application/json');
        if (!$this->hasPermission('chat', 'e')) {
            $this->sendJson(['success' => false, 'message' => 'Доступ заборонено'], 403);
        }
        $groupId = (int)($this->params['id'] ?? 0);
        $data = json_decode(file_get_contents('php://input'), true);
        $name = trim($data['name'] ?? '');
        $members = $data['members'] ?? [];
        
        if ($groupId <= 0 || empty($name) || empty($members)) {
             $this->sendJson(['success' => false, 'message' => 'Некоректні дані.'], 400);
        }
        
        if ($this->mMessages->updateGroup($groupId, $name, $members, $this->currentUser['id'])) {
            $this->sendJson(['success' => true, 'message' => 'Групу оновлено.']);
        } else {
            $this->sendJson(['success' => false, 'message' => 'Помилка оновлення групи.'], 500);
        }
    }

    public function deleteGroupAction()
    {
        header('Content-Type: application/json');
        if (!$this->hasPermission('chat', 'e')) {
            $this->sendJson(['success' => false, 'message' => 'Доступ заборонено'], 403);
        }
        $groupId = (int)($this->params['id'] ?? 0);
        if ($this->mMessages->deleteGroup($groupId, $this->currentUser['id'])) {
            $this->sendJson(['success' => true, 'message' => 'Групу видалено.']);
        } else {
            $this->sendJson(['success' => false, 'message' => 'Помилка видалення групи.'], 500);
        }
    }
}