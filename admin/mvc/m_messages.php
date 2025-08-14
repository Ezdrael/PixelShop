<?php
// mvc/m_messages.php

class M_Messages {
    private $db;

    public function __construct() {
        $this->db = DB::getInstance();
    }

    // --- РОБОТА З ГРУПАМИ ---
    public function getGroupsForUser(int $userId): array {
        $sql = "
            SELECT g.id, g.group_name as title
            FROM chat_groups g
            JOIN chat_group_members gm ON g.id = gm.group_id
            WHERE gm.user_id = ?
            ORDER BY g.group_name ASC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function isUserInGroup(int $userId, int $groupId): bool {
        $stmt = $this->db->prepare("SELECT 1 FROM chat_group_members WHERE user_id = ? AND group_id = ?");
        $stmt->execute([$userId, $groupId]);
        return $stmt->fetchColumn() !== false;
    }

    // --- РОБОТА З ПОВІДОМЛЕННЯМИ ---
    public function getPrivateMessages(int $userOneId, int $userTwoId, int $limit = 50): array {
        $sql = "
            SELECT m.*, u.name as sender_name
            FROM chat_messages m
            JOIN users u ON m.sender_id = u.id
            WHERE m.group_id IS NULL AND 
                  ((m.sender_id = ? AND m.recipient_id = ?) OR (m.sender_id = ? AND m.recipient_id = ?))
            ORDER BY m.created_at ASC
            LIMIT ?
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(1, $userOneId, PDO::PARAM_INT);
        $stmt->bindValue(2, $userTwoId, PDO::PARAM_INT);
        $stmt->bindValue(3, $userTwoId, PDO::PARAM_INT);
        $stmt->bindValue(4, $userOneId, PDO::PARAM_INT);
        $stmt->bindValue(5, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getGroupMessages(int $groupId, int $limit = 50): array {
        $sql = "
            SELECT m.*, u.name as sender_name
            FROM chat_messages m
            JOIN users u ON m.sender_id = u.id
            WHERE m.group_id = ?
            ORDER BY m.created_at ASC
            LIMIT ?
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(1, $groupId, PDO::PARAM_INT);
        $stmt->bindValue(2, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function createMessage(array $data): ?array {
        $sql = "
            INSERT INTO chat_messages (sender_id, recipient_id, group_id, body) 
            VALUES (?, ?, ?, ?)
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['sender_id'],
            $data['recipient_id'] ?? null,
            $data['group_id'] ?? null,
            $data['body']
        ]);
        $messageId = $this->db->lastInsertId();

        if (!$messageId) {
            return null; // Помилка вставки
        }

        // Повертаємо дані щойно створеного повідомлення
        $stmtGet = $this->db->prepare(
            "SELECT m.*, u.name as sender_name
             FROM chat_messages m JOIN users u ON m.sender_id = u.id WHERE m.id = ?"
        );
        $stmtGet->execute([$messageId]);
        return $stmtGet->fetch();
    }
}