<?php
// app/Mvc/Models/Notes.php
namespace App\Mvc\Models;

use App\Core\DB;
use PDO;

class Notes 
{
    private $db;

    public function __construct() 
    {
        $this->db = DB::getInstance();
    }

    /**
     * Отримує всі нотатки для конкретного користувача.
     */
    public function getByUserId(int $userId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM user_notes WHERE user_id = ? ORDER BY updated_at DESC");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Створює нову нотатку.
     */
    public function create(int $userId, string $content): ?array
    {
        $stmt = $this->db->prepare("INSERT INTO user_notes (user_id, content) VALUES (?, ?)");
        if ($stmt->execute([$userId, $content])) {
            $lastId = $this->db->lastInsertId();
            $newNoteStmt = $this->db->prepare("SELECT * FROM user_notes WHERE id = ?");
            $newNoteStmt->execute([$lastId]);
            return $newNoteStmt->fetch(PDO::FETCH_ASSOC);
        }
        return null;
    }

    /**
     * Оновлює існуючу нотатку.
     */
    public function update(int $noteId, int $userId, string $content): ?array
    {
        $stmt = $this->db->prepare("UPDATE user_notes SET content = ? WHERE id = ? AND user_id = ?");
        if ($stmt->execute([$content, $noteId, $userId])) {
            $updatedNoteStmt = $this->db->prepare("SELECT * FROM user_notes WHERE id = ?");
            $updatedNoteStmt->execute([$noteId]);
            return $updatedNoteStmt->fetch(PDO::FETCH_ASSOC);
        }
        return null;
    }

    /**
     * Видаляє нотатку.
     */
    public function delete(int $noteId, int $userId): bool
    {
        $stmt = $this->db->prepare("DELETE FROM user_notes WHERE id = ? AND user_id = ?");
        return $stmt->execute([$noteId, $userId]);
    }
}