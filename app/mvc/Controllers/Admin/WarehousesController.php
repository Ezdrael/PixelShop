<?php
// ===================================================================
// Файл: mvc/c_warehouses.php 🆕
// ===================================================================
namespace App\Mvc\Controllers\Admin;

use App\Mvc\Models\Roles; 
use App\Mvc\Models\Warehouses;

class WarehousesController extends BaseController {
    protected $mWarehouses;

    public function __construct($params) {
        parent::__construct($params);
        $this->mWarehouses = new Warehouses();
    }

    public function indexAction() {
        if (!$this->hasPermission('warehouses', 'v')) return $this->showAccessDenied();
        $this->title = 'Керування складами';
        $warehouses = $this->mWarehouses->getAll();
        $this->render('v_warehouses_list', ['warehouses' => $warehouses]);
    }
    
    public function watchAction() {
        if (!$this->hasPermission('warehouses', 'v')) {
            return $this->showAccessDenied();
        }
        
        $id = (int)($this->params['id'] ?? 0);
        
        // Отримуємо інформацію про склад
        $warehouse = $this->mWarehouses->getById($id);
        
        // Отримуємо список товарів на цьому складі
        $products = $this->mWarehouses->getProductsByWarehouseId($id);

        $this->title = $warehouse ? 'Перегляд складу: ' . htmlspecialchars($warehouse['name']) : 'Склад не знайдено';
        
        // Передаємо у вид дані про склад та товари
        $this->render('v_warehouse_single', [
            'warehouse' => $warehouse,
            'products' => $products
        ]);
    }

    public function addAction() {
        if (!$this->hasPermission('warehouses', 'a')) return $this->showAccessDenied();
        $this->title = 'Додавання нового складу';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) die('CSRF Error');
            if (!empty($_POST['name'])) {
                $this->mWarehouses->add($_POST);
                $_SESSION['flash_message'] = ['type' => 'success', 'text' => 'Новий склад успішно додано.'];
                header('Location: ' . BASE_URL . '/warehouses');
                exit();
            } else {
                $_SESSION['flash_message'] = ['type' => 'error', 'text' => 'Назва складу не може бути порожньою.'];
            }
        }
        $this->render('v_warehouse_add');
    }
    
    public function editAction() {
        if (!$this->hasPermission('warehouses', 'e')) return $this->showAccessDenied();
        $id = (int)($this->params['id'] ?? 0);
        $this->title = 'Редагування складу';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) die('CSRF Error');
            if (!empty($_POST['name'])) {
                $this->mWarehouses->update($id, $_POST);
                $_SESSION['flash_message'] = ['type' => 'success', 'text' => 'Склад успішно оновлено.'];
            } else {
                $_SESSION['flash_message'] = ['type' => 'error', 'text' => 'Назва складу не може бути порожньою.'];
            }
            header('Location: ' . BASE_URL . '/warehouses/edit/' . $id);
            exit();
        }
        
        $warehouse = $this->mWarehouses->getById($id);
        $this->render('v_warehouse_edit', ['warehouse' => $warehouse]);
    }

    public function deleteAction() {
        header('Content-Type: application/json');
        if (!$this->hasPermission('warehouses', 'd')) {
            echo json_encode(['success' => false, 'message' => 'Немає прав для видалення.']); exit();
        }
        $headers = getallheaders();
        $tokenFromHeader = $headers['X-CSRF-TOKEN'] ?? '';
        if (empty($tokenFromHeader) || !hash_equals($_SESSION['csrf_token'], $tokenFromHeader)) {
            echo json_encode(['success' => false, 'message' => 'CSRF-помилка.']); exit();
        }
        $id = (int)($this->params['id'] ?? 0);
        if ($this->mWarehouses->deleteById($id)) {
            $_SESSION['flash_message'] = ['type' => 'success', 'text' => 'Склад успішно видалено.'];
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Не вдалося видалити склад.']);
        }
        exit();
    }
}