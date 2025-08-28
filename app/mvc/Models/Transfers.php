<?php
// mvc/m_transfers.php
namespace App\Mvc\Models;

use App\Core\DB;
use PDO;

class Transfers {
    private $db;

    public function __construct() {
        $this->db = DB::getInstance();
    }

    /**
     * Метод для створення нового переміщення.
     */
    public function create(array $data, int $userId): bool
    {
        $fromWarehouseId = (int)($data['from_warehouse_id'] ?? 0);
        $toWarehouseId = (int)($data['to_warehouse_id'] ?? 0);
        $goods = $data['goods'] ?? [];

        if ($fromWarehouseId === $toWarehouseId || empty($goods)) {
            return false;
        }

        try {
            $this->db->beginTransaction();
            $date = date('Y-m-d H:i:s');

            foreach ($goods as $good) {
                $goodId = (int)$good['id'];
                $quantity = (float)$good['quantity'];

                // Транзакція списання (transfer_out)
                $outStmt = $this->db->prepare(
                    "INSERT INTO product_transactions (transaction_date, good_id, warehouse_id, quantity, transaction_type, document_type, user_id, status) 
                     VALUES (?, ?, ?, ?, 'transfer_out', 'transfer_form', ?, 'completed')"
                );
                $outStmt->execute([$date, $goodId, $fromWarehouseId, -$quantity, $userId]);
                $outTxId = $this->db->lastInsertId();

                // Транзакція надходження (transfer_in)
                $inStmt = $this->db->prepare(
                    "INSERT INTO product_transactions (transaction_date, good_id, warehouse_id, quantity, transaction_type, document_type, user_id, related_transaction_id, status) 
                     VALUES (?, ?, ?, ?, 'transfer_in', 'transfer_form', ?, ?, 'completed')"
                );
                $inStmt->execute([$date, $goodId, $toWarehouseId, $quantity, $userId, $outTxId]);

                // Оновлення залишків
                $this->db->prepare("UPDATE product_stock SET quantity = quantity - ? WHERE good_id = ? AND warehouse_id = ?")->execute([$quantity, $goodId, $fromWarehouseId]);
                $this->db->prepare("UPDATE product_stock SET quantity = quantity + ? WHERE good_id = ? AND warehouse_id = ?")->execute([$quantity, $goodId, $toWarehouseId]);
            }

            $this->db->commit();
            return true;

        } catch (\Exception $e) {
            $this->db->rollBack();
            error_log("Transfer creation failed: " . $e->getMessage());
            return false;
        }
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
    public function getTransfersList()
    {
        $sql = "
            SELECT 
                pt_out.transaction_date,
                u.name as user_name,
                pt_out.status,
                GROUP_CONCAT(pt_out.id) as transaction_ids, -- Збираємо ID для посилання
                GROUP_CONCAT(
                    CONCAT(
                        '<span class=\"category-id-badge\">', g.name, ' | ', 
                        FORMAT(ABS(pt_out.quantity), 0), ' шт. | ', 
                        w_from.name, ' → ', w_to.name, '</span>'
                    ) 
                    SEPARATOR '<br>'
                ) as positions_html -- Формуємо HTML для списку позицій
            FROM 
                product_transactions as pt_out
            JOIN 
                product_transactions as pt_in ON pt_in.related_transaction_id = pt_out.id
            JOIN 
                goods g ON pt_out.good_id = g.id
            JOIN 
                warehouses w_from ON pt_out.warehouse_id = w_from.id
            JOIN 
                warehouses w_to ON pt_in.warehouse_id = w_to.id
            LEFT JOIN 
                users u ON pt_out.user_id = u.id
            WHERE 
                pt_out.transaction_type = 'transfer_out'
            GROUP BY
                pt_out.transaction_date, 
                pt_out.user_id,
                pt_out.status -- Групуємо за датою, користувачем та статусом
            ORDER BY 
                pt_out.transaction_date DESC
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
    
    /**
     * Скасовує переміщення та позначає оригінальні транзакції як скасовані.
     */
    public function cancelTransfer(array $transactionIds, int $userId, string $comment): bool
    {
        try {
            $this->db->beginTransaction();

            // 1. Отримуємо деталі оригінальних транзакцій
            $placeholders = implode(',', array_fill(0, count($transactionIds), '?'));
            $sql = "SELECT * FROM product_transactions WHERE id IN ($placeholders) AND transaction_type LIKE 'transfer_%' FOR UPDATE";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($transactionIds);
            $originalTransactions = $stmt->fetchAll();

            if (count($originalTransactions) !== count($transactionIds)) {
                throw new \Exception("Одна з транзакцій не знайдена або не є переміщенням.");
            }

            // 2. Створюємо зворотні (сторнуючі) транзакції
            foreach ($originalTransactions as $trx) {
                // Створюємо зворотну транзакцію
                $this->createReverseTransaction($trx, $userId, $comment);
            }
            
            // 3. Оновлюємо статус оригінальних транзакцій на 'canceled'
            $updateSql = "UPDATE product_transactions SET status = 'canceled' WHERE id IN ($placeholders)";
            $updateStmt = $this->db->prepare($updateSql);
            $updateStmt->execute($transactionIds);

            $this->db->commit();
            return true;

        } catch (\Exception $e) {
            $this->db->rollBack();
            error_log("Transfer Cancellation Failed: " . $e->getMessage());
            return false;
        }
    }


}