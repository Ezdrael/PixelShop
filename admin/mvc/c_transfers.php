<?php
// mvc/c_transfers.php

class C_Transfers extends C_Base {
    protected $mTransfers;

    public function __construct($params) {
        parent::__construct($params);
        if (!$this->hasPermission('transfers', 'v')) {
            $this->showAccessDenied();
            exit();
        }
        $this->mTransfers = new M_Transfers();
    }

    // mvc/c_transfers.php

    public function indexAction() {
        // Ця перевірка вже є в конструкторі, але для ясності можна залишити
        if (!$this->hasPermission('transfers', 'v')) {
            return $this->showAccessDenied();
        }
        
        $this->title = 'Історія переміщень';

        // Отримуємо згруповані дані з моделі
        $transfersList = $this->mTransfers->getTransfersList();

        // Рендеримо новий вид для списку
        $this->render('v_transfers_list', ['transfersList' => $transfersList]);
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

    public function addAction() {
        if (!$this->hasPermission('transfers', 'a')) {
            return $this->showAccessDenied();
        }
        $this->title = 'Створення переміщення';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) die('CSRF Error');
            
            $data = [
                'user_id' => $this->currentUser['id'],
                'order_date' => $_POST['order_date'],
                'comment' => trim($_POST['comment'] ?? ''),
                'items' => []
            ];

            if (isset($_POST['good_id']) && is_array($_POST['good_id'])) {
                for ($i = 0; $i < count($_POST['good_id']); $i++) {
                    $data['items'][] = [
                        'good_id' => $_POST['good_id'][$i],
                        'from_warehouse_id' => $_POST['from_warehouse_id'][$i],
                        'to_warehouse_id' => $_POST['to_warehouse_id'][$i],
                        'quantity' => $_POST['quantity'][$i]
                    ];
                }
            }
            
            if ($this->mTransfers->processNewTransfer($data)) {
                $_SESSION['flash_message'] = ['type' => 'success', 'text' => 'Переміщення успішно створено.'];
                header('Location: ' . BASE_URL . '/transfers/add'); // Перезавантажуємо сторінку для нової операції
                exit();
            }
            // Якщо були помилки, модель сама встановить flash-повідомлення
            header('Location: ' . BASE_URL . '/transfers/add');
            exit();
        }

        // Завантажуємо довідники для форми
        $mWarehouses = new M_Warehouses();
        $mGoods = new M_Goods();
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
}