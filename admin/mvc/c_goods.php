<?php
// ===================================================================
// Файл: mvc/c_goods.php 🆕
// ===================================================================

class C_Goods extends C_Base {
    protected $mGoods;
    protected $mCategories;

    public function __construct($params) {
        parent::__construct($params);
        $this->mGoods = new M_Goods();
        $this->mCategories = new M_Categories(); // Для вибору категорії
    }

    public function indexAction() {
        if (!$this->hasPermission('goods', 'v')) return $this->showAccessDenied();
        $this->title = 'Керування товарами';
        $goods = $this->mGoods->getAll();
        $this->render('v_goods_list', ['goods' => $goods]);
    }
    
    public function watchAction() {
        if (!$this->hasPermission('goods', 'v')) {
            return $this->showAccessDenied();
        }

        $id = (int)($this->params['id'] ?? 0);
        
        // Отримуємо всі необхідні дані
        $good = $this->mGoods->getById($id);
        $stockLevels = $this->mGoods->getCurrentStockByWarehouses($id);
        $history = $this->mGoods->getTransactionHistory($id); // Нові дані

        $this->title = $good ? 'Перегляд товару: ' . htmlspecialchars($good['name']) : 'Товар не знайдено';
        
        // Передаємо всі дані у вид
        $this->render('v_good_single', [
            'good' => $good,
            'stockLevels' => $stockLevels,
            'history' => $history // Новий масив з історією
        ]);
    }

    public function addAction() {
        if (!$this->hasPermission('goods', 'a')) return $this->showAccessDenied();
        $this->title = 'Додавання нового товару';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) die('CSRF Error');
            // Збираємо дані
            $data = [
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
            if (!empty($data['name']) && $data['category_id'] > 0) {
                // Логіка для addAction()
                if (strpos($this->title, 'Додавання') !== false) {
                    if ($this->mGoods->add($data)) {
                        $_SESSION['flash_message'] = ['type' => 'success', 'text' => 'Новий товар успішно додано.'];
                        header('Location: ' . BASE_URL . '/goods');
                        exit();
                    }
                // Логіка для editAction()
                } else {
                    if ($this->mGoods->update($id, $data)) {
                        $_SESSION['flash_message'] = ['type' => 'success', 'text' => 'Товар успішно оновлено.'];
                    }
                }
            } else {
                $_SESSION['flash_message'] = ['type' => 'error', 'text' => 'Будь ласка, заповніть всі обов\'язкові поля.'];
            }
            // Редірект
            if (strpos($this->title, 'Додавання') !== false) {
                header('Location: ' . BASE_URL . '/goods/add');
            } else {
                header('Location: ' . BASE_URL . '/goods/edit/' . $id);
            }
            exit();
        }
        
        $categories = $this->mCategories->getAll();
        $this->render('v_good_add', ['categories' => $categories]);
    }
    
    public function editAction() {
        if (!$this->hasPermission('goods', 'e')) return $this->showAccessDenied();
        $id = (int)($this->params['id'] ?? 0);
        $this->title = 'Редагування товару';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) die('CSRF Error');
            // Збираємо дані
            $data = [
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
            if (!empty($data['name']) && $data['category_id'] > 0) {
                // Логіка для addAction()
                if (strpos($this->title, 'Додавання') !== false) {
                    if ($this->mGoods->add($data)) {
                        $_SESSION['flash_message'] = ['type' => 'success', 'text' => 'Новий товар успішно додано.'];
                        header('Location: ' . BASE_URL . '/goods');
                        exit();
                    }
                // Логіка для editAction()
                } else {
                    if ($this->mGoods->update($id, $data)) {
                        $_SESSION['flash_message'] = ['type' => 'success', 'text' => 'Товар успішно оновлено.'];
                    }
                }
            } else {
                $_SESSION['flash_message'] = ['type' => 'error', 'text' => 'Будь ласка, заповніть всі обов\'язкові поля.'];
            }
            // Редірект
            if (strpos($this->title, 'Додавання') !== false) {
                header('Location: ' . BASE_URL . '/goods/add');
            } else {
                header('Location: ' . BASE_URL . '/goods/edit/' . $id);
            }
            exit();
        }
        
        $good = $this->mGoods->getById($id);
        $categories = $this->mCategories->getAll();
        $this->render('v_good_edit', ['good' => $good, 'categories' => $categories]);
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
}