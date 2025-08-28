<?php
// ===================================================================
// Файл: mvc/m_roles.php 🕰️
// Розміщення: /mvc/m_roles.php
// ===================================================================
namespace App\Mvc\Models;

use App\Core\DB;
use PDO;

class Roles {
    private $db;

    public function __construct() {
        $this->db = DB::getInstance();
    }

    public function getAll() {
        $sql = "
            SELECT r.*, COUNT(u.id) as user_count
            FROM roles r
            LEFT JOIN users u ON r.id = u.role_id
            GROUP BY r.id
            ORDER BY r.id ASC
        ";
        return $this->db->query($sql)->fetchAll();
    }

    public function getUsersByRoleId(int $roleId): array {
        $stmt = $this->db->prepare("SELECT id, name, email FROM users WHERE role_id = ? ORDER BY name ASC");
        $stmt->execute([$roleId]);
        return $stmt->fetchAll();
    }

    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM roles WHERE id = ?");
        $stmt->execute([(int)$id]);
        return $stmt->fetch();
    }

    public function add($data) {
        $sql = "INSERT INTO roles ( role_name, 
                                    perm_users, 
                                    perm_categories, 
                                    perm_goods, 
                                    perm_roles, 
                                    perm_warehouses, 
                                    perm_arrivals, 
                                    perm_chat, 
                                    perm_transfers, 
                                    perm_albums, 
                                    perm_currencies,
                                    perm_settings,
                                    perm_notes,
                                    perm_clipboard) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['role_name'],
            $data['perm_users'],
            $data['perm_categories'],
            $data['perm_goods'],
            $data['perm_roles'],
            $data['perm_warehouses'],
            $data['perm_arrivals'],
            $data['perm_chat'],
            $data['perm_transfers'],
            $data['perm_albums'],
            $data['perm_currencies'],
            $data['perm_settings'],
            $data['perm_notes'],
            $data['perm_clipboard'],
        ]);
    }

    public function update($id, $data) {
        $sql = "UPDATE roles SET role_name = ?, 
                                perm_users = ?, 
                                perm_categories = ?, 
                                perm_goods = ?, 
                                perm_roles = ?, 
                                perm_warehouses = ?, 
                                perm_arrivals = ?, 
                                perm_chat = ?, 
                                perm_transfers = ?, 
                                perm_albums = ?,
                                perm_currencies = ?,
                                perm_writeoffs = ?,
                                perm_settings = ?,
                                perm_notes = ?,
                                perm_clipboard = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['role_name'],
            $data['perm_users'],
            $data['perm_categories'],
            $data['perm_goods'],
            $data['perm_roles'],
            $data['perm_warehouses'],
            $data['perm_arrivals'],
            $data['perm_chat'],
            $data['perm_transfers'], 
            $data['perm_albums'],
            $data['perm_currencies'],
            $data['perm_writeoffs'],
            $data['perm_settings'],
            $data['perm_notes'],
            $data['perm_clipboard'],
            (int)$id
        ]);
    }

    public function deleteById($id) {
        // Заборона видалення першої ролі (зазвичай це супер-адміністратор)
        if ((int)$id === 1) {
            return false;
        }
        $stmt = $this->db->prepare("DELETE FROM roles WHERE id = ?");
        return $stmt->execute([(int)$id]);
    }
}