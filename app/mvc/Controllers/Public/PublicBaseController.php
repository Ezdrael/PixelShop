<?php
// app/Mvc/Controllers/Public/PublicBaseController.php
namespace App\Mvc\Controllers\Public;

use App\Core\Controller;
use App\Core\View;
use App\Mvc\Models\Categories;

abstract class PublicBaseController extends Controller
{
    protected $view;
    protected $title;
    protected $pageCSS = [];
    protected $pageJS = [];
    protected $layoutVars = [];

    public function __construct()
    {
        parent::__construct();
        // Ініціалізуємо шаблонізатор Twig для теми 'default'
        $this->view = new View('default');
        $this->title = 'PixelShop';

        // Завантажуємо категорії для меню на всіх сторінках
        $mCategories = new Categories();
        $allCategories = $mCategories->getAllForMenu();
        $this->layoutVars['categoryTree'] = $this->buildCategoryTree($allCategories);
    }
    
    protected function addCSS($path)
    {
        $this->pageCSS[] = $path;
    }

    protected function addJS($path)
    {
        $this->pageJS[] = $path;
    }

    /**
     * ✅ ВИПРАВЛЕНО: Метод тепер використовує Twig для рендерингу
     */
    protected function render(string $template, array $data = [])
    {
        // Автоматично додаємо глобальні змінні до всіх шаблонів
        $defaultData = [
            'title' => $this->title,
            'BASE_URL' => BASE_URL,
            'PROJECT_URL' => PROJECT_URL,
            'pageCSS' => $this->pageCSS,
            'pageJS' => $this->pageJS
        ];
        
        $finalData = array_merge($data, $this->layoutVars, $defaultData);

        $this->view->render($template, $finalData);
    }

    private function buildCategoryTree(array $categories, $parentId = null): array
    {
        $branch = [];
        foreach ($categories as $category) {
            if ($category['parent_id'] == $parentId) {
                $children = $this->buildCategoryTree($categories, $category['id']);
                if ($children) {
                    $category['children'] = $children;
                }
                $branch[] = $category;
            }
        }
        return $branch;
    }
    
    public function notFoundAction()
    {
        $this->title = '404 - Сторінку не знайдено';
        // Рендеримо новий Twig-шаблон для 404 сторінки
        $this->render('404.html.twig', []);
    }
}