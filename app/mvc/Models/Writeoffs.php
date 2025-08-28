<?php
// app/Mvc/Models/Writeoffs.php
namespace App\Mvc\Models;

use App\Core\DB;
use PDO;

class Writeoffs
{
    private $db;

    public function __construct()
    {
        $this->db = DB::getInstance();
    }
    
    /**
     * Тепер групує позиції та додає склад/коментар в HTML.
     */
    public function getGroupedWriteoffs(): array
    {
        $sql = "
            SELECT 
                t.transaction_date,
                t.user_id,
                u.name as user_name,
                GROUP_CONCAT(DISTINCT t.id ORDER BY t.id) as transaction_ids,
                (SELECT status FROM product_transactions WHERE id = MIN(t.id)) as status,
                GROUP_CONCAT(
                    CONCAT(
                        g.name, 
                        ' <span class=\"category-id-badge\">', 
                        FORMAT(ABS(t.quantity), 3), ' шт.</span>'
                    ) 
                    ORDER BY g.name SEPARATOR '<br>'
                ) as positions_html,
                wh.name as warehouse_name,
                t.comment
            FROM product_transactions t
            JOIN goods g ON t.good_id = g.id
            JOIN warehouses wh ON t.warehouse_id = wh.id
            LEFT JOIN users u ON t.user_id = u.id
            WHERE t.transaction_type = 'writeoff'
            GROUP BY t.transaction_date, t.user_id, t.warehouse_id, t.comment
            ORDER BY t.transaction_date DESC
        ";
        return $this->db->query($sql)->fetchAll();
    }
    
    /**
     * Отримує деталі одного документа списання для редагування.
     */
    public function getWriteoffDocumentByIds(string $ids): ?array
    {
        $idArray = explode(',', $ids);
        if (empty($idArray)) return null;

        $placeholders = implode(',', array_fill(0, count($idArray), '?'));

        $sql = "
            SELECT 
                t.id, t.good_id, t.warehouse_id, ABS(t.quantity) as quantity, t.comment,
                g.name as good_name
            FROM product_transactions t
            JOIN goods g ON t.good_id = g.id
            WHERE t.id IN ($placeholders) AND t.transaction_type = 'writeoff'
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($idArray);
        
        $items = $stmt->fetchAll();
        if (empty($items)) return null;

        return [
            'warehouse_id' => $items[0]['warehouse_id'],
            'comment' => $items[0]['comment'],
            'items' => $items
        ];
    }
    
    /**
     * Видалення (скасування) документа списання.
     * Повертає товар на склад.
     */
    public function delete(string $ids): bool
    {
        $idArray = explode(',', $ids);
        if (empty($idArray)) return false;

        try {
            $this->db->beginTransaction();

            $placeholders = implode(',', array_fill(0, count($idArray), '?'));
            $selectStmt = $this->db->prepare("SELECT * FROM product_transactions WHERE id IN ($placeholders) FOR UPDATE");
            $selectStmt->execute($idArray);
            $transactions = $selectStmt->fetchAll();

            foreach ($transactions as $tx) {
                // Повертаємо товар на склад
                $stockStmt = $this->db->prepare("UPDATE product_stock SET quantity = quantity + ? WHERE good_id = ? AND warehouse_id = ?");
                $stockStmt->execute([abs($tx['quantity']), $tx['good_id'], $tx['warehouse_id']]);

                // Позначаємо транзакцію як скасовану
                $updateStmt = $this->db->prepare("UPDATE product_transactions SET status = 'canceled' WHERE id = ?");
                $updateStmt->execute([$tx['id']]);
            }
            
            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            error_log("Writeoff deletion failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Створює списання, де склад вказується для кожної позиції.
     */
    public function create(array $data, int $userId): bool
    {
        try {
            $this->db->beginTransaction();

            $date = $data['date'] ?? date('Y-m-d H:i:s');
            $comment = $data['comment'];

            $stockStmt = $this->db->prepare("UPDATE product_stock SET quantity = quantity - ? WHERE good_id = ? AND warehouse_id = ?");
            $transactionStmt = $this->db->prepare(
                "INSERT INTO product_transactions 
                    (transaction_date, good_id, warehouse_id, quantity, transaction_type, document_type, user_id, comment, balance, status) 
                 VALUES (?, ?, ?, ?, 'writeoff', 'writeoff_form', ?, ?, 
                    (SELECT quantity FROM product_stock WHERE good_id = ? AND warehouse_id = ?), 'completed')"
            );

            foreach ($data['goods'] as $good) {
                $goodId = $good['id'];
                $quantity = $good['quantity'];
                $warehouseId = $good['warehouse_id']; // Беремо склад для конкретної позиції

                $stockStmt->execute([$quantity, $goodId, $warehouseId]);
                $transactionStmt->execute([$date, $goodId, $warehouseId, -$quantity, $userId, $comment, $goodId, $warehouseId]);
            }

            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            error_log("Writeoff creation failed: " . $e->getMessage());
            return false;
        }
    }
}