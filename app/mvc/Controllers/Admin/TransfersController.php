<?php
// mvc/c_transfers.php
namespace App\Mvc\Controllers\Admin;

use App\Mvc\Models\Transfers;
use App\Mvc\Models\Warehouses;
use App\Mvc\Models\Goods;

class TransfersController extends BaseController {
    protected $mTransfers;

    public function __construct($params) {
        parent::__construct($params);
        if (!$this->hasPermission('transfers', 'v')) {
            $this->showAccessDenied();
            exit();
        }
        $this->mTransfers = new Transfers();
    }

    public function indexAction()
    {
        if (!$this->hasPermission('transfers', 'v')) {
            return $this->showAccessDenied();
        }
        
        $this->title = 'Переміщення товарів';
        
        // Отримуємо список усіх переміщень з моделі
        $transfers = $this->mTransfers->getTransfersList();
        
        // Передаємо отримані дані у шаблон під ключем 'transfers'
        $this->render('v_transfers_list', ['transfers' => $transfers]);
    }

    public function watchAction() {
        if (!$this->hasPermission('transfers', 'v')) {
            return $this->showAccessDenied();
        }
        
        $ids = $this->params['ids'] ?? '';
        $transferData = $this->mTransfers->getTransferDetailsByOutIds($ids);

        $this->title = $transferData ? 'Перегляд переміщення' : 'Операцію не знайдено';

        $this->render('v_transfer_single', ['transferData' => $transferData, 'ids' => $ids]);
    }

    public function addAction()
    {
        if (!$this->hasPermission('transfers', 'a')) {
            return $this->showAccessDenied();
        }
        $this->title = 'Створення переміщення';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                die('CSRF Error');
            }

            $result = $this->mTransfers->create($_POST, $this->currentUser['id']);

            if ($result) {
                $_SESSION['flash_message'] = ['type' => 'success', 'text' => 'Документ переміщення успішно створено.'];
                header('Location: ' . BASE_URL . '/transfers');
            } else {
                $_SESSION['flash_message'] = ['type' => 'error', 'text' => 'Помилка при створенні переміщення.'];
                header('Location: ' . BASE_URL . '/transfers/add');
            }
            exit();
        }

        $mWarehouses = new Warehouses();
        $mGoods = new Goods();

        $this->render('v_transfer_add', [
            'warehouses' => $mWarehouses->getAll(),
            'goods' => $mGoods->getAll()
        ]);
    }
    
    public function editAction() {
        if (!$this->hasPermission('transfers', 'e')) {
            return $this->showAccessDenied();
        }
        
        $ids = $this->params['ids'] ?? '';
        $transferData = $this->mTransfers->getTransferDetailsByOutIds($ids);
        
        if (!$transferData) {
            // Обробка, якщо документ не знайдено
            header("Location: " . BASE_URL . "/transfers");
            exit();
        }

        $this->title = 'Редагування переміщення';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) die('CSRF Error');
            
            $comment = trim($_POST['comment'] ?? '');
            if ($this->mTransfers->updateTransferComment($ids, $comment)) {
                $_SESSION['flash_message'] = ['type' => 'success', 'text' => 'Коментар оновлено.'];
            } else {
                $_SESSION['flash_message'] = ['type' => 'error', 'text' => 'Не вдалося оновити коментар.'];
            }
            header('Location: ' . BASE_URL . '/transfers/edit/' . $ids);
            exit();
        }

        $this->render('v_transfer_edit', ['transferData' => $transferData, 'ids' => $ids]);
    }

    /**
     * Отримує список згрупованих операцій переміщення з таблиці транзакцій.
     */
    public function getTransfersList(): array {
        $sql = "
            SELECT 
                t_out.transaction_date,
                t_out.user_id,
                u.name AS user_name,
                t_out.comment,
                GROUP_CONCAT(
                    CONCAT(
                        g.name, 
                        ' (', 
                        FORMAT(ABS(t_out.quantity), 3), 
                        '): ', 
                        w_from.name, 
                        ' → ', 
                        w_to.name
                    ) SEPARATOR '<br>'
                ) AS positions_html
            FROM 
                product_transactions AS t_out
            JOIN 
                product_transactions AS t_in ON t_out.id = t_in.related_transaction_id
            JOIN 
                users u ON t_out.user_id = u.id
            JOIN 
                goods g ON t_out.good_id = g.id
            JOIN 
                warehouses w_from ON t_out.warehouse_id = w_from.id
            JOIN 
                warehouses w_to ON t_in.warehouse_id = w_to.id
            WHERE 
                t_out.transaction_type = 'transfer_out'
            GROUP BY 
                t_out.transaction_date, t_out.user_id, t_out.comment
            ORDER BY 
                t_out.transaction_date DESC
        ";
        
        return $this->db->query($sql)->fetchAll();
    }

    public function cancelAction() {
        if (!$this->hasPermission('transfers', 'd')) {
            return $this->showAccessDenied();
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/transfers');
            exit();
        }

        $ids = $this->params['ids'] ?? '';
        
        if ($this->mTransfers->cancelTransfer($ids, $this->currentUser['id'])) {
            $_SESSION['flash_message'] = ['type' => 'success', 'text' => 'Операцію переміщення успішно скасовано.'];
        }
        
        header('Location: ' . BASE_URL . '/transfers');
        exit();
    }

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
}