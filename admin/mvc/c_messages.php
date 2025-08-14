<?php
// mvc/c_messages.php

class C_Messages extends C_Base {
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
        $this->mMessages = new M_Messages();
        $this->mUsers = new M_Users();
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
        
        if (empty($body)) exit();

        $messageData = [
            'sender_id' => $this->currentUser['id'],
            'body' => $body
        ];
        $channel = '';

        if ($chatType === 'group') {
            if (!$this->mMessages->isUserInGroup($this->currentUser['id'], $chatId)) {
                http_response_code(403); exit();
            }
            $messageData['group_id'] = $chatId;
            $channel = 'private-group-' . $chatId;

        } elseif ($chatType === 'user') {
            $messageData['recipient_id'] = $chatId;
            // Створюємо послідовну назву каналу для пари користувачів
            $user_ids = [$this->currentUser['id'], $chatId];
            sort($user_ids);
            $channel = 'private-chat-' . implode('-', $user_ids);
        }

        $newMessage = $this->mMessages->createMessage($messageData);

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
        $conversationId = (int)($_POST['conversation_id'] ?? 0);
        if ($this->mMessages->isUserInConversation($this->currentUser['id'], $conversationId)) {
            $this->mMessages->markConversationAsRead($this->currentUser['id'], $conversationId);
            echo json_encode(['success' => true]);
        } else {
            http_response_code(403);
        }
        exit();
    }
}