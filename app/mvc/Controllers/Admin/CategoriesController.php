<?php
// ===================================================================
// Файл: mvc/c_categories.php 
// ===================================================================
namespace App\Mvc\Controllers\Admin;

use App\Mvc\Models\Categories;
use App\Mvc\Models\Goods;

class CategoriesController extends BaseController
{
    // Видаліть рядки "use" звідси

    public function __construct($params)
    {
        parent::__construct($params);
        $this->mCategories = new Categories();
        $this->mGoods = new Goods();
    }

    public function indexAction()
    {
        if (!$this->hasPermission('categories', 'v')) {
            return $this->showAccessDenied();
        }
        $this->title = 'Керування категоріями';
        $allCategories = $this->mCategories->getAll();
        $categoriesTree = $this->mCategories->buildTree($allCategories);
        $this->render('v_categories_list', ['categoriesTree' => $categoriesTree, 'controller' => $this]);
    }
    
    public function addAction()
    {
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
    
    public function editAction()
    {
        if (!$this->hasPermission('categories', 'e')) {
            return $this->showAccessDenied();
        }
        
        $id = (int)($this->params['id'] ?? 0);
        $this->title = 'Редагування категорії';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // ... (код обробки POST-запиту без змін) ...
        }
        
        $category = $this->mCategories->getById($id);
        $availableParents = $this->mCategories->getAvailableParents($id);

        $this->render('v_category_edit', [
            'category' => $category, 
            'categories' => $availableParents // Передаємо у шаблон відфільтрований список
        ]);
    }

    public function deleteAction()
    {
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

    public function watchAction()
    {
        if (!$this->hasPermission('categories', 'v')) {
            return $this->showAccessDenied();
        }

        $id = (int)($this->params['id'] ?? 0);
        $category = $this->mCategories->getById($id);

        if (!$category) {
            $this->notFoundAction();
            return;
        }

        $this->title = 'Перегляд категорії: ' . htmlspecialchars($category['name']);

        // === НОВИЙ РЯДОК: Отримуємо прямих нащадків (дочірні категорії) ===
        $children = $this->mCategories->getChildren($id);
        
        // Отримуємо ID всіх вкладених підкатегорій для товарів
        $allSubCategoryIds = $this->mCategories->getDescendantIds($id);
        $allCategoryIds = array_merge([$id], $allSubCategoryIds);
        
        $goodsInCategory = $this->mGoods->getByCategoryIds($allCategoryIds);

        // === ЗМІНЕНО: Додаємо змінну $children до масиву, що передається у шаблон ===
        $this->render('v_category_single', [
            'category' => $category,
            'goods' => $goodsInCategory,
            'children' => $children 
        ]);
    }
}