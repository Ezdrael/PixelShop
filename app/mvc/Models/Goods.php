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

    /**
     * Отримує опції для конкретного товару.
     */
    public function getOptionsForProduct(int $goodId): array
    {
        $sql = "SELECT og.name as group_name, ov.value 
                FROM product_options po
                JOIN option_values ov ON po.option_value_id = ov.id
                JOIN option_groups og ON ov.group_id = og.id
                WHERE po.good_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$goodId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Зберігає опції для товару.
     */
    public function saveOptionsForProduct(int $goodId, array $optionsData): void
    {
        // 1. Видаляємо старі опції для цього товару
        $stmtDelete = $this->db->prepare("DELETE FROM product_options WHERE good_id = ?");
        $stmtDelete->execute([$goodId]);

        if (empty($optionsData)) {
            return; // Якщо нових опцій немає, просто виходимо
        }

        // 2. Додаємо нові опції
        $sql = "INSERT INTO product_options (good_id, option_value_id) VALUES (?, ?)";
        $stmtInsert = $this->db->prepare($sql);

        foreach ($optionsData as $groupId => $valueIds) {
            if (is_array($valueIds)) {
                foreach ($valueIds as $valueId) {
                    $stmtInsert->execute([$goodId, (int)$valueId]);
                }
            }
        }
    }

    public function getAttributesForProduct(int $goodId): array
    {
        $sql = "SELECT pa.attribute_id, a.name, pa.value
                FROM product_attributes pa
                JOIN attributes a ON pa.attribute_id = a.id
                WHERE pa.good_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$goodId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function saveAttributesForProduct(int $goodId, array $attributesData): void
    {
        $this->db->prepare("DELETE FROM product_attributes WHERE good_id = ?")->execute([$goodId]);
        if (empty($attributesData)) return;

        $sql = "INSERT INTO product_attributes (good_id, attribute_id, value) VALUES (?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        foreach ($attributesData as $attr) {
            if (!empty($attr['id']) && !empty($attr['value'])) {
                $stmt->execute([$goodId, (int)$attr['id'], trim($attr['value'])]);
            }
        }
    }
}