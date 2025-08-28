<?php
// app/Mvc/Controllers/Admin/WriteoffsController.php
namespace App\Mvc\Controllers\Admin;

use App\Mvc\Models\Goods;
use App\Mvc\Models\Warehouses;
use App\Mvc\Models\Writeoffs;

class WriteoffsController extends BaseController
{
    protected $mWriteoffs;

    public function __construct($params)
    {
        parent::__construct($params);
        $this->mWriteoffs = new Writeoffs();
    }

    public function indexAction()
    {
        if (!$this->hasPermission('writeoffs', 'v')) {
            return $this->showAccessDenied();
        }
        $this->title = 'Списання';
        $writeoffs = $this->mWriteoffs->getGroupedWriteoffs();
        $this->render('v_writeoffs_list', ['writeoffs' => $writeoffs]);
    }

    public function addAction()
    {
        if (!$this->hasPermission('writeoffs', 'a')) {
            return $this->showAccessDenied();
        }
        $this->title = 'Створення списання';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) die('CSRF Error');
            
            $result = $this->mWriteoffs->create($_POST, $this->currentUser['id']);
            if ($result) {
                $_SESSION['flash_message'] = ['type' => 'success', 'text' => 'Документ списання успішно створено.'];
                header('Location: ' . BASE_URL . '/writeoffs');
            } else {
                $_SESSION['flash_message'] = ['type' => 'error', 'text' => 'Помилка при створенні списання.'];
                header('Location: ' . BASE_URL . '/writeoffs/add');
            }
            exit();
        }

        $mWarehouses = new Warehouses();
        $mGoods = new Goods();
        $this->render('v_writeoff_add', [
            'warehouses' => $mWarehouses->getAll(),
            'goods' => $mGoods->getAll()
        ]);
    }
    
    /**
     * Метод для редагування списання.
     */
    public function editAction()
    {
        if (!$this->hasPermission('writeoffs', 'e')) {
            return $this->showAccessDenied();
        }
        $this->title = 'Редагування списання';
        $ids = $this->params['ids'] ?? '';
        
        $document = $this->mWriteoffs->getWriteoffDocumentByIds($ids);
        if (!$document) {
            return $this->notFoundAction();
        }

        // Тут буде логіка для POST-запиту (перепроведення)
        // ...

        $mWarehouses = new Warehouses();
        $mGoods = new Goods();
        $this->render('v_writeoff_edit', [
            'document' => $document,
            'transaction_ids' => $ids,
            'warehouses' => $mWarehouses->getAll(),
            'goods' => $mGoods->getAll()
        ]);
    }
    
    /**
     * Метод для видалення списання.
     */
    public function deleteAction()
    {
        header('Content-Type: application/json');
        if (!$this->hasPermission('writeoffs', 'd')) {
            echo json_encode(['success' => false, 'message' => 'Немає прав доступу.']);
            exit();
        }
        
        $ids = $this->params['ids'] ?? '';
        if ($this->mWriteoffs->delete($ids)) {
            $_SESSION['flash_message'] = ['type' => 'success', 'text' => 'Списання успішно скасовано.'];
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Помилка скасування списання.']);
        }
        exit();
    }
}