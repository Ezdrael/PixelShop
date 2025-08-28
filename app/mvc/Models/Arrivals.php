<?php
// app/Mvc/Models/Arrivals.php
namespace App\Mvc\Models;

use App\Core\DB;
use PDO;

class Arrivals 
{
    private $db;

    public function __construct() 
    {
        $this->db = DB::getInstance();
    }

    /**
     * Отримує список ОСТАННІХ АКТИВНИХ версій надходжень для головної сторінки.
     */
    public function getArrivalsList(array $filters = []): array
    {
        $baseSql = "
            SELECT 
                t_main.document_id,
                t_main.revision_id,
                t_main.transaction_date,
                t_main.status,
                u.name as user_name,
                GROUP_CONCAT(
                    CONCAT(g.name, ' <span class=\"category-id-badge\">', t_main.quantity, ' шт.</span>') 
                    SEPARATOR '<br>'
                ) as positions_summary
            FROM 
                product_transactions t_main
            INNER JOIN (
                SELECT 
                    revision_id,
                    SUBSTRING_INDEX(GROUP_CONCAT(document_id ORDER BY transaction_date DESC, id DESC), ',', 1) AS latest_document_id
                FROM 
                    product_transactions
                WHERE 
                    document_type = 'arrival_form' AND revision_id IS NOT NULL
                GROUP BY 
                    revision_id
            ) AS latest_docs ON t_main.document_id = latest_docs.latest_document_id
            LEFT JOIN users u ON t_main.user_id = u.id
            LEFT JOIN goods g ON t_main.good_id = g.id
        ";

        $where = ["t_main.document_type = 'arrival_form'", "t_main.status = 'completed'"];
        $params = [];

        if (!empty($filters['date_from'])) {
            $where[] = "t_main.transaction_date >= :date_from";
            $params[':date_from'] = $filters['date_from'] . ' 00:00:00';
        }
        if (!empty($filters['date_to'])) {
            $where[] = "t_main.transaction_date <= :date_to";
            $params[':date_to'] = $filters['date_to'] . ' 23:59:59';
        }
        if (!empty($filters['document_id'])) {
            $where[] = "t_main.document_id LIKE :document_id";
            $params[':document_id'] = '%' . $filters['document_id'] . '%';
        }
        if (!empty($filters['user_id'])) {
            $where[] = "t_main.user_id = :user_id";
            $params[':user_id'] = $filters['user_id'];
        }

        $sql = $baseSql . " WHERE " . implode(' AND ', $where);
        
        $sql .= " GROUP BY t_main.document_id, t_main.revision_id, t_main.transaction_date, t_main.status, u.name";
        
        if (!empty($filters['positions'])) {
            $sql .= " HAVING positions_summary LIKE :positions";
            $params[':positions'] = '%' . $filters['positions'] . '%';
        }
        
        $sql .= " ORDER BY t_main.transaction_date DESC;";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Створює нове надходження (використовується для першого створення та для оновлення).
     * @param array $data Дані з форми.
     * @param int $userId ID користувача.
     * @param string|null $revisionId ID групи ревізій (для нових версій).
     * @return bool
     */
    public function processNewArrival(array $data, int $userId, ?string $revisionId = null): bool
    {
        try {
            $this->db->beginTransaction();
            $this->_processNewArrivalWork($data, $userId, $revisionId);
            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            error_log("Arrival processing failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Скасовує документ.
     * @param string $documentId ID документа для скасування.
     * @param int $userId ID користувача, що виконує дію.
     * @return bool
     */
    public function cancelArrival(string $documentId, int $userId): bool
    {
        try {
            $this->db->beginTransaction();
            $this->_cancelArrivalWork($documentId, $userId);
            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            error_log("Arrival cancellation failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Оновлює надходження (скасовує старе і створює нове).
     * @param string $originalDocumentId ID документа, який редагується.
     * @param array $newData Нові дані з форми.
     * @param int $userId ID користувача.
     * @return bool
     */
    public function updateArrival(string $originalDocumentId, array $newData, int $userId): bool
    {
        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare("SELECT revision_id FROM product_transactions WHERE document_id = ? AND status = 'completed' LIMIT 1");
            $stmt->execute([$originalDocumentId]);
            $revisionId = $stmt->fetchColumn();

            if (!$revisionId) {
                throw new \Exception("Не знайдено ID ревізії для документа або документ не є проведеним.");
            }
            
            // !! ЗМІНА: Передаємо час редагування у метод скасування !!
            $editTimestamp = $newData['arrival_datetime'];
            $this->_cancelArrivalWork($originalDocumentId, $userId, $editTimestamp);
            
            $this->db->prepare("UPDATE product_transactions SET status = 'edited' WHERE document_id = ?")->execute([$originalDocumentId]);

            // !! ЗМІНА: Встановлюємо час для нової версії на 1 секунду пізніше !!
            $newVersionTimestamp = date('Y-m-d H:i:s', strtotime($editTimestamp . ' +1 second'));
            $newData['arrival_datetime'] = $newVersionTimestamp;
            $this->_processNewArrivalWork($newData, $userId, $revisionId);

            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            error_log("Arrival update failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Отримує деталі ОДНІЄЇ версії документа.
     */
    public function getArrivalDetails(string $documentId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT 
                t.*, 
                u.name as user_name, 
                g.name as good_name, 
                g.is_active, 
                w.name as warehouse_name
            FROM product_transactions t
            LEFT JOIN users u ON t.user_id = u.id
            LEFT JOIN goods g ON t.good_id = g.id
            LEFT JOIN warehouses w ON t.warehouse_id = w.id
            WHERE 
                t.document_id = ? 
                AND t.document_type = 'arrival_form'
                AND t.transaction_type = 'arrival'
        ");
        $stmt->execute([$documentId]);
        $items = $stmt->fetchAll();

        if (empty($items)) {
            return null;
        }

        $total_positions = count($items);
        $total_quantity = 0;
        foreach ($items as $item) {
            $total_quantity += $item['quantity'];
        }

        return [
            'details' => array_merge($items[0], [
                'total_positions' => $total_positions,
                'total_quantity' => $total_quantity
            ]),
            'items' => $items
        ];
    }
    
    /**
     * Отримує всі версії одного документа (крім скасовуючих) для випадаючого списку.
     */
    public function getArrivalVersions(string $documentId): array
    {
        $stmt_rev = $this->db->prepare("SELECT revision_id FROM product_transactions WHERE document_id = ? LIMIT 1");
        $stmt_rev->execute([$documentId]);
        $revisionId = $stmt_rev->fetchColumn();

        if (!$revisionId) {
            return [];
        }
        
        $stmt_docs = $this->db->prepare("
            SELECT 
                document_id,
                MAX(transaction_date) as transaction_date,
                MAX(status) as status
            FROM product_transactions 
            WHERE 
                revision_id = ? 
                AND transaction_type <> 'arrival_reversal'
            GROUP BY document_id
            ORDER BY transaction_date DESC
        ");
        $stmt_docs->execute([$revisionId]);
        
        return $stmt_docs->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Знаходить коригуючі транзакції для скасованого або відредагованого документа.
     */
    public function findCorrectiveTransactions(string $documentId, string $status): ?array
    {
        $revisionIdStmt = $this->db->prepare("SELECT revision_id FROM product_transactions WHERE document_id = ? LIMIT 1");
        $revisionIdStmt->execute([$documentId]);
        $revisionId = $revisionIdStmt->fetchColumn();

        if (!$revisionId) {
            return null;
        }

        $query = "";
        $params = [];
        $result = [];

        if ($status === 'edited') {
            $result['title'] = 'Нова версія документа (Редакція)';
            $query = "
                SELECT document_id, transaction_date FROM product_transactions 
                WHERE revision_id = ? AND status = 'completed' 
                ORDER BY transaction_date DESC LIMIT 1
            ";
            $params = [$revisionId];

        } elseif ($status === 'canceled') {
            $result['title'] = 'Документ сторнування (Скасування)';
            $query = "
                SELECT t_reversal.document_id, t_reversal.transaction_date 
                FROM product_transactions t_original
                JOIN product_transactions t_reversal ON t_original.id = t_reversal.related_transaction_id
                WHERE t_original.document_id = ? AND t_reversal.transaction_type = 'arrival_reversal'
                LIMIT 1
            ";
            $params = [$documentId];
        }

        if (empty($query)) {
            return null;
        }

        $correctiveDocStmt = $this->db->prepare($query);
        $correctiveDocStmt->execute($params);
        $correctiveDoc = $correctiveDocStmt->fetch(PDO::FETCH_ASSOC);

        if (!$correctiveDoc) {
            return null;
        }
        
        $result['details'] = $this->getArrivalDetails($correctiveDoc['document_id']);
        return $result;
    }
    
    public function getArrivalByDocumentId(string $documentId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT t.good_id, t.warehouse_id, t.quantity, g.name as good_name, t.transaction_date, t.comment
            FROM product_transactions t
            JOIN goods g ON t.good_id = g.id
            WHERE t.document_id = ? AND t.status = 'completed'
        ");
        $stmt->execute([$documentId]);
        $items = $stmt->fetchAll();

        if (empty($items)) {
            return null;
        }
        
        return [
            'document_id' => $documentId, 
            'transaction_date' => $items[0]['transaction_date'],
            'comment' => $items[0]['comment'],
            'items' => $items
        ];
    }

    /**
     * Порівнює дві версії документа і повертає деталізований список змін.
     * @param string $oldDocId ID старого документа.
     * @param string $newDocId ID нового документа.
     * @return array Масив зі списками товарів: unchanged, modified, added, removed.
     */
    public function getArrivalComparison(string $oldDocId, string $newDocId): array
    {
        $oldItemsRaw = $this->getArrivalDetails($oldDocId)['items'] ?? [];
        $newItemsRaw = $this->getArrivalDetails($newDocId)['items'] ?? [];

        // Перетворюємо масиви для легкого доступу за ключем "товар-склад"
        $oldItems = [];
        foreach ($oldItemsRaw as $item) {
            $key = $item['good_id'] . '-' . $item['warehouse_id'];
            $oldItems[$key] = $item;
        }

        $newItems = [];
        foreach ($newItemsRaw as $item) {
            $key = $item['good_id'] . '-' . $item['warehouse_id'];
            $newItems[$key] = $item;
        }

        $comparison = [
            'unchanged' => [],
            'modified' => [],
            'added' => [],
            'removed' => []
        ];

        // Знаходимо незмінні та змінені позиції
        foreach ($oldItems as $key => $oldItem) {
            if (isset($newItems[$key])) {
                $newItem = $newItems[$key];
                if ($oldItem['quantity'] == $newItem['quantity']) {
                    $comparison['unchanged'][] = $newItem;
                } else {
                    $comparison['modified'][] = [
                        'new' => $newItem,
                        'old_quantity' => $oldItem['quantity']
                    ];
                }
            } else {
                // Якщо позиція є в старому, але немає в новому - її видалили
                $comparison['removed'][] = $oldItem;
            }
        }

        // Знаходимо додані позиції
        foreach ($newItems as $key => $newItem) {
            if (!isset($oldItems[$key])) {
                $comparison['added'][] = $newItem;
            }
        }

        return $comparison;
    }
    
    // --- ПРИВАТНІ "РОБОЧІ" МЕТОДИ (БЕЗ ТРАНЗАКЦІЙ) ---

    private function _processNewArrivalWork(array $data, int $userId, ?string $revisionId = null)
    {
        $items = $data['items'] ?? [];
        if (empty($items)) {
            throw new \Exception("Немає позицій для проведення надходження.");
        }

        $datetime = $data['arrival_datetime'];
        $comment = $data['comment'] ?? '';
        $documentId = 'ARR-' . date('Ymd-His-') . uniqid();
        
        if ($revisionId === null) {
            $revisionId = $documentId;
        }

        $stockUpdateStmt = $this->db->prepare(
            "INSERT INTO product_stock (good_id, warehouse_id, quantity) VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE quantity = quantity + VALUES(quantity)"
        );
        
        $transactionStmt = $this->db->prepare(
            "INSERT INTO product_transactions 
                (transaction_date, user_id, document_id, revision_id, good_id, warehouse_id, quantity, transaction_type, document_type, status, comment, balance) 
             VALUES (?, ?, ?, ?, ?, ?, ?, 'arrival', 'arrival_form', 'completed', ?, 
                (SELECT quantity FROM product_stock WHERE good_id = ? AND warehouse_id = ?))"
        );

        foreach ($items as $item) {
            $goodId = (int)$item['good_id'];
            $warehouseId = (int)$item['warehouse_id'];
            $quantity = (float)$item['quantity'];

            if ($goodId <= 0 || $warehouseId <= 0 || $quantity <= 0) continue;

            $stockUpdateStmt->execute([$goodId, $warehouseId, $quantity]);
            
            $transactionStmt->execute([$datetime, $userId, $documentId, $revisionId, $goodId, $warehouseId, $quantity, $comment, $goodId, $warehouseId]);
        }
    }

    private function _cancelArrivalWork(string $documentId, int $userId, string $timestamp)
    {
        $stmt = $this->db->prepare("SELECT * FROM product_transactions WHERE document_id = ? AND status = 'completed'");
        $stmt->execute([$documentId]);
        $originalTransactions = $stmt->fetchAll();

        if (empty($originalTransactions)) {
            throw new \Exception("Документ не знайдено або він вже скасований/відредагований.");
        }

        $reversalStmt = $this->db->prepare(
            "INSERT INTO product_transactions (transaction_date, user_id, document_id, revision_id, good_id, warehouse_id, quantity, transaction_type, document_type, status, comment, related_transaction_id) 
             VALUES (?, ?, ?, ?, ?, ?, ?, 'arrival_reversal', 'arrival_form', 'canceled', 'Скасування документа', ?)"
        );

        // !! КЛЮЧОВА ЗМІНА: Додано запит на ОНОВЛЕННЯ ЗАЛИШКІВ !!
        $stockUpdateStmt = $this->db->prepare("UPDATE product_stock SET quantity = quantity - ? WHERE good_id = ? AND warehouse_id = ?");
        
        foreach ($originalTransactions as $tx) {
            // Виконуємо віднімання старої кількості від залишку
            $stockUpdateStmt->execute([$tx['quantity'], $tx['good_id'], $tx['warehouse_id']]);
            
            // Створюємо транзакцію скасування
            $reversalStmt->execute([$timestamp, $userId, $tx['document_id'], $tx['revision_id'], $tx['good_id'], $tx['warehouse_id'], -$tx['quantity'], $tx['id']]);
        }
        
        // Цей рядок залишається без змін
        $updateStatusStmt = $this->db->prepare("UPDATE product_transactions SET status = 'canceled' WHERE document_id = ?");
        $updateStatusStmt->execute([$documentId]);
    }
}