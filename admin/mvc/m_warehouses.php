<?php
// ===================================================================
// Файл: mvc/m_warehouses.php 🆕
// ===================================================================

class M_Warehouses {
    private $db;

    public function __construct() {
        $this->db = DB::getInstance();
    }

    public function getAll() {
        $stmt = $this->db->query("SELECT * FROM warehouses ORDER BY name ASC");
        return $stmt->fetchAll();
    }

    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM warehouses WHERE id = ?");
        $stmt->execute([(int)$id]);
        return $stmt->fetch();
    }

    public function add($data) {
        $stmt = $this->db->prepare("INSERT INTO warehouses (name, address) VALUES (?, ?)");
        return $stmt->execute([$data['name'], $data['address']]);
    }

    public function update($id, $data) {
        $stmt = $this->db->prepare("UPDATE warehouses SET name = ?, address = ? WHERE id = ?");
        return $stmt->execute([$data['name'], $data['address'], (int)$id]);
    }

    public function deleteById($id) {
        $stmt = $this->db->prepare("DELETE FROM warehouses WHERE id = ?");
        return $stmt->execute([(int)$id]);
    }

    /**
     * Отримує список товарів та їхній актуальний залишок на конкретному складі
     * з таблиці product_stock.
     *
     * @param int $warehouseId ID складу
     * @return array
     */
    public function getProductsByWarehouseId(int $warehouseId): array
    {
        $sql = "
            SELECT
                g.id as good_id,
                g.name as good_name,
                g.is_active,
                ps.quantity as stock_level
            FROM
                product_stock ps
            JOIN
                goods g ON ps.good_id = g.id
            WHERE
                ps.warehouse_id = ? AND ps.quantity != 0
            ORDER BY
                g.name;
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$warehouseId]);
        return $stmt->fetchAll();
    }
}