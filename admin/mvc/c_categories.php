<?php
// ===================================================================
// Файл: mvc/c_categories.php 🆕
// ===================================================================

class C_Categories extends C_Base {
    protected $mCategories;

    public function __construct($params) {
        parent::__construct($params);
        $this->mCategories = new M_Categories();
    }

    public function indexAction() {
        if (!$this->hasPermission('categories', 'v')) {
            return $this->showAccessDenied();
        }
        $this->title = 'Керування категоріями';
        $allCategories = $this->mCategories->getAll();
        $categoriesTree = $this->mCategories->buildTree($allCategories);
        $this->render('v_categories_list', ['categoriesTree' => $categoriesTree, 'controller' => $this]);
    }
    
    public function addAction() {
        if (!$this->hasPermission('categories', 'a')) {
            return $this->showAccessDenied();
        }
        $this->title = 'Додавання нової категорії';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                die('CSRF token validation failed!');
            }
            if (!empty($_POST['name'])) {
                $postData = $_POST;
                $postData['is_active'] = isset($_POST['is_active']) ? 1 : 0; // Обробка значення чекбокса
                $this->mCategories->add($postData);
                $_SESSION['flash_message'] = ['type' => 'success', 'text' => 'Нову категорію успішно додано.'];
                header('Location: ' . BASE_URL . '/categories');
                exit();
            } else {
                $_SESSION['flash_message'] = ['type' => 'error', 'text' => 'Назва категорії не може бути порожньою.'];
            }
        }
        
        $categories = $this->mCategories->getAll();
        $this->render('v_category_add', ['categories' => $categories]);
    }
    
    public function editAction() {
        if (!$this->hasPermission('categories', 'e')) {
            return $this->showAccessDenied();
        }

        $id = (int)($this->params['id'] ?? 0);
        $this->title = 'Редагування категорії';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                die('CSRF token validation failed!');
            }
            if (!empty($_POST['name'])) {
                $postData = $_POST;
                $postData['is_active'] = isset($_POST['is_active']) ? 1 : 0; // Обробка значення чекбокса
                $this->mCategories->update($id, $postData);
                $_SESSION['flash_message'] = ['type' => 'success', 'text' => 'Категорію успішно оновлено.'];
            } else {
                $_SESSION['flash_message'] = ['type' => 'error', 'text' => 'Назва категорії не може бути порожньою.'];
            }
            header('Location: ' . BASE_URL . '/categories/edit/' . $id);
            exit();
        }
        
        $category = $this->mCategories->getById($id);
        $categories = $this->mCategories->getAll();
        $this->render('v_category_edit', ['category' => $category, 'categories' => $categories]);
    }

    public function deleteAction() {
        header('Content-Type: application/json');

        if (!$this->hasPermission('categories', 'd')) {
            echo json_encode(['success' => false, 'message' => 'У вас немає прав для виконання цієї дії.']);
            exit();
        }

        $headers = getallheaders();
        $tokenFromHeader = $headers['X-CSRF-TOKEN'] ?? $headers['x-csrf-token'] ?? '';
        if (empty($tokenFromHeader) || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $tokenFromHeader)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Помилка валідації CSRF-токена.']);
            exit();
        }

        $id = (int)($this->params['id'] ?? 0);
        $result = $this->mCategories->deleteById($id);

        if ($result['success']) {
            $_SESSION['flash_message'] = ['type' => 'success', 'text' => 'Категорію успішно видалено.'];
            echo json_encode(['success' => true]);
        } else {
            $message = 'Не вдалося видалити категорію з невідомої причини.';
            if (isset($result['reason'])) {
                if ($result['reason'] === 'children') {
                    $message = 'Не можна видалити категорію, оскільки вона містить підкатегорії.';
                } elseif ($result['reason'] === 'products') {
                    $message = 'Не можна видалити категорію, оскільки до неї прив\'язані товари.';
                }
            }
            echo json_encode(['success' => false, 'message' => $message]);
        }
        
        exit();
    }

    public function watchAction() {
        if (!$this->hasPermission('categories', 'v')) {
            return $this->showAccessDenied();
        }

        $id = (int)($this->params['id'] ?? 0);
        
        // Отримуємо дані поточної категорії
        $category = $this->mCategories->getById($id);
        $this->title = $category ? 'Перегляд категорії: ' . htmlspecialchars($category['name']) : 'Категорію не знайдено';

        // Отримуємо всіх предків
        $ancestors = $this->mCategories->getAncestors($id);
        
        // Отримуємо дочірні категорії та товари (як і раніше)
        $children = $this->mCategories->getChildren($id);
        $allCategories = $this->mCategories->getAll();
        $categoryIds = $this->mCategories->getDescendantIds($id, $allCategories);

        if (!isset($this->mGoods)) {
            require_once ROOT . '/mvc/m_goods.php';
            $this->mGoods = new M_Goods();
        }
        $goods = $this->mGoods->getByCategoryIds($categoryIds);
        
        // Передаємо у вид дані про категорію, її предків, нащадків та товари
        $this->render('v_category_single', [
            'category' => $category,
            'ancestors' => $ancestors, // Новий масив з предками
            'children' => $children,
            'goods' => $goods
        ]);
    }
}