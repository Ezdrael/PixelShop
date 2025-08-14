<?php
// mvc/m_transfers.php

class M_Transfers {
    private $db;

    public function __construct() {
        $this->db = DB::getInstance();
    }

    /**
     * Обробити нове переміщення: створює транзакції та оновлює залишки.
     */
    public function processNewTransfer(array $data): bool {
        try {
            $this->db->beginTransaction();

            $stmtStockCheck = $this->db->prepare("SELECT quantity FROM product_stock WHERE good_id = ? AND warehouse_id = ? FOR UPDATE");
            $stmtTransaction = $this->db->prepare("INSERT INTO product_transactions (transaction_date, good_id, warehouse_id, quantity, balance, transaction_type, document_type, user_id, related_transaction_id, comment) VALUES (?,?,?,?,?,?,?,?,?,?)");
            $stmtStockUpdate = $this->db->prepare("UPDATE product_stock SET quantity = ? WHERE good_id = ? AND warehouse_id = ?");

            foreach ($data['items'] as $item) {
                if (empty($item['good_id']) || empty($item['from_warehouse_id']) || empty($item['to_warehouse_id']) || !isset($item['quantity']) || $item['quantity'] <= 0) {
                    continue;
                }
                if ($item['from_warehouse_id'] == $item['to_warehouse_id']) {
                    throw new \Exception('Склад-відправник та склад-отримувач не можуть бути однаковими.');
                }

                $goodId = (int)$item['good_id'];
                $fromWarehouseId = (int)$item['from_warehouse_id'];
                $toWarehouseId = (int)$item['to_warehouse_id'];
                $quantity = (float)$item['quantity'];

                // --- 1. СПИСАННЯ ЗІ СКЛАДУ-ВІДПРАВНИКА ---
                $stmtStockCheck->execute([$goodId, $fromWarehouseId]);
                $currentStockFrom = (float)$stmtStockCheck->fetchColumn();
                if ($currentStockFrom < $quantity) {
                    $mGoods = new M_Goods();
                    $good = $mGoods->getById($goodId);
                    throw new \Exception("Недостатньо товару '" . ($good['name'] ?? 'ID:'.$goodId) . "' на складі-відправнику. В наявності: " . $currentStockFrom);
                }

                $newStockFrom = $currentStockFrom - $quantity;
                $stmtTransaction->execute([$data['order_date'], $goodId, $fromWarehouseId, -$quantity, $newStockFrom, 'transfer_out', 'transfer_form', $data['user_id'], null, $data['comment']]);
                $outTransactionId = $this->db->lastInsertId();
                $stmtStockUpdate->execute([$newStockFrom, $goodId, $fromWarehouseId]);

                // --- 2. НАДХОДЖЕННЯ НА СКЛАД-ОТРИМУВАЧ ---
                $stmtStockCheck->execute([$goodId, $toWarehouseId]);
                $currentStockTo = (float)$stmtStockCheck->fetchColumn();
                if ($currentStockTo === false) { $currentStockTo = 0.0; } 
                
                $newStockTo = $currentStockTo + $quantity;
                $stmtTransaction->execute([$data['order_date'], $goodId, $toWarehouseId, $quantity, $newStockTo, 'transfer_in', 'transfer_form', $data['user_id'], $outTransactionId, $data['comment']]);
                
                $stmtStockUpsert = $this->db->prepare(
                    "INSERT INTO product_stock (good_id, warehouse_id, quantity) VALUES (?, ?, ?)
                    ON DUPLICATE KEY UPDATE quantity = ?"
                );
                $stmtStockUpsert->execute([$goodId, $toWarehouseId, $newStockTo, $newStockTo]);
            }

            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            $_SESSION['flash_message'] = ['type' => 'error', 'text' => 'Помилка переміщення: ' . $e->getMessage()];
            return false;
        }
    }

    /**
     * Отримує список згрупованих операцій переміщення.
     */
    public function getTransfersList(): array {
        $sql = "
            SELECT 
                t_out.transaction_date,
                t_out.user_id,
                COALESCE(u.name, '[Видалений користувач]') AS user_name,
                t_out.comment,
                GROUP_CONCAT(t_out.id) as out_transaction_ids, -- Збираємо ID для посилання
                GROUP_CONCAT(
                    CONCAT(
                        '<span class=\"category-id-badge\">', COALESCE(g.name, '[?]'), '</span>',
                        '<span class=\"category-id-badge\"><i class=\"fas fa-box-open\"></i> ', FORMAT(ABS(t_out.quantity), 3), '</span>',
                        '<span class=\"category-id-badge\">', COALESCE(w_from.name, '[?]'), ' → ', COALESCE(w_to.name, '[?]'), '</span>'
                    ) SEPARATOR '<br>'
                ) AS positions_html
            FROM 
                product_transactions AS t_out
            LEFT JOIN 
                product_transactions AS t_in ON t_out.id = t_in.related_transaction_id AND t_in.transaction_type = 'transfer_in'
            LEFT JOIN users u ON t_out.user_id = u.id
            LEFT JOIN goods g ON t_out.good_id = g.id
            LEFT JOIN warehouses w_from ON t_out.warehouse_id = w_from.id
            LEFT JOIN warehouses w_to ON t_in.warehouse_id = w_to.id
            WHERE 
                t_out.transaction_type = 'transfer_out'
            GROUP BY 
                t_out.transaction_date, t_out.user_id, t_out.comment
            ORDER BY 
                t_out.transaction_date DESC
        ";
        return $this->db->query($sql)->fetchAll();
    }

