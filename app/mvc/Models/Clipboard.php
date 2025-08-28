<?php
// app/Mvc/Models/Clipboard.php
namespace App\Mvc\Models;

use App\Core\DB;
use PDO;

class Clipboard 
{
    private $db;
    private const CLIPBOARD_LIMIT = 10;

    public function __construct() 
    {
        $this->db = DB::getInstance();
    }

    public function getByUserId(int $userId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM user_clipboard WHERE user_id = ? ORDER BY created_at DESC LIMIT " . self::CLIPBOARD_LIMIT);
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function add(int $userId, string $content): bool
    {
        try {
            $this->db->beginTransaction();

            // 1. Додаємо новий запис
            $stmt = $this->db->prepare("INSERT INTO user_clipboard (user_id, content) VALUES (?, ?)");
            $stmt->execute([$userId, $content]);
            
            // 2. Отримуємо ID всіх записів цього користувача, крім 10 найновіших
            $stmt_old = $this->db->prepare("
                SELECT id FROM user_clipboard 
                WHERE user_id = ? 
                ORDER BY created_at DESC 
                LIMIT 100 OFFSET " . self::CLIPBOARD_LIMIT
            );
            $stmt_old->execute([$userId]);
            $idsToDelete = $stmt_old->fetchAll(PDO::FETCH_COLUMN);

            // 3. Видаляємо старі записи, якщо вони є
            if (!empty($idsToDelete)) {
                $placeholders = implode(',', array_fill(0, count($idsToDelete), '?'));
                $stmt_delete = $this->db->prepare("DELETE FROM user_clipboard WHERE id IN ($placeholders)");
                $stmt_delete->execute($idsToDelete);
            }

            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            error_log("Clipboard add failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Повністю очищує буфер обміну для вказаного користувача.
     * @param int $userId ID користувача.
     * @return bool
     */
    public function clearByUserId(int $userId): bool
    {
        $stmt = $this->db->prepare("DELETE FROM user_clipboard WHERE user_id = ?");
        return $stmt->execute([$userId]);
    }
}