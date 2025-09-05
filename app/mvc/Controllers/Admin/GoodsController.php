<?php
// app/Mvc/Controllers/Admin/GoodsController.php
namespace App\Mvc\Controllers\Admin;

use App\Mvc\Models\Goods;
use App\Mvc\Models\Categories;
use App\Mvc\Models\Warehouses;
use App\Mvc\Models\Options;
use App\Mvc\Models\Attributes;
use App\Mvc\Models\Currencies;

class GoodsController extends BaseController  {
    protected $mGoods;
    protected $mCategories;
    protected $mWarehouses;
    protected $mOptions;
    protected $mAttributes;

    public function __construct($params) {
        parent::__construct($params);
        $this->mGoods = new Goods();
        $this->mCategories = new Categories();
        $this->mWarehouses = new Warehouses();
        $this->mOptions = new Options();
        $this->mAttributes = new Attributes();
    }

    public function indexAction()
    {
        if (!$this->hasPermission('goods', 'v')) return $this->showAccessDenied();
        $this->title = 'Керування товарами';
        $goods = $this->mGoods->getAll();
        $this->render('v_goods_list', ['goods' => $goods]);
    }
    
    public function watchAction()
    {
        if (!$this->hasPermission('goods', 'v')) {
            return $this->showAccessDenied();
        }
        $id = (int)($this->params['id'] ?? 0);
        
        $good = $this->mGoods->getById($id);
        $stockLevels = $this->mGoods->getCurrentStockByWarehouses($id);
        $history = $this->mGoods->getTransactionHistory($id);

        $this->title = $good ? 'Перегляд товару: ' . htmlspecialchars($good['name']) : 'Товар не знайдено';
        
        $this->render('v_good_single', [
            'good' => $good,
            'stockLevels' => $stockLevels,
            'history' => $history
        ]);
    }

    public function addAction()
    {
        if (!$this->hasPermission('goods', 'a')) return $this->showAccessDenied();
        $this->title = 'Додавання нового товару';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) die('CSRF Error');
            
            $data = $this->preparePostData();
            if (!empty($data['name']) && $data['category_id'] > 0) {
                $newGoodId = $this->mGoods->add($data);
                if ($newGoodId) {
                    $this->mGoods->saveAttributesForProduct($newGoodId, $_POST['attributes'] ?? []);
                    $this->mGoods->saveOptionsForProduct($newGoodId, $_POST['options'] ?? []);

                    $_SESSION['flash_message'] = ['type' => 'success', 'text' => 'Новий товар успішно додано.'];
                    header('Location: ' . BASE_URL . '/goods/edit/' . $newGoodId);
                    exit();
                }
            }
             $_SESSION['flash_message'] = ['type' => 'error', 'text' => 'Помилка: заповніть усі обов\'язкові поля.'];
        }

        $this->addJS(PROJECT_URL . '/resources/js/product-edit.js');
        
        $mCurrencies = new Currencies();

        $this->render('v_good_add', [
            'categories' => $this->mCategories->getAll(),
            'attributes' => $this->mAttributes->getAll(),
            'options' => $this->mOptions->getAllGroupsWithValues(),
            'currencies' => $mCurrencies->getAll()
        ]);
    }
    
    public function editAction()
    {
        if (!$this->hasPermission('goods', 'e')) return $this->showAccessDenied();
        $id = (int)($this->params['id'] ?? 0);
        $this->title = 'Редагування товару';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) die('CSRF Error');
            
            $data = $this->preparePostData();
            if (!empty($data['name']) && $data['category_id'] > 0) {
                if ($this->mGoods->update($id, $data)) {
                    $this->mGoods->saveAttributesForProduct($id, $_POST['attributes'] ?? []);
                    $this->mGoods->saveOptionsForProduct($id, $_POST['options'] ?? []);
                    $_SESSION['flash_message'] = ['type' => 'success', 'text' => 'Товар успішно оновлено.'];
                } else {
                    $_SESSION['flash_message'] = ['type' => 'error', 'text' => 'Не вдалося оновити товар.'];
                }
            } else {
                 $_SESSION['flash_message'] = ['type' => 'error', 'text' => 'Помилка: заповніть усі обов\'язкові поля.'];
            }
            header('Location: ' . BASE_URL . '/goods/edit/' . $id);
            exit();
        }
        
        $this->addJS(PROJECT_URL . '/resources/js/product-edit.js');
        
        $mCurrencies = new Currencies();
        $good = $this->mGoods->getById($id);

        $this->render('v_good_edit', [
            'good' => $good,
            'categories' => $this->mCategories->getAll(),
            'attributes' => $this->mAttributes->getAll(),
            'options' => $this->mOptions->getAllGroupsWithValues(),
            'good_attributes' => $this->mGoods->getAttributesForProduct($id),
            'good_options' => $this->mGoods->getOptionsForProduct($id),
            'currencies' => $mCurrencies->getAll()
        ]);
    }

    public function deleteAction() {
        header('Content-Type: application/json');
        if (!$this->hasPermission('goods', 'd')) {
            echo json_encode(['success' => false, 'message' => 'Немає прав для видалення.']); exit();
        }
        $headers = getallheaders();
        $tokenFromHeader = $headers['X-CSRF-TOKEN'] ?? '';
        if (empty($tokenFromHeader) || !hash_equals($_SESSION['csrf_token'], $tokenFromHeader)) {
            echo json_encode(['success' => false, 'message' => 'CSRF-помилка.']); exit();
        }
        $id = (int)($this->params['id'] ?? 0);
        if ($this->mGoods->deleteById($id)) {
            $_SESSION['flash_message'] = ['type' => 'success', 'text' => 'Товар успішно видалено.'];
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Не вдалося видалити товар.']);
        }
        exit();
    }
    
    /**
     * Метод для AJAX-запитів, повертає товари на складі.
     */
    public function getByWarehouseAction()
    {
        header('Content-Type: application/json');
        $warehouseId = (int)($_GET['warehouse_id'] ?? 0);
        if ($warehouseId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Не вказано ID складу.']);
            exit();
        }
        $goods = $this->mGoods->getGoodsInStockByWarehouse($warehouseId);
        echo json_encode(['success' => true, 'goods' => $goods]);
        exit();
    }

    public function getWarehousesForGoodAction()
    {
        header('Content-Type: application/json');
        $goodId = (int)($_GET['good_id'] ?? 0);
        if ($goodId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Не вказано ID товару.']);
            exit();
        }
        $warehouses = $this->mGoods->getWarehousesWithStockForGood($goodId);
        echo json_encode(['success' => true, 'warehouses' => $warehouses]);
        exit();
    }

    private function preparePostData(): array
    {
        return [
            'name' => trim($_POST['name'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'keywords' => trim($_POST['keywords'] ?? ''),
            'price' => (float)($_POST['price'] ?? 0),
            'length' => (float)($_POST['length'] ?? 0),
            'width' => (float)($_POST['width'] ?? 0),
            'height' => (float)($_POST['height'] ?? 0),
            'weight' => (float)($_POST['weight'] ?? 0),
            'category_id' => (int)($_POST['category_id'] ?? 0),
            'is_active' => isset($_POST['is_active']) ? 1 : 0
        ];
    }
}