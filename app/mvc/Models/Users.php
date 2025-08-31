<?php
// ===================================================================
// Файл: mvc/m_users.php 🕰️
// Розміщення: /mvc/m_users.php
// ===================================================================
namespace App\Mvc\Models;

use App\Core\DB;
use PDO;

class Users {
    private $db;

    public function __construct() {
        $this->db = DB::getInstance();
    }

    public function getAll() {
        $sql = "SELECT u.*, r.role_name FROM users u LEFT JOIN roles r ON u.role_id = r.id ORDER BY u.id ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function getById($id) {
        $sql = "SELECT u.*, r.role_name, 
                            r.perm_users, 
                            r.perm_categories, 
                            r.perm_goods, 
                            r.perm_roles, 
                            r.perm_warehouses, 
                            r.perm_arrivals, 
                            r.perm_chat, 
                            r.perm_transfers,
                            r.perm_albums,
                            r.perm_settings,
                            r.perm_currencies,
                            r.perm_writeoffs,
                            r.perm_notes,
                            r.perm_clipboard
            FROM users u 
            LEFT JOIN roles r ON u.role_id = r.id 
            WHERE u.id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function findByEmail($email) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }
    
    public function emailExists($email, $excludeId = 0) {
        $sql = "SELECT id FROM users WHERE email = ? AND id != ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$email, $excludeId]);
        return $stmt->fetch() !== false;
    }

    public function updateToken($id, $token) {
        $stmt = $this->db->prepare("UPDATE users SET token = ? WHERE id = ?");
        return $stmt->execute([$token, $id]);
    }

    public function checkAuth($id, $token) {
        $stmt = $this->db->prepare("SELECT id FROM users WHERE id = ? AND token = ?");
        $stmt->execute([$id, $token]);
        return $stmt->fetch() !== false;
    }
    
    public function clearToken($id) {
        $stmt = $this->db->prepare("UPDATE users SET token = NULL WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function deleteById($id) {
        $stmt = $this->db->prepare("DELETE FROM users WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function update($id, $data) {
        $fields = [];
        $params = [];

        // Динамічно формуємо список полів для оновлення
        foreach ($data as $key => $value) {
            // Дозволяємо оновлювати лише певні поля для безпеки
            if (in_array($key, ['name', 'email', 'role_id', 'password', 'avatar_url'])) {
                $fields[] = "`{$key}` = ?";

                if ($key === 'password') {
                    $params[] = password_hash($value, PASSWORD_DEFAULT);
                } else {
                    // Для avatar_url зберігаємо NULL, якщо рядок порожній
                    $params[] = ($key === 'avatar_url' && empty($value)) ? null : $value;
                }
            }
        }

        if (empty($fields)) {
            return false;
        }

        $params[] = (int)$id;

        $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = ?";

        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);
        } catch (\PDOException $e) {
            error_log("User update failed: " . $e->getMessage());
            return false;
        }
    }

    public function add($data) {
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
        $avatarUrl = !empty($data['avatar_url']) ? $data['avatar_url'] : null;

        $stmt = $this->db->prepare("INSERT INTO users (name, email, password, role_id, avatar_url) VALUES (?, ?, ?, ?, ?)");
        return $stmt->execute([$data['name'], $data['email'], $hashedPassword, $data['role_id'], $avatarUrl]);
    }

    /**
     * Очищує токен авторизації для користувача (використовується при виході).
     * @param int $userId ID користувача
     * @return bool
     */
    public function clearAuthToken(int $userId): bool
    {
        // Припускаємо, що колонка з токеном називається 'auth_token'
        $sql = "UPDATE users SET auth_token = NULL WHERE id = ?";
        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$userId]);
        } catch (\PDOException $e) {
            // Можна додати логування помилки
            return false;
        }
    }

}