    /**
     * Отримує деталі операції переміщення за ID транзакцій списання.
     */
    public function getTransferDetailsByOutIds(string $ids): ?array {
        // Захист від SQL-ін'єкцій
        $id_array = array_map('intval', explode(',', $ids));
        if (empty($id_array)) return null;
        $placeholders = implode(',', array_fill(0, count($id_array), '?'));

        $sql = "
            SELECT 
                t_out.*,
                COALESCE(u.name, '[Видалений користувач]') AS user_name,
                g.id as good_id, -- ДОДАНО ID ТОВАРУ
                g.is_active, -- ДОДАНО СТАТУС ТОВАРУ
                COALESCE(g.name, '[Видалений товар]') AS good_name,
                COALESCE(w_from.name, '[Видалений склад]') AS from_warehouse,
                COALESCE(w_to.name, '[Видалений склад]') AS to_warehouse
            FROM 
                product_transactions AS t_out
            LEFT JOIN product_transactions AS t_in ON t_out.id = t_in.related_transaction_id
            LEFT JOIN users u ON t_out.user_id = u.id
            LEFT JOIN goods g ON t_out.good_id = g.id
            LEFT JOIN warehouses w_from ON t_out.warehouse_id = w_from.id
            LEFT JOIN warehouses w_to ON t_in.warehouse_id = w_to.id
            WHERE t_out.id IN ($placeholders) AND t_out.transaction_type = 'transfer_out'
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($id_array);
        $items = $stmt->fetchAll();

        if (empty($items)) return null;

        // Повертаємо загальну інформацію та список позицій
        return [
            'details' => [
                'transaction_date' => $items[0]['transaction_date'],
                'user_name' => $items[0]['user_name'],
                'comment' => $items[0]['comment']
            ],
            'items' => $items
        ];
    }

    /**
     * Оновлює коментар для групи транзакцій переміщення.
     */
    public function updateTransferComment(string $ids, string $comment): bool {
        $id_array = array_map('intval', explode(',', $ids));
        if (empty($id_array)) return false;
        $placeholders = implode(',', array_fill(0, count($id_array), '?'));

        // Оновлюємо коментар і для 'transfer_out', і для пов'язаних 'transfer_in'
        $sql = "
            UPDATE product_transactions 
            SET comment = ? 
            WHERE id IN ($placeholders) OR related_transaction_id IN ($placeholders)
        ";
        
        $params = array_merge([$comment], $id_array, $id_array);
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }
    
    public function cancelTransfer(string $out_ids_str, int $userId): bool {
        $out_ids = array_map('intval', explode(',', $out_ids_str));
        if (empty($out_ids)) return false;

        try {
            $this->db->beginTransaction();

            $placeholders = implode(',', array_fill(0, count($out_ids), '?'));
            // Знаходимо всі оригінальні транзакції (і списання, і надходження)
            $stmtFind = $this->db->prepare(
                "SELECT * FROM product_transactions 
                WHERE id IN ($placeholders) OR related_transaction_id IN ($placeholders)"
            );
            $stmtFind->execute(array_merge($out_ids, $out_ids));
            $originalTransactions = $stmtFind->fetchAll();

            // Готуємо запити для створення нових (зворотних) транзакцій
            $stmtTransaction = $this->db->prepare("INSERT INTO product_transactions (transaction_date, good_id, warehouse_id, quantity, balance, transaction_type, document_type, user_id, comment) VALUES (?,?,?,?,?,?,?,?,?)");
            $stmtStockUpdate = $this->db->prepare("UPDATE product_stock SET quantity = ? WHERE good_id = ? AND warehouse_id = ?");
            $stmtStockCheck = $this->db->prepare("SELECT quantity FROM product_stock WHERE good_id = ? AND warehouse_id = ? FOR UPDATE");

            foreach ($originalTransactions as $t) {
                // Створюємо зворотну транзакцію
                $newQuantity = -$t['quantity']; // Інвертуємо кількість
                $newType = ($t['transaction_type'] === 'transfer_out') ? 'cancellation_in' : 'cancellation_out';
                $comment = 'Скасування операції від ' . date('d.m.Y H:i', strtotime($t['transaction_date']));

                // Оновлюємо залишок
                $stmtStockCheck->execute([$t['good_id'], $t['warehouse_id']]);
                $currentStock = (float)$stmtStockCheck->fetchColumn();
                $newBalance = $currentStock + $newQuantity;

                // Створюємо транзакцію та оновлюємо залишок
                $stmtTransaction->execute([date('Y-m-d H:i:s'), $t['good_id'], $t['warehouse_id'], $newQuantity, $newBalance, $newType, 'cancellation', $userId, $comment]);
                $stmtStockUpdate->execute([$newBalance, $t['good_id'], $t['warehouse_id']]);
            }
            
            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            $_SESSION['flash_message'] = ['type' => 'error', 'text' => 'Помилка скасування: ' . $e->getMessage()];
            return false;
        }
    }
}