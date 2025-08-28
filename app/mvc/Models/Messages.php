<?php
// mvc/m_messages.php
namespace App\Mvc\Models;

use App\Core\DB;
use PDO;

class Messages {
    private $db;

    public function __construct() {
        $this->db = DB::getInstance();
    }

    // --- РОБОТА З ГРУПАМИ ---
    public function getGroupsForUser(int $userId): array {
        $sql = "
            SELECT g.id, g.group_name
            FROM chat_groups g
            JOIN chat_group_members gm ON g.id = gm.group_id
            WHERE gm.user_id = ? ORDER BY g.group_name ASC
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
    
    public function getAllGroupsWithMembers(int $creatorId): array {
        $stmt = $this->db->prepare("SELECT * FROM chat_groups WHERE creator_id = ?");
        $stmt->execute([$creatorId]);
        $groups = $stmt->fetchAll();

        $membersStmt = $this->db->prepare("SELECT user_id FROM chat_group_members WHERE group_id = ?");
        foreach ($groups as $key => $group) {
            $membersStmt->execute([$group['id']]);
            $groups[$key]['members'] = $membersStmt->fetchAll(PDO::FETCH_COLUMN);
        }
        return $groups;
    }
    
    public function createGroup(string $name, int $creatorId, array $memberIds): ?int {
        try {
            $this->db->beginTransaction();
            $stmt = $this->db->prepare("INSERT INTO chat_groups (group_name, creator_id) VALUES (?, ?)");
            $stmt->execute([$name, $creatorId]);
            $groupId = $this->db->lastInsertId();

            $memberStmt = $this->db->prepare("INSERT INTO chat_group_members (group_id, user_id) VALUES (?, ?)");
            // Додаємо творця групи
            $memberStmt->execute([$groupId, $creatorId]);
            // Додаємо інших учасників
            foreach ($memberIds as $userId) {
                if ($userId != $creatorId) { // Перевірка, щоб не додати двічі
                    $memberStmt->execute([$groupId, $userId]);
                }
            }
            $this->db->commit();
            return (int)$groupId;
        } catch (\Exception $e) {
            $this->db->rollBack();
            return null;
        }
    }
    
    public function updateGroup(int $groupId, string $name, array $memberIds, int $creatorId): bool {
        try {
            $this->db->beginTransaction();
            // Оновлюємо назву (тільки творець може це зробити)
            $this->db->prepare("UPDATE chat_groups SET group_name = ? WHERE id = ? AND creator_id = ?")
                     ->execute([$name, $groupId, $creatorId]);
            
            // Видаляємо старих учасників
            $this->db->prepare("DELETE FROM chat_group_members WHERE group_id = ?")->execute([$groupId]);
            
            // Додаємо новий список учасників
            $memberStmt = $this->db->prepare("INSERT INTO chat_group_members (group_id, user_id) VALUES (?, ?)");
            $memberIds[] = $creatorId; // Завжди додаємо творця
            foreach (array_unique($memberIds) as $userId) {
                $memberStmt->execute([$groupId, $userId]);
            }
            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    public function deleteGroup(int $groupId, int $creatorId): bool {
        // Видаляти групу може тільки її творець
        $stmt = $this->db->prepare("DELETE FROM chat_groups WHERE id = ? AND creator_id = ?");
        return $stmt->execute([$groupId, $creatorId]);
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

    /**
     * Отримує ID всіх учасників групи.
     */
    public function getGroupMembers(int $groupId): array {
        $sql = "SELECT user_id FROM chat_group_members WHERE group_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$groupId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}