<?php
// ===================================================================
// app/Mvc/Models/Goods.php
// ===================================================================
namespace App\Mvc\Models;

use App\Core\DB;
use PDO;

class Goods {
    private $db;

    public function __construct() {
        $this->db = DB::getInstance();
    }

    public function getAll() {
        $sql = "SELECT g.*, c.name as category_name 
                FROM goods g 
                LEFT JOIN categories c ON g.category_id = c.id 
                ORDER BY g.id DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function getById($id) {
        $sql = "SELECT g.*, c.name as category_name 
                FROM goods g 
                LEFT JOIN categories c ON g.category_id = c.id 
                WHERE g.id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([(int)$id]);
        return $stmt->fetch();
    }

    public function add($data) {
        $sql = "INSERT INTO goods (name, description, keywords, price, length, width, height, weight, category_id, is_active) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['name'], $data['description'], $data['keywords'], $data['price'],
            $data['length'] ?: null, $data['width'] ?: null, $data['height'] ?: null, $data['weight'] ?: null,
            $data['category_id'], $data['is_active']
        ]);
    }

    public function update($id, $data) {
        $sql = "UPDATE goods SET 
                    name = ?, 
                    description = ?, 
                    keywords = ?, 
                    price = ?, 
                    length = ?, 
                    width = ?, 
                    height = ?, 
                    weight = ?, 
                    category_id = ?, 
                    is_active = ? 
                WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['name'], $data['description'], $data['keywords'], $data['price'],
            $data['length'] ?: null, $data['width'] ?: null, $data['height'] ?: null, $data['weight'] ?: null,
            $data['category_id'], $data['is_active'], (int)$id
        ]);
    }

    public function deleteById($id) {
        $stmt = $this->db->prepare("DELETE FROM goods WHERE id = ?");
        return $stmt->execute([(int)$id]);
    }

    public function getByCategoryIds(array $categoryIds) {
        if (empty($categoryIds)) {
            return []; // Повертаємо пустий масив, якщо немає ID
        }
        $placeholders = implode(',', array_fill(0, count($categoryIds), '?'));
        
        $sql = "SELECT * FROM goods WHERE category_id IN ($placeholders) ORDER BY name ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($categoryIds);
        return $stmt->fetchAll();
    }

    /**
     * Отримує поточний залишок товару на складах з таблиці product_stock.
     *
     * @param int $goodId ID товару
     * @return array Список складів та актуальний залишок товару на них
     */
    public function getCurrentStockByWarehouses(int $goodId): array
    {
        $sql = "
            SELECT 
                w.id as warehouse_id,
                w.name as warehouse_name,
                ps.quantity as stock_level
            FROM 
                product_stock ps
            JOIN 
                warehouses w ON ps.warehouse_id = w.id
            WHERE 
                ps.good_id = ? AND ps.quantity != 0
            ORDER BY 
                w.name
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$goodId]);
        return $stmt->fetchAll();
    }

    /**
     * Отримує історію всіх транзакцій для конкретного товару.
     *
    * @param int $goodId ID товару
    * @return array
    */
    public function getTransactionHistory(int $goodId): array
    {
        $sql = "
            SELECT 
                t.*,
                u.name as user_name,
                w.name as warehouse_name
            FROM 
                product_transactions t
            LEFT JOIN 
                users u ON t.user_id = u.id
            LEFT JOIN 
                warehouses w ON t.warehouse_id = w.id
            WHERE 
                t.good_id = ?
            ORDER BY 
                t.transaction_date DESC, t.id DESC
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$goodId]);
        return $stmt->fetchAll();
    }

    /**
     * Отримує останні додані активні товари.
     * @param int $limit - Кількість товарів для вибірки.
     * @return array
     */
    public function getNewest(int $limit = 8): array
    {
        $sql = "SELECT g.*, c.name as category_name 
                FROM goods g
                LEFT JOIN categories c ON g.category_id = c.id
                WHERE g.is_active = 1 
                ORDER BY g.id DESC 
                LIMIT ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }
    
    /**
     * Отримує список товарів та їх залишки на конкретному складі.
     * @param int $warehouseId - ID складу.
     * @return array
     */
    public function getGoodsInStockByWarehouse(int $warehouseId): array
    {
        $sql = "SELECT g.id, g.name, ps.quantity FROM goods g JOIN product_stock ps ON g.id = ps.good_id WHERE ps.warehouse_id = ? AND ps.quantity > 0 ORDER BY g.name ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$warehouseId]);
        return $stmt->fetchAll();
    }

    /**
     * Отримує список складів та залишків для конкретного товару.
     * @param int $goodId - ID товару.
     * @return array
     */
    public function getWarehousesWithStockForGood(int $goodId): array
    {
        $sql = "SELECT w.id, w.name, ps.quantity FROM warehouses w JOIN product_stock ps ON w.id = ps.warehouse_id WHERE ps.good_id = ? AND ps.quantity > 0 ORDER BY w.name ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$goodId]);
        return $stmt->fetchAll();
    }
}