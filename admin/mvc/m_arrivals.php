<?php
// mvc/m_arrivals.php

class M_Arrivals {
    private $db;

    public function __construct() {
        $this->db = DB::getInstance();
    }

    /**
     * Отримує згруповані дані про всі надходження з таблиці транзакцій.
     *
     * @return array
     */
    public function getArrivalsList(): array
    {
        $sql = "
            SELECT 
                transaction_date,
                user_id,
                u.name as user_name,
                GROUP_CONCAT(
                    CONCAT('<span class=\"category-id-badge\">', g.name, ' - ', t.quantity, '</span>') 
                    SEPARATOR ' '
                ) as positions_html,
                SUM(t.quantity) as total_quantity
            FROM 
                product_transactions t
            JOIN 
                users u ON t.user_id = u.id
            JOIN
                goods g ON t.good_id = g.id
            WHERE 
                t.transaction_type = 'arrival'
            GROUP BY 
                t.transaction_date, t.user_id 
            ORDER BY 
                t.transaction_date DESC;
        ";
        return $this->db->query($sql)->fetchAll();
    }
    
    /**
     * Обробити нове надходження: створює транзакції та оновлює залишки.
     *
     * @param array $data Дані з форми ('arrival_datetime', 'user_id', 'items' => [...])
     * @return bool Успіх чи невдача операції
     */
    public function processNewArrival(array $data): bool
    {
        try {
            // 1. Починаємо головну транзакцію бази даних
            $this->db->beginTransaction();

            foreach ($data['items'] as $item) {
                // Перевірка, чи є всі необхідні дані для позиції
                if (empty($item['good_id']) || empty($item['warehouse_id']) || empty($item['quantity'])) {
                    continue; // Пропускаємо порожні рядки
                }

                $goodId = (int)$item['good_id'];
                $warehouseId = (int)$item['warehouse_id'];
                $quantityChange = (float)$item['quantity'];

                // 2. Отримуємо останній залишок І БЛОКУЄМО РЯДКИ для цього товару/складу
                $stmtLast = $this->db->prepare(
                    "SELECT balance FROM product_transactions 
                     WHERE good_id = ? AND warehouse_id = ? 
                     ORDER BY transaction_date DESC, id DESC 
                     LIMIT 1 FOR UPDATE"
                );
                $stmtLast->execute([$goodId, $warehouseId]);
                $lastBalance = (float)$stmtLast->fetchColumn();

                // 3. Розраховуємо новий залишок
                $newBalance = $lastBalance + $quantityChange;

                // 4. Створюємо запис у журналі транзакцій
                $stmtTransaction = $this->db->prepare(
                    "INSERT INTO product_transactions (transaction_date, good_id, warehouse_id, quantity, balance, transaction_type, document_type, user_id) 
                     VALUES (?, ?, ?, ?, ?, 'arrival', 'arrival_form', ?)"
                );
                $stmtTransaction->execute([
                    $data['arrival_datetime'],
                    $goodId,
                    $warehouseId,
                    $quantityChange, // Додатне число
                    $newBalance,     // Новий розрахований залишок
                    $data['user_id']
                ]);

                // 5. Оновлюємо поточний залишок у таблиці product_stock
                $stmtStock = $this->db->prepare(
                    "INSERT INTO product_stock (good_id, warehouse_id, quantity) VALUES (?, ?, ?)
                     ON DUPLICATE KEY UPDATE quantity = quantity + ?"
                );
                $stmtStock->execute([$goodId, $warehouseId, $quantityChange, $quantityChange]);
            }

            // 6. Фіксуємо всі зміни, якщо не було помилок
            $this->db->commit();
            return true;

        } catch (Exception $e) {
            // 7. Відкочуємо всі зміни у разі будь-якої помилки
            $this->db->rollBack();
            error_log("Arrival Processing Error: " . $e->getMessage()); // Для відладки
            return false;
        }
    }

    /**
     * Отримує деталі одного "віртуального" надходження за датою та ID користувача.
     *
     * @param string $datetime Точна дата й час транзакції
     * @param int $userId ID користувача
     * @return array|null
     */
    public function getArrivalDetails(string $datetime, int $userId): ?array
    {
        $sql = "
            SELECT 
                t.transaction_date,
                u.name as user_name,
                g.id as good_id, -- ДОДАНО ID ТОВАРУ
                g.name as good_name,
                g.is_active, -- ДОДАНО СТАТУС ТОВАРУ
                w.name as warehouse_name,
                t.quantity
            FROM 
                product_transactions t
            JOIN
                users u ON t.user_id = u.id
            JOIN
                goods g ON t.good_id = g.id
            JOIN
                warehouses w ON t.warehouse_id = w.id
            WHERE 
                t.transaction_type = 'arrival' AND t.transaction_date = ? AND t.user_id = ?
            ORDER BY
                g.name;
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$datetime, $userId]);
        $items = $stmt->fetchAll();

        if (empty($items)) {
            return null;
        }

        // Збираємо дані для повернення
        $total_quantity = 0;
        foreach ($items as $item) {
            $total_quantity += $item['quantity'];
        }

        return [
            'details' => [
                'transaction_date' => $items[0]['transaction_date'],
                'user_name' => $items[0]['user_name'],
                'total_quantity' => $total_quantity,
                'total_positions' => count($items)
            ],
            'items' => $items
        ];
    }
